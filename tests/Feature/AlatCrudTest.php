<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_display_alat_index(): void
    {
        Alat::create([
            'kode_alat' => 'ALT-TEST-001',
            'nama_alat' => 'Multimeter Digital Test',
            'kategori' => 'Elektronika',
            'stok_total' => 10,
            'stok_tersedia' => 10,
            'kondisi' => 'Baik',
        ]);

        $response = $this->get(route('alat.index'));
        $response->assertStatus(200);
        $response->assertSee('Multimeter Digital Test');
        $response->assertSee('ALT-TEST-001');
    }

    public function test_can_create_alat(): void
    {
        $payload = [
            'kode_alat' => 'ALT-ELK-100',
            'nama_alat' => 'Function Generator GW Instek',
            'kategori' => 'Elektronika',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'kondisi' => 'Baik',
        ];

        $response = $this->post(route('alat.store'), $payload);
        $response->assertRedirect(route('alat.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('alat', [
            'kode_alat' => 'ALT-ELK-100',
            'nama_alat' => 'Function Generator GW Instek',
        ]);
    }

    public function test_cannot_create_alat_when_available_stock_exceeds_total_stock(): void
    {
        $payload = [
            'kode_alat' => 'ALT-ERR-001',
            'nama_alat' => 'Alat Tidak Valid',
            'kategori' => 'Umum',
            'stok_total' => 5,
            'stok_tersedia' => 10, // Invalid: stok tersedia > stok total
            'kondisi' => 'Baik',
        ];

        $response = $this->post(route('alat.store'), $payload);
        $response->assertSessionHasErrors(['stok_tersedia']);
    }

    public function test_can_update_alat(): void
    {
        $alat = Alat::create([
            'kode_alat' => 'ALT-UPD-001',
            'nama_alat' => 'Solder Hakko',
            'kategori' => 'Perkakas',
            'stok_total' => 10,
            'stok_tersedia' => 10,
            'kondisi' => 'Baik',
        ]);

        $response = $this->put(route('alat.update', $alat->id), [
            'kode_alat' => 'ALT-UPD-001',
            'nama_alat' => 'Solder Hakko FX-888D New Edition',
            'kategori' => 'Perkakas Lab',
            'stok_total' => 12,
            'stok_tersedia' => 12,
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('alat.index'));
        $this->assertDatabaseHas('alat', [
            'id' => $alat->id,
            'nama_alat' => 'Solder Hakko FX-888D New Edition',
            'stok_total' => 12,
        ]);
    }

    public function test_can_delete_alat_without_transactions(): void
    {
        $alat = Alat::create([
            'kode_alat' => 'ALT-DEL-001',
            'nama_alat' => 'Kabel Jumper Eksperimen',
            'kategori' => 'Elektronika',
            'stok_total' => 50,
            'stok_tersedia' => 50,
            'kondisi' => 'Baik',
        ]);

        $response = $this->delete(route('alat.destroy', $alat->id));
        $response->assertRedirect(route('alat.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('alat', ['id' => $alat->id]);
    }

    public function test_cannot_delete_alat_with_transactions(): void
    {
        $mhs = Mahasiswa::create([
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar',
            'program_studi' => 'Teknik Informatika',
        ]);

        $alat = Alat::create([
            'kode_alat' => 'ALT-PRT-001',
            'nama_alat' => 'Mikroskop Riset',
            'kategori' => 'Optik',
            'stok_total' => 4,
            'stok_tersedia' => 3,
            'kondisi' => 'Baik',
        ]);

        $peminjaman = Peminjaman::create([
            'kode_transaksi' => 'TRX-TEST-ALAT-01',
            'mahasiswa_id' => $mhs->id,
            'tanggal_pinjam' => Carbon::today()->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);

        DetailPeminjaman::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
        ]);

        $response = $this->delete(route('alat.destroy', $alat->id));
        $response->assertRedirect(route('alat.index'));
        $response->assertSessionHas('error');

        // Alat tidak boleh terhapus
        $this->assertDatabaseHas('alat', ['id' => $alat->id]);
    }
}
