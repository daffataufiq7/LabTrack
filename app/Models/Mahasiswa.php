<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'nama',
        'program_studi',
        'no_hp',
    ];

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'mahasiswa_id');
    }

    /**
     * Cek apakah mahasiswa memiliki riwayat transaksi peminjaman.
     */
    public function hasPeminjaman(): bool
    {
        return $this->peminjaman()->exists();
    }
}
