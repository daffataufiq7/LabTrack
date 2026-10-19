<aside id="sidebar" class="sidebar">
    <!-- Brand Header -->
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <a href="{{ Auth::check() && Auth::user()->isMahasiswa() ? route('mahasiswa.portal.dashboard') : route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-funnel"></i>
            </div>
            <div>
                <span class="sidebar-brand-title">Lab Track</span>
                <span class="sidebar-brand-subtitle text-truncate d-inline-block" style="max-width: 175px;" title="{{ $labSettings['nama_lab'] ?? 'Sistem Peminjaman Alat Laboratorium' }}">{{ $labSettings['nama_lab'] ?? 'Sistem Peminjaman Alat Laboratorium' }}</span>
            </div>
        </a>
        <button type="button" class="btn btn-sm btn-link text-white-50 d-lg-none p-2 me-2" id="sidebarCloseBtn">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <!-- Navigation List -->
    <div class="sidebar-content py-2">
        @if(Auth::check() && Auth::user()->isAdmin())
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.index') }}" class="nav-link {{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Mahasiswa</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('alat.index') }}" class="nav-link {{ request()->routeIs('alat.*') ? 'active' : '' }}">
                        <i class="bi bi-tools"></i>
                        <span>Alat Laboratorium</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('peminjaman.index') }}" class="nav-link {{ request()->routeIs('peminjaman.index') || request()->routeIs('peminjaman.create') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check"></i>
                        <span>Peminjaman</span>
                    </a>
                </li>
                <li class="nav-item">
                    @php
                        $pendingAccCount = \App\Models\Peminjaman::where('status', 'menunggu_acc')->count();
                    @endphp
                    <a href="{{ route('peminjaman.antrean_acc') }}" class="nav-link {{ request()->routeIs('peminjaman.antrean_acc') ? 'active' : '' }} d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-patch-check"></i>
                            <span>Verifikasi (ACC)</span>
                        </div>
                        @if($pendingAccCount > 0)
                            <span class="badge rounded-pill bg-warning text-dark px-2" style="font-size: 0.7rem;">{{ $pendingAccCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pengembalian.index') }}" class="nav-link {{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Pengembalian</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('denda.index') }}" class="nav-link {{ request()->routeIs('denda.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Denda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Laporan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pengaturan.index') }}" class="nav-link {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Pengaturan</span>
                    </a>
                </li>
            </ul>
        @elseif(Auth::check() && Auth::user()->isMahasiswa())
            <!-- Portal Mahasiswa Navigation -->
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.portal.dashboard') }}" class="nav-link {{ request()->routeIs('mahasiswa.portal.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.portal.katalog') }}" class="nav-link {{ request()->routeIs('mahasiswa.portal.katalog') ? 'active' : '' }}">
                        <i class="bi bi-tools"></i>
                        <span>Katalog Alat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.portal.ajukan') }}" class="nav-link {{ request()->routeIs('mahasiswa.portal.ajukan') ? 'active' : '' }}">
                        <i class="bi bi-calendar-plus"></i>
                        <span>Ajukan Pinjam</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mahasiswa.portal.riwayat') }}" class="nav-link {{ request()->routeIs('mahasiswa.portal.riwayat') || request()->routeIs('mahasiswa.portal.detail') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i>
                        <span>Riwayat & Denda</span>
                    </a>
                </li>
            </ul>
        @endif
    </div>
</aside>
