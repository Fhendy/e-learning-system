{{-- resources/views/attendance/student-scan.blade.php --}}
@extends('layouts.app')

@section('title', 'Scan QR Code Absensi')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Scan QR Code Absensi</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Scan QR Code untuk melakukan absensi
                        </p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <!-- Alert Info -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle-fill fs-5"></i>
                            <div>Arahkan kamera ke QR Code yang ditampilkan guru untuk melakukan absensi.</div>
                        </div>
                    </div>
                    
                    @if($todayAttendance)
                    <div class="alert alert-success mb-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <div>
                                Anda sudah melakukan absensi hari ini pada 
                                {{ $todayAttendance->checked_in_at ? \Carbon\Carbon::parse($todayAttendance->checked_in_at)->format('H:i') : '-' }}
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- QR Code Scanner Container -->
                    <div class="qr-scanner-container text-center mb-4">
                        <div id="qr-reader" style="width: 100%; min-height: 300px;"></div>
                        
                        <div class="mt-3">
                            <button id="start-scanner" class="btn btn-primary">
                                <i class="bi bi-camera me-2"></i>Mulai Scan
                            </button>
                            <button id="stop-scanner" class="btn btn-outline-secondary" style="display: none;">
                                <i class="bi bi-stop-circle me-2"></i>Stop Scan
                            </button>
                        </div>
                    </div>

                    @if($specificQrCode && !$todayAttendance)
                    <div class="alert alert-success mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                @php
                                    $qrImageUrl = null;
                                    $imagePath = $specificQrCode->qr_code_image;
                                    
                                    if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                                        $qrImageUrl = Storage::url($imagePath);
                                    } elseif ($specificQrCode->code) {
                                        $qrImageUrl = asset('storage/qr-codes/' . $specificQrCode->code . '.png');
                                    }
                                @endphp
                                
                                @if($qrImageUrl)
                                    <img src="{{ $qrImageUrl }}" 
                                         alt="QR Code" 
                                         class="img-fluid border p-2 rounded shadow-sm"
                                         style="max-width: 150px;">
                                @else
                                    <div class="alert alert-info p-3">
                                        <i class="bi bi-qr-code fs-1 mb-2 d-block"></i>
                                        <p class="mb-0"><strong>Kode QR:</strong><br>{{ $specificQrCode->code }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h5><i class="bi bi-qr-code me-2"></i>QR Code Ditemukan!</h5>
                                <p class="mb-1">Kelas: <strong>{{ $specificQrCode->class->class_name ?? 'N/A' }}</strong></p>
                                <p class="mb-1">Waktu: <strong>{{ $specificQrCode->formatted_start_time ?? $specificQrCode->start_time ?? 'N/A' }} - {{ $specificQrCode->formatted_end_time ?? $specificQrCode->end_time ?? 'N/A' }}</strong></p>
                                <p class="mb-0">Kode: <code>{{ $specificQrCode->code ?? 'N/A' }}</code></p>
                                <div class="mt-3">
                                    <button class="btn btn-success" onclick="processSpecificQrCode('{{ $specificQrCode->code ?? '' }}')">
                                        <i class="bi bi-check-circle me-2"></i>Absen Sekarang
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                                        <i class="bi bi-arrow-repeat me-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Manual Input -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body bg-light rounded">
                            <h6 class="mb-2">
                                <i class="bi bi-keyboard me-2 text-primary"></i>Masukkan Kode Manual
                            </h6>
                            <p class="text-muted small mb-3">
                                Jika QR Code tidak dapat discan, masukkan kode secara manual
                            </p>
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <input type="text" 
                                           id="manual_qr_code_input"
                                           class="form-control" 
                                           placeholder="Masukkan kode QR (contoh: ABC12345)"
                                           autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" id="submit-manual-btn" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle me-2"></i>Submit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="bi bi-question-circle me-2 text-primary"></i>Petunjuk Penggunaan
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Izinkan akses kamera ketika browser meminta</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Arahkan kamera ke QR Code dengan jarak yang cukup</li>
                                <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Pastikan pencahayaan cukup dan QR Code tidak blur</li>
                                <li><i class="bi bi-check-circle text-success me-2"></i>Jika scan gagal, gunakan input manual</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include QR Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js"></script>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let html5QrCode = null;
const studentId = {{ Auth::id() }};
const csrfToken = '{{ csrf_token() }}';
const apiUrl = '{{ route("attendance.scan.process") }}';

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
    
    showWarning: (title, message) => {
        Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#f59e0b',
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-warning btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    showInfo: (title, message) => {
        Swal.fire({
            title: title,
            text: message,
            icon: 'info',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#3b82f6',
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-info btn-sm px-4',
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
    
    confirmLocation: () => {
        return Swal.fire({
            title: 'Verifikasi Lokasi',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-geo-alt" style="font-size: 3.5rem; color: #4f46e5;"></i>
                    </div>
                    <p class="mb-3">QR Code ini memerlukan verifikasi lokasi.</p>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Sistem akan meminta akses lokasi Anda untuk memastikan Anda berada di area yang benar.
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Lanjutkan',
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
    
    showLocationError: (message) => {
        return Swal.fire({
            title: 'Gagal Mendapatkan Lokasi',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-geo-alt-fill" style="font-size: 3.5rem; color: #ef4444;"></i>
                    </div>
                    <p class="mb-3">${message}</p>
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Pastikan Anda mengizinkan akses lokasi di browser Anda.
                    </div>
                </div>
            `,
            icon: undefined,
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#ef4444',
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-danger btn-sm px-4',
            },
            buttonsStyling: false
        });
    }
};

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
    
    .btn-info {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    .btn-info:hover {
        background: #2563eb;
        border-color: #2563eb;
    }
    
    .btn-warning {
        background: #f59e0b;
        border-color: #f59e0b;
        color: white;
    }
    
    .btn-warning:hover {
        background: #d97706;
        border-color: #d97706;
    }
`;

document.head.appendChild(swalStyles);

// Fungsi untuk mengekstrak kode QR dari URL
function extractQrCodeFromUrl(input) {
    console.log('Raw input:', input);
    
    if (!input) return null;
    
    if (input.includes('qr_code=')) {
        try {
            let url;
            if (input.startsWith('http')) {
                url = new URL(input);
            } else {
                url = new URL('http://' + input);
            }
            const qrCode = url.searchParams.get('qr_code');
            if (qrCode) {
                console.log('Extracted from URL:', qrCode);
                return qrCode;
            }
        } catch (e) {
            console.log('URL parsing failed, trying regex');
        }
        
        const match = input.match(/qr_code[=:]([A-Z0-9]+)/i);
        if (match && match[1]) {
            console.log('Extracted via regex:', match[1]);
            return match[1];
        }
    }
    
    const trimmed = input.trim();
    if (/^[A-Z0-9]{6,10}$/i.test(trimmed)) {
        console.log('Direct code:', trimmed);
        return trimmed.toUpperCase();
    }
    
    if (/^\d+$/.test(trimmed)) {
        console.log('Numeric ID:', trimmed);
        return trimmed;
    }
    
    console.log('Cannot extract, returning original:', input);
    return input;
}

function onScanSuccess(decodedText, decodedResult) {
    console.log('Scan result:', decodedText);
    
    if (html5QrCode) {
        html5QrCode.stop();
        const startBtn = document.getElementById('start-scanner');
        const stopBtn = document.getElementById('stop-scanner');
        if (startBtn) startBtn.style.display = 'inline-block';
        if (stopBtn) stopBtn.style.display = 'none';
    }
    
    const qrCode = extractQrCodeFromUrl(decodedText);
    
    if (!qrCode) {
        CustomSwal.showError('Gagal!', 'Gagal membaca QR Code. Silakan coba lagi.');
        return;
    }
    
    CustomSwal.showSuccess('Berhasil!', 'QR Code berhasil dibaca! Kode: ' + qrCode);
    processQrCode(qrCode);
}

function onScanFailure(error) {
    console.log('Scan error (ignored):', error);
}

function processSpecificQrCode(code) {
    if (!code) {
        CustomSwal.showError('Gagal!', 'Kode QR tidak valid.');
        return;
    }
    processQrCode(code);
}

async function processQrCode(code) {
    if (!code) {
        CustomSwal.showError('Gagal!', 'Kode QR tidak valid.');
        return;
    }
    
    // Tanyakan apakah perlu verifikasi lokasi
    const needLocation = await CustomSwal.confirmLocation();
    
    if (!needLocation.isConfirmed) {
        CustomSwal.showInfo('Dibatalkan', 'Proses absensi dibatalkan.');
        return;
    }
    
    CustomSwal.showLoading('Mendapatkan Lokasi...', 'Mohon izinkan akses lokasi');
    
    // Dapatkan lokasi
    navigator.geolocation.getCurrentPosition(
        async function(position) {
            CustomSwal.closeLoading();
            
            const data = {
                qr_code: code,
                student_id: studentId,
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            };
            
            CustomSwal.showLoading('Memproses Absensi...', 'Mohon tunggu sebentar');
            
            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                CustomSwal.closeLoading();
                
                if (result.success) {
                    CustomSwal.showSuccess('Berhasil!', result.message);
                    setTimeout(() => {
                        window.location.href = '{{ url("/attendance/scan-result") }}/' + result.data.attendance_id;
                    }, 2000);
                } else {
                    CustomSwal.showError('Gagal!', result.message);
                }
            } catch (error) {
                CustomSwal.closeLoading();
                console.error('Error:', error);
                CustomSwal.showError('Error!', 'Terjadi kesalahan: ' + error.message);
            }
        },
        async function(error) {
            CustomSwal.closeLoading();
            
            let errorMsg = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Izin lokasi ditolak. Silakan izinkan akses lokasi di browser Anda.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Informasi lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    errorMsg = 'Waktu permintaan lokasi habis. Silakan coba lagi.';
                    break;
                default:
                    errorMsg = error.message;
            }
            
            await CustomSwal.showLocationError(errorMsg);
            
            // Tawarkan input manual
            const result = await Swal.fire({
                title: 'Input Manual',
                text: 'Apakah Anda ingin memasukkan kode secara manual?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Manual',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#4f46e5'
            });
            
            if (result.isConfirmed) {
                document.getElementById('manual_qr_code_input').focus();
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

function startScanner() {
    const startScannerBtn = document.getElementById('start-scanner');
    const stopScannerBtn = document.getElementById('stop-scanner');
    
    if (html5QrCode) {
        html5QrCode.stop();
    }
    
    html5QrCode = new Html5Qrcode("qr-reader");
    
    const config = { 
        fps: 10, 
        qrbox: { width: 250, height: 250 } 
    };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanFailure
    ).then(() => {
        if (startScannerBtn) startScannerBtn.style.display = 'none';
        if (stopScannerBtn) stopScannerBtn.style.display = 'inline-block';
        CustomSwal.showInfo('Scanner Aktif', 'Arahkan kamera ke QR Code.');
    }).catch(err => {
        console.error('Failed to start scanner:', err);
        let errorMsg = 'Gagal memulai scanner';
        if (err.message && err.message.includes('NotAllowedError')) {
            errorMsg = 'Izin kamera ditolak. Silakan izinkan akses kamera.';
        }
        CustomSwal.showError('Gagal!', errorMsg);
        if (startScannerBtn) startScannerBtn.style.display = 'inline-block';
        if (stopScannerBtn) stopScannerBtn.style.display = 'none';
    });
}

function stopScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            const startBtn = document.getElementById('start-scanner');
            const stopBtn = document.getElementById('stop-scanner');
            if (startBtn) startBtn.style.display = 'inline-block';
            if (stopBtn) stopBtn.style.display = 'none';
            CustomSwal.showInfo('Scanner Berhenti', 'Scanner kamera telah dihentikan.');
        }).catch(err => {
            console.error('Failed to stop scanner:', err);
        });
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const startScannerBtn = document.getElementById('start-scanner');
    const stopScannerBtn = document.getElementById('stop-scanner');
    const submitManualBtn = document.getElementById('submit-manual-btn');
    const manualInput = document.getElementById('manual_qr_code_input');
    
    if (startScannerBtn) {
        startScannerBtn.addEventListener('click', startScanner);
    }
    
    if (stopScannerBtn) {
        stopScannerBtn.addEventListener('click', stopScanner);
    }
    
    if (submitManualBtn && manualInput) {
        submitManualBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const qrCode = manualInput.value.trim();
            if (!qrCode) {
                CustomSwal.showWarning('Peringatan', 'Masukkan kode QR terlebih dahulu.');
                manualInput.focus();
                return;
            }
            const extractedCode = extractQrCodeFromUrl(qrCode);
            if (extractedCode) {
                processQrCode(extractedCode);
            } else {
                CustomSwal.showError('Gagal!', 'Kode QR tidak valid.');
            }
            manualInput.value = '';
        });
        
        manualInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitManualBtn.click();
            }
        });
    }
});

// Cek dukungan kamera
if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    const startScannerBtn = document.getElementById('start-scanner');
    if (startScannerBtn) {
        startScannerBtn.disabled = true;
        startScannerBtn.innerHTML = '<i class="bi bi-camera-video-off me-2"></i>Kamera Tidak Didukung';
    }
    CustomSwal.showWarning('Peringatan', 'Browser Anda tidak mendukung akses kamera. Silakan gunakan input manual.');
}

// Auto start jika ada specific QR code
@if(isset($specificQrCode) && $specificQrCode && !$todayAttendance)
    setTimeout(() => {
        const code = '{{ $specificQrCode->code }}';
        if (code) {
            processSpecificQrCode(code);
        }
    }, 1000);
@endif

// Jika ada qr_code di URL parameter
@if(isset($qrCodeParam) && $qrCodeParam)
    setTimeout(() => {
        const extractedCode = extractQrCodeFromUrl('{{ $qrCodeParam }}');
        if (extractedCode) {
            processSpecificQrCode(extractedCode);
        }
    }, 1000);
@endif
</script>

<style>
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

.qr-scanner-container {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 1rem;
    padding: 1.25rem;
}

#qr-reader {
    min-height: 280px;
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
}

#qr-reader video {
    width: 100%;
    border-radius: 0.75rem;
}

.alert {
    border-radius: 0.75rem;
}

@media (max-width: 768px) {
    .qr-scanner-container {
        padding: 1rem;
    }
    #qr-reader {
        min-height: 250px;
    }
}
</style>
@endsection