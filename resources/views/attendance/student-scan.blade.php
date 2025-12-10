@extends('layouts.app')

@section('title', 'Scan QR Code Absensi')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-qr-code me-2"></i>Scan QR Code Absensi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Arahkan kamera ke QR Code yang ditampilkan guru untuk melakukan absensi.
                    </div>
                    
                    <!-- QR Code Scanner Container -->
                    <div class="qr-scanner-container text-center mb-4">
                        <div id="qr-reader" style="width: 100%; min-height: 300px;"></div>
                        
                        <div class="mt-3">
                            <button id="start-scanner" class="btn btn-primary">
                                <i class="fas fa-camera me-2"></i>Mulai Scan
                            </button>
                            <button id="stop-scanner" class="btn btn-secondary" style="display: none;">
                                <i class="fas fa-stop-circle me-2"></i>Stop Scan
                            </button>
                        </div>
                    </div>
                    {{-- resources/views/attendance/student-scan.blade.php --}}

@if($specificQrCode)
<div class="alert alert-success">
    <h5><i class="fas fa-qrcode me-2"></i>QR Code Ditemukan!</h5>
    <p class="mb-1">Kelas: <strong>{{ $specificQrCode->class->class_name }}</strong></p>
    <p class="mb-1">Waktu: <strong>{{ $specificQrCode->formatted_time_range }}</strong></p>
    <p class="mb-0">Kode: <code>{{ $specificQrCode->code }}</code></p>
    <div class="mt-2">
        <button class="btn btn-success" onclick="processSpecificQrCode('{{ $specificQrCode->code }}')">
            <i class="fas fa-check-circle me-2"></i>Absen Sekarang
        </button>
    </div>
</div>
@endif

<script>
function processSpecificQrCode(code) {
    showAlert('info', 'Memproses QR Code...');
    processQrCode(code);
}

// Jika ada QR code parameter di URL, auto process
@if($specificQrCode && !$todayAttendance)
    setTimeout(() => {
        processSpecificQrCode('{{ $specificQrCode->code }}');
    }, 1000);
@endif
</script>
                    <!-- Manual Input -->
                    <div class="manual-input card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-keyboard me-2"></i>Masukkan Kode Manual
                            </h6>
                            <p class="text-muted small mb-3">
                                Jika QR Code tidak dapat discan, masukkan kode secara manual
                            </p>
                            <form id="manual-form" class="row g-3">
                                @csrf
                                <div class="col-md-8">
                                    <input type="text" name="qr_code" 
                                           class="form-control" 
                                           placeholder="Masukkan kode QR (contoh: ABC12345)"
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check-circle me-2"></i>Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Active QR Codes -->
                    @if(isset($activeQrCodes) && $activeQrCodes->count() > 0)
                    <div class="active-qr-codes card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-list-check me-2"></i>QR Code Aktif Hari Ini
                            </h6>
                            <div class="list-group">
                                @foreach($activeQrCodes as $qrCode)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $qrCode->class->class_name ?? 'N/A' }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $qrCode->formatted_time_range ?? 'N/A' }}
                                            </small>
                                        </div>
                                        <div>
                                            @if($qrCode->location_restricted)
                                            <span class="badge bg-warning">Lokasi Terbatas</span>
                                            @endif
                                            <span class="badge bg-{{ $qrCode->status_color ?? 'info' }} ms-1">
                                                {{ $qrCode->status_text ?? 'Aktif' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Instructions -->
                    <div class="instructions card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-question-circle me-2"></i>Petunjuk Penggunaan
                            </h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Izinkan akses kamera ketika browser meminta
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Arahkan kamera ke QR Code dengan jarak yang cukup
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Pastikan pencahayaan cukup dan QR Code tidak blur
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Jika scan gagal, gunakan input manual
                                </li>
                                <li>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Pastikan Anda berada di lokasi yang benar jika ada batasan lokasi
                                </li>
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

<script>
let html5QrCode = null;

document.addEventListener('DOMContentLoaded', function() {
    const startScannerBtn = document.getElementById('start-scanner');
    const stopScannerBtn = document.getElementById('stop-scanner');
    const manualForm = document.getElementById('manual-form');
    
    // Start Scanner
    startScannerBtn.addEventListener('click', function() {
        const qrReader = document.getElementById('qr-reader');
        
        html5QrCode = new Html5Qrcode("qr-reader");
        
        const config = { 
            fps: 10, 
            qrbox: { 
                width: 250, 
                height: 250 
            } 
        };
        
        html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        ).then(() => {
            startScannerBtn.style.display = 'none';
            stopScannerBtn.style.display = 'inline-block';
            showAlert('info', 'Scanner berjalan. Arahkan kamera ke QR Code.');
        }).catch(err => {
            showAlert('danger', 'Gagal memulai scanner: ' + err);
        });
    });
    
    // Stop Scanner
    stopScannerBtn.addEventListener('click', function() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                startScannerBtn.style.display = 'inline-block';
                stopScannerBtn.style.display = 'none';
                showAlert('info', 'Scanner dihentikan.');
            }).catch(err => {
                showAlert('danger', 'Gagal menghentikan scanner: ' + err);
            });
        }
    });
    
    // Manual Form Submission
    manualForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const qrCodeInput = this.querySelector('input[name="qr_code"]');
        const qrCode = qrCodeInput.value.trim();
        
        if (!qrCode) {
            showAlert('warning', 'Masukkan kode QR terlebih dahulu.');
            return;
        }
        
        // Process manual QR code
        processQrCode(qrCode);
        
        // Clear input
        qrCodeInput.value = '';
    });
});

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanner setelah berhasil
    if (html5QrCode) {
        html5QrCode.stop();
        document.getElementById('start-scanner').style.display = 'inline-block';
        document.getElementById('stop-scanner').style.display = 'none';
    }
    
    showAlert('success', 'QR Code berhasil dibaca! Memproses...');
    
    // Process QR code
    processQrCode(decodedText);
}

