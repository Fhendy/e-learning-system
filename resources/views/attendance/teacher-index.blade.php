@extends('layouts.app')

@section('title', 'Manajemen Absensi Guru')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-clipboard-check-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Manajemen Absensi</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Kelola absensi siswa dan kehadiran
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Generate QR Code
                </a>
                <a href="{{ route('attendance.teacher.export') }}" class="btn btn-success">
                    <i class="bi bi-download me-2"></i>Export Data
                </a>
                <button class="btn btn-info" onclick="showQuickStats()">
                    <i class="bi bi-graph-up me-2"></i>Quick Stats
                </button>
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <!-- Statistik Hari Ini -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-bar-chart me-2 text-primary"></i>
                Statistik Absensi Hari Ini
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-2">
                    <div class="stat-mini text-center p-3 rounded bg-success-light">
                        <div class="stat-mini-value text-success fw-bold fs-2">{{ $todayStats['present'] ?? 0 }}</div>
                        <div class="stat-mini-label text-muted small">Hadir</div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-mini text-center p-3 rounded bg-warning-light">
                        <div class="stat-mini-value text-warning fw-bold fs-2">{{ $todayStats['late'] ?? 0 }}</div>
                        <div class="stat-mini-label text-muted small">Terlambat</div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-mini text-center p-3 rounded bg-danger-light">
                        <div class="stat-mini-value text-danger fw-bold fs-2">{{ $todayStats['absent'] ?? 0 }}</div>
                        <div class="stat-mini-label text-muted small">Absen</div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-mini text-center p-3 rounded bg-info-light">
                        <div class="stat-mini-value text-info fw-bold fs-2">{{ $todayStats['sick'] ?? 0 }}</div>
                        <div class="stat-mini-label text-muted small">Sakit</div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-mini text-center p-3 rounded bg-primary-light">
                        <div class="stat-mini-value text-primary fw-bold fs-2">{{ $todayStats['permission'] ?? 0 }}</div>
                        <div class="stat-mini-label text-muted small">Izin</div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="stat-mini text-center p-3 rounded bg-secondary-light">
                        <div class="stat-mini-value text-secondary fw-bold fs-2">{{ $todayStats['total'] ?? 0 }}</div>
                        <div class="stat-mini-label text-muted small">Total</div>
                    </div>
                </div>
            </div>
            
            @if(($todayStats['total'] ?? 0) > 0)
            <div class="mt-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Persentase Kehadiran</span>
                    <span class="small fw-semibold">{{ $todayStats['percentage'] ?? 0 }}%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $todayStats['percentage'] ?? 0 }}%"></div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- QR Code Aktif -->
    @if(isset($activeQrCodes) && $activeQrCodes->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-qr-code me-2 text-success"></i>
                QR Code Aktif Hari Ini
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($activeQrCodes as $qrCode)
                <div class="col-md-4">
                    <div class="qr-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">{{ $qrCode->class->class_name }}</h6>
                            <span class="status-badge {{ $qrCode->isActive() ? 'active' : 'inactive' }}">
                                <i class="bi bi-circle-fill"></i>
                                {{ $qrCode->isActive() ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($qrCode->start_time)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($qrCode->end_time)->format('H:i') }}
                        </div>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-person-badge me-1"></i>
                            {{ $qrCode->class->teacher->name ?? 'Guru' }}
                        </div>
                        <div class="mb-3">
                            @php
                                $attendanceCount = $qrCode->getAttendanceCount();
                                $totalStudents = $qrCode->class->students->count() ?? 0;
                                $percentage = $totalStudents > 0 ? ($attendanceCount / $totalStudents) * 100 : 0;
                            @endphp
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Absensi:</span>
                                <span class="small fw-semibold">{{ $attendanceCount }}/{{ $totalStudents }}</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('qr-codes.show', $qrCode) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>
                            @if($qrCode->isActive())
                            <form action="{{ route('qr-codes.deactivate', $qrCode) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-toggle-off me-1"></i>Nonaktifkan
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('attendance.teacher.class.show', $class->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-people me-1"></i>Kelas
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

<!-- Filter dan Form Input Manual -->
<div class="row g-3 g-md-4 mb-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>
                    Input Manual Absensi
                </h5>
            </div>
            <div class="card-body">
                <!-- PERBAIKAN: Ganti route dari 'attendance.teacher.manual' menjadi 'attendance.teacher.manual.store' -->
                <form action="{{ route('attendance.teacher.manual.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="class_id" class="form-select" required id="classSelect">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Siswa</label>
                        <select name="student_id" class="form-select" required id="studentSelect">
                            <option value="">Pilih Siswa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="attendance_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Absen</option>
                            <option value="sick">Sakit</option>
                            <option value="permission">Izin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Waktu Absen (HH:MM)</label>
                        <input type="time" name="checked_in_at" class="form-control" value="{{ date('H:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan Absensi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- ... sisanya tetap sama ... -->
</div>

    <!-- Daftar Absensi -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list me-2 text-primary"></i>
                    Daftar Absensi
                </h5>
                <div>
                    <span class="badge bg-primary me-2">{{ $attendances->total() }} Data</span>
                    <button class="btn btn-sm btn-outline-success" onclick="exportTableToExcel()">
                        <i class="bi bi-file-earmark-excel me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($attendances->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="attendanceTable">
                    <thead>
                        <tr>
                            <th class="ps-3 ps-md-4">#</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Keterangan</th>
                            <th class="text-end pe-3 pe-md-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            <td class="ps-3 ps-md-4">{{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}</td>
                            <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}</td>
                            <td>
                                <div>
                                    <div class="fw-semibold">{{ $attendance->student->name ?? 'N/A' }}</div>
                                    <div class="text-muted small">{{ $attendance->student->nis_nip ?? '-' }}</div>
                                </div>
                            </td>
                            <td>{{ $attendance->class->class_name ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColor = match($attendance->status) {
                                        'present' => 'success',
                                        'late' => 'warning',
                                        'absent' => 'danger',
                                        'sick' => 'info',
                                        'permission' => 'secondary',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ ucfirst($attendance->status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td>
                                @if($attendance->checked_in_at)
                                    {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $attendance->notes ? Str::limit($attendance->notes, 30) : '-' }}</td>
                            <td class="text-end pe-3 pe-md-4">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('attendance.teacher.attendance.edit', $attendance->id) }}" class="btn btn-sm btn-outline-warning">
                                       class="btn btn-sm btn-outline-warning"
                                       data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="showAttendanceDetail({{ $attendance->id }})"
                                            data-bs-toggle="tooltip" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <form action="{{ route('attendance.teacher.attendance.destroy', $attendance) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Hapus absensi ini?')"
                                                data-bs-toggle="tooltip" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
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
                <p class="text-muted mb-4">Mulai dengan membuat QR Code atau input manual</p>
                <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                    <i class="bi bi-qr-code me-2"></i>Buat QR Code
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Stats Modal -->
<div class="modal fade" id="quickStatsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-graph-up me-2"></i>Quick Statistics
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="quickStatsContent" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat statistik...</p>
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

.card-footer {
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 0.75rem 1rem;
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

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 20px;
    font-size: 0.688rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.active i {
    color: #10b981;
    font-size: 0.5rem;
}

.status-badge.inactive {
    background: #f3f4f6;
    color: #6b7280;
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

.btn-group {
    gap: 6px;
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

.btn-outline-warning {
    border-color: #e5e7eb;
    color: #f59e0b;
    background: white;
}

.btn-outline-warning:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.btn-outline-danger {
    border-color: #e5e7eb;
    color: #ef4444;
    background: white;
}

.btn-outline-danger:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.btn-outline-success {
    border-color: #e5e7eb;
    color: #10b981;
    background: white;
}

.btn-outline-success:hover {
    background: #10b981;
    border-color: #10b981;
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

.btn-info {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.btn-info:hover {
    background: #2563eb;
    border-color: #2563eb;
}

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.bg-primary { background: #4f46e5 !important; }
.bg-success { background: #10b981 !important; }
.bg-warning { background: #f59e0b !important; }
.bg-danger { background: #ef4444 !important; }
.bg-info { background: #3b82f6 !important; }
.bg-secondary { background: #6b7280 !important; }

/* Progress */
.progress {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 3px;
    transition: width 0.6s ease;
}

/* Form */
.form-label {
    font-weight: 500;
    font-size: 0.813rem;
    color: #374151;
    margin-bottom: 0.375rem;
}

.form-select,
.form-control {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    font-size: 0.813rem;
    transition: var(--transition);
}

.form-select:focus,
.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
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

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-success {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}

.alert-danger {
    background: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
}

/* Modal */
.modal-content {
    background: white;
    border: none;
    border-radius: var(--border-radius);
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 1.25rem;
}

.modal-body {
    padding: 1.25rem;
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

/* Border */
.border-top {
    border-top: 1px solid #e5e7eb !important;
}

/* Responsive */
@media (min-width: 992px) {
    .card-body {
        padding: 1.25rem;
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
    
    .table thead th,
    .table tbody td {
        padding: 0.625rem;
    }
    
    .btn-group {
        flex-wrap: wrap;
        justify-content: flex-end;
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

.card {
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
    
    // Dynamic student selection based on class for manual input
    const classSelect = document.getElementById('classSelect');
    const studentSelect = document.getElementById('studentSelect');
    
    if (classSelect && studentSelect) {
        classSelect.addEventListener('change', function() {
            const classId = this.value;
            studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';
            
            if (classId) {
                fetch(`/api/classes/${classId}/students`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(student => {
                            const option = document.createElement('option');
                            option.value = student.id;
                            option.textContent = student.name + ' (' + student.nis_nip + ')';
                            studentSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    }
});

function showQuickStats() {
    const modal = new bootstrap.Modal(document.getElementById('quickStatsModal'));
    modal.show();
    
    setTimeout(() => {
        document.getElementById('quickStatsContent').innerHTML = `
            <div class="text-center py-3">
                <i class="bi bi-graph-up fs-1 text-primary mb-3 d-block"></i>
                <h6 class="mb-2">Statistik Absensi</h6>
                <p class="text-muted small">Fitur quick stats akan segera tersedia</p>
                <div class="alert alert-info small mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    Statistik lengkap dapat dilihat di halaman dashboard
                </div>
            </div>
        `;
    }, 500);
}

function showAttendanceDetail(attendanceId) {
    window.location.href = `/attendance/${attendanceId}`;
}

function exportTableToExcel() {
    const table = document.getElementById('attendanceTable');
    if (!table) return;
    
    let csv = [];
    let headers = [];
    
    for (let i = 0; i < table.rows[0].cells.length - 1; i++) {
        headers.push(table.rows[0].cells[i].innerText);
    }
    csv.push(headers.join(','));
    
    for (let i = 1; i < table.rows.length; i++) {
        let row = [];
        for (let j = 0; j < table.rows[i].cells.length - 1; j++) {
            row.push(table.rows[i].cells[j].innerText.replace(/,/g, ';'));
        }
        csv.push(row.join(','));
    }
    
    const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "absensi_" + new Date().toISOString().split('T')[0] + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection