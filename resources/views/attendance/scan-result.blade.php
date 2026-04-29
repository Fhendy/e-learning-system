{{-- resources/views/attendance/scan-result.blade.php --}}
@extends('layouts.app')

@section('title', 'Hasil Absensi')

@section('content')
<div class="container py-3 py-md-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            <!-- Success Card -->
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <!-- Header Gradient -->
                <div class="card-header bg-gradient-success text-white text-center py-4 py-md-5 border-0">
                    <div class="success-animation mb-3">
                        <div class="checkmark-circle">
                            <i class="bi bi-check-lg checkmark"></i>
                        </div>
                    </div>
                    <h2 class="mb-1 fw-bold fs-3 fs-md-2">Absensi Berhasil!</h2>
                    <p class="mb-0 opacity-75 small">Terima kasih, absensi Anda telah tercatat</p>
                </div>
                
                <!-- Body -->
                <div class="card-body p-3 p-md-4 p-lg-5">
                    <!-- Waktu Absen -->
                    <div class="attendance-time text-center mb-4">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                            <i class="bi bi-clock me-1"></i>
                            {{ $attendance->checked_in_at ? \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i:s') : '-' }}
                        </span>
                        <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill ms-2">
                            <i class="bi bi-calendar me-1"></i>
                            {{ $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d F Y') : '-' }}
                        </span>
                    </div>
                    
                    <!-- Status Card -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-3 p-md-4">
                            <div class="row align-items-center">
                                <div class="col-4 col-md-3 text-center">
                                    <div class="status-icon mx-auto {{ $attendance->status == 'present' ? 'bg-success' : ($attendance->status == 'late' ? 'bg-warning' : 'bg-info') }} rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi {{ $attendance->status == 'present' ? 'bi-emoji-smile' : ($attendance->status == 'late' ? 'bi-emoji-frown' : 'bi-thermometer-half') }} text-white fs-1"></i>
                                    </div>
                                </div>
                                <div class="col-8 col-md-9">
                                    <p class="text-muted small mb-1">Status Kehadiran</p>
                                    <h4 class="mb-0 fw-bold {{ $attendance->status == 'present' ? 'text-success' : ($attendance->status == 'late' ? 'text-warning' : 'text-info') }}">
                                        {{ $attendance->status_text ?? ($attendance->status == 'present' ? 'Hadir' : ($attendance->status == 'late' ? 'Terlambat' : ($attendance->status == 'sick' ? 'Sakit' : 'Izin'))) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="info-card h-100 p-3 rounded-3 border">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="info-icon bg-primary-subtle text-primary rounded-circle">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-muted small mb-1">Kelas</p>
                                        <h6 class="mb-0 fw-semibold">{{ $attendance->class->class_name ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $attendance->class->class_code ?? '' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="info-card h-100 p-3 rounded-3 border">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="info-icon bg-info-subtle text-info rounded-circle">
                                        <i class="bi bi-person-badge"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="text-muted small mb-1">Guru</p>
                                        <h6 class="mb-0 fw-semibold">{{ $attendance->class->teacher->name ?? 'N/A' }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- QR Code Info (if available) -->
                    @if($attendance->qrCode)
                    <div class="qr-info-card p-3 rounded-3 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="qr-icon bg-white rounded-circle p-2">
                                <i class="bi bi-qr-code-scan text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 text-white">
                                <p class="small mb-1 opacity-75">Kode QR Absensi</p>
                                <code class="text-white bg-white bg-opacity-25 px-2 py-1 rounded small">{{ $attendance->qrCode->code }}</code>
                            </div>
                            <button class="btn btn-sm btn-light rounded-pill" onclick="copyToClipboard('{{ $attendance->qrCode->code }}')">
                                <i class="bi bi-copy"></i>
                            </button>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('attendance.student.index') }}" class="btn btn-primary btn-lg py-3 rounded-3">
                            <i class="bi bi-calendar-check me-2"></i>Lihat Riwayat Absensi
                        </a>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <a href="{{ route('attendance.scan.page') }}" class="btn btn-outline-success w-100 py-2 rounded-3">
                                    <i class="bi bi-qr-code-scan me-2"></i>Scan Lagi
                                </a>
                            </div>
                            <div class="col-12 col-sm-6">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100 py-2 rounded-3">
                                    <i class="bi bi-house-door me-2"></i>Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="card-footer bg-white border-0 text-center py-3">
                    <small class="text-muted">
                        <i class="bi bi-clock-history me-1"></i>
                        Dicatat pada {{ now()->format('d/m/Y H:i:s') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Variables */
:root {
    --success: #10b981;
    --success-dark: #059669;
    --warning: #f59e0b;
    --info: #3b82f6;
    --border-radius: 16px;
}

/* Gradient Background */
.bg-gradient-success {
    background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
}

/* Success Animation */
.success-animation {
    display: flex;
    justify-content: center;
    align-items: center;
}

.checkmark-circle {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: scaleIn 0.5s ease-out;
}

.checkmark {
    font-size: 2.5rem;
    color: white;
    animation: checkmarkDraw 0.5s ease-out 0.2s both;
}

@keyframes scaleIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes checkmarkDraw {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Status Icon */
.status-icon {
    width: 65px;
    height: 65px;
}

.status-icon i {
    font-size: 1.8rem;
}

/* Info Card */
.info-card {
    background: #f8fafc;
    transition: all 0.3s ease;
    border-color: #e2e8f0 !important;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.info-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.bg-primary-subtle {
    background: #e0e7ff;
}

.bg-info-subtle {
    background: #dbeafe;
}

.bg-success-subtle {
    background: #d1fae5;
}

.bg-warning-subtle {
    background: #fef3c7;
}

/* QR Info Card */
.qr-info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.qr-icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-icon i {
    font-size: 1.3rem;
}

/* Buttons */
.btn-lg {
    font-size: 1rem;
    font-weight: 600;
}

.btn-primary {
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #4338ca, #312e81);
    transform: translateY(-1px);
}

.btn-outline-success {
    border: 1px solid #10b981;
    color: #10b981;
}

.btn-outline-success:hover {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-outline-secondary {
    border: 1px solid #e2e8f0;
    color: #64748b;
}

.btn-outline-secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #475569;
}

/* Responsive */
@media (max-width: 576px) {
    .card-header {
        padding: 1.5rem 1rem !important;
    }
    
    .card-body {
        padding: 1rem !important;
    }
    
    .checkmark-circle {
        width: 55px;
        height: 55px;
    }
    
    .checkmark {
        font-size: 2rem;
    }
    
    .status-icon {
        width: 50px;
        height: 50px;
    }
    
    .status-icon i {
        font-size: 1.3rem;
    }
    
    .info-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    h2 {
        font-size: 1.5rem !important;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
    }
}

@media (min-width: 768px) {
    .status-icon {
        width: 75px;
        height: 75px;
    }
    
    .status-icon i {
        font-size: 2rem;
    }
    
    .checkmark-circle {
        width: 80px;
        height: 80px;
    }
    
    .checkmark {
        font-size: 3rem;
    }
}

/* Animation for cards */
.card {
    animation: slideUp 0.4s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover effects */
.btn {
    transition: all 0.2s ease;
}

.btn:active {
    transform: translateY(1px);
}
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success toast
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 m-3 bg-success text-white px-4 py-2 rounded-3 shadow';
        toast.style.zIndex = '9999';
        toast.innerHTML = '<i class="bi bi-check-circle me-2"></i>Kode berhasil disalin';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        alert('Gagal menyalin kode');
    });
}
</script>
@endsection