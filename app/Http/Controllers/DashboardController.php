<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\QRCode;
use App\Models\Submission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Student Dashboard dengan data lengkap
public function studentDashboard()
{
    $user = Auth::user();
    
    // Data dasar
    $classes = $user->classesAsStudent;
    $classIds = $classes->pluck('id');
    
    // Tugas yang belum dikumpulkan
    $pendingAssignments = Assignment::whereIn('class_id', $classIds)
        ->whereDoesntHave('submissions', function($query) use ($user) {
            $query->where('student_id', $user->id);
        })
        ->where('due_date', '>', now())
        ->orderBy('due_date', 'asc')
        ->get();
    
    // Tugas mendesak (≤ 3 hari)
    $urgentAssignments = Assignment::whereIn('class_id', $classIds)
        ->whereDoesntHave('submissions', function($query) use ($user) {
            $query->where('student_id', $user->id);
        })
        ->where('due_date', '>', now())
        ->where('due_date', '<=', now()->addDays(3))
        ->count();
    
    // Pengumpulan terakhir
    $recentSubmissions = Submission::where('student_id', $user->id)
        ->with('assignment')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    // =================== STATISTIK NILAI LENGKAP ===================
    
    // Semua submission yang sudah dinilai
    $gradedSubmissions = Submission::where('student_id', $user->id)
        ->whereNotNull('score')
        ->get();
    
    // Jumlah tugas yang sudah dinilai
    $gradedCount = $gradedSubmissions->count();
    
    // Rata-rata nilai
    $averageScore = $gradedCount > 0 ? round($gradedSubmissions->avg('score'), 1) : 0;
    
    // Nilai tertinggi dan terendah
    $highestScore = $gradedCount > 0 ? $gradedSubmissions->max('score') : 0;
    $lowestScore = $gradedCount > 0 ? $gradedSubmissions->min('score') : 0;
    
    // Distribusi nilai berdasarkan grade
    $scoreDistribution = [
        'A' => [
            'count' => 0, 
            'range' => '90-100', 
            'color' => 'success',
            'icon' => 'bi-trophy'
        ],
        'B' => [
            'count' => 0, 
            'range' => '80-89', 
            'color' => 'primary',
            'icon' => 'bi-star-fill'
        ],
        'C' => [
            'count' => 0, 
            'range' => '70-79', 
            'color' => 'info',
            'icon' => 'bi-check-circle'
        ],
        'D' => [
            'count' => 0, 
            'range' => '60-69', 
            'color' => 'warning',
            'icon' => 'bi-exclamation-circle'
        ],
        'E' => [
            'count' => 0, 
            'range' => '0-59', 
            'color' => 'danger',
            'icon' => 'bi-exclamation-triangle'
        ],
    ];
    
    foreach ($gradedSubmissions as $submission) {
        $score = $submission->score;
        if ($score >= 90) {
            $scoreDistribution['A']['count']++;
        } elseif ($score >= 80) {
            $scoreDistribution['B']['count']++;
        } elseif ($score >= 70) {
            $scoreDistribution['C']['count']++;
        } elseif ($score >= 60) {
            $scoreDistribution['D']['count']++;
        } else {
            $scoreDistribution['E']['count']++;
        }
    }
    
    // Persentase untuk progress bar distribution
    foreach ($scoreDistribution as $grade => $data) {
        $percentage = $gradedCount > 0 ? round(($data['count'] / $gradedCount) * 100) : 0;
        $scoreDistribution[$grade]['percentage'] = $percentage;
    }
    
    // Grade berdasarkan rata-rata nilai
    $grade = $this->calculateGrade($averageScore);
    
    // Data untuk chart perkembangan nilai
    $scoreChartData = $this->getScoreChartData($user);
    
    // =================== END STATISTIK NILAI ===================
    
    // Absensi hari ini
    $todayAttendance = Attendance::where('student_id', $user->id)
        ->whereDate('attendance_date', today())
        ->first();
    
    // Statistik absensi 30 hari terakhir
    $last30Days = now()->subDays(30);
    $recentAttendances = Attendance::where('student_id', $user->id)
        ->where('attendance_date', '>=', $last30Days)
        ->get();
    
    $attendanceStats = [
        'present' => $recentAttendances->where('status', 'present')->count(),
        'late' => $recentAttendances->where('status', 'late')->count(),
        'absent' => $recentAttendances->where('status', 'absent')->count(),
        'sick' => $recentAttendances->where('status', 'sick')->count(),
        'permission' => $recentAttendances->where('status', 'permission')->count(),
    ];
    
    $totalAttendances = array_sum($attendanceStats);
    $attendancePercentage = $totalAttendances > 0 
        ? round((($attendanceStats['present'] + $attendanceStats['late']) / $totalAttendances) * 100)
        : 0;
    
    return view('dashboard.student', compact(
        'classes',
        'pendingAssignments',
        'urgentAssignments',
        'recentSubmissions',
        // TAMBAHKAN VARIABEL BERIKUT:
        'gradedSubmissions', // Variabel ini yang hilang
        'gradedCount',
        'averageScore',
        'highestScore',
        'lowestScore',
        'scoreDistribution',
        'grade',
        'scoreChartData',
        'todayAttendance',
        'attendanceStats',
        'attendancePercentage'
    ));
}

    // Helper method untuk menghitung grade
    private function calculateGrade($score)
    {
        if ($score >= 90) {
            return [
                'letter' => 'A',
                'color' => 'success',
                'icon' => 'bi-trophy',
                'message' => 'Luar biasa! Pertahankan performa Anda.'
            ];
        } elseif ($score >= 80) {
            return [
                'letter' => 'B',
                'color' => 'primary',
                'icon' => 'bi-star-fill',
                'message' => 'Bagus! Hampir mencapai nilai sempurna.'
            ];
        } elseif ($score >= 70) {
            return [
                'letter' => 'C',
                'color' => 'info',
                'icon' => 'bi-check-circle',
                'message' => 'Cukup baik. Masih bisa ditingkatkan.'
            ];
        } elseif ($score >= 60) {
            return [
                'letter' => 'D',
                'color' => 'warning',
                'icon' => 'bi-exclamation-circle',
                'message' => 'Perlu perbaikan. Belajar lebih giat.'
            ];
        } else {
            return [
                'letter' => 'E',
                'color' => 'danger',
                'icon' => 'bi-exclamation-triangle',
                'message' => 'Perlu perhatian khusus. Segera konsultasi dengan guru.'
            ];
        }
    }

    // Helper method untuk data chart nilai
    private function getScoreChartData($user)
    {
        $data = [];
        $labels = [];
        
        // Ambil 6 submission terbaru yang sudah dinilai
        $recentGradedSubmissions = Submission::where('student_id', $user->id)
            ->whereNotNull('score')
            ->with('assignment')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get()
            ->reverse(); // Reverse untuk urutan dari terlama ke terbaru
        
        foreach ($recentGradedSubmissions as $submission) {
            $labels[] = $submission->assignment ? 
                Str::limit($submission->assignment->title, 15) : 
                'Tugas ' . $submission->assignment_id;
            $data[] = $submission->score;
        }
        
        return [
            'labels' => $labels,
            'scores' => $data
        ];
    }

    // Teacher Dashboard dengan data lengkap
public function teacherDashboard()
{
    $user = Auth::user();
    
    // PERBAIKAN: Pastikan method teacherClasses() ada di model User
    $classes = $user->teacherClasses()->withCount('students')->get();
    
    // Total siswa di semua kelas
    $totalStudents = $classes->sum('students_count');
    
    // Tugas aktif - PERBAIKAN: Cek apakah kolom teacher_id ada di tabel assignments
    // Jika tidak, gunakan relasi melalui class
    $activeAssignments = Assignment::whereHas('class', function($query) use ($user) {
        $query->where('teacher_id', $user->id);
    })
    ->where('due_date', '>', now())
    ->count();
    
    // Tugas terbaru - PERBAIKAN: Gunakan whereHas untuk filter melalui class
    $recentAssignments = Assignment::whereHas('class', function($query) use ($user) {
        $query->where('teacher_id', $user->id);
    })
    ->with(['class', 'submissions'])
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();
    
    // Pengumpulan belum dinilai - PERBAIKAN: Optimasi query
    $teacherAssignmentIds = Assignment::whereHas('class', function($query) use ($user) {
        $query->where('teacher_id', $user->id);
    })->pluck('id');
    
    $pendingSubmissions = Submission::whereIn('assignment_id', $teacherAssignmentIds)
        ->whereNull('score')
        ->count();
    
    // Pengumpulan terbaru
    $recentSubmissions = Submission::whereIn('assignment_id', $teacherAssignmentIds)
        ->with(['student', 'assignment'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    // QR Code aktif hari ini - PERBAIKAN: Gunakan relasi melalui class
    $activeQrCodes = QRCode::whereHas('class', function($query) use ($user) {
        $query->where('teacher_id', $user->id);
    })
    ->whereDate('date', today())
    ->where('is_active', true)
    ->where('end_time', '>', now()->format('H:i:s'))
    ->count();
    
    // Kelas dengan QR Code hari ini - PERBAIKAN: Query yang lebih baik
    $todayClasses = $user->teacherClasses()
        ->with(['qrCodes' => function($query) {
            $query->whereDate('date', today())
                  ->where('is_active', true)
                  ->orderBy('start_time', 'asc');
        }, 'students'])
        ->whereHas('qrCodes', function($query) {
            $query->whereDate('date', today())
                  ->where('is_active', true);
        })
        ->get()
        ->map(function($class) {
            // Ambil QR Code aktif pertama untuk setiap kelas
            $class->active_qr_code = $class->qrCodes->first();
            return $class;
        });
    
    // Statistik absensi 7 hari terakhir untuk chart
    $attendanceChartData = $this->getAttendanceChartData($user);
    
    // Statistik nilai per kelas - PERBAIKAN: Optimasi query
    $classStats = collect();
    $allClassIds = $classes->pluck('id');
    
    if ($allClassIds->count() > 0) {
        // Query untuk statistik kelas dalam sekali query
        $classStatistics = DB::table('assignments')
            ->select(
                'assignments.class_id',
                DB::raw('COUNT(DISTINCT assignments.id) as total_assignments'),
                DB::raw('COUNT(DISTINCT submissions.id) as total_submissions'),
                DB::raw('COUNT(DISTINCT CASE WHEN submissions.score IS NOT NULL THEN submissions.id END) as graded_submissions'),
                DB::raw('AVG(submissions.score) as average_score')
            )
            ->leftJoin('submissions', 'assignments.id', '=', 'submissions.assignment_id')
            ->whereIn('assignments.class_id', $allClassIds)
            ->groupBy('assignments.class_id')
            ->get()
            ->keyBy('class_id');
        
        foreach ($classes as $class) {
            $stats = $classStatistics->get($class->id);
            
            $classStats->push([
                'class' => $class,
                'average_score' => $stats ? round($stats->average_score, 1) : 0,
                'submission_percentage' => $stats && $stats->total_assignments > 0 
                    ? round(($stats->total_submissions / $stats->total_assignments) * 100) 
                    : 0,
                'student_count' => $class->students_count,
                'graded_count' => $stats ? $stats->graded_submissions : 0,
                'total_assignments' => $stats ? $stats->total_assignments : 0,
                'total_submissions' => $stats ? $stats->total_submissions : 0
            ]);
        }
    }
    
    // Data untuk chart - PERBAIKAN: Format sesuai kebutuhan view
    $attendanceStats = [
        'labels' => $attendanceChartData['dates'] ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        'present' => $attendanceChartData['present'] ?? [12, 19, 15, 17, 14, 16, 18],
        'late' => $attendanceChartData['late'] ?? [2, 3, 1, 4, 2, 3, 1],
        'absent' => $attendanceChartData['absent'] ?? [1, 0, 2, 1, 0, 0, 1]
    ];
    
    return view('dashboard.teacher', compact(
        'classes',
        'totalStudents',
        'activeAssignments',
        'recentAssignments',
        'pendingSubmissions',
        'recentSubmissions',
        'activeQrCodes',
        'todayClasses',
        'attendanceStats', // Diubah dari attendanceChartData ke attendanceStats
        'classStats'
    ));
}

// Helper method untuk data chart absensi - PERBAIKAN
private function getAttendanceChartData($user)
{
    $data = [
        'dates' => [],
        'present' => [],
        'late' => [],
        'absent' => []
    ];
    
    // Generate data untuk 7 hari terakhir
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $dateString = $date->format('Y-m-d');
        
        // Format label untuk chart (singkatan hari dalam bahasa Indonesia)
        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $shortDayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $dayIndex = $date->dayOfWeek;
        
        $data['dates'][] = $shortDayNames[$dayIndex];
        
        // Query untuk menghitung absensi per hari
        $attendanceCounts = Attendance::whereHas('class', function($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
        ->whereDate('attendance_date', $dateString)
        ->selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();
        
        $data['present'][] = $attendanceCounts['present'] ?? 0;
        $data['late'][] = $attendanceCounts['late'] ?? 0;
        $data['absent'][] = $attendanceCounts['absent'] ?? 0;
    }
    
    return $data;
}
    
    // Helper method untuk data chart nilai siswa di kelas (untuk teacher)
    private function getClassScoreChartData($classId)
    {
        $submissions = Submission::whereHas('assignment', function($query) use ($classId) {
            $query->where('class_id', $classId);
        })->whereNotNull('score')
            ->with('student')
            ->get();
        
        $students = [];
        $scores = [];
        
        // Group by student
        $studentScores = [];
        foreach ($submissions as $submission) {
            $studentId = $submission->student_id;
            $studentName = $submission->student->name;
            
            if (!isset($studentScores[$studentId])) {
                $studentScores[$studentId] = [
                    'name' => $studentName,
                    'total' => 0,
                    'count' => 0
                ];
            }
            
            $studentScores[$studentId]['total'] += $submission->score;
            $studentScores[$studentId]['count']++;
        }
        
        // Calculate average per student
        foreach ($studentScores as $studentId => $data) {
            if ($data['count'] > 0) {
                $students[] = Str::limit($data['name'], 10);
                $scores[] = round($data['total'] / $data['count'], 1);
            }
        }
        
        return [
            'students' => $students,
            'scores' => $scores
        ];
    }
}