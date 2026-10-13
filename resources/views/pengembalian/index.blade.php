@extends('layouts.app')

@section('title', 'Pengembalian Alat')
@section('page_title', 'Sirkulasi Pengembalian Alat')
@section('page_subtitle', 'Pemrosesan penerimaan kembali alat laboratorium, verifikasi kondisi fisik, dan kalkulasi denda')

@section('content')
<div class="card mb-4">
    <div class="card-body p-3 p-md-4">
        <!-- Top Toolbar & Pencarian -->
        <div class="row g-3 align-items-center mb-3">
            <div class="col-12 col-md-8">
                <form action="{{ route('pengembalian.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari kode transaksi, nama mahasiswa peminjam..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-12 col-sm-4 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100" title="Cari">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>
                        @if($search)
                            <a href="{{ route('pengembalian.index') }}" class="btn btn-outline-secondary" title="Reset">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">
                    <i class="bi bi-hourglass-split me-1"></i> Total {{ $peminjamanAktif->total() }} Peminjaman Aktif
                </span>
            </div>
        </div>

        <!-- Tabel Transaksi Aktif -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode Transaksi</th>
                        <th>Nama Peminjam</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status Sirkulasi</th>
                        <th>Alat yang Dipinjam</th>
                        <th class="text-end" style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamanAktif as $index => $trx)
                        <tr>
                            <td class="text-muted">{{ $peminjamanAktif->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-bold font-monospace text-primary">{{ $trx->kode_transaksi }}</span>
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
                                @if($trx->isTerlambat())
                                    <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25" style="font-size: 0.7rem;">
                                        <i class="bi bi-alarm-fill me-1"></i>Terlambat {{ $trx->hitungHariTerlambat() }} hari
                                    </span>
                                @else
                                    <small class="text-muted" style="font-size: 0.72rem;">
                                        Sisa {{ \Carbon\Carbon::today()->diffInDays($trx->tanggal_jatuh_tempo, false) }} hari
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($trx->isTerlambat())
                                    <span class="badge bg-danger badge-status">
                                        <i class="bi bi-clock-history me-1"></i>Melewati Tempo
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark badge-status">
                                        <i class="bi bi-hourglass-split me-1"></i>Masa Pinjam
                                    </span>
                                @endif
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0 small text-muted">
                                    @foreach($trx->detailPeminjaman as $detail)
                                        <li>&bull; {{ $detail->alat->nama_alat }} (<strong>{{ $detail->jumlah }} unit</strong>)</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('pengembalian.form', $trx->id) }}" class="btn btn-sm btn-primary shadow-sm text-nowrap">
                                    <i class="bi bi-box-arrow-in-down-left me-1"></i> Proses Kembali
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon text-success">
                                        <i class="bi bi-check2-circle"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Tidak Ada Peminjaman Aktif yang Tertunda</h6>
                                    <p class="text-muted small mb-0">Semua alat laboratorium saat ini telah dikembalikan ke inventaris.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if($peminjamanAktif->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <small class="text-muted">
                    Menampilkan {{ $peminjamanAktif->firstItem() }} - {{ $peminjamanAktif->lastItem() }} dari total {{ $peminjamanAktif->total() }} transaksi aktif
                </small>
                <div>
                    {{ $peminjamanAktif->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
