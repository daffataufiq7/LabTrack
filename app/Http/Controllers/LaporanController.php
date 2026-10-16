<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'semua');
        $tabActive = $request->input('tab', 'peminjaman');
        $dariTanggal = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');

        $query = Peminjaman::with(['mahasiswa', 'detailPeminjaman.alat']);

        if ($dariTanggal) {
            $query->whereDate('tanggal_pinjam', '>=', $dariTanggal);
        }

        if ($sampaiTanggal) {
            $query->whereDate('tanggal_pinjam', '<=', $sampaiTanggal);
        }

        if ($statusFilter && $statusFilter !== 'semua') {
            if ($statusFilter === 'terlambat') {
                $query->where('status', 'dipinjam')
                      ->where('tanggal_jatuh_tempo', '<', Carbon::today());
            } else {
                $query->where('status', $statusFilter);
            }
        }

        // Summary Statistics untuk filter yang aktif
        $totalTransaksi = (clone $query)->count();
        $totalDipinjam = (clone $query)->where('status', 'dipinjam')->count();
        $totalDikembalikan = (clone $query)->where('status', 'dikembalikan')->count();
        $totalTerlambat = (clone $query)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('status', 'dipinjam')
                        ->where('tanggal_jatuh_tempo', '<', Carbon::today());
                })->orWhere(function ($sub) {
                    $sub->where('status', 'dikembalikan')
                        ->whereColumn('tanggal_kembali', '>', 'tanggal_jatuh_tempo');
                });
            })->count();

        $totalDenda = (clone $query)->where('status', 'dikembalikan')->sum('total_denda');
        $totalBiayaPerbaikan = (clone $query)->where('status', 'dikembalikan')->sum('total_biaya_perbaikan');
        $totalPendapatan = $totalDenda + $totalBiayaPerbaikan;

        $laporan = $query->orderByDesc('id')->paginate(10)->withQueryString();

        // Peminjaman per kategori alat untuk bar statistik
        $kategoriDistribution = DB::table('detail_peminjaman')
            ->join('alat', 'detail_peminjaman.alat_id', '=', 'alat.id')
            ->select('alat.kategori', DB::raw('SUM(detail_peminjaman.jumlah) as total'))
            ->groupBy('alat.kategori')
            ->orderByDesc('total')
            ->get();

        $maxKategoriCount = $kategoriDistribution->max('total') ?: 1;

        return view('laporan.index', compact(
            'laporan',
            'statusFilter',
            'tabActive',
            'dariTanggal',
            'sampaiTanggal',
            'totalTransaksi',
            'totalDipinjam',
            'totalDikembalikan',
            'totalTerlambat',
            'totalDenda',
            'totalBiayaPerbaikan',
            'totalPendapatan',
            'kategoriDistribution',
            'maxKategoriCount'
        ));
    }

    public function print(Request $request)
    {
        $statusFilter = $request->input('status', 'semua');
        $dariTanggal = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');

        $query = Peminjaman::with(['mahasiswa', 'detailPeminjaman.alat']);

        if ($dariTanggal) {
            $query->whereDate('tanggal_pinjam', '>=', $dariTanggal);
        }

        if ($sampaiTanggal) {
            $query->whereDate('tanggal_pinjam', '<=', $sampaiTanggal);
        }

        if ($statusFilter && $statusFilter !== 'semua') {
            if ($statusFilter === 'terlambat') {
                $query->where('status', 'dipinjam')
                      ->where('tanggal_jatuh_tempo', '<', Carbon::today());
            } else {
                $query->where('status', $statusFilter);
            }
        }

        $laporan = $query->orderByDesc('id')->get();

        $totalDenda = $laporan->where('status', 'dikembalikan')->sum('total_denda');
        $totalBiayaPerbaikan = $laporan->where('status', 'dikembalikan')->sum('total_biaya_perbaikan');

        return view('laporan.print', compact(
            'laporan',
            'statusFilter',
            'dariTanggal',
            'sampaiTanggal',
            'totalDenda',
            'totalBiayaPerbaikan'
        ));
    }
}
