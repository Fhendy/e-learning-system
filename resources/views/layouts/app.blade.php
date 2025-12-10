<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - E-Learning System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    
    @stack('styles')
</head>
<body class="@auth app-body @endauth">
    @auth
        <div class="app-container">
            <!-- Sidebar -->
            @include('layouts.partials.sidebar')
            
            <!-- Main Content -->
            <div class="main-content" id="mainContent">
                <!-- Top Header -->
                @include('layouts.partials.header')
                
                <!-- Page Content -->
                <main class="page-content">
                    <!-- Alerts -->
                    <div class="container-fluid">
                        @include('layouts.partials.alerts')
                    </div>
                    
                    <!-- Main Content -->
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </main>
                
                <!-- Footer -->
                @include('layouts.partials.footer')
            </div>
        </div>
    @else
        <!-- Content for non-authenticated users -->
        <div class="auth-layout">
            @yield('content')
        </div>
    @endauth
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>