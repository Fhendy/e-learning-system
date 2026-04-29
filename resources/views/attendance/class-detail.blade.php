@extends('layouts.app')

@section('title', 'Detail Absensi Kelas')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Absensi Kelas</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-building me-1"></i>{{ $class->class_name }}
                            <span class="mx-2">•</span>
                            <i class="bi bi-person-badge me-1"></i>{{ $class->teacher->name ?? 'Guru' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button onclick="exportClassAttendance()" class="btn btn-success">
                    <i class="bi bi-download me-2"></i>Export
                </button>
                <a href="{{ route('qr-codes.create') }}?class_id={{ $class->id }}" class="btn btn-primary">
                    <i class="bi bi-qr-code me-2"></i>Buat QR Code
                </a>
                <a href="{{ route('attendance.teacher.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <!-- Class Statistics -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi Kelas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-list">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="info-label">Nama Kelas</div>
                                        <div class="info-value">{{ $class->class_name }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="bi bi-book"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="info-label">Mata Pelajaran</div>
                                        <div class="info-value">{{ $class->subject ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="info-label">Guru</div>
                                        <div class="info-value">{{ $class->teacher->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-list">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="info-label">Jumlah Siswa</div>
                                        <div class="info-value">{{ $students->count() ?? 0 }} siswa</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="bi bi-calendar"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="info-label">Jumlah Pertemuan</div>
                                        <div class="info-value">{{ $totalSessions ?? 0 }} pertemuan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-funnel me-2 text-primary"></i>
                        Filter Data
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('attendance.class.show', $class->id) }}">
                        <div class="mb-3">
                            <label class="form-label">Bulan</label>
                            <select name="month" class="form-select">
                                <option value="">Semua Bulan</option>
                                @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tahun</label>
                            <select name="year" class="form-select">
                                <option value="">Semua Tahun</option>
                                @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Statistics Summary -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="stat-mini text-center p-3 rounded bg-success-light">
                <div class="stat-mini-value text-success fw-bold fs-2">{{ $totalStats['present'] ?? 0 }}</div>
                <div class="stat-mini-label text-muted small">Hadir</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-mini text-center p-3 rounded bg-warning-light">
                <div class="stat-mini-value text-warning fw-bold fs-2">{{ $totalStats['late'] ?? 0 }}</div>
                <div class="stat-mini-label text-muted small">Terlambat</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-mini text-center p-3 rounded bg-danger-light">
                <div class="stat-mini-value text-danger fw-bold fs-2">{{ $totalStats['absent'] ?? 0 }}</div>
                <div class="stat-mini-label text-muted small">Absen</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-mini text-center p-3 rounded bg-info-light">
                <div class="stat-mini-value text-info fw-bold fs-2">{{ ($totalStats['sick'] ?? 0) + ($totalStats['permission'] ?? 0) }}</div>
                <div class="stat-mini-label text-muted small">Sakit/Izin</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-mini text-center p-3 rounded bg-primary-light">
                <div class="stat-mini-value text-primary fw-bold fs-2">{{ $attendanceRate ?? 0 }}%</div>
                <div class="stat-mini-label text-muted small">Kehadiran</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-mini text-center p-3 rounded bg-secondary-light">
                <div class="stat-mini-value text-secondary fw-bold fs-2">{{ $students->count() ?? 0 }}</div>
                <div class="stat-mini-label text-muted small">Total Siswa</div>
            </div>
        </div>
    </div>

    <!-- Daftar Siswa dan Absensi -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table me-2 text-primary"></i>
                    Daftar Siswa dan Absensi
                </h5>
                <span class="badge bg-primary">{{ $students->count() }} Siswa</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="classAttendanceTable">
                    <thead>
                        <tr class="table-light">
                            <th rowspan="2" class="align-middle text-center" style="width: 50px">No</th>
                            <th rowspan="2" class="align-middle">SISWA</th>
                            <th rowspan="2" class="align-middle text-center" style="width: 100px">NIS</th>
                            <th colspan="5" class="text-center" style="background: #f8fafc">STATISTIK</th>
                            @foreach($dates as $date)
                            <th class="text-center" style="min-width: 65px; background: #f1f5f9">
                                {{ $date->format('d/m') }}
                            </th>
                            @endforeach
                        </tr>
                        <tr class="table-light">
                            <th class="text-center" style="width: 50px">H</th>
                            <th class="text-center" style="width: 50px">T</th>
                            <th class="text-center" style="width: 50px">A</th>
                            <th class="text-center" style="width: 60px">S/I</th>
                            <th class="text-center" style="width: 60px">%</th>
                            @foreach($dates as $date)
                            <th></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $studentStats = $student->getAttendanceStats($class->id, $month, $year);
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="student-avatar">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $student->name }}</div>
                                        <div class="text-muted small">{{ $student->nis_nip }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $student->nis_nip }}</td>
                            
                            <!-- Statistics -->
                            <td class="text-center text-success fw-semibold">{{ $studentStats['present'] }}</td>
                            <td class="text-center text-warning fw-semibold">{{ $studentStats['late'] }}</td>
                            <td class="text-center text-danger fw-semibold">{{ $studentStats['absent'] }}</td>
                            <td class="text-center text-info fw-semibold">{{ ($studentStats['sick'] ?? 0) + ($studentStats['permission'] ?? 0) }}</td>
                            <td class="text-center">
                                @php
                                    $percentageClass = ($studentStats['percentage'] ?? 0) >= 75 ? 'success' : (($studentStats['percentage'] ?? 0) >= 50 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $percentageClass }}">
                                    {{ $studentStats['percentage'] ?? 0 }}%
                                </span>
                            </td>
                            
                            <!-- Daily attendance -->
                            @foreach($dates as $date)
                            @php
                                $attendance = $student->attendances
                                    ->where('class_id', $class->id)
                                    ->where('attendance_date', $date->format('Y-m-d'))
                                    ->first();
                                $statusClass = match($attendance->status ?? null) {
                                    'present' => 'success',
                                    'late' => 'warning',
                                    'absent' => 'danger',
                                    'sick' => 'info',
                                    'permission' => 'secondary',
                                    default => 'light'
                                };
                                $statusLetter = match($attendance->status ?? null) {
                                    'present' => 'H',
                                    'late' => 'T',
                                    'absent' => 'A',
                                    'sick' => 'S',
                                    'permission' => 'I',
                                    default => '-'
                                };
                            @endphp
                            <td class="text-center">
                                @if($attendance)
                                    <span class="badge bg-{{ $statusClass }}" 
                                          title="{{ ucfirst($attendance->status) }} - {{ $attendance->checked_in_at ? \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i') : '' }}">
                                        {{ $statusLetter }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        
                        <!-- Footer Statistics -->
                        <tr class="table-secondary fw-semibold">
                            <td colspan="3" class="text-end">TOTAL</td>
                            <td class="text-center">{{ $totalStats['present'] ?? 0 }}</td>
                            <td class="text-center">{{ $totalStats['late'] ?? 0 }}</td>
                            <td class="text-center">{{ $totalStats['absent'] ?? 0 }}</td>
                            <td class="text-center">{{ ($totalStats['sick'] ?? 0) + ($totalStats['permission'] ?? 0) }}</td>
                            <td class="text-center">{{ $attendanceRate ?? 0 }}%</td>
                            @foreach($dates as $date)
                            @php
                                $dateStats = $class->getDateStats($date);
                                $dateClass = ($dateStats['percentage'] ?? 0) >= 75 ? 'success' : (($dateStats['percentage'] ?? 0) >= 50 ? 'warning' : 'danger');
                            @endphp
                            <td class="text-center">
                                <small class="text-{{ $dateClass }}">
                                    {{ $dateStats['present'] ?? 0 }}/{{ $dateStats['total'] ?? 0 }}
                                </small>
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Legend -->
            <div class="p-3 border-top">
                <h6 class="mb-2">
                    <i class="bi bi-info-circle me-2 text-primary"></i>
                    Keterangan:
                </h6>
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success">H</span>
                        <small class="text-muted">Hadir</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning">T</span>
                        <small class="text-muted">Terlambat</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger">A</span>
                        <small class="text-muted">Absen</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-info">S</span>
                        <small class="text-muted">Sakit</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">I</span>
                        <small class="text-muted">Izin</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted">-</span>
                        <small class="text-muted">Tidak ada data</small>
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
    --info: #3b82f6;
    --info-light: #dbeafe;
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

.page-icon-large {
    width: clamp(44px, 10vw, 56px);
    height: clamp(44px, 10vw, 56px);
    border-radius: 14px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(1.25rem, 3vw, 1.5rem);
    flex-shrink: 0;
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
    gap: 0.875rem;
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

/* Stat Mini */
.stat-mini {
    transition: var(--transition);
}

.stat-mini:hover {
    transform: translateY(-2px);
}

.stat-mini-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-mini-label {
    font-size: 0.688rem;
}

/* Student Avatar */
.student-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.813rem;
    flex-shrink: 0;
}

/* Table */
.table {
    margin: 0;
    font-size: 0.813rem;
}

.table thead th {
    font-weight: 600;
    font-size: 0.688rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
}

.table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f9fafb;
}

.table-bordered {
    border-color: #e5e7eb;
}

.table-bordered td,
.table-bordered th {
    border-color: #e5e7eb;
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
.bg-info { background: #3b82f6 !important; }
.bg-secondary { background: #6b7280 !important; }
.bg-light { background: #f3f4f6 !important; color: #6b7280; }

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
}

.btn-success {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-success:hover {
    background: #0d9e70;
    border-color: #0d9e70;
}

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
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

/* Form */
.form-label {
    font-weight: 500;
    font-size: 0.813rem;
    color: #374151;
    margin-bottom: 0.375rem;
}

.form-select {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    font-size: 0.813rem;
    transition: var(--transition);
}

.form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-success {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}

/* Colors */
.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-warning-light { background: #fef3c7; }
.bg-danger-light { background: #fee2e2; }
.bg-info-light { background: #dbeafe; }
.bg-secondary-light { background: #f3f4f6; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }
.text-info { color: #3b82f6 !important; }
.text-muted { color: #6b7280 !important; }

/* Table Light */
.table-light {
    background-color: #f8fafc !important;
}

/* Responsive */
@media (min-width: 992px) {
    .card-body {
        padding: 1.25rem;
    }
    
    .student-avatar {
        width: 40px;
        height: 40px;
        font-size: 0.875rem;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .page-icon-large {
        width: 44px;
        height: 44px;
    }
    
    .info-icon {
        width: 28px;
        height: 28px;
    }
    
    .student-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.5rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .info-item {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-icon {
        margin-bottom: 0;
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

.card {
    animation: fadeIn 0.3s ease forwards;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function exportClassAttendance() {
    const table = document.getElementById('classAttendanceTable');
    if (!table) return;
    
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.table_to_sheet(table, { raw: true });
    
    // Auto-size columns
    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let C = range.s.c; C <= range.e.c; ++C) {
        let max_width = 10;
        for (let R = range.s.r; R <= range.e.r; ++R) {
            const cell_address = XLSX.utils.encode_cell({ r: R, c: C });
            const cell = ws[cell_address];
            if (cell && cell.v) {
                const width = String(cell.v).length;
                if (width > max_width) max_width = Math.min(width, 30);
            }
        }
        if (!ws['!cols']) ws['!cols'] = [];
        ws['!cols'][C] = { wch: max_width };
    }
    
    XLSX.utils.book_append_sheet(wb, ws, 'Absensi Kelas');
    
    const className = '{{ $class->class_name }}'.replace(/\s+/g, '_');
    const filename = `Absensi_${className}_{{ date('Y_m_d') }}.xlsx`;
    
    XLSX.writeFile(wb, filename);
}
</script>
@endsection