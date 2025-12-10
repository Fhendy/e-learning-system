@extends('layouts.app')

@section('title', 'Daftar Kelas')

@section('content')
<div class="container-fluid px-0 px-md-3">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-people-fill me-3"></i>
                    Daftar Kelas
                </h1>
                <p class="page-subtitle text-muted">
                    Kelola semua kelas dan siswa Anda di satu tempat
                </p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <button class="btn btn-outline-secondary" onclick="showQuickStats()">
                    <i class="bi bi-graph-up me-2"></i>
                    Statistik
                </button>
                <a href="{{ route('classes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Kelas Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-6" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-6" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="stats-grid mb-6">
        <div class="stats-card">
            <div class="stats-icon bg-primary-light text-primary">
                <i class="bi bi-building"></i>
            </div>
            <div class="stats-content">
                <h3 class="stats-value">{{ $classes->total() }}</h3>
                <p class="stats-label">Total Kelas</p>
            </div>
            <div class="stats-trend text-success">
                <i class="bi bi-arrow-up"></i>
                <span>Semua kelas</span>
            </div>
        </div>

        <div class="stats-card">
            <div class="stats-icon bg-success-light text-success">
                <i class="bi bi-people"></i>
            </div>
            <div class="stats-content">
                <h3 class="stats-value">
                    @php
                        $totalStudents = 0;
                        foreach($classes as $class) {
                            $totalStudents += $class->students->count();
                        }
                        echo $totalStudents;
                    @endphp
                </h3>
                <p class="stats-label">Total Siswa</p>
            </div>
            <div class="stats-trend text-info">
                <span>Semua kelas</span>
            </div>
        </div>

        <div class="stats-card">
            <div class="stats-icon bg-info-light text-info">
                <i class="bi bi-calculator"></i>
            </div>
            <div class="stats-content">
                <h3 class="stats-value">
                    @if($classes->count() > 0)
                        {{ round($totalStudents / $classes->count(), 1) }}
                    @else
                        0
                    @endif
                </h3>
                <p class="stats-label">Rata-rata Siswa</p>
            </div>
            <div class="stats-trend text-muted">
                <span>Per kelas</span>
            </div>
        </div>

        <div class="stats-card">
            <div class="stats-icon bg-warning-light text-warning">
                <i class="bi bi-toggle-on"></i>
            </div>
            <div class="stats-content">
                <h3 class="stats-value">
                    @php
                        $activeClasses = $classes->where('is_active', true)->count();
                        echo $activeClasses;
                    @endphp
                </h3>
                <p class="stats-label">Kelas Aktif</p>
            </div>
            <div class="stats-trend">
                <span class="badge bg-success">{{ $activeClasses }} aktif</span>
            </div>
        </div>
    </div>

    <!-- Classes Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="card-title mb-1">Daftar Kelas</h5>
                    <p class="card-subtitle text-muted">
                        {{ $classes->total() }} kelas ditemukan
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari kelas..." id="searchInput">
                    </div>
                </div>
            </div>
        </div>
        
        @if($classes->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">KELAS</th>
                        <th>SISWA</th>
                        <th>STATUS</th>
                        <th class="text-end pe-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $class)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="class-avatar">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $class->class_name }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <code class="class-code">{{ $class->class_code }}</code>
                                        <span class="text-muted">•</span>
                                        <small class="text-muted">
                                            {{ $class->created_at->format('d M Y') }}
                                        </small>
                                    </div>
                                    @if($class->description)
                                    <p class="text-muted small mt-1 mb-0">
                                        {{ Str::limit($class->description, 60) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="student-count">
                                    {{ $class->students_count ?? $class->students->count() }}
                                </span>
                                <span class="text-muted">siswa</span>
                            </div>
                        </td>
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
                        </td>
                        <td class="pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('classes.show', $class) }}" 
                                   class="btn btn-icon btn-sm" 
                                   data-bs-toggle="tooltip" 
                                   title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('attendance.class.show', $class->id) }}" 
                                   class="btn btn-icon btn-sm" 
                                   data-bs-toggle="tooltip" 
                                   title="Absensi">
                                    <i class="bi bi-calendar-check"></i>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-sm" 
                                            type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="{{ route('assignments.teacher.index', ['class_id' => $class->id]) }}" 
                                           class="dropdown-item">
                                            <i class="bi bi-tasks me-2"></i>
                                            Tugas
                                        </a>
                                        <a href="{{ route('classes.edit', $class) }}" 
                                           class="dropdown-item">
                                            <i class="bi bi-pencil me-2"></i>
                                            Edit
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        @if($class->is_active)
                                        <form action="{{ route('classes.deactivate', $class) }}" 
                                              method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="dropdown-item text-danger"
                                                    onclick="return confirm('Nonaktifkan kelas {{ $class->class_name }}?')">
                                                <i class="bi bi-toggle-off me-2"></i>
                                                Nonaktifkan
                                            </button>
                                        </form>
                                        @else
                                        <form action="{{ route('classes.activate', $class) }}" 
                                              method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="dropdown-item text-success"
                                                    onclick="return confirm('Aktifkan kelas {{ $class->class_name }}?')">
                                                <i class="bi bi-toggle-on me-2"></i>
                                                Aktifkan
                                            </button>
                                        </form>
                                        @endif
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
        @if ($classes->hasPages())
        <div class="card-footer">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div>
                    <p class="mb-0 text-muted small">
                        Menampilkan <strong>{{ $classes->firstItem() ?? 0 }}-{{ $classes->lastItem() ?? 0 }}</strong> 
                        dari <strong>{{ $classes->total() }}</strong> kelas
                    </p>
                </div>
                <nav aria-label="Page navigation">
                    {{ $classes->links('vendor.pagination.custom') }}
                </nav>
            </div>
        </div>
        @endif

        @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-people"></i>
            </div>
            <h5>Belum ada kelas</h5>
            <p class="text-muted">Mulai dengan membuat kelas pertama Anda</p>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-graph-up me-2"></i>
                    Statistik Kelas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="quickStatsContent" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat statistik...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #4f46e5;
    --primary-light: #e0e7ff;
    --success-color: #10b981;
    --success-light: #d1fae5;
    --info-color: #06b6d4;
    --info-light: #cffafe;
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
    margin-bottom: 0.5rem;
}

