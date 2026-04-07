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
                                        {{ $class->class_name }} {{ $class->subject ? '- ' . $class->subject : '' }}
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
                                       value="{{ old('date', $qrCode->date instanceof \Carbon\Carbon ? $qrCode->date->format('Y-m-d') : \Carbon\Carbon::parse($qrCode->date)->format('Y-m-d')) }}" 
                                       required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Mulai *</label>
                                <input type="time" name="start_time" 
                                       class="form-control @error('start_time') is-invalid @enderror"
                                       value="{{ old('start_time', $qrCode->start_time) }}" 
                                       required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Berakhir *</label>
                                <input type="time" name="end_time" 
                                       class="form-control @error('end_time') is-invalid @enderror"
                                       value="{{ old('end_time', $qrCode->end_time) }}" 
                                       required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Durasi (menit) *</label>
                                <input type="number" name="duration_minutes" 
                                       class="form-control @error('duration_minutes') is-invalid @enderror"
                                       value="{{ old('duration_minutes', $qrCode->duration_minutes) }}" 
                                       min="1" max="1440" required>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Durasi QR Code akan aktif (1-1440 menit)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', $qrCode->is_active) ? 'selected' : '' }}>
                                        <i class="bi bi-check-circle"></i> Aktif
                                    </option>
                                    <option value="0" {{ !old('is_active', $qrCode->is_active) ? 'selected' : '' }}>
                                        <i class="bi bi-x-circle"></i> Nonaktif
                                    </option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="location_restricted" 
                                           id="locationRestricted"
                                           value="1"
                                           {{ old('location_restricted', $qrCode->location_restricted) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="locationRestricted">
                                        <i class="bi bi-geo-alt-fill me-1"></i>
                                        Batasi lokasi absensi
                                    </label>
                                </div>
                            </div>
                            
                            <div id="locationSection" style="display: {{ old('location_restricted', $qrCode->location_restricted) ? 'block' : 'none' }};">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Latitude</label>
                                        <input type="number" step="0.000001" name="latitude" 
                                               class="form-control @error('latitude') is-invalid @enderror"
                                               value="{{ old('latitude', $qrCode->latitude) }}"
                                               placeholder="-6.123456">
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Longitude</label>
                                        <input type="number" step="0.000001" name="longitude" 
                                               class="form-control @error('longitude') is-invalid @enderror"
                                               value="{{ old('longitude', $qrCode->longitude) }}"
                                               placeholder="106.123456">
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Radius (meter)</label>
                                        <input type="number" name="radius" 
                                               class="form-control @error('radius') is-invalid @enderror"
                                               value="{{ old('radius', $qrCode->radius) }}"
                                               placeholder="100"
                                               min="10" max="1000">
                                        @error('radius')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="getCurrentLocation()">
                                            <i class="bi bi-geo-alt me-2"></i>Gunakan Lokasi Sekarang
                                        </button>
                                        <small class="text-muted ms-2">Ambil lokasi GPS Anda saat ini</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Keterangan (Opsional)</label>
                                <textarea name="notes" 
                                          class="form-control @error('notes') is-invalid @enderror" 
                                          rows="3"
                                          placeholder="Tambahkan catatan untuk QR Code ini...">{{ old('notes', $qrCode->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="regenerate_qr" id="regenerateQr" value="1">
                                    <label class="form-check-label" for="regenerateQr">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        Generate ulang gambar QR Code
                                    </label>
                                    <small class="text-muted d-block ms-4">
                                        Centang jika ingin membuat gambar QR Code baru
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <form action="{{ route('qr-codes.destroy', $qrCode) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus QR Code ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
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
                        @php
                            $imageUrl = null;
                            if ($qrCode->qr_code_image) {
                                if (Storage::disk('public')->exists($qrCode->qr_code_image)) {
                                    $imageUrl = Storage::url($qrCode->qr_code_image);
                                } elseif (Storage::disk('public')->exists('qr-codes/' . $qrCode->code . '.png')) {
                                    $imageUrl = Storage::url('qr-codes/' . $qrCode->code . '.png');
                                }
                            }
                        @endphp
                        
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" 
                                 alt="QR Code" 
                                 class="img-fluid border p-2 rounded"
                                 style="max-width: 200px;"
                                 id="previewImage">
                        @else
                            <div class="qr-placeholder bg-light p-4 rounded">
                                <i class="bi bi-qr-code fs-1 text-muted"></i>
                                <p class="mt-2 text-muted small">QR Code akan diperbarui setelah disimpan</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="info-preview">
                        <h5 id="previewClassName" class="text-primary mb-2">
                            {{ $qrCode->class->class_name ?? 'N/A' }}
                        </h5>
                        <p class="mb-1">
                            <i class="bi bi-calendar me-2"></i>
                            <span id="previewDate">
                                @if($qrCode->date instanceof \Carbon\Carbon)
                                    {{ $qrCode->date->format('d F Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($qrCode->date)->format('d F Y') }}
                                @endif
                            </span>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-clock me-2"></i>
                            <span id="previewTime">{{ $qrCode->formatted_time_range ?? ($qrCode->start_time . ' - ' . $qrCode->end_time) }}</span>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-hourglass me-2"></i>
                            <span id="previewDuration">{{ $qrCode->duration_minutes }} menit</span>
                        </p>
                        <p class="mb-0 mt-2">
                            <span class="badge bg-{{ $qrCode->is_active ? 'success' : 'danger' }}" id="previewStatus">
                                {{ $qrCode->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">Kode: <code>{{ $qrCode->code }}</code></small>
                    </div>
                </div>
            </div>
            
            <!-- Information Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi QR Code</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td width="40%"><i class="bi bi-calendar-plus me-2"></i>Dibuat</td>
                            <td class="text-end">
                                @if($qrCode->created_at instanceof \Carbon\Carbon)
                                    {{ $qrCode->created_at->format('d/m/Y H:i') }}
                                @else
                                    {{ \Carbon\Carbon::parse($qrCode->created_at)->format('d/m/Y H:i') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-pencil-square me-2"></i>Diperbarui</td>
                            <td class="text-end">
                                @if($qrCode->updated_at instanceof \Carbon\Carbon)
                                    {{ $qrCode->updated_at->format('d/m/Y H:i') }}
                                @else
                                    {{ \Carbon\Carbon::parse($qrCode->updated_at)->format('d/m/Y H:i') }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-camera me-2"></i>Total Scan</td>
                            <td class="text-end">{{ number_format($qrCode->scan_count) }} kali</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-people me-2"></i>Kehadiran</td>
                            <td class="text-end">
                                <span class="badge bg-primary">
                                    {{ $qrCode->attendances->whereIn('status', ['present', 'late'])->count() }} siswa
                                </span>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="mt-3">
                        <a href="{{ url('/attendance/scan-page?qr_code=' . $qrCode->code) }}" 
                           target="_blank" class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-qr-code-scan me-2"></i>Halaman Scan
                        </a>
                        <a href="{{ route('qr-codes.download', $qrCode) }}" 
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
            
            // Toggle required attributes
            const latInput = document.querySelector('[name="latitude"]');
            const lngInput = document.querySelector('[name="longitude"]');
            const radiusInput = document.querySelector('[name="radius"]');
            
            if (latInput && lngInput && radiusInput) {
                if (this.checked) {
                    latInput.required = true;
                    lngInput.required = true;
                    radiusInput.required = true;
                } else {
                    latInput.required = false;
                    lngInput.required = false;
                    radiusInput.required = false;
                }
            }
        });
    }
    
    // Update preview on form changes
    const form = document.getElementById('qrCodeForm');
    const previewClassName = document.getElementById('previewClassName');
    const previewDate = document.getElementById('previewDate');
    const previewTime = document.getElementById('previewTime');
    const previewDuration = document.getElementById('previewDuration');
    const previewStatus = document.getElementById('previewStatus');
    
    function updatePreview() {
        // Update class name
        const classSelect = form.querySelector('[name="class_id"]');
        if (classSelect && classSelect.selectedOptions[0]) {
            const text = classSelect.selectedOptions[0].text;
            previewClassName.textContent = text.split(' - ')[0];
        }
        
        // Update date
        const dateValue = form.querySelector('[name="date"]');
        if (dateValue && dateValue.value) {
            const date = new Date(dateValue.value);
            previewDate.textContent = date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }
        
        // Update time
        const startTime = form.querySelector('[name="start_time"]');
        const endTime = form.querySelector('[name="end_time"]');
        if (startTime && endTime && startTime.value && endTime.value) {
            previewTime.textContent = `${startTime.value} - ${endTime.value}`;
        }
        
        // Update duration
        const duration = form.querySelector('[name="duration_minutes"]');
        if (duration && duration.value) {
            previewDuration.textContent = `${duration.value} menit`;
        }
        
        // Update status
        const statusSelect = form.querySelector('[name="is_active"]');
        if (statusSelect) {
            const isActive = statusSelect.value === '1';
            previewStatus.textContent = isActive ? 'AKTIF' : 'NONAKTIF';
            previewStatus.className = `badge bg-${isActive ? 'success' : 'danger'}`;
        }
    }
    
    // Attach event listeners
    const fields = ['class_id', 'date', 'start_time', 'end_time', 'duration_minutes', 'is_active'];
    fields.forEach(field => {
        const element = form.querySelector(`[name="${field}"]`);
        if (element) {
            element.addEventListener('change', updatePreview);
            element.addEventListener('input', updatePreview);
        }
    });
    
    // Form validation
    form.addEventListener('submit', function(e) {
        const startTime = document.querySelector('[name="start_time"]').value;
        const endTime = document.querySelector('[name="end_time"]').value;
        
        if (startTime && endTime && startTime >= endTime) {
            e.preventDefault();
            alert('Waktu selesai harus setelah waktu mulai!');
            return false;
        }
        
        // Validate location if restricted
        const locationRestricted = document.getElementById('locationRestricted');
        if (locationRestricted && locationRestricted.checked) {
            const lat = document.querySelector('[name="latitude"]').value;
            const lng = document.querySelector('[name="longitude"]').value;
            const radius = document.querySelector('[name="radius"]').value;
            
            if (!lat || !lng) {
                e.preventDefault();
                alert('Mohon isi Latitude dan Longitude untuk pembatasan lokasi!');
                return false;
            }
            
            if (lat < -90 || lat > 90) {
                e.preventDefault();
                alert('Latitude harus antara -90 dan 90');
                return false;
            }
            
            if (lng < -180 || lng > 180) {
                e.preventDefault();
                alert('Longitude harus antara -180 dan 180');
                return false;
            }
            
            if (!radius || radius < 10 || radius > 1000) {
                e.preventDefault();
                alert('Radius harus antara 10 dan 1000 meter');
                return false;
            }
        }
        
        return true;
    });
});

function getCurrentLocation() {
    if (navigator.geolocation) {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mendapatkan lokasi...';
        btn.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const latitudeInput = document.querySelector('[name="latitude"]');
                const longitudeInput = document.querySelector('[name="longitude"]');
                
                if (latitudeInput && longitudeInput) {
                    latitudeInput.value = position.coords.latitude.toFixed(6);
                    longitudeInput.value = position.coords.longitude.toFixed(6);
                    
                    // Trigger change event to update preview if needed
                    latitudeInput.dispatchEvent(new Event('change'));
                    longitudeInput.dispatchEvent(new Event('change'));
                    
                    alert('✓ Lokasi berhasil diambil!\nLatitude: ' + latitudeInput.value + '\nLongitude: ' + longitudeInput.value);
                }
                
                btn.innerHTML = originalText;
                btn.disabled = false;
            },
            function(error) {
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
                btn.innerHTML = originalText;
                btn.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
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
.form-switch .form-check-input {
    width: 2.5em;
    height: 1.25em;
    cursor: pointer;
}
#previewImage {
    transition: all 0.3s ease;
}
.bi {
    vertical-align: middle;
}
</style>
@endsection