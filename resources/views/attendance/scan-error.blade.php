<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Scan QR Code</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-top: 100px;
            overflow: hidden;
        }
        
        .error-header {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .error-body {
            padding: 40px;
        }
        
        .error-message {
            background: #fff3f3;
            border-left: 5px solid #f44336;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        
        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .qr-tips {
            background: #e8f4fd;
            border-left: 5px solid #2196f3;
            padding: 20px;
            margin-top: 30px;
            border-radius: 8px;
        }
        
        .qr-tips h6 {
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="error-container">
                    <!-- Header -->
                    <div class="error-header">
                        <div class="error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h1><i class="fas fa-qrcode me-2"></i>Error Scan QR Code</h1>
                        <p class="mb-0">Terjadi masalah saat memindai QR Code</p>
                    </div>
                    
                    <!-- Body -->
                    <div class="error-body">
                        <!-- Error Message -->
                        <div class="error-message">
                            <h4><i class="fas fa-times-circle me-2"></i>Gagal Memindai</h4>
                            <p class="mb-0">{{ $message ?? 'Terjadi kesalahan yang tidak diketahui.' }}</p>
                        </div>
                        
                        <!-- Error Details (if available) -->
                        @if(isset($details) && $details)
                        <div class="error-details">
                            <h6><i class="fas fa-info-circle me-2"></i>Detail Error:</h6>
                            <pre class="mb-0" style="font-size: 12px;">{{ $details }}</pre>
                        </div>
                        @endif
                        
                        <!-- QR Tips -->
                        <div class="qr-tips">
                            <h6><i class="fas fa-lightbulb me-2"></i>Tips untuk scan QR Code:</h6>
                            <ul class="mb-0">
                                <li>Pastikan kamera fokus pada QR Code</li>
                                <li>Pastikan pencahayaan cukup</li>
                                <li>QR Code harus utuh dan tidak rusak</li>
                                <li>Scan pada jarak yang tepat (30-50 cm)</li>
                                <li>Pastikan QR Code masih berlaku</li>
                            </ul>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="text-center mt-4">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="{{ url()->previous() }}" class="btn btn-back me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                
                                <a href="{{ route('attendance.student.index') }}" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Dashboard Absensi
                                </a>
                                
                                <a href="{{ route('attendance.scan.page') }}" class="btn btn-success">
                                    <i class="fas fa-qrcode me-2"></i>Coba Scan Lagi
                                </a>
                            </div>
                        </div>
                        
                        <!-- Debug Info (for development only) -->
                        @if(app()->environment('local'))
                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-bug me-1"></i>
                                Debug: {{ now()->format('Y-m-d H:i:s') }} | 
                                User: {{ Auth::user()->name ?? 'Guest' }}
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Support Info -->
                <div class="text-center mt-4">
                    <p class="text-white mb-1">
                        Butuh bantuan? Hubungi administrator sistem.
                    </p>
                    <p class="text-white">
                        <i class="fas fa-phone me-1"></i> Support: 021-12345678 | 
                        <i class="fas fa-envelope ms-3 me-1"></i> support@sekolah.id
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto redirect after 10 seconds
        setTimeout(function() {
            window.location.href = "{{ url()->previous() }}";
        }, 10000);
        
        // Show error code if available
        @if(isset($errorCode))
        console.error('Error Code:', '{{ $errorCode }}');
        @endif
    </script>
</body>
</html>