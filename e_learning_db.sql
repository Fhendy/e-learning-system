-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 29, 2026 at 04:18 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e_learning_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `due_date` datetime NOT NULL,
  `max_score` int NOT NULL DEFAULT '100',
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` bigint UNSIGNED NOT NULL,
  `assignment_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `score` int DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','submitted','late','graded','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `qr_code_id` bigint UNSIGNED DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','late','absent','sick','permission') COLLATE utf8mb4_unicode_ci NOT NULL,
  `checked_in_at` time DEFAULT NULL,
  `checked_out_at` time DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `accuracy` decimal(8,2) DEFAULT NULL COMMENT 'Akurasi GPS dalam meter',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `marked_by` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `student_id`, `class_id`, `qr_code_id`, `attendance_date`, `status`, `checked_in_at`, `checked_out_at`, `notes`, `latitude`, `longitude`, `accuracy`, `ip_address`, `user_agent`, `created_at`, `updated_at`, `marked_by`) VALUES
(30, 16, 3, 98, '2026-04-18', 'present', '17:55:42', NULL, NULL, '-6.92680000', '109.56420000', NULL, NULL, NULL, '2026-04-18 10:55:42', '2026-04-18 10:55:42', 16);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint UNSIGNED NOT NULL,
  `class_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semester` enum('ganjil','genap') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `teacher_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `class_code`, `description`, `subject`, `semester`, `academic_year`, `school_year`, `is_active`, `teacher_id`, `created_at`, `updated_at`) VALUES
(1, 'XI PBS', 'XIPBS', '', 'SEJARAH', 'ganjil', NULL, NULL, 1, 2, '2026-04-07 17:35:40', '2026-04-11 04:29:49'),
(2, 'XI RPL', 'XIRPL', '', 'DKV', 'ganjil', NULL, NULL, 1, 2, '2026-04-09 15:44:52', '2026-04-11 11:25:32'),
(3, 'DKV XI RPL', 'DKVXIRPL', 'Kelas DKV XI RPL', 'Desain Komunikasi Visual', 'ganjil', '2024/2025', NULL, 1, 2, '2026-04-11 10:42:36', '2026-04-11 17:01:15'),
(4, 'KK XII RPL', 'KKXIRPL', 'Kelas KK XII RPL', 'Konsentrasi Keahlian', 'ganjil', '2024/2025', NULL, 1, 2, '2026-04-11 10:42:36', '2026-04-11 11:56:10');

-- --------------------------------------------------------

--
-- Table structure for table `class_student`
--

CREATE TABLE `class_student` (
  `id` bigint UNSIGNED NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_student`
--

INSERT INTO `class_student` (`id`, `class_id`, `student_id`, `enrolled_at`, `created_at`, `updated_at`) VALUES
(8, 3, 10, NULL, '2026-04-11 10:53:42', '2026-04-11 10:53:42'),
(9, 3, 11, NULL, '2026-04-11 10:53:42', '2026-04-11 10:53:42'),
(10, 3, 12, NULL, '2026-04-11 10:53:42', '2026-04-11 10:53:42'),
(13, 3, 15, NULL, '2026-04-11 10:53:43', '2026-04-11 10:53:43'),
(14, 3, 16, NULL, '2026-04-11 14:37:36', '2026-04-11 14:37:36');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_12_01_165345_create_users_table', 1),
(6, '2025_12_01_165349_create_classes_table', 1),
(7, '2025_12_01_165352_create_assignments_table', 1),
(8, '2025_12_01_165355_create_submissions_table', 1),
(9, '2025_12_02_122734_create_attendances_table', 2),
(10, '2025_12_02_164711_create_qr_codes_table', 2),
(11, '2025_12_03_115143_add_is_active_to_classes_table', 2),
(12, '2025_12_03_134735_create_assignment_submissions_table', 3),
(13, '2025_12_06_105144_add_enrolled_at_to_class_student_table', 3),
(14, '2025_12_06_112040_add_is_active_to_users_table', 3),
(15, '2025_12_06_113810_fix_classes_table_columns', 3),
(16, '2025_12_06_153253_add_subject_to_classes_table', 3),
(17, '2025_12_07_195740_add_marked_by_to_attendances_table', 3),
(18, '2025_12_07_195952_update_attendances_table_add_missing_columns', 3),
(19, '2025_12_07_200027_check_attendance_table_columns', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_minutes` int NOT NULL DEFAULT '15',
  `location_restricted` tinyint(1) NOT NULL DEFAULT '0',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `radius` int DEFAULT NULL COMMENT 'in meters',
  `qr_code_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `scan_count` int NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_codes`
--

INSERT INTO `qr_codes` (`id`, `code`, `class_id`, `date`, `start_time`, `end_time`, `duration_minutes`, `location_restricted`, `latitude`, `longitude`, `radius`, `qr_code_image`, `is_active`, `scan_count`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(98, '93VT2792', 3, '2026-04-18', '17:54:00', '18:54:00', 30, 0, NULL, NULL, NULL, 'qr-codes/93VT2792.png', 1, 1, NULL, 2, '2026-04-18 10:55:33', '2026-04-18 10:55:42');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` bigint UNSIGNED NOT NULL,
  `assignment_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `submission_text` text COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` int DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `status` enum('submitted','graded','late') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','teacher','student') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `nis_nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `is_active`, `nis_nip`, `phone`, `address`, `birth_date`, `profile_image`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@elearning.com', NULL, '$2y$12$FeafrWuC.QqVE5ufta7OxueJvD0QVry32pu9zDaGkMjq3KzdD3Pzu', 'admin', 1, 'ADM001', NULL, NULL, NULL, NULL, NULL, '2026-04-07 17:35:39', '2026-04-07 17:35:39'),
