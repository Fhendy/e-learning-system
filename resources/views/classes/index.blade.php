@extends('layouts.app')

@section('title', 'Daftar Kelas')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Daftar Kelas</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Kelola semua kelas dan siswa Anda di satu tempat
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-2"></i>Import
                </button>
                <button class="btn btn-outline-secondary" onclick="showQuickStats()">
                    <i class="bi bi-graph-up me-2"></i>Statistik
                </button>
                <a href="{{ route('classes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Kelas Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="bi bi-upload me-2"></i>
                        Import Kelas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('classes.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="modal-body pt-0">
                        <div class="text-center mb-4">
                            <div class="import-icon mx-auto mb-3">
                                <i class="bi bi-file-earmark-excel fs-1"></i>
                            </div>
                            <p>Upload file Excel dengan format yang sesuai</p>
                        </div>
                        
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-2"></i>
                            Download template terlebih dahulu untuk memastikan format benar
                        </div>
                        
                        <div class="mb-3">
                            <label for="import_file" class="form-label">Pilih File Excel</label>
                            <input type="file" class="form-control" id="import_file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="text-muted small mt-1">Maksimal ukuran file: 5MB</div>
                        </div>
                        
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
                    <div class="modal-footer border-0 pt-0">
                        <a href="{{ route('classes.import.template') }}" class="btn btn-outline-primary me-2">
                            <i class="bi bi-download me-2"></i>Download Template
                        </a>
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="importSubmitBtn">
                            <i class="bi bi-upload me-2"></i>Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- HAPUS SEMUA NOTIFIKASI SESSION -->
    <!-- Tidak ada alert session success/error lagi -->

    @php
        $totalStudents = 0;
        foreach($classes as $class) {
            $totalStudents += $class->students->count();
        }
        $activeClasses = $classes->where('is_active', true)->count();
        $avgStudents = $classes->count() > 0 ? round($totalStudents / $classes->count(), 1) : 0;
    @endphp

    <!-- Stats Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-primary-light text-primary">
                        <i class="bi bi-building fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $classes->total() }}</h3>
                        <p class="stats-label mb-0">Total Kelas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $totalStudents }}</h3>
                        <p class="stats-label mb-0">Total Siswa</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-info-light text-info">
                        <i class="bi bi-calculator fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $avgStudents }}</h3>
                        <p class="stats-label mb-0">Rata-rata Siswa</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-warning-light text-warning">
                        <i class="bi bi-toggle-on fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $activeClasses }}</h3>
                        <p class="stats-label mb-0">Kelas Aktif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classes Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="bi bi-table me-2"></i>
                        Daftar Kelas
                    </h5>
                    <p class="card-subtitle text-muted mb-0">
                        {{ $classes->total() }} kelas ditemukan
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-icon btn-sm" id="refreshBtn" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari kelas atau kode..." id="searchInput">
                    </div>
                </div>
            </div>
        </div>
        
        @if($classes->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="classesTable">
                <thead>
                    <tr>
                        <th class="ps-3 ps-md-4">KELAS</th>
                        <th>SISWA</th>
                        <th>STATUS</th>
                        <th class="text-end pe-3 pe-md-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $class)
                    <tr class="class-row" data-class-id="{{ $class->id }}" data-class-name="{{ $class->class_name }}">
                        <td class="ps-3 ps-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="class-avatar">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $class->class_name }}</h6>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <code class="class-code">{{ $class->class_code }}</code>
                                        <span class="text-muted">•</span>
                                        <small class="text-muted">
                                            {{ $class->created_at ? $class->created_at->format('d M Y') : '-' }}
                                        </small>
                                    </div>
                                    @if($class->description)
                                    <p class="text-muted small mt-1 mb-0">
                                        {{ Str::limit($class->description, 50) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <td>
                            <div>
                                <span class="fw-semibold">{{ $class->students_count ?? $class->students->count() }}</span>
                                <span class="text-muted small">siswa</span>
                            </div>
                        </div>
                        <td>
                            @if($class->is_active)
                            <span class="status-badge active">
                                <i class="bi bi-circle-fill"></i>
                                Aktif
                            </span>
                            @else
                            <span class="status-badge inactive">
                                <i class="bi bi-circle-fill"></i>
                                Nonaktif
                            </span>
                            @endif
                        </div>
                        <td class="text-end pe-3 pe-md-4">
                            <div class="btn-group" role="group">
                                <a href="{{ route('classes.show', $class) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="tooltip" 
                                   title="Detail Kelas">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('classes.edit', $class) }}" 
                                   class="btn btn-sm btn-outline-warning" 
                                   data-bs-toggle="tooltip" 
                                   title="Edit Kelas">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($class->is_active)
                                <form action="{{ route('classes.deactivate', $class) }}" 
                                      method="POST" 
                                      class="d-inline toggle-class-form"
                                      data-action="deactivate">
                                    @csrf
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger toggle-class-btn" 
                                            data-bs-toggle="tooltip" 
                                            title="Nonaktifkan"
                                            data-class-id="{{ $class->id }}"
                                            data-class-name="{{ $class->class_name }}"
                                            data-student-count="{{ $class->students_count ?? $class->students->count() }}"
                                            data-action="deactivate">
                                        <i class="bi bi-toggle-off"></i>
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('classes.activate', $class) }}" 
                                      method="POST" 
                                      class="d-inline toggle-class-form"
                                      data-action="activate">
                                    @csrf
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-success toggle-class-btn" 
                                            data-bs-toggle="tooltip" 
                                            title="Aktifkan"
                                            data-class-id="{{ $class->id }}"
                                            data-class-name="{{ $class->class_name }}"
                                            data-student-count="{{ $class->students_count ?? $class->students->count() }}"
                                            data-action="activate">
                                        <i class="bi bi-toggle-on"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($classes->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div>
                    <p class="mb-0 text-muted small">
                        Menampilkan <strong>{{ $classes->firstItem() ?? 0 }}</strong> 
                        sampai <strong>{{ $classes->lastItem() ?? 0 }}</strong> 
                        dari <strong>{{ $classes->total() }}</strong> kelas
                    </p>
                </div>
                <nav aria-label="Page navigation">
                    {{ $classes->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                </nav>
            </div>
        </div>
        @endif

        @else
        <div class="empty-state text-center py-5">
            <div class="empty-icon mx-auto mb-3">
                <i class="bi bi-people fs-1 text-muted"></i>
            </div>
            <h5 class="mb-2">Belum ada kelas</h5>
            <p class="text-muted mb-4">Mulai dengan membuat kelas pertama Anda</p>
            <a href="{{ route('classes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Buat Kelas
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Quick Stats Modal -->
<div class="modal fade" id="quickStatsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-graph-up me-2"></i>
                    Statistik Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="quickStatsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Memuat statistik...</p>
                    </div>
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
    --info: #06b6d4;
    --info-light: #cffafe;
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

/* Class Avatar */
.class-avatar {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.class-code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.688rem;
    color: #374151;
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
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.active i {
    color: #10b981;
    font-size: 0.5rem;
}

.status-badge.inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.status-badge.inactive i {
    color: #9ca3af;
    font-size: 0.5rem;
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

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
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

/* Import Icon */
.import-icon {
    width: 64px;
    height: 64px;
    background: #e0e7ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4f46e5;
    margin: 0 auto;
}

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

.form-check-input {
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
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

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-info {
    background: #cffafe;
    border-color: #06b6d4;
    color: #155e75;
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

/* Progress */
.progress {
    height: 6px;
    border-radius: 3px;
    background-color: #e5e7eb;
}

.progress-bar {
    background: #10b981;
    border-radius: 3px;
}

/* Input Group */
.input-group-text {
    background: white;
    border: 1px solid #e5e7eb;
    color: #6b7280;
}

/* Utilities */
.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-info-light { background: #cffafe; }
.bg-warning-light { background: #fef3c7; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-info { color: #06b6d4 !important; }
.text-warning { color: #f59e0b !important; }

.fw-semibold { font-weight: 600; }

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
    
    .class-avatar {
        width: 36px;
        height: 36px;
        font-size: 1rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.688rem;
    }
    
    .btn-group {
        gap: 4px;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .input-group {
        width: 100% !important;
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

.stats-card {
    animation: fadeIn 0.3s ease forwards;
}

.stats-card:nth-child(1) { animation-delay: 0s; }
.stats-card:nth-child(2) { animation-delay: 0.05s; }
.stats-card:nth-child(3) { animation-delay: 0.1s; }
.stats-card:nth-child(4) { animation-delay: 0.15s; }
</style>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// SweetAlert Modern
const CustomSwal = {
    // Konfirmasi Aktivasi
    confirmActivate: (className) => {
        return Swal.fire({
            title: 'Aktifkan Kelas',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-toggle-on" style="font-size: 3.5rem; color: #10b981;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">${className}</h6>
                    <p class="mb-3">Apakah Anda yakin ingin mengaktifkan kelas ini?</p>
                    <div class="alert alert-success small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Kelas yang aktif dapat diakses oleh siswa
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Aktifkan',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Batal',
            reverseButtons: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-success btn-sm px-4',
                cancelButton: 'btn btn-secondary btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    // Konfirmasi Nonaktifkan
    confirmDeactivate: (className, studentCount = 0) => {
        let warningHtml = '';
        if (studentCount > 0) {
            warningHtml = `
                <div class="alert alert-warning small mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Kelas ini memiliki <strong>${studentCount} siswa</strong> yang terdaftar
                </div>
            `;
        }
        
        return Swal.fire({
            title: 'Nonaktifkan Kelas',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-toggle-off" style="font-size: 3.5rem; color: #ef4444;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">${className}</h6>
                    <p class="mb-3">Apakah Anda yakin ingin menonaktifkan kelas ini?</p>
                    ${warningHtml}
                    <div class="alert alert-danger small mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Kelas yang dinonaktifkan tidak dapat diakses oleh siswa
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Nonaktifkan',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Batal',
            reverseButtons: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-danger btn-sm px-4',
                cancelButton: 'btn btn-secondary btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    // Loading Dialog
    showLoading: (title = 'Sedang Memproses...') => {
        return Swal.fire({
            title: title,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: {
                popup: 'custom-swal-popup'
            }
        });
    },
    
    // Close Loading
    closeLoading: () => {
        Swal.close();
    },
    
    // Success Dialog (Toast seperti notifikasi sukses)
    showSuccessToast: (message) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        Toast.fire({
            icon: 'success',
            title: message,
            background: '#ffffff',
            iconColor: '#10b981'
        });
    },
    
    // Success Dialog (Modal)
    showSuccessModal: (title, message) => {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#4f46e5',
            timer: 2000,
            timerProgressBar: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-primary btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    // Error Dialog
    showError: (title, message) => {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#ef4444',
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-danger btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    // Konfirmasi Import
    confirmImport: (fileName) => {
        return Swal.fire({
            title: 'Konfirmasi Import',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-file-earmark-excel" style="font-size: 3.5rem; color: #4f46e5;"></i>
                    </div>
                    <p class="mb-2">Apakah Anda yakin ingin mengimport file</p>
                    <h6 class="fw-semibold mb-3">${fileName}</h6>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Pastikan file sudah sesuai dengan template yang disediakan
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-upload me-2"></i>Import',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Batal',
            reverseButtons: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-primary btn-sm px-4',
                cancelButton: 'btn btn-secondary btn-sm px-4',
            },
            buttonsStyling: false
        });
    }
};

// Custom CSS untuk SweetAlert
const swalStyles = document.createElement('style');
swalStyles.textContent = `
    .custom-swal-popup {
        border-radius: 16px !important;
        padding: 0 !important;
        width: 420px !important;
        font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    .custom-swal-popup .swal2-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #1f2937 !important;
        padding: 1.25rem 1.25rem 0 !important;
        margin-bottom: 0 !important;
    }
    
    .custom-swal-popup .swal2-html-container {
        padding: 0 1.25rem 1.25rem !important;
        margin-top: 0 !important;
    }
    
    .custom-swal-popup .swal2-actions {
        padding: 0 1.25rem 1.25rem !important;
        gap: 0.75rem !important;
        margin-top: 0 !important;
    }
    
    .custom-swal-popup .swal2-loader {
        border-color: #4f46e5 !important;
        border-right-color: transparent !important;
    }
    
    .custom-swal-popup .swal2-timer-progress-bar {
        background: linear-gradient(90deg, #4f46e5, #818cf8) !important;
    }
    
    .swal-icon-wrapper {
        margin-top: 0.5rem;
    }
    
    /* Alert dalam SweetAlert */
    .custom-swal-popup .alert {
        border-radius: 10px;
        font-size: 0.75rem;
        text-align: left;
    }
    
    .custom-swal-popup .alert-warning {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #92400e;
    }
    
    .custom-swal-popup .alert-success {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    
    .custom-swal-popup .alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    
    .custom-swal-popup .alert-info {
        background: #cffafe;
        border: 1px solid #bae6fd;
        color: #155e75;
    }
    
    /* Toast Notification */
    .swal2-toast {
        border-radius: 12px !important;
    }
`;

document.head.appendChild(swalStyles);

// Function to handle class toggle
async function confirmToggleClass(form, action, className, studentCount) {
    const isActivate = action === 'activate';
    
    let result;
    
    if (isActivate) {
        result = await CustomSwal.confirmActivate(className);
    } else {
        result = await CustomSwal.confirmDeactivate(className, studentCount);
    }
    
    if (result.isConfirmed) {
        const loadingTitle = isActivate ? 'Mengaktifkan Kelas...' : 'Menonaktifkan Kelas...';
        CustomSwal.showLoading(loadingTitle);
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            });
            
            const data = await response.json();
            CustomSwal.closeLoading();
            
            if (data.success) {
                // Hanya menampilkan SweetAlert sukses, tidak ada notifikasi redirect dengan session
                await CustomSwal.showSuccessModal(
                    isActivate ? 'Berhasil!' : 'Berhasil!',
                    data.message
                );
                // Refresh halaman untuk update tampilan
                window.location.reload();
            } else {
                await CustomSwal.showError('Gagal!', data.message || 'Terjadi kesalahan saat memproses data.');
            }
        } catch (error) {
            CustomSwal.closeLoading();
            await CustomSwal.showError('Error!', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
            console.error('Error:', error);
        }
    }
}

// Function to show quick stats
function showQuickStats() {
    const modal = new bootstrap.Modal(document.getElementById('quickStatsModal'));
    modal.show();
    
    setTimeout(() => {
        document.getElementById('quickStatsContent').innerHTML = `
            <div class="row g-2 g-md-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="stats-card text-center p-3">
                        <div class="stats-icon bg-primary-light text-primary mx-auto mb-2">
                            <i class="bi bi-building"></i>
                        </div>
                        <h3 class="stats-value mb-0">{{ $classes->total() }}</h3>
                        <p class="stats-label mb-0">Total Kelas</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stats-card text-center p-3">
                        <div class="stats-icon bg-success-light text-success mx-auto mb-2">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3 class="stats-value mb-0">{{ $totalStudents }}</h3>
                        <p class="stats-label mb-0">Total Siswa</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stats-card text-center p-3">
                        <div class="stats-icon bg-info-light text-info mx-auto mb-2">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <h3 class="stats-value mb-0">{{ $avgStudents }}</h3>
                        <p class="stats-label mb-0">Rata-rata per kelas</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stats-card text-center p-3">
                        <div class="stats-icon bg-warning-light text-warning mx-auto mb-2">
                            <i class="bi bi-toggle-on"></i>
                        </div>
                        <h3 class="stats-value mb-0">{{ $activeClasses }}</h3>
                        <p class="stats-label mb-0">Kelas Aktif</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 pt-2 border-top">
                <h6 class="mb-3">Distribusi Kelas</h6>
                <div class="progress mb-2">
                    <div class="progress-bar" 
                         style="width: {{ $classes->count() > 0 ? ($activeClasses / $classes->count()) * 100 : 0 }}%">
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted">
                        <i class="bi bi-toggle-on text-success me-1"></i>
                        {{ $activeClasses }} kelas aktif
                    </small>
                    <small class="text-muted">
                        <i class="bi bi-toggle-off text-secondary me-1"></i>
                        {{ $classes->count() - $activeClasses }} tidak aktif
                    </small>
                </div>
            </div>
        `;
    }, 300);
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add CSRF token meta tag if not exists
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }
    
    // SweetAlert untuk tombol toggle
    const toggleButtons = document.querySelectorAll('.toggle-class-btn');
    toggleButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const form = this.closest('.toggle-class-form');
            const action = form.getAttribute('data-action');
            const className = this.getAttribute('data-class-name');
            const studentCount = parseInt(this.getAttribute('data-student-count') || '0');
            
            await confirmToggleClass(form, action, className, studentCount);
        });
    });
    
    // Refresh button
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.classList.add('rotate');
            setTimeout(() => {
                window.location.reload();
            }, 300);
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.class-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = searchTerm === '' || text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Import form submission
    const importForm = document.getElementById('importForm');
    const importSubmitBtn = document.getElementById('importSubmitBtn');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    if (importForm && importSubmitBtn) {
        importForm.addEventListener('submit', async function(e) {
            const fileInput = document.getElementById('import_file');
            
            if (!fileInput.files.length) {
                e.preventDefault();
                await CustomSwal.showError('File Tidak Ditemukan', 'Silakan pilih file terlebih dahulu');
                return false;
            }
            
            const fileName = fileInput.files[0].name;
            const fileExt = fileName.split('.').pop().toLowerCase();
            
            if (!['xlsx', 'xls', 'csv'].includes(fileExt)) {
                e.preventDefault();
                await CustomSwal.showError('Format Tidak Didukung', 'Gunakan file dengan format .xlsx, .xls, atau .csv');
                return false;
            }
            
            const fileSize = fileInput.files[0].size;
            const maxSize = 5 * 1024 * 1024;
            
            if (fileSize > maxSize) {
                e.preventDefault();
                await CustomSwal.showError('Ukuran File Terlalu Besar', 'Maksimal ukuran file adalah 5MB');
                return false;
            }
            
            e.preventDefault();
            
            const result = await CustomSwal.confirmImport(fileName);
            
            if (result.isConfirmed) {
                loadingModal.show();
                importSubmitBtn.disabled = true;
                importSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
                importForm.submit();
            }
        });
    }
});

// Refresh button animation
const rotateStyle = document.createElement('style');
rotateStyle.textContent = `
@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.rotate {
    animation: rotate 0.5s ease;
}
`;
document.head.appendChild(rotateStyle);
</script>
@endsection