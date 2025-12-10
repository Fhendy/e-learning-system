@extends('layouts.app')

@section('title', $class->class_name)

@section('content')
<div class="container-fluid px-0 px-md-3">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="class-icon-large">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title">{{ $class->class_name }}</h1>
                        <p class="page-subtitle text-muted">
                            <i class="bi bi-hash me-1"></i>{{ $class->class_code }}
                            @if($class->subject)
                            <span class="mx-2">•</span>
                            <i class="bi bi-book me-1"></i>{{ $class->subject }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if(auth()->user()->isTeacher() || auth()->user()->isAdmin())
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-plus-lg me-2"></i>Tambah
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('students.create') }}?class_id={{ $class->id }}" class="dropdown-item">
                            <i class="bi bi-person-plus me-2"></i>Siswa
                        </a>
                        <a href="{{ route('assignments.teacher.create') }}?class_id={{ $class->id }}" class="dropdown-item">
                            <i class="bi bi-journal-plus me-2"></i>Tugas
                        </a>
                        <a href="{{ route('qr-codes.create') }}?class_id={{ $class->id }}" class="dropdown-item">
                            <i class="bi bi-qr-code me-2"></i>QR Code
                        </a>
                    </div>
                </div>
                <a href="{{ route('classes.edit', $class) }}" class="btn btn-secondary">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>
                @endif
                <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
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

    <!-- Quick Stats -->
    <div class="stats-grid mb-6">
        <div class="stats-card">
            <div class="stats-icon bg-primary-light text-primary">
                <i class="bi bi-people"></i>
            </div>
            <div class="stats-content">
                <h3 class="stats-value">{{ $students->total() }}</h3>
                <p class="stats-label">Total Siswa</p>
            </div>
        </div>

        <div class="stats-card">
            <div class="stats-icon bg-success-light text-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stats-content">
                <h3 class="stats-value">{{ $attendanceStats['present'] ?? 0 }}</h3>
                <p class="stats-label">Hadir</p>
            </div>
        </div>

        <div class="stats-card">
            <div class="stats-icon bg-warning-light text-warning">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stats-content">
                <h3 class="stats-value">{{ $attendanceStats['late'] ?? 0 }}</h3>
                <p class="stats-label">Terlambat</p>
            </div>
        </div>

        <div class="stats-card">
            <div class="stats-icon bg-danger-light text-danger">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stats-content">
                <h3 class="stats-value">{{ $attendanceStats['absent'] ?? 0 }}</h3>
                <p class="stats-label">Absen</p>
            </div>
        </div>
    </div>

    <!-- Class Info & Tabs -->
    <div class="row">
        <!-- Class Information -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Kelas</h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <i class="bi bi-hash text-primary"></i>
                            <div>
                                <small class="text-muted">Kode Kelas</small>
                                <p class="mb-0">{{ $class->class_code }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-book text-primary"></i>
                            <div>
                                <small class="text-muted">Mata Pelajaran</small>
                                <p class="mb-0">{{ $class->subject ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-person-badge text-primary"></i>
                            <div>
                                <small class="text-muted">Wali Kelas</small>
                                <p class="mb-0">{{ $class->teacher->name ?? 'Belum ada guru' }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <div>
                                <small class="text-muted">Tahun Ajaran</small>
                                <p class="mb-0">{{ $class->school_year ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-calendar-range text-primary"></i>
                            <div>
                                <small class="text-muted">Semester</small>
                                <p class="mb-0">{{ $class->semester ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="bi bi-power text-primary"></i>
                            <div>
                                <small class="text-muted">Status</small>
                                <p class="mb-0">
                                    @if($class->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                    @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    @if($class->description)
                    <div class="mt-4">
                        <h6 class="mb-2">Deskripsi</h6>
                        <p class="text-muted mb-0">{{ $class->description }}</p>
                    </div>
                    @endif
                    
                    <div class="mt-4">
                        <div class="attendance-progress">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Kehadiran</small>
                                <small>{{ $attendanceStats['attendance_rate'] ?? 0 }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" 
                                     style="width: {{ $attendanceStats['attendance_rate'] ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="classTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="students-tab" data-bs-toggle="tab" 
                                    data-bs-target="#students" type="button" role="tab">
                                <i class="bi bi-people me-2"></i>
                                Siswa
                                <span class="badge bg-primary rounded-pill ms-1">{{ $students->total() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" 
                                    data-bs-target="#assignments" type="button" role="tab">
                                <i class="bi bi-journal-text me-2"></i>
                                Tugas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" 
                                    data-bs-target="#attendance" type="button" role="tab">
                                <i class="bi bi-calendar-check me-2"></i>
                                Absensi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="qrcodes-tab" data-bs-toggle="tab" 
                                    data-bs-target="#qrcodes" type="button" role="tab">
                                <i class="bi bi-qr-code me-2"></i>
                                QR Codes
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="classTabsContent">
                        <!-- Students Tab -->
                        <div class="tab-pane fade show active" id="students" role="tabpanel">
                            @if($students->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">NAMA SISWA</th>
                                            <th>KEHADIRAN</th>
                                            <th class="text-end pe-4">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="student-avatar">
                                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $student->name }}</h6>
                                                        <div class="text-muted small">
                                                            {{ $student->nis_nip }} • {{ $student->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $total = $student->attendances_count;
                                                    $present = $student->present_count;
                                                    $late = $student->late_count;
                                                    $absent = $student->absent_count;
                                                    $attended = $present + $late;
                                                    $percentage = $total > 0 ? round(($attended / $total) * 100) : 0;
                                                @endphp
                                                <div class="attendance-bar">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small>{{ $attended }}/{{ $total }}</small>
                                                        <small>{{ $percentage }}%</small>
                                                    </div>
                                                    <div class="progress" style="height: 4px;">
                                                        <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="pe-4">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="{{ route('students.show', $student) }}" 
                                                       class="btn btn-icon btn-sm" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if(auth()->user()->isTeacher() || auth()->user()->isAdmin())
                                                    <div class="dropdown">
                                                        <button class="btn btn-icon btn-sm" 
                                                                type="button" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a href="{{ route('students.edit', $student) }}" 
                                                               class="dropdown-item">
                                                                <i class="bi bi-pencil me-2"></i>
                                                                Edit
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <button type="button" 
                                                                    class="dropdown-item text-danger"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#removeStudentModal{{ $student->id }}">
                                                                <i class="bi bi-person-dash me-2"></i>
                                                                Keluarkan
                                                            </button>
                                                        </div>
                                                    </div>
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
                                <div class="empty-state-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h5>Belum ada siswa</h5>
                                <p class="text-muted mb-4">Tambahkan siswa pertama ke kelas ini</p>
                                <a href="{{ route('students.create') }}?class_id={{ $class->id }}" class="btn btn-primary">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Tambah Siswa
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Assignments Tab -->
                        <div class="tab-pane fade" id="assignments" role="tabpanel">
                            @if($assignments->count() > 0)
                            <div class="assignment-list">
                                @foreach($assignments as $assignment)
                                <div class="assignment-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $assignment->title }}</h6>
                                            <p class="text-muted small mb-2">{{ Str::limit($assignment->description, 100) }}</p>
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="badge bg-info">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    {{ $assignment->due_date->format('d/m/Y H:i') }}
                                                </span>
                                                <span class="text-muted small">
                                                    <i class="bi bi-journal-check me-1"></i>
                                                    {{ $assignment->submissions_count }}/{{ $students->total() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ route('assignments.show', $assignment) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-center">
                                <a href="{{ route('assignments.teacher.index') }}?class_id={{ $class->id }}" 
                                   class="btn btn-outline-primary">
                                    Lihat Semua Tugas
                                </a>
                            </div>
                            @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-state-icon">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <h5>Belum ada tugas</h5>
                                <p class="text-muted mb-4">Buat tugas pertama untuk kelas ini</p>
                                <a href="{{ route('assignments.teacher.create') }}?class_id={{ $class->id }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-journal-plus me-2"></i>
                                    Buat Tugas
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Attendance Tab -->
                        <div class="tab-pane fade" id="attendance" role="tabpanel">
                            <div class="attendance-stats mb-4">
                                <h6 class="mb-3">Statistik Kehadiran</h6>
                                <div class="row text-center">
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="stat-card bg-success-light">
                                            <div class="stat-value text-success">{{ $attendanceStats['present'] ?? 0 }}</div>
                                            <div class="stat-label">Hadir</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="stat-card bg-warning-light">
                                            <div class="stat-value text-warning">{{ $attendanceStats['late'] ?? 0 }}</div>
                                            <div class="stat-label">Terlambat</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="stat-card bg-danger-light">
                                            <div class="stat-value text-danger">{{ $attendanceStats['absent'] ?? 0 }}</div>
                                            <div class="stat-label">Absen</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="stat-card bg-primary-light">
                                            <div class="stat-value text-primary">{{ $attendanceStats['attendance_rate'] ?? 0 }}%</div>
                                            <div class="stat-label">Rata-rata</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-actions">
                                <h6 class="mb-3">Aksi Cepat</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('attendance.manual') }}?class_id={{ $class->id }}" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-pencil-square me-2"></i>
                                        Input Manual
                                    </a>
                                    <a href="{{ route('qr-codes.create') }}?class_id={{ $class->id }}" 
                                       class="btn btn-outline-success">
                                        <i class="bi bi-qr-code me-2"></i>
                                        Buat QR Code
                                    </a>
                                    <a href="{{ route('attendance.teacher.index') }}?class_id={{ $class->id }}" 
                                       class="btn btn-outline-info">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        Detail Absensi
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- QR Codes Tab -->
                        <div class="tab-pane fade" id="qrcodes" role="tabpanel">
                            @if($recentQrCodes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>TANGGAL</th>
                                            <th>WAKTU</th>
                                            <th>STATUS</th>
                                            <th class="text-end">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentQrCodes as $qrCode)
                                        <tr>
                                            <td>{{ $qrCode->date->format('d/m/Y') }}</td>
                                            <td>{{ $qrCode->start_time }} - {{ $qrCode->end_time }}</td>
                                            <td>
                                                @if($qrCode->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                                @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('qr-codes.show', $qrCode) }}" 
                                                   class="btn btn-icon btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 text-center">
                                <a href="{{ route('qr-codes.index') }}?class_id={{ $class->id }}" 
                                   class="btn btn-outline-primary">
                                    Lihat Semua QR Code
                                </a>
                            </div>
                            @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-state-icon">
                                    <i class="bi bi-qr-code"></i>
                                </div>
                                <h5>Belum ada QR Code</h5>
                                <p class="text-muted mb-4">Buat QR Code untuk absensi kelas</p>
                                <a href="{{ route('qr-codes.create') }}?class_id={{ $class->id }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-qr-code me-2"></i>
                                    Buat QR Code
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remove Student Modals -->
@foreach($students as $student)
<div class="modal fade" id="removeStudentModal{{ $student->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="student-avatar-lg mb-3">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <h6>{{ $student->name }}</h6>
                    <p class="text-muted mb-0">{{ $student->nis_nip }}</p>
                </div>
                <p class="text-center">Apakah Anda yakin ingin mengeluarkan <strong>{{ $student->name }}</strong> dari kelas <strong>{{ $class->class_name }}</strong>?</p>
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-2"></i>
                    Data absensi dan tugas siswa di kelas ini akan tetap tersimpan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('classes.remove-student', [$class, $student]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Keluarkan</button>
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
    --warning-color: #f59e0b;
    --warning-light: #fef3c7;
    --danger-color: #ef4444;
    --danger-light: #fee2e2;
    --info-color: #06b6d4;
    --info-light: #cffafe;
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

.class-icon-large {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--primary-color), #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stats-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
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
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
}

.stats-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    line-height: 1;
}

.stats-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0.25rem 0 0;
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
    padding: 1rem 1.25rem;
}

.card-title {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
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

.info-item i {
    font-size: 1.125rem;
    margin-top: 0.125rem;
}

.info-item small {
    font-size: 0.75rem;
}

/* Student Avatar */
.student-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
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
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.5rem;
    margin: 0 auto;
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
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.table tbody tr:hover {
    background-color: #f9fafb;
}

/* Attendance Bar */
.attendance-bar {
    min-width: 120px;
}

/* Assignment List */
.assignment-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.assignment-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 1rem;
    transition: var(--transition);
}

.assignment-card:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow-sm);
}

/* Attendance Stats */
.stat-card {
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: var(--transition);
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

/* Empty State */
.empty-state {
    padding: 3rem 1rem;
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

/* Tabs */
.nav-tabs {
    border-bottom: 1px solid #e5e7eb;
    gap: 0.5rem;
}

.nav-tabs .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    padding: 0.75rem 1rem;
    color: #6b7280;
    font-weight: 500;
    display: flex;
    align-items: center;
}

.nav-tabs .nav-link:hover {
    color: var(--primary-color);
    background: #f9fafb;
}

.nav-tabs .nav-link.active {
    color: var(--primary-color);
    background: white;
    border-bottom: 2px solid var(--primary-color);
}

/* Progress */
.progress {
    border-radius: 4px;
    background: #f3f4f6;
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
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .page-header .d-flex {
        flex-direction: column;
        align-items: stretch;
    }
    
    .class-icon-large {
        width: 48px;
        height: 48px;
        font-size: 1.25rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.75rem;
    }
    
    .student-avatar {
        width: 32px;
        height: 32px;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
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
    
    .progress {
        background: #374151;
    }
    
    .nav-tabs {
        border-color: #374151;
    }
    
    .nav-tabs .nav-link {
        color: #9ca3af;
    }
    
    .nav-tabs .nav-link:hover {
        background: #374151;
        color: #e5e7eb;
    }
    
    .nav-tabs .nav-link.active {
        background: #1f2937;
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const triggerTabList = [].slice.call(document.querySelectorAll('#classTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
            
            // Save active tab to localStorage
            const activeTab = triggerEl.getAttribute('data-bs-target');
            localStorage.setItem('activeClassTab', activeTab);
        });
    });
    
    // Restore active tab from localStorage
    const activeTab = localStorage.getItem('activeClassTab');
    if (activeTab) {
        const triggerEl = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (triggerEl) {
            bootstrap.Tab.getInstance(triggerEl)?.show();
        }
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection