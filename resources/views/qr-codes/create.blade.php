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
                            <button type="submit" class="btn btn-primary">
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
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>Generate Cepat
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($classes as $class)
                    <div class="d-grid gap-2 mb-2">
                        <button class="btn btn-outline-primary quick-generate-btn" 
                                data-class-id="{{ $class->id }}"
                                data-class-name="{{ $class->class_name }}">
                            <i class="fas fa-qrcode me-2"></i>{{ $class->class_name }} (30 menit)
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Perhatian:</h6>
                        <ul class="mb-0">
                            <li>QR Code hanya aktif pada tanggal dan waktu yang ditentukan</li>
                            <li>Siswa hanya dapat melakukan absensi 1 kali per QR Code</li>
                            <li>QR Code akan otomatis dinonaktifkan setelah waktu berakhir</li>
                        </ul>
                    </div>
                </div>
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
    
    // Toggle location fields
    if (locationCheckbox) {
        locationCheckbox.addEventListener('change', function() {
            if (this.checked) {
                locationFields.style.display = 'block';
                // Set required attribute for location fields
                document.getElementById('latitude').required = true;
                document.getElementById('longitude').required = true;
                document.getElementById('radius').required = true;
            } else {
                locationFields.style.display = 'none';
                // Remove required attribute
                document.getElementById('latitude').required = false;
                document.getElementById('longitude').required = false;
                document.getElementById('radius').required = false;
            }
        });
    }
    
    // Quick generate buttons
    document.querySelectorAll('.quick-generate-btn').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.dataset.classId;
            const className = this.dataset.className;
            
            if (confirm(`Generate QR Code untuk kelas ${className} selama 30 menit?`)) {
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
                    if (data.success) {
                        alert('QR Code berhasil dibuat!');
                        if (data.data.realtime_url) {
                            window.location.href = data.data.realtime_url;
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan.');
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
            
            return true;
        });
    }
});
</script>
@endpush