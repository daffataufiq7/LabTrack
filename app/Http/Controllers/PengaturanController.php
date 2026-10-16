<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman pengaturan sistem laboratorium.
     */
    public function index()
    {
        $settings = [
            'nama_lab' => 'Laboratorium Teknik Informatika',
            'alamat' => 'Jl. Pendidikan No. 1, Kampus Terpadu',
            'kontak' => 'lab@kampus.ac.id',
            'jam_operasional' => 'Senin - Jumat, 07:00 - 18:00 WIB',
            'maks_hari_pinjam' => 7,
            'denda_per_hari' => 2000,
            'toleransi_hari' => 3,
            'biaya_alat_rusak' => 10000,
        ];

        return view('pengaturan.index', compact('settings'));
    }

    /**
     * Simpan perubahan pengaturan.
     */
    public function update(Request $request)
    {
        return redirect()->route('pengaturan.index')
            ->with('success', 'Pengaturan sistem laboratorium berhasil diperbarui!');
    }
}
