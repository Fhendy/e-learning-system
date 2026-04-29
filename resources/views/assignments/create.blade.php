@extends('layouts.app')

@section('title', 'Buat Tugas Baru')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Buat Tugas Baru</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Buat tugas baru untuk siswa
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('assignments.teacher.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
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
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-journal-plus me-2 text-primary"></i>
                        Form Buat Tugas
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('assignments.teacher.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" 
                                           placeholder="Masukkan judul tugas" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-select @error('class_id') is-invalid @enderror" 
                                            id="class_id" name="class_id" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->class_name }} ({{ $class->class_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Tugas <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="6" 
                                      placeholder="Jelaskan tugas dengan jelas, termasuk instruksi dan ketentuan" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="text-muted small mt-1" id="charCounter">0 karakter</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Batas Waktu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_score" class="form-label">Nilai Maksimal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('max_score') is-invalid @enderror" 
                                           id="max_score" name="max_score" value="{{ old('max_score', 100) }}" 
                                           min="1" max="1000" required>
                                    @error('max_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted small">Nilai maksimal yang bisa diperoleh siswa</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="attachment" class="form-label">File Lampiran (Opsional)</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror" 
                                   id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.png">
                            @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="text-muted small mt-1">
                                Format: PDF, DOC, DOCX, TXT, JPG, PNG (Max: 2MB)
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Informasi:</strong> Tugas yang dibuat akan langsung terlihat oleh siswa di kelas yang dipilih.
                        </div>

                        <div class="d-flex justify-content-between gap-3 pt-3 border-top">
                            <a href="{{ route('assignments.teacher.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save me-2"></i>Simpan Tugas
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

.form-control::placeholder {
    color: #9ca3af;
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

/* Border */
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
    const form = document.getElementById('createForm');
    const submitBtn = document.getElementById('submitBtn');
    const descriptionTextarea = document.getElementById('description');
    const charCounter = document.getElementById('charCounter');
    
    // Description character counter
    if (descriptionTextarea && charCounter) {
        const updateCharCount = function() {
            const charCount = this.value.length;
            charCounter.textContent = charCount + ' karakter';
            if (charCount > 5000) {
                charCounter.classList.add('text-danger');
            } else {
                charCounter.classList.remove('text-danger');
            }
        };
        
        descriptionTextarea.addEventListener('input', updateCharCount);
        updateCharCount.call(descriptionTextarea);
    }
    
    // Form validation
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            const dueDate = new Date(document.getElementById('due_date').value);
            const now = new Date();
            
            if (dueDate < now) {
                e.preventDefault();
                alert('Batas waktu tidak boleh kurang dari waktu sekarang');
                return false;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        });
    }
});
</script>
@endsection