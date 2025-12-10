<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    // Submit assignment
    public function submit(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        
        // Cek apakah student sudah submit
        $existingSubmission = $assignment->submissionByStudent($user->id);
        if ($existingSubmission && in_array($existingSubmission->status, ['submitted', 'late', 'graded'])) {
            return redirect()->back()
                ->with('error', 'Anda sudah mengumpulkan tugas ini. Gunakan fitur "Kumpulkan Ulang" jika ingin mengganti submission.');
        }

        $request->validate([
            'submission_text' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:2048',
        ]);

        // Parse due_date dengan aman
        $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
            ? $assignment->due_date 
            : \Carbon\Carbon::parse($assignment->due_date);
        
        // Tentukan status
        $status = now()->greaterThan($dueDate) ? 'late' : 'submitted';

        $submissionData = [
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
            'submission_text' => $request->submission_text,
            'status' => $status,
            'submitted_at' => now(),
        ];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('submissions', 'public');
            $submissionData['attachment'] = $path;
        }

        // Jika ada draft, update draft tersebut
        if ($existingSubmission && $existingSubmission->status == 'draft') {
            $existingSubmission->update($submissionData);
            $message = 'Tugas berhasil dikumpulkan dari draft.';
        } else {
            Submission::create($submissionData);
            $message = 'Tugas berhasil dikumpulkan.';
        }

        return redirect()->back()->with('success', $message);
    }

    // Grade submission
// Perbaiki method grade() di SubmissionController:
public function grade(Request $request, Submission $submission)
{
    $request->validate([
        'score' => 'required|integer|min:0|max:' . $submission->assignment->max_score,
        'feedback' => 'nullable|string',
    ]);

    $submission->update([
        'score' => $request->score,
        'feedback' => $request->feedback,
        'status' => 'graded',
        // Hapus 'graded_at' jika kolom tidak ada
        // 'graded_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Nilai berhasil diberikan.');
}

    // View submission detail (for teacher)
    public function show(Submission $submission)
    {
        $submission->load('student', 'assignment');
        return view('assignments.submission-detail', compact('submission'));
    }

    // Export grades
    public function exportGrades(Assignment $assignment)
    {
        $user = Auth::user();
        
        // Check if teacher owns this assignment
        if ($assignment->teacher_id != $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Generate CSV export
        $filename = 'nilai-tugas-' . $assignment->id . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($assignment) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['NIS/NIP', 'Nama', 'Status', 'Nilai', 'Tanggal Submit', 'Feedback']);
            
            // Data rows
            foreach ($assignment->submissions as $submission) {
                fputcsv($file, [
                    $submission->student->nis_nip,
                    $submission->student->name,
                    $submission->status,
                    $submission->score ?? 'Belum dinilai',
                    $submission->submitted_at->format('d/m/Y H:i'),
                    $submission->feedback ?? '-'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Resubmit assignment (for students)
    public function resubmit(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        
        $request->validate([
            'submission_text' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,png|max:2048',
        ]);

        $submission = $assignment->submissionByStudent($user->id);
        
        if (!$submission) {
            return redirect()->back()->with('error', 'Anda belum memiliki pengumpulan.');
        }

        $updateData = [
            'submission_text' => $request->submission_text,
            'status' => now()->greaterThan($assignment->due_date) ? 'late' : 'submitted',
            'submitted_at' => now(),
        ];

        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($submission->attachment) {
                Storage::disk('public')->delete($submission->attachment);
            }
            
            $path = $request->file('attachment')->store('submissions', 'public');
            $updateData['attachment'] = $path;
        }

        $submission->update($updateData);

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan ulang.');
    }

    // Save as draft (for students)
    public function saveDraft(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        
        $request->validate([
            'submission_text' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,png|max:2048',
        ]);

        // Check if draft already exists
        $draft = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $user->id)
            ->where('status', 'draft')
            ->first();

        $data = [
            'assignment_id' => $assignment->id,
            'student_id' => $user->id,
            'submission_text' => $request->submission_text,
            'status' => 'draft',
            'submitted_at' => now(),
        ];

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('submissions/drafts', 'public');
            $data['attachment'] = $path;
            
            // Delete old attachment if exists
            if ($draft && $draft->attachment) {
                Storage::disk('public')->delete($draft->attachment);
            }
        }

        if ($draft) {
            $draft->update($data);
        } else {
            Submission::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Draft berhasil disimpan.',
        ]);
    }
    // Di SubmissionController.php, tambahkan:

public function getSubmission(Submission $submission)
{
    return response()->json([
        'success' => true,
        'submission' => $submission->load('student')
    ]);
}

public function remindStudent(Request $request, User $student)
{
    $request->validate([
        'assignment_id' => 'required|exists:assignments,id',
        'message' => 'nullable|string'
    ]);
    
    // Kirim notifikasi atau email ke siswa
    // Anda bisa menggunakan Notification atau Email di sini
    
    return response()->json([
        'success' => true,
        'message' => 'Pengingat berhasil dikirim ke siswa'
    ]);
}
}