(2, 'Fhendy S.Kom', 'teacher@elearning.com', NULL, '$2y$12$XEZdJZ/Id4YlqP9BWQvOv.AHH4aO/pqTYujqIXa05ScHNzntVzATS', 'teacher', 1, 'TCH001', '081234567890', NULL, NULL, NULL, NULL, '2026-04-07 17:35:39', '2026-04-09 15:00:36'),
(10, 'Eko Prasetyo', 'eko.prasetyo05@gmail.com', NULL, '$2y$12$.K6WXDtgJaWB4iDhXIEvc.6KoLdvPaMwrKM6jh8mbL5XeAz2o0mLW', 'student', 1, '2024003', NULL, NULL, NULL, NULL, NULL, '2026-04-11 10:53:42', '2026-04-11 10:53:42'),
(11, 'Fajar Nugroho', 'fajar.nugroho06@gmail.com', NULL, '$2y$12$gn2jMDXyWc3eXgCd.oJ5sOWtM0gExJPDofPnUrKroovdkGiNdLV5y', 'student', 1, '2024004', NULL, NULL, NULL, NULL, NULL, '2026-04-11 10:53:42', '2026-04-11 10:53:42'),
(12, 'Gita Sari', 'gita.sari07@gmail.com', NULL, '$2y$12$McPrv/FLpo0tKC0HzmdDeuIECckSTLiraTucTi5Aw.XwprNi2vGVq', 'student', 1, '2024005', NULL, NULL, NULL, NULL, NULL, '2026-04-11 10:53:42', '2026-04-11 10:53:42'),
(15, 'Joko Susilo', 'joko.susilo10@gmail.com', NULL, '$2y$12$zWxgbFXhP.T7EDMZreEXYucUWCJ3A1loirUQcFORDqDvcq0hRmgdO', 'student', 1, '2024008', NULL, NULL, NULL, NULL, NULL, '2026-04-11 10:53:43', '2026-04-11 10:53:43'),
(16, 'fhen', 'fhen@gmail.com', NULL, '$2y$12$nIvht133PB9mkzGEsZgHzO7Vi27oZrPngEzNOMXsdL4lUebvJQoSC', 'student', 1, 'F235893', NULL, NULL, NULL, NULL, NULL, '2026-04-11 14:37:36', '2026-04-11 14:37:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignments_class_id_foreign` (`class_id`),
  ADD KEY `assignments_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assignment_submissions_assignment_id_student_id_unique` (`assignment_id`,`student_id`),
  ADD KEY `assignment_submissions_student_id_foreign` (`student_id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_student_id_class_id_attendance_date_index` (`student_id`,`class_id`,`attendance_date`),
  ADD KEY `attendances_class_id_attendance_date_index` (`class_id`,`attendance_date`),
  ADD KEY `attendances_qr_code_id_attendance_date_index` (`qr_code_id`,`attendance_date`),
  ADD KEY `attendances_marked_by_foreign` (`marked_by`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `classes_class_code_unique` (`class_code`),
  ADD KEY `classes_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `class_student`
--
ALTER TABLE `class_student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_student_class_id_foreign` (`class_id`),
  ADD KEY `class_student_student_id_foreign` (`student_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_codes_code_unique` (`code`),
  ADD KEY `qr_codes_class_id_foreign` (`class_id`),
  ADD KEY `qr_codes_created_by_foreign` (`created_by`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submissions_assignment_id_foreign` (`assignment_id`),
  ADD KEY `submissions_student_id_foreign` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_nis_nip_unique` (`nis_nip`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `class_student`
--
ALTER TABLE `class_student`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_marked_by_foreign` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `class_student`
--
ALTER TABLE `class_student`
  ADD CONSTRAINT `class_student_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qr_codes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
