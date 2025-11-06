<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Salon Management</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    
    <!-- Icons (Boxicons) -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bs-primary: #e30083;
            --bs-primary-rgb: 105, 108, 255;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .authentication-wrapper {
            width: 100%;
            padding: 1.5rem;
        }
        
        .authentication-inner {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
            background: #fff;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .app-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .app-brand-logo {
            width: 30px;
            height: 30px;
            margin-right: 0.5rem;
        }
        
        .app-brand-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: #566a7f;
        }
        
        h4 {
            color: #566a7f;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .text-muted {
            color: #a1acb8 !important;
            font-size: 0.9375rem;
        }
        
        .form-label {
            color: #566a7f;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.4375rem 0.875rem;
            font-size: 0.9375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
        }
        
        .form-control.is-invalid {
            border-color: #ff3e1d;
            background-image: none;
        }
        
        .invalid-feedback {
            display: block;
            color: #ff3e1d;
            font-size: 0.8125rem;
            margin-top: 0.25rem;
        }
        
        .form-password-toggle {
            position: relative;
        }
        
        .input-group-text {
            background: transparent;
            border: 1px solid #d9dee3;
            border-left: none;
            cursor: pointer;
            padding: 0.4375rem 0.875rem;
        }
        
        .input-group-text:hover {
            background-color: #f8f9fa;
        }
        
        .input-group .form-control {
            border-right: none;
        }
        
        .form-check-input {
            width: 1.125rem;
            height: 1.125rem;
            border: 1px solid #d9dee3;
            margin-top: 0.125rem;
        }
        
        .form-check-input:checked {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        
        .form-check-label {
            color: #566a7f;
            font-size: 0.9375rem;
            margin-left: 0.25rem;
        }
        
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #fff;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        
        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #e30083;
            border-color: #e30083;
            box-shadow: 0 0.125rem 0.25rem rgba(105, 108, 255, 0.4);
        }
        
        .d-grid {
            display: grid;
        }
        
        a {
            color: var(--bs-primary);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        a:hover {
            color: #e30083;
        }
        
        .alert {
            border-radius: 0.375rem;
            padding: 0.875rem 1rem;
            font-size: 0.9375rem;
        }
        
        .alert-success {
            background-color: #e7f6ec;
            border-color: #28c76f;
            color: #28c76f;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        
        small {
            font-size: 0.8125rem;
        }
    </style>
</head>

<body>
    <div class="container-xxl">
        <div class="authentication-wrapper">
            <div class="authentication-inner">
                <div class="card">
                    <div class="card-body">
                    
                        
                        <h4>Selamat Datang! 👋</h4>
                        <p class="text-muted mb-4">Silakan login ke akun Anda untuk memulai</p>

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="alert alert-success mb-3">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <!-- Email Address -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="Masukkan email Anda" 
                                       required 
                                       autofocus 
                                       autocomplete="username" />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Password -->
                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label" for="password">Password</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}">
                                            <small>Lupa Password?</small>
                                        </a>
                                    @endif
                                </div>
                                <div class="input-group">
                                    <input type="password" 
                                           id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password" 
                                           placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" 
                                           required 
                                           autocomplete="current-password" />
                                    <span class="input-group-text" id="toggle-password">
                                        <i class="bx bx-hide"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Remember Me -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="remember_me" 
                                           name="remember" />
                                    <label class="form-check-label" for="remember_me">
                                        Ingat Saya
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Password Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const icon = togglePassword.querySelector('i');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    icon.classList.toggle('bx-hide');
                    icon.classList.toggle('bx-show');
                });
            }
        });
    </script>
</body>
</html>