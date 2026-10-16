@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<!-- Page Header (Screen 8) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Laporan</h4>
        <p class="text-muted small mb-0">Lihat rekap dan statistik peminjaman alat laboratorium.</p>
    </div>
</div>

<!-- Tabs Pill Navigation (Screen 8) -->
<div class="d-flex align-items-center gap-2 mb-4 overflow-auto pb-1">
    <a href="{{ route('laporan.index', ['tab' => 'peminjaman']) }}" class="btn btn-sm {{ $tabActive === 'peminjaman' ? 'btn-primary-dark' : 'btn-outline-secondary bg-white' }} px-3 py-2 rounded-3 text-nowrap">
        Peminjaman
    </a>
    <a href="{{ route('denda.index') }}" class="btn btn-sm btn-outline-secondary bg-white px-3 py-2 rounded-3 text-nowrap">
        Denda
    </a>
    <a href="{{ route('alat.index') }}" class="btn btn-sm btn-outline-secondary bg-white px-3 py-2 rounded-3 text-nowrap">
        Alat
    </a>
    <a href="{{ route('mahasiswa.index') }}" class="btn btn-sm btn-outline-secondary bg-white px-3 py-2 rounded-3 text-nowrap">
        Mahasiswa
    </a>
</div>

<!-- Date Filter & Cetak Laporan Button (Screen 8) -->
<div class="card p-3 mb-4">
    <form action="{{ route('laporan.index') }}" method="GET" class="row g-2 align-items-center">
        <input type="hidden" name="tab" value="{{ $tabActive }}">
        <div class="col-12 col-md-4">
            <input type="date" name="dari_tanggal" class="form-control py-2" value="{{ $dariTanggal }}" placeholder="Dari Tanggal">
        </div>
        <div class="col-12 col-md-4">
            <input type="date" name="sampai_tanggal" class="form-control py-2" value="{{ $sampaiTanggal }}" placeholder="Sampai Tanggal">
        </div>
        <div class="col-12 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-outline-secondary w-50 py-2">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="{{ route('laporan.print', ['dari_tanggal' => $dariTanggal, 'sampai_tanggal' => $sampaiTanggal]) }}" target="_blank" class="btn-primary-dark w-50 justify-content-center py-2 text-nowrap">
                <i class="bi bi-printer"></i> Cetak Laporan
            </a>
        </div>
    </form>
</div>

<!-- 3 Summary Stat Cards (Screen 8) -->
<div class="row g-3 mb-4">
    <!-- 1. Total Peminjaman -->
    <div class="col-12 col-md-4">
        <div class="card p-3 p-md-4 h-100">
            <span class="text-muted small d-block mb-1">Total Peminjaman</span>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalTransaksi) }}</h3>
            <small class="text-success mt-2 d-block" style="font-size: 0.75rem;">
                <i class="bi bi-arrow-up-short"></i> 18% dari periode lalu
            </small>
        </div>
    </div>

    <!-- 2. Total Pengembalian -->
    <div class="col-12 col-md-4">
        <div class="card p-3 p-md-4 h-100">
            <span class="text-muted small d-block mb-1">Total Pengembalian</span>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalDikembalikan) }}</h3>
            <small class="text-success mt-2 d-block" style="font-size: 0.75rem;">
                <i class="bi bi-check-circle"></i> Selesai dikembalikan
            </small>
        </div>
    </div>

    <!-- 3. Total Pendapatan / Denda -->
    <div class="col-12 col-md-4">
        <div class="card p-3 p-md-4 h-100">
            <span class="text-muted small d-block mb-1">Total Pendapatan</span>
            <h3 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
            <small class="text-success mt-2 d-block" style="font-size: 0.75rem;">
                <i class="bi bi-arrow-up-short"></i> 30% dari denda & perbaikan
            </small>
        </div>
    </div>
</div>

<!-- Peminjaman per Kategori Alat (Horizontal Bars - Screen 8) -->
<div class="card p-3 p-md-4 mb-4">
    <h6 class="fw-bold text-dark mb-4">Peminjaman per Kategori Alat</h6>
    <div class="d-flex flex-column gap-3">
        @php
            $colors = ['#1e40af', '#0284c7', '#38bdf8', '#60a5fa', '#93c5fd'];
        @endphp
        @forelse($kategoriDistribution as $idx => $k)
            @php
                $pct = round(($k->total / $maxKategoriCount) * 100);
                $barColor = $colors[$idx % count($colors)];
            @endphp
            <div>
                <div class="d-flex justify-content-between align-items-center mb-1 small">
                    <span class="text-dark fw-medium" style="min-width: 180px;">{{ $k->kategori }}</span>
                    <div class="progress flex-grow-1 mx-3" style="height: 10px; border-radius: 9999px; background-color: #f1f5f9;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background-color: {{ $barColor }}; border-radius: 9999px;"></div>
                    </div>
                    <span class="fw-bold text-dark" style="min-width: 30px; text-align: right;">{{ $k->total }}</span>
                </div>
            </div>
        @empty
            <div class="text-muted small text-center py-3">Belum ada data distribusi kategori.</div>
        @endforelse
    </div>
</div>

<!-- Tabel Rincian Laporan -->
<div class="card p-3 p-md-4">
    <h6 class="fw-bold text-dark mb-3">Daftar Transaksi Terdata</h6>
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode</th>
                    <th>Mahasiswa</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Denda</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $index => $row)
                    <tr>
                        <td>{{ $laporan->firstItem() + $index }}</td>
                        <td class="font-monospace text-dark">{{ $row->kode_transaksi }}</td>
                        <td class="fw-medium text-dark">{{ $row->mahasiswa->nama ?? '-' }}</td>
                        <td class="text-secondary">{{ \Carbon\Carbon::parse($row->tanggal_pinjam)->isoFormat('DD/MM/YYYY') }}</td>
                        <td class="text-secondary">{{ $row->tanggal_kembali ? \Carbon\Carbon::parse($row->tanggal_kembali)->isoFormat('DD/MM/YYYY') : '-' }}</td>
                        <td class="text-center">
                            @if($row->status === 'dikembalikan')
                                <span class="badge-pill-status badge-pill-success">Dikembalikan</span>
                            @else
                                <span class="badge-pill-status badge-pill-info">Dipinjam</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-dark">
                            Rp {{ number_format($row->total_denda + $row->total_biaya_perbaikan, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted small">Tidak ada data untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $laporan->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
