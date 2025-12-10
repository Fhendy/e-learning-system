<div class="modal-header">
    <h5 class="modal-title">Pengumpulan: {{ $submission->student->name }}</h5>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6 mb-3">
            <h6>Informasi Siswa</h6>
            <p><strong>Nama:</strong> {{ $submission->student->name }}</p>
            <p><strong>NIS/NIP:</strong> {{ $submission->student->nis_nip }}</p>
            <p><strong>Kelas:</strong> {{ $submission->assignment->class->class_name }}</p>
        </div>
        <div class="col-md-6 mb-3">
            <h6>Informasi Pengumpulan</h6>
            <p><strong>Status:</strong> 
                <span class="badge bg-{{ $submission->status == 'graded' ? 'success' : ($submission->status == 'late' ? 'danger' : 'info') }}">
                    {{ ucfirst($submission->status) }}
                </span>
            </p>
            <p><strong>Tanggal Submit:</strong> {{ $submission->submitted_at->format('d/m/Y H:i') }}</p>
            @if($submission->score)
                <p><strong>Nilai:</strong> 
                    <span class="badge bg-{{ $submission->score >= 80 ? 'success' : ($submission->score >= 60 ? 'warning' : 'danger') }}">
                        {{ $submission->score }}/{{ $submission->assignment->max_score }}
                    </span>
                </p>
            @endif
        </div>
    </div>
    
    @if($submission->submission_text)
    <div class="mb-3">
        <h6>Jawaban Teks</h6>
        <div class="p-3 border rounded bg-light">
            {{ $submission->submission_text }}
        </div>
    </div>
    @endif
    
    @if($submission->attachment)
    <div class="mb-3">
        <h6>File Lampiran</h6>
        <div class="attachment-box p-3 border rounded">
            <div class="d-flex align-items-center">
                <i class="bi bi-paperclip fa-2x text-primary me-3"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold">{{ basename($submission->attachment) }}</div>
                    <small class="text-muted">File pengumpulan siswa</small>
                </div>
                <a href="{{ Storage::url($submission->attachment) }}" 
                   class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="bi bi-download me-1"></i>Download
                </a>
            </div>
        </div>
    </div>
    @endif
    
    @if($submission->feedback)
    <div class="mb-3">
        <h6>Feedback dari Guru</h6>
        <div class="p-3 border rounded bg-info bg-opacity-10">
            {{ $submission->feedback }}
        </div>
    </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>