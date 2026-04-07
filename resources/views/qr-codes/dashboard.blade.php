@extends('layouts.app')

@section('title', 'Dashboard QR Code')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">
                    <i class="fas fa-qrcode me-2"></i> Dashboard QR Code
                </h4>
            </div>
        </div>
    </div>

    <!-- Error Alert -->
    @if(isset($error))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total QR Code
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-qrcode fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                QR Code Aktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Scan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_scans'] ?? 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-camera fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tingkat Kehadiran
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['attendance_rate'] ?? 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 1: Active QR Codes & Recent QR Codes -->
    <div class="row mb-4">
        <!-- Active QR Codes -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i> QR Code Aktif Hari Ini
                    </h6>
                    <a href="{{ route('qr-codes.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Buat Baru
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($activeQrCodes) && $activeQrCodes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Kelas</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeQrCodes as $qrCode)
                                    <tr>
                                        <td>
                                            <code>{{ $qrCode->code }}</code>
                                        </td>
                                        <td>{{ $qrCode->class->class_name ?? 'N/A' }}</td>
                                        <td>
                                            <small>{{ $qrCode->formatted_time_range ?? ($qrCode->start_time . ' - ' . $qrCode->end_time) }}</small>
                                            <br>
                                            <small class="text-muted">
                                                @if($qrCode->date instanceof \Carbon\Carbon)
                                                    {{ $qrCode->date->format('d/m/Y') }}
                                                @else
                                                    {{ \Carbon\Carbon::parse($qrCode->date)->format('d/m/Y') }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $qrCode->status_color ?? 'secondary' }}">
                                                {{ $qrCode->status_text ?? ($qrCode->is_active ? 'Aktif' : 'Nonaktif') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('qr-codes.show', $qrCode) }}" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(isset($qrCode->is_active_now) && $qrCode->is_active_now)
                                            <a href="{{ route('attendance.teacher.realtime', $qrCode->id) }}" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-qrcode fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada QR Code aktif hari ini</h5>
                            <p class="text-muted">Buat QR Code baru untuk memulai absensi</p>
                            <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Buat QR Code
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent QR Codes -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i> QR Code Terbaru
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($recentQrCodes) && $recentQrCodes->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentQrCodes as $qrCode)
                            <a href="{{ route('qr-codes.show', $qrCode) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $qrCode->class->class_name ?? 'N/A' }}</h6>
                                    <small>
                                        @if($qrCode->created_at instanceof \Carbon\Carbon)
                                            {{ $qrCode->created_at->diffForHumans() }}
                                        @else
                                            {{ \Carbon\Carbon::parse($qrCode->created_at)->diffForHumans() }}
                                        @endif
                                    </small>
                                </div>
                                <p class="mb-1">
                                    <code>{{ $qrCode->code }}</code>
                                    <span class="badge bg-{{ $qrCode->status_color ?? 'secondary' }} ms-2">
                                        {{ $qrCode->status_text ?? ($qrCode->is_active ? 'Aktif' : 'Nonaktif') }}
                                    </span>
                                </p>
                                <small class="text-muted">
                                    @if($qrCode->date instanceof \Carbon\Carbon)
                                        {{ $qrCode->date->format('d/m/Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($qrCode->date)->format('d/m/Y') }}
                                    @endif
                                     • {{ $qrCode->formatted_time_range ?? ($qrCode->start_time . ' - ' . $qrCode->end_time) }}
                                </small>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Belum ada QR Code</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('qr-codes.index') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua QR Code
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Chart & Upcoming QR Codes -->
    <div class="row mb-4">
        <!-- Activity Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i> Aktivitas QR Code (7 Hari Terakhir)
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($qrActivityChart) && !empty($qrActivityChart['labels']))
                        <div class="chart-area">
                            <canvas id="qrActivityChart"></canvas>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Tidak ada data aktivitas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming QR Codes -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt me-2"></i> QR Code Mendatang
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($upcomingQrCodes) && $upcomingQrCodes->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($upcomingQrCodes as $qrCode)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $qrCode->class->class_name ?? 'N/A' }}</h6>
                                    <small class="text-{{ $qrCode->is_active_now ? 'success' : 'warning' }}">
                                        {{ $qrCode->time_until_start ?? 'Mendatang' }}
                                    </small>
                                </div>
                                <p class="mb-1">
                                    <code>{{ $qrCode->code }}</code>
                                </p>
                                <small class="text-muted">
                                    @if($qrCode->date instanceof \Carbon\Carbon)
                                        {{ $qrCode->date->format('d/m/Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($qrCode->date)->format('d/m/Y') }}
                                    @endif
                                     • {{ $qrCode->formatted_time_range ?? ($qrCode->start_time . ' - ' . $qrCode->end_time) }}
                                </small>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Tidak ada QR Code mendatang</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Class Distribution -->
    @if(isset($classDistribution) && $classDistribution->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i> Distribusi QR Code per Kelas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-pie pt-4">
                                <canvas id="classDistributionChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mt-4">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Kelas</th>
                                            <th class="text-end">Jumlah QR Code</th>
                                            <th class="text-end">Persentase</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalQR = $classDistribution->sum('count');
                                        @endphp
                                        @foreach($classDistribution as $class)
                                        <tr>
                                            <td>
                                                <span class="badge" style="background-color: {{ $class->color ?? '#4e73df' }}">
                                                    {{ $class->class_name }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ $class->count }}</td>
                                            <td class="text-end">
                                                {{ $totalQR > 0 ? round(($class->count / $totalQR) * 100, 1) : 0 }}%
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
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 10px;
    border: none;
}
.card-header {
    border-bottom: 1px solid #e3e6f0;
    background-color: #f8f9fc;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.chart-area {
    position: relative;
    height: 300px;
    width: 100%;
}
.chart-pie {
    position: relative;
    height: 250px;
    width: 100%;
}
.list-group-item {
    border: none;
    border-bottom: 1px solid #e3e6f0;
}
.list-group-item:last-child {
    border-bottom: none;
}
.text-xs {
    font-size: 0.7rem;
}
.text-gray-800 {
    color: #5a5c69;
}
.font-weight-bold {
    font-weight: 700;
}
</style>
@endpush

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // QR Activity Chart
    @if(isset($qrActivityChart) && !empty($qrActivityChart['labels']))
    var ctx = document.getElementById('qrActivityChart');
    if (ctx) {
        var qrActivityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($qrActivityChart['labels']),
                datasets: [{
                    label: 'Dibuat',
                    data: @json($qrActivityChart['created']),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Digunakan',
                    data: @json($qrActivityChart['used']),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.05)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#1cc88a',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            stepSize: 1
                        },
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    @endif

    // Class Distribution Chart
    @if(isset($classDistribution) && $classDistribution->count() > 0)
    var ctx2 = document.getElementById('classDistributionChart');
    if (ctx2) {
        var classDistributionChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: @json($classDistribution->pluck('class_name')),
                datasets: [{
                    data: @json($classDistribution->pluck('count')),
                    backgroundColor: @json($classDistribution->pluck('color')),
                    hoverBackgroundColor: @json($classDistribution->pluck('color')),
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                    borderWidth: 2,
                    borderColor: '#fff'
                }],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '65%',
            },
        });
    }
    @endif
});
</script>
@endpush