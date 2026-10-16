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

        // Statistik Real Database (Sesuai 4 Card di Mockup Gambar)
        $totalMahasiswa = Mahasiswa::count();
        $totalJenisAlat = Alat::count();
        $totalStokAlat = Alat::sum('stok_total');
        $totalStokTersedia = Alat::sum('stok_tersedia');
        $totalPeminjaman = Peminjaman::count();
        $totalDenda = Peminjaman::sum('total_denda') + Peminjaman::sum('total_biaya_perbaikan');

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
            ->limit(5)
            ->get();

        // Trend 7 Hari Terakhir untuk Line Chart
        $trendLabels = [];
        $trendValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $trendLabels[] = $d->isoFormat('D MMM');
            $count = Peminjaman::whereDate('tanggal_pinjam', $d->format('Y-m-d'))->count();
            $trendValues[] = $count;
        }

        // Parameter Donut Chart (Opsi 1): Status Ketersediaan & Kondisi Fisik Alat
        $unitTersedia = \App\Models\AlatUnit::where('status', 'tersedia')->count();
        $unitDipinjam = \App\Models\AlatUnit::where('status', 'dipinjam')->count();
        $unitRusak = \App\Models\AlatUnit::whereIn('status', ['rusak', 'maintenance'])->count();

        $totalUnitFisik = $unitTersedia + $unitDipinjam + $unitRusak;
        if ($totalUnitFisik === 0) {
            $unitTersedia = (int) Alat::sum('stok_tersedia');
            $unitRusak = (int) Alat::sum('stok_rusak');
            $unitDipinjam = max(0, (int) Alat::sum('stok_total') - ($unitTersedia + $unitRusak));
            $totalUnitFisik = $unitTersedia + $unitDipinjam + $unitRusak;
        }

        $donutLabels = ['Tersedia (Ready)', 'Sedang Dipinjam', 'Rusak / Karantina'];
        $donutValues = [$unitTersedia, $unitDipinjam, $unitRusak];
        $donutPercentages = [
            $totalUnitFisik > 0 ? round(($unitTersedia / $totalUnitFisik) * 100) : 0,
            $totalUnitFisik > 0 ? round(($unitDipinjam / $totalUnitFisik) * 100) : 0,
            $totalUnitFisik > 0 ? round(($unitRusak / $totalUnitFisik) * 100) : 0,
        ];
        $donutColors = ['#10b981', '#2563eb', '#ef4444'];

        return view('dashboard.index', compact(
            'totalMahasiswa',
            'totalJenisAlat',
            'totalStokAlat',
            'totalStokTersedia',
            'totalPeminjaman',
            'totalDenda',
            'peminjamanAktif',
            'peminjamanTerlambat',
            'totalDendaTerkumpul',
            'totalBiayaPerbaikan',
            'transaksiTerbaru',
            'trendLabels',
            'trendValues',
            'donutLabels',
            'donutValues',
            'donutPercentages',
            'donutColors'
        ));
    }
}
