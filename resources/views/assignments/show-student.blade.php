@extends('layouts.app')

@section('title', 'Tugas: ' . $assignment->title)

@section('content')
@php
    use Carbon\Carbon;
    
    $dueDate = $assignment->due_date instanceof Carbon 
        ? $assignment->due_date 
        : Carbon::parse($assignment->due_date);
    
    $isPastDue = $dueDate->isPast();
    
    if ($submission) {
        $submittedAt = $submission->submitted_at instanceof Carbon 
            ? $submission->submitted_at 
            : Carbon::parse($submission->submitted_at);
    }
@endphp

<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">{{ $assignment->title }}</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-building me-1"></i>{{ $assignment->class->class_name ?? 'N/A' }}
                            <span class="mx-2">•</span>
                            <i class="bi bi-person-badge me-1"></i>{{ $assignment->teacher->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('assignments.student.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <div class="row g-3 g-md-4">
        <!-- Left Column - Assignment Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Detail Tugas
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Description -->
                    <div class="assignment-description mb-4">
                        <h6 class="mb-2">Deskripsi Tugas</h6>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($assignment->description)) !!}
                        </div>
                    </div>
                    
                    <!-- Information Grid -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="info-label">Kelas</div>
                                    <div class="info-value">
                                        {{ $assignment->class->class_name ?? 'N/A' }}
                                        <span class="badge bg-info ms-2">{{ $assignment->class->class_code ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="info-label">Guru</div>
                                    <div class="info-value">{{ $assignment->teacher->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-calendar"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="info-label">Batas Waktu</div>
                                    <div class="info-value">
                                        <span class="{{ $isPastDue ? 'text-danger' : 'text-success' }} fw-semibold">
                                            {{ $dueDate->format('d F Y, H:i') }}
                                        </span>
                                        <div class="small {{ $isPastDue ? 'text-danger' : 'text-success' }}">
                                            @if($isPastDue)
                                                <i class="bi bi-exclamation-triangle me-1"></i>Tugas sudah selesai
                                            @else
                                                <i class="bi bi-clock me-1"></i>{{ now()->diffForHumans($dueDate, true) }} lagi
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="bi bi-star"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="info-label">Nilai Maksimal</div>
                                    <div class="info-value">
                                        <span class="badge bg-primary">{{ $assignment->max_score }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attachment from Teacher -->
                    @if($assignment->attachment)
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3">
                            <i class="bi bi-paperclip me-2 text-primary"></i>
                            Lampiran dari Guru
                        </h6>
                        <div class="attachment-box p-3 border rounded bg-light">
                            <div class="d-flex align-items-center gap-3">
                                <div class="attachment-icon">
                                    <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ basename($assignment->attachment) }}</div>
                                    <div class="text-muted small">File lampiran tugas</div>
                                </div>
                                <div>
                                    <a href="{{ Storage::url($assignment->attachment) }}" 
                                       class="btn btn-sm btn-primary" target="_blank" download>
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Student's Submission -->
                    @if($submission)
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3">
                            <i class="bi bi-upload me-2 text-primary"></i>
                            Pengumpulan Anda
                        </h6>
                        
                        @if($submission->submission_text)
                        <div class="mb-3">
                            <div class="info-label mb-1">Jawaban Teks</div>
                            <div class="p-3 border rounded bg-light">
                                {!! nl2br(e($submission->submission_text)) !!}
                            </div>
                        </div>
                        @endif
                        
                        @if($submission->attachment)
                        <div class="attachment-box p-3 border rounded bg-light">
                            <div class="d-flex align-items-center gap-3">
                                <div class="attachment-icon">
                                    <i class="bi bi-file-earmark-arrow-up fs-1 text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ basename($submission->attachment) }}</div>
                                    <div class="text-muted small">File yang Anda kumpulkan</div>
                                </div>
                                <div>
                                    <a href="{{ Storage::url($submission->attachment) }}" 
                                       class="btn btn-sm btn-success" target="_blank" download>
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="text-muted small mt-2">
                            <i class="bi bi-clock me-1"></i>
                            Dikumpulkan pada: {{ $submittedAt->format('d F Y, H:i:s') }}
                        </div>
                        
                        @if($submission->feedback)
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6 class="mb-1">
                                <i class="bi bi-chat-left-text me-2 text-info"></i>
                                Feedback dari Guru
                            </h6>
                            <p class="mb-0 small">{{ $submission->feedback }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Status & Actions -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard-check me-2 text-primary"></i>
                        Status Pengumpulan
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($submission)
                        @if($submission->status == 'graded')
                            <div class="status-icon mb-3">
                                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                            </div>
                            <h5 class="text-success mb-2">Tugas Sudah Dinilai</h5>
                            @php
                                $score = $submission->score ?? 0;
                                $percentage = $assignment->max_score > 0 ? round(($score / $assignment->max_score) * 100) : 0;
                                $scoreClass = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
                            @endphp
                            <div class="score-display mb-2">
                                <span class="badge bg-{{ $scoreClass }} fs-4 px-4 py-2">
                                    {{ $score }}/{{ $assignment->max_score }}
                                </span>
                            </div>
                            <div class="text-muted small">{{ $percentage }}%</div>
                        @elseif($submission->status == 'late')
                            <div class="status-icon mb-3">
                                <i class="bi bi-clock-history text-danger fs-1"></i>
                            </div>
                            <h5 class="text-danger mb-2">Terkumpul Terlambat</h5>
                            <div class="text-muted small">{{ $submittedAt->format('d/m/Y H:i') }}</div>
                        @else
                            <div class="status-icon mb-3">
                                <i class="bi bi-check-circle text-info fs-1"></i>
                            </div>
                            <h5 class="text-info mb-2">Terkumpul</h5>
                            <div class="text-muted small">{{ $submittedAt->format('d/m/Y H:i') }}</div>
                        @endif
                    @else
                        <div class="status-icon mb-3">
                            <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                        </div>
                        <h5 class="text-warning mb-2">Belum Dikumpulkan</h5>
                        <div class="text-muted small">
                            @if($isPastDue)
                                <span class="text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Batas waktu telah lewat
                                </span>
                            @else
                                <i class="bi bi-clock me-1"></i>
                                {{ now()->diffForHumans($dueDate, true) }} lagi
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2 text-primary"></i>
                        Aksi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!$submission && !$isPastDue)
                            <button type="button" class="btn btn-primary" 
                                    data-bs-toggle="modal" data-bs-target="#submitModal">
                                <i class="bi bi-upload me-2"></i>Kumpulkan Tugas
                            </button>
                            
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="saveAsDraft()">
                                <i class="bi bi-save me-2"></i>Simpan Draft
                            </button>
                        @elseif($submission && !$submission->score && !$isPastDue)
                            <button type="button" class="btn btn-warning" 
                                    data-bs-toggle="modal" data-bs-target="#resubmitModal">
                                <i class="bi bi-arrow-repeat me-2"></i>Kumpulkan Ulang
                            </button>
                        @endif
                        
                        @if($submission && $submission->attachment)
                            <a href="{{ Storage::url($submission->attachment) }}" 
                               class="btn btn-outline-success" download>
                                <i class="bi bi-download me-2"></i>Download Pengumpulan
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Important Dates -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-date me-2 text-primary"></i>
                        Tanggal Penting
                    </h5>
                </div>
                <div class="card-body">
                    <div class="important-dates">
                        <div class="date-item mb-3">
                            <div class="date-label">Batas Waktu</div>
                            <div class="date-value {{ $isPastDue ? 'text-danger' : 'text-success' }}">
                                {{ $dueDate->format('d F Y, H:i') }}
                            </div>
                            <div class="date-status small {{ $isPastDue ? 'text-danger' : 'text-success' }}">
                                @if($isPastDue)
                                    <i class="bi bi-exclamation-triangle me-1"></i>Telah berakhir
                                @else
                                    <i class="bi bi-clock me-1"></i>
                                    {{ now()->diffForHumans($dueDate, true) }} lagi
                                @endif
                            </div>
                        </div>
                        
                        @if($submission)
                        <div class="date-item mb-3">
                            <div class="date-label">Dikumpulkan</div>
                            <div class="date-value text-info">
                                {{ $submittedAt->format('d F Y, H:i') }}
                            </div>
                            <div class="date-status small text-info">
                                @if($submission->status == 'late')
                                    <i class="bi bi-clock-history me-1"></i>Terlambat
                                @else
                                    <i class="bi bi-check-circle me-1"></i>Tepat waktu
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($submission && $submission->score)
                        <div class="date-item">
                            <div class="date-label">Dinilai</div>
                            <div class="date-value text-success">
                                @php
                                    $gradedAt = $submission->updated_at instanceof Carbon 
                                        ? $submission->updated_at 
                                        : Carbon::parse($submission->updated_at);
                                @endphp
                                {{ $gradedAt->format('d F Y, H:i') }}
                            </div>
                            <div class="date-status small text-success">
                                <i class="bi bi-check-circle-fill me-1"></i>Sudah dinilai
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Kumpulkan Tugas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('submissions.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">Jawaban Teks (Opsional)</label>
                        <textarea class="form-control" name="submission_text" rows="5" 
                                  placeholder="Tulis jawaban Anda di sini..." id="submissionText"></textarea>
                        <div class="text-muted small mt-1" id="charCounter">0 karakter</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Lampiran (Opsional)</label>
                        <input type="file" class="form-control" name="attachment" 
                               accept=".pdf,.doc,.docx,.txt,.jpg,.png,.jpeg">
                        <div class="text-muted small mt-1">
                            Format: PDF, DOC, DOCX, TXT, JPG, PNG (Max: 2MB)
                        </div>
                    </div>
                    
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Setelah dikumpulkan, Anda masih bisa mengumpulkan ulang sebelum batas waktu.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-2"></i>Kumpulkan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resubmit Modal -->
@if($submission && !$submission->score && !$isPastDue)
<div class="modal fade" id="resubmitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat me-2"></i>Kumpulkan Ulang Tugas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('submissions.resubmit', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pt-0">
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Pengumpulan sebelumnya akan digantikan dengan yang baru.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jawaban Teks Baru</label>
                        <textarea class="form-control" name="submission_text" rows="5">{{ $submission->submission_text ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Lampiran Baru</label>
                        <input type="file" class="form-control" name="attachment" 
                               accept=".pdf,.doc,.docx,.txt,.jpg,.png,.jpeg">
                        <div class="text-muted small mt-1">
                            File saat ini: 
                            @if($submission->attachment)
                                <a href="{{ Storage::url($submission->attachment) }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-download me-1"></i>{{ basename($submission->attachment) }}
                                </a>
                            @else
                                Tidak ada file
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-arrow-repeat me-2"></i>Kumpulkan Ulang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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

/* Info Item */
.info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.5rem 0;
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

/* Status Icon */
.status-icon {
    width: 64px;
    height: 64px;
    background: #f8fafc;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Score Display */
.score-display .badge {
    font-size: 1.25rem;
    padding: 0.5rem 1rem;
    border-radius: 1rem;
}

/* Attachment Icon */
.attachment-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Important Dates */
.important-dates {
    padding: 0;
}

.date-item {
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.date-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.date-label {
    font-size: 0.688rem;
    color: #6b7280;
    margin-bottom: 0.125rem;
}

.date-value {
    font-size: 0.813rem;
    font-weight: 500;
}

.date-status {
    font-size: 0.688rem;
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

.btn-warning {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background: #d97706;
    border-color: #d97706;
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

.bg-primary { background: #4f46e5 !important; }
.bg-info { background: #3b82f6 !important; }
.bg-success { background: #10b981 !important; }
.bg-warning { background: #f59e0b !important; }
.bg-danger { background: #ef4444 !important; }

/* Form */
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

.alert-warning {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
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

/* Background */
.bg-light {
    background: #f8fafc !important;
}

/* Text Colors */
.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }
.text-info { color: #3b82f6 !important; }
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
    // Character counter for textarea
    const textarea = document.getElementById('submissionText');
    const charCounter = document.getElementById('charCounter');
    
    if (textarea && charCounter) {
        textarea.addEventListener('input', function() {
            const charCount = this.value.length;
            charCounter.textContent = `${charCount} karakter`;
            
            if (charCount > 5000) {
                charCounter.classList.add('text-danger');
            } else {
                charCounter.classList.remove('text-danger');
            }
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) {
                setTimeout(() => bsAlert.close(), 1000);
            }
        });
    }, 5000);
    
    // Auto-show modal if URL has #submit
    if (window.location.hash === '#submit') {
        const submitModal = new bootstrap.Modal(document.getElementById('submitModal'));
        submitModal.show();
    }
});

function saveAsDraft() {
    const formData = new FormData();
    const textarea = document.querySelector('#submitModal textarea[name="submission_text"]');
    const fileInput = document.querySelector('#submitModal input[name="attachment"]');
    
    if (textarea && textarea.value) formData.append('submission_text', textarea.value);
    if (fileInput && fileInput.files[0]) formData.append('attachment', fileInput.files[0]);
    
    fetch('{{ route("submissions.save-draft", $assignment) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Draft berhasil disimpan');
            location.reload();
        } else {
            alert('Gagal menyimpan draft: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menyimpan draft');
    });
}
</script>
@endsection