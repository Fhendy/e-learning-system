@extends('layouts.app')

@section('title', 'Tambah Absensi Manual')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-pencil-square-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Tambah Absensi Manual</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Input absensi siswa secara manual
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('attendance.teacher.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Hapus notifikasi session bawaan, akan diganti SweetAlert -->

    <div class="row g-3 g-md-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>
                        Form Tambah Absensi Manual
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.teacher.manual.store') }}" method="POST" id="manualAttendanceForm">
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
                                    <label for="attendance_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="attendance_date" id="attendance_date" class="form-control" 
                                           value="{{ old('attendance_date', $defaultDate) }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                                    <select name="student_id" id="student_id" class="form-select" required disabled>
                                        <option value="">Pilih kelas terlebih dahulu</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="">Pilih Status</option>
                                        <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                                        <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                        <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                        <option value="sick" {{ old('status') == 'sick' ? 'selected' : '' }}>Sakit</option>
                                        <option value="permission" {{ old('status') == 'permission' ? 'selected' : '' }}>Izin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="checked_in_at" class="form-label">Waktu Absen</label>
                                    <input type="time" name="checked_in_at" id="checked_in_at" class="form-control" 
                                           value="{{ old('checked_in_at', $defaultTime) }}">
                                    <div class="text-muted small mt-1">Kosongkan untuk waktu saat ini</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Catatan</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2" 
                                              placeholder="Masukkan catatan jika perlu">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Informasi:</strong> Absensi manual akan menggantikan absensi yang sudah ada pada tanggal yang sama.
                        </div>
                        
                        <div class="d-flex justify-content-between gap-3 pt-3 border-top">
                            <a href="{{ route('attendance.teacher.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" id="submit_and_new_btn" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan & Tambah Lagi
                                </button>
                                <button type="button" id="submit_btn" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle me-2"></i>Petunjuk:</h6>
                        <ul class="mb-0 small">
                            <li>Pilih kelas terlebih dahulu untuk memuat daftar siswa</li>
                            <li>Status "Hadir" dan "Terlambat" akan otomatis mencatat waktu saat ini jika tidak diisi</li>
                            <li>Absensi manual akan menggantikan absensi yang sudah ada pada tanggal yang sama</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>Perhatian:</h6>
                        <p class="mb-0 small">Pastikan data yang diinput sudah benar. Perubahan absensi manual akan tercatat dalam sistem.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
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

.form-control:disabled,
.form-select:disabled {
    background-color: #f8fafc;
    cursor: not-allowed;
}

textarea.form-control {
    resize: vertical;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
}

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
}

.btn-success {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-success:hover {
    background: #0d9e70;
    border-color: #0d9e70;
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

.alert-success {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}

.alert-danger {
    background: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
}

/* Colors */
.text-primary { color: #4f46e5 !important; }
.text-muted { color: #6b7280 !important; }
.text-danger { color: #ef4444 !important; }

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
    
    .d-flex.gap-2 {
        flex-direction: column;
    }
    
    .d-flex.gap-2 .btn {
        width: 100%;
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

<script>
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
`;

document.head.appendChild(swalStyles);

document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    const studentSelect = document.getElementById('student_id');
    const statusSelect = document.getElementById('status');
    const timeInput = document.getElementById('checked_in_at');
    const form = document.getElementById('manualAttendanceForm');
    const submitBtn = document.getElementById('submit_btn');
    const submitAndNewBtn = document.getElementById('submit_and_new_btn');
    
    // Tampilkan error dari session dengan SweetAlert
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
    
    // Load students when class is selected
    function loadStudents() {
        const classId = classSelect.value;
        
        if (!classId) {
            studentSelect.innerHTML = '<option value="">Pilih kelas terlebih dahulu</option>';
            studentSelect.disabled = true;
            return;
        }
        
        studentSelect.innerHTML = '<option value="">Memuat siswa...</option>';
        studentSelect.disabled = true;
        
        fetch(`/api/classes/${classId}/students`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';
                if (data && data.length > 0) {
                    data.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.id;
                        option.textContent = student.text || student.name;
                        studentSelect.appendChild(option);
                    });
                    studentSelect.disabled = false;
                } else {
                    studentSelect.innerHTML = '<option value="">Tidak ada siswa di kelas ini</option>';
                    studentSelect.disabled = true;
                }
                
                // Set old value if exists
                const oldValue = "{{ old('student_id') }}";
                if (oldValue && oldValue !== '') {
                    studentSelect.value = oldValue;
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                studentSelect.innerHTML = '<option value="">Gagal memuat siswa</option>';
                studentSelect.disabled = true;
                CustomSwal.showError('Error!', 'Gagal memuat daftar siswa. Silakan coba lagi.');
            });
    }
    
    // Event listener for class change
    if (classSelect) {
        classSelect.addEventListener('change', loadStudents);
        
        // Load students if class is already selected
        if (classSelect.value) {
            loadStudents();
        }
    }
    
    // Auto-fill time for present/late status
    if (statusSelect && timeInput) {
        statusSelect.addEventListener('change', function() {
            if (!timeInput.value && (this.value === 'present' || this.value === 'late')) {
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                timeInput.value = `${hours}:${minutes}`;
            }
        });
    }
    
    // Function to submit form with AJAX
    async function submitForm(submitAndNew = false) {
        const studentId = studentSelect.value;
        const status = statusSelect.value;
        
        if (!studentId) {
            CustomSwal.showError('Validasi Gagal', 'Silakan pilih siswa terlebih dahulu');
            return false;
        }
        
        if (!status) {
            CustomSwal.showError('Validasi Gagal', 'Silakan pilih status absensi');
            return false;
        }
        
        // Auto-fill time if needed
        if ((status === 'present' || status === 'late') && !timeInput.value) {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            timeInput.value = `${hours}:${minutes}`;
        }
        
        // Prepare form data
        const formData = new FormData(form);
        if (submitAndNew) {
            formData.append('submit_and_new', '1');
        }
        
        CustomSwal.showLoading('Menyimpan...', 'Mohon tunggu sebentar');
        
        try {
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
            
            if (data.success) {
                if (submitAndNew) {
                    // Reset form but keep class selection
                    studentSelect.value = '';
                    statusSelect.value = '';
                    timeInput.value = '';
                    document.getElementById('notes').value = '';
                    
                    CustomSwal.showSuccess('Berhasil!', data.message);
                } else {
                    CustomSwal.showSuccess('Berhasil!', data.message, '{{ route("attendance.teacher.index") }}');
                }
            } else {
                CustomSwal.showError('Gagal!', data.message || 'Terjadi kesalahan saat menyimpan.');
            }
        } catch (error) {
            CustomSwal.closeLoading();
            console.error('Error:', error);
            CustomSwal.showError('Error!', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        }
    }
    
    // Submit button
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitForm(false);
        });
    }
    
    // Submit and new button
    if (submitAndNewBtn) {
        submitAndNewBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitForm(true);
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection