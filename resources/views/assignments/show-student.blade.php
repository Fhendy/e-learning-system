@extends('layouts.app')

@section('title', 'Tugas: ' . $assignment->title)

@section('content')
@php
    // Parse dates safely
    use Carbon\Carbon;
    
    $dueDate = $assignment->due_date instanceof Carbon 
        ? $assignment->due_date 
        : Carbon::parse($assignment->due_date);
    
    $isPastDue = $dueDate->isPast();
    
    if ($submission) {
        $submittedAt = $submission->submitted_at instanceof Carbon 
            ? $submission->submitted_at 
            : Carbon::parse($submission->submitted_at);
    }
@endphp

<div class="container-fluid">
    <!-- Assignment Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-primary">{{ $assignment->title }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.student') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.student.index') }}">Tugas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('assignments.student.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
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

    <div class="row">
        <!-- Left Column - Assignment Details -->
        <div class="col-lg-8">
            <!-- Assignment Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">Detail Tugas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-primary mb-3">
                                <i class="bi bi-journal-text me-2"></i>{{ $assignment->title }}
                            </h4>
                            
                            <div class="assignment-description mb-4">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="bi bi-card-text me-1"></i>Deskripsi Tugas:
                                </h6>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($assignment->description)) !!}
                                </div>
                            </div>
                            
                            <div class="assignment-meta">
                                <h6 class="font-weight-bold text-dark mb-3">
                                    <i class="bi bi-info-circle me-1"></i>Informasi:
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="bi bi-people text-primary me-2"></i>
                                                <span class="fw-bold">Kelas:</span>
                                            </div>
                                            <div class="ps-4">
                                                {{ $assignment->class->class_name ?? 'Tidak ada kelas' }}
                                                <span class="badge bg-info ms-2">{{ $assignment->class->class_code ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="bi bi-person text-primary me-2"></i>
                                                <span class="fw-bold">Guru:</span>
                                            </div>
                                            <div class="ps-4">
                                                {{ $assignment->teacher->name ?? 'Tidak ada guru' }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="info-item mb-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="bi bi-calendar text-primary me-2"></i>
                                                <span class="fw-bold">Batas Waktu:</span>
                                            </div>
                                            <div class="ps-4">
                                                <span class="{{ $isPastDue ? 'text-danger' : 'text-success' }} fw-bold">
                                                    {{ $dueDate->format('d F Y, H:i') }}
                                                </span>
                                                <div class="{{ $isPastDue ? 'text-danger' : 'text-success' }} small">
                                                    <i class="bi bi-clock me-1"></i>
                                                    @if($isPastDue)
                                                        Tugas sudah selesai
                                                    @else
                                                        {{ now()->diffForHumans($dueDate, true) }} lagi
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item mb-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="bi bi-star text-primary me-2"></i>
                                                <span class="fw-bold">Nilai Maksimal:</span>
                                            </div>
                                            <div class="ps-4">
                                                <span class="badge bg-primary">{{ $assignment->max_score }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="status-card border-start border-3 border-{{ $submission ? ($submission->status == 'graded' ? 'success' : ($submission->status == 'late' ? 'danger' : 'info')) : 'warning' }} ps-3">
                                <h6 class="font-weight-bold text-primary mb-3">
                                    <i class="bi bi-clipboard-check me-2"></i>Status Pengumpulan
                                </h6>
                                
                                @if($submission)
                                    <div class="status-indicator mb-3">
                                        @if($submission->status == 'graded')
                                            <div class="text-center mb-3">
                                                <i class="bi bi-check-circle-fill fa-3x text-success"></i>
                                            </div>
                                            <h5 class="text-center text-success mb-2">Tugas Sudah Dinilai</h5>
                                            <div class="text-center">
                                                @php
                                                    $score = $submission->score ?? 0;
                                                    $percentage = $assignment->max_score > 0 ? round(($score / $assignment->max_score) * 100) : 0;
                                                    $scoreColor = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
                                                @endphp
                                                <span class="score-display badge bg-{{ $scoreColor }} fs-4">
                                                    {{ $score }}/{{ $assignment->max_score }}
                                                </span>
                                            </div>
                                        @elseif($submission->status == 'late')
                                            <div class="text-center mb-3">
                                                <i class="bi bi-clock-history fa-3x text-danger"></i>
                                            </div>
                                            <h5 class="text-center text-danger mb-2">Terkumpul Terlambat</h5>
                                            <div class="text-center text-muted">
                                                {{ $submittedAt->format('d/m/Y H:i') }}
                                            </div>
                                        @else
                                            <div class="text-center mb-3">
                                                <i class="bi bi-check-circle fa-3x text-info"></i>
                                            </div>
                                            <h5 class="text-center text-info mb-2">Terkumpul</h5>
                                            <div class="text-center text-muted">
                                                {{ $submittedAt->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($submission->feedback)
                                    <div class="feedback-box mt-3 p-3 bg-light rounded">
                                        <h6 class="font-weight-bold mb-2">
                                            <i class="bi bi-chat-left-text me-2"></i>Feedback dari Guru:
                                        </h6>
                                        <p class="mb-0">{{ $submission->feedback }}</p>
                                    </div>
                                    @endif
                                @else
                                    <div class="status-indicator mb-3">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-exclamation-triangle fa-3x text-warning"></i>
                                        </div>
                                        <h5 class="text-center text-warning mb-2">Belum Dikumpulkan</h5>
                                        <div class="text-center text-muted">
                                            @if($isPastDue)
                                                <span class="text-danger">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Batas waktu telah lewat
                                                </span>
                                            @else
                                                <i class="bi bi-clock me-1"></i>
                                                {{ now()->diffForHumans($dueDate, true) }} lagi
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if(!$isPastDue)
                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitModal">
                                            <i class="bi bi-upload me-2"></i>Kumpulkan Tugas
                                        </button>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attachment from Teacher -->
                    @if($assignment->attachment)
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="bi bi-paperclip me-2"></i>Lampiran dari Guru
                        </h6>
                        <div class="attachment-box p-3 border rounded bg-light">
                            <div class="d-flex align-items-center">
                                <div class="attachment-icon">
                                    <i class="bi bi-file-earmark-text fa-3x text-primary"></i>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <div class="fw-bold">{{ basename($assignment->attachment) }}</div>
                                    <div class="text-muted small">File lampiran tugas</div>
                                </div>
                                <div>
                                    <a href="{{ Storage::url($assignment->attachment) }}" 
                                       class="btn btn-sm btn-primary" target="_blank" download>
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Student's Submission -->
                    @if($submission)
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="bi bi-upload me-2"></i>Pengumpulan Anda
                        </h6>
                        
                        @if($submission->submission_text)
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-dark mb-2">Jawaban Teks:</h6>
                            <div class="p-3 border rounded bg-light">
                                {!! nl2br(e($submission->submission_text)) !!}
                            </div>
                        </div>
                        @endif
                        
                        @if($submission->attachment)
                        <div>
                            <h6 class="font-weight-bold text-dark mb-2">File Lampiran:</h6>
                            <div class="attachment-box p-3 border rounded bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="attachment-icon">
                                        <i class="bi bi-file-earmark-arrow-up fa-3x text-success"></i>
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <div class="fw-bold">{{ basename($submission->attachment) }}</div>
                                        <div class="text-muted small">File yang Anda kumpulkan</div>
                                    </div>
                                    <div>
                                        <a href="{{ Storage::url($submission->attachment) }}" 
                                           class="btn btn-sm btn-success" target="_blank" download>
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="mt-3 text-muted small">
                            <i class="bi bi-clock me-1"></i>
                            Dikumpulkan pada: {{ $submittedAt->format('d F Y, H:i:s') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Actions & Info -->
        <div class="col-lg-4">
            <!-- Action Buttons -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-lightning me-2"></i>Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!$submission && !$isPastDue)
                            <button type="button" class="btn btn-primary btn-lg" 
                                    data-bs-toggle="modal" data-bs-target="#submitModal">
                                <i class="bi bi-upload me-2"></i>Kumpulkan Tugas
                            </button>
                            
                            <button type="button" class="btn btn-outline-primary" 
                                    onclick="saveAsDraft()">
                                <i class="bi bi-save me-2"></i>Simpan sebagai Draft
                            </button>
                        @elseif($submission && !$submission->score && !$isPastDue)
                            <button type="button" class="btn btn-warning btn-lg" 
                                    data-bs-toggle="modal" data-bs-target="#resubmitModal">
                                <i class="bi bi-arrow-repeat me-2"></i>Kumpulkan Ulang
                            </button>
                        @endif
                        
                        <a href="{{ route('assignments.student.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-2"></i>Lihat Semua Tugas
                        </a>
                        
                        @if($submission && $submission->attachment)
                            <a href="{{ Storage::url($submission->attachment) }}" 
                               class="btn btn-outline-success" download>
                                <i class="bi bi-download me-2"></i>Download Pengumpulan
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Important Dates -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-calendar-date me-2"></i>Tanggal Penting
                    </h6>
                </div>
                <div class="card-body">
                    <div class="important-dates">
                        <div class="date-item mb-3">
                            <div class="date-label fw-bold">Batas Waktu:</div>
                            <div class="date-value {{ $isPastDue ? 'text-danger' : 'text-success' }}">
                                {{ $dueDate->format('d F Y, H:i') }}
                            </div>
                            <div class="date-status small {{ $isPastDue ? 'text-danger' : 'text-success' }}">
                                @if($isPastDue)
                                    <i class="bi bi-exclamation-triangle me-1"></i>Telah berakhir
                                @else
                                    <i class="bi bi-clock me-1"></i>
                                    {{ now()->diffForHumans($dueDate, true) }} lagi
                                @endif
                            </div>
                        </div>
                        
                        @if($submission)
                        <div class="date-item mb-3">
                            <div class="date-label fw-bold">Dikumpulkan:</div>
                            <div class="date-value text-info">
                                {{ $submittedAt->format('d F Y, H:i') }}
                            </div>
                            <div class="date-status small text-info">
                                @if($submission->status == 'late')
                                    <i class="bi bi-clock-history me-1"></i>Terlambat
                                @else
                                    <i class="bi bi-check-circle me-1"></i>Tepat waktu
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($submission && $submission->score)
                        <div class="date-item">
                            <div class="date-label fw-bold">Dinilai:</div>
                            <div class="date-value text-success">
                                @php
                                    $gradedAt = $submission->updated_at instanceof Carbon 
                                        ? $submission->updated_at 
                                        : Carbon::parse($submission->updated_at);
                                @endphp
                                {{ $gradedAt->format('d F Y, H:i') }}
                            </div>
                            <div class="date-status small text-success">
                                <i class="bi bi-check-circle-fill me-1"></i>Sudah dinilai
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Class Information -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-people me-2"></i>Informasi Kelas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="class-info">
                        <div class="mb-3">
                            <div class="fw-bold mb-1">Nama Kelas:</div>
                            <div>{{ $assignment->class->class_name ?? 'Tidak ada kelas' }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="fw-bold mb-1">Kode Kelas:</div>
                            <div class="badge bg-info">{{ $assignment->class->class_code ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="fw-bold mb-1">Guru:</div>
                            <div>{{ $assignment->teacher->name ?? 'Tidak ada guru' }}</div>
                            @if($assignment->teacher && $assignment->teacher->email)
                            <div class="small text-muted">{{ $assignment->teacher->email }}</div>
                            @endif
                        </div>
                        
                        <div>
                            <div class="fw-bold mb-1">Jumlah Siswa:</div>
                            <div>{{ $assignment->class->students->count() ?? 0 }} siswa</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Modal -->
<div class="modal fade" id="submitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-upload me-2"></i>Kumpulkan Tugas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('submissions.submit', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jawaban Teks (Opsional)</label>
                        <textarea class="form-control" name="submission_text" rows="6" 
                                  placeholder="Tulis jawaban Anda di sini..." id="submissionText"></textarea>
                        <div class="form-text text-end" id="charCounter">0 karakter</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Lampiran (Opsional)</label>
                        <input type="file" class="form-control" name="attachment" 
                               accept=".pdf,.doc,.docx,.txt,.jpg,.png,.jpeg">
                        <div class="form-text">
                            Format yang didukung: PDF, DOC, DOCX, TXT, JPG, PNG (Max: 2MB)
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Perhatian:</strong> Setelah dikumpulkan, Anda masih bisa mengumpulkan ulang sebelum batas waktu.
                        Pastikan semua jawaban sudah benar sebelum mengumpulkan.
                    </div>
                    
                    <div class="border-top pt-3">
                        <h6><i class="bi bi-calendar me-2"></i>Informasi Pengumpulan:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="small text-muted">Batas Waktu:</div>
                                <div class="fw-bold">{{ $dueDate->format('d F Y, H:i') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted">Waktu Sekarang:</div>
                                <div class="fw-bold">{{ now()->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>Kumpulkan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resubmit Modal -->
@if($submission && !$submission->score && !$isPastDue)
<div class="modal fade" id="resubmitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat me-2"></i>Kumpulkan Ulang Tugas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('submissions.resubmit', $assignment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Pengumpulan sebelumnya akan digantikan dengan yang baru.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jawaban Teks Baru</label>
                        <textarea class="form-control" name="submission_text" rows="6">{{ $submission->submission_text ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Lampiran Baru</label>
                        <input type="file" class="form-control" name="attachment" 
                               accept=".pdf,.doc,.docx,.txt,.jpg,.png,.jpeg">
                        <div class="form-text">
                            File saat ini: 
                            @if($submission->attachment)
                                <a href="{{ Storage::url($submission->attachment) }}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-download me-1"></i>{{ basename($submission->attachment) }}
                                </a>
                            @else
                                Tidak ada file
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat me-2"></i>Kumpulkan Ulang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
.status-card {
    background-color: #f8f9fc;
    border-radius: 10px;
    padding: 20px;
}
.score-display {
    font-size: 24px;
    padding: 10px 20px;
    border-radius: 10px;
}
.feedback-box {
    border-left: 4px solid #36b9cc;
}
.attachment-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.date-item {
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
.date-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.date-label {
    color: #6c757d;
    font-size: 14px;
}
.date-value {
    font-size: 16px;
    margin-bottom: 5px;
}
.date-status {
    font-size: 13px;
}
.important-dates {
    padding: 10px 0;
}
.class-info {
    font-size: 14px;
}
.card-header {
    border-radius: 10px 10px 0 0 !important;
}
</style>

<script>
function saveAsDraft() {
    const formData = new FormData();
    const textarea = document.querySelector('#submitModal textarea[name="submission_text"]');
    const fileInput = document.querySelector('#submitModal input[name="attachment"]');
    
    if (textarea.value) formData.append('submission_text', textarea.value);
    if (fileInput.files[0]) formData.append('attachment', fileInput.files[0]);
    
    fetch('{{ route("submissions.save-draft", $assignment) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Draft berhasil disimpan');
            location.reload();
        } else {
            alert('Gagal menyimpan draft');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menyimpan draft');
    });
}

// Auto-show modal if URL has #submit
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#submit') {
        const submitModal = new bootstrap.Modal(document.getElementById('submitModal'));
        submitModal.show();
    }
    
    // Character counter for textarea
    const textarea = document.getElementById('submissionText');
    const charCounter = document.getElementById('charCounter');
    
    if (textarea && charCounter) {
        textarea.addEventListener('input', function() {
            const charCount = this.value.length;
            charCounter.textContent = `${charCount} karakter`;
            
            if (charCount > 5000) {
                charCounter.classList.add('text-danger');
            } else {
                charCounter.classList.remove('text-danger');
            }
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
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