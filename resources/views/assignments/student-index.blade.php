@extends('layouts.app')

@section('title', 'Daftar Tugas - Siswa')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tugas Saya</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('assignments.student.index', ['status' => 'pending']) }}" 
               class="btn btn-warning shadow-sm">
                <i class="bi bi-exclamation-triangle me-2"></i>Belum Dikerjakan
            </a>
            <a href="#" class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-filter me-2"></i>Filter
            </a>
        </div>
    </div>

    <!-- Stats Cards untuk Siswa -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tugas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalAssignments ?? $assignments->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-journal-text fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Belum Dikerjakan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $pendingCount ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Sudah Dikerjakan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $completedCount ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Terlambat
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $lateCount ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card shadow mb-4">
        <div class="card-body">
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

    <!-- Tabel Tugas -->
    <div class="card shadow mb-4">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($assignments->count() > 0)
                <div class="row">
                    @foreach($assignments as $assignment)
                    @php
                        // Menggunakan method submissionByStudent dari model Assignment
                        $submission = $assignment->submissionByStudent(auth()->id());
                        
                        // Pastikan due_date adalah Carbon instance
                        try {
                            $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
                                ? $assignment->due_date 
                                : \Carbon\Carbon::parse($assignment->due_date);
                                
                            $now = now();
                            $diffDays = $now->diffInDays($dueDate, false);
                            $isOverdue = $diffDays < 0;
                            $isUrgent = $diffDays <= 3 && $diffDays >= 0;
                            $dueDateFormatted = $dueDate->format('d/m/Y H:i');
                        } catch (\Exception $e) {
                            $dueDate = null;
                            $diffDays = 0;
                            $isOverdue = false;
                            $isUrgent = false;
                            $dueDateFormatted = 'Tanggal tidak valid';
                        }
                        
                        // Tentukan status submission
                        $submissionStatus = $submission ? $submission->status : null;
                        $hasScore = $submission && $submission->score !== null;
                        
                        // Tentukan warna border berdasarkan status
                        $borderColor = 'primary';
                        if ($isOverdue) {
                            $borderColor = 'danger';
                        } elseif ($isUrgent) {
                            $borderColor = 'warning';
                        } elseif ($submissionStatus == 'graded') {
                            $borderColor = 'success';
                        } elseif ($submissionStatus == 'late') {
                            $borderColor = 'danger';
                        }
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card assignment-card h-100 border-left-{{ $borderColor }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        @if($assignment->attachment)
                                            <span class="badge bg-info mb-2">
                                                <i class="bi bi-paperclip me-1"></i>Lampiran
                                            </span>
                                        @endif
                                        <h5 class="card-title mb-1">
                                            <a href="{{ route('assignments.show', $assignment) }}" 
                                               class="text-decoration-none text-dark">
                                                {{ Str::limit($assignment->title, 40) }}
                                            </a>
                                        </h5>
                                        <span class="badge bg-secondary">
                                            {{ $assignment->class->class_name ?? 'Kelas Tidak Ditemukan' }}
                                        </span>
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

                                <p class="card-text text-muted small mb-3">
                                    @if($assignment->description)
                                        {{ Str::limit(strip_tags($assignment->description), 80) }}
                                    @else
                                        <span class="text-muted fst-italic">Tidak ada deskripsi</span>
                                    @endif
                                </p>

                                <div class="assignment-info mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Batas Waktu:</span>
                                        <span class="{{ $isOverdue ? 'text-danger' : ($isUrgent ? 'text-warning' : 'text-success') }} fw-bold">
                                            {{ $dueDateFormatted }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Status Deadline:</span>
                                        <span>
                                            @if($dueDate)
                                                @if($isOverdue)
                                                    <span class="text-danger">Terlambat {{ abs($diffDays) }} hari</span>
                                                @elseif($isUrgent)
                                                    <span class="text-warning">{{ $diffDays }} hari lagi</span>
                                                @else
                                                    <span class="text-success">{{ $diffDays }} hari lagi</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </span>
                                    </div>
                                    @if($hasScore)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Nilai:</span>
                                            <span class="badge bg-{{ $submission->score >= 80 ? 'success' : ($submission->score >= 60 ? 'warning' : 'danger') }}">
                                                {{ $submission->score }}/{{ $assignment->max_score }}
                                                @if($assignment->max_score > 0)
                                                ({{ round(($submission->score / $assignment->max_score) * 100) }}%)
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-person me-1"></i>
                                        {{ $assignment->teacher->name ?? 'Guru Tidak Ditemukan' }}
                                    </small>
                                    <div>
                                        <a href="{{ route('assignments.show', $assignment) }}" 
                                           class="btn btn-sm btn-{{ $submissionStatus ? 'outline-primary' : 'primary' }}">
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
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state text-center py-5">
                    @if(request()->has('status') || request()->has('class_id') || request()->has('start_date') || request()->has('end_date'))
                        <i class="bi bi-search fa-4x text-secondary mb-3"></i>
                        <h5>Tidak ada tugas yang sesuai filter</h5>
                        <p class="text-muted mb-3">
                            @if(request('status') == 'pending')
                                Tidak ada tugas yang belum dikerjakan
                            @elseif(request('status') == 'submitted')
                                Tidak ada tugas yang sudah dikumpulkan
                            @elseif(request('status') == 'graded')
                                Tidak ada tugas yang sudah dinilai
                            @elseif(request('status') == 'late')
                                Tidak ada tugas yang terlambat
                            @else
                                Tidak ada tugas yang sesuai dengan filter
                            @endif
                        </p>
                        <a href="{{ route('assignments.student.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-1"></i>Lihat Semua Tugas
                        </a>
                    @else
                        <i class="bi bi-check-circle fa-4x text-success mb-3"></i>
                        <h5>Tidak ada tugas</h5>
                        <p class="text-muted mb-0">Anda belum memiliki tugas saat ini.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($assignments->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Menampilkan {{ $assignments->firstItem() ?? 0 }} - {{ $assignments->lastItem() ?? 0 }} dari {{ $assignments->total() }} tugas
        </div>
        <div>
            {{ $assignments->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('assignments.student.index') }}">
                <div class="modal-body">
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
                    <div class="row mb-3">
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
                <div class="modal-footer">
                    <a href="{{ route('assignments.student.index') }}" class="btn btn-secondary">Reset Filter</a>
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.assignment-card {
    transition: transform 0.2s;
}
.assignment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }
.border-left-danger { border-left: 4px solid #e74a3b !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}
.nav-tabs .nav-link.active {
    color: #4e73df;
    border-bottom: 2px solid #4e73df;
    font-weight: 600;
}
.empty-state {
    padding: 3rem 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabs = document.querySelectorAll('.nav-link');
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Set active tab based on URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    if (status) {
        const activeTab = document.querySelector(`.nav-link[href*="status=${status}"]`);
        if (activeTab) {
            tabs.forEach(t => t.classList.remove('active'));
            activeTab.classList.add('active');
        }
    }
});
</script>
@endsection