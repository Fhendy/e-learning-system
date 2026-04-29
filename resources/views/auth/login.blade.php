<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - E-Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon/apple-touch-icon.png') }}">
    
    <style>
        /* CSS Variables - Hapus dark mode */
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #e0e7ff;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --border-radius: 16px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            position: relative;
        }

        /* Login Container */
        .login-container {
            display: flex;
            width: 100%;
            max-width: 1100px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }

        /* Left Illustration */
        .login-illustration {
            flex: 1.2;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-illustration::before {
            content: "";
            position: absolute;
            top: -80px;
            right: -80px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }

        .login-illustration::after {
            content: "";
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }

        .illustration-content {
            position: relative;
            z-index: 1;
        }

        .illustration-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            animation: float 3s ease-in-out infinite;
        }

        .illustration-title {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .illustration-subtitle {
            font-size: clamp(0.875rem, 3vw, 1rem);
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        /* Features */
        .features {
            margin-top: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
            padding: 0.5rem;
            border-radius: 12px;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .feature-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .feature-text h5 {
            font-size: 0.938rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .feature-text p {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-bottom: 0;
        }

        /* Right Login Form */
        .login-content {
            flex: 1;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }

        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .logo-icon i {
            font-size: 2rem;
            color: white;
        }

        .logo h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .logo p {
            font-size: 0.813rem;
            color: var(--gray-500);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.813rem;
            color: var(--gray-700);
            margin-bottom: 0.375rem;
        }

        .input-group {
            border-radius: 10px;
            transition: var(--transition);
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .input-group-text {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: var(--gray-500);
        }

        .form-control {
            border: 1px solid var(--gray-200);
            border-left: none;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--gray-200);
            box-shadow: none;
        }

        .form-control:focus + .input-group-text {
            border-color: var(--primary);
        }

        .btn-login {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 10px;
            transition: var(--transition);
            margin-top: 0.5rem;
            color: white;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            transform: none;
        }

        /* Checkbox */
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-input {
            width: 1rem;
            height: 1rem;
            margin-top: 0;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            font-size: 0.813rem;
            color: var(--gray-600);
            cursor: pointer;
        }

        /* Alert */
        .alert {
            border-radius: 10px;
            border: none;
            font-size: 0.813rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .login-footer p {
            font-size: 0.688rem;
            color: var(--gray-500);
            margin: 0;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                max-width: 550px;
            }
            
            .login-illustration {
                padding: 1.5rem;
                text-align: center;
            }
            
            .illustration-icon {
                font-size: 2.5rem;
            }
            
            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 0.75rem;
                margin-top: 1.5rem;
            }
            
            .feature-item {
                margin-bottom: 0;
                flex-direction: column;
                text-align: center;
                padding: 1rem;
            }
            
            .feature-icon {
                margin-bottom: 0.5rem;
            }
            
            .login-content {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 0.75rem;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
            
            .logo-icon {
                width: 56px;
                height: 56px;
            }
            
            .logo-icon i {
                font-size: 1.5rem;
            }
            
            .logo h2 {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 0.5rem;
            }
            
            .login-illustration {
                padding: 1rem;
            }
            
            .illustration-icon {
                font-size: 2rem;
                margin-bottom: 1rem;
            }
            
            .illustration-title {
                font-size: 1.25rem;
            }
            
            .illustration-subtitle {
                font-size: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .feature-item {
                padding: 0.75rem;
            }
            
            .feature-icon {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
            
            .feature-text h5 {
                font-size: 0.813rem;
            }
            
            .feature-text p {
                font-size: 0.688rem;
            }
            
            .login-content {
                padding: 1rem;
            }
            
            .logo-icon {
                width: 48px;
                height: 48px;
            }
            
            .logo-icon i {
                font-size: 1.25rem;
            }
            
            .logo h2 {
                font-size: 1.125rem;
            }
            
            .logo p {
                font-size: 0.688rem;
            }
            
            .btn-login {
                padding: 0.625rem;
                font-size: 0.813rem;
            }
        }

        /* Perbaikan untuk input di mobile */
        @media (max-width: 480px) {
            .form-control,
            .input-group-text {
                font-size: 0.813rem;
                padding: 0.5rem 0.75rem;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
            
            .alert {
                font-size: 0.75rem;
                padding: 0.625rem 0.875rem;
            }
        }

        /* Hilangkan dark mode - tidak ada preferensi dark */
        /* Semua warna tetap terang dan konsisten */
        
        /* Perbaikan untuk touch targets di mobile */
        .btn-login,
        .form-check-input,
        .form-check-label {
            cursor: pointer;
        }
        
        .btn-login:active {
            transform: scale(0.98);
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Illustration -->
        <div class="login-illustration">
            <div class="illustration-content">
                <div class="illustration-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h1 class="illustration-title">E-Learning System</h1>
                <p class="illustration-subtitle">
                    Platform pembelajaran digital untuk meningkatkan kualitas pendidikan
                </p>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="feature-text">
                            <h5>Manajemen Siswa</h5>
                            <p>Kelola data siswa dengan mudah</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="feature-text">
                            <h5>Kelola Tugas</h5>
                            <p>Beri dan nilai tugas siswa</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                        <div class="feature-text">
                            <h5>Absensi Digital</h5>
                            <p>Absen secara online</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Login Form -->
        <div class="login-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h2>Selamat Datang Kembali</h2>
                <p>Silakan login untuk melanjutkan</p>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required 
                               placeholder="nama@email.com" autocomplete="email" autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required placeholder="Masukkan password" autocomplete="current-password">
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login w-100" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
                
                <div class="login-footer">
                    <p>© 2025 E-Learning System. Developed by Fhendy.</p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const loginBtn = document.getElementById('loginBtn');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            // Perbaikan untuk mobile - disable zoom on focus untuk input (optional)
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                // Hapus zoom pada iOS saat focus
                if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                    input.style.fontSize = '16px';
                }
            });
            
            // Input focus effects (dengan performa lebih baik)
            const inputGroups = document.querySelectorAll('.input-group');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.01)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Form validation
            if (form && loginBtn) {
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    // Reset error states
                    emailInput.classList.remove('is-invalid');
                    passwordInput.classList.remove('is-invalid');
                    
                    // Email validation
                    if (!emailInput.value.trim()) {
                        emailInput.classList.add('is-invalid');
                        isValid = false;
                    } else if (!/^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/.test(emailInput.value)) {
                        emailInput.classList.add('is-invalid');
                        isValid = false;
                    }
                    
                    // Password validation
                    if (!passwordInput.value) {
                        passwordInput.classList.add('is-invalid');
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        // Scroll ke error pertama
                        const firstError = document.querySelector('.is-invalid');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstError.focus();
                        }
                        return false;
                    }
                    
                    // Show loading state
                    loginBtn.disabled = true;
                    loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                });
            }
            
            // Remove invalid class on input
            emailInput.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
            
            passwordInput.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
            
            // Perbaikan untuk touch di mobile
            const buttons = document.querySelectorAll('.btn-login, .form-check-input, .form-check-label');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {
                    // Hanya untuk feedback sentuhan
                    this.style.opacity = '0.8';
                });
                
                button.addEventListener('touchend', function() {
                    this.style.opacity = '1';
                });
            });
            
            // Prevent double submission
            let submitted = false;
            if (form) {
                form.addEventListener('submit', function() {
                    if (submitted) {
                        return false;
                    }
                    submitted = true;
                });
            }
        });
    </script>
</body>
</html>