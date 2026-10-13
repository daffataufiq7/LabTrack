@extends('layouts.app')

@section('title', 'Tambah Alat Lab')
@section('page_title', 'Tambah Data Alat Laboratorium')
@section('page_subtitle', 'Mendaftarkan inventaris perangkat dan instrumen laboratorium baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-plus-circle-fill text-primary me-2"></i>Form Tambah Alat Lab
                </h6>
                <a href="{{ route('alat.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('alat.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <!-- Kode Alat -->
                        <div class="col-12 col-md-6">
                            <label for="kode_alat" class="form-label">Kode Alat Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" name="kode_alat" id="kode_alat" class="form-control @error('kode_alat') is-invalid @enderror" placeholder="Contoh: ALT-ELK-003" value="{{ old('kode_alat') }}" required autofocus>
                            @error('kode_alat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Kode unik inventaris (misal: ALT-KATEGORI-NOMOR).</div>
                        </div>

                        <!-- Kategori -->
                        <div class="col-12 col-md-6">
                            <label for="kategori" class="form-label">Kategori Alat <span class="text-danger">*</span></label>
                            <input type="text" name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" placeholder="Contoh: Elektronika, Jaringan, IoT..." value="{{ old('kategori') }}" required list="kategoriList">
                            <datalist id="kategoriList">
                                @foreach($kategoriSuggestions as $kat)
                                    <option value="{{ $kat }}">
                                @endforeach
                                <option value="Elektronika & Instrumentasi">
                                <option value="IoT & Embedded System">
                                <option value="Jaringan Komputer">
                                <option value="Instrumen & Optik">
                                <option value="Perkakas Lab">
                            </datalist>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama Alat -->
                        <div class="col-12">
                            <label for="nama_alat" class="form-label">Nama Lengkap Alat Laboratorium <span class="text-danger">*</span></label>
                            <input type="text" name="nama_alat" id="nama_alat" class="form-control @error('nama_alat') is-invalid @enderror" placeholder="Contoh: Osiloskop Digital Rigol DS1054Z" value="{{ old('nama_alat') }}" required>
                            @error('nama_alat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Stok Total -->
                        <div class="col-12 col-md-4">
                            <label for="stok_total" class="form-label">Stok Total Unit <span class="text-danger">*</span></label>
                            <input type="number" name="stok_total" id="stok_total" class="form-control @error('stok_total') is-invalid @enderror" placeholder="0" min="0" value="{{ old('stok_total', 1) }}" required>
                            @error('stok_total')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Keseluruhan kepemilikan aset lab.</div>
                        </div>

                        <!-- Stok Tersedia -->
                        <div class="col-12 col-md-4">
                            <label for="stok_tersedia" class="form-label">Stok Tersedia (Ready) <span class="text-danger">*</span></label>
                            <input type="number" name="stok_tersedia" id="stok_tersedia" class="form-control @error('stok_tersedia') is-invalid @enderror" placeholder="0" min="0" value="{{ old('stok_tersedia', 1) }}" required>
                            @error('stok_tersedia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Tidak boleh melebihi stok total.</div>
                        </div>

                        <!-- Kondisi Fisik -->
                        <div class="col-12 col-md-4">
                            <label for="kondisi" class="form-label">Kondisi Fisik Alat <span class="text-danger">*</span></label>
                            <select name="kondisi" id="kondisi" class="form-select @error('kondisi') is-invalid @enderror" required>
                                <option value="Baik" {{ old('kondisi') == 'Baik' ? 'selected' : '' }}>Baik (Siap Pakai)</option>
                                <option value="Perlu Perawatan" {{ old('kondisi') == 'Perlu Perawatan' ? 'selected' : '' }}>Perlu Perawatan</option>
                                <option value="Rusak Ringan" {{ old('kondisi') == 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="Rusak Berat" {{ old('kondisi') == 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                            @error('kondisi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('alat.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Alat Lab
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Sinkronisasi otomatis stok tersedia saat mengisi stok total untuk item baru
    const stokTotalInput = document.getElementById('stok_total');
    const stokTersediaInput = document.getElementById('stok_tersedia');

    stokTotalInput.addEventListener('input', function() {
        if (!stokTersediaInput.dataset.manualEdited) {
            stokTersediaInput.value = this.value;
        }
    });

    stokTersediaInput.addEventListener('input', function() {
        this.dataset.manualEdited = true;
    });
</script>
@endpush
