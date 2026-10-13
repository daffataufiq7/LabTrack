<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    /**
     * Menampilkan daftar transaksi yang aktif dipinjam dan siap diproses pengembaliannya.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Peminjaman::with(['mahasiswa', 'detailPeminjaman.alat'])
            ->where('status', 'dipinjam');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('mahasiswa', function ($m) use ($search) {
                      $m->where('nama', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                  });
            });
        }

        $peminjamanAktif = $query->orderBy('tanggal_jatuh_tempo', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('pengembalian.index', compact('peminjamanAktif', 'search'));
    }

    /**
     * Form konfirmasi dan inspeksi pengembalian alat.
     */
    public function form(Peminjaman $peminjaman)
    {
        // Cegah memproses transaksi yang sudah bukan 'dipinjam'
        if ($peminjaman->status !== 'dipinjam') {
            return redirect()->route('peminjaman.show', $peminjaman->id)
                ->with('error', 'Transaksi ini tidak dapat diproses karena statusnya adalah: ' . $peminjaman->status);
        }

        $peminjaman->load(['mahasiswa', 'detailPeminjaman.alat']);
        $today = Carbon::today()->format('Y-m-d');

        // Kalkulasi estimasi jika dikembalikan hari ini
        $hariTerlambatHariIni = $peminjaman->hitungHariTerlambat($today);
        $hariKenaDendaHariIni = $peminjaman->hitungHariKenaDenda($today);
        $estimasiDendaHariIni = $peminjaman->hitungDenda($today);

        return view('pengembalian.form', compact(
            'peminjaman',
            'today',
            'hariTerlambatHariIni',
            'hariKenaDendaHariIni',
            'estimasiDendaHariIni'
        ));
    }

    /**
     * Eksekusi transaksi pengembalian dengan aturan bisnis, inspeksi unit rusak, denda, dan pencatatan pembayaran.
     */
    public function process(Request $request, Peminjaman $peminjaman)
    {
        // 1. Validasi Input Form
        $validated = $request->validate([
            'tanggal_kembali' => [
                'required',
                'date',
                'after_or_equal:' . $peminjaman->tanggal_pinjam->format('Y-m-d'),
            ],
            'status_pembayaran' => ['nullable', 'string', 'in:lunas,belum_lunas'],
            'metode_pembayaran' => ['nullable', 'string', 'max:50'],
            'detail' => ['required', 'array'],
            'detail.*.jumlah_rusak' => ['required', 'integer', 'min:0'],
            'detail.*.kondisi_catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal pinjam (' . $peminjaman->tanggal_pinjam->translatedFormat('d F Y') . ').',
            'detail.*.jumlah_rusak.required' => 'Jumlah unit rusak harus diisi (isi 0 jika semua unit baik).',
            'detail.*.jumlah_rusak.min' => 'Jumlah rusak tidak boleh negatif.',
        ]);

        try {
            DB::transaction(function () use ($peminjaman, $validated, $request) {
                // 2. Kunci baris peminjaman untuk mencegah Race Condition & Pengembalian Ganda
                $lockedPeminjaman = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->firstOrFail();

                if ($lockedPeminjaman->status !== 'dipinjam') {
                    throw new \Exception('Transaksi ini sudah diproses pengembaliannya sebelumnya (pencegahan pengembalian ganda).');
                }

                $tglKembali = Carbon::parse($validated['tanggal_kembali'])->startOfDay();
                $tglTempo = $lockedPeminjaman->tanggal_jatuh_tempo->copy()->startOfDay();

                // 3. Kalkulasi Hari Terlambat & Denda (Sesuai Aturan Bisnis)
                $selisihHari = $tglKembali->gt($tglTempo) ? (int) $tglTempo->diffInDays($tglKembali) : 0;
                $hariKenaDenda = max(0, $selisihHari - Peminjaman::HARI_MASA_TENGGANG);
                $totalDenda = $hariKenaDenda * Peminjaman::TARIF_DENDA_PER_HARI;

                $totalBiayaPerbaikan = 0;

                // 4. Proses Setiap Detail Alat yang Dipinjam
                foreach ($lockedPeminjaman->detailPeminjaman as $detail) {
                    $inputDetail = $validated['detail'][$detail->id] ?? null;
                    $jumlahRusak = $inputDetail ? (int) $inputDetail['jumlah_rusak'] : 0;
                    $catatanKondisi = $inputDetail['kondisi_catatan'] ?? null;

                    if ($jumlahRusak > $detail->jumlah) {
                        throw new \Exception("Jumlah rusak ({$jumlahRusak}) melebihi kuantitas pinjam ({$detail->jumlah}) untuk alat: {$detail->alat->nama_alat}.");
                    }

                    $jumlahLayak = $detail->jumlah - $jumlahRusak;

                    // Biaya perbaikan: Rp10.000 per unit rusak
                    $biayaPerbaikanItem = $jumlahRusak * Peminjaman::BIAYA_PERBAIKAN_PER_UNIT;
                    $totalBiayaPerbaikan += $biayaPerbaikanItem;

                    $ringkasanKondisi = $jumlahRusak > 0
                        ? "{$jumlahLayak} Baik, {$jumlahRusak} Rusak" . ($catatanKondisi ? " ({$catatanKondisi})" : '')
                        : "Baik (" . ($catatanKondisi ?: 'Lengkap & Normal') . ")";

                    $detail->update([
                        'kondisi_kembali' => $ringkasanKondisi,
                        'biaya_perbaikan' => $biayaPerbaikanItem,
                    ]);

                    // Update status unit fisik spesifik
                    $assignedUnits = \App\Models\AlatUnit::where('peminjaman_id', $lockedPeminjaman->id)
                        ->where('alat_id', $detail->alat_id)
                        ->get();

                    $rusakCounter = 0;
                    foreach ($assignedUnits as $u) {
                        if ($rusakCounter < $jumlahRusak) {
                            $u->update([
                                'status' => 'rusak',
                                'kondisi' => 'Rusak (' . ($catatanKondisi ?: 'Laporan Pengembalian ' . $lockedPeminjaman->kode_transaksi) . ')',
                                'catatan' => "Tercatat rusak pada pengembalian {$lockedPeminjaman->kode_transaksi}",
                                'peminjaman_id' => null,
                            ]);
                            $rusakCounter++;
                        } else {
                            $u->update([
                                'status' => 'tersedia',
                                'kondisi' => 'Baik',
                                'peminjaman_id' => null,
                            ]);
                        }
                    }

                    // ATURAN RESTORASI STOK:
                    // Pulihkan unit layak ke stok_tersedia, unit rusak dicatat ke stok_rusak
                    $alat = Alat::where('id', $detail->alat_id)->lockForUpdate()->firstOrFail();

                    if ($jumlahLayak > 0) {
                        $stokBaru = min($alat->stok_total, $alat->stok_tersedia + $jumlahLayak);
                        $alat->update(['stok_tersedia' => $stokBaru]);
                    }

                    if ($jumlahRusak > 0) {
                        $alat->increment('stok_rusak', $jumlahRusak);
                    }
                }

                // 5. Penetapan Status & Metode Pembayaran
                $totalKewajiban = $totalDenda + $totalBiayaPerbaikan;
                if ($totalKewajiban <= 0) {
                    // Bebas denda & bebas biaya kerusakan -> otomatis lunas
                    $statusBayar = 'lunas';
                    $metodeBayar = 'Bebas Biaya';
                    $waktuBayar = now();
                } else {
                    // Ada tagihan denda atau kerusakan
                    $statusBayar = $request->input('status_pembayaran', 'lunas');
                    if ($statusBayar === 'lunas') {
                        $metodeBayar = $request->input('metode_pembayaran', 'Tunai (Cash Lab)');
                        $waktuBayar = now();
                    } else {
                        $statusBayar = 'belum_lunas';
                        $metodeBayar = null;
                        $waktuBayar = null;
                    }
                }

                // 6. Update Status Header Transaksi
                $lockedPeminjaman->update([
                    'tanggal_kembali' => $tglKembali->toDateString(),
                    'status' => 'dikembalikan',
                    'total_denda' => $totalDenda,
                    'total_biaya_perbaikan' => $totalBiayaPerbaikan,
                    'status_pembayaran' => $statusBayar,
                    'metode_pembayaran' => $metodeBayar,
                    'waktu_pembayaran' => $waktuBayar,
                ]);
            });

            return redirect()->route('peminjaman.show', $peminjaman->id)
                ->with('success', "Transaksi pengembalian [{$peminjaman->kode_transaksi}] berhasil diproses dengan aman.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    /**
     * Konfirmasi pelunasan pembayaran denda / kerusakan yang sebelumnya tertunggak (belum_lunas).
     */
    public function bayarDenda(Request $request, Peminjaman $peminjaman)
    {
        if ($peminjaman->status !== 'dikembalikan') {
            return back()->with('error', 'Hanya transaksi yang sudah dikembalikan yang dapat diproses pelunasan dendanya.');
        }

        if ($peminjaman->isLunas()) {
            return back()->with('info', 'Tagihan transaksi ini sudah berstatus LUNAS.');
        }

        $validated = $request->validate([
            'metode_pembayaran' => ['required', 'string', 'max:50'],
        ], [
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih (Tunai / QRIS / Transfer).',
        ]);

        $peminjaman->update([
            'status_pembayaran' => 'lunas',
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'waktu_pembayaran' => now(),
        ]);

        return back()->with('success', "Pelunasan tagihan [{$peminjaman->kode_transaksi}] sebesar Rp " . number_format($peminjaman->totalTagihan(), 0, ',', '.') . " berhasil dikonfirmasi LUNAS via {$validated['metode_pembayaran']}.");
    }
}
