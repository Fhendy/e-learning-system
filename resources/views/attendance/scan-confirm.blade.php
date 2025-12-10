@extends('layouts.app')

@section('title', 'Konfirmasi Absensi')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-qr-code-scan me-2"></i>Konfirmasi Absensi
                    </h3>
                </div>
                <div class="card-body p-4">
                    <!-- QR Code Information -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <div class="qr-code-badge">
                                <i class="fas fa-check-circle fa-3x text-success"></i>
                            </div>
                        </div>
                        <h4 class="text-success fw-bold mb-2">QR Code Valid!</h4>
                        <p class="text-muted">Silakan konfirmasi data absensi Anda</p>
                    </div>
                    
                    <!-- Class Details -->
                    <div class="card mb-4 border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">
                                <i class="fas fa-chalkboard-teacher me-2"></i>{{ $qrCode->class->class_name ?? 'N/A' }}
                            </h5>
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-2">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                        <strong class="text-muted">Tanggal:</strong>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-clock me-2 text-primary"></i>
                                        <strong class="text-muted">Waktu:</strong>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-user-tie me-2 text-primary"></i>
                                        <strong class="text-muted">Guru:</strong>
                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                    <p class="mb-2 fw-bold">{{ $qrCode->date->format('d F Y') }}</p>
                                    <p class="mb-2 fw-bold">{{ $qrCode->formatted_time_range }}</p>
                                    <p class="mb-0 fw-bold">{{ $qrCode->class->teacher->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            
                            <!-- Time Remaining -->
                            @if($qrCode->is_active_now)
                            <div class="alert alert-info mt-3 mb-0 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-hourglass-half me-2"></i>
                                    <div class="flex-grow-1">
                                        <small class="fw-bold">Sisa Waktu:</small>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $qrCode->time_remaining }} menit</span>
                                            <div class="progress" style="width: 100px; height: 6px;">
                                                @php
                                                    $totalMinutes = $qrCode->duration_minutes_calculated;
                                                    $remainingMinutes = $qrCode->time_remaining;
                                                    $percentage = $totalMinutes > 0 ? ($remainingMinutes / $totalMinutes) * 100 : 0;
                                                @endphp
                                                <div class="progress-bar bg-{{ $remainingMinutes > 10 ? 'success' : ($remainingMinutes > 5 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $percentage }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Student Information -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user-circle me-2"></i>Data Anda
                            </h6>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ auth()->user()->name }}</h5>
                                    <p class="text-muted mb-0">{{ auth()->user()->nis_nip ?? 'N/A' }}</p>
                                    @if(auth()->user()->class)
                                        <p class="text-muted small mb-0">{{ auth()->user()->class->class_name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location Warning -->
                    @if($qrCode->location_restricted)
                    <div class="alert alert-warning mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-map-marker-alt me-2 fa-lg"></i>
                            <div>
                                <strong class="d-block">Perhatian: Batasan Lokasi</strong>
                                <small>Absensi ini hanya dapat dilakukan dalam radius {{ $qrCode->radius }} meter dari lokasi kelas.</small>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Attendance Form -->
                    <form id="attendanceForm" action="{{ route('attendance.process-scan', $qrCode->code) }}" method="POST">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="accuracy" id="accuracy">
                        
                        <!-- Status Selection (if allowed) -->
                        @if($allowStatusSelection ?? false)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Status Kehadiran</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="status" id="statusPresent" value="present" checked>
                                <label class="btn btn-outline-success" for="statusPresent">
                                    <i class="fas fa-check me-2"></i>Hadir
                                </label>
                                
                                <input type="radio" class="btn-check" name="status" id="statusLate" value="late">
                                <label class="btn btn-outline-warning" for="statusLate">
                                    <i class="fas fa-clock me-2"></i>Terlambat
                                </label>
                                
                                <input type="radio" class="btn-check" name="status" id="statusSick" value="sick">
                                <label class="btn btn-outline-info" for="statusSick">
                                    <i class="fas fa-thermometer me-2"></i>Sakit
                                </label>
                                
                                <input type="radio" class="btn-check" name="status" id="statusPermission" value="permission">
                                <label class="btn btn-outline-primary" for="statusPermission">
                                    <i class="fas fa-file-alt me-2"></i>Izin
                                </label>
                            </div>
                        </div>
                        
                        <!-- Notes Field -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Keterangan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="2" 
                                      placeholder="Tambahkan keterangan jika diperlukan..."></textarea>
                        </div>
                        @endif
                        
                        <!-- Location Information -->
                        <div class="mb-4" id="locationInfo" style="display: none;">
                            <div class="alert alert-info py-2 mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <div>
                                        <small class="fw-bold">Lokasi Terdeteksi</small>
                                        <div class="d-flex justify-content-between">
                                            <span id="locationText">Mengambil lokasi...</span>
                                            <span id="locationAccuracy" class="badge bg-info"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg py-3" id="submitBtn">
                                <i class="fas fa-check-circle me-2"></i>KONFIRMASI ABSENSI
                            </button>
                            <a href="{{ route('attendance.student.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times-circle me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                    
                    <!-- Loading Indicator -->
                    <div id="loading" class="text-center mt-3 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Mendapatkan lokasi...</p>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center py-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Pastikan data di atas sudah benar sebelum konfirmasi
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('attendanceForm');
    const submitBtn = document.getElementById('submitBtn');
    const loading = document.getElementById('loading');
    const locationInfo = document.getElementById('locationInfo');
    const locationText = document.getElementById('locationText');
    const locationAccuracy = document.getElementById('locationAccuracy');
    
    // Try to get location immediately
    getLocation();
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate location for restricted QR codes
        @if($qrCode->location_restricted)
        if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
            alert('Lokasi diperlukan untuk absensi ini. Mohon aktifkan akses lokasi di browser Anda.');
            getLocation();
            return;
        }
        @endif
        
        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        loading.classList.remove('d-none');
        
        // Submit form
        setTimeout(() => {
            form.submit();
        }, 1000);
    });
    
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Success: got location
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const acc = Math.round(position.coords.accuracy);
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    document.getElementById('accuracy').value = acc;
                    
                    // Show location info
                    locationInfo.style.display = 'block';
                    locationText.textContent = 'Lokasi berhasil didapatkan';
                    locationAccuracy.textContent = `±${acc}m`;
                    
                    // Check if within radius for restricted QR codes
                    @if($qrCode->location_restricted && $qrCode->latitude && $qrCode->longitude)
                        const distance = calculateDistance(
                            lat, lng, 
                            {{ $qrCode->latitude }}, {{ $qrCode->longitude }}
                        );
                        
                        if (distance > {{ $qrCode->radius }}) {
                            locationText.innerHTML = 
                                `<span class="text-danger">Anda berada di luar radius (${Math.round(distance)}m)</span>`;
                            locationAccuracy.className = 'badge bg-danger';
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Di luar radius';
                        } else {
                            locationText.innerHTML = 
                                `<span class="text-success">Dalam radius (${Math.round(distance)}m)</span>`;
                            locationAccuracy.className = 'badge bg-success';
                        }
                    @endif
                },
                function(error) {
                    // Error: couldn't get location
                    console.error('Geolocation error:', error);
                    
                    @if($qrCode->location_restricted)
                        locationInfo.style.display = 'block';
                        locationText.innerHTML = 
                            '<span class="text-danger">Gagal mendapatkan lokasi</span>';
                        locationAccuracy.textContent = 'Error';
                        locationAccuracy.className = 'badge bg-danger';
                        
                        if (error.code === error.PERMISSION_DENIED) {
                            locationText.innerHTML += '<br><small>Mohon izinkan akses lokasi</small>';
                        }
                    @endif
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            // Browser doesn't support geolocation
            console.log('Geolocation is not supported by this browser.');
            
            @if($qrCode->location_restricted)
                locationInfo.style.display = 'block';
                locationText.innerHTML = 
                    '<span class="text-warning">Browser tidak mendukung geolocation</span>';
                locationAccuracy.textContent = 'N/A';
                locationAccuracy.className = 'badge bg-warning';
            @endif
        }
    }
    
    // Calculate distance between two coordinates in meters
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;
        
        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        
        return R * c; // Distance in meters
    }
    
    // Auto submit after 30 seconds if user doesn't act
    let autoSubmitTimer;
    function startAutoSubmitTimer() {
        autoSubmitTimer = setTimeout(() => {
            if (!submitBtn.disabled) {
                if (confirm('Waktu absensi hampir habis. Lanjutkan?')) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        }, 30000); // 30 seconds
    }
    
    // Start timer when page loads
    startAutoSubmitTimer();
    
    // Reset timer on any user interaction
    ['click', 'keydown', 'mousemove'].forEach(event => {
        document.addEventListener(event, () => {
            clearTimeout(autoSubmitTimer);
            startAutoSubmitTimer();
        });
    });
});
</script>

<style>
.min-vh-100 {
    min-height: 100vh;
}
.qr-code-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: #d4edda;
    border-radius: 50%;
    border: 3px solid #28a745;
}
.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.card {
    border-radius: 15px;
}
.btn-check:checked + .btn-outline-success {
    background-color: #28a745;
    color: white;
}
.btn-check:checked + .btn-outline-warning {
    background-color: #ffc107;
    color: white;
}
.btn-check:checked + .btn-outline-info {
    background-color: #17a2b8;
    color: white;
}
.btn-check:checked + .btn-outline-primary {
    background-color: #007bff;
    color: white;
}
.btn-lg {
    font-weight: 600;
}
.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}
@keyframes progress-bar-stripes {
    from { background-position: 1rem 0; }
    to { background-position: 0 0; }
}
</style>
@endsection