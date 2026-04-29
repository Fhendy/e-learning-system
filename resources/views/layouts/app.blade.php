<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) - E-Learning System</title>
    
    <!-- =================== FAVICON (UNTUK SEMUA HALAMAN) =================== -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon/favicon-48x48.png') }}">
    
    <!-- PWA Manual -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}?v={{ time() }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc !important;
            color: #1e293b !important;
            overflow-x: hidden;
        }
        
        /* App Container */
        .app-container {
            display: flex;
            min-height: 100vh;
            background: #f8fafc;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        /* Page Content */
        .page-content {
            flex: 1;
            padding: 1.5rem;
            width: 100%;
        }
        
        /* Container Fluid - Tanpa padding horizontal */
        .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
            width: 100%;
        }
        
        /* Content Wrapper dengan max-width agar tidak terlalu lebar */
        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            padding: 0 1rem;
        }
        
        /* Untuk halaman yang membutuhkan full width (dashboard, tabel besar) */
        .content-wrapper.full-width {
            max-width: 100%;
            padding: 0;
        }
        
        /* Responsive untuk desktop dengan sidebar */
        @media (min-width: 992px) {
            .main-content {
                margin-left: 280px;
                width: calc(100% - 280px);
            }
            
            .page-content {
                padding: 1.5rem 2rem;
            }
            
            .content-wrapper {
                padding: 0 1.5rem;
            }
        }
        
        /* Untuk layar sangat lebar (1440px ke atas) */
        @media (min-width: 1440px) {
            .content-wrapper {
                max-width: 1200px;
                padding: 0 2rem;
            }
        }
        
        /* Untuk layar 1920px ke atas */
        @media (min-width: 1920px) {
            .content-wrapper {
                max-width: 1400px;
            }
        }
        
        /* Responsive untuk tablet */
        @media (min-width: 768px) and (max-width: 991px) {
            .page-content {
                padding: 1.25rem 1.5rem;
            }
            
            .content-wrapper {
                padding: 0 1rem;
            }
        }
        
        /* Responsive untuk mobile */
        @media (max-width: 767px) {
            .page-content {
                padding: 1rem;
            }
            
            .content-wrapper {
                padding: 0 0.75rem;
            }
        }
        
        @media (max-width: 576px) {
            .page-content {
                padding: 0.75rem;
            }
            
            .content-wrapper {
                padding: 0 0.5rem;
            }
        }
        
        /* Auth Layout */
        .auth-layout {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Scrollbar Global */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Utility Classes */
        .no-padding {
            padding: 0 !important;
        }
        
        .no-margin {
            margin: 0 !important;
        }
        
        .full-width {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Card Styles untuk konsistensi */
        .card {
            background: #ffffff;
            border: none;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            border-radius: 16px 16px 0 0 !important;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Table Styles */
        .table {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
        }
        
        /* Button Styles */
        .btn-primary {
            background: #4f46e5;
            border-color: #4f46e5;
        }
        
        .btn-primary:hover {
            background: #4338ca;
            border-color: #4338ca;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @auth
        <div class="app-container">
            @include('layouts.partials.sidebar')
            
            <div class="main-content">
                @include('layouts.partials.header')
                <main class="page-content">
                    <div class="container-fluid">
                        @include('layouts.partials.alerts')
                        <div class="content-wrapper">
                            @yield('content')
                        </div>
                    </div>
                </main>
                @include('layouts.partials.footer')
            </div>
        </div>
        
        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <script>
            $(document).ready(function() {
                // Sidebar Toggle
                $('#sidebarToggle').click(function() {
                    $('#sidebar').toggleClass('active');
                    $('#sidebarOverlay').toggleClass('active');
                    $('body').toggleClass('sidebar-open');
                });
                
                $('#sidebarClose, #sidebarOverlay').click(function() {
                    $('#sidebar').removeClass('active');
                    $('#sidebarOverlay').removeClass('active');
                    $('body').removeClass('sidebar-open');
                });
                
                // Dropdown Toggle
                $('#userBtn').click(function(e) {
                    e.stopPropagation();
                    $('#userDropdown').toggleClass('show');
                    $(this).attr('aria-expanded', function(i, attr) {
                        return attr === 'true' ? 'false' : 'true';
                    });
                });
                
                // Close dropdowns when clicking outside
                $(document).click(function() {
                    $('.user-dropdown').removeClass('show');
                    $('#userBtn').attr('aria-expanded', 'false');
                });
                
                $('.user-dropdown').click(function(e) {
                    e.stopPropagation();
                });
                
                // Logout confirmation
                $('.logout-btn').click(function(e) {
                    if (!confirm('Apakah Anda yakin ingin keluar?')) {
                        e.preventDefault();
                    }
                });
                
                // Handle window resize
                $(window).resize(function() {
                    if ($(window).width() >= 992) {
                        $('.main-content').css('margin-left', '');
                        $('#sidebar').removeClass('active');
                        $('#sidebarOverlay').removeClass('active');
                    }
                });
            });
            
            // Service Worker - PWA
            if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js').then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    }).catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
                });
            }
        </script>
        
        @stack('scripts')
    @else
        <div class="auth-layout">
            @yield('content')
        </div>
    @endauth
</body>
</html>