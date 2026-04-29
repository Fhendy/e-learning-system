@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Welcome Section -->
    <div class="welcome-card mb-4">
        <div class="welcome-content">
            <div class="welcome-text">
                <h1 class="welcome-title">Selamat Datang, {{ auth()->user()->name }}</h1>
                <p class="welcome-date">
                    <i class="bi bi-calendar-check"></i>
                    {{ now()->translatedFormat('l, d F Y') }}
                </p>
                <div class="welcome-actions">
                    <a href="{{ route('assignments.student.index') }}" class="btn btn-primary">
                        <i class="bi bi-journal-text me-2"></i>
                        Lihat Tugas
                        @if(($pendingAssignments ?? collect())->count() > 0)
                            <span class="badge bg-danger ms-1">{{ ($pendingAssignments ?? collect())->count() }}</span>
                        @endif
                    </a>
                    <a href="{{ route('attendance.student.index') }}" class="btn btn-outline-light">
                        <i class="bi bi-calendar-check me-2"></i>
                        Riwayat Absensi
                    </a>
                    @if(!($todayAttendance ?? null))
                        <a href="{{ route('attendance.scan.page') }}" class="btn btn-danger">
                            <i class="bi bi-qr-code-scan me-2"></i>
                            Absen Sekarang
                        </a>
                    @endif
                </div>
            </div>
            <div class="attendance-status">
                @php
                    $attendanceStatus = $todayAttendance ?? null;
                    $statusType = $attendanceStatus ? $attendanceStatus->status : 'none';
                    $statusIcon = match($statusType) {
                        'present' => 'bi bi-check-circle-fill',
                        'late' => 'bi bi-clock-history',
                        'absent' => 'bi bi-x-circle-fill',
                        default => 'bi bi-exclamation-triangle-fill'
                    };
                    $statusLabel = match($statusType) {
                        'present' => 'HADIR',
                        'late' => 'TERLAMBAT',
                        'absent' => 'TIDAK HADIR',
                        default => 'BELUM ABSEN'
                    };
                    $statusColor = match($statusType) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        default => 'secondary'
                    };
                @endphp
                <div class="status-card status-{{ $statusColor }}">
                    <div class="status-icon">
                        <i class="{{ $statusIcon }}"></i>
                    </div>
                    <div class="status-info">
                        <div class="status-label">{{ $statusLabel }}</div>
                        <div class="status-time">
                            @if($attendanceStatus && $attendanceStatus->checked_in_at)
                                {{ \Carbon\Carbon::parse($attendanceStatus->checked_in_at)->format('H:i') }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-primary-light text-primary">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ ($classes ?? collect())->count() }}</h3>
                        <p class="stats-label mb-0">Total Kelas</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-danger-light text-danger">
                        <i class="bi bi-alarm-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $urgentAssignments ?? 0 }}</h3>
                        <p class="stats-label mb-0">Tugas Mendesak</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-star-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ number_format($averageScore ?? 0, 1) }}</h3>
                        <p class="stats-label mb-0">Rata-rata Nilai</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-info-light text-info">
                        <i class="bi bi-calendar-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $attendancePercentage ?? 0 }}%</h3>
                        <p class="stats-label mb-0">Kehadiran</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Statistics -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Statistik Nilai
                </h5>
                @php
                    $gradeColor = $grade['color'] ?? 'secondary';
                    $gradeLetter = $grade['letter'] ?? '-';
                @endphp
                <span class="grade-badge {{ $gradeColor }}">
                    Grade: {{ $gradeLetter }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="chart-circle mx-auto">
                        <svg width="140" height="140" viewBox="0 0 140 140">
                            <circle cx="70" cy="70" r="60" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                            <circle cx="70" cy="70" r="60" fill="none" 
                                    stroke="var(--color-{{ $gradeColor }})" 
                                    stroke-width="8" stroke-linecap="round"
                                    stroke-dasharray="{{ 2 * 3.14 * 60 * (($averageScore ?? 0) / 100) }}, 376.8"/>
                        </svg>
                        <div class="chart-value">
                            <div class="value">{{ number_format($averageScore ?? 0, 1) }}</div>
                            <div class="label">Rata-rata</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="score-details">
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-trophy-fill"></i>
                                Nilai Tertinggi
                            </div>
                            <div class="detail-value">{{ $highestScore ?? 0 }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-graph-up-arrow"></i>
                                Nilai Terendah
                            </div>
                            <div class="detail-value">{{ $lowestScore ?? 0 }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-check-circle-fill"></i>
                                Tugas Dinilai
                            </div>
                            <div class="detail-value">{{ $gradedCount ?? 0 }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="bi bi-chat-left-text-fill"></i>
                                Predikat
                            </div>
                            <div class="detail-value {{ $gradeColor }}">
                                {{ $grade['message'] ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="score-distribution">
                        <h6 class="distribution-title mb-3">
                            <i class="bi bi-bar-chart me-2"></i>
                            Distribusi Nilai
                        </h6>
                        @forelse(($scoreDistribution ?? []) as $gradeKey => $data)
                        <div class="distribution-item mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">{{ $gradeKey }}</span>
                                <span class="small text-muted">{{ $data['count'] ?? 0 }} ({{ $data['percentage'] ?? 0 }}%)</span>
                            </div>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar {{ $data['color'] ?? 'secondary' }}" style="width: {{ $data['percentage'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3">
                            <p class="text-muted small mb-0">Belum ada data nilai</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-3 g-md-4">
        <!-- Urgent Assignments -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                            Tugas Mendesak
                        </h5>
                        <a href="{{ route('assignments.student.index') }}" class="btn btn-link btn-sm">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(($pendingAssignments ?? collect())->count() > 0)
                        <div class="assignments-list">
                            @foreach($pendingAssignments as $assignment)
                            @php
                                $dueDate = \Carbon\Carbon::parse($assignment->due_date);
                                $now = now();
                                $diffDays = (int)$now->diffInDays($dueDate, false);
                                $isOverdue = $diffDays < 0;
                                $isUrgent = $diffDays <= 3 && $diffDays >= 0;
                                $hasSubmission = $assignment->submissions()->where('student_id', auth()->id())->exists();
                            @endphp
                            <div class="assignment-item {{ $isOverdue ? 'overdue' : ($isUrgent ? 'urgent' : 'normal') }}">
                                <div class="assignment-header">
                                    <a href="{{ route('assignments.show', $assignment) }}" class="assignment-title">
                                        {{ Str::limit($assignment->title, 30) }}
                                    </a>
                                    <span class="class-badge">{{ $assignment->class->class_code ?? '' }}</span>
                                </div>
                                <div class="assignment-meta">
                                    <span class="due-date">
                                        <i class="bi bi-calendar"></i>
                                        {{ $dueDate->format('d M, H:i') }}
                                    </span>
                                </div>
                                <div class="assignment-footer">
                                    @if($hasSubmission)
                                        <span class="status-badge submitted">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Sudah Dikerjakan
                                        </span>
                                    @else
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <span class="status-badge {{ $isOverdue ? 'overdue' : ($isUrgent ? 'urgent' : 'pending') }}">
                                                @if($isOverdue)
                                                    <i class="bi bi-clock-history me-1"></i>
                                                    Terlambat {{ abs($diffDays) }}h
                                                @else
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $diffDays }} hari lagi
                                                @endif
                                            </span>
                                            <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-primary">
                                                Kerjakan
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mx-auto mb-3">
                                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                            </div>
                            <h6 class="mb-1">Tidak ada tugas mendesak</h6>
                            <p class="text-muted small mb-0">Semua tugas sudah dikerjakan!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Attendance Status -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>
                            Status Absensi
                        </h5>
                        <a href="{{ route('attendance.student.index') }}" class="btn btn-link btn-sm">Riwayat</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2 g-md-3 mb-4">
                        <div class="col-4">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-success-light">
                                <div class="stat-mini-value text-success fw-bold fs-3">{{ ($attendanceStats['present'] ?? 0) }}</div>
                                <div class="stat-mini-label text-muted small">Hadir</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-warning-light">
                                <div class="stat-mini-value text-warning fw-bold fs-3">{{ ($attendanceStats['late'] ?? 0) }}</div>
                                <div class="stat-mini-label text-muted small">Terlambat</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini text-center p-2 p-md-3 rounded bg-danger-light">
                                <div class="stat-mini-value text-danger fw-bold fs-3">{{ ($attendanceStats['absent'] ?? 0) }}</div>
                                <div class="stat-mini-label text-muted small">Absen</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="recent-attendance">
                        <h6 class="mb-3">Absensi Terakhir</h6>
                        @if(($recentAttendance ?? collect())->count() > 0)
                            <div class="attendance-list">
                                @foreach($recentAttendance as $attendance)
                                <div class="attendance-item">
                                    <div class="attendance-marker {{ $attendance->status ?? 'absent' }}">
                                    </div>
                                    <div class="attendance-content">
                                        <div class="attendance-date">
                                            {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M') }}
                                        </div>
                                        <div class="attendance-info">
                                            <span class="attendance-status-label {{ $attendance->status ?? 'absent' }}">
                                                {{ ucfirst($attendance->status ?? 'Absent') }}
                                                @if($attendance->checked_in_at)
                                                    - {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i') }}
                                                @endif
                                            </span>
                                            <span class="attendance-class">
                                                {{ $attendance->class->class_name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3">
                                <p class="text-muted small mb-0">Belum ada riwayat absensi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-upload me-2 text-info"></i>
                            Pengumpulan Terakhir
                        </h5>
                        <a href="{{ route('assignments.student.index') }}" class="btn btn-link btn-sm">Semua Tugas</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(($recentSubmissions ?? collect())->count() > 0)
                        <div class="submissions-list">
                            @foreach($recentSubmissions as $submission)
                            <div class="submission-item">
                                <div class="submission-icon">
                                    <i class="bi bi-file-text"></i>
                                </div>
                                <div class="submission-content">
                                    <div class="submission-title">
                                        {{ Str::limit($submission->assignment->title ?? '', 25) }}
                                    </div>
                                    <div class="submission-meta">
                                        <span class="submission-time">
                                            <i class="bi bi-calendar"></i>
                                            {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d M, H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="submission-score">
                                    @if($submission->score ?? null)
                                        @php
                                            $scoreClass = ($submission->score ?? 0) >= 80 ? 'success' : (($submission->score ?? 0) >= 60 ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-{{ $scoreClass }}">
                                            {{ $submission->score }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Menunggu</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mx-auto mb-3">
                                <i class="bi bi-upload fs-1 text-muted"></i>
                            </div>
                            <h6 class="mb-1">Belum ada pengumpulan</h6>
                            <p class="text-muted small mb-0">Kerjakan dan kumpulkan tugas Anda</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- My Classes -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people me-2 text-primary"></i>
                            Kelas Saya
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    @if(($classes ?? collect())->count() > 0)
                        <div class="classes-grid">
                            @foreach($classes as $class)
                            <div class="class-card">
                                <div class="class-header">
                                    <h6 class="class-name mb-0">{{ $class->class_name }}</h6>
                                    <span class="class-code">{{ $class->class_code }}</span>
                                </div>
                                <div class="class-body">
                                    <div class="class-teacher">
                                        <i class="bi bi-person-badge me-1"></i>
                                        {{ $class->teacher->name ?? 'Guru' }}
                                    </div>
                                    <div class="class-members">
                                        <i class="bi bi-people me-1"></i>
                                        {{ $class->students->count() ?? 0 }} siswa
                                    </div>
                                </div>
                                <div class="class-footer">
                                    <a href="{{ route('classes.show', $class->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mx-auto mb-3">
                                <i class="bi bi-people fs-1 text-muted"></i>
                            </div>
                            <h6 class="mb-1">Belum mengikuti kelas</h6>
                            <p class="text-muted small mb-0">Hubungi guru untuk ditambahkan ke kelas</p>
                        </div>
                    @endif
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
    --info: #3b82f6;
    --info-light: #dbeafe;
    --secondary: #64748b;
    --secondary-light: #f1f5f9;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --transition: all 0.2s ease;
}

/* Welcome Card */
.welcome-card {
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    border-radius: 1rem;
    padding: 1.5rem;
    color: white;
    box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);
}

.welcome-content {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.welcome-text {
    flex: 1;
}

.welcome-title {
    font-size: clamp(1.25rem, 4vw, 1.5rem);
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.welcome-date {
    opacity: 0.9;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.welcome-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.welcome-actions .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    font-weight: 600;
    font-size: 0.813rem;
    transition: var(--transition);
}

.welcome-actions .btn-primary {
    background: white;
    color: #4f46e5;
}

.welcome-actions .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.welcome-actions .btn-outline-light {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
}

.welcome-actions .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

.welcome-actions .btn-danger {
    background: #ef4444;
}

.welcome-actions .btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

.welcome-actions .badge {
    font-size: 0.688rem;
    padding: 0.125rem 0.375rem;
}

/* Attendance Status */
.attendance-status {
    min-width: 160px;
}

.status-card {
    background: white;
    border-radius: 1rem;
    padding: 0.875rem;
    text-align: center;
}

.status-card.status-success { border-left: 3px solid #10b981; }
.status-card.status-warning { border-left: 3px solid #f59e0b; }
.status-card.status-danger { border-left: 3px solid #ef4444; }
.status-card.status-secondary { border-left: 3px solid #64748b; }

.status-icon {
    font-size: 2rem;
    margin-bottom: 0.25rem;
}

.status-card.status-success .status-icon { color: #10b981; }
.status-card.status-warning .status-icon { color: #f59e0b; }
.status-card.status-danger .status-icon { color: #ef4444; }
.status-card.status-secondary .status-icon { color: #64748b; }

.status-label {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 0.125rem;
    color: #6b7280;
}

.status-time {
    font-size: 0.688rem;
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

/* Cards */
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

/* Chart Circle */
.chart-circle {
    position: relative;
    width: 140px;
    height: 140px;
}

.chart-circle svg {
    transform: rotate(-90deg);
}

.chart-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.chart-value .value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
}

.chart-value .label {
    font-size: 0.688rem;
    color: #6b7280;
}

/* Score Details */
.score-details {
    padding: 0.5rem 0;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #475569;
}

.detail-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.detail-value.success { color: #10b981; }
.detail-value.info { color: #3b82f6; }
.detail-value.warning { color: #f59e0b; }
.detail-value.danger { color: #ef4444; }

/* Grade Badge */
.grade-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.grade-badge.success { background: #d1fae5; color: #065f46; }
.grade-badge.info { background: #dbeafe; color: #1e40af; }
.grade-badge.warning { background: #fef3c7; color: #92400e; }
.grade-badge.danger { background: #fee2e2; color: #991b1b; }
.grade-badge.secondary { background: #f1f5f9; color: #475569; }

/* Score Distribution */
.score-distribution {
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 0.75rem;
}

.distribution-title {
    font-size: 0.813rem;
    font-weight: 600;
    color: #1f2937;
}

/* Assignment Item */
.assignments-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.assignment-item {
    padding: 0.75rem;
    border-radius: 0.75rem;
    background: #f8fafc;
    transition: var(--transition);
}

.assignment-item.overdue {
    border-left: 3px solid #ef4444;
    background: rgba(239, 68, 68, 0.05);
}

.assignment-item.urgent {
    border-left: 3px solid #f59e0b;
    background: rgba(245, 158, 11, 0.05);
}

.assignment-item.normal {
    border-left: 3px solid #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.assignment-item:hover {
    background: #f1f5f9;
}

.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.assignment-title {
    font-size: 0.813rem;
    font-weight: 600;
    color: #1f2937;
    text-decoration: none;
}

.assignment-title:hover {
    color: #4f46e5;
}

.class-badge {
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
    background: #e2e8f0;
    color: #475569;
    border-radius: 0.75rem;
}

.assignment-meta {
    margin-bottom: 0.5rem;
}

.due-date {
    font-size: 0.688rem;
    color: #6b7280;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.assignment-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.625rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.status-badge.submitted {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.overdue {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge.urgent {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.pending {
    background: #dbeafe;
    color: #1e40af;
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

/* Attendance List */
.attendance-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.attendance-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 0.75rem;
}

.attendance-marker {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.attendance-marker.present { background: #10b981; }
.attendance-marker.late { background: #f59e0b; }
.attendance-marker.absent { background: #ef4444; }

.attendance-content {
    flex: 1;
}

.attendance-date {
    font-size: 0.625rem;
    color: #6b7280;
}

.attendance-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.attendance-status-label {
    font-size: 0.688rem;
    font-weight: 500;
}

.attendance-status-label.present { color: #10b981; }
.attendance-status-label.late { color: #f59e0b; }
.attendance-status-label.absent { color: #ef4444; }

.attendance-class {
    font-size: 0.625rem;
    color: #6b7280;
}

/* Submissions List */
.submissions-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.submission-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 0.75rem;
    transition: var(--transition);
}

.submission-item:hover {
    background: #f1f5f9;
}

.submission-icon {
    width: 36px;
    height: 36px;
    border-radius: 0.625rem;
    background: #e0e7ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.submission-content {
    flex: 1;
}

.submission-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.125rem;
}

.submission-meta {
    font-size: 0.625rem;
    color: #6b7280;
}

.submission-time {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.submission-score .badge {
    font-size: 0.688rem;
    padding: 0.25rem 0.5rem;
}

/* Classes Grid */
.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 0.75rem;
}

.class-card {
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 0.75rem;
    transition: var(--transition);
}

.class-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
    background: #f1f5f9;
}

.class-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.class-name {
    font-size: 0.813rem;
    font-weight: 600;
    color: #1f2937;
}

.class-code {
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
    background: #e2e8f0;
    color: #475569;
    border-radius: 0.5rem;
}

.class-teacher,
.class-members {
    font-size: 0.688rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-bottom: 0.25rem;
}

.class-footer {
    margin-top: 0.5rem;
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

.btn-link {
    color: #4f46e5;
    text-decoration: none;
    font-size: 0.75rem;
}

.btn-link:hover {
    text-decoration: underline;
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

/* Progress */
.progress {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 2px;
}

.progress-bar.success { background: #10b981; }
.progress-bar.info { background: #3b82f6; }
.progress-bar.warning { background: #f59e0b; }
.progress-bar.danger { background: #ef4444; }
.progress-bar.secondary { background: #64748b; }

/* Empty State */
.empty-state {
    text-align: center;
}

.empty-icon {
    width: 48px;
    height: 48px;
    background: #f9fafb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.empty-state h6 {
    font-size: 0.813rem;
    font-weight: 600;
    color: #1f2937;
}

.empty-state p {
    font-size: 0.688rem;
    color: #6b7280;
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
.text-info { color: #3b82f6 !important; }
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
    
    .welcome-card {
        padding: 1rem;
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
    
    .card-body {
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .welcome-content {
        flex-direction: column;
        text-align: center;
    }
    
    .welcome-actions {
        justify-content: center;
    }
    
    .attendance-status {
        width: 100%;
    }
    
    .classes-grid {
        grid-template-columns: 1fr;
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
    // Animate progress circles
    document.querySelectorAll('.chart-circle svg circle:last-child').forEach(circle => {
        const dasharray = circle.getAttribute('stroke-dasharray');
        if (dasharray && dasharray !== '0, 376.8') {
            circle.style.strokeDasharray = '0, 376.8';
            setTimeout(() => {
                circle.style.transition = 'stroke-dasharray 1s ease-in-out';
                circle.style.strokeDasharray = dasharray;
            }, 300);
        }
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection