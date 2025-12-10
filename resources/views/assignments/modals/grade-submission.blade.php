<div class="modal fade" id="gradeSubmissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nilai Pengumpulan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="gradeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nilai (0-{{ $assignment->max_score }})</label>
                        <input type="number" class="form-control" name="score" 
                               min="0" max="{{ $assignment->max_score }}" 
                               step="0.5" required>
                        <div class="form-text">
                            Nilai maksimal: {{ $assignment->max_score }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback untuk Siswa</label>
                        <textarea class="form-control" name="feedback" rows="4" 
                                  placeholder="Beri masukan, pujian, atau koreksi..."></textarea>
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