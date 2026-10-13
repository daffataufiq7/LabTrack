<nav class="navbar navbar-expand navbar-light bg-white border-bottom sticky-top px-3 py-2">
    <div class="container-fluid px-0">
        <button class="btn btn-sm btn-outline-secondary d-lg-none me-3" id="sidebarToggleBtn" type="button" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-5"></i>
        </button>

        <div class="navbar-breadcrumb d-none d-sm-block">
            <h6 class="mb-0 fw-bold text-dark">@yield('page_title', 'Sistem Manajemen Laboratorium')</h6>
            <small class="text-muted" style="font-size: 0.75rem;">@yield('page_subtitle', 'Pengelolaan sirkulasi alat dan logistik lab')</small>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1 shadow-sm px-3">
                <i class="bi bi-plus-circle-fill"></i>
                <span class="d-none d-md-inline">Peminjaman Baru</span>
            </a>

            <div class="vr mx-2 text-muted opacity-50 d-none d-md-block"></div>

            <div class="d-flex align-items-center gap-2 ps-2">
                <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                    <i class="bi bi-person-badge fs-6"></i>
                </div>
                <div class="d-none d-md-block text-start lh-1">
                    <span class="fw-semibold text-dark d-block" style="font-size: 0.85rem;">Petugas Lab</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Administrator</small>
                </div>
            </div>
        </div>
    </div>
</nav>