.page-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stats-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid #e5e7eb;
    transition: var(--transition);
}

.stats-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.stats-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.stats-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    line-height: 1;
}

.stats-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0.25rem 0 0.5rem;
}

.stats-trend {
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
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

/* Class Avatar */
.class-avatar {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary-color), #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.class-code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-family: 'SF Mono', Monaco, monospace;
    font-size: 0.75rem;
    color: #374151;
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

/* Modal */
.modal-content {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
    padding: 1.25rem 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

/* Pagination */
.card-footer {
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 1.25rem 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table-responsive {
        border: none;
    }
    
    .card-header {
        padding: 1rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.75rem 1rem;
    }
    
    .class-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .btn-icon {
        width: 28px;
        height: 28px;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .stats-card,
    .card,
    .modal-content {
        background: #1f2937;
        border-color: #374151;
    }
    
    .page-title,
    .card-title,
    .stats-value,
    .table tbody h6 {
        color: #f9fafb;
    }
    
    .page-subtitle,
    .card-subtitle,
    .stats-label,
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
    
    .class-code {
        background: #374151;
        color: #e5e7eb;
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
}
</style>

<script>
function showQuickStats() {
    const modal = new bootstrap.Modal(document.getElementById('quickStatsModal'));
    modal.show();
    
    // Simulasi loading data
    setTimeout(() => {
        document.getElementById('quickStatsContent').innerHTML = `
            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-icon bg-primary-light text-primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-value">{{ $classes->total() }}</h3>
                        <p class="stats-label">Total Kelas</p>
                    </div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-value">
                            @php
                                $totalStudents = 0;
                                foreach($classes as $class) {
                                    $totalStudents += $class->students->count();
                                }
                                echo $totalStudents;
                            @endphp
                        </h3>
                        <p class="stats-label">Total Siswa</p>
                    </div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon bg-info-light text-info">
                        <i class="bi bi-pie-chart"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-value">
                            @if($classes->count() > 0)
                                {{ round($totalStudents / $classes->count(), 1) }}
                            @else
                                0
                            @endif
                        </h3>
                        <p class="stats-label">Rata-rata</p>
                    </div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon bg-warning-light text-warning">
                        <i class="bi bi-toggle-on"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-value">
                            @php
                                $activeClasses = $classes->where('is_active', true)->count();
                                echo $activeClasses;
                            @endphp
                        </h3>
                        <p class="stats-label">Aktif</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-start">
                <h6 class="mb-3">Distribusi Kelas</h6>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" 
                         style="width: {{ $classes->count() > 0 ? ($activeClasses / $classes->count()) * 100 : 0 }}%">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-muted">{{ $activeClasses }} kelas aktif</small>
                    <small class="text-muted">{{ $classes->count() - $activeClasses }} tidak aktif</small>
                </div>
            </div>
        `;
    }, 500);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Refresh button
    document.getElementById('refreshBtn')?.addEventListener('click', function() {
        this.classList.add('rotate');
        setTimeout(() => {
            window.location.reload();
        }, 500);
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
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