<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .login-illustration {
            flex: 1;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-illustration::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .login-illustration::after {
            content: "";
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 250px;
            height: 250px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .login-content {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h2 {
            color: #4e73df;
            font-weight: 700;
            margin-top: 1rem;
        }
        
        .logo i {
            font-size: 3.5rem;
            color: #4e73df;
            background: #f0f4ff;
            padding: 1rem;
            border-radius: 50%;
        }
        
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .forgot-password {
            text-align: right;
            margin-top: 0.5rem;
        }
        
        .forgot-password a {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password a:hover {
            color: #4e73df;
        }
        
        .login-features {
            margin-top: 2rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .feature-icon {
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        /* Responsive styles */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .login-illustration {
                padding: 2rem;
                text-align: center;
            }
            
            .login-content {
                padding: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-illustration, .login-content {
                padding: 1.5rem;
            }
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Bagian ilustrasi/deskripsi di kiri -->
        <div class="login-illustration">
            <div class="d-flex flex-column align-items-center">
                <i class="bi bi-mortarboard-fill mb-4" style="font-size: 4rem; color: rgba(255,255,255,0.9);"></i>
                <h2 class="mb-3">Selamat Datang di</h2>
                <h1 class="mb-4" style="font-weight: 800;">E-Learning System</h1>
                <p class="text-center mb-4" style="font-size: 1.1rem; max-width: 400px;">
                    Platform website yang dibuat oleh @fhendy.hp - @fhdigital.id - @mussidev.id untuk kebutuhan personal guru
                </p>
                
                <div class="login-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                        </div>
                        <div>
                            <h5 class="mb-0">Akses Data Siswa</h5>
                            <p class="mb-0">Mengatur Absensi Siswa</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                        </div>
                        <div>
                            <h5 class="mb-0">Akses Tugas</h5>
                            <p class="mb-0">Memberi tTugas Kepada Siswa</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                        </div>
                        <div>
                            <h5 class="mb-0">Akses Absensi</h5>
                            <p class="mb-0">Absen Secara Digital</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bagian form login di kanan -->
        <div class="login-content">
            <div class="logo">
                <i class="bi bi-mortarboard-fill"></i>
                <h2>Login Akun</h2>
                <p class="text-muted">Masukkan kredensial Anda untuk mengakses sistem</p>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required 
                               placeholder="contoh: nama@email.com">
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required placeholder="Masukkan password">
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3 d-flex justify-content-between">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
                
                <div class="text-center mt-4">
                    <p class="text-muted mb-0">© 2025 E-Learning System. By Fhendy.</p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Tambahkan efek visual pada form
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                // Efek saat input aktif
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Validasi sederhana
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const email = document.getElementById('email');
                const password = document.getElementById('password');
                
                if (!email.value || !password.value) {
                    e.preventDefault();
                    if (!email.value) {
                        email.classList.add('is-invalid');
                    }
                    if (!password.value) {
                        password.classList.add('is-invalid');
                    }
                }
            });
        });
    </script>
</body>
</html>