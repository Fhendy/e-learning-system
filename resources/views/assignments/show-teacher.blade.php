@extends('layouts.app')

@section('title', 'Detail Tugas: ' . $assignment->title)

@section('content')
<div class="container-fluid">
    <!-- Assignment Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-primary">{{ $assignment->title }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.teacher') }}">
                        <i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.teacher.index') }}">
                        <i class="bi bi-journal-text me-1"></i>Tugas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('assignments.teacher.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear me-2"></i>Aksi
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('assignments.teacher.edit', $assignment) }}">
                            <i class="bi bi-pencil me-2"></i>Edit Tugas
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('submissions.export-grades', $assignment) }}">
                            <i class="bi bi-download me-2"></i>Export Nilai
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteAssignmentModal">
                            <i class="bi bi-trash me-2"></i>Hapus Tugas
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Alert Notification -->
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

    <div class="row">
        <!-- Left Column - Assignment Details -->
        <div class="col-lg-8">
            <!-- Assignment Info Card -->
            <div class="card shadow mb-4 border-primary">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-info-circle me-2"></i>Detail Tugas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-primary mb-3">
                                <i class="bi bi-journal-text me-2"></i>{{ $assignment->title }}
                            </h4>
                            
                            <!-- Description -->
                            <div class="mb-4">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="bi bi-card-text me-1"></i>Deskripsi:
                                </h6>
                                <div class="p-3 bg-light rounded border">
                                    {!! nl2br(e($assignment->description)) !!}
                                </div>
                            </div>
                            
                            <!-- Information Grid -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-card p-3 border rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="icon-wrapper bg-info rounded-circle p-2 me-3">
                                                <i class="bi bi-people text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Kelas</h6>
                                                <div class="text-muted small">
                                                    <span class="badge bg-info">{{ $assignment->class->class_code }}</span>
                                                    {{ $assignment->class->class_name }}
                                                    <span class="badge bg-secondary ms-2">
                                                        <i class="bi bi-person me-1"></i>{{ $assignment->class->students->count() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-card p-3 border rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="icon-wrapper bg-primary rounded-circle p-2 me-3">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Guru</h6>
                                                <div class="text-muted">{{ $assignment->teacher->name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-card p-3 border rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="icon-wrapper bg-{{ $assignment->isPastDue() ? 'danger' : 'success' }} rounded-circle p-2 me-3">
                                                <i class="bi bi-calendar text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Batas Waktu</h6>
                                                <div class="{{ $assignment->isPastDue() ? 'text-danger' : 'text-success' }} fw-bold">
                                                    {{ $assignment->due_date->format('d F Y, H:i') }}
                                                    @if($assignment->isPastDue())
                                                        <span class="badge bg-danger ms-2">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>Selesai
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success ms-2">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ now()->diffForHumans($assignment->due_date, true) }} lagi
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-card p-3 border rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="icon-wrapper bg-warning rounded-circle p-2 me-3">
                                                <i class="bi bi-star text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Nilai Maksimal</h6>
                                                <span class="badge bg-warning fs-6">{{ $assignment->max_score }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats Column -->
                        <div class="col-md-4">
                            <div class="stats-container border-start border-primary border-3 ps-3">
                                <h6 class="font-weight-bold text-primary mb-3">
                                    <i class="bi bi-graph-up me-2"></i>Statistik
                                </h6>
                                
                                @php
                                    $totalStudents = $assignment->class->students->count();
                                    $submitted = $assignment->submissions->count();
                                    $graded = $assignment->submissions()->whereNotNull('score')->count();
                                    $late = $assignment->submissions()->where('status', 'late')->count();
                                    $submissionPercentage = $totalStudents > 0 ? round(($submitted / $totalStudents) * 100) : 0;
                                    $gradedPercentage = $submitted > 0 ? round(($graded / $submitted) * 100) : 0;
                                @endphp
                                
                                <!-- Submission Progress -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">Pengumpulan</span>
                                        <span class="badge bg-success">{{ $submissionPercentage }}%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $submissionPercentage }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">{{ $submitted }}/{{ $totalStudents }}</small>
                                        <small class="text-muted">Siswa</small>
                                    </div>
                                </div>
                                
                                <!-- Grading Progress -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">Sudah Dinilai</span>
                                        <span class="badge bg-info">{{ $gradedPercentage }}%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $gradedPercentage }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">{{ $graded }}/{{ $submitted }}</small>
                                        <small class="text-muted">Pengumpulan</small>
                                    </div>
                                </div>
                                
                                <!-- Stats Grid -->
                                <div class="stats-grid mt-4">
                                    <div class="stat-item text-center p-2 bg-danger bg-opacity-10 rounded">
                                        <div class="stat-number text-danger">{{ $late }}</div>
                                        <div class="stat-label">
                                            <i class="bi bi-clock-history me-1"></i>Terlambat
                                        </div>
                                    </div>
                                    <div class="stat-item text-center p-2 bg-warning bg-opacity-10 rounded">
                                        <div class="stat-number text-warning">{{ $submitted - $graded }}</div>
                                        <div class="stat-label">
                                            <i class="bi bi-hourglass me-1"></i>Belum Dinilai
                                        </div>
                                    </div>
                                    <div class="stat-item text-center p-2 bg-secondary bg-opacity-10 rounded">
                                        <div class="stat-number text-secondary">{{ $totalStudents - $submitted }}</div>
                                        <div class="stat-label">
                                            <i class="bi bi-person-x me-1"></i>Belum Kumpul
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attachment Section -->
                    @if($assignment->attachment)
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="bi bi-paperclip me-2"></i>Lampiran Tugas
                        </h6>
                        <div class="attachment-box p-3 border rounded bg-light">
                            <div class="d-flex align-items-center">
                                <div class="attachment-icon">
                                    <i class="bi bi-file-earmark-text fa-3x text-primary"></i>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <div class="fw-bold">{{ basename($assignment->attachment) }}</div>
                                    <div class="text-muted small">File lampiran tugas dari guru</div>
                                </div>
                                <div class="btn-group">
                                    <a href="{{ Storage::url($assignment->attachment) }}" 
                                       class="btn btn-primary" target="_blank" download>
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                    <a href="{{ Storage::url($assignment->attachment) }}" 
                                       class="btn btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Submissions Table -->
            <div class="card shadow border-info">
                <div class="card-header bg-info text-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-people me-2"></i>Pengumpulan Siswa
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm" id="gradeSelected">
                            <i class="bi bi-check-square me-1"></i>Nilai Terpilih
                        </button>
                        <a href="{{ route('submissions.export-grades', $assignment) }}" 
                           class="btn btn-light btn-sm">
                            <i class="bi bi-download me-1"></i>Export Nilai
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="submissionsTable">
                                <thead>
                                    <tr class="table-light">
                                        <th width="50" class="text-center">
                                            <input type="checkbox" id="selectAllSubmissions">
                                        </th>
                                        <th>Siswa</th>
                                        <th>Status</th>
                                        <th>Tanggal Submit</th>
                                        <th>Nilai</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submissions as $submission)
                                    @php
                                        $isLate = $submission->status == 'late';
                                        $isGraded = $submission->status == 'graded';
                                        $isSubmitted = $submission->status == 'submitted';
                                    @endphp
                                    <tr>
                                        <td class="text-center align-middle">
                                            <input type="checkbox" class="submission-check" 
                                                   value="{{ $submission->id }}"
                                                   {{ $isGraded ? 'disabled' : '' }}>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="student-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <strong>{{ substr($submission->student->name, 0, 1) }}</strong>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $submission->student->name }}</div>
                                                    <div class="text-muted small">
                                                        <i class="bi bi-person-badge me-1"></i>{{ $submission->student->nis_nip }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($isLate)
                                                <span class="badge bg-danger py-2 px-3">
                                                    <i class="bi bi-clock-history me-1"></i>Terlambat
                                                </span>
                                            @elseif($isGraded)
                                                <span class="badge bg-success py-2 px-3">
                                                    <i class="bi bi-check-circle me-1"></i>Dinilai
                                                </span>
                                            @else
                                                <span class="badge bg-info py-2 px-3">
                                                    <i class="bi bi-upload me-1"></i>Dikumpulkan
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div>
                                                <i class="bi bi-calendar me-1"></i>{{ $submission->submitted_at->format('d/m/Y') }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-clock me-1"></i>{{ $submission->submitted_at->format('H:i') }}
                                            </div>
                                            @if($isLate)
                                                <div class="text-danger small mt-1">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    +{{ $assignment->due_date->diffInHours($submission->submitted_at) }} jam
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @if($submission->score)
                                                <span class="score-badge badge bg-{{ $submission->score >= 80 ? 'success' : ($submission->score >= 60 ? 'warning' : 'danger') }} py-2 px-3">
                                                    {{ $submission->score }}
                                                </span>
                                                <div class="text-muted small mt-1">
                                                    dari {{ $assignment->max_score }}
                                                </div>
                                            @else
                                                <span class="badge bg-secondary py-2 px-3">Belum</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="viewSubmission({{ $submission->id }})"
                                                        title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning"
                                                        onclick="gradeSubmission({{ $submission->id }})"
                                                        title="Nilai Tugas"
                                                        {{ $isGraded ? 'disabled' : '' }}>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @if($submission->attachment)
                                                <a href="{{ Storage::url($submission->attachment) }}" 
                                                   class="btn btn-sm btn-success" target="_blank" title="Download File">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-people fa-4x text-gray-300 mb-3"></i>
                            <h5 class="text-muted">Belum ada pengumpulan</h5>
                            <p class="text-muted">Siswa belum mengumpulkan tugas ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Stats & Actions -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4 border-warning">
                <div class="card-header bg-warning text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-lightning me-2"></i>Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('assignments.teacher.edit', $assignment) }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center py-3">
                            <div class="icon-wrapper bg-primary rounded-circle p-2 me-3">
                                <i class="bi bi-pencil text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">Edit Tugas</div>
                                <small class="text-muted">Ubah informasi tugas</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center py-3"
                           data-bs-toggle="modal" data-bs-target="#extendDeadlineModal">
                            <div class="icon-wrapper bg-warning rounded-circle p-2 me-3">
                                <i class="bi bi-clock text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">Perpanjang Waktu</div>
                                <small class="text-muted">Perpanjang batas waktu</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center py-3"
                           data-bs-toggle="modal" data-bs-target="#announcementModal">
                            <div class="icon-wrapper bg-info rounded-circle p-2 me-3">
                                <i class="bi bi-megaphone text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">Beri Pengumuman</div>
                                <small class="text-muted">Beri informasi ke siswa</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                        
                        <button onclick="downloadAllSubmissions()" 
                                class="list-group-item list-group-item-action d-flex align-items-center py-3">
                            <div class="icon-wrapper bg-success rounded-circle p-2 me-3">
                                <i class="bi bi-download text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">Download Semua</div>
                                <small class="text-muted">Download semua pengumpulan</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Not Submitted -->
            <div class="card shadow mb-4 border-danger">
                <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-person-x me-2"></i>Belum Mengumpulkan
                    </h6>
                    <span class="badge bg-light text-danger">{{ $totalStudents - $submitted }}</span>
                </div>
                <div class="card-body">
                    @php
                        $submittedIds = $assignment->submissions->pluck('student_id')->toArray();
                        $notSubmitted = $assignment->class->students->whereNotIn('id', $submittedIds);
                    @endphp
                    
                    @if($notSubmitted->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($notSubmitted->take(5) as $student)
                            <div class="list-group-item d-flex align-items-center py-2">
                                <div class="student-avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <strong>{{ substr($student->name, 0, 1) }}</strong>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $student->name }}</div>
                                    <div class="text-muted small">{{ $student->nis_nip }}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="remindStudent({{ $student->id }})"
                                        title="Kirim Pengingat">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            </div>
                            @endforeach
                            
                            @if($notSubmitted->count() > 5)
                            <div class="list-group-item text-center py-2">
                                <small class="text-muted">
                                    dan {{ $notSubmitted->count() - 5 }} siswa lainnya
                                </small>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle fa-3x text-success mb-3"></i>
                            <h6 class="text-success fw-bold">Selamat!</h6>
                            <p class="text-muted mb-0">Semua siswa sudah mengumpulkan!</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assignment Timeline -->
            <div class="card shadow border-primary">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-clock-history me-2"></i>Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Created -->
                        <div class="timeline-item">
                            <div class="timeline-icon bg-primary">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-bold">Tugas Dibuat</div>
                                <div class="text-muted small">{{ $assignment->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        
                        <!-- Deadline -->
                        <div class="timeline-item">
                            <div class="timeline-icon bg-warning">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-bold">Batas Waktu</div>
                                <div class="text-muted small">{{ \Carbon\Carbon::parse($assignment->due_date)->format('d F Y, H:i') }}</div>
                            </div>
                        </div>
                        
                        <!-- First Submission -->
                        <div class="timeline-item">
                            <div class="timeline-icon bg-success">
                                <i class="bi bi-upload"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-bold">Pengumpulan Pertama</div>
                                <div class="text-muted small">
                                    @if($submissions->count() > 0)
                                        {{ $submissions->sortBy('submitted_at')->first()->submitted_at->format('d/m/Y H:i') }}
                                    @else
                                        Belum ada
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Last Graded -->
                        <div class="timeline-item">
                            <div class="timeline-icon bg-info">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-bold">Penilaian Terakhir</div>
                                <div class="text-muted small">
                                    @php
                                        $lastGraded = $assignment->submissions()
                                            ->whereNotNull('score')
                                            ->orderBy('updated_at', 'desc')
                                            ->first();
                                    @endphp
                                    @if($lastGraded)
                                        {{ $lastGraded->updated_at->format('d/m/Y H:i') }}
                                    @else
                                        Belum ada
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('assignments.modals.view-submission')
@include('assignments.modals.grade-submission')
@include('assignments.modals.extend-deadline')
@include('assignments.modals.send-announcement')

<!-- Batch Grade Modal -->
<div class="modal fade" id="batchGradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nilai Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="batchGradeForm" method="POST">
                @csrf
                <input type="hidden" name="submission_ids" id="batchSubmissionIds">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="batchScore" class="form-label">Nilai</label>
                        <input type="number" class="form-control" id="batchScore" name="score" 
                               min="0" max="{{ $assignment->max_score }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="batchFeedback" class="form-label">Feedback (Opsional)</label>
                        <textarea class="form-control" id="batchFeedback" name="feedback" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus tugas:</p>
                <h5 class="text-danger">"{{ $assignment->title }}"</h5>
                <div class="alert alert-danger mt-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Peringatan:</strong> Tindakan ini akan menghapus:
                    <ul class="mt-2 mb-0">
                        <li>Semua pengumpulan siswa ({{ $submissions->count() }} data)</li>
                        <li>Semua nilai yang sudah diberikan</li>
                        <li>File lampiran tugas</li>
                    </ul>
                    <p class="mt-2 mb-0"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('assignments.teacher.destroy', $assignment) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Ya, Hapus Tugas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom CSS */
.student-avatar {
    width: 40px;
    height: 40px;
    font-size: 16px;
}
.student-avatar-sm {
    width: 35px;
    height: 35px;
    font-size: 14px;
}
.icon-wrapper {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.score-badge {
    font-size: 14px;
    min-width: 50px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 20px;
}
.stat-item {
    padding: 10px;
    border-radius: 8px;
}
.stat-number {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 5px;
}
.stat-label {
    font-size: 11px;
    color: #6c757d;
}
.attachment-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e3e6f0, #4e73df);
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-icon {
    position: absolute;
    left: -30px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    z-index: 1;
}
.timeline-content {
    padding-bottom: 10px;
}
.info-card {
    transition: transform 0.2s;
}
.info-card:hover {
    transform: translateY(-2px);
}
.submission-check:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
// Select All Submissions
document.getElementById('selectAllSubmissions')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.submission-check:not(:disabled)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

// Grade Selected Submissions
document.getElementById('gradeSelected')?.addEventListener('click', function() {
    const selected = Array.from(document.querySelectorAll('.submission-check:checked:not(:disabled)'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        alert('Pilih pengumpulan yang akan dinilai terlebih dahulu');
        return;
    }
    
    if (selected.length === 1) {
        gradeSubmission(selected[0]);
    } else {
        // Show batch grade modal
        const modal = new bootstrap.Modal(document.getElementById('batchGradeModal'));
        document.getElementById('batchSubmissionIds').value = JSON.stringify(selected);
        modal.show();
    }
});

// View Submission
function viewSubmission(submissionId) {
    fetch(`/submissions/${submissionId}`)
        .then(response => response.text())
        .then(html => {
            const modal = new bootstrap.Modal(document.getElementById('viewSubmissionModal'));
            document.getElementById('viewSubmissionContent').innerHTML = html;
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data pengumpulan');
        });
}

// Grade Submission
function gradeSubmission(submissionId) {
    fetch(`/submissions/${submissionId}/get`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate grade form
                const modal = new bootstrap.Modal(document.getElementById('gradeSubmissionModal'));
                const form = document.getElementById('gradeForm');
                form.action = `/submissions/${submissionId}/grade`;
                document.querySelector('#gradeForm input[name="score"]').value = data.submission.score || '';
                document.querySelector('#gradeForm textarea[name="feedback"]').value = data.submission.feedback || '';
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data pengumpulan');
        });
}

// Remind Student
function remindStudent(studentId) {
    if (confirm('Kirim pengingat ke siswa ini?')) {
        fetch(`/submissions/students/${studentId}/remind`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                assignment_id: {{ $assignment->id }},
                message: 'Ingatkan untuk mengumpulkan tugas "' + '{{ $assignment->title }}' + '"'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pengingat berhasil dikirim ke siswa');
            }
        });
    }
}

// Update selected count
function updateSelectedCount() {
    const selected = document.querySelectorAll('.submission-check:checked:not(:disabled)');
    const gradeBtn = document.getElementById('gradeSelected');
    
    if (selected.length > 0) {
        gradeBtn.innerHTML = `<i class="bi bi-check-square me-1"></i>Nilai (${selected.length})`;
        gradeBtn.classList.remove('btn-light');
        gradeBtn.classList.add('btn-primary');
    } else {
        gradeBtn.innerHTML = `<i class="bi bi-check-square me-1"></i>Nilai Terpilih`;
        gradeBtn.classList.remove('btn-primary');
        gradeBtn.classList.add('btn-light');
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to checkboxes
    document.querySelectorAll('.submission-check').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Initialize count
    updateSelectedCount();
    
    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            if (alert.classList.contains('show')) {
                new bootstrap.Alert(alert).close();
            }
        });
    }, 5000);
});
</script>
@endsection