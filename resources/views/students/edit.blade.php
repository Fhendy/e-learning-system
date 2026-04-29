@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Edit Data Siswa</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-person-badge me-1"></i>{{ $student->nis_nip }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
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

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil-square me-2"></i>
                        Form Edit Siswa
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('students.update', $student) }}" id="studentForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="mb-3 pb-2 border-bottom">
                                        <i class="bi bi-person-badge me-2 text-primary"></i>
                                        Data Pribadi
                                    </h6>
                                    
                                    <!-- Name -->
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $student->name) }}" 
                                               placeholder="Masukkan nama lengkap" required>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- NIS -->
                                    <div class="mb-3">
                                        <label for="nis_nip" class="form-label">NIS <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nis_nip') is-invalid @enderror" 
                                               id="nis_nip" name="nis_nip" value="{{ old('nis_nip', $student->nis_nip) }}" 
                                               placeholder="Contoh: 2024001" required>
                                        @error('nis_nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $student->email) }}" 
                                               placeholder="siswa@example.com" required>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Phone (Optional) -->
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Nomor Telepon (Opsional)</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" value="{{ old('phone', $student->phone ?? '') }}" 
                                               placeholder="08123456789">
                                        @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="mb-3 pb-2 border-bottom">
                                        <i class="bi bi-shield-lock me-2 text-primary"></i>
                                        Keamanan & Status
                                    </h6>

                                    <!-- Password (Optional) -->
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" 
                                               placeholder="Kosongkan jika tidak ingin mengubah">
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Minimal 6 karakter. Kosongkan jika tidak ingin mengubah password.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" 
                                               id="password_confirmation" name="password_confirmation"
                                               placeholder="Ulangi password">
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label">Status</label>
                                        <select class="form-select @error('is_active') is-invalid @enderror" 
                                                id="is_active" name="is_active">
                                            <option value="1" {{ old('is_active', $student->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ old('is_active', $student->is_active) == '0' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                        @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Siswa nonaktif tidak dapat mengakses sistem</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Classes -->
                        <div class="mb-4">
                            <h6 class="mb-3 pb-2 border-bottom">
                                <i class="bi bi-people me-2 text-primary"></i>
                                Pendaftaran Kelas
                            </h6>
                            
                            @if($classes->count() > 0)
                            <div class="row g-3">
                                @foreach($classes as $class)
                                <div class="col-md-4 col-sm-6">
                                    <div class="class-checkbox">
                                        <input class="form-check-input" type="checkbox" 
                                               name="classes[]" value="{{ $class->id }}" 
                                               id="class{{ $class->id }}"
                                               {{ in_array($class->id, $studentClasses) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="class{{ $class->id }}">
                                            <div class="class-info">
                                                <strong>{{ $class->class_name }}</strong>
                                                <small class="text-muted d-block">{{ $class->class_code }}</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Belum ada kelas yang tersedia.
                            </div>
                            @endif
                            @error('classes')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between gap-3 pt-4 border-top">
                            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Update Data
                            </button>
                        </div>
                    </form>
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
    box-shadow: var(--shadow-sm);
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
    padding: 1.25rem;
}

/* Form Styles */
.form-label {
    font-weight: 500;
    font-size: 0.813rem;
    color: #374151;
    margin-bottom: 0.375rem;
}

.form-control,
.form-select {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    font-size: 0.813rem;
    transition: var(--transition);
}

.form-control:focus,
.form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

.form-control::placeholder {
    color: #9ca3af;
}

/* Class Checkbox */
.class-checkbox {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.75rem;
    transition: var(--transition);
    cursor: pointer;
}

.class-checkbox:hover {
    background: #f1f5f9;
    border-color: #4f46e5;
}

.class-checkbox .form-check-input {
    margin-right: 0.5rem;
    margin-top: 0.125rem;
}

.class-checkbox .form-check-label {
    width: calc(100% - 1.5rem);
    cursor: pointer;
}

.class-checkbox .class-info {
    margin-left: 0.5rem;
}

.class-checkbox .class-info strong {
    font-size: 0.875rem;
    color: #1f2937;
}

.class-checkbox .class-info small {
    font-size: 0.688rem;
}

/* Border */
.border-bottom {
    border-bottom: 1px solid #e5e7eb !important;
}

.border-top {
    border-top: 1px solid #e5e7eb !important;
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

.alert-warning {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

/* Text Colors */
.text-primary {
    color: #4f46e5 !important;
}

.text-muted {
    color: #6b7280 !important;
}

.text-danger {
    color: #ef4444 !important;
}

/* Responsive */
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
    
    .class-checkbox {
        padding: 0.5rem;
    }
}

@media (max-width: 576px) {
    .card-body {
        padding: 0.875rem;
    }
    
    .btn {
        padding: 0.375rem 0.75rem;
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
    const form = document.getElementById('studentForm');
    const submitBtn = document.getElementById('submitBtn');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    
    // Form submission handler
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Basic validation
            const name = document.getElementById('name').value.trim();
            const nis = document.getElementById('nis_nip').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = passwordInput.value;
            const passwordConfirm = passwordConfirmInput.value;
            
            if (name.length < 3) {
                e.preventDefault();
                alert('Nama lengkap minimal 3 karakter');
                return;
            }
            
            if (nis.length < 3) {
                e.preventDefault();
                alert('NIS minimal 3 karakter');
                return;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Email tidak valid');
                return;
            }
            
            // Only validate password if it's filled
            if (password) {
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password minimal 6 karakter');
                    return;
                }
                
                if (password !== passwordConfirm) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak sama');
                    return;
                }
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        });
    }
});
</script>
@endsection