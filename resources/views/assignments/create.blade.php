@extends('layouts.app')

@section('title', 'Buat Tugas Baru')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Buat Tugas Baru</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.teacher') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('assignments.teacher.index') }}">Tugas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Buat Baru</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('assignments.teacher.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Buat Tugas</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('assignments.teacher.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Tugas *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Kelas *</label>
                                    <select class="form-control @error('class_id') is-invalid @enderror" 
                                            id="class_id" name="class_id" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->class_name }} ({{ $class->class_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Deskripsi Tugas *</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="6" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Jelaskan tugas dengan jelas, termasuk instruksi dan ketentuan.
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Batas Waktu *</label>
                                    <input type="datetime-local" class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_score" class="form-label">Nilai Maksimal *</label>
                                    <input type="number" class="form-control @error('max_score') is-invalid @enderror" 
                                           id="max_score" name="max_score" value="{{ old('max_score', 100) }}" 
                                           min="1" max="1000" required>
                                    @error('max_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Nilai maksimal yang bisa diperoleh siswa</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="attachment" class="form-label">File Lampiran (Opsional)</label>
                            <input type="file" class="form-control @error('attachment') is-invalid @enderror" 
                                   id="attachment" name="attachment" accept=".pdf,.doc,.docx,.txt,.jpg,.png">
                            @error('attachment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Format: PDF, DOC, DOCX, TXT, JPG, PNG (Max: 2MB)
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Informasi:</strong> Tugas yang dibuat akan langsung terlihat oleh siswa di kelas yang dipilih.
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('assignments.teacher.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const dueDate = new Date(document.getElementById('due_date').value);
            const now = new Date();
            
            if (dueDate < now) {
                e.preventDefault();
                alert('Batas waktu tidak boleh kurang dari waktu sekarang');
                return false;
            }
            
            // Show loading
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
                submitButton.disabled = true;
            }
        });
    }
    
    // Description character counter
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        counter.id = 'charCounter';
        descriptionTextarea.parentNode.appendChild(counter);
        
        descriptionTextarea.addEventListener('input', function() {
            const charCount = this.value.length;
            counter.textContent = `${charCount} karakter`;
            
            if (charCount > 5000) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
        
        // Initial count
        descriptionTextarea.dispatchEvent(new Event('input'));
    }
});
</script>
@endsection