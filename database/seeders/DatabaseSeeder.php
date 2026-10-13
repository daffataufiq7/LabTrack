<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Data Mahasiswa Demo
        $mahasiswaList = [
            [
                'nim' => '22051204001',
                'nama' => 'Ahmad Fajar Pratama',
                'program_studi' => 'Teknik Informatika',
                'no_hp' => '081234567890',
            ],
            [
                'nim' => '22051204015',
                'nama' => 'Siti Nur Aisyah',
                'program_studi' => 'Sistem Informasi',
                'no_hp' => '082198765432',
            ],
            [
                'nim' => '22051204028',
                'nama' => 'Budi Santoso',
                'program_studi' => 'Teknologi Informasi',
                'no_hp' => '085712345678',
            ],
            [
                'nim' => '22051204033',
                'nama' => 'Dewi Lestari Putri',
                'program_studi' => 'Teknik Informatika',
                'no_hp' => '087812349988',
            ],
            [
                'nim' => '22051204049',
                'nama' => 'Rian Hidayatullah',
                'program_studi' => 'Sistem Informasi',
                'no_hp' => '089611223344',
            ],
        ];

        $mahasiswaModels = [];
        foreach ($mahasiswaList as $data) {
            $mahasiswaModels[] = Mahasiswa::create($data);
        }

        // 2. Data Alat Demo
        $alatList = [
            [
                'kode_alat' => 'ALT-ELK-001',
                'nama_alat' => 'Osiloskop Digital Rigol DS1054Z',
                'kategori' => 'Elektronika & Instrumentasi',
                'stok_total' => 8,
                'stok_tersedia' => 8,
                'kondisi' => 'Baik',
            ],
            [
                'kode_alat' => 'ALT-ELK-002',
                'nama_alat' => 'Multimeter Digital Sanwa CD800a',
                'kategori' => 'Elektronika & Instrumentasi',
                'stok_total' => 15,
                'stok_tersedia' => 15,
                'kondisi' => 'Baik',
            ],
            [
                'kode_alat' => 'ALT-IOT-001',
                'nama_alat' => 'ESP32 NodeMCU Development Board kit',
                'kategori' => 'IoT & Embedded System',
                'stok_total' => 20,
                'stok_tersedia' => 20,
                'kondisi' => 'Baik',
            ],
            [
                'kode_alat' => 'ALT-IOT-002',
                'nama_alat' => 'Raspberry Pi 4 Model B (4GB RAM)',
                'kategori' => 'IoT & Embedded System',
                'stok_total' => 10,
                'stok_tersedia' => 10,
                'kondisi' => 'Baik',
            ],
            [
                'kode_alat' => 'ALT-JRG-001',
                'nama_alat' => 'Crimping Tool RJ45 Proskit + Cable Tester',
                'kategori' => 'Jaringan Komputer',
                'stok_total' => 12,
                'stok_tersedia' => 12,
                'kondisi' => 'Baik',
            ],
            [
                'kode_alat' => 'ALT-OPT-001',
                'nama_alat' => 'Mikroskop Binokuler Olympus CX23',
                'kategori' => 'Instrumen & Optik',
                'stok_total' => 6,
                'stok_tersedia' => 6,
                'kondisi' => 'Baik',
            ],
            [
                'kode_alat' => 'ALT-PRK-001',
                'nama_alat' => 'Solder Station Hakko FX-888D ESD Safe',
                'kategori' => 'Perkakas Lab',
                'stok_total' => 10,
                'stok_tersedia' => 10,
                'kondisi' => 'Baik',
            ],
        ];

        $alatMap = [];
        foreach ($alatList as $item) {
            $created = Alat::create($item);
            $alatMap[$created->kode_alat] = $created;
        }

        // 3. Data Transaksi Demo

        // Transaksi 1: Selesai Tepat Waktu (Ahmad Fajar Pratama)
        // Pinjam 10 hari lalu, tempo 3 hari lalu, kembali 3 hari lalu (tepat waktu)
        $tglPinjam1 = Carbon::today()->subDays(10);
        $tglTempo1 = Carbon::today()->subDays(3);
        $tglKembali1 = Carbon::today()->subDays(3);

        $trx1 = Peminjaman::create([
            'kode_transaksi' => 'TRX-' . $tglPinjam1->format('Ymd') . '-0001',
            'mahasiswa_id' => $mahasiswaModels[0]->id,
            'tanggal_pinjam' => $tglPinjam1->toDateString(),
            'tanggal_jatuh_tempo' => $tglTempo1->toDateString(),
            'tanggal_kembali' => $tglKembali1->toDateString(),
            'status' => 'dikembalikan',
            'total_denda' => 0,
            'total_biaya_perbaikan' => 0,
        ]);

        DetailPeminjaman::create([
            'peminjaman_id' => $trx1->id,
            'alat_id' => $alatMap['ALT-ELK-002']->id,
            'jumlah' => 2,
            'kondisi_kembali' => 'Baik',
            'biaya_perbaikan' => 0,
        ]);

        // Transaksi 2: Selesai Terlambat 5 Hari + 1 Unit Rusak (Siti Nur Aisyah)
        // Jatuh tempo 7 hari lalu, kembali 2 hari lalu (terlambat 5 hari).
        // Denda: 5 hari - 3 masa tenggang = 2 hari kena denda × Rp2.000 = Rp4.000
        // Kerusakan: 1 unit ESP32 rusak (biaya Rp10.000). Total perbaikan = Rp10.000.
        // Stok: 1 unit ESP32 rusak tidak dipulihkan, 1 unit baik dipulihkan.
        $tglPinjam2 = Carbon::today()->subDays(14);
        $tglTempo2 = Carbon::today()->subDays(7);
        $tglKembali2 = Carbon::today()->subDays(2);

        $trx2 = Peminjaman::create([
            'kode_transaksi' => 'TRX-' . $tglPinjam2->format('Ymd') . '-0002',
            'mahasiswa_id' => $mahasiswaModels[1]->id,
            'tanggal_pinjam' => $tglPinjam2->toDateString(),
            'tanggal_jatuh_tempo' => $tglTempo2->toDateString(),
            'tanggal_kembali' => $tglKembali2->toDateString(),
            'status' => 'dikembalikan',
            'total_denda' => 4000,
            'total_biaya_perbaikan' => 10000,
        ]);

        DetailPeminjaman::create([
            'peminjaman_id' => $trx2->id,
            'alat_id' => $alatMap['ALT-IOT-001']->id,
            'jumlah' => 2,
            'kondisi_kembali' => '1 Rusak, 1 Baik',
            'biaya_perbaikan' => 10000,
        ]);
        // Potong stok tersedia untuk unit yang rusak (tidak dipulihkan):
        $alatMap['ALT-IOT-001']->decrement('stok_tersedia', 1);

        // Transaksi 3: Sedang Aktif Dipinjam - Belum Jatuh Tempo (Budi Santoso)
        // Pinjam 2 hari lalu, jatuh tempo 5 hari ke depan
        $tglPinjam3 = Carbon::today()->subDays(2);
        $tglTempo3 = Carbon::today()->addDays(5);

        $trx3 = Peminjaman::create([
            'kode_transaksi' => 'TRX-' . $tglPinjam3->format('Ymd') . '-0003',
            'mahasiswa_id' => $mahasiswaModels[2]->id,
            'tanggal_pinjam' => $tglPinjam3->toDateString(),
            'tanggal_jatuh_tempo' => $tglTempo3->toDateString(),
            'tanggal_kembali' => null,
            'status' => 'dipinjam',
            'total_denda' => 0,
            'total_biaya_perbaikan' => 0,
        ]);

        DetailPeminjaman::create([
            'peminjaman_id' => $trx3->id,
            'alat_id' => $alatMap['ALT-ELK-001']->id,
            'jumlah' => 1,
            'kondisi_kembali' => null,
            'biaya_perbaikan' => 0,
        ]);
        DetailPeminjaman::create([
            'peminjaman_id' => $trx3->id,
            'alat_id' => $alatMap['ALT-PRK-001']->id,
            'jumlah' => 2,
            'kondisi_kembali' => null,
            'biaya_perbaikan' => 0,
        ]);
        // Kurangi stok tersedia alat yang sedang dipinjam:
        $alatMap['ALT-ELK-001']->decrement('stok_tersedia', 1);
        $alatMap['ALT-PRK-001']->decrement('stok_tersedia', 2);

        // Transaksi 4: Sedang Aktif Dipinjam - SUDAH TERLAMBAT (Dewi Lestari Putri)
        // Pinjam 12 hari lalu, jatuh tempo 4 hari lalu (terlambat 4 hari aktif!)
        $tglPinjam4 = Carbon::today()->subDays(12);
        $tglTempo4 = Carbon::today()->subDays(4);

        $trx4 = Peminjaman::create([
            'kode_transaksi' => 'TRX-' . $tglPinjam4->format('Ymd') . '-0004',
            'mahasiswa_id' => $mahasiswaModels[3]->id,
            'tanggal_pinjam' => $tglPinjam4->toDateString(),
            'tanggal_jatuh_tempo' => $tglTempo4->toDateString(),
            'tanggal_kembali' => null,
            'status' => 'dipinjam',
            'total_denda' => 0,
            'total_biaya_perbaikan' => 0,
        ]);

        DetailPeminjaman::create([
            'peminjaman_id' => $trx4->id,
            'alat_id' => $alatMap['ALT-IOT-002']->id,
            'jumlah' => 2,
            'kondisi_kembali' => null,
            'biaya_perbaikan' => 0,
        ]);
        $alatMap['ALT-IOT-002']->decrement('stok_tersedia', 2);
    }
}
