<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::students()->with('classesAsStudent');
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nis_nip', 'like', "%{$search}%");
            });
        }
        
        // Filter by class
        if ($request->has('class_id') && $request->class_id != 'all') {
            $query->whereHas('classesAsStudent', function($q) use ($request) {
                $q->where('classes.id', $request->class_id);
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $status = $request->status == 'active' ? true : false;
            $query->where('is_active', $status);
        }
        
        $students = $query->latest()->paginate(20);
        $classes = ClassModel::active()->get();
        
        return view('students.index', compact('students', 'classes'));
    }
    
    public function create()
    {
        $classes = ClassModel::active()->get();
        return view('students.create', compact('classes'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'nis_nip' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:classes,id'],
            // HAPUS validasi profile_photo
            'is_active' => ['boolean'],
        ]);

        // HAPUS handle profile photo

        $student = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nis_nip' => $request->nis_nip,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'is_active' => $request->is_active ?? true,
        ]);

        // Attach to classes
        if ($request->has('classes')) {
            $student->classesAsStudent()->attach($request->classes);
        }

        return redirect()->route('students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }
        public function import()
    {
        $classes = ClassModel::active()->get();
        return view('students.import', compact('classes'));
    }
    
    public function processImport(Request $request)
    {
        // Logic untuk import Excel
        return redirect()->route('students.index')
            ->with('success', 'Data siswa berhasil diimport.');
    }
    public function show(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $student->load(['classesAsStudent.teacher', 'submissions.assignment']);
        return view('students.show', compact('student'));
    }
    
    public function edit(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $classes = ClassModel::active()->get();
        $studentClasses = $student->classesAsStudent->pluck('id')->toArray();
        
        return view('students.edit', compact('student', 'classes', 'studentClasses'));
    }
    
    public function update(Request $request, User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $student->id],
            'nis_nip' => ['required', 'string', 'max:20', 'unique:users,nis_nip,' . $student->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:classes,id'],
            // HAPUS validasi profile_photo
            'is_active' => ['boolean'],
        ]);

        // HAPUS handle profile photo

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'nis_nip' => $request->nis_nip,
            'is_active' => $request->is_active ?? $student->is_active,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $student->update($updateData);

        // Sync classes
        if ($request->has('classes')) {
            $student->classesAsStudent()->sync($request->classes);
        } else {
            $student->classesAsStudent()->detach();
        }

        return redirect()->route('students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }
    
    public function destroy(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        // HAPUS delete profile photo
        
        // Detach from all classes first
        $student->classesAsStudent()->detach();
        
        // Delete the student
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }
    
}