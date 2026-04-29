@extends('layouts.app')

@section('title', 'Detail QR Code')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Detail QR Code</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-hash me-1"></i>{{ $qrCode->code }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('qr-codes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <!-- Left Column - QR Code Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi QR Code
                    </h5>
                </div>
                <div class="card-body">
                    <!-- QR Code Image Section -->
                    <div class="text-center mb-4" id="qrCodeImageSection">
                        @php
                            $imageUrl = null;
                            $imageExists = false;
                            
                            if ($qrCode->qr_code_image) {
                                if (Storage::disk('public')->exists($qrCode->qr_code_image)) {
                                    $imageExists = true;
                                    $imageUrl = Storage::url($qrCode->qr_code_image);
                                } elseif (Storage::disk('public')->exists('qr-codes/' . $qrCode->code . '.png')) {
                                    $imageExists = true;
                                    $imageUrl = Storage::url('qr-codes/' . $qrCode->code . '.png');
                                } elseif (file_exists(public_path('storage/' . $qrCode->qr_code_image))) {
                                    $imageExists = true;
                                    $imageUrl = asset('storage/' . $qrCode->qr_code_image);
                                }
                            }
                            
                            if (!$imageExists && $qrCode->code) {
                                $possiblePaths = [
                                    'qr-codes/' . $qrCode->code . '.png',
                                    'qr-codes/quick-' . $qrCode->code . '.png',
                                    'qr-codes/bulk-' . $qrCode->code . '.png',
                                    'qr-codes/class-' . $qrCode->code . '.png',
                                ];
                                
                                foreach ($possiblePaths as $path) {
                                    if (Storage::disk('public')->exists($path)) {
                                        $imageExists = true;
                                        $imageUrl = Storage::url($path);
                                        break;
                                    }
                                }
                            }
                        @endphp
                        
                        @if($imageExists && $imageUrl)
                            <img src="{{ $imageUrl }}" 
                                 alt="QR Code {{ $qrCode->code }}" 
                                 class="img-fluid mb-3 border p-2 rounded shadow-sm"
                                 style="max-width: 200px;"
                                 id="qrCodeImage">
                            <p class="text-muted small">Scan QR code di atas untuk absensi</p>
                            <div class="btn-group" role="group">
                                <a href="{{ $imageUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> Lihat
                                </a>
                                <a href="{{ route('qr-codes.download', $qrCode) }}" class="btn btn-sm btn-outline-success" id="downloadQrBtn">
                                    <i class="bi bi-download me-1"></i> Download
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Gambar QR Code tidak ditemukan</strong>
                                <hr class="my-2">
                                <p class="mb-2 small">Anda masih dapat menggunakan kode manual:</p>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" value="{{ $qrCode->code }}" id="qrCodeCopy" readonly>
                                    <button class="btn btn-sm btn-primary" type="button" id="copyQrCodeBtn">
                                        <i class="bi bi-copy me-1"></i> Copy
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-primary w-100" id="regenerateBtn">
                                <i class="bi bi-arrow-repeat me-2"></i> Generate Ulang
                            </button>
                        @endif
                    </div>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-hash"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Kode</div>
                                <div class="info-value"><code>{{ $qrCode->code }}</code></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Kelas</div>
                                <div class="info-value">{{ $qrCode->class->class_name }} ({{ $qrCode->class->class_code }})</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Tanggal</div>
                                <div class="info-value">
                                    @if($qrCode->date instanceof \Carbon\Carbon)
                                        {{ $qrCode->date->format('d F Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($qrCode->date)->format('d F Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Waktu</div>
                                <div class="info-value">{{ $qrCode->formatted_start_time ?? $qrCode->start_time }} - {{ $qrCode->formatted_end_time ?? $qrCode->end_time }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-hourglass"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Durasi</div>
                                <div class="info-value">{{ $qrCode->duration_minutes }} menit</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-power"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="status-badge {{ $qrCode->status_color == 'success' ? 'active' : ($qrCode->status_color == 'danger' ? 'inactive' : 'secondary') }}" id="statusBadge">
                                        <i class="bi bi-circle-fill"></i>
                                        {{ $qrCode->status_text ?? ($qrCode->is_active ? 'Aktif' : 'Nonaktif') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location Information - Enhanced -->
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Lokasi Terbatas</div>
                                <div class="info-value">
                                    @if($qrCode->location_restricted && $qrCode->latitude && $qrCode->longitude)
                                        <span class="badge bg-warning mb-2">Ya</span>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-pin-map me-1"></i> Radius: {{ $qrCode->radius }} meter
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-globe me-1"></i> 
                                            Lat: {{ number_format($qrCode->latitude, 6) }}, 
                                            Lng: {{ number_format($qrCode->longitude, 6) }}
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="openMap({{ $qrCode->latitude }}, {{ $qrCode->longitude }})">
                                            <i class="bi bi-map me-1"></i> Lihat di Peta
                                        </button>
                                    @else
                                        <span class="badge bg-secondary">Tidak</span>
                                        <div class="small text-muted mt-1">Tidak ada batasan lokasi untuk QR Code ini</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Dibuat Oleh</div>
                                <div class="info-value">{{ $qrCode->creator->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-camera"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Total Scan</div>
                                <div class="info-value">{{ $qrCode->scan_count }} kali</div>
                            </div>
                        </div>
                        @if($qrCode->notes)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-chat"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Catatan</div>
                                <div class="info-value">{{ $qrCode->notes }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="d-flex justify-content-center gap-2 mt-4 pt-3 border-top">
                        @if($qrCode->canBeEdited())
                            <a href="{{ route('qr-codes.edit', $qrCode) }}" class="btn btn-outline-warning btn-sm"
                               data-bs-toggle="tooltip" title="Edit QR Code">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                        @endif
                        @if($qrCode->canBeDeleted())
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    data-bs-toggle="tooltip" title="Hapus QR Code"
                                    id="deleteQrBtn">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Statistics -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2 text-primary"></i>
                        Statistik Absensi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stat-mini text-center p-3 rounded bg-primary-light">
                                <div class="stat-mini-value text-primary fw-bold fs-2">{{ $totalStudents ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Total Siswa</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini text-center p-3 rounded bg-success-light">
                                <div class="stat-mini-value text-success fw-bold fs-2">{{ $attendanceStats['present'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Hadir</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini text-center p-3 rounded bg-warning-light">
                                <div class="stat-mini-value text-warning fw-bold fs-2">{{ $attendanceStats['late'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Terlambat</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-mini text-center p-3 rounded bg-danger-light">
                                <div class="stat-mini-value text-danger fw-bold fs-2">{{ $attendanceStats['absent'] ?? 0 }}</div>
                                <div class="stat-mini-label text-muted small">Tidak Hadir</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-muted">Kehadiran Keseluruhan</span>
                            <span class="small fw-semibold">{{ $attendancePercentage ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $attendancePercentage ?? 0 }}%"></div>
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Detail Absensi</h6>
                    @if(isset($attendances) && $attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3 ps-md-4">#</th>
                                        <th>SISWA</th>
                                        <th>NIS</th>
                                        <th>STATUS</th>
                                        <th>WAKTU</th>
                                        <th class="text-end pe-3 pe-md-4">LOKASI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $attendance)
                                    <tr>
                                        <td class="ps-3 ps-md-4">{{ $loop->iteration }}</td>
                                        <td>{{ $attendance->student->name ?? 'N/A' }}</td>
                                        <td>{{ $attendance->student->nis_nip ?? '-' }}</td>
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
                                                {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 pe-md-4">
                                            @if($attendance->latitude && $attendance->longitude)
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="openMap({{ $attendance->latitude }}, {{ $attendance->longitude }})"
                                                        title="Lihat Lokasi">
                                                    <i class="bi bi-geo-alt"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $attendances->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="empty-state text-center py-4">
                            <div class="empty-icon mx-auto mb-3">
                                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                            </div>
                            <h6 class="mb-1">Belum ada absensi</h6>
                            <p class="text-muted small mb-0">Belum ada siswa yang melakukan absensi untuk QR Code ini</p>
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

.status-badge.inactive i {
    color: #9ca3af;
    font-size: 0.5rem;
}

.status-badge.secondary {
    background: #f1f5f9;
    color: #475569;
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

.btn-outline-secondary {
    border-color: #e5e7eb;
    color: #6b7280;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #374151;
}

.btn-group {
    gap: 6px;
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

.alert-success {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}

.alert-info {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
}

.alert-warning {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
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

.empty-state h6 {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.empty-state p {
    font-size: 0.75rem;
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
    
    .info-icon {
        width: 28px;
        height: 28px;
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

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.loading-spinner {
    animation: spin 1s linear infinite;
}
</style>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// SweetAlert Modern
const CustomSwal = {
    showSuccess: (title, message, redirectUrl = null) => {
        Swal.fire({
            title: title,
            text: message,
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#4f46e5',
            timer: 2000,
            timerProgressBar: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-primary btn-sm px-4',
            },
            buttonsStyling: false
        }).then(() => {
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });
    },
    
    showError: (title, message) => {
        Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#ef4444',
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-danger btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    confirmDelete: (qrCodeCode) => {
        return Swal.fire({
            title: 'Hapus QR Code',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-trash3" style="font-size: 3.5rem; color: #ef4444;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">${qrCodeCode}</h6>
                    <p class="mb-3">Apakah Anda yakin ingin menghapus QR Code ini?</p>
                    <div class="alert alert-danger small mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Tindakan ini tidak dapat dibatalkan. Semua data absensi terkait akan dihapus.
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Ya, Hapus!',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Batal',
            reverseButtons: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-danger btn-sm px-4',
                cancelButton: 'btn btn-secondary btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    confirmRegenerate: (qrCodeCode) => {
        return Swal.fire({
            title: 'Generate Ulang QR Code',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-arrow-repeat" style="font-size: 3.5rem; color: #4f46e5;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">${qrCodeCode}</h6>
                    <p class="mb-3">Apakah Anda yakin ingin membuat ulang QR Code ini?</p>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        QR Code baru akan dibuat dengan data yang sama.
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Ya, Generate!',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Batal',
            reverseButtons: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-primary btn-sm px-4',
                cancelButton: 'btn btn-secondary btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    showLoading: (title = 'Memproses...', text = 'Mohon tunggu sebentar') => {
        return Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: {
                popup: 'custom-swal-popup'
            }
        });
    },
    
    closeLoading: () => {
        Swal.close();
    },
    
    showSuccessToast: (message) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: 'success',
            title: message,
            background: '#ffffff',
            iconColor: '#10b981'
        });
    },
    
    showInfoToast: (message) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: 'info',
            title: message,
            background: '#ffffff',
            iconColor: '#3b82f6'
        });
    }
};

// Fungsi untuk membuka peta
function openMap(latitude, longitude) {
    window.open(`https://www.google.com/maps?q=${latitude},${longitude}`, '_blank');
}

// Custom CSS untuk SweetAlert
const swalStyles = document.createElement('style');
swalStyles.textContent = `
    .custom-swal-popup {
        border-radius: 16px !important;
        padding: 0 !important;
        width: 420px !important;
        font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    .custom-swal-popup .swal2-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #1f2937 !important;
        padding: 1.25rem 1.25rem 0 !important;
        margin-bottom: 0 !important;
    }
    
    .custom-swal-popup .swal2-html-container {
        padding: 0 1.25rem 1.25rem !important;
        margin-top: 0 !important;
    }
    
    .custom-swal-popup .swal2-actions {
        padding: 0 1.25rem 1.25rem !important;
        gap: 0.75rem !important;
        margin-top: 0 !important;
    }
    
    .custom-swal-popup .swal2-loader {
        border-color: #4f46e5 !important;
        border-right-color: transparent !important;
    }
    
    .custom-swal-popup .swal2-timer-progress-bar {
        background: linear-gradient(90deg, #4f46e5, #818cf8) !important;
    }
    
    .swal-icon-wrapper {
        margin-top: 0.5rem;
    }
    
    .custom-swal-popup .alert {
        border-radius: 10px;
        font-size: 0.75rem;
        text-align: left;
    }
    
    .custom-swal-popup .alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    
    .custom-swal-popup .alert-info {
        background: #cffafe;
        border: 1px solid #bae6fd;
        color: #155e75;
    }
    
    .swal2-toast {
        border-radius: 12px !important;
    }
`;

document.head.appendChild(swalStyles);

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Tampilkan success dari session
    @if(session('success'))
        CustomSwal.showSuccess('Berhasil!', '{{ session('success') }}');
    @endif
    
    @if(session('error'))
        CustomSwal.showError('Gagal!', '{{ session('error') }}');
    @endif
    
    // DELETE QR CODE WITH AJAX
    const deleteBtn = document.getElementById('deleteQrBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            const qrCodeId = '{{ $qrCode->id }}';
            const qrCodeCode = '{{ $qrCode->code }}';
            
            const result = await CustomSwal.confirmDelete(qrCodeCode);
            
            if (result.isConfirmed) {
                CustomSwal.showLoading('Menghapus QR Code...', 'Mohon tunggu sebentar');
                
                try {
                    const response = await fetch(`/qr-codes/${qrCodeId}/delete`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    CustomSwal.closeLoading();
                    
                    if (data.success) {
                        CustomSwal.showSuccess('Berhasil!', data.message || 'QR Code berhasil dihapus.', '{{ route('qr-codes.index') }}');
                    } else {
                        CustomSwal.showError('Gagal!', data.message || 'Terjadi kesalahan saat menghapus QR Code.');
                    }
                } catch (error) {
                    CustomSwal.closeLoading();
                    console.error('Error:', error);
                    CustomSwal.showError('Error!', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                }
            }
        });
    }
    
    // REGENERATE QR CODE WITH AJAX
    const regenerateBtn = document.getElementById('regenerateBtn');
    if (regenerateBtn) {
        regenerateBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            const qrCodeId = '{{ $qrCode->id }}';
            const qrCodeCode = '{{ $qrCode->code }}';
            
            const result = await CustomSwal.confirmRegenerate(qrCodeCode);
            
            if (result.isConfirmed) {
                CustomSwal.showLoading('Membuat Ulang QR Code...', 'Mohon tunggu sebentar');
                
                try {
                    const response = await fetch(`/qr-codes/${qrCodeId}/regenerate-image`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    CustomSwal.closeLoading();
                    
                    if (data.success) {
                        if (data.qr_code_image) {
                            const qrImage = document.getElementById('qrCodeImage');
                            if (qrImage) {
                                qrImage.src = data.qr_code_image + '?t=' + new Date().getTime();
                            } else {
                                window.location.reload();
                            }
                        }
                        CustomSwal.showSuccess('Berhasil!', data.message || 'QR Code berhasil dibuat ulang.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        CustomSwal.showError('Gagal!', data.message || 'Terjadi kesalahan saat membuat ulang QR Code.');
                    }
                } catch (error) {
                    CustomSwal.closeLoading();
                    console.error('Error:', error);
                    CustomSwal.showError('Error!', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                }
            }
        });
    }
    
    // Copy QR Code button
    const copyBtn = document.getElementById('copyQrCodeBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const copyText = document.getElementById('qrCodeCopy');
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand('copy');
            CustomSwal.showSuccessToast('Kode QR berhasil disalin!');
        });
    }
    
    // Download QR Code notification
    const downloadBtn = document.getElementById('downloadQrBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            CustomSwal.showInfoToast('Mengunduh QR Code...');
        });
    }
});
</script>
@endsection