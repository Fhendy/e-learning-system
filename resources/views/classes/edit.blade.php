@extends('layouts.app')

@section('title', 'Edit Kelas - ' . $class->class_name)

@section('content')
<div class="container-fluid px-0 px-md-3">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
            <div>
                <h1 class="page-title">Edit Kelas</h1>
                <p class="page-subtitle text-muted">
                    <i class="bi bi-hash me-1"></i>{{ $class->class_code }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('classes.show', $class) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-6" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-6" role="alert">
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
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Edit Kelas</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('classes.update', $class) }}" method="POST" id="editClassForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="mb-3 border-bottom pb-2">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Informasi Dasar
                                    </h6>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('class_name') is-invalid @enderror" 
                                               id="class_name" name="class_name" 
                                               value="{{ old('class_name', $class->class_name) }}" 
                                               placeholder="Nama Kelas" required>
                                        <label for="class_name" class="text-muted">Nama Kelas *</label>
                                        @error('class_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('class_code') is-invalid @enderror" 
                                               id="class_code" name="class_code" 
                                               value="{{ old('class_code', $class->class_code) }}" 
                                               placeholder="Kode Kelas" required>
                                        <label for="class_code" class="text-muted">Kode Kelas *</label>
                                        @error('class_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted mt-1 d-block">Contoh: MAT-XIPA1-2024</small>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                               id="subject" name="subject" 
                                               value="{{ old('subject', $class->subject) }}" 
                                               placeholder="Mata Pelajaran">
                                        <label for="subject" class="text-muted">Mata Pelajaran</label>
                                        @error('subject')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="mb-3 border-bottom pb-2">
                                        <i class="bi bi-gear me-2"></i>
                                        Pengaturan
                                    </h6>
                                    
                                    <div class="form-floating mb-3">
                                        <select class="form-select @error('semester') is-invalid @enderror" 
                                                id="semester" name="semester">
                                            <option value="">Pilih Semester</option>
                                            <option value="ganjil" {{ old('semester', $class->semester) == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                            <option value="genap" {{ old('semester', $class->semester) == 'genap' ? 'selected' : '' }}>Genap</option>
                                        </select>
                                        <label for="semester" class="text-muted">Semester</label>
                                        @error('semester')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('academic_year') is-invalid @enderror" 
                                               id="academic_year" name="academic_year" 
                                               value="{{ old('academic_year', $class->academic_year ?? $class->school_year) }}"
                                               placeholder="2024/2025">
                                        <label for="academic_year" class="text-muted">Tahun Ajaran</label>
                                        @error('academic_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    @if(auth()->user()->isAdmin())
                                    <div class="form-floating mb-3">
                                        <select class="form-select @error('teacher_id') is-invalid @enderror" 
                                                id="teacher_id" name="teacher_id" required>
                                            <option value="">Pilih Guru</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" 
                                                    {{ old('teacher_id', $class->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->name }} ({{ $teacher->nis_nip }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="teacher_id" class="text-muted">Guru Pengampu *</label>
                                        @error('teacher_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif
                                    
                                    @if($hasIsActive)
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" 
                                               name="is_active" id="is_active" value="1"
                                               {{ old('is_active', $class->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <i class="bi bi-power me-2"></i> Status Aktif
                                        </label>
                                        <small class="text-muted d-block mt-1">Kelas yang tidak aktif tidak akan muncul di daftar</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-4">
                            <h6 class="mb-3 border-bottom pb-2">
                                <i class="bi bi-text-paragraph me-2"></i>
                                Deskripsi
                            </h6>
                            
                            <div class="form-floating">
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" 
                                          placeholder="Deskripsi singkat tentang kelas ini..." 
                                          style="height: 100px">{{ old('description', $class->description) }}</textarea>
                                <label for="description" class="text-muted">Deskripsi Kelas</label>
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
/* Custom styles for edit form */
.form-floating .form-control {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: var(--transition);
}

.form-floating .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-floating > label {
    padding: 1rem 1rem;
    color: #6b7280;
}

.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
    cursor: pointer;
}

.form-switch .form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
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
        form.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        });
    }
});
</script>
@endsection