<aside id="sidebar" class="sidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-between px-3 py-3 border-bottom border-secondary border-opacity-25">
        <a href="{{ Auth::check() && Auth::user()->isMahasiswa() ? route('mahasiswa.portal.dashboard') : route('dashboard') }}" class="d-flex align-items-center text-decoration-none text-white gap-2">
            <div class="brand-icon rounded-3 d-flex align-items-center justify-content-center bg-primary text-white shadow-sm" style="width: 38px; height: 38px;">
                <i class="bi bi-cpu-fill fs-5"></i>
            </div>
            <div>
                <span class="fs-5 fw-bold tracking-tight text-white mb-0 d-block lh-1">LabTrack</span>
                <small class="text-secondary text-opacity-75" style="font-size: 0.72rem; letter-spacing: 0.5px;">SISTEM LAB TERPADU</small>
            </div>
        </a>
        <button type="button" class="btn btn-sm btn-link text-white-50 d-lg-none p-0" id="sidebarCloseBtn">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <div class="sidebar-content py-3 px-3">
        @if(Auth::check() && Auth::user()->isAdmin())
            <!-- MENU ADMIN / PETUGAS LAB -->
            <div class="sidebar-label text-uppercase text-secondary text-opacity-75 fw-semibold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 1px;">
                Menu Utama Admin
            </div>
            <ul class="nav nav-pills flex-column gap-1 mb-3">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-label text-uppercase text-secondary text-opacity-75 fw-semibold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 1px;">
                Sirkulasi & Persetujuan
            </div>
            <ul class="nav nav-pills flex-column gap-1 mb-3">
                @php
                    $pendingAccCount = \App\Models\Peminjaman::where('status', 'menunggu_acc')->count();
                @endphp
                <li class="nav-item">
                    <a href="{{ route('peminjaman.antrean_acc') }}" class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('peminjaman.antrean_acc') ? 'active' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-inbox-fill text-warning"></i>
                            <span>Antrean ACC</span>
                        </div>
                        @if($pendingAccCount > 0)
                            <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                {{ $pendingAccCount }}
                            </span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('peminjaman.index') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('peminjaman.index') || request()->routeIs('peminjaman.show') || request()->routeIs('peminjaman.create') ? 'active' : '' }}">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Peminjaman Alat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pengembalian.index') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">
                        <i class="bi bi-box-arrow-in-down-left"></i>
                        <span>Pengembalian Alat</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-label text-uppercase text-secondary text-opacity-75 fw-semibold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 1px;">
                Data Master
            </div>
            <ul class="nav nav-pills flex-column gap-1 mb-3">
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.index') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i>
                        <span>Data Mahasiswa</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('alat.index') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('alat.*') ? 'active' : '' }}">
                        <i class="bi bi-tools"></i>
                        <span>Data Alat Lab</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-label text-uppercase text-secondary text-opacity-75 fw-semibold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 1px;">
                Rekapitulasi
            </div>
            <ul class="nav nav-pills flex-column gap-1 mb-3">
                <li class="nav-item">
                    <a href="{{ route('laporan.index') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        <span>Laporan & Denda</span>
                    </a>
                </li>
            </ul>

        @elseif(Auth::check() && Auth::user()->isMahasiswa())
            <!-- MENU MAHASISWA -->
            <div class="sidebar-label text-uppercase text-secondary text-opacity-75 fw-semibold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 1px;">
                Portal Mahasiswa
            </div>
            <ul class="nav nav-pills flex-column gap-1 mb-3">
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.portal.dashboard') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('mahasiswa.portal.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard Saya</span>
                    </a>
                </li>
                @php
                    $mhsUser = Auth::user();
                    $mhsPendingCount = $mhsUser && $mhsUser->mahasiswa_id 
                        ? \App\Models\Peminjaman::where('mahasiswa_id', $mhsUser->mahasiswa_id)->where('status', 'menunggu_acc')->count() 
                        : 0;
                @endphp
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.portal.riwayat') }}" class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('mahasiswa.portal.riwayat') || request()->routeIs('mahasiswa.portal.detail') ? 'active' : '' }}">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-clock-history"></i>
                            <span>Riwayat & Status ACC</span>
                        </div>
                        @if($mhsPendingCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                {{ $mhsPendingCount }} ACC
                            </span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.portal.katalog') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('mahasiswa.portal.katalog') ? 'active' : '' }}">
                        <i class="bi bi-search"></i>
                        <span>Katalog & Stok Alat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.portal.ajukan') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('mahasiswa.portal.ajukan') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle"></i>
                        <span>Ajukan Peminjaman</span>
                    </a>
                </li>
            </ul>

            <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 mb-3 text-white">
                <small class="text-white-50 d-block" style="font-size: 0.72rem;">Profil Mahasiswa:</small>
                <div class="fw-bold text-white fs-6 text-truncate">{{ Auth::user()->name }}</div>
                <small class="text-info font-monospace">{{ Auth::user()->mahasiswa->nim ?? 'NIM Aktif' }}</small>
            </div>
        @else
            <!-- GUEST / BELUM LOGIN -->
            <ul class="nav nav-pills flex-column gap-1 mb-3">
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link d-flex align-items-center gap-3 active">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login Sistem</span>
                    </a>
                </li>
            </ul>
        @endif

        <div class="sidebar-footer-card p-3 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 mt-auto">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                    <i class="bi bi-shield-check me-1"></i>Aturan Terpasang
                </span>
            </div>
            <div class="small text-white-50" style="font-size: 0.75rem;">
                Masa Tenggang: <strong>3 Hari</strong><br>
                Denda: <strong>Rp2.000/hari</strong><br>
                Biaya Rusak: <strong>Rp10.000/unit</strong>
            </div>
        </div>
    </div>
</aside>
