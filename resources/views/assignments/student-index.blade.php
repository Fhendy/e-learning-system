@extends('layouts.app')

@section('title', 'Tugas Saya')

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
                        <h1 class="page-title mb-1">Tugas Saya</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Kelola tugas dan pengumpulan Anda
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-filter me-2"></i>Filter
                </button>
                <a href="{{ route('assignments.student.index', ['status' => 'pending']) }}" 
                   class="btn btn-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>Belum Dikerjakan
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-primary-light text-primary">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $totalAssignments ?? $assignments->total() ?? 0 }}</h3>
                        <p class="stats-label mb-0">Total Tugas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-warning-light text-warning">
                        <i class="bi bi-clock fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $pendingCount ?? 0 }}</h3>
                        <p class="stats-label mb-0">Belum Dikerjakan</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $completedCount ?? 0 }}</h3>
                        <p class="stats-label mb-0">Sudah Dikerjakan</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-danger-light text-danger">
                        <i class="bi bi-exclamation-triangle fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $lateCount ?? 0 }}</h3>
                        <p class="stats-label mb-0">Terlambat</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-tabs" id="assignmentTabs">
                <li class="nav-item">
                    <a class="nav-link {{ !request('status') ? 'active' : '' }}" 
                       href="{{ route('assignments.student.index') }}">Semua</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'pending' ? 'active' : '' }}" 
                       href="{{ route('assignments.student.index', ['status' => 'pending']) }}">Belum Dikerjakan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'submitted' ? 'active' : '' }}" 
                       href="{{ route('assignments.student.index', ['status' => 'submitted']) }}">Sudah Dikumpulkan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'graded' ? 'active' : '' }}" 
                       href="{{ route('assignments.student.index', ['status' => 'graded']) }}">Sudah Dinilai</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('status') == 'late' ? 'active' : '' }}" 
                       href="{{ route('assignments.student.index', ['status' => 'late']) }}">Terlambat</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tugas List -->
    @if($assignments->count() > 0)
        <div class="row g-3 g-md-4">
            @foreach($assignments as $assignment)
            @php
                $submission = $assignment->submissionByStudent(auth()->id());
                $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
                    ? $assignment->due_date 
                    : \Carbon\Carbon::parse($assignment->due_date);
                $now = now();
                $diffDays = $now->diffInDays($dueDate, false);
                $isOverdue = $diffDays < 0;
                $isUrgent = $diffDays <= 3 && $diffDays >= 0;
                $submissionStatus = $submission ? $submission->status : null;
                $hasScore = $submission && $submission->score !== null;
                $borderColor = $isOverdue ? 'danger' : ($isUrgent ? 'warning' : ($submissionStatus == 'graded' ? 'success' : 'primary'));
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="assignment-card card border-left-{{ $borderColor }}">
                    <div class="card-body">
                        <div class="assignment-header mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="assignment-title">
                                    @if($assignment->attachment)
                                        <span class="badge bg-info mb-2">
                                            <i class="bi bi-paperclip me-1"></i>Lampiran
                                        </span>
                                    @endif
                                    <h5 class="card-title mb-1">
                                        <a href="{{ route('assignments.show', $assignment) }}" class="assignment-link">
                                            {{ Str::limit($assignment->title, 35) }}
                                        </a>
                                    </h5>
                                    <span class="badge bg-secondary">{{ $assignment->class->class_name ?? 'Kelas' }}</span>
                                </div>
                                <div class="assignment-status">
                                    @if($submissionStatus)
                                        @if($submissionStatus == 'late')
                                            <span class="badge bg-danger">Terlambat</span>
                                        @elseif($submissionStatus == 'graded')
                                            <span class="badge bg-success">Dinilai</span>
                                        @elseif($submissionStatus == 'submitted')
                                            <span class="badge bg-info">Dikumpulkan</span>
                                        @elseif($submissionStatus == 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ $submissionStatus }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-warning">Belum Dikerjakan</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <p class="card-text text-muted small mb-3">
                            @if($assignment->description)
                                {{ Str::limit(strip_tags($assignment->description), 80) }}
                            @else
                                <span class="text-muted fst-italic">Tidak ada deskripsi</span>
                            @endif
                        </p>

                        <div class="assignment-info mb-3">
                            <div class="info-row d-flex justify-content-between mb-2">
                                <span class="text-muted">Batas Waktu:</span>
                                <span class="fw-semibold {{ $isOverdue ? 'text-danger' : ($isUrgent ? 'text-warning' : 'text-success') }}">
                                    {{ $dueDate->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="info-row d-flex justify-content-between mb-2">
                                <span class="text-muted">Status Deadline:</span>
                                <span>
                                    @if($isOverdue)
                                        <span class="text-danger">Terlambat {{ abs($diffDays) }} hari</span>
                                    @elseif($isUrgent)
                                        <span class="text-warning">{{ $diffDays }} hari lagi</span>
                                    @else
                                        <span class="text-success">{{ $diffDays }} hari lagi</span>
                                    @endif
                                </span>
                            </div>
                            @if($hasScore)
                            <div class="info-row d-flex justify-content-between mb-2">
                                <span class="text-muted">Nilai:</span>
                                @php
                                    $scorePercentage = $assignment->max_score > 0 ? round(($submission->score / $assignment->max_score) * 100) : 0;
                                    $scoreClass = $scorePercentage >= 80 ? 'success' : ($scorePercentage >= 60 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $scoreClass }}">
                                    {{ $submission->score }}/{{ $assignment->max_score }}
                                    ({{ $scorePercentage }}%)
                                </span>
                            </div>
                            @endif
                        </div>

                        <div class="assignment-footer d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>
                                {{ $assignment->teacher->name ?? 'Guru' }}
                            </small>
                            <a href="{{ route('assignments.show', $assignment) }}" 
                               class="btn btn-sm {{ $submissionStatus ? 'btn-outline-primary' : 'btn-primary' }}">
                                @if($submissionStatus)
                                    @if($submissionStatus == 'draft')
                                        <i class="bi bi-pencil me-1"></i>Lanjutkan
                                    @else
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    @endif
                                @else
                                    <i class="bi bi-pencil me-1"></i>Kerjakan
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($assignments->hasPages())
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div>
                        <p class="mb-0 text-muted small">
                            Menampilkan <strong>{{ $assignments->firstItem() ?? 0 }}</strong> 
                            sampai <strong>{{ $assignments->lastItem() ?? 0 }}</strong> 
                            dari <strong>{{ $assignments->total() }}</strong> tugas
                        </p>
                    </div>
                    <nav aria-label="Page navigation">
                        {{ $assignments->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                    </nav>
                </div>
            </div>
        </div>
        @endif

    @else
        <div class="card">
            <div class="card-body">
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mx-auto mb-3">
                        @if(request()->has('status') || request()->has('class_id') || request()->has('start_date') || request()->has('end_date'))
                            <i class="bi bi-search fs-1 text-muted"></i>
                        @else
                            <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                        @endif
                    </div>
                    <h5 class="mb-2">
                        @if(request()->has('status') || request()->has('class_id') || request()->has('start_date') || request()->has('end_date'))
                            Tidak ada tugas yang sesuai filter
                        @else
                            Tidak ada tugas
                        @endif
                    </h5>
                    <p class="text-muted mb-0">
                        @if(request('status') == 'pending')
                            Tidak ada tugas yang belum dikerjakan
                        @elseif(request('status') == 'submitted')
                            Tidak ada tugas yang sudah dikumpulkan
                        @elseif(request('status') == 'graded')
                            Tidak ada tugas yang sudah dinilai
                        @elseif(request('status') == 'late')
                            Tidak ada tugas yang terlambat
                        @else
                            Anda belum memiliki tugas saat ini.
                        @endif
                    </p>
                    @if(request()->has('status') || request()->has('class_id') || request()->has('start_date') || request()->has('end_date'))
                        <a href="{{ route('assignments.student.index') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-arrow-left me-2"></i>Lihat Semua Tugas
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-filter me-2"></i>Filter Tugas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('assignments.student.index') }}">
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="class_id">
                            <option value="">Semua Kelas</option>
                            @foreach($classes ?? [] as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }} ({{ $class->class_code }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Belum Dikerjakan</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Sudah Dikumpulkan</option>
                            <option value="graded" {{ request('status') == 'graded' ? 'selected' : '' }}>Sudah Dinilai</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <a href="{{ route('assignments.student.index') }}" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
                    <button type="submit" class="btn btn-primary btn-sm">Terapkan Filter</button>
                </div>
            </form>
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

/* Stats Cards */
.stats-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 0.875rem;
    border: 1px solid #e5e7eb;
    transition: var(--transition);
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stats-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.stats-label {
    font-size: 0.688rem;
    color: #6b7280;
}

/* Assignment Card */
.assignment-card {
    transition: var(--transition);
    height: 100%;
}

.assignment-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.border-left-primary { border-left: 4px solid #4f46e5 !important; }
.border-left-success { border-left: 4px solid #10b981 !important; }
.border-left-warning { border-left: 4px solid #f59e0b !important; }
.border-left-danger { border-left: 4px solid #ef4444 !important; }

.assignment-link {
    text-decoration: none;
    color: #1f2937;
    transition: var(--transition);
}

.assignment-link:hover {
    color: #4f46e5;
}

/* Tabs */
.nav-tabs {
    border-bottom: 1px solid #e5e7eb;
    gap: 0.25rem;
    padding: 0 0.5rem;
}

.nav-tabs .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    padding: 0.625rem 1rem;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.813rem;
    transition: var(--transition);
}

.nav-tabs .nav-link:hover {
    color: #4f46e5;
    background: #f9fafb;
}

.nav-tabs .nav-link.active {
    color: #4f46e5;
    background: white;
    border-bottom: 2px solid #4f46e5;
}

/* Info Row */
.info-row {
    font-size: 0.75rem;
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

.btn-outline-secondary {
    border-color: #e5e7eb;
    color: #6b7280;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #374151;
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

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.bg-primary { background: #4f46e5 !important; }
.bg-success { background: #10b981 !important; }
.bg-warning { background: #f59e0b !important; }
.bg-danger { background: #ef4444 !important; }
.bg-info { background: #3b82f6 !important; }
.bg-secondary { background: #6b7280 !important; }

/* Card */
.card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.card-body {
    padding: 1rem;
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

/* Form */
.form-label {
    font-weight: 500;
    font-size: 0.813rem;
    color: #374151;
    margin-bottom: 0.375rem;
}

.form-select,
.form-control {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    font-size: 0.813rem;
    transition: var(--transition);
}

.form-select:focus,
.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

/* Colors */
.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-warning-light { background: #fef3c7; }
.bg-danger-light { background: #fee2e2; }
.bg-info-light { background: #dbeafe; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }
.text-muted { color: #6b7280 !important; }

/* Responsive */
@media (min-width: 992px) {
    .stats-icon {
        width: 44px;
        height: 44px;
    }
    
    .stats-value {
        font-size: 1.375rem;
    }
    
    .card-body {
        padding: 1.25rem;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .stats-card {
        padding: 0.75rem;
    }
    
    .stats-icon {
        width: 32px;
        height: 32px;
    }
    
    .stats-icon i {
        font-size: 0.875rem;
    }
    
    .stats-value {
        font-size: 1rem;
    }
    
    .nav-tabs {
        padding: 0;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 0.625rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .assignment-footer {
        flex-direction: column;
        gap: 0.5rem;
        align-items: stretch;
    }
    
    .assignment-footer .btn {
        width: 100%;
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

.stats-card, .assignment-card {
    animation: fadeIn 0.3s ease forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set active tab based on URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    if (status) {
        const activeTab = document.querySelector(`.nav-link[href*="status=${status}"]`);
        if (activeTab) {
            document.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
            activeTab.classList.add('active');
        }
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection