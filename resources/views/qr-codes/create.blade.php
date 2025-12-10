@extends('layouts.app')

@section('title', 'Buat QR Code Baru')

@section('content')
<!-- Quick Generate Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-bolt text-warning me-2"></i>Generate Cepat untuk Hari Ini
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($classes as $class)
            <div class="col-md-4 mb-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h6 class="card-title">{{ $class->class_name }}</h6>
                        <p class="card-text text-muted small mb-2">
                            {{ $class->students()->count() }} siswa
                        </p>
                        <button class="btn btn-primary quick-generate-class" 
                                data-class-id="{{ $class->id }}">
                            <i class="fas fa-qrcode me-1"></i> QR 30 Menit
                        </button>
                        <button class="btn btn-outline-primary mt-2 quick-generate-custom" 
                                data-class-id="{{ $class->id }}"
                                data-class-name="{{ $class->class_name }}">
                            <i class="fas fa-cog me-1"></i> Custom
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal untuk custom QR Code -->
<div class="modal fade" id="customQrModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate QR Code Custom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customQrForm">
                    @csrf
                    <input type="hidden" name="class_id" id="customClassId">
                    
                    <div class="mb-3">
                        <label class="form-label">Durasi (menit)</label>
                        <input type="number" name="duration_minutes" class="form-control" 
                               value="30" min="5" max="240" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="Contoh: Ujian Tengah Semester"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="generateCustomQr">Generate</button>
            </div>
        </div>
    </div>
</div>
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
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Preview</h5>
                </div>
                <div class="card-body text-center">
                    <div id="qrCodePreview">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Preview akan muncul setelah mengisi form
                        </div>
                        
                        <div id="previewContent" style="display: none;">
                            <div class="mb-3">
                                <div id="qrImageContainer">
                                    <!-- QR Code image will be inserted here -->
                                </div>
                            </div>
                            
                            <div class="text-start">
                                <p><strong>Kelas:</strong> <span id="previewClassName">-</span></p>
                                <p><strong>Tanggal:</strong> <span id="previewDate">-</span></p>
                                <p><strong>Waktu:</strong> <span id="previewTime">-</span></p>
                                <p><strong>Durasi:</strong> <span id="previewDuration">-</span></p>
                                <p><strong>Total Siswa:</strong> <span id="previewStudentCount">-</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
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

@push('styles')
<style>
#qrImageContainer {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

#qrImageContainer img {
    max-width: 200px;
    height: auto;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('qrCodeForm');
    const locationCheckbox = document.getElementById('location_restricted');
    const locationFields = document.getElementById('location_fields');
    
    // Toggle location fields
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
    
    // Preview functionality
    const previewElements = {
        className: document.getElementById('previewClassName'),
        date: document.getElementById('previewDate'),
        time: document.getElementById('previewTime'),
        duration: document.getElementById('previewDuration'),
        studentCount: document.getElementById('previewStudentCount'),
        qrImageContainer: document.getElementById('qrImageContainer'),
        previewContent: document.getElementById('previewContent')
    };
    
    // Generate preview data
    async function generatePreview() {
        const classId = document.getElementById('class_id').value;
        const date = document.getElementById('date').value;
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const duration = document.getElementById('duration_minutes').value;
        
        // Validate required fields
        if (!classId || !date || !startTime || !endTime) {
            previewElements.previewContent.style.display = 'none';
            return;
        }
        
        try {
            // Get class info
            const classResponse = await fetch(`/api/classes/${classId}/student-count`);
            const classData = await classResponse.json();
            
            // Format date
            const dateObj = new Date(date);
            const formattedDate = dateObj.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // Get class name
            const classSelect = document.getElementById('class_id');
            const selectedOption = classSelect.options[classSelect.selectedIndex];
            const className = selectedOption.text.split(' (')[0];
            
            // Update preview text
            previewElements.className.textContent = className;
            previewElements.date.textContent = formattedDate;
            previewElements.time.textContent = `${startTime} - ${endTime}`;
            previewElements.duration.textContent = `${duration} menit`;
            previewElements.studentCount.textContent = classData.count || 0;
            
            // Generate temporary QR code image
            generateQrImage(classId, date, startTime, endTime);
            
            // Show preview
            previewElements.previewContent.style.display = 'block';
            
        } catch (error) {
            console.error('Error generating preview:', error);
            previewElements.previewContent.style.display = 'none';
        }
    }
    
    // Generate QR image using a simple approach
    function generateQrImage(classId, date, startTime, endTime) {
        // Generate a temporary code for preview
        const tempCode = 'PREVIEW-' + Date.now();
        
        // Create a simple QR code using a service (or you can use a library)
        // For now, we'll use a placeholder
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(tempCode)}`;
        
        // Clear previous image
        previewElements.qrImageContainer.innerHTML = '';
        
        // Create image element
        const img = document.createElement('img');
        img.src = qrUrl;
        img.alt = 'QR Code Preview';
        img.className = 'img-fluid';
        
        previewElements.qrImageContainer.appendChild(img);
    }
    
    // Event listeners for form changes
    ['class_id', 'date', 'start_time', 'end_time', 'duration_minutes'].forEach(fieldId => {
        document.getElementById(fieldId).addEventListener('change', generatePreview);
        document.getElementById(fieldId).addEventListener('input', generatePreview);
    });
    
    // Initial preview if form has values
    setTimeout(generatePreview, 500);
    
    // Form validation for time
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
});
// Quick generate 30 minutes
document.querySelectorAll('.quick-generate-class').forEach(button => {
    button.addEventListener('click', function() {
        const classId = this.dataset.classId;
        
        fetch('/qr-codes/generate-for-class', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                class_id: classId,
                date: new Date().toISOString().split('T')[0],
                start_time: new Date().toTimeString().split(' ')[0].substring(0, 5),
                end_time: new Date(Date.now() + 30 * 60000).toTimeString().split(' ')[0].substring(0, 5),
                duration_minutes: 30
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('QR Code berhasil dibuat!');
                window.location.href = data.data.realtime_url;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan.');
        });
    });
});

// Custom generate
document.querySelectorAll('.quick-generate-custom').forEach(button => {
    button.addEventListener('click', function() {
        const classId = this.dataset.classId;
        const className = this.dataset.className;
        
        document.getElementById('customClassId').value = classId;
        document.getElementById('customQrForm').querySelector('[name="notes"]').value = 
            'Absensi kelas ' + className;
        
        const modal = new bootstrap.Modal(document.getElementById('customQrModal'));
        modal.show();
    });
});

// Submit custom form
document.getElementById('generateCustomQr').addEventListener('click', function() {
    const form = document.getElementById('customQrForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Add current time
    const now = new Date();
    data.date = now.toISOString().split('T')[0];
    data.start_time = now.toTimeString().split(' ')[0].substring(0, 5);
    
    // Calculate end time
    const duration = parseInt(data.duration_minutes);
    const endTime = new Date(now.getTime() + duration * 60000);
    data.end_time = endTime.toTimeString().split(' ')[0].substring(0, 5);
    
    fetch('/qr-codes/generate-for-class', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('QR Code berhasil dibuat!');
            bootstrap.Modal.getInstance(document.getElementById('customQrModal')).hide();
            window.location.href = data.data.realtime_url;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan.');
    });
});
</script>
@endpush