<!-- Modal View Submission -->
<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-file-text me-2 text-primary"></i>
                    Detail Pengumpulan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0" id="viewSubmissionContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Memuat data pengumpulan...</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Grade Submission -->
<div class="modal fade" id="gradeSubmissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>
                    Beri Nilai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="gradeForm" method="POST">
                @csrf
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">Nilai <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="score" 
                               min="0" max="100" step="1" required>
                        <div class="text-muted small mt-1">Nilai maksimal: <span id="maxScoreValue">100</span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback (Opsional)</label>
                        <textarea class="form-control" name="feedback" rows="4" 
                                  placeholder="Berikan feedback untuk siswa..."></textarea>
                    </div>
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Nilai yang sudah diberikan dapat diubah nanti.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-2"></i>Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Extend Deadline -->
<div class="modal fade" id="extendDeadlineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>
                    Perpanjang Batas Waktu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('assignments.teacher.extend', $assignment) }}" method="POST">
                @csrf
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">Batas Waktu Saat Ini</label>
                        <input type="text" class="form-control" 
                               value="{{ $assignment->due_date->format('d/m/Y H:i') }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Batas Waktu Baru <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="new_deadline" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Perpanjangan (Opsional)</label>
                        <textarea class="form-control" name="reason" rows="3" 
                                  placeholder="Berikan alasan perpanjangan waktu..."></textarea>
                    </div>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Perpanjangan waktu akan memberitahu siswa melalui notifikasi.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-calendar-plus me-2"></i>Perpanjang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Send Announcement -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-megaphone me-2 text-primary"></i>
                    Kirim Pengumuman
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('assignments.teacher.announce', $assignment) }}" method="POST">
                @csrf
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label">Judul Pengumuman <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" 
                               value="Pengumuman Tugas: {{ $assignment->title }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="message" rows="5" 
                                  placeholder="Tulis pesan pengumuman untuk siswa..."></textarea>
                    </div>
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        Pengumuman akan dikirim ke semua siswa di kelas ini.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-2"></i>Kirim Pengumuman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
}

.modal-header {
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 1.25rem;
}

.modal-body {
    padding: 1.25rem;
}

.modal-footer {
    border-top: 1px solid #e5e7eb;
    padding: 1rem 1.25rem;
}

/* Form Styles */
.form-label {
    font-weight: 500;
    font-size: 0.813rem;
    color: #374151;
    margin-bottom: 0.375rem;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    font-size: 0.813rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    outline: none;
}

.form-control:disabled {
    background-color: #f8fafc;
    color: #6b7280;
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

/* Alert */
.alert {
    border-radius: 10px;
}

.alert-info {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
}

.alert-warning {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

/* Spinner */
.spinner-border {
    width: 2rem;
    height: 2rem;
}

/* Colors */
.text-primary { color: #4f46e5 !important; }
.text-muted { color: #6b7280 !important; }
.text-danger { color: #ef4444 !important; }

/* Responsive */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 0.75rem 1rem;
    }
}
</style>