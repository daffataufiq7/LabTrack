@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi')
@section('page_title', 'Laporan Sirkulasi & Rekapitulasi Keuangan')
@section('page_subtitle', 'Laporan komprehensif seluruh transaksi peminjaman, keterlambatan, denda, dan biaya perbaikan alat')

@section('content')
<!-- Filter Toolbar & Ekspor -->
<div class="card mb-4">
    <div class="card-body p-3 p-md-4">
        <form action="{{ route('laporan.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small text-muted">Dari Tanggal Pinjam</label>
                    <input type="date" name="dari_tanggal" class="form-control" value="{{ $dariTanggal }}">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small text-muted">Sampai Tanggal Pinjam</label>
                    <input type="date" name="sampai_tanggal" class="form-control" value="{{ $sampaiTanggal }}">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label small text-muted">Filter Status Sirkulasi</label>
                    <select name="status" class="form-select">
                        <option value="semua" {{ $statusFilter == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="dipinjam" {{ $statusFilter == 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                        <option value="terlambat" {{ $statusFilter == 'terlambat' ? 'selected' : '' }}>Terlambat (Aktif / Melewati Tempo)</option>
                        <option value="dikembalikan" {{ $statusFilter == 'dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                        <option value="dibatalkan" {{ $statusFilter == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    @if($dariTanggal || $sampaiTanggal || $statusFilter !== 'semua')
                        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                    <a href="{{ route('laporan.print', request()->query()) }}" target="_blank" class="btn btn-outline-dark" title="Cetak / PDF">
                        <i class="bi bi-printer-fill me-1"></i> Cetak
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Row Kartu Ringkasan Laporan -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="card p-3 stat-card h-100 text-center">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Total Transaksi</span>
            <h4 class="fw-bold text-dark mt-1 mb-0">{{ number_format($totalTransaksi) }}</h4>
            <small class="text-muted" style="font-size: 0.7rem;">Sesuai filter</small>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card p-3 stat-card h-100 text-center">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Aktif Dipinjam</span>
            <h4 class="fw-bold text-primary mt-1 mb-0">{{ number_format($totalDipinjam) }}</h4>
            <small class="text-muted" style="font-size: 0.7rem;">Sedang dipinjam</small>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card p-3 stat-card h-100 text-center">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Selesai Kembali</span>
            <h4 class="fw-bold text-success mt-1 mb-0">{{ number_format($totalDikembalikan) }}</h4>
            <small class="text-muted" style="font-size: 0.7rem;">Sudah kembali</small>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card p-3 stat-card h-100 text-center">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Total Terlambat</span>
            <h4 class="fw-bold text-danger mt-1 mb-0">{{ number_format($totalTerlambat) }}</h4>
            <small class="text-muted" style="font-size: 0.7rem;">Melewati tempo</small>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-2">
        <div class="card p-3 stat-card h-100 text-center border-warning">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Total Denda</span>
            <h5 class="fw-bold text-warning-emphasis mt-1 mb-0">Rp {{ number_format($totalDenda, 0, ',', '.') }}</h5>
            <small class="text-muted" style="font-size: 0.7rem;">Terkumpul</small>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-2">
        <div class="card p-3 stat-card h-100 text-center border-danger">
            <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Biaya Kerusakan</span>
            <h5 class="fw-bold text-danger mt-1 mb-0">Rp {{ number_format($totalBiayaPerbaikan, 0, ',', '.') }}</h5>
            <small class="text-muted" style="font-size: 0.7rem;">Unit alat rusak</small>
        </div>
    </div>
</div>

<!-- Tabel Laporan Rinci -->
<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode & Peminjam</th>
                        <th>Periode Pinjam</th>
                        <th>Alat yang Dipinjam</th>
                        <th>Status</th>
                        <th class="text-end">Denda</th>
                        <th class="text-end">Perbaikan</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-center" style="width: 80px;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $index => $trx)
                        <tr>
                            <td class="text-muted">{{ $laporan->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('peminjaman.show', $trx->id) }}" class="fw-bold text-primary text-decoration-none font-monospace">
                                    {{ $trx->kode_transaksi }}
                                </a>
                                <div class="fw-semibold text-dark">{{ $trx->mahasiswa->nama }}</div>
                                <small class="text-muted">NIM: {{ $trx->mahasiswa->nim }} &bull; {{ $trx->mahasiswa->program_studi }}</small>
                            </td>
                            <td>
                                <div class="small">Pinjam: <strong>{{ $trx->tanggal_pinjam->translatedFormat('d/m/Y') }}</strong></div>
                                <div class="small">Tempo: <strong class="{{ $trx->isTerlambat() ? 'text-danger' : '' }}">{{ $trx->tanggal_jatuh_tempo->translatedFormat('d/m/Y') }}</strong></div>
                                <div class="small text-muted">Kembali: {{ $trx->tanggal_kembali ? $trx->tanggal_kembali->translatedFormat('d/m/Y') : '-' }}</div>
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0 small text-muted">
                                    @foreach($trx->detailPeminjaman as $d)
                                        <li>&bull; {{ $d->alat->nama_alat }} (<strong>{{ $d->jumlah }} unit</strong>)</li>
                                    @endforeach
                                </ul>
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
                                @if($trx->total_denda > 0)
                                    <span class="fw-semibold text-danger">Rp {{ number_format($trx->total_denda, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted small">Rp 0</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($trx->total_biaya_perbaikan > 0)
                                    <span class="fw-semibold text-danger">Rp {{ number_format($trx->total_biaya_perbaikan, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted small">Rp 0</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="{{ ($trx->total_denda + $trx->total_biaya_perbaikan) > 0 ? 'text-danger' : 'text-dark' }}">
                                    Rp {{ number_format($trx->total_denda + $trx->total_biaya_perbaikan, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('peminjaman.show', $trx->id) }}" class="btn btn-sm btn-light border" title="Lihat Detail Transaksi">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-file-earmark-x"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Data Laporan Tidak Ditemukan</h6>
                                    <p class="text-muted small mb-0">Tidak ada transaksi yang sesuai dengan parameter filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if($laporan->hasPages())
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <small class="text-muted">
                    Menampilkan {{ $laporan->firstItem() }} - {{ $laporan->lastItem() }} dari total {{ $laporan->total() }} data laporan
                </small>
                <div>
                    {{ $laporan->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
