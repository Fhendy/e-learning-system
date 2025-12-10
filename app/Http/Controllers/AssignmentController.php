<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\ClassModel;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    // List assignments for teacher dengan filter dan stats
    public function indexTeacher(Request $request)
    {
        $user = Auth::user();
        
        // Get all classes taught by the teacher
        $classes = ClassModel::where('teacher_id', $user->id)->get();
        
        // Base query
        $query = Assignment::where('teacher_id', $user->id)
            ->with(['class', 'submissions']);
        
        // Filter berdasarkan status
        if ($request->has('status')) {
            if ($request->status == 'active') {
                $query->where('due_date', '>', now());
            } elseif ($request->status == 'past') {
                $query->where('due_date', '<=', now());
            }
        }
        
        // Filter berdasarkan kelas
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $assignments = $query->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Calculate statistics
        $totalAssignments = Assignment::where('teacher_id', $user->id)->count();
        $activeCount = Assignment::where('teacher_id', $user->id)
            ->where('due_date', '>', now())
            ->count();
        
        // Hitung pengumpulan yang belum dinilai
        $pendingCount = Submission::whereIn('assignment_id', 
            Assignment::where('teacher_id', $user->id)->pluck('id')
        )->whereNull('score')
            ->count();
        
        // Hitung rata-rata persentase pengumpulan
        $totalWithSubmissions = Assignment::where('teacher_id', $user->id)
            ->has('submissions')
            ->count();
        $averageSubmission = $totalAssignments > 0 
            ? round(($totalWithSubmissions / $totalAssignments) * 100) 
            : 0;
        
        return view('assignments.teacher-index', compact(
            'assignments', 
            'classes',
            'totalAssignments',
            'activeCount',
            'pendingCount',
            'averageSubmission'
        ));
    }

    // List assignments for student dengan filter dan stats
    public function indexStudent(Request $request)
    {
        $user = Auth::user();
        
        // Get all classes where user is enrolled
        $classes = $user->classesAsStudent;
        $classIds = $classes->pluck('id');
        
        // Base query
        $query = Assignment::whereIn('class_id', $classIds)
            ->with(['class', 'submissions' => function($query) use ($user) {
                $query->where('student_id', $user->id);
            }]);
        
        // Filter berdasarkan status
        if ($request->has('status')) {
            if ($request->status == 'pending') {
                $query->whereDoesntHave('submissions', function($q) use ($user) {
                    $q->where('student_id', $user->id);
                });
            } elseif ($request->status == 'submitted') {
                $query->whereHas('submissions', function($q) use ($user) {
                    $q->where('student_id', $user->id);
                });
            } elseif ($request->status == 'graded') {
                $query->whereHas('submissions', function($q) use ($user) {
                    $q->where('student_id', $user->id)
                      ->whereNotNull('score');
                });
            } elseif ($request->status == 'late') {
                $query->whereHas('submissions', function($q) use ($user) {
                    $q->where('student_id', $user->id)
                      ->where('status', 'late');
                });
            }
        }
        
        // Filter lainnya
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('start_date')) {
            $query->whereDate('due_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('due_date', '<=', $request->end_date);
        }
        
        // Order by due date
        $assignments = $query->orderBy('due_date', 'asc')
            ->paginate(12);
        
        // Calculate statistics
        $totalAssignments = Assignment::whereIn('class_id', $classIds)->count();
        $pendingCount = Assignment::whereIn('class_id', $classIds)
            ->whereDoesntHave('submissions', function($query) use ($user) {
                $query->where('student_id', $user->id);
            })
            ->where('due_date', '>', now())
            ->count();
        
        $completedCount = Assignment::whereIn('class_id', $classIds)
            ->whereHas('submissions', function($query) use ($user) {
                $query->where('student_id', $user->id);
            })
            ->count();
        
        $lateCount = Assignment::whereIn('class_id', $classIds)
            ->whereHas('submissions', function($query) use ($user) {
                $query->where('student_id', $user->id)
                      ->where('status', 'late');
            })
            ->count();
        
        return view('assignments.student-index', compact(
            'assignments',
            'classes',
            'totalAssignments',
            'pendingCount',
            'completedCount',
            'lateCount'
        ));
    }

    // Show create assignment form
    public function create()
    {
        $user = Auth::user();
        $classes = ClassModel::where('teacher_id', $user->id)->get();
        return view('assignments.create', compact('classes'));
    }

    // Store new assignment
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'due_date' => 'required|date|after:now',
            'max_score' => 'required|integer|min:1|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,png|max:2048',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('assignments', 'public');
            $validated['attachment'] = $path;
        }

        $validated['teacher_id'] = Auth::id();

        Assignment::create($validated);

        return redirect()->route('assignments.teacher.index')
            ->with('success', 'Tugas berhasil dibuat.');
    }

    // Show assignment details
    public function show(Assignment $assignment)
    {
        $user = Auth::user();
        
        // Parse due_date dengan aman
        $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
            ? $assignment->due_date 
            : \Carbon\Carbon::parse($assignment->due_date);
        
        if ($user->isTeacher() || $user->isAdmin()) {
            // Teacher view - show all submissions
            $submissions = $assignment->submissions()
                ->with('student')
                ->orderBy('submitted_at', 'desc')
                ->get();
            
            return view('assignments.show-teacher', compact('assignment', 'submissions', 'dueDate'));
        } else {
            // Student view - show only their submission
            $submission = $assignment->submissionByStudent($user->id);
            
            // Parse submission date jika ada
            if ($submission && $submission->submitted_at) {
                $submittedAt = $submission->submitted_at instanceof \Carbon\Carbon 
                    ? $submission->submitted_at 
                    : \Carbon\Carbon::parse($submission->submitted_at);
                return view('assignments.show-student', compact('assignment', 'submission', 'dueDate', 'submittedAt'));
            }
            
            return view('assignments.show-student', compact('assignment', 'submission', 'dueDate'));
        }
    }

    // Edit assignment
    public function edit(Assignment $assignment)
    {
        $user = Auth::user();
        
        // Check if teacher owns this assignment
        if ($assignment->teacher_id != $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $classes = ClassModel::where('teacher_id', $user->id)->get();
        
        return view('assignments.edit', compact('assignment', 'classes'));
    }

    // Update assignment
    public function update(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        
        // Check if teacher owns this assignment
        if ($assignment->teacher_id != $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'due_date' => 'required|date|after_or_equal:now',
            'max_score' => 'required|integer|min:1|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,png|max:2048',
            'remove_attachment' => 'nullable|boolean',
        ]);

        // Handle attachment removal
        if ($request->has('remove_attachment') && $request->remove_attachment == 1) {
            if ($assignment->attachment) {
                Storage::disk('public')->delete($assignment->attachment);
                $validated['attachment'] = null;
            }
        }
        
        // Handle new attachment upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($assignment->attachment) {
                Storage::disk('public')->delete($assignment->attachment);
            }
            
            $path = $request->file('attachment')->store('assignments', 'public');
            $validated['attachment'] = $path;
        } elseif (!isset($validated['attachment'])) {
            // Keep existing attachment if not removing and not uploading new one
            $validated['attachment'] = $assignment->attachment;
        }

        $assignment->update($validated);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    // Delete assignment
    public function destroy(Assignment $assignment)
    {
        $user = Auth::user();
        
        // Check if teacher owns this assignment OR user is admin
        if ($assignment->teacher_id != $user->id && !$user->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus tugas ini.');
        }
        
        try {
            // Delete all submissions and their attachments
            foreach ($assignment->submissions as $submission) {
                if ($submission->attachment) {
                    Storage::disk('public')->delete($submission->attachment);
                }
                $submission->delete();
            }
            
            // Delete assignment attachment
            if ($assignment->attachment) {
                Storage::disk('public')->delete($assignment->attachment);
            }
            
            // Delete the assignment
            $assignmentTitle = $assignment->title;
            $assignment->delete();
            
            return redirect()->route('assignments.teacher.index')
                ->with('success', 'Tugas "' . $assignmentTitle . '" berhasil dihapus.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus tugas: ' . $e->getMessage());
        }
    }
}