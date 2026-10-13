<?php

use App\Http\Controllers\AlatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::redirect('/', '/dashboard');

// Modul 1: Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Modul 2: CRUD Mahasiswa
Route::resource('mahasiswa', MahasiswaController::class)->except(['show']);

// Modul 3: CRUD Alat
Route::resource('alat', AlatController::class)->except(['show']);

// Modul 4: Transaksi Peminjaman
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::get('/peminjaman/tambah', [PeminjamanController::class, 'create'])->name('peminjaman.create');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])->name('peminjaman.show');
Route::post('/peminjaman/{peminjaman}/batal', [PeminjamanController::class, 'cancel'])->name('peminjaman.cancel');

// Modul 5: Transaksi Pengembalian
Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
Route::get('/pengembalian/{peminjaman}', [PengembalianController::class, 'form'])->name('pengembalian.form');
Route::post('/pengembalian/{peminjaman}', [PengembalianController::class, 'process'])->name('pengembalian.process');

// Modul 6: Laporan
Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
Route::get('/laporan/cetak', [LaporanController::class, 'print'])->name('laporan.print');
