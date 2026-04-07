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

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi QR Code</h5>
                </div>
                <div class="card-body">
                    <!-- QR Code Image Section - FIXED -->
                    <div class="text-center mb-4">
                        @php
                            $imageUrl = null;
                            $imageExists = false;
                            
                            if ($qrCode->qr_code_image) {
                                // Cek berbagai kemungkinan path
                                if (Storage::disk('public')->exists($qrCode->qr_code_image)) {
                                    $imageExists = true;
                                    $imageUrl = Storage::url($qrCode->qr_code_image);
                                } elseif (Storage::disk('public')->exists('qr-codes/' . $qrCode->code . '.png')) {
                                    $imageExists = true;
                                    $imageUrl = Storage::url('qr-codes/' . $qrCode->code . '.png');
                                } elseif (file_exists(public_path('storage/' . $qrCode->qr_code_image))) {
                                    $imageExists = true;
                                    $imageUrl = asset('storage/' . $qrCode->qr_code_image);
                                } elseif (file_exists(public_path($qrCode->qr_code_image))) {
                                    $imageExists = true;
                                    $imageUrl = asset($qrCode->qr_code_image);
                                }
                            }
                            
                            // Coba cari file berdasarkan kode
                            if (!$imageExists && $qrCode->code) {
                                $possiblePaths = [
                                    'qr-codes/' . $qrCode->code . '.png',
                                    'qr-codes/' . strtolower($qrCode->code) . '.png',
                                    'qr-codes/' . strtoupper($qrCode->code) . '.png',
                                    'qr-codes/quick-' . $qrCode->code . '.png',
                                    'qr-codes/bulk-' . $qrCode->code . '.png',
                                    'qr-codes/class-' . $qrCode->code . '.png',
                                ];
                                
                                foreach ($possiblePaths as $path) {
                                    if (Storage::disk('public')->exists($path)) {
                                        $imageExists = true;
                                        $imageUrl = Storage::url($path);
                                        break;
                                    }
                                }
                            }
                        @endphp
                        
                        @if($imageExists && $imageUrl)
                            <img src="{{ $imageUrl }}" 
                                 alt="QR Code {{ $qrCode->code }}" 
                                 class="img-fluid mb-3 border p-2 rounded shadow-sm"
                                 style="max-width: 250px;"
                                 onerror="this.style.display='none'; this.parentElement.querySelector('.qr-fallback').style.display='block';">
                            <div class="qr-fallback" style="display: none;">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Gambar tidak dapat dimuat, tetapi Anda masih bisa menggunakan kode manual.<br>
                                    <strong>Kode: {{ $qrCode->code }}</strong>
                                </div>
                            </div>
                            <p class="text-muted mt-2">Scan QR code di atas untuk absensi</p>
                            <div class="btn-group" role="group">
                                <a href="{{ $imageUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-external-link-alt"></i> Lihat Gambar
                                </a>
                                <a href="{{ route('qr-codes.download', $qrCode) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Gambar QR Code tidak ditemukan</strong><br>
                                <small class="text-muted">Path: {{ $qrCode->qr_code_image ?? 'null' }}</small>
                                <hr>
                                <p class="mb-2">Anda masih dapat menggunakan kode manual untuk absensi:</p>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" value="{{ $qrCode->code }}" id="qrCodeCopy" readonly>
                                    <button class="btn btn-sm btn-primary" onclick="copyQrCode()">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                            
                            <form action="{{ route('qr-codes.update', $qrCode) }}" method="POST" class="mt-3">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="regenerate_qr" value="1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-sync-alt me-2"></i> Generate Ulang QR Code
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Kode</th>
                            <td><code>{{ $qrCode->code }}</code></td>
                        </tr>
                        <tr>
                            <th>Kelas</th>
                            <td>{{ $qrCode->class->class_name }} ({{ $qrCode->class->class_code }})</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $qrCode->date instanceof Carbon\Carbon ? $qrCode->date->format('d F Y') : \Carbon\Carbon::parse($qrCode->date)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Waktu</th>
                            <td>{{ $qrCode->formatted_start_time ?? $qrCode->start_time }} - {{ $qrCode->formatted_end_time ?? $qrCode->end_time }}</td>
                        </tr>
                        <tr>
                            <th>Durasi</th>
                            <td>{{ $qrCode->duration_minutes }} menit</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $qrCode->status_color ?? 'secondary' }}">
                                    {{ $qrCode->status_text ?? ($qrCode->is_active ? 'Aktif' : 'Nonaktif') }}
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
                                    <h2 class="text-primary">{{ $totalStudents ?? 0 }}</h2>
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
                             style="width: {{ $attendancePercentage ?? 0 }}%"
                             aria-valuenow="{{ $attendancePercentage ?? 0 }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $attendancePercentage ?? 0 }}% Kehadiran
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Detail Absensi</h6>
                    @if(isset($attendances) && $attendances->count() > 0)
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
                                        <td>{{ $attendance->student->name ?? 'N/A' }}</td>
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
function copyQrCode() {
    const copyText = document.getElementById('qrCodeCopy');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show notification
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        Kode QR berhasil disalin!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 2000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            if (alert.classList.contains('alert-dismissible')) {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) closeBtn.click();
            }
        });
    }, 5000);
});
</script>

<style>
.qr-fallback {
    margin-top: 10px;
}

#qrCodeCopy {
    font-family: monospace;
    font-size: 12px;
}

.btn-group {
    display: flex;
    gap: 5px;
    justify-content: center;
}
</style>
@endpush