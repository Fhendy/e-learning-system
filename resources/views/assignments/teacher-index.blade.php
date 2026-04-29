@extends('layouts.app')

@section('title', 'Daftar Tugas - Guru')

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
                        <h1 class="page-title mb-1">Manajemen Tugas</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Kelola tugas dan pengumpulan siswa
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-filter me-2"></i>Filter
                </button>
                <a href="{{ route('assignments.teacher.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Tugas Baru
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
                        <h3 class="stats-value mb-0">{{ $assignments->total() }}</h3>
                        <p class="stats-label mb-0">Total Tugas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-clock fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $activeCount ?? 0 }}</h3>
                        <p class="stats-label mb-0">Tugas Aktif</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-warning-light text-warning">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $pendingCount ?? 0 }}</h3>
                        <p class="stats-label mb-0">Belum Dinilai</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-info-light text-info">
                        <i class="bi bi-percent fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $averageSubmission ?? 0 }}%</h3>
                        <p class="stats-label mb-0">Rata-rata Pengumpulan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Tugas -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="bi bi-table me-2"></i>
                        Daftar Tugas
                    </h5>
                    <p class="card-subtitle text-muted mb-0">
                        {{ $assignments->total() }} tugas ditemukan
                    </p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="#" class="dropdown-item">
                            <i class="bi bi-file-earmark-pdf me-2"></i>PDF
                        </a>
                        <a href="#" class="dropdown-item">
                            <i class="bi bi-file-earmark-excel me-2"></i>Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                    <div class="flex-grow-1">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            @endif

            @if($assignments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30" class="ps-3 ps-md-4">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>JUDUL TUGAS</th>
                            <th>KELAS</th>
                            <th>BATAS WAKTU</th>
                            <th>STATUS</th>
                            <th>PENGUMPULAN</th>
                            <th class="text-end pe-3 pe-md-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                        @php
                            $submitted = $assignment->submissions->count();
                            $total = $assignment->class->students->count() ?? 1;
                            $percentage = $total > 0 ? round(($submitted / $total) * 100) : 0;
                            $isPastDue = \Carbon\Carbon::parse($assignment->due_date)->isPast();
                            $dueDate = \Carbon\Carbon::parse($assignment->due_date);
                        @endphp
                        <tr>
                            <td class="ps-3 ps-md-4">
                                <input type="checkbox" class="assignment-check form-check-input" value="{{ $assignment->id }}">
                            </td>
                            <td>
                                <div>
                                    @if($assignment->attachment)
                                        <i class="bi bi-paperclip text-primary me-1"></i>
                                    @endif
                                    <a href="{{ route('assignments.show', $assignment) }}" class="assignment-title">
                                        {{ $assignment->title }}
                                    </a>
                                    <div class="text-muted small mt-1">
                                        {{ Str::limit($assignment->description, 50) }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $assignment->class->class_code }}</span>
                                <div class="small text-muted mt-1">{{ $assignment->class->class_name }}</div>
                            </td>
                            <td>
                                <div>
                                    <span class="{{ $isPastDue ? 'text-danger' : 'text-success' }} fw-semibold">
                                        {{ $dueDate->format('d/m/Y H:i') }}
                                    </span>
                                    @if(!$isPastDue)
                                        <div class="small text-muted">
                                            {{ now()->diffForHumans($dueDate, true) }} lagi
                                        </div>
                                    @else
                                        <div class="small text-danger">
                                            Terlambat {{ now()->diffForHumans($dueDate, true) }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($isPastDue)
                                    <span class="badge bg-danger">Selesai</span>
                                @else
                                    <span class="badge bg-success">Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="assignment-progress">
                                    <div class="progress-info mb-1">
                                        <span class="progress-value">{{ $percentage }}%</span>
                                        <span class="progress-text">{{ $submitted }}/{{ $total }}</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $percentage == 100 ? 'success' : ($percentage > 50 ? 'info' : 'warning') }}" 
                                             style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-check-circle text-success me-1"></i>
                                        {{ $assignment->submissions()->whereNotNull('score')->count() }} dinilai
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pe-3 pe-md-4">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('assignments.show', $assignment) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       data-bs-toggle="tooltip" 
                                       title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('assignments.teacher.edit', $assignment) }}" 
                                       class="btn btn-sm btn-outline-warning" 
                                       data-bs-toggle="tooltip" 
                                       title="Edit Tugas">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('assignments.teacher.destroy', $assignment) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="tooltip" 
                                                title="Hapus Tugas"
                                                onclick="return confirm('Hapus tugas \"{{ $assignment->title }}\"?\n\nAkan menghapus {{ $submitted }} pengumpulan siswa.\nTindakan ini tidak dapat dibatalkan!')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-icon mx-auto mb-3">
                                        <i class="bi bi-journal-text fs-1 text-muted"></i>
                                    </div>
                                    <h5 class="mb-2">Belum ada tugas</h5>
                                    <p class="text-muted mb-4">Buat tugas pertama Anda untuk siswa</p>
                                    <a href="{{ route('assignments.teacher.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Buat Tugas
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($assignments->hasPages())
            <div class="card-footer bg-white">
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
            @endif

            @else
            <div class="empty-state text-center py-5">
                <div class="empty-icon mx-auto mb-3">
                    <i class="bi bi-journal-text fs-1 text-muted"></i>
                </div>
                <h5 class="mb-2">Belum ada tugas</h5>
                <p class="text-muted mb-4">Buat tugas pertama Anda untuk siswa</p>
                <a href="{{ route('assignments.teacher.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Buat Tugas
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Batch Actions -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <div>
                    <span id="selectedCount" class="text-muted small">0 tugas terpilih</span>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="batchDownload">
                        <i class="bi bi-download me-1"></i>Download
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="batchDelete">
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
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
            <form method="GET" action="{{ route('assignments.teacher.index') }}">
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="past" {{ request('status') == 'past' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="class_id">
                            <option value="">Semua Kelas</option>
                            @foreach($allClasses ?? $classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
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
                    <a href="{{ route('assignments.teacher.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
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
    --info: #3b82f6;
    --info-light: #dbeafe;
    --danger: #ef4444;
    --danger-light: #fee2e2;
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

.card-subtitle {
    font-size: 0.688rem;
    color: #6b7280;
}

.card-footer {
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 0.75rem 1rem;
}

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

/* Assignment Title */
.assignment-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    text-decoration: none;
}

.assignment-title:hover {
    color: #4f46e5;
}

/* Assignment Progress */
.assignment-progress {
    min-width: 120px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.progress-value {
    font-size: 0.75rem;
    font-weight: 600;
    color: #4f46e5;
}

.progress-text {
    font-size: 0.688rem;
    color: #64748b;
}

.progress {
    height: 5px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    border-radius: 3px;
    transition: width 0.6s ease;
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

.btn-outline-danger {
    border-color: #e5e7eb;
    color: #ef4444;
    background: white;
}

.btn-outline-danger:hover {
    background: #ef4444;
    border-color: #ef4444;
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

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
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

.bg-success {
    background: #10b981 !important;
}

.bg-danger {
    background: #ef4444 !important;
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

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-success {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}

/* Colors */
.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-warning-light { background: #fef3c7; }
.bg-info-light { background: #dbeafe; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-info { color: #3b82f6 !important; }
.text-danger { color: #ef4444 !important; }
.text-muted { color: #6b7280 !important; }

/* Checkbox */
.form-check-input {
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

/* Responsive */
@media (min-width: 992px) {
    .stats-icon {
        width: 44px;
        height: 44px;
    }
    
    .stats-value {
        font-size: 1.375rem;
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
    
    .table thead th,
    .table tbody td {
        padding: 0.625rem;
    }
    
    .assignment-progress {
        min-width: 100px;
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

.stats-card, .card {
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
    
    // Select All Checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.assignment-check');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Update selected count
    function updateSelectedCount() {
        const selected = document.querySelectorAll('.assignment-check:checked');
        const countSpan = document.getElementById('selectedCount');
        if (countSpan) {
            countSpan.textContent = selected.length + ' tugas terpilih';
        }
    }
    
    // Add event listeners to checkboxes
    document.querySelectorAll('.assignment-check').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Batch Download
    const batchDownload = document.getElementById('batchDownload');
    if (batchDownload) {
        batchDownload.addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.assignment-check:checked'))
                .map(cb => cb.value);
            if (selected.length > 0) {
                alert('Download ' + selected.length + ' tugas');
                // Implement batch download here
            } else {
                alert('Pilih tugas terlebih dahulu');
            }
        });
    }
    
    // Batch Delete
    const batchDelete = document.getElementById('batchDelete');
    if (batchDelete) {
        batchDelete.addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.assignment-check:checked'))
                .map(cb => cb.value);
            if (selected.length > 0) {
                if (confirm('Hapus ' + selected.length + ' tugas terpilih?\n\nSemua pengumpulan akan ikut terhapus!')) {
                    alert('Batch delete untuk ' + selected.length + ' tugas');
                    // Implement batch delete here
                }
            } else {
                alert('Pilih tugas terlebih dahulu');
            }
        });
    }
});
</script>
@endsection