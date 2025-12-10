<header class="header">
    <div class="container-fluid">
        <div class="header-content">
            <!-- Sidebar Toggle for Mobile -->
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Page Title (Mobile) -->
            <div class="header-title d-block d-lg-none">
                <h1 class="h5 mb-0">
                    @yield('title', config('app.name'))
                </h1>
            </div>
            
            <!-- Header Right -->
            <div class="header-right">
                <!-- User Menu -->
                <div class="user-menu dropdown">
                    <button class="user-btn" type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="user-info d-none d-md-block">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                        <i class="bi bi-chevron-down d-none d-md-block"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="bi bi-person me-2"></i>Profil
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" class="dropdown-item-form">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
/* HEADER STYLES */
.header {
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    height: 70px;
    position: sticky;
    top: 0;
    z-index: 1000; /* HARUS LEBIH TINGGI DARI SIDEBAR */
    border-bottom: 1px solid #f1f5f9;
    width: 100%;
}

.header .container-fluid {
    height: 100%;
    padding: 0;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding: 0 20px;
    gap: 15px;
    background: white; /* Pastikan background solid */
}

/* Sidebar Toggle (Mobile Only) */
.sidebar-toggle {
    background: none;
    border: none;
    color: #4f46e5;
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    z-index: 1001; /* Lebih tinggi dari header */
    position: relative; /* Untuk z-index bekerja */
}

.sidebar-toggle:hover {
    background: #f8fafc;
}

@media (min-width: 992px) {
    .sidebar-toggle {
        display: none;
    }
}

/* Page Title (Mobile Only) */
.header-title {
    flex: 1;
    overflow: hidden;
    text-align: center;
    position: relative;
    z-index: 1;
}

.header-title h1 {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #1e293b;
    font-weight: 600;
    font-size: 1rem;
    margin: 0;
}

@media (min-width: 992px) {
    .header-title {
        display: none;
    }
}

/* Header Right */
.header-right {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}

/* User Menu */
.user-menu {
    position: relative;
    z-index: 1;
}

.user-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px;
    cursor: pointer;
    border-radius: 8px;
    min-width: 0;
    position: relative;
    z-index: 1;
}

.user-btn:hover {
    background: #f8fafc;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #4f46e5;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
}

.user-info {
    text-align: left;
    min-width: 0;
}

.user-name {
    display: block;
    font-weight: 600;
    font-size: 0.875rem;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

.user-role {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
}

.user-btn i {
    font-size: 0.875rem;
    color: #64748b;
}

/* Dropdown Menu */
.user-menu .dropdown-menu {
    width: 200px;
    border: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    padding: 8px;
    margin-top: 10px;
    border: 1px solid #f1f5f9;
    z-index: 1002; /* Lebih tinggi dari header */
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    border-radius: 6px;
    color: #334155;
    font-size: 0.875rem;
    text-decoration: none;
}

.dropdown-item:hover {
    background: #f8fafc;
    color: #4f46e5;
}

.dropdown-item i {
    width: 20px;
    margin-right: 10px;
    font-size: 1rem;
}

.dropdown-item.text-danger {
    color: #ef4444 !important;
}

.dropdown-item.text-danger:hover {
    background: rgba(239, 68, 68, 0.1);
}

.dropdown-divider {
    margin: 8px 12px;
    border-top: 1px solid #f1f5f9;
}

.dropdown-item-form {
    padding: 0;
}

.dropdown-item-form button {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: 10px 12px;
    font: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
}

/* Responsive */
@media (max-width: 768px) {
    .header-content {
        padding: 0 15px;
    }
    
    .user-info {
        display: none;
    }
    
    .user-btn i {
        display: none;
    }
    
    .user-menu .dropdown-menu {
        width: 180px;
        position: fixed !important;
        top: 60px !important;
        right: 15px !important;
        left: auto !important;
        z-index: 1002;
    }
    
    .user-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .header-content {
        padding: 0 10px;
    }
    
    .header-title h1 {
        font-size: 0.9rem;
    }
    
    .user-menu .dropdown-menu {
        right: 10px !important;
        top: 60px !important;
    }
}

/* Small Phones */
@media (max-width: 375px) {
    .user-avatar {
        width: 32px;
        height: 32px;
    }
    
    .sidebar-toggle {
        width: 36px;
        height: 36px;
    }
}

/* Pastikan header selalu di atas sidebar */
@media (max-width: 991px) {
    .header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
    }
    
    .main-content {
        margin-top: 70px; /* Beri margin top untuk konten */
    }
}
</style>