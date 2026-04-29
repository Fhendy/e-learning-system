@extends('layouts.app')

@section('title', 'Edit Absensi')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-pencil-square-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Edit Absensi</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-person me-1"></i>{{ $attendance->student->name ?? 'Siswa' }}
                            <span class="mx-2">•</span>
                            <i class="bi bi-building me-1"></i>{{ $attendance->class->class_name ?? 'Kelas' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('attendance.teacher.index') }}" class="btn btn-outline-secondary">
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

    <div class="row g-3 g-md-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        Form Edit Absensi
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.teacher.update', $attendance) }}" method="POST" id="editForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-select" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" 
                                                {{ $attendance->class_id == $class->id ? 'selected' : '' }}>
                                                {{ $class->class_name }} ({{ $class->class_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                                    <select name="student_id" id="student_id" class="form-select" required>
                                        <option value="">Pilih Siswa</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" 
                                                {{ $attendance->student_id == $student->id ? 'selected' : '' }}>
                                                {{ $student->name }} ({{ $student->nis_nip ?? 'NIS' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('student_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attendance_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="attendance_date" id="attendance_date" 
                                           class="form-control" 
                                           value="{{ old('attendance_date', $attendance->attendance_date instanceof \Carbon\Carbon ? $attendance->attendance_date->format('Y-m-d') : \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d')) }}" 
                                           required>
                                    @error('attendance_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="">Pilih Status</option>
                                        <option value="present" {{ $attendance->status == 'present' ? 'selected' : '' }}>Hadir</option>
                                        <option value="late" {{ $attendance->status == 'late' ? 'selected' : '' }}>Terlambat</option>
                                        <option value="absent" {{ $attendance->status == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                        <option value="sick" {{ $attendance->status == 'sick' ? 'selected' : '' }}>Sakit</option>
                                        <option value="permission" {{ $attendance->status == 'permission' ? 'selected' : '' }}>Izin</option>
                                    </select>
                                    @error('status')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="checked_in_at" class="form-label">Waktu Absen</label>
                                    <input type="time" name="checked_in_at" id="checked_in_at" 
                                           class="form-control" 
                                           value="{{ old('checked_in_at', $attendance->checked_in_at ? (\Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i')) : '') }}">
                                    @error('checked_in_at')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted small">Kosongkan jika tidak ada</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="qr_code_id" class="form-label">QR Code (Opsional)</label>
                                    <select name="qr_code_id" id="qr_code_id" class="form-select">
                                        <option value="">Tidak menggunakan QR Code</option>
                                        @foreach($qrCodes as $qrCode)
                                            <option value="{{ $qrCode->id }}" 
                                                {{ $attendance->qr_code_id == $qrCode->id ? 'selected' : '' }}>
                                                {{ $qrCode->code }} - {{ \Carbon\Carbon::parse($qrCode->date)->format('d/m/Y') }} ({{ $qrCode->formatted_time_range ?? $qrCode->start_time . ' - ' . $qrCode->end_time }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('qr_code_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" 
                                      placeholder="Tambahkan catatan untuk absensi ini...">{{ old('notes', $attendance->notes) }}</textarea>
                            @error('notes')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Perubahan akan tercatat dalam sistem.
                        </div>
                        
                        <div class="d-flex justify-content-between gap-3 pt-3 border-top">
                            <a href="{{ route('attendance.teacher.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi Absensi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-hash"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">ID Absensi</div>
                                <div class="info-value">{{ $attendance->id }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar-plus"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Dibuat</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($attendance->created_at)->format('d/m/Y H:i') }}
                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($attendance->created_at)->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Diperbarui</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($attendance->updated_at)->format('d/m/Y H:i') }}
                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($attendance->updated_at)->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                        @if($attendance->qrCode)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">QR Code</div>
                                <div class="info-value">
                                    <span class="badge bg-info">{{ $attendance->qrCode->code }}</span>
                                    <div class="text-muted small">
                                        {{ \Carbon\Carbon::parse($attendance->qrCode->date)->format('d/m/Y') }} • 
                                        {{ $attendance->qrCode->formatted_time_range ?? $attendance->qrCode->start_time . ' - ' . $attendance->qrCode->end_time }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($attendance->marked_by)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Ditandai Oleh</div>
                                <div class="info-value">{{ $attendance->marker->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Informasi:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Hanya guru yang mengajar kelas ini yang dapat mengedit absensi</li>
                            <li>Perubahan akan tercatat dalam sistem</li>
                            <li>QR Code opsional, digunakan jika absensi via scan</li>
                        </ul>
                    </div>
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

.bg-info {
    background: #3b82f6 !important;
}

/* Colors */
.text-primary { color: #4f46e5 !important; }
.text-muted { color: #6b7280 !important; }
.text-danger { color: #ef4444 !important; }

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
    
    .info-icon {
        width: 28px;
        height: 28px;
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
    const classSelect = document.getElementById('class_id');
    const studentSelect = document.getElementById('student_id');
    const statusSelect = document.getElementById('status');
    const timeInput = document.getElementById('checked_in_at');
    const form = document.getElementById('editForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Update siswa berdasarkan kelas yang dipilih
    if (classSelect && studentSelect) {
        classSelect.addEventListener('change', function() {
            const classId = this.value;
            
            if (!classId) {
                studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';
                return;
            }
            
            studentSelect.innerHTML = '<option value="">Memuat siswa...</option>';
            studentSelect.disabled = true;
            
            fetch(`/api/classes/${classId}/students`)
                .then(response => response.json())
                .then(data => {
                    studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';
                    data.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.id;
                        option.textContent = student.text || student.name;
                        studentSelect.appendChild(option);
                    });
                    studentSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                    studentSelect.innerHTML = '<option value="">Gagal memuat siswa</option>';
                    studentSelect.disabled = false;
                });
        });
    }
    
    // Auto-fill waktu jika status Hadir/Terlambat dan waktu kosong
    if (statusSelect && timeInput) {
        statusSelect.addEventListener('change', function() {
            if (!timeInput.value && (this.value === 'present' || this.value === 'late')) {
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                timeInput.value = `${hours}:${minutes}`;
            }
        });
    }
    
    // Form submission
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
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