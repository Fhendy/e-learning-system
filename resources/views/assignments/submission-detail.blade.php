<div class="modal-header border-0 pb-0">
    <h5 class="modal-title">
        <i class="bi bi-file-text me-2 text-primary"></i>
        Pengumpulan: {{ $submission->student->name }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body pt-0">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-card-title">
                    <i class="bi bi-person me-2 text-primary"></i>
                    Informasi Siswa
                </h6>
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value">{{ $submission->student->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIS/NIP</div>
                        <div class="info-value">{{ $submission->student->nis_nip ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kelas</div>
                        <div class="info-value">{{ $submission->assignment->class->class_name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="info-card-title">
                    <i class="bi bi-upload me-2 text-primary"></i>
                    Informasi Pengumpulan
                </h6>
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @php
                                $statusColor = match($submission->status) {
                                    'graded' => 'success',
                                    'late' => 'danger',
                                    'submitted' => 'info',
                                    'draft' => 'secondary',
                                    default => 'secondary'
                                };
                                $statusText = match($submission->status) {
                                    'graded' => 'Dinilai',
                                    'late' => 'Terlambat',
                                    'submitted' => 'Dikumpulkan',
                                    'draft' => 'Draft',
                                    default => ucfirst($submission->status)
                                };
                            @endphp
                            <span class="badge bg-{{ $statusColor }} px-3 py-2">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tanggal Submit</div>
                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @if($submission->score)
                    <div class="info-item">
                        <div class="info-label">Nilai</div>
                        <div class="info-value">
                            @php
                                $score = $submission->score;
                                $maxScore = $submission->assignment->max_score;
                                $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
                                $scoreColor = $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
                            @endphp
                            <span class="badge bg-{{ $scoreColor }} px-3 py-2">
                                {{ $score }}/{{ $maxScore }} ({{ $percentage }}%)
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @if($submission->submission_text)
    <div class="mt-3">
        <div class="info-card">
            <h6 class="info-card-title">
                <i class="bi bi-card-text me-2 text-primary"></i>
                Jawaban Teks
            </h6>
            <div class="submission-text p-3 bg-light rounded">
                {!! nl2br(e($submission->submission_text)) !!}
            </div>
        </div>
    </div>
    @endif
    
    @if($submission->attachment)
    <div class="mt-3">
        <div class="info-card">
            <h6 class="info-card-title">
                <i class="bi bi-paperclip me-2 text-primary"></i>
                File Lampiran
            </h6>
            <div class="attachment-box p-3 border rounded bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="attachment-icon">
                        <i class="bi bi-file-earmark-text fs-1 text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ basename($submission->attachment) }}</div>
                        <div class="text-muted small">File pengumpulan siswa</div>
                    </div>
                    <div>
                        <a href="{{ Storage::url($submission->attachment) }}" 
                           class="btn btn-sm btn-primary" target="_blank" download>
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if($submission->feedback)
    <div class="mt-3">
        <div class="info-card">
            <h6 class="info-card-title">
                <i class="bi bi-chat-left-text me-2 text-info"></i>
                Feedback dari Guru
            </h6>
            <div class="feedback-box p-3 bg-info bg-opacity-10 rounded">
                {!! nl2br(e($submission->feedback)) !!}
            </div>
        </div>
    </div>
    @endif
</div>
<div class="modal-footer border-0 pt-0">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
        <i class="bi bi-x-circle me-2"></i>Tutup
    </button>
    @if(!$submission->score && $submission->status != 'graded')
    <a href="{{ route('submissions.grade', $submission) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-pencil-square me-2"></i>Beri Nilai
    </a>
    @endif
</div>

<style>
/* Info Card */
.info-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 1rem;
    height: 100%;
}

.info-card-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #1f2937;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
}

.info-label {
    font-size: 0.75rem;
    color: #6b7280;
}

.info-value {
    font-size: 0.813rem;
    font-weight: 500;
    color: #1f2937;
}

/* Submission Text */
.submission-text {
    font-size: 0.875rem;
    line-height: 1.5;
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* Attachment Box */
.attachment-box {
    border-left: 3px solid #4f46e5;
}

.attachment-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Feedback Box */
.feedback-box {
    border-left: 3px solid #3b82f6;
    font-size: 0.875rem;
    line-height: 1.5;
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* Badge */
.badge {
    font-size: 0.688rem;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 8px;
}

/* Modal */
.modal-content {
    border-radius: 12px;
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
}

/* Buttons */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.375rem 0.875rem;
    transition: all 0.2s ease;
    font-size: 0.813rem;
}

.btn-sm {
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
}

.btn-primary {
    background: #4f46e5;
    border-color: #4f46e5;
}

.btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
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

/* Colors */
.text-primary { color: #4f46e5 !important; }
.text-info { color: #3b82f6 !important; }
.text-muted { color: #6b7280 !important; }

/* Responsive */
@media (max-width: 576px) {
    .info-item {
        flex-direction: column;
        gap: 0.125rem;
    }
    
    .info-card {
        height: auto;
    }
    
    .attachment-box .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .attachment-icon {
        margin-bottom: 0.5rem;
    }
}
</style>