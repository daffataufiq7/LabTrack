<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman pengaturan sistem laboratorium.
     */
    public function index(Request $request)
    {
        $settings = Pengaturan::getAll();
        $user = Auth::user();
        $activeTab = $request->query('tab', 'profil');

        // Statistik ringkas untuk tab Backup & Pemeliharaan
        $dbStats = [
            'database' => config('database.connections.mysql.database', 'labtrack'),
            'total_mahasiswa' => Mahasiswa::count(),
            'total_alat' => Alat::count(),
            'total_transaksi' => Peminjaman::count(),
            'total_user' => User::count(),
            'last_backup' => Pengaturan::get('last_backup_time', 'Belum pernah diunduh'),
        ];

        return view('pengaturan.index', compact('settings', 'user', 'activeTab', 'dbStats'));
    }

    /**
     * Simpan perubahan pengaturan profil laboratorium & aturan bisnis.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_lab' => 'required|string|max:150',
            'alamat' => 'required|string|max:255',
            'kontak' => 'required|string|max:100',
            'jam_operasional' => 'required|string|max:150',
            'denda_per_hari' => 'nullable|numeric|min:0',
            'toleransi_hari' => 'nullable|integer|min:0|max:30',
            'biaya_alat_rusak' => 'nullable|numeric|min:0',
            'maks_hari_pinjam' => 'nullable|integer|min:1|max:30',
        ]);

        foreach ($validated as $key => $value) {
            Pengaturan::set($key, $value);
        }

        return redirect()->route('pengaturan.index', ['tab' => 'profil'])
            ->with('success', 'Informasi profil laboratorium berhasil diperbarui!');
    }

    /**
     * Perbarui data akun admin yang sedang login & ubah password.
     */
    public function updateAkun(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:4|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
            'password.min' => 'Kata sandi minimal 4 karakter.',
            'username.unique' => 'Username ini sudah digunakan akun lain.',
        ]);

        // Jika mengisi ganti password, verifikasi password saat ini
        if (!empty($validated['password'])) {
            if (empty($validated['current_password'])) {
                return redirect()->route('pengaturan.index', ['tab' => 'akun'])
                    ->withErrors(['current_password' => 'Kata sandi saat ini wajib diisi untuk mengganti kata sandi baru.']);
            }

            if (!Hash::check($validated['current_password'], $user->password)) {
                return redirect()->route('pengaturan.index', ['tab' => 'akun'])
                    ->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
            }

            $user->password = Hash::make($validated['password']);
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('pengaturan.index', ['tab' => 'akun'])
            ->with('success', 'Data akun pengelola laboratorium berhasil diperbarui!');
    }

    /**
     * Simpan preferensi notifikasi sistem.
     */
    public function updateNotifikasi(Request $request)
    {
        Pengaturan::set('notif_pengajuan_baru', $request->has('notif_pengajuan_baru') ? '1' : '0');
        Pengaturan::set('notif_jatuh_tempo', $request->has('notif_jatuh_tempo') ? '1' : '0');
        Pengaturan::set('notif_denda', $request->has('notif_denda') ? '1' : '0');
        Pengaturan::set('notif_email_alert', $request->has('notif_email_alert') ? '1' : '0');

        return redirect()->route('pengaturan.index', ['tab' => 'notifikasi'])
            ->with('success', 'Preferensi notifikasi sistem berhasil disimpan!');
    }

    /**
     * Download backup database dalam format file .sql
     */
    public function backupDownload()
    {
        $dbName = config('database.connections.mysql.database', 'labtrack');
        $filename = 'backup_' . $dbName . '_' . date('Ymd_His') . '.sql';

        $tables = [
            'users',
            'mahasiswa',
            'alat',
            'alat_unit',
            'peminjaman',
            'detail_peminjaman',
            'pengaturan',
        ];

        $sql = "-- ============================================================\n";
        $sql .= "-- LabTrack Database Backup\n";
        $sql .= "-- Waktu Pembuatan : " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database         : " . $dbName . "\n";
        $sql .= "-- ============================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            $sql .= "-- ------------------------------------------------------------\n";
            $sql .= "-- Struktur Tabel `{$table}`\n";
            $sql .= "-- ------------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
                if ($row && !empty($row->sql)) {
                    $sql .= $row->sql . ";\n\n";
                }
            } else {
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                if (!empty($createTable)) {
                    $createRow = (array) $createTable[0];
                    $sql .= ($createRow['Create Table'] ?? '') . ";\n\n";
                }
            }

            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data Tabel `{$table}`\n";
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $columns = array_map(fn($col) => "`" . $col . "`", array_keys($rowArray));
                    $values = array_map(function ($val) {
                        if (is_null($val)) {
                            return "NULL";
                        }
                        return "'" . addslashes((string) $val) . "'";
                    }, array_values($rowArray));

                    $sql .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $sql .= "-- Backup Selesai --\n";

        Pengaturan::set('last_backup_time', date('d/m/Y H:i:s'));

        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Bersihkan cache sistem aplikasi.
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            $msg = 'Cache sistem, views, dan konfigurasi berhasil dibersihkan!';
        } catch (\Throwable $e) {
            $msg = 'Pembersihan cache selesai: ' . $e->getMessage();
        }

        return redirect()->route('pengaturan.index', ['tab' => 'backup'])
            ->with('success', $msg);
    }
}
