@extends('layouts.app')

@section('title', 'Manajemen QR Code')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-primary">Manajemen QR Code</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Buat QR Code
            </a>
            <a href="{{ route('qr-codes.dashboard') }}" class="btn btn-info">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter QR Code</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('qr-codes.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
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
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Mendatang</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('qr-codes.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-repeat me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- QR Codes List -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar QR Code</h6>
            <span class="badge bg-primary">{{ $qrCodes->total() }} Data</span>
        </div>
        <div class="card-body">
            @if($qrCodes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Scan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($qrCodes as $qrCode)
                        <tr>
                            <td>
                                {{ $loop->iteration + ($qrCodes->currentPage() - 1) * $qrCodes->perPage() }}
                             </td>
                            <td>
                                <code>{{ $qrCode->code }}</code>
                                @if($qrCode->location_restricted)
                                    <br><small class="text-muted"><i class="bi bi-geo-alt"></i> Terbatas lokasi</small>
                                @endif
                             </td>
                            <td>
                                <strong>{{ $qrCode->class->class_name ?? 'N/A' }}</strong>
                                @if(isset($qrCode->class->subject))
                                    <br><small>{{ $qrCode->class->subject }}</small>
                                @endif
                             </td>
                            <td>
                                @php
                                    $dateObj = $qrCode->date instanceof \Carbon\Carbon 
                                        ? $qrCode->date 
                                        : \Carbon\Carbon::parse($qrCode->date);
                                @endphp
                                {{ $dateObj->format('d/m/Y') }}
                                <br><small class="text-muted">{{ $dateObj->diffForHumans() }}</small>
                             </td>
                            <td>
                                @php
                                    $startTime = $qrCode->start_time;
                                    $endTime = $qrCode->end_time;
                                    if (strlen($startTime) === 5) $startTime .= ':00';
                                    if (strlen($endTime) === 5) $endTime .= ':00';
                                @endphp
                                {{ \Carbon\Carbon::parse($startTime)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($endTime)->format('H:i') }}
                                <br>
                                <small class="text-muted">
                                    {{ $qrCode->duration_minutes ?? $qrCode->duration_minutes_calculated ?? '?' }} menit
                                </small>
                             </td>
                            <td>
                                @php
                                    $statusColor = $qrCode->status_color ?? 'secondary';
                                    $statusText = $qrCode->status_text ?? ($qrCode->is_active ? 'Aktif' : 'Nonaktif');
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $statusText }}
                                </span>
                                @if(isset($qrCode->is_active_now) && $qrCode->is_active_now)
                                    <br>
                                    <small class="text-success">
                                        <i class="bi bi-clock-history"></i> 
                                        Sisa: {{ $qrCode->time_remaining ?? '?' }} menit
                                    </small>
                                @endif
                             </td>
                            <td>
                                <div class="text-center">
                                    <div class="h5 mb-0">{{ $qrCode->scan_count ?? 0 }}</div>
                                    <small class="text-muted">scan</small>
                                    @php
                                        $attendanceCount = $qrCode->attendances ? $qrCode->attendances->count() : 0;
                                    @endphp
                                    <div>
                                        <small class="text-muted">{{ $attendanceCount }} siswa</small>
                                    </div>
                                </div>
                             </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('qr-codes.show', $qrCode) }}" 
                                       class="btn btn-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
@if($qrCode->can_be_edited)
    <a href="{{ route('qr-codes.edit', $qrCode) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil"></i> Edit
    </a>
@endif
                                    @if($qrCode->is_active)
                                    <form action="{{ route('qr-codes.deactivate', $qrCode) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" 
                                                title="Nonaktifkan" 
                                                onclick="return confirm('Nonaktifkan QR Code ini?')">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('qr-codes.activate', $qrCode) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" 
                                                title="Aktifkan" 
                                                onclick="return confirm('Aktifkan QR Code ini?')">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                    @endif
@if($qrCode->can_be_deleted)
    <form action="{{ route('qr-codes.destroy', $qrCode) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus QR Code ini?')">
            <i class="bi bi-trash"></i> Hapus
        </button>
    </form>
@endif
                                </div>
                             </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $qrCodes->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-qr-code fs-1 text-gray-300 mb-3"></i>
                <h5 class="text-muted">Belum ada QR Code</h5>
                <p class="text-muted">Mulai dengan membuat QR Code pertama Anda</p>
                <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Buat QR Code
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Tooltip initialization (if using Bootstrap 5)
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Optional: Auto refresh jika ada QR Code aktif (every 30 seconds)
let refreshInterval = null;

function startAutoRefresh() {
    const hasActiveQr = document.querySelector('.badge.bg-success, .badge.bg-warning');
    if (hasActiveQr && !refreshInterval) {
        refreshInterval = setInterval(function() {
            // Check if page is still visible
            if (!document.hidden) {
                location.reload();
            }
        }, 30000); // Refresh every 30 seconds
    } else if (!hasActiveQr && refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
}

// Start auto refresh if there are active QR codes
document.addEventListener('DOMContentLoaded', startAutoRefresh);

// Stop auto refresh when page is hidden to save resources
document.addEventListener('visibilitychange', function() {
    if (document.hidden && refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    } else if (!document.hidden) {
        startAutoRefresh();
    }
});
</script>

<style>
code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 11px;
}
.badge {
    font-size: 11px;
    padding: 5px 8px;
}
.btn-group .btn {
    padding: 0.25rem 0.5rem;
}
.table td {
    vertical-align: middle;
}
.text-gray-300 {
    color: #dee2e6;
}
.fs-1 {
    font-size: 3rem;
}
</style>
@endsection