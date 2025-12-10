@extends('layouts.app')

@section('title', 'Detail Absensi Kelas')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-primary">Absensi Kelas {{ $class->class_name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('attendance.teacher.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <button onclick="exportClassAttendance()" class="btn btn-success">
                <i class="bi bi-download me-2"></i>Export
            </button>
            <a href="{{ route('qr-codes.create') }}?class_id={{ $class->id }}" 
               class="btn btn-primary">
                <i class="bi bi-qr-code me-2"></i>Buat QR Code
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistik Kelas -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Kelas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Nama Kelas</th>
                                    <td>: {{ $class->class_name }}</td>
                                </tr>
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <td>: {{ $class->subject }}</td>
                                </tr>
                                <tr>
                                    <th>Guru</th>
                                    <td>: {{ $class->teacher->name }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Siswa</th>
                                    <td>: {{ $class->students_count ?? 0 }} siswa</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Pertemuan</th>
                                    <td>: {{ $totalSessions }} pertemuan</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="mb-3">
                                    <canvas id="attendancePieChart" width="200" height="200"></canvas>
                                </div>
                                <p class="text-muted">Persentase Kehadiran: {{ $attendanceRate }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('attendance.class.show', $class->id) }}">
                        <div class="mb-3">
                            <label class="form-label">Bulan</label>
                            <select name="month" class="form-select">
                                <option value="">Semua Bulan</option>
                                @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tahun</label>
                            <select name="year" class="form-select">
                                <option value="">Semua Tahun</option>
                                @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-filter me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Siswa dan Absensi -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa dan Absensi</h6>
                    <span class="badge bg-primary">{{ $students->count() }} Siswa</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="classAttendanceTable">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle">No</th>
                                    <th rowspan="2" class="align-middle">Nama Siswa</th>
                                    <th rowspan="2" class="align-middle">NIS</th>
                                    <th colspan="5" class="text-center">Statistik</th>
                                    @foreach($dates as $date)
                                    <th class="text-center" style="min-width: 80px">
                                        {{ $date->format('d/m') }}
                                    </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th class="text-center">H</th>
                                    <th class="text-center">T</th>
                                    <th class="text-center">A</th>
                                    <th class="text-center">S/I</th>
                                    <th class="text-center">%</th>
                                    @foreach($dates as $date)
                                    <th></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                @php
                                    $studentStats = $student->getAttendanceStats($class->id, $month, $year);
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->nis_nip }}</td>
                                    
                                    <!-- Statistics -->
                                    <td class="text-center text-success">{{ $studentStats['present'] }}</td>
                                    <td class="text-center text-warning">{{ $studentStats['late'] }}</td>
                                    <td class="text-center text-danger">{{ $studentStats['absent'] }}</td>
                                    <td class="text-center text-info">{{ $studentStats['sick'] + $studentStats['permission'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $studentStats['percentage'] >= 75 ? 'success' : ($studentStats['percentage'] >= 50 ? 'warning' : 'danger') }}">
                                            {{ $studentStats['percentage'] }}%
                                        </span>
                                    </td>
                                    
                                    <!-- Daily attendance -->
                                    @foreach($dates as $date)
                                    @php
                                        $attendance = $student->attendances
                                            ->where('class_id', $class->id)
                                            ->where('attendance_date', $date->format('Y-m-d'))
                                            ->first();
                                    @endphp
                                    <td class="text-center">
                                        @if($attendance)
                                            <span class="badge bg-{{ $attendance->status_color }}" 
                                                  title="{{ ucfirst($attendance->status) }} - {{ $attendance->checked_in_at ? $attendance->checked_in_at->format('H:i') : '' }}">
                                                {{ strtoupper(substr($attendance->status, 0, 1)) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                                
                                <!-- Footer Statistics -->
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end">TOTAL</td>
                                    <td class="text-center">{{ $totalStats['present'] }}</td>
                                    <td class="text-center">{{ $totalStats['late'] }}</td>
                                    <td class="text-center">{{ $totalStats['absent'] }}</td>
                                    <td class="text-center">{{ $totalStats['sick'] + $totalStats['permission'] }}</td>
                                    <td class="text-center">{{ $attendanceRate }}%</td>
                                    @foreach($dates as $date)
                                    @php
                                        $dateStats = $class->getDateStats($date);
                                    @endphp
                                    <td class="text-center">
                                        <small class="{{ $dateStats['percentage'] >= 75 ? 'text-success' : ($dateStats['percentage'] >= 50 ? 'text-warning' : 'text-danger') }}">
                                            {{ $dateStats['present'] }}/{{ $dateStats['total'] }}
                                        </small>
                                    </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Legend -->
                    <div class="mt-4">
                        <h6 class="text-primary mb-2">Keterangan:</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">H</span>
                                <small>Hadir</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2">T</span>
                                <small>Terlambat</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2">A</span>
                                <small>Absen</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2">S/I</span>
                                <small>Sakit/Izin</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2">-</span>
                                <small>Tidak ada data</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pie chart for attendance statistics
    const ctx = document.getElementById('attendancePieChart').getContext('2d');
    const pieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Terlambat', 'Absen', 'Sakit/Izin'],
            datasets: [{
                data: [
                    {{ $totalStats['present'] }},
                    {{ $totalStats['late'] }},
                    {{ $totalStats['absent'] }},
                    {{ $totalStats['sick'] + $totalStats['permission'] }}
                ],
                backgroundColor: [
                    '#1cc88a',
                    '#f6c23e',
                    '#e74a3b',
                    '#36b9cc'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});

function exportClassAttendance() {
    // Get the table element
    const table = document.getElementById('classAttendanceTable');
    
    // Create a new workbook
    const wb = XLSX.utils.book_new();
    
    // Convert table to worksheet
    const ws = XLSX.utils.table_to_sheet(table);
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Absensi Kelas');
    
    // Generate filename
    const filename = `Absensi_${"{{ $class->class_name }}".replace(/\s+/g, '_')}_{{ date('Y_m_d') }}.xlsx`;
    
    // Save the file
    XLSX.writeFile(wb, filename);
}
</script>

<style>
#classAttendanceTable th {
    vertical-align: middle;
    text-align: center;
    font-size: 0.85rem;
}
#classAttendanceTable td {
    vertical-align: middle;
    text-align: center;
}
.badge {
    font-size: 11px;
    padding: 3px 6px;
}
</style>
@endsection