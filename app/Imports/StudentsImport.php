<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $classId;

    public function __construct($classId = null)
    {
        $this->classId = $classId;
    }

    public function model(array $row)
    {
        // Generate random password
        $password = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
        
        $student = User::create([
            'nis_nip' => $row['nis'] ?? $row['nis_nip'],
            'name' => $row['nama'] ?? $row['name'],
            'email' => $row['email'],
            'password' => Hash::make($password),
            'role' => 'student',
            'is_active' => true,
        ]);
        
        // Attach to class if specified
        if ($this->classId) {
            $student->classesAsStudent()->attach($this->classId, [
                'enrolled_at' => now(),
                'status' => 'active'
            ]);
        }
        
        return $student;
    }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'unique:users,nis_nip'],
            'nama' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nis.required' => 'NIS harus diisi',
            'nis.unique' => 'NIS sudah terdaftar',
            'nama.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
        ];
    }
}