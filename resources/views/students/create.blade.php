@extends('layouts.app')

@section('title', 'Tambah Siswa Baru')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Siswa Baru</h1>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Form Tambah Siswa</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('students.store') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- NIS -->
                        <div class="mb-3">
                            <label for="nis_nip" class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nis_nip') is-invalid @enderror" 
                                   id="nis_nip" name="nis_nip" value="{{ old('nis_nip') }}" required>
                            @error('nis_nip')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-control @error('is_active') is-invalid @enderror" 
                                    id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', 1) ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Classes -->
                        <div class="mb-3">
                            <label class="form-label">Kelas (Opsional)</label>
                            <div class="row">
                                @foreach($classes as $class)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="classes[]" value="{{ $class->id }}" 
                                               id="class{{ $class->id }}">
                                        <label class="form-check-label" for="class{{ $class->id }}">
                                            {{ $class->class_name }} ({{ $class->class_code }})
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('classes')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Simpan Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Generate NIS automatically if empty
document.getElementById('name').addEventListener('blur', function() {
    const nameInput = this.value;
    const nisInput = document.getElementById('nis_nip');
    
    if (!nisInput.value && nameInput) {
        // Generate NIS from name initials and timestamp
        const initials = nameInput.split(' ').map(n => n[0]).join('').toUpperCase();
        const timestamp = Date.now().toString().slice(-6);
        nisInput.value = initials + timestamp;
    }
});
</script>
@endsection