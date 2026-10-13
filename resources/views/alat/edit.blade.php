@extends('layouts.app')

@section('title', 'Edit Alat Lab')
@section('page_title', 'Perbarui Data Alat Laboratorium')
@section('page_subtitle', 'Memperbarui data inventaris, status kondisi, atau jumlah stok alat')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Form Edit Alat Lab
                </h6>
                <a href="{{ route('alat.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('alat.update', $alat->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Kode Alat -->
                        <div class="col-12 col-md-6">
                            <label for="kode_alat" class="form-label">Kode Alat Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" name="kode_alat" id="kode_alat" class="form-control @error('kode_alat') is-invalid @enderror" value="{{ old('kode_alat', $alat->kode_alat) }}" required>
                            @error('kode_alat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kategori -->
                        <div class="col-12 col-md-6">
                            <label for="kategori" class="form-label">Kategori Alat <span class="text-danger">*</span></label>
                            <input type="text" name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('kategori', $alat->kategori) }}" required list="kategoriList">
                            <datalist id="kategoriList">
                                @foreach($kategoriSuggestions as $kat)
                                    <option value="{{ $kat }}">
                                @endforeach
                            </datalist>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama Alat -->
                        <div class="col-12">
                            <label for="nama_alat" class="form-label">Nama Lengkap Alat Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" name="nama_alat" id="nama_alat" class="form-control @error('nama_alat') is-invalid @enderror" value="{{ old('nama_alat', $alat->nama_alat) }}" required>
                            @error('nama_alat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stok Total -->
                        <div class="col-12 col-md-3">
                            <label for="stok_total" class="form-label">Stok Total Unit <span class="text-danger">*</span></label>
                            <input type="number" name="stok_total" id="stok_total" class="form-control @error('stok_total') is-invalid @enderror" min="0" value="{{ old('stok_total', $alat->stok_total) }}" required>
                            @error('stok_total')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stok Tersedia -->
                        <div class="col-12 col-md-3">
                            <label for="stok_tersedia" class="form-label">Stok Tersedia (Ready) <span class="text-danger">*</span></label>
                            <input type="number" name="stok_tersedia" id="stok_tersedia" class="form-control @error('stok_tersedia') is-invalid @enderror" min="0" value="{{ old('stok_tersedia', $alat->stok_tersedia) }}" required>
                            @error('stok_tersedia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stok Rusak (Dalam Perbaikan) -->
                        <div class="col-12 col-md-3">
                            <label for="stok_rusak" class="form-label">Stok Rusak (Perbaikan)</label>
                            <input type="number" name="stok_rusak" id="stok_rusak" class="form-control @error('stok_rusak') is-invalid @enderror" min="0" value="{{ old('stok_rusak', $alat->stok_rusak ?? 0) }}">
                            @error('stok_rusak')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kondisi Fisik -->
                        <div class="col-12 col-md-3">
                            <label for="kondisi" class="form-label">Kondisi Fisik Alat <span class="text-danger">*</span></label>
                            <select name="kondisi" id="kondisi" class="form-select @error('kondisi') is-invalid @enderror" required>
                                <option value="Baik" {{ old('kondisi', $alat->kondisi) == 'Baik' ? 'selected' : '' }}>Baik (Siap Pakai)</option>
                                <option value="Perlu Perawatan" {{ old('kondisi', $alat->kondisi) == 'Perlu Perawatan' ? 'selected' : '' }}>Perlu Perawatan</option>
                                <option value="Rusak Ringan" {{ old('kondisi', $alat->kondisi) == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="Rusak Berat" {{ old('kondisi', $alat->kondisi) == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                            @error('kondisi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('alat.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Perbarui Alat Lab
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
