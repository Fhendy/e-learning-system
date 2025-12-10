<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassModel;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Submission;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get student classes
        $classes = $user->studentClasses()->with('teacher')->get();
        
        // Get pending assignments
        $pendingAssignments = Assignment::whereHas('class', function($query) use ($user) {
            $query->whereHas('students', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })
        ->where('status', 'active')
        ->where('due_date', '>', now())
        ->with('class')
        ->orderBy('due_date', 'asc')
        ->get();
        
        // Count urgent assignments (due in 3 days or less)
        $urgentAssignments = $pendingAssignments->filter(function($assignment) {
            return now()->diffInDays($assignment->due_date, false) <= 3;
        })->count();
        
        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();
        
        // Get attendance stats for last 30 days
        $last30Days = Attendance::where('user_id', $user->id)
            ->whereDate('date', '>=', now()->subDays(30))
            ->get();
            
        $total = $last30Days->count();
        $present = $last30Days->where('status', 'present')->count();
        $late = $last30Days->where('status', 'late')->count();
        $absent = $last30Days->where('status', 'absent')->count();
        
        $attendancePercentage = $total > 0 ? round(($present / $total) * 100) : 0;
        $attendanceStats = [
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'percentage' => $attendancePercentage
        ];
        
        // Get recent attendance (last 5)
        $recentAttendance = Attendance::where('user_id', $user->id)
            ->with('class')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();
        
        // Calculate scores
        $submissions = $user->submissions()->whereNotNull('score')->get();
        $averageScore = $submissions->avg('score') ?? 0;
        $gradedCount = $submissions->count();
        $highestScore = $submissions->max('score') ?? 0;
        $lowestScore = $submissions->min('score') ?? 0;
        
        // Calculate grade
        $grade = $this->calculateGrade($averageScore);
        
        // Score distribution
        $scoreDistribution = $this->calculateScoreDistribution($submissions);
        
        // Recent submissions
        $recentSubmissions = $user->submissions()
            ->with('assignment')
            ->orderBy('submitted_at', 'desc')
            ->take(5)
            ->get();
        
        return view('dashboard.student', compact(
            'classes',
            'pendingAssignments',
            'urgentAssignments',
            'todayAttendance',
            'attendanceStats',
            'attendancePercentage',
            'recentAttendance',
            'averageScore',
            'gradedCount',
            'highestScore',
            'lowestScore',
            'grade',
            'scoreDistribution',
            'recentSubmissions'
        ));
    }
    
    private function calculateGrade($score)
    {
        if ($score >= 90) return ['letter' => 'A', 'color' => 'success', 'message' => 'Sangat Baik'];
        if ($score >= 80) return ['letter' => 'B', 'color' => 'info', 'message' => 'Baik'];
        if ($score >= 70) return ['letter' => 'C', 'color' => 'warning', 'message' => 'Cukup'];
        if ($score >= 60) return ['letter' => 'D', 'color' => 'danger', 'message' => 'Kurang'];
        return ['letter' => 'E', 'color' => 'secondary', 'message' => 'Sangat Kurang'];
    }
    
    private function calculateScoreDistribution($submissions)
    {
        $distribution = [
            'A (90-100)' => ['count' => 0, 'percentage' => 0, 'color' => 'success'],
            'B (80-89)' => ['count' => 0, 'percentage' => 0, 'color' => 'info'],
            'C (70-79)' => ['count' => 0, 'percentage' => 0, 'color' => 'warning'],
            'D (60-69)' => ['count' => 0, 'percentage' => 0, 'color' => 'danger'],
            'E (<60)' => ['count' => 0, 'percentage' => 0, 'color' => 'secondary']
        ];
        
        foreach ($submissions as $submission) {
            $score = $submission->score;
            if ($score >= 90) $distribution['A (90-100)']['count']++;
            elseif ($score >= 80) $distribution['B (80-89)']['count']++;
            elseif ($score >= 70) $distribution['C (70-79)']['count']++;
            elseif ($score >= 60) $distribution['D (60-69)']['count']++;
            else $distribution['E (<60)']['count']++;
        }
        
        $total = $submissions->count();
        if ($total > 0) {
            foreach ($distribution as $key => $data) {
                $distribution[$key]['percentage'] = round(($data['count'] / $total) * 100);
            }
        }
        
        return $distribution;
    }
}