@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Manajemen Laboratorium')
@section('page_subtitle', 'Ringkasan operasional peminjaman alat, logistik, dan statistik sirkulasi')

@section('content')
<!-- Banner Peringatan Keterlambatan Aktif (Jika ada) -->
@if($peminjamanTerlambat > 0)
    <div class="alert alert-danger d-flex align-items-center justify-content-between p-3 rounded-4 shadow-sm mb-4 border-danger border-opacity-25" role="alert">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-danger bg-opacity-20 text-danger rounded-circle">
                <i class="bi bi-alarm-fill fs-4"></i>
            </div>
            <div>
                <h6 class="alert-heading mb-1 fw-bold text-danger">Perhatian: Ada {{ $peminjamanTerlambat }} Transaksi Peminjaman Telah Melewati Jatuh Tempo!</h6>
                <p class="mb-0 small text-danger-emphasis">
                    Terdapat peminjam yang belum mengembalikan alat setelah tanggal jatuh tempo. Harap segera tindak lanjuti sebelum denda bertambah.
                </p>
            </div>
        </div>
        <a href="{{ route('peminjaman.index', ['status' => 'terlambat']) }}" class="btn btn-sm btn-danger shadow-sm text-nowrap">
            Lihat Transaksi Terlambat <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
@endif

<!-- Row Kartu Statistik Utama -->
<div class="row g-3 mb-4">
    <!-- Mahasiswa Terdaftar -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Mahasiswa Terdaftar</span>
                    <h3 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($totalMahasiswa) }}</h3>
                    <small class="text-muted" style="font-size: 0.75rem;">Data peminjam aktif</small>
                </div>
                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Jenis Alat Lab -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Koleksi Alat Lab</span>
                    <h3 class="fw-bold mb-0 mt-1 text-dark">{{ number_format($totalJenisAlat) }} <span class="fs-6 fw-normal text-muted">jenis</span></h3>
                    <small class="text-success fw-medium" style="font-size: 0.75rem;">
                        <i class="bi bi-check-circle"></i> {{ $totalStokTersedia }} / {{ $totalStokAlat }} unit tersedia
                    </small>
                </div>
                <div class="icon-box bg-info bg-opacity-10 text-info">
                    <i class="bi bi-tools fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Peminjaman Aktif -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Peminjaman Aktif</span>
                    <h3 class="fw-bold mb-0 mt-1 text-primary">{{ number_format($peminjamanAktif) }} <span class="fs-6 fw-normal text-muted">transaksi</span></h3>
                    <small class="text-muted" style="font-size: 0.75rem;">Sedang digunakan</small>
                </div>
                <div class="icon-box bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Terlambat Aktif -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card h-100 p-3 {{ $peminjamanTerlambat > 0 ? 'border-danger border-opacity-50' : '' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Melewati Jatuh Tempo</span>
                    <h3 class="fw-bold mb-0 mt-1 {{ $peminjamanTerlambat > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($peminjamanTerlambat) }} <span class="fs-6 fw-normal text-muted">transaksi</span>
                    </h3>
                    <small class="{{ $peminjamanTerlambat > 0 ? 'text-danger' : 'text-muted' }}" style="font-size: 0.75rem;">
                        {{ $peminjamanTerlambat > 0 ? 'Memerlukan tindakan' : 'Sirkulasi tepat waktu' }}
                    </small>
                </div>
                <div class="icon-box {{ $peminjamanTerlambat > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }}">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row Finansial / Denda & Biaya Perbaikan -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Denda Terkumpul</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($totalDendaTerkumpul, 0, ',', '.') }}</h4>
                    <small class="text-muted">Dari keterlambatan melewati masa tenggang 3 hari (Rp2.000/hari)</small>
                </div>
                <div class="icon-box bg-warning bg-opacity-15 text-warning rounded-circle">
                    <i class="bi bi-cash-coin fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Biaya Perbaikan Terkumpul</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($totalBiayaPerbaikan, 0, ',', '.') }}</h4>
                    <small class="text-muted">Biaya pemulihan alat rusak (Rp10.000/unit)</small>
                </div>
                <div class="icon-box bg-danger bg-opacity-15 text-danger rounded-circle">
                    <i class="bi bi-tools fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row Konten Utama Dashboard -->
<div class="row g-4">
    <!-- Kolom Kiri: Transaksi Terbaru -->
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Aktivitas Transaksi Terbaru</h6>
                    <small class="text-muted">Daftar sirkulasi peminjaman laboratorium terkini</small>
                </div>
                <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-primary">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kode & Peminjam</th>
                                <th>Item Dipinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksiTerbaru as $trx)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $trx->kode_transaksi }}</div>
                                        <div class="small text-dark">{{ $trx->mahasiswa->nama }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">NIM: {{ $trx->mahasiswa->nim }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-box-seam me-1"></i>{{ $trx->detailPeminjaman->sum('jumlah') }} unit ({{ $trx->detailPeminjaman->count() }} jenis)
                                        </span>
                                    </td>
                                    <td>
                                        <div>{{ $trx->tanggal_jatuh_tempo->translatedFormat('d M Y') }}</div>
                                        @if($trx->status === 'dipinjam')
                                            @if($trx->isTerlambat())
                                                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25" style="font-size: 0.7rem;">
                                                    <i class="bi bi-exclamation-circle me-1"></i>Terlambat {{ $trx->hitungHariTerlambat() }} hari
                                                </span>
                                            @else
                                                <small class="text-muted" style="font-size: 0.72rem;">
                                                    Sisa {{ \Carbon\Carbon::today()->diffInDays($trx->tanggal_jatuh_tempo, false) }} hari
                                                </small>
                                            @endif
                                        @elseif($trx->status === 'dikembalikan')
                                            <small class="text-success" style="font-size: 0.72rem;">
                                                <i class="bi bi-check-circle"></i> Kembali {{ $trx->tanggal_kembali?->translatedFormat('d M Y') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($trx->status === 'dipinjam')
                                            @if($trx->isTerlambat())
                                                <span class="badge bg-danger badge-status">Terlambat</span>
                                            @else
                                                <span class="badge bg-warning text-dark badge-status">Dipinjam</span>
                                            @endif
                                        @elseif($trx->status === 'dikembalikan')
                                            <span class="badge bg-success badge-status">Dikembalikan</span>
                                        @else
                                            <span class="badge bg-secondary badge-status">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('peminjaman.show', $trx->id) }}" class="btn btn-sm btn-light border" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($trx->status === 'dipinjam')
                                            <a href="{{ route('pengembalian.form', $trx->id) }}" class="btn btn-sm btn-primary ms-1" title="Proses Pengembalian">
                                                <i class="bi bi-box-arrow-in-down-left"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Belum ada transaksi peminjaman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Alat Stok Kritis & Shortcut -->
    <div class="col-12 col-lg-4">
        <!-- Card Monitoring Stok Kritis -->
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Stok Menipis (≤ 2 Unit)
                </h6>
                <a href="{{ route('alat.index') }}" class="btn btn-sm btn-link p-0 text-decoration-none small">Semua Alat</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($alatStokKritis as $alat)
                        <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                            <div>
                                <div class="fw-semibold text-dark mb-0" style="font-size: 0.88rem;">{{ $alat->nama_alat }}</div>
                                <small class="text-muted">{{ $alat->kode_alat }} &bull; {{ $alat->kategori }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $alat->stok_tersedia == 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill px-2 py-1">
                                    {{ $alat->stok_tersedia }} / {{ $alat->stok_total }} unit
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item py-4 text-center text-muted small">
                            <i class="bi bi-check-circle-fill text-success fs-5 d-block mb-1"></i>
                            Semua persediaan alat dalam kondisi aman.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Card Petunjuk Aturan Bisnis UTS -->
        <div class="card bg-light border-0">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-info-circle-fill text-primary"></i> Aturan Bisnis Laboratorium
                </h6>
                <ul class="small text-secondary mb-0 ps-3" style="line-height: 1.6;">
                    <li><strong>Masa Tenggang:</strong> 3 hari setelah tanggal jatuh tempo tanpa denda.</li>
                    <li><strong>Denda Terlambat:</strong> Dihitung pada hari ke-4 keterlambatan sebesar <strong>Rp2.000 / hari</strong>.</li>
                    <li><strong>Biaya Kerusakan:</strong> Dikenakan <strong>Rp10.000 / unit</strong> alat yang dilaporkan rusak saat kembali.</li>
                    <li><strong>Restorasi Stok:</strong> Stok tersedia hanya dipulihkan untuk alat yang kembali dalam kondisi layak pakai.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
