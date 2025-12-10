{{-- resources/views/attendance/realtime.blade.php --}}
@extends('layouts.app')

@section('title', 'Monitoring Absensi Real-time')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Monitoring Absensi Real-time</h4>
                <div class="page-title-right">
                    <span class="badge bg-{{ $qrCode->is_active ? 'success' : 'danger' }}">
                        {{ $qrCode->is_active ? 'AKTIF' : 'NONAKTIF' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Info -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title">{{ $qrCode->class->class_name }}</h5>
                            <p class="card-text text-muted mb-1">
                                <i class="fas fa-calendar-alt me-2"></i>{{ $qrCode->date->format('d F Y') }}
                            </p>
                            <p class="card-text text-muted mb-1">
                                <i class="fas fa-clock me-2"></i>{{ $qrCode->formatted_time_range }}
                                ({{ $qrCode->duration_minutes }} menit)
                            </p>
                            <p class="card-text text-muted mb-0">
                                <i class="fas fa-qrcode me-2"></i>Kode: <strong>{{ $qrCode->code }}</strong>
                            </p>
                        </div>
                        <div class="text-end">
                            <div id="timeRemaining" class="display-4 text-primary mb-2">00:00</div>
                            <small class="text-muted">Sisa Waktu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($qrCode->qr_code_image)
                        <img src="{{ Storage::url($qrCode->qr_code_image) }}" 
                             alt="QR Code" 
                             class="img-fluid mb-3"
                             style="max-width: 200px;">
                    @endif
                    <div class="d-grid gap-2">
                        <a href="{{ route('qr-codes.show', $qrCode) }}" class="btn btn-outline-primary">
                            <i class="fas fa-info-circle"></i> Detail QR Code
                        </a>
                        @if($qrCode->is_active)
                        <form action="{{ route('qr-codes.deactivate', $qrCode) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-stop-circle"></i> Nonaktifkan QR
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 col-6">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">SUDAH ABSEN</h6>
                    <h2 class="mb-0">{{ $attendedStudents }}</h2>
                    <small>siswa</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">BELUM ABSEN</h6>
                    <h2 class="mb-0">{{ $totalStudents - $attendedStudents }}</h2>
                    <small>siswa</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">TOTAL SISWA</h6>
                    <h2 class="mb-0">{{ $totalStudents }}</h2>
                    <small>siswa</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="card-title">PERSENTASE</h6>
                    <h2 class="mb-0">{{ $attendancePercentage }}%</h2>
                    <small>kehadiran</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Progress Kehadiran</span>
                        <span><strong>{{ $attendedStudents }}</strong> / {{ $totalStudents }} siswa</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $attendancePercentage }}%"
                             aria-valuenow="{{ $attendancePercentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $attendancePercentage }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Kehadiran Siswa</h5>
                        <div>
                            <button id="refreshBtn" class="btn btn-primary btn-sm">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button id="exportBtn" class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Nama Siswa</th>
                                    <th width="15%">NIS/NIP</th>
                                    <th width="20%">Status</th>
                                    <th width="20%">Waktu Absen</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceTableBody">
                                @foreach($students as $student)
                                @php
                                    $attendance = $student->attendances->first();
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->nis_nip ?? '-' }}</td>
                                    <td>
                                        @if($attendance)
                                            <span class="badge bg-{{ $attendance->status_color }}">
                                                {{ $attendance->status_text }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Belum Absen</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance && $attendance->checked_in_at)
                                            {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i:s') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$attendance)
                                        <button class="btn btn-sm btn-outline-primary mark-attendance" 
                                                data-student-id="{{ $student->id }}"
                                                data-student-name="{{ $student->name }}">
                                            <i class="fas fa-check"></i> Tandai Hadir
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tandai Kehadiran Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="markAttendanceForm">
                    @csrf
                    <input type="hidden" name="qr_code_id" value="{{ $qrCode->id }}">
                    <input type="hidden" name="student_id" id="modalStudentId">
                    
                    <div class="mb-3">
                        <label class="form-label">Siswa</label>
                        <input type="text" class="form-control" id="modalStudentName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="sick">Sakit</option>
                            <option value="permission">Izin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Waktu</label>
                        <input type="time" name="checked_in_at" class="form-control" 
                               value="{{ now()->format('H:i') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveAttendanceBtn">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let refreshInterval;
let qrCodeEndTime = new Date('{{ $qrCode->full_end_datetime }}').getTime();

function updateTimeRemaining() {
    const now = new Date().getTime();
    const timeLeft = qrCodeEndTime - now;
    
    if (timeLeft <= 0) {
        document.getElementById('timeRemaining').textContent = '00:00';
        clearInterval(refreshInterval);
        location.reload();
        return;
    }
    
    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
    
    document.getElementById('timeRemaining').textContent = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function refreshAttendanceData() {
    fetch(`{{ route('api.attendance.realtime', $qrCode->id) }}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateTable(data.data.students);
                updateStatistics(data.data.statistics);
            }
        })
        .catch(error => console.error('Error:', error));
}

function updateTable(students) {
    const tbody = document.getElementById('attendanceTableBody');
    tbody.innerHTML = '';
    
    students.forEach((student, index) => {
        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${student.name}</td>
                <td>${student.nis_nip || '-'}</td>
                <td>
                    <span class="badge bg-${student.status_color}">
                        ${student.attendance_status}
                    </span>
                </td>
                <td>${student.attendance_time || '-'}</td>
                <td>
                    ${student.has_attended ? '' : `
                        <button class="btn btn-sm btn-outline-primary mark-attendance" 
                                data-student-id="${student.id}"
                                data-student-name="${student.name}">
                            <i class="fas fa-check"></i> Tandai Hadir
                        </button>
                    `}
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    // Re-attach event listeners
    attachMarkAttendanceListeners();
}

function updateStatistics(stats) {
    // Update statistics display if needed
    console.log('Updated statistics:', stats);
}

function attachMarkAttendanceListeners() {
    document.querySelectorAll('.mark-attendance').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.dataset.studentId;
            const studentName = this.dataset.studentName;
            
            document.getElementById('modalStudentId').value = studentId;
            document.getElementById('modalStudentName').value = studentName;
            
            const modal = new bootstrap.Modal(document.getElementById('markAttendanceModal'));
            modal.show();
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize time remaining
    updateTimeRemaining();
    setInterval(updateTimeRemaining, 1000);
    
    // Set up auto-refresh every 10 seconds
    refreshInterval = setInterval(refreshAttendanceData, 10000);
    
    // Manual refresh button
    document.getElementById('refreshBtn').addEventListener('click', refreshAttendanceData);
    
    // Export button
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Implement export functionality
        alert('Fitur export akan segera hadir!');
    });
    
    // Mark attendance form
    document.getElementById('saveAttendanceBtn').addEventListener('click', function() {
        const form = document.getElementById('markAttendanceForm');
        const formData = new FormData(form);
        
        fetch('{{ route("attendance.mark") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Absensi berhasil dicatat!');
                refreshAttendanceData();
                bootstrap.Modal.getInstance(document.getElementById('markAttendanceModal')).hide();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan absensi.');
        });
    });
    
    // Initial attachment of event listeners
    attachMarkAttendanceListeners();
});
</script>
@endpush

<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.badge {
    font-size: 0.9em;
    padding: 0.5em 1em;
}
#timeRemaining {
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}
.progress {
    border-radius: 10px;
}
</style>