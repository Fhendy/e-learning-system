<!-- Breadcrumb Component - Dapat digunakan di setiap halaman -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
        </li>
        
        @if(isset($breadcrumbs))
            @foreach($breadcrumbs as $breadcrumb)
                @if(!$loop->last)
                    <li class="breadcrumb-item">
                        <a href="{{ $breadcrumb['url'] }}">
                            @if(isset($breadcrumb['icon']))
                                <i class="{{ $breadcrumb['icon'] }}"></i>
                            @endif
                            {{ $breadcrumb['title'] }}
                        </a>
                    </li>
                @else
                    <li class="breadcrumb-item active" aria-current="page">
                        @if(isset($breadcrumb['icon']))
                            <i class="{{ $breadcrumb['icon'] }}"></i>
                        @endif
                        {{ $breadcrumb['title'] }}
                    </li>
                @endif
            @endforeach
        @else
            <!-- Fallback untuk breadcrumb manual -->
            @if(request()->routeIs('students.index'))
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-people"></i> Daftar Siswa
                </li>
            @elseif(request()->routeIs('students.create'))
                <li class="breadcrumb-item">
                    <a href="{{ route('students.index') }}">
                        <i class="bi bi-people"></i> Siswa
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-plus-circle"></i> Tambah Siswa
                </li>
            @elseif(request()->routeIs('students.edit'))
                <li class="breadcrumb-item">
                    <a href="{{ route('students.index') }}">
                        <i class="bi bi-people"></i> Siswa
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-pencil"></i> Edit Siswa
                </li>
            @elseif(request()->routeIs('classes.index'))
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-people-fill"></i> Kelas
                </li>
            @elseif(request()->routeIs('assignments.*'))
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-journal-text"></i> Tugas
                </li>
            @elseif(request()->routeIs('attendance.*'))
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-calendar-check"></i> Absensi
                </li>
            @elseif(request()->routeIs('profile.edit'))
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-person-circle"></i> Profil
                </li>
            @endif
        @endif
    </ol>
</nav>

<style>
/* Breadcrumb Styles - Konsisten dengan tema global */
.breadcrumb {
    background: transparent;
    padding: 0;
    margin-bottom: 0;
    font-size: 0.875rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item a {
    color: #6b7280;
    text-decoration: none;
    transition: color 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.breadcrumb-item a:hover {
    color: #4f46e5;
}

.breadcrumb-item.active {
    color: #1e293b;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.breadcrumb-item i {
    font-size: 0.875rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #cbd5e1;
    padding: 0 0.5rem;
    font-size: 1rem;
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
    .breadcrumb-item a {
        color: #94a3b8;
    }
    
    .breadcrumb-item a:hover {
        color: #818cf8;
    }
    
    .breadcrumb-item.active {
        color: #f1f5f9;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        color: #475569;
    }
}

/* Responsive */
@media (max-width: 576px) {
    .breadcrumb {
        font-size: 0.75rem;
    }
    
    .breadcrumb-item i {
        font-size: 0.75rem;
    }
}
</style>