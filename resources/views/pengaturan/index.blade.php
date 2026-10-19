@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<!-- Page Header (Screen 9) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Pengaturan</h4>
        <p class="text-muted small mb-0">Kelola pengaturan sistem laboratorium.</p>
    </div>
</div>

<!-- Tabs Pill Navigation -->
<ul class="nav nav-pills gap-2 mb-4 overflow-auto pb-1 flex-nowrap" id="pengaturanTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link btn-tab-pill {{ $activeTab === 'profil' ? 'active' : '' }}" 
                id="tab-profil-btn" 
                data-bs-toggle="pill" 
                data-bs-target="#pane-profil" 
                type="button" 
                role="tab" 
                aria-controls="pane-profil" 
                aria-selected="{{ $activeTab === 'profil' ? 'true' : 'false' }}">
            <i class="bi bi-building me-1"></i> Profil Lab
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link btn-tab-pill {{ $activeTab === 'akun' ? 'active' : '' }}" 
                id="tab-akun-btn" 
                data-bs-toggle="pill" 
                data-bs-target="#pane-akun" 
                type="button" 
                role="tab" 
                aria-controls="pane-akun" 
                aria-selected="{{ $activeTab === 'akun' ? 'true' : 'false' }}">
            <i class="bi bi-person-gear me-1"></i> Akun
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link btn-tab-pill {{ $activeTab === 'notifikasi' ? 'active' : '' }}" 
                id="tab-notifikasi-btn" 
                data-bs-toggle="pill" 
                data-bs-target="#pane-notifikasi" 
                type="button" 
                role="tab" 
                aria-controls="pane-notifikasi" 
                aria-selected="{{ $activeTab === 'notifikasi' ? 'true' : 'false' }}">
            <i class="bi bi-bell me-1"></i> Notifikasi
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link btn-tab-pill {{ $activeTab === 'backup' ? 'active' : '' }}" 
                id="tab-backup-btn" 
                data-bs-toggle="pill" 
                data-bs-target="#pane-backup" 
                type="button" 
                role="tab" 
                aria-controls="pane-backup" 
                aria-selected="{{ $activeTab === 'backup' ? 'true' : 'false' }}">
            <i class="bi bi-database-down me-1"></i> Backup
        </button>
    </li>
</ul>

<!-- Tab Content Container -->
<div class="tab-content" id="pengaturanTabContent">

    <!-- ============================================================== -->
    <!-- TAB 1: PROFIL LAB                                              -->
    <!-- ============================================================== -->
    <div class="tab-pane fade {{ $activeTab === 'profil' ? 'show active' : '' }}" id="pane-profil" role="tabpanel" aria-labelledby="tab-profil-btn">
        <div class="card p-3 p-md-4 shadow-sm border-0" style="max-width: 800px; border-radius: 12px;">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-info-circle text-primary me-2"></i>Informasi Laboratorium
                </h6>
                <span class="badge bg-light text-secondary border">Identitas Instansi</span>
            </div>

            <form action="{{ route('pengaturan.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #475569;">Nama Laboratorium</label>
                    <input type="text" name="nama_lab" class="form-control" value="{{ old('nama_lab', $settings['nama_lab'] ?? 'Laboratorium Teknik Informatika') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #475569;">Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="{{ old('alamat', $settings['alamat'] ?? 'Jl. Pendidikan No. 1, Kampus Terpadu') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #475569;">Kontak / Email Resmi</label>
                    <input type="email" name="kontak" class="form-control" value="{{ old('kontak', $settings['kontak'] ?? 'lab@kampus.ac.id') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #475569;">Jam Operasional</label>
                    <input type="text" name="jam_operasional" class="form-control" value="{{ old('jam_operasional', $settings['jam_operasional'] ?? 'Senin - Jumat, 07:00 - 18:00 WIB') }}" required>
                </div>

                <!-- Parameter Sirkulasi & Denda -->
                <div class="p-3 mb-4 rounded-3 bg-light border">
                    <h6 class="fw-bold text-dark mb-3" style="font-size: 0.88rem;">
                        <i class="bi bi-sliders text-primary me-1"></i> Parameter Kebijakan & Denda Lab
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.8rem; color: #475569;">Tarif Denda Keterlambatan (/hari)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">Rp</span>
                                <input type="number" name="denda_per_hari" class="form-control" value="{{ old('denda_per_hari', $settings['denda_per_hari'] ?? 2000) }}" min="0" step="500">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.8rem; color: #475569;">Masa Tenggang Toleransi (Hari)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="toleransi_hari" class="form-control" value="{{ old('toleransi_hari', $settings['toleransi_hari'] ?? 3) }}" min="0" max="30">
                                <span class="input-group-text bg-white">Hari</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.8rem; color: #475569;">Standar Biaya Unit Rusak</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white">Rp</span>
                                <input type="number" name="biaya_alat_rusak" class="form-control" value="{{ old('biaya_alat_rusak', $settings['biaya_alat_rusak'] ?? 10000) }}" min="0" step="1000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.8rem; color: #475569;">Maksimal Durasi Sewa</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="maks_hari_pinjam" class="form-control" value="{{ old('maks_hari_pinjam', $settings['maks_hari_pinjam'] ?? 7) }}" min="1" max="30">
                                <span class="input-group-text bg-white">Hari</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary-dark px-4 py-2 rounded-3 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: AKUN (PROFIL ADMIN & MANAJEMEN AKUN USER MAHASISWA)     -->
    <!-- ============================================================== -->
    <div class="tab-pane fade {{ $activeTab === 'akun' ? 'show active' : '' }}" id="pane-akun" role="tabpanel" aria-labelledby="tab-akun-btn">
        
        <!-- Sub-Pill Switcher: Akun Saya vs Kelola Akun Mahasiswa -->
        <ul class="nav nav-pills gap-2 mb-3 bg-white p-2 rounded-3 border shadow-sm d-inline-flex flex-wrap" id="subtabAkunGroup" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-3 rounded-2 fw-semibold btn-subtab {{ $subtab !== 'user' ? 'active' : '' }}" 
                        id="sub-saya-btn" 
                        data-bs-toggle="pill" 
                        data-bs-target="#pane-sub-saya" 
                        type="button" 
                        role="tab"
                        aria-selected="{{ $subtab !== 'user' ? 'true' : 'false' }}">
                    <i class="bi bi-person-gear me-1"></i> Profil Akun Saya (Admin)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-3 rounded-2 fw-semibold btn-subtab {{ $subtab === 'user' ? 'active' : '' }}" 
                        id="sub-user-btn" 
                        data-bs-toggle="pill" 
                        data-bs-target="#pane-sub-user" 
                        type="button" 
                        role="tab"
                        aria-selected="{{ $subtab === 'user' ? 'true' : 'false' }}">
                    <i class="bi bi-people me-1"></i> Kelola Akun User Mahasiswa
                    <span class="badge bg-primary bg-opacity-25 text-primary ms-1 px-2">{{ $userMahasiswa->total() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="subtabAkunContent">

            <!-- SUB-TAB 1: PROFIL AKUN SAYA (ADMIN) -->
            <div class="tab-pane fade {{ $subtab !== 'user' ? 'show active' : '' }}" id="pane-sub-saya" role="tabpanel">
                <div class="card p-3 p-md-4 shadow-sm border-0" style="max-width: 800px; border-radius: 12px;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-person-badge text-primary me-2"></i>Informasi Akun Pengelola Lab
                        </h6>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                            Role: {{ ucfirst($user->role ?? 'Admin') }}
                        </span>
                    </div>

                    <form action="{{ route('pengaturan.akun') }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #475569;">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #475569;">Username</label>
                                <input type="text" name="username" class="form-control" value="{{ old('username', $user->username ?? '') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size: 0.82rem; color: #475569;">Email Akun</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" placeholder="admin@kampus.ac.id">
                            </div>
                        </div>

                        <!-- Keamanan & Ganti Password -->
                        <div class="p-3 mb-4 rounded-3 bg-light border">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.88rem;">
                                    <i class="bi bi-shield-lock text-primary me-1"></i> Ganti Kata Sandi (Opsional)
                                </h6>
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti kata sandi</small>
                            </div>
                            <div class="row g-3 pt-2">
                                <div class="col-md-12">
                                    <label class="form-label" style="font-size: 0.8rem; color: #475569;">Kata Sandi Saat Ini</label>
                                    <input type="password" name="current_password" class="form-control form-control-sm" placeholder="Masukkan kata sandi lama">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.8rem; color: #475569;">Kata Sandi Baru</label>
                                    <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimal 4 karakter">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-size: 0.8rem; color: #475569;">Ulangi Kata Sandi Baru</label>
                                    <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Ulangi kata sandi baru">
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary-dark px-4 py-2 rounded-3 fw-semibold">
                                <i class="bi bi-save me-1"></i> Perbarui Akun & Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SUB-TAB 2: KELOLA AKUN USER MAHASISWA -->
            <div class="tab-pane fade {{ $subtab === 'user' ? 'show active' : '' }}" id="pane-sub-user" role="tabpanel">
                <div class="card p-3 p-md-4 shadow-sm border-0" style="max-width: 1000px; border-radius: 12px;">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">
                                <i class="bi bi-people text-primary me-2"></i>Daftar Akun User Mahasiswa
                            </h6>
                            <p class="text-muted small mb-0">Kelola akses portal mahasiswa, edit kredensial, reset sandi, atau nonaktifkan/suspend akun.</p>
                        </div>
                        <span class="badge bg-light text-secondary border">
                            Total: {{ $userMahasiswa->total() }} Akun
                        </span>
                    </div>

                    <!-- Search Bar Mahasiswa -->
                    <form action="{{ route('pengaturan.index') }}" method="GET" class="mb-3">
                        <input type="hidden" name="tab" value="akun">
                        <input type="hidden" name="subtab" value="user">
                        <div class="input-group input-group-sm" style="max-width: 420px;">
                            <input type="text" name="cari_user" class="form-control" placeholder="Cari nama mahasiswa, NIM, atau email..." value="{{ $searchUser ?? '' }}">
                            <button type="submit" class="btn btn-primary-dark">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            @if(!empty($searchUser))
                                <a href="{{ route('pengaturan.index', ['tab' => 'akun', 'subtab' => 'user']) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            @endif
                        </div>
                    </form>

                    <!-- Table Akun Mahasiswa -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Mahasiswa / Pengguna</th>
                                    <th>NIM / Username</th>
                                    <th>Email / Kontak</th>
                                    <th>Status Akun</th>
                                    <th class="text-end" style="min-width: 170px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userMahasiswa as $index => $mhsUser)
                                    <tr>
                                        <td class="text-muted">{{ $userMahasiswa->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                                    {{ strtoupper(substr($mhsUser->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $mhsUser->name }}</div>
                                                    <small class="text-muted">{{ $mhsUser->mahasiswa->program_studi ?? 'Mahasiswa' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border font-monospace">
                                                {{ $mhsUser->username }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $mhsUser->email ?: '-' }}</div>
                                            <small class="text-muted">{{ $mhsUser->mahasiswa->no_hp ?? '' }}</small>
                                        </td>
                                        <td>
                                            @if($mhsUser->isActive())
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                                </span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                                    <i class="bi bi-slash-circle-fill me-1"></i> Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <!-- Tombol Edit Akun -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary px-2 py-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditUser-{{ $mhsUser->id }}"
                                                        title="Edit Akun & Reset Sandi">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                </button>

                                                <!-- Tombol Toggle Nonaktifkan / Aktifkan -->
                                                <form action="{{ route('pengaturan.users.toggle', $mhsUser->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @if($mhsUser->isActive())
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger px-2 py-1" 
                                                                title="Nonaktifkan Akun"
                                                                onclick="return confirm('Nonaktifkan akun mahasiswa {{ $mhsUser->name }}? Mahasiswa tidak akan dapat login ke portal sampai diaktifkan kembali.')">
                                                            <i class="bi bi-person-x me-1"></i> Nonaktifkan
                                                        </button>
                                                    @else
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-success px-2 py-1" 
                                                                title="Aktifkan Kembali Akun"
                                                                onclick="return confirm('Aktifkan kembali akun mahasiswa {{ $mhsUser->name }}?')">
                                                            <i class="bi bi-person-check me-1"></i> Aktifkan
                                                        </button>
                                                    @endif
                                                </form>
                                            </div>

                                            <!-- Modal Edit User Mahasiswa -->
                                            <div class="modal fade text-start" id="modalEditUser-{{ $mhsUser->id }}" tabindex="-1" aria-labelledby="modalEditUserLabel-{{ $mhsUser->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow">
                                                        <div class="modal-header border-bottom py-3">
                                                            <h6 class="modal-title fw-bold text-dark" id="modalEditUserLabel-{{ $mhsUser->id }}">
                                                                <i class="bi bi-pencil-square text-primary me-2"></i>Edit Akun User Mahasiswa
                                                            </h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('pengaturan.users.update', $mhsUser->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body p-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold" style="font-size: 0.82rem;">Nama Mahasiswa</label>
                                                                    <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $mhsUser->name) }}" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold" style="font-size: 0.82rem;">Username / NIM</label>
                                                                    <input type="text" name="username" class="form-control form-control-sm" value="{{ old('username', $mhsUser->username) }}" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold" style="font-size: 0.82rem;">Alamat Email</label>
                                                                    <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $mhsUser->email) }}" placeholder="opsional@kampus.ac.id">
                                                                </div>

                                                                <div class="mb-3 p-3 bg-light rounded-3 border">
                                                                    <label class="form-label fw-semibold d-block mb-1" style="font-size: 0.82rem;">
                                                                        <i class="bi bi-key text-primary me-1"></i> Reset Kata Sandi Baru
                                                                    </label>
                                                                    <small class="text-muted d-block mb-2">Kosongkan jika mahasiswa tidak ingin mengubah kata sandi.</small>
                                                                    <input type="password" name="password" class="form-control form-control-sm" placeholder="Masukkan kata sandi baru (min. 4 karakter)">
                                                                </div>

                                                                <div class="mb-2">
                                                                    <label class="form-label fw-semibold" style="font-size: 0.82rem;">Status Akses Akun</label>
                                                                    <div class="d-flex gap-3">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio" name="is_active" id="statusAktif-{{ $mhsUser->id }}" value="1" {{ $mhsUser->isActive() ? 'checked' : '' }}>
                                                                            <label class="form-check-label text-success fw-semibold small" for="statusAktif-{{ $mhsUser->id }}">
                                                                                <i class="bi bi-check-circle me-1"></i> Aktif (Bisa Login & Pinjam)
                                                                            </label>
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio" name="is_active" id="statusNonaktif-{{ $mhsUser->id }}" value="0" {{ !$mhsUser->isActive() ? 'checked' : '' }}>
                                                                            <label class="form-check-label text-danger fw-semibold small" for="statusNonaktif-{{ $mhsUser->id }}">
                                                                                <i class="bi bi-slash-circle me-1"></i> Nonaktif (Ditangguhkan)
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light border-top py-2">
                                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-sm btn-primary-dark px-3">
                                                                    <i class="bi bi-check-lg me-1"></i> Simpan Perubahan Akun
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-person-x fs-3 d-block mb-2 text-secondary"></i>
                                            Tidak ditemukan akun mahasiswa{{ !empty($searchUser) ? ' dengan kata kunci "' . $searchUser . '"' : '' }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($userMahasiswa->hasPages())
                        <div class="mt-3 pt-2 border-top">
                            {{ $userMahasiswa->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 3: NOTIFIKASI                                              -->
    <!-- ============================================================== -->
    <div class="tab-pane fade {{ $activeTab === 'notifikasi' ? 'show active' : '' }}" id="pane-notifikasi" role="tabpanel" aria-labelledby="tab-notifikasi-btn">
        <div class="card p-3 p-md-4 shadow-sm border-0" style="max-width: 800px; border-radius: 12px;">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-bell text-primary me-2"></i>Preferensi Notifikasi & Peringatan Sistem
                </h6>
                <span class="badge bg-light text-secondary border">Real-Time Alerts</span>
            </div>

            <form action="{{ route('pengaturan.notifikasi') }}" method="POST">
                @csrf

                <div class="list-group list-group-flush mb-4">
                    <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fw-bold text-dark d-block">Notifikasi Pengajuan Peminjaman Baru (Menunggu ACC)</span>
                            <small class="text-muted">Menampilkan badge merah indikator di navbar saat ada mahasiswa mengajukan pinjaman alat baru.</small>
                        </div>
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" name="notif_pengajuan_baru" value="1" {{ ($settings['notif_pengajuan_baru'] ?? '1') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fw-bold text-dark d-block">Peringatan Jatuh Tempo & Keterlambatan</span>
                            <small class="text-muted">Memberikan tanda peringatan visual pada dashboard ketika terdapat alat yang belum dikembalikan melewati jadwal jatuh tempo.</small>
                        </div>
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" name="notif_jatuh_tempo" value="1" {{ ($settings['notif_jatuh_tempo'] ?? '1') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fw-bold text-dark d-block">Kalkulasi Otomatis Denda di Kasir Lab</span>
                            <small class="text-muted">Otomatis menghitung denda harian dan biaya unit rusak saat form inspeksi pengembalian dibuka.</small>
                        </div>
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" name="notif_denda" value="1" {{ ($settings['notif_denda'] ?? '1') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>

                    <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="fw-bold text-dark d-block">Peringatan Flash Pop-up (SweetAlert / Toast)</span>
                            <small class="text-muted">Tampilkan pop-up dialog interaktif setiap kali tindakan persetujuan, penolakan, atau simpan data selesai.</small>
                        </div>
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" name="notif_email_alert" value="1" {{ ($settings['notif_email_alert'] ?? '0') == '1' ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary-dark px-4 py-2 rounded-3 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Pengaturan Notifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 4: BACKUP & PEMELIHARAAN SISTEM                            -->
    <!-- ============================================================== -->
    <div class="tab-pane fade {{ $activeTab === 'backup' ? 'show active' : '' }}" id="pane-backup" role="tabpanel" aria-labelledby="tab-backup-btn">
        <div class="d-flex flex-column gap-4" style="max-width: 800px;">
            
            <!-- Card 1: Database Backup -->
            <div class="card p-3 p-md-4 shadow-sm border-0" style="border-radius: 12px;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-database-down text-primary me-2"></i>Cadangan Basis Data (Database Backup)
                    </h6>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                        Status DB: Online (Active)
                    </span>
                </div>

                <p class="text-muted small mb-3">
                    Unduh seluruh skema tabel, data master, serta rekaman transaksi sirkulasi laboratorium ke dalam berkas <code>.sql</code> standar yang dapat di-restore kapan saja di phpMyAdmin atau terminal MySQL.
                </p>

                <!-- Database Metrics Overview -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-3">
                        <div class="p-2 border rounded-3 bg-light text-center">
                            <div class="text-muted" style="font-size: 0.72rem;">Database</div>
                            <div class="fw-bold text-dark small text-truncate">{{ $dbStats['database'] }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2 border rounded-3 bg-light text-center">
                            <div class="text-muted" style="font-size: 0.72rem;">Total Alat Lab</div>
                            <div class="fw-bold text-primary small">{{ $dbStats['total_alat'] }} Instrumen</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2 border rounded-3 bg-light text-center">
                            <div class="text-muted" style="font-size: 0.72rem;">Mahasiswa</div>
                            <div class="fw-bold text-success small">{{ $dbStats['total_mahasiswa'] }} Peminjam</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-2 border rounded-3 bg-light text-center">
                            <div class="text-muted" style="font-size: 0.72rem;">Transaksi</div>
                            <div class="fw-bold text-info small">{{ $dbStats['total_transaksi'] }} Record</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 pt-2 border-top">
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-clock-history me-1"></i> Unduhan Terakhir: <strong>{{ $dbStats['last_backup'] }}</strong>
                    </small>
                    <a href="{{ route('pengaturan.backup.download') }}" class="btn btn-primary-dark px-3 py-2 rounded-3 fw-semibold text-nowrap">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Unduh File Backup (.SQL)
                    </a>
                </div>
            </div>

            <!-- Card 2: Cache & System Maintenance -->
            <div class="card p-3 p-md-4 shadow-sm border-0" style="border-radius: 12px;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-cpu text-primary me-2"></i>Pemeliharaan & Optimasi Cache
                    </h6>
                    <span class="badge bg-light text-secondary border">Maintenance Tools</span>
                </div>

                <p class="text-muted small mb-3">
                    Jika sistem mengalami ketidaksesuaian tampilan antarmuka (*view cache*), sesi yang tertahan, atau konfigurasi lingkungan yang belum sinkron, Anda dapat membersihkan cache internal Laravel secara aman.
                </p>

                <form action="{{ route('pengaturan.clear_cache') }}" method="POST">
                    @csrf
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i> Menghapus Application Cache, Compiled Views, & Config Cache.
                        </span>
                        <button type="submit" class="btn btn-outline-danger px-3 py-2 rounded-3 fw-semibold text-nowrap">
                            <i class="bi bi-trash3 me-1"></i> Bersihkan Cache Sekarang
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>

<!-- Styling for Tab Pills & Form Controls -->
<style>
.btn-tab-pill {
    background-color: #ffffff;
    color: #64748b;
    border: 1px solid #cbd5e1;
    font-size: 0.88rem;
    font-weight: 600;
    padding: 0.45rem 1.1rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.btn-tab-pill:hover {
    background-color: #f8fafc;
    color: #1e293b;
    border-color: #94a3b8;
}
.btn-tab-pill.active {
    background-color: #1e3a8a !important; /* Rich Dark Navy */
    color: #ffffff !important;
    border-color: #1e3a8a !important;
    box-shadow: 0 4px 6px -1px rgba(30, 58, 138, 0.25);
}
.btn-subtab {
    color: #64748b;
    border: 1px solid transparent;
    font-size: 0.82rem;
    transition: all 0.2s ease;
}
.btn-subtab:hover {
    color: #1e293b;
    background-color: #f1f5f9;
}
.btn-subtab.active {
    background-color: #2563eb !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}
.form-control:focus, .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
</style>

<!-- Script to handle URL Hash and Tab Persistence -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if there is a tab or subtab query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const subtabParam = urlParams.get('subtab');
    
    if (tabParam) {
        const targetBtn = document.getElementById('tab-' + tabParam + '-btn');
        if (targetBtn) {
            const tabInstance = new bootstrap.Tab(targetBtn);
            tabInstance.show();
        }
    }

    if (subtabParam) {
        const targetSubBtn = document.getElementById('sub-' + subtabParam + '-btn');
        if (targetSubBtn) {
            const subtabInstance = new bootstrap.Tab(targetSubBtn);
            subtabInstance.show();
        }
    }

    // Update URL query string when tab is clicked
    const tabButtons = document.querySelectorAll('#pengaturanTabs button[data-bs-toggle="pill"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target').replace('#pane-', '');
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', targetId);
            window.history.replaceState({}, '', newUrl);
        });
    });

    // Update URL query string when subtab is clicked
    const subtabButtons = document.querySelectorAll('#subtabAkunGroup button[data-bs-toggle="pill"]');
    subtabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target').replace('#pane-sub-', '');
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('subtab', targetId);
            window.history.replaceState({}, '', newUrl);
        });
    });
});
</script>
@endsection
