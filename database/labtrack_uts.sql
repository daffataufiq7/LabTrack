-- =====================================================================
-- UJIAN TENGAH SEMESTER (UTS) PRAKTIKUM MANAJEMEN SISTEM INFORMASI
-- Studi Kasus 1: Sistem Peminjaman Alat Laboratorium (LabTrack)
-- Target RDBMS : MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `labtrack`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `labtrack`;

-- ---------------------------------------------------------------------
-- 1. Tabel Master: MAHASISWA
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `detail_peminjaman`;
DROP TABLE IF EXISTS `peminjaman`;
DROP TABLE IF EXISTS `alat`;
DROP TABLE IF EXISTS `mahasiswa`;

CREATE TABLE `mahasiswa` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nim` VARCHAR(30) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `program_studi` VARCHAR(100) NOT NULL,
    `no_hp` VARCHAR(20) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `uq_mahasiswa_nim` UNIQUE (`nim`),
    INDEX `idx_mahasiswa_nama` (`nama`),
    INDEX `idx_mahasiswa_prodi` (`program_studi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Tabel Master: ALAT
-- ---------------------------------------------------------------------
CREATE TABLE `alat` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_alat` VARCHAR(30) NOT NULL,
    `nama_alat` VARCHAR(150) NOT NULL,
    `kategori` VARCHAR(100) NOT NULL,
    `gambar` VARCHAR(255) NULL,
    `stok_total` INT UNSIGNED NOT NULL DEFAULT 0,
    `stok_tersedia` INT UNSIGNED NOT NULL DEFAULT 0,
    `stok_rusak` INT UNSIGNED NOT NULL DEFAULT 0,
    `kondisi` VARCHAR(50) NOT NULL DEFAULT 'Baik',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `uq_alat_kode` UNIQUE (`kode_alat`),
    INDEX `idx_alat_nama` (`nama_alat`),
    INDEX `idx_alat_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2b. Tabel Pengguna Sistem (Multi-Role Authentication)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(255) NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa',
    `mahasiswa_id` BIGINT UNSIGNED NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_mahasiswa_id` (`mahasiswa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Tabel Transaksi Utama: PEMINJAMAN
