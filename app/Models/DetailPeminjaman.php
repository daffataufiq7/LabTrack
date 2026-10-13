<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPeminjaman extends Model
{
    use HasFactory;

    protected $table = 'detail_peminjaman';

    protected $fillable = [
        'peminjaman_id',
        'alat_id',
        'jumlah',
        'kode_unit_dipinjam',
        'kondisi_kembali',
        'biaya_perbaikan',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'biaya_perbaikan' => 'decimal:2',
    ];

    /**
     * Mengambil daftar kode unit sebagai array.
     */
    public function getUnitListAttribute(): array
    {
        if (empty($this->kode_unit_dipinjam)) {
            return [];
        }
        return array_map('trim', explode(',', $this->kode_unit_dipinjam));
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }
}
