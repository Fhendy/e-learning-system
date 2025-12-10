@extends('layouts.app')

@section('title', 'Tambah Absensi Manual')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Tambah Absensi Manual</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('attendance.manual.store') }}" method="POST" id="manualAttendanceForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                <select name="class_id" id="class_id" class="form-select" required onchange="loadStudents()">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->class_name }} ({{ $class->class_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="attendance_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="attendance_date" id="attendance_date" class="form-control" 
                                       value="{{ old('attendance_date', $defaultDate) }}" required>
                                @error('attendance_date')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="student_id" class="form-label">Siswa <span class="text-danger">*</span></label>
                                <select name="student_id" id="student_id" class="form-select" required disabled>
                                    <option value="">Pilih kelas terlebih dahulu</option>
                                </select>
                                @error('student_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="">Pilih Status</option>
                                    <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Hadir</option>
                                    <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                                    <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                                    <option value="sick" {{ old('status') == 'sick' ? 'selected' : '' }}>Sakit</option>
                                    <option value="permission" {{ old('status') == 'permission' ? 'selected' : '' }}>Izin</option>
                                </select>
                                @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="checked_in_at" class="form-label">Waktu Absen</label>
                                <input type="time" name="checked_in_at" id="checked_in_at" class="form-control" 
                                       value="{{ old('checked_in_at', $defaultTime) }}">
                                <small class="text-muted">Kosongkan untuk waktu saat ini</small>
                                @error('checked_in_at')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea name="notes" id="notes" class="form-control" rows="1" placeholder="Masukkan catatan jika perlu">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('attendance.teacher.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <div>
                                <button type="submit" name="submit_and_new" value="1" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan & Tambah Lagi
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Informasi</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Petunjuk:</h6>
                        <ul class="mb-0">
                            <li>Pilih kelas terlebih dahulu untuk memuat daftar siswa</li>
                            <li>Status "Hadir" dan "Terlambat" akan otomatis mencatat waktu saat ini jika tidak diisi</li>
                            <li>Absensi manual akan menggantikan absensi yang sudah ada pada tanggal yang sama</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Perhatian:</h6>
                        <p class="mb-0">Pastikan data yang diinput sudah benar. Perubahan absensi manual akan tercatat dalam sistem.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadStudents() {
    const classId = document.getElementById('class_id').value;
    const studentSelect = document.getElementById('student_id');
    
    if (!classId) {
        studentSelect.innerHTML = '<option value="">Pilih kelas terlebih dahulu</option>';
        studentSelect.disabled = true;
        return;
    }
    
    // Tampilkan loading
    studentSelect.innerHTML = '<option value="">Memuat siswa...</option>';
    studentSelect.disabled = true;
    
    // Ambil data siswa via AJAX
    fetch(`/api/classes/${classId}/students`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            studentSelect.innerHTML = '<option value="">Pilih Siswa</option>';
            if (data.students && data.students.length > 0) {
                data.students.forEach(student => {
                    const option = document.createElement('option');
                    option.value = student.id;
                    option.textContent = student.name + ' (' + (student.nis_nip || 'N/A') + ')';
                    studentSelect.appendChild(option);
                });
            } else {
                studentSelect.innerHTML = '<option value="">Tidak ada siswa di kelas ini</option>';
            }
            studentSelect.disabled = false;
            
            // Jika ada old value, set selected
            const oldValue = "{{ old('student_id') }}";
            if (oldValue) {
                studentSelect.value = oldValue;
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            studentSelect.innerHTML = '<option value="">Gagal memuat siswa</option>';
            studentSelect.disabled = true;
            
            // Tampilkan alert
            Swal.fire({
                icon: 'error',
                title: 'Gagal memuat siswa',
                text: 'Silakan coba lagi atau hubungi administrator.',
                timer: 3000
            });
        });
}

// Event listener untuk perubahan kelas
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    
    // Jika ada nilai sebelumnya, load siswa
    if (classSelect.value) {
        loadStudents();
    }
    
    // Validasi form
    document.getElementById('manualAttendanceForm').addEventListener('submit', function(e) {
        const status = document.getElementById('status').value;
        const checkedInAt = document.getElementById('checked_in_at').value;
        
        // Jika status Hadir atau Terlambat tapi waktu tidak diisi, set waktu sekarang
        if ((status === 'present' || status === 'late') && !checkedInAt) {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            document.getElementById('checked_in_at').value = `${hours}:${minutes}`;
        }
        
        // Validasi input
        const studentId = document.getElementById('student_id').value;
        if (!studentId) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Pilih Siswa',
                text: 'Silakan pilih siswa terlebih dahulu',
                timer: 3000
            });
            return false;
        }
    });
});

// CSRF Token untuk AJAX
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>
@endpush

<style>
.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.form-control:disabled {
    background-color: #e9ecef;
    opacity: 1;
}
</style>