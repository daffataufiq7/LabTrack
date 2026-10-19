<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan form login dengan desain akademik resmi.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('dashboard')
                : redirect()->route('mahasiswa.portal.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login (mendukung username, NIM, atau email).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username atau NIM wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        // Coba login via username / NIM
        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $user = Auth::user();

            if (!$user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withInput($request->only('username', 'remember'))
                    ->with('error', 'Akun Anda dinonaktifkan oleh Petugas Laboratorium. Silakan hubungi pengelola lab untuk mengaktifkan kembali.');
            }

            $request->session()->regenerate();

            if ($user->isAdmin()) {
                return redirect()->intended(route('dashboard'))
                    ->with('success', "Selamat datang, {$user->name} (Petugas Lab)!");
            }

            return redirect()->intended(route('mahasiswa.portal.dashboard'))
                ->with('success', "Selamat datang di Portal Mahasiswa, {$user->name}!");
        }

        // Coba alternatif login via email
        if (Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            $user = Auth::user();

            if (!$user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withInput($request->only('username', 'remember'))
                    ->with('error', 'Akun Anda dinonaktifkan oleh Petugas Laboratorium. Silakan hubungi pengelola lab untuk mengaktifkan kembali.');
            }

            $request->session()->regenerate();

            if ($user->isAdmin()) {
                return redirect()->intended(route('dashboard'))
                    ->with('success', "Selamat datang, {$user->name} (Petugas Lab)!");
            }

            return redirect()->intended(route('mahasiswa.portal.dashboard'))
                ->with('success', "Selamat datang di Portal Mahasiswa, {$user->name}!");
        }

        return back()->withInput($request->only('username', 'remember'))
            ->with('error', 'Kombinasi akun atau kata sandi tidak valid. Gunakan tombol demo (1 / 1 untuk Mahasiswa atau 2 / 2 untuk Admin).');
    }

    /**
     * Tampilkan form pendaftaran mandiri akun mahasiswa baru.
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('dashboard')
                : redirect()->route('mahasiswa.portal.dashboard');
        }

        return view('auth.register');
    }

    /**
     * Simpan pendaftaran akun mahasiswa baru (terintegrasi ke tabel mahasiswa & users).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:30', 'unique:mahasiswa,nim'],
            'nama' => ['required', 'string', 'max:100'],
            'program_studi' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:1', 'confirmed'],
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM ini sudah terdaftar di sistem.',
            'nama.required' => 'Nama lengkap wajib diisi.',
            'program_studi.required' => 'Program studi wajib dipilih/diisi.',
            'no_hp.required' => 'Nomor WhatsApp / HP wajib diisi untuk konfirmasi sirkulasi alat.',
            'username.required' => 'Username akun login wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih yang lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // 1. Buat record di tabel master mahasiswa
            $mahasiswa = Mahasiswa::create([
                'nim' => $validated['nim'],
                'nama' => $validated['nama'],
                'program_studi' => $validated['program_studi'],
                'no_hp' => $validated['no_hp'],
            ]);

            // 2. Buat record di tabel users
            $newUser = User::create([
                'name' => $validated['nama'],
                'username' => $validated['username'],
                'email' => null,
                'password' => Hash::make($validated['password']),
                'role' => 'mahasiswa',
                'mahasiswa_id' => $mahasiswa->id,
            ]);

            return $newUser;
        });

        // Login otomatis setelah registrasi berhasil
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('mahasiswa.portal.dashboard')
            ->with('success', 'Akun mahasiswa berhasil didaftarkan! Selamat datang di Sistem Laboratorium LabTrack.');
    }

    /**
     * Logout pengguna dan bersihkan sesi.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar dari sistem LabTrack.');
    }
}
