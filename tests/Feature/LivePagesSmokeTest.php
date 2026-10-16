<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivePagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $userMahasiswa;
    private Mahasiswa $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mahasiswa = Mahasiswa::create([
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar Pratama',
            'program_studi' => 'Teknik Informatika',
            'no_hp' => '081234567890',
        ]);

        $this->admin = User::create([
            'name' => 'Petugas Laboratorium',
            'username' => '2',
            'email' => 'admin@lab.ac.id',
            'password' => bcrypt('2'),
            'role' => 'admin',
        ]);

        $this->userMahasiswa = User::create([
            'name' => 'Ahmad Fajar Pratama',
            'username' => '1',
            'email' => '22051204001@mhs.ac.id',
            'password' => bcrypt('1'),
            'role' => 'mahasiswa',
            'mahasiswa_id' => $this->mahasiswa->id,
        ]);

        Alat::create([
            'kode_alat' => 'ALT-TI-01',
            'nama_alat' => 'Mikroskop Digital',
            'kategori' => 'Optik',
            'stok_total' => 5,
            'stok_tersedia' => 5,
            'kondisi' => 'Baik',
        ]);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sistem Peminjaman');
        $response->assertSee('Masuk ke Akun Anda');
    }

    public function test_register_page_renders_successfully(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_all_admin_pages_render_successfully(): void
    {
        $adminUrls = [
            '/dashboard' => 'Selamat Datang, Admin Lab!',
            '/mahasiswa' => 'Data Mahasiswa',
            '/alat' => 'Data Alat Laboratorium',
            '/peminjaman' => 'Transaksi Peminjaman',
            '/pengembalian' => 'Pengembalian Alat',
            '/denda' => 'Data Denda',
            '/laporan' => 'Laporan',
            '/pengaturan' => 'Pengaturan',
        ];

        foreach ($adminUrls as $url => $expectedText) {
            $response = $this->actingAs($this->admin)->get($url);
            $response->assertStatus(200);
            $response->assertSee($expectedText);
        }
    }

    public function test_all_student_portal_pages_render_successfully(): void
    {
        $studentUrls = [
            '/portal/dashboard',
            '/portal/katalog',
            '/portal/ajukan',
            '/portal/riwayat',
        ];

        foreach ($studentUrls as $url) {
            $response = $this->actingAs($this->userMahasiswa)->get($url);
            $response->assertStatus(200);
        }
    }
}
