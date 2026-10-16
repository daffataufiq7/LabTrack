@extends('layouts.app')

@section('title', 'Transaksi Peminjaman')

@section('content')
<!-- Page Header (Screen 5) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Transaksi Peminjaman</h4>
        <p class="text-muted small mb-0">Input dan kelola transaksi peminjaman alat.</p>
    </div>
    <div>
        <a href="{{ route('peminjaman.create') }}" class="btn-primary-dark">
            <i class="bi bi-plus-lg"></i>
            <span>Peminjaman Baru</span>
        </a>
    </div>
</div>

<!-- Table Card (Screen 5) -->
<div class="card p-3 p-md-4">
    <!-- Top Table Bar: Show entries & Search -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div class="d-flex align-items-center gap-2" style="font-size: 0.82rem; color: #64748b;">
            <span>Show</span>
            <select class="form-select form-select-sm py-1 px-2" style="width: 70px; font-size: 0.82rem;">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span>entries</span>
        </div>

        <form action="{{ route('peminjaman.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <div class="position-relative" style="max-width: 280px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.82rem;"></i>
                <input type="text" name="search" class="form-control form-control-sm ps-5 pe-3 py-1" style="border-radius: 6px; font-size: 0.84rem;" placeholder="Cari nama mahasiswa atau alat..." value="{{ $search }}">
            </div>
            @if($search)
                <a href="{{ route('peminjaman.index') }}" class="btn btn-sm btn-outline-secondary py-1" title="Reset">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Table Responsive -->
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Tanggal</th>
                    <th>Nama Mahasiswa</th>
                    <th>Nama Alat</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjaman as $index => $item)
                    <tr>
                        <td>{{ $peminjaman->firstItem() + $index }}</td>
                        <td class="text-secondary text-nowrap">
                            {{ \Carbon\Carbon::parse($item->tanggal_pinjam)->isoFormat('DD MMM Y') }}
                        </td>
                        <td>
                            <span class="fw-semibold text-dark d-block">{{ $item->mahasiswa->nama ?? 'Mahasiswa' }}</span>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $item->mahasiswa->nim ?? '-' }}</small>
                        </td>
                        <td>
                            @php
                                $firstItem = $item->detailPeminjaman->first();
                                $itemCount = $item->detailPeminjaman->count();
                            @endphp
                            <span class="text-dark">{{ $firstItem->alat->nama_alat ?? 'Alat Lab' }}</span>
                            @if($itemCount > 1)
                                <small class="text-muted d-block" style="font-size: 0.72rem;">+ {{ $itemCount - 1 }} alat lainnya</small>
                            @endif
                        </td>
                        <td class="text-center fw-semibold text-dark">
                            {{ $item->detailPeminjaman->sum('jumlah') }}
                        </td>
                        <td class="text-center">
                            @if($item->status === 'dipinjam')
                                <span class="badge-pill-status badge-pill-info">Dipinjam</span>
                            @elseif($item->status === 'dikembalikan')
                                <span class="badge-pill-status badge-pill-success">Dikembalikan</span>
                            @elseif($item->status === 'menunggu_acc')
                                <span class="badge-pill-status badge-pill-warning">Menunggu ACC</span>
                            @elseif($item->status === 'ditolak' || $item->status === 'dibatalkan')
                                <span class="badge-pill-status badge-pill-danger">{{ ucfirst($item->status) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <!-- Cetak Nota / Bukti -->
                                <a href="{{ route('peminjaman.nota', $item->id) }}" class="btn-action-icon" title="Cetak Nota" target="_blank">
                                    <i class="bi bi-receipt"></i>
                                </a>
                                <!-- Detail Transaksi -->
                                <a href="{{ route('peminjaman.show', $item->id) }}" class="btn-action-icon edit-btn" title="Detail Transaksi">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($item->status === 'dipinjam')
                                    <!-- Proses Kembalikan -->
                                    <a href="{{ route('pengembalian.form', $item->id) }}" class="btn-action-icon" style="color: #16a34a;" title="Proses Pengembalian">
                                        <i class="bi bi-box-arrow-in-down-left"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted small">
                            Tidak ada data transaksi peminjaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination (Screen 5) -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-4 pt-2 border-top">
        <span class="small text-muted" style="font-size: 0.8rem;">
            Menampilkan {{ $peminjaman->firstItem() ?? 0 }} - {{ $peminjaman->lastItem() ?? 0 }} dari {{ $peminjaman->total() }} data
        </span>
        <div>
            {{ $peminjaman->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
