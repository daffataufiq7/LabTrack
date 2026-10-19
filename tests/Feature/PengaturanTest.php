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
}
