@extends('layouts.app')

@section('title', 'Katalog Ketersediaan Alat Laboratorium')
@section('page_title', 'Katalog Inventaris & Ketersediaan Alat')
@section('page_subtitle', 'Pantau stok fisik alat laboratorium yang siap dipinjam untuk praktikum secara real-time')

@section('content')
<!-- Filter & Pencarian -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('mahasiswa.portal.katalog') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama alat atau kode inventaris..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-6 col-md-4">
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori Lab</option>
                    @foreach($daftarKategori as $kat)
                        <option value="{{ $kat }}" {{ $kategori == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                @if($search || $kategori)
                    <a href="{{ route('mahasiswa.portal.katalog') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Grid Kartu Alat -->
<div class="row g-3">
    @forelse($alat as $item)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100 hover-card">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                                {{ $item->kode_alat }}
                            </span>
                            @if($item->stok_tersedia > 0)
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i> Ready {{ $item->stok_tersedia }} Unit
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                    <i class="bi bi-x-circle me-1"></i> Stok Habis
                                </span>
                            @endif
                        </div>

                        <h6 class="fw-bold text-dark mb-1">{{ $item->nama_alat }}</h6>
                        <span class="badge bg-secondary-subtle text-secondary mb-3" style="font-size: 0.72rem;">
                            {{ $item->kategori }}
                        </span>

                        <div class="bg-light p-2 rounded small mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Aset:</span>
                                <strong>{{ $item->stok_total }} Unit</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Tersedia Siap Pinjam:</span>
                                <strong class="{{ $item->stok_tersedia > 0 ? 'text-success' : 'text-danger' }}">{{ $item->stok_tersedia }} Unit</strong>
                            </div>
                            @if($item->stok_rusak > 0)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Dalam Perbaikan:</span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis">{{ $item->stok_rusak }} Unit</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        @if($item->stok_tersedia > 0)
                            <a href="{{ route('mahasiswa.portal.ajukan', ['alat_id' => $item->id]) }}" class="btn btn-sm btn-primary w-100 shadow-sm">
                                <i class="bi bi-plus-circle me-1"></i> Ajukan Peminjaman
                            </a>
                        @else
                            <button class="btn btn-sm btn-light text-muted w-100" disabled>
                                <i class="bi bi-slash-circle me-1"></i> Tidak Tersedia Saat Ini
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5 bg-white rounded-3 border">
                <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
                <h6 class="fw-bold text-dark">Alat Laboratorium Tidak Ditemukan</h6>
                <p class="text-muted small mb-0">Coba gunakan kata kunci pencarian atau kategori lain.</p>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $alat->links() }}
</div>
@endsection
