<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Laboratorium',
            'username' => 'admin_test',
            'email' => 'admin_test@kampus.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    public function test_can_render_pengaturan_page_with_all_tabs(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pengaturan.index'));

        $response->assertStatus(200);
        $response->assertSee('Profil Lab');
        $response->assertSee('Akun');
        $response->assertSee('Notifikasi');
        $response->assertSee('Backup');
        $response->assertSee('Informasi Laboratorium');
    }

    public function test_can_update_lab_profile_settings_and_persists(): void
    {
        $payload = [
            'nama_lab' => 'Laboratorium Rekayasa Perangkat Lunak Terpadu',
            'alamat' => 'Gedung TI Lantai 3, Kampus Barat',
            'kontak' => 'rpl_lab@kampus.ac.id',
            'jam_operasional' => 'Senin - Sabtu, 08:00 - 17:00 WIB',
            'denda_per_hari' => 3000,
            'toleransi_hari' => 2,
            'biaya_alat_rusak' => 15000,
            'maks_hari_pinjam' => 5,
        ];

        $response = $this->actingAs($this->admin)->post(route('pengaturan.update'), $payload);

        $response->assertRedirect(route('pengaturan.index', ['tab' => 'profil']));
        $response->assertSessionHas('success');

        // Verifikasi perubahan tersimpan permanen di basis data
        $this->assertEquals('Laboratorium Rekayasa Perangkat Lunak Terpadu', Pengaturan::get('nama_lab'));
        $this->assertEquals('Gedung TI Lantai 3, Kampus Barat', Pengaturan::get('alamat'));
        $this->assertEquals('rpl_lab@kampus.ac.id', Pengaturan::get('kontak'));
        $this->assertEquals('3000', Pengaturan::get('denda_per_hari'));

        // Verifikasi langsung terlihat di header dashboard
        $dashboardResponse = $this->actingAs($this->admin)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Laboratorium Rekayasa Perangkat Lunak Terpadu');
    }

    public function test_can_update_admin_account_and_password(): void
    {
        $payload = [
            'name' => 'Kepala Laboran Utama',
            'username' => 'laboran_utama',
            'email' => 'kepala_lab@kampus.ac.id',
            'current_password' => 'password123',
            'password' => 'sandi_baru_123',
            'password_confirmation' => 'sandi_baru_123',
        ];

        $response = $this->actingAs($this->admin)->post(route('pengaturan.akun'), $payload);

        $response->assertRedirect(route('pengaturan.index', ['tab' => 'akun']));
        $response->assertSessionHas('success');

        $this->admin->refresh();
        $this->assertEquals('Kepala Laboran Utama', $this->admin->name);
        $this->assertEquals('laboran_utama', $this->admin->username);
        $this->assertTrue(Hash::check('sandi_baru_123', $this->admin->password));
    }

    public function test_can_update_notification_preferences(): void
    {
        $payload = [
            'notif_pengajuan_baru' => '1',
            'notif_jatuh_tempo' => '1',
            // notif_denda intentionally omitted to test unchecked switch
            'notif_email_alert' => '1',
        ];

        $response = $this->actingAs($this->admin)->post(route('pengaturan.notifikasi'), $payload);

        $response->assertRedirect(route('pengaturan.index', ['tab' => 'notifikasi']));
        $response->assertSessionHas('success');

        $this->assertEquals('1', Pengaturan::get('notif_pengajuan_baru'));
        $this->assertEquals('0', Pengaturan::get('notif_denda'));
        $this->assertEquals('1', Pengaturan::get('notif_email_alert'));
    }

    public function test_can_download_database_backup_sql(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pengaturan.backup.download'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/sql');
        $this->assertStringContainsString('attachment; filename="backup_', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('LabTrack Database Backup', $response->getContent());
        $this->assertStringContainsString('CREATE TABLE', $response->getContent());
    }

    public function test_can_clear_system_cache(): void
    {
        $response = $this->actingAs($this->admin)->post(route('pengaturan.clear_cache'));

        $response->assertRedirect(route('pengaturan.index', ['tab' => 'backup']));
        $response->assertSessionHas('success');
    }

    public function test_can_view_student_user_accounts_list(): void
    {
        $student = User::create([
            'name' => 'Budi Pratama',
            'username' => '230101999',
            'email' => 'budi_test@kampus.ac.id',
            'password' => Hash::make('1'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('pengaturan.index', ['tab' => 'akun', 'subtab' => 'user']));

        $response->assertStatus(200);
        $response->assertSee('Budi Pratama');
        $response->assertSee('230101999');
        $response->assertSee('Kelola Akun User Mahasiswa');
    }

    public function test_can_toggle_deactivate_and_activate_student_user(): void
    {
        $student = User::create([
            'name' => 'Siti Aisyah',
            'username' => '230101888',
            'email' => 'siti_test@kampus.ac.id',
            'password' => Hash::make('1'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);

        // Nonaktifkan
        $response = $this->actingAs($this->admin)->post(route('pengaturan.users.toggle', $student->id));
        $response->assertRedirect(route('pengaturan.index', ['tab' => 'akun', 'subtab' => 'user']));
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertFalse($student->isActive());

        // Aktifkan kembali
        $response2 = $this->actingAs($this->admin)->post(route('pengaturan.users.toggle', $student->id));
        $response2->assertRedirect(route('pengaturan.index', ['tab' => 'akun', 'subtab' => 'user']));

        $student->refresh();
        $this->assertTrue($student->isActive());
    }

    public function test_can_update_student_user_info_and_password(): void
    {
        $student = User::create([
            'name' => 'Ahmad Dani',
            'username' => '230101777',
            'email' => 'ahmad_old@kampus.ac.id',
            'password' => Hash::make('old_pass'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Ahmad Dani Setyawan',
            'username' => '230101777',
            'email' => 'ahmad_new@kampus.ac.id',
            'password' => 'new_pass_456',
            'is_active' => '0',
        ];

        $response = $this->actingAs($this->admin)->put(route('pengaturan.users.update', $student->id), $payload);
        $response->assertRedirect(route('pengaturan.index', ['tab' => 'akun', 'subtab' => 'user']));
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Ahmad Dani Setyawan', $student->name);
        $this->assertEquals('ahmad_new@kampus.ac.id', $student->email);
        $this->assertFalse($student->isActive());
        $this->assertTrue(Hash::check('new_pass_456', $student->password));
    }

    public function test_deactivated_student_cannot_login(): void
    {
        User::create([
            'name' => 'User Nonaktif',
            'username' => '230101666',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'is_active' => false,
        ]);

        $response = $this->post(route('login.post'), [
            'username' => '230101666',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHas('error');
    }
}

