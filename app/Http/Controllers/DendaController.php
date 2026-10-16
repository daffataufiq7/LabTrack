<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DendaController extends Controller
{
    /**
     * Menampilkan daftar transaksi yang memiliki denda keterlambatan atau biaya kerusakan.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status', 'semua');
        $dariTanggal = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');

        // Query transaksi yang memiliki denda > 0 atau biaya perbaikan > 0
        $query = Peminjaman::with(['mahasiswa', 'detailPeminjaman.alat'])
            ->where(function ($q) {
                $q->where('total_denda', '>', 0)
                  ->orWhere('total_biaya_perbaikan', '>', 0);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('mahasiswa', function ($m) use ($search) {
                      $m->where('nama', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                  });
            });
        }

        if ($dariTanggal) {
            $query->whereDate('tanggal_pinjam', '>=', $dariTanggal);
        }

        if ($sampaiTanggal) {
            $query->whereDate('tanggal_pinjam', '<=', $sampaiTanggal);
        }

        if ($statusFilter && $statusFilter !== 'semua') {
            $query->where('status_pembayaran', $statusFilter);
        }

        $totalDendaBelumLunas = (clone $query)->where('status_pembayaran', 'belum_lunas')->sum('total_denda')
            + (clone $query)->where('status_pembayaran', 'belum_lunas')->sum('total_biaya_perbaikan');

        $totalDendaLunas = (clone $query)->where('status_pembayaran', 'lunas')->sum('total_denda')
            + (clone $query)->where('status_pembayaran', 'lunas')->sum('total_biaya_perbaikan');

        $daftarDenda = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return view('denda.index', compact(
            'daftarDenda',
            'search',
            'statusFilter',
            'dariTanggal',
            'sampaiTanggal',
            'totalDendaBelumLunas',
            'totalDendaLunas'
        ));
    }
}
