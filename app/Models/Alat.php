<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    use HasFactory;

    protected $table = 'alat';

    protected $fillable = [
        'kode_alat',
        'nama_alat',
        'kategori',
        'stok_total',
        'stok_tersedia',
        'kondisi',
    ];

    protected $casts = [
        'stok_total' => 'integer',
        'stok_tersedia' => 'integer',
    ];

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'alat_id');
    }

    /**
     * Cek apakah alat pernah tercatat dalam transaksi.
     */
    public function hasTransactions(): bool
    {
        return $this->detailPeminjaman()->exists();
    }

    /**
     * Cek apakah alat saat ini sedang aktif dipinjam.
     */
    public function isCurrentlyBorrowed(): bool
    {
        return $this->detailPeminjaman()
            ->whereHas('peminjaman', function ($q) {
                $q->where('status', 'dipinjam');
            })->exists();
    }
}
