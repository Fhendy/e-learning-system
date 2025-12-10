@extends('layouts.app')

@section('title', 'Kelola Siswa')

@section('content')
<div class="container-fluid px-0 px-md-3">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
            <div>
                <h1 class="page-title">Kelola Siswa</h1>
                <p class="page-subtitle text-muted">
                    Kelola data siswa dan kehadiran mereka
                </p>
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
    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('students.index') }}" class="filter-form">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group input-group-search">
                            <span class="input-group-text">
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
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="card-title mb-1">Daftar Siswa</h5>
                    <p class="card-subtitle text-muted">
                        {{ $students->total() }} siswa ditemukan
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary" id="refreshBtn" title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-file-earmark-excel me-2"></i>Excel
                            </a>
                            <a href="#" class="dropdown-item">
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
                        <th class="ps-4">SISWA</th>
                        <th>KONTAK</th>
                        <th>KELAS</th>
                        <th>STATUS</th>
                        <th class="text-end pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $student->name }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <code class="student-nis">{{ $student->nis_nip }}</code>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="student-contact">
                                <div class="text-truncate" style="max-width: 200px;">
                                    <i class="bi bi-envelope me-1 text-muted"></i>
                                    {{ $student->email }}
                                </div>
                                @if($student->phone)
                                <small class="text-muted">
                                    <i class="bi bi-phone me-1"></i>
                                    {{ $student->phone }}
                                </small>
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
                        <td class="pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('students.show', $student) }}" 
                                   class="btn btn-icon btn-sm" 
                                   data-bs-toggle="tooltip" 
                                   title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('students.edit', $student) }}" 
                                   class="btn btn-icon btn-sm" 
                                   data-bs-toggle="tooltip" 
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-sm" 
                                            type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="#" class="dropdown-item">
                                            <i class="bi bi-calendar-check me-2"></i>
                                            Absensi
                                        </a>
                                        <a href="#" class="dropdown-item">
                                            <i class="bi bi-journal-text me-2"></i>
                                            Nilai
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <button type="button" 
                                                class="dropdown-item text-danger"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $student->id }}">
                                            <i class="bi bi-trash me-2"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($students->hasPages())
        <div class="card-footer">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div>
                    <p class="mb-0 text-muted small">
                        Menampilkan <strong>{{ $students->firstItem() ?? 0 }}-{{ $students->lastItem() ?? 0 }}</strong> 
                        dari <strong>{{ $students->total() }}</strong> siswa
                    </p>
                </div>
                <nav aria-label="Page navigation">
                    {{ $students->links('vendor.pagination.custom') }}
                </nav>
            </div>
        </div>
        @endif

        @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-person"></i>
            </div>
            <h5>Belum ada siswa</h5>
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
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>
                    Import Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="import-icon mb-3">
                        <i class="bi bi-file-earmark-excel"></i>
                    </div>
                    <p>Upload file Excel dengan format yang sesuai</p>
                </div>
                
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-2"></i>
                    Download template terlebih dahulu untuk memastikan format benar
                </div>
                
                <div class="mt-4 text-center">
                    <a href="#" class="btn btn-outline-primary me-2">
                        <i class="bi bi-download me-2"></i>
                        Template
                    </a>
                    <button class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>
                        Upload File
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modals -->
@foreach($students as $student)
<div class="modal fade" id="deleteModal{{ $student->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="student-avatar-lg mb-3">
                        {{ strtoupper(substr($student->name, 0, 2)) }}
                    </div>
                    <h6>{{ $student->name }}</h6>
                    <p class="text-muted mb-0">{{ $student->nis_nip }}</p>
                </div>
                <p class="text-center">Apakah Anda yakin ingin menghapus siswa <strong>{{ $student->name }}</strong>?</p>
                <div class="alert alert-danger small">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Tindakan ini tidak dapat dibatalkan. Semua data siswa akan dihapus permanen.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('students.destroy', $student) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

<style>
:root {
    --primary-color: #4f46e5;
    --primary-light: #e0e7ff;
    --success-color: #10b981;
    --success-light: #d1fae5;
    --danger-color: #ef4444;
    --danger-light: #fee2e2;
    --warning-color: #f59e0b;
    --warning-light: #fef3c7;
    --border-radius: 12px;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --transition: all 0.2s ease;
}

