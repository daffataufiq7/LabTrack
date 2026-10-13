<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    /**
     * Parameter Aturan Bisnis (Dapat disesuaikan dengan mudah jika regulasi laboratorium berubah)
     */
    public const TARIF_DENDA_PER_HARI = 2000;
    public const HARI_MASA_TENGGANG = 3;
    public const BIAYA_PERBAIKAN_PER_UNIT = 10000;

    protected $fillable = [
        'kode_transaksi',
        'mahasiswa_id',
        'tanggal_pinjam',
        'tanggal_jatuh_tempo',
        'tanggal_kembali',
        'status',
        'total_denda',
        'total_biaya_perbaikan',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_kembali' => 'date',
        'total_denda' => 'decimal:2',
        'total_biaya_perbaikan' => 'decimal:2',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'peminjaman_id');
    }

    /**
     * Mengecek apakah peminjaman ini berstatus aktif terlambat (berdasarkan tanggal jatuh tempo).
     */
    public function isTerlambat(): bool
    {
        if ($this->status === 'dipinjam') {
            $today = Carbon::today();
            return $today->gt($this->tanggal_jatuh_tempo);
        }

        if ($this->status === 'dikembalikan' && $this->tanggal_kembali) {
            return $this->tanggal_kembali->gt($this->tanggal_jatuh_tempo);
        }

        return false;
    }

    /**
     * Menghitung total hari selisih keterlambatan dari tanggal jatuh tempo.
     * @param Carbon|string|null $tanggalRef Jika null, memakai tanggal_kembali (bila sudah kembali) atau hari ini (bila masih dipinjam)
     */
    public function hitungHariTerlambat($tanggalRef = null): int
    {
        if ($tanggalRef) {
            $ref = is_string($tanggalRef) ? Carbon::parse($tanggalRef)->startOfDay() : $tanggalRef->copy()->startOfDay();
        } elseif ($this->status === 'dikembalikan' && $this->tanggal_kembali) {
            $ref = $this->tanggal_kembali->copy()->startOfDay();
        } else {
            $ref = Carbon::today();
        }

        $tempo = $this->tanggal_jatuh_tempo->copy()->startOfDay();

        if ($ref->gt($tempo)) {
            return (int) $tempo->diffInDays($ref);
        }

        return 0;
    }

    /**
     * Menghitung hari terlambat yang dikenai denda (setelah dikurangi masa tenggang 3 hari).
     */
    public function hitungHariKenaDenda($tanggalRef = null): int
    {
        $selisihHari = $this->hitungHariTerlambat($tanggalRef);
        return max(0, $selisihHari - self::HARI_MASA_TENGGANG);
    }

    /**
     * Menghitung estimasi/kalkulasi denda keterlambatan: hari dikenai × Rp2.000.
     */
    public function hitungDenda($tanggalRef = null): float
    {
        $hariKenaDenda = $this->hitungHariKenaDenda($tanggalRef);
        return (float) ($hariKenaDenda * self::TARIF_DENDA_PER_HARI);
    }

    /**
     * Generate kode transaksi unik otomatis (misal: TRX-20261014-0001).
     */
    public static function generateKodeTransaksi(): string
    {
        $prefix = 'TRX-' . date('Ymd') . '-';
        $last = self::where('kode_transaksi', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->kode_transaksi, strlen($prefix));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
