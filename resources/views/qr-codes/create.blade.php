@extends('layouts.app')

@section('title', 'Buat QR Code Baru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Buat QR Code Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('qr-codes.store') }}" method="POST" id="qrCodeForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                <select name="class_id" id="class_id" class="form-select" required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->class_name }} ({{ $class->class_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="date" class="form-control" 
                                       value="{{ old('date', $defaults['date']) }}" 
                                       min="{{ now()->format('Y-m-d') }}" required>
                                @error('date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" id="start_time" class="form-control" 
                                       value="{{ old('start_time', $defaults['start_time']) }}" required>
                                @error('start_time')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" id="end_time" class="form-control" 
                                       value="{{ old('end_time', $defaults['end_time']) }}" required>
                                @error('end_time')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="duration_minutes" class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" 
                                       value="{{ old('duration_minutes', $defaults['duration_minutes']) }}" 
                                       min="1" max="1440" required>
                                @error('duration_minutes')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" name="location_restricted" id="location_restricted" 
                                           class="form-check-input" value="1"
                                           {{ old('location_restricted', $defaults['location_restricted']) ? 'checked' : '' }}>
                                    <label for="location_restricted" class="form-check-label">
                                        Batasi lokasi absensi
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="location_fields" style="display: {{ old('location_restricted', $defaults['location_restricted']) ? 'block' : 'none' }}">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="latitude" class="form-label">Latitude</label>
                                    <input type="number" step="any" name="latitude" id="latitude" class="form-control" 
                                           value="{{ old('latitude') }}" 
                                           min="-90" max="90">
                                    @error('latitude')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="longitude" class="form-label">Longitude</label>
                                    <input type="number" step="any" name="longitude" id="longitude" class="form-control" 
                                           value="{{ old('longitude') }}" 
                                           min="-180" max="180">
                                    @error('longitude')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="radius" class="form-label">Radius (meter)</label>
                                    <input type="number" name="radius" id="radius" class="form-control" 
                                           value="{{ old('radius', 100) }}" 
                                           min="10" max="1000">
                                    @error('radius')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <button type="button" id="getCurrentLocation" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-map-marker-alt"></i> Gunakan Lokasi Saya
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Catatan (opsional)</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('qr-codes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-qrcode"></i> Generate QR Code
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Quick Generate Section -->
            <div class="card mb-3">
                <div class="card-header bg-warning">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-dark me-2"></i>Generate Cepat (30 menit)
                    </h5>
                </div>
                <div class="card-body">
                    @if($classes->count() > 0)
                        @foreach($classes as $class)
                        <div class="d-grid gap-2 mb-2">
                            <button class="btn btn-outline-primary quick-generate-btn" 
                                    data-class-id="{{ $class->id }}"
                                    data-class-name="{{ $class->class_name }}">
                                <i class="fas fa-qrcode me-2"></i>{{ $class->class_name }}
                            </button>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Belum ada kelas yang tersedia. Silakan buat kelas terlebih dahulu.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Perhatian:</h6>
                        <ul class="mb-0 small">
                            <li>QR Code hanya aktif pada tanggal dan waktu yang ditentukan</li>
                            <li>Siswa hanya dapat melakukan absensi 1 kali per QR Code</li>
                            <li>QR Code akan otomatis dinonaktifkan setelah waktu berakhir</li>
                            <li>Pastikan waktu selesai setelah waktu mulai</li>
                        </ul>
                    </div>
                    <hr>
                    <h6><i class="fas fa-lightbulb text-warning me-2"></i>Tips:</h6>
                    <ul class="small">
                        <li>Gunakan fitur "Generate Cepat" untuk membuat QR Code dengan durasi 30 menit</li>
                        <li>Jika membatasi lokasi, pastikan radius mencukupi area kelas</li>
                        <li>QR Code dapat didownload dan dicetak untuk dibagikan ke siswa</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6>Memproses QR Code...</h6>
                <p class="text-muted small mb-0">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('qrCodeForm');
    const locationCheckbox = document.getElementById('location_restricted');
    const locationFields = document.getElementById('location_fields');
    const submitBtn = document.getElementById('submitBtn');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    // Toggle location fields
    if (locationCheckbox) {
        locationCheckbox.addEventListener('change', function() {
            if (this.checked) {
                locationFields.style.display = 'block';
                // Set required attribute for location fields
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const radiusInput = document.getElementById('radius');
                
                if (latInput) latInput.required = true;
                if (lngInput) lngInput.required = true;
                if (radiusInput) radiusInput.required = true;
            } else {
                locationFields.style.display = 'none';
                // Remove required attribute
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const radiusInput = document.getElementById('radius');
                
                if (latInput) latInput.required = false;
                if (lngInput) lngInput.required = false;
                if (radiusInput) radiusInput.required = false;
            }
        });
    }
    
    // Get current location button
    const getLocationBtn = document.getElementById('getCurrentLocation');
    if (getLocationBtn) {
        getLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                getLocationBtn.disabled = true;
                getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendapatkan lokasi...';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                        getLocationBtn.disabled = false;
                        getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Gunakan Lokasi Saya';
                        alert('Lokasi berhasil didapatkan!');
                    },
                    function(error) {
                        getLocationBtn.disabled = false;
                        getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Gunakan Lokasi Saya';
                        let errorMessage = 'Gagal mendapatkan lokasi: ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'Izin lokasi ditolak.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'Informasi lokasi tidak tersedia.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'Waktu permintaan habis.';
                                break;
                            default:
                                errorMessage += error.message;
                        }
                        alert(errorMessage);
                    }
                );
            } else {
                alert('Browser Anda tidak mendukung geolocation.');
            }
        });
    }
    
    // Quick generate buttons
    document.querySelectorAll('.quick-generate-btn').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.dataset.classId;
            const className = this.dataset.className;
            
            if (confirm(`Generate QR Code untuk kelas ${className} selama 30 menit?`)) {
                // Show loading
                loadingModal.show();
                
                fetch(`/attendance/quick-generate-qr/${classId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loadingModal.hide();
                    
                    if (data.success) {
                        alert('QR Code berhasil dibuat!');
                        if (data.data && data.data.id) {
                            window.location.href = `/qr-codes/${data.data.id}`;
                        } else if (data.data && data.data.realtime_url) {
                            window.location.href = data.data.realtime_url;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    loadingModal.hide();
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat membuat QR Code. Silakan coba lagi.');
                });
            }
        });
    });
    
    // Form validation for time
    if (form) {
        form.addEventListener('submit', function(e) {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (startTime && endTime) {
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);
                
                if (end <= start) {
                    e.preventDefault();
                    alert('Waktu selesai harus setelah waktu mulai.');
                    return false;
                }
            }
            
            // If location restricted, validate coordinates
            if (locationCheckbox && locationCheckbox.checked) {
                const lat = document.getElementById('latitude').value;
                const lng = document.getElementById('longitude').value;
                const radius = document.getElementById('radius').value;
                
                if (!lat || !lng) {
                    e.preventDefault();
                    alert('Mohon isi Latitude dan Longitude untuk pembatasan lokasi.');
                    return false;
                }
                
                if (lat < -90 || lat > 90) {
                    e.preventDefault();
                    alert('Latitude harus antara -90 dan 90.');
                    return false;
                }
                
                if (lng < -180 || lng > 180) {
                    e.preventDefault();
                    alert('Longitude harus antara -180 dan 180.');
                    return false;
                }
                
                if (!radius || radius < 10 || radius > 1000) {
                    e.preventDefault();
                    alert('Radius harus antara 10 dan 1000 meter.');
                    return false;
                }
            }
            
            // Show loading on submit
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            loadingModal.show();
            
            return true;
        });
    }
    
    // Auto-calculate duration from start and end time
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const durationInput = document.getElementById('duration_minutes');
    
    function calculateDuration() {
        if (startTimeInput.value && endTimeInput.value) {
            const start = new Date(`2000-01-01T${startTimeInput.value}`);
            const end = new Date(`2000-01-01T${endTimeInput.value}`);
            
            if (end > start) {
                const diffMinutes = Math.round((end - start) / (1000 * 60));
                if (durationInput && diffMinutes > 0) {
                    durationInput.value = diffMinutes;
                }
            }
        }
    }
    
    if (startTimeInput && endTimeInput) {
        startTimeInput.addEventListener('change', calculateDuration);
        endTimeInput.addEventListener('change', calculateDuration);
    }
});
</script>
@endpush