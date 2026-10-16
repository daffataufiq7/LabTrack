@extends('layouts.app')

@section('title', 'Data Mahasiswa')

@section('content')
<!-- Page Header (Screen 3) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Data Mahasiswa</h4>
        <p class="text-muted small mb-0">Kelola data mahasiswa yang dapat melakukan peminjaman alat.</p>
    </div>
    <div>
        <a href="{{ route('mahasiswa.create') }}" class="btn-primary-dark">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Mahasiswa</span>
        </a>
    </div>
</div>

<!-- Table Card (Screen 3) -->
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

        <form action="{{ route('mahasiswa.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <div class="position-relative" style="max-width: 260px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.82rem;"></i>
                <input type="text" name="search" class="form-control form-control-sm ps-5 pe-3 py-1" style="border-radius: 6px; font-size: 0.84rem;" placeholder="Cari nama atau NIM..." value="{{ $search }}">
            </div>
            @if($search)
                <a href="{{ route('mahasiswa.index') }}" class="btn btn-sm btn-outline-secondary py-1" title="Reset">
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
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Program Studi</th>
                    <th>No. HP</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mahasiswa as $index => $mhs)
                    <tr>
                        <td>{{ $mahasiswa->firstItem() + $index }}</td>
                        <td class="fw-medium text-dark">{{ $mhs->nim }}</td>
                        <td class="fw-semibold text-dark">{{ $mhs->nama }}</td>
                        <td class="text-secondary">{{ $mhs->program_studi }}</td>
                        <td class="text-secondary">{{ $mhs->no_hp ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge-pill-status badge-pill-success">Aktif</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="{{ route('mahasiswa.edit', $mhs->id) }}" class="btn-action-icon edit-btn" title="Edit Data">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('mahasiswa.destroy', $mhs->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mahasiswa {{ $mhs->nama }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-icon delete-btn" title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted small">
                            Tidak ada data mahasiswa yang cocok dengan pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination (Screen 3) -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-4 pt-2 border-top">
        <span class="small text-muted" style="font-size: 0.8rem;">
            Menampilkan {{ $mahasiswa->firstItem() ?? 0 }} - {{ $mahasiswa->lastItem() ?? 0 }} dari {{ $mahasiswa->total() }} data
        </span>
        <div>
            {{ $mahasiswa->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
