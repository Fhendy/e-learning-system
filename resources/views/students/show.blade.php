@extends('layouts.app')

@section('title', $student->name)

@section('content')
<div class="container-fluid px-0 px-md-3">
    <!-- Page Header -->
    <div class="page-header mb-6">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-3">
                <div class="student-avatar-large">
                    {{ strtoupper(substr($student->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="page-title">{{ $student->name }}</h1>
                    <p class="page-subtitle text-muted">
                        <i class="bi bi-person-badge me-1"></i>{{ $student->nis_nip }}
                        <span class="mx-2">•</span>
                        <i class="bi bi-envelope me-1"></i>{{ $student->email }}
                    </p>
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
    <div class="row mb-6">
        <div class="col-12">
            <div class="stats-grid">
                <!-- Attendance Card -->
                <div class="stats-card">
                    <div class="stats-icon bg-primary-light">
                        <i class="bi bi-check-circle text-primary"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-value">{{ $attendanceStats['attendance_rate'] ?? 0 }}%</h3>
                        <p class="stats-label">Kehadiran</p>
                    </div>
                    <div class="stats-detail">
                        <small class="text-muted">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            {{ $attendanceStats['present'] ?? 0 }} hadir
                        </small>
                    </div>
                </div>

                <!-- Average Score Card -->
                <div class="stats-card">
                    <div class="stats-icon bg-success-light">
                        <i class="bi bi-graph-up text-success"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-value">{{ $gradeStats['average_score'] ?? '-' }}</h3>
                        <p class="stats-label">Rata-rata Nilai</p>
                    </div>
                    <div class="stats-detail">
                        <small class="text-muted">
                            <i class="bi bi-journal-check text-info me-1"></i>
                            {{ $gradeStats['total_graded'] ?? 0 }} tugas dinilai
                        </small>
                    </div>
                </div>

                <!-- Classes Card -->
                <div class="stats-card">
                    <div class="stats-icon bg-info-light">
                        <i class="bi bi-building text-info"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-value">{{ $student->classesAsStudent->count() }}</h3>
                        <p class="stats-label">Kelas</p>
                    </div>
                    <div class="stats-detail">
                        <small class="text-muted">
                            <i class="bi bi-people text-info me-1"></i>
                            Total kelas diikuti
                        </small>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="stats-card">
                    <div class="stats-icon {{ $student->is_active ? 'bg-success-light' : 'bg-danger-light' }}">
                        <i class="bi bi-power {{ $student->is_active ? 'text-success' : 'text-danger' }}"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-value">{{ $student->is_active ? 'Aktif' : 'Nonaktif' }}</h3>
                        <p class="stats-label">Status</p>
                    </div>
                    <div class="stats-detail">
                        @if($student->is_active)
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Siswa aktif
                        </small>
                        @else
                        <small class="text-danger">
                            <i class="bi bi-x-circle-fill me-1"></i>
                            Siswa nonaktif
                        </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Student Information -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Siswa</h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <small class="text-muted">Nama Lengkap</small>
                                <p class="mb-0">{{ $student->name }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div>
                                <small class="text-muted">NIS</small>
                                <p class="mb-0">{{ $student->nis_nip }}</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div>
                                <small class="text-muted">Email</small>
                                <p class="mb-0">{{ $student->email }}</p>
                            </div>
                        </div>
                        @if($student->phone)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-phone"></i>
                            </div>
                            <div>
                                <small class="text-muted">Telepon</small>
                                <p class="mb-0">{{ $student->phone }}</p>
                            </div>
                        </div>
                        @endif
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar"></i>
                            </div>
                            <div>
                                <small class="text-muted">Bergabung</small>
                                <p class="mb-0">{{ $student->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classes Card -->
            @if($student->classesAsStudent->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Kelas</h5>
                </div>
                <div class="card-body">
                    <div class="classes-list">
                        @foreach($student->classesAsStudent as $class)
                        <div class="class-item">
                            <div class="class-icon">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $class->class_name }}</h6>
                                <div class="text-muted small">
                                    <span class="me-2">{{ $class->class_code }}</span>
                                    <span class="me-2">•</span>
                                    <span>{{ $class->students->count() }} siswa</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Activity & Attendance -->
        <div class="col-lg-8">
            <!-- Recent Activity -->
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="activityTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" 
                                    data-bs-target="#attendance" type="button" role="tab">
                                <i class="bi bi-calendar-check me-2"></i>
                                Absensi Terbaru
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="submissions-tab" data-bs-toggle="tab" 
                                    data-bs-target="#submissions" type="button" role="tab">
                                <i class="bi bi-journal-check me-2"></i>
                                Pengumpulan
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="activityTabsContent">
                        <!-- Attendance Tab -->
                        <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                            @if($student->attendances && $student->attendances->count() > 0)
                            <div class="activity-list">
                                @foreach($student->attendances->take(10) as $attendance)
                                <div class="activity-item">
                                    <div class="activity-icon bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'late' ? 'warning' : 'danger') }}">
                                        <i class="bi bi-{{ $attendance->status == 'present' ? 'check' : ($attendance->status == 'late' ? 'clock' : 'x') }}"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h6 class="mb-1">{{ $attendance->class->class_name ?? 'Kelas' }}</h6>
                                        <p class="text-muted small mb-0">
                                            {{ ucfirst($attendance->status) }}
                                            <span class="mx-1">•</span>
                                            {{ $attendance->attendance_date->format('d M Y') }}
                                            <span class="mx-1">•</span>
                                            {{ $attendance->created_at->format('H:i') }}
                                        </p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="empty-state-small text-center py-4">
                                <i class="bi bi-calendar-x display-5 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">Belum ada catatan absensi</p>
                            </div>
                            @endif
                        </div>

                        <!-- Submissions Tab -->
                        <div class="tab-pane fade" id="submissions" role="tabpanel">
                            @if($student->submissions && $student->submissions->count() > 0)
                            <div class="activity-list">
                                @foreach($student->submissions->take(10) as $submission)
                                <div class="activity-item">
                                    <div class="activity-icon bg-info">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h6 class="mb-1">{{ $submission->assignment->title ?? 'Tugas' }}</h6>
                                        <div class="d-flex justify-content-between">
                                            <p class="text-muted small mb-0">
                                                {{ $submission->submitted_at->format('d M Y H:i') }}
                                            </p>
                                            @if($submission->score)
                                            <span class="badge bg-success">{{ $submission->score }}</span>
                                            @else
                                            <span class="badge bg-warning">Belum dinilai</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="empty-state-small text-center py-4">
                                <i class="bi bi-journal-x display-5 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">Belum ada pengumpulan tugas</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistik Kehadiran</h5>
                </div>
                <div class="card-body">
                    <div class="attendance-stats-grid">
                        <div class="attendance-stat">
                            <div class="stat-icon bg-success-light">
                                <i class="bi bi-check-circle text-success"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value">{{ $attendanceStats['present'] ?? 0 }}</h4>
                                <p class="stat-label">Hadir</p>
                            </div>
                        </div>
                        <div class="attendance-stat">
                            <div class="stat-icon bg-warning-light">
                                <i class="bi bi-clock text-warning"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value">{{ $attendanceStats['late'] ?? 0 }}</h4>
                                <p class="stat-label">Terlambat</p>
                            </div>
                        </div>
                        <div class="attendance-stat">
                            <div class="stat-icon bg-danger-light">
                                <i class="bi bi-x-circle text-danger"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value">{{ $attendanceStats['absent'] ?? 0 }}</h4>
                                <p class="stat-label">Absen</p>
                            </div>
                        </div>
                        <div class="attendance-stat">
                            <div class="stat-icon bg-info-light">
                                <i class="bi bi-activity text-info"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value">{{ $attendanceStats['sick'] ?? 0 }}</h4>
                                <p class="stat-label">Sakit</p>
                            </div>
                        </div>
                        <div class="attendance-stat">
                            <div class="stat-icon bg-secondary-light">
                                <i class="bi bi-clipboard-check text-secondary"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value">{{ $attendanceStats['permission'] ?? 0 }}</h4>
                                <p class="stat-label">Izin</p>
                            </div>
                        </div>
                        <div class="attendance-stat">
                            <div class="stat-icon bg-primary-light">
                                <i class="bi bi-bar-chart text-primary"></i>
                            </div>
                            <div class="stat-content">
                                <h4 class="stat-value">{{ $attendanceStats['total'] ?? 0 }}</h4>
                                <p class="stat-label">Total</p>
                            </div>
                        </div>
                    </div>
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
    --warning-color: #f59e0b;
    --warning-light: #fef3c7;
    --danger-color: #ef4444;
    --danger-light: #fee2e2;
    --info-color: #06b6d4;
    --info-light: #cffafe;
    --secondary-color: #6b7280;
    --secondary-light: #f3f4f6;
    --border-radius: 12px;
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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

.student-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: linear-gradient(135deg, var(--primary-color), #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.5rem;
    flex-shrink: 0;
}

/* Stats Grid - PERBAIKAN UTAMA */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stats-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-color);
}

.stats-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-primary-light {
    background-color: var(--primary-light);
}

.bg-success-light {
    background-color: var(--success-light);
}

.bg-warning-light {
    background-color: var(--warning-light);
}

.bg-danger-light {
    background-color: var(--danger-light);
}

.bg-info-light {
    background-color: var(--info-light);
}

.bg-secondary-light {
    background-color: var(--secondary-light);
}

.text-primary {
    color: var(--primary-color);
}

.text-success {
    color: var(--success-color);
}

.text-warning {
    color: var(--warning-color);
}

.text-danger {
    color: var(--danger-color);
}

.text-info {
    color: var(--info-color);
}

.text-secondary {
    color: var(--secondary-color);
}

.stats-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.stats-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    line-height: 1;
}

.stats-label {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    margin: 0;
}

.stats-detail {
    margin-top: auto;
}

/* Card Styles */
.card {
    border: none;
    box-shadow: var(--shadow-sm);
    border-radius: var(--border-radius);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.card-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
}

.card-title {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    font-size: 1rem;
}

.card-body {
    padding: 1.5rem;
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
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
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.info-item small {
    font-size: 0.75rem;
    color: #6b7280;
    display: block;
    margin-bottom: 0.25rem;
}

.info-item p {
    font-size: 0.875rem;
    color: #1f2937;
    margin: 0;
    font-weight: 500;
}

/* Classes List */
.classes-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.class-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    background: #f9fafb;
    transition: var(--transition);
}

.class-item:hover {
    background: #f3f4f6;
}

.class-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--primary-light);
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.class-item h6 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
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
    font-size: 0.875rem;
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

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    transition: var(--transition);
}

.activity-item:hover {
    background: #f9fafb;
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
    color: white;
}

.bg-success {
    background-color: var(--success-color);
}

.bg-warning {
    background-color: var(--warning-color);
}

.bg-danger {
    background-color: var(--danger-color);
}

.bg-info {
    background-color: var(--info-color);
}

.activity-content {
    flex-grow: 1;
}

.activity-content h6 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

/* Attendance Statistics Grid */
.attendance-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
}

