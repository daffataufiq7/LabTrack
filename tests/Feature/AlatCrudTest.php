<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
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

    public function test_creating_alat_automatically_generates_physical_unit_records(): void
    {
        $alat = Alat::create([
            'kode_alat' => 'ALT-TEST-UNIT',
            'nama_alat' => 'Spectrum Analyzer',
            'kategori' => 'Elektronika',
            'stok_total' => 3,
            'stok_tersedia' => 3,
            'kondisi' => 'Baik',
        ]);

        $this->assertCount(3, $alat->units);
        $this->assertDatabaseHas('alat_unit', [
            'alat_id' => $alat->id,
            'kode_unit' => 'ALT-TEST-UNIT-U01',
            'status' => 'tersedia',
        ]);
        $this->assertDatabaseHas('alat_unit', [
            'alat_id' => $alat->id,
            'kode_unit' => 'ALT-TEST-UNIT-U02',
            'status' => 'tersedia',
        ]);
        $this->assertDatabaseHas('alat_unit', [
            'alat_id' => $alat->id,
            'kode_unit' => 'ALT-TEST-UNIT-U03',
            'status' => 'tersedia',
        ]);
    }

    public function test_borrowing_allocates_units_and_returning_with_damage_marks_unit_as_rusak(): void
    {
        $mhs = Mahasiswa::create([
            'nim' => '22051204099',
            'nama' => 'Test Unit Tracking Mhs',
            'program_studi' => 'Teknik Elektro',
        ]);

        $alat = Alat::create([
            'kode_alat' => 'ALT-OSC-99',
            'nama_alat' => 'Osiloskop Dual Channel',
            'kategori' => 'Elektronika',
            'stok_total' => 2,
            'stok_tersedia' => 2,
            'kondisi' => 'Baik',
        ]);

        // Pinjam 2 unit
        $response = $this->post(route('peminjaman.store'), [
            'mahasiswa_id' => $mhs->id,
            'tanggal_pinjam' => Carbon::today()->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7)->toDateString(),
            'items' => [
                ['alat_id' => $alat->id, 'jumlah' => 2],
            ],
        ]);

        $peminjaman = Peminjaman::latest('id')->first();
        $detail = $peminjaman->detailPeminjaman->first();

        // Verifikasi kode unit tersimpan pada detail transaksi
        $this->assertNotNull($detail->kode_unit_dipinjam);
        $this->assertStringContainsString('ALT-OSC-99-U01', $detail->kode_unit_dipinjam);
        $this->assertStringContainsString('ALT-OSC-99-U02', $detail->kode_unit_dipinjam);

        // Verifikasi status unit berubah jadi 'dipinjam'
        $this->assertEquals(0, $alat->fresh()->stok_tersedia);
        $this->assertDatabaseHas('alat_unit', [
            'kode_unit' => 'ALT-OSC-99-U01',
            'status' => 'dipinjam',
            'peminjaman_id' => $peminjaman->id,
        ]);

        // Pengembalian: 1 unit rusak, 1 unit baik
        $kembaliResponse = $this->post(route('pengembalian.process', $peminjaman->id), [
            'tanggal_kembali' => Carbon::today()->toDateString(),
            'status_pembayaran' => 'lunas',
            'metode_pembayaran' => 'Tunai',
            'detail' => [
                $detail->id => [
                    'jumlah_rusak' => 1,
                    'kondisi_catatan' => 'Layar bergaris',
                ],
            ],
        ]);
        $kembaliResponse->assertSessionHas('success');

        // Unit 1 harus rusak, Unit 2 harus kembali tersedia
        $this->assertDatabaseHas('alat_unit', [
            'kode_unit' => 'ALT-OSC-99-U01',
            'status' => 'rusak',
            'peminjaman_id' => null,
        ]);
        $this->assertDatabaseHas('alat_unit', [
            'kode_unit' => 'ALT-OSC-99-U02',
            'status' => 'tersedia',
            'peminjaman_id' => null,
        ]);

        // Stok induk harus mencerminkan 1 tersedia dan 1 rusak
        $this->assertEquals(1, $alat->fresh()->stok_tersedia);
        $this->assertEquals(1, $alat->fresh()->stok_rusak);
    }

    public function test_can_upload_image_when_creating_and_updating_alat(): void
    {
        $fakeImage = UploadedFile::fake()->image('test_oscillo.jpg', 600, 600);
        $alat = null;
        $oldFilePath = null;

        try {
            $response = $this->post(route('alat.store'), [
                'kode_alat' => 'ALT-IMG-001',
                'nama_alat' => 'Alat Dengan Gambar',
                'kategori' => 'Elektronika',
                'stok_total' => 2,
                'stok_tersedia' => 2,
                'kondisi' => 'Baik',
                'gambar' => $fakeImage,
            ]);

            $response->assertRedirect(route('alat.index'));
            $alat = Alat::where('kode_alat', 'ALT-IMG-001')->first();
            $this->assertNotNull($alat);
            $this->assertNotNull($alat->gambar);
            $this->assertStringStartsWith('images/alat/', $alat->gambar);
            $this->assertFileExists(public_path($alat->gambar));

            // Test update dengan gambar baru
            $newImage = UploadedFile::fake()->image('test_oscillo_updated.png', 600, 600);
            $oldFilePath = public_path($alat->gambar);

            $updateResponse = $this->put(route('alat.update', $alat->id), [
                'kode_alat' => 'ALT-IMG-001',
                'nama_alat' => 'Alat Dengan Gambar Terupdate',
                'kategori' => 'Elektronika',
                'stok_total' => 2,
                'stok_tersedia' => 2,
                'kondisi' => 'Baik',
                'gambar' => $newImage,
            ]);

            $updateResponse->assertRedirect(route('alat.index'));
            $alat->refresh();
            $this->assertFileExists(public_path($alat->gambar));
        } finally {
            if ($alat && $alat->gambar && File::exists(public_path($alat->gambar))) {
                File::delete(public_path($alat->gambar));
            }
            if ($oldFilePath && File::exists($oldFilePath)) {
                File::delete($oldFilePath);
            }
        }
    }
}
