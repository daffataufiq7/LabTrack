<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
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

        $laporan = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('laporan.index', compact(
            'laporan',
            'statusFilter',
            'dariTanggal',
            'sampaiTanggal',
            'totalTransaksi',
            'totalDipinjam',
            'totalDikembalikan',
            'totalTerlambat',
            'totalDenda',
            'totalBiayaPerbaikan'
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