-- ---------------------------------------------------------------------
CREATE TABLE `peminjaman` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_transaksi` VARCHAR(40) NOT NULL,
    `mahasiswa_id` BIGINT UNSIGNED NOT NULL,
    `tanggal_pinjam` DATE NOT NULL,
    `tanggal_jatuh_tempo` DATE NOT NULL,
    `tanggal_kembali` DATE NULL,
    `status` ENUM('menunggu_acc', 'dipinjam', 'dikembalikan', 'ditolak', 'dibatalkan') NOT NULL DEFAULT 'dipinjam',
    `total_denda` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `total_biaya_perbaikan` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `status_pembayaran` ENUM('lunas', 'belum_lunas') NOT NULL DEFAULT 'lunas',
    `metode_pembayaran` VARCHAR(50) NULL,
    `waktu_pembayaran` TIMESTAMP NULL,
    `catatan_admin` TEXT NULL,
    `approved_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `uq_peminjaman_kode` UNIQUE (`kode_transaksi`),
    CONSTRAINT `fk_peminjaman_mahasiswa`
        FOREIGN KEY (`mahasiswa_id`)
        REFERENCES `mahasiswa` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    INDEX `idx_peminjaman_tgl_pinjam` (`tanggal_pinjam`),
    INDEX `idx_peminjaman_tgl_tempo` (`tanggal_jatuh_tempo`),
    INDEX `idx_peminjaman_tgl_kembali` (`tanggal_kembali`),
    INDEX `idx_peminjaman_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Tabel Detail Transaksi: DETAIL_PEMINJAMAN
-- ---------------------------------------------------------------------
CREATE TABLE `detail_peminjaman` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `peminjaman_id` BIGINT UNSIGNED NOT NULL,
    `alat_id` BIGINT UNSIGNED NOT NULL,
    `jumlah` INT UNSIGNED NOT NULL DEFAULT 1,
    `kode_unit_dipinjam` TEXT NULL,
    `kondisi_kembali` VARCHAR(50) NULL,
    `biaya_perbaikan` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_detail_peminjaman_header`
        FOREIGN KEY (`peminjaman_id`)
        REFERENCES `peminjaman` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_detail_peminjaman_alat`
        FOREIGN KEY (`alat_id`)
        REFERENCES `alat` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    INDEX `idx_detail_peminjaman_id` (`peminjaman_id`),
    INDEX `idx_detail_alat_id` (`alat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4b. Tabel Master Unit Fisik Alat (Tracking Serial Number / Barcode Fisik)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `alat_unit` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `alat_id` BIGINT UNSIGNED NOT NULL,
    `kode_unit` VARCHAR(50) NOT NULL UNIQUE,
    `nomor_seri` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('tersedia', 'dipinjam', 'rusak', 'maintenance') NOT NULL DEFAULT 'tersedia',
    `kondisi` VARCHAR(100) NOT NULL DEFAULT 'Baik',
    `peminjaman_id` BIGINT UNSIGNED DEFAULT NULL,
    `catatan` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_alat_unit_alat`
        FOREIGN KEY (`alat_id`)
        REFERENCES `alat` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_alat_unit_peminjaman`
        FOREIGN KEY (`peminjaman_id`)
        REFERENCES `peminjaman` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX `idx_alat_unit_alat_id` (`alat_id`),
    INDEX `idx_alat_unit_status` (`status`),
    INDEX `idx_alat_unit_peminjaman_id` (`peminjaman_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATA DUMMY / SEEDER AWAL PENGUJIAN ATURAN BISNIS
-- =====================================================================

-- Insert Master Data Mahasiswa
INSERT INTO `mahasiswa` (`id`, `nim`, `nama`, `program_studi`, `no_hp`) VALUES
(1, '22051204001', 'Ahmad Fajar Pratama', 'Teknik Informatika', '081234567890'),
(2, '22051204015', 'Siti Nur Aisyah', 'Sistem Informasi', '082198765432'),
(3, '22051204028', 'Budi Santoso', 'Teknologi Informasi', '085712345678'),
(4, '22051204033', 'Dewi Lestari Putri', 'Teknik Informatika', '087812349988'),
(5, '22051204049', 'Rian Hidayatullah', 'Sistem Informasi', '089611223344');

-- Insert Master Data Alat
INSERT INTO `alat` (`id`, `kode_alat`, `nama_alat`, `kategori`, `gambar`, `stok_total`, `stok_tersedia`, `stok_rusak`, `kondisi`) VALUES
(1, 'ALT-ELK-001', 'Osiloskop Digital Rigol DS1054Z', 'Elektronika & Instrumentasi', 'images/alat/alt_elk_001.jpg', 8, 7, 0, 'Baik'),
(2, 'ALT-ELK-002', 'Multimeter Digital Sanwa CD800a', 'Elektronika & Instrumentasi', 'images/alat/alt_elk_002.jpg', 15, 15, 0, 'Baik'),
(3, 'ALT-IOT-001', 'ESP32 NodeMCU Development Board kit', 'IoT & Embedded System', 'images/alat/alt_iot_001.jpg', 20, 19, 1, 'Baik'),
(4, 'ALT-IOT-002', 'Raspberry Pi 4 Model B (4GB RAM)', 'IoT & Embedded System', 'images/alat/alt_iot_002.jpg', 10, 8, 0, 'Baik'),
(5, 'ALT-JRG-001', 'Crimping Tool RJ45 Proskit + Cable Tester', 'Jaringan Komputer', 'images/alat/alt_jrg_001.jpg', 12, 12, 0, 'Baik'),
(6, 'ALT-OPT-001', 'Mikroskop Binokuler Olympus CX23', 'Instrumen & Optik', 'images/alat/alt_opt_001.jpg', 6, 6, 0, 'Baik'),
(7, 'ALT-PRK-001', 'Solder Station Hakko FX-888D ESD Safe', 'Perkakas Lab', 'images/alat/alt_prk_001.jpg', 10, 8, 0, 'Baik');

-- Insert Data Transaksi 1: Selesai Tepat Waktu (Denda 0, Biaya 0)
INSERT INTO `peminjaman` (`id`, `kode_transaksi`, `mahasiswa_id`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status`, `total_denda`, `total_biaya_perbaikan`) VALUES
(1, 'TRX-20261001-0001', 1, '2026-10-01', '2026-10-08', '2026-10-08', 'dikembalikan', 0.00, 0.00);

