@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<!-- Page Header (Screen 9) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Pengaturan</h4>
        <p class="text-muted small mb-0">Kelola pengaturan sistem.</p>
    </div>
</div>

<!-- Tabs Pill Navigation (Screen 9) -->
<div class="d-flex align-items-center gap-2 mb-4 overflow-auto pb-1">
    <button class="btn btn-sm btn-primary-dark px-3 py-2 rounded-3 text-nowrap">
        Profil Lab
    </button>
    <button class="btn btn-sm btn-outline-secondary bg-white px-3 py-2 rounded-3 text-nowrap">
        Akun
    </button>
    <button class="btn btn-sm btn-outline-secondary bg-white px-3 py-2 rounded-3 text-nowrap">
        Notifikasi
    </button>
    <button class="btn btn-sm btn-outline-secondary bg-white px-3 py-2 rounded-3 text-nowrap">
        Backup
    </button>
</div>

<!-- Form Card: Informasi Laboratorium (Screen 9) -->
<div class="card p-3 p-md-4" style="max-width: 800px;">
    <h6 class="fw-bold text-dark mb-4 pb-2 border-bottom">Informasi Laboratorium</h6>

    <form action="{{ route('pengaturan.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label" style="font-size: 0.82rem; color: #475569;">Nama Laboratorium</label>
            <input type="text" name="nama_lab" class="form-control" value="{{ $settings['nama_lab'] ?? 'Laboratorium Teknik Informatika' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-size: 0.82rem; color: #475569;">Alamat</label>
            <input type="text" name="alamat" class="form-control" value="{{ $settings['alamat'] ?? 'Jl. Pendidikan No. 1, Kampus' }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label" style="font-size: 0.82rem; color: #475569;">Kontak</label>
            <input type="email" name="kontak" class="form-control" value="{{ $settings['kontak'] ?? 'lab@kampus.ac.id' }}" required>
        </div>

        <div class="mb-4">
            <label class="form-label" style="font-size: 0.82rem; color: #475569;">Jam Operasional</label>
            <input type="text" name="jam_operasional" class="form-control" value="{{ $settings['jam_operasional'] ?? 'Senin - Jumat, 07:00 - 18:00' }}" required>
        </div>

        <div>
            <button type="submit" class="btn-primary-dark px-4 py-2">
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>
@endsection
