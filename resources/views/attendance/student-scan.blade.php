{{-- resources/views/attendance/student-scan.blade.php --}}
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
                    
                    @if($todayAttendance)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Anda sudah melakukan absensi hari ini ({{ $todayAttendance->status_text ?? 'Hadir' }}) pada 
                        {{ $todayAttendance->checked_in_at ? Carbon\Carbon::parse($todayAttendance->checked_in_at)->format('H:i') : '-' }}
                    </div>
                    @endif
                    
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

                    @if($specificQrCode && !$todayAttendance)
                    <div class="alert alert-success">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                @php
                                    $qrImageUrl = null;
                                    $imagePath = $specificQrCode->qr_code_image;
                                    
                                    if ($imagePath) {
                                        // Cek berbagai kemungkinan path
                                        if (Storage::disk('public')->exists($imagePath)) {
                                            $qrImageUrl = Storage::url($imagePath);
                                        } elseif (Storage::disk('public')->exists('qr-codes/' . $specificQrCode->code . '.png')) {
                                            $qrImageUrl = Storage::url('qr-codes/' . $specificQrCode->code . '.png');
                                        } elseif (file_exists(public_path('storage/' . $imagePath))) {
                                            $qrImageUrl = asset('storage/' . $imagePath);
                                        }
                                    }
                                    
                                    // Jika masih tidak ada, coba generate URL alternatif
                                    if (!$qrImageUrl && $specificQrCode->code) {
                                        $qrImageUrl = asset('storage/qr-codes/' . $specificQrCode->code . '.png');
                                    }
                                @endphp
                                
                                @if($qrImageUrl)
                                    <img src="{{ $qrImageUrl }}" 
                                         alt="QR Code" 
                                         class="img-fluid border p-2 rounded shadow-sm"
                                         style="max-width: 150px;"
                                         onerror="this.style.display='none'; this.parentElement.querySelector('.qr-fallback').style.display='block';">
                                    <div class="qr-fallback" style="display: none;">
                                        <div class="alert alert-warning mt-2 p-2 small">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Gambar tidak tersedia<br>
                                            <strong>Kode: {{ $specificQrCode->code }}</strong>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info p-3">
                                        <i class="fas fa-qrcode fa-3x mb-2"></i>
                                        <p class="mb-0"><strong>Kode QR:</strong><br>{{ $specificQrCode->code }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h5><i class="fas fa-qrcode me-2"></i>QR Code Ditemukan!</h5>
                                <p class="mb-1">Kelas: <strong>{{ $specificQrCode->class->class_name ?? 'N/A' }}</strong></p>
                                <p class="mb-1">Waktu: <strong>{{ $specificQrCode->formatted_start_time ?? $specificQrCode->start_time ?? 'N/A' }} - {{ $specificQrCode->formatted_end_time ?? $specificQrCode->end_time ?? 'N/A' }}</strong></p>
                                <p class="mb-0">Kode: <code>{{ $specificQrCode->code ?? 'N/A' }}</code></p>
                                <div class="mt-3">
                                    <button class="btn btn-success" onclick="processSpecificQrCode('{{ $specificQrCode->code ?? '' }}')">
                                        <i class="fas fa-check-circle me-2"></i>Absen Sekarang
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                                        <i class="fas fa-sync-alt me-2"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

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
                                    <input type="text" 
                                           id="manual_qr_code_input"
                                           class="form-control" 
                                           placeholder="Masukkan kode QR (contoh: ABC12345)"
                                           autocomplete="off"
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" id="submit-manual-btn" class="btn btn-success w-100">
                                        <i class="fas fa-check-circle me-2"></i>Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Active QR Codes -->
                    @if($activeQrCodes && $activeQrCodes->count() > 0)
                    <div class="active-qr-codes card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-list-check me-2"></i>QR Code Aktif Hari Ini
                            </h6>
                            <div class="list-group">
                                @foreach($activeQrCodes as $qrCodeItem)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $qrCodeItem->class->class_name ?? 'N/A' }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $qrCodeItem->formatted_start_time ?? $qrCodeItem->start_time ?? 'N/A' }} - {{ $qrCodeItem->formatted_end_time ?? $qrCodeItem->end_time ?? 'N/A' }}
                                            </small>
                                            <div class="mt-1">
                                                <code class="small">{{ $qrCodeItem->code }}</code>
                                            </div>
                                        </div>
                                        <div>
                                            @if($qrCodeItem->location_restricted)
                                            <span class="badge bg-warning">Lokasi Terbatas</span>
                                            @endif
                                            <span class="badge bg-success ms-1">Aktif</span>
                                            <button class="btn btn-sm btn-outline-primary mt-2 d-block" onclick="processSpecificQrCode('{{ $qrCodeItem->code }}')">
                                                <i class="fas fa-check me-1"></i>Absen
                                            </button>
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
const studentId = {{ Auth::id() }};
const csrfToken = '{{ csrf_token() }}';
const apiUrl = '{{ url("/api/attendance/scan-process") }}';

