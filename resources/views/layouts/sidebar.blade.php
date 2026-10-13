<aside id="sidebar" class="sidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-between px-3 py-3 border-bottom border-secondary border-opacity-25">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none text-white gap-2">
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
        <div class="sidebar-label text-uppercase text-secondary text-opacity-75 fw-semibold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 1px;">
            Menu Utama
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
            Sirkulasi & Transaksi
        </div>
        <ul class="nav nav-pills flex-column gap-1 mb-3">
            <li class="nav-item">
                <a href="{{ route('peminjaman.index') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
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
            Rekapitulasi
        </div>
        <ul class="nav nav-pills flex-column gap-1 mb-4">
            <li class="nav-item">
                <a href="{{ route('laporan.index') }}" class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Laporan & Denda</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer-card p-3 rounded-3 bg-dark bg-opacity-50 border border-secondary border-opacity-25 mt-auto">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                    <i class="bi bi-shield-check me-1"></i>Lab Aktif
                </span>
            </div>
            <div class="small text-white-50" style="font-size: 0.75rem;">
                Masa Tenggang: <strong>3 Hari</strong><br>
                Denda: <strong>Rp2.000/hari</strong>
            </div>
        </div>
    </div>
</aside>
