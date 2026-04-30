# 📚 E-Learning System

Sistem Manajemen Pembelajaran Online (LMS) yang komprehensif untuk mengelola kelas, tugas, absensi, dan nilai siswa secara digital.

![Laravel Version](https://img.shields.io/badge/Laravel-10.x-red.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)
![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-blue.svg)
![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## ✨ Fitur Utama

### 👨‍🏫 Untuk Guru
- **Manajemen Kelas** - Buat, edit, dan kelola kelas
- **Generate QR Code Absensi** - Buat QR Code untuk absensi siswa (bisa discan via HP)
- **Manajemen Tugas** - Buat tugas, upload materi, dan atur deadline
- **Penilaian** - Beri nilai tugas siswa secara manual atau batch
- **Statistik Absensi** - Lihat grafik kehadiran siswa per hari/minggu/bulan
- **Export Data** - Export data absensi dan nilai ke Excel/CSV
- **Input Manual** - Input absensi manual untuk siswa yang tidak hadir

### 👨‍🎓 Untuk Siswa
- **Scan QR Code Absensi** - Absensi dengan scan QR Code via kamera HP
- **Lihat Tugas** - Lihat daftar tugas dan deadline
- **Submit Tugas** - Upload tugas, bisa resubmit jika diperlukan
- **Lihat Nilai** - Lihat nilai tugas yang sudah dinilai
- **Riwayat Absensi** - Lihat riwayat kehadiran
- **Statistik Pribadi** - Lihat grafik perkembangan nilai dan absensi

### 👑 Untuk Admin
- **Manajemen Pengguna** - Kelola akun guru dan siswa
- **Import/Export Data** - Import siswa via Excel, export data keseluruhan
- **Laporan** - Generate laporan lengkap
- **Dashboard Monitoring** - Pantau aktivitas seluruh pengguna

## 🚀 Teknologi yang Digunakan

| Teknologi | Versi | Keterangan |
|-----------|-------|-------------|
| Laravel | 10.x | Framework PHP backend |
| PHP | 8.1+ | Bahasa pemrograman |
| MySQL | 5.7+ | Database |
| Bootstrap | 5.3 | Frontend framework |
| jQuery | 3.6 | JavaScript library |
| Chart.js | 4.4 | Library grafik |
| SweetAlert2 | 11 | Alert & notifikasi modern |
| HTML5 QR Code | 2.0 | Scanner QR code via kamera |

## 📋 Prasyarat

Sebelum menginstall aplikasi, pastikan server Anda memenuhi persyaratan berikut:

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (opsional, untuk development)
- Ekstensi PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD, cURL

## 🔧 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/e-learning-system.git
cd e-learning-system
