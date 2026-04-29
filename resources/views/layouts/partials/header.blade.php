<header class="header">
    <div class="container-fluid">
        <div class="header-content">
            <!-- Sidebar Toggle (kiri) -->
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            
            <!-- Page Title (center) -->
            <div class="header-title">
                <h1 class="header-title-text">@yield('title', config('app.name'))</h1>
            </div>
            
            <!-- Header Right (kanan) - Profil di sini -->
            <div class="header-right">
                <!-- User Menu -->
                <div class="user-wrapper">
                    <button class="user-btn" id="userBtn" aria-label="User Menu">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role">{{ ucfirst(auth()->user()->role) }}</span>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="bi bi-person me-2"></i>Profil
                        </a>
                        <a href="{{ route('profile.edit') }}#password" class="dropdown-item">
                            <i class="bi bi-shield-lock me-2"></i>Ubah Password
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form-header">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger logout-btn">
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
/* Header Styles - Konsisten putih di semua device */
.header {
    background: #ffffff !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    height: 64px;
    position: sticky;
    top: 0;
    z-index: 1030;
    border-bottom: 1px solid #e5e7eb;
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
    padding: 0 1.5rem;
    gap: 1rem;
    background: #ffffff !important;
}

/* Header Left (Sidebar Toggle) */
.header-left {
    flex-shrink: 0;
    min-width: 40px;
}

/* Sidebar Toggle */
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
    transition: all 0.2s ease;
}

.sidebar-toggle:hover {
    background: #f8fafc;
}

@media (min-width: 992px) {
    .sidebar-toggle {
        display: none;
    }
}

/* Header Title - Center */
.header-title {
    flex: 1;
    text-align: center;
}

.header-title-text {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (max-width: 768px) {
    .header-title-text {
        font-size: 1rem;
    }
}

/* Header Right - Profil di kanan */
.header-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
    flex-shrink: 0;
    min-width: 40px;
}

/* User Menu Styles */
.user-wrapper {
    position: relative;
}

.user-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.25rem 0.75rem;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.user-btn:hover {
    background: #f8fafc;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #3730a3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.user-info {
    text-align: right;
}

.user-name {
    display: block;
    font-weight: 600;
    font-size: 0.875rem;
    color: #1e293b;
    line-height: 1.3;
}

.user-role {
    display: block;
    font-size: 0.688rem;
    color: #6b7280;
}

.user-btn i {
    font-size: 0.75rem;
    color: #6b7280;
    transition: transform 0.2s ease;
}

.user-btn[aria-expanded="true"] i {
    transform: rotate(180deg);
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 240px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    margin-top: 0.5rem;
    display: none;
    z-index: 1050;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.user-dropdown.show {
    display: block;
    animation: fadeIn 0.2s ease-out;
}

/* Dropdown Items */
.dropdown-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.dropdown-header h6 {
    margin: 0;
    font-weight: 600;
    color: #1e293b;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    color: #1e293b;
    text-decoration: none;
    transition: background 0.2s ease;
    cursor: pointer;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    font-size: 0.875rem;
}

.dropdown-item:hover {
    background: #f8fafc;
}

.dropdown-item i {
    font-size: 1rem;
    width: 20px;
}

.dropdown-item.text-danger {
    color: #ef4444 !important;
}

.dropdown-item.text-danger:hover {
    background: #fef2f2;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Desktop */
@media (min-width: 992px) {
    .header-content {
        padding: 0 2rem;
    }
    
    .user-info {
        display: block !important;
    }
    
    .user-btn {
        padding: 0.25rem 1rem;
    }
}

/* Responsive Mobile */
@media (max-width: 768px) {
    .header-content {
        padding: 0 1rem;
    }
    
    .user-info {
        display: none;
    }
    
    .user-btn {
        padding: 0.25rem 0.5rem;
    }
    
    .user-dropdown {
        position: fixed;
        top: 60px;
        right: 10px;
        left: auto;
        width: 280px;
        max-width: calc(100% - 20px);
    }
}

@media (max-width: 576px) {
    .header-title-text {
        font-size: 0.875rem;
    }
    
    .user-avatar {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .user-dropdown {
        width: 260px;
    }
}
</style>