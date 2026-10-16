@extends('layouts.app')

@section('title', 'Data Denda')

@section('content')
<!-- Page Header (Screen 7) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Data Denda</h4>
        <p class="text-muted small mb-0">Daftar denda keterlambatan dan biaya kerusakan alat.</p>
    </div>
    <!-- Summary Badge -->
    <div class="d-flex gap-2">
        <div class="bg-white border rounded-3 px-3 py-2 text-end shadow-sm">
            <small class="text-muted d-block" style="font-size: 0.72rem;">Belum Terbayar</small>
            <span class="fw-bold text-danger" style="font-size: 0.95rem;">Rp {{ number_format($totalDendaBelumLunas, 0, ',', '.') }}</span>
        </div>
        <div class="bg-white border rounded-3 px-3 py-2 text-end shadow-sm">
            <small class="text-muted d-block" style="font-size: 0.72rem;">Sudah Lunas</small>
            <span class="fw-bold text-success" style="font-size: 0.95rem;">Rp {{ number_format($totalDendaLunas, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

<!-- Table Card (Screen 7) -->
<div class="card p-3 p-md-4">
    <!-- Filter Date Range & Search with Button Filter -->
    <form action="{{ route('denda.index') }}" method="GET" class="row g-2 align-items-center mb-4">
        <div class="col-12 col-md-3">
            <input type="date" name="dari_tanggal" class="form-control py-2" value="{{ $dariTanggal }}" placeholder="Dari Tanggal">
        </div>
        <div class="col-12 col-md-3">
            <input type="date" name="sampai_tanggal" class="form-control py-2" value="{{ $sampaiTanggal }}" placeholder="Sampai Tanggal">
        </div>
        <div class="col-12 col-md-4">
            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem;"></i>
                <input type="text" name="search" class="form-control ps-5 pe-3 py-2" placeholder="Cari nama mahasiswa..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn-primary-dark w-100 justify-content-center py-2">
                <i class="bi bi-funnel"></i>
                <span>Filter</span>
            </button>
            @if($search || $dariTanggal || $sampaiTanggal)
                <a href="{{ route('denda.index') }}" class="btn btn-outline-secondary py-2 px-3" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </div>
    </form>

    <!-- Table Responsive (Screen 7) -->
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Tanggal</th>
                    <th>Nama Mahasiswa</th>
                    <th>Nama Alat</th>
                    <th>Jenis Denda</th>
                    <th>Nominal</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarDenda as $index => $item)
                    @php
                        $firstAlat = $item->detailPeminjaman->first()->alat ?? null;
                        $nominal = $item->total_denda + $item->total_biaya_perbaikan;
                        $jenisDenda = [];
                        if ($item->total_denda > 0) {
                            $jenisDenda[] = 'Keterlambatan (' . ceil($item->total_denda / 2000) . ' hari)';
                        }
                        if ($item->total_biaya_perbaikan > 0) {
                            $jenisDenda[] = 'Kerusakan';
                        }
                    @endphp
                    <tr>
                        <td>{{ $daftarDenda->firstItem() + $index }}</td>
                        <td class="text-secondary text-nowrap">
                            {{ \Carbon\Carbon::parse($item->tanggal_kembali ?? $item->tanggal_pinjam)->isoFormat('DD MMM Y') }}
                        </td>
                        <td>
                            <span class="fw-semibold text-dark d-block">{{ $item->mahasiswa->nama ?? 'Mahasiswa' }}</span>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $item->mahasiswa->nim ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="text-dark">{{ $firstAlat->nama_alat ?? 'Alat Lab' }}</span>
                        </td>
                        <td>
                            <span class="text-secondary">{{ implode(', ', $jenisDenda) ?: 'Denda Lab' }}</span>
                        </td>
                        <td class="fw-bold text-dark">
                            Rp {{ number_format($nominal, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($item->status_pembayaran === 'lunas')
                                <span class="badge-pill-status badge-pill-success">Lunas</span>
                            @else
                                <span class="badge-pill-status badge-pill-danger">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->status_pembayaran === 'belum_lunas')
                                <form action="{{ route('pengembalian.bayar', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Konfirmasi pelunasan denda sebesar Rp {{ number_format($nominal, 0, ',', '.') }} untuk {{ $item->mahasiswa->nama }}?');">
                                    @csrf
                                    <input type="hidden" name="metode_pembayaran" value="Tunai">
                                    <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2" style="font-size: 0.78rem;" title="Konfirmasi Pelunasan">
                                        <i class="bi bi-check-circle me-1"></i> Bayar
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('peminjaman.show', $item->id) }}" class="btn-action-icon edit-btn" title="Detail Pembayaran">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted small">
                            Tidak ada data denda keterlambatan atau biaya kerusakan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination (Screen 7) -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-4 pt-2 border-top">
        <span class="small text-muted" style="font-size: 0.8rem;">
            Menampilkan {{ $daftarDenda->firstItem() ?? 0 }} - {{ $daftarDenda->lastItem() ?? 0 }} dari {{ $daftarDenda->total() }} data
        </span>
        <div>
            {{ $daftarDenda->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
