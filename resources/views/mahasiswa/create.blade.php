@extends('layouts.app')

@section('title', 'Tambah Mahasiswa')
@section('page_title', 'Tambah Data Mahasiswa')
@section('page_subtitle', 'Mendaftarkan mahasiswa baru sebagai peminjam laboratorium')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-plus-fill text-primary me-2"></i>Form Tambah Mahasiswa
                </h6>
                <a href="{{ route('mahasiswa.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('mahasiswa.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <!-- NIM -->
                        <div class="col-12 col-md-6">
                            <label for="nim" class="form-label">Nomor Induk Mahasiswa (NIM) <span class="text-danger">*</span></label>
                            <input type="text" name="nim" id="nim" class="form-control @error('nim') is-invalid @enderror" placeholder="Contoh: 22051204001" value="{{ old('nim') }}" required autofocus>
                            @error('nim')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">NIM harus unik dan menjadi identitas utama mahasiswa.</div>
                        </div>

                        <!-- No HP -->
                        <div class="col-12 col-md-6">
                            <label for="no_hp" class="form-label">Nomor Handphone / WhatsApp</label>
                            <input type="text" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" placeholder="Contoh: 081234567890" value="{{ old('no_hp') }}">
                            @error('no_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Opsional untuk kontak konfirmasi pengembalian.</div>
                        </div>

                        <!-- Nama Mahasiswa -->
                        <div class="col-12">
                            <label for="nama" class="form-label">Nama Lengkap Mahasiswa <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Masukkan nama lengkap mahasiswa" value="{{ old('nama') }}" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Program Studi -->
                        <div class="col-12">
                            <label for="program_studi" class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <input type="text" name="program_studi" id="program_studi" class="form-control @error('program_studi') is-invalid @enderror" placeholder="Contoh: Teknik Informatika, Sistem Informasi, dll." value="{{ old('program_studi') }}" required list="prodiOptions">
                            <datalist id="prodiOptions">
                                <option value="Teknik Informatika">
                                <option value="Sistem Informasi">
                                <option value="Teknologi Informasi">
                                <option value="Pendidikan Teknologi Informasi">
                                <option value="Teknik Elektro">
                            </datalist>
                            @error('program_studi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Mahasiswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
