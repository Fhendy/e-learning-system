@extends('layouts.app')

@section('title', 'Import Data Siswa')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Import Data Siswa</h1>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Import dari Excel</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle me-2"></i>Format File:</h6>
                            <ul class="mb-0">
                                <li>File harus dalam format .xlsx, .xls, atau .csv</li>
                                <li>Kolom wajib: <strong>NIS</strong>, <strong>Nama</strong>, <strong>Email</strong></li>
                                <li>Password akan digenerate otomatis</li>
                                <li>Download template: 
                                    <a href="{{ asset('templates/template-siswa.xlsx') }}" class="text-decoration-none">
                                        <i class="bi bi-download me-1"></i>Template Excel
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="class_id" class="form-label">Tambahkan ke Kelas (Opsional)</label>
                            <select class="form-control" id="class_id" name="class_id">
                                <option value="">Pilih Kelas</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload me-1"></i>Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection