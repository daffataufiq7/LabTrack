<header class="top-navbar">
    <div class="d-flex align-items-center gap-3 flex-grow-1">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggleBtn" type="button" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Search Bar matching design -->
        <div class="navbar-search d-none d-sm-block">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Cari alat, mahasiswa, atau transaksi..." aria-label="Search">
        </div>
    </div>

    <!-- Right Header Profile & Notification -->
    <div class="d-flex align-items-center gap-3">
        <!-- Notification Bell with Dot -->
        @php
            $pendingCount = \App\Models\Peminjaman::where('status', 'menunggu_acc')->count();
        @endphp
        <div class="position-relative">
            <a href="{{ Auth::check() && Auth::user()->isAdmin() ? route('peminjaman.index') : '#' }}" class="btn btn-sm text-secondary p-1 position-relative" title="Notifikasi">
                <i class="bi bi-bell fs-5"></i>
                @if($pendingCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">Notifikasi Baru</span>
                    </span>
                @endif
            </a>
        </div>

        @if(Auth::check())
            <!-- User Profile Avatar & Name -->
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; border: 1px solid #e2e8f0;">
                        <i class="bi {{ Auth::user()->isAdmin() ? 'bi-person-fill' : 'bi-mortarboard-fill' }} fs-6"></i>
                    </div>
                    <div class="d-none d-md-block text-start lh-1">
                        <span class="fw-semibold text-dark d-block" style="font-size: 0.85rem;">
                            {{ Auth::user()->isAdmin() ? 'Admin Lab' : Auth::user()->name }}
                        </span>
                        <small class="text-muted" style="font-size: 0.72rem;">
                            {{ Auth::user()->isAdmin() ? 'Administrator' : 'Mahasiswa' }}
                        </small>
                    </div>
                    <i class="bi bi-chevron-down text-muted small ms-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2 mt-2">
                    <li class="px-3 py-1">
                        <span class="d-block fw-bold text-dark small">{{ Auth::user()->name }}</span>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ Auth::user()->username }}</small>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    @if(Auth::user()->isAdmin())
                        <li>
                            <a class="dropdown-item small py-2" href="{{ route('pengaturan.index') }}">
                                <i class="bi bi-gear me-2 text-muted"></i> Pengaturan
                            </a>
                        </li>
                    @endif
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item small text-danger py-2">
                                <i class="bi bi-box-arrow-right me-2"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn-primary-dark">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Login</span>
            </a>
        @endif
    </div>
</header>
