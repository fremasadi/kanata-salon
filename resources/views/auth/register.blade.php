<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Salon Management</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans&display=swap" rel="stylesheet" />

    <!-- Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Public Sans', sans-serif;
            background-color: #f5f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .authentication-wrapper { width: 100%; padding: 1.5rem; }
        .authentication-inner { max-width: 400px; margin: 0 auto; }
        .card { border: none; border-radius: 0.5rem; box-shadow: 0 2px 6px rgba(67, 89, 113, 0.12); }
        .card-body { padding: 2rem; }
        h4 { color: #566a7f; font-weight: 600; margin-bottom: 0.5rem; }
        .form-label { color: #566a7f; font-size: 0.8125rem; font-weight: 500; margin-bottom: 0.5rem; }
        .form-control { border: 1px solid #d9dee3; border-radius: 0.375rem; padding: 0.4375rem 0.875rem; }
        .form-control.is-invalid { border-color: #ff3e1d; }
        .invalid-feedback { display: block; color: #ff3e1d; font-size: 0.8125rem; margin-top: 0.25rem; }
        .btn-primary { background-color: #e30083; border-color: #e30083; color: #fff; font-weight: 500; padding: 0.5rem 1rem; border-radius: 0.375rem; }
        .btn-primary:hover { background-color: #c70073; border-color: #c70073; }
    </style>
</head>
<body>
<div class="authentication-wrapper">
    <div class="authentication-inner">
        <div class="card">
            <div class="card-body">
                <h4>Daftar Akun Baru</h4>
                <p class="text-muted mb-4">Silakan isi data Anda untuk membuat akun</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- No HP (pelanggan) -->
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">Nomor HP</label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" class="form-control @error('no_hp') is-invalid @enderror" required>
                        @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Daftar</button>
                    </div>

                    <p class="mt-3 text-center small">
                        Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
