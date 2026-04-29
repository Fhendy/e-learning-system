@extends('layouts.app')

@section('title', $class->class_name)

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="class-icon-large">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">{{ $class->class_name }}</h1>
                        <p class="page-subtitle text-muted mb-0">
                            <i class="bi bi-hash me-1"></i>{{ $class->class_code }}
                            @if($class->subject)
                            <span class="mx-2">•</span>
                            <i class="bi bi-book me-1"></i>{{ $class->subject }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if(in_array(auth()->user()->role, ['teacher', 'admin', 'guru']))
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-plus-lg me-2"></i>Tambah
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('students.create') }}?class_id={{ $class->id }}" class="dropdown-item">
                            <i class="bi bi-person-plus me-2"></i>Siswa
                        </a>
                        <a href="{{ route('assignments.teacher.create') }}?class_id={{ $class->id }}" class="dropdown-item">
                            <i class="bi bi-journal-plus me-2"></i>Tugas
                        </a>
                        <a href="{{ route('qr-codes.create') }}?class_id={{ $class->id }}" class="dropdown-item">
                            <i class="bi bi-qr-code me-2"></i>QR Code
                        </a>
                    </div>
                </div>
                <a href="{{ route('classes.edit', $class) }}" class="btn btn-secondary">
                    <i class="bi bi-pencil me-2"></i>Edit
                </a>
                @endif
                <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
            <div class="flex-grow-1">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    @endif

    @php
        $totalStudents = $students->total() ?? 0;
        $presentCount = $attendanceStats['present'] ?? 0;
        $lateCount = $attendanceStats['late'] ?? 0;
        $absentCount = $attendanceStats['absent'] ?? 0;
        $attendanceRate = $attendanceStats['attendance_rate'] ?? 0;
    @endphp

    <!-- Quick Stats -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-primary-light text-primary">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $totalStudents }}</h3>
                        <p class="stats-label mb-0">Total Siswa</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-success-light text-success">
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $presentCount }}</h3>
                        <p class="stats-label mb-0">Hadir</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-warning-light text-warning">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $lateCount }}</h3>
                        <p class="stats-label mb-0">Terlambat</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stats-icon bg-danger-light text-danger">
                        <i class="bi bi-x-circle fs-5"></i>
                    </div>
                    <div>
                        <h3 class="stats-value mb-0">{{ $absentCount }}</h3>
                        <p class="stats-label mb-0">Absen</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Info & Tabs -->
    <div class="row g-3 g-md-4">
        <!-- Class Information -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>
                        Informasi Kelas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-hash text-primary"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Kode Kelas</div>
                                <div class="info-value">{{ $class->class_code }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-book text-primary"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Mata Pelajaran</div>
                                <div class="info-value">{{ $class->subject ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person-badge text-primary"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Wali Kelas</div>
                                <div class="info-value">{{ $class->teacher->name ?? 'Belum ada guru' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar3 text-primary"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Tahun Ajaran</div>
                                <div class="info-value">{{ $class->school_year ?? $class->academic_year ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-calendar-range text-primary"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Semester</div>
                                <div class="info-value">{{ $class->semester ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-power text-primary"></i>
                            </div>
                            <div class="info-text">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    @if($class->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($class->description)
                    <div class="mt-4 pt-3 border-top">
                        <div class="info-label mb-2">Deskripsi</div>
                        <p class="mb-0 small text-muted">{{ $class->description }}</p>
                    </div>
                    @endif
                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-muted">Kehadiran Keseluruhan</span>
                            <span class="small fw-semibold">{{ $attendanceRate }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $attendanceRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Content -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white p-0 p-md-3">
                    <ul class="nav nav-tabs card-header-tabs" id="classTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="students-tab" data-bs-toggle="tab" 
                                    data-bs-target="#students" type="button" role="tab">
                                <i class="bi bi-people me-1 me-md-2"></i>
                                <span>Siswa</span>
                                <span class="badge bg-primary rounded-pill ms-1">{{ $totalStudents }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" 
                                    data-bs-target="#assignments" type="button" role="tab">
                                <i class="bi bi-journal-text me-1 me-md-2"></i>
                                <span>Tugas</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" 
                                    data-bs-target="#attendance" type="button" role="tab">
                                <i class="bi bi-calendar-check me-1 me-md-2"></i>
                                <span>Absensi</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="qrcodes-tab" data-bs-toggle="tab" 
                                    data-bs-target="#qrcodes" type="button" role="tab">
                                <i class="bi bi-qr-code me-1 me-md-2"></i>
                                <span>QR Codes</span>
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- Students Tab -->
                        <div class="tab-pane fade show active" id="students" role="tabpanel">
                            @if($students->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3 ps-md-4">SISWA</th>
                                            <th class="text-center">KEHADIRAN</th>
                                            <th class="text-end pe-3 pe-md-4">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                        @php
                                            $total = $student->attendances_count ?? 0;
                                            $present = $student->present_count ?? 0;
                                            $late = $student->late_count ?? 0;
                                            $attended = $present + $late;
                                            $percentage = $total > 0 ? round(($attended / $total) * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td class="ps-3 ps-md-4">
                                                <div class="d-flex align-items-center gap-2 gap-md-3">
                                                    <div class="student-avatar">
                                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold mb-1">{{ $student->name }}</div>
                                                        <div class="text-muted small">{{ $student->nis_nip }}</div>
                                                    </div>
                                                </div>
                                              </td>
                                            <td class="text-center" style="min-width: 100px;">
                                                <div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small class="text-muted">{{ $attended }}/{{ $total }}</small>
                                                        <small class="fw-semibold">{{ $percentage }}%</small>
                                                    </div>
                                                    <div class="progress" style="height: 5px;">
                                                        <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                </div>
                                              </td>
                                            <td class="text-end pe-3 pe-md-4">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="{{ route('students.show', $student) }}" 
                                                       class="btn btn-icon btn-sm" 
                                                       data-bs-toggle="tooltip" 
                                                       title="Detail Siswa">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if(in_array(auth()->user()->role, ['teacher', 'admin', 'guru']))
                                                    <div class="dropdown">
                                                        <button class="btn btn-icon btn-sm" 
                                                                type="button" 
                                                                data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a href="{{ route('students.edit', $student) }}" class="dropdown-item">
                                                                <i class="bi bi-pencil me-2"></i>Edit
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <button type="button" 
                                                                    class="dropdown-item text-danger"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#removeStudentModal{{ $student->id }}">
                                                                <i class="bi bi-person-dash me-2"></i>Keluarkan
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                              </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="p-3 border-top">
                                {{ $students->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                            </div>
                            @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-icon mb-3">
                                    <i class="bi bi-people fs-1 text-muted"></i>
                                </div>
                                <h5 class="mb-2">Belum ada siswa</h5>
                                <p class="text-muted mb-4">Tambahkan siswa pertama ke kelas ini</p>
                                <a href="{{ route('students.create') }}?class_id={{ $class->id }}" class="btn btn-primary">
                                    <i class="bi bi-person-plus me-2"></i>Tambah Siswa
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Assignments Tab -->
                        <div class="tab-pane fade" id="assignments" role="tabpanel">
                            @if(isset($assignments) && $assignments->count() > 0)
                            <div class="p-3">
                                @foreach($assignments as $assignment)
                                <div class="assignment-card mb-3">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $assignment->title }}</h6>
                                            <p class="text-muted small mb-2">{{ Str::limit($assignment->description, 80) }}</p>
                                            <div class="d-flex flex-wrap align-items-center gap-3">
                                                <span class="badge bg-info">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    {{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y H:i') }}
                                                </span>
                                                <span class="text-muted small">
                                                    <i class="bi bi-journal-check me-1"></i>
                                                    {{ $assignment->submissions_count ?? 0 }}/{{ $totalStudents }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ route('assignments.show', $assignment) }}" 
                                               class="btn btn-sm btn-outline-primary w-100 w-sm-auto">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                <div class="text-center mt-3 pt-2 border-top">
                                    <a href="{{ route('assignments.teacher.index') }}?class_id={{ $class->id }}" 
                                       class="btn btn-outline-primary">
                                        Lihat Semua Tugas
                                    </a>
                                </div>
                            </div>
                            @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-icon mb-3">
                                    <i class="bi bi-journal-text fs-1 text-muted"></i>
                                </div>
                                <h5 class="mb-2">Belum ada tugas</h5>
                                <p class="text-muted mb-4">Buat tugas pertama untuk kelas ini</p>
                                <a href="{{ route('assignments.teacher.create') }}?class_id={{ $class->id }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-journal-plus me-2"></i>Buat Tugas
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Attendance Tab -->
                        <div class="tab-pane fade" id="attendance" role="tabpanel">
                            <div class="p-3">
                                <div class="row g-2 g-md-3 mb-4">
                                    <div class="col-6 col-md-3">
                                        <div class="stat-mini bg-success-light text-center p-3 rounded">
                                            <div class="stat-mini-value text-success fw-bold fs-2">{{ $presentCount }}</div>
                                            <div class="stat-mini-label text-muted small">Hadir</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="stat-mini bg-warning-light text-center p-3 rounded">
                                            <div class="stat-mini-value text-warning fw-bold fs-2">{{ $lateCount }}</div>
                                            <div class="stat-mini-label text-muted small">Terlambat</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="stat-mini bg-danger-light text-center p-3 rounded">
                                            <div class="stat-mini-value text-danger fw-bold fs-2">{{ $absentCount }}</div>
                                            <div class="stat-mini-label text-muted small">Absen</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="stat-mini bg-primary-light text-center p-3 rounded">
                                            <div class="stat-mini-value text-primary fw-bold fs-2">{{ $attendanceRate }}%</div>
                                            <div class="stat-mini-label text-muted small">Rata-rata</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="mb-3">Aksi Cepat</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('attendance.teacher.manual.create') }}" class="btn btn-primary">
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil-square me-2"></i>Input Manual
                                        </a>
                                        <a href="{{ route('qr-codes.create') }}?class_id={{ $class->id }}" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="bi bi-qr-code me-2"></i>Buat QR Code
                                        </a>
                                        <a href="{{ route('attendance.teacher.index') }}?class_id={{ $class->id }}" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-calendar-check me-2"></i>Detail Absensi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Codes Tab -->
                        <div class="tab-pane fade" id="qrcodes" role="tabpanel">
                            @if(isset($recentQrCodes) && $recentQrCodes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3 ps-md-4">TANGGAL</th>
                                            <th>WAKTU</th>
                                            <th>STATUS</th>
                                            <th class="text-end pe-3 pe-md-4">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentQrCodes as $qrCode)
                                        <tr>
                                            <td class="ps-3 ps-md-4">{{ \Carbon\Carbon::parse($qrCode->date)->format('d/m/Y') }}</td>
                                            <td>{{ $qrCode->start_time }} - {{ $qrCode->end_time }}</td>
                                            <td>
                                                @if($qrCode->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3 pe-md-4">
                                                <a href="{{ route('qr-codes.show', $qrCode) }}" 
                                                   class="btn btn-icon btn-sm"
                                                   title="Detail QR Code">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center p-3 border-top">
                                <a href="{{ route('qr-codes.index') }}?class_id={{ $class->id }}" 
                                   class="btn btn-outline-primary">
                                    Lihat Semua QR Code
                                </a>
                            </div>
                            @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-icon mb-3">
                                    <i class="bi bi-qr-code fs-1 text-muted"></i>
                                </div>
                                <h5 class="mb-2">Belum ada QR Code</h5>
                                <p class="text-muted mb-4">Buat QR Code untuk absensi kelas</p>
                                <a href="{{ route('qr-codes.create') }}?class_id={{ $class->id }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-qr-code me-2"></i>Buat QR Code
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remove Student Modals -->
@foreach($students as $student)
<div class="modal fade" id="removeStudentModal{{ $student->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="student-avatar-lg mx-auto mb-3">
                    {{ strtoupper(substr($student->name, 0, 1)) }}
                </div>
                <h6 class="mb-1">{{ $student->name }}</h6>
                <p class="text-muted small mb-3">{{ $student->nis_nip }}</p>
                <p class="mb-3">Yakin ingin mengeluarkan <strong>{{ $student->name }}</strong> dari kelas <strong>{{ $class->class_name }}</strong>?</p>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Data absensi dan tugas akan tetap tersimpan.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('classes.remove-student', [$class, $student]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Keluarkan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

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
    --info: #06b6d4;
    --info-light: #cffafe;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

/* Page Header */
.page-header {
    margin-bottom: 1.5rem;
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

.class-icon-large {
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

/* Stats Cards */
.stats-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 0.875rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stats-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.stats-label {
    font-size: 0.688rem;
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

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.info-icon {
    width: 28px;
    flex-shrink: 0;
}

.info-icon i {
    font-size: 1rem;
    color: #4f46e5;
}

.info-text {
    flex: 1;
}

.info-label {
    font-size: 0.688rem;
    color: #6b7280;
    margin-bottom: 0.125rem;
}

.info-value {
    font-size: 0.813rem;
    font-weight: 500;
    color: #1f2937;
}

/* Student Avatar */
.student-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.student-avatar-lg {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.5rem;
    margin: 0 auto;
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
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.table tbody td {
    padding: 0.875rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f9fafb;
}

/* Progress */
.progress {
    border-radius: 4px;
    background: #e5e7eb;
    overflow: hidden;
}

.progress-bar {
    background: #10b981;
}

/* Assignment Card */
.assignment-card {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.875rem;
    transition: all 0.2s ease;
}

.assignment-card:hover {
    border-color: #4f46e5;
    box-shadow: var(--shadow-sm);
}

/* Stat Mini */
.stat-mini {
    background: #f8fafc;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.stat-mini:hover {
    transform: translateY(-2px);
}

.stat-mini-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-mini-label {
    font-size: 0.688rem;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    font-size: 0.813rem;
    transition: all 0.2s ease;
}

.btn-sm {
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
}

.btn-icon {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    background: white;
}

.btn-icon:hover {
    background: #f9fafb;
    color: #4f46e5;
    border-color: #d1d5db;
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

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

/* Tabs */
.nav-tabs {
    border-bottom: 1px solid #e5e7eb;
    gap: 0.25rem;
    padding: 0 0.5rem;
}

.nav-tabs .nav-link {
    border: none;
    border-radius: 8px 8px 0 0;
    padding: 0.625rem 0.875rem;
    color: #6b7280;
    font-weight: 500;
    font-size: 0.813rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.nav-tabs .nav-link:hover {
    color: #4f46e5;
    background: #f9fafb;
}

.nav-tabs .nav-link.active {
    color: #4f46e5;
    background: white;
    border-bottom: 2px solid #4f46e5;
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

/* Dropdown */
.dropdown-menu {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.375rem;
    min-width: 150px;
}

.dropdown-item {
    border-radius: 6px;
    padding: 0.375rem 0.75rem;
    font-size: 0.813rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #1f2937;
}

.dropdown-item:hover {
    background: #f9fafb;
}

.dropdown-item.text-danger:hover {
    background: #fee2e2;
}

/* Modal */
.modal-content {
    background: white;
    border: none;
    border-radius: var(--border-radius);
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

/* Alert */
.alert-info {
    background: #cffafe;
    border-color: #06b6d4;
    color: #155e75;
}

/* Border */
.border-top {
    border-top: 1px solid #e5e7eb !important;
}

/* Colors */
.bg-primary-light { background: #e0e7ff; }
.bg-success-light { background: #d1fae5; }
.bg-warning-light { background: #fef3c7; }
.bg-danger-light { background: #fee2e2; }
.bg-info-light { background: #cffafe; }

.text-primary { color: #4f46e5 !important; }
.text-success { color: #10b981 !important; }
.text-warning { color: #f59e0b !important; }
.text-danger { color: #ef4444 !important; }

/* Responsive */
@media (min-width: 992px) {
    .stats-icon {
        width: 44px;
        height: 44px;
    }
    
    .stats-value {
        font-size: 1.375rem;
    }
    
    .nav-tabs .nav-link {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 768px) {
    .stats-card {
        padding: 0.75rem;
    }
    
    .stats-icon {
        width: 32px;
        height: 32px;
    }
    
    .stats-icon i {
        font-size: 0.875rem;
    }
    
    .stats-value {
        font-size: 1rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.625rem;
    }
    
    .student-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .nav-tabs {
        gap: 0.125rem;
        padding: 0;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 0.625rem;
        font-size: 0.75rem;
    }
    
    .btn-icon {
        width: 28px;
        height: 28px;
    }
}

@media (max-width: 576px) {
    .info-item {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-icon {
        width: auto;
    }
    
    .assignment-card .d-flex {
        flex-direction: column;
        align-items: stretch;
    }
    
    .assignment-card .btn {
        width: 100%;
    }
    
    .table-responsive {
        border: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality with localStorage
    const triggerTabList = [].slice.call(document.querySelectorAll('#classTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
            
            const activeTab = triggerEl.getAttribute('data-bs-target');
            if (activeTab) {
                localStorage.setItem('activeClassTab', activeTab);
            }
        });
    });
    
    // Restore active tab
    const activeTab = localStorage.getItem('activeClassTab');
    if (activeTab) {
        const triggerEl = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (triggerEl) {
            const tab = bootstrap.Tab.getInstance(triggerEl);
            if (tab) tab.show();
        }
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection