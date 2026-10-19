<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pengaturan')) {
            Schema::create('pengaturan', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });

            // Initial default settings
            $defaults = [
                ['key' => 'nama_lab', 'value' => 'Laboratorium Teknik Informatika'],
                ['key' => 'alamat', 'value' => 'Jl. Pendidikan No. 1, Kampus Terpadu'],
                ['key' => 'kontak', 'value' => 'lab@kampus.ac.id'],
                ['key' => 'jam_operasional', 'value' => 'Senin - Jumat, 07:00 - 18:00 WIB'],
                ['key' => 'maks_hari_pinjam', 'value' => '7'],
                ['key' => 'denda_per_hari', 'value' => '2000'],
                ['key' => 'toleransi_hari', 'value' => '3'],
                ['key' => 'biaya_alat_rusak', 'value' => '10000'],
                ['key' => 'notif_pengajuan_baru', 'value' => '1'],
                ['key' => 'notif_jatuh_tempo', 'value' => '1'],
                ['key' => 'notif_denda', 'value' => '1'],
                ['key' => 'notif_email_alert', 'value' => '0'],
            ];

            foreach ($defaults as $item) {
                DB::table('pengaturan')->insertOrIgnore([
                    'key' => $item['key'],
                    'value' => $item['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
