<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase;

    private Mahasiswa $mhs;
    private Alat $alat1;
    private Alat $alat2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mhs = Mahasiswa::create([
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar',
            'program_studi' => 'Teknik Informatika',
        ]);

        $this->alat1 = Alat::create([
            'kode_alat' => 'ALT-TEST-01',
            'nama_alat' => 'Osiloskop Test',
            'kategori' => 'Elektronika',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'kondisi' => 'Baik',
        ]);

        $this->alat2 = Alat::create([
            'kode_alat' => 'ALT-TEST-02',
            'nama_alat' => 'Multimeter Test',
            'kategori' => 'Elektronika',
            'stok_total' => 10,
            'stok_tersedia' => 10,
            'kondisi' => 'Baik',
        ]);
    }

    public function test_can_create_peminjaman_when_stock_is_sufficient(): void
    {
        $payload = [
            'mahasiswa_id' => $this->mhs->id,
            'tanggal_pinjam' => Carbon::today()->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7)->toDateString(),
            'items' => [
                [
                    'alat_id' => $this->alat1->id,
                    'jumlah' => 2,
                ],
                [
                    'alat_id' => $this->alat2->id,
                    'jumlah' => 3,
                ],
            ],
        ];

        $response = $this->post(route('peminjaman.store'), $payload);
        $response->assertSessionHas('success');

        // Pastikan header transaksi terbuat dengan status 'dipinjam'
        $peminjaman = Peminjaman::first();
        $this->assertNotNull($peminjaman);
        $this->assertEquals('dipinjam', $peminjaman->status);
        $this->assertEquals(2, $peminjaman->detailPeminjaman()->count());

        // Pastikan stok tersedia berkurang sesuai jumlah pinjam
        $this->assertEquals(3, $this->alat1->fresh()->stok_tersedia); // 5 - 2 = 3
        $this->assertEquals(7, $this->alat2->fresh()->stok_tersedia); // 10 - 3 = 7
    }

    public function test_fails_and_rollbacks_when_stock_is_insufficient(): void
    {
        $payload = [
            'mahasiswa_id' => $this->mhs->id,
            'tanggal_pinjam' => Carbon::today()->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7)->toDateString(),
            'items' => [
                [
                    'alat_id' => $this->alat1->id,
                    'jumlah' => 2,
                ],
                [
                    'alat_id' => $this->alat2->id,
                    'jumlah' => 15, // Melebihi stok_tersedia (10)
                ],
            ],
        ];

        $response = $this->post(route('peminjaman.store'), $payload);
        $response->assertSessionHas('error');

        // Transaksi tidak boleh tersimpan ke database (Rollback)
        $this->assertEquals(0, Peminjaman::count());

        // Stok kedua alat TIDAK BOLEH berkurang
        $this->assertEquals(5, $this->alat1->fresh()->stok_tersedia);
        $this->assertEquals(10, $this->alat2->fresh()->stok_tersedia);
    }

    public function test_fails_when_due_date_is_before_borrow_date(): void
    {
        $payload = [
            'mahasiswa_id' => $this->mhs->id,
            'tanggal_pinjam' => '2026-10-15',
            'tanggal_jatuh_tempo' => '2026-10-10', // Sebelum tanggal pinjam
            'items' => [
                [
                    'alat_id' => $this->alat1->id,
                    'jumlah' => 1,
                ],
            ],
        ];

        $response = $this->post(route('peminjaman.store'), $payload);
        $response->assertSessionHasErrors(['tanggal_jatuh_tempo']);
        $this->assertEquals(0, Peminjaman::count());
    }

    public function test_fails_when_item_quantity_is_not_positive(): void
    {
        $payload = [
            'mahasiswa_id' => $this->mhs->id,
            'tanggal_pinjam' => Carbon::today()->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7)->toDateString(),
            'items' => [
                [
                    'alat_id' => $this->alat1->id,
                    'jumlah' => 0, // Invalid: harus >= 1
                ],
            ],
        ];

        $response = $this->post(route('peminjaman.store'), $payload);
        $response->assertSessionHasErrors(['items.0.jumlah']);
    }

    public function test_can_cancel_active_loan_and_restores_stock(): void
    {
        // Buat peminjaman
        $this->post(route('peminjaman.store'), [
            'mahasiswa_id' => $this->mhs->id,
            'tanggal_pinjam' => Carbon::today()->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7)->toDateString(),
            'items' => [
                ['alat_id' => $this->alat1->id, 'jumlah' => 2],
            ],
        ]);

        $this->assertEquals(3, $this->alat1->fresh()->stok_tersedia);
        $peminjaman = Peminjaman::first();

        // Batalkan peminjaman
        $response = $this->post(route('peminjaman.cancel', $peminjaman->id));
        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // Status berubah jadi dibatalkan dan stok dipulihkan kembali ke 5
        $this->assertEquals('dibatalkan', $peminjaman->fresh()->status);
        $this->assertEquals(5, $this->alat1->fresh()->stok_tersedia);
    }
}
