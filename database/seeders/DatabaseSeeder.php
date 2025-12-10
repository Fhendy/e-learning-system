<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@elearning.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'nis_nip' => 'ADM001',
        ]);

        // Create sample teacher
        $teacher = User::create([
            'name' => 'Guru Contoh',
            'email' => 'teacher@elearning.com',
            'password' => Hash::make('password123'),
            'role' => 'teacher',
            'nis_nip' => 'TCH001',
            'phone' => '081234567890',
        ]);

        // Create sample students
        $students = [
            [
                'name' => 'Siswa 1',
                'email' => 'student1@elearning.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'nis_nip' => 'STD001',
            ],
            [
                'name' => 'Siswa 2',
                'email' => 'student2@elearning.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'nis_nip' => 'STD002',
            ],
        ];

        foreach ($students as $studentData) {
            User::create($studentData);
        }

        // Create sample class
        $class = ClassModel::create([
            'class_name' => 'Matematika Kelas 10',
            'class_code' => 'MATH-10-A',
            'description' => 'Kelas Matematika untuk Kelas 10',
            'teacher_id' => $teacher->id,
        ]);

        // Add students to class
        $studentUsers = User::where('role', 'student')->get();
        $class->students()->attach($studentUsers->pluck('id'));
    }
}