/* Page Header */
.page-header {
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.page-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Filter Form */
.filter-form .input-group-search {
    position: relative;
}

.filter-form .input-group-text {
    background: white;
    border-right: none;
    border-radius: 8px 0 0 8px;
}

.filter-form .form-control,
.filter-form .form-select {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: var(--transition);
}

.filter-form .form-control:focus,
.filter-form .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Card Styles */
.card {
    border: none;
    box-shadow: var(--shadow-sm);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.card-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 1.25rem 1.5rem;
}

.card-title {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.card-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

/* Table Styles */
.table {
    margin: 0;
}

.table thead th {
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.table tbody td {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.table tbody tr:hover {
    background-color: #f9fafb;
}

/* Student Avatar */
.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary-color), #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.student-avatar-lg {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--primary-color), #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.25rem;
    margin: 0 auto;
}

.student-nis {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-family: 'SF Mono', Monaco, monospace;
    font-size: 0.75rem;
    color: #374151;
}

/* Class Tags */
.class-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    max-width: 150px;
}

.class-tag {
    background: var(--primary-light);
    color: var(--primary-color);
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
}

.class-tag-more {
    background: #f3f4f6;
    color: #6b7280;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
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

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: var(--transition);
}

.btn-primary {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
}

.btn-icon {
    width: 32px;
    height: 32px;
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
    color: var(--primary-color);
    border-color: #d1d5db;
}

/* Dropdown */
.dropdown-menu {
    border: none;
    box-shadow: var(--shadow-lg);
    border-radius: 8px;
    padding: 0.5rem;
    min-width: 160px;
}

.dropdown-item {
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
}

.dropdown-item:hover {
    background: #f9fafb;
}

/* Empty State */
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: #f9fafb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #9ca3af;
}

.empty-state h5 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

/* Import Modal */
.import-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    background: var(--primary-light);
    color: var(--primary-color);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Modal */
.modal-content {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
}

/* Responsive */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .page-header .d-flex {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-form .row {
        gap: 1rem;
    }
    
    .filter-form .col-md-2,
    .filter-form .col-md-3,
    .filter-form .col-md-5 {
        width: 100%;
    }
    
    .card-header {
        padding: 1rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.75rem 1rem;
    }
    
    .student-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .btn-icon {
        width: 28px;
        height: 28px;
    }
    
    .class-tags {
        max-width: 100px;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .card,
    .modal-content {
        background: #1f2937;
        border-color: #374151;
    }
    
    .page-title,
    .card-title,
    .table tbody h6 {
        color: #f9fafb;
    }
    
    .page-subtitle,
    .card-subtitle,
    .text-muted {
        color: #9ca3af;
    }
    
    .table thead th {
        background: #111827;
        border-color: #374151;
        color: #9ca3af;
    }
    
    .table tbody td {
        border-color: #374151;
        color: #e5e7eb;
    }
    
    .table tbody tr:hover {
        background: #111827;
    }
    
    .student-nis {
        background: #374151;
        color: #e5e7eb;
    }
    
    .class-tag {
        background: #374151;
        color: #e5e7eb;
    }
    
    .class-tag-more {
        background: #4b5563;
        color: #9ca3af;
    }
    
    .btn-icon {
        background: #374151;
        border-color: #4b5563;
        color: #9ca3af;
    }
    
    .btn-icon:hover {
        background: #4b5563;
        color: #e5e7eb;
    }
    
    .dropdown-menu {
        background: #1f2937;
        border: 1px solid #374151;
    }
    
    .dropdown-item {
        color: #e5e7eb;
    }
    
    .dropdown-item:hover {
        background: #374151;
    }
    
    .empty-state-icon {
        background: #374151;
        color: #6b7280;
    }
    
    .status-badge.active {
        background: #064e3b;
        color: #a7f3d0;
    }
    
    .status-badge.inactive {
        background: #374151;
        color: #9ca3af;
    }
    
    .filter-form .form-control,
    .filter-form .form-select {
        background: #1f2937;
        border-color: #374151;
        color: #e5e7eb;
    }
    
    .filter-form .input-group-text {
        background: #1f2937;
        border-color: #374151;
        color: #9ca3af;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh button
    document.getElementById('refreshBtn')?.addEventListener('click', function() {
        this.classList.add('rotate');
        setTimeout(() => {
            window.location.reload();
        }, 500);
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
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
});

// Add animation for refresh button
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