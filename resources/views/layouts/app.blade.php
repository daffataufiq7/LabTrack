<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LabTrack') — Sistem Peminjaman Alat Laboratorium</title>

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #2563eb;
            --content-bg: #f8fafc;
            --card-border: #e2e8f0;
            --primary-accent: #2563eb;
            --font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--content-bg);
            color: #1e293b;
            min-height: 100vh;
            margin: 0;
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            transition: transform 0.3s ease-in-out;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
        }

        .sidebar .nav-link {
            color: #94a3b8;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 0.65rem 0.9rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #f8fafc;
            background-color: var(--sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--sidebar-active);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
        }

        /* Main Wrapper */
        .main-wrapper {
            flex-grow: 1;
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--content-bg);
            transition: margin-left 0.3s ease-in-out;
        }

        /* Mobile Sidebar Backdrop */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 1035;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar-backdrop.show {
                display: block;
            }
            .main-wrapper {
                margin-left: 0;
            }
        }

        /* Custom Cards & Shadows */
        .card {
            border: 1px solid var(--card-border);
            border-radius: 0.85rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }

        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
        }

        /* Tables */
        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--card-border);
            padding: 0.85rem 1rem;
        }

        .table tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.88rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Status Badges */
        .badge-status {
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            letter-spacing: 0.2px;
        }

        /* Form Controls */
        .form-control, .form-select {
            border-color: #cbd5e1;
            border-radius: 0.55rem;
            font-size: 0.88rem;
            padding: 0.6rem 0.85rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.84rem;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        /* Custom Buttons */
        .btn {
            border-radius: 0.55rem;
            font-weight: 600;
            font-size: 0.86rem;
            padding: 0.55rem 1rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary-accent);
            border-color: var(--primary-accent);
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        /* Empty State */
        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .empty-state-icon {
            font-size: 3.5rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Mobile Backdrop -->
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Navbar -->
        @include('layouts.navbar')

        <!-- Main Content -->
        <main class="flex-grow-1 p-3 p-md-4">
            <div class="container-fluid px-0">
                <!-- Flash Alerts -->
                @include('layouts.alerts')

                <!-- Page Content -->
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <span>&copy; {{ date('Y') }} <strong>LabTrack</strong> — Sistem Peminjaman Alat Laboratorium. UTS Manajemen Sistem Informasi.</span>
                <span class="text-secondary opacity-75">Laravel {{ app()->version() }} &bull; PHP {{ PHP_VERSION }}</span>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Mobile Sidebar Toggle JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const closeBtn = document.getElementById('sidebarCloseBtn');
            const backdrop = document.getElementById('sidebarBackdrop');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
            if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
            if (backdrop) backdrop.addEventListener('click', toggleSidebar);
        });
    </script>
    @stack('scripts')
</body>
</html>
