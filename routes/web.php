<?php

use App\Http\Controllers\AlatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DendaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MahasiswaPortalController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PengembalianController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root cerdas berdasarkan status login
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin() 
            ? redirect()->route('dashboard') 
            : redirect()->route('mahasiswa.portal.dashboard');
    }
    return redirect()->route('login');
});

// Autentikasi Pengguna & Pendaftaran Akun
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =========================================================================
// PORTAL MAHASISWA (Lihat Stok, Ajukan Pinjaman, Cek Denda & Riwayat)
// =========================================================================
Route::prefix('portal')->name('mahasiswa.portal.')->group(function () {
    Route::get('/dashboard', [MahasiswaPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/riwayat', [MahasiswaPortalController::class, 'riwayat'])->name('riwayat');
    Route::get('/katalog', [MahasiswaPortalController::class, 'katalog'])->name('katalog');
    Route::get('/ajukan', [MahasiswaPortalController::class, 'createPengajuan'])->name('ajukan');
    Route::post('/ajukan', [MahasiswaPortalController::class, 'storePengajuan'])->name('ajukan.store');
    Route::post('/pinjaman/{peminjaman}/batalkan-pengajuan', [MahasiswaPortalController::class, 'batalkanPengajuan'])->name('ajukan.batal');
    Route::get('/pinjaman/{peminjaman}', [MahasiswaPortalController::class, 'detail'])->name('detail');
});

// =========================================================================
// PORTAL ADMIN / PETUGAS LABORATORIUM
// =========================================================================

// Modul 1: Dashboard Admin
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Modul 2: CRUD Mahasiswa
Route::resource('mahasiswa', MahasiswaController::class)->except(['show']);

// Modul 3: CRUD Alat
Route::resource('alat', AlatController::class)->except(['show']);

// Modul 4: Transaksi Peminjaman & Antrean ACC
Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
Route::get('/peminjaman/tambah', [PeminjamanController::class, 'create'])->name('peminjaman.create');
Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
Route::get('/peminjaman/antrean-acc', [PeminjamanController::class, 'antreanAcc'])->name('peminjaman.antrean_acc');
Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve'])->name('peminjaman.approve');
Route::post('/peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject'])->name('peminjaman.reject');
Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])->name('peminjaman.show');
Route::get('/peminjaman/{peminjaman}/nota', [PeminjamanController::class, 'nota'])->name('peminjaman.nota');
Route::post('/peminjaman/{peminjaman}/batal', [PeminjamanController::class, 'cancel'])->name('peminjaman.cancel');

// Modul 5: Transaksi Pengembalian
Route::get('/pengembalian', [PengembalianController::class, 'index'])->name('pengembalian.index');
Route::get('/pengembalian/{peminjaman}', [PengembalianController::class, 'form'])->name('pengembalian.form');
Route::post('/pengembalian/{peminjaman}', [PengembalianController::class, 'process'])->name('pengembalian.process');
Route::post('/pengembalian/{peminjaman}/bayar', [PengembalianController::class, 'bayarDenda'])->name('pengembalian.bayar');

// Modul 6: Denda
Route::get('/denda', [DendaController::class, 'index'])->name('denda.index');

// Modul 7: Laporan
Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
Route::get('/laporan/cetak', [LaporanController::class, 'print'])->name('laporan.print');

// Modul 8: Pengaturan
Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
Route::post('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
Route::post('/pengaturan/akun', [PengaturanController::class, 'updateAkun'])->name('pengaturan.akun');
Route::post('/pengaturan/notifikasi', [PengaturanController::class, 'updateNotifikasi'])->name('pengaturan.notifikasi');
Route::get('/pengaturan/backup/download', [PengaturanController::class, 'backupDownload'])->name('pengaturan.backup.download');
Route::post('/pengaturan/clear-cache', [PengaturanController::class, 'clearCache'])->name('pengaturan.clear_cache');
