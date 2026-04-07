@extends('layouts.app')

@section('title', 'Absensi Kelas - ' . $class->class_name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-primary">
            <i class="fas fa-calendar-check me-2"></i>Absensi Kelas {{ $class->class_name }}
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('classes.show', $class) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Kelas
            </a>
            <a href="{{ route('attendance.teacher.export', $class) }}" class="btn btn-info">
                <i class="fas fa-download me-2"></i>Export Data
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Tanggal Absensi -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-calendar-alt me-2"></i>Absensi Hari Ini
            </h6>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <form method="GET" class="d-inline">
                        <input type="date" name="date" value="{{ $today }}" 
                               class="form-control d-inline w-auto" 
                               onchange="this.form.submit()">
                    </form>
                </div>
            </div>

            <!-- Tombol Status Cepat -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-success" onclick="markAll('present')">
                            <i class="fas fa-check-circle me-2"></i>Tandai Semua Hadir
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="markAll('late')">
                            <i class="fas fa-clock me-2"></i>Tandai Semua Terlambat
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="markAll('absent')">
                            <i class="fas fa-times-circle me-2"></i>Tandai Semua Absen
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Absensi -->
            <form id="attendanceForm" action="{{ route('attendance.mark') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $today }}">
                <input type="hidden" name="class_id" value="{{ $class->id }}">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Nama Siswa</th>
                                <th>NIS</th>
                                <th width="150">Status</th>
                                <th width="200">Keterangan</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            @php
                                $attendance = $todayAttendance[$student->id] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle-sm bg-primary text-white me-2">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        {{ $student->name }}
                                    </div>
                                </td>
                                <td>{{ $student->nis_nip ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" 
                                               class="btn-check status-radio" 
                                               name="status[{{ $student->id }}]" 
                                               id="present_{{ $student->id }}" 
                                               value="present"
                                               {{ ($attendance && $attendance->status == 'present') ? 'checked' : '' }}
                                               autocomplete="off">
                                        <label class="btn btn-outline-success" for="present_{{ $student->id }}">
                                            <i class="fas fa-check"></i>
                                        </label>

                                        <input type="radio" 
                                               class="btn-check status-radio" 
                                               name="status[{{ $student->id }}]" 
                                               id="late_{{ $student->id }}" 
                                               value="late"
                                               {{ ($attendance && $attendance->status == 'late') ? 'checked' : '' }}
                                               autocomplete="off">
                                        <label class="btn btn-outline-warning" for="late_{{ $student->id }}">
                                            <i class="fas fa-clock"></i>
                                        </label>

                                        <input type="radio" 
                                               class="btn-check status-radio" 
                                               name="status[{{ $student->id }}]" 
                                               id="absent_{{ $student->id }}" 
                                               value="absent"
                                               {{ ($attendance && $attendance->status == 'absent') ? 'checked' : '' }}
                                               autocomplete="off">
                                        <label class="btn btn-outline-danger" for="absent_{{ $student->id }}">
                                            <i class="fas fa-times"></i>
                                        </label>

                                        <input type="radio" 
                                               class="btn-check status-radio" 
                                               name="status[{{ $student->id }}]" 
                                               id="excused_{{ $student->id }}" 
                                               value="excused"
                                               {{ ($attendance && $attendance->status == 'excused') ? 'checked' : '' }}
                                               autocomplete="off">
                                        <label class="btn btn-outline-info" for="excused_{{ $student->id }}">
                                            <i class="fas fa-user-clock"></i>
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
                                <td>
                                    <button type="button" 
                                            class="btn btn-primary btn-sm save-btn"
                                            onclick="saveAttendance({{ $student->id }})">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-center">
                    <button type="button" class="btn btn-primary btn-lg" onclick="saveAll()">
                        <i class="fas fa-save me-2"></i>Simpan Semua Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Hadir
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="present-count">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Terlambat
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="late-count">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Absen
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="absent-count">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Izin
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="excused-count">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
        alert('Pilih status absensi terlebih dahulu');
        return;
    }

    const formData = new FormData();
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    formData.append('student_id', studentId);
    formData.append('class_id', {{ $class->id }});
    formData.append('date', '{{ $today }}');
    formData.append('status', status.value);
    formData.append('notes', notes.value);

    fetch('{{ route("attendance.mark") }}', {
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
    });
}

// Simpan semua absensi
function saveAll() {
    const students = @json($students->pluck('id'));
    let saved = 0;
    let errors = 0;

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

            fetch('{{ route("attendance.mark") }}', {
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
                
                // Jika sudah semua
                if (saved + errors === students.length) {
                    showToast('success', `Berhasil menyimpan ${saved} dari ${students.length} absensi`);
                }
            });
        } else {
            errors++;
        }
    });

    if (errors > 0) {
        showToast('warning', `${errors} siswa belum memiliki status absensi`);
    }
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
        'excused': 'Izin'
    };
    return names[status] || status;
}

function showToast(type, message) {
    // Implementasi toast notification sederhana
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>

<style>
.avatar-circle-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.btn-check:checked + .btn-outline-success {
    background-color: #1cc88a;
    color: white;
}

.btn-check:checked + .btn-outline-warning {
    background-color: #f6c23e;
    color: white;
}

.btn-check:checked + .btn-outline-danger {
    background-color: #e74a3b;
    color: white;
}

.btn-check:checked + .btn-outline-info {
    background-color: #36b9cc;
    color: white;
}

.notes-input {
    font-size: 0.875rem;
}
</style>
@endsection