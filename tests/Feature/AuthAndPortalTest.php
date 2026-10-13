<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $userMahasiswa;
    private Mahasiswa $mahasiswa;
    private Alat $alat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mahasiswa = Mahasiswa::create([
            'nim' => '22051204001',
            'nama' => 'Ahmad Fajar Pratama',
            'program_studi' => 'Teknik Informatika',
            'no_hp' => '081234567890',
        ]);

        $this->userMahasiswa = User::create([
            'name' => 'Ahmad Fajar Pratama',
            'username' => '1',
            'password' => bcrypt('1'),
            'role' => 'mahasiswa',
            'mahasiswa_id' => $this->mahasiswa->id,
        ]);

        $this->admin = User::create([
            'name' => 'Petugas Laboratorium',
            'username' => '2',
            'password' => bcrypt('2'),
            'role' => 'admin',
        ]);

        $this->alat = Alat::create([
            'kode_alat' => 'ALT-TEST-001',
            'nama_alat' => 'Digital Oscilloscope',
            'kategori' => 'Elektronika',
            'stok_total' => 10,
            'stok_tersedia' => 10,
            'kondisi' => 'Baik',
        ]);
    }

    public function test_login_as_mahasiswa_with_username_and_password_1(): void
    {
        $response = $this->post('/login', [
            'username' => '1',
            'password' => '1',
        ]);

        $response->assertRedirect('/portal/dashboard');
        $this->assertAuthenticatedAs($this->userMahasiswa);
    }

    public function test_login_as_admin_with_username_and_password_2(): void
    {
        $response = $this->post('/login', [
            'username' => '2',
            'password' => '2',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_logout(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_mahasiswa_can_view_katalog_and_stok(): void
    {
        $response = $this->actingAs($this->userMahasiswa)->get('/portal/katalog');
        $response->assertStatus(200);
        $response->assertSee('Digital Oscilloscope');
        $response->assertSee('10');
    }

    public function test_mahasiswa_can_submit_loan_request_with_status_menunggu_acc(): void
    {
        $tglPinjam = Carbon::today()->toDateString();
        $tglTempo = Carbon::today()->addDays(7)->toDateString();

        $response = $this->actingAs($this->userMahasiswa)->post('/portal/ajukan', [
            'tanggal_pinjam' => $tglPinjam,
            'tanggal_jatuh_tempo' => $tglTempo,
            'items' => [
                [
                    'alat_id' => $this->alat->id,
                    'jumlah' => 2,
                ]
            ],
        ]);

        $response->assertRedirect('/portal/riwayat?tab=menunggu_acc');
        $this->assertDatabaseHas('peminjaman', [
            'mahasiswa_id' => $this->mahasiswa->id,
            'status' => 'menunggu_acc',
        ]);

        // Pastikan stok belum berkurang saat masih menunggu ACC
        $this->alat->refresh();
        $this->assertEquals(10, $this->alat->stok_tersedia);
    }

    public function test_admin_can_approve_pending_loan_and_deducts_stock(): void
    {
        // Buat pengajuan peminjaman menunggu ACC
        $peminjaman = Peminjaman::create([
            'kode_transaksi' => Peminjaman::generateKodeTransaksi(),
            'mahasiswa_id' => $this->mahasiswa->id,
            'tanggal_pinjam' => Carbon::today(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7),
            'status' => 'menunggu_acc',
        ]);

        $peminjaman->detailPeminjaman()->create([
            'alat_id' => $this->alat->id,
            'jumlah' => 3,
        ]);

        // Admin melakukan ACC
        $response = $this->actingAs($this->admin)->post("/peminjaman/{$peminjaman->id}/approve", [
            'catatan_admin' => 'Disetujui untuk praktikum elektronika',
        ]);

        $response->assertRedirect();
        $peminjaman->refresh();
        $this->assertEquals('dipinjam', $peminjaman->status);
        $this->assertNotNull($peminjaman->approved_at);

        // Pastikan stok tersedia berkurang dari 10 menjadi 7
        $this->alat->refresh();
        $this->assertEquals(7, $this->alat->stok_tersedia);
    }

    public function test_admin_can_reject_pending_loan_and_stock_remains(): void
    {
        $peminjaman = Peminjaman::create([
            'kode_transaksi' => Peminjaman::generateKodeTransaksi(),
            'mahasiswa_id' => $this->mahasiswa->id,
            'tanggal_pinjam' => Carbon::today(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7),
            'status' => 'menunggu_acc',
        ]);

        $peminjaman->detailPeminjaman()->create([
            'alat_id' => $this->alat->id,
            'jumlah' => 4,
        ]);

        // Admin menolak pengajuan
        $response = $this->actingAs($this->admin)->post("/peminjaman/{$peminjaman->id}/reject", [
            'catatan_admin' => 'Jadwal praktikum bertabrakan dengan kelas lain',
        ]);

        $response->assertRedirect();
        $peminjaman->refresh();
        $this->assertEquals('ditolak', $peminjaman->status);
        $this->assertEquals('Jadwal praktikum bertabrakan dengan kelas lain', $peminjaman->catatan_admin);

        // Stok tetap 10
        $this->alat->refresh();
        $this->assertEquals(10, $this->alat->stok_tersedia);
    }

    public function test_mahasiswa_can_view_loan_detail(): void
    {
        $peminjaman = Peminjaman::create([
            'kode_transaksi' => Peminjaman::generateKodeTransaksi(),
            'mahasiswa_id' => $this->mahasiswa->id,
            'tanggal_pinjam' => Carbon::today(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7),
            'status' => 'dipinjam',
        ]);

        $peminjaman->detailPeminjaman()->create([
            'alat_id' => $this->alat->id,
            'jumlah' => 1,
        ]);

        $response = $this->actingAs($this->userMahasiswa)->get("/portal/pinjaman/{$peminjaman->id}");
        $response->assertStatus(200);
        $response->assertSee($peminjaman->kode_transaksi);
    }

    public function test_student_can_register_new_account(): void
    {
        $response = $this->post('/register', [
            'nim' => '22051204099',
            'nama' => 'Bambang Pamungkas',
            'program_studi' => 'Teknik Informatika',
            'no_hp' => '081299887766',
            'username' => 'bambang99',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/portal/dashboard');
        $this->assertDatabaseHas('mahasiswa', [
            'nim' => '22051204099',
            'nama' => 'Bambang Pamungkas',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'bambang99',
            'role' => 'mahasiswa',
        ]);
    }

    public function test_cannot_submit_loan_with_past_date(): void
    {
        $pastDate = Carbon::yesterday()->toDateString();
        $response = $this->actingAs($this->userMahasiswa)->post('/portal/ajukan', [
            'tanggal_pinjam' => $pastDate,
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7)->toDateString(),
            'items' => [
                ['alat_id' => $this->alat->id, 'jumlah' => 1]
            ],
        ]);

        $response->assertSessionHasErrors(['tanggal_pinjam']);
    }

    public function test_cannot_submit_loan_with_return_date_before_borrow_date(): void
    {
        $borrowDate = Carbon::today()->addDays(5)->toDateString();
        $returnDate = Carbon::today()->addDays(2)->toDateString();

        $response = $this->actingAs($this->userMahasiswa)->post('/portal/ajukan', [
            'tanggal_pinjam' => $borrowDate,
            'tanggal_jatuh_tempo' => $returnDate,
            'items' => [
                ['alat_id' => $this->alat->id, 'jumlah' => 1]
            ],
        ]);

        $response->assertSessionHasErrors(['tanggal_jatuh_tempo']);
    }

    public function test_student_can_view_riwayat_page_and_tabs(): void
    {
        $response = $this->actingAs($this->userMahasiswa)->get('/portal/riwayat?tab=menunggu_acc');
        $response->assertStatus(200);
        $response->assertSee('Menunggu ACC');
    }

    public function test_student_can_cancel_pending_acc_application(): void
    {
        $peminjaman = Peminjaman::create([
            'kode_transaksi' => Peminjaman::generateKodeTransaksi(),
            'mahasiswa_id' => $this->mahasiswa->id,
            'tanggal_pinjam' => Carbon::today(),
            'tanggal_jatuh_tempo' => Carbon::today()->addDays(7),
            'status' => 'menunggu_acc',
        ]);

        $response = $this->actingAs($this->userMahasiswa)
            ->post("/portal/pinjaman/{$peminjaman->id}/batalkan-pengajuan");

        $response->assertSessionHas('success');
        $peminjaman->refresh();
        $this->assertEquals('dibatalkan', $peminjaman->status);
    }
}
