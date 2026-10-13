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
     * Eksekusi transaksi pengembalian dengan aturan bisnis, row locking, dan kalkulasi denda & biaya kerusakan.
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
            DB::transaction(function () use ($peminjaman, $validated) {
                // 2. Kunci baris peminjaman untuk mencegah Race Condition & Pengembalian Ganda
                $lockedPeminjaman = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->firstOrFail();

                if ($lockedPeminjaman->status !== 'dipinjam') {
                    throw new \Exception('Transaksi ini sudah diproses pengembaliannya sebelumnya (pencegahan pengembalian ganda).');
                }

                $tglKembali = Carbon::parse($validated['tanggal_kembali'])->startOfDay();
                $tglTempo = $lockedPeminjaman->tanggal_jatuh_tempo->copy()->startOfDay();

                // 3. Kalkulasi Hari Terlambat & Denda (Sesuai Aturan Bisnis)
                // Masa tenggang: 3 hari
                // Hari terlambat kena denda = max(0, selisih hari - 3)
                // Denda = hari kena denda × Rp2.000
                $selisihHari = $tglKembali->gt($tglTempo) ? (int) $tglTempo->diffInDays($tglKembali) : 0;
                $hariKenaDenda = max(0, $selisihHari - Peminjaman::HARI_MASA_TENGGANG);
                $totalDenda = $hariKenaDenda * Peminjaman::TARIF_DENDA_PER_HARI;

                $totalBiayaPerbaikan = 0;

                // 4. Proses Setiap Detail Alat yang Dipinjam
                foreach ($lockedPeminjaman->detailPeminjaman as $detail) {
                    $inputDetail = $validated['detail'][$detail->id] ?? null;
                    $jumlahRusak = $inputDetail ? (int) $inputDetail['jumlah_rusak'] : 0;
                    $catatanKondisi = $inputDetail['kondisi_catatan'] ?? null;

                    // Validasi: jumlah rusak tidak boleh melebihi jumlah pinjam
                    if ($jumlahRusak > $detail->jumlah) {
                        throw new \Exception("Jumlah rusak ({$jumlahRusak}) melebihi jumlah yang dipinjam ({$detail->jumlah}) untuk alat: {$detail->alat->nama_alat}.");
                    }

                    $jumlahLayak = $detail->jumlah - $jumlahRusak;

                    // Biaya perbaikan: Rp10.000 per unit rusak
                    $biayaPerbaikanItem = $jumlahRusak * Peminjaman::BIAYA_PERBAIKAN_PER_UNIT;
                    $totalBiayaPerbaikan += $biayaPerbaikanItem;

                    // Ringkasan kondisi kembali
                    $ringkasanKondisi = $jumlahRusak > 0
                        ? "{$jumlahLayak} Baik, {$jumlahRusak} Rusak" . ($catatanKondisi ? " ({$catatanKondisi})" : '')
                        : "Baik (" . ($catatanKondisi ?: 'Lengkap & Normal') . ")";

                    // Update detail peminjaman
                    $detail->update([
                        'kondisi_kembali' => $ringkasanKondisi,
                        'biaya_perbaikan' => $biayaPerbaikanItem,
                    ]);

                    // ATURAN RESTORASI STOK:
                    // "Jangan pulihkan stok untuk unit rusak; pulihkan stok hanya untuk unit yang kembali layak digunakan."
                    // Pulihkan HANYA unit yang layak ($jumlahLayak)
                    $alat = Alat::where('id', $detail->alat_id)->lockForUpdate()->firstOrFail();

                    if ($jumlahLayak > 0) {
                        // Pastikan penambahan tidak melebihi stok total (cegah stok overflow/negatif)
                        $stokBaru = min($alat->stok_total, $alat->stok_tersedia + $jumlahLayak);
                        $alat->update(['stok_tersedia' => $stokBaru]);
                    }
                }

                // 5. Update Status Header Transaksi
                $lockedPeminjaman->update([
                    'tanggal_kembali' => $tglKembali->toDateString(),
                    'status' => 'dikembalikan',
                    'total_denda' => $totalDenda,
                    'total_biaya_perbaikan' => $totalBiayaPerbaikan,
                ]);
            });

            return redirect()->route('peminjaman.show', $peminjaman->id)
                ->with('success', "Transaksi pengembalian [{$peminjaman->kode_transaksi}] berhasil diproses dengan aman.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}
