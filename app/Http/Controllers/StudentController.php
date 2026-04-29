<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentImportedMail;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::students()->with('classesAsStudent');
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nis_nip', 'like', "%{$search}%");
            });
        }
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('classesAsStudent', function($q) use ($request) {
                $q->where('classes.id', $request->class_id);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $status = $request->status == 'active' ? true : false;
            $query->where('is_active', $status);
        }
        
        $students = $query->latest()->paginate(20);
        $classes = ClassModel::active()->get();
        
        return view('students.index', compact('students', 'classes'));
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = ClassModel::active()->get();
        return view('students.create', compact('classes'));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'nis_nip' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:15'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:classes,id'],
            'is_active' => ['boolean'],
        ]);

        $student = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nis_nip' => $request->nis_nip,
            'phone' => $request->phone,
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
    
    /**
     * Show import form
     */
    public function import()
    {
        $classes = ClassModel::active()->get();
        return view('students.import', compact('classes'));
    }
    
    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        try {
            // Create template file using PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $sheet->setCellValue('A1', 'NIS');
            $sheet->setCellValue('B1', 'Nama');
            $sheet->setCellValue('C1', 'Email');
            $sheet->setCellValue('D1', 'Password');
            $sheet->setCellValue('E1', 'Telepon');
            
            // Style headers
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            
            // Add example data
            $sheet->setCellValue('A2', '2024001');
            $sheet->setCellValue('B2', 'Budi Santoso');
            $sheet->setCellValue('C2', 'budi@email.com');
            $sheet->setCellValue('D2', 'password123');
            $sheet->setCellValue('E2', '08123456789');
            
            $sheet->setCellValue('A3', '2024002');
            $sheet->setCellValue('B3', 'Ani Wijaya');
            $sheet->setCellValue('C3', 'ani@email.com');
            $sheet->setCellValue('D3', 'password123');
            $sheet->setCellValue('E3', '08198765432');
            
            // Add instruction sheet
            $instructionSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Petunjuk');
            $spreadsheet->addSheet($instructionSheet, 0);
            $instructionSheet->setCellValue('A1', 'PANDUAN IMPORT DATA SISWA');
            $instructionSheet->setCellValue('A2', '================================');
            $instructionSheet->setCellValue('A4', 'Kolom yang wajib diisi:');
            $instructionSheet->setCellValue('A5', '1. NIS - Nomor Induk Siswa (unik)');
            $instructionSheet->setCellValue('A6', '2. Nama - Nama lengkap siswa');
            $instructionSheet->setCellValue('A7', '3. Email - Alamat email (unik)');
            $instructionSheet->setCellValue('A9', 'Kolom opsional:');
            $instructionSheet->setCellValue('A10', '4. Password - Jika kosong akan diisi "password123"');
            $instructionSheet->setCellValue('A11', '5. Telepon - Nomor telepon siswa');
            $instructionSheet->setCellValue('A13', 'Catatan:');
            $instructionSheet->setCellValue('A14', '- File harus dalam format .xlsx atau .xls');
            $instructionSheet->setCellValue('A15', '- Maksimal ukuran file 5MB');
            $instructionSheet->setCellValue('A16', '- Data dengan NIS yang sudah ada akan dilewati jika opsi skip dipilih');
            
            $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $instructionSheet->getColumnDimension('A')->setWidth(60);
            
            // Set active sheet to data
            $spreadsheet->setActiveSheetIndex(1);
            
            // Create writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="template_siswa.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit();
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }
    
    /**
     * Process import Excel file
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'class_id' => 'nullable|exists:classes,id'
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
            $successStudents = [];
            
            foreach ($rows as $index => $row) {
                try {
                    $nis = trim($row[0] ?? '');
                    $name = trim($row[1] ?? '');
                    $email = trim($row[2] ?? '');
                    $password = trim($row[3] ?? '');
                    $phone = trim($row[4] ?? '');
                    
                    // Skip empty rows
                    if (empty($nis) && empty($name) && empty($email)) {
                        continue;
                    }
                    
                    // Validate required fields
                    if (empty($nis)) {
                        $errors[] = "Baris " . ($index + 2) . ": NIS wajib diisi";
                        $errorCount++;
                        continue;
                    }
                    
                    if (empty($name)) {
                        $errors[] = "Baris " . ($index + 2) . ": Nama wajib diisi";
                        $errorCount++;
                        continue;
                    }
                    
                    if (empty($email)) {
                        $errors[] = "Baris " . ($index + 2) . ": Email wajib diisi";
                        $errorCount++;
                        continue;
                    }
                    
                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Baris " . ($index + 2) . ": Format email tidak valid ({$email})";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check if student already exists
                    $existingStudent = User::where('nis_nip', $nis)->first();
                    
                    if ($existingStudent && $request->has('skip_duplicates')) {
                        continue;
                    }
                    
                    if ($existingStudent) {
                        $errors[] = "Baris " . ($index + 2) . ": NIS {$nis} sudah ada";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check email uniqueness
                    $existingEmail = User::where('email', $email)->first();
                    if ($existingEmail) {
                        $errors[] = "Baris " . ($index + 2) . ": Email {$email} sudah digunakan";
                        $errorCount++;
                        continue;
                    }
                    
                    // Create student
                    $student = User::create([
                        'name' => $name,
                        'email' => $email,
                        'nis_nip' => $nis,
                        'phone' => $phone,
                        'password' => Hash::make($password ?: 'password123'),
                        'role' => 'student',
                        'is_active' => true
                    ]);
                    
                    // Add to class if selected
                    if ($request->filled('class_id')) {
                        $student->classesAsStudent()->attach($request->class_id);
                    }
                    
                    $successCount++;
                    $successStudents[] = $student;
                    
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                    $errorCount++;
                }
            }
            
            $message = "Import selesai! Berhasil: {$successCount} siswa, Gagal: {$errorCount} siswa.";
            
            // Send emails if option selected
            if ($request->has('send_email') && $successCount > 0) {
                foreach ($successStudents as $student) {
                    try {
                        Mail::to($student->email)->send(new StudentImportedMail($student));
                    } catch (\Exception $e) {
                        // Log error but continue
                        \Log::error('Failed to send email to ' . $student->email . ': ' . $e->getMessage());
                    }
                }
                $message .= " Email notifikasi telah dikirim.";
            }
            
            if ($successCount > 0) {
                return redirect()->route('students.index')->with('success', $message);
            } else {
                return redirect()->back()->with('error', $message)->with('import_errors', $errors);
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import file: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $student->load(['classesAsStudent.teacher', 'submissions.assignment']);
        return view('students.show', compact('student'));
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $classes = ClassModel::active()->get();
        $studentClasses = $student->classesAsStudent->pluck('id')->toArray();
        
        return view('students.edit', compact('student', 'classes', 'studentClasses'));
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $student->id],
            'nis_nip' => ['required', 'string', 'max:20', 'unique:users,nis_nip,' . $student->id],
            'phone' => ['nullable', 'string', 'max:15'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:classes,id'],
            'is_active' => ['boolean'],
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'nis_nip' => $request->nis_nip,
            'phone' => $request->phone,
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
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }
        
        // Detach from all classes first
        $student->classesAsStudent()->detach();
        
        // Delete the student
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }
    
    /**
     * Export students to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = User::students()->with('classesAsStudent');
        
        // Apply filters
        if ($request->has('class_id') && $request->class_id) {
            $query->whereHas('classesAsStudent', function($q) use ($request) {
                $q->where('classes.id', $request->class_id);
            });
        }
        
        $students = $query->get();
        
        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'NAMA');
        $sheet->setCellValue('D1', 'EMAIL');
        $sheet->setCellValue('E1', 'TELEPON');
        $sheet->setCellValue('F1', 'KELAS');
        $sheet->setCellValue('G1', 'STATUS');
        
        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Add data
        $row = 2;
        foreach ($students as $index => $student) {
            $classes = $student->classesAsStudent->pluck('class_name')->implode(', ');
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $student->nis_nip);
            $sheet->setCellValue('C' . $row, $student->name);
            $sheet->setCellValue('D' . $row, $student->email);
            $sheet->setCellValue('E' . $row, $student->phone);
            $sheet->setCellValue('F' . $row, $classes ?: '-');
            $sheet->setCellValue('G' . $row, $student->is_active ? 'Aktif' : 'Nonaktif');
            $row++;
        }
        
        // Auto size columns
        foreach(range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="siswa_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit();
    }
    
    /**
     * Export students to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = User::students()->with('classesAsStudent');
        
        if ($request->has('class_id') && $request->class_id) {
            $query->whereHas('classesAsStudent', function($q) use ($request) {
                $q->where('classes.id', $request->class_id);
            });
        }
        
        $students = $query->get();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.export-pdf', compact('students'));
        return $pdf->download('siswa_' . date('Y-m-d') . '.pdf');
    }
}