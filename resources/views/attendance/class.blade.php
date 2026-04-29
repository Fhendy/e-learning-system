@extends('layouts.app')

@section('title', 'Absensi Kelas - ' . $class->class_name)

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Absensi Kelas</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-building me-1"></i>{{ $class->class_name }}
                            <span class="mx-2">•</span>
                            <i class="bi bi-people me-1"></i>{{ $students->count() }} siswa
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('classes.show', $class) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Kelas
                </a>
                <a href="{{ route('attendance.teacher.export', $class) }}" class="btn btn-info">
                    <i class="bi bi-download me-2"></i>Export Data
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    <!-- Tanggal Absensi -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-alt me-2 text-primary"></i>
                    Absensi Hari Ini
                </h5>
                <form method="GET" class="d-flex gap-2">
                    <input type="date" name="date" value="{{ $today }}" 
                           class="form-control form-control-sm" 
                           onchange="this.form.submit()">
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
            </div>

            <!-- Tombol Status Cepat -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">
                    <i class="bi bi-check-circle me-1"></i>Hadir Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="markAll('late')">
                    <i class="bi bi-clock me-1"></i>Terlambat Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">
                    <i class="bi bi-x-circle me-1"></i>Absen Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="markAll('excused')">
                    <i class="bi bi-person-check me-1"></i>Izin Semua
                </button>
            </div>

            <!-- Form Absensi -->
            <form id="attendanceForm" action="{{ route('attendance.teacher.mark') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $today }}">
                <input type="hidden" name="class_id" value="{{ $class->id }}">

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3 ps-md-4">#</th>
                                <th>SISWA</th>
                                <th>NIS</th>
                                <th>STATUS</th>
                                <th>KETERANGAN</th>
                                <th class="text-end pe-3 pe-md-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            @php
                                $attendance = $todayAttendance[$student->id] ?? null;
                            @endphp
                            <tr>
                                <td class="ps-3 ps-md-4">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="student-avatar">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $student->name }}</div>
                                            <div class="text-muted small">{{ $student->nis_nip }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->nis_nip }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" 
                                               class="btn-check status-radio" 
                                               name="status[{{ $student->id }}]" 
                                               id="present_{{ $student->id }}" 
                                               value="present"
                                               {{ ($attendance && $attendance->status == 'present') ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success" for="present_{{ $student->id }}" title="Hadir">
                                            <i class="bi bi-check-lg"></i>
                                        </label>

                                        <input type="radio" 
                                               class="btn-check status-radio" 
                                               name="status[{{ $student->id }}]" 
                                               id="late_{{ $student->id }}" 
                                               value="late"
                                               {{ ($attendance && $attendance->status == 'late') ? 'checked' : '' }}>
                                        <label class="btn btn-outline-warning" for="late_{{ $student->id }}" title="Terlambat">
                                            <i class="bi bi-clock"></i>
                                        </label>

                                        <input type="radio" 
                                               class="btn-check status-radio" 
                                               name="status[{{ $student->id }}]" 
                                               id="absent_{{ $student->id }}" 
                                               value="absent"
                                               {{ ($attendance && $attendance->status == 'absent') ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger" for="absent_{{ $student->id }}" title="Absen">
                                            <i class="bi bi-x-lg"></i>
                                        </label>

                                        <input type="radio" 
                                               class="btn-check status-radio" 
                                               name="status[{{ $student->id }}]" 
                                               id="excused_{{ $student->id }}" 
                                               value="excused"
                                               {{ ($attendance && $attendance->status == 'excused') ? 'checked' : '' }}>
                                        <label class="btn btn-outline-info" for="excused_{{ $student->id }}" title="Izin/Sakit">
                                            <i class="bi bi-person-check"></i>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm notes-input" 
                                           name="notes[{{ $student->id }}]" 
                                           value="{{ $attendance->notes ?? '' }}" 
                                           placeholder="Keterangan (opsional)">
                                </td>
                                <td class="text-end pe-3 pe-md-4">
                                    <button type="button" 
                                            class="btn btn-sm btn-primary save-btn"
                                            onclick="saveAttendance({{ $student->id }})">
                                        <i class="bi bi-save"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-primary" onclick="saveAll()">
                        <i class="bi bi-save me-2"></i>Simpan Semua Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row g-2 g-md-3">
        <div class="col-6 col-md-3">
            <div class="stat-card stat-success">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-light text-success">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stat-value mb-0" id="present-count">0</h3>
                        <p class="stat-label mb-0">Hadir</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-warning">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-light text-warning">
                        <i class="bi bi-clock fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stat-value mb-0" id="late-count">0</h3>
                        <p class="stat-label mb-0">Terlambat</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-danger">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger-light text-danger">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stat-value mb-0" id="absent-count">0</h3>
                        <p class="stat-label mb-0">Absen</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-info">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-light text-info">
                        <i class="bi bi-person-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stat-value mb-0" id="excused-count">0</h3>
                        <p class="stat-label mb-0">Izin/Sakit</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Variables */
:root {
    --primary: #4f46e5;
    --primary-light: #e0e7ff;
    --success: #10b981;
    --success-light: #d1fae5;
    --warning: #f59e0b;
    --warning-light: #fef3c7;
    --danger: #ef4444;
    --danger-light: #fee2e2;
    --info: #3b82f6;
    --info-light: #dbeafe;
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

/* Stat Card */
.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 0.875rem;
    border: 1px solid #e5e7eb;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.stat-label {
    font-size: 0.688rem;
    color: #6b7280;
}

/* Student Avatar */
.student-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

/* Table */
.table {
    margin: 0;
}

.table thead th {
    font-weight: 600;
    font-size: 0.688rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.table tbody td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f9fafb;
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

.btn-group {
    gap: 4px;
}

.btn-outline-success {
    border-color: #e5e7eb;
    color: #10b981;
    background: white;
}

.btn-outline-success:hover {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-outline-warning {
    border-color: #e5e7eb;
    color: #f59e0b;
    background: white;
}

.btn-outline-warning:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.btn-outline-danger {
    border-color: #e5e7eb;
    color: #ef4444;
    background: white;
}

.btn-outline-danger:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
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

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
}

.btn-info {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

.btn-info:hover {
    background: #2563eb;
    border-color: #2563eb;
}

/* Radio Button Checked States */
.btn-check:checked + .btn-outline-success {
    background-color: #10b981;
    color: white;
    border-color: #10b981;
}

.btn-check:checked + .btn-outline-warning {
    background-color: #f59e0b;
    color: white;
    border-color: #f59e0b;
}

.btn-check:checked + .btn-outline-danger {
    background-color: #ef4444;
    color: white;
    border-color: #ef4444;
}

.btn-check:checked + .btn-outline-info {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

/* Form */
.form-control {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    transition: var(--transition);
}

.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

.form-control-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.688rem;
}

.notes-input {
    font-size: 0.75rem;
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

/* Colors */
.bg-success-light { background: #d1fae5; }
.bg-warning-light { background: #fef3c7; }
.bg-danger-light { background: #fee2e2; }
.bg-info-light { background: #dbeafe; }

.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }
.text-info { color: #3b82f6 !important; }
.text-muted { color: #6b7280 !important; }

/* Border */
.border-top {
    border-top: 1px solid #e5e7eb !important;
}

/* Responsive */
@media (min-width: 992px) {
    .card-body {
        padding: 1.25rem;
    }
    
    .student-avatar {
        width: 40px;
        height: 40px;
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
    
    .student-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.625rem;
    }
    
    .btn-group {
        flex-wrap: wrap;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-group {
        flex-wrap: wrap;
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

.card, .stat-card {
    animation: fadeIn 0.3s ease forwards;
}
</style>

<script>
// Hitung status awal
function updateCounts() {
    const present = document.querySelectorAll('input[value="present"]:checked').length;
    const late = document.querySelectorAll('input[value="late"]:checked').length;
    const absent = document.querySelectorAll('input[value="absent"]:checked').length;
    const excused = document.querySelectorAll('input[value="excused"]:checked').length;
    
    document.getElementById('present-count').textContent = present;
    document.getElementById('late-count').textContent = late;
    document.getElementById('absent-count').textContent = absent;
    document.getElementById('excused-count').textContent = excused;
}

// Update counts saat radio berubah
document.querySelectorAll('.status-radio').forEach(radio => {
    radio.addEventListener('change', updateCounts);
});

// Hitung awal
document.addEventListener('DOMContentLoaded', updateCounts);

// Simpan absensi per siswa
function saveAttendance(studentId) {
    const status = document.querySelector(`input[name="status[${studentId}]"]:checked`);
    const notes = document.querySelector(`input[name="notes[${studentId}]"]`);
    
    if (!status) {
        showToast('warning', 'Pilih status absensi terlebih dahulu');
        return;
    }

    const formData = new FormData();
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    formData.append('student_id', studentId);
    formData.append('class_id', {{ $class->id }});
    formData.append('date', '{{ $today }}');
    formData.append('status', status.value);
    formData.append('notes', notes ? notes.value : '');

    // Show loading on button
    const btn = document.querySelector(`button[onclick="saveAttendance(${studentId})"]`);
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;

    fetch('{{ route("attendance.teacher.mark") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Absensi berhasil disimpan');
        } else {
            showToast('error', data.error || 'Gagal menyimpan absensi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Terjadi kesalahan');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

// Simpan semua absensi
function saveAll() {
    const students = @json($students->pluck('id'));
    let saved = 0;
    let errors = 0;
    let processed = 0;

    const saveBtn = document.querySelector('button[onclick="saveAll()"]');
    const originalHtml = saveBtn.innerHTML;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    saveBtn.disabled = true;

    students.forEach(studentId => {
        const status = document.querySelector(`input[name="status[${studentId}]"]:checked`);
        const notes = document.querySelector(`input[name="notes[${studentId}]"]`);
        
        if (status) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('student_id', studentId);
            formData.append('class_id', {{ $class->id }});
            formData.append('date', '{{ $today }}');
            formData.append('status', status.value);
            formData.append('notes', notes ? notes.value : '');

            fetch('{{ route("attendance.teacher.mark") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    saved++;
                } else {
                    errors++;
                }
                processed++;
                
                if (processed === students.length) {
                    showToast('success', `Berhasil menyimpan ${saved} dari ${students.length} absensi`);
                    saveBtn.innerHTML = originalHtml;
                    saveBtn.disabled = false;
                }
            });
        } else {
            errors++;
            processed++;
            
            if (processed === students.length) {
                showToast('warning', `${errors} siswa belum memiliki status absensi`);
                saveBtn.innerHTML = originalHtml;
                saveBtn.disabled = false;
            }
        }
    });
}

// Tandai semua dengan status tertentu
function markAll(status) {
    document.querySelectorAll('.status-radio').forEach(radio => {
        if (radio.value === status) {
            radio.checked = true;
        }
    });
    updateCounts();
    showToast('info', `Semua siswa ditandai sebagai ${getStatusName(status)}`);
}

function getStatusName(status) {
    const names = {
        'present': 'Hadir',
        'late': 'Terlambat',
        'absent': 'Absen',
        'excused': 'Izin/Sakit'
    };
    return names[status] || status;
}

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '1050';
    toast.style.minWidth = '250px';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : (type === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill')} me-2"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endsection