@extends('layouts.app')

@section('title', 'Edit Absensi')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('attendance.teacher.index') }}">Absensi</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Absensi</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Data Absensi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.update', $attendance) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                <select name="class_id" id="class_id" class="form-select" required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" 
                                            {{ $attendance->class_id == $class->id ? 'selected' : '' }}>
                                            {{ $class->class_name }} ({{ $class->class_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="student_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                                <select name="student_id" id="student_id" class="form-select" required>
                                    <option value="">Pilih Siswa</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" 
                                            {{ $attendance->student_id == $student->id ? 'selected' : '' }}>
                                            {{ $student->name }} ({{ $student->nis_nip ?? 'NIS' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="attendance_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="attendance_date" id="attendance_date" 
                                       class="form-control" 
                                       value="{{ old('attendance_date', $attendance->attendance_date->format('Y-m-d')) }}" 
                                       required>
                                @error('attendance_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="">Pilih Status</option>
                                    <option value="present" {{ $attendance->status == 'present' ? 'selected' : '' }}>Hadir</option>
                                    <option value="late" {{ $attendance->status == 'late' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="absent" {{ $attendance->status == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                    <option value="sick" {{ $attendance->status == 'sick' ? 'selected' : '' }}>Sakit</option>
                                    <option value="permission" {{ $attendance->status == 'permission' ? 'selected' : '' }}>Izin</option>
                                </select>
                                @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="checked_in_at" class="form-label">Waktu Absen</label>
                                <input type="time" name="checked_in_at" id="checked_in_at" 
                                       class="form-control" 
                                       value="{{ old('checked_in_at', $attendance->checked_in_at ? $attendance->checked_in_at->format('H:i') : '') }}">
                                @error('checked_in_at')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="qr_code_id" class="form-label">QR Code (Opsional)</label>
                                <select name="qr_code_id" id="qr_code_id" class="form-select">
                                    <option value="">Tidak menggunakan QR Code</option>
                                    @foreach($qrCodes as $qrCode)
                                        <option value="{{ $qrCode->id }}" 
                                            {{ $attendance->qr_code_id == $qrCode->id ? 'selected' : '' }}>
                                            {{ $qrCode->code }} - {{ $qrCode->date->format('d/m/Y') }} ({{ $qrCode->formatted_time_range }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('qr_code_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $attendance->notes) }}</textarea>
                                @error('notes')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('attendance.teacher.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Absensi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>ID</th>
                            <td>{{ $attendance->id }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>
                                {{ $attendance->created_at->format('d/m/Y H:i') }}
                                <br>
                                <small class="text-muted">{{ $attendance->created_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Diperbarui</th>
                            <td>
                                {{ $attendance->updated_at->format('d/m/Y H:i') }}
                                <br>
                                <small class="text-muted">{{ $attendance->updated_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                        @if($attendance->qrCode)
                        <tr>
                            <th>QR Code</th>
                            <td>
                                <span class="badge bg-info">{{ $attendance->qrCode->code }}</span>
                                <br>
                                <small class="text-muted">
                                    {{ $attendance->qrCode->date->format('d/m/Y') }} • {{ $attendance->qrCode->formatted_time_range }}
                                </small>
                            </td>
                        </tr>
                        @endif
                        @if($attendance->marked_by)
                        <tr>
                            <th>Ditandai Oleh</th>
                            <td>{{ $attendance->marker->name ?? 'N/A' }}</td>
                        </tr>
                        @endif
                    </table>
                    
                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-info-circle"></i> Informasi:</h6>
                        <ul class="mb-0">
                            <li>Hanya guru yang mengajar kelas ini yang dapat mengedit absensi</li>
                            <li>Perubahan akan tercatat dalam sistem</li>
                            <li>QR Code opsional, digunakan jika absensi via scan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update siswa berdasarkan kelas yang dipilih
    const classSelect = document.getElementById('class_id');
    const studentSelect = document.getElementById('student_id');
    
    classSelect.addEventListener('change', function() {
        const classId = this.value;
        
        if (!classId) {
            studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';
            return;
        }
        
        // Tampilkan loading
        studentSelect.innerHTML = '<option value="">Memuat siswa...</option>';
        studentSelect.disabled = true;
        
        // Ambil data siswa via AJAX
        fetch(`/api/classes/${classId}/students`)
            .then(response => response.json())
            .then(data => {
                studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';
                data.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.id;
                    option.textContent = student.text;
                    studentSelect.appendChild(option);
                });
                studentSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading students:', error);
                studentSelect.innerHTML = '<option value="">Gagal memuat siswa</option>';
            });
    });
    
    // Auto-fill waktu jika status Hadir/Terlambat dan waktu kosong
    const statusSelect = document.getElementById('status');
    const timeInput = document.getElementById('checked_in_at');
    
    statusSelect.addEventListener('change', function() {
        if (!timeInput.value && (this.value === 'present' || this.value === 'late')) {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            timeInput.value = `${hours}:${minutes}`;
        }
    });
});
</script>
@endpush