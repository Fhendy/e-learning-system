@extends('layouts.app')

@section('title', 'Import Data Kelas')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-upload"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Import Data Kelas</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Import data kelas dari file Excel
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Notifikasi -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
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
                        <i class="bi bi-file-earmark-excel me-2 text-success"></i>
                        Import dari Excel
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('classes.import.process') }}" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <!-- Informasi Format File -->
                        <div class="alert alert-info mb-4">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div>
                                    <h6 class="mb-2">Format File:</h6>
                                    <ul class="mb-0 small">
                                        <li>File harus dalam format <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong></li>
                                        <li>Kolom wajib: <strong>Nama Kelas</strong>, <strong>Kode Kelas</strong></li>
                                        <li>Kolom opsional: <strong>Mata Pelajaran</strong>, <strong>Deskripsi</strong>, <strong>Semester</strong>, <strong>Tahun Ajaran</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- File Input -->
                        <div class="mb-4">
                            <label for="file" class="form-label">
                                <i class="bi bi-file-earmark-excel me-2 text-primary"></i>
                                Pilih File Excel
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" 
                                   accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                                   required>
                            @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="text-muted small mt-1">
                                <i class="bi bi-info-circle me-1"></i>
                                Maksimal ukuran file: 5MB
                            </div>
                        </div>

                        <!-- Default Values untuk Semester dan Tahun Ajaran -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="default_semester" class="form-label">
                                    <i class="bi bi-calendar me-2 text-primary"></i>
                                    Default Semester (jika kosong di Excel)
                                </label>
                                <select class="form-select" id="default_semester" name="default_semester">
                                    <option value="">Pilih Semester (Opsional)</option>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                                <div class="text-muted small mt-1">
                                    Akan digunakan jika kolom Semester di Excel kosong
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="default_academic_year" class="form-label">
                                    <i class="bi bi-calendar3 me-2 text-primary"></i>
                                    Default Tahun Ajaran (jika kosong di Excel)
                                </label>
                                <input type="text" class="form-control" id="default_academic_year" name="default_academic_year" 
                                       placeholder="Contoh: 2024/2025" value="{{ date('Y') . '/' . (date('Y') + 1) }}">
                                <div class="text-muted small mt-1">
                                    Akan digunakan jika kolom Tahun Ajaran di Excel kosong
                                </div>
                            </div>
                        </div>

                        <!-- Opsi Import -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-gear me-2 text-primary"></i>
                                Opsi Import
                            </label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skipDuplicates" value="1" checked>
                                <label class="form-check-label" for="skipDuplicates">
                                    Lewati data yang sudah ada (berdasarkan kode kelas)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="auto_activate" id="autoActivate" value="1" checked>
                                <label class="form-check-label" for="autoActivate">
                                    Aktifkan kelas yang diimport secara otomatis
                                </label>
                            </div>
                        </div>

                        <!-- Preview Import -->
                        <div class="mb-4">
                            <div class="alert alert-warning">
                                <div class="d-flex gap-2">
                                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                    <div>
                                        <strong>Perhatian:</strong>
                                        <ul class="mb-0 small mt-1">
                                            <li>Pastikan data sudah benar sebelum import</li>
                                            <li>Kode kelas harus unik</li>
                                            <li>Proses import mungkin memakan waktu beberapa saat</li>
                                            <li>Jangan tutup halaman atau refresh browser selama proses import</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Download Template -->
                        <div class="mb-4">
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <i class="bi bi-download me-2"></i>
                                        <strong>Belum punya template?</strong> Download template Excel di bawah ini:
                                    </div>
                                    <a href="{{ route('classes.import.template') }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-download me-2"></i>Download Template Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between gap-3 pt-3 border-top">
                            <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-upload me-2"></i>Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="mb-2">Sedang Mengimport Data...</h6>
                <p class="text-muted small mb-0">Mohon tunggu, proses import sedang berjalan</p>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Variables */
:root {
    --primary: #4f46e5;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --transition: all 0.2s ease;
}

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
    padding: 1rem;
}

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

.form-check-input {
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
}

.btn-sm {
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
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

.btn-success {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-success:hover {
    background: #0d9e70;
    border-color: #0d9e70;
}

.alert {
    border-radius: 10px;
}

.alert-info {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
}

.alert-warning {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
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

.border-top {
    border-top: 1px solid #e5e7eb !important;
}

.text-primary { color: #4f46e5 !important; }
.text-muted { color: #6b7280 !important; }
.text-success { color: #10b981 !important; }

.modal-content {
    background: white;
    border: none;
    border-radius: var(--border-radius);
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

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
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
}

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
    const form = document.getElementById('importForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    const fileInput = document.getElementById('file');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Silakan pilih file terlebih dahulu');
                return false;
            }
            
            const fileName = fileInput.files[0].name;
            const fileExt = fileName.split('.').pop().toLowerCase();
            
            if (!['xlsx', 'xls', 'csv'].includes(fileExt)) {
                e.preventDefault();
                alert('Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv');
                return false;
            }
            
            const fileSize = fileInput.files[0].size;
            const maxSize = 5 * 1024 * 1024;
            
            if (fileSize > maxSize) {
                e.preventDefault();
                alert('Ukuran file terlalu besar. Maksimal 5MB');
                return false;
            }
            
            loadingModal.show();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
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