<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/students/search', function (Request $request) {
        $query = $request->get('q');
        
        $students = User::where('role', 'student')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('nis_nip', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'email', 'nis_nip']);
        
        return response()->json($students);
    });
    
    Route::get('/classes/stats', function () {
        $teacherId = auth()->id();
        
        $classes = ClassModel::where('teacher_id', $teacherId)->get();
        
        $stats = [
            'total_classes' => $classes->count(),
            'total_students' => $classes->sum(function($class) {
                return $class->students->count();
            }),
            'active_classes' => $classes->where('is_active', true)->count(),
            'inactive_classes' => $classes->where('is_active', false)->count(),
            'avg_students_per_class' => $classes->count() > 0 ? 
                $classes->sum(function($class) {
                    return $class->students->count();
                }) / $classes->count() : 0,
            'class_names' => $classes->pluck('class_name'),
            'student_counts' => $classes->map(function($class) {
                return $class->students->count();
            })
        ];
        
        return response()->json($stats);
    });
    // routes/api.php atau routes/web.php
Route::get('/api/classes/{class}/students', function($classId) {
    $class = \App\Models\ClassModel::with('students')->findOrFail($classId);
    
    return response()->json(
        $class->students->map(function($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'nis_nip' => $student->nis_nip
            ];
        })
    );
})->middleware('auth');
});