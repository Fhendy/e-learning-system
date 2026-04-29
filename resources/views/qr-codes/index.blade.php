@extends('layouts.app')

@section('title', 'Manajemen QR Code')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon-large">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Manajemen QR Code</h1>
                        <p class="page-subtitle text-muted mb-0">
                            Kelola QR Code untuk absensi kelas
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Buat QR Code
                </a>
            </div>
        </div>
    </div>

    <!-- Hapus notifikasi session bawaan, akan diganti SweetAlert -->

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-filter me-2 text-primary"></i>
                Filter QR Code
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('qr-codes.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" id="statusFilter">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                            <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Mendatang</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('qr-codes.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-repeat me-2"></i>Reset
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- QR Codes List -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="bi bi-table me-2"></i>
                        Daftar QR Code
                    </h5>
                    <p class="card-subtitle text-muted mb-0">
                        {{ $qrCodes->total() }} QR Code ditemukan
                    </p>
                </div>
                <span class="badge bg-primary">{{ $qrCodes->total() }} Data</span>
            </div>
        </div>
        
        @if($qrCodes->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3 ps-md-4">#</th>
                        <th>KODE</th>
                        <th>KELAS</th>
                        <th>TANGGAL</th>
                        <th>WAKTU</th>
                        <th>STATUS</th>
                        <th>SCAN</th>
                        <th class="text-end pe-3 pe-md-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($qrCodes as $qrCode)
                    @php
                        $dateObj = $qrCode->date instanceof \Carbon\Carbon 
                            ? $qrCode->date 
                            : \Carbon\Carbon::parse($qrCode->date);
                        $startTime = $qrCode->start_time;
                        $endTime = $qrCode->end_time;
                        if (strlen($startTime) === 5) $startTime .= ':00';
                        if (strlen($endTime) === 5) $endTime .= ':00';
                        $statusColor = $qrCode->status_color ?? 'secondary';
                        $statusText = $qrCode->status_text ?? ($qrCode->is_active ? 'Aktif' : 'Nonaktif');
                        $attendanceCount = $qrCode->attendances ? $qrCode->attendances->count() : 0;
                    @endphp
                    <tr class="qr-row" data-qr-id="{{ $qrCode->id }}" data-qr-code="{{ $qrCode->code }}">
                        <td class="ps-3 ps-md-4">
                            {{ $loop->iteration + ($qrCodes->currentPage() - 1) * $qrCodes->perPage() }}
                        </td>
                        <td>
                            <code class="qr-code">{{ $qrCode->code }}</code>
                            @if($qrCode->location_restricted)
                                <br><small class="text-muted"><i class="bi bi-geo-alt"></i> Terbatas lokasi</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $qrCode->class->class_name ?? 'N/A' }}</strong>
                            @if(isset($qrCode->class->subject))
                                <br><small class="text-muted">{{ $qrCode->class->subject }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $dateObj->format('d/m/Y') }}
                            <br><small class="text-muted">{{ $dateObj->diffForHumans() }}</small>
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($startTime)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($endTime)->format('H:i') }}
                            <br>
                            <small class="text-muted">
                                {{ $qrCode->duration_minutes ?? $qrCode->duration_minutes_calculated ?? '?' }} menit
                            </small>
                        </td>
                        <td>
                            <span class="status-badge {{ $statusColor == 'success' ? 'active' : ($statusColor == 'danger' ? 'inactive' : 'secondary') }}">
                                <i class="bi bi-circle-fill"></i>
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
                                <div class="fw-bold fs-5">{{ $qrCode->scan_count ?? 0 }}</div>
                                <small class="text-muted">scan</small>
                                <div>
                                    <small class="text-muted">{{ $attendanceCount }} siswa</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-end pe-3 pe-md-4">
                            <div class="btn-group" role="group">
                                <a href="{{ route('qr-codes.show', $qrCode) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   data-bs-toggle="tooltip" 
                                   title="Detail QR Code">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(isset($qrCode->can_be_edited) && $qrCode->can_be_edited)
                                <a href="{{ route('qr-codes.edit', $qrCode) }}" 
                                   class="btn btn-sm btn-outline-warning" 
                                   data-bs-toggle="tooltip" 
                                   title="Edit QR Code">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif
                                @if($qrCode->is_active)
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger toggle-status-btn" 
                                        data-bs-toggle="tooltip" 
                                        title="Nonaktifkan"
                                        data-qr-id="{{ $qrCode->id }}"
                                        data-qr-code="{{ $qrCode->code }}"
                                        data-action="deactivate">
                                    <i class="bi bi-toggle-off"></i>
                                </button>
                                @else
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success toggle-status-btn" 
                                        data-bs-toggle="tooltip" 
                                        title="Aktifkan"
                                        data-qr-id="{{ $qrCode->id }}"
                                        data-qr-code="{{ $qrCode->code }}"
                                        data-action="activate">
                                    <i class="bi bi-toggle-on"></i>
                                </button>
                                @endif
                                @if(isset($qrCode->can_be_deleted) && $qrCode->can_be_deleted)
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger delete-qr-btn" 
                                        data-bs-toggle="tooltip" 
                                        title="Hapus QR Code"
                                        data-qr-id="{{ $qrCode->id }}"
                                        data-qr-code="{{ $qrCode->code }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($qrCodes->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div>
                    <p class="mb-0 text-muted small">
                        Menampilkan <strong>{{ $qrCodes->firstItem() ?? 0 }}</strong> 
                        sampai <strong>{{ $qrCodes->lastItem() ?? 0 }}</strong> 
                        dari <strong>{{ $qrCodes->total() }}</strong> QR Code
                    </p>
                </div>
                <nav aria-label="Page navigation">
                    {{ $qrCodes->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                </nav>
            </div>
        </div>
        @endif

        @else
        <div class="empty-state text-center py-5">
            <div class="empty-icon mx-auto mb-3">
                <i class="bi bi-qr-code fs-1 text-muted"></i>
            </div>
            <h5 class="mb-2">Belum ada QR Code</h5>
            <p class="text-muted mb-4">Mulai dengan membuat QR Code pertama Anda</p>
            <a href="{{ route('qr-codes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Buat QR Code
            </a>
        </div>
        @endif
    </div>
</div>

<style>
/* CSS Variables */
:root {
    --primary: #4f46e5;
    --primary-light: #e0e7ff;
    --success: #10b981;
    --success-light: #d1fae5;
    --warning: #f59e0b;
    --warning-light: #fef3c7;
    --danger: #ef4444;
    --danger-light: #fee2e2;
    --info: #3b82f6;
    --info-light: #dbeafe;
    --secondary: #6b7280;
    --secondary-light: #f3f4f6;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --transition: all 0.2s ease;
}

/* Page Header */
.page-header {
    margin-bottom: 1.5rem;
}

.page-icon-large {
    width: clamp(44px, 10vw, 56px);
    height: clamp(44px, 10vw, 56px);
    border-radius: 14px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(1.25rem, 3vw, 1.5rem);
    flex-shrink: 0;
}

.page-title {
    font-size: clamp(1.25rem, 5vw, 1.5rem);
    font-weight: 700;
    color: #1f2937;
}

.page-subtitle {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Card */
.card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.card-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.875rem 1rem;
}

.card-title {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    font-size: 0.938rem;
}

.card-subtitle {
    font-size: 0.688rem;
    color: #6b7280;
}

.card-footer {
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 0.75rem 1rem;
}

/* Form */
.form-label {
    font-weight: 500;
    font-size: 0.813rem;
    color: #374151;
    margin-bottom: 0.375rem;
}

.form-select,
.form-control {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    font-size: 0.813rem;
    transition: var(--transition);
}

.form-select:focus,
.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

/* Table */
.table {
    margin: 0;
}

.table thead th {
    font-weight: 600;
    font-size: 0.688rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.table tbody td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f9fafb;
}

/* QR Code */
.qr-code {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.688rem;
    color: #374151;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 20px;
    font-size: 0.688rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.active i {
    color: #10b981;
    font-size: 0.5rem;
}

.status-badge.inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.status-badge.inactive i {
    color: #9ca3af;
    font-size: 0.5rem;
}

.status-badge.secondary {
    background: #f1f5f9;
    color: #475569;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: var(--transition);
    font-size: 0.813rem;
}

.btn-sm {
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
}

.btn-group {
    gap: 6px;
}

.btn-outline-primary {
    border-color: #e5e7eb;
    color: #4f46e5;
    background: white;
}

.btn-outline-primary:hover {
    background: #4f46e5;
    border-color: #4f46e5;
    color: white;
}

.btn-outline-warning {
    border-color: #e5e7eb;
    color: #f59e0b;
    background: white;
}

.btn-outline-warning:hover {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.btn-outline-danger {
    border-color: #e5e7eb;
    color: #ef4444;
    background: white;
}

.btn-outline-danger:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.btn-outline-success {
    border-color: #e5e7eb;
    color: #10b981;
    background: white;
}

.btn-outline-success:hover {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-outline-secondary {
    border-color: #e5e7eb;
    color: #6b7280;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #374151;
}

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
}

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-success {
    background: #d1fae5;
    border-color: #10b981;
    color: #065f46;
}

.alert-danger {
    background: #fee2e2;
    border-color: #ef4444;
    color: #991b1b;
}

/* Empty State */
.empty-state {
    padding: 2rem 1rem;
}

.empty-icon {
    width: 64px;
    height: 64px;
    background: #f9fafb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.empty-state h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
}

.empty-state p {
    font-size: 0.813rem;
    color: #6b7280;
}

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.bg-primary {
    background: #4f46e5 !important;
}

/* Colors */
.text-primary { color: #4f46e5 !important; }
.text-muted { color: #6b7280 !important; }
.text-success { color: #10b981 !important; }

/* Responsive */
@media (min-width: 992px) {
    .card-body {
        padding: 1.25rem;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .page-icon-large {
        width: 44px;
        height: 44px;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.625rem;
    }
    
    .btn-group {
        flex-wrap: wrap;
        justify-content: flex-end;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-group {
        flex-wrap: wrap;
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeIn 0.3s ease forwards;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.loading-spinner {
    animation: spin 1s linear infinite;
}
</style>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// SweetAlert Modern
const CustomSwal = {
    showSuccess: (title, message, redirectUrl = null) => {
        Swal.fire({
            title: title,
            text: message,
            icon: 'success',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#4f46e5',
            timer: 2000,
            timerProgressBar: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-primary btn-sm px-4',
            },
            buttonsStyling: false
        }).then(() => {
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });
    },
    
    showError: (title, message) => {
        Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>OK',
            confirmButtonColor: '#ef4444',
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-danger btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    confirmActivate: (qrCodeCode) => {
        return Swal.fire({
            title: 'Aktifkan QR Code',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-toggle-on" style="font-size: 3.5rem; color: #10b981;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">${qrCodeCode}</h6>
                    <p class="mb-3">Apakah Anda yakin ingin mengaktifkan QR Code ini?</p>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        QR Code yang aktif dapat discan oleh siswa.
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Ya, Aktifkan!',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Batal',
            reverseButtons: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-success btn-sm px-4',
                cancelButton: 'btn btn-secondary btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    confirmDeactivate: (qrCodeCode) => {
        return Swal.fire({
            title: 'Nonaktifkan QR Code',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-toggle-off" style="font-size: 3.5rem; color: #ef4444;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">${qrCodeCode}</h6>
                    <p class="mb-3">Apakah Anda yakin ingin menonaktifkan QR Code ini?</p>
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        QR Code yang dinonaktifkan tidak dapat discan oleh siswa.
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Ya, Nonaktifkan!',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Batal',
            reverseButtons: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-danger btn-sm px-4',
                cancelButton: 'btn btn-secondary btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    confirmDelete: (qrCodeCode) => {
        return Swal.fire({
            title: 'Hapus QR Code',
            html: `
                <div class="text-center">
                    <div class="swal-icon-wrapper mb-3">
                        <i class="bi bi-trash3" style="font-size: 3.5rem; color: #ef4444;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">${qrCodeCode}</h6>
                    <p class="mb-3">Apakah Anda yakin ingin menghapus QR Code ini?</p>
                    <div class="alert alert-danger small mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Tindakan ini tidak dapat dibatalkan. Semua data absensi terkait akan dihapus.
                    </div>
                </div>
            `,
            icon: undefined,
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Ya, Hapus!',
            cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Batal',
            reverseButtons: true,
            customClass: {
                popup: 'custom-swal-popup',
                confirmButton: 'btn btn-danger btn-sm px-4',
                cancelButton: 'btn btn-secondary btn-sm px-4',
            },
            buttonsStyling: false
        });
    },
    
    showLoading: (title = 'Memproses...', text = 'Mohon tunggu sebentar') => {
        return Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: {
                popup: 'custom-swal-popup'
            }
        });
    },
    
    closeLoading: () => {
        Swal.close();
    }
};

// Custom CSS untuk SweetAlert
const swalStyles = document.createElement('style');
swalStyles.textContent = `
    .custom-swal-popup {
        border-radius: 16px !important;
        padding: 0 !important;
        width: 420px !important;
        font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif !important;
    }
    
    .custom-swal-popup .swal2-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #1f2937 !important;
        padding: 1.25rem 1.25rem 0 !important;
        margin-bottom: 0 !important;
    }
    
    .custom-swal-popup .swal2-html-container {
        padding: 0 1.25rem 1.25rem !important;
        margin-top: 0 !important;
    }
    
    .custom-swal-popup .swal2-actions {
        padding: 0 1.25rem 1.25rem !important;
        gap: 0.75rem !important;
        margin-top: 0 !important;
    }
    
    .custom-swal-popup .swal2-loader {
        border-color: #4f46e5 !important;
        border-right-color: transparent !important;
    }
    
    .custom-swal-popup .swal2-timer-progress-bar {
        background: linear-gradient(90deg, #4f46e5, #818cf8) !important;
    }
    
    .swal-icon-wrapper {
        margin-top: 0.5rem;
    }
    
    .custom-swal-popup .alert {
        border-radius: 10px;
        font-size: 0.75rem;
        text-align: left;
    }
    
    .custom-swal-popup .alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    
    .custom-swal-popup .alert-info {
        background: #cffafe;
        border: 1px solid #bae6fd;
        color: #155e75;
    }
    
    .custom-swal-popup .alert-warning {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #92400e;
    }
    
    .swal2-toast {
        border-radius: 12px !important;
    }
`;

document.head.appendChild(swalStyles);

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Tampilkan success dari session dengan SweetAlert
    @if(session('success'))
        CustomSwal.showSuccess('Berhasil!', '{{ session('success') }}');
    @endif
    
    @if(session('error'))
        CustomSwal.showError('Gagal!', '{{ session('error') }}');
    @endif
    
    // Toggle Status (Aktifkan/Nonaktifkan) dengan AJAX
    const toggleButtons = document.querySelectorAll('.toggle-status-btn');
    toggleButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const qrId = this.dataset.qrId;
            const qrCode = this.dataset.qrCode;
            const action = this.dataset.action;
            const isActivate = action === 'activate';
            
            let result;
            if (isActivate) {
                result = await CustomSwal.confirmActivate(qrCode);
            } else {
                result = await CustomSwal.confirmDeactivate(qrCode);
            }
            
            if (result.isConfirmed) {
                CustomSwal.showLoading(isActivate ? 'Mengaktifkan...' : 'Menonaktifkan...', 'Mohon tunggu sebentar');
                
                try {
                    const url = isActivate ? `/qr-codes/${qrId}/activate` : `/qr-codes/${qrId}/deactivate`;
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    CustomSwal.closeLoading();
                    
                    if (data.success) {
                        CustomSwal.showSuccess('Berhasil!', data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        CustomSwal.showError('Gagal!', data.message || 'Terjadi kesalahan.');
                    }
                } catch (error) {
                    CustomSwal.closeLoading();
                    console.error('Error:', error);
                    CustomSwal.showError('Error!', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                }
            }
        });
    });
    
    // Delete QR Code dengan AJAX (menggunakan route POST /{qrCode}/delete)
    const deleteButtons = document.querySelectorAll('.delete-qr-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const qrId = this.dataset.qrId;
            const qrCode = this.dataset.qrCode;
            
            const result = await CustomSwal.confirmDelete(qrCode);
            
            if (result.isConfirmed) {
                CustomSwal.showLoading('Menghapus QR Code...', 'Mohon tunggu sebentar');
                
                try {
                    const response = await fetch(`/qr-codes/${qrId}/delete`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    CustomSwal.closeLoading();
                    
                    if (data.success) {
                        CustomSwal.showSuccess('Berhasil!', data.message || 'QR Code berhasil dihapus.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        CustomSwal.showError('Gagal!', data.message || 'Terjadi kesalahan saat menghapus QR Code.');
                    }
                } catch (error) {
                    CustomSwal.closeLoading();
                    console.error('Error:', error);
                    CustomSwal.showError('Error!', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                }
            }
        });
    });
    
    // Auto refresh jika ada QR Code aktif (every 30 seconds)
    let refreshInterval = null;

    function startAutoRefresh() {
        const hasActiveQr = document.querySelector('.status-badge.active');
        if (hasActiveQr && !refreshInterval) {
            refreshInterval = setInterval(function() {
                if (!document.hidden) {
                    location.reload();
                }
            }, 30000);
        } else if (!hasActiveQr && refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    startAutoRefresh();

    document.addEventListener('visibilitychange', function() {
        if (document.hidden && refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        } else if (!document.hidden) {
            startAutoRefresh();
        }
    });
});
</script>
@endsection