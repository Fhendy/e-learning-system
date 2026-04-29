@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Edit Profil</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Kelola informasi akun Anda
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <div class="row g-3 g-md-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('tab', 'profile') == 'profile' ? 'active' : '' }}" 
                                    id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" 
                                    type="button" role="tab">
                                <i class="bi bi-person me-2"></i>
                                <span>Informasi Profil</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('tab') == 'password' ? 'active' : '' }}" 
                                    id="password-tab" data-bs-toggle="tab" data-bs-target="#password" 
                                    type="button" role="tab">
                                <i class="bi bi-key me-2"></i>
                                <span>Ubah Password</span>
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade {{ session('tab', 'profile') == 'profile' ? 'show active' : '' }}" 
                             id="profile" role="tabpanel">
                            <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', $user->name) }}" 
                                                   placeholder="Masukkan nama lengkap" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" name="email" value="{{ old('email', $user->email) }}" 
                                                   placeholder="nama@email.com" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Nomor Telepon</label>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" 
                                                   placeholder="08123456789">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <input type="text" class="form-control" 
                                                   value="{{ ucfirst($user->role) }}" disabled readonly>
                                            <div class="text-muted small mt-1">Role tidak dapat diubah</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Alamat</label>
                                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                                      id="address" name="address" rows="3" 
                                                      placeholder="Masukkan alamat lengkap">{{ old('address', $user->address ?? '') }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between gap-3 pt-3 border-top">
                                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitProfileBtn">
                                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Password Tab -->
                        <div class="tab-pane fade {{ session('tab') == 'password' ? 'show active' : '' }}" 
                             id="password" role="tabpanel">
                            <form action="{{ route('profile.password') }}" method="POST" id="passwordForm">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" name="current_password" 
                                           placeholder="Masukkan password saat ini" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" name="password" 
                                                   placeholder="Minimal 8 karakter" required>
                                            <div class="text-muted small mt-1">Minimal 8 karakter</div>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" 
                                                   id="password_confirmation" name="password_confirmation" 
                                                   placeholder="Ulangi password baru" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Password baru harus berbeda dari password lama.
                                </div>
                                
                                <div class="d-flex justify-content-between gap-3 pt-3 border-top">
                                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitPasswordBtn">
                                        <i class="bi bi-key me-2"></i>Ubah Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Account Information Card -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi Akun
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">ID Pengguna</div>
                                <div class="info-value">{{ $user->id }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar-plus"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Tanggal Daftar</div>
                                <div class="info-value">{{ $user->created_at->format('d F Y') }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Terakhir Login</div>
                                <div class="info-value">
                                    @if($user->last_login_at)
                                        {{ \Carbon\Carbon::parse($user->last_login_at)->format('d F Y H:i') }}
                                    @else
                                        <span class="text-muted">Belum pernah login</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Status Akun</div>
                                <div class="info-value">
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Terverifikasi
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Belum diverifikasi
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Tips Card -->
            <div class="card mt-3 mt-md-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2 text-primary"></i>
                        Tips Keamanan
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Gunakan password yang kuat
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Jangan bagikan password kepada siapapun
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Update password secara berkala
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Pastikan email Anda aktif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Variables */
:root {
    --primary: #4f46e5;
    --primary-light: #e0e7ff;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --transition: all 0.2s ease;
}

/* Page Header */
.page-header {
    margin-bottom: 1.5rem;
}

.page-icon-large {
    width: clamp(44px, 10vw, 56px);
    height: clamp(44px, 10vw, 56px);
    border-radius: 14px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(1.25rem, 3vw, 1.5rem);
    flex-shrink: 0;
}

.page-title {
    font-size: clamp(1.25rem, 5vw, 1.5rem);
    font-weight: 700;
    color: #1f2937;
}

.page-subtitle {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Card */
.card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.card-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.875rem 1rem;
}

.card-title {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    font-size: 0.938rem;
}

.card-body {
    padding: 1rem;
}

/* Tabs */
.nav-tabs {
    border-bottom: 1px solid #e5e7eb;
    gap: 0.25rem;
    padding: 0 0.5rem;
}

.nav-tabs .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    padding: 0.625rem 1rem;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.813rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.nav-tabs .nav-link:hover {
    color: #4f46e5;
    background: #f9fafb;
}

.nav-tabs .nav-link.active {
    color: #4f46e5;
    background: white;
    border-bottom: 2px solid #4f46e5;
}

/* Form Styles */
.form-label {
    font-weight: 500;
    font-size: 0.813rem;
    color: #374151;
    margin-bottom: 0.375rem;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    font-size: 0.813rem;
    transition: var(--transition);
}

.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

textarea.form-control {
    resize: vertical;
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.info-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f3f4f6;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.info-label {
    font-size: 0.688rem;
    color: #6b7280;
    margin-bottom: 0.125rem;
}

.info-value {
    font-size: 0.813rem;
    font-weight: 500;
    color: #1f2937;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
}

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
}

.btn-outline-secondary {
    border-color: #e5e7eb;
    color: #6b7280;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #374151;
}

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-success {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}

.alert-danger {
    background: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
}

.alert-info {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
}

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.bg-success {
    background: #10b981 !important;
}

.bg-warning {
    background: #f59e0b !important;
}

/* Border */
.border-top {
    border-top: 1px solid #e5e7eb !important;
}

/* Colors */
.text-primary { color: #4f46e5 !important; }
.text-muted { color: #6b7280 !important; }
.text-danger { color: #ef4444 !important; }
.text-success { color: #10b981 !important; }

/* Responsive */
@media (min-width: 992px) {
    .card-body {
        padding: 1.25rem;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .page-icon-large {
        width: 44px;
        height: 44px;
    }
    
    .info-icon {
        width: 28px;
        height: 28px;
    }
    
    .nav-tabs {
        padding: 0;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 0.625rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .info-item {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-icon {
        margin-bottom: 0;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeIn 0.3s ease forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality with localStorage
    const triggerTabList = [].slice.call(document.querySelectorAll('#profileTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
            
            const activeTab = triggerEl.getAttribute('data-bs-target');
            if (activeTab) {
                localStorage.setItem('activeProfileTab', activeTab);
            }
        });
    });
    
    // Restore active tab from localStorage
    const activeTab = localStorage.getItem('activeProfileTab');
    if (activeTab) {
        const triggerEl = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (triggerEl) {
            const tab = bootstrap.Tab.getInstance(triggerEl);
            if (tab) tab.show();
        }
    }
    
    // Profile form submission
    const profileForm = document.getElementById('profileForm');
    const submitProfileBtn = document.getElementById('submitProfileBtn');
    
    if (profileForm && submitProfileBtn) {
        profileForm.addEventListener('submit', function() {
            submitProfileBtn.disabled = true;
            submitProfileBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        });
    }
    
    // Password form submission
    const passwordForm = document.getElementById('passwordForm');
    const submitPasswordBtn = document.getElementById('submitPasswordBtn');
    
    if (passwordForm && submitPasswordBtn) {
        passwordForm.addEventListener('submit', function() {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirmation').value;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak sama');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter');
                return false;
            }
            
            submitPasswordBtn.disabled = true;
            submitPasswordBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection