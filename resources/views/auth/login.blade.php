<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun Anda — LabBorrow</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .login-wrapper {
            display: flex;
            width: 100vw;
            min-height: 100vh;
        }

        /* Left Side: Dark Hero with Lab Photo */
        .login-hero {
            flex: 1.1;
            background: linear-gradient(135deg, rgba(16, 27, 43, 0.92) 0%, rgba(22, 36, 56, 0.88) 100%),
                        url('{{ asset("images/lab_login_bg.jpg") }}') center center / cover no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 60px 50px;
            color: #ffffff;
        }

        .hero-flask-icon {
            width: 52px;
            height: 52px;
            border: 2px solid rgba(255, 255, 255, 0.85);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.6;
            max-width: 440px;
        }

        /* Right Side: Clean Form */
        .login-form-side {
            flex: 1;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
        }

        .form-card-container {
            width: 100%;
            max-width: 400px;
        }

        .flask-logo-center {
            width: 52px;
            height: 52px;
            background-color: #ffffff;
            border: 2px solid #111c2e;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #111c2e;
            margin: 0 auto 20px auto;
        }

        .form-heading {
            font-size: 1.45rem;
            font-weight: 700;
            color: #111c2e;
            text-align: center;
            margin-bottom: 6px;
        }

        .form-subheading {
            font-size: 0.86rem;
            color: #64748b;
            text-align: center;
            margin-bottom: 28px;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 18px;
        }

        .input-wrapper label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
            display: block;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-custom i.prefix-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 1rem;
            pointer-events: none;
        }

        .input-group-custom .form-control {
            padding: 10px 14px 10px 42px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #1e293b;
            width: 100%;
            transition: all 0.2s ease;
        }

        .input-group-custom .form-control:focus {
            border-color: #111c2e;
            box-shadow: 0 0 0 3px rgba(17, 28, 46, 0.12);
            outline: none;
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
        }

        .toggle-password-btn:hover {
            color: #475569;
        }

        .btn-login-navy {
            background-color: #111c2e;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 11px;
            border-radius: 8px;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-login-navy:hover {
            background-color: #1a2b47;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(17, 28, 46, 0.2);
        }

        .login-footer-copy {
            margin-top: 40px;
            text-align: center;
            font-size: 0.78rem;
            color: #94a3b8;
        }

        .quick-demo-box {
            margin-top: 24px;
            padding: 12px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
        }

        @media (max-width: 900px) {
            .login-hero {
                display: none;
            }
            .login-form-side {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Sisi Kiri: Hero Banner dengan Foto Lab & Flask Icon -->
        <div class="login-hero">
            <div class="hero-flask-icon">
                <i class="bi bi-funnel"></i>
            </div>
            <h1 class="hero-title">
                Sistem Peminjaman<br>Alat Laboratorium
            </h1>
            <p class="hero-subtitle">
                Kelola peminjaman alat laboratorium dengan mudah, cepat, dan aman.
            </p>
        </div>

        <!-- Sisi Kanan: Form Login Bersih -->
        <div class="login-form-side">
            <div class="form-card-container">
                <!-- Flask Logo Center -->
                <div class="flask-logo-center">
                    <i class="bi bi-funnel"></i>
                </div>

                <h2 class="form-heading">Masuk ke Akun Anda</h2>
                <p class="form-subheading">Silakan login untuk melanjutkan ke sistem.</p>

                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success py-2 px-3 small rounded-3 mb-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <!-- Username -->
                    <div class="input-wrapper">
                        <label for="username">Username</label>
                        <div class="input-group-custom">
                            <i class="bi bi-person prefix-icon"></i>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="input-wrapper">
                        <label for="password">Password</label>
                        <div class="input-group-custom">
                            <i class="bi bi-lock prefix-icon"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password-btn" id="togglePasswordBtn" aria-label="Toggle Password">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login-navy">
                        <span>Login</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <!-- Panel Uji Coba Cepat (UTS Demo Helper) -->
                <div class="quick-demo-box">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <small class="fw-bold text-secondary" style="font-size: 0.72rem;">UJI COBA DEMO CEPAT:</small>
                        <a href="{{ route('register') }}" class="text-decoration-none fw-semibold text-primary" style="font-size: 0.75rem;">
                            Daftar Mahasiswa <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" style="font-size: 0.78rem;" onclick="fillLogin('2', '2')">
                            <i class="bi bi-shield-lock me-1"></i> Admin (2 / 2)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" style="font-size: 0.78rem;" onclick="fillLogin('1', '1')">
                            <i class="bi bi-mortarboard me-1"></i> Mhs (1 / 1)
                        </button>
                    </div>
                </div>

                <div class="login-footer-copy">
                    &copy; 2025 Laboratorium Kampus
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle Show/Hide Password
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        }

        // Quick login fill
        function fillLogin(u, p) {
            document.getElementById('username').value = u;
            document.getElementById('password').value = p;
        }
    </script>
</body>
</html>