function onScanFailure(error) {
    // Handle scan failure (biasanya terjadi terus menerus)
    // console.warn(`QR scan failed: ${error}`);
}

function processQrCode(code) {
    // Kirim QR code ke server untuk validasi dan absensi
    fetch('{{ route("attendance.scan-process") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            qr_code: code,
            student_id: '{{ Auth::id() }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Redirect ke halaman absensi siswa
            setTimeout(() => {
                window.location.href = '{{ route("attendance.student.index") }}';
            }, 2000);
        } else {
            showAlert('danger', data.message);
            // Jika QR code valid tapi sudah absen, tampilkan info
            if (data.data) {
                showAlert('info', `Anda sudah absen pada ${data.data.time} dengan status ${data.data.status}`);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat memproses QR Code.');
    });
}

function submitAttendance(code, latitude, longitude) {
    // Submit via AJAX
    fetch(`{{ route('attendance.scan', ['code' => '__CODE__']) }}`.replace('__CODE__', code), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            latitude: latitude,
            longitude: longitude
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            
            // Redirect to confirmation or dashboard
            if (data.redirect_url) {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 2000);
            } else {
                setTimeout(() => {
                    window.location.href = '{{ route("attendance.student.index") }}';
                }, 2000);
            }
        } else {
            showAlert('danger', data.message);
            
            // If requires manual input or other handling
            if (data.requires_location) {
                showAlert('warning', 'Silakan izinkan akses lokasi dan coba lagi.');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan saat memproses absensi.');
    });
}

function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.dynamic-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show dynamic-alert mt-3`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert after scanner container
    const scannerContainer = document.querySelector('.qr-scanner-container');
    scannerContainer.parentNode.insertBefore(alertDiv, scannerContainer.nextSibling);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Check if browser supports camera
if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    document.getElementById('start-scanner').disabled = true;
    document.getElementById('start-scanner').innerHTML = '<i class="fas fa-ban me-2"></i>Kamera Tidak Didukung';
    showAlert('warning', 'Browser Anda tidak mendukung akses kamera.');
}
</script>

<style>
.qr-scanner-container {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

#qr-reader {
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

#qr-reader video {
    width: 100%;
    max-width: 400px;
    border-radius: 8px;
}

.manual-input, .instructions, .active-qr-codes {
    border-radius: 10px;
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.list-group-item:first-child {
    border-top: none;
}

.badge {
    font-size: 0.8em;
    padding: 0.4em 0.8em;
}
</style>
@endsection