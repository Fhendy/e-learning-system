<div class="modal fade" id="extendDeadlineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perpanjang Batas Waktu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('assignments.extend', $assignment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Batas Waktu Saat Ini</label>
                        <input type="text" class="form-control" 
                               value="{{ $assignment->due_date->format('d F Y, H:i') }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Batas Waktu Baru *</label>
                        <input type="datetime-local" class="form-control" name="new_due_date" 
                               value="{{ $assignment->due_date->addDay()->format('Y-m-d\TH:i') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}" required>
                        <div class="form-text">Waktu harus lebih dari sekarang</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Perpanjangan (Opsional)</label>
                        <textarea class="form-control" name="reason" rows="3" 
                                  placeholder="Misal: Banyak siswa yang meminta perpanjangan waktu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Perpanjang Waktu</button>
                </div>
            </form>
        </div>
    </div>
</div>