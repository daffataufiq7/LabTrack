@extends('layouts.app')

@section('title', 'Data Alat Laboratorium')

@section('content')
<!-- Page Header (Screen 4) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Data Alat Laboratorium</h4>
        <p class="text-muted small mb-0">Kelola data alat laboratorium yang tersedia.</p>
    </div>
    <div>
        <a href="{{ route('alat.create') }}" class="btn-primary-dark">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Alat</span>
        </a>
    </div>
</div>

<!-- Table Card (Screen 4) -->
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

        <form action="{{ route('alat.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <div class="position-relative" style="max-width: 260px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.82rem;"></i>
                <input type="text" name="search" class="form-control form-control-sm ps-5 pe-3 py-1" style="border-radius: 6px; font-size: 0.84rem;" placeholder="Cari nama alat..." value="{{ $search }}">
            </div>
            @if($search)
                <a href="{{ route('alat.index') }}" class="btn btn-sm btn-outline-secondary py-1" title="Reset">
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
                    <th>Nama Alat</th>
                    <th>Kategori</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alat as $index => $item)
                    <tr>
                        <td>{{ $alat->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $item->gambar_url }}" alt="{{ $item->nama_alat }}" class="rounded-3 border shadow-sm flex-shrink-0" style="width: 46px; height: 46px; object-fit: cover;">
                                <div>
                                    <span class="fw-semibold text-dark d-block">{{ $item->nama_alat }}</span>
                                    <small class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $item->kode_alat }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary">{{ $item->kategori }}</td>
                        <td class="text-center fw-bold text-dark">
                            {{ $item->stok_tersedia }} <span class="text-muted fw-normal" style="font-size: 0.75rem;">/ {{ $item->stok_total }}</span>
                        </td>
                        <td class="text-center">
                            @if($item->stok_tersedia > 0)
                                <span class="badge-pill-status badge-pill-success">Tersedia</span>
                            @else
                                <span class="badge-pill-status badge-pill-danger">Habis</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <!-- Modal View Unit Fisik -->
                                <button type="button" class="btn-action-icon" data-bs-toggle="modal" data-bs-target="#unitModal{{ $item->id }}" title="Lihat Unit Fisik">
                                    <i class="bi bi-upc-scan"></i>
                                </button>
                                <a href="{{ route('alat.edit', $item->id) }}" class="btn-action-icon edit-btn" title="Edit Alat">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('alat.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alat {{ $item->nama_alat }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-icon delete-btn" title="Hapus Alat">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Modal Unit Fisik -->
                            <div class="modal fade text-start" id="unitModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header border-bottom py-3">
                                            <h6 class="modal-title fw-bold text-dark">
                                                <i class="bi bi-upc-scan me-2 text-primary"></i> Unit Fisik: {{ $item->nama_alat }}
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-3">
                                            <div class="d-flex justify-content-between mb-3 small text-muted">
                                                <span>Total: <strong>{{ $item->units->count() }} unit</strong></span>
                                                <span>Siap Pakai: <strong class="text-success">{{ $item->stok_tersedia }}</strong> | Rusak: <strong class="text-danger">{{ $item->stok_rusak }}</strong></span>
                                            </div>
                                            <div class="list-group list-group-flush border rounded-3">
                                                @forelse($item->units as $u)
                                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 small">
                                                        <div>
                                                            <span class="font-monospace fw-bold text-dark">{{ $u->kode_unit }}</span>
                                                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $u->nomor_seri ?? 'SN: Standar Pabrik' }}</small>
                                                        </div>
                                                        <span class="badge-pill-status {{ $u->status === 'tersedia' ? 'badge-pill-success' : ($u->status === 'dipinjam' ? 'badge-pill-info' : 'badge-pill-danger') }}">
                                                            {{ ucfirst($u->status) }}
                                                        </span>
                                                    </div>
                                                @empty
                                                    <div class="p-3 text-center text-muted small">Belum ada unit fisik terdata.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted small">
                            Tidak ada data alat yang cocok dengan pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination (Screen 4) -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-4 pt-2 border-top">
        <span class="small text-muted" style="font-size: 0.8rem;">
            Menampilkan {{ $alat->firstItem() ?? 0 }} - {{ $alat->lastItem() ?? 0 }} dari {{ $alat->total() }} data
        </span>
        <div>
            {{ $alat->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
