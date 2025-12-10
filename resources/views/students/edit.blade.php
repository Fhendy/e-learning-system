@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Data Siswa</h1>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Form Edit Siswa</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('students.update', $student) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $student->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $student->email) }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- NIS -->
                        <div class="mb-3">
                            <label for="nis_nip" class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nis_nip') is-invalid @enderror" 
                                   id="nis_nip" name="nis_nip" value="{{ old('nis_nip', $student->nis_nip) }}" required>
                            @error('nis_nip')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-control @error('is_active') is-invalid @enderror" 
                                    id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $student->is_active) ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active', $student->is_active) == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password (Optional) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" 
                                       placeholder="Kosongkan jika tidak ingin mengubah">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation"
                                       placeholder="Kosongkan jika tidak ingin mengubah">
                            </div>
                        </div>

                        <!-- Classes -->
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <div class="row">
                                @foreach($classes as $class)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="classes[]" value="{{ $class->id }}" 
                                               id="class{{ $class->id }}"
                                               {{ in_array($class->id, $studentClasses) ? 'checked' : '' }}>
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
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>Update Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection