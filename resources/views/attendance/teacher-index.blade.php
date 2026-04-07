@extends('layouts.app')

@section('title', 'Manajemen Absensi Guru')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-primary">
            <i class="fas fa-clipboard-check me-2"></i>Manajemen Absensi
        </h1>
        <div class="d-flex gap-2">
            <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Generate QR Code
            </a>
            <a href="{{ route('attendance.teacher.export') }}" class="btn btn-success">
                <i class="fas fa-download me-2"></i>Export Data
            </a>
            <button class="btn btn-info" onclick="showQuickStats()">
                <i class="fas fa-chart-pie me-2"></i>Quick Stats
            </button>
                <!-- Quick Generate QR Code for today -->
    @foreach($classes as $class)
    <button class="btn btn-success quick-generate-btn" 
            data-class-id="{{ $class->id }}"
            data-class-name="{{ $class->class_name }}">
        <i class="fas fa-bolt me-2"></i>QR {{ $class->class_code }}
    </button>
    @endforeach
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistik Hari Ini -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar me-2"></i>Statistik Absensi Hari Ini
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2 col-4 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body py-3">
                                    <div class="h4 mb-0">{{ $todayStats['present'] ?? 0 }}</div>
                                    <small>Hadir</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-3">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body py-3">
                                    <div class="h4 mb-0">{{ $todayStats['late'] ?? 0 }}</div>
                                    <small>Terlambat</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body py-3">
                                    <div class="h4 mb-0">{{ $todayStats['absent'] ?? 0 }}</div>
                                    <small>Absen</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body py-3">
                                    <div class="h4 mb-0">{{ $todayStats['sick'] ?? 0 }}</div>
                                    <small>Sakit</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body py-3">
                                    <div class="h4 mb-0">{{ $todayStats['permission'] ?? 0 }}</div>
                                    <small>Izin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-4 mb-3">
                            <div class="card bg-secondary text-white h-100">
                                <div class="card-body py-3">
                                    <div class="h4 mb-0">{{ $todayStats['total'] ?? 0 }}</div>
                                    <small>Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($todayStats['total'] > 0)
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Persentase Kehadiran:</span>
                            <span class="fw-bold">{{ $todayStats['percentage'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $todayStats['percentage'] ?? 0 }}%">
                                {{ $todayStats['percentage'] ?? 0 }}%
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Aktif -->
    @if($activeQrCodes && $activeQrCodes->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-left-success">
                <div class="card-header bg-success text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-qrcode me-2"></i>QR Code Aktif Hari Ini
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($activeQrCodes as $qrCode)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">{{ $qrCode->class->class_name }}</h5>
                                        <span class="badge bg-{{ $qrCode->isActive() ? 'success' : 'danger' }}">
                                            {{ $qrCode->isActive() ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($qrCode->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($qrCode->end_time)->format('H:i') }}
                                    </p>
                                    <p class="card-text">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                        {{ $qrCode->class->teacher->name }}
                                    </p>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Absensi:</span>
                                            <span class="badge bg-primary">
                                                {{ $qrCode->getAttendanceCount() }} / {{ $qrCode->class->students->count() ?? 0 }}
                                            </span>
                                        </div>
                                        <div class="progress mt-1" style="height: 8px;">
                                            @php
                                                $percentage = $qrCode->class->students->count() > 0 
                                                    ? ($qrCode->getAttendanceCount() / $qrCode->class->students->count()) * 100 
                                                    : 0;
                                            @endphp
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('qr-codes.show', $qrCode) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </a>
                                        @if($qrCode->isActive())
                                        <form action="{{ route('qr-codes.deactivate', $qrCode) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="fas fa-power-off me-1"></i>Nonaktifkan
                                            </button>
                                        </form>
                                        @endif
                                        <a href="{{ route('attendance.class.show', $qrCode->class_id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-users me-1"></i>Kelas
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter dan Form Input Manual -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit me-2"></i>Input Manual Absensi
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.teacher.manual') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <select name="class_id" class="form-select" required id="classSelect">
                                <option value="">Pilih Kelas</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Siswa</label>
                            <select name="student_id" class="form-select" required id="studentSelect">
                                <option value="">Pilih Siswa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="attendance_date" class="form-control" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="present">Hadir</option>
                                <option value="late">Terlambat</option>
                                <option value="absent">Absen</option>
                                <option value="sick">Sakit</option>
                                <option value="permission">Izin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Waktu Absen (HH:MM)</label>
                            <input type="time" name="checked_in_at" class="form-control" 
                                   value="{{ date('H:i') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Absensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Filter Data Absensi
                    </h6>
                    <a href="{{ route('attendance.teacher.index') }}" class="btn btn-sm btn-secondary">
                        Reset Filter
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('attendance.teacher.index') }}">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kelas</label>
                                <select name="class_id" class="form-select">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="date" class="form-control" 
                                       value="{{ request('date', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absen</option>
                                    <option value="sick" {{ request('status') == 'sick' ? 'selected' : '' }}>Sakit</option>
                                    <option value="permission" {{ request('status') == 'permission' ? 'selected' : '' }}>Izin</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control" 
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control" 
                                       value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter Data
                            </button>
                        </div>
                    </form>
                    
                    <!-- Quick Links to Classes -->
                    <div class="mt-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-bolt me-2"></i>Akses Cepat Kelas:
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($classes as $class)
                            <a href="{{ route('attendance.class.show', $class->id) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-users me-1"></i>{{ $class->class_name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Absensi -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>Daftar Absensi
                    </h6>
                    <div>
                        <span class="badge bg-primary me-2">{{ $attendances->total() }} Data</span>
                        <button class="btn btn-sm btn-success" onclick="exportTableToExcel()">
                            <i class="fas fa-file-excel me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($attendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                <tr>
                                    <td>{{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}</td>
                                    <td>{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $attendance->student->name }}</strong><br>
                                        <small class="text-muted">{{ $attendance->student->nis_nip }}</small>
                                    </td>
                                    <td>{{ $attendance->class->class_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $attendance->status_color }}">
                                            <i class="fas {{ $attendance->getStatusIcon() }} me-1"></i>
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($attendance->checked_in_at)
                                            {{ $attendance->checked_in_at->format('H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $attendance->notes ? Str::limit($attendance->notes, 30) : '-' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('attendance.edit', $attendance) }}" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-info" 
                                                    onclick="showAttendanceDetail({{ $attendance->id }})"
                                                    title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form action="{{ route('attendance.destroy', $attendance) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" 
                                                        onclick="return confirm('Hapus absensi ini?')"
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $attendances->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-gray-300 mb-3"></i>
                        <h5 class="text-muted">Belum ada data absensi</h5>
                        <p class="text-muted">Mulai dengan membuat QR Code atau input manual</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Modal -->
<div class="modal fade" id="quickStatsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-chart-pie me-2"></i>Quick Statistics
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="quickStatsContent">
                    <!-- Stats will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.quick-generate-btn').forEach(button => {
    button.addEventListener('click', function() {
        const classId = this.dataset.classId;
        const className = this.dataset.className;
        
        if (confirm(`Buat QR Code cepat untuk kelas ${className}?`)) {
            fetch(`/attendance/quick-generate/${classId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('QR Code berhasil dibuat!');
                    // Redirect ke real-time monitoring
                    window.location.href = data.data.realtime_url;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat membuat QR Code.');
            });
        }
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Dynamic student selection based on class for manual input
    const classSelect = document.getElementById('classSelect');
    const studentSelect = document.getElementById('studentSelect');
    
    if (classSelect && studentSelect) {
        classSelect.addEventListener('change', function() {
            const classId = this.value;
            studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';
            
            if (classId) {
                fetch(`/api/classes/${classId}/students`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(student => {
                            const option = document.createElement('option');
                            option.value = student.id;
                            option.textContent = student.name + ' (' + student.nis_nip + ')';
                            studentSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    }
});

function showQuickStats() {
    const modal = new bootstrap.Modal(document.getElementById('quickStatsModal'));
    
    // Fetch quick stats
    fetch('/api/attendance/quick-stats')
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('quickStatsContent');
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Statistik Minggu Ini</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="weeklyStatsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Top 5 Kelas</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    ${data.top_classes.map(cls => `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            ${cls.class_name}
                                            <span class="badge bg-primary rounded-pill">
                                                ${cls.attendance_rate}%
                                            </span>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Render chart
            if (data.weekly_stats) {
                const ctx = document.getElementById('weeklyStatsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.weekly_stats.labels,
                        datasets: [{
                            label: 'Kehadiran',
                            data: data.weekly_stats.data,
                            backgroundColor: '#4e73df'
                        }]
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('quickStatsContent').innerHTML = 
                '<div class="alert alert-danger">Gagal memuat statistik</div>';
        });
    
    modal.show();
}

function showAttendanceDetail(attendanceId) {
    // You can implement a modal or redirect to detail page
    window.location.href = `/attendance/${attendanceId}`;
}

function exportTableToExcel() {
    const table = document.getElementById('attendanceTable');
    let csv = [];
    
    // Get table headers
    let headers = [];
    for (let i = 0; i < table.rows[0].cells.length - 1; i++) { // Exclude actions column
        headers.push(table.rows[0].cells[i].innerText);
    }
    csv.push(headers.join(','));
    
    // Get table rows
    for (let i = 1; i < table.rows.length; i++) {
        let row = [];
        for (let j = 0; j < table.rows[i].cells.length - 1; j++) { // Exclude actions column
            row.push(table.rows[i].cells[j].innerText);
        }
        csv.push(row.join(','));
    }
    
    // Download CSV
    const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "absensi_" + new Date().toISOString().split('T')[0] + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<style>
.card {
    border-radius: 10px;
}
.badge {
    font-size: 12px;
    padding: 5px 10px;
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.progress {
    border-radius: 10px;
}
</style>
@endsection