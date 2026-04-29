@extends('layouts.app')

@section('title', $student->name)

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="student-avatar-large">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="page-title mb-1">{{ $student->name }}</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-person-badge me-1"></i>{{ $student->nis_nip }}
                            <span class="mx-2">•</span>
                            <i class="bi bi-envelope me-1"></i>{{ $student->email }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('students.edit', $student) }}" class="btn btn-secondary">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Student Stats -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-primary-light text-primary">
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $attendanceStats['attendance_rate'] ?? 0 }}%</h3>
                        <p class="stats-label mb-0">Kehadiran</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-graph-up fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $gradeStats['average_score'] ?? '-' }}</h3>
                        <p class="stats-label mb-0">Rata-rata Nilai</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-info-light text-info">
                        <i class="bi bi-building fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $student->classesAsStudent->count() }}</h3>
                        <p class="stats-label mb-0">Kelas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon {{ $student->is_active ? 'bg-success-light' : 'bg-danger-light' }}">
                        <i class="bi bi-power fs-5 {{ $student->is_active ? 'text-success' : 'text-danger' }}"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $student->is_active ? 'Aktif' : 'Nonaktif' }}</h3>
                        <p class="stats-label mb-0">Status</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <!-- Student Information -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi Siswa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Nama Lengkap</div>
                                <div class="info-value">{{ $student->name }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">NIS</div>
                                <div class="info-value">{{ $student->nis_nip }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $student->email }}</div>
                            </div>
                        </div>
                        @if($student->phone)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-phone"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Telepon</div>
                                <div class="info-value">{{ $student->phone }}</div>
                            </div>
                        </div>
                        @endif
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Bergabung</div>
                                <div class="info-value">{{ $student->created_at->format('d F Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classes Card -->
            @if($student->classesAsStudent->count() > 0)
            <div class="card mt-3 mt-md-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people me-2 text-primary"></i>
                        Kelas yang Diikuti
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="classes-list">
                        @foreach($student->classesAsStudent as $class)
                        <div class="class-item">
                            <div class="class-icon">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <div class="class-info">
                                <div class="class-name">{{ $class->class_name }}</div>
                                <div class="class-meta">
                                    <span class="class-code">{{ $class->class_code }}</span>
                                    <span class="text-muted">•</span>
                                    <span>{{ $class->students->count() }} siswa</span>
                                </div>
                            </div>
                            <a href="{{ route('classes.show', $class) }}" class="btn btn-icon btn-sm" title="Lihat Kelas">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Activity & Attendance -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white p-0 p-md-3">
                    <ul class="nav nav-tabs card-header-tabs" id="activityTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" 
                                    data-bs-target="#attendance" type="button" role="tab">
                                <i class="bi bi-calendar-check me-1 me-md-2"></i>
                                <span>Absensi Terbaru</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="submissions-tab" data-bs-toggle="tab" 
                                    data-bs-target="#submissions" type="button" role="tab">
                                <i class="bi bi-journal-check me-1 me-md-2"></i>
                                <span>Pengumpulan Tugas</span>
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-0">
                    <div class="tab-content" id="activityTabsContent">
                        <!-- Attendance Tab -->
                        <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                            @if(isset($student->attendances) && $student->attendances->count() > 0)
                            <div class="activity-list p-3">
                                @foreach($student->attendances->take(10) as $attendance)
                                <div class="activity-item">
                                    <div class="activity-icon {{ $attendance->status == 'present' ? 'bg-success' : ($attendance->status == 'late' ? 'bg-warning' : 'bg-danger') }}">
                                        <i class="bi bi-{{ $attendance->status == 'present' ? 'check' : ($attendance->status == 'late' ? 'clock' : 'x') }}"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ $attendance->class->class_name ?? 'Kelas' }}</div>
                                        <div class="activity-meta">
                                            <span class="badge {{ $attendance->status == 'present' ? 'bg-success' : ($attendance->status == 'late' ? 'bg-warning' : 'bg-danger') }}">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                            <span class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}
                                            </span>
                                            <span class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($attendance->created_at)->format('H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-icon mx-auto mb-3">
                                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                </div>
                                <h5 class="mb-2">Belum ada catatan absensi</h5>
                                <p class="text-muted">Belum ada riwayat absensi untuk siswa ini</p>
                            </div>
                            @endif
                        </div>

                        <!-- Submissions Tab -->
                        <div class="tab-pane fade" id="submissions" role="tabpanel">
                            @if(isset($student->submissions) && $student->submissions->count() > 0)
                            <div class="activity-list p-3">
                                @foreach($student->submissions->take(10) as $submission)
                                <div class="activity-item">
                                    <div class="activity-icon bg-info">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ $submission->assignment->title ?? 'Tugas' }}</div>
                                        <div class="activity-meta">
                                            <span class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d M Y H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="activity-score">
                                        @if($submission->score)
                                            <span class="badge bg-success fs-6">{{ $submission->score }}</span>
                                        @else
                                            <span class="badge bg-warning">Belum dinilai</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-icon mx-auto mb-3">
                                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                                </div>
                                <h5 class="mb-2">Belum ada pengumpulan tugas</h5>
                                <p class="text-muted">Belum ada riwayat pengumpulan tugas</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Statistics -->
            <div class="card mt-3 mt-md-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2 text-primary"></i>
                        Statistik Kehadiran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 g-md-3">
                        <div class="col-4 col-md-2">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-success-light">
                                <div class="stat-mini-value text-success fw-bold fs-3">{{ $attendanceStats['present'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Hadir</div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-warning-light">
                                <div class="stat-mini-value text-warning fw-bold fs-3">{{ $attendanceStats['late'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Terlambat</div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-danger-light">
                                <div class="stat-mini-value text-danger fw-bold fs-3">{{ $attendanceStats['absent'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Absen</div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-info-light">
                                <div class="stat-mini-value text-info fw-bold fs-3">{{ $attendanceStats['sick'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Sakit</div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-secondary-light">
                                <div class="stat-mini-value text-secondary fw-bold fs-3">{{ $attendanceStats['permission'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Izin</div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-primary-light">
                                <div class="stat-mini-value text-primary fw-bold fs-3">{{ $attendanceStats['total'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Total</div>
                            </div>
                        </div>
                    </div>
                </div>
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

.page-title {
    font-size: clamp(1.25rem, 5vw, 1.5rem);
    font-weight: 700;
    color: #1f2937;
}

.page-subtitle {
    font-size: 0.75rem;
    color: #6b7280;
}

.student-avatar-large {
    width: clamp(56px, 10vw, 70px);
    height: clamp(56px, 10vw, 70px);
    border-radius: 16px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: clamp(1.25rem, 3vw, 1.5rem);
    flex-shrink: 0;
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

.card-body {
    padding: 1rem;
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

/* Classes List */
.classes-list {
    display: flex;
    flex-direction: column;
}

.class-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    transition: var(--transition);
}

.class-item:last-child {
    border-bottom: none;
}

.class-item:hover {
    background-color: #f9fafb;
}

.class-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #e0e7ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.class-info {
    flex: 1;
}

.class-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.125rem;
}

.class-meta {
    font-size: 0.688rem;
    color: #6b7280;
}

.class-code {
    background: #f3f4f6;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-family: monospace;
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
    padding: 0.625rem 0.875rem;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.813rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
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

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 10px;
    transition: var(--transition);
}

.activity-item:hover {
    background: #f1f5f9;
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
    flex-shrink: 0;
}

.activity-icon.bg-success { background: #10b981; }
.activity-icon.bg-warning { background: #f59e0b; }
.activity-icon.bg-danger { background: #ef4444; }
.activity-icon.bg-info { background: #06b6d4; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 0.813rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.activity-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}

.activity-meta .badge {
    font-size: 0.688rem;
    padding: 0.125rem 0.5rem;
}

.activity-meta .text-muted {
    font-size: 0.688rem;
}

.activity-score {
    flex-shrink: 0;
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

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
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

.btn-secondary {
    background: #6b7280;
    border-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
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

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.bg-success { background: #10b981 !important; }
.bg-warning { background: #f59e0b !important; }
.bg-danger { background: #ef4444 !important; }
.bg-info { background: #06b6d4 !important; }

/* Colors */
.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-warning-light { background: #fef3c7; }
.bg-danger-light { background: #fee2e2; }
.bg-info-light { background: #cffafe; }
.bg-secondary-light { background: #f3f4f6; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }
.text-info { color: #06b6d4 !important; }
.text-secondary { color: #6b7280 !important; }

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
    
    .student-avatar-large {
        width: 50px;
        height: 50px;
        font-size: 1.125rem;
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
    
    .info-icon {
        width: 28px;
        height: 28px;
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
    .info-item {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-icon {
        margin-bottom: 0;
    }
    
    .activity-meta {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .activity-score {
        align-self: flex-start;
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

.card, .stats-card {
    animation: fadeIn 0.3s ease forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality with localStorage
    const triggerTabList = [].slice.call(document.querySelectorAll('#activityTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
            
            // Save active tab to localStorage
            const activeTab = triggerEl.getAttribute('data-bs-target');
            if (activeTab) {
                localStorage.setItem('activeStudentTab', activeTab);
            }
        });
    });
    
    // Restore active tab from localStorage
    const activeTab = localStorage.getItem('activeStudentTab');
    if (activeTab) {
        const triggerEl = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (triggerEl) {
            const tab = bootstrap.Tab.getInstance(triggerEl);
            if (tab) tab.show();
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