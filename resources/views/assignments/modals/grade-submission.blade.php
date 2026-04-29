<!-- Modal Grade Submission -->
<div class="modal fade" id="gradeSubmissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>
                    Nilai Pengumpulan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="gradeForm" method="POST">
                @csrf
                <div class="modal-body pt-0">
                    <!-- Student Info Preview -->
                    <div class="student-info-preview mb-4 p-3 bg-light rounded">
                        <div class="d-flex align-items-center gap-3">
                            <div class="student-avatar-preview">
                                <i class="bi bi-person fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-semibold" id="studentNamePreview">Memuat...</div>
                                <div class="text-muted small" id="studentClassPreview"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="score" 
                               id="scoreInput"
                               min="0" max="{{ $assignment->max_score }}" 
                               step="0.5" required
                               placeholder="Masukkan nilai">
                        <div class="text-muted small mt-1">
                            Nilai maksimal: <span class="fw-semibold">{{ $assignment->max_score }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Feedback untuk Siswa</label>
                        <textarea class="form-control" name="feedback" rows="4" 
                                  id="feedbackInput"
                                  placeholder="Beri masukan, pujian, atau koreksi..."></textarea>
                        <div class="text-muted small mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            Feedback akan membantu siswa memahami nilai yang diberikan
                        </div>
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
                    <button type="submit" class="btn btn-primary btn-sm" id="submitGradeBtn">
                        <i class="bi bi-save me-2"></i>Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gradeForm = document.getElementById('gradeForm');
    const scoreInput = document.getElementById('scoreInput');
    const submitBtn = document.getElementById('submitGradeBtn');
    const maxScore = {{ $assignment->max_score ?? 100 }};
    
    // Set max attribute
    if (scoreInput) {
        scoreInput.max = maxScore;
    }
    
    // Form validation
    if (gradeForm && submitBtn) {
        gradeForm.addEventListener('submit', function(e) {
            const score = parseFloat(scoreInput.value);
            
            if (isNaN(score)) {
                e.preventDefault();
                alert('Mohon masukkan nilai yang valid');
                return false;
            }
            
            if (score < 0 || score > maxScore) {
                e.preventDefault();
                alert(`Nilai harus antara 0 dan ${maxScore}`);
                return false;
            }
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
        });
    }
});
</script>

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

/* Student Info Preview */
.student-info-preview {
    background: #f8fafc;
    border-radius: 10px;
    border-left: 3px solid #4f46e5;
}

.student-avatar-preview {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
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

textarea.form-control {
    resize: vertical;
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

/* Spinner */
.spinner-border {
    width: 1rem;
    height: 1rem;
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
    
    .student-info-preview {
        margin-bottom: 1rem;
    }
    
    .student-avatar-preview {
        width: 36px;
        height: 36px;
    }
}
</style>