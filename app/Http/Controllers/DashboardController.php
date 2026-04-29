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
    /**
     * Student Dashboard dengan data lengkap
     */
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
            ->with('assignment')
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
            'gradedSubmissions',
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

    /**
     * Helper method untuk menghitung grade
     */
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

    /**
     * Helper method untuk data chart nilai
     */
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
            ->reverse();
        
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

    /**
     * Teacher Dashboard dengan data lengkap
     */
    public function teacherDashboard()
    {
        $user = Auth::user();
        
        // Ambil semua kelas yang diajar oleh guru
        $classes = ClassModel::where('teacher_id', $user->id)
            ->withCount('students')
            ->get();
        
        // Total siswa
        $totalStudents = $classes->sum('students_count');
        
        // Tugas aktif
        $activeAssignments = Assignment::whereHas('class', function($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
        ->where('due_date', '>', now())
        ->count();
        
        // Tugas terbaru
        $recentAssignments = Assignment::whereHas('class', function($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
        ->with(['class', 'submissions'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
        
        // Pengumpulan belum dinilai
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
        
        // QR Code aktif hari ini
        $activeQrCodes = QRCode::whereHas('class', function($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
        ->whereDate('date', today())
        ->where('is_active', true)
        ->where('end_time', '>', now()->format('H:i:s'))
        ->count();
        
        // Kelas dengan QR Code hari ini
        $todayClasses = ClassModel::where('teacher_id', $user->id)
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
                $class->active_qr_code = $class->qrCodes->first();
                return $class;
            });
        
        // ==================== STATISTIK ABSENSI ====================
        $attendanceStats = $this->getAttendanceChartData($user);
        
        // Statistik nilai per kelas
        $classStats = collect();
        $allClassIds = $classes->pluck('id');
        
        if ($allClassIds->count() > 0) {
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
        
        return view('dashboard.teacher', compact(
            'classes',
            'totalStudents',
            'activeAssignments',
            'recentAssignments',
            'pendingSubmissions',
            'recentSubmissions',
            'activeQrCodes',
            'todayClasses',
            'attendanceStats',
            'classStats'
        ));
    }

    /**
     * Helper method untuk data chart absensi - FIXED
     */
    private function getAttendanceChartData($user)
    {
        $data = [
            'labels' => [],
            'present' => [],
            'late' => [],
            'absent' => []
        ];
        
        // Nama hari dalam bahasa Indonesia (singkat)
        $shortDayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        
        // Generate data untuk 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $dayIndex = $date->dayOfWeek;
            
            // Gunakan index yang valid (0-6)
            $validIndex = ($dayIndex >= 0 && $dayIndex <= 6) ? $dayIndex : $i;
            $data['labels'][] = $shortDayNames[$validIndex];
            
            // Query untuk menghitung absensi per hari
            $attendanceCounts = Attendance::whereHas('class', function($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                })
                ->whereDate('attendance_date', $dateString)
                ->get();
            
            $data['present'][] = $attendanceCounts->where('status', 'present')->count();
            $data['late'][] = $attendanceCounts->where('status', 'late')->count();
            $data['absent'][] = $attendanceCounts->where('status', 'absent')->count();
        }
        
        return $data;
    }
}