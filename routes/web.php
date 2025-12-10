<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
// Protected Routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Dashboard Alias
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        if (in_array($user->role, ['admin', 'teacher', 'guru'])) {
            return redirect()->route('dashboard.teacher');
        } else {
            return redirect()->route('dashboard.student');
        }
    })->name('dashboard');
    
    // Redirect home ke dashboard yang sesuai
    Route::get('/home', function () {
        $user = auth()->user();
        
        if (in_array($user->role, ['admin', 'teacher', 'guru'])) {
            return redirect()->route('dashboard.teacher');
        } else {
            return redirect()->route('dashboard.student');
        }
    })->name('home');
    
    // Dashboard Routes
    Route::get('/dashboard/student', [DashboardController::class, 'studentDashboard'])
        ->name('dashboard.student')->middleware('role:student');
    
    Route::get('/dashboard/teacher', [DashboardController::class, 'teacherDashboard'])
        ->name('dashboard.teacher')->middleware('role:teacher,guru,admin');
    
    // =================== ASSIGNMENT ROUTES ===================
    Route::prefix('assignments')->name('assignments.')->group(function () {
        // Untuk siswa
        Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
            Route::get('/', [AssignmentController::class, 'indexStudent'])->name('index');
            Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
        });
        
        // Untuk guru/admin
        Route::prefix('teacher')->name('teacher.')->middleware('role:teacher,guru,admin')->group(function () {
            Route::get('/', [AssignmentController::class, 'indexTeacher'])->name('index');
            Route::get('/create', [AssignmentController::class, 'create'])->name('create');
            Route::post('/', [AssignmentController::class, 'store'])->name('store');
            Route::get('/{assignment}/edit', [AssignmentController::class, 'edit'])->name('edit');
            Route::put('/{assignment}', [AssignmentController::class, 'update'])->name('update');
            Route::delete('/{assignment}', [AssignmentController::class, 'destroy'])->name('destroy');
            
            // Fitur tambahan untuk guru
            Route::post('/{assignment}/extend', [AssignmentController::class, 'extendDeadline'])
                ->name('extend');
            Route::post('/{assignment}/announce', [AssignmentController::class, 'sendAnnouncement'])
                ->name('announce');
            Route::get('/{assignment}/download-all', [AssignmentController::class, 'downloadAllSubmissions'])
                ->name('download-all');
            Route::post('/{assignment}/batch-grade', [AssignmentController::class, 'batchGrade'])
                ->name('batch-grade');
        });
        
        // Route umum (untuk semua role)
        Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
    });
    
    // =================== SUBMISSION ROUTES ===================
    Route::prefix('submissions')->name('submissions.')->group(function () {
        // Untuk siswa (mengumpulkan tugas)
        Route::middleware('role:student')->group(function () {
            Route::post('/{assignment}/submit', [SubmissionController::class, 'submit'])->name('submit');
            Route::post('/{assignment}/resubmit', [SubmissionController::class, 'resubmit'])->name('resubmit');
            Route::post('/{assignment}/save-draft', [SubmissionController::class, 'saveDraft'])->name('save-draft');
        });
        
        // Untuk guru/admin (menilai tugas)
        Route::middleware('role:teacher,guru,admin')->group(function () {
            Route::post('/{submission}/grade', [SubmissionController::class, 'grade'])->name('grade');
            Route::get('/{submission}', [SubmissionController::class, 'show'])->name('show');
            Route::get('/{assignment}/export-grades', [SubmissionController::class, 'exportGrades'])->name('export-grades');
            Route::post('/students/{student}/remind', [SubmissionController::class, 'remindStudent'])->name('remind-student');
        });
        
        // Route untuk mendapatkan submission (AJAX)
        Route::get('/{submission}/get', [SubmissionController::class, 'getSubmission'])
            ->name('get-submission');
    });
    
    // =================== ATTENDANCE ROUTES ===================
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // SCAN QR CODE ROUTE - Harus ditempatkan di atas yang lain
        Route::get('/scan', [AttendanceController::class, 'scanPage'])->name('scan'); // TAMBAHKAN INI
        Route::get('/scan/{code}', [AttendanceController::class, 'scanQrCode'])
            ->name('scan')
            ->middleware('role:student');
            
        
Route::post('/attendance/scan/{code}', [AttendanceController::class, 'processScan'])
    ->name('attendance.process-scan');
    Route::get('/attendance/scan/{code}/confirm', [AttendanceController::class, 'showConfirm'])
    ->name('attendance.scan-confirm');
        // Untuk guru
        Route::middleware('role:teacher,guru,admin')->group(function () {
            Route::get('/teacher', [AttendanceController::class, 'indexTeacher'])->name('teacher.index');
            Route::get('/class/{class}', [AttendanceController::class, 'classAttendance'])->name('class.show');
            Route::get('/student/{class}/{student}', [AttendanceController::class, 'studentAttendance'])->name('student.detail');
            Route::post('/mark', [AttendanceController::class, 'markAttendance'])->name('mark');
            Route::get('/export', [AttendanceController::class, 'export'])->name('export');
            Route::get('/class-detail/{class}', [AttendanceController::class, 'showClass'])->name('class.detail');
            
            // Manual entry
            Route::get('/manual/create', [AttendanceController::class, 'createManual'])->name('manual.create');
            Route::post('/manual/store', [AttendanceController::class, 'storeManual'])->name('manual.store');
            Route::get('/manual', [AttendanceController::class, 'createManual'])->name('manual'); // Alias
            
            // Edit/delete
            Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
            Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
            Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
            
            // QR Code related
            Route::get('/qr/{qrCode}', [AttendanceController::class, 'viewQrAttendance'])->name('qr.view');
        });
        
        // Untuk siswa
        Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
            Route::get('/', [AttendanceController::class, 'indexStudent'])->name('index');
            Route::get('/{attendance}', [AttendanceController::class, 'showStudent'])->name('show');
            Route::get('/statistics', [AttendanceController::class, 'studentStatistics'])->name('statistics');
        });
    });
    
    // =================== QR CODE ROUTES ===================
    Route::prefix('qr-codes')->name('qr-codes.')->middleware('role:teacher,guru,admin')->group(function () {
        Route::get('/', [QrCodeController::class, 'index'])->name('index');
        Route::get('/create', [QrCodeController::class, 'create'])->name('create');
        Route::post('/', [QrCodeController::class, 'store'])->name('store');
        Route::get('/dashboard', [QrCodeController::class, 'dashboard'])->name('dashboard');
        Route::get('/{qrCode}', [QrCodeController::class, 'show'])->name('show');
        Route::get('/{qrCode}/edit', [QrCodeController::class, 'edit'])->name('edit');
        Route::put('/{qrCode}', [QrCodeController::class, 'update'])->name('update');
        Route::delete('/{qrCode}', [QrCodeController::class, 'destroy'])->name('destroy');
        Route::get('/{qrCode}/download', [QrCodeController::class, 'download'])->name('download');
        
        // QR Code Activation/Deactivation
        Route::post('/{qrCode}/activate', [QrCodeController::class, 'activate'])->name('activate');
        Route::post('/{qrCode}/deactivate', [QrCodeController::class, 'deactivate'])->name('deactivate');
        
        // QR Code Statistics
        Route::get('/{qrCode}/stats', [QrCodeController::class, 'stats'])->name('stats');
        Route::get('/{qrCode}/export', [QrCodeController::class, 'export'])->name('export');
        
        // QR Code Preview (AJAX)
        Route::post('/preview', [QrCodeController::class, 'preview'])->name('preview');
        Route::get('/active/date', [QrCodeController::class, 'getActiveForDate'])->name('active.date');
    });
    
// =================== CLASS ROUTES ===================
Route::prefix('classes')->name('classes.')->group(function () {
    // Routes untuk semua user yang memiliki akses
    Route::get('/', [ClassController::class, 'index'])->name('index');
    
    // Routes hanya untuk teacher/guru/admin - HARUS DITEMPATKAN SEBELUM {class}
    Route::middleware(['auth'])->group(function () {
        Route::get('/create', [ClassController::class, 'create'])->name('create');
        Route::post('/', [ClassController::class, 'store'])->name('store');
    });
    
    // Route dengan parameter HARUS DITEMPATKAN TERAKHIR
    Route::get('/{id}', [ClassController::class, 'show'])->name('show');
    
    Route::middleware(['auth'])->group(function () {
        Route::get('/{id}/edit', [ClassController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ClassController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClassController::class, 'destroy'])->name('destroy');
        
        // Student management
        Route::post('/{id}/add-student', [ClassController::class, 'addStudent'])->name('add-student');
        Route::delete('/{id}/remove-student/{student}', [ClassController::class, 'removeStudent'])->name('remove-student');
        Route::post('/{id}/bulk-add-students', [ClassController::class, 'bulkAddStudents'])->name('bulk-add-students');
        
        // Student list view
        Route::get('/{id}/students', [ClassController::class, 'students'])->name('students');
        
        // Activation
        Route::post('/{id}/activate', [ClassController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [ClassController::class, 'deactivate'])->name('deactivate');
        
        // Search students (AJAX)
        Route::get('/{id}/search-students', [ClassController::class, 'searchStudents'])->name('search-students');
        
        // Statistics (AJAX)
        Route::get('/{id}/stats', [ClassController::class, 'stats'])->name('stats');
    });
});
    
    // =================== STUDENT ROUTES ===================
    Route::prefix('students')->name('students.')->middleware('role:teacher,guru,admin')->group(function () {
        // CRUD routes
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/{student}', [StudentController::class, 'show'])->name('show');
        Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
        Route::put('/{student}', [StudentController::class, 'update'])->name('update');
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        
        // Attendance routes
        Route::get('/{student}/attendance', [StudentController::class, 'attendance'])->name('attendance');
        
        // AJAX routes
        Route::get('/search', [StudentController::class, 'search'])->name('search');
        
        // IMPORT ROUTES - UNCOMMENT atau HAPUS jika tidak digunakan
        // Jika ingin digunakan, pastikan method import() dan processImport() ada di StudentController
        Route::get('/import', [StudentController::class, 'import'])->name('import');
        Route::post('/import/process', [StudentController::class, 'processImport'])->name('import.process');
        
        // Jika tidak ingin menggunakan import, HAPUS atau COMMENT kedua route di atas
        // Export routes (opsional)
        // Route::get('/export/excel', [StudentController::class, 'exportExcel'])->name('export.excel');
        // Route::get('/export/pdf', [StudentController::class, 'exportPdf'])->name('export.pdf');
    });
    
    // =================== AJAX API ROUTES ===================
    Route::prefix('api')->name('api.')->middleware('auth')->group(function () {
        // Get students not in a class
        Route::get('/classes/{class}/available-students', function($classId) {
            $class = \App\Models\ClassModel::findOrFail($classId);
            $availableStudents = $class->getAvailableStudents();
            
            return response()->json($availableStudents);
        })->name('classes.available-students');
        
        // Get student details
        Route::get('/students/{student}/details', function($studentId) {
            $student = \App\Models\User::findOrFail($studentId);
            
            return response()->json([
                'id' => $student->id,
                'name' => $student->name,
                'nis_nip' => $student->nis_nip,
                'email' => $student->email,
                'is_active' => $student->is_active
            ]);
        })->name('students.details');
        
        // Existing API routes
        Route::get('/classes/{class}/student-count', function($classId) {
            $class = \App\Models\ClassModel::findOrFail($classId);
            return response()->json(['count' => $class->students()->count()]);
        })->name('class.student-count');
        
        Route::get('/qr-codes/active', [QrCodeController::class, 'apiActive'])->name('qr-codes.active');
        Route::get('/qr-codes/stats', [QrCodeController::class, 'apiStats'])->name('qr-codes.stats');
        
        Route::get('/attendance/today-stats', [AttendanceController::class, 'getTodayStats'])->name('attendance.today-stats');
    });
    
    // Additional Routes untuk error handling
    Route::get('/unauthorized', function () {
        return view('errors.unauthorized');
    })->name('unauthorized');
});

// =================== ERROR PAGES ===================
Route::fallback(function () {
    return view('errors.404');
    
});
Route::get('/debug/test-attendance/{code}', function($code) {
    $qrCode = \App\Models\QRCode::where('code', $code)->first();
    
    if (!$qrCode) {
        return "QR Code tidak ditemukan";
    }
    
    return view('debug.test-attendance', compact('qrCode'));
});
// routes/web.php - Tambahkan route berikut:

// QR Code Routes
Route::middleware(['auth'])->group(function () {
    // Generate QR Code for class attendance
    Route::post('/qr-codes/generate-for-class', [QrCodeController::class, 'generateForClass'])
        ->name('qr-codes.generate-for-class');
    
    // Quick generate QR Code
    Route::post('/attendance/quick-generate/{classId}', [AttendanceController::class, 'quickGenerateQr'])
        ->name('attendance.quick-generate');
});

// Attendance Routes
Route::middleware(['auth'])->group(function () {
     Route::get('/attendance/scan-page', [AttendanceController::class, 'scanPage'])
        ->name('attendance.scan.page'); // INI YANG DIBUTUHKAN
    // Process QR Code scan
    Route::post('/attendance/scan-process', [AttendanceController::class, 'scanProcess'])
        ->name('attendance.scan-process');
    
    // Real-time attendance monitoring
    Route::get('/attendance/realtime/{qrCodeId}', [AttendanceController::class, 'realtimeAttendance'])
        ->name('attendance.realtime');
    
    // API for real-time data
    Route::get('/api/attendance/realtime/{qrCodeId}', [AttendanceController::class, 'getRealtimeData'])
        ->name('api.attendance.realtime');
});
// QR Code Routes
Route::middleware(['auth'])->prefix('qr-codes')->group(function () {
    Route::get('/debug', [QrCodeController::class, 'debug'])->name('qr-codes.debug');
    Route::get('/bulk-create', [QrCodeController::class, 'bulkGenerate'])->name('qr-codes.bulk-create');
    Route::post('/bulk-create', [QrCodeController::class, 'bulkGenerate'])->name('qr-codes.bulk-store');
    Route::post('/generate-base64', [QrCodeController::class, 'generateBase64'])->name('qr-codes.generate-base64');
    
    // Existing routes
    Route::get('/', [QrCodeController::class, 'index'])->name('qr-codes.index');
    Route::get('/create', [QrCodeController::class, 'create'])->name('qr-codes.create');
    Route::post('/', [QrCodeController::class, 'store'])->name('qr-codes.store');
    Route::get('/{qrCode}', [QrCodeController::class, 'show'])->name('qr-codes.show');
    Route::get('/{qrCode}/edit', [QrCodeController::class, 'edit'])->name('qr-codes.edit');
    Route::put('/{qrCode}', [QrCodeController::class, 'update'])->name('qr-codes.update');
    Route::delete('/{qrCode}', [QrCodeController::class, 'destroy'])->name('qr-codes.destroy');
    Route::post('/{qrCode}/activate', [QrCodeController::class, 'activate'])->name('qr-codes.activate');
    Route::post('/{qrCode}/deactivate', [QrCodeController::class, 'deactivate'])->name('qr-codes.deactivate');
    Route::get('/{qrCode}/download', [QrCodeController::class, 'download'])->name('qr-codes.download');
    Route::get('/dashboard/overview', [QrCodeController::class, 'dashboard'])->name('qr-codes.dashboard');
    Route::post('/preview', [QrCodeController::class, 'preview'])->name('qr-codes.preview');
    Route::get('/api/active-for-date', [QrCodeController::class, 'getActiveForDate'])->name('qr-codes.active-for-date');
    Route::post('/generate-for-class', [QrCodeController::class, 'generateForClass'])->name('qr-codes.generate-for-class');
    Route::post('/quick-generate', [QrCodeController::class, 'quickGenerate'])->name('qr-codes.quick-generate');
});