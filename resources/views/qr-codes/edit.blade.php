@extends('layouts.app')

@section('title', 'Edit QR Code')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-primary">Edit QR Code</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('qr-codes.show', $qrCode) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit QR Code</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('qr-codes.update', $qrCode) }}" method="POST" id="qrCodeForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas *</label>
                                <select name="class_id" 
                                        class="form-select @error('class_id') is-invalid @enderror" 
                                        required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}" 
                                            {{ old('class_id', $qrCode->class_id) == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }} - {{ $class->subject }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal *</label>
                                <input type="date" name="date" 
                                       class="form-control @error('date') is-invalid @enderror"
                                       value="{{ old('date', $qrCode->date->format('Y-m-d')) }}" 
                                       required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Mulai *</label>
                                <input type="time" name="start_time" 
                                       class="form-control @error('start_time') is-invalid @enderror"
                                       value="{{ old('start_time', $qrCode->start_time->format('H:i')) }}" 
                                       required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Berakhir *</label>
                                <input type="time" name="end_time" 
                                       class="form-control @error('end_time') is-invalid @enderror"
                                       value="{{ old('end_time', $qrCode->end_time->format('H:i')) }}" 
                                       required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="location_restricted" 
                                           id="locationRestricted"
                                           {{ old('location_restricted', $qrCode->location_restricted) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="locationRestricted">
                                        Batasi lokasi absensi
                                    </label>
                                </div>
                            </div>
                            
                            <div id="locationSection" style="display: {{ $qrCode->location_restricted ? 'block' : 'none' }};">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Latitude</label>
                                        <input type="number" step="0.000001" name="latitude" 
                                               class="form-control @error('latitude') is-invalid @enderror"
                                               value="{{ old('latitude', $qrCode->latitude) }}">
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Longitude</label>
                                        <input type="number" step="0.000001" name="longitude" 
                                               class="form-control @error('longitude') is-invalid @enderror"
                                               value="{{ old('longitude', $qrCode->longitude) }}">
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Radius (meter)</label>
                                        <input type="number" name="radius" 
                                               class="form-control @error('radius') is-invalid @enderror"
                                               value="{{ old('radius', $qrCode->radius) }}">
                                        @error('radius')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="getCurrentLocation()">
                                            <i class="bi bi-geo-alt me-2"></i>Gunakan Lokasi Sekarang
                                        </button>
                                        <small class="text-muted ms-2">Ambil lokasi GPS Anda saat ini</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Durasi Tampilan (menit)</label>
                                <div class="input-group">
                                    <input type="number" name="duration_minutes" 
                                           class="form-control @error('duration_minutes') is-invalid @enderror"
                                           value="{{ old('duration_minutes', $qrCode->duration_minutes) }}" 
                                           min="5" max="60">
                                    <span class="input-group-text">menit</span>
                                </div>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">QR Code akan aktif selama waktu ini</small>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', $qrCode->is_active) ? 'selected' : '' }}>
                                        Aktif
                                    </option>
                                    <option value="0" {{ !old('is_active', $qrCode->is_active) ? 'selected' : '' }}>
                                        Nonaktif
                                    </option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Keterangan (Opsional)</label>
                                <textarea name="notes" 
                                          class="form-control @error('notes') is-invalid @enderror" 
                                          rows="3">{{ old('notes', $qrCode->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <form action="{{ route('qr-codes.destroy', $qrCode) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Hapus QR Code ini?')">
                                        <i class="bi bi-trash me-2"></i>Hapus
                                    </button>
                                </form>
                            </div>
                            
                            <div>
                                <button type="reset" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle me-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Preview Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Preview QR Code</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($qrCode->qr_code_image)
                        <img src="{{ asset('storage/' . $qrCode->qr_code_image) }}" 
                             alt="QR Code" class="img-fluid" style="max-width: 200px;">
                        @else
                        <div class="qr-placeholder bg-light p-4 rounded">
                            <i class="bi bi-qr-code fa-4x text-muted"></i>
                            <p class="mt-2 text-muted">QR Code akan diperbarui setelah disimpan</p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="info-preview">
                        <h5 id="previewClassName" class="text-primary">{{ $qrCode->class->class_name }}</h5>
                        <p class="mb-1">
                            <i class="bi bi-calendar me-2"></i>
                            <span id="previewDate">{{ $qrCode->date->format('d F Y') }}</span>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-clock me-2"></i>
                            <span id="previewTime">{{ $qrCode->formatted_time_range }}</span>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-hourglass me-2"></i>
                            <span id="previewDuration">{{ $qrCode->duration_minutes }} menit</span>
                        </p>
                        <p class="mb-0 mt-2">
                            <span class="badge bg-{{ $qrCode->is_active ? 'success' : 'danger' }}">
                                {{ $qrCode->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">Code: {{ $qrCode->code }}</small>
                    </div>
                </div>
            </div>
            
            <!-- Information Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik QR Code</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Dibuat</td>
                            <td class="text-end">{{ $qrCode->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Diperbarui</td>
                            <td class="text-end">{{ $qrCode->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Jumlah Scan</td>
                            <td class="text-end">{{ $qrCode->scan_count }}</td>
                        </tr>
                        <tr>
                            <td>Kehadiran</td>
                            <td class="text-end">
                                <span class="badge bg-primary">
                                    {{ $qrCode->attendances->whereIn('status', ['present', 'late'])->count() }} siswa
                                </span>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="mt-3">
                        <a href="{{ route('attendance.scan', $qrCode->code) }}" 
                           target="_blank" class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-qr-code-scan me-2"></i>Scan QR Code
                        </a>
                        <a href="data:image/png;base64,{{ base64_encode(QrCode::size(300)->generate(route('attendance.scan', $qrCode->code))) }}" 
                           download="qr-code-{{ $qrCode->code }}-new.png" 
                           class="btn btn-outline-success w-100">
                            <i class="bi bi-download me-2"></i>Download QR Code
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle location section
    const locationToggle = document.getElementById('locationRestricted');
    const locationSection = document.getElementById('locationSection');
    
    if (locationToggle && locationSection) {
        locationToggle.addEventListener('change', function() {
            locationSection.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Update preview
    const form = document.getElementById('qrCodeForm');
    const previewClassName = document.getElementById('previewClassName');
    const previewDate = document.getElementById('previewDate');
    const previewTime = document.getElementById('previewTime');
    const previewDuration = document.getElementById('previewDuration');
    
    function updatePreview() {
        const className = form.querySelector('[name="class_id"] option:selected').text;
        const dateValue = form.querySelector('[name="date"]').value;
        const startTime = form.querySelector('[name="start_time"]').value;
        const endTime = form.querySelector('[name="end_time"]').value;
        const duration = form.querySelector('[name="duration_minutes"]').value;
        
        if (className && className !== 'Pilih Kelas') {
            previewClassName.textContent = className.split(' - ')[0];
        }
        
        if (dateValue) {
            const date = new Date(dateValue);
            previewDate.textContent = date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }
        
        if (startTime && endTime) {
            previewTime.textContent = `${startTime} - ${endTime}`;
        }
        
        if (duration) {
            previewDuration.textContent = `${duration} menit`;
        }
    }
    
    // Attach event listeners
    ['class_id', 'date', 'start_time', 'end_time', 'duration_minutes'].forEach(field => {
        const element = form.querySelector(`[name="${field}"]`);
        if (element) {
            element.addEventListener('change', updatePreview);
        }
    });
});

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const latitudeInput = document.querySelector('[name="latitude"]');
                const longitudeInput = document.querySelector('[name="longitude"]');
                
                if (latitudeInput && longitudeInput) {
                    latitudeInput.value = position.coords.latitude.toFixed(6);
                    longitudeInput.value = position.coords.longitude.toFixed(6);
                    
                    // Show success message
                    alert('Lokasi berhasil diambil!');
                }
            },
            function(error) {
                alert('Gagal mendapatkan lokasi: ' + error.message);
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    } else {
        alert('Browser tidak mendukung geolocation');
    }
}
</script>

<style>
.qr-placeholder {
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.info-preview {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}
</style>
@endsection