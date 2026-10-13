<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Statistik Real Database
        $totalMahasiswa = Mahasiswa::count();
        $totalJenisAlat = Alat::count();
        $totalStokAlat = Alat::sum('stok_total');
        $totalStokTersedia = Alat::sum('stok_tersedia');

        $peminjamanAktif = Peminjaman::where('status', 'dipinjam')->count();

        // Keterlambatan aktif dihitung dari tanggal jatuh tempo
        $peminjamanTerlambat = Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_jatuh_tempo', '<', $today)
            ->count();

        $totalDendaTerkumpul = Peminjaman::where('status', 'dikembalikan')->sum('total_denda');
        $totalBiayaPerbaikan = Peminjaman::where('status', 'dikembalikan')->sum('total_biaya_perbaikan');

        // Transaksi Terbaru
        $transaksiTerbaru = Peminjaman::with(['mahasiswa', 'detailPeminjaman.alat'])
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        // Daftar Peminjaman Aktif yang Jatuh Tempo / Lewat Jatuh Tempo
        $pinjamanPerluPerhatian = Peminjaman::with(['mahasiswa', 'detailPeminjaman.alat'])
            ->where('status', 'dipinjam')
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->limit(5)
            ->get();

        // Alat dengan stok menipis (< 3 tersedia)
        $alatStokKritis = Alat::where('stok_tersedia', '<=', 2)
            ->orderBy('stok_tersedia', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalMahasiswa',
            'totalJenisAlat',
            'totalStokAlat',
            'totalStokTersedia',
            'peminjamanAktif',
            'peminjamanTerlambat',
            'totalDendaTerkumpul',
            'totalBiayaPerbaikan',
            'transaksiTerbaru',
            'pinjamanPerluPerhatian',
            'alatStokKritis'
        ));
    }
}