INSERT INTO `detail_peminjaman` (`id`, `peminjaman_id`, `alat_id`, `jumlah`, `kondisi_kembali`, `biaya_perbaikan`) VALUES
(1, 1, 2, 2, 'Baik', 0.00);

-- Insert Data Transaksi 2: Selesai Terlambat 5 Hari + 1 Unit Rusak
-- Jatuh tempo: 2026-10-05, Kembali: 2026-10-10 (terlambat 5 hari).
-- Hari kena denda = 5 - 3 = 2 hari * Rp2.000 = Rp4.000
-- Biaya perbaikan = 1 unit rusak * Rp10.000 = Rp10.000
-- Total kewajiban = Rp14.000
INSERT INTO `peminjaman` (`id`, `kode_transaksi`, `mahasiswa_id`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status`, `total_denda`, `total_biaya_perbaikan`) VALUES
(2, 'TRX-20261001-0002', 2, '2026-10-01', '2026-10-05', '2026-10-10', 'dikembalikan', 4000.00, 10000.00);

INSERT INTO `detail_peminjaman` (`id`, `peminjaman_id`, `alat_id`, `jumlah`, `kondisi_kembali`, `biaya_perbaikan`) VALUES
(2, 2, 3, 2, '1 Rusak, 1 Baik', 10000.00);

-- Insert Data Transaksi 3: Aktif Dipinjam - Belum Jatuh Tempo
INSERT INTO `peminjaman` (`id`, `kode_transaksi`, `mahasiswa_id`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status`, `total_denda`, `total_biaya_perbaikan`) VALUES
(3, 'TRX-20261012-0003', 3, '2026-10-12', '2026-10-19', NULL, 'dipinjam', 0.00, 0.00);

INSERT INTO `detail_peminjaman` (`id`, `peminjaman_id`, `alat_id`, `jumlah`, `kondisi_kembali`, `biaya_perbaikan`) VALUES
(3, 3, 1, 1, NULL, 0.00),
(4, 3, 7, 2, NULL, 0.00);

