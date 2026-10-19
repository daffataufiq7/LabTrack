<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Default settings mapping
     */
    public static function defaults(): array
    {
        return [
            'nama_lab' => 'Laboratorium Teknik Informatika',
            'alamat' => 'Jl. Pendidikan No. 1, Kampus Terpadu',
            'kontak' => 'lab@kampus.ac.id',
            'jam_operasional' => 'Senin - Jumat, 07:00 - 18:00 WIB',
            'maks_hari_pinjam' => '7',
            'denda_per_hari' => '2000',
            'toleransi_hari' => '3',
            'biaya_alat_rusak' => '10000',
            'notif_pengajuan_baru' => '1',
            'notif_jatuh_tempo' => '1',
            'notif_denda' => '1',
            'notif_email_alert' => '0',
        ];
    }

    /**
     * Get a setting by key with fallback default
     */
    public static function get(string $key, $default = null)
    {
        $defaults = self::defaults();
        $fallback = $default ?? ($defaults[$key] ?? null);

        if (!Schema::hasTable('pengaturan')) {
            return $fallback;
        }

        $row = self::where('key', $key)->first();
        return $row ? $row->value : $fallback;
    }

    /**
     * Set a setting by key
     */
    public static function set(string $key, $value): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }

    /**
     * Get all settings merged with defaults
     */
    public static function getAll(): array
    {
        $settings = self::defaults();

        if (Schema::hasTable('pengaturan')) {
            $rows = self::all();
            foreach ($rows as $row) {
                $settings[$row->key] = $row->value;
            }
        }

        return $settings;
    }
}
