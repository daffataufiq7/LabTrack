<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lab Track') — Sistem Peminjaman Alat Laboratorium</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js for Dashboard & Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --sidebar-bg: #111c2e;
            --sidebar-hover: #182740;
            --sidebar-active: #1d2c45;
            --sidebar-text: #94a3b8;
            --content-bg: #f8fafc;
            --card-border: #e2e8f0;
            --primary-blue: #1e40af;
            --primary-accent: #2563eb;
            --font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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
            width: 250px;
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
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-brand {
            padding: 24px 20px 20px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            border: 2px solid rgba(255, 255, 255, 0.85);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.25rem;
        }

        .sidebar-brand-title {
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            line-height: 1.1;
        }

        .sidebar-brand-subtitle {
            color: #64748b;
            font-size: 0.68rem;
            display: block;
            margin-top: 3px;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.65rem 1rem;
            margin: 2px 14px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: var(--sidebar-active);
            font-weight: 600;
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Wrapper */
        .main-wrapper {
            flex-grow: 1;
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--content-bg);
            transition: margin-left 0.3s ease-in-out;
        }

        /* Top Navbar */
        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--card-border);
            padding: 12px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .navbar-search {
            position: relative;
            max-width: 380px;
            width: 100%;
        }

        .navbar-search input {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            padding: 7px 16px 7px 38px;
            font-size: 0.84rem;
            color: #475569;
            width: 100%;
            transition: all 0.2s ease;
        }

        .navbar-search input:focus {
            background-color: #ffffff;
            border-color: #94a3b8;
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .navbar-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.88rem;
        }

        /* Mobile Sidebar Backdrop */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(17, 28, 46, 0.6);
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

        /* Custom Cards */
        .card {
            border: 1px solid var(--card-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            background-color: #ffffff;
        }

        /* Stat Cards */
        .stat-card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .stat-card-custom:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }

        .stat-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        /* Tables matching design */
        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-custom thead th {
            background-color: #ffffff;
            color: #64748b;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: none;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom tbody td {
            padding: 12px 16px;
            font-size: 0.85rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .table-custom tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Status Pills */
        .badge-pill-status {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-block;
        }

        .badge-pill-success {
            background-color: #dcfce7;
            color: #15803d;
        }

        .badge-pill-info {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-pill-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .badge-pill-warning {
            background-color: #fef3c7;
            color: #b45309;
        }

        /* Action Buttons */
        .btn-action-icon {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-action-icon:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .btn-action-icon.edit-btn:hover {
            border-color: #38bdf8;
            color: #0284c7;
            background-color: #f0f9ff;
        }

        .btn-action-icon.delete-btn:hover {
            border-color: #f87171;
            color: #dc2626;
            background-color: #fef2f2;
        }

        /* Buttons */
        .btn-primary-dark {
            background-color: #1e40af;
            border: 1px solid #1e40af;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.84rem;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary-dark:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(30, 64, 175, 0.2);
        }

        /* Form Controls */
        .form-control, .form-select {
            border-color: #cbd5e1;
            border-radius: 8px;
            font-size: 0.86rem;
            padding: 8px 12px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.15);
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
                <span>&copy; {{ date('Y') }} <strong>Lab Track</strong> — Sistem Peminjaman Alat Laboratorium.</span>
                <span class="text-secondary opacity-75">Laravel {{ app()->version() }} &bull; UTS Praktikum MSI</span>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
