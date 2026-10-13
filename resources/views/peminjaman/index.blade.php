@extends('layouts.app')

@section('title', 'Peminjaman Alat')
@section('page_title', 'Sirkulasi Peminjaman Alat')
@section('page_subtitle', 'Pencatatan transaksi peminjaman alat laboratorium dan pemantauan masa pinjam')

@section('content')
<div class="card mb-4">
    <div class="card-body p-3 p-md-4">
        <!-- Top Toolbar: Pencarian, Filter & Tombol Peminjaman Baru -->
        <form action="{{ route('peminjaman.index') }}" method="GET" class="mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4 col-xl-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari kode transaksi, nama, NIM..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="dipinjam" {{ $statusFilter == 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                        <option value="terlambat" {{ $statusFilter == 'terlambat' ? 'selected' : '' }}>Terlambat (Aktif)</option>
                        <option value="dikembalikan" {{ $statusFilter == 'dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                        <option value="dibatalkan" {{ $statusFilter == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 col-xl-2">
                    <input type="date" name="dari_tanggal" class="form-control" title="Dari Tanggal Pinjam" value="{{ $dariTanggal }}">
                </div>
                <div class="col-6 col-md-2 col-xl-2">
                    <input type="date" name="sampai_tanggal" class="form-control" title="Sampai Tanggal Pinjam" value="{{ $sampaiTanggal }}">
                </div>
                <div class="col-6 col-md-1 col-xl-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" title="Terapkan Filter">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                    @if($search || $statusFilter || $dariTanggal || $sampaiTanggal)
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
                <div class="col-12 col-xl-2 text-xl-end mt-2 mt-xl-0">
                    <a href="{{ route('peminjaman.create') }}" class="btn btn-primary w-100 w-xl-auto shadow-sm">
                        <i class="bi bi-plus-circle-fill me-1"></i> Peminjaman Baru
                    </a>
                </div>
            </div>
        </form>

        <!-- Tabel Riwayat Peminjaman -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode Transaksi</th>
                        <th>Peminjam (Mahasiswa)</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Denda & Kerusakan</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjaman as $index => $trx)
                        <tr>
                            <td class="text-muted">{{ $peminjaman->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('peminjaman.show', $trx->id) }}" class="fw-bold text-primary text-decoration-none font-monospace">
                                    {{ $trx->kode_transaksi }}
                                </a>
                                <div class="text-muted small" style="font-size: 0.72rem;">
                                    {{ $trx->detailPeminjaman->sum('jumlah') }} unit alat ({{ $trx->detailPeminjaman->count() }} jenis)
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $trx->mahasiswa->nama }}</div>
                                <small class="text-muted">NIM: {{ $trx->mahasiswa->nim }} &bull; {{ $trx->mahasiswa->program_studi }}</small>
                            </td>
                            <td>
                                <div>{{ $trx->tanggal_pinjam->translatedFormat('d M Y') }}</div>
                            </td>
                            <td>
                                <div>{{ $trx->tanggal_jatuh_tempo->translatedFormat('d M Y') }}</div>
                                @if($trx->status === 'dipinjam')
                                    @if($trx->isTerlambat())
                                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25" style="font-size: 0.7rem;">
                                            <i class="bi bi-alarm me-1"></i>Terlambat {{ $trx->hitungHariTerlambat() }} Hari
                                        </span>
                                    @else
                                        <small class="text-muted" style="font-size: 0.72rem;">
                                            Sisa {{ \Carbon\Carbon::today()->diffInDays($trx->tanggal_jatuh_tempo, false) }} hari lagi
                                        </small>
                                    @endif
                                @elseif($trx->status === 'dikembalikan')
                                    <small class="text-success" style="font-size: 0.72rem;">
                                        Kembali: {{ $trx->tanggal_kembali?->translatedFormat('d M Y') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($trx->status === 'dipinjam')
                                    @if($trx->isTerlambat())
                                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 badge-status">
                                            <i class="bi bi-clock-history me-1"></i>Terlambat
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning border-opacity-25 badge-status">
                                            <i class="bi bi-hourglass-split me-1"></i>Dipinjam
                                        </span>
                                    @endif
                                @elseif($trx->status === 'dikembalikan')
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 badge-status">
                                        <i class="bi bi-check-circle me-1"></i>Dikembalikan
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-25 badge-status">
                                        <i class="bi bi-x-circle me-1"></i>Dibatalkan
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($trx->total_denda > 0 || $trx->total_biaya_perbaikan > 0)
                                    <div>
                                        @if($trx->total_denda > 0)
                                            <span class="badge bg-warning text-dark me-1" style="font-size: 0.7rem;">
                                                Denda: Rp{{ number_format($trx->total_denda, 0, ',', '.') }}
                                            </span>
                                        @endif
                                        @if($trx->total_biaya_perbaikan > 0)
                                            <span class="badge bg-danger" style="font-size: 0.7rem;">
                                                Rusak: Rp{{ number_format($trx->total_biaya_perbaikan, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                    <strong class="text-danger small">Total: Rp{{ number_format($trx->total_denda + $trx->total_biaya_perbaikan, 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted small">Rp 0</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('peminjaman.show', $trx->id) }}" class="btn btn-outline-secondary" title="Lihat Detail Transaksi">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($trx->status === 'dipinjam')
                                        <a href="{{ route('pengembalian.form', $trx->id) }}" class="btn btn-outline-primary" title="Proses Pengembalian">
                                            <i class="bi bi-box-arrow-in-down-left"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-journal-x"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Data Transaksi Tidak Ditemukan</h6>
                                    <p class="text-muted small mb-3">Tidak ada data peminjaman yang sesuai dengan filter atau kata kunci yang ditentukan.</p>
                                    @if($search || $statusFilter || $dariTanggal || $sampaiTanggal)
                                        <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-secondary">Reset Filter</a>
                                    @else
                                        <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-primary">Buat Peminjaman Baru</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if($peminjaman->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <small class="text-muted">
                    Menampilkan {{ $peminjaman->firstItem() }} - {{ $peminjaman->lastItem() }} dari total {{ $peminjaman->total() }} transaksi
                </small>
                <div>
                    {{ $peminjaman->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
