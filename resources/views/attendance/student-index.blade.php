@extends('layouts.app')

@section('title', 'Absensi Saya')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Absensi Saya</h4>
            </div>
        </div>
    </div>

    <!-- Statistik Bulan Ini -->
    <div class="row mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Hadir</h6>
                    <h2 class="text-primary">{{ $monthStats['present'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Terlambat</h6>
                    <h2 class="text-warning">{{ $monthStats['late'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Tidak Hadir</h6>
                    <h2 class="text-danger">{{ $monthStats['absent'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Persentase</h6>
                    <h2 class="text-success">{{ $monthStats['percentage'] ?? 0 }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bulan -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('attendance.student.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="month" class="form-label">Bulan</label>
                            <select name="month" id="month" class="form-select">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year" class="form-label">Tahun</label>
                            <select name="year" id="year" class="form-select">
                                @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('attendance.student.index') }}" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Absensi -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Riwayat Absensi Bulan {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</h5>
                        <div>
                            <a href="{{ route('attendance.student.statistics') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-chart-bar"></i> Statistik Lengkap
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Kelas</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="15%">Status</th>
                                        <th width="15%">Waktu</th>
                                        <th width="15%">Keterangan</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $attendance)
                                    <tr>
                                        <td>{{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}</td>
                                        <td>
                                            <strong>{{ $attendance->class->class_name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $attendance->class->class_code ?? '' }}</small>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'present' => 'success',
                                                    'late' => 'warning',
                                                    'absent' => 'danger',
                                                    'sick' => 'info',
                                                    'permission' => 'secondary'
                                                ];
                                                $statusTexts = [
                                                    'present' => 'Hadir',
                                                    'late' => 'Terlambat',
                                                    'absent' => 'Tidak Hadir',
                                                    'sick' => 'Sakit',
                                                    'permission' => 'Izin'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$attendance->status] ?? 'secondary' }}">
                                                {{ $statusTexts[$attendance->status] ?? $attendance->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->checked_in_at)
                                                {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attendance->qrCode)
                                                <small class="text-muted">QR: {{ $attendance->qrCode->code }}</small>
                                            @endif
                                            @if($attendance->notes)
                                                <br>
                                                <small>{{ Str::limit($attendance->notes, 30) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <!-- PERBAIKAN: TAMBAHKAN PARAMETER attendance -->
                                            <a href="{{ route('attendance.student.show', ['attendance' => $attendance->id]) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $attendances->links() }}
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h5>Tidak ada data absensi</h5>
                            <p class="mb-0">Belum ada absensi untuk bulan {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0">
                                <small class="text-muted">
                                    Menampilkan {{ $attendances->firstItem() ?? 0 }} - {{ $attendances->lastItem() ?? 0 }} dari {{ $attendances->total() }} data
                                </small>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                Total: {{ $monthStats['total'] ?? 0 }} absensi
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Kelas -->
    @if($classes->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Kelas Saya</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($classes as $class)
                        <div class="col-md-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $class->class_name }}</h6>
                                    <p class="card-text mb-1">
                                        <small class="text-muted">Kode: {{ $class->class_code }}</small>
                                    </p>
                                    <p class="card-text mb-1">
                                        <small class="text-muted">Guru: {{ $class->teacher->name ?? 'N/A' }}</small>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-users"></i> {{ $class->students_count ?? 0 }} siswa
                                        </small>
                                    </p>
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

    <!-- QR Codes Aktif Hari Ini -->
    @if(isset($recentQrCodes) && $recentQrCodes->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">QR Code Aktif Hari Ini</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-qrcode"></i> Scan QR Code berikut untuk absensi hari ini:
                    </div>
                    <div class="row">
                        @foreach($recentQrCodes as $qrCode)
                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ $qrCode->class->class_name }}</h6>
                                    <p class="card-text mb-2">
                                        <small>{{ $qrCode->formatted_time_range }}</small>
                                    </p>
                                    @if($qrCode->qr_code_image)
                                    <img src="{{ Storage::url($qrCode->qr_code_image) }}" 
                                         alt="QR Code" 
                                         class="img-fluid mb-2"
                                         style="max-width: 150px;">
                                    @endif
                                    <p class="card-text">
                                        <small class="text-muted">Kode: {{ $qrCode->code }}</small>
                                    </p>
                                    <a href="{{ route('attendance.scan', $qrCode->code) }}" 
                                       class="btn btn-sm btn-success">
                                        <i class="fas fa-camera"></i> Scan QR
                                    </a>
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
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.badge {
    font-size: 0.8em;
    padding: 0.4em 0.8em;
}
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.alert-info {
    background-color: #e7f3ff;
    border-color: #b6d4fe;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto refresh every 30 seconds if there are active QR codes
    @if(isset($recentQrCodes) && $recentQrCodes->count() > 0)
    setTimeout(function() {
        window.location.reload();
    }, 30000);
    @endif
    
    // Tooltip initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush