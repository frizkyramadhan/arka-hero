SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `additionaldatas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cloth_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pants_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shoes_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `height` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `glasses` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `administrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `position_id` bigint(20) UNSIGNED NOT NULL,
  `nik` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `doh` date NOT NULL,
  `foc` date DEFAULT NULL,
  `agreement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_program` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_fptk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_sk_active` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poh` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `basic_salary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_allowance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `other_allowance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `termination_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coe_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `banks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `banks` (`id`, `bank_name`, `bank_status`, `created_at`, `updated_at`) VALUES
(1, 'Mandiri', 1, '2023-06-06 21:42:27', '2023-06-06 21:42:27'),
(2, 'BNI', 1, '2023-06-06 21:42:27', '2023-06-06 21:42:27'),
(3, 'BRI', 1, '2023-06-06 21:42:27', '2023-06-06 21:42:27'),
(4, 'Kaltimtara', 1, '2023-06-06 21:42:27', '2023-06-06 21:42:27'),
(5, 'BPD Kaltim', 1, '2023-06-06 21:42:27', '2023-06-06 21:42:27');

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `departments` (`id`, `department_name`, `slug`, `department_status`, `created_at`, `updated_at`) VALUES
(1, 'Accounting', 'ACC', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(2, 'Corporate Secretary', 'CORSEC', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(3, 'Design & Construction', 'DNC', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(4, 'Finance', 'FIN', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(5, 'Human Capital & Support', 'HCS', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(6, 'Internal Audit & System', 'IAS', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(7, 'Information Technology', 'ITY', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(8, 'Logistic', 'LOG', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(9, 'Operation', 'OPS', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(10, 'Plant', 'PLT', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(11, 'Procurement', 'PROC', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(12, 'Production', 'PROD', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(13, 'Relation & Coordination', 'RNC', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(14, 'Safety, Health & Environment', 'SHE', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(15, 'Board of Directors', 'BOD', 1, '2023-06-06 21:47:17', '2023-06-06 21:47:17'),
(16, 'Engineering', 'ENG', 1, '2023-06-06 21:48:01', '2023-06-06 21:48:01');

CREATE TABLE `educations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `education_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employeebanks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_id` bigint(20) UNSIGNED NOT NULL,
  `bank_account_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employees` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emp_pob` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emp_dob` date NOT NULL,
  `blood_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion_id` bigint(20) UNSIGNED NOT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `village` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identity_card` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emrgcalls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emrg_call_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emrg_call_relation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emrg_call_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emrg_call_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `families` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_relationship` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_birthplace` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `family_birthdate` date DEFAULT NULL,
  `family_remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjsks_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_profile` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `insurances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `health_insurance_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `health_insurance_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `health_facility` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `health_insurance_remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobexperiences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quit_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `licenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_license_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_license_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_license_exp` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2022_11_04_005024_create_projects_table', 1),
(6, '2022_11_04_011931_create_banks_table', 1),
(7, '2022_11_04_013831_create_departments_table', 1),
(8, '2022_11_04_014956_create_positions_table', 1),
(9, '2022_11_04_015736_create_religions_table', 1),
(10, '2022_11_04_025647_create_employees_table', 1),
(11, '2022_11_05_043127_create_licenses_table', 1),
(12, '2022_11_05_053757_create_insurances_table', 1),
(13, '2022_11_07_004716_create_families_table', 1),
(14, '2022_11_07_010510_create_educations_table', 1),
(15, '2022_11_07_014825_create_courses_table', 1),
(16, '2022_11_07_015958_create_emrgcalls_table', 1),
(17, '2022_11_07_021023_create_additionaldatas_table', 1),
(18, '2022_11_07_022004_create_employeebanks_table', 1),
(19, '2022_11_07_035825_create_administrations_table', 1),
(20, '2022_11_29_055533_create_jobexperiences_table', 1),
(21, '2022_11_29_055702_create_operableunits_table', 1),
(22, '2022_11_29_072159_create_taxidentifications_table', 1),
(23, '2022_11_29_072326_create_images_table', 1),
(24, '2022_12_16_062332_add_foc_to_administrations_table', 1),
(25, '2022_12_20_002951_create_schools_table', 1),
(26, '2023_01_09_034425_create_notifications_table', 1);

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `operableunits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `position_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `position_status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `positions` (`id`, `position_name`, `department_id`, `position_status`, `created_at`, `updated_at`) VALUES
(1, 'Accounting & IT Manager', 1, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(2, 'Accounting Major Supervisor', 1, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(3, 'Accounting Officer', 1, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(4, 'Accounting Senior Officer', 1, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(5, 'Act. Mechanic Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(6, 'Admin APS', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(7, 'Admin HR', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(8, 'Admin Logistic', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(9, 'Admin Plant', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(10, 'Admin PPO', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(11, 'Admin Production', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(12, 'Admin Safety', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(13, 'Asset & Cost Control Senior Officer', 4, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(14, 'Asset & Cost Control Superintendent', 4, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(15, 'Assisten Engineering', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(16, 'Asst. Paramedic', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(17, 'Auditor Major Officer', 6, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(18, 'Auditor Major Supervisor', 6, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(19, 'Blasting Senior Supervisor', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(20, 'Body Repair Advance', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(21, 'Body Repair General Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(22, 'Carpenter', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(23, 'CCR', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(24, 'CCR Engineering', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(25, 'CCR Plant', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(26, 'Civil Engineer General Supervisor', 3, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(27, 'Civil Engineer Officer', 3, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(28, 'Civil Engineer Senior Officer', 3, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(29, 'Clerk CCR', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(30, 'Clerk Plant', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(31, 'Cook', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(32, 'Corsec General Officer', 2, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(33, 'Corsec Senior Manager', 2, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(34, 'Corsec Senior Officer', 2, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(35, 'Crew Blasting', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(36, 'D&C Administrator', 3, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(37, 'Danru Security ', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(38, 'Design & Construction Manager', 3, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(39, 'Director', 4, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(40, 'Director', 9, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(41, 'Drilling & Blasing Supervisor', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(42, 'Drilling & Blasting Foreman', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(43, 'Drilling & Blasting Senior Foreman', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(44, 'Driver', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(45, 'Driver Fuel Truck', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(46, 'Driver Fuel Truck', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(47, 'Driver Lube Truck', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(48, 'Driver MMU', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(49, 'Driver Sarana', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(50, 'Driver Sarana', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(51, 'Driver Sarana', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(52, 'Driver Truck Support', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(53, 'Driver Water Truck', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(54, 'Dumpman', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(55, 'Dumpman', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(56, 'Eksternal Major Officer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(57, 'Eksternal Relations Senior Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(58, 'Electric', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(59, 'Electric Advance', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(60, 'Electric Senior', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(61, 'Electric Senior Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(62, 'Engineering Superintendent', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(63, 'Equipment Health General Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(64, 'Equipment Health Senior Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(65, 'Fabrication & Machinist Senior Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(66, 'Finance Division Manager', 4, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(67, 'Finance Officer', 4, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(68, 'Finance Senior Superintendent', 4, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(69, 'Finance Senior Supervisor', 4, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(70, 'Finance Supervisor', 4, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(71, 'Foreman Mechanic', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(72, 'Front End Developer', 7, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(73, 'Fuel Truck', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(74, 'Fuelman', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(75, 'Fuelman', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(76, 'GA Assistant', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(77, 'GA Major Officer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(78, 'GA Senior Officer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(79, 'GA Superintendent', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(80, 'GA Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(81, 'GA Support', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(82, 'General Helper', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(83, 'HCS Manager', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(84, 'Helper Blasting', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(85, 'Helper Cook', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(86, 'Helper Mechanic', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(87, 'Helper Survey', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(88, 'Helper Tyre', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(89, 'HR & GA General Superintendent', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(90, 'HR & GA General Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(91, 'HR & GA Major Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(92, 'HR & GA Senior Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(93, 'HR Administrator', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(94, 'HR Clerk', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(95, 'HR General Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(96, 'HR Major Officer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(97, 'HR Officer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(98, 'HR Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(99, 'HRGA Clerk', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(100, 'Instrument Hand', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(101, 'Instrument Hand', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(102, 'Internal Audit & System Manager', 6, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(103, 'IT General Supervisor', 7, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(104, 'IT Major Officer', 7, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(105, 'IT Officer', 7, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(106, 'Laundry', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(107, 'Legal General Officer', 2, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(108, 'Legal Major Supervisor', 2, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(109, 'Logistic General Foreman', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(110, 'Logistic Major Officer', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(111, 'Logistic Senior Foreman', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(112, 'Logistic Senior Officer', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(113, 'Logistic Senior Superintendent', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(114, 'Logistic Senior Supervisor', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(115, 'Lube Truck', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(116, 'Machinist', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(117, 'Machinist Advance', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(118, 'Machinist Senior', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(119, 'Maintenance Planner Senior Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(120, 'Marketing & Secretary Supervisor', 13, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(121, 'Marketng & Adv Major Officer', 13, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(122, 'Mechanic', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(123, 'Mechanic Advance ', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(124, 'Mechanic Advance Sarana', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(125, 'Mechanic Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(126, 'Mechanic General Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(127, 'Mechanic Major Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(128, 'Mechanic Rebuild Senior', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(129, 'Mechanic Senior', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(130, 'Mechanic Senior Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(131, 'Mechanic Senior Rebuild', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(132, 'Mechanic Senior Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(133, 'Mechanic Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(134, 'Mess Boy', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(135, 'Mine Engineer Foreman', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(136, 'Mine Engineer Senior Foreman', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(137, 'Mine Engineer Superintendent', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(138, 'Monitoring & Controlling Engineer Foreman', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(139, 'Office Boy', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(140, 'Oil Man', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(141, 'Operation General Manager', 9, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(142, 'Operator 777-E', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(143, 'Operator Adt', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(144, 'Operator Adt Volvo A40G', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(145, 'Operator All Round He', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(146, 'Operator Bulldozer', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(147, 'Operator Crane Advance', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(148, 'Operator Dozer', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(149, 'Operator Dozer D8R', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(150, 'Operator Dozer D8R/D9R', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(151, 'Operator Dozer D9R', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(152, 'Operator Drilling', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(153, 'Operator Drilling', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(154, 'Operator Dt Sany', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(155, 'Operator Excavator', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(156, 'Operator Excavator', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(157, 'Operator Excavator (Big Digger)', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(158, 'Operator Excavator 1200', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(159, 'Operator Excavator 1200, 870, 850, 210', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(160, 'Operator Excavator 1250', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(161, 'Operator Excavator 2600', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(162, 'Operator Excavator 2600-6', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(163, 'Operator Excavator Ex 1200/ Pc 1250 Sp-8', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(164, 'Operator Excavator Ex 2600-6', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(165, 'Operator Excavator Pc 1250', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(166, 'Operator Excavator Pc 1250', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(167, 'Operator Excavator Pc200', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(168, 'Operator Forklif', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(169, 'Operator Grader', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(170, 'Operator Hitachi Ex- 870', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(171, 'Operator Hitachi Ex-1200', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(172, 'Operator Motor Grader', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(173, 'Operator Motor Grader 16M', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(174, 'Operator OHT', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(175, 'Operator OHT 773', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(176, 'Operator OHT 773E', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(177, 'Operator OHT 777', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(178, 'Operator OHT 777D', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(179, 'Operator OHT 777D', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(180, 'Operator OHT 777D', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(181, 'Operator OHT 777E', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(182, 'Operator OHT 785', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(183, 'Operator OHT 785-7', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(184, 'Operator OHT Cat 777D', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(185, 'Operator Trainer Foreman', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(186, 'Operator Trainer Senior Foreman', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(187, 'Operator Trainer Senior Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(188, 'Operator Trainer Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(189, 'Operator Volvo Adt', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(190, 'Operator Volvo Adt A40G', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(191, 'Operator Water Truck', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(192, 'Operator Wheel Loader', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(193, 'Paramedic Foreman', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(194, 'Paramedic Senior Foreman', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(195, 'Payroll & Benefit Major Supervisor', 13, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(196, 'Planner Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(197, 'Plant Clerk', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(198, 'Plant Deputy Manager', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(199, 'Plant Engineer General Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(200, 'Plant Engineer Major Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(201, 'Plant Engineer Major Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(202, 'Plant Engineer Senior Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(203, 'Plant General Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(204, 'Plant Senior Manager', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(205, 'Plant Senior Superintendent', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(206, 'Plant Senior Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(207, 'Plant Superintendent', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(208, 'President Director', 15, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(209, 'Procurement Administrasi Officer', 11, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(210, 'Procurement Administrator', 11, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(211, 'Procurement General Officer', 11, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(212, 'Procurement General Supervisor', 11, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(213, 'Procurement Major Officer', 11, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(214, 'Procurement Manager', 11, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(215, 'Production Clerk', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(216, 'Production Deputy Manager', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(217, 'Production Foreman', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(218, 'Production General Foreman', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(219, 'Production General Supervisor', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(220, 'Production Major Foreman', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(221, 'Production Senior Foreman', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(222, 'Production Senior Superintendent', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(223, 'Production Senior Supervisor', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(224, 'Production Supervisor', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(225, 'Project Manager', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(226, 'Project Manager', 9, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(227, 'Pumpman', 12, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(228, 'Pumpman', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(229, 'Pumpman', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(230, 'R & C General Manager', 13, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(231, 'Recruitment Senior Officer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(232, 'Rigger & General Worker', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(233, 'RNC Officer', 13, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(234, 'Safety Assisstant', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(235, 'Safety Officer', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(236, 'Safety Patrol', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(237, 'Safetyman', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(238, 'Sarana Support Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(239, 'Security', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(240, 'Senior Cook', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(241, 'Senior Machinist', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(242, 'Senior Project Manager', 9, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(243, 'Service Analyst General Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(244, 'SHE Officer', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(245, 'SHE Senior Officer', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(246, 'SHE Senior Supervisor', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(247, 'SHE Superintendent', 14, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(248, 'Site Accounting Officer', 1, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(249, 'Spotter', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(250, 'Store Senior Officer', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(251, 'Storeman', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(252, 'Storeman I', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(253, 'Storeman Ii', 8, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(254, 'Survey General Supervisor', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(255, 'Surveyor', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(256, 'Surveyor General Supervisor', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(257, 'Surveyor Major Foreman', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(258, 'Surveyor Major Supervisor', 16, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(259, 'TA Rebuild Senior Supervisor', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(260, 'Technical Trainer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(261, 'Technical Trainer Foreman', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(262, 'Technical Trainer Major Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(263, 'Technical Trainer Senior Officer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(264, 'Technical Trainer Supervisor', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(265, 'Tool Keeper', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(266, 'Trackman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(267, 'Trainer Fasilitator Senior Officer', 5, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(268, 'Tyre Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(269, 'Tyre General Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(270, 'Tyre Major Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(271, 'Tyreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(272, 'Tyreman Advance', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(273, 'Tyreman Senior', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(274, 'Welder', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(275, 'Welder Advance', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(276, 'Welder General Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(277, 'Welder Major Foreman', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12'),
(278, 'Welder Senior', 10, 1, '2023-06-06 21:59:12', '2023-06-06 21:59:12');

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bowheer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` (`id`, `project_code`, `project_name`, `project_location`, `bowheer`, `project_status`, `created_at`, `updated_at`) VALUES
(1, '000H', 'HO - Balikpapan', 'Balikpapan', 'Arka', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(2, '001H', 'BO - Jakarta', 'Jakarta', 'Arka', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(3, '017C', 'KPUC - Malinau', 'Malinau', 'KPUC', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(4, '021C', 'SBI - Bogor', 'Bogor', 'SBI', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(5, '022C', 'GPK - Melak', 'Melak', 'GPK', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(6, '023C', 'BEK - Muara Lawa', 'Muara Lawa', 'BEK', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01'),
(7, 'APS', 'APS - Kariangau', 'Kariangau', 'Arka', 1, '2023-06-06 21:43:01', '2023-06-06 21:43:01');

CREATE TABLE `religions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `religion_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `religion_status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `religions` (`id`, `religion_name`, `religion_status`, `created_at`, `updated_at`) VALUES
(1, 'Islam', 1, '2023-06-06 21:40:04', '2023-06-06 21:40:04'),
(2, 'Kristen', 1, '2023-06-06 21:40:04', '2023-06-06 21:40:04'),
(3, 'Katolik', 1, '2023-06-06 21:40:04', '2023-06-06 21:40:04'),
(4, 'Hindu', 1, '2023-06-06 21:40:04', '2023-06-06 21:40:04'),
(5, 'Budha', 1, '2023-06-06 21:40:04', '2023-06-06 21:40:04'),
(6, 'Konghucu', 1, '2023-06-06 21:40:04', '2023-06-06 21:40:04');

CREATE TABLE `schools` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `education_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `taxidentifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_valid_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_status` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `level`, `user_status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@arka.co.id', '2023-06-06 21:40:04', '$2y$10$PXnsIKMZ9rWcPvG7emnU1u1gNyBpV6M9JKPlflpLJ88RgtdPhPdDW', 'superadmin', 1, '8D72KqFMHtyTcsuGpCjzonAnyCzEprwXnPA3KkoMg3mDQI1iRjQtlY9XADMK', '2023-06-06 21:40:04', '2023-06-06 21:40:04');


ALTER TABLE `additionaldatas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `additionaldatas_employee_id_foreign` (`employee_id`);

ALTER TABLE `administrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `administrations_employee_id_foreign` (`employee_id`),
  ADD KEY `administrations_project_id_foreign` (`project_id`),
  ADD KEY `administrations_position_id_foreign` (`position_id`),
  ADD KEY `administrations_user_id_foreign` (`user_id`);

ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_employee_id_foreign` (`employee_id`);

ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `educations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educations_employee_id_foreign` (`employee_id`);

ALTER TABLE `employeebanks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employeebanks_employee_id_foreign` (`employee_id`),
  ADD KEY `employeebanks_bank_id_foreign` (`bank_id`);

ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_identity_card_unique` (`identity_card`),
  ADD KEY `employees_religion_id_foreign` (`religion_id`),
  ADD KEY `employees_user_id_foreign` (`user_id`);

ALTER TABLE `emrgcalls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emrgcalls_employee_id_foreign` (`employee_id`);

ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

ALTER TABLE `families`
  ADD PRIMARY KEY (`id`),
  ADD KEY `families_employee_id_foreign` (`employee_id`);

ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `images_employee_id_foreign` (`employee_id`);

ALTER TABLE `insurances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `insurances_employee_id_foreign` (`employee_id`);

ALTER TABLE `jobexperiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobexperiences_employee_id_foreign` (`employee_id`);

ALTER TABLE `licenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `licenses_employee_id_foreign` (`employee_id`);

ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

ALTER TABLE `operableunits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operableunits_employee_id_foreign` (`employee_id`);

ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `positions_department_id_foreign` (`department_id`);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `religions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schools_employee_id_foreign` (`employee_id`);

ALTER TABLE `taxidentifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `taxidentifications_employee_id_foreign` (`employee_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);


ALTER TABLE `additionaldatas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `administrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `banks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `educations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `employeebanks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `emrgcalls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `families`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `insurances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `jobexperiences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `licenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

ALTER TABLE `operableunits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=279;

ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `religions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `schools`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `taxidentifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `additionaldatas`
  ADD CONSTRAINT `additionaldatas_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `administrations`
  ADD CONSTRAINT `administrations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `administrations_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`),
  ADD CONSTRAINT `administrations_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `administrations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `courses`
  ADD CONSTRAINT `courses_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `educations`
  ADD CONSTRAINT `educations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `employeebanks`
  ADD CONSTRAINT `employeebanks_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`),
  ADD CONSTRAINT `employeebanks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `employees`
  ADD CONSTRAINT `employees_religion_id_foreign` FOREIGN KEY (`religion_id`) REFERENCES `religions` (`id`),
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `emrgcalls`
  ADD CONSTRAINT `emrgcalls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `families`
  ADD CONSTRAINT `families_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `images`
  ADD CONSTRAINT `images_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `insurances`
  ADD CONSTRAINT `insurances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `jobexperiences`
  ADD CONSTRAINT `jobexperiences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `licenses`
  ADD CONSTRAINT `licenses_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `operableunits`
  ADD CONSTRAINT `operableunits_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `positions`
  ADD CONSTRAINT `positions_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

ALTER TABLE `schools`
  ADD CONSTRAINT `schools_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

ALTER TABLE `taxidentifications`
  ADD CONSTRAINT `taxidentifications_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
