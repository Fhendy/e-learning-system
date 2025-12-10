<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::students();
        
        if (isset($this->filters['class_id']) && $this->filters['class_id'] != 'all') {
            $query->whereHas('classesAsStudent', function($q) {
                $q->where('classes.id', $this->filters['class_id']);
            });
        }
        
        if (isset($this->filters['status']) && $this->filters['status'] != 'all') {
            $status = $this->filters['status'] == 'active' ? true : false;
            $query->where('is_active', $status);
        }
        
        return $query->with('classesAsStudent')->get();
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Lengkap',
            'Email',
            'Kelas',
            'Status',
            'Tanggal Bergabung'
        ];
    }

    public function map($student): array
    {
        $classes = $student->classesAsStudent->pluck('class_code')->implode(', ');
        
        return [
            $student->nis_nip,
            $student->name,
            $student->email,
            $classes,
            $student->is_active ? 'Aktif' : 'Nonaktif',
            $student->created_at->format('d/m/Y')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            
            // Set column widths
            'A' => ['width' => 15],
            'B' => ['width' => 30],
            'C' => ['width' => 30],
            'D' => ['width' => 20],
            'E' => ['width' => 15],
            'F' => ['width' => 20],
        ];
    }
}