@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>Edit Profil
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Terdapat kesalahan:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('tab', 'profile') == 'profile' ? 'active' : '' }}" 
                                    id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" 
                                    type="button" role="tab">
                                <i class="bi bi-person me-2"></i>Informasi Profil
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('tab') == 'password' ? 'active' : '' }}" 
                                    id="password-tab" data-bs-toggle="tab" data-bs-target="#password" 
                                    type="button" role="tab">
                                <i class="bi bi-key me-2"></i>Ubah Password
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="profileTabsContent">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade {{ session('tab', 'profile') == 'profile' ? 'show active' : '' }}" 
                             id="profile" role="tabpanel">
                            <form action="{{ route('profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">No. Telepon</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <input type="text" class="form-control" 
                                               value="{{ ucfirst($user->role) }}" disabled readonly>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label for="address" class="form-label">Alamat</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                                  id="address" name="address" rows="3">{{ old('address', $user->address ?? '') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('home') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Password Tab -->
                        <div class="tab-pane fade {{ session('tab') == 'password' ? 'show active' : '' }}" 
                             id="password" role="tabpanel">
                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    <small class="text-muted">Minimal 8 karakter</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('home') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-key me-2"></i>Ubah Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Information -->
            <div class="card shadow mt-4">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Informasi Akun
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">ID Pengguna</label>
                                <p class="fw-bold">{{ $user->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Tanggal Daftar</label>
                                <p class="fw-bold">{{ $user->created_at->format('d F Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Terakhir Login</label>
                                <p class="fw-bold">
                                    @if($user->last_login_at)
                                        {{ \Carbon\Carbon::parse($user->last_login_at)->format('d F Y H:i') }}
                                    @else
                                        Belum pernah login
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Status Akun</label>
                                <p>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Belum diverifikasi
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    color: #6c757d;
    font-weight: 500;
    border: none;
    padding: 10px 20px;
}
.nav-tabs .nav-link.active {
    color: #4e73df;
    background-color: transparent;
    border-bottom: 3px solid #4e73df;
}
.nav-tabs .nav-link:hover {
    color: #4e73df;
    border-color: transparent;
}
.tab-content {
    padding-top: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set active tab based on session or URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    if (tabParam) {
        const tab = document.querySelector(`[data-bs-target="#${tabParam}"]`);
        if (tab) {
            new bootstrap.Tab(tab).show();
        }
    }
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            if (alert.classList.contains('show')) {
                new bootstrap.Alert(alert).close();
            }
        });
    }, 5000);
});
</script>
@endsection