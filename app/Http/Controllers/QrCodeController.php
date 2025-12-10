<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use App\Models\ClassModel;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Different data based on user role
        if ($user->role === 'teacher' || $user->role === 'guru') {
            // Teacher sees their own QR codes
            $qrCodes = QRCode::whereHas('class', function ($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->with('class')
            ->latest()
            ->paginate(15);
            
            // Get teacher's classes
            $classes = ClassModel::where('teacher_id', $user->id)->get();
        } else if ($user->role === 'admin') {
            // Admin sees all QR codes
            $qrCodes = QRCode::with('class')
                ->latest()
                ->paginate(15);
                
            $classes = ClassModel::all();
        } else {
            // Student doesn't have access to QR code management
            abort(403, 'Akses ditolak.');
        }
        
        return view('qr-codes.index', compact('qrCodes', 'classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        // Get classes taught by the teacher or all classes for admin
        if (in_array($user->role, ['teacher', 'guru'])) {
            $classes = ClassModel::where('teacher_id', $user->id)
                ->where('is_active', true)
                ->get();
        } else {
            $classes = ClassModel::where('is_active', true)->get();
        }
        
        if ($classes->isEmpty()) {
            return redirect()->route('qr-codes.index')
                ->with('warning', 'Anda belum memiliki kelas aktif. Silakan buat kelas terlebih dahulu.');
        }
        
        // Set default values
        $defaults = [
            'date' => now()->format('Y-m-d'),
            'start_time' => now()->format('H:i'),
            'end_time' => now()->addMinutes(60)->format('H:i'),
            'duration_minutes' => 30,
            'location_restricted' => false,
        ];
        
        return view('qr-codes.create', compact('classes', 'defaults'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $user = Auth::user();
    
    // Check permissions
    if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
        abort(403, 'Akses ditolak.');
    }
    
    Log::info('Starting QR Code Creation', ['user_id' => $user->id, 'role' => $user->role]);
    
    // ... existing validation code ...
    
    DB::beginTransaction();
    
    try {
        // Generate unique QR code
        $code = $this->generateUniqueCode();
        
        Log::info('Generating QR Code', [
            'code' => $code,
            'class_id' => $request->class_id,
            'user_id' => Auth::id(),
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
        ]);
        
        // ... existing time formatting and validation ...
        
        // Create QR code record
        $qrData = [
            'code' => $code,
            'class_id' => $request->class_id,
            'date' => $request->date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $request->duration_minutes,
            'location_restricted' => $request->boolean('location_restricted'),
            'notes' => $request->notes,
            'is_active' => true,
            'created_by' => Auth::id(),
            'scan_count' => 0,
        ];
        
        // Add location data if restricted
        if ($request->boolean('location_restricted')) {
            $qrData['latitude'] = $request->latitude;
            $qrData['longitude'] = $request->longitude;
            $qrData['radius'] = $request->radius;
        }
        
        Log::info('Creating QR Code record', $qrData);
        $qrCode = QRCode::create($qrData);
        
        // Generate QR code URL
        $url = url('/attendance/scan-page') . '?qr_code=' . $code;
        Log::info('QR Code URL', ['url' => $url]);
        
        // Generate QR code image
        Log::info('Starting QR image generation');
        $qrCodeImage = $this->generateQrCodeSafely($url, $code);
        
        if (!$qrCodeImage) {
            throw new \Exception('QR Code image generation returned null');
        }
        
        Log::info('QR image generated', ['size_bytes' => strlen($qrCodeImage)]);
        
        // Simpan sebagai PNG
        $imageName = 'qr-codes/' . $code . '.png';
        
        // Ensure directory exists
        $directory = 'qr-codes';
        if (!Storage::disk('public')->exists($directory)) {
            Log::info('Creating directory: ' . $directory);
            Storage::disk('public')->makeDirectory($directory);
        }
        
        // Save image
        Log::info('Saving image to: ' . $imageName);
        $saved = Storage::disk('public')->put($imageName, $qrCodeImage);
        
        if (!$saved) {
            throw new \Exception('Failed to save image to storage');
        }
        
        // Verify the file exists
        $fileExists = Storage::disk('public')->exists($imageName);
        $fileSize = Storage::disk('public')->size($imageName);
        
        Log::info('Image saved verification', [
            'exists' => $fileExists,
            'size' => $fileSize,
            'path' => $imageName
        ]);
        
        // Update with image path
        $qrCode->update(['qr_code_image' => $imageName]);
        
        DB::commit();
        
        Log::info('QR Code Created Successfully', [
            'qr_code_id' => $qrCode->id,
            'image_path' => $imageName,
            'storage_url' => Storage::url($imageName),
            'full_path' => Storage::disk('public')->path($imageName),
            'url' => $url
        ]);
        
        // Redirect dengan data yang diperlukan untuk preview
        return redirect()->route('qr-codes.show', $qrCode)
            ->with('success', 'QR Code berhasil dibuat! Kode: ' . $code)
            ->with('qr_code_image_url', Storage::url($imageName))
            ->with('qr_code', $code)
            ->with('debug_info', [
                'image_path' => $imageName,
                'file_exists' => $fileExists,
                'file_size' => $fileSize,
                'storage_url' => Storage::url($imageName)
            ]);
            
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Error creating QR Code', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
            'code' => $code ?? 'unknown'
        ]);
        
        return back()->withInput()
            ->with('error', 'Gagal membuat QR Code: ' . $e->getMessage());
    }
}

    /**
     * Generate unique QR code
     */
    private function generateUniqueCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            // Check if code exists
            $exists = QRCode::where('code', $code)->exists();
            
            if ($exists) {
                for ($attempt = 0; $attempt < 10; $attempt++) {
                    $code = '';
                    for ($i = 0; $i < 8; $i++) {
                        $code .= $characters[random_int(0, strlen($characters) - 1)];
                    }
                    if (!QRCode::where('code', $code)->exists()) {
                        return $code;
                    }
                }
                $code = 'QR' . time() . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3);
            }
        } while ($exists);
        
        return $code;
    }

    /**
     * Generate QR code safely without Imagick dependency
     */
private function generateQrCodeSafely($url, $code = null)
{
    Log::info('QR Generation Started', ['url' => substr($url, 0, 100), 'code' => $code]);

    // Coba QuickChart dulu
    try {
        Log::info('Trying QuickChart API');
        $imageData = $this->methodQuickChartApi($url, $code);
        if ($imageData && strlen($imageData) > 100) {
            Log::info('QuickChart API Success', ['size' => strlen($imageData)]);
            return $imageData;
        }
    } catch (\Exception $e) {
        Log::warning('QuickChart failed: ' . $e->getMessage());
    }
    
    // Coba Google Charts
    try {
        Log::info('Trying Google Charts API');
        $imageData = $this->methodGoogleChartsApi($url, $code);
        if ($imageData && strlen($imageData) > 100) {
            Log::info('Google Charts API Success', ['size' => strlen($imageData)]);
            return $imageData;
        }
    } catch (\Exception $e) {
        Log::warning('Google Charts failed: ' . $e->getMessage());
    }
    
    // Fallback ke GD
    try {
        Log::info('Trying GD Library');
        $imageData = $this->methodCreateWithGd($url, $code);
        if ($imageData) {
            Log::info('GD Library Success', ['size' => strlen($imageData)]);
            return $imageData;
        }
    } catch (\Exception $e) {
        Log::warning('GD Library failed: ' . $e->getMessage());
    }
    
    // Ultimate fallback
    Log::warning('All methods failed, using empty image');
    return $this->createEmptyQrImage($code);
}

private function methodQuickChartApi($url, $code = null)
{
    try {
        $quickchartUrl = 'https://quickchart.io/qr?' . http_build_query([
            'text' => $url,
            'size' => 300,
            'margin' => 1,
            'format' => 'png'
        ]);
        
        Log::info('QuickChart URL', ['url' => substr($quickchartUrl, 0, 150)]);
        
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
                'header' => "User-Agent: Mozilla/5.0\r\n"
            ]
        ]);
        
        $imageData = @file_get_contents($quickchartUrl, false, $context);
        
        if ($imageData === false) {
            $error = error_get_last();
            throw new \Exception('Failed to fetch: ' . ($error['message'] ?? 'Unknown error'));
        }
        
        if (strlen($imageData) < 100) {
            throw new \Exception('Response too small: ' . strlen($imageData) . ' bytes');
        }
        
        return $imageData;
        
    } catch (\Exception $e) {
        throw new \Exception('QuickChart API failed: ' . $e->getMessage());
    }
}
    
    /**
     * Force GD driver untuk semua image operations
     */
    private function forceGdDriver()
    {
        // Force config
        config(['image.driver' => 'gd']);
        
        // Check GD
        if (!function_exists('gd_info')) {
            throw new \Exception('GD Library tidak tersedia di PHP. Install dengan: sudo apt-get install php8.x-gd');
        }
        
        $gdInfo = gd_info();
        if (!isset($gdInfo['PNG Support']) || !$gdInfo['PNG Support']) {
            throw new \Exception('GD Library tidak mendukung PNG');
        }
        
        return $gdInfo;
    }
    
    /**
     * Method 1: Google Charts API (sudah terbukti bekerja)
     */
private function methodGoogleChartsApi($url, $code = null)
{
    try {
        // URL encode untuk aman
        $encodedUrl = urlencode($url);
        
        $googleChartsUrl = 'https://chart.googleapis.com/chart?' . http_build_query([
            'chs' => '300x300',
            'cht' => 'qr',
            'chl' => $encodedUrl,
            'choe' => 'UTF-8',
            'chld' => 'H|2',
        ]);
        
        Log::info('Google Charts API Request', [
            'url' => $googleChartsUrl,
            'original_url' => $url,
            'encoded_url' => $encodedUrl
        ]);
        
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
            ]
        ]);
        
        $imageData = @file_get_contents($googleChartsUrl, false, $context);
        
        if ($imageData === false) {
            $error = error_get_last();
            throw new \Exception('File get contents failed: ' . ($error['message'] ?? 'Unknown error'));
        }
        
        if (strlen($imageData) < 100) {
            throw new \Exception('Image data too small: ' . strlen($imageData) . ' bytes');
        }
        
        // Check if it's a PNG
        if (substr($imageData, 1, 3) !== 'PNG') {
            // Try to get error message from response
            $errorMessage = substr($imageData, 0, 200);
            throw new \Exception('Not a valid PNG. Response: ' . $errorMessage);
        }
        
        Log::info('Google Charts API Success', [
            'bytes_received' => strlen($imageData),
            'is_png' => substr($imageData, 1, 3) === 'PNG'
        ]);
        
        return $imageData;
        
    } catch (\Exception $e) {
        Log::error('Google Charts API Error', [
            'error' => $e->getMessage(),
            'url' => $url
        ]);
        throw new \Exception('Google Charts API failed: ' . $e->getMessage());
    }
}
    
    /**
     * Method 2: Create with GD Library directly
     */
    private function methodCreateWithGd($url, $code = null)
    {
        // Check if GD is available
        if (!function_exists('gd_info')) {
            throw new \Exception('GD Library tidak tersedia');
        }
        
        $size = 300;
        $image = imagecreatetruecolor($size, $size);
        
        if (!$image) {
            throw new \Exception('Gagal membuat image dengan GD');
        }
        
        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 200, 200, 200);
        
        // Fill background
        imagefilledrectangle($image, 0, 0, $size, $size, $white);
        
        // Add decorative border
        imagerectangle($image, 10, 10, $size-10, $size-10, $gray);
        imagerectangle($image, 8, 8, $size-8, $size-8, $black);
        
        // Add text
        $font = 4; // Built-in font
        
        // Title
        $title = "QR CODE ABSENSI";
        $titleWidth = imagefontwidth($font) * strlen($title);
        $titleX = ($size - $titleWidth) / 2;
        imagestring($image, $font, $titleX, 50, $title, $black);
        
        // Code
        $displayCode = $code ?: substr($url, -8);
        $codeText = "Kode: " . $displayCode;
        $codeWidth = imagefontwidth($font) * strlen($codeText);
        $codeX = ($size - $codeWidth) / 2;
        imagestring($image, $font, $codeX, 100, $codeText, $black);
        
        // Instructions
        $instruction = "Scan untuk absensi";
        $instWidth = imagefontwidth($font) * strlen($instruction);
        $instX = ($size - $instWidth) / 2;
        imagestring($image, $font, $instX, 150, $instruction, $black);
        
        // URL (shortened)
        $shortUrl = parse_url($url, PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);
        $urlText = substr($shortUrl, 0, 30);
        $urlWidth = imagefontwidth($font) * strlen($urlText);
        $urlX = ($size - $urlWidth) / 2;
        imagestring($image, $font, $urlX, 200, $urlText, $black);
        
        // Output sebagai PNG
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return $imageData;
    }
    
    /**
     * Method 3: Simple image fallback
     */
    private function methodCreateSimpleImage($url, $code = null)
    {
        $size = 300;
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        imagefilledrectangle($image, 0, 0, $size, $size, $white);
        
        // Add border
        imagerectangle($image, 5, 5, $size-5, $size-5, $black);
        
        // Add text
        $text = "SCAN UNTUK\nABSENSI\n\nKode: " . ($code ?: 'N/A');
        $lines = explode("\n", $text);
        
        $font = 5;
        $lineHeight = 25;
        $startY = ($size - (count($lines) * $lineHeight)) / 2;
        
        foreach ($lines as $i => $line) {
            $textWidth = imagefontwidth($font) * strlen($line);
            $x = ($size - $textWidth) / 2;
            $y = $startY + ($i * $lineHeight);
            imagestring($image, $font, $x, $y, $line, $black);
        }
        
        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        imagedestroy($image);
        
        return $data;
    }
    
    /**
     * Create empty QR image as last resort
     */
    private function createEmptyQrImage($code = null)
    {
        $size = 300;
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        
        imagefilledrectangle($image, 0, 0, $size, $size, $white);
        
        // Warning border
        imagerectangle($image, 5, 5, $size-5, $size-5, $red);
        
        // Warning text
        $warning = "PERHATIAN!";
        $font = 5;
        $warningWidth = imagefontwidth($font) * strlen($warning);
        $warningX = ($size - $warningWidth) / 2;
        imagestring($image, $font, $warningX, 100, $warning, $red);
        
        // Message
        $message = "QR Code gagal digenerate";
        $msgWidth = imagefontwidth($font) * strlen($message);
        $msgX = ($size - $msgWidth) / 2;
        imagestring($image, $font, $msgX, 130, $message, $black);
        
        // Code if available
        if ($code) {
            $codeText = "Kode: " . $code;
            $codeWidth = imagefontwidth($font) * strlen($codeText);
            $codeX = ($size - $codeWidth) / 2;
            imagestring($image, $font, $codeX, 160, $codeText, $black);
        }
        
        // Instruction
        $instruction = "Hubungi admin";
        $instWidth = imagefontwidth($font) * strlen($instruction);
        $instX = ($size - $instWidth) / 2;
        imagestring($image, $font, $instX, 190, $instruction, $black);
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return $imageData;
    }

    /**
     * Display the specified resource.
     */
  public function show(QRCode $qrCode)
{
    $user = Auth::user();
    
    // Check permissions
    if ($user->role === 'student') {
        abort(403, 'Akses ditolak.');
    }
    
    if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
        abort(403, 'Akses ditolak. Ini bukan kelas Anda.');
    }
    
    // Log untuk debugging
    Log::info('Showing QR Code', [
        'qr_code_id' => $qrCode->id,
        'code' => $qrCode->code,
        'image_path' => $qrCode->qr_code_image,
        'user_id' => $user->id
    ]);
    
    // Check if image exists
    $imageExists = false;
    $imageUrl = null;
    $imagePath = null;
    
    if ($qrCode->qr_code_image) {
        $imageExists = Storage::disk('public')->exists($qrCode->qr_code_image);
        $imageUrl = Storage::url($qrCode->qr_code_image);
        $imagePath = Storage::disk('public')->path($qrCode->qr_code_image);
        
        Log::info('QR Image Check', [
            'exists' => $imageExists,
            'url' => $imageUrl,
            'path' => $imagePath,
            'full_path' => $imagePath
        ]);
    }
    
    $qrCode->load(['class', 'class.teacher', 'attendances', 'attendances.student']);
    
    // Get attendance statistics
    $attendanceStats = $qrCode->attendances()
        ->selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();
    
    // Get paginated attendances
    $attendances = $qrCode->attendances()
        ->with('student')
        ->latest()
        ->paginate(15);
    
    // Get total students in class
    $totalStudents = $qrCode->class->students()->count();
    
    // Calculate attendance statistics
    $attendedCount = ($attendanceStats['present'] ?? 0) + ($attendanceStats['late'] ?? 0);
    $attendancePercentage = $totalStudents > 0 ? round(($attendedCount / $totalStudents) * 100, 1) : 0;
    
    // Calculate total scans
    $totalScans = $qrCode->attendances()->count();
    
    // Get QR code validity status
    $isExpired = $qrCode->is_expired;
    $isActive = $qrCode->is_active_now;
    $isFuture = now() < $qrCode->full_start_datetime;
    
    return view('qr-codes.show', compact(
        'qrCode', 
        'attendanceStats', 
        'attendances',
        'totalStudents',
        'attendancePercentage',
        'totalScans',
        'isExpired',
        'isActive',
        'isFuture',
        'imageExists',
        'imageUrl',
        'imagePath'
    ));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QRCode $qrCode)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
        // Check if QR code can be edited (not expired)
        $endDateTime = Carbon::parse($qrCode->date . ' ' . $qrCode->end_time);
        if (now()->gt($endDateTime)) {
            return redirect()->route('qr-codes.show', $qrCode)
                ->with('warning', 'QR Code sudah kadaluarsa dan tidak dapat diedit.');
        }
        
        // Get available classes
        if (in_array($user->role, ['teacher', 'guru'])) {
            $classes = ClassModel::where('teacher_id', $user->id)
                ->where('is_active', true)
                ->get();
        } else {
            $classes = ClassModel::where('is_active', true)->get();
        }
        
        return view('qr-codes.edit', compact('qrCode', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QRCode $qrCode)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
        // Validation rules
        $rules = [
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'location_restricted' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ];
        
        // Conditional validation for location restriction
        if ($request->boolean('location_restricted')) {
            $rules['latitude'] = 'required|numeric|between:-90,90';
            $rules['longitude'] = 'required|numeric|between:-180,180';
            $rules['radius'] = 'required|integer|min:10|max:1000';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        // Additional validation for teachers
        if (in_array($user->role, ['teacher', 'guru'])) {
            $validator->after(function ($validator) use ($user, $request) {
                $class = ClassModel::find($request->class_id);
                if ($class && $class->teacher_id != $user->id) {
                    $validator->errors()->add('class_id', 'Anda hanya dapat mengupdate QR Code untuk kelas Anda sendiri.');
                }
            });
        }
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            // Format times properly
            $startTime = Carbon::parse($request->start_time)->format('H:i:s');
            $endTime = Carbon::parse($request->end_time)->format('H:i:s');
            
            // Prepare update data
            $updateData = [
                'class_id' => $request->class_id,
                'date' => $request->date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $request->duration_minutes,
                'location_restricted' => $request->boolean('location_restricted'),
                'is_active' => $request->boolean('is_active'),
                'notes' => $request->notes,
            ];
            
            // Add location data if restricted
            if ($request->boolean('location_restricted')) {
                $updateData['latitude'] = $request->latitude;
                $updateData['longitude'] = $request->longitude;
                $updateData['radius'] = $request->radius;
            } else {
                $updateData['latitude'] = null;
                $updateData['longitude'] = null;
                $updateData['radius'] = null;
            }
            
            $qrCode->update($updateData);
            
            // Regenerate QR code image if needed
            if ($request->has('regenerate_qr') && $request->boolean('regenerate_qr')) {
                $url = url('/attendance/scan-page') . '?qr_code=' . $qrCode->code;
                $qrCodeImage = $this->generateQrCodeSafely($url, $qrCode->code);
                
                $imageName = 'qr-codes/' . $qrCode->code . '.png';
                
                // Ensure directory exists
                if (!Storage::disk('public')->exists('qr-codes')) {
                    Storage::disk('public')->makeDirectory('qr-codes');
                }
                
                Storage::disk('public')->put($imageName, $qrCodeImage);
                
                $qrCode->update(['qr_code_image' => $imageName]);
            }
            
            Log::info('QR Code Updated Successfully', [
                'qr_code_id' => $qrCode->id,
                'updated_by' => Auth::id()
            ]);
            
            return redirect()->route('qr-codes.show', $qrCode)
                ->with('success', 'QR Code berhasil diperbarui!');
                
        } catch (\Exception $e) {
            Log::error('Error updating QR Code', [
                'error' => $e->getMessage(),
                'qr_code_id' => $qrCode->id
            ]);
            
            return back()->withInput()
                ->with('error', 'Gagal memperbarui QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QRCode $qrCode)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
        try {
            // Delete QR code image if exists
            if ($qrCode->qr_code_image && Storage::disk('public')->exists($qrCode->qr_code_image)) {
                Storage::disk('public')->delete($qrCode->qr_code_image);
            }
            
            $qrCode->delete();
            
            Log::info('QR Code Deleted', [
                'qr_code_id' => $qrCode->id,
                'deleted_by' => Auth::id()
            ]);
            
            return redirect()->route('qr-codes.index')
                ->with('success', 'QR Code berhasil dihapus!');
                
        } catch (\Exception $e) {
            Log::error('Error deleting QR Code', [
                'error' => $e->getMessage(),
                'qr_code_id' => $qrCode->id
            ]);
            
            return back()->with('error', 'Gagal menghapus QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Activate QR Code
     */
    public function activate(QRCode $qrCode)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
        $qrCode->update(['is_active' => true]);
        
        return back()->with('success', 'QR Code berhasil diaktifkan!');
    }

    /**
     * Deactivate QR Code
     */
    public function deactivate(QRCode $qrCode)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
        $qrCode->update(['is_active' => false]);
        
        return back()->with('success', 'QR Code berhasil dinonaktifkan!');
    }

    /**
     * Display QR Code dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        try {
            // Base queries
            $qrQuery = QRCode::query();
            $attendanceQuery = Attendance::query();
            
            if (in_array($user->role, ['teacher', 'guru'])) {
                $qrQuery->whereHas('class', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
                
                $attendanceQuery->whereHas('class', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                });
            }
            
            // Statistics
            $stats = [
                'total' => (clone $qrQuery)->count() ?? 0,
                'active' => (clone $qrQuery)->where('is_active', true)->count() ?? 0,
                'total_scans' => (clone $attendanceQuery)->count() ?? 0,
                'attendance_rate' => 0,
            ];
            
            // Active QR Codes for today
            $activeQrCodes = (clone $qrQuery)
                ->where('is_active', true)
                ->whereDate('date', today())
                ->where('end_time', '>', now()->format('H:i:s'))
                ->with('class')
                ->latest()
                ->take(6)
                ->get();

            // Recent QR Codes
            $recentQrCodes = (clone $qrQuery)
                ->with('class')
                ->latest()
                ->take(5)
                ->get();
            
            // Upcoming QR Codes
            $upcomingQrCodes = (clone $qrQuery)
                ->where('date', '>=', today())
                ->where('is_active', true)
                ->with('class')
                ->orderBy('date')
                ->orderBy('start_time')
                ->take(5)
                ->get();
            
            // Class Distribution
            $classDistribution = (clone $qrQuery)
                ->selectRaw('classes.class_name, COUNT(*) as count')
                ->join('classes', 'qr_codes.class_id', '=', 'classes.id')
                ->groupBy('classes.id', 'classes.class_name')
                ->get()
                ->map(function ($item) {
                    $item->color = $this->generateColor($item->class_name);
                    return $item;
                });
            
            // QR Activity Chart Data (last 7 days)
            $qrActivityChart = [
                'labels' => [],
                'created' => [],
                'used' => []
            ];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $qrActivityChart['labels'][] = $date->format('d/m');
                
                // QR codes created on this day
                $qrActivityChart['created'][] = (clone $qrQuery)
                    ->whereDate('created_at', $date)
                    ->count();
                
                // QR codes used (with attendances) on this day
                $qrActivityChart['used'][] = (clone $qrQuery)
                    ->whereHas('attendances', function ($query) use ($date) {
                        $query->whereDate('created_at', $date);
                    })
                    ->count();
            }
            
            return view('qr-codes.dashboard', compact(
                'stats', 
                'activeQrCodes', 
                'recentQrCodes', 
                'upcomingQrCodes', 
                'classDistribution',
                'qrActivityChart'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading QR Code dashboard', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return default values jika error
            $defaultStats = [
                'active' => 0,
                'total' => 0,
                'total_scans' => 0,
                'attendance_rate' => 0,
            ];
            
            return view('qr-codes.dashboard', [
                'stats' => $defaultStats,
                'activeQrCodes' => collect(),
                'recentQrCodes' => collect(),
                'upcomingQrCodes' => collect(),
                'classDistribution' => collect(),
                'qrActivityChart' => [
                    'labels' => [],
                    'created' => [],
                    'used' => []
                ],
                'error' => 'Gagal memuat dashboard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download QR Code image
     */
    public function download(QRCode $qrCode)
    {
        $user = Auth::user();
        
        // Check permissions
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
        if (!$qrCode->qr_code_image || !Storage::disk('public')->exists($qrCode->qr_code_image)) {
            return back()->with('error', 'Gambar QR Code tidak ditemukan.');
        }
        
        $path = Storage::disk('public')->path($qrCode->qr_code_image);
        $filename = 'qr-code-' . $qrCode->code . '-' . now()->format('Ymd-His') . '.png';
        
        return response()->download($path, $filename);
    }

    /**
     * Preview QR Code Generation (for AJAX)
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $class = ClassModel::findOrFail($request->class_id);
            
            // Generate temporary code for preview
            $tempCode = 'PREVIEW' . time();
            $url = url('/attendance/scan-page') . '?qr_code=' . $tempCode;
            
            // Generate QR code image
            $qrCodeImage = $this->generateQrCodeSafely($url, $tempCode);
            
            $base64Image = 'data:image/png;base64,' . base64_encode($qrCodeImage);
            
            return response()->json([
                'success' => true,
                'preview' => [
                    'class_name' => $class->class_name,
                    'class_code' => $class->class_code,
                    'teacher_name' => $class->teacher->name ?? 'N/A',
                    'date' => Carbon::parse($request->date)->translatedFormat('l, d F Y'),
                    'time' => Carbon::parse($request->start_time)->format('H:i') . ' - ' . 
                              Carbon::parse($request->end_time)->format('H:i'),
                    'total_students' => $class->students()->count(),
                    'qr_image' => $base64Image,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating QR preview', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active QR codes for specific date
     */
    public function getActiveForDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'class_id' => 'nullable|exists:classes,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        
        $user = Auth::user();
        $query = QRCode::where('is_active', true)
            ->whereDate('date', $request->date);
        
        if (in_array($user->role, ['teacher', 'guru'])) {
            $query->whereHas('class', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }
        
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        $qrCodes = $query->with('class')->get();
        
        return response()->json([
            'success' => true,
            'data' => $qrCodes,
            'count' => $qrCodes->count()
        ]);
    }

    /**
     * Generate consistent color for class names
     */
    private function generateColor($string)
    {
        $colors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#5a5c69', '#858796', '#6f42c1', '#20c9a6', '#fd7e14',
            '#6610f2', '#6f42c1', '#e83e8c', '#17a2b8', '#ffc107'
        ];
        
        $hash = crc32($string);
        return $colors[abs($hash) % count($colors)];
    }

    /**
     * Generate QR Code untuk absensi semua siswa di kelas (Single QR Code)
     */
    public function generateForClass(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:5|max:240',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if teacher owns the class
        $class = ClassModel::find($request->class_id);
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak mengajar kelas ini'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Generate unique code untuk kelas
            $classCode = strtoupper(substr(preg_replace('/[^A-Z]/', '', $class->class_name), 0, 3));
            $dateCode = now()->format('Ymd');
            
            do {
                $randomCode = Str::random(4);
                $code = $classCode . '-' . $dateCode . '-' . strtoupper($randomCode);
                
                $exists = QRCode::where('code', $code)->exists();
            } while ($exists);
            
            // Format times
            $startTime = Carbon::parse($request->start_time)->format('H:i:s');
            $endTime = Carbon::parse($request->end_time)->format('H:i:s');
            
            // Create QR code
            $qrCode = QRCode::create([
                'code' => $code,
                'class_id' => $class->id,
                'date' => $request->date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $request->duration_minutes,
                'notes' => $request->notes,
                'is_active' => true,
                'created_by' => $user->id,
                'scan_count' => 0,
            ]);

            // Generate QR code URL
            $url = url('/attendance/scan-page') . '?qr_code=' . $code;
            
            // Generate QR code image
            $qrCodeImage = $this->generateQrCodeSafely($url, $code);

            // Save image
            $imageName = 'qr-codes/class-' . $code . '.png';
            
            // Ensure directory exists
            if (!Storage::disk('public')->exists('qr-codes')) {
                Storage::disk('public')->makeDirectory('qr-codes');
            }
            
            Storage::disk('public')->put($imageName, $qrCodeImage);
            
            $qrCode->update(['qr_code_image' => $imageName]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil dibuat untuk semua siswa di kelas',
                'data' => [
                    'qr_code_id' => $qrCode->id,
                    'code' => $code,
                    'qr_image_url' => Storage::url($imageName),
                    'class_name' => $class->class_name,
                    'date' => $qrCode->date->format('d F Y'),
                    'time_range' => $qrCode->formatted_time_range,
                    'duration' => $qrCode->duration_minutes . ' menit',
                    'realtime_url' => route('attendance.realtime', $qrCode->id),
                    'total_students' => $class->students()->count(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating QR code for class', [
                'error' => $e->getMessage(),
                'class_id' => $request->class_id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick generate QR Code untuk kelas hari ini (30 menit)
     */
    public function quickGenerate(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $class = ClassModel::find($request->class_id);

        // Authorization check
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak mengajar kelas ini'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Generate unique code
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            // Set waktu: 30 menit dari sekarang
            $startTime = now();
            $endTime = now()->addMinutes(30);

            // Create QR code
            $qrCode = QRCode::create([
                'code' => $code,
                'class_id' => $class->id,
                'date' => today(),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'duration_minutes' => 30,
                'is_active' => true,
                'created_by' => $user->id,
                'scan_count' => 0,
            ]);

            // Generate QR code URL
            $url = url('/attendance/scan-page') . '?qr_code=' . $code;
            
            // Generate QR code image
            $qrCodeImage = $this->generateQrCodeSafely($url, $code);

            $imageName = 'qr-codes/quick-' . $code . '.png';
            
            // Ensure directory exists
            if (!Storage::disk('public')->exists('qr-codes')) {
                Storage::disk('public')->makeDirectory('qr-codes');
            }
            
            Storage::disk('public')->put($imageName, $qrCodeImage);
            
            $qrCode->update(['qr_code_image' => $imageName]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil dibuat',
                'data' => [
                    'id' => $qrCode->id,
                    'code' => $code,
                    'qr_image_url' => Storage::url($imageName),
                    'class_name' => $class->class_name,
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                    'realtime_url' => route('attendance.realtime', $qrCode->id),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error quick generating QR', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QR Code: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Debug QR Code Generation
     */
    public function debug()
    {
        // Cek environment
        $debugInfo = [
            'PHP Version' => phpversion(),
            'GD Library' => function_exists('gd_info') ? 'Available' : 'Not Available',
            'GD Info' => function_exists('gd_info') ? json_encode(gd_info()) : 'N/A',
            'ImageMagick' => extension_loaded('imagick') ? 'Available' : 'Not Available',
            'Storage Path' => storage_path(),
            'Public Path' => public_path(),
            'Config Driver' => config('image.driver', 'default'),
        ];

        // Test QR generation
        $testUrl = 'https://example.com/test';
        $testCode = 'TEST123';
        
        $methods = ['methodGoogleChartsApi', 'methodCreateWithGd', 'methodCreateSimpleImage'];
        $results = [];
        
        foreach ($methods as $method) {
            try {
                $start = microtime(true);
                $result = $this->{$method}($testUrl, $testCode);
                $time = round((microtime(true) - $start) * 1000, 2);
                
                $results[$method] = [
                    'status' => 'Success',
                    'time_ms' => $time,
                    'size_bytes' => strlen($result),
                    'base64_preview' => 'data:image/png;base64,' . base64_encode(substr($result, 0, 100)) . '...'
                ];
            } catch (\Exception $e) {
                $results[$method] = [
                    'status' => 'Failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return view('qr-codes.debug', compact('debugInfo', 'results'));
    }

    /**
     * Generate QR Code as Base64 (for API responses)
     */
    public function generateBase64(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'size' => 'integer|min:100|max:1000',
            'margin' => 'integer|min:0|max:10',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        
        try {
            $content = $request->content;
            
            // Generate QR
            $imageData = $this->generateQrCodeSafely($content);
            
            if (!$imageData) {
                throw new \Exception('Failed to generate QR code');
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'base64' => 'data:image/png;base64,' . base64_encode($imageData),
                    'size' => strlen($imageData),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('API QR Generation Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk generate QR codes for multiple dates
     */
    public function bulkGenerate(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['teacher', 'admin', 'guru'])) {
            abort(403, 'Akses ditolak.');
        }
        
        // Get teacher's classes
        if (in_array($user->role, ['teacher', 'guru'])) {
            $classes = ClassModel::where('teacher_id', $user->id)
                ->where('is_active', true)
                ->get();
        } else {
            $classes = ClassModel::where('is_active', true)->get();
        }
        
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'class_id' => 'required|exists:classes,id',
                'dates' => 'required|array|min:1',
                'dates.*' => 'date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'duration_minutes' => 'required|integer|min:1|max:1440',
                'location_restricted' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            // Authorization check for teachers
            if (in_array($user->role, ['teacher', 'guru'])) {
                $class = ClassModel::find($request->class_id);
                if ($class->teacher_id != $user->id) {
                    return back()->with('error', 'Anda hanya dapat membuat QR Code untuk kelas Anda sendiri.');
                }
            }
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($request->dates as $date) {
                try {
                    DB::beginTransaction();
                    
                    // Check if QR already exists for this date and time
                    $existing = QRCode::where('class_id', $request->class_id)
                        ->whereDate('date', $date)
                        ->where('start_time', $request->start_time)
                        ->where('end_time', $request->end_time)
                        ->exists();
                    
                    if ($existing) {
                        $errors[] = "QR Code untuk tanggal {$date} sudah ada";
                        $errorCount++;
                        DB::rollBack();
                        continue;
                    }
                    
                    // Generate unique code
                    $code = $this->generateUniqueCode();
                    
                    // Create QR code
                    $qrData = [
                        'code' => $code,
                        'class_id' => $request->class_id,
                        'date' => $date,
                        'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
                        'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
                        'duration_minutes' => $request->duration_minutes,
                        'location_restricted' => $request->boolean('location_restricted'),
                        'is_active' => true,
                        'created_by' => $user->id,
                        'scan_count' => 0,
                    ];
                    
                    // Add location data if restricted
                    if ($request->boolean('location_restricted')) {
                        $qrData['latitude'] = $request->latitude;
                        $qrData['longitude'] = $request->longitude;
                        $qrData['radius'] = $request->radius;
                    }
                    
                    $qrCode = QRCode::create($qrData);
                    
                    // Generate QR code image
                    $url = url('/attendance/scan-page') . '?qr_code=' . $code;
                    $qrCodeImage = $this->generateQrCodeSafely($url, $code);
                    
                    $imageName = 'qr-codes/bulk-' . $code . '.png';
                    Storage::disk('public')->put($imageName, $qrCodeImage);
                    
                    $qrCode->update(['qr_code_image' => $imageName]);
                    
                    DB::commit();
                    $successCount++;
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errorCount++;
                    $errors[] = "Tanggal {$date}: " . $e->getMessage();
                    Log::error('Bulk QR generation error', [
                        'date' => $date,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $message = "Berhasil membuat {$successCount} QR Code.";
            if ($errorCount > 0) {
                $message .= " Gagal: {$errorCount}.";
            }
            
            return redirect()->route('qr-codes.index')
                ->with('success', $message)
                ->with('errors', $errors);
        }
        
        return view('qr-codes.bulk-create', compact('classes'));
    }
}