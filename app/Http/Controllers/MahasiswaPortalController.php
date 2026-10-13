<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MahasiswaPortalController extends Controller
{
    /**
     * Pastikan mahasiswa sudah terhubung dengan data profil mahasiswa.
     */
    private function getMahasiswa(): Mahasiswa
    {
        $user = Auth::user();
        if (!$user->mahasiswa_id) {
            abort(403, 'Akun pengguna ini belum dihubungkan dengan data profil Mahasiswa.');
        }

        $mahasiswa = Mahasiswa::find($user->mahasiswa_id);
        if (!$mahasiswa) {
            abort(404, 'Data mahasiswa tidak ditemukan.');
        }

        return $mahasiswa;
    }

    /**
     * Dashboard Utama Portal Mahasiswa.
     */
    public function dashboard()
    {
        $mahasiswa = $this->getMahasiswa();

        // 1. Data Peminjaman Aktif (Sedang Dipinjam)
        $pinjamanAktif = Peminjaman::with('detailPeminjaman.alat')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'dipinjam')
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->get();

        // 2. Data Pengajuan Menunggu ACC Admin
        $menungguAcc = Peminjaman::with('detailPeminjaman.alat')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'menunggu_acc')
            ->orderByDesc('id')
            ->get();

        // 3. Riwayat Selesai (Dikembalikan)
        $riwayatSelesai = Peminjaman::with('detailPeminjaman.alat')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'dikembalikan')
            ->orderByDesc('tanggal_kembali')
            ->paginate(5);

        // 4. Ringkasan Finansial Mahasiswa
        $totalDendaDibayar = Peminjaman::where('mahasiswa_id', $mahasiswa->id)->sum('total_denda');
        $totalBiayaKerusakan = Peminjaman::where('mahasiswa_id', $mahasiswa->id)->sum('total_biaya_perbaikan');
        $totalKewajiban = $totalDendaDibayar + $totalBiayaKerusakan;

        // 5. Transaksi Terakhir untuk Timeline Visual
        $latestLoan = Peminjaman::with('detailPeminjaman.alat')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderByDesc('id')
            ->first();

        return view('mahasiswa_portal.dashboard', compact(
            'mahasiswa',
            'pinjamanAktif',
            'menungguAcc',
            'riwayatSelesai',
            'totalDendaDibayar',
            'totalBiayaKerusakan',
            'totalKewajiban',
            'latestLoan'
        ));
    }

    /**
     * Halaman Riwayat & Status Peminjaman (Preview Menunggu ACC & Pinjaman Aktif).
     */
    public function riwayat(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();
        $tab = $request->input('tab', 'semua');

        $query = Peminjaman::with(['detailPeminjaman.alat'])
            ->where('mahasiswa_id', $mahasiswa->id);

        if ($tab === 'menunggu_acc') {
            $query->where('status', 'menunggu_acc');
        } elseif ($tab === 'dipinjam') {
            $query->where('status', 'dipinjam');
        } elseif ($tab === 'dikembalikan') {
            $query->where('status', 'dikembalikan');
        } elseif ($tab === 'ditolak') {
            $query->where('status', 'ditolak');
        }

        $daftarPeminjaman = $query->orderByDesc('id')->paginate(10)->withQueryString();

        // Counter untuk badge tiap tab
        $countMenungguAcc = Peminjaman::where('mahasiswa_id', $mahasiswa->id)->where('status', 'menunggu_acc')->count();
        $countDipinjam = Peminjaman::where('mahasiswa_id', $mahasiswa->id)->where('status', 'dipinjam')->count();
        $countDikembalikan = Peminjaman::where('mahasiswa_id', $mahasiswa->id)->where('status', 'dikembalikan')->count();
        $countDitolak = Peminjaman::where('mahasiswa_id', $mahasiswa->id)->where('status', 'ditolak')->count();
        $countSemua = Peminjaman::where('mahasiswa_id', $mahasiswa->id)->count();

        return view('mahasiswa_portal.riwayat', compact(
            'mahasiswa',
            'daftarPeminjaman',
            'tab',
            'countMenungguAcc',
            'countDipinjam',
            'countDikembalikan',
            'countDitolak',
            'countSemua'
        ));
    }

    /**
     * Katalog Ketersediaan Alat Laboratorium (Stok Ready vs Habis).
     */
    public function katalog(Request $request)
    {
        $search = $request->input('search');
        $kategori = $request->input('kategori');

        $query = Alat::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_alat', 'like', "%{$search}%")
                  ->orWhere('kode_alat', 'like', "%{$search}%");
            });
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        $daftarKategori = Alat::select('kategori')->distinct()->pluck('kategori');
        $alat = $query->orderBy('nama_alat', 'asc')->paginate(9)->withQueryString();

        return view('mahasiswa_portal.katalog', compact('alat', 'search', 'kategori', 'daftarKategori'));
    }

    /**
     * Form Pengajuan Peminjaman Alat oleh Mahasiswa.
     */
    public function createPengajuan()
    {
        $mahasiswa = $this->getMahasiswa();

        // Cek apakah mahasiswa masih punya tunggakan aktif
        $pinjamanTerlambat = Peminjaman::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', Carbon::today())
            ->first();

        if ($pinjamanTerlambat) {
            return redirect()->route('mahasiswa.portal.riwayat', ['tab' => 'dipinjam'])
                ->with('error', "Pengajuan ditolak: Anda masih memiliki peminjaman aktif ({$pinjamanTerlambat->kode_transaksi}) yang melewati tanggal jatuh tempo. Harap selesaikan pengembalian ke laboratorium terlebih dahulu.");
        }

        // Cek apakah mahasiswa masih punya tunggakan denda/kerusakan yang BELUM LUNAS
        $tunggakanDenda = Peminjaman::where('mahasiswa_id', $mahasiswa->id)
            ->where('status_pembayaran', 'belum_lunas')
            ->first();

        if ($tunggakanDenda) {
            return redirect()->route('mahasiswa.portal.riwayat', ['tab' => 'dikembalikan'])
                ->with('error', "Pengajuan ditolak: Anda masih memiliki tagihan denda/kerusakan yang BELUM LUNAS pada transaksi ({$tunggakanDenda->kode_transaksi}) sebesar Rp " . number_format($tunggakanDenda->totalTagihan(), 0, ',', '.') . ". Harap selesaikan pembayaran di kasir lab terlebih dahulu.");
        }

        $alat = Alat::where('stok_tersedia', '>', 0)->orderBy('nama_alat', 'asc')->get();
        return view('mahasiswa_portal.ajukan', compact('mahasiswa', 'alat'));
    }

    /**
     * Simpan Pengajuan Peminjaman (Status awal: menunggu_acc).
     */
    public function storePengajuan(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();

        $validated = $request->validate([
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_jatuh_tempo' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.alat_id' => ['required', 'distinct', 'exists:alat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'tanggal_pinjam.required' => 'Tanggal mulai sewa wajib diisi.',
            'tanggal_pinjam.after_or_equal' => 'Tanggal sewa tidak boleh di masa lampau (sebelum hari ini).',
            'tanggal_jatuh_tempo.required' => 'Tanggal selesai sewa (batas kembali) wajib diisi.',
            'tanggal_jatuh_tempo.after_or_equal' => 'Tanggal selesai sewa tidak boleh sebelum tanggal mulai sewa.',
            'items.required' => 'Minimal pilih 1 jenis alat laboratorium.',
            'items.*.alat_id.distinct' => 'Terdapat duplikasi alat yang sama.',
            'items.*.jumlah.min' => 'Jumlah alat minimal 1 unit.',
        ]);

        // Cek tunggakan pinjaman aktif terlambat
        $overdue = Peminjaman::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', Carbon::today())
            ->first();

        if ($overdue) {
            return back()->withInput()->with('error', "Anda masih memiliki pinjaman aktif yang melewati jatuh tempo ({$overdue->kode_transaksi}). Harap kembalikan terlebih dahulu.");
        }

        // Cek tunggakan denda belum lunas
        $tunggakanDenda = Peminjaman::where('mahasiswa_id', $mahasiswa->id)
            ->where('status_pembayaran', 'belum_lunas')
            ->first();

        if ($tunggakanDenda) {
            return back()->withInput()->with('error', "Anda masih memiliki tagihan denda/kerusakan yang BELUM LUNAS pada transaksi ({$tunggakanDenda->kode_transaksi}) sebesar Rp " . number_format($tunggakanDenda->totalTagihan(), 0, ',', '.') . ". Harap lunasi di kasir lab terlebih dahulu.");
        }

        // Cek kuota total unit (maksimal 10 unit)
        $totalUnit = array_sum(array_column($validated['items'], 'jumlah'));
        if ($totalUnit > 10) {
            return back()->withInput()->with('error', "Pengajuan melebihi kuota! Maksimal peminjaman adalah 10 unit dalam satu transaksi (Anda mengajukan: {$totalUnit} unit).");
        }

        // Cek ketersediaan stok awal
        foreach ($validated['items'] as $item) {
            $alat = Alat::find($item['alat_id']);
            if ($alat->stok_tersedia < $item['jumlah']) {
                return back()->withInput()->with('error', "Stok alat '{$alat->nama_alat}' saat ini hanya tersedia {$alat->stok_tersedia} unit (Anda meminta {$item['jumlah']}).");
            }
        }

        $peminjaman = DB::transaction(function () use ($mahasiswa, $validated) {
            // Header Transaksi dengan status 'menunggu_acc'
            $newPeminjaman = Peminjaman::create([
                'kode_transaksi' => Peminjaman::generateKodeTransaksi(),
                'mahasiswa_id' => $mahasiswa->id,
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                'tanggal_kembali' => null,
                'status' => 'menunggu_acc',
                'total_denda' => 0,
                'total_biaya_perbaikan' => 0,
            ]);

            foreach ($validated['items'] as $item) {
                DetailPeminjaman::create([
                    'peminjaman_id' => $newPeminjaman->id,
                    'alat_id' => $item['alat_id'],
                    'jumlah' => $item['jumlah'],
                    'biaya_perbaikan' => 0,
                ]);
            }

            return $newPeminjaman;
        });

        return redirect()->route('mahasiswa.portal.riwayat', ['tab' => 'menunggu_acc'])
            ->with('success', "Permohonan [{$peminjaman->kode_transaksi}] berhasil dikirim! Status saat ini: Menunggu Persetujuan (ACC) dari Petugas Lab.");
    }

    /**
     * Mahasiswa membatalkan pengajuan sendiri selagi masih berstatus 'menunggu_acc'.
     */
    public function batalkanPengajuan(Peminjaman $peminjaman)
    {
        $mahasiswa = $this->getMahasiswa();

        if ($peminjaman->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($peminjaman->status !== 'menunggu_acc') {
            return back()->with('error', 'Hanya permohonan berstatus menunggu ACC yang dapat dibatalkan.');
        }

        $peminjaman->update(['status' => 'dibatalkan']);

        return back()->with('success', "Permohonan [{$peminjaman->kode_transaksi}] telah berhasil dibatalkan.");
    }

    /**
     * Lihat detail dan rincian transaksi peminjaman milik mahasiswa sendiri.
     */
    public function detail(Peminjaman $peminjaman)
    {
        $mahasiswa = $this->getMahasiswa();

        // Proteksi otorisasi: hanya peminjam yang bersangkutan yang dapat melihat
        if ($peminjaman->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat transaksi mahasiswa lain.');
        }

        $peminjaman->load(['mahasiswa', 'detailPeminjaman.alat']);
        return view('mahasiswa_portal.detail', compact('peminjaman', 'mahasiswa'));
    }
}
