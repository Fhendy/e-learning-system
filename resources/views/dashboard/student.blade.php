@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.student') }}" class="breadcrumb-link">
                    <i class="bi bi-house-door"></i>
                    <span>Dashboard Siswa</span>
                </a>
            </li>
        </ol>
    </div>
</nav>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-card">
        <div class="welcome-content">
            <div class="welcome-text">
                <h1 class="welcome-title">Selamat Datang, {{ auth()->user()->name }}! 👋</h1>
                <p class="welcome-date">
                    <i class="bi bi-calendar-check"></i>
                    {{ now()->translatedFormat('l, d F Y') }}
                </p>
                <div class="welcome-actions">
                    <a href="{{ route('assignments.student.index') }}" class="btn btn-primary">
                        <i class="bi bi-journal-text"></i>
                        Lihat Tugas
                        @if(($pendingAssignments ?? collect())->count() > 0)
                            <span class="badge">{{ ($pendingAssignments ?? collect())->count() }}</span>
                        @endif
                    </a>
                    <a href="{{ route('attendance.student.index') }}" class="btn btn-outline">
                        <i class="bi bi-calendar-check"></i>
                        Riwayat Absensi
                    </a>
                    @if(!($todayAttendance ?? null))
                        <a href="{{ route('attendance.student.index') }}" class="btn btn-danger">
                            <i class="bi bi-qr-code-scan"></i>
                            Absen Sekarang
                        </a>
                    @endif
                </div>
            </div>
            <div class="attendance-status">
                <div class="status-card {{ ($todayAttendance ?? null) ? 'status-' . $todayAttendance->status : 'status-none' }}">
                    <div class="status-icon">
                        @if($todayAttendance ?? null)
                            @if($todayAttendance->status == 'present')
                                <i class="bi bi-check-circle-fill"></i>
                            @elseif($todayAttendance->status == 'late')
                                <i class="bi bi-clock-history"></i>
                            @else
                                <i class="bi bi-x-circle-fill"></i>
                            @endif
                        @else
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        @endif
                    </div>
                    <div class="status-info">
                        <div class="status-label">
                            {{ ($todayAttendance ?? null) ? strtoupper($todayAttendance->status) : 'BELUM ABSEN' }}
                        </div>
                        <div class="status-time">
                            @if(($todayAttendance ?? null) && $todayAttendance->checked_in_at)
                                {{ $todayAttendance->checked_in_at->format('H:i') }}
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
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ ($classes ?? collect())->count() }}</div>
                <div class="stat-title">Total Kelas</div>
                <div class="stat-subtitle">Yang diikuti</div>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-icon">
                <i class="bi bi-alarm-fill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $urgentAssignments ?? 0 }}</div>
                <div class="stat-title">Tugas Mendesak</div>
                <div class="stat-subtitle">Batas ≤ 3 hari</div>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="bi bi-star-fill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($averageScore ?? 0, 1) }}</div>
                <div class="stat-title">Rata-rata Nilai</div>
                <div class="stat-subtitle">{{ $gradedCount ?? 0 }} tugas dinilai</div>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $attendancePercentage ?? 0 }}%</div>
                <div class="stat-title">Kehadiran</div>
                <div class="stat-subtitle">30 hari terakhir</div>
                <div class="stat-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $attendancePercentage ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Statistics -->
    <div class="score-stats-section">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-graph-up"></i>
                    Statistik Nilai
                </h3>
                <span class="grade-badge {{ $grade['color'] ?? 'secondary' }}">
                    Grade: {{ $grade['letter'] ?? '-' }}
                </span>
            </div>
            <div class="card-body">
                <div class="score-content">
                    <div class="score-chart">
                        <div class="chart-circle">
                            <div class="circle-progress" data-percentage="{{ $averageScore ?? 0 }}">
                                <svg width="160" height="160" viewBox="0 0 160 160">
                                    <circle cx="80" cy="80" r="70" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                                    <circle cx="80" cy="80" r="70" fill="none" 
                                            stroke="var(--color-{{ $grade['color'] ?? 'secondary' }})" 
                                            stroke-width="8" stroke-linecap="round"
                                            stroke-dasharray="{{ 2 * 3.14 * 70 * (($averageScore ?? 0) / 100) }}, 439.6"/>
                                </svg>
                            </div>
                            <div class="chart-value">
                                <div class="value">{{ number_format($averageScore ?? 0, 1) }}</div>
                                <div class="label">Rata-rata</div>
                            </div>
                        </div>
                    </div>
                    
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
                            <div class="detail-value {{ $grade['color'] ?? 'secondary' }}">
                                {{ $grade['message'] ?? '-' }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="score-distribution">
                        <h4 class="distribution-title">
                            <i class="bi bi-bar-chart"></i>
                            Distribusi Nilai
                        </h4>
                        @foreach(($scoreDistribution ?? []) as $gradeKey => $data)
                        <div class="distribution-item">
                            <div class="distribution-header">
                                <span class="distribution-label">{{ $gradeKey }}</span>
                                <span class="distribution-count">{{ $data['count'] }} ({{ $data['percentage'] }}%)</span>
                            </div>
                            <div class="distribution-bar">
                                <div class="bar-fill {{ $data['color'] }}" style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <!-- Urgent Assignments -->
        <div class="urgent-assignments-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Tugas Mendesak
                    </h3>
                    <a href="{{ route('assignments.student.index') }}" class="btn btn-link">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @if(($pendingAssignments ?? collect())->count() > 0)
                        <div class="assignments-list">
                            @foreach($pendingAssignments as $assignment)
                            @php
                                $dueDate = \Carbon\Carbon::parse($assignment->due_date);
                                $now = now();
                                $diffDays = $now->diffInDays($dueDate, false);
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
                                        {{ $assignment->due_date->format('d M, H:i') }}
                                    </span>
                                </div>
                                <div class="assignment-status">
                                    @if($hasSubmission)
                                        <span class="status-badge submitted">
                                            <i class="bi bi-check-circle"></i>
                                            Sudah Dikerjakan
                                        </span>
                                    @else
                                        <span class="status-badge {{ $isOverdue ? 'overdue' : ($isUrgent ? 'urgent' : 'pending') }}">
                                            @if($isOverdue)
                                                <i class="bi bi-clock-history"></i>
                                                Terlambat {{ abs($diffDays) }}h
                                            @else
                                                <i class="bi bi-clock"></i>
                                                {{ $diffDays }} hari lagi
                                            @endif
                                        </span>
                                        <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-primary">
                                            Kerjakan
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <h4>Tidak ada tugas mendesak</h4>
                            <p>Semua tugas sudah dikerjakan!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Attendance Status -->
        <div class="attendance-status-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-calendar-check"></i>
                        Status Absensi
                    </h3>
                    <a href="{{ route('attendance.student.index') }}" class="btn btn-link">Riwayat</a>
                </div>
                <div class="card-body">
                    <div class="attendance-stats">
                        <div class="stat-item present">
                            <div class="stat-number">{{ ($attendanceStats['present'] ?? 0) }}</div>
                            <div class="stat-label">Hadir</div>
                        </div>
                        <div class="stat-item late">
                            <div class="stat-number">{{ ($attendanceStats['late'] ?? 0) }}</div>
                            <div class="stat-label">Terlambat</div>
                        </div>
                        <div class="stat-item absent">
                            <div class="stat-number">{{ ($attendanceStats['absent'] ?? 0) }}</div>
                            <div class="stat-label">Absen</div>
                        </div>
                    </div>
                    
                    <div class="recent-attendance">
                        <h4 class="section-title">Absensi Terakhir</h4>
                        @if(($recentAttendance ?? collect())->count() > 0)
                            <div class="attendance-list">
                                @foreach($recentAttendance as $attendance)
                                <div class="attendance-item">
                                    <div class="attendance-marker {{ $attendance->status }}">
                                    </div>
                                    <div class="attendance-content">
                                        <div class="attendance-date">
                                            {{ $attendance->date->format('d M') }}
                                        </div>
                                        <div class="attendance-info">
                                            <div class="attendance-status-label {{ $attendance->status }}">
                                                {{ ucfirst($attendance->status) }}
                                                @if($attendance->checked_in_at)
                                                    - {{ $attendance->checked_in_at->format('H:i') }}
                                                @endif
                                            </div>
                                            <div class="attendance-class">
                                                {{ $attendance->class->class_name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state small">
                                <p class="text-muted">Belum ada riwayat absensi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="recent-submissions-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-upload"></i>
                        Pengumpulan Terakhir
                    </h3>
                    <a href="{{ route('assignments.student.index') }}" class="btn btn-link">Semua Tugas</a>
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
                                            {{ $submission->submitted_at->format('d M, H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="submission-score">
                                    @if($submission->score ?? null)
                                        <span class="score-badge {{ ($submission->score ?? 0) >= 80 ? 'excellent' : (($submission->score ?? 0) >= 60 ? 'good' : 'poor') }}">
                                            {{ $submission->score }}
                                        </span>
                                    @else
                                        <span class="score-badge waiting">Menunggu</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="bi bi-upload"></i>
                            </div>
                            <h4>Belum ada pengumpulan</h4>
                            <p>Kerjakan dan kumpulkan tugas Anda</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- My Classes -->
        <div class="my-classes-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-people"></i>
                        Kelas Saya
                    </h3>
                </div>
                <div class="card-body">
                    @if(($classes ?? collect())->count() > 0)
                        <div class="classes-grid">
                            @foreach($classes as $class)
                            <div class="class-card">
                                <div class="class-header">
                                    <h4 class="class-name">{{ $class->class_name }}</h4>
                                    <span class="class-code">{{ $class->class_code }}</span>
                                </div>
                                <div class="class-body">
                                    <div class="class-info">
                                        <div class="class-teacher">
                                            <i class="bi bi-person-badge"></i>
                                            {{ $class->teacher->name ?? 'Guru' }}
                                        </div>
                                        <div class="class-members">
                                            <i class="bi bi-people"></i>
                                            {{ $class->students->count() ?? 0 }} siswa
                                        </div>
                                    </div>
                                </div>
                                <div class="class-footer">
                                    <a href="#" class="btn btn-outline btn-sm">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                        Masuk
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h4>Belum mengikuti kelas</h4>
                            <p>Hubungi guru untuk ditambahkan ke kelas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="quick-links-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-lightning"></i>
                        Akses Cepat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="links-grid">
                        <a href="{{ route('assignments.student.index') }}" class="link-item">
                            <div class="link-icon primary">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="link-text">Tugas</div>
                        </a>
                        
                        <a href="{{ route('attendance.student.index') }}" class="link-item">
                            <div class="link-icon success">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="link-text">Absensi</div>
                        </a>
                        
                        <a href="#" class="link-item">
                            <div class="link-icon info">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="link-text">Kelas</div>
                        </a>
                        
                        <a href="#" class="link-item">
                            <div class="link-icon warning">
                                <i class="bi bi-file-text"></i>
                            </div>
                            <div class="link-text">Materi</div>
                        </a>
                        
                        <a href="#" class="link-item">
                            <div class="link-icon danger">
                                <i class="bi bi-chat-left-text"></i>
                            </div>
                            <div class="link-text">Diskusi</div>
                        </a>
                        
                        <a href="#" class="link-item">
                            <div class="link-icon secondary">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div class="link-text">Nilai</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Base Styles */
.dashboard-container {
    padding: 20px;
}

/* Welcome Card */
.welcome-card {
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    border-radius: 16px;
    padding: 30px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);
}

.welcome-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

@media (min-width: 768px) {
    .welcome-content {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.welcome-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    color: white;
}

.welcome-date {
    opacity: 0.9;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
}

.welcome-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.welcome-actions .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}

.welcome-actions .btn-primary {
    background: white;
    color: #4f46e5;
    border: none;
}

.welcome-actions .btn-outline {
    background: transparent;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.welcome-actions .btn-danger {
    background: #ef4444;
    color: white;
    border: none;
}

.welcome-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.welcome-actions .badge {
    background: #ef4444;
    color: white;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
    margin-left: 8px;
}

/* Attendance Status */
.attendance-status {
    min-width: 200px;
}

.status-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.status-present { border-left: 4px solid #10b981; }
.status-late { border-left: 4px solid #f59e0b; }
.status-absent { border-left: 4px solid #ef4444; }
.status-none { border-left: 4px solid #64748b; }

.status-icon {
    font-size: 40px;
    margin-bottom: 12px;
}

.status-present .status-icon { color: #10b981; }
.status-late .status-icon { color: #f59e0b; }
.status-absent .status-icon { color: #ef4444; }
.status-none .status-icon { color: #64748b; }

.status-label {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #333;
}

.status-time {
    font-size: 14px;
    color: #64748b;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
}

.stat-primary::before { background: #4f46e5; }
.stat-danger::before { background: #ef4444; }
.stat-success::before { background: #10b981; }
.stat-info::before { background: #3b82f6; }

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-primary .stat-icon { background: #4f46e5; }
.stat-danger .stat-icon { background: #ef4444; }
.stat-success .stat-icon { background: #10b981; }
.stat-info .stat-icon { background: #3b82f6; }

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 4px;
}

.stat-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 2px;
    color: #333;
}

.stat-subtitle {
    font-size: 12px;
    color: #666;
}

.stat-progress {
    margin-top: 12px;
}

.progress-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #3b82f6, #2563eb);
}

/* Score Statistics */
.score-stats-section {
    margin-bottom: 30px;
}

.score-stats-section .card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
}

.card-title i {
    color: #4f46e5;
}

.grade-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.grade-badge.success { background: #d1fae5; color: #065f46; }
.grade-badge.info { background: #dbeafe; color: #1e40af; }
.grade-badge.warning { background: #fef3c7; color: #92400e; }
.grade-badge.danger { background: #fee2e2; color: #991b1b; }
.grade-badge.secondary { background: #f1f5f9; color: #475569; }

.card-body {
    padding: 30px;
}

.score-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
}

@media (min-width: 1024px) {
    .score-content {
        grid-template-columns: 1fr 1fr 1fr;
    }
}

.score-chart {
    display: flex;
    justify-content: center;
    align-items: center;
}

.chart-circle {
    position: relative;
    width: 160px;
    height: 160px;
}

.circle-progress svg {
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
    font-size: 32px;
    font-weight: 700;
    color: #333;
}

.chart-value .label {
    font-size: 14px;
    color: #64748b;
}

.score-details {
    padding: 20px 0;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #475569;
}

.detail-label i {
    font-size: 16px;
}

.detail-value {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.detail-value.success { color: #10b981; }
.detail-value.info { color: #3b82f6; }
.detail-value.warning { color: #f59e0b; }
.detail-value.danger { color: #ef4444; }
.detail-value.secondary { color: #64748b; }

.score-distribution {
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
}

.distribution-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #333;
}

.distribution-item {
    margin-bottom: 12px;
}

.distribution-item:last-child {
    margin-bottom: 0;
}

.distribution-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.distribution-label {
    font-size: 13px;
    font-weight: 500;
    color: #475569;
}

.distribution-count {
    font-size: 12px;
    color: #64748b;
}

.distribution-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 1s ease-in-out;
}

.bar-fill.success { background: #10b981; }
.bar-fill.info { background: #3b82f6; }
.bar-fill.warning { background: #f59e0b; }
.bar-fill.danger { background: #ef4444; }
.bar-fill.secondary { background: #64748b; }

/* Dashboard Content */
.dashboard-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 1024px) {
    .dashboard-content {
        grid-template-columns: 1fr 1fr;
    }
}

/* Urgent Assignments */
.urgent-assignments-section {
    grid-column: 1;
}

@media (min-width: 1024px) {
    .urgent-assignments-section {
        grid-column: 1;
        grid-row: 1;
    }
}

.assignments-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.assignment-item {
    padding: 20px;
    border-radius: 12px;
    background: #f8fafc;
    transition: background 0.2s;
}

.assignment-item.overdue {
    border-left: 4px solid #ef4444;
    background: rgba(239, 68, 68, 0.05);
}

.assignment-item.urgent {
    border-left: 4px solid #f59e0b;
    background: rgba(245, 158, 11, 0.05);
}

.assignment-item.normal {
    border-left: 4px solid #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.assignment-item:hover {
    background: #f1f5f9;
}

.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.assignment-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    text-decoration: none;
    flex: 1;
}

.assignment-title:hover {
    color: #4f46e5;
}

.class-badge {
    font-size: 12px;
    padding: 4px 12px;
    background: #e2e8f0;
    color: #475569;
    border-radius: 20px;
    font-weight: 500;
    margin-left: 12px;
}

.assignment-meta {
    margin-bottom: 12px;
}

.due-date {
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}

.assignment-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
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

.btn-sm {
    padding: 6px 16px !important;
    font-size: 12px !important;
}

/* Attendance Status Section */
.attendance-status-section {
    grid-column: 1;
}

@media (min-width: 1024px) {
    .attendance-status-section {
        grid-column: 2;
        grid-row: 1;
    }
}

.attendance-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-item {
    text-align: center;
    padding: 20px;
    border-radius: 12px;
    color: white;
}

.stat-item.present { background: linear-gradient(135deg, #10b981, #0d9e70); }
.stat-item.late { background: linear-gradient(135deg, #f59e0b, #d97706); }
.stat-item.absent { background: linear-gradient(135deg, #ef4444, #dc2626); }

.stat-number {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 13px;
    opacity: 0.9;
}

.recent-attendance {
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
    color: #333;
}

.attendance-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.attendance-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
}

.attendance-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.attendance-marker.present { background: #10b981; }
.attendance-marker.late { background: #f59e0b; }
.attendance-marker.absent { background: #ef4444; }

.attendance-content {
    flex: 1;
}

.attendance-date {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 2px;
}

.attendance-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.attendance-status-label {
    font-size: 13px;
    font-weight: 500;
}

.attendance-status-label.present { color: #10b981; }
.attendance-status-label.late { color: #f59e0b; }
.attendance-status-label.absent { color: #ef4444; }

.attendance-class {
    font-size: 12px;
    color: #64748b;
}

/* Recent Submissions */
.recent-submissions-section {
    grid-column: 1;
}

@media (min-width: 1024px) {
    .recent-submissions-section {
        grid-column: 1;
        grid-row: 2;
    }
}

.submissions-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.submission-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    transition: background 0.2s;
}

.submission-item:hover {
    background: #f1f5f9;
}

.submission-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #e0e7ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.submission-content {
    flex: 1;
}

.submission-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.submission-meta {
    font-size: 12px;
    color: #64748b;
}

.submission-time {
    display: flex;
    align-items: center;
    gap: 4px;
}

.submission-score .score-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.score-badge.excellent { background: #10b981; }
.score-badge.good { background: #3b82f6; }
.score-badge.poor { background: #ef4444; }
.score-badge.waiting { background: #64748b; }

/* My Classes */
.my-classes-section {
    grid-column: 1;
}

@media (min-width: 1024px) {
    .my-classes-section {
        grid-column: 2;
        grid-row: 2;
    }
}

.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
}

.class-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.class-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    background: #f1f5f9;
}

.class-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.class-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.class-code {
    font-size: 11px;
    padding: 4px 8px;
    background: #e2e8f0;
    color: #475569;
    border-radius: 12px;
    font-weight: 500;
}

.class-info {
    margin-bottom: 16px;
}

.class-teacher,
.class-members {
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 6px;
}

.class-footer .btn-outline {
    background: transparent;
    border: 1px solid #4f46e5;
    color: #4f46e5;
}

.class-footer .btn-outline:hover {
    background: #4f46e5;
    color: white;
}

/* Quick Links */
.quick-links-section {
    grid-column: 1 / -1;
}

.links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
}

.link-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    border-radius: 12px;
    text-decoration: none;
    transition: transform 0.2s;
    background: #f8fafc;
}

.link-item:hover {
    transform: translateY(-4px);
    background: #f1f5f9;
}

.link-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    margin-bottom: 12px;
}

.link-icon.primary { background: #4f46e5; }
.link-icon.success { background: #10b981; }
.link-icon.info { background: #3b82f6; }
.link-icon.warning { background: #f59e0b; }
.link-icon.danger { background: #ef4444; }
.link-icon.secondary { background: #64748b; }

.link-text {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

/* Empty States */
.empty-state {
    padding: 40px 20px;
    text-align: center;
}

.empty-state.small {
    padding: 20px;
}

.empty-icon {
    font-size: 48px;
    color: #cbd5e1;
    margin-bottom: 16px;
}

.empty-state h4 {
    font-size: 16px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 20px;
    font-size: 14px;
}

.empty-state .text-muted {
    color: #94a3b8 !important;
    font-size: 14px;
}

/* Breadcrumb */
.breadcrumb-nav {
    background: white;
    padding: 16px 0;
    margin-bottom: 24px;
    border-bottom: 1px solid #e2e8f0;
}

.breadcrumb-nav .container {
    padding: 0 20px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.breadcrumb-link {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
}

.breadcrumb-link:hover {
    color: #4f46e5;
}

.breadcrumb-link i {
    font-size: 16px;
}

/* Utilities */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background: #3730a3;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    border: 1px solid #4f46e5;
    color: #4f46e5;
}

.btn-outline:hover {
    background: #4f46e5;
    color: white;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

.btn-link {
    text-decoration: none;
    color: #4f46e5;
    font-weight: 500;
    background: none;
    border: none;
    padding: 0;
}

.btn-link:hover {
    text-decoration: underline;
}

.btn-sm {
    padding: 6px 12px !important;
    font-size: 12px !important;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 16px;
    }
    
    .welcome-card {
        padding: 20px;
    }
    
    .welcome-content {
        flex-direction: column;
        gap: 20px;
    }
    
    .attendance-status {
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .score-content {
        grid-template-columns: 1fr;
    }
    
    .attendance-stats {
        grid-template-columns: 1fr;
    }
    
    .classes-grid {
        grid-template-columns: 1fr;
    }
    
    .links-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .welcome-actions {
        flex-direction: column;
    }
    
    .welcome-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .links-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress circles
    document.querySelectorAll('.circle-progress').forEach(circle => {
        const percentage = parseFloat(circle.getAttribute('data-percentage')) || 0;
        const strokeDasharray = 2 * Math.PI * 70 * (percentage / 100);
        const path = circle.querySelector('circle:last-child');
        if (path) {
            path.style.strokeDasharray = `${strokeDasharray}, 439.6`;
        }
    });

    // Animate distribution bars
    document.querySelectorAll('.bar-fill').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease-in-out';
            bar.style.width = width;
        }, 300);
    });

    // Animate stat cards on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.stat-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
});
</script>
@endsection