<!-- Overlay untuk mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}">
                <i class="bi bi-mortarboard-fill"></i>
                <span class="brand-name">E-Learning</span>
            </a>
        </div>
        <button class="sidebar-close" id="sidebarClose" aria-label="Close Sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    
    <div class="sidebar-menu">
        <!-- Dashboard -->
        <div class="sidebar-item">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </div>

        @if(in_array(auth()->user()->role, ['teacher', 'admin', 'guru']))
        <!-- Menu Guru -->
        <div class="sidebar-item">
            <a href="{{ route('classes.index') }}" class="sidebar-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Kelas</span>
            </a>
        </div>
        
        <div class="sidebar-item">
            <a href="{{ route('students.index') }}" class="sidebar-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>Siswa</span>
            </a>
        </div>
        
        <div class="sidebar-item">
            <a href="{{ route('assignments.teacher.index') }}" class="sidebar-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Tugas</span>
            </a>
        </div>
        
        <div class="sidebar-item">
            <a href="{{ route('qr-codes.index') }}" class="sidebar-link {{ request()->routeIs('qr-codes.*') ? 'active' : '' }}">
                <i class="bi bi-qr-code-scan"></i>
                <span>QR Absensi</span>
            </a>
        </div>
        
        <div class="sidebar-item">
            <a href="{{ route('attendance.teacher.index') }}" class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i>
                <span>Absensi</span>
            </a>
        </div>
        @else
        <!-- Menu Siswa -->
        <div class="sidebar-item">
            <a href="{{ route('assignments.student.index') }}" class="sidebar-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Tugas</span>
                @if(($pendingAssignmentsCount ?? 0) > 0)
                    <span class="badge bg-danger ms-auto">{{ $pendingAssignmentsCount ?? 0 }}</span>
                @endif
            </a>
        </div>
        
        <div class="sidebar-item">
            <a href="{{ route('attendance.student.index') }}" class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i>
                <span>Absensi</span>
            </a>
        </div>
        @endif
        
        <!-- Menu Umum -->
        <div class="sidebar-divider"></div>
        
        <div class="sidebar-item">
            <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span>Profil</span>
            </a>
        </div>
        
        <div class="sidebar-item">
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();" class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
    
    <div class="sidebar-footer">
        <div class="sidebar-version">
            <small>v1.0.0</small>
        </div>
    </div>
</nav>

<style>
/* Sidebar Styles - Warna putih konsisten */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: #ffffff !important;
    color: #1e293b;
    z-index: 1040;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05);
}

.sidebar.active {
    transform: translateX(0);
}

@media (min-width: 992px) {
    .sidebar {
        transform: translateX(0);
    }
}

@media (max-width: 991px) {
    .sidebar {
        width: 280px;
        top: 0;
        height: 100vh;
    }
}

/* Sidebar Header */
.sidebar-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 70px;
}

.sidebar-brand a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #4f46e5;
    text-decoration: none;
    font-size: 1.25rem;
    font-weight: 700;
}

.sidebar-brand i {
    font-size: 1.5rem;
}

.sidebar-close {
    display: none;
    background: #f1f5f9;
    border: none;
    color: #64748b;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    align-items: center;
    justify-content: center;
}

@media (max-width: 991px) {
    .sidebar-close {
        display: flex;
    }
}

/* Sidebar Menu */
.sidebar-menu {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto;
}

.sidebar-item {
    padding: 0 1rem;
    margin-bottom: 0.25rem;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #475569;
    text-decoration: none;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
}

.sidebar-link:hover {
    background: #f1f5f9;
    color: #4f46e5;
}

.sidebar-link.active {
    background: #eef2ff;
    color: #4f46e5;
    font-weight: 600;
}

.sidebar-link i {
    font-size: 1.25rem;
    width: 1.5rem;
}

.sidebar-link .badge {
    font-size: 0.688rem;
    padding: 0.25rem 0.5rem;
    margin-left: auto;
}

.sidebar-link.text-danger {
    color: #ef4444;
}

.sidebar-link.text-danger:hover {
    background: #fef2f2;
    color: #dc2626;
}

/* Sidebar Divider */
.sidebar-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 1rem 1.25rem;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.sidebar-version small {
    color: #94a3b8;
    font-size: 0.75rem;
}

/* Scrollbar */
.sidebar::-webkit-scrollbar,
.sidebar-menu::-webkit-scrollbar {
    width: 4px;
}

.sidebar::-webkit-scrollbar-track,
.sidebar-menu::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.sidebar::-webkit-scrollbar-thumb,
.sidebar-menu::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

/* Overlay */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1035;
    display: none;
}

.sidebar-overlay.active {
    display: block;
}

@media (min-width: 992px) {
    .sidebar-overlay {
        display: none !important;
    }
}

/* Responsive */
@media (max-width: 576px) {
    .sidebar {
        width: 260px;
    }
    
    .sidebar-header {
        padding: 0.875rem 1rem;
    }
    
    .sidebar-brand a {
        font-size: 1.125rem;
    }
    
    .sidebar-brand i {
        font-size: 1.25rem;
    }
    
    .sidebar-link {
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
    }
    
    .sidebar-link i {
        font-size: 1.125rem;
    }
}
</style>