document.addEventListener('DOMContentLoaded', function() {
    const startScannerBtn = document.getElementById('start-scanner');
    const stopScannerBtn = document.getElementById('stop-scanner');
    const submitManualBtn = document.getElementById('submit-manual-btn');
    const manualInput = document.getElementById('manual_qr_code_input');
    
    // Start Scanner
    if (startScannerBtn) {
        startScannerBtn.addEventListener('click', function() {
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
    }
    
    // Stop Scanner
    if (stopScannerBtn) {
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
    }
    
    // Manual Form Submission
    if (submitManualBtn && manualInput) {
        submitManualBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const qrCode = manualInput.value.trim();
            
            if (!qrCode) {
                showAlert('warning', 'Masukkan kode QR terlebih dahulu.');
                manualInput.focus();
                return;
            }
            
            // Process manual QR code
            processQrCode(qrCode);
            
            // Clear input
            manualInput.value = '';
        });
        
        // Allow Enter key to submit
        manualInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitManualBtn.click();
            }
        });
    }
});

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanner setelah berhasil
    if (html5QrCode) {
        html5QrCode.stop();
        const startScannerBtn = document.getElementById('start-scanner');
        const stopScannerBtn = document.getElementById('stop-scanner');
        if (startScannerBtn) startScannerBtn.style.display = 'inline-block';
        if (stopScannerBtn) stopScannerBtn.style.display = 'none';
    }
    
    showAlert('success', 'QR Code berhasil dibaca! Memproses...');
    
    // Process QR code
    processQrCode(decodedText);
}

function onScanFailure(error) {
    // Handle scan failure - silent
}

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

function processQrCode(code) {
    // Show loading
    const loadingAlert = showAlert('info', 'Memproses absensi... <i class="fas fa-spinner fa-spin ms-2"></i>');
    
    const data = {
        qr_code: code,
        student_id: studentId
    };
    
    console.log('Sending data:', data);
    
    // Kirim QR code ke server untuk validasi dan absensi
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        
        if (data.success) {
            showAlert('success', data.message + ' ✅');
            // Redirect ke halaman absensi siswa
            setTimeout(() => {
                window.location.href = '{{ route("attendance.student.index") }}';
            }, 2000);
        } else {
            showAlert('danger', data.message + ' ❌');
            // Jika QR code valid tapi sudah absen, tampilkan info
            if (data.data) {
                setTimeout(() => {
                    showAlert('info', `Anda sudah absen pada ${data.data.time} dengan status ${data.data.status}`);
                }, 3000);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Terjadi kesalahan: ' + error.message);
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
    if (scannerContainer && scannerContainer.parentNode) {
        scannerContainer.parentNode.insertBefore(alertDiv, scannerContainer.nextSibling);
    } else {
        const cardBody = document.querySelector('.card-body');
        if (cardBody) cardBody.appendChild(alertDiv);
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
    
    return alertDiv;
}

// Check if browser supports camera
if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    const startScannerBtn = document.getElementById('start-scanner');
    if (startScannerBtn) {
        startScannerBtn.disabled = true;
        startScannerBtn.innerHTML = '<i class="fas fa-ban me-2"></i>Kamera Tidak Didukung';
    }
    showAlert('warning', 'Browser Anda tidak mendukung akses kamera.');
}

// Refresh page function
function refreshPage() {
    window.location.reload();
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

.dynamic-alert {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.list-group-item button {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
}

code {
    font-size: 0.75rem;
    background: #f8f9fa;
    padding: 2px 4px;
    border-radius: 4px;
}
</style>
@endsection