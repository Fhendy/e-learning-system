<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Learning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h2 {
            color: #4e73df;
            font-weight: 700;
        }
        .logo i {
            font-size: 3rem;
            color: #4e73df;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="logo">
            <i class="bi bi-mortarboard-fill"></i>
            <h2>Daftar Akun Baru</h2>
            <p class="text-muted">Buat akun untuk mengakses sistem</p>
        </div>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="{{ old('name') }}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="{{ old('email') }}" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="password_confirmation" 
                           name="password_confirmation" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nis_nip" class="form-label">NIS/NIP</label>
                    <input type="text" class="form-control" id="nis_nip" name="nis_nip" 
                           value="{{ old('nis_nip') }}" required>
                    <small class="text-muted">Nomor induk siswa/nomor induk pegawai</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Peran</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="">Pilih Peran</option>
                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Siswa</option>
                        <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Guru</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">
                        Saya menyetujui syarat dan ketentuan
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-person-plus"></i> Daftar
            </button>
            
            <div class="text-center mt-3">
                <p class="text-muted">Sudah punya akun? 
                    <a href="{{ route('login') }}">Login disini</a>
                </p>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>
</html>