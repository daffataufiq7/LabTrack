<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Views dioptimalkan khusus untuk RDBMS MySQL / MariaDB (XAMPP Environment)
        if (DB::getDriverName() === 'mysql') {
            // 1. View Peminjaman Terlambat Aktif
            DB::statement("
                CREATE OR REPLACE VIEW view_peminjaman_terlambat AS
                SELECT 
                    p.id AS peminjaman_id,
                    p.kode_transaksi,
                    m.nim,
                    m.nama AS nama_mahasiswa,
                    m.program_studi,
                    m.no_hp,
                    p.tanggal_pinjam,
                    p.tanggal_jatuh_tempo,
                    DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) AS hari_terlambat,
                    GREATEST(0, DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) - 3) AS hari_kena_denda,
                    (GREATEST(0, DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) - 3) * 2000) AS estimasi_denda
                FROM peminjaman p
                JOIN mahasiswa m ON p.mahasiswa_id = m.id
                WHERE p.status = 'dipinjam' 
                  AND CURDATE() > p.tanggal_jatuh_tempo
            ");

            // 2. View Rekap Keuangan
            DB::statement("
                CREATE OR REPLACE VIEW view_rekap_keuangan AS
                SELECT 
                    COUNT(p.id) AS total_transaksi,
                    SUM(CASE WHEN p.status = 'dipinjam' THEN 1 ELSE 0 END) AS transaksi_aktif,
                    SUM(CASE WHEN p.status = 'dikembalikan' THEN 1 ELSE 0 END) AS transaksi_selesai,
                    SUM(p.total_denda) AS total_denda_terkumpul,
                    SUM(p.total_biaya_perbaikan) AS total_biaya_kerusakan,
                    (SUM(p.total_denda) + SUM(p.total_biaya_perbaikan)) AS total_seluruh_penerimaan
                FROM peminjaman p
            ");

            // 3. View Audit Inventaris Lab
            DB::statement("
                CREATE OR REPLACE VIEW view_audit_inventaris_lab AS
                SELECT 
                    a.id AS alat_id,
                    a.kode_alat,
                    a.nama_alat,
                    a.kategori,
                    a.stok_total,
                    a.stok_tersedia,
                    a.stok_rusak,
                    (a.stok_total - (a.stok_tersedia + a.stok_rusak)) AS stok_sedang_dipinjam,
                    a.kondisi
                FROM alat a
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("DROP VIEW IF EXISTS view_peminjaman_terlambat");
            DB::statement("DROP VIEW IF EXISTS view_rekap_keuangan");
            DB::statement("DROP VIEW IF EXISTS view_audit_inventaris_lab");
        }
    }
};
