@extends('layouts.app')

@section('title', 'Edit Tugas: ' . $assignment->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Tugas</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.teacher') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.teacher.index') }}">Tugas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.show', $assignment) }}">{{ Str::limit($assignment->title, 20) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Edit Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Informasi Tugas</h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading">Terjadi kesalahan:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('assignments.teacher.update', $assignment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Tugas *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $assignment->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Kelas *</label>
                                    <select class="form-control @error('class_id') is-invalid @enderror" 
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

                        <div class="mb-4">
                            <label for="description" class="form-label">Deskripsi Tugas *</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="6" required>{{ old('description', $assignment->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Jelaskan tugas dengan jelas, termasuk instruksi dan ketentuan.
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Batas Waktu *</label>
                                    <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" name="due_date" 
                                           value="{{ old('due_date', $assignment->due_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_score" class="form-label">Nilai Maksimal *</label>
                                    <input type="number" class="form-control @error('max_score') is-invalid @enderror" 
                                           id="max_score" name="max_score" 
                                           value="{{ old('max_score', $assignment->max_score) }}" 
                                           min="1" max="1000" required>
                                    @error('max_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Nilai maksimal yang bisa diperoleh siswa</div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Attachment -->
                        @if($assignment->attachment)
                        <div class="mb-4">
                            <label class="form-label">Lampiran Saat Ini</label>
                            <div class="current-attachment p-3 border rounded bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="attachment-icon me-3">
                                        <i class="bi bi-paperclip fa-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ basename($assignment->attachment) }}</div>
                                        <div class="text-muted small">File lampiran saat ini</div>
                                    </div>
                                    <div>
                                        <a href="{{ Storage::url($assignment->attachment) }}" 
                                           class="btn btn-sm btn-outline-primary me-2" target="_blank">
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
                            <div class="form-text">
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

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="bi bi-trash me-2"></i>Hapus Tugas
                                </button>
                                <button type="submit" class="btn btn-primary">
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
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Tugas</h6>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <div class="fw-bold text-primary mb-1">Status Tugas</div>
                        <div>
                            @if($assignment->isPastDue())
                                <span class="badge bg-danger">Selesai</span>
                                <div class="text-danger small mt-1">
                                    Batas waktu telah lewat
                                </div>
                            @else
                                <span class="badge bg-success">Aktif</span>
                                <div class="text-success small mt-1">
                                    {{ now()->diffForHumans($assignment->due_date, true) }} lagi
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="fw-bold text-primary mb-1">Pengumpulan</div>
                        <div>
                            @php
                                $submitted = $assignment->submissions->count();
                                $total = $assignment->class->students->count();
                                $percentage = $total > 0 ? round(($submitted / $total) * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $percentage == 100 ? 'success' : 'info' }}" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="small">{{ $percentage }}%</span>
                            </div>
                            <div class="text-muted small mt-1">
                                {{ $submitted }}/{{ $total }} siswa
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="fw-bold text-primary mb-1">Dibuat</div>
                        <div class="text-muted">
                            {{ $assignment->created_at->format('d F Y, H:i') }}
                            <div class="small">Oleh: {{ $assignment->teacher->name }}</div>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <div class="fw-bold text-primary mb-1">Terakhir Diubah</div>
                        <div class="text-muted">
                            {{ $assignment->updated_at->format('d F Y, H:i') }}
                            <div class="small">{{ $assignment->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('assignments.show', $assignment) }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="action-icon bg-info text-white rounded-circle me-3">
                                <i class="bi bi-eye"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Lihat Tugas</div>
                                <small class="text-muted">Lihat halaman detail</small>
                            </div>
                        </a>
                        
                        <a href="{{ route('assignments.teacher.create') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="action-icon bg-success text-white rounded-circle me-3">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Tugas Baru</div>
                                <small class="text-muted">Buat tugas baru</small>
                            </div>
                        </a>
                        
                        <a href="{{ route('assignments.teacher.index') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="action-icon bg-primary text-white rounded-circle me-3">
                                <i class="bi bi-list-ul"></i>
                            </div>
                            <div>
                                <div class="fw-bold">Semua Tugas</div>
                                <small class="text-muted">Kembali ke daftar</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">PERINGATAN: Tindakan ini tidak dapat dibatalkan!</h6>
                    <p class="mb-0">Anda akan menghapus tugas: <strong>"{{ $assignment->title }}"</strong></p>
                </div>
                
                <div class="delete-details">
                    <h6>Data yang akan dihapus:</h6>
                    <ul class="text-danger">
                        <li>Tugas "{{ $assignment->title }}"</li>
                        <li>{{ $assignment->submissions->count() }} pengumpulan siswa</li>
                        <li>File lampiran (jika ada)</li>
                        <li>Semua nilai dan feedback</li>
                    </ul>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                    <label class="form-check-label text-danger fw-bold" for="confirmDelete">
                        Saya mengerti dan ingin menghapus tugas ini
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </button>
                <form action="{{ route('assignments.teacher.destroy', $assignment) }}" method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="deleteButton" disabled>
                        <i class="bi bi-trash me-2"></i>Hapus Tugas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.attachment-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.action-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.info-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}
.info-item:last-child {
    border-bottom: none;
}
.current-attachment {
    border-left: 4px solid #4e73df;
}
.delete-details {
    background-color: #fff5f5;
    border: 1px solid #f8d7da;
    border-radius: 5px;
    padding: 15px;
    margin: 15px 0;
}
.delete-details ul {
    margin-bottom: 0;
}
</style>

<script>
// Live Preview
document.addEventListener('DOMContentLoaded', function() {
    // Title preview
    const titleInput = document.getElementById('title');
    const titlePreview = document.getElementById('titlePreview');
    
    if (titleInput && titlePreview) {
        titleInput.addEventListener('input', function() {
            titlePreview.textContent = this.value || '{{ $assignment->title }}';
            titlePreview.classList.add('bg-warning', 'bg-opacity-10');
            setTimeout(() => {
                titlePreview.classList.remove('bg-warning', 'bg-opacity-10');
            }, 1000);
        });
    }
    
    // Due date preview
    const dueDateInput = document.getElementById('due_date');
    const dueDatePreview = document.getElementById('dueDatePreview');
    
    if (dueDateInput && dueDatePreview) {
        dueDateInput.addEventListener('change', function() {
            if (this.value) {
                const date = new Date(this.value);
                const formatted = date.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                dueDatePreview.textContent = formatted;
                dueDatePreview.classList.add('bg-warning', 'bg-opacity-10');
                setTimeout(() => {
                    dueDatePreview.classList.remove('bg-warning', 'bg-opacity-10');
                }, 1000);
            }
        });
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
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const dueDate = new Date(document.getElementById('due_date').value);
            const now = new Date();
            
            if (dueDate < now) {
                e.preventDefault();
                alert('Batas waktu tidak boleh kurang dari waktu sekarang');
                return false;
            }
            
            // Show loading
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
                submitButton.disabled = true;
            }
        });
    }
    
    // Description character counter
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        counter.id = 'charCounter';
        descriptionTextarea.parentNode.appendChild(counter);
        
        descriptionTextarea.addEventListener('input', function() {
            const charCount = this.value.length;
            counter.textContent = `${charCount} karakter`;
            
            if (charCount > 5000) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
        
        // Initial count
        descriptionTextarea.dispatchEvent(new Event('input'));
    }
});

// Show delete modal
function confirmDelete() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection