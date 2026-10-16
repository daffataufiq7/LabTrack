@extends('layouts.app')

@section('title', 'Pengembalian Alat')

@section('content')
<!-- Page Header (Screen 6) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Pengembalian Alat</h4>
        <p class="text-muted small mb-0">Proses pengembalian alat laboratorium.</p>
    </div>
</div>

<!-- Table Card (Screen 6) -->
<div class="card p-3 p-md-4">
    <!-- Filter Search Bar with Button Cari -->
    <form action="{{ route('pengembalian.index') }}" method="GET" class="row g-2 align-items-center mb-4">
        <div class="col-12 col-md-5">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
                <input type="text" name="search" class="form-control ps-5 pe-3 py-2" placeholder="Cari nama mahasiswa atau alat..." value="{{ $search ?? '' }}">
            </div>
        </div>
        <div class="col-8 col-md-4">
            <select name="status_kondisi" class="form-select py-2">
                <option value="">-- Pilih Tanggal / Status --</option>
                <option value="tepat_waktu">Tepat Waktu (Normal)</option>
                <option value="terlambat">Melewati Jatuh Tempo</option>
            </select>
        </div>
        <div class="col-4 col-md-3 d-flex gap-2">
            <button type="submit" class="btn-primary-dark w-100 justify-content-center py-2">
                <i class="bi bi-search"></i>
                <span>Cari</span>
            </button>
            @if(request('search') || request('status_kondisi'))
                <a href="{{ route('pengembalian.index') }}" class="btn btn-outline-secondary py-2 px-3" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </div>
    </form>

    <!-- Table Responsive (Screen 6) -->
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Nama Mahasiswa</th>
                    <th>Nama Alat</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjamanAktif as $index => $item)
                    @php
                        $isTerlambat = \Carbon\Carbon::today()->gt(\Carbon\Carbon::parse($item->tanggal_jatuh_tempo));
                        $firstAlat = $item->detailPeminjaman->first()->alat ?? null;
                    @endphp
                    <tr>
                        <td>{{ $peminjamanAktif->firstItem() + $index }}</td>
                        <td class="text-secondary text-nowrap">
                            {{ \Carbon\Carbon::parse($item->tanggal_pinjam)->isoFormat('DD MMM Y') }}
                        </td>
                        <td class="text-secondary text-nowrap">
                            {{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->isoFormat('DD MMM Y') }}
                        </td>
                        <td>
                            <span class="fw-semibold text-dark d-block">{{ $item->mahasiswa->nama ?? 'Mahasiswa' }}</span>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $item->mahasiswa->nim ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="text-dark">{{ $firstAlat->nama_alat ?? 'Alat Lab' }}</span>
                            @if($item->detailPeminjaman->count() > 1)
                                <small class="text-muted d-block" style="font-size: 0.72rem;">+ {{ $item->detailPeminjaman->count() - 1 }} alat lainnya</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($isTerlambat)
                                <span class="badge-pill-status badge-pill-danger">Terlambat</span>
                            @else
                                <span class="badge-pill-status badge-pill-success">Normal</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('pengembalian.form', $item->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 0.78rem;">
                                <i class="bi bi-box-arrow-in-down-left"></i>
                                <span>Kembalikan</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted small">
                            Tidak ada transaksi peminjaman aktif yang menunggu pengembalian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-4 pt-2 border-top">
        <span class="small text-muted" style="font-size: 0.8rem;">
            Menampilkan {{ $peminjamanAktif->firstItem() ?? 0 }} - {{ $peminjamanAktif->lastItem() ?? 0 }} dari {{ $peminjamanAktif->total() }} transaksi aktif
        </span>
        <div>
            {{ $peminjamanAktif->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
