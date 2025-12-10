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
            
            return redirect()->route('classes.show', $class->id)
                ->with('success', 'Kelas berhasil dibuat! Kode Kelas: ' . $class->class_code);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating class', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'request' => $request->all()
            ]);
            
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
            
            return redirect()->route('classes.show', $class)
                ->with('success', 'Kelas berhasil diperbarui!');
            
        } catch (\Exception $e) {
            Log::error('Error updating class', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
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
            
            return redirect()->route('classes.index')
                ->with('success', 'Kelas berhasil dihapus.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting class', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
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
                return back()->with('error', 'Siswa sudah terdaftar di kelas ini.');
            }

            // Check if user is a student
            if ($student->role !== 'student') {
                return back()->with('error', 'User bukan siswa.');
            }

            // Attach student to class
            $class->students()->attach($student->id);

            Log::info('Student added to class', [
                'class_id' => $class->id,
                'student_id' => $student->id,
                'added_by' => $user->id
            ]);

            return back()->with('success', 'Siswa berhasil ditambahkan ke kelas.');

        } catch (\Exception $e) {
            Log::error('Error adding student to class', [
                'error' => $e->getMessage(),
                'class_id' => $id,
                'user_id' => $user->id
            ]);
            
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
                return back()->with('error', 'Siswa tidak terdaftar di kelas ini.');
            }
            
            // Remove student from class
            $class->students()->detach($student->id);
            
            Log::info('Student removed from class', [
                'class_id' => $class->id,
                'student_id' => $student->id,
                'removed_by' => $user->id
            ]);
            
            return back()->with('success', 'Siswa berhasil dikeluarkan dari kelas.');
            
        } catch (\Exception $e) {
            Log::error('Error removing student from class', [
                'error' => $e->getMessage(),
                'class_id' => $classId,
                'student_id' => $studentId,
                'user_id' => $user->id
            ]);
            
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
}