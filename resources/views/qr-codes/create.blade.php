@extends('layouts.app')

@section('title', 'Buat QR Code Baru')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Buat QR Code Baru</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Buat QR Code untuk absensi kelas
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('qr-codes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Hapus notifikasi error bawaan, akan diganti SweetAlert -->

    <div class="row g-3 g-md-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-qr-code me-2 text-primary"></i>
                        Form Buat QR Code
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('qr-codes.store') }}" method="POST" id="qrCodeForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select name="class_id" id="class_id" class="form-select" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->class_name }} ({{ $class->class_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="date" id="date" class="form-control" 
                                           value="{{ old('date', $defaults['date']) }}" 
                                           min="{{ now()->format('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" id="start_time" class="form-control" 
                                           value="{{ old('start_time', $defaults['start_time']) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time" class="form-control" 
                                           value="{{ old('end_time', $defaults['end_time']) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                                    <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" 
                                           value="{{ old('duration_minutes', $defaults['duration_minutes']) }}" 
                                           min="1" max="1440" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location Restriction Checkbox -->
<div class="mb-3">
    <div class="form-check">
        <input type="checkbox" name="location_restricted" id="location_restricted" 
               class="form-check-input" value="1"
               {{ old('location_restricted') ? 'checked' : '' }}>
        <label for="location_restricted" class="form-check-label">
            <i class="bi bi-geo-alt me-1"></i> Batasi absensi berdasarkan lokasi
        </label>
    </div>
</div>

<!-- Location Fields (hidden by default) -->
<div id="location_fields" class="location-fields mb-3" style="display: none;">
    <div class="card bg-light border-0">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-geo-alt text-primary me-2"></i>Informasi Lokasi</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="number" step="any" name="latitude" id="latitude" 
                           class="form-control" placeholder="-6.123456"
                           value="{{ old('latitude') }}">
                    <small class="text-muted">Contoh: -6.123456</small>
                </div>
                <div class="col-md-4">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="number" step="any" name="longitude" id="longitude" 
                           class="form-control" placeholder="106.123456"
                           value="{{ old('longitude') }}">
                    <small class="text-muted">Contoh: 106.123456</small>
                </div>
                <div class="col-md-4">
                    <label for="radius" class="form-label">Radius (meter)</label>
                    <input type="number" name="radius" id="radius" 
                           class="form-control" placeholder="100"
                           value="{{ old('radius', 100) }}">
                    <small class="text-muted">Min: 10m, Max: 1000m</small>
                </div>
            </div>
            <div class="mt-3">
                <button type="button" id="getCurrentLocation" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-geo-alt me-2"></i> Gunakan Lokasi Saya
                </button>
            </div>
        </div>
    </div>
</div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (opsional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2" 
                                      placeholder="Tambahkan catatan untuk QR Code ini...">{{ old('notes') }}</textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Informasi:</strong> QR Code akan otomatis aktif pada tanggal dan waktu yang ditentukan.
                        </div>
                        
                        <div class="d-flex justify-content-between gap-3 pt-3 border-top">
                            <a href="{{ route('qr-codes.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-qr-code me-2"></i>Generate QR Code
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Quick Generate Section -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bolt me-2"></i>Generate Cepat (30 menit)
                    </h5>
                </div>
                <div class="card-body">
                    @if($classes->count() > 0)
                        <div class="d-grid gap-2">
                            @foreach($classes as $class)
                            <button class="btn btn-outline-primary quick-generate-btn" 
                                    data-class-id="{{ $class->id }}"
                                    data-class-name="{{ $class->class_name }}">
                                <i class="bi bi-qr-code me-2"></i>{{ $class->class_name }}
                            </button>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Belum ada kelas yang tersedia. Silakan buat kelas terlebih dahulu.
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Information Card -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Informasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>Perhatian:</h6>
                        <ul class="mb-0 small">
                            <li>QR Code hanya aktif pada tanggal dan waktu yang ditentukan</li>
                            <li>Siswa hanya dapat melakukan absensi 1 kali per QR Code</li>
                            <li>QR Code akan otomatis dinonaktifkan setelah waktu berakhir</li>
                            <li>Pastikan waktu selesai setelah waktu mulai</li>
                        </ul>
                    </div>
                    <hr>
                    <h6><i class="bi bi-lightbulb text-warning me-2"></i>Tips:</h6>
                    <ul class="small mb-0">
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
                <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="mb-1">Memproses QR Code...</h6>
                <p class="text-muted small mb-0">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>
</div>

<style>
.location-fields {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* CSS Variables */
:root {
    --primary: #4f46e5;
    --primary-light: #e0e7ff;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --transition: all 0.2s ease;
}

/* Page Header */
.page-header {
    margin-bottom: 1.5rem;
}

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

/* Card */
.card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.card-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.875rem 1rem;
}

.card-title {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    font-size: 0.938rem;
}

.card-body {
    padding: 1rem;
}

/* Form Styles */
.form-label {
    font-weight: 500;
    font-size: 0.813rem;
    color: #374151;
    margin-bottom: 0.375rem;
}

.form-control,
.form-select {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    font-size: 0.813rem;
    transition: var(--transition);
}

.form-control:focus,
.form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

textarea.form-control {
    resize: vertical;
}

.form-check-input {
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.form-check-label {
    cursor: pointer;
}

/* Location Fields */
.location-fields {
    background: #f8fafc;
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 1rem;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
}

.btn-sm {
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
}

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
}

.btn-outline-primary {
    border-color: #e5e7eb;
    color: #4f46e5;
    background: white;
}

.btn-outline-primary:hover {
    background: #4f46e5;
    border-color: #4f46e5;
    color: white;
}

.btn-outline-secondary {
    border-color: #e5e7eb;
    color: #6b7280;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #374151;
}

.btn-outline-info {
    border-color: #e5e7eb;
    color: #3b82f6;
    background: white;
}

.btn-outline-info:hover {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-info {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
}

.alert-warning {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

/* Modal */
.modal-content {
    background: white;
    border: none;
    border-radius: var(--border-radius);
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

.modal-body {
    padding: 1.25rem;
}

/* Colors */
.bg-warning {
    background: #f59e0b !important;
}

.bg-info {
    background: #3b82f6 !important;
}

.text-primary {
    color: #4f46e5 !important;
}

.text-muted {
    color: #6b7280 !important;
}

.text-danger {
    color: #ef4444 !important;
}

/* Border */
.border-top {
    border-top: 1px solid #e5e7eb !important;
}

/* Responsive */
@media (min-width: 992px) {
    .card-body {
        padding: 1.25rem;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .page-icon-large {
        width: 44px;
        height: 44px;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .location-fields .row {
        flex-direction: column;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeIn 0.3s ease forwards;
}
</style>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// SweetAlert Modern
const CustomSwal = {
    // Success Dialog
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
    
    // Error Dialog
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
    
    // Warning Dialog
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
    
    // Confirm Dialog untuk Generate Cepat
    confirmQuickGenerate: (className) => {
        return Swal.fire({
            title: 'Generate QR Code Cepat',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-qr-code" style="font-size: 3.5rem; color: #4f46e5;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">${className}</h6>
                    <p class="mb-3">Apakah Anda yakin ingin membuat QR Code untuk kelas ini?</p>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        QR Code akan aktif selama 30 menit dari waktu sekarang
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Ya, Generate!',
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
    
    // Confirm Dialog untuk Validasi Form
    confirmValidation: (message) => {
        return Swal.fire({
            title: 'Validasi Gagal',
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
    
    // Loading Dialog
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
    
    // Close Loading
    closeLoading: () => {
        Swal.close();
    },
    
    // Info Toast
    showInfoToast: (message) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: 'info',
            title: message,
            background: '#ffffff',
            iconColor: '#3b82f6'
        });
    },
    
    // Success Toast
    showSuccessToast: (message) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: 'success',
            title: message,
            background: '#ffffff',
            iconColor: '#10b981'
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
    
    .custom-swal-popup .alert-info {
        background: #cffafe;
        border: 1px solid #bae6fd;
        color: #155e75;
    }
    
    .swal2-toast {
        border-radius: 12px !important;
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

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('qrCodeForm');
    const locationCheckbox = document.getElementById('location_restricted');
    const locationFields = document.getElementById('location_fields');
    const submitBtn = document.getElementById('submitBtn');
    
    // Tampilkan error dari Laravel dengan SweetAlert
    @if($errors->any())
        let errorMessage = '';
        @foreach($errors->all() as $error)
            errorMessage += '• {{ addslashes($error) }}\n';
        @endforeach
        CustomSwal.showError('Validasi Gagal', errorMessage);
    @endif
    
    // Tampilkan success dari session
    @if(session('success'))
        CustomSwal.showSuccess('Berhasil!', '{{ session('success') }}');
    @endif
    
    @if(session('error'))
        CustomSwal.showError('Gagal!', '{{ session('error') }}');
    @endif
    
    // Toggle location fields
    if (locationCheckbox) {
        locationCheckbox.addEventListener('change', function() {
            if (this.checked) {
                locationFields.style.display = 'block';
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const radiusInput = document.getElementById('radius');
                
                if (latInput) latInput.required = true;
                if (lngInput) lngInput.required = true;
                if (radiusInput) radiusInput.required = true;
            } else {
                locationFields.style.display = 'none';
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
                CustomSwal.showLoading('Mendapatkan Lokasi...', 'Mohon izinkan akses lokasi');
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        CustomSwal.closeLoading();
                        document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                        document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
                        CustomSwal.showSuccessToast('Lokasi berhasil didapatkan!');
                    },
                    function(error) {
                        CustomSwal.closeLoading();
                        let errorMessage = '';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Izin lokasi ditolak. Silakan izinkan akses lokasi.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Informasi lokasi tidak tersedia.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Waktu permintaan habis. Silakan coba lagi.';
                                break;
                            default:
                                errorMessage = error.message;
                        }
                        CustomSwal.showError('Gagal Mendapatkan Lokasi', errorMessage);
                    }
                );
            } else {
                CustomSwal.showError('Tidak Didukung', 'Browser Anda tidak mendukung geolocation.');
            }
        });
    }
    
    // Quick generate buttons
    document.querySelectorAll('.quick-generate-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const classId = this.dataset.classId;
            const className = this.dataset.className;
            
            const result = await CustomSwal.confirmQuickGenerate(className);
            
            if (result.isConfirmed) {
                CustomSwal.showLoading('Membuat QR Code...', 'Mohon tunggu sebentar');
                
                try {
                    const response = await fetch(`/attendance/quick-generate-qr/${classId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    CustomSwal.closeLoading();
                    
                    if (data.success) {
                        CustomSwal.showSuccess('Berhasil!', 'QR Code berhasil dibuat!', data.data && data.data.id ? `/qr-codes/${data.data.id}` : null);
                        if (!data.data || !data.data.id) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        }
                    } else {
                        CustomSwal.showError('Gagal!', data.message || 'Terjadi kesalahan saat membuat QR Code');
                    }
                } catch (error) {
                    CustomSwal.closeLoading();
                    console.error('Error:', error);
                    CustomSwal.showError('Error!', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                }
            }
        });
    });
    
    // Form validation for time
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const classId = document.getElementById('class_id').value;
            const date = document.getElementById('date').value;
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            // Validasi kelas
            if (!classId) {
                await CustomSwal.confirmValidation('Silakan pilih kelas terlebih dahulu.');
                return false;
            }
            
            // Validasi tanggal
            if (!date) {
                await CustomSwal.confirmValidation('Silakan pilih tanggal terlebih dahulu.');
                return false;
            }
            
            // Validasi waktu
            if (!startTime || !endTime) {
                await CustomSwal.confirmValidation('Silakan isi waktu mulai dan waktu selesai.');
                return false;
            }
            
            if (startTime && endTime) {
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);
                
                if (end <= start) {
                    await CustomSwal.confirmValidation('Waktu selesai harus setelah waktu mulai.');
                    return false;
                }
            }
            
            // Validasi lokasi jika dibatasi
            if (locationCheckbox && locationCheckbox.checked) {
                const lat = document.getElementById('latitude').value;
                const lng = document.getElementById('longitude').value;
                const radius = document.getElementById('radius').value;
                
                if (!lat || !lng) {
                    await CustomSwal.confirmValidation('Mohon isi Latitude dan Longitude untuk pembatasan lokasi.');
                    return false;
                }
                
                if (lat < -90 || lat > 90) {
                    await CustomSwal.confirmValidation('Latitude harus antara -90 dan 90.');
                    return false;
                }
                
                if (lng < -180 || lng > 180) {
                    await CustomSwal.confirmValidation('Longitude harus antara -180 dan 180.');
                    return false;
                }
                
                if (!radius || radius < 10 || radius > 1000) {
                    await CustomSwal.confirmValidation('Radius harus antara 10 dan 1000 meter.');
                    return false;
                }
            }
            
            // Submit form
            CustomSwal.showLoading('Membuat QR Code...', 'Mohon tunggu sebentar');
            submitBtn.disabled = true;
            
            // Submit form via AJAX untuk SweetAlert
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                CustomSwal.closeLoading();
                submitBtn.disabled = false;
                
                if (data.success) {
                    CustomSwal.showSuccess('Berhasil!', data.message || 'QR Code berhasil dibuat!', data.redirect_url || `/qr-codes/${data.data.id}`);
                } else {
                    let errorMsg = data.message || 'Terjadi kesalahan';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    }
                    CustomSwal.showError('Gagal!', errorMsg);
                }
            } catch (error) {
                CustomSwal.closeLoading();
                submitBtn.disabled = false;
                console.error('Error:', error);
                CustomSwal.showError('Error!', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
            }
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

// Toggle location fields
document.getElementById('location_restricted').addEventListener('change', function() {
    const locationFields = document.getElementById('location_fields');
    locationFields.style.display = this.checked ? 'block' : 'none';
});

// Get current location
document.getElementById('getCurrentLocation').addEventListener('click', function() {
    if (navigator.geolocation) {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mendapatkan lokasi...';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
                alert('Lokasi berhasil didapatkan!');
                document.getElementById('getCurrentLocation').disabled = false;
                document.getElementById('getCurrentLocation').innerHTML = '<i class="bi bi-geo-alt me-2"></i> Gunakan Lokasi Saya';
            },
            function(error) {
                let errorMsg = 'Gagal mendapatkan lokasi: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg += 'Izin lokasi ditolak.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg += 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        errorMsg += 'Waktu permintaan habis.';
                        break;
                    default:
                        errorMsg += error.message;
                }
                alert(errorMsg);
                document.getElementById('getCurrentLocation').disabled = false;
                document.getElementById('getCurrentLocation').innerHTML = '<i class="bi bi-geo-alt me-2"></i> Gunakan Lokasi Saya';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        alert('Browser Anda tidak mendukung geolocation.');
    }
});

</script>
@endsection