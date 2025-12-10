@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('breadcrumb')
<nav class="breadcrumb-nav">
    <div class="container">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard.teacher') }}" class="breadcrumb-link">
                    <i class="bi bi-house-door"></i>
                    <span>Dashboard Guru</span>
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
            </div>
            <div class="welcome-actions">
                <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                    <i class="bi bi-qr-code-scan"></i>
                    <span>Buat QR Absensi</span>
                </a>
                <a href="{{ route('assignments.teacher.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i>
                    <span>Tugas Baru</span>
                </a>
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
                <div class="stat-number">{{ $classes->count() }}</div>
                <div class="stat-title">Total Kelas</div>
                <div class="stat-subtitle">Anda mengajar</div>
            </div>
            <a href="{{ route('classes.index') }}" class="stat-link">
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="bi bi-person-badge"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $totalStudents }}</div>
                <div class="stat-title">Total Siswa</div>
                <div class="stat-subtitle">Di semua kelas</div>
            </div>
            <a href="{{ route('students.index') }}" class="stat-link">
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="bi bi-journal-text"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $activeAssignments }}</div>
                <div class="stat-title">Tugas Aktif</div>
                <div class="stat-subtitle">{{ $pendingSubmissions }} belum dinilai</div>
            </div>
            <a href="{{ route('assignments.teacher.index') }}" class="stat-link">
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="bi bi-qr-code-scan"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $activeQrCodes }}</div>
                <div class="stat-title">QR Code Aktif</div>
                <div class="stat-subtitle">Hari ini</div>
            </div>
            <a href="{{ route('qr-codes.dashboard') }}" class="stat-link">
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <!-- Chart Section -->
        <div class="chart-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-bar-chart"></i>
                        Statistik Absensi 7 Hari Terakhir
                    </h3>
                    <div class="period-selector">
                        <button class="period-btn active">7 Hari</button>
                        <button class="period-btn">30 Hari</button>
                        <button class="period-btn">Semester</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Classes -->
        <div class="today-classes-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-calendar-day"></i>
                        Kelas Hari Ini
                    </h3>
                </div>
                <div class="card-body">
                    @if($todayClasses->count() > 0)
                        <div class="classes-list">
                            @foreach($todayClasses as $class)
                            <div class="class-item">
                                <div class="class-icon">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <div class="class-info">
                                    <h4>{{ $class->class_name }}</h4>
                                    <div class="class-details">
                                        <span class="badge">{{ $class->class_code }}</span>
                                        <span class="students">
                                            <i class="bi bi-people"></i>
                                            {{ $class->students->count() }} siswa
                                        </span>
                                    </div>
                                    @php
                                        $qrCode = $class->active_qr_code ?? $class->qrCodes()->whereDate('date', today())->first();
                                    @endphp
                                    <div class="class-schedule">
                                        @if($qrCode)
                                            <span class="time">
                                                <i class="bi bi-clock"></i>
                                                {{ $qrCode->formatted_start_time }}
                                            </span>
                                            @if($qrCode->is_active)
                                                <span class="status active">Aktif</span>
                                            @endif
                                        @else
                                            <span class="time muted">
                                                <i class="bi bi-clock"></i>
                                                Belum ada jadwal
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="class-actions">
                                    @if($qrCode && $qrCode->is_active)
                                        <a href="{{ route('qr-codes.show', $qrCode) }}" class="btn btn-sm btn-success">
                                            <i class="bi bi-qr-code-scan"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('qr-codes.create') }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-plus"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="bi bi-calendar-x"></i>
                            </div>
                            <h4>Tidak ada kelas hari ini</h4>
                            <p>Anda bisa membuat QR code untuk kelas lain</p>
                            <a href="{{ route('qr-codes.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-qr-code-scan"></i>
                                Buat QR Code
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Assignments -->
        <div class="recent-assignments-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-journal-text"></i>
                        Tugas Terbaru
                    </h3>
                    <a href="{{ route('assignments.teacher.index') }}" class="btn btn-link btn-sm">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @if($recentAssignments->count() > 0)
                        <div class="assignments-list">
                            @foreach($recentAssignments as $assignment)
                            <div class="assignment-item">
                                <div class="assignment-header">
                                    <a href="{{ route('assignments.show', $assignment) }}" class="assignment-title">
                                        {{ $assignment->title }}
                                    </a>
                                    <span class="badge bg-secondary">{{ $assignment->class->class_code }}</span>
                                </div>
                                <div class="assignment-meta">
                                    <span class="due-date">
                                        <i class="bi bi-calendar"></i>
                                        {{ $assignment->due_date->format('d M, H:i') }}
                                    </span>
                                </div>
                                <div class="assignment-progress">
                                    @php
                                        $submitted = $assignment->submissions->count();
                                        $total = $assignment->class->students->count();
                                        $percentage = $total > 0 ? round(($submitted / $total) * 100) : 0;
                                    @endphp
                                    <div class="progress-info">
                                        <span class="progress-value">{{ $percentage }}%</span>
                                        <span class="progress-text">{{ $submitted }}/{{ $total }} siswa</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="bi bi-journal"></i>
                            </div>
                            <h4>Belum ada tugas</h4>
                            <p>Buat tugas pertama untuk kelas Anda</p>
                            <a href="{{ route('assignments.teacher.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i>
                                Buat Tugas
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="recent-submissions-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-upload"></i>
                        Pengumpulan Terbaru
                    </h3>
                    <a href="{{ route('assignments.teacher.index') }}" class="btn btn-link btn-sm">Lihat Semua</a>
                </div>
                <div class="card-body">
                    @if($recentSubmissions->count() > 0)
                        <div class="submissions-list">
                            @foreach($recentSubmissions as $submission)
                            <div class="submission-item">
                                <div class="submission-avatar">
                                    <div class="avatar">
                                        {{ strtoupper(substr($submission->student->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="submission-content">
                                    <h5>{{ $submission->student->name }}</h5>
                                    <p class="text-muted small mb-1">{{ Str::limit($submission->assignment->title, 30) }}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i>
                                        {{ $submission->submitted_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="submission-status">
                                    @if($submission->score)
                                        <span class="badge bg-success">{{ $submission->score }}</span>
                                    @else
                                        <a href="{{ route('assignments.submissions.grade', $submission) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Nilai
                                        </a>
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
                            <p>Tunggu siswa mengumpulkan tugas mereka</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-lightning"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <a href="{{ route('classes.create') }}" class="quick-action action-primary">
                            <div class="action-icon">
                                <i class="bi bi-plus-circle-fill"></i>
                            </div>
                            <div class="action-text">Buat Kelas</div>
                        </a>
                        
                        <a href="{{ route('assignments.teacher.create') }}" class="quick-action action-success">
                            <div class="action-icon">
                                <i class="bi bi-journal-plus"></i>
                            </div>
                            <div class="action-text">Buat Tugas</div>
                        </a>
                        
                        <a href="{{ route('qr-codes.create') }}" class="quick-action action-info">
                            <div class="action-icon">
                                <i class="bi bi-qr-code-scan"></i>
                            </div>
                            <div class="action-text">QR Absensi</div>
                        </a>
                        
                        <a href="{{ route('attendance.teacher.index') }}" class="quick-action action-warning">
                            <div class="action-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="action-text">Kelola Absensi</div>
                        </a>
                        
                        <a href="{{ route('students.index') }}" class="quick-action action-secondary">
                            <div class="action-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="action-text">Daftar Siswa</div>
                        </a>
                        
                        <a href="{{ route('students.create') }}" class="quick-action action-danger">
                            <div class="action-icon">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div class="action-text">Tambah Siswa</div>
                        </a>
                        
                        <a href="{{ route('classes.index') }}" class="quick-action action-purple">
                            <div class="action-icon">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                            <div class="action-text">Kelola Kelas</div>
                        </a>
                        
                        <a href="{{ route('students.import') }}" class="quick-action action-orange">
                            <div class="action-icon">
                                <i class="bi bi-file-excel"></i>
                            </div>
                            <div class="action-text">Import Excel</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    window.attendanceChartData = {
        labels: <?php echo json_encode($attendanceStats['labels'] ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']); ?>,
        present: <?php echo json_encode($attendanceStats['present'] ?? [12, 19, 15, 17, 14, 16, 18]); ?>,
        late: <?php echo json_encode($attendanceStats['late'] ?? [2, 3, 1, 4, 2, 3, 1]); ?>,
        absent: <?php echo json_encode($attendanceStats['absent'] ?? [1, 0, 2, 1, 0, 0, 1]); ?>
    };
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    
    const attendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: window.attendanceChartData.labels,
            datasets: [{
                label: 'Hadir',
                data: window.attendanceChartData.present,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }, {
                label: 'Terlambat',
                data: window.attendanceChartData.late,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }, {
                label: 'Absen',
                data: window.attendanceChartData.absent,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5
                    }
                }
            }
        }
    });

    // Period Selector
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>

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
    gap: 20px;
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
}

.welcome-date {
    opacity: 0.9;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
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
    border: none;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
}

.welcome-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.welcome-actions .btn-primary {
    background: white;
    color: #4f46e5;
}

.welcome-actions .btn-success {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.welcome-actions .btn-success:hover {
    background: rgba(255, 255, 255, 0.25);
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
.stat-success::before { background: #10b981; }
.stat-info::before { background: #3b82f6; }
.stat-warning::before { background: #f59e0b; }

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
.stat-success .stat-icon { background: #10b981; }
.stat-info .stat-icon { background: #3b82f6; }
.stat-warning .stat-icon { background: #f59e0b; }

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

.stat-link {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f8fafc;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    opacity: 0;
    transition: opacity 0.2s, background 0.2s;
}

.stat-card:hover .stat-link {
    opacity: 1;
}

.stat-link:hover {
    background: #e2e8f0;
    color: #4f46e5;
}

/* Dashboard Content */
.dashboard-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 1024px) {
    .dashboard-content {
        grid-template-columns: 2fr 1fr;
    }
}

/* Card Styles */
.card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    overflow: hidden;
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

.card-body {
    padding: 20px;
}

/* Chart Container */
.chart-section {
    grid-column: 1 / -1;
}

@media (min-width: 1024px) {
    .chart-section {
        grid-column: 1;
    }
}

.chart-container {
    height: 300px;
    position: relative;
}

.period-selector {
    display: flex;
    background: #f8fafc;
    border-radius: 12px;
    padding: 4px;
}

.period-btn {
    padding: 6px 16px;
    border: none;
    background: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}

.period-btn:hover {
    color: #4f46e5;
}

.period-btn.active {
    background: white;
    color: #4f46e5;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Today's Classes */
.today-classes-section {
    order: 2;
}

@media (min-width: 1024px) {
    .today-classes-section {
        order: 1;
        grid-column: 2;
        grid-row: 1;
    }
}

.classes-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.class-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    transition: background 0.2s;
}

.class-item:hover {
    background: #f1f5f9;
}

.class-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #e0e7ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.class-info {
    flex: 1;
}

.class-info h4 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
    color: #333;
}

.class-details {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
}

.class-details .badge {
    background: #e2e8f0;
    color: #475569;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.students {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
}

.class-schedule {
    display: flex;
    align-items: center;
    gap: 8px;
}

.time {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
}

.time i {
    color: #10b981;
}

.time.muted {
    color: #94a3b8;
}

.time.muted i {
    color: #94a3b8;
}

.status {
    font-size: 11px;
    padding: 2px 8px;
    background: #10b981;
    color: white;
    border-radius: 12px;
    font-weight: 500;
}

.class-actions .btn {
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}

/* Recent Assignments */
.recent-assignments-section {
    order: 3;
}

@media (min-width: 1024px) {
    .recent-assignments-section {
        grid-column: 1;
        grid-row: 2;
    }
}

.assignments-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.assignment-item {
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
}

.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.assignment-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    text-decoration: none;
    flex: 1;
}

.assignment-title:hover {
    color: #4f46e5;
}

.assignment-meta {
    margin-bottom: 12px;
}

.due-date {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
}

.assignment-progress {
    margin-top: 12px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.progress-value {
    font-size: 14px;
    font-weight: 600;
    color: #4f46e5;
}

.progress-text {
    font-size: 12px;
    color: #64748b;
}

.progress {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #4f46e5, #3730a3);
    border-radius: 3px;
    transition: width 1s ease-in-out;
}

/* Recent Submissions */
.recent-submissions-section {
    order: 4;
}

@media (min-width: 1024px) {
    .recent-submissions-section {
        grid-column: 2;
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
    gap: 12px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.submission-content {
    flex: 1;
}

.submission-content h5 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 2px;
    color: #333;
}

.submission-status .btn {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 8px;
}

/* Quick Actions */
.quick-actions-section {
    order: 5;
    grid-column: 1 / -1;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    border-radius: 16px;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
    min-height: 120px;
}

.quick-action:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.action-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    margin-bottom: 12px;
}

.action-text {
    font-size: 14px;
    font-weight: 600;
    color: white;
    text-align: center;
}

/* Color Variants for Quick Actions */
.action-primary { background: linear-gradient(135deg, #4f46e5, #3730a3); }
.action-success { background: linear-gradient(135deg, #10b981, #0d9e70); }
.action-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.action-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
.action-secondary { background: linear-gradient(135deg, #64748b, #475569); }
.action-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
.action-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.action-orange { background: linear-gradient(135deg, #fb923c, #f97316); }

/* Empty States */
.empty-state {
    padding: 40px 20px;
    text-align: center;
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

.empty-state .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
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

/* Responsive Adjustments */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 16px;
    }
    
    .welcome-card {
        padding: 20px;
    }
    
    .welcome-title {
        font-size: 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 20px;
    }
    
    .stat-number {
        font-size: 28px;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .period-selector {
        align-self: stretch;
    }
    
    .quick-actions-grid {
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
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .class-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .class-info {
        width: 100%;
    }
    
    .class-actions {
        align-self: flex-end;
    }
}

/* Utilities */
.text-muted {
    color: #64748b !important;
}

.small {
    font-size: 12px !important;
}

.mb-1 {
    margin-bottom: 4px !important;
}

.btn-link {
    text-decoration: none;
    color: #4f46e5;
    font-weight: 500;
}

.btn-link:hover {
    text-decoration: underline;
}

.btn-sm {
    padding: 6px 12px !important;
    font-size: 12px !important;
}

.btn-outline-primary {
    background: transparent;
    border: 1px solid #4f46e5;
    color: #4f46e5;
}

.btn-outline-primary:hover {
    background: #4f46e5;
    color: white;
}

/* Bootstrap Badge Override */
.badge {
    font-size: 12px;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 12px;
}

.bg-secondary {
    background: #64748b !important;
}

.bg-success {
    background: #10b981 !important;
}

.bg-warning {
    background: #f59e0b !important;
}
</style>
@endsection