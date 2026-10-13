<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengembalianTest extends TestCase
{
    use RefreshDatabase;

    private Mahasiswa $mhs;
    private Alat $alat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mhs = Mahasiswa::create([
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar',
            'program_studi' => 'Teknik Informatika',
        ]);

        $this->alat = Alat::create([
            'kode_alat' => 'ALT-TEST-01',
            'nama_alat' => 'Osiloskop Test',
            'kategori' => 'Elektronika',
            'stok_total' => 10,
            'stok_tersedia' => 8, // Sedang dipinjam 2
            'kondisi' => 'Baik',
        ]);
    }

    private function createLoan(string $tglPinjam, string $tglTempo, int $jumlah = 2): Peminjaman
    {
        $peminjaman = Peminjaman::create([
            'kode_transaksi' => 'TRX-TEST-KEMBALI',
            'mahasiswa_id' => $this->mhs->id,
            'tanggal_pinjam' => $tglPinjam,
            'tanggal_jatuh_tempo' => $tglTempo,
            'status' => 'dipinjam',
            'total_denda' => 0,
            'total_biaya_perbaikan' => 0,
        ]);

        DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $this->alat->id,
            'jumlah' => $jumlah,
        ]);

        return $peminjaman;
    }

    public function test_pengembalian_tepat_waktu(): void
    {
        $tglPinjam = '2026-10-01';
        $tglTempo = '2026-10-08';
        $tglKembali = '2026-10-08'; // Tepat waktu

        $peminjaman = $this->createLoan($tglPinjam, $tglTempo, 2);
        $detail = $peminjaman->detailPeminjaman->first();

        $payload = [
            'tanggal_kembali' => $tglKembali,
            'detail' => [
                $detail->id => [
                    'jumlah_rusak' => 0,
                    'kondisi_catatan' => 'Lengkap dan bersih',
                ],
            ],
        ];

        $response = $this->post(route('pengembalian.process', $peminjaman->id), $payload);
        $response->assertRedirect(route('peminjaman.show', $peminjaman->id));
        $response->assertSessionHas('success');

        $peminjamanRefreshed = $peminjaman->fresh();
        $this->assertEquals('dikembalikan', $peminjamanRefreshed->status);
        $this->assertEquals(0, (float) $peminjamanRefreshed->total_denda);
        $this->assertEquals(0, (float) $peminjamanRefreshed->total_biaya_perbaikan);

        // Stok layak dipulihkan penuh: 8 + 2 = 10
        $this->assertEquals(10, $this->alat->fresh()->stok_tersedia);
    }

    public function test_pengembalian_terlambat_5_hari_dikenai_denda_dengan_masa_tenggang_3_hari(): void
    {
        // Jatuh tempo: 2026-10-05
        // Tanggal kembali: 2026-10-10 (terlambat 5 hari)
        // Masa tenggang: 3 hari
        // Hari dikenai denda: 5 - 3 = 2 hari
        // Denda: 2 hari * Rp2.000 = Rp4.000
        $tglPinjam = '2026-09-28';
        $tglTempo = '2026-10-05';
        $tglKembali = '2026-10-10';

        $peminjaman = $this->createLoan($tglPinjam, $tglTempo, 2);
        $detail = $peminjaman->detailPeminjaman->first();

        $payload = [
            'tanggal_kembali' => $tglKembali,
            'detail' => [
                $detail->id => [
                    'jumlah_rusak' => 0,
                    'kondisi_catatan' => 'Semua baik',
                ],
            ],
        ];

        $response = $this->post(route('pengembalian.process', $peminjaman->id), $payload);
        $response->assertSessionHas('success');

        $peminjamanRefreshed = $peminjaman->fresh();
        $this->assertEquals('dikembalikan', $peminjamanRefreshed->status);
        $this->assertEquals(4000, (float) $peminjamanRefreshed->total_denda);
        $this->assertEquals(0, (float) $peminjamanRefreshed->total_biaya_perbaikan);
        $this->assertEquals(10, $this->alat->fresh()->stok_tersedia);
    }

    public function test_pengembalian_dengan_alat_rusak_menghitung_biaya_perbaikan_dan_tidak_memulihkan_stok_rusak(): void
    {
        // Pinjam 2 unit alat. Saat kembali: 1 unit rusak, 1 unit baik.
        // Biaya perbaikan: 1 * Rp10.000 = Rp10.000.
        // Stok: Awal tersedia 8. Hanya 1 unit baik yang dipulihkan, jadi 8 + 1 = 9 (unit rusak TIDAK dipulihkan).
        $tglPinjam = '2026-10-01';
        $tglTempo = '2026-10-08';
        $tglKembali = '2026-10-07';

        $peminjaman = $this->createLoan($tglPinjam, $tglTempo, 2);
        $detail = $peminjaman->detailPeminjaman->first();

        $payload = [
            'tanggal_kembali' => $tglKembali,
            'detail' => [
                $detail->id => [
                    'jumlah_rusak' => 1,
                    'kondisi_catatan' => '1 Unit layar retak',
                ],
            ],
        ];

        $response = $this->post(route('pengembalian.process', $peminjaman->id), $payload);
        $response->assertSessionHas('success');

        $peminjamanRefreshed = $peminjaman->fresh();
        $this->assertEquals('dikembalikan', $peminjamanRefreshed->status);
        $this->assertEquals(0, (float) $peminjamanRefreshed->total_denda);
        $this->assertEquals(10000, (float) $peminjamanRefreshed->total_biaya_perbaikan);

        // Verifikasi detail record
        $detailRefreshed = $detail->fresh();
        $this->assertEquals(10000, (float) $detailRefreshed->biaya_perbaikan);
        $this->assertStringContainsString('1 Baik, 1 Rusak', $detailRefreshed->kondisi_kembali);

        // Verifikasi stok: Hanya 1 unit layak yang dipulihkan!
        // 8 + 1 = 9 unit (bukan 10), dan 1 unit tercatat di stok_rusak
        $this->assertEquals(9, $this->alat->fresh()->stok_tersedia);
        $this->assertEquals(1, $this->alat->fresh()->stok_rusak);
    }

    public function test_fails_when_return_date_is_before_borrow_date(): void
    {
        $tglPinjam = '2026-10-10';
        $tglTempo = '2026-10-17';
        $tglKembali = '2026-10-05'; // Sebelum tanggal pinjam!

        $peminjaman = $this->createLoan($tglPinjam, $tglTempo, 1);
        $detail = $peminjaman->detailPeminjaman->first();

        $payload = [
            'tanggal_kembali' => $tglKembali,
            'detail' => [
                $detail->id => [
                    'jumlah_rusak' => 0,
                ],
            ],
        ];

        $response = $this->post(route('pengembalian.process', $peminjaman->id), $payload);
        $response->assertSessionHasErrors(['tanggal_kembali']);
        $this->assertEquals('dipinjam', $peminjaman->fresh()->status);
    }

    public function test_prevents_double_return(): void
    {
        $tglPinjam = '2026-10-01';
        $tglTempo = '2026-10-08';
        $tglKembali = '2026-10-08';

        $peminjaman = $this->createLoan($tglPinjam, $tglTempo, 2);
        $detail = $peminjaman->detailPeminjaman->first();

        $payload = [
            'tanggal_kembali' => $tglKembali,
            'detail' => [
                $detail->id => [
                    'jumlah_rusak' => 0,
                ],
            ],
        ];

        // Pengembalian pertama (sukses)
        $this->post(route('pengembalian.process', $peminjaman->id), $payload);
        $this->assertEquals('dikembalikan', $peminjaman->fresh()->status);
        $this->assertEquals(10, $this->alat->fresh()->stok_tersedia);

        // Percobaan pengembalian kedua untuk transaksi yang sama (Harus Ditolak!)
        $response2 = $this->post(route('pengembalian.process', $peminjaman->id), $payload);
        $response2->assertSessionHas('error');

        // Pastikan stok tidak bertambah lagi (mencegah overflow stok)
        $this->assertEquals(10, $this->alat->fresh()->stok_tersedia);
    }

    public function test_pengembalian_dengan_denda_dapat_disimpan_belum_lunas_dan_memblokir_peminjaman_baru(): void
    {
        $tglPinjam = '2026-09-28';
        $tglTempo = '2026-10-05';
        $tglKembali = '2026-10-10'; // Terlambat 5 hari -> denda Rp4.000

        $peminjaman = $this->createLoan($tglPinjam, $tglTempo, 1);
        $detail = $peminjaman->detailPeminjaman->first();

        $payload = [
            'tanggal_kembali' => $tglKembali,
            'status_pembayaran' => 'belum_lunas',
            'detail' => [
                $detail->id => [
                    'jumlah_rusak' => 0,
                    'kondisi_catatan' => 'Kembali lengkap tapi belum bawa uang denda',
                ],
            ],
        ];

        $response = $this->post(route('pengembalian.process', $peminjaman->id), $payload);
        $response->assertSessionHas('success');

        $peminjamanRefreshed = $peminjaman->fresh();
        $this->assertEquals('dikembalikan', $peminjamanRefreshed->status);
        $this->assertEquals('belum_lunas', $peminjamanRefreshed->status_pembayaran);
        $this->assertEquals(4000, (float) $peminjamanRefreshed->total_denda);

        // Percobaan meminjam lagi saat masih menunggak denda -> HARUS DITOLAK
        $pinjamBaruPayload = [
            'mahasiswa_id' => $this->mhs->id,
            'tanggal_pinjam' => '2026-10-14',
            'tanggal_jatuh_tempo' => '2026-10-21',
            'items' => [
                ['alat_id' => $this->alat->id, 'jumlah' => 1],
            ],
        ];

        $pinjamResponse = $this->post(route('peminjaman.store'), $pinjamBaruPayload);
        $pinjamResponse->assertSessionHas('error');
        $this->assertStringContainsString('BELUM LUNAS', session('error'));
    }

    public function test_admin_dapat_mengonfirmasi_pelunasan_denda_dan_membuka_blokir(): void
    {
        $tglPinjam = '2026-09-28';
        $tglTempo = '2026-10-05';
        $tglKembali = '2026-10-10';

        $peminjaman = $this->createLoan($tglPinjam, $tglTempo, 1);
        $detail = $peminjaman->detailPeminjaman->first();

        // 1. Pengembalian dengan status belum lunas
        $this->post(route('pengembalian.process', $peminjaman->id), [
            'tanggal_kembali' => $tglKembali,
            'status_pembayaran' => 'belum_lunas',
            'detail' => [
                $detail->id => ['jumlah_rusak' => 0],
            ],
        ]);

        $this->assertEquals('belum_lunas', $peminjaman->fresh()->status_pembayaran);

        // 2. Admin konfirmasi pelunasan kasir lab
        $bayarResponse = $this->from(route('peminjaman.show', $peminjaman->id))
            ->post(route('pengembalian.bayar', $peminjaman->id), [
                'metode_pembayaran' => 'QRIS Laboratorium',
                'catatan' => 'Dibayar via QRIS Dinamis Kasir Lab',
            ]);

        $bayarResponse->assertRedirect(route('peminjaman.show', $peminjaman->id));
        $bayarResponse->assertSessionHas('success');

        $peminjamanLunas = $peminjaman->fresh();
        $this->assertEquals('lunas', $peminjamanLunas->status_pembayaran);
        $this->assertEquals('QRIS Laboratorium', $peminjamanLunas->metode_pembayaran);
        $this->assertNotNull($peminjamanLunas->waktu_pembayaran);

        // 3. Mahasiswa sekarang sudah bisa meminjam lagi tanpa diblokir
        $pinjamBaruPayload = [
            'mahasiswa_id' => $this->mhs->id,
            'tanggal_pinjam' => '2026-10-14',
            'tanggal_jatuh_tempo' => '2026-10-21',
            'items' => [
                ['alat_id' => $this->alat->id, 'jumlah' => 1],
            ],
        ];

        $pinjamResponse = $this->post(route('peminjaman.store'), $pinjamBaruPayload);
        $pinjamResponse->assertSessionHas('success');
    }
}
