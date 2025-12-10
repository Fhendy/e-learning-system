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
        <button class="sidebar-close" id="sidebarClose">
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

        @if(auth()->user()->role == 'teacher')
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
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();" class="sidebar-link text-danger">
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
    
/* SIDEBAR STYLES */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: #4f46e5;
    background: linear-gradient(180deg, #4f46e5 0%, #3730a3 100%);
    color: white;
    z-index: 990; /* DIBAWAH HEADER (1000) */
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

/* Desktop: Sidebar selalu tampil */
@media (min-width: 992px) {
    .sidebar {
        transform: translateX(0) !important;
        position: fixed;
    }
}

/* Mobile: Sidebar hidden by default */
@media (max-width: 991px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        top: 70px; /* TURUNKAN KE BAWAH HEADER */
        height: calc(100vh - 70px); /* KURANGI TINGGI DENGAN HEADER */
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
}

/* Sidebar Header */
.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(180deg, #4f46e5 0%, #3730a3 100%);
}

/* Di mobile, sidebar header harus tetap visible */
@media (max-width: 991px) {
    .sidebar-header {
        padding: 15px 20px;
        position: sticky;
        top: 0;
        z-index: 1;
        background: linear-gradient(180deg, #4f46e5 0%, #3730a3 100%);
    }
}

.sidebar-brand a {
    display: flex;
    align-items: center;
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: 600;
    gap: 10px;
}

.sidebar-brand i {
    font-size: 1.5rem;
}

/* Close Button (Mobile Only) */
.sidebar-close {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    z-index: 1001; /* DI ATAS HEADER */
}

@media (max-width: 991px) {
    .sidebar-close {
        display: flex;
    }
    
    .sidebar-close:hover {
        background: rgba(255, 255, 255, 0.1);
    }
}

/* Sidebar Menu */
.sidebar-menu {
    flex: 1;
    padding: 15px 0;
    overflow-y: auto;
}

/* Sidebar Items */
.sidebar-item {
    padding: 0 15px;
    margin-bottom: 5px;
}

.sidebar-link {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    border-radius: 8px;
    transition: background-color 0.2s;
}

.sidebar-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.sidebar-link.active {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    font-weight: 600;
}

.sidebar-link i {
    margin-right: 12px;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

/* Badge */
.sidebar-link .badge {
    font-size: 0.7rem;
    padding: 3px 6px;
    margin-left: auto;
}

/* Divider */
.sidebar-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 15px;
}

/* Sidebar Footer */
.sidebar-footer {
    padding: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.sidebar-version small {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.8rem;
}

/* Overlay untuk Mobile */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 989; /* DIBAWAH SIDEBAR */
    display: none;
}

.sidebar-overlay.active {
    display: block;
}

/* Pastikan konten utama ada di bawah header */
@media (max-width: 991px) {
    body {
        padding-top: 70px; /* Offset untuk header fixed */
    }
    
    .app-container {
        margin-top: 0;
    }
    
    .main-content {
        margin-top: 70px;
        min-height: calc(100vh - 70px);
    }
    
    .page-content {
        padding-top: 0;
    }
}

/* Scrollbar untuk sidebar */
.sidebar-menu::-webkit-scrollbar {
    width: 5px;
}

.sidebar-menu::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar-menu::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

/* Z-index hierarchy: */
/* 1. Header: 1000 */
/* 2. Sidebar close button: 1001 */
/* 3. Sidebar: 990 */
/* 4. Overlay: 989 */
</style>

<script>
// SIMPLE SIDEBAR TOGGLE FOR MOBILE
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Fungsi buka sidebar
    function openSidebar() {
        if (sidebar) sidebar.classList.add('active');
        if (sidebarOverlay) sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Fungsi tutup sidebar
    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('active');
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Event Listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', openSidebar);
    }
    
    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    // Tutup sidebar saat klik link (mobile only)
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                // Khusus logout, tampilkan konfirmasi
                if (this.classList.contains('text-danger')) {
                    if (!confirm('Apakah Anda yakin ingin logout?')) {
                        return;
                    }
                }
                closeSidebar();
            }
        });
    });
    
    // Tutup sidebar dengan ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
    
    // Auto-close saat resize ke desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });
});
</script>