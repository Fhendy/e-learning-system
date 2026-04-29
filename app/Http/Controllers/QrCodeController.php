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

class QrCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'teacher' || $user->role === 'guru') {
            $qrCodes = QRCode::whereHas('class', function ($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->with('class')
            ->latest()
            ->paginate(15);
            
            $classes = ClassModel::where('teacher_id', $user->id)->get();
        } else if ($user->role === 'admin') {
            $qrCodes = QRCode::with('class')
                ->latest()
                ->paginate(15);
                
            $classes = ClassModel::all();
        } else {
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
     * Generate QR Code using QR Server API (Reliable)
     */
    private function generateQrCodeImage($url, $code = null)
    {
        // Method 1: QR Server API (Most reliable)
        try {
            $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($url);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $imageData && strlen($imageData) > 500) {
                Log::info('QR Code generated via QR Server API', ['code' => $code, 'size' => strlen($imageData)]);
                return $imageData;
            }
        } catch (\Exception $e) {
            Log::warning('QR Server API failed: ' . $e->getMessage());
        }
        
        // Method 2: QuickChart API (Fallback)
        try {
            $apiUrl = 'https://quickchart.io/qr?text=' . urlencode($url) . '&size=300&margin=2';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $imageData && strlen($imageData) > 500) {
                Log::info('QR Code generated via QuickChart API', ['code' => $code, 'size' => strlen($imageData)]);
                return $imageData;
            }
        } catch (\Exception $e) {
            Log::warning('QuickChart API failed: ' . $e->getMessage());
        }
        
        // Method 3: Google Charts API (Last fallback)
        try {
            $apiUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($url) . '&choe=UTF-8&chld=H|2';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $imageData && strlen($imageData) > 500) {
                Log::info('QR Code generated via Google Charts API', ['code' => $code, 'size' => strlen($imageData)]);
                return $imageData;
            }
        } catch (\Exception $e) {
            Log::warning('Google Charts API failed: ' . $e->getMessage());
        }
        
        // Ultimate fallback: Create simple text image
        Log::warning('All QR APIs failed, creating fallback image', ['code' => $code]);
        return $this->createFallbackImage($code);
    }
    
    /**
     * Create fallback image (if all APIs fail)
     */
    private function createFallbackImage($code)
    {
        $size = 300;
        $image = imagecreatetruecolor($size, $size);
        
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 100, 100, 100);
        
        imagefilledrectangle($image, 0, 0, $size, $size, $white);
        imagerectangle($image, 5, 5, $size-5, $size-5, $gray);
        
        $font = 5;
        $text = "QR CODE";
        $textWidth = imagefontwidth($font) * strlen($text);
        $textX = ($size - $textWidth) / 2;
        $textY = ($size / 2) - 20;
        imagestring($image, $font, $textX, $textY, $text, $black);
        
        if ($code) {
            $codeText = $code;
            $codeWidth = imagefontwidth($font) * strlen($codeText);
            $codeX = ($size - $codeWidth) / 2;
            $codeY = $textY + 30;
            imagestring($image, $font, $codeX, $codeY, $codeText, $gray);
        }
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return $imageData;
    }

    /**
     * Regenerate QR Code Image
     */
    public function regenerateImage($id)
    {
        try {
            $qrCode = QRCode::findOrFail($id);
            $url = url('/attendance/scan-page') . '?qr_code=' . $qrCode->code;
            $qrCodeImage = $this->generateQrCodeImage($url, $qrCode->code);
            
            $imageName = 'qr-codes/' . $qrCode->code . '.png';
            
            if (!Storage::disk('public')->exists('qr-codes')) {
                Storage::disk('public')->makeDirectory('qr-codes');
            }
            
            Storage::disk('public')->put($imageName, $qrCodeImage);
            $qrCode->update(['qr_code_image' => $imageName]);
            
            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil digenerate ulang',
                'qr_code_image' => Storage::url($imageName)
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
        'end_time' => 'required|date_format:H:i',
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
    
    // PERBAIKAN UTAMA: Validasi waktu dengan Carbon
    $validator->after(function ($validator) use ($request) {
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        $date = $request->date;
        
        if ($startTime && $endTime && $date) {
            // Parse dengan tanggal yang sama
            $start = Carbon::parse($date . ' ' . $startTime);
            $end = Carbon::parse($date . ' ' . $endTime);
            
            // Jika end time lebih kecil dari start time, berarti melewati tengah malam
            // Tambahkan 1 hari ke end time
            if ($end->lt($start)) {
                $end = $end->addDay();
            }
            
            // Bandingkan
            if ($end->lte($start)) {
                $validator->errors()->add('end_time', 'Waktu selesai harus setelah waktu mulai.');
            }
            
            // Validasi durasi maksimal (opsional)
            $duration = $start->diffInMinutes($end);
            if ($duration > 1440) { // Maksimal 24 jam
                $validator->errors()->add('duration_minutes', 'Durasi tidak boleh lebih dari 24 jam.');
            }
        }
    });
    
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
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
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
        $qrCodeImage = $this->generateQrCodeImage($url, $code);
        
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
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil dibuat!',
                'data' => $qrCode,
                'redirect_url' => route('qr-codes.show', $qrCode)
            ]);
        }
        
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
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QR Code: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->withInput()
            ->with('error', 'Gagal membuat QR Code: ' . $e->getMessage());
    }
}
    private function generateUniqueCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $exists = QRCode::where('code', $code)->exists();
        } while ($exists);
        
        return $code;
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
        'end_time' => 'required|date_format:H:i',
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
    
    // PERBAIKAN: Validasi waktu
    $validator->after(function ($validator) use ($request) {
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        
        if ($startTime && $endTime) {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            
            if ($end->lte($start)) {
                $validator->errors()->add('end_time', 'Waktu selesai harus setelah waktu mulai.');
            }
        }
    });
    
    if (in_array($user->role, ['teacher', 'guru'])) {
        $validator->after(function ($validator) use ($user, $request) {
            $class = ClassModel::find($request->class_id);
            if ($class && $class->teacher_id != $user->id) {
                $validator->errors()->add('class_id', 'Anda hanya dapat mengupdate QR Code untuk kelas Anda sendiri.');
            }
        });
    }
    
    if ($validator->fails()) {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
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
            $qrCodeImage = $this->generateQrCodeImage($url, $qrCode->code);
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
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil diperbarui!'
            ]);
        }
        
        return redirect()->route('qr-codes.show', $qrCode)
            ->with('success', 'QR Code berhasil diperbarui!');
            
    } catch (\Exception $e) {
        Log::error('Error updating QR Code', [
            'error' => $e->getMessage(),
            'qr_code_id' => $qrCode->id
        ]);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui QR Code: ' . $e->getMessage()
            ], 500);
        }
        
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
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Ini bukan QR Code Anda.'
                ], 403);
            }
            abort(403, 'Akses ditolak.');
        }
        
        try {
            if ($qrCode->qr_code_image && Storage::disk('public')->exists($qrCode->qr_code_image)) {
                Storage::disk('public')->delete($qrCode->qr_code_image);
            }
            
            $qrCode->delete();
            
            Log::info('QR Code Deleted', ['qr_code_id' => $qrCode->id, 'deleted_by' => Auth::id()]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'QR Code berhasil dihapus!'
                ]);
            }
            
            return redirect()->route('qr-codes.index')
                ->with('success', 'QR Code berhasil dihapus!');
                
        } catch (\Exception $e) {
            Log::error('Error deleting QR Code', ['error' => $e->getMessage()]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus QR Code: ' . $e->getMessage()
                ], 500);
            }
            
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
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Ini bukan QR Code Anda.'
                ], 403);
            }
            abort(403, 'Akses ditolak.');
        }
        
        try {
            $qrCode->update(['is_active' => true]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'QR Code berhasil diaktifkan!'
                ]);
            }
            
            return back()->with('success', 'QR Code berhasil diaktifkan!');
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengaktifkan QR Code: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal mengaktifkan QR Code: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate QR Code
     */
    public function deactivate(QRCode $qrCode)
    {
        $user = Auth::user();
        
        if ($user->role === 'student') {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }
            abort(403, 'Akses ditolak.');
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $qrCode->class->teacher_id !== $user->id) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Ini bukan QR Code Anda.'
                ], 403);
            }
            abort(403, 'Akses ditolak.');
        }
        
        try {
            $qrCode->update(['is_active' => false]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'QR Code berhasil dinonaktifkan!'
                ]);
            }
            
            return back()->with('success', 'QR Code berhasil dinonaktifkan!');
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menonaktifkan QR Code: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal menonaktifkan QR Code: ' . $e->getMessage());
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
            Log::error('Error loading QR Code dashboard', ['error' => $e->getMessage()]);
            
            return view('qr-codes.dashboard', [
                'stats' => ['active' => 0, 'total' => 0, 'total_scans' => 0, 'attendance_rate' => 0],
                'activeQrCodes' => collect(),
                'recentQrCodes' => collect(),
                'upcomingQrCodes' => collect(),
                'classDistribution' => collect(),
                'qrActivityChart' => ['labels' => [], 'created' => [], 'used' => []],
                'error' => 'Gagal memuat dashboard: ' . $e->getMessage()
            ]);
        }
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