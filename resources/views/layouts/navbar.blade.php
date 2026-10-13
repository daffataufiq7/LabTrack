<nav class="navbar navbar-expand navbar-light bg-white border-bottom sticky-top px-3 py-2">
    <div class="container-fluid px-0">
        <button class="btn btn-sm btn-outline-secondary d-lg-none me-3" id="sidebarToggleBtn" type="button" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-5"></i>
        </button>

        <div class="navbar-breadcrumb d-none d-sm-block">
            <h6 class="mb-0 fw-bold text-dark">@yield('page_title', 'Sistem Manajemen Laboratorium')</h6>
            <small class="text-muted" style="font-size: 0.75rem;">@yield('page_subtitle', 'Pengelolaan sirkulasi alat dan logistik lab terpadu')</small>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            @if(Auth::check())
                @if(Auth::user()->isAdmin())
                    @php
                        $pendingAccCount = \App\Models\Peminjaman::where('status', 'menunggu_acc')->count();
                    @endphp
                    @if($pendingAccCount > 0)
                        <a href="{{ route('peminjaman.antrean_acc') }}" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 shadow-sm px-2">
                            <i class="bi bi-inbox-fill"></i>
                            <span class="d-none d-md-inline">ACC ({{ $pendingAccCount }})</span>
                        </a>
                    @endif
                    <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1 shadow-sm px-3">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span class="d-none d-md-inline">Peminjaman Baru</span>
                    </a>
                @elseif(Auth::user()->isMahasiswa())
                    <a href="{{ route('mahasiswa.portal.ajukan') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1 shadow-sm px-3">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span class="d-none d-md-inline">Ajukan Pinjam</span>
                    </a>
                @endif

                <div class="vr mx-2 text-muted opacity-50 d-none d-md-block"></div>

                <div class="d-flex align-items-center gap-2 ps-1">
                    <div class="avatar-sm rounded-circle {{ Auth::user()->isAdmin() ? 'bg-primary bg-opacity-10 text-primary' : 'bg-success bg-opacity-10 text-success' }} d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                        <i class="bi {{ Auth::user()->isAdmin() ? 'bi-shield-lock' : 'bi-person-badge' }} fs-6"></i>
                    </div>
                    <div class="d-none d-md-block text-start lh-1">
                        <span class="fw-semibold text-dark d-block text-truncate" style="font-size: 0.85rem; max-width: 140px;">
                            {{ Auth::user()->name }}
                        </span>
                        <small class="text-muted" style="font-size: 0.72rem;">
                            {{ Auth::user()->isAdmin() ? 'Administrator' : 'Mahasiswa (' . (Auth::user()->mahasiswa->nim ?? Auth::user()->username) . ')' }}
                        </small>
                    </div>

                    <!-- Tombol Logout -->
                    <form action="{{ route('logout') }}" method="POST" class="d-inline ms-1">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Keluar dari akun">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-lg-inline">Keluar</span>
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1 shadow-sm px-3">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Masuk / Login</span>
                </a>
            @endif
        </div>
    </div>
</nav>
