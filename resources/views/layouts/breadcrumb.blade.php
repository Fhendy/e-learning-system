<!-- resources/views/layouts/breadcrumb.blade.php atau tambahkan di setiap view -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.teacher') }}">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('students.index') }}">
                <i class="bi bi-people"></i> Siswa
            </a>
        </li>
        
        @if(isset($student))
        <li class="breadcrumb-item active" aria-current="page">
            {{ $student->name }}
        </li>
        @elseif(Route::currentRouteName() == 'students.create')
        <li class="breadcrumb-item active" aria-current="page">
            Tambah Siswa
        </li>
        @elseif(Route::currentRouteName() == 'students.edit')
        <li class="breadcrumb-item active" aria-current="page">
            Edit Siswa
        </li>
        @else
        <li class="breadcrumb-item active" aria-current="page">
            Daftar Siswa
        </li>
        @endif
    </ol>
</nav>