@extends('layouts.app')

@section('title', 'Data Mahasiswa')
@section('page_title', 'Master Data Mahasiswa')
@section('page_subtitle', 'Pengelolaan data mahasiswa peminjam alat laboratorium')

@section('content')
<div class="card mb-4">
    <div class="card-body p-3 p-md-4">
        <!-- Top Toolbar: Pencarian, Filter & Tombol Tambah -->
        <div class="row g-3 align-items-center mb-3">
            <div class="col-12 col-lg-7">
                <form action="{{ route('mahasiswa.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-sm-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari NIM, nama, no HP..." value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-8 col-sm-4">
                        <select name="program_studi" class="form-select">
                            <option value="">-- Semua Prodi --</option>
                            @foreach($daftarProdi as $prodi)
                                <option value="{{ $prodi }}" {{ $prodiFilter == $prodi ? 'selected' : '' }}>{{ $prodi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-sm-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100 px-2" title="Terapkan Filter">
                            <i class="bi bi-funnel-fill"></i>
                        </button>
                        @if($search || $prodiFilter)
                            <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="col-12 col-lg-5 text-lg-end">
                <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Mahasiswa
                </a>
            </div>
        </div>

        <!-- Tabel Data Mahasiswa -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>No. Handphone</th>
                        <th class="text-center">Riwayat Pinjam</th>
                        <th class="text-end" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswa as $index => $mhs)
                        <tr>
                            <td class="text-muted">{{ $mahasiswa->firstItem() + $index }}</td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                    {{ $mhs->nim }}
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">{{ $mhs->nama }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-25 px-2 py-1">
                                    {{ $mhs->program_studi }}
                                </span>
                            </td>
                            <td>
                                @if($mhs->no_hp)
                                    <span class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $mhs->no_hp }}</span>
                                @else
                                    <span class="text-muted fst-italic">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($mhs->peminjaman_count > 0)
                                    <span class="badge bg-info-subtle text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                        {{ $mhs->peminjaman_count }} Transaksi
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1">Belum ada</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('mahasiswa.edit', $mhs->id) }}" class="btn btn-outline-secondary" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $mhs->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <!-- Modal Konfirmasi Hapus -->
                                <div class="modal fade text-start" id="deleteModal{{ $mhs->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $mhs->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fs-6 fw-bold" id="deleteModalLabel{{ $mhs->id }}">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Mahasiswa
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body py-4">
                                                <p class="mb-2">Apakah Anda yakin ingin menghapus data mahasiswa berikut?</p>
                                                <div class="p-3 bg-light rounded-3 border">
                                                    <strong>{{ $mhs->nama }}</strong><br>
                                                    <small class="text-muted">NIM: {{ $mhs->nim }} &bull; {{ $mhs->program_studi }}</small>
                                                </div>
                                                @if($mhs->peminjaman_count > 0)
                                                    <div class="alert alert-warning mt-3 mb-0 py-2 small">
                                                        <i class="bi bi-shield-exclamation me-1"></i>
                                                        <strong>Peringatan Keamanan:</strong> Mahasiswa ini memiliki {{ $mhs->peminjaman_count }} riwayat transaksi. Sistem akan menolak penghapusan untuk menjaga integritas data laboratorium.
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('mahasiswa.destroy', $mhs->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus Data</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Data Mahasiswa Tidak Ditemukan</h6>
                                    <p class="text-muted small mb-3">Tidak ada data mahasiswa yang sesuai dengan kriteria pencarian Anda.</p>
                                    @if($search || $prodiFilter)
                                        <a href="{{ route('mahasiswa.index') }}" class="btn btn-sm btn-outline-secondary">Reset Pencarian</a>
                                    @else
                                        <a href="{{ route('mahasiswa.create') }}" class="btn btn-sm btn-primary">Tambah Mahasiswa Baru</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginasi -->
        @if($mahasiswa->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                <small class="text-muted">
                    Menampilkan {{ $mahasiswa->firstItem() }} - {{ $mahasiswa->lastItem() }} dari total {{ $mahasiswa->total() }} mahasiswa
                </small>
                <div>
                    {{ $mahasiswa->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
