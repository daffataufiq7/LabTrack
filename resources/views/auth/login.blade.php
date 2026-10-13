<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem — LabTrack Laboratorium</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-container {
            max-width: 440px;
            width: 100%;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        .auth-header {
            padding: 28px 28px 20px 28px;
            border-bottom: 1px solid #e2e8f0;
            background-color: #ffffff;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .auth-body {
            padding: 28px;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-control {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 9px 13px;
            font-size: 0.925rem;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn-primary-custom {
            background-color: #1e40af;
            border-color: #1e40af;
            color: #ffffff;
            font-weight: 600;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.925rem;
            width: 100%;
        }

        .btn-primary-custom:hover {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
            color: #ffffff;
        }

        .demo-panel {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 20px;
        }

        .btn-demo-quick {
            font-size: 0.8rem;
            padding: 6px 10px;
            border-radius: 5px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            text-align: left;
            width: 100%;
            transition: background 0.15s;
        }

        .btn-demo-quick:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <!-- Header Portal Akademik -->
        <div class="auth-header text-center">
            <h5 class="fw-bold mb-1 text-dark tracking-tight">LabTrack</h5>
            <p class="text-secondary small mb-0">Sistem Informasi Peminjaman Alat Laboratorium</p>
            <span class="badge text-bg-light border text-secondary mt-2 px-2 py-1" style="font-size: 0.75rem; font-weight: normal;">
                Universitas Negeri Yogyakarta
            </span>
        </div>

        <div class="auth-body">
            <!-- Flash Message -->
            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 small rounded-2 mb-3" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info py-2 px-3 small rounded-2 mb-3" role="alert">
                    {{ session('info') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success py-2 px-3 small rounded-2 mb-3" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Kotak Bantuan Login Demo -->
            <div class="demo-panel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-semibold text-secondary">Akun Demo Pengujian:</span>
                    <span class="text-muted" style="font-size: 0.72rem;">Klik untuk isi</span>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn-demo-quick" id="btnDemoMhs">
                            <span class="fw-semibold d-block">Mahasiswa</span>
                            <span class="text-muted" style="font-size: 0.72rem;">User: 1 | Pass: 1</span>
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn-demo-quick" id="btnDemoAdmin">
                            <span class="fw-semibold d-block">Petugas Lab</span>
                            <span class="text-muted" style="font-size: 0.72rem;">User: 2 | Pass: 2</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Formulir Login -->
            <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                @csrf

                <div class="mb-3">
                    <label for="username" class="form-label">Username atau NIM</label>
                    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" 
                           placeholder="Contoh: 1 atau 22051204001" value="{{ old('username') }}" required autofocus>
                    @error('username')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label mb-0">Kata Sandi</label>
                    </div>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Masukkan kata sandi" required>
                    @error('password')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-secondary" for="remember">
                        Ingat sesi login saya
                    </label>
                </div>

                <button type="submit" class="btn btn-primary-custom mb-3">
                    Masuk ke Sistem
                </button>
            </form>

            <div class="text-center pt-3 border-top">
                <p class="small text-secondary mb-2">Mahasiswa belum memiliki akun?</p>
                <a href="{{ route('register') }}" class="btn btn-sm btn-outline-primary w-100 py-1" style="font-weight: 500;">
                    Daftar Akun Mahasiswa Baru
                </a>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <small class="text-secondary" style="font-size: 0.75rem;">
            Praktikum Manajemen Sistem Informasi &bull; UTS 2026
        </small>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userInput = document.getElementById('username');
    const passInput = document.getElementById('password');
    const form = document.getElementById('loginForm');

    document.getElementById('btnDemoMhs').addEventListener('click', function () {
        userInput.value = '1';
        passInput.value = '1';
        form.submit();
    });

    document.getElementById('btnDemoAdmin').addEventListener('click', function () {
        userInput.value = '2';
        passInput.value = '2';
        form.submit();
    });
});
</script>

</body>
</html>
