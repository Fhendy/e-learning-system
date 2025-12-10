@extends('layouts.app')

@section('title', 'Daftar Tugas - Guru')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Tugas</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('assignments.teacher.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle me-2"></i>Tugas Baru
            </a>
            <a href="#" class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bi bi-filter me-2"></i>Filter
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tugas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $assignments->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-journal-text fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tugas Aktif
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activeCount ?? 0 }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span>Batas waktu belum lewat</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Belum Dinilai
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $pendingCount ?? 0 }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span>Pengumpulan menunggu</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Rata-rata Pengumpulan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $averageSubmission ?? 0 }}%
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span>Per kelas</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-percent fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Tugas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Tugas</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                        data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">PDF</a></li>
                    <li><a class="dropdown-item" href="#">Excel</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Judul Tugas</th>
                            <th>Kelas</th>
                            <th>Batas Waktu</th>
                            <th>Status</th>
                            <th>Pengumpulan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                        <tr>
                            <td>
                                <input type="checkbox" class="assignment-check" value="{{ $assignment->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($assignment->attachment)
                                        <i class="bi bi-paperclip text-primary me-2"></i>
                                    @endif
                                    <div>
                                        <a href="{{ route('assignments.show', $assignment) }}" 
                                           class="text-decoration-none text-dark fw-bold">
                                            {{ $assignment->title }}
                                        </a>
                                        <div class="text-muted small mt-1">
                                            {{ Str::limit($assignment->description, 50) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $assignment->class->class_code }}</span>
                                <div class="small text-muted">{{ $assignment->class->class_name }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="{{ $assignment->isPastDue() ? 'text-danger' : 'text-success' }}">
                                        {{ $assignment->due_date->format('d/m/Y H:i') }}
                                    </span>
                                    @if(!$assignment->isPastDue())
                                        <small class="text-muted">
                                            {{ now()->diffForHumans($assignment->due_date, true) }} lagi
                                        </small>
                                    @else
                                        <small class="text-danger">
                                            Terlambat {{ now()->diffForHumans($assignment->due_date, true) }}
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($assignment->isPastDue())
                                    <span class="badge bg-danger">Selesai</span>
                                @else
                                    <span class="badge bg-success">Aktif</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $submitted = $assignment->submissions->count();
                                    $total = $assignment->class->students->count();
                                    $percentage = $total > 0 ? round(($submitted / $total) * 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $percentage == 100 ? 'success' : ($percentage > 50 ? 'info' : 'warning') }}" 
                                             role="progressbar" style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                    <span class="small">{{ $submitted }}/{{ $total }}</span>
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-check-circle text-success me-1"></i>
                                    {{ $assignment->submissions()->whereNotNull('score')->count() }} dinilai
                                </div>
                            </td>
<td>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('assignments.show', $assignment) }}" 
           class="btn btn-info" title="Lihat Detail">
            <i class="bi bi-eye"></i>
        </a>
        <a href="{{ route('assignments.teacher.edit', $assignment) }}" 
           class="btn btn-warning" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
        <form action="{{ route('assignments.teacher.destroy', $assignment) }}" 
              method="POST" class="d-inline delete-form"
              data-title="{{ $assignment->title }}"
              data-submissions="{{ $assignment->submissions->count() }}"
              onsubmit="return confirm('Hapus tugas \"{{ $assignment->title }}\"?\\n\\nAkan menghapus {{ $assignment->submissions->count() }} pengumpulan siswa.\\nTindakan ini tidak dapat dibatalkan!')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" title="Hapus">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </div>
</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="bi bi-journal-text fa-4x text-gray-300 mb-3"></i>
                                    <h5>Belum ada tugas</h5>
                                    <p class="text-muted">Buat tugas pertama Anda untuk siswa</p>
                                    <a href="{{ route('assignments.teacher.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Buat Tugas
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($assignments->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan {{ $assignments->firstItem() }} - {{ $assignments->lastItem() }} dari {{ $assignments->total() }} tugas
                </div>
                <div>
                    {{ $assignments->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Batch Actions -->
    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span id="selectedCount" class="text-muted">0 tugas terpilih</span>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary" id="batchDownload">
                        <i class="bi bi-download me-1"></i>Download
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="batchDelete">
                        <i class="bi bi-trash me-1"></i>Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('assignments.teacher.index') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="past" {{ request('status') == 'past' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="class_id">
                            <option value="">Semua Kelas</option>
                            @foreach($allClasses ?? $classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('assignments.teacher.index') }}" class="btn btn-secondary">Reset</a>
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.table th {
    background-color: #f8f9fc;
    font-weight: 600;
}
.empty-state {
    padding: 3rem 0;
}
.progress {
    min-width: 80px;
}
.btn-group-sm form {
    display: inline-block;
}
</style>

<script>
// Simple Delete Confirmation
function confirmDelete(button) {
    const form = button.closest('form');
    const title = form.getAttribute('data-title');
    const submissions = form.getAttribute('data-submissions');
    
    if (confirm(`Hapus tugas "${title}"?\n\nAkan menghapus:\n• ${submissions} pengumpulan siswa\n• Semua file lampiran\n\nTindakan ini tidak dapat dibatalkan!`)) {
        // Show loading
        button.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        button.disabled = true;
        
        // Submit form
        form.submit();
        return true;
    }
    return false;
}

// Select All Checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.assignment-check');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

// Update selected count
function updateSelectedCount() {
    const selected = document.querySelectorAll('.assignment-check:checked');
    document.getElementById('selectedCount').textContent = selected.length + ' tugas terpilih';
}

// Batch actions
document.getElementById('batchDownload').addEventListener('click', function() {
    const selected = Array.from(document.querySelectorAll('.assignment-check:checked'))
        .map(cb => cb.value);
    if (selected.length > 0) {
        alert('Download ' + selected.length + ' tugas');
        // Implement batch download here
    } else {
        alert('Pilih tugas terlebih dahulu');
    }
});

document.getElementById('batchDelete').addEventListener('click', function() {
    const selected = Array.from(document.querySelectorAll('.assignment-check:checked'))
        .map(cb => cb.value);
    if (selected.length > 0) {
        if (confirm('Hapus ' + selected.length + ' tugas terpilih?\n\nSemua pengumpulan akan ikut terhapus!')) {
            // Implement batch delete here
            fetch('/assignments/batch-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ids: selected})
            }).then(response => {
                if (response.ok) {
                    location.reload();
                }
            });
        }
    } else {
        alert('Pilih tugas terlebih dahulu');
    }
});

// Add event listeners to checkboxes
document.querySelectorAll('.assignment-check').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});
</script>
@endsection