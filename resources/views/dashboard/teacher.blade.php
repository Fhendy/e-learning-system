@extends('layouts.app')

@section('title', 'Dashboard Guru')

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
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-primary-light text-primary">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $classes->count() ?? 0 }}</h3>
                        <p class="stats-label mb-0">Total Kelas</p>
                    </div>
                </div>
                <a href="{{ route('classes.index') }}" class="stats-link">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-person-badge fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $totalStudents ?? 0 }}</h3>
                        <p class="stats-label mb-0">Total Siswa</p>
                    </div>
                </div>
                <a href="{{ route('students.index') }}" class="stats-link">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-info-light text-info">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $activeAssignments ?? 0 }}</h3>
                        <p class="stats-label mb-0">Tugas Aktif</p>
                    </div>
                </div>
                <a href="{{ route('assignments.teacher.index') }}" class="stats-link">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-warning-light text-warning">
                        <i class="bi bi-qr-code-scan fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $activeQrCodes ?? 0 }}</h3>
                        <p class="stats-label mb-0">QR Code Aktif</p>
                    </div>
                </div>
                <a href="{{ route('qr-codes.dashboard') }}" class="stats-link">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="row g-3 g-md-4">
        <!-- Left Column -->
        <div class="col-xl-8 col-lg-7">
            <!-- Chart Section -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart me-2"></i>
                            Statistik Absensi (7 Hari Terakhir)
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshChart()">
                            <i class="bi bi-arrow-repeat"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                    <div id="chartEmptyMessage" class="alert alert-info text-center mt-3" style="display: none;">
                        <i class="bi bi-info-circle me-2"></i>
                        Belum ada data absensi untuk 7 hari terakhir. Silakan buat QR Code dan lakukan absensi terlebih dahulu.
                    </div>
                </div>
            </div>

            <!-- Recent Assignments -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-journal-text me-2"></i>
                            Tugas Terbaru
                        </h5>
                        <a href="{{ route('assignments.teacher.index') }}" class="btn btn-link btn-sm">
                            Lihat Semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recentAssignments) && $recentAssignments->count() > 0)
                        <div class="assignments-list">
                            @foreach($recentAssignments as $assignment)
                            <div class="assignment-item">
                                <div class="assignment-header">
                                    <a href="{{ route('assignments.show', $assignment) }}" class="assignment-title">
                                        {{ $assignment->title }}
                                    </a>
                                    <span class="badge bg-secondary">{{ $assignment->class->class_code ?? 'N/A' }}</span>
                                </div>
                                <div class="assignment-meta">
                                    <span class="due-date">
                                        <i class="bi bi-calendar"></i>
                                        Deadline: {{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}
                                    </span>
                                </div>
                                <div class="assignment-progress">
                                    @php
                                        $submitted = $assignment->submissions->count();
                                        $total = $assignment->class->students->count() ?? 1;
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
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mx-auto mb-3">
                                <i class="bi bi-journal fs-1 text-muted"></i>
                            </div>
                            <h5 class="mb-2">Belum ada tugas</h5>
                            <p class="text-muted mb-3">Buat tugas pertama untuk kelas Anda</p>
                            <a href="{{ route('assignments.teacher.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle me-2"></i>Buat Tugas
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-xl-4 col-lg-5">
            <!-- Today's Classes -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-day me-2"></i>
                        Kelas Hari Ini
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(isset($todayClasses) && $todayClasses->count() > 0)
                        <div class="classes-list">
                            @foreach($todayClasses as $class)
                            <div class="class-item">
                                <div class="class-icon">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <div class="class-info">
                                    <h6 class="class-name">{{ $class->class_name }}</h6>
                                    <div class="class-meta">
                                        <span class="class-code">{{ $class->class_code }}</span>
                                        <span class="student-count">
                                            <i class="bi bi-people"></i>
                                            {{ $class->students->count() ?? 0 }} siswa
                                        </span>
                                    </div>
                                    @php
                                        $qrCode = $class->active_qr_code ?? $class->qrCodes()->whereDate('date', today())->first();
                                    @endphp
                                    <div class="class-schedule">
                                        @if($qrCode)
                                            <span class="time">
                                                <i class="bi bi-clock"></i>
                                                {{ $qrCode->formatted_start_time ?? $qrCode->start_time }}
                                            </span>
                                            @if($qrCode->is_active)
                                                <span class="status-badge active small">Aktif</span>
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
                                        <a href="{{ route('qr-codes.show', $qrCode) }}" class="btn btn-icon btn-sm" title="Lihat QR Code">
                                            <i class="bi bi-qr-code-scan"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('qr-codes.create', ['class_id' => $class->id]) }}" class="btn btn-icon btn-sm" title="Buat QR Code">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mx-auto mb-3">
                                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                            </div>
                            <h5 class="mb-2">Tidak ada kelas hari ini</h5>
                            <p class="text-muted mb-3">Anda bisa membuat QR code untuk kelas lain</p>
                            <a href="{{ route('qr-codes.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-qr-code-scan me-2"></i>Buat QR Code
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Submissions -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-upload me-2"></i>
                            Pengumpulan Terbaru
                        </h5>
                        <a href="{{ route('assignments.teacher.index') }}" class="btn btn-link btn-sm">
                            Lihat Semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(isset($recentSubmissions) && $recentSubmissions->count() > 0)
                        <div class="submissions-list">
                            @foreach($recentSubmissions as $submission)
                            <div class="submission-item">
                                <div class="submission-avatar">
                                    <div class="avatar">
                                        {{ strtoupper(substr($submission->student->name ?? 'U', 0, 1)) }}
                                    </div>
                                </div>
                                <div class="submission-content">
                                    <h6 class="submission-name">{{ $submission->student->name ?? 'Unknown' }}</h6>
                                    <p class="submission-title">{{ Str::limit($submission->assignment->title ?? 'Tugas', 30) }}</p>
                                    <small class="submission-time">
                                        <i class="bi bi-clock"></i>
                                        {{ $submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->diffForHumans() : 'Baru saja' }}
                                    </small>
                                </div>
                                <div class="submission-status">
                                    @if($submission->score)
                                        <span class="badge bg-success">{{ $submission->score }}</span>
                                    @else
                                        <a href="{{ route('submissions.grade', $submission) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Nilai
                                        </a>
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
                            <h5 class="mb-2">Belum ada pengumpulan</h5>
                            <p class="text-muted">Tunggu siswa mengumpulkan tugas mereka</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Aksi Cepat
                    </h5>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Data dari controller
        var attendanceStats = @json($attendanceStats ?? []);
        
        var labels = attendanceStats.labels || ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        var presentData = attendanceStats.present || [0, 0, 0, 0, 0, 0, 0];
        var lateData = attendanceStats.late || [0, 0, 0, 0, 0, 0, 0];
        var absentData = attendanceStats.absent || [0, 0, 0, 0, 0, 0, 0];
        
        // Pastikan 7 elemen
        while (labels.length < 7) { labels.push('Hari'); }
        while (presentData.length < 7) { presentData.push(0); }
        while (lateData.length < 7) { lateData.push(0); }
        while (absentData.length < 7) { absentData.push(0); }
        
        var ctx = document.getElementById('attendanceChart');
        if (!ctx) return;
        
        var hasData = false;
        for (var i = 0; i < presentData.length; i++) {
            if (presentData[i] > 0 || lateData[i] > 0 || absentData[i] > 0) {
                hasData = true;
                break;
            }
        }
        
        if (!hasData) {
            var emptyMsg = document.getElementById('chartEmptyMessage');
            if (emptyMsg) emptyMsg.style.display = 'block';
            return;
        }
        
        var emptyMsg = document.getElementById('chartEmptyMessage');
        if (emptyMsg) emptyMsg.style.display = 'none';
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Hadir',
                    data: presentData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Terlambat',
                    data: lateData,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Absen',
                    data: absentData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10 } },
                    tooltip: { callbacks: { label: function(context) { return context.dataset.label + ': ' + context.raw + ' siswa'; } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 5, callback: function(v) { return v + ' siswa'; } }, title: { display: true, text: 'Jumlah Siswa' } },
                    x: { title: { display: true, text: 'Hari' } }
                }
            }
        });
        
        // Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(el) {
            new bootstrap.Tooltip(el);
        });
    });
})();

function refreshChart() {
    window.location.reload();
}
</script>

<style>
/* CSS Variables */
:root {
    --primary: #4f46e5;
    --primary-light: #e0e7ff;
    --success: #10b981;
    --success-light: #d1fae5;
    --warning: #f59e0b;
    --warning-light: #fef3c7;
    --info: #3b82f6;
    --info-light: #dbeafe;
    --danger: #ef4444;
    --danger-light: #fee2e2;
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
    border: none;
}

.welcome-actions .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.welcome-actions .btn-success {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.welcome-actions .btn-success:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
}

/* Stats Cards */
.stats-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 0.875rem;
    border: 1px solid #e5e7eb;
    transition: var(--transition);
    position: relative;
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

.stats-link {
    position: absolute;
    bottom: 0.875rem;
    right: 0.875rem;
    width: 28px;
    height: 28px;
    border-radius: 0.5rem;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: var(--transition);
}

.stats-link:hover {
    background: #e2e8f0;
    color: #4f46e5;
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

/* Chart Container */
.chart-container {
    height: 300px;
    position: relative;
}

@media (max-width: 768px) {
    .chart-container {
        height: 250px;
    }
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
    border-bottom: 1px solid #e5e7eb;
    transition: var(--transition);
}

.class-item:last-child {
    border-bottom: none;
}

.class-item:hover {
    background-color: #f8fafc;
}

.class-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #e0e7ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.class-info {
    flex: 1;
}

