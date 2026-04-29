@extends('layouts.app')

@section('title', 'Kelola Siswa')

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
                        <h1 class="page-title mb-1">Kelola Siswa</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Kelola data siswa dan kehadiran mereka
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-upload me-2"></i>Import
                </button>
                <a href="{{ route('students.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Tambah Siswa
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('students.index') }}" class="filter-form">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" 
                                   name="search" 
                                   placeholder="Cari nama, NIS, atau email..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="class_id">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-funnel me-2"></i>Filter
                        </button>
                        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary" title="Reset">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="bi bi-table me-2"></i>
                        Daftar Siswa
                    </h5>
                    <p class="card-subtitle text-muted mb-0">
                        {{ $students->total() }} siswa ditemukan
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-icon btn-sm" id="refreshBtn" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="{{ route('students.export.excel') }}" class="dropdown-item">
                                <i class="bi bi-file-earmark-excel me-2"></i>Excel
                            </a>
                            <a href="{{ route('students.export.pdf') }}" class="dropdown-item">
                                <i class="bi bi-file-earmark-pdf me-2"></i>PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($students->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3 ps-md-4">SISWA</th>
                        <th>KONTAK</th>
                        <th>KELAS</th>
                        <th>STATUS</th>
                        <th class="text-end pe-3 pe-md-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr class="student-row">
                        <td class="ps-3 ps-md-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $student->name }}</h6>
                                    <code class="student-nis">{{ $student->nis_nip }}</code>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="d-flex align-items-center gap-1 text-muted small">
                                    <i class="bi bi-envelope"></i>
                                    <span>{{ Str::limit($student->email, 25) }}</span>
                                </div>
                                @if($student->phone)
                                <div class="d-flex align-items-center gap-1 text-muted small mt-1">
                                    <i class="bi bi-phone"></i>
                                    <span>{{ $student->phone }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($student->classesAsStudent->count() > 0)
                            <div class="class-tags">
                                @foreach($student->classesAsStudent->take(2) as $class)
                                <span class="class-tag">{{ $class->class_code }}</span>
                                @endforeach
                                @if($student->classesAsStudent->count() > 2)
                                <span class="class-tag-more">+{{ $student->classesAsStudent->count() - 2 }}</span>
                                @endif
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($student->is_active)
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
                        </td>
                        <td class="text-end pe-3 pe-md-4">
                            <div class="btn-group" role="group">
                                <a href="{{ route('students.show', $student) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="tooltip" 
                                   title="Detail Siswa">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('students.edit', $student) }}" 
                                   class="btn btn-sm btn-outline-warning" 
                                   data-bs-toggle="tooltip" 
                                   title="Edit Siswa">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger" 
                                        data-bs-toggle="tooltip" 
                                        title="Hapus Siswa"
                                        onclick="confirmDelete({{ $student->id }}, '{{ addslashes($student->name) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($students->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div>
                    <p class="mb-0 text-muted small">
                        Menampilkan <strong>{{ $students->firstItem() ?? 0 }}</strong> 
                        sampai <strong>{{ $students->lastItem() ?? 0 }}</strong> 
                        dari <strong>{{ $students->total() }}</strong> siswa
                    </p>
                </div>
                <nav aria-label="Page navigation">
                    {{ $students->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                </nav>
            </div>
        </div>
        @endif

        @else
        <div class="empty-state text-center py-5">
            <div class="empty-icon mx-auto mb-3">
                <i class="bi bi-person fs-1 text-muted"></i>
            </div>
            <h5 class="mb-2">Belum ada siswa</h5>
            <p class="text-muted mb-4">Mulai dengan menambahkan siswa pertama</p>
            <a href="{{ route('students.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>
                Tambah Siswa
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>
                    Import Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('students.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
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
                    
                    <div class="mb-3">
                        <label for="import_class_id" class="form-label">Tambahkan ke Kelas (Opsional)</label>
                        <select class="form-select" id="import_class_id" name="class_id">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name }} ({{ $class->class_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skipDuplicates" value="1" checked>
                        <label class="form-check-label" for="skipDuplicates">
                            Lewati data yang sudah ada (berdasarkan NIS)
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="send_email" id="sendEmail" value="1">
                        <label class="form-check-label" for="sendEmail">
                            Kirim email notifikasi ke siswa
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <a href="{{ route('students.import.template') }}" class="btn btn-outline-primary me-2">
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="delete-icon mx-auto mb-3">
                    <i class="bi bi-trash3 fs-1 text-danger"></i>
                </div>
                <h6 id="deleteStudentName">Nama Siswa</h6>
                <p class="mb-3">Apakah Anda yakin ingin menghapus siswa ini?</p>
                <div class="alert alert-danger small">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Tindakan ini tidak dapat dibatalkan. Semua data siswa akan dihapus permanen.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
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

/* Filter Form */
.filter-form .input-group-text {
    background: white;
    border: 1px solid #e5e7eb;
    border-right: none;
    border-radius: 8px 0 0 8px;
}

.filter-form .form-control,
.filter-form .form-select {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: var(--transition);
    font-size: 0.813rem;
}

.filter-form .form-control:focus,
.filter-form .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

.filter-form .form-control {
    border-radius: 0 8px 8px 0;
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

.student-nis {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.688rem;
    color: #374151;
}

/* Class Tags */
.class-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.class-tag {
    background: #e0e7ff;
    color: #4f46e5;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.688rem;
    font-weight: 500;
}

.class-tag-more {
    background: #f3f4f6;
    color: #6b7280;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.688rem;
    font-weight: 500;
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

/* Dropdown */
.dropdown-menu {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.375rem;
    min-width: 140px;
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
}

.dropdown-item {
    border-radius: 6px;
    padding: 0.375rem 0.75rem;
    font-size: 0.813rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #1f2937;
}

.dropdown-item:hover {
    background: #f9fafb;
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

.delete-icon {
    width: 64px;
    height: 64px;
    background: #fee2e2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.import-icon {
    width: 64px;
    height: 64px;
    background: #e0e7ff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4f46e5;
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

.alert-danger {
    background: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
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

/* Responsive */
@media (min-width: 992px) {
    .student-avatar {
        width: 44px;
        height: 44px;
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
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.688rem;
    }
    
    .btn-group {
        gap: 4px;
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
function confirmDelete(studentId, studentName) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const studentNameSpan = document.getElementById('deleteStudentName');
    
    deleteForm.action = '/students/' + studentId;
    studentNameSpan.textContent = studentName;
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
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
    
    // Filter form auto-submit on select change
    const classSelect = document.querySelector('select[name="class_id"]');
    const statusSelect = document.querySelector('select[name="status"]');
    
    if (classSelect) {
        classSelect.addEventListener('change', function() {
            this.closest('form').submit();
        });
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            this.closest('form').submit();
        });
    }
    
    // Import form submission
    const importForm = document.getElementById('importForm');
    const importSubmitBtn = document.getElementById('importSubmitBtn');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    if (importForm && importSubmitBtn) {
        importForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('import_file');
            
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
            importSubmitBtn.disabled = true;
            importSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
        });
    }
});

// Refresh button animation
const style = document.createElement('style');
style.textContent = `
@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.rotate {
    animation: rotate 0.5s ease;
}
`;
document.head.appendChild(style);
</script>
@endsection