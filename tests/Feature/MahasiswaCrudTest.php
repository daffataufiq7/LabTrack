<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPeminjaman;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahasiswaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_display_mahasiswa_index(): void
    {
        Mahasiswa::create([
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar',
            'program_studi' => 'Teknik Informatika',
            'no_hp' => '081234567890',
        ]);

        $response = $this->get(route('mahasiswa.index'));
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fajar');
        $response->assertSee('22051204001');
    }

    public function test_can_create_mahasiswa(): void
    {
        $payload = [
            'nim' => '22051204099',
            'nama' => 'Bambang Sudibyo',
            'program_studi' => 'Sistem Informasi',
            'no_hp' => '081299998888',
        ];

        $response = $this->post(route('mahasiswa.store'), $payload);
        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mahasiswa', [
            'nim' => '22051204099',
            'nama' => 'Bambang Sudibyo',
        ]);
    }

    public function test_cannot_create_mahasiswa_with_duplicate_nim(): void
    {
        Mahasiswa::create([
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar',
            'program_studi' => 'Teknik Informatika',
        ]);

        $response = $this->post(route('mahasiswa.store'), [
            'nim' => '22051204001',
            'nama' => 'Siswa Baru Duplikat',
            'program_studi' => 'Sistem Informasi',
        ]);

        $response->assertSessionHasErrors(['nim']);
    }

    public function test_can_update_mahasiswa(): void
    {
        $mhs = Mahasiswa::create([
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar',
            'program_studi' => 'Teknik Informatika',
        ]);

        $response = $this->put(route('mahasiswa.update', $mhs->id), [
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar Pratama, S.Kom.',
            'program_studi' => 'Teknik Informatika',
            'no_hp' => '081234567899',
        ]);

        $response->assertRedirect(route('mahasiswa.index'));
        $this->assertDatabaseHas('mahasiswa', [
            'id' => $mhs->id,
            'nama' => 'Ahmad Fajar Pratama, S.Kom.',
            'no_hp' => '081234567899',
        ]);
    }

    public function test_can_delete_mahasiswa_without_transactions(): void
    {
        $mhs = Mahasiswa::create([
            'nim' => '22051204002',
            'nama' => 'Siti Aminah',
            'program_studi' => 'Teknik Informatika',
        ]);

        $response = $this->delete(route('mahasiswa.destroy', $mhs->id));
        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('mahasiswa', ['id' => $mhs->id]);
    }

    public function test_cannot_delete_mahasiswa_with_transactions(): void
    {
        $mhs = Mahasiswa::create([
            'nim' => '22051204003',
            'nama' => 'Rina Wijaya',
            'program_studi' => 'Sistem Informasi',
        ]);

        $peminjaman = Peminjaman::create([
            'kode_transaksi' => 'TRX-TEST-001',
            'mahasiswa_id' => $mhs->id,
            'tanggal_pinjam' => Carbon::today()->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);

        $response = $this->delete(route('mahasiswa.destroy', $mhs->id));
        $response->assertRedirect(route('mahasiswa.index'));
        $response->assertSessionHas('error');

        // Data mahasiswa harus tetap ada
        $this->assertDatabaseHas('mahasiswa', ['id' => $mhs->id]);
    }
}
