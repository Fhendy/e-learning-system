@extends('layouts.app')

@section('title', 'Absensi Saya')

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
                        <h1 class="page-title mb-1">Absensi Saya</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Riwayat absensi Anda
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('attendance.student.statistics') }}" class="btn btn-outline-info">
                    <i class="bi bi-graph-up me-2"></i>Statistik Lengkap
                </a>
            </div>
        </div>
    </div>

    <!-- Statistik Bulan Ini -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-primary-light text-primary">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $monthStats['present'] ?? 0 }}</h3>
                        <p class="stats-label mb-0">Hadir</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-warning-light text-warning">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $monthStats['late'] ?? 0 }}</h3>
                        <p class="stats-label mb-0">Terlambat</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-danger-light text-danger">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $monthStats['absent'] ?? 0 }}</h3>
                        <p class="stats-label mb-0">Tidak Hadir</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-percent fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $monthStats['percentage'] ?? 0 }}%</h3>
                        <p class="stats-label mb-0">Persentase</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bulan -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel me-2 text-primary"></i>
                Filter Absensi
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('attendance.student.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Bulan</label>
                    <select name="month" id="month" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <select name="year" id="year" class="form-select">
                        @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                            <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('attendance.student.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-repeat me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Absensi -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table me-2 text-primary"></i>
                    Riwayat Absensi Bulan {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
                </h5>
                <span class="badge bg-primary">{{ $attendances->total() }} Data</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($attendances->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 ps-md-4">#</th>
                            <th>KELAS</th>
                            <th>TANGGAL</th>
                            <th>STATUS</th>
                            <th>WAKTU</th>
                            <th>KETERANGAN</th>
                            <th class="text-end pe-3 pe-md-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        @php
                            $statusColors = [
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'danger',
                                'sick' => 'info',
                                'permission' => 'secondary'
                            ];
                            $statusTexts = [
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir',
                                'sick' => 'Sakit',
                                'permission' => 'Izin'
                            ];
                            $statusColor = $statusColors[$attendance->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td class="ps-3 ps-md-4">{{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}</td>
                            <td>
                                <div>
                                    <div class="fw-semibold">{{ $attendance->class->class_name ?? 'N/A' }}</div>
                                    <div class="text-muted small">{{ $attendance->class->class_code ?? '' }}</div>
                                </div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $statusTexts[$attendance->status] ?? $attendance->status }}
                                </span>
                            </td>
                            <td>
                                @if($attendance->checked_in_at)
                                    {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->qrCode)
                                    <small class="text-muted">QR: {{ $attendance->qrCode->code }}</small>
                                @endif
                                @if($attendance->notes)
                                    <br>
                                    <small>{{ Str::limit($attendance->notes, 30) }}</small>
                                @endif
                            </td>
                            <td class="text-end pe-3 pe-md-4">
                                <a href="{{ route('attendance.student.show', $attendance->id) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   data-bs-toggle="tooltip" 
                                   title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div>
                        <p class="mb-0 text-muted small">
                            Menampilkan <strong>{{ $attendances->firstItem() ?? 0 }}</strong> 
                            sampai <strong>{{ $attendances->lastItem() ?? 0 }}</strong> 
                            dari <strong>{{ $attendances->total() }}</strong> data
                        </p>
                    </div>
                    <nav aria-label="Page navigation">
                        {{ $attendances->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                    </nav>
                </div>
            </div>
            @else
            <div class="empty-state text-center py-5">
                <div class="empty-icon mx-auto mb-3">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                </div>
                <h5 class="mb-2">Belum ada data absensi</h5>
                <p class="text-muted">Belum ada absensi untuk bulan {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
                <a href="{{ route('attendance.scan.page') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-qr-code-scan me-2"></i>Absen Sekarang
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Informasi Kelas -->
    @if($classes->count() > 0)
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-people me-2 text-primary"></i>
                Kelas Saya
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($classes as $class)
                <div class="col-md-4 col-sm-6">
                    <div class="class-card">
                        <div class="class-header">
                            <h6 class="class-name">{{ $class->class_name }}</h6>
                            <span class="class-code">{{ $class->class_code }}</span>
                        </div>
                        <div class="class-info">
                            <div class="class-teacher">
                                <i class="bi bi-person-badge me-1"></i>
                                {{ $class->teacher->name ?? 'N/A' }}
                            </div>
                            <div class="class-members">
                                <i class="bi bi-people me-1"></i>
                                {{ $class->students_count ?? 0 }} siswa
                            </div>
                        </div>
                        <div class="class-footer">
                            <a href="{{ route('classes.show', $class->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Lihat Kelas
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- QR Codes Aktif Hari Ini -->
    @if(isset($recentQrCodes) && $recentQrCodes->count() > 0)
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-qr-code me-2 text-success"></i>
                QR Code Aktif Hari Ini
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                Scan QR Code berikut untuk absensi hari ini
            </div>
            <div class="row g-3">
                @foreach($recentQrCodes as $qrCode)
                <div class="col-md-4">
                    <div class="qr-card">
                        <div class="text-center">
                            <h6 class="mb-2">{{ $qrCode->class->class_name }}</h6>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-clock me-1"></i>
                                {{ $qrCode->formatted_time_range ?? $qrCode->start_time . ' - ' . $qrCode->end_time }}
                            </p>
                            @if($qrCode->qr_code_image)
                                @php
                                    $imageUrl = null;
                                    if (Storage::disk('public')->exists($qrCode->qr_code_image)) {
                                        $imageUrl = Storage::url($qrCode->qr_code_image);
                                    } elseif (Storage::disk('public')->exists('qr-codes/' . $qrCode->code . '.png')) {
                                        $imageUrl = Storage::url('qr-codes/' . $qrCode->code . '.png');
                                    }
                                @endphp
                                @if($imageUrl)
                                <img src="{{ $imageUrl }}" 
                                     alt="QR Code" 
                                     class="img-fluid mb-2"
                                     style="max-width: 120px;">
                                @endif
                            @endif
                            <p class="small text-muted mb-2">Kode: <code>{{ $qrCode->code }}</code></p>
                            <a href="{{ route('attendance.scan.page', ['qr_code' => $qrCode->code]) }}" 
                               class="btn btn-sm btn-success w-100">
                                <i class="bi bi-camera me-1"></i> Scan QR
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
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

.card-footer {
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 0.75rem 1rem;
}

/* Table */
.table {
    margin: 0;
}

.table thead th {
    font-weight: 600;
    font-size: 0.688rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.table tbody td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f9fafb;
}

/* Class Card */
.class-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 1rem;
    transition: var(--transition);
    height: 100%;
}

.class-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.class-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.class-name {
    font-size: 0.875rem;
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

.class-info {
    margin-bottom: 0.75rem;
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

/* QR Card */
.qr-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 1rem;
    transition: var(--transition);
    height: 100%;
}

.qr-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
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

.btn-outline-info {
    border-color: #e5e7eb;
    color: #3b82f6;
    background: white;
}

.btn-outline-info:hover {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
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

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
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

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-info {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
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
    margin: 0 auto;
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
    
    .table thead th,
    .table tbody td {
        padding: 0.625rem;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-group {
        flex-wrap: wrap;
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
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto refresh every 30 seconds if there are active QR codes
    @if(isset($recentQrCodes) && $recentQrCodes->count() > 0)
    let refreshTimer = setTimeout(function() {
        if (!document.hidden) {
            window.location.reload();
        }
    }, 30000);
    
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && refreshTimer) {
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(function() {
                window.location.reload();
            }, 30000);
        }
    });
    @endif
});
</script>
@endsection