@extends('layouts.app')

@section('title', 'Buat Kelas Baru')

@section('content')
<div class="container-fluid px-0 px-md-3">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
            <div>
                <h1 class="page-title">Buat Kelas Baru</h1>
                <p class="page-subtitle text-muted">
                    Kelola pembelajaran dengan lebih terstruktur
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
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

<div class="row g-3 g-md-4">
    <div class="col-12 col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-plus-circle me-2"></i>
                    Form Kelas Baru
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('classes.store') }}" method="POST" id="classForm">
                    @csrf
                    
                    <div class="row g-3 g-md-4">
                        <div class="col-12 col-md-6">
                                <div class="mb-4">
                                    <h6 class="mb-3 border-bottom pb-2">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Informasi Kelas
                                    </h6>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('class_name') is-invalid @enderror"
                                               id="class_name" name="class_name"
                                               value="{{ old('class_name') }}" 
                                               placeholder="Nama Kelas" required>
                                        <label for="class_name" class="text-muted">Nama Kelas *</label>
                                        @error('class_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('class_code') is-invalid @enderror"
                                               id="class_code" name="class_code"
                                               value="{{ old('class_code') }}" 
                                               placeholder="Kode Kelas" required>
                                        <label for="class_code" class="text-muted">Kode Kelas *</label>
                                        @error('class_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted mt-1 d-block">Contoh: XIPA1, XIIS2</small>
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                               id="subject" name="subject"
                                               value="{{ old('subject') }}" 
                                               placeholder="Mata Pelajaran">
                                        <label for="subject" class="text-muted">Mata Pelajaran</label>
                                        @error('subject')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="mb-4">
                                    <h6 class="mb-3 border-bottom pb-2">
                                        <i class="bi bi-calendar me-2"></i>
                                        Periode Akademik
                                    </h6>
                                    
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control @error('academic_year') is-invalid @enderror"
                                               id="academic_year" name="academic_year"
                                               value="{{ old('academic_year') }}" 
                                               placeholder="2024/2025">
                                        <label for="academic_year" class="text-muted">Tahun Ajaran</label>
                                        @error('academic_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-floating mb-3">
                                        <select name="semester" 
                                                class="form-select @error('semester') is-invalid @enderror">
                                            <option value="">Pilih Semester</option>
                                            <option value="ganjil" {{ old('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                            <option value="genap" {{ old('semester') == 'genap' ? 'selected' : '' }}>Genap</option>
                                        </select>
                                        <label for="semester" class="text-muted">Semester</label>
                                        @error('semester')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    @if(auth()->user()->role === 'admin' && isset($teachers))
                                    <div class="form-floating mb-3">
                                        <select name="teacher_id" 
                                                class="form-select @error('teacher_id') is-invalid @enderror"
                                                required>
                                            <option value="">Pilih Guru</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
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
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" 
                                               name="is_active" id="isActive" value="1" checked>
                                        <label class="form-check-label" for="isActive">
                                            <i class="bi bi-power me-2"></i> Aktifkan kelas
                                        </label>
                                        <small class="text-muted d-block mt-1">
                                            Kelas aktif dapat diakses oleh siswa
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-4">
                            <h6 class="mb-3 border-bottom pb-2">
                                <i class="bi bi-text-paragraph me-2"></i>
                                Deskripsi Kelas
                            </h6>
                            
                            <div class="form-floating">
                                <textarea name="description" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          placeholder="Tambahkan deskripsi tentang kelas ini..."
                                          style="height: 100px">{{ old('description') }}</textarea>
                                <label for="description" class="text-muted">Deskripsi (Opsional)</label>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 pt-4 border-top">
                        <button type="reset" class="btn btn-outline-secondary order-2 order-sm-1">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary order-1 order-sm-2">
                            <i class="bi bi-check-circle me-2"></i>Buat Kelas
                        </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate class code from class name
    const classCodeInput = document.getElementById('class_code');
    const classNameInput = document.getElementById('class_name');
    
    classNameInput.addEventListener('blur', function() {
        if (!classCodeInput.value && this.value) {
            generateClassCode(this.value);
        }
    });
    
    function generateClassCode(className) {
        // Remove non-alphanumeric characters and convert to uppercase
        let code = className
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '');
        
        // Take first 8 characters
        code = code.substring(0, 8);
        
        if (code.length >= 3) {
            classCodeInput.value = code;
        }
    }
    
    // Auto uppercase class code
    classCodeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Form submission loading state
    const form = document.getElementById('classForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Membuat...';
        
        // Basic validation
        const className = document.getElementById('class_name').value.trim();
        const classCode = document.getElementById('class_code').value.trim();
        
        if (className.length < 3) {
            e.preventDefault();
            alert('Nama kelas harus minimal 3 karakter');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Buat Kelas';
            return;
        }
        
        if (classCode.length < 3) {
            e.preventDefault();
            alert('Kode kelas harus minimal 3 karakter');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Buat Kelas';
            return;
        }
    });
});
</script>

<style>
.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-floating .form-control {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.form-floating .form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
    cursor: pointer;
}

.form-switch .form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.page-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}
</style>
@endsection