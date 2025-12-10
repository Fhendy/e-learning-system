@extends('layouts.app')

@section('title', 'Detail QR Code')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('qr_code_image_url'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <img src="{{ session('qr_code_image_url') }}" alt="QR Code" class="img-thumbnail" style="width: 100px;">
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading">QR Code Berhasil Dibuat!</h5>
                    <p class="mb-1">Kode: <strong>{{ session('qr_code') }}</strong></p>
                    <p class="mb-0">QR Code telah disimpan dan siap digunakan.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('debug_info'))
<div class="alert alert-info">
    <h5>Debug Info:</h5>
    <pre>{{ json_encode(session('debug_info'), JSON_PRETTY_PRINT) }}</pre>
</div>
@endif

@if(isset($imageExists) && !$imageExists)
<div class="alert alert-warning">
    <h5>⚠️ Image Not Found!</h5>
    <p>Image path: {{ $qrCode->qr_code_image }}</p>
    <p>Full path: {{ $imagePath ?? 'N/A' }}</p>
    <p>Storage URL: {{ $imageUrl ?? 'N/A' }}</p>
</div>
@endif

{{-- Di bagian QR Code Image --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">QR Code</h5>
    </div>
    <div class="card-body text-center">
        @if($qrCode->qr_code_image && $imageExists)
            <img src="{{ $imageUrl }}" alt="QR Code" class="img-fluid mb-3" style="max-width: 300px;">
            <p class="text-muted">Kode: {{ $qrCode->code }}</p>
            <a href="{{ $imageUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-external-link-alt"></i> Open Image
            </a>
        @else
            <div class="alert alert-danger">
                <p>❌ QR Code image tidak ditemukan!</p>
                <p>Path: {{ $qrCode->qr_code_image ?? 'null' }}</p>
                <p>Status: {{ $imageExists ? 'Exists' : 'Missing' }}</p>
            </div>
        @endif
    </div>
</div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi QR Code</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($qrCode->qr_code_image && Storage::disk('public')->exists($qrCode->qr_code_image))
                            <img src="{{ Storage::url($qrCode->qr_code_image) }}" 
                                 alt="QR Code {{ $qrCode->code }}" 
                                 class="img-fluid mb-3"
                                 style="max-width: 250px;">
                            <p class="text-muted">Scan QR code di atas untuk absensi</p>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Gambar QR Code tidak ditemukan
                            </div>
                            <form action="{{ route('qr-codes.update', $qrCode) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="regenerate_qr" value="1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Generate Ulang QR Code
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Kode</th>
                            <td>{{ $qrCode->code }}</td>
                        </tr>
                        <tr>
                            <th>Kelas</th>
                            <td>{{ $qrCode->class->class_name }} ({{ $qrCode->class->class_code }})</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $qrCode->date->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Waktu</th>
                            <td>{{ $qrCode->formatted_start_time }} - {{ $qrCode->formatted_end_time }}</td>
                        </tr>
                        <tr>
                            <th>Durasi</th>
                            <td>{{ $qrCode->duration_minutes }} menit</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $qrCode->status_color }}">
                                    {{ $qrCode->status_text }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Lokasi Terbatas</th>
                            <td>
                                @if($qrCode->location_restricted)
                                    <span class="badge bg-warning">Ya</span>
                                    <small class="text-muted d-block">
                                        Radius: {{ $qrCode->radius }} meter
                                    </small>
                                @else
                                    <span class="badge bg-secondary">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>{{ $qrCode->creator->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Total Scan</th>
                            <td>{{ $qrCode->scan_count }} kali</td>
                        </tr>
                        @if($qrCode->notes)
                        <tr>
                            <th>Catatan</th>
                            <td>{{ $qrCode->notes }}</td>
                        </tr>
                        @endif
                    </table>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-3">
                        <a href="{{ route('qr-codes.download', $qrCode) }}" class="btn btn-outline-primary">
                            <i class="fas fa-download"></i> Download QR
                        </a>
                        @if($qrCode->canBeEdited())
                            <a href="{{ route('qr-codes.edit', $qrCode) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        @if($qrCode->canBeDeleted())
                            <form action="{{ route('qr-codes.destroy', $qrCode) }}" method="POST" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus QR Code ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistik Absensi</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3 col-6">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Siswa</h6>
                                    <h2 class="text-primary">{{ $totalStudents }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Hadir</h6>
                                    <h2 class="text-success">{{ $attendanceStats['present'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Terlambat</h6>
                                    <h2 class="text-warning">{{ $attendanceStats['late'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Tidak Hadir</h6>
                                    <h2 class="text-danger">{{ $attendanceStats['absent'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $attendancePercentage }}%"
                             aria-valuenow="{{ $attendancePercentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $attendancePercentage }}% Kehadiran
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Detail Absensi</h6>
                    @if($attendances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Siswa</th>
                                        <th>NIS</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances as $attendance)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $attendance->student->name }}</td>
                                        <td>{{ $attendance->student->nis_nip ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $attendance->status_color ?? 'secondary' }}">
                                                {{ $attendance->status_text ?? $attendance->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($attendance->checked_in_at)
                                                {{ \Carbon\Carbon::parse($attendance->checked_in_at)->format('H:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $attendance->notes ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            {{ $attendances->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada absensi untuk QR Code ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
});
</script>
@endpush