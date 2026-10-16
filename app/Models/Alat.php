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
        'gambar',
        'stok_total',
        'stok_tersedia',
        'stok_rusak',
        'kondisi',
    ];

    protected $casts = [
        'stok_total' => 'integer',
        'stok_tersedia' => 'integer',
        'stok_rusak' => 'integer',
    ];

    protected static function booted(): void
    {
        static::created(function (Alat $alat) {
            $alat->generateUnits();
        });

        static::updated(function (Alat $alat) {
            if ($alat->wasChanged('stok_total') && $alat->stok_total > $alat->units()->count()) {
                $alat->generateUnits();
            }
        });
    }

    public function units(): HasMany
    {
        return $this->hasMany(AlatUnit::class, 'alat_id');
    }

    public function unitTersedia(): HasMany
    {
        return $this->hasMany(AlatUnit::class, 'alat_id')->where('status', 'tersedia');
    }

    public function unitRusak(): HasMany
    {
        return $this->hasMany(AlatUnit::class, 'alat_id')->where('status', 'rusak');
    }

    public function unitDipinjam(): HasMany
    {
        return $this->hasMany(AlatUnit::class, 'alat_id')->where('status', 'dipinjam');
    }

    /**
     * Otomatis menerbitkan kode unit fisik per barang.
     */
    public function generateUnits(): void
    {
        $existingCount = $this->units()->count();
        if ($existingCount >= $this->stok_total) {
            return;
        }

        $rusakTarget = $this->stok_rusak ?? 0;
        $tersediaTarget = $this->stok_tersedia ?? ($this->stok_total - $rusakTarget);
        $dipinjamTarget = max(0, $this->stok_total - ($tersediaTarget + $rusakTarget));

        for ($i = $existingCount + 1; $i <= $this->stok_total; $i++) {
            $nomorPadded = str_pad($i, 2, '0', STR_PAD_LEFT);
            $kodeUnit = "{$this->kode_alat}-U{$nomorPadded}";

            $status = 'tersedia';
            $kondisi = 'Baik';

            $currentRusak = $this->units()->where('status', 'rusak')->count();
            $currentDipinjam = $this->units()->where('status', 'dipinjam')->count();

            if ($currentRusak < $rusakTarget) {
                $status = 'rusak';
                $kondisi = 'Rusak Ringan/Karantina';
            } elseif ($currentDipinjam < $dipinjamTarget) {
                $status = 'dipinjam';
            }

            AlatUnit::firstOrCreate(
                ['kode_unit' => $kodeUnit],
                [
                    'alat_id' => $this->id,
                    'nomor_seri' => 'SN-' . strtoupper(substr(md5($kodeUnit), 0, 8)),
                    'status' => $status,
                    'kondisi' => $kondisi,
                ]
            );
        }
    }

    /**
     * Sinkronisasi angka stok induk berdasarkan tabel alat_unit.
     */
    public function sinkronkanStok(): void
    {
        $total = $this->units()->count();
        $tersedia = $this->units()->where('status', 'tersedia')->count();
        $rusak = $this->units()->where('status', 'rusak')->count();

        $this->updateQuietly([
            'stok_total' => $total,
            'stok_tersedia' => $tersedia,
            'stok_rusak' => $rusak,
        ]);
    }

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

    /**
     * Mendapatkan URL gambar publik alat atau fallback gambar default.
     */
    public function getGambarUrlAttribute(): string
    {
        if ($this->gambar && file_exists(public_path($this->gambar))) {
            return asset($this->gambar);
        }

        // Cek fallback gambar spesifik berdasarkan kode_alat
        $kodeClean = strtolower(str_replace('-', '_', $this->kode_alat));
        $possiblePaths = [
            "images/alat/{$kodeClean}.jpg",
            "images/alat/{$kodeClean}.png",
            "images/alat/{$kodeClean}.webp",
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }

        return asset('images/alat/default_instrument.jpg');
    }
}
