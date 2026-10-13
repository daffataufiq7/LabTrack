# LabTrack — Sistem Peminjaman Alat Laboratorium

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.2-blue.svg)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com)
[![Database](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-orange.svg)](https://mariadb.org)
[![Tests Passing](https://img.shields.io/badge/Tests-24%20Passed-brightgreen.svg)]()

**LabTrack** adalah aplikasi web berbasis Laravel dan MySQL yang dirancang untuk mengelola inventaris, sirkulasi peminjaman, pemeriksaan fisik pengembalian, serta pencatatan denda dan biaya perbaikan alat laboratorium secara terpadu, aman, dan transparan.

Aplikasi ini dibangun untuk memenuhi spesifikasi lengkap tugas **Ujian Tengah Semester (UTS) Manajemen Sistem Informasi**.

---

## 📋 Daftar Isi
1. [Prasyarat Sistem](#-prasyarat-sistem)
2. [Panduan Instalasi & Konfigurasi](#-panduan-instalasi--konfigurasi)
3. [Cara Menjalankan Aplikasi](#-cara-menjalankan-aplikasi)
4. [Modul & Fitur Aplikasi](#-modul--fitur-aplikasi)
5. [Aturan Bisnis & Asumsi UTS](#-aturan-bisnis--asumsi-uts)
6. [Struktur & Skema Database](#-struktur--skema-database)
7. [Panduan Pengujian Otomatis (Testing)](#-panduan-pengujian-otomatis-testing)
8. [Keamanan & Integritas Data](#-keamanan--integritas-data)

---

## 💻 Prasyarat Sistem

Sebelum menginstal dan menjalankan aplikasi, pastikan komputer Anda telah terpasang:
- **PHP** versi 8.2 atau lebih baru (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`)
- **Composer** versi 2.x
- **MySQL** atau **MariaDB** (melalui XAMPP/Laragon/Native)
- **Web Browser Modern** (Google Chrome, Microsoft Edge, Mozilla Firefox)

---

## ⚙️ Panduan Instalasi & Konfigurasi

### 1. Masuk ke Direktori Proyek
Buka terminal (PowerShell / Command Prompt) dan arahkan ke folder proyek:
```bash
cd "f:\TI -K SMT 5\Manajemen Sistem Informasi\UTS"
```

### 2. Instal Dependensi Composer (Jika belum diinstal)
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin file konfigurasi contoh atau pastikan file `.env` sudah memuat pengaturan berikut:
```env
APP_NAME="LabTrack"
APP_ENV=local
APP_KEY=base64:Re2aHwRhebYpTJdMQl5K9AqcOgbUjWzkI2KyXhzuh+M=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=labtrack
DB_USERNAME=root
DB_PASSWORD=
```
> **Catatan Keamanan:** File `.env` sudah otomatis masuk ke dalam `.gitignore` sehingga informasi kredensial database tidak akan pernah terunggah ke repositori Git.

### 4. Buat Database MySQL & Jalankan Migrasi + Seeder
Jalankan perintah berikut untuk mengeksekusi migrasi skema tabel dan mengisi data demo realistis:
```bash
php artisan migrate:fresh --seed
```

---

## 🚀 Cara Menjalankan Aplikasi

Jalankan server pengembangan Laravel:
```bash
php artisan serve
```
Buka browser dan akses aplikasi melalui tautan:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)** (otomatis dialihkan ke `/dashboard`).

---

## 🧩 Modul & Fitur Aplikasi

Aplikasi LabTrack memiliki 6 modul terpadu:

### 1. Dashboard (`/dashboard`)
- **Statistik Dinamis Real-Time:** Menampilkan jumlah total mahasiswa, total varian alat lab, ketersediaan unit stok, peminjaman aktif, peminjaman melewati tempo, akumulasi denda, dan total biaya perbaikan langsung dari kueri database (tanpa hardcode).
- **Banner Peringatan Keterlambatan:** Indikator merah otomatis jika terdapat mahasiswa yang belum mengembalikan alat melewati batas tempo.
- **Tabel Transaksi Terbaru:** Pantauan aktivitas sirkulasi terkini.
- **Monitoring Stok Kritis:** Menampilkan alat dengan stok tersedia $\le 2$ unit.

### 2. Master Data Mahasiswa (`/mahasiswa`)
- CRUD lengkap (Tambah, Lihat, Edit, Hapus).
- Pencarian multi-kolom (NIM, Nama, No. HP) dan filter Program Studi.
- Validasi NIM unik dan wajib diisi.
- **Pencegahan Penghapusan Data Master:** Sistem menolak penghapusan data mahasiswa yang sudah memiliki riwayat transaksi peminjaman untuk menjaga integritas data laboratorium.

### 3. Master Data Alat Laboratorium (`/alat`)
- CRUD lengkap dengan kode inventaris unik (misal: `ALT-ELK-001`).
- Filter berdasarkan Kategori dan Kondisi Fisik.
- Badge ketersediaan stok interaktif (`Stok Tersedia / Stok Total`).
- Validasi stok: stok total $\ge 0$ dan stok tersedia tidak boleh melebihi stok total.
- **Pencegahan Penghapusan Data Master:** Alat yang sudah pernah tercatat dalam transaksi tidak dapat dihapus.

### 4. Transaksi Peminjaman Alat (`/peminjaman`)
- Peminjaman multi-item: satu transaksi dapat meminjam beberapa jenis alat sekaligus.
- Interface dinamis (tambah/hapus baris item alat secara interaktif).
- Validasi formulir:
  - Mahasiswa peminjam wajib valid.
  - Tanggal jatuh tempo $\ge$ Tanggal pinjam.
  - Jumlah pinjam harus berupa bilangan bulat positif ($\ge 1$).
  - Pencegahan pemilihan alat duplikat dalam satu transaksi.
- **Database Transaction & Row Locking (`lockForUpdate`):** Stok diperiksa dan dikurangi secara atomik. Jika ada stok yang kurang, seluruh transaksi di-rollback secara otomatis.
- Fitur pembatalan transaksi aktif yang secara otomatis mengembalikan alokasi stok.

### 5. Transaksi Pengembalian Alat (`/pengembalian`)
- Hanya transaksi berstatus aktif (`dipinjam`) yang dapat diproses.
- **Pencegahan Pengembalian Ganda:** Pengembalian dikunci menggunakan transaksi database untuk mencegah eksekusi berulang.
- Validasi: Tanggal kembali tidak boleh sebelum tanggal pinjam.
- **Inspeksi Fisik Unit:** Petugas dapat memasukkan jumlah unit rusak dan kondisi catatan.
- **Restorasi Stok Selektif:** Unit rusak **tidak dipulihkan** ke stok tersedia; hanya unit yang layak pakai yang dikembalikan ke inventaris.

### 6. Rekapitulasi & Laporan (`/laporan`)
- Filter berdasarkan rentang tanggal peminjaman dan status sirkulasi (Semua, Dipinjam, Terlambat, Dikembalikan, Dibatalkan).
- Kartu statistik ringkasan finansial (Total Denda, Total Biaya Kerusakan).
- Pratinjau Cetak resmi (`/laporan/cetak`) berstandar kop surat laboratorium dengan format tanda tangan kepala laboratorium dan kompatibel cetak PDF (`Ctrl + P`).

---

## ⚖️ Aturan Bisnis & Asumsi UTS

Sesuai ketentuan penugasan UTS, logika bisnis diimplementasikan secara tersentralisasi pada model `app/Models/Peminjaman.php` agar mudah disesuaikan jika aturan laboratorium diperbarui:

1. **Status Keterlambatan:**
   - Keterlambatan aktif dihitung secara dinamis dari selisih tanggal acuan dengan `tanggal_jatuh_tempo`, **bukan** disimpan sebagai status permanen dalam kolom status (status tersimpan hanya: `dipinjam`, `dikembalikan`, `dibatalkan`).
2. **Masa Tenggang Keterlambatan (Grace Period):**
   - Asumsi masa tenggang adalah **3 hari** setelah tanggal jatuh tempo.
   - Hari keterlambatan yang dikenai denda dihitung dengan rumus:
     $$\text{Hari Kena Denda} = \max(0, \text{Selisih Hari} - 3)$$
3. **Tarif Denda:**
   - Dikenakan tarif **Rp2.000 / hari** untuk hari-hari keterlambatan yang melewati masa tenggang:
     $$\text{Total Denda} = \text{Hari Kena Denda} \times \text{Rp2.000}$$
4. **Biaya Kerusakan Alat:**
   - Dikenakan biaya perbaikan sebesar **Rp10.000 / unit** untuk setiap alat yang dilaporkan rusak saat pengembalian:
     $$\text{Total Biaya Perbaikan} = \sum (\text{Jumlah Rusak} \times \text{Rp10.000})$$
5. **Pemulihan Stok (Stock Restoration):**
   - Unit yang dinyatakan rusak **TIDAK dipulihkan** ke `stok_tersedia`.
   - Hanya unit yang kembali dalam kondisi **layak pakai** ($\text{Jumlah Pinjam} - \text{Jumlah Rusak}$) yang ditambahkan kembali ke `stok_tersedia`.
6. **Pengembalian Kolektif:**
   - Untuk menyederhanakan ruang lingkup UTS, seluruh detail item dalam satu transaksi peminjaman dikembalikan secara bersamaan pada satu tanggal pengembalian.

---

## 🗄️ Struktur & Skema Database

```
+-------------------------------------------------------------------------+
|                               mahasiswa                                 |
+-------------------------------------------------------------------------+
| id             : BIGINT UNSIGNED (PK, Auto Increment)                   |
| nim            : VARCHAR(30) UNIQUE                                     |
| nama           : VARCHAR(100)                                           |
| program_studi  : VARCHAR(100)                                           |
| no_hp          : VARCHAR(20) NULLABLE                                   |
| created_at     : TIMESTAMP                                              |
| updated_at     : TIMESTAMP                                              |
+-------------------------------------------------------------------------+
                                     | 1
                                     | hasMany
                                     | N
+-------------------------------------------------------------------------+
|                              peminjaman                                 |
+-------------------------------------------------------------------------+
| id                    : BIGINT UNSIGNED (PK, Auto Increment)            |
| kode_transaksi        : VARCHAR(40) UNIQUE                              |
| mahasiswa_id          : BIGINT UNSIGNED (FK -> mahasiswa.id)           |
| tanggal_pinjam        : DATE                                            |
| tanggal_jatuh_tempo   : DATE                                            |
| tanggal_kembali       : DATE NULLABLE                                   |
| status                : ENUM('dipinjam','dikembalikan','dibatalkan')    |
| total_denda           : DECIMAL(12,2) DEFAULT 0                         |
| total_biaya_perbaikan : DECIMAL(12,2) DEFAULT 0                         |
| created_at            : TIMESTAMP                                       |
| updated_at            : TIMESTAMP                                       |
+-------------------------------------------------------------------------+
                                     | 1
                                     | hasMany (Cascade Delete)
                                     | N
+-------------------------------------------------------------------------+
|                          detail_peminjaman                              |
+-------------------------------------------------------------------------+
| id              : BIGINT UNSIGNED (PK, Auto Increment)                  |
| peminjaman_id   : BIGINT UNSIGNED (FK -> peminjaman.id)                 |
| alat_id         : BIGINT UNSIGNED (FK -> alat.id)                       |
| jumlah          : INT UNSIGNED                                          |
| kondisi_kembali : VARCHAR(50) NULLABLE                                  |
| biaya_perbaikan : DECIMAL(12,2) DEFAULT 0                               |
| created_at      : TIMESTAMP                                             |
| updated_at      : TIMESTAMP                                             |
+-------------------------------------------------------------------------+
                                     | N
                                     | belongsTo (Restrict Delete)
                                     | 1
+-------------------------------------------------------------------------+
|                                  alat                                   |
+-------------------------------------------------------------------------+
| id             : BIGINT UNSIGNED (PK, Auto Increment)                   |
| kode_alat      : VARCHAR(30) UNIQUE                                     |
| nama_alat      : VARCHAR(150)                                           |
| kategori       : VARCHAR(100)                                           |
| stok_total     : INT UNSIGNED                                           |
| stok_tersedia  : INT UNSIGNED                                           |
| kondisi        : VARCHAR(50) DEFAULT 'Baik'                             |
| created_at     : TIMESTAMP                                              |
| updated_at     : TIMESTAMP                                              |
+-------------------------------------------------------------------------+
```

---

## 🧪 Panduan Pengujian Otomatis (Testing)

Seluruh aturan bisnis, validasi form, transaksi atomik, dan kalkulasi telah diuji menggunakan test suite otomatis PHPUnit / Laravel Test.

### Menjalankan Seluruh Pengujian:
```bash
php artisan test
```

### Rincian Skenario Pengujian yang Dijalankan (24 Tests, 91 Assertions - 100% PASS):

1. **`AlatCrudTest`**:
   - `can_display_alat_index`: Menampilkan daftar alat laboratorium.
   - `can_create_alat`: Berhasil menyimpan inventaris baru.
   - `cannot_create_alat_when_available_stock_exceeds_total_stock`: Validasi gagal jika stok tersedia > stok total.
   - `can_update_alat`: Berhasil memperbarui data alat.
   - `can_delete_alat_without_transactions`: Hapus alat yang belum ada transaksi.
   - `cannot_delete_alat_with_transactions`: Pencegahan penghapusan alat yang sudah terdaftar dalam transaksi.

2. **`MahasiswaCrudTest`**:
   - `can_display_mahasiswa_index`: Menampilkan katalog mahasiswa.
   - `can_create_mahasiswa`: Berhasil menambah mahasiswa.
   - `cannot_create_mahasiswa_with_duplicate_nim`: Validasi penolakan NIM duplikat.
   - `can_update_mahasiswa`: Berhasil memperbarui profil.
   - `can_delete_mahasiswa_without_transactions`: Hapus data tanpa riwayat.
   - `cannot_delete_mahasiswa_with_transactions`: Pencegahan penghapusan mahasiswa yang memiliki transaksi pinjam.

3. **`PeminjamanTest`**:
   - `can_create_peminjaman_when_stock_is_sufficient`: Peminjaman multi-item berhasil dan stok berkurang secara atomik.
   - `fails_and_rollbacks_when_stock_is_insufficient`: Pengujian rollback saat salah satu stok alat tidak mencukupi (stok awal tetap utuh).
   - `fails_when_due_date_is_before_borrow_date`: Validasi gagal jika jatuh tempo sebelum tanggal pinjam.
   - `fails_when_item_quantity_is_not_positive`: Validasi gagal jika jumlah $\le 0$.
   - `can_cancel_active_loan_and_restores_stock`: Pembatalan peminjaman memulihkan alokasi stok.

4. **`PengembalianTest`**:
   - `pengembalian_tepat_waktu`: Denda Rp0, biaya perbaikan Rp0, stok dipulihkan utuh.
   - `pengembalian_terlambat_5_hari_dikenai_denda_dengan_masa_tenggang_3_hari`: Terlambat 5 hari $-$ 3 hari masa tenggang = 2 hari kena denda $\times$ Rp2.000 = **Rp4.000**.
   - `pengembalian_dengan_alat_rusak_menghitung_biaya_perbaikan_dan_tidak_memulihkan_stok_rusak`: 1 unit rusak dikenai biaya **Rp10.000** dan stok unit rusak **tidak dipulihkan**.
   - `fails_when_return_date_is_before_borrow_date`: Validasi gagal jika tanggal kembali sebelum tanggal pinjam.
   - `prevents_double_return`: Transaksi yang sudah selesai dikembalikan ditolak saat mencoba diproses ulang.

---

## 🔒 Keamanan & Integritas Data

- **Perlindungan CSRF:** Setiap formulir POST, PUT, dan DELETE dilindungi oleh token `@csrf`.
- **Perlindungan SQL Injection:** Seluruh manipulasi kueri menggunakan PDO prepared statements melalui Eloquent ORM.
- **Pencegahan Race Condition:** Penguncian baris (`lockForUpdate()`) diimplementasikan pada operasi stok dan status pengembalian.
- **Blade Escaping:** Seluruh data teks yang ditampilkan ke layar di-escape otomatis menggunakan sintaks `{{ ... }}` untuk mencegah Cross-Site Scripting (XSS).
- **Relasi Foreign Key Restrict:** Mencegah orphaned records pada tabel `mahasiswa` dan `alat`.