.attendance-stat {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    transition: var(--transition);
}

.attendance-stat:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin: 0 auto 0.75rem;
}

.stat-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Empty State */
.empty-state-small {
    padding: 2rem 0;
}

.empty-state-small i {
    font-size: 3rem;
    opacity: 0.3;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: var(--transition);
}

.btn-secondary {
    background: #6b7280;
    border-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    border-color: #4b5563;
    color: white;
}

.btn-outline-secondary {
    border-color: #d1d5db;
    color: #6b7280;
}

.btn-outline-secondary:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
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
    
    .student-avatar-large {
        width: 60px;
        height: 60px;
        font-size: 1.25rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .attendance-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .card-header,
    .card-body {
        padding: 1rem;
    }
    
    .info-icon {
        width: 28px;
        height: 28px;
        font-size: 0.875rem;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .stats-card,
    .card,
    .attendance-stat {
        background: #1f2937;
        border-color: #374151;
    }
    
    .page-title,
    .stats-value,
    .stat-value,
    .info-item p,
    .class-item h6,
    .activity-content h6 {
        color: #f9fafb;
    }
    
    .page-subtitle,
    .stats-label,
    .stat-label,
    .info-item small,
    .text-muted {
        color: #9ca3af;
    }
    
    .stats-card:hover,
    .attendance-stat:hover {
        border-color: var(--primary-color);
    }
    
    .info-icon {
        background: #374151;
        color: #e5e7eb;
    }
    
    .class-item {
        background: #374151;
    }
    
    .class-item:hover {
        background: #4b5563;
    }
    
    .class-icon {
        background: #4b5563;
        color: #e5e7eb;
    }
    
    .activity-item:hover {
        background: #374151;
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
    
    .bg-primary-light { background-color: rgba(79, 70, 229, 0.2); }
    .bg-success-light { background-color: rgba(16, 185, 129, 0.2); }
    .bg-warning-light { background-color: rgba(245, 158, 11, 0.2); }
    .bg-danger-light { background-color: rgba(239, 68, 68, 0.2); }
    .bg-info-light { background-color: rgba(6, 182, 212, 0.2); }
    .bg-secondary-light { background-color: rgba(107, 114, 128, 0.2); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const triggerTabList = [].slice.call(document.querySelectorAll('#activityTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
            
            // Save active tab to localStorage
            const activeTab = triggerEl.getAttribute('data-bs-target');
            localStorage.setItem('activeStudentTab', activeTab);
        });
    });
    
    // Restore active tab from localStorage
    const activeTab = localStorage.getItem('activeStudentTab');
    if (activeTab) {
        const triggerEl = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (triggerEl) {
            bootstrap.Tab.getInstance(triggerEl)?.show();
        }
    }
});
</script>
@endsection