<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlatUnit extends Model
{
    use HasFactory;

    protected $table = 'alat_unit';

    protected $fillable = [
        'alat_id',
        'kode_unit',
        'nomor_seri',
        'status',
        'kondisi',
        'peminjaman_id',
        'catatan',
    ];

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    public function isTersedia(): bool
    {
        return $this->status === 'tersedia';
    }

    public function isDipinjam(): bool
    {
        return $this->status === 'dipinjam';
    }

    public function isRusak(): bool
    {
        return $this->status === 'rusak';
    }
}
