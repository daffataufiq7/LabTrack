<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $dariTanggal = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');

        $query = Peminjaman::with(['mahasiswa', 'detailPeminjaman.alat']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('mahasiswa', function ($m) use ($search) {
                      $m->where('nama', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                  });
            });
        }

        if ($statusFilter) {
            if ($statusFilter === 'terlambat') {
                $query->where('status', 'dipinjam')
                      ->where('tanggal_jatuh_tempo', '<', Carbon::today());
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if ($dariTanggal) {
            $query->whereDate('tanggal_pinjam', '>=', $dariTanggal);
        }

        if ($sampaiTanggal) {
            $query->whereDate('tanggal_pinjam', '<=', $sampaiTanggal);
        }

        $peminjaman = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return view('peminjaman.index', compact('peminjaman', 'search', 'statusFilter', 'dariTanggal', 'sampaiTanggal'));
    }

    public function create()
    {
        $mahasiswa = Mahasiswa::orderBy('nama', 'asc')->get();
        // Alat yang memiliki stok tersedia minimal 1
        $alat = Alat::where('stok_tersedia', '>', 0)->orderBy('nama_alat', 'asc')->get();

        return view('peminjaman.create', compact('mahasiswa', 'alat'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input Form
        $validated = $request->validate([
            'mahasiswa_id' => ['required', 'exists:mahasiswa,id'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_jatuh_tempo' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.alat_id' => ['required', 'distinct', 'exists:alat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'mahasiswa_id.required' => 'Mahasiswa peminjam wajib dipilih.',
            'mahasiswa_id.exists' => 'Mahasiswa yang dipilih tidak valid.',
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'tanggal_jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.',
            'items.required' => 'Minimal satu jenis alat harus dipilih.',
            'items.min' => 'Minimal satu jenis alat harus dipilih.',
            'items.*.alat_id.required' => 'Alat wajib dipilih.',
            'items.*.alat_id.distinct' => 'Terdapat duplikasi alat dalam satu transaksi.',
            'items.*.jumlah.required' => 'Jumlah alat wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah alat harus berupa angka positif minimal 1.',
        ]);

        // 2. Validasi Aturan Bisnis Khusus (Batas Kuota & Tunggakan Keterlambatan)
        // A. Cek apakah mahasiswa memiliki pinjaman aktif yang sudah terlambat (overdue)
        $pinjamanTerlambat = Peminjaman::where('mahasiswa_id', $validated['mahasiswa_id'])
            ->where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', Carbon::today())
            ->first();

        if ($pinjamanTerlambat) {
            $mhs = Mahasiswa::find($validated['mahasiswa_id']);
            return back()->withInput()->with('error', "Peminjaman ditolak: Mahasiswa {$mhs->nama} masih memiliki transaksi peminjaman aktif yang telah melewati jatuh tempo ({$pinjamanTerlambat->kode_transaksi}). Harap selesaikan pengembalian sebelumnya terlebih dahulu.");
        }

        // A2. Cek apakah mahasiswa memiliki tunggakan denda/kerusakan yang BELUM LUNAS
        $tunggakanDenda = Peminjaman::where('mahasiswa_id', $validated['mahasiswa_id'])
            ->where('status_pembayaran', 'belum_lunas')
            ->first();

        if ($tunggakanDenda) {
            $mhs = Mahasiswa::find($validated['mahasiswa_id']);
            return back()->withInput()->with('error', "Peminjaman ditolak: Mahasiswa {$mhs->nama} masih memiliki tagihan denda/biaya perbaikan yang BELUM LUNAS pada transaksi ({$tunggakanDenda->kode_transaksi}) sebesar Rp " . number_format($tunggakanDenda->totalTagihan(), 0, ',', '.') . ". Harap selesaikan pembayaran terlebih dahulu.");
        }

        // B. Cek batas maksimal kuantitas unit per transaksi (Maksimal 10 unit total)
        $totalKuantitas = array_sum(array_column($validated['items'], 'jumlah'));
        if ($totalKuantitas > 10) {
            return back()->withInput()->with('error', "Peminjaman ditolak: Total kuantitas alat yang dipinjam dalam satu transaksi maksimal 10 unit (saat ini diminta: {$totalKuantitas} unit).");
        }

        // C. Cek batas transaksi aktif per mahasiswa (Maksimal 2 transaksi aktif)
        $jumlahTransaksiAktif = Peminjaman::where('mahasiswa_id', $validated['mahasiswa_id'])
            ->where('status', 'dipinjam')
            ->count();

        if ($jumlahTransaksiAktif >= 2) {
            return back()->withInput()->with('error', "Peminjaman ditolak: Mahasiswa telah memiliki 2 transaksi peminjaman aktif. Harap kembalikan alat sebelumnya sebelum meminjam kembali.");
        }

        try {
            $peminjaman = DB::transaction(function () use ($validated) {
                // Kunci dan cek ketersediaan stok semua alat terlebih dahulu (Row Locking)
                $alatModels = [];
                foreach ($validated['items'] as $itemData) {
                    $alat = Alat::where('id', $itemData['alat_id'])->lockForUpdate()->first();

                    if (!$alat) {
                        throw new \Exception("Data alat dengan ID {$itemData['alat_id']} tidak ditemukan.");
                    }

                    if ($alat->stok_tersedia < $itemData['jumlah']) {
                        throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi! Tersedia: {$alat->stok_tersedia}, Diminta: {$itemData['jumlah']}.");
                    }

                    $alatModels[] = [
                        'model' => $alat,
                        'jumlah' => $itemData['jumlah'],
                    ];
                }

                // Buat Header Transaksi Peminjaman
                $peminjaman = Peminjaman::create([
                    'kode_transaksi' => Peminjaman::generateKodeTransaksi(),
                    'mahasiswa_id' => $validated['mahasiswa_id'],
                    'tanggal_pinjam' => $validated['tanggal_pinjam'],
                    'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                    'status' => 'dipinjam',
                    'total_denda' => 0,
                    'total_biaya_perbaikan' => 0,
                ]);

                // Buat Detail Peminjaman dan Kurangi Stok Tersedia
                foreach ($alatModels as $item) {
                    $units = \App\Models\AlatUnit::where('alat_id', $item['model']->id)
                        ->where('status', 'tersedia')
                        ->limit($item['jumlah'])
                        ->get();

                    $unitCodeList = $units->pluck('kode_unit')->implode(', ');

                    DetailPeminjaman::create([
                        'peminjaman_id' => $peminjaman->id,
                        'alat_id' => $item['model']->id,
                        'jumlah' => $item['jumlah'],
                        'kode_unit_dipinjam' => $unitCodeList ?: null,
                        'kondisi_kembali' => null,
                        'biaya_perbaikan' => 0,
                    ]);

                    foreach ($units as $u) {
                        $u->update([
                            'status' => 'dipinjam',
                            'peminjaman_id' => $peminjaman->id,
                        ]);
                    }

                    // Kurangi stok tersedia
                    $item['model']->decrement('stok_tersedia', $item['jumlah']);
                }

                return $peminjaman;
            });

            return redirect()->route('peminjaman.show', $peminjaman->id)
                ->with('success', "Transaksi peminjaman [{$peminjaman->kode_transaksi}] berhasil dibuat dan stok telah dialokasikan.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses peminjaman: ' . $e->getMessage());
        }
    }

    public function show(Peminjaman $peminjaman)
    {
        $peminjaman->load(['mahasiswa', 'detailPeminjaman.alat']);
        return view('peminjaman.show', compact('peminjaman'));
    }

    /**
     * Tampilan cetak nota bukti transaksi peminjaman & pengembalian (struk bebas lab / bukti denda).
     */
    public function nota(Peminjaman $peminjaman)
    {
        $peminjaman->load(['mahasiswa', 'detailPeminjaman.alat']);
        return view('peminjaman.nota', compact('peminjaman'));
    }

    public function cancel(Peminjaman $peminjaman)
    {
        if ($peminjaman->status !== 'dipinjam') {
            return back()->with('error', 'Hanya transaksi berstatus aktif (dipinjam) yang dapat dibatalkan.');
        }

        try {
            DB::transaction(function () use ($peminjaman) {
                $peminjamanLocked = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->first();
                if ($peminjamanLocked->status !== 'dipinjam') {
                    throw new \Exception('Status transaksi sudah berubah.');
                }

                // Kembalikan alokasi unit fisik
                \App\Models\AlatUnit::where('peminjaman_id', $peminjamanLocked->id)
                    ->update([
                        'status' => 'tersedia',
                        'peminjaman_id' => null,
                    ]);

                // Kembalikan alokasi stok untuk semua detail alat
                foreach ($peminjamanLocked->detailPeminjaman as $detail) {
                    $alat = Alat::where('id', $detail->alat_id)->lockForUpdate()->first();
                    if ($alat) {
                        $alat->increment('stok_tersedia', $detail->jumlah);
                    }
                }

                $peminjamanLocked->update([
                    'status' => 'dibatalkan',
                ]);
            });

            return redirect()->route('peminjaman.index')
                ->with('success', "Transaksi [{$peminjaman->kode_transaksi}] berhasil dibatalkan dan alokasi stok telah dipulihkan.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar permohonan pinjam yang menunggu persetujuan (ACC) admin.
     */
    public function antreanAcc(Request $request)
    {
        $antrean = Peminjaman::with(['mahasiswa', 'detailPeminjaman.alat'])
            ->where('status', 'menunggu_acc')
            ->orderBy('id', 'asc')
            ->get();

        return view('peminjaman.antrean_acc', compact('antrean'));
    }

    /**
     * Menyetujui (ACC) permohonan peminjaman dari mahasiswa dan memotong stok alat secara atomik.
     */
    public function approve(Request $request, Peminjaman $peminjaman)
    {
        if ($peminjaman->status !== 'menunggu_acc') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu persetujuan yang dapat di-ACC.');
        }

        try {
            DB::transaction(function () use ($peminjaman, $request) {
                $peminjamanLocked = Peminjaman::where('id', $peminjaman->id)->lockForUpdate()->firstOrFail();

                if ($peminjamanLocked->status !== 'menunggu_acc') {
                    throw new \Exception('Status permohonan ini sudah berubah.');
                }

                // Cek ketersediaan stok fisik setiap alat dan potong stok
                foreach ($peminjamanLocked->detailPeminjaman as $detail) {
                    $alat = Alat::where('id', $detail->alat_id)->lockForUpdate()->firstOrFail();

                    if ($alat->stok_tersedia < $detail->jumlah) {
                        throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi untuk di-ACC! Tersedia: {$alat->stok_tersedia}, Diminta: {$detail->jumlah}.");
                    }

                    // Alokasikan unit fisik berstatus 'tersedia'
                    $units = \App\Models\AlatUnit::where('alat_id', $detail->alat_id)
                        ->where('status', 'tersedia')
                        ->limit($detail->jumlah)
                        ->get();

                    $unitCodeList = $units->pluck('kode_unit')->implode(', ');
                    $detail->update(['kode_unit_dipinjam' => $unitCodeList]);

                    foreach ($units as $u) {
                        $u->update([
                            'status' => 'dipinjam',
                            'peminjaman_id' => $peminjamanLocked->id,
                        ]);
                    }

                    $alat->decrement('stok_tersedia', $detail->jumlah);
                }

                // Update status transaksi menjadi aktif dipinjam
                $peminjamanLocked->update([
                    'status' => 'dipinjam',
                    'approved_at' => now(),
                    'catatan_admin' => $request->input('catatan_admin') ?: 'Disetujui oleh Petugas Laboratorium.',
                ]);
            });

            return redirect()->route('peminjaman.antrean_acc')
                ->with('success', "Permohonan [{$peminjaman->kode_transaksi}] berhasil DISETUJUI (ACC). Stok alat telah dialokasikan.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Menolak permohonan peminjaman dari mahasiswa.
     */
    public function reject(Request $request, Peminjaman $peminjaman)
    {
        if ($peminjaman->status !== 'menunggu_acc') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu persetujuan yang dapat ditolak.');
        }

        $request->validate([
            'catatan_admin' => ['required', 'string', 'max:255'],
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi agar mahasiswa mengetahuinya.',
        ]);

        $peminjaman->update([
            'status' => 'ditolak',
            'catatan_admin' => $request->input('catatan_admin'),
        ]);

        return redirect()->route('peminjaman.antrean_acc')
            ->with('info', "Permohonan [{$peminjaman->kode_transaksi}] telah DITOLAK. Alasan tercatat untuk mahasiswa.");
    }
}
