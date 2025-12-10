@extends('layouts.app')

@section('title', 'Detail Absensi')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('attendance.student.index') }}">Absensi Saya</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Absensi</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detail Absensi</h5>
                </div>
                <div class="card-body">
                    @if($attendance)
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Kelas</th>
                            <td>{{ $attendance->class->class_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @php
                                    $statusColors = [
                                        'present' => 'success',
                                        'late' => 'warning',
                                        'absent' => 'danger',
                                        'sick' => 'info',
                                        'permission' => 'primary'
                                    ];
                                    $statusTexts = [
                                        'present' => 'Hadir',
                                        'late' => 'Terlambat',
                                        'absent' => 'Tidak Hadir',
                                        'sick' => 'Sakit',
                                        'permission' => 'Izin'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$attendance->status] ?? 'secondary' }} p-2" style="font-size: 1em;">
                                    {{ $statusTexts[$attendance->status] ?? $attendance->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Waktu Absensi</th>
                            <td>
                                @if($attendance->checked_in_at)
                                    {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i:s') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @if($attendance->qrCode)
                        <tr>
                            <th>QR Code</th>
                            <td>
                                <span class="badge bg-info">{{ $attendance->qrCode->code }}</span>
                                <small class="text-muted d-block">
                                    {{ $attendance->qrCode->formatted_time_range ?? 'N/A' }}
                                </small>
                            </td>
                        </tr>
                        @endif
                        @if($attendance->notes)
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $attendance->notes }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Dibuat</th>
                            <td>
                                {{ $attendance->created_at->format('d/m/Y H:i') }}
                                <small class="text-muted d-block">
                                    {{ $attendance->created_at->diffForHumans() }}
                                </small>
                            </td>
                        </tr>
                        @if($attendance->latitude && $attendance->longitude)
                        <tr>
                            <th>Lokasi</th>
                            <td>
                                <small class="text-muted">
                                    Lat: {{ number_format($attendance->latitude, 6) }}, 
                                    Long: {{ number_format($attendance->longitude, 6) }}
                                </small>
                                @if($attendance->accuracy)
                                <br>
                                <small class="text-muted">Akurasi: ±{{ $attendance->accuracy }}m</small>
                                @endif
                            </td>
                        </tr>
                        @endif
                    </table>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Data absensi tidak ditemukan.
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('attendance.student.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-info-circle text-primary"></i> Keterangan Status:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="badge bg-success p-2">Hadir</span>
                                <small class="text-muted"> - Hadir tepat waktu</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-warning p-2">Terlambat</span>
                                <small class="text-muted"> - Terlambat</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-danger p-2">Tidak Hadir</span>
                                <small class="text-muted"> - Tidak hadir</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-info p-2">Sakit</span>
                                <small class="text-muted"> - Sakit</small>
                            </li>
                            <li>
                                <span class="badge bg-primary p-2">Izin</span>
                                <small class="text-muted"> - Izin</small>
                            </li>
                        </ul>
                    </div>
                    
                    @if($attendance && $attendance->status === 'late')
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-clock"></i> Informasi Keterlambatan:</h6>
                        <p class="mb-0">Anda tercatat terlambat pada absensi ini. Pastikan untuk datang tepat waktu di kesempatan berikutnya.</p>
                    </div>
                    @endif
                    
                    @if($attendance && in_array($attendance->status, ['absent', 'sick', 'permission']))
                    <div class="alert alert-info">
                        <h6><i class="fas fa-sticky-note"></i> Catatan:</h6>
                        <p class="mb-0">Status {{ $statusTexts[$attendance->status] ?? $attendance->status }} telah tercatat. Untuk keterangan lebih lanjut, hubungi guru kelas.</p>
                    </div>
                    @endif
                </div>
            </div>
            
            @if($attendance && $attendance->qrCode && $attendance->qrCode->location_restricted)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt"></i> Informasi Lokasi</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <small>
                            <i class="fas fa-info-circle"></i> Absensi ini memiliki batasan lokasi.
                            Anda harus berada dalam radius 
                            <strong>{{ $attendance->qrCode->radius ?? 0 }} meter</strong> 
                            dari lokasi yang ditentukan.
                        </small>
                    </p>
                    @if($attendance->latitude && $attendance->longitude && $attendance->qrCode->latitude && $attendance->qrCode->longitude)
                    @php
                        // Calculate distance if all coordinates are available
                        function calculateDistance($lat1, $lon1, $lat2, $lon2) {
                            $earthRadius = 6371000; // meters
                            $latFrom = deg2rad($lat1);
                            $lonFrom = deg2rad($lon1);
                            $latTo = deg2rad($lat2);
                            $lonTo = deg2rad($lon2);
                            
                            $latDelta = $latTo - $latFrom;
                            $lonDelta = $lonTo - $lonFrom;
                            
                            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + 
                                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                            
                            return $angle * $earthRadius;
                        }
                        
                        $distance = calculateDistance(
                            $attendance->latitude,
                            $attendance->longitude,
                            $attendance->qrCode->latitude,
                            $attendance->qrCode->longitude
                        );
                    @endphp
                    <p class="card-text">
                        <small>
                            <i class="fas fa-ruler"></i> Jarak Anda dari lokasi yang ditentukan: 
                            <strong>{{ round($distance) }} meter</strong>
                        </small>
                    </p>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- QR Code Image if available -->
            @if($attendance && $attendance->qrCode && $attendance->qrCode->qr_code_image)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-qrcode"></i> QR Code</h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ Storage::url($attendance->qrCode->qr_code_image) }}" 
                         alt="QR Code" 
                         class="img-fluid"
                         style="max-width: 200px;">
                    <p class="mt-2 mb-0">
                        <small class="text-muted">Kode: {{ $attendance->qrCode->code }}</small>
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.breadcrumb {
    background-color: transparent;
    padding: 0;
}
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.table td, .table th {
    vertical-align: middle;
}
.badge {
    font-size: 0.9em;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any interactive functionality here if needed
});
</script>
@endpush