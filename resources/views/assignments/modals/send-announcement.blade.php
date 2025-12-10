<div class="modal fade" id="announcementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Beri Pengumuman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('assignments.announce', $assignment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kepada</label>
                        <input type="text" class="form-control" 
                               value="Siswa kelas {{ $assignment->class->class_name }}" disabled>
                        <div class="form-text">
                            {{ $assignment->class->students->count() }} siswa akan menerima pengumuman
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan Pengumuman *</label>
                        <textarea class="form-control" name="message" rows="5" 
                                  placeholder="Tulis pengumuman untuk siswa..." 
                                  required></textarea>
                        <div class="form-text">Maksimal 1000 karakter</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Pengumuman akan dikirim ke semua siswa di kelas ini.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Pengumuman</button>
                </div>
            </form>
        </div>
    </div>
</div>