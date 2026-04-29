@extends('layouts.app')

@section('title', 'Edit Tugas: ' . $assignment->title)

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
                        <h1 class="page-title mb-1">Edit Tugas</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-journal-text me-1"></i>{{ Str::limit($assignment->title, 40) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-outline-secondary">
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

    @php
        $submitted = $assignment->submissions->count();
        $total = $assignment->class->students->count();
        $percentage = $total > 0 ? round(($submitted / $total) * 100) : 0;
        $isPastDue = \Carbon\Carbon::parse($assignment->due_date)->isPast();
    @endphp

    <div class="row g-3 g-md-4">
        <div class="col-lg-8">
            <!-- Edit Form Card -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        Edit Informasi Tugas
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('assignments.teacher.update', $assignment) }}" method="POST" enctype="multipart/form-data" id="editForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $assignment->title) }}" 
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
                                            <option value="{{ $class->id }}" 
                                                {{ old('class_id', $assignment->class_id) == $class->id ? 'selected' : '' }}>
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
                                      placeholder="Jelaskan tugas dengan jelas, termasuk instruksi dan ketentuan" required>{{ old('description', $assignment->description) }}</textarea>
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
                                           id="due_date" name="due_date" 
                                           value="{{ old('due_date', \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d\TH:i')) }}" required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_score" class="form-label">Nilai Maksimal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('max_score') is-invalid @enderror" 
                                           id="max_score" name="max_score" 
                                           value="{{ old('max_score', $assignment->max_score) }}" 
                                           min="1" max="1000" required>
                                    @error('max_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted small">Nilai maksimal yang bisa diperoleh siswa</div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Attachment -->
                        @if($assignment->attachment)
                        <div class="mb-4">
                            <label class="form-label">Lampiran Saat Ini</label>
                            <div class="current-attachment p-3 border rounded bg-light">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="attachment-icon">
                                        <i class="bi bi-paperclip fs-2 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ basename($assignment->attachment) }}</div>
                                        <div class="text-muted small">File lampiran saat ini</div>
                                    </div>
                                    <div class="btn-group">
                                        <a href="{{ Storage::url($assignment->attachment) }}" 
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="bi bi-eye me-1"></i>Lihat
                                        </a>
                                        <a href="{{ Storage::url($assignment->attachment) }}" 
                                           class="btn btn-sm btn-outline-success" download>
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remove_attachment" name="remove_attachment" value="1">
                                <label class="form-check-label text-danger" for="remove_attachment">
                                    <i class="bi bi-trash me-1"></i>Hapus lampiran ini
                                </label>
                            </div>
                        </div>
                        @endif

                        <!-- New Attachment -->
                        <div class="mb-4">
                            <label for="attachment" class="form-label">
                                @if($assignment->attachment)
                                    Ganti Lampiran
                                @else
                                    Tambah Lampiran (Opsional)
                                @endif
                            </label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror" 
                                   id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.png">
                            @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="text-muted small mt-1">
                                Format: PDF, DOC, DOCX, TXT, JPG, PNG (Max: 2MB)
                                @if($assignment->attachment)
                                    <span class="text-warning">File baru akan menggantikan file lama</span>
                                @endif
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Mengubah batas waktu tidak akan mempengaruhi status pengumpulan yang sudah ada.
                            Siswa yang sudah terlambat akan tetap tercatat sebagai terlambat.
                        </div>

                        <div class="d-flex justify-content-between gap-3 pt-3 border-top">
                            <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="bi bi-trash me-2"></i>Hapus
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Information & Preview -->
        <div class="col-lg-4">
            <!-- Assignment Information -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi Tugas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Status Tugas</div>
                        <div class="info-value">
                            @if($isPastDue)
                                <span class="badge bg-danger">Selesai</span>
                                <div class="text-danger small mt-1">Batas waktu telah lewat</div>
                            @else
                                <span class="badge bg-success">Aktif</span>
                                <div class="text-success small mt-1">
                                    {{ now()->diffForHumans(\Carbon\Carbon::parse($assignment->due_date), true) }} lagi
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Pengumpulan</div>
                        <div class="info-value">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $percentage == 100 ? 'success' : 'info' }}" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="small fw-semibold">{{ $percentage }}%</span>
                            </div>
                            <div class="text-muted small mt-1">
                                {{ $submitted }}/{{ $total }} siswa
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Dibuat</div>
                        <div class="info-value">
                            <div>{{ \Carbon\Carbon::parse($assignment->created_at)->format('d F Y, H:i') }}</div>
                            <div class="text-muted small">Oleh: {{ $assignment->teacher->name }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Terakhir Diubah</div>
                        <div class="info-value">
                            <div>{{ \Carbon\Carbon::parse($assignment->updated_at)->format('d F Y, H:i') }}</div>
                            <div class="text-muted small">{{ \Carbon\Carbon::parse($assignment->updated_at)->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2 text-primary"></i>
                        Aksi Cepat
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('assignments.show', $assignment) }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <div class="action-icon-small bg-info-light text-info">
                                <i class="bi bi-eye"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Lihat Tugas</div>
                                <small class="text-muted">Lihat halaman detail</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        
                        <a href="{{ route('assignments.teacher.create') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <div class="action-icon-small bg-success-light text-success">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Tugas Baru</div>
                                <small class="text-muted">Buat tugas baru</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        
                        <a href="{{ route('assignments.teacher.index') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <div class="action-icon-small bg-primary-light text-primary">
                                <i class="bi bi-list-ul"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Semua Tugas</div>
                                <small class="text-muted">Kembali ke daftar</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="delete-icon mx-auto mb-3">
                    <i class="bi bi-trash3 text-danger fs-1"></i>
                </div>
                <h5 class="mb-2">"{{ $assignment->title }}"</h5>
                
                <div class="alert alert-danger text-start mt-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!
                    <ul class="mt-2 mb-0">
                        <li>Tugas "{{ $assignment->title }}"</li>
                        <li>{{ $submitted }} pengumpulan siswa</li>
                        <li>File lampiran (jika ada)</li>
                        <li>Semua nilai dan feedback</li>
                    </ul>
                </div>
                
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirmDelete">
                    <label class="form-check-label text-danger fw-semibold" for="confirmDelete">
                        Saya mengerti dan ingin menghapus tugas ini
                    </label>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </button>
                <form action="{{ route('assignments.teacher.destroy', $assignment) }}" method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" id="deleteButton" disabled>
                        <i class="bi bi-trash me-2"></i>Hapus Tugas
                    </button>
                </form>
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
    --success-light: #d1fae5;
    --warning: #f59e0b;
    --warning-light: #fef3c7;
    --danger: #ef4444;
    --danger-light: #fee2e2;
    --info: #3b82f6;
    --info-light: #dbeafe;
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

/* Cards */
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

/* Info Item */
.info-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-label {
    font-size: 0.688rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 0.813rem;
    color: #1f2937;
}

/* Progress */
.progress {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 3px;
    transition: width 0.6s ease;
}

/* Current Attachment */
.current-attachment {
    border-left: 3px solid #4f46e5;
    border-radius: 8px;
}

.attachment-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Action Icon Small */
.action-icon-small {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

/* List Group */
.list-group-item {
    border-color: #e5e7eb;
    background: white;
}

.list-group-item-action:hover {
    background: #f8fafc;
}

/* Buttons */
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

.btn-danger {
    background: #ef4444;
    border-color: #ef4444;
}

.btn-danger:hover {
    background: #dc2626;
    border-color: #dc2626;
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

.btn-outline-primary {
    border-color: #e5e7eb;
    color: #4f46e5;
    background: white;
}

.btn-outline-primary:hover {
    background: #4f46e5;
    border-color: #4f46e5;
    color: white;
}

.btn-outline-success {
    border-color: #e5e7eb;
    color: #10b981;
    background: white;
}

.btn-outline-success:hover {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
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

/* Checkbox */
.form-check-input {
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

/* Modal */
.modal-content {
    background: white;
    border: none;
    border-radius: var(--border-radius);
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 1.25rem;
}

.modal-body {
    padding: 1.25rem;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
    padding: 1rem 1.25rem;
}

.delete-icon {
    width: 64px;
    height: 64px;
    background: #fee2e2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Colors */
.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-info-light { background: #dbeafe; }
.bg-warning-light { background: #fef3c7; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-danger { color: #ef4444 !important; }
.text-muted { color: #6b7280 !important; }

/* Border */
.border-top {
    border-top: 1px solid #e5e7eb !important;
}

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
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-group {
        flex-wrap: wrap;
        justify-content: flex-end;
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
    // Description character counter
    const descriptionTextarea = document.getElementById('description');
    const charCounter = document.getElementById('charCounter');
    
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
    
    // Delete confirmation
    const confirmCheckbox = document.getElementById('confirmDelete');
    const deleteButton = document.getElementById('deleteButton');
    
    if (confirmCheckbox && deleteButton) {
        confirmCheckbox.addEventListener('change', function() {
            deleteButton.disabled = !this.checked;
        });
    }
    
    // Form validation
    const form = document.getElementById('editForm');
    const submitBtn = document.getElementById('submitBtn');
    
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

// Show delete modal
function confirmDelete() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection