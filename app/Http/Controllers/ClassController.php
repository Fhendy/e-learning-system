<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Assignment;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index()
    {
        $user = Auth::user();
        
        try {
            if (in_array($user->role, ['teacher', 'guru', 'admin'])) {
                $query = ClassModel::withCount('students')
                    ->with('teacher');
                
                if ($user->role !== 'admin') {
                    $query->where('teacher_id', $user->id);
                }
                
                $classes = $query->orderBy('created_at', 'desc')->paginate(10);
            } else {
                // For students, show only enrolled classes
                $classes = $user->classes()
                    ->withCount('students')
                    ->with('teacher')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            }
            
            return view('classes.index', compact('classes'));
            
        } catch (\Exception $e) {
            Log::error('Error in ClassController@index: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['teacher', 'guru', 'admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk membuat kelas.');
        }
        
        // Get all teachers for admin to assign
        $teachers = null;
        if ($user->role === 'admin') {
            $teachers = User::whereIn('role', ['teacher', 'guru'])->get();
        }
        
        return view('classes.create', compact('teachers'));
    }

    /**
     * Store a newly created class.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['teacher', 'guru', 'admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk membuat kelas.');
        }
        
        // Validasi input
        $validator = Validator::make($request->all(), [
            'class_name' => 'required|string|max:255',
            'class_code' => 'required|string|max:50|unique:classes,class_code',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'semester' => 'nullable|in:ganjil,genap',
            'academic_year' => 'nullable|string|max:20',
            'school_year' => 'nullable|string|max:20',
        ]);
        
        // Additional validation for teacher_id if admin
        if ($user->role === 'admin') {
            $validator->addRules([
                'teacher_id' => 'required|exists:users,id'
            ]);
        }
        
        if ($validator->fails()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Prepare class data
            $classData = [
                'class_name' => $request->class_name,
                'class_code' => strtoupper($request->class_code),
                'subject' => $request->subject ?? '',
                'description' => $request->description ?? '',
                'teacher_id' => $user->id, // Default to current user
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Handle teacher_id for admin
            if ($user->role === 'admin' && $request->has('teacher_id')) {
                $classData['teacher_id'] = $request->teacher_id;
            }
            
            // Handle optional fields
            if ($request->filled('semester')) {
                $classData['semester'] = $request->semester;
            }
            if ($request->filled('academic_year')) {
                $classData['academic_year'] = $request->academic_year;
            }
            if ($request->filled('school_year')) {
                $classData['school_year'] = $request->school_year;
            }
            
            // Create the class
            $class = ClassModel::create($classData);
            
            DB::commit();
            
            Log::info('Class created successfully', [
                'class_id' => $class->id,
                'class_code' => $class->class_code,
                'teacher_id' => $class->teacher_id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kelas berhasil dibuat!',
                    'data' => $class
                ]);
            }
            
            return redirect()->route('classes.show', $class->id)
                ->with('success', 'Kelas berhasil dibuat! Kode Kelas: ' . $class->class_code);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating class', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'request' => $request->all()
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat kelas: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal membuat kelas: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified class.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        try {
            // Find class with relations
            $class = ClassModel::with(['teacher', 'students'])
                ->withCount('students')
                ->findOrFail($id);
            
            // Check access permissions
            if (!$this->hasAccessToClass($user, $class)) {
                abort(403, 'Anda tidak memiliki akses ke kelas ini.');
            }
            
            // Load students with pagination
            $students = $class->students()
                ->orderBy('name')
                ->paginate(15);
            
            // Get assignments for this class
            $assignments = collect();
            if (class_exists('App\Models\Assignment')) {
                $assignments = Assignment::where('class_id', $class->id)
                    ->withCount('submissions')
                    ->latest()
                    ->limit(5)
                    ->get();
            }
            
            // Get attendance statistics
            $attendanceStats = $this->getClassAttendanceStats($class);
            
            // Get recent QR codes
            $recentQrCodes = collect();
            if (class_exists('App\Models\QRCode')) {
                $recentQrCodes = QRCode::where('class_id', $class->id)
                    ->latest()
                    ->limit(5)
                    ->get();
            }
            
            return view('classes.show', compact(
                'class', 
                'students',
                'assignments', 
                'attendanceStats', 
                'recentQrCodes'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error showing class', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
            return redirect()->route('classes.index')
                ->with('error', 'Kelas tidak ditemukan atau terjadi kesalahan.');
        }
    }

    /**
     * Get class attendance statistics
     */
    private function getClassAttendanceStats(ClassModel $class)
    {
        try {
            if (!class_exists('App\Models\Attendance')) {
                return [
                    'total' => 0,
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'sick' => 0,
                    'permission' => 0,
                    'attendance_rate' => 0
                ];
            }
            
            $attendances = Attendance::where('class_id', $class->id)->get();
            
            $total = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            $late = $attendances->where('status', 'late')->count();
            
            return [
                'total' => $total,
                'present' => $present,
                'late' => $late,
                'absent' => $attendances->where('status', 'absent')->count(),
                'sick' => $attendances->where('status', 'sick')->count(),
                'permission' => $attendances->where('status', 'permission')->count(),
                'attendance_rate' => $total > 0 ? 
                    round((($present + $late) / $total) * 100, 1) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Error getting attendance stats', [
                'error' => $e->getMessage(),
                'class_id' => $class->id
            ]);
            
            return [
                'total' => 0,
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'sick' => 0,
                'permission' => 0,
                'attendance_rate' => 0
            ];
        }
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        try {
            $class = ClassModel::with('teacher')->findOrFail($id);
            
            // Check permissions
            if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
                abort(403, 'Anda tidak memiliki akses untuk mengedit kelas ini.');
            }
            
            // Get all teachers for admin
            $teachers = null;
            if ($user->role === 'admin') {
                $teachers = User::whereIn('role', ['teacher', 'guru'])->get();
            }
            
            // Check if is_active column exists
            $hasIsActive = Schema::hasColumn('classes', 'is_active');
            
            return view('classes.edit', compact('class', 'teachers', 'hasIsActive'));
            
        } catch (\Exception $e) {
            Log::error('Error editing class', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
            return redirect()->route('classes.index')
                ->with('error', 'Kelas tidak ditemukan.');
        }
    }

    /**
     * Update the specified class.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        try {
            $class = ClassModel::findOrFail($id);
            
            // Check permissions
            if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
                abort(403, 'Anda tidak memiliki akses untuk mengedit kelas ini.');
            }
            
            // Validasi
            $validator = Validator::make($request->all(), [
                'class_name' => 'required|string|max:255',
                'class_code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('classes')->ignore($class->id)
                ],
                'description' => 'nullable|string',
                'subject' => 'nullable|string|max:255',
                'semester' => 'nullable|in:ganjil,genap',
                'academic_year' => 'nullable|string|max:20',
                'school_year' => 'nullable|string|max:20',
            ]);
            
            // Additional validation for teacher_id if admin
            if ($user->role === 'admin') {
                $validator->addRules([
                    'teacher_id' => 'required|exists:users,id'
                ]);
            }
            
            if ($validator->fails()) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi gagal',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Prepare update data
            $updateData = [
                'class_name' => $request->class_name,
                'class_code' => strtoupper($request->class_code),
                'description' => $request->description ?? '',
                'subject' => $request->subject ?? '',
                'semester' => $request->semester,
                'updated_at' => now(),
            ];
            
            // Handle academic years
            if ($request->filled('academic_year')) {
                $updateData['academic_year'] = $request->academic_year;
            }
            if ($request->filled('school_year')) {
                $updateData['school_year'] = $request->school_year;
            }
            
            // Handle is_active
            if (Schema::hasColumn('classes', 'is_active')) {
                $updateData['is_active'] = $request->has('is_active') ? 1 : 0;
            }
            
            // Handle teacher_id for admin
            if ($user->role === 'admin') {
                $updateData['teacher_id'] = $request->teacher_id;
            }
            
            // Update the class
            $class->update($updateData);
            
            Log::info('Class updated successfully', [
                'class_id' => $class->id,
                'updated_by' => $user->id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kelas berhasil diperbarui!',
                    'data' => $class
                ]);
            }
            
            return redirect()->route('classes.show', $class)
                ->with('success', 'Kelas berhasil diperbarui!');
            
        } catch (\Exception $e) {
            Log::error('Error updating class', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified class.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        try {
            $class = ClassModel::findOrFail($id);
            
            // Check permissions
            if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
                abort(403, 'Anda tidak memiliki akses untuk menghapus kelas ini.');
            }
            
            DB::beginTransaction();
            
            // Detach all students
            $class->students()->detach();
            
            // Delete related data
            $class->attendances()->delete();
            
            // Delete assignments if exists
            if ($class->assignments()->exists()) {
                $class->assignments()->delete();
            }
            
            // Delete QR codes if exists
            if (class_exists('App\Models\QRCode') && $class->qrCodes()->exists()) {
                $class->qrCodes()->delete();
            }
            
            // Delete class
            $class->delete();
            
            DB::commit();
            
            Log::info('Class deleted successfully', [
                'class_id' => $id,
                'deleted_by' => $user->id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kelas berhasil dihapus.'
                ]);
            }
            
            return redirect()->route('classes.index')
                ->with('success', 'Kelas berhasil dihapus.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting class', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus kelas: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    /**
     * Add student to class
     */
    public function addStudent(Request $request, $id)
    {
        $user = Auth::user();
        
        try {
            $class = ClassModel::findOrFail($id);
            
            // Check permissions
            if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
                abort(403, 'Anda tidak memiliki akses untuk menambahkan siswa.');
            }
            
            $request->validate([
                'student_id' => ['required', 'exists:users,id']
            ]);

            $student = User::findOrFail($request->student_id);

            // Check if student is already in class
            if ($class->students()->where('users.id', $student->id)->exists()) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siswa sudah terdaftar di kelas ini.'
                    ], 422);
                }
                return back()->with('error', 'Siswa sudah terdaftar di kelas ini.');
            }

            // Check if user is a student
            if ($student->role !== 'student') {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User bukan siswa.'
                    ], 422);
                }
                return back()->with('error', 'User bukan siswa.');
            }

            // Attach student to class
            $class->students()->attach($student->id);

            Log::info('Student added to class', [
                'class_id' => $class->id,
                'student_id' => $student->id,
                'added_by' => $user->id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Siswa berhasil ditambahkan ke kelas.',
                    'data' => $student
                ]);
            }

            return back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');

        } catch (\Exception $e) {
            Log::error('Error adding student to class', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan siswa: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal menambahkan siswa: ' . $e->getMessage());
        }
    }

    /**
     * Remove student from class
     */
    public function removeStudent($classId, $studentId)
    {
        $user = Auth::user();
        
        try {
            $class = ClassModel::findOrFail($classId);
            $student = User::findOrFail($studentId);
            
            // Check permissions
            if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
                abort(403, 'Anda tidak memiliki akses untuk mengeluarkan siswa.');
            }
            
            // Check if student is in this class
            if (!$class->students()->where('users.id', $student->id)->exists()) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siswa tidak terdaftar di kelas ini.'
                    ], 422);
                }
                return back()->with('error', 'Siswa tidak terdaftar di kelas ini.');
            }
            
            // Remove student from class
            $class->students()->detach($student->id);
            
            Log::info('Student removed from class', [
                'class_id' => $class->id,
                'student_id' => $student->id,
                'removed_by' => $user->id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Siswa berhasil dikeluarkan dari kelas.'
                ]);
            }
            
            return back()->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
            
        } catch (\Exception $e) {
            Log::error('Error removing student from class', [
                'error' => $e->getMessage(),
                'class_id' => $classId,
                'student_id' => $studentId,
                'user_id' => $user->id
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengeluarkan siswa: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Gagal mengeluarkan siswa: ' . $e->getMessage());
        }
    }

    /**
     * Check if user has access to class
     */
    private function hasAccessToClass($user, $class)
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        if (in_array($user->role, ['teacher', 'guru']) && $class->teacher_id === $user->id) {
            return true;
        }
        
        if ($user->role === 'student' && $class->students()->where('users.id', $user->id)->exists()) {
            return true;
        }
        
        return false;
    }

    /**
     * Show class statistics dashboard
     */
    public function dashboard($id)
    {
        $user = Auth::user();
        
        try {
            $class = ClassModel::with(['teacher', 'students'])
                ->withCount('students')
                ->findOrFail($id);
            
            // Check access permissions
            if (!$this->hasAccessToClass($user, $class)) {
                abort(403, 'Anda tidak memiliki akses ke kelas ini.');
            }
            
            // Get statistics
            $stats = [
                'total_students' => $class->students_count,
                'total_assignments' => 0,
                'total_attendances' => 0,
                'attendance_rate' => 0
            ];
            
            // Get assignment count
            if (class_exists('App\Models\Assignment')) {
                $stats['total_assignments'] = Assignment::where('class_id', $class->id)->count();
            }
            
            // Get attendance data
            if (class_exists('App\Models\Attendance')) {
                $totalAttendances = Attendance::where('class_id', $class->id)->count();
                $presentAttendances = Attendance::where('class_id', $class->id)
                    ->whereIn('status', ['present', 'late'])
                    ->count();
                
                $stats['total_attendances'] = $totalAttendances;
                $stats['attendance_rate'] = $totalAttendances > 0 ? 
                    round(($presentAttendances / $totalAttendances) * 100, 1) : 0;
            }
            
            return view('classes.dashboard', compact('class', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error in class dashboard', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
            return redirect()->route('classes.show', $id)
                ->with('error', 'Gagal memuat dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Show import form for classes
     */
    public function import()
    {
        return view('classes.import');
    }

    /**
     * Download template Excel for classes
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $sheet->setCellValue('A1', 'Nama Kelas');
            $sheet->setCellValue('B1', 'Kode Kelas');
            $sheet->setCellValue('C1', 'Mata Pelajaran');
            $sheet->setCellValue('D1', 'Semester');
            $sheet->setCellValue('E1', 'Tahun Ajaran');
            $sheet->setCellValue('F1', 'Deskripsi');
            
            // Style headers
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(50);
            
            // Add example data
            $sheet->setCellValue('A2', 'Matematika X IPA 1');
            $sheet->setCellValue('B2', 'M-XIPA1');
            $sheet->setCellValue('C2', 'Matematika');
            $sheet->setCellValue('D2', 'ganjil');
            $sheet->setCellValue('E2', '2024/2025');
            $sheet->setCellValue('F2', 'Kelas Matematika untuk jurusan IPA');
            
            $sheet->setCellValue('A3', 'Fisika XI IPA 2');
            $sheet->setCellValue('B3', 'F-XIPA2');
            $sheet->setCellValue('C3', 'Fisika');
            $sheet->setCellValue('D3', 'genap');
            $sheet->setCellValue('E3', '2024/2025');
            $sheet->setCellValue('F3', 'Kelas Fisika tingkat lanjut');
            
            // Add instruction sheet
            $instructionSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Petunjuk');
            $spreadsheet->addSheet($instructionSheet, 0);
            $instructionSheet->setCellValue('A1', 'PANDUAN IMPORT DATA KELAS');
            $instructionSheet->setCellValue('A2', '================================');
            $instructionSheet->setCellValue('A4', 'Kolom yang wajib diisi:');
            $instructionSheet->setCellValue('A5', '1. Nama Kelas - Nama lengkap kelas');
            $instructionSheet->setCellValue('A6', '2. Kode Kelas - Kode unik kelas (wajib unik)');
            $instructionSheet->setCellValue('A8', 'Kolom opsional:');
            $instructionSheet->setCellValue('A9', '3. Mata Pelajaran - Mata pelajaran yang diajarkan');
            $instructionSheet->setCellValue('A10', '4. Semester - Ganjil / Genap');
            $instructionSheet->setCellValue('A11', '5. Tahun Ajaran - Format: YYYY/YYYY (contoh: 2024/2025)');
            $instructionSheet->setCellValue('A12', '6. Deskripsi - Deskripsi singkat tentang kelas');
            $instructionSheet->setCellValue('A14', 'Catatan:');
            $instructionSheet->setCellValue('A15', '- Jika Semester kosong, akan menggunakan default dari form');
            $instructionSheet->setCellValue('A16', '- Jika Tahun Ajaran kosong, akan menggunakan default dari form');
            $instructionSheet->setCellValue('A17', '- Kode kelas harus unik (tidak boleh sama dengan yang sudah ada)');
            
            $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $instructionSheet->getColumnDimension('A')->setWidth(60);
            
            // Set active sheet to data
            $spreadsheet->setActiveSheetIndex(1);
            
            // Create writer
            $writer = new Xlsx($spreadsheet);
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="template_kelas.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit();
            
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat template: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }

    /**
     * Activate the specified class.
     */
    public function activate(ClassModel $class)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengaktifkan kelas ini.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        $class->update(['is_active' => true]);
        
        // Check if request is AJAX
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil diaktifkan.',
                'data' => [
                    'class_id' => $class->id,
                    'class_name' => $class->class_name,
                    'is_active' => $class->is_active
                ]
            ]);
        }
        
        // Hapus redirect with success message untuk AJAX
        return redirect()->back();
    }

    /**
     * Deactivate the specified class.
     */
    public function deactivate(ClassModel $class)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($class->teacher_id !== $user->id && $user->role !== 'admin') {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menonaktifkan kelas ini.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        $class->update(['is_active' => false]);
        
        // Check if request is AJAX
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil dinonaktifkan.',
                'data' => [
                    'class_id' => $class->id,
                    'class_name' => $class->class_name,
                    'is_active' => $class->is_active
                ]
            ]);
        }
        
        // Hapus redirect with success message untuk AJAX
        return redirect()->back();
    }

    /**
     * Process import Excel file for classes
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'default_semester' => 'nullable|in:ganjil,genap',
            'default_academic_year' => 'nullable|string|max:20',
        ]);
        
        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row
            array_shift($rows);
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $defaultSemester = $request->default_semester;
            $defaultAcademicYear = $request->default_academic_year;
            
            foreach ($rows as $index => $row) {
                try {
                    $className = trim($row[0] ?? '');
                    $classCode = trim($row[1] ?? '');
                    $subject = trim($row[2] ?? '');
                    $semester = trim($row[3] ?? '');
                    $academicYear = trim($row[4] ?? '');
                    $description = trim($row[5] ?? '');
                    
                    // Skip empty rows
                    if (empty($className) && empty($classCode)) {
                        continue;
                    }
                    
                    // Validate required fields
                    if (empty($className)) {
                        $errors[] = "Baris " . ($index + 2) . ": Nama Kelas wajib diisi";
                        $errorCount++;
                        continue;
                    }
                    
                    if (empty($classCode)) {
                        $errors[] = "Baris " . ($index + 2) . ": Kode Kelas wajib diisi";
                        $errorCount++;
                        continue;
                    }
                    
                    // Use default values if empty
                    if (empty($semester) && $defaultSemester) {
                        $semester = $defaultSemester;
                    }
                    
                    if (empty($academicYear) && $defaultAcademicYear) {
                        $academicYear = $defaultAcademicYear;
                    }
                    
                    // Validate semester if provided
                    if (!empty($semester) && !in_array($semester, ['ganjil', 'genap'])) {
                        $errors[] = "Baris " . ($index + 2) . ": Semester harus 'ganjil' atau 'genap'";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check if class already exists
                    $existingClass = ClassModel::where('class_code', $classCode)->first();
                    
                    if ($existingClass && $request->has('skip_duplicates')) {
                        continue;
                    }
                    
                    if ($existingClass) {
                        $errors[] = "Baris " . ($index + 2) . ": Kode Kelas {$classCode} sudah ada";
                        $errorCount++;
                        continue;
                    }
                    
                    // Create class
                    ClassModel::create([
                        'class_name' => $className,
                        'class_code' => strtoupper($classCode),
                        'subject' => $subject,
                        'semester' => $semester,
                        'academic_year' => $academicYear,
                        'description' => $description,
                        'is_active' => $request->has('auto_activate'),
                        'teacher_id' => auth()->id(),
                    ]);
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                    $errorCount++;
                }
            }
            
            $message = "Import selesai! Berhasil: {$successCount} kelas, Gagal: {$errorCount} kelas.";
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => $successCount > 0,
                    'message' => $message,
                    'data' => [
                        'success_count' => $successCount,
                        'error_count' => $errorCount,
                        'errors' => $errors
                    ]
                ]);
            }
            
            if ($successCount > 0) {
                return redirect()->route('classes.index')->with('success', $message);
            } else {
                return redirect()->back()->with('error', $message)->with('import_errors', $errors);
            }
            
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal import file: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal import file: ' . $e->getMessage());
        }
    }
}