.class-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.class-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.688rem;
    color: #6b7280;
}

.class-code {
    background: #f3f4f6;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-family: monospace;
}

.class-schedule {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.25rem;
}

.time {
    font-size: 0.688rem;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.time.muted {
    color: #94a3b8;
}

.status-badge.active {
    background: #10b981;
    color: white;
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
    border-radius: 1rem;
}

.class-actions {
    flex-shrink: 0;
}

/* Assignments List */
.assignments-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.assignment-item {
    padding: 0.875rem;
    background: #f8fafc;
    border-radius: 10px;
}

.assignment-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
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

.assignment-meta {
    margin-bottom: 0.75rem;
}

.due-date {
    font-size: 0.688rem;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.assignment-progress {
    margin-top: 0.5rem;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.375rem;
}

.progress-value {
    font-size: 0.75rem;
    font-weight: 600;
    color: #4f46e5;
}

.progress-text {
    font-size: 0.688rem;
    color: #64748b;
}

.progress {
    height: 5px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #4f46e5, #3730a3);
    border-radius: 3px;
    transition: width 0.6s ease;
}

/* Submissions List */
.submissions-list {
    display: flex;
    flex-direction: column;
}

.submission-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.submission-item:last-child {
    border-bottom: none;
}

.submission-avatar {
    flex-shrink: 0;
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
    font-size: 0.875rem;
}

.submission-content {
    flex: 1;
}

.submission-name {
    font-size: 0.813rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.125rem;
}

.submission-title {
    font-size: 0.688rem;
    color: #64748b;
    margin-bottom: 0.125rem;
}

.submission-time {
    font-size: 0.625rem;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.submission-status {
    flex-shrink: 0;
}

.submission-status .btn {
    padding: 0.25rem 0.625rem;
    font-size: 0.688rem;
    border-radius: 0.5rem;
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 0.75rem;
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.875rem;
    border-radius: 0.75rem;
    text-decoration: none;
    transition: var(--transition);
}

.quick-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: white;
    margin-bottom: 0.5rem;
}

.action-text {
    font-size: 0.688rem;
    font-weight: 600;
    color: white;
    text-align: center;
}

/* Action Colors */
.action-primary { background: linear-gradient(135deg, #4f46e5, #3730a3); }
.action-success { background: linear-gradient(135deg, #10b981, #0d9e70); }
.action-info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.action-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
.action-secondary { background: linear-gradient(135deg, #64748b, #475569); }
.action-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }

/* Empty State */
.empty-state {
    text-align: center;
}

.empty-icon {
    width: 56px;
    height: 56px;
    background: #f9fafb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state h5 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.empty-state p {
    font-size: 0.75rem;
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

.btn-sm {
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
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

.btn-link {
    color: #4f46e5;
    text-decoration: none;
    font-size: 0.75rem;
}

.btn-link:hover {
    text-decoration: underline;
}

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-info-light { background: #dbeafe; }
.bg-warning-light { background: #fef3c7; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-info { color: #3b82f6 !important; }
.text-warning { color: #f59e0b !important; }

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
    
    .class-icon {
        width: 36px;
        height: 36px;
        font-size: 1rem;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(3, 1fr);
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
    
    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
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
@endsection