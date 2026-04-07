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
     * Regenerate QR Code Image
     */
    public function regenerateImage($id)
    {
        try {
            $qrCode = QRCode::findOrFail($id);
            
            // Generate URL
            $url = url('/attendance/scan-page') . '?qr_code=' . $qrCode->code;
            
            // Generate new QR code image
            $qrCodeImage = $this->generateQrCodeSafely($url, $qrCode->code);
            
            $imageName = 'qr-codes/' . $qrCode->code . '.png';
            
            // Ensure directory exists
            if (!Storage::disk('public')->exists('qr-codes')) {
                Storage::disk('public')->makeDirectory('qr-codes');
            }
            
            // Save image
            Storage::disk('public')->put($imageName, $qrCodeImage);
            
            // Update database
            $qrCode->update(['qr_code_image' => $imageName]);
            
            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil digenerate ulang',
                'image_url' => Storage::url($imageName)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Regenerate image failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate ulang: ' . $e->getMessage()
            ], 500);
        }
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
        
        // Validation rules
        $rules = [
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:1|max:1440',
            'location_restricted' => 'boolean',
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
                    $validator->errors()->add('class_id', 'Anda hanya dapat membuat QR Code untuk kelas Anda sendiri.');
                }
            });
        }
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
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
            
            // Format times properly
            $startTime = Carbon::parse($request->start_time)->format('H:i:s');
            $endTime = Carbon::parse($request->end_time)->format('H:i:s');
            
            // Validate time
            if ($endTime <= $startTime) {
                throw new \Exception('Waktu selesai harus setelah waktu mulai.');
            }
            
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
            if (!Storage::disk('public')->exists('qr-codes')) {
                Storage::disk('public')->makeDirectory('qr-codes');
            }
            
            // Save image
            Storage::disk('public')->put($imageName, $qrCodeImage);
            
            // Verify the file exists
            if (!Storage::disk('public')->exists($imageName)) {
                throw new \Exception('Failed to save image to storage');
            }
            
            // Update with image path
            $qrCode->update(['qr_code_image' => $imageName]);
            
            DB::commit();
            
            Log::info('QR Code Created Successfully', [
                'qr_code_id' => $qrCode->id,
                'image_path' => $imageName,
                'storage_url' => Storage::url($imageName),
                'url' => $url
            ]);
            
            return redirect()->route('qr-codes.show', $qrCode)
                ->with('success', 'QR Code berhasil dibuat! Kode: ' . $code);
                
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
     * Generate QR code image safely with multiple fallbacks
     */
    private function generateQrCodeSafely($url, $code = null)
    {
        Log::info('QR Generation Started', ['url' => substr($url, 0, 100), 'code' => $code]);

        // Coba Google Charts API
        try {
            Log::info('Trying Google Charts API');
            $imageData = $this->methodGoogleChartsApi($url);
            if ($imageData && strlen($imageData) > 100) {
                Log::info('Google Charts API Success', ['size' => strlen($imageData)]);
                return $imageData;
            }
        } catch (\Exception $e) {
            Log::warning('Google Charts failed: ' . $e->getMessage());
        }
        
        // Coba QuickChart API
        try {
            Log::info('Trying QuickChart API');
            $imageData = $this->methodQuickChartApi($url);
            if ($imageData && strlen($imageData) > 100) {
                Log::info('QuickChart API Success', ['size' => strlen($imageData)]);
                return $imageData;
            }
        } catch (\Exception $e) {
            Log::warning('QuickChart failed: ' . $e->getMessage());
        }
        
        // Fallback ke GD Library
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

    /**
     * Generate with Google Charts API
     */
    private function methodGoogleChartsApi($url)
    {
        $googleChartsUrl = 'https://chart.googleapis.com/chart?' . http_build_query([
            'chs' => '300x300',
            'cht' => 'qr',
            'chl' => urlencode($url),
            'choe' => 'UTF-8',
            'chld' => 'H|2',
        ]);
        
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
                'header' => "User-Agent: Mozilla/5.0\r\n"
            ]
        ]);
        
        $imageData = @file_get_contents($googleChartsUrl, false, $context);
        
        if ($imageData === false || strlen($imageData) < 100) {
            throw new \Exception('Failed to fetch QR code from Google Charts');
        }
        
        return $imageData;
    }

    /**
     * Generate with QuickChart API
     */
    private function methodQuickChartApi($url)
    {
        $quickchartUrl = 'https://quickchart.io/qr?' . http_build_query([
            'text' => $url,
            'size' => 300,
            'margin' => 1,
            'format' => 'png'
        ]);
        
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
                'header' => "User-Agent: Mozilla/5.0\r\n"
            ]
        ]);
        
        $imageData = @file_get_contents($quickchartUrl, false, $context);
        
        if ($imageData === false || strlen($imageData) < 100) {
            throw new \Exception('Failed to fetch QR code from QuickChart');
        }
        
        return $imageData;
    }

    /**
     * Create QR with GD Library
     */
    private function methodCreateWithGd($url, $code = null)
    {
        if (!function_exists('gd_info')) {
            throw new \Exception('GD Library not available');
        }
        
        $size = 300;
        $image = imagecreatetruecolor($size, $size);
        
        if (!$image) {
            throw new \Exception('Failed to create image with GD');
        }
        
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 200, 200, 200);
        
        imagefilledrectangle($image, 0, 0, $size, $size, $white);
        imagerectangle($image, 10, 10, $size-10, $size-10, $gray);
        imagerectangle($image, 8, 8, $size-8, $size-8, $black);
        
        $font = 4;
        
        $title = "QR CODE ABSENSI";
        $titleWidth = imagefontwidth($font) * strlen($title);
        $titleX = ($size - $titleWidth) / 2;
        imagestring($image, $font, $titleX, 50, $title, $black);
        
        $displayCode = $code ?: substr($url, -8);
        $codeText = "Kode: " . $displayCode;
        $codeWidth = imagefontwidth($font) * strlen($codeText);
        $codeX = ($size - $codeWidth) / 2;
        imagestring($image, $font, $codeX, 100, $codeText, $black);
        
        $instruction = "Scan untuk absensi";
        $instWidth = imagefontwidth($font) * strlen($instruction);
        $instX = ($size - $instWidth) / 2;
        imagestring($image, $font, $instX, 150, $instruction, $black);
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return $imageData;
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
        imagerectangle($image, 5, 5, $size-5, $size-5, $red);
        
        $font = 5;
        
        $warning = "PERHATIAN!";
        $warningWidth = imagefontwidth($font) * strlen($warning);
        $warningX = ($size - $warningWidth) / 2;
        imagestring($image, $font, $warningX, 100, $warning, $red);
        
        $message = "QR Code gagal digenerate";
        $msgWidth = imagefontwidth($font) * strlen($message);
        $msgX = ($size - $msgWidth) / 2;
        imagestring($image, $font, $msgX, 130, $message, $black);
        
        if ($code) {
            $codeText = "Kode: " . $code;
            $codeWidth = imagefontwidth($font) * strlen($codeText);
            $codeX = ($size - $codeWidth) / 2;
            imagestring($image, $font, $codeX, 160, $codeText, $black);
        }
        
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
        
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak. Ini bukan kelas Anda.');
        }
        
        Log::info('Showing QR Code', [
            'qr_code_id' => $qrCode->id,
            'code' => $qrCode->code,
            'image_path' => $qrCode->qr_code_image,
            'user_id' => $user->id
        ]);
        
        // Check if image exists
        $imageExists = false;
        $imageUrl = null;
        
        if ($qrCode->qr_code_image) {
            $imageExists = Storage::disk('public')->exists($qrCode->qr_code_image);
            $imageUrl = Storage::url($qrCode->qr_code_image);
        }
        
        $qrCode->load(['class', 'class.teacher', 'attendances', 'attendances.student']);
        
        $attendanceStats = $qrCode->attendances()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $attendances = $qrCode->attendances()
            ->with('student')
            ->latest()
            ->paginate(15);
        
        $totalStudents = $qrCode->class->students()->count();
        $attendedCount = ($attendanceStats['present'] ?? 0) + ($attendanceStats['late'] ?? 0);
        $attendancePercentage = $totalStudents > 0 ? round(($attendedCount / $totalStudents) * 100, 1) : 0;
        $totalScans = $qrCode->attendances()->count();
        
        $isExpired = $qrCode->is_expired ?? false;
        $isActive = $qrCode->is_active && !$isExpired;
        
        return view('qr-codes.show', compact(
            'qrCode', 
            'attendanceStats', 
            'attendances',
            'totalStudents',
            'attendancePercentage',
            'totalScans',
            'isExpired',
            'isActive',
            'imageExists',
            'imageUrl'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QRCode $qrCode)
    {
        $user = Auth::user();
        
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
        $isExpired = $qrCode->is_expired ?? false;
        
        if ($isExpired) {
            return redirect()->route('qr-codes.show', $qrCode)
                ->with('warning', 'QR Code sudah kadaluarsa dan tidak dapat diedit.');
        }
        
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
        
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
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
        
        if ($request->boolean('location_restricted')) {
            $rules['latitude'] = 'required|numeric|between:-90,90';
            $rules['longitude'] = 'required|numeric|between:-180,180';
            $rules['radius'] = 'required|integer|min:10|max:1000';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
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
            $startTime = Carbon::parse($request->start_time)->format('H:i:s');
            $endTime = Carbon::parse($request->end_time)->format('H:i:s');
            
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
            
            if ($request->boolean('regenerate_qr')) {
                $url = url('/attendance/scan-page') . '?qr_code=' . $qrCode->code;
                $qrCodeImage = $this->generateQrCodeSafely($url, $qrCode->code);
                $imageName = 'qr-codes/' . $qrCode->code . '.png';
                
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
        
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }
        
        try {
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
            
            $stats = [
                'total' => (clone $qrQuery)->count() ?? 0,
                'active' => (clone $qrQuery)->where('is_active', true)->count() ?? 0,
                'total_scans' => (clone $attendanceQuery)->count() ?? 0,
                'attendance_rate' => 0,
            ];
            
            $activeQrCodes = (clone $qrQuery)
                ->where('is_active', true)
                ->whereDate('date', today())
                ->where('end_time', '>', now()->format('H:i:s'))
                ->with('class')
                ->latest()
                ->take(6)
                ->get();

            $recentQrCodes = (clone $qrQuery)
                ->with('class')
                ->latest()
                ->take(5)
                ->get();
            
            $upcomingQrCodes = (clone $qrQuery)
                ->where('date', '>=', today())
                ->where('is_active', true)
                ->with('class')
                ->orderBy('date')
                ->orderBy('start_time')
                ->take(5)
                ->get();
            
            $classDistribution = (clone $qrQuery)
                ->selectRaw('classes.class_name, COUNT(*) as count')
                ->join('classes', 'qr_codes.class_id', '=', 'classes.id')
                ->groupBy('classes.id', 'classes.class_name')
                ->get()
                ->map(function ($item) {
                    $item->color = $this->generateColor($item->class_name);
                    return $item;
                });
            
            $qrActivityChart = [
                'labels' => [],
                'created' => [],
                'used' => []
            ];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $qrActivityChart['labels'][] = $date->format('d/m');
                $qrActivityChart['created'][] = (clone $qrQuery)->whereDate('created_at', $date)->count();
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
}