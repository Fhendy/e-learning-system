@extends('layouts.app')

@section('title', 'Detail Absensi')

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
                        <h1 class="page-title mb-1">Detail Absensi</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-building me-1"></i>{{ $attendance->class->class_name ?? 'N/A' }}
                            <span class="mx-2">•</span>
                            <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($attendance->attendance_date)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('attendance.student.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    @php
        $statusColors = [
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            'sick' => 'info',
            'permission' => 'primary'
        ];
        $statusTexts = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin'
        ];
        $statusColor = $statusColors[$attendance->status] ?? 'secondary';
        $statusText = $statusTexts[$attendance->status] ?? $attendance->status;
    @endphp

    <div class="row g-3 g-md-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi Absensi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Kelas</div>
                                <div class="info-value">{{ $attendance->class->class_name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Tanggal</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($attendance->attendance_date)->translatedFormat('d F Y') }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-tag"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="badge bg-{{ $statusColor }} px-3 py-2">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Waktu Absensi</div>
                                <div class="info-value">
                                    @if($attendance->checked_in_at)
                                        {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i:s') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($attendance->qrCode)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">QR Code</div>
                                <div class="info-value">
                                    <span class="badge bg-info">{{ $attendance->qrCode->code }}</span>
                                    <div class="text-muted small mt-1">
                                        {{ $attendance->qrCode->formatted_time_range ?? $attendance->qrCode->start_time . ' - ' . $attendance->qrCode->end_time }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($attendance->notes)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-chat"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Catatan</div>
                                <div class="info-value">{{ $attendance->notes }}</div>
                            </div>
                        </div>
                        @endif
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Dibuat</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($attendance->created_at)->format('d/m/Y H:i') }}
                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($attendance->created_at)->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                        @if($attendance->latitude && $attendance->longitude)
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="info-label">Lokasi</div>
                                <div class="info-value">
                                    <div>Lat: {{ number_format($attendance->latitude, 6) }}</div>
                                    <div>Long: {{ number_format($attendance->longitude, 6) }}</div>
                                    @if($attendance->accuracy)
                                    <div class="text-muted small">Akurasi: ±{{ $attendance->accuracy }}m</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <a href="{{ route('attendance.student.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Status Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="status-list">
                        <div class="status-item">
                            <span class="badge bg-success px-3 py-2">Hadir</span>
                            <small class="text-muted ms-2">Hadir tepat waktu</small>
                        </div>
                        <div class="status-item">
                            <span class="badge bg-warning px-3 py-2">Terlambat</span>
                            <small class="text-muted ms-2">Terlambat</small>
                        </div>
                        <div class="status-item">
                            <span class="badge bg-danger px-3 py-2">Tidak Hadir</span>
                            <small class="text-muted ms-2">Tidak hadir</small>
                        </div>
                        <div class="status-item">
                            <span class="badge bg-info px-3 py-2">Sakit</span>
                            <small class="text-muted ms-2">Sakit</small>
                        </div>
                        <div class="status-item">
                            <span class="badge bg-primary px-3 py-2">Izin</span>
                            <small class="text-muted ms-2">Izin</small>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($attendance->status === 'late')
            <div class="alert alert-warning mb-4">
                <div class="d-flex gap-2">
                    <i class="bi bi-clock-history fs-5"></i>
                    <div>
                        <h6 class="mb-1">Informasi Keterlambatan</h6>
                        <p class="mb-0 small">Anda tercatat terlambat pada absensi ini. Pastikan untuk datang tepat waktu di kesempatan berikutnya.</p>
                    </div>
                </div>
            </div>
            @endif
            
            @if(in_array($attendance->status, ['absent', 'sick', 'permission']))
            <div class="alert alert-info mb-4">
                <div class="d-flex gap-2">
                    <i class="bi bi-sticky-note fs-5"></i>
                    <div>
                        <h6 class="mb-1">Catatan</h6>
                        <p class="mb-0 small">Status {{ $statusText }} telah tercatat. Untuk keterangan lebih lanjut, hubungi guru kelas.</p>
                    </div>
                </div>
            </div>
            @endif
            
            @if($attendance->qrCode && $attendance->qrCode->location_restricted)
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-geo-alt me-2 text-primary"></i>
                        Informasi Lokasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Absensi ini memiliki batasan lokasi.
                        Anda harus berada dalam radius 
                        <strong>{{ $attendance->qrCode->radius ?? 0 }} meter</strong> 
                        dari lokasi yang ditentukan.
                    </div>
                    @if($attendance->latitude && $attendance->longitude && $attendance->qrCode->latitude && $attendance->qrCode->longitude)
                    @php
                        function calculateDistance($lat1, $lon1, $lat2, $lon2) {
                            $earthRadius = 6371000;
                            $latFrom = deg2rad($lat1);
                            $lonFrom = deg2rad($lon1);
                            $latTo = deg2rad($lat2);
                            $lonTo = deg2rad($lon2);
                            
                            $latDelta = $latTo - $latFrom;
                            $lonDelta = $lonTo - $lonFrom;
                            
                            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + 
                                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                            
                            return $angle * $earthRadius;
                        }
                        
                        $distance = calculateDistance(
                            $attendance->latitude,
                            $attendance->longitude,
                            $attendance->qrCode->latitude,
                            $attendance->qrCode->longitude
                        );
                    @endphp
                    <div class="mt-2">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Jarak Anda dari lokasi:</small>
                            <small class="fw-semibold">{{ round($distance) }} meter</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            @php
                                $radius = $attendance->qrCode->radius ?? 100;
                                $percentage = min(100, ($distance / $radius) * 100);
                                $progressClass = $distance <= $radius ? 'success' : 'danger';
                            @endphp
                            <div class="progress-bar bg-{{ $progressClass }}" style="width: {{ $percentage }}%"></div>
                        </div>
                        @if($distance <= $radius)
                        <div class="text-success small mt-1">
                            <i class="bi bi-check-circle me-1"></i>Dalam radius yang diizinkan
                        </div>
                        @else
                        <div class="text-danger small mt-1">
                            <i class="bi bi-x-circle me-1"></i>Di luar radius yang diizinkan
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- QR Code Image if available -->
            @if($attendance->qrCode && $attendance->qrCode->qr_code_image)
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-qr-code me-2 text-primary"></i>
                        QR Code
                    </h5>
                </div>
                <div class="card-body text-center">
                    @php
                        $qrImageUrl = null;
                        if (Storage::disk('public')->exists($attendance->qrCode->qr_code_image)) {
                            $qrImageUrl = Storage::url($attendance->qrCode->qr_code_image);
                        } elseif (Storage::disk('public')->exists('qr-codes/' . $attendance->qrCode->code . '.png')) {
                            $qrImageUrl = Storage::url('qr-codes/' . $attendance->qrCode->code . '.png');
                        }
                    @endphp
                    @if($qrImageUrl)
                    <img src="{{ $qrImageUrl }}" 
                         alt="QR Code" 
                         class="img-fluid border p-2 rounded shadow-sm"
                         style="max-width: 180px;">
                    @endif
                    <p class="mt-2 mb-0">
                        <small class="text-muted">Kode: <code>{{ $attendance->qrCode->code }}</code></small>
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
/* CSS Variables */
:root {
    --primary: #4f46e5;
    --primary-light: #e0e7ff;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --secondary: #6b7280;
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
    padding: 0.875rem 1rem;
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

/* Status List */
.status-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.status-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.status-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

/* Badge */
.badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
}

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-warning {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

.alert-info {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
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

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
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

/* Colors */
.bg-primary { background: #4f46e5 !important; }
.bg-success { background: #10b981 !important; }
.bg-warning { background: #f59e0b !important; }
.bg-danger { background: #ef4444 !important; }
.bg-info { background: #3b82f6 !important; }

.text-primary { color: #4f46e5 !important; }
.text-muted { color: #6b7280 !important; }

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection