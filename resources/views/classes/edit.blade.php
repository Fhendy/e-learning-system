@extends('layouts.app')

@section('title', 'Edit Kelas - ' . $class->class_name)

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
                        <h1 class="page-title mb-1">Edit Kelas</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-hash me-1"></i>{{ $class->class_code }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('classes.show', $class) }}" class="btn btn-outline-secondary">
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
                        Form Edit Kelas
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('classes.update', $class) }}" method="POST" id="editClassForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="mb-3 pb-2 border-bottom">
                                        <i class="bi bi-info-circle me-2 text-primary"></i>
                                        Informasi Dasar
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="class_name" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('class_name') is-invalid @enderror" 
                                               id="class_name" name="class_name" 
                                               value="{{ old('class_name', $class->class_name) }}" 
                                               placeholder="Masukkan nama kelas" required>
                                        @error('class_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="class_code" class="form-label">Kode Kelas <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('class_code') is-invalid @enderror" 
                                               id="class_code" name="class_code" 
                                               value="{{ old('class_code', $class->class_code) }}" 
                                               placeholder="Contoh: XIPA1" required>
                                        @error('class_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Kode kelas akan otomatis uppercase</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Mata Pelajaran</label>
                                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                               id="subject" name="subject" 
                                               value="{{ old('subject', $class->subject) }}" 
                                               placeholder="Contoh: Matematika Wajib">
                                        @error('subject')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="mb-3 pb-2 border-bottom">
                                        <i class="bi bi-gear me-2 text-primary"></i>
                                        Pengaturan
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label for="semester" class="form-label">Semester</label>
                                        <select class="form-select @error('semester') is-invalid @enderror" 
                                                id="semester" name="semester">
                                            <option value="">Pilih Semester</option>
                                            <option value="ganjil" {{ old('semester', $class->semester) == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                            <option value="genap" {{ old('semester', $class->semester) == 'genap' ? 'selected' : '' }}>Genap</option>
                                        </select>
                                        @error('semester')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="academic_year" class="form-label">Tahun Ajaran</label>
                                        <input type="text" class="form-control @error('academic_year') is-invalid @enderror" 
                                               id="academic_year" name="academic_year" 
                                               value="{{ old('academic_year', $class->academic_year ?? $class->school_year) }}"
                                               placeholder="2024/2025">
                                        @error('academic_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    @if(auth()->user()->role === 'admin')
                                    <div class="mb-3">
                                        <label for="teacher_id" class="form-label">Guru Pengampu <span class="text-danger">*</span></label>
                                        <select class="form-select @error('teacher_id') is-invalid @enderror" 
                                                id="teacher_id" name="teacher_id" required>
                                            <option value="">Pilih Guru</option>
                                            @foreach($teachers ?? [] as $teacher)
                                                <option value="{{ $teacher->id }}" 
                                                    {{ old('teacher_id', $class->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->name }} ({{ $teacher->nis_nip }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('teacher_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               name="is_active" id="is_active" value="1"
                                               {{ old('is_active', $class->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="bi bi-power me-2"></i> Status Aktif
                                        </label>
                                        <div class="text-muted small mt-1">Kelas yang tidak aktif tidak akan muncul di daftar</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-4">
                            <h6 class="mb-3 pb-2 border-bottom">
                                <i class="bi bi-text-paragraph me-2 text-primary"></i>
                                Deskripsi
                            </h6>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi Kelas</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" 
                                          placeholder="Deskripsi singkat tentang kelas ini..." 
                                          rows="4">{{ old('description', $class->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between gap-3 pt-4 border-top">
                            <a href="{{ route('classes.show', $class) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
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

textarea.form-control {
    resize: vertical;
}

.form-check-input {
    width: 2.5em;
    height: 1.25em;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.form-check-label {
    font-weight: 500;
    cursor: pointer;
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

/* Border */
.border-bottom {
    border-bottom: 1px solid #e5e7eb !important;
}

.border-top {
    border-top: 1px solid #e5e7eb !important;
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
    // Auto uppercase class code
    const classCodeInput = document.getElementById('class_code');
    if (classCodeInput) {
        classCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    // Form submission handler
    const form = document.getElementById('editClassForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        });
    }
});
</script>
@endsection