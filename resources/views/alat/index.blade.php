@extends('layouts.app')

@section('title', 'Data Alat Lab')
@section('page_title', 'Master Data Alat Laboratorium')
@section('page_subtitle', 'Pengelolaan inventaris, logistik, kategori, dan ketersediaan stok alat')

@section('content')
<div class="card mb-4">
    <div class="card-body p-3 p-md-4">
        <!-- Top Toolbar: Pencarian, Filter & Tambah -->
        <div class="row g-3 align-items-center mb-3">
            <div class="col-12 col-xl-8">
                <form action="{{ route('alat.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-sm-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari kode atau nama alat..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <select name="kategori" class="form-select">
                            <option value="">-- Semua Kategori --</option>
                            @foreach($daftarKategori as $kat)
                                <option value="{{ $kat }}" {{ $kategoriFilter == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-sm-2">
                        <select name="kondisi" class="form-select">
                            <option value="">-- Semua Kondisi --</option>
                            @foreach($daftarKondisi as $kon)
                                <option value="{{ $kon }}" {{ $kondisiFilter == $kon ? 'selected' : '' }}>{{ $kon }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100" title="Terapkan Filter">
                            <i class="bi bi-funnel-fill"></i>
                        </button>
                        @if($search || $kategoriFilter || $kondisiFilter)
                            <a href="{{ route('alat.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="col-12 col-xl-4 text-xl-end">
                <a href="{{ route('alat.create') }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> Tambah Alat Lab
                </a>
            </div>
        </div>

        <!-- Tabel Data Alat -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode Alat</th>
                        <th>Nama Alat Laboratorium</th>
                        <th>Kategori</th>
                        <th class="text-center">Ketersediaan Stok</th>
                        <th>Kondisi Fisik</th>
                        <th class="text-center">Riwayat Pakai</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alat as $index => $item)
                        <tr>
                            <td class="text-muted">{{ $alat->firstItem() + $index }}</td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                    {{ $item->kode_alat }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->nama_alat }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    {{ $item->kategori }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex flex-column align-items-center">
                                    <span class="badge {{ $item->stok_tersedia == 0 ? 'bg-danger' : ($item->stok_tersedia <= 2 ? 'bg-warning text-dark' : 'bg-success') }} px-3 py-1 rounded-pill">
                                        {{ $item->stok_tersedia }} / {{ $item->stok_total }} Unit
                                    </span>
                                    <small class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">
                                        @if($item->stok_tersedia == 0)
                                            <span class="text-danger fw-semibold">Habis Dipinjam</span>
                                        @else
                                            Tersedia {{ $item->stok_tersedia }}
                                        @endif
                                        @if($item->stok_rusak > 0)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" title="Unit Rusak (Perlu Perbaikan)">
                                                <i class="bi bi-wrench"></i> {{ $item->stok_rusak }} Rusak
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            </td>
                            <td>
                                @if($item->kondisi === 'Baik')
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i>Baik
                                    </span>
                                @elseif($item->kondisi === 'Perlu Perawatan')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">
                                        <i class="bi bi-wrench me-1"></i>Perlu Perawatan
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                        <i class="bi bi-x-circle me-1"></i>{{ $item->kondisi }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->detail_peminjaman_count > 0)
                                    <span class="badge bg-info-subtle text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                        {{ $item->detail_peminjaman_count }}x Dipinjam
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1">Belum pernah</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#unitAlatModal{{ $item->id }}" title="Daftar Unit Fisik & Serial Number">
                                        <i class="bi bi-upc-scan me-1"></i> Unit
                                    </button>
                                    <a href="{{ route('alat.edit', $item->id) }}" class="btn btn-outline-secondary" title="Edit Alat">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAlatModal{{ $item->id }}" title="Hapus Alat">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <!-- Modal Daftar Unit Fisik Alat -->
                                <div class="modal fade text-start" id="unitAlatModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-primary text-white">
                                                <div>
                                                    <h5 class="modal-title fs-6 fw-bold mb-0">
                                                        <i class="bi bi-upc-scan me-2"></i>Daftar Unit Fisik (Serial Number): {{ $item->nama_alat }}
                                                    </h5>
                                                    <small class="text-white-50 font-monospace">Kode Induk: {{ $item->kode_alat }} &bull; Total: {{ $item->stok_total }} Unit</small>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-3">
                                                <div class="d-flex gap-2 mb-3">
                                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2">
                                                        <i class="bi bi-check-circle me-1"></i>{{ $item->units->where('status', 'tersedia')->count() }} Ready
                                                    </span>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-3 py-2">
                                                        <i class="bi bi-person-walking me-1"></i>{{ $item->units->where('status', 'dipinjam')->count() }} Dipinjam
                                                    </span>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>{{ $item->units->where('status', 'rusak')->count() }} Rusak/Karantina
                                                    </span>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 40px;" class="text-center">No</th>
                                                                <th>Kode Unit Fisik</th>
                                                                <th>Nomor Seri / Barcode</th>
                                                                <th class="text-center" style="width: 120px;">Status Unit</th>
                                                                <th>Kondisi & Catatan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($item->units as $uIdx => $u)
                                                                <tr>
                                                                    <td class="text-center text-muted">{{ $uIdx + 1 }}</td>
                                                                    <td>
                                                                        <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                                                            {{ $u->kode_unit }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="font-monospace small text-muted">
                                                                        {{ $u->nomor_seri ?: '-' }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if($u->status === 'tersedia')
                                                                            <span class="badge bg-success-subtle text-success border border-success">Tersedia</span>
                                                                        @elseif($u->status === 'dipinjam')
                                                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning">Dipinjam</span>
                                                                        @elseif($u->status === 'rusak')
                                                                            <span class="badge bg-danger-subtle text-danger border border-danger">Rusak</span>
                                                                        @else
                                                                            <span class="badge bg-secondary">{{ $u->status }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="small">
                                                                        <div class="fw-semibold">{{ $u->kondisi }}</div>
                                                                        @if($u->catatan)
                                                                            <div class="text-danger" style="font-size: 0.72rem;">{{ $u->catatan }}</div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-muted py-3">Belum ada unit yang terdaftar.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Konfirmasi Hapus Alat -->
                                <div class="modal fade text-start" id="deleteAlatModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteAlatModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fs-6 fw-bold" id="deleteAlatModalLabel{{ $item->id }}">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Alat Lab
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body py-4">
                                                <p class="mb-2">Apakah Anda yakin ingin menghapus inventaris alat laboratorium ini?</p>
                                                <div class="p-3 bg-light rounded-3 border">
                                                    <strong>{{ $item->nama_alat }}</strong><br>
                                                    <small class="text-muted">Kode: {{ $item->kode_alat }} &bull; Kategori: {{ $item->kategori }} &bull; Total Stok: {{ $item->stok_total }} unit</small>
                                                </div>
                                                @if($item->detail_peminjaman_count > 0)
                                                    <div class="alert alert-warning mt-3 mb-0 py-2 small">
                                                        <i class="bi bi-shield-exclamation me-1"></i>
                                                        <strong>Pencegahan Penghapusan Data Master:</strong> Alat ini sudah pernah tercatat dalam {{ $item->detail_peminjaman_count }} riwayat transaksi peminjaman. Sistem akan menolak penghapusan demi audit logistik.
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('alat.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus Alat</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-tools"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Data Alat Laboratorium Tidak Ditemukan</h6>
                                    <p class="text-muted small mb-3">Tidak ada data alat lab yang cocok dengan filter atau kata kunci pencarian.</p>
                                    @if($search || $kategoriFilter || $kondisiFilter)
                                        <a href="{{ route('alat.index') }}" class="btn btn-sm btn-outline-secondary">Reset Filter</a>
                                    @else
                                        <a href="{{ route('alat.create') }}" class="btn btn-sm btn-primary">Tambah Alat Baru</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if($alat->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <small class="text-muted">
                    Menampilkan {{ $alat->firstItem() }} - {{ $alat->lastItem() }} dari total {{ $alat->total() }} alat laboratorium
                </small>
                <div>
                    {{ $alat->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
