@extends('layouts.app')

@section('title', 'Detail Tugas: ' . $assignment->title)

@section('content')
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
                            <i class="bi bi-book me-1"></i>{{ $assignment->class->class_name }}
                            <span class="mx-2">•</span>
                            <i class="bi bi-people me-1"></i>{{ $assignment->class->students->count() }} siswa
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('assignments.teacher.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-2"></i>Aksi
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('assignments.teacher.edit', $assignment) }}">
                                <i class="bi bi-pencil me-2"></i>Edit Tugas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('submissions.export-grades', $assignment) }}">
                                <i class="bi bi-download me-2"></i>Export Nilai
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteAssignmentModal">
                                <i class="bi bi-trash me-2"></i>Hapus Tugas
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notification -->
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

    @php
        $totalStudents = $assignment->class->students->count();
        $submitted = $assignment->submissions->count();
        $graded = $assignment->submissions()->whereNotNull('score')->count();
        $late = $assignment->submissions()->where('status', 'late')->count();
        $submissionPercentage = $totalStudents > 0 ? round(($submitted / $totalStudents) * 100) : 0;
        $gradedPercentage = $submitted > 0 ? round(($graded / $submitted) * 100) : 0;
        $isPastDue = \Carbon\Carbon::parse($assignment->due_date)->isPast();
        $dueDate = \Carbon\Carbon::parse($assignment->due_date);
    @endphp

    <div class="row g-3 g-md-4">
        <!-- Left Column - Assignment Details -->
        <div class="col-lg-8">
            <!-- Assignment Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Detail Tugas
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Description -->
                    <div class="mb-4">
                        <h6 class="mb-2">Deskripsi</h6>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($assignment->description)) !!}
                        </div>
                    </div>
                    
                    <!-- Information Grid -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-card p-3 border rounded">
                                <div class="info-label mb-1">Kelas</div>
                                <div class="info-value d-flex align-items-center gap-2">
                                    <span class="badge bg-info">{{ $assignment->class->class_code }}</span>
                                    <span>{{ $assignment->class->class_name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card p-3 border rounded">
                                <div class="info-label mb-1">Guru Pengampu</div>
                                <div class="info-value">{{ $assignment->teacher->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card p-3 border rounded">
                                <div class="info-label mb-1">Batas Waktu</div>
                                <div class="info-value">
                                    <span class="fw-semibold {{ $isPastDue ? 'text-danger' : 'text-success' }}">
                                        {{ $dueDate->format('d F Y, H:i') }}
                                    </span>
                                    @if($isPastDue)
                                        <span class="badge bg-danger ms-2">Selesai</span>
                                    @else
                                        <span class="badge bg-success ms-2">
                                            {{ now()->diffForHumans($dueDate, true) }} lagi
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card p-3 border rounded">
                                <div class="info-label mb-1">Nilai Maksimal</div>
                                <div class="info-value">
                                    <span class="badge bg-warning fs-6">{{ $assignment->max_score }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attachment Section -->
                    @if($assignment->attachment)
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3">
                            <i class="bi bi-paperclip me-2 text-primary"></i>
                            Lampiran Tugas
                        </h6>
                        <div class="attachment-box p-3 border rounded bg-light">
                            <div class="d-flex align-items-center gap-3">
                                <div class="attachment-icon">
                                    <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ basename($assignment->attachment) }}</div>
                                    <div class="text-muted small">File lampiran tugas dari guru</div>
                                </div>
                                <div class="btn-group">
                                    <a href="{{ Storage::url($assignment->attachment) }}" 
                                       class="btn btn-sm btn-primary" download>
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                    <a href="{{ Storage::url($assignment->attachment) }}" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Submissions Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people me-2 text-primary"></i>
                            Pengumpulan Siswa
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="gradeSelected">
                                <i class="bi bi-check-square me-1"></i>Nilai Terpilih
                            </button>
                            <a href="{{ route('submissions.export-grades', $assignment) }}" 
                               class="btn btn-sm btn-outline-success">
                                <i class="bi bi-download me-1"></i>Export Nilai
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="submissionsTable">
                                <thead>
                                    <tr>
                                        <th width="30" class="ps-3 ps-md-4">
                                            <input type="checkbox" id="selectAllSubmissions" class="form-check-input">
                                        </th>
                                        <th>SISWA</th>
                                        <th>STATUS</th>
                                        <th>TANGGAL SUBMIT</th>
                                        <th>NILAI</th>
                                        <th class="text-end pe-3 pe-md-4">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                    @php
                                        $isLate = $submission->status == 'late';
                                        $isGraded = $submission->status == 'graded' || $submission->score !== null;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 ps-md-4">
                                            <input type="checkbox" class="submission-check form-check-input" 
                                                   value="{{ $submission->id }}"
                                                   {{ $isGraded ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="student-avatar">
                                                    {{ strtoupper(substr($submission->student->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $submission->student->name }}</div>
                                                    <div class="text-muted small">{{ $submission->student->nis_nip }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($isLate)
                                                <span class="status-badge active bg-danger">
                                                    <i class="bi bi-clock-history me-1"></i>Terlambat
                                                </span>
                                            @elseif($isGraded)
                                                <span class="status-badge active bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>Dinilai
                                                </span>
                                            @else
                                                <span class="status-badge active bg-info">
                                                    <i class="bi bi-upload me-1"></i>Dikumpulkan
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ \Carbon\Carbon::parse($submission->submitted_at)->format('d/m/Y') }}</div>
                                            <div class="text-muted small">{{ \Carbon\Carbon::parse($submission->submitted_at)->format('H:i') }}</div>
                                            @if($isLate)
                                                <div class="text-danger small mt-1">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    +{{ $dueDate->diffInHours($submission->submitted_at) }} jam
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($submission->score)
                                                @php
                                                    $scoreClass = $submission->score >= 80 ? 'success' : ($submission->score >= 60 ? 'warning' : 'danger');
                                                @endphp
                                                <span class="badge bg-{{ $scoreClass }} fs-6 px-3 py-2">
                                                    {{ $submission->score }}
                                                </span>
                                                <div class="text-muted small mt-1">dari {{ $assignment->max_score }}</div>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2">Belum</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 pe-md-4">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="viewSubmission({{ $submission->id }})"
                                                        data-bs-toggle="tooltip" 
                                                        title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="gradeSubmission({{ $submission->id }})"
                                                        data-bs-toggle="tooltip" 
                                                        title="Nilai Tugas"
                                                        {{ $isGraded ? 'disabled' : '' }}>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @if($submission->attachment)
                                                <a href="{{ Storage::url($submission->attachment) }}" 
                                                   class="btn btn-sm btn-outline-success" target="_blank"
                                                   data-bs-toggle="tooltip" 
                                                   title="Download File">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state text-center py-5">
                            <div class="empty-icon mx-auto mb-3">
                                <i class="bi bi-people fs-1 text-muted"></i>
                            </div>
                            <h5 class="mb-2">Belum ada pengumpulan</h5>
                            <p class="text-muted">Siswa belum mengumpulkan tugas ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Stats & Actions -->
        <div class="col-lg-4">
            <!-- Stats Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2 text-primary"></i>
                        Statistik
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Submission Progress -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">Pengumpulan</span>
                            <span class="badge bg-success">{{ $submissionPercentage }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $submissionPercentage }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">{{ $submitted }}/{{ $totalStudents }}</small>
                            <small class="text-muted">Siswa</small>
                        </div>
                    </div>
                    
                    <!-- Grading Progress -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">Sudah Dinilai</span>
                            <span class="badge bg-info">{{ $gradedPercentage }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ $gradedPercentage }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">{{ $graded }}/{{ $submitted }}</small>
                            <small class="text-muted">Pengumpulan</small>
                        </div>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="row g-2 mt-3">
                        <div class="col-4">
                            <div class="stat-mini text-center p-2 rounded bg-danger-light">
                                <div class="stat-mini-value text-danger fw-bold fs-4">{{ $late }}</div>
                                <div class="stat-mini-label text-muted small">Terlambat</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini text-center p-2 rounded bg-warning-light">
                                <div class="stat-mini-value text-warning fw-bold fs-4">{{ $submitted - $graded }}</div>
                                <div class="stat-mini-label text-muted small">Belum Dinilai</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini text-center p-2 rounded bg-secondary-light">
                                <div class="stat-mini-value text-secondary fw-bold fs-4">{{ $totalStudents - $submitted }}</div>
                                <div class="stat-mini-label text-muted small">Belum Kumpul</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2 text-primary"></i>
                        Aksi Cepat
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('assignments.teacher.edit', $assignment) }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <div class="action-icon-small bg-primary-light text-primary">
                                <i class="bi bi-pencil"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Edit Tugas</div>
                                <small class="text-muted">Ubah informasi tugas</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3"
                                data-bs-toggle="modal" data-bs-target="#extendDeadlineModal">
                            <div class="action-icon-small bg-warning-light text-warning">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Perpanjang Waktu</div>
                                <small class="text-muted">Perpanjang batas waktu</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </button>
                        
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3"
                                data-bs-toggle="modal" data-bs-target="#announcementModal">
                            <div class="action-icon-small bg-info-light text-info">
                                <i class="bi bi-megaphone"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Beri Pengumuman</div>
                                <small class="text-muted">Beri informasi ke siswa</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </button>
                        
                        <button onclick="downloadAllSubmissions()" 
                                class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                            <div class="action-icon-small bg-success-light text-success">
                                <i class="bi bi-download"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Download Semua</div>
                                <small class="text-muted">Download semua pengumpulan</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Not Submitted -->
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-x me-2 text-danger"></i>
                            Belum Mengumpulkan
                        </h5>
                        <span class="badge bg-danger">{{ $totalStudents - $submitted }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        $submittedIds = $assignment->submissions->pluck('student_id')->toArray();
                        $notSubmitted = $assignment->class->students->whereNotIn('id', $submittedIds);
                    @endphp
                    
                    @if($notSubmitted->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notSubmitted->take(5) as $student)
                            <div class="list-group-item d-flex align-items-center gap-3 py-3">
                                <div class="student-avatar-sm bg-danger">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $student->name }}</div>
                                    <div class="text-muted small">{{ $student->nis_nip }}</div>
                                </div>
                                <button type="button" class="btn btn-icon btn-sm" 
                                        onclick="remindStudent({{ $student->id }})"
                                        data-bs-toggle="tooltip" 
                                        title="Kirim Pengingat">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </div>
                            @endforeach
                            
                            @if($notSubmitted->count() > 5)
                            <div class="list-group-item text-center py-2">
                                <small class="text-muted">
                                    dan {{ $notSubmitted->count() - 5 }} siswa lainnya
                                </small>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mx-auto mb-2">
                                <i class="bi bi-check-circle-fill text-success fs-2"></i>
                            </div>
                            <h6 class="text-success fw-semibold">Selamat!</h6>
                            <p class="text-muted small mb-0">Semua siswa sudah mengumpulkan!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('assignments.modals.view-submission')
@include('assignments.modals.grade-submission')
@include('assignments.modals.extend-deadline')
@include('assignments.modals.send-announcement')

<!-- Batch Grade Modal -->
<div class="modal fade" id="batchGradeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-check-square me-2"></i>Nilai Batch
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="batchGradeForm" method="POST" action="{{ route('assignments.teacher.batch-grade', $assignment) }}">
                @csrf
                <input type="hidden" name="submission_ids" id="batchSubmissionIds">
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">Nilai</label>
                        <input type="number" class="form-control" name="score" 
                               min="0" max="{{ $assignment->max_score }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback (Opsional)</label>
                        <textarea class="form-control" name="feedback" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAssignmentModal" tabindex="-1">
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
                    <strong>Peringatan:</strong> Tindakan ini akan menghapus:
                    <ul class="mt-2 mb-0">
                        <li>Semua pengumpulan siswa ({{ $submissions->count() }} data)</li>
                        <li>Semua nilai yang sudah diberikan</li>
                        <li>File lampiran tugas</li>
                    </ul>
                    <p class="mt-2 mb-0"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('assignments.teacher.destroy', $assignment) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Ya, Hapus Tugas</button>
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
    --secondary: #6b7280;
    --secondary-light: #f3f4f6;
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

/* Info Card */
.info-card {
    background: #f8fafc;
    transition: var(--transition);
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.info-label {
    font-size: 0.688rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #1f2937;
}

/* Student Avatar */
.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.student-avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.813rem;
    flex-shrink: 0;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 20px;
    font-size: 0.688rem;
    font-weight: 500;
    color: white;
}

.status-badge.bg-danger { background: #ef4444 !important; }
.status-badge.bg-success { background: #10b981 !important; }
.status-badge.bg-info { background: #3b82f6 !important; }

/* Table */
.table {
    margin: 0;
}

.table thead th {
    font-weight: 600;
    font-size: 0.688rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.table tbody td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f9fafb;
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

.btn-group {
    gap: 6px;
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

.btn-outline-warning {
    border-color: #e5e7eb;
    color: #f59e0b;
    background: white;
}

.btn-outline-warning:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.btn-outline-info {
    border-color: #e5e7eb;
    color: #3b82f6;
    background: white;
}

.btn-outline-info:hover {
    background: #3b82f6;
    border-color: #3b82f6;
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

.btn-outline-secondary {
    border-color: #e5e7eb;
    color: #6b7280;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #374151;
}

.btn-icon {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    background: white;
}

.btn-icon:hover {
    background: #f9fafb;
    color: #4f46e5;
    border-color: #d1d5db;
}

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
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

/* Stat Mini */
.stat-mini {
    transition: var(--transition);
}

.stat-mini:hover {
    transform: translateY(-2px);
}

.stat-mini-value {
    font-size: 1.25rem;
    font-weight: 700;
}

.stat-mini-label {
    font-size: 0.688rem;
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

/* Empty State */
.empty-state {
    padding: 2rem 1rem;
}

.empty-icon {
    width: 64px;
    height: 64px;
    background: #f9fafb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.empty-state h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
}

.empty-state p {
    font-size: 0.813rem;
    color: #6b7280;
}

/* List Group */
.list-group-item {
    border-color: #e5e7eb;
    background: white;
}

.list-group-item-action:hover {
    background: #f8fafc;
}

/* Attachment */
.attachment-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Colors */
.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-warning-light { background: #fef3c7; }
.bg-danger-light { background: #fee2e2; }
.bg-info-light { background: #dbeafe; }
.bg-secondary-light { background: #f3f4f6; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }
.text-info { color: #3b82f6 !important; }
.text-muted { color: #6b7280 !important; }

/* Checkbox */
.form-check-input {
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.form-check-input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
    
    .student-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .btn-group {
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.625rem;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
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
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Select All Submissions
    const selectAllCheckbox = document.getElementById('selectAllSubmissions');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.submission-check:not(:disabled)');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Grade Selected Submissions
    const gradeSelectedBtn = document.getElementById('gradeSelected');
    if (gradeSelectedBtn) {
        gradeSelectedBtn.addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.submission-check:checked:not(:disabled)'))
                .map(cb => cb.value);
            
            if (selected.length === 0) {
                alert('Pilih pengumpulan yang akan dinilai terlebih dahulu');
                return;
            }
            
            if (selected.length === 1) {
                gradeSubmission(selected[0]);
            } else {
                const modal = new bootstrap.Modal(document.getElementById('batchGradeModal'));
                document.getElementById('batchSubmissionIds').value = JSON.stringify(selected);
                modal.show();
            }
        });
    }
    
    // Update selected count
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.submission-check:checked:not(:disabled)');
        const gradeBtn = document.getElementById('gradeSelected');
        
        if (selected.length > 0 && gradeBtn) {
            gradeBtn.innerHTML = `<i class="bi bi-check-square me-1"></i>Nilai (${selected.length})`;
            gradeBtn.classList.remove('btn-outline-primary');
            gradeBtn.classList.add('btn-primary');
        } else if (gradeBtn) {
            gradeBtn.innerHTML = `<i class="bi bi-check-square me-1"></i>Nilai Terpilih`;
            gradeBtn.classList.remove('btn-primary');
            gradeBtn.classList.add('btn-outline-primary');
        }
    }
    
    // Add event listeners to checkboxes
    document.querySelectorAll('.submission-check').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Initialize count
    updateSelectedCount();
    
    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) {
                setTimeout(() => bsAlert.close(), 5000);
            }
        });
    }, 1000);
});

// View Submission
function viewSubmission(submissionId) {
    fetch(`/submissions/${submissionId}`)
        .then(response => response.text())
        .then(html => {
            const modal = new bootstrap.Modal(document.getElementById('viewSubmissionModal'));
            const content = document.getElementById('viewSubmissionContent');
            if (content) content.innerHTML = html;
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data pengumpulan');
        });
}

// Grade Submission
function gradeSubmission(submissionId) {
    fetch(`/submissions/${submissionId}/get`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = new bootstrap.Modal(document.getElementById('gradeSubmissionModal'));
                const form = document.getElementById('gradeForm');
                if (form) {
                    form.action = `/submissions/${submissionId}/grade`;
                    const scoreInput = form.querySelector('input[name="score"]');
                    const feedbackInput = form.querySelector('textarea[name="feedback"]');
                    if (scoreInput) scoreInput.value = data.submission.score || '';
                    if (feedbackInput) feedbackInput.value = data.submission.feedback || '';
                }
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data pengumpulan');
        });
}

// Remind Student
function remindStudent(studentId) {
    if (confirm('Kirim pengingat ke siswa ini?')) {
        fetch(`/submissions/students/${studentId}/remind`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                assignment_id: {{ $assignment->id }},
                message: 'Ingatkan untuk mengumpulkan tugas "' + '{{ addslashes($assignment->title) }}' + '"'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pengingat berhasil dikirim ke siswa');
            } else {
                alert('Gagal mengirim pengingat: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengirim pengingat');
        });
    }
}

// Download All Submissions
function downloadAllSubmissions() {
    window.location.href = '{{ route("assignments.teacher.download-all", $assignment) }}';
}
</script>
@endsection