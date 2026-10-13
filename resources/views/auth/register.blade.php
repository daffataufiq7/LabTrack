<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Mahasiswa — LabTrack</title>
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
            padding: 30px 16px;
        }

        .auth-container {
            max-width: 520px;
            width: 100%;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .auth-header {
            padding: 24px 28px 18px 28px;
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
            font-size: 0.85rem;
            color: #334155;
            margin-bottom: 5px;
        }

        .form-control, .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .form-control:focus, .form-select:focus {
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
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <!-- Header Registrasi -->
        <div class="auth-header text-center">
            <h5 class="fw-bold mb-1 text-dark tracking-tight">Pendaftaran Akun Mahasiswa</h5>
            <p class="text-secondary small mb-0">LabTrack &bull; Sistem Informasi Sirkulasi Laboratorium</p>
        </div>

        <div class="auth-body">
            @if($errors->any())
                <div class="alert alert-danger py-2 px-3 small rounded-2 mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="nim" class="form-label">Nomor Induk Mahasiswa (NIM) <span class="text-danger">*</span></label>
                        <input type="text" name="nim" id="nim" class="form-control @error('nim') is-invalid @enderror" 
                               value="{{ old('nim') }}" placeholder="Contoh: 22051204099" required autofocus>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" 
                               value="{{ old('nama') }}" placeholder="Nama sesuai KTM" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-6">
                        <label for="program_studi" class="form-label">Program Studi <span class="text-danger">*</span></label>
                        <select name="program_studi" id="program_studi" class="form-select @error('program_studi') is-invalid @enderror" required>
                            <option value="">-- Pilih Program Studi --</option>
                            <option value="Teknik Informatika" {{ old('program_studi') === 'Teknik Informatika' ? 'selected' : '' }}>Teknik Informatika</option>
                            <option value="Sistem Informasi" {{ old('program_studi') === 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                            <option value="Teknologi Informasi" {{ old('program_studi') === 'Teknologi Informasi' ? 'selected' : '' }}>Teknologi Informasi</option>
                            <option value="Pendidikan Teknik Elektronika" {{ old('program_studi') === 'Pendidikan Teknik Elektronika' ? 'selected' : '' }}>Pendidikan Teknik Elektronika</option>
                            <option value="Teknik Elektro" {{ old('program_studi') === 'Teknik Elektro' ? 'selected' : '' }}>Teknik Elektro</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="no_hp" class="form-label">No. WhatsApp / HP <span class="text-danger">*</span></label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" 
                               value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username Login <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" 
                           value="{{ old('username') }}" placeholder="Dapat berupa NIM atau username unik" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Username digunakan untuk masuk ke portal mahasiswa.</small>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6">
                        <label for="password" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Minimal 1 karakter" required>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label for="password_confirmation" class="form-label">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                               placeholder="Konfirmasi kata sandi" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom mb-3">
                    Daftar & Masuk ke Portal Mahasiswa
                </button>
            </form>

            <div class="text-center pt-3 border-top">
                <span class="small text-secondary">Sudah memiliki akun terdaftar?</span>
                <a href="{{ route('login') }}" class="small text-primary fw-semibold text-decoration-none ms-1">
                    Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill username with NIM if user types NIM first and username is empty
document.getElementById('nim').addEventListener('input', function() {
    const userField = document.getElementById('username');
    if (!userField.value || userField.dataset.autofilled === 'true') {
        userField.value = this.value;
        userField.dataset.autofilled = 'true';
    }
});

document.getElementById('username').addEventListener('input', function() {
    this.dataset.autofilled = 'false';
});
</script>

</body>
</html>