-- Insert Data Transaksi 4: Aktif Dipinjam - MELEWATI TEMPO (Terlambat)
INSERT INTO `peminjaman` (`id`, `kode_transaksi`, `mahasiswa_id`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status`, `total_denda`, `total_biaya_perbaikan`) VALUES
(4, 'TRX-20261002-0004', 4, '2026-10-02', '2026-10-09', NULL, 'dipinjam', 0.00, 0.00);

INSERT INTO `detail_peminjaman` (`id`, `peminjaman_id`, `alat_id`, `jumlah`, `kondisi_kembali`, `biaya_perbaikan`) VALUES
(5, 4, 4, 2, NULL, 0.00);

-- =====================================================================
-- CONTOH KUERI LAPORAN (REPORTING QUERIES) UNTUK PENILAIAN UTS
-- =====================================================================

-- Query 1: Laporan Peminjaman yang Sedang Terlambat beserta Estimasi Hari & Denda
-- Menghitung keterlambatan aktif per hari ini (CURDATE())
SELECT 
    p.kode_transaksi,
    m.nim,
    m.nama AS nama_mahasiswa,
    m.no_hp,
    p.tanggal_pinjam,
    p.tanggal_jatuh_tempo,
    DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) AS hari_terlambat,
    GREATEST(0, DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) - 3) AS hari_kena_denda,
    GREATEST(0, DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) - 3) * 2000 AS estimasi_denda
FROM peminjaman p
JOIN mahasiswa m ON p.mahasiswa_id = m.id
WHERE p.status = 'dipinjam' 
  AND CURDATE() > p.tanggal_jatuh_tempo
ORDER BY hari_terlambat DESC;

-- Query 2: Laporan Rekapitulasi Finansial Laboratorium (Denda & Kerusakan)
SELECT 
    COUNT(p.id) AS total_transaksi,
    SUM(CASE WHEN p.status = 'dipinjam' THEN 1 ELSE 0 END) AS transaksi_aktif,
    SUM(CASE WHEN p.status = 'dikembalikan' THEN 1 ELSE 0 END) AS transaksi_selesai,
    SUM(p.total_denda) AS total_penerimaan_denda,
    SUM(p.total_biaya_perbaikan) AS total_biaya_kerusakan,
    (SUM(p.total_denda) + SUM(p.total_biaya_perbaikan)) AS total_seluruh_penerimaan
FROM peminjaman p;

-- Query 3: Monitoring Stok Kritis Alat Lab (Stok Tersedia <= 2 Unit)
SELECT 
    kode_alat,
    nama_alat,
    kategori,
    stok_total,
    stok_tersedia,
    (stok_total - stok_tersedia) AS sedang_dipinjam,
    kondisi
FROM alat
WHERE stok_tersedia <= 2
ORDER BY stok_tersedia ASC;

-- Query 4: Riwayat Lengkap Peminjaman dan Alat yang Dipinjam
SELECT 
    p.kode_transaksi,
    m.nim,
    m.nama AS mahasiswa,
    a.kode_alat,
    a.nama_alat,
    dp.jumlah,
    p.tanggal_pinjam,
    p.tanggal_jatuh_tempo,
    p.tanggal_kembali,
    p.status,
    dp.kondisi_kembali,
    p.total_denda,
    dp.biaya_perbaikan
FROM peminjaman p
JOIN mahasiswa m ON p.mahasiswa_id = m.id
JOIN detail_peminjaman dp ON dp.peminjaman_id = p.id
JOIN alat a ON dp.alat_id = a.id
ORDER BY p.id DESC;

-- =====================================================================
-- DATABASE VIEWS (UNTUK NILAI TAMBAH PEMANFAATAN RDBMS NATIVE)
-- =====================================================================

-- View 1: Peminjaman Terlambat Aktif Beserta Denda
CREATE OR REPLACE VIEW `view_peminjaman_terlambat` AS
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
  AND CURDATE() > p.tanggal_jatuh_tempo;

-- View 2: Rekapitulasi Audit Sirkulasi dan Finansial Laboratorium
CREATE OR REPLACE VIEW `view_rekap_keuangan` AS
SELECT 
    COUNT(p.id) AS total_transaksi,
    SUM(CASE WHEN p.status = 'dipinjam' THEN 1 ELSE 0 END) AS transaksi_aktif,
    SUM(CASE WHEN p.status = 'dikembalikan' THEN 1 ELSE 0 END) AS transaksi_selesai,
    SUM(p.total_denda) AS total_denda_terkumpul,
    SUM(p.total_biaya_perbaikan) AS total_biaya_kerusakan,
    (SUM(p.total_denda) + SUM(p.total_biaya_perbaikan)) AS total_seluruh_penerimaan
FROM peminjaman p;

-- View 3: Audit Inventaris & Keseimbangan Stok Alat
-- Membuktikan formula: stok_total = stok_tersedia + stok_dipinjam + stok_rusak
CREATE OR REPLACE VIEW `view_audit_inventaris_lab` AS
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
FROM alat a;
