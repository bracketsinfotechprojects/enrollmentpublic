-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2026 at 12:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `auztraining`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_datetime` datetime NOT NULL,
  `booked_date` datetime NOT NULL DEFAULT current_timestamp(),
  `booked_by` int(11) NOT NULL,
  `booked_by_name` varchar(255) DEFAULT NULL,
  `booking_comments` text DEFAULT NULL,
  `purpose_id` int(11) NOT NULL,
  `appointment_to_see` int(11) DEFAULT NULL COMMENT 'Staff member ID',
  `appointment_status` varchar(50) DEFAULT 'scheduled' COMMENT 'scheduled, completed, cancelled, no-show, missed',
  `meeting_happened` tinyint(1) DEFAULT 0,
  `attendee_type_id` int(11) NOT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `student_phone` varchar(20) DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `business_contact` varchar(255) DEFAULT NULL,
  `send_email` tinyint(1) DEFAULT 1,
  `staff_member_type` varchar(50) DEFAULT NULL COMMENT 'Admin, Trainers, Management',
  `staff_member_id` int(11) DEFAULT NULL,
  `meeting_type` varchar(50) NOT NULL COMMENT 'Online, Face to Face, Phone',
  `location_id` int(11) DEFAULT NULL,
  `platform_id` int(11) DEFAULT NULL,
  `online_meeting_link` text DEFAULT NULL,
  `timezone_state` varchar(100) DEFAULT NULL COMMENT 'State timezone (e.g., Melbourne, Adelaide)',
  `appointment_time_state` datetime DEFAULT NULL COMMENT 'Appointment time in state timezone',
  `appointment_time_adelaide` datetime DEFAULT NULL COMMENT 'Appointment time in Adelaide timezone',
  `appointment_time_india` datetime DEFAULT NULL COMMENT 'Appointment time in India timezone',
  `appointment_time_philippines` datetime DEFAULT NULL COMMENT 'Appointment time in Philippines timezone',
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `connected_enquiry_id` varchar(255) DEFAULT NULL COMMENT 'Link to student_enquiry.st_enquiry_id',
  `connected_enrolment_id` varchar(255) DEFAULT NULL COMMENT 'Link to student_enrolments.st_unique_id',
  `connected_counselling_id` int(11) DEFAULT NULL COMMENT 'Link to counseling_details.counsil_id',
  `appointment_notes` text DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `delete_status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `appointment_date`, `appointment_time`, `appointment_datetime`, `booked_date`, `booked_by`, `booked_by_name`, `booking_comments`, `purpose_id`, `appointment_to_see`, `appointment_status`, `meeting_happened`, `attendee_type_id`, `student_name`, `student_phone`, `student_email`, `business_name`, `business_contact`, `send_email`, `staff_member_type`, `staff_member_id`, `meeting_type`, `location_id`, `platform_id`, `online_meeting_link`, `timezone_state`, `appointment_time_state`, `appointment_time_adelaide`, `appointment_time_india`, `appointment_time_philippines`, `time_in`, `time_out`, `connected_enquiry_id`, `connected_enrolment_id`, `connected_counselling_id`, `appointment_notes`, `created_date`, `created_by`, `modified_date`, `modified_by`, `delete_status`) VALUES
(1, '2025-12-28', '05:22:00', '2025-12-28 05:22:00', '2025-12-26 12:42:17', 1, 'test1', 'this is testing', 2, 1, 'scheduled', 0, 2, '', '', '', 'testing', 'testing@gmail.com', 1, 'Trainers', NULL, 'Face to Face', 2, NULL, '', 'Sydney', '2025-12-28 05:22:00', '2025-12-28 05:22:00', '2025-12-28 05:22:00', '2025-12-28 05:22:00', NULL, NULL, NULL, NULL, NULL, 'testing', '2025-12-26 07:12:17', 1, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_attendee_types`
--

CREATE TABLE `appointment_attendee_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `type_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointment_attendee_types`
--

INSERT INTO `appointment_attendee_types` (`type_id`, `type_name`, `type_status`, `created_date`) VALUES
(1, 'Student', 0, '2025-12-26 07:10:25'),
(2, 'Business Purpose', 0, '2025-12-26 07:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_blocks`
--

CREATE TABLE `appointment_blocks` (
  `block_id` int(11) NOT NULL,
  `block_date` date NOT NULL,
  `block_start_time` time NOT NULL,
  `block_end_time` time NOT NULL,
  `block_reason` varchar(255) DEFAULT NULL,
  `staff_member_id` int(11) DEFAULT NULL COMMENT 'NULL means all staff',
  `block_status` tinyint(1) DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_locations`
--

CREATE TABLE `appointment_locations` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `location_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointment_locations`
--

INSERT INTO `appointment_locations` (`location_id`, `location_name`, `location_status`, `created_date`) VALUES
(1, 'Adelaide Office', 0, '2025-12-26 07:10:25'),
(2, 'Melbourne Office', 0, '2025-12-26 07:10:25'),
(3, 'Online', 0, '2025-12-26 07:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_platforms`
--

CREATE TABLE `appointment_platforms` (
  `platform_id` int(11) NOT NULL,
  `platform_name` varchar(255) NOT NULL,
  `platform_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointment_platforms`
--

INSERT INTO `appointment_platforms` (`platform_id`, `platform_name`, `platform_status`, `created_date`) VALUES
(1, 'Zoom', 0, '2025-12-26 07:10:25'),
(2, 'Google Meet', 0, '2025-12-26 07:10:25'),
(3, 'Outlook', 0, '2025-12-26 07:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_purposes`
--

CREATE TABLE `appointment_purposes` (
  `purpose_id` int(11) NOT NULL,
  `purpose_name` varchar(255) NOT NULL,
  `purpose_color` varchar(20) DEFAULT '#0bb197',
  `purpose_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointment_purposes`
--

INSERT INTO `appointment_purposes` (`purpose_id`, `purpose_name`, `purpose_color`, `purpose_status`, `created_date`) VALUES
(1, 'Counselling', '#0bb197', 0, '2025-12-26 07:10:25'),
(2, 'Complaints', '#ff3d60', 0, '2025-12-26 07:10:25'),
(3, 'Course Withdrawal', '#fcb92c', 0, '2025-12-26 07:10:25'),
(4, 'Enrolment', '#4aa3ff', 0, '2025-12-26 07:10:25'),
(5, 'Assignments', '#564ab1', 0, '2025-12-26 07:10:25'),
(6, 'Logbook Submission', '#0ac074', 0, '2025-12-26 07:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_reminders`
--

CREATE TABLE `appointment_reminders` (
  `reminder_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `reminder_sent_date` datetime DEFAULT NULL,
  `reminder_type` varchar(50) DEFAULT NULL COMMENT 'email, notification',
  `reminder_to` int(11) DEFAULT NULL COMMENT 'Staff member ID',
  `reminder_supervisor` int(11) DEFAULT NULL COMMENT 'Supervisor ID',
  `missed_meeting_notification` tinyint(1) DEFAULT 0,
  `missed_meeting_sent_date` datetime DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment`
--

CREATE TABLE `assessment` (
  `assessment_id` int(11) NOT NULL,
  `assessment_unique_id` varchar(20) NOT NULL,
  `assessment_name` varchar(50) NOT NULL,
  `marks` int(11) NOT NULL,
  `passing_marks` int(11) DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment`
--

INSERT INTO `assessment` (`assessment_id`, `assessment_unique_id`, `assessment_name`, `marks`, `passing_marks`, `duration`, `created_date`, `updated_date`, `status`) VALUES
(8, 'ASM8122', 'technical assessment', 50, 30, 30, '2026-05-15 02:15:16', '2026-05-15 02:15:16', 0),
(9, '', 'new test ', 100, 60, 60, '2026-05-15 02:15:11', '2026-05-15 02:15:11', 1),
(10, '', 'Test Assessment', 20, 10, 10, '2026-05-19 04:56:03', '2026-05-19 04:56:03', 0);

-- --------------------------------------------------------

--
-- Table structure for table `assessment_answers`
--

CREATE TABLE `assessment_answers` (
  `answer_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `student_enrol_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_option` text DEFAULT NULL COMMENT 'For choice questions: 1-4, multiple: JSON array, text: answer text',
  `is_correct` tinyint(1) DEFAULT 0 COMMENT '0=Incorrect, 1=Correct',
  `marks_obtained` int(11) DEFAULT 0,
  `answered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_answers`
--

INSERT INTO `assessment_answers` (`answer_id`, `assessment_id`, `student_enrol_id`, `question_id`, `answer_option`, `is_correct`, `marks_obtained`, `answered_at`) VALUES
(16, 24, 6, 19, '1', 1, 10, '2026-04-21 12:57:39'),
(17, 24, 6, 20, 'True', 0, 0, '2026-04-21 12:57:51'),
(18, 24, 6, 21, '[\"2\",\"3\",\"4\"]', 1, 10, '2026-04-21 12:57:58'),
(19, 24, 6, 22, 'test', 0, 0, '2026-04-21 12:58:12'),
(20, 1, 6, 29, '1', 1, 5, '2026-04-23 18:25:37'),
(21, 1, 6, 30, '3', 1, 5, '2026-04-23 18:25:41'),
(22, 1, 6, 31, '2', 1, 5, '2026-04-23 18:25:46'),
(23, 1, 6, 32, '4', 1, 5, '2026-04-23 18:25:50'),
(24, 6, 6, 35, '4', 0, 0, '2026-05-01 09:27:06'),
(73, 9, 39, 19, '1', 1, 5, '2026-05-19 08:55:32'),
(74, 9, 39, 20, 'True', 0, 0, '2026-05-19 08:55:37'),
(75, 9, 39, 21, '[\"1\",\"2\",\"3\"]', 0, 0, '2026-05-19 08:55:44'),
(76, 9, 39, 24, '1', 1, 5, '2026-05-19 08:55:48'),
(77, 9, 39, 25, '3', 1, 10, '2026-05-19 08:55:51'),
(78, 9, 39, 26, '2', 1, 10, '2026-05-19 08:55:55'),
(79, 9, 39, 27, '4', 1, 10, '2026-05-19 08:56:00'),
(80, 9, 39, 46, 'False', 0, 0, '2026-05-19 08:56:04'),
(81, 9, 39, 48, '3', 1, 10, '2026-05-19 08:56:07'),
(82, 9, 39, 49, '[\"2\",\"3\",\"4\"]', 0, 0, '2026-05-19 08:56:15'),
(83, 9, 39, 50, 'True', 0, 0, '2026-05-19 08:56:21'),
(84, 9, 39, 52, '3', 1, 10, '2026-05-19 08:56:24'),
(85, 10, 39, 29, '1', 0, 0, '2026-05-22 23:54:42'),
(86, 10, 39, 30, '3', 0, 0, '2026-05-22 23:54:46'),
(87, 10, 39, 56, '[]', 0, 0, '2026-05-23 00:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_assignments`
--

CREATE TABLE `assessment_assignments` (
  `id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `student_enrol_id` int(11) NOT NULL,
  `assigned_date` datetime NOT NULL DEFAULT current_timestamp(),
  `assessment_count` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `assign_status` varchar(11) DEFAULT 'active',
  `passing_status` enum('','pass','manual_pass') NOT NULL DEFAULT '',
  `attempt_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `feedback` text DEFAULT NULL,
  `enrolment_status` int(11) DEFAULT 0,
  `manual_pass_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_assignments`
--

INSERT INTO `assessment_assignments` (`id`, `assessment_id`, `student_enrol_id`, `assigned_date`, `assessment_count`, `status`, `created_at`, `assign_status`, `passing_status`, `attempt_count`, `feedback`, `enrolment_status`, `manual_pass_reason`) VALUES
(2, 24, 1, '2026-04-20 20:51:40', 0, 0, '2026-05-14 18:09:18', 'active', '', 0, NULL, 0, NULL),
(3, 24, 6, '2026-04-20 22:22:57', 0, 0, '2026-05-14 18:09:18', 'active', '', 0, NULL, 0, NULL),
(4, 26, 6, '2026-04-21 12:55:49', 0, 0, '2026-05-14 18:09:18', 'active', '', 0, NULL, 0, NULL),
(5, 26, 1, '2026-04-21 12:55:49', 0, 0, '2026-05-14 18:09:18', 'active', '', 0, NULL, 0, NULL),
(6, 1, 6, '2026-04-23 18:21:00', 0, 0, '2026-05-14 18:09:18', 'active', '', 0, NULL, 0, NULL),
(7, 3, 6, '2026-04-24 14:45:15', 0, 0, '2026-05-14 18:09:18', 'active', '', 0, NULL, 0, NULL),
(8, 6, 6, '2026-05-01 09:24:07', 0, 0, '2026-05-14 18:09:18', 'active', '', 0, NULL, 0, NULL),
(14, 8, 12, '2026-05-14 19:44:54', 0, 0, '2026-05-14 19:44:54', 'active', '', 0, NULL, 0, NULL),
(20, 9, 39, '2026-05-18 23:54:11', 3, 1, '2026-05-18 23:54:11', 'deleted', 'pass', 0, NULL, 1, NULL),
(21, 9, 39, '2026-05-19 08:21:16', 3, 1, '2026-05-19 08:21:16', 'deleted', '', 1, 'test for feedback', 0, NULL),
(22, 9, 39, '2026-05-19 08:55:19', 3, 1, '2026-05-19 08:55:19', 'active', '', 2, 'wrong ans', 0, NULL),
(25, 10, 39, '2026-05-22 23:52:15', 0, 1, '2026-05-22 23:52:15', 'active', '', 0, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `assessment_questions`
--

CREATE TABLE `assessment_questions` (
  `question_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` tinyint(1) DEFAULT 1 COMMENT '1=Single Choice, 2=True/False, 3=Multiple Choice, 4=Text Answer, 5=Image Based\r\n',
  `option_1` varchar(255) DEFAULT NULL,
  `option_2` varchar(255) DEFAULT NULL,
  `option_3` varchar(255) DEFAULT NULL,
  `option_4` varchar(255) DEFAULT NULL,
  `correct_option` int(11) DEFAULT NULL,
  `correct_options_multi` text DEFAULT NULL COMMENT 'JSON array for multiple correct options',
  `marks` int(11) NOT NULL DEFAULT 1,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_questions`
--

INSERT INTO `assessment_questions` (`question_id`, `assessment_id`, `question_text`, `question_type`, `option_1`, `option_2`, `option_3`, `option_4`, `correct_option`, `correct_options_multi`, `marks`, `status`, `created_date`) VALUES
(19, 9, 'Which of the following is the brain of the Computer?', 1, ' Central Processing Unit', 'Memory', ' Arithmetic and Logic unit', ' Control unit', 1, NULL, 5, 0, '2026-04-20 20:40:37'),
(20, 9, 'Bit is the smallest unit of data in a computer.', 2, NULL, NULL, NULL, NULL, 1, NULL, 5, 0, '2026-04-20 20:41:26'),
(21, 9, 'Which of the following is a type of computer code?\n', 3, 'EDIC', 'ASCII', 'BCD', 'EBCDIC', NULL, '[\"2\",\"3\",\"4\"]', 5, 0, '2026-04-20 20:42:50'),
(24, 9, 'What is the full form of RAM?', 1, 'Random Access Memory', 'Read Access Memory', 'Readily Available Memory', 'Random Available Memory', 1, NULL, 5, 0, '2026-04-21 12:07:50'),
(25, 9, 'Which device is used to input data into a computer?\n', 1, 'Printer', 'Monitor', 'Keyboard', 'Speaker', 3, NULL, 10, 0, '2026-04-21 12:08:40'),
(26, 9, 'Which of the following is an example of system software?\n', 1, 'Microsoft Excel.', 'Windows 10', 'Google Chrome', 'Adobe Reader', 2, NULL, 10, 0, '2026-04-21 12:09:36'),
(27, 9, 'Which of the following is NOT a programming language', 1, 'Python', 'HTML', 'Java', 'Microsoft Word', 4, NULL, 10, 0, '2026-04-21 12:30:44'),
(28, 26, 'Which part of the computer is responsible for executing instructions?', 1, 'Hard Disk', 'RAM', 'CPU', 'Motherboard', 3, NULL, 1, 0, '2026-04-21 12:48:24'),
(29, 10, 'What is the full form of RAM?', 1, 'Random Access Memory', 'Read Access Memory', 'Readily Available Memory', 'Random Available Memory', 1, NULL, 5, 0, '2026-04-23 17:40:56'),
(30, 10, 'Which device is used to input data into a computer?', 1, 'Printer', 'Monitor', 'Keyboard', 'Speaker', 3, NULL, 5, 0, '2026-04-23 17:57:42'),
(31, 10, 'Which of the following is an example of system software?', 1, 'Microsoft Excel.', 'Windows 10', 'Adobe Reader', 'Google Chrome', 2, NULL, 5, 0, '2026-04-23 17:59:28'),
(32, 1, 'Which of the following is NOT a programming language?', 1, 'Python', 'HTML', 'Java', 'Microsoft Word', 4, NULL, 5, 0, '2026-04-23 18:00:45'),
(34, 4, 'test 123', 2, NULL, NULL, NULL, NULL, 2, NULL, 1, 0, '2026-04-24 14:42:44'),
(35, 6, 'What does GUI stand for?', 1, 'Graphical User Interface', 'General User Interface', 'Guided User Interface', 'Graphical User Interaction', 1, NULL, 10, 0, '2026-04-28 21:57:59'),
(36, 6, 'Which of the following is a volatile memory?', 1, 'SSD', 'Hard Disk', 'RAM', 'ROM', 3, NULL, 10, 0, '2026-04-28 21:58:54'),
(37, 6, 'What is the main function of an operating system?', 1, 'Perform calculations', 'Manage hardware and software resources', 'Browse the internet', 'Create documents', 2, NULL, 10, 0, '2026-04-28 22:00:13'),
(38, 6, 'Which storage device has the largest capacity?', 1, 'DVD', 'USB Flash Drive', 'SSD', 'Hard Disk Drive', 4, NULL, 10, 0, '2026-04-28 22:13:42'),
(39, 6, 'What does URL stand for?', 1, 'Uniform Resource Locator', 'Uniform Resource Link', 'Universal Resource Locator', 'Universal Resource Link', 1, NULL, 10, 0, '2026-04-28 22:14:43'),
(40, 6, 'What is the purpose of a firewall in a computer network?', 1, 'To speed up the internet', 'To protect against unauthorized access', 'To manage network traffic', 'To boost Wi-Fi signal', 2, NULL, 10, 0, '2026-04-28 22:16:04'),
(41, 6, 'What type of software is used to surf the web?', 1, 'Operating system', 'Web browser', 'Database', 'Text editor', 2, NULL, 10, 0, '2026-04-28 22:17:01'),
(42, 6, 'What does \'HTTP\' stand for?', 1, 'HyperText Transmission Protocol', 'HyperText Transfer Protocol', 'HyperTransfer Text Protocol', 'Hyper Transmission Transfer Protocol', 2, NULL, 10, 0, '2026-04-28 22:18:08'),
(43, 6, 'What is the purpose of the ALU in a computer?', 1, 'To manage memory', 'To perform arithmetic and logic operations', 'To control input and output', 'To manage storage', 2, NULL, 10, 0, '2026-04-28 22:19:13'),
(44, 6, 'Which file format is used for images?', 1, '.docx', '.xlsx', '.jpg', '.pptx', 3, NULL, 10, 0, '2026-04-28 22:20:12'),
(45, 3, 'test', 3, '1', '3', '2', '5', NULL, '[\"1\",\"2\",\"3\"]', 1, 0, '2026-05-01 09:15:55'),
(46, 9, 'html is programming language?', 2, NULL, NULL, NULL, NULL, 0, NULL, 10, 0, '2026-05-01 16:38:44'),
(47, 7, 'test', 3, '1', 'CPU', '3', 'Software', NULL, '[\"1\",\"3\"]', 1, 0, '2026-05-02 16:49:10'),
(48, 9, 'test', 1, '1', '2', '3', '4', 3, NULL, 10, 0, '2026-05-15 07:14:22'),
(49, 9, 'true or false', 3, 'Mouse', 'ASCII', 'BCD', 'Software', NULL, '[\"1\",\"2\"]', 10, 0, '2026-05-15 07:30:17'),
(50, 9, 'test', 2, NULL, NULL, NULL, NULL, 1, NULL, 10, 0, '2026-05-15 07:42:52'),
(52, 9, 'hdfsud', 1, 'dfsdgfg', 'gfg', 'Java', 'Microsoft Word', 3, NULL, 10, 0, '2026-05-15 08:13:06'),
(53, 8, 'test by monika', 2, NULL, NULL, NULL, NULL, 1, NULL, 1, 0, '2026-05-18 22:11:11'),
(55, 10, 'is it test assessment?', 2, NULL, NULL, NULL, NULL, 1, NULL, 1, 0, '2026-05-19 10:46:21'),
(56, 10, 'select name of month', 3, 'may', 'october', 'july', 'twelve', NULL, '[\"1\",\"2\",\"3\"]', 1, 0, '2026-05-19 10:49:37'),
(57, 10, 'course name ', 4, NULL, NULL, NULL, NULL, NULL, 'Mental Health', 1, 0, '2026-05-19 10:50:41'),
(58, 10, 'test', 1, '1', '2', '3', '4', 1, NULL, 1, 0, '2026-05-19 11:00:51'),
(60, 8, 'testzhdfbdxkhfg', 5, '1', '2', '3', '4', 3, 'qimg_1779473839_2618.jpg', 2, 0, '2026-05-22 23:47:19'),
(61, 10, 'Which digital payment service provider is represented by the QR code shown in the image?', 5, 'PhonePe', 'Google Pay (GPay)', 'Paytm', 'Amazon Pay', 2, 'qimg_1779474100_3322.jpg', 1, 0, '2026-05-22 23:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_submissions`
--

CREATE TABLE `assessment_submissions` (
  `submission_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `student_enrol_id` int(11) NOT NULL,
  `attempted_questions` text DEFAULT NULL,
  `pending_questions` text DEFAULT NULL,
  `total_marks` int(11) DEFAULT 0,
  `obtained_marks` int(11) DEFAULT 0,
  `percentage` decimal(5,2) DEFAULT 0.00,
  `status` tinyint(1) DEFAULT 0 COMMENT '0=In Progress, 1=Submitted, 2=Graded',
  `started_at` datetime DEFAULT current_timestamp(),
  `submitted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_submissions`
--

INSERT INTO `assessment_submissions` (`submission_id`, `assessment_id`, `student_enrol_id`, `attempted_questions`, `pending_questions`, `total_marks`, `obtained_marks`, `percentage`, `status`, `started_at`, `submitted_at`) VALUES
(3, 24, 6, '4', '1', 50, 20, 40.00, 1, '2026-04-21 12:58:22', NULL),
(4, 1, 6, '4', '0', 20, 20, 100.00, 1, '2026-04-23 18:25:52', NULL),
(5, 3, 6, '0', '0', 0, 0, 0.00, 1, '2026-04-24 15:27:02', NULL),
(11, 9, 39, '12', '0', 100, 60, 60.00, 1, '2026-05-19 08:56:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `counseling_details`
--

CREATE TABLE `counseling_details` (
  `counsil_id` int(11) NOT NULL,
  `st_enquiry_id` varchar(255) DEFAULT NULL,
  `counsil_mem_name` varchar(255) DEFAULT NULL,
  `counsil_vaccine_status` tinyint(1) DEFAULT NULL,
  `counsil_job_nature` varchar(255) DEFAULT NULL,
  `counsil_module_result` varchar(100) DEFAULT NULL,
  `counsil_timing` timestamp NULL DEFAULT NULL,
  `counsil_end_time` timestamp NULL DEFAULT NULL,
  `counsil_pref_comments` text DEFAULT NULL,
  `counsil_eng_rate` varchar(10) DEFAULT NULL,
  `counsil_migration_test` tinyint(1) DEFAULT NULL,
  `counsil_overall_result` varchar(100) DEFAULT NULL,
  `counsil_course` varchar(255) DEFAULT NULL,
  `counsil_university` varchar(255) DEFAULT NULL,
  `counsil_qualification` varchar(255) DEFAULT NULL,
  `counsil_type` tinyint(1) DEFAULT NULL,
  `counsil_aus_stay_time` varchar(255) DEFAULT NULL,
  `counsil_visa_condition` tinyint(1) DEFAULT NULL,
  `counsil_education` varchar(255) DEFAULT NULL,
  `counsil_aus_study_status` tinyint(1) DEFAULT NULL,
  `counsil_work_status` tinyint(1) DEFAULT NULL,
  `counsil_remarks` text DEFAULT NULL,
  `counsil_created_date` datetime DEFAULT current_timestamp(),
  `counsil_createdby` int(11) DEFAULT NULL,
  `counsil_modified_date` date DEFAULT NULL,
  `counsil_modified_by` int(11) DEFAULT NULL,
  `counsil_delete_note` varchar(255) DEFAULT NULL,
  `counsil_enquiry_status` tinyint(4) NOT NULL DEFAULT 0,
  `student_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `counseling_details`
--

INSERT INTO `counseling_details` (`counsil_id`, `st_enquiry_id`, `counsil_mem_name`, `counsil_vaccine_status`, `counsil_job_nature`, `counsil_module_result`, `counsil_timing`, `counsil_end_time`, `counsil_pref_comments`, `counsil_eng_rate`, `counsil_migration_test`, `counsil_overall_result`, `counsil_course`, `counsil_university`, `counsil_qualification`, `counsil_type`, `counsil_aus_stay_time`, `counsil_visa_condition`, `counsil_education`, `counsil_aus_study_status`, `counsil_work_status`, `counsil_remarks`, `counsil_created_date`, `counsil_createdby`, `counsil_modified_date`, `counsil_modified_by`, `counsil_delete_note`, `counsil_enquiry_status`, `student_user_id`) VALUES
(1, 'EQ00002', 'surya', 1, '', '', '2023-10-02 14:30:00', '2023-10-02 16:30:00', 'nothing', '2', 2, '', '', '', 'nothing', 1, '2 years', 1, 'nothing', 2, 2, '[\"1\"]', '2023-10-01 20:02:58', 1, '2023-10-10', 1, NULL, 0, NULL),
(2, 'EQ00004', 'test name', 2, '', '', '2023-09-08 18:30:00', '2023-09-08 20:30:00', '', '2', 2, '', '', '', 'test adge', 1, '2 years', 1, 'name edic', 2, 2, '', '2023-10-02 19:58:16', 1, '2023-10-10', 1, NULL, 0, NULL),
(3, 'EQ00003', 'fdvfcbf', 1, 'vfdgdf', ' cbfbf', '2023-10-06 00:19:00', '2023-10-06 01:41:00', 'vvd', 'regrgr', 1, ' fcbcbc', ' vbvgbg', 'bfff', 'bfbgf', 1, 'bcfbg', 1, 'cbvc b', 1, 1, '[\"1\",\"2\",\"3\"]', '2023-10-03 12:50:42', 1, '2023-10-07', 1, NULL, 0, NULL),
(4, 'EQ00010', 'Krishna', 1, 'Yes', '5.2,6.2', '2023-10-05 13:00:00', '2023-10-05 14:30:00', 'Fast Track', '8', 1, '8.5', 'Aged Care', 'OXFORD', '1', 1, '1 Year', 2, 'BTECH', 1, 2, '[\"2\",\"3\",\"13\"]', '2023-10-04 11:01:47', 1, '2023-10-10', 1, NULL, 0, NULL),
(5, 'EQ00001', 'krishna', 1, 'YES', 'GOOD', '2025-11-01 18:16:00', '2025-10-30 18:16:00', '', '8', 1, '8', 'IS', 'UNISA', '1 YEAR', 1, '2 months', 3, 'BE', 1, 1, '[\"10\"]', '2025-10-29 07:36:45', 4, NULL, NULL, NULL, 0, NULL),
(6, 'EQ00016', 'Sulab', NULL, NULL, NULL, '2026-05-14 04:43:11', NULL, NULL, NULL, NULL, NULL, '[\"3\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-14 10:13:11', 5, NULL, NULL, NULL, 0, NULL),
(7, 'EQ00025', 'Monika Dighe', NULL, NULL, NULL, '2026-05-14 17:32:55', NULL, NULL, NULL, NULL, NULL, '[\"1\",\"2\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-14 23:02:55', 5, NULL, NULL, NULL, 0, 39),
(9, 'EQ00026', 'Rakesh', NULL, NULL, NULL, '2026-05-19 09:00:00', NULL, NULL, NULL, NULL, NULL, '[\"1\",\"2\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-19 14:30:00', 5, NULL, NULL, NULL, 0, 40);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_sname` varchar(255) NOT NULL,
  `course_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `course_sname`, `course_status`, `created_date`) VALUES
(1, 'CHC33021', 'Certificate III in Individual Support (Ageing)', 0, '2026-05-05 11:01:17'),
(2, 'CHC33021', 'Certificate III in Individual Support (Disability)', 0, '2026-05-05 11:01:17'),
(3, 'CHC33021', 'Certificate III in Individual Support (Ageing & Disability)', 0, '2026-05-05 11:01:17'),
(4, 'CHC43015', 'Certificate IV in Ageing Support', 0, '2026-05-05 11:01:17'),
(5, 'CHC43121', 'Certificate IV in Disability', 0, '2026-05-05 11:01:17'),
(6, 'CHC32021', 'Certificate III in Community Services', 0, '2026-05-05 11:01:17'),
(7, 'CHC42021', 'Certificate IV in Community Services', 0, '2026-05-05 11:01:17'),
(8, 'CHC52021', 'Diploma of Community Services', 0, '2026-05-05 11:01:17'),
(9, 'CHC43315', 'Certificate IV in Mental Health', 0, '2026-05-05 11:01:17'),
(10, 'CHC53315', 'Diploma of Mental Health', 0, '2026-05-05 11:01:17'),
(11, 'HLT33021', 'Certificate III in Allied Health Assistance', 0, '2026-05-05 11:01:17'),
(12, 'CHC43415', 'Certificate IV in Leisure and Health', 0, '2026-05-05 11:01:17'),
(13, 'MHT-R', 'Manual Handling Training (Refresher)', 0, '2026-05-05 11:01:17'),
(14, 'MHT-F', 'Manual Handling Training (Full)', 0, '2026-05-05 11:01:17'),
(15, 'Med-R', 'Medication Training (Refresher)', 0, '2026-05-05 11:01:17'),
(16, 'Med-F', 'Medication Training (Full)', 0, '2026-05-05 11:01:17'),
(17, 'Insulin', 'Safely Injecting Insulin Training', 0, '2026-05-05 11:01:17');

-- --------------------------------------------------------

--
-- Table structure for table `course_cancellations`
--

CREATE TABLE `course_cancellations` (
  `cancellation_id` int(11) NOT NULL,
  `cancellation_unique_id` varchar(255) DEFAULT NULL,
  `title` varchar(10) DEFAULT NULL,
  `family_name` varchar(255) NOT NULL,
  `given_names` varchar(255) NOT NULL,
  `residential_address` varchar(500) NOT NULL,
  `post_code` varchar(10) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `course_code` varchar(255) DEFAULT NULL,
  `course_title` varchar(500) DEFAULT NULL,
  `date_of_enrolment` date DEFAULT NULL,
  `reason_for_cancellation` varchar(255) DEFAULT NULL,
  `reason_other_details` text DEFAULT NULL,
  `cancellation_effective_date` date DEFAULT NULL,
  `cooling_off_period` varchar(10) DEFAULT NULL,
  `account_type` varchar(20) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bsb` varchar(10) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `refund_to_be_issued` varchar(10) DEFAULT NULL,
  `refund_approved_by` varchar(255) DEFAULT NULL,
  `refund_approved_date` date DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT NULL,
  `date_forwarded_to_finance` date DEFAULT NULL,
  `finance_initial` varchar(255) DEFAULT NULL,
  `office_comments` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_cancellations`
--

INSERT INTO `course_cancellations` (`cancellation_id`, `cancellation_unique_id`, `title`, `family_name`, `given_names`, `residential_address`, `post_code`, `contact_number`, `email`, `date_of_birth`, `gender`, `course_code`, `course_title`, `date_of_enrolment`, `reason_for_cancellation`, `reason_other_details`, `cancellation_effective_date`, `cooling_off_period`, `account_type`, `bank_name`, `bsb`, `account_number`, `full_name`, `signature`, `submission_date`, `refund_to_be_issued`, `refund_approved_by`, `refund_approved_date`, `refund_amount`, `date_forwarded_to_finance`, `finance_initial`, `office_comments`, `status`, `created_date`, `created_by`, `modified_date`, `modified_by`) VALUES
(1, 'CC00001', 'Ms', 'sai', 'satya', 'Agraharam', '535558', '8309603262', 'saisatya51@gmail.com', '1998-06-10', 'M', 'DM-3434', 'Title', '2026-01-29', 'Personal difficulties', NULL, '2026-01-16', 'Yes', NULL, NULL, NULL, NULL, 'Full Name *', 'Signature *', '2026-01-21', 'Yes', 'test', '2026-01-01', 4345.00, '2026-01-01', 'test', NULL, 0, '2026-01-25 08:22:37', NULL, '2026-01-25', 1),
(2, 'CC00002', 'Ms', 'sai', 'satya', 'Agraharam', '535558', '8309603262', 'saisatya51@gmail.com', '1998-06-10', 'M', 'DM-3434', 'Title', '1998-06-10', 'Personal difficulties', NULL, '2026-01-01', 'Yes', NULL, NULL, NULL, NULL, 'Full Name *', 'Signature ', '2026-01-21', 'Yes', 'test', '2026-01-01', 345345.00, '2026-01-01', 'test', NULL, 0, '2026-01-25 08:25:55', NULL, '2026-01-25', 1),
(3, 'CC00003', 'Mr', 'sai', 'satya', 'Agraharam', '535558', '8309603262', 'saisatya51@gmail.com', '1998-06-10', 'M', 'DM-3434', 'Title', '2026-01-01', 'Transfer to another RTO', NULL, '2026-01-01', 'Yes', NULL, NULL, NULL, NULL, 'Full Name *', 'Signature *', '2026-01-20', '', 'tet', '2026-01-01', 34.00, '2026-01-01', 'test', 'test', 0, '2026-01-25 08:28:32', NULL, '2026-01-25', 1),
(4, 'CC00004', 'Mr', 'sai', 'satya', 'Agraharam', '535558', 'test', 'saisatya51@gmail.com', '1998-06-10', 'M', 'DM-3434', 'Title', '1998-06-10', 'Increased workload', NULL, '2026-01-20', 'Yes', NULL, NULL, NULL, NULL, 'Name', 'Signature ', '0000-00-00', '', 'tet', '0026-01-01', 345345.00, '2026-01-01', 'test', NULL, 0, '2026-01-25 10:33:00', NULL, '2026-01-25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `course_extensions`
--

CREATE TABLE `course_extensions` (
  `extension_id` int(11) NOT NULL,
  `extension_unique_id` varchar(255) DEFAULT NULL,
  `title` varchar(10) DEFAULT NULL,
  `family_name` varchar(255) NOT NULL,
  `given_names` varchar(255) NOT NULL,
  `residential_address` varchar(500) NOT NULL,
  `post_code` varchar(10) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `course_code` varchar(255) DEFAULT NULL,
  `course_title` varchar(500) DEFAULT NULL,
  `enrolment_date` date DEFAULT NULL,
  `reason_for_extension` varchar(255) DEFAULT NULL,
  `reason_other_details` text DEFAULT NULL,
  `extension_duration` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `extension_approved` varchar(10) DEFAULT NULL,
  `application_approved_by` varchar(255) DEFAULT NULL,
  `approval_initial` varchar(255) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `rollover_fee` decimal(10,2) DEFAULT NULL,
  `office_comments` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_extensions`
--

INSERT INTO `course_extensions` (`extension_id`, `extension_unique_id`, `title`, `family_name`, `given_names`, `residential_address`, `post_code`, `contact_number`, `email`, `course_code`, `course_title`, `enrolment_date`, `reason_for_extension`, `reason_other_details`, `extension_duration`, `full_name`, `signature`, `submission_date`, `extension_approved`, `application_approved_by`, `approval_initial`, `approval_date`, `rollover_fee`, `office_comments`, `status`, `created_date`, `created_by`, `modified_date`, `modified_by`) VALUES
(1, 'CE00001', 'Ms', 'Family Name *', 'Given Names *', 'Residential Address *', '535558', '8309603262', 'saisatya51@gmail.com', 'DM-3434', 'Title', '1998-01-10', 'Bereavement', NULL, NULL, 'Full Name *', 'Signature ', '2026-01-01', 'Y', 'test', 'test', '2026-01-01', 344.00, NULL, 0, '2026-01-25 08:30:46', NULL, '2026-01-25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `crm_email_log`
--

CREATE TABLE `crm_email_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `send_status` varchar(16) NOT NULL DEFAULT 'sent',
  `error_message` text DEFAULT NULL,
  `recipient_to` varchar(512) NOT NULL,
  `subject` varchar(998) NOT NULL,
  `body_html` mediumtext DEFAULT NULL,
  `email_category` varchar(64) NOT NULL DEFAULT 'general',
  `sent_by_user_id` int(11) DEFAULT NULL,
  `sent_by_user_name` varchar(128) DEFAULT NULL,
  `st_enquiry_id` varchar(32) DEFAULT NULL,
  `st_id` int(11) DEFAULT NULL,
  `meta_json` text DEFAULT NULL,
  `request_uri` varchar(512) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `crm_email_log`
--

INSERT INTO `crm_email_log` (`id`, `created_at`, `send_status`, `error_message`, `recipient_to`, `subject`, `body_html`, `email_category`, `sent_by_user_id`, `sent_by_user_name`, `st_enquiry_id`, `st_id`, `meta_json`, `request_uri`, `ip_address`) VALUES
(1, '2026-05-05 16:32:35', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">390553</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(2, '2026-05-05 16:37:39', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">845708</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(3, '2026-05-13 16:04:07', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">291970</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(4, '2026-05-13 17:36:44', 'failed', 'Connection could not be established with host \"ssl://smtp.hostinger.com:465\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp.hostinger.com failed: No such host is known. ', 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your student login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">858187</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"student\"}', '/new/auztraining/includes/datacontrol', '::1'),
(5, '2026-05-13 17:38:57', 'failed', 'Connection could not be established with host \"ssl://smtp.hostinger.com:465\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp.hostinger.com failed: No such host is known. ', 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your student login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">135328</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"student\"}', '/new/auztraining/includes/datacontrol', '::1'),
(6, '2026-05-13 17:51:13', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">847591</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(7, '2026-05-13 17:53:33', 'failed', 'Failed to authenticate on SMTP server with username \"noreply@nationalcollege.edu.au\" using the following authenticators: \"LOGIN\", \"PLAIN\". Authenticator \"LOGIN\" returned \"Expected response code \"334\" but got code \"421\", with message \"421 4.4.2 smtp.hostinger.com Error: timeout exceeded\".\". Authenticator \"PLAIN\" returned \"Expected response code \"235\" but got empty code.\".', 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your student login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">946838</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"student\"}', '/new/auztraining/includes/datacontrol', '::1'),
(8, '2026-05-13 17:58:32', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your student login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">280031</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"student\"}', '/new/auztraining/includes/datacontrol', '::1'),
(9, '2026-05-13 19:56:56', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">194294</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(10, '2026-05-14 06:50:09', 'failed', 'Connection could not be established with host \"ssl://smtp.hostinger.com:465\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp.hostinger.com failed: No such host is known. ', 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">633667</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(11, '2026-05-14 06:51:07', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">338801</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(12, '2026-05-14 07:00:03', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your student login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">220454</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"student\"}', '/new/auztraining/includes/datacontrol', '::1'),
(13, '2026-05-14 09:53:28', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">218692</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(14, '2026-05-14 12:34:07', 'failed', 'Connection could not be established with host \"ssl://smtp.hostinger.com:465\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp.hostinger.com failed: No such host is known. ', 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">123275</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(15, '2026-05-14 12:34:55', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">434798</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(16, '2026-05-14 17:48:43', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your student login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">466318</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"student\"}', '/new/auztraining/includes/datacontrol', '::1'),
(17, '2026-05-15 06:29:02', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">990049</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(18, '2026-05-15 08:05:28', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your student login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">674598</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"student\"}', '/new/auztraining/includes/datacontrol', '::1'),
(19, '2026-05-15 16:08:39', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your staff/admin login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">920994</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"staff\\/admin\"}', '/new/auztraining/includes/datacontrol', '::1'),
(20, '2026-05-15 17:27:36', 'sent', NULL, 'monikashinde8421@gmail.com', 'Your Login OTP - National College Australia', '<div style=\"font-family:Segoe UI,Arial,sans-serif;max-width:560px;margin:auto;\"><h3 style=\"margin:0 0 12px;\">Login verification</h3><p style=\"margin:0 0 10px;\">Use this OTP to continue your student login.</p><p style=\"font-size:26px;letter-spacing:3px;font-weight:700;margin:12px 0;\">236605</p><p style=\"margin:0;color:#666;\">This OTP is valid for 10 minutes.</p><p style=\"margin-top:14px;color:#666;\">If this was not you, please ignore this email.</p></div>', 'login_otp', NULL, NULL, NULL, NULL, '{\"context\":\"student\"}', '/new/auztraining/includes/datacontrol', '::1'),
(21, '2026-05-18 23:36:13', 'sent', NULL, 'monikashinde8421@gmail.com', 'Action Required: missing doc', '\r\n<p>Dear Monika  dighe,</p>\r\n<p>We have raised a query regarding your enrolment record (<strong>2026CERTIFICATEIVINMENTALHEALTH0002</strong>).</p>\r\n<p><strong>Subject:</strong> missing doc</p>\r\n<p><strong>Details:</strong><br>missing doc</p>\r\n<p>Kindly log in to the <strong>Student Panel</strong> and review your enrolment details. If you have any questions or need to provide additional information, please contact us.</p>\r\n<br>\r\n<p>Regards,<br><strong>National College Australia</strong><br>RTO: 91000</p>\r\n', 'general', 5, 'ujala', NULL, NULL, NULL, '/new/auztraining/includes/datacontrol', '::1'),
(22, '2026-05-19 09:58:09', 'sent', NULL, 'monikashinde8421@gmail.com', 'Action Required: doc missing', '\r\n<p>Dear Monika  dighe,</p>\r\n<p>We have raised a query regarding your enrolment record (<strong>2026CERTIFICATEIVINMENTALHEALTH0002</strong>).</p>\r\n<p><strong>Subject:</strong> doc missing</p>\r\n<p><strong>Details:</strong><br>test for doc</p>\r\n<p>Kindly log in to the <strong>Student Panel</strong> and review your enrolment details. If you have any questions or need to provide additional information, please contact us.</p>\r\n<br>\r\n<p>Regards,<br><strong>National College Australia</strong><br>RTO: 91000</p>\r\n', 'general', 5, 'ujala', NULL, NULL, NULL, '/new/auztraining/includes/datacontrol', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `document_shortcode` varchar(255) NOT NULL,
  `document_status` tinyint(1) NOT NULL DEFAULT 0,
  `document_created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`document_id`, `document_name`, `document_shortcode`, `document_status`, `document_created_date`) VALUES
(1, 'Date of  Birth', 'dob', 0, '2023-08-27 11:50:16'),
(2, 'Address', 'address', 0, '2023-08-27 11:50:16');

-- --------------------------------------------------------

--
-- Table structure for table `enquiry_forms`
--

CREATE TABLE `enquiry_forms` (
  `enq_form_id` int(11) NOT NULL,
  `enq_admin_id` int(11) DEFAULT NULL,
  `enq_status` tinyint(1) NOT NULL,
  `enq_created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrolment_form_new`
--

CREATE TABLE `enrolment_form_new` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `status` enum('pending','complete','raise_query','resolve_query') NOT NULL DEFAULT 'pending',
  `updated_status` varchar(50) NOT NULL DEFAULT '',
  `form_source` varchar(50) NOT NULL DEFAULT 'enrolment_form_new',
  `qualification_code_title` varchar(255) NOT NULL DEFAULT '',
  `usi_id` varchar(10) NOT NULL DEFAULT '',
  `given_name` varchar(100) NOT NULL DEFAULT '',
  `surname` varchar(100) NOT NULL DEFAULT '',
  `age_declaration_18` tinyint(1) NOT NULL DEFAULT 0,
  `dob` date DEFAULT NULL,
  `gender_check` tinyint(1) NOT NULL DEFAULT 0,
  `street_details` varchar(255) NOT NULL DEFAULT '',
  `post_code` varchar(6) NOT NULL DEFAULT '',
  `sub_urb` varchar(100) NOT NULL DEFAULT '',
  `stu_state` varchar(10) NOT NULL DEFAULT '',
  `postal_same_as_above` tinyint(1) NOT NULL DEFAULT 0,
  `postal_address` text DEFAULT NULL,
  `english_read_write` tinyint(1) NOT NULL DEFAULT 0,
  `mobile_num` varchar(20) NOT NULL DEFAULT '',
  `work_phone` varchar(20) NOT NULL DEFAULT '',
  `home_phone` varchar(20) NOT NULL DEFAULT '',
  `email_address` varchar(150) NOT NULL DEFAULT '',
  `em_full_name` varchar(100) NOT NULL DEFAULT '',
  `em_relation` varchar(100) NOT NULL DEFAULT '',
  `em_mobile_num` varchar(20) NOT NULL DEFAULT '',
  `birth_country` varchar(100) NOT NULL DEFAULT '',
  `city_of_birth` varchar(100) NOT NULL DEFAULT '',
  `lan_spoken` tinyint(1) NOT NULL DEFAULT 0,
  `lan_spoken_other` varchar(100) NOT NULL DEFAULT '',
  `origin` tinyint(1) NOT NULL DEFAULT 0,
  `disability` tinyint(1) NOT NULL DEFAULT 0,
  `st_disability_type` varchar(50) NOT NULL DEFAULT '',
  `disability_type_other` varchar(255) NOT NULL DEFAULT '',
  `highest_school` tinyint(1) NOT NULL DEFAULT 0,
  `sec_school` tinyint(1) NOT NULL DEFAULT 0,
  `year_completed_school` varchar(4) NOT NULL DEFAULT '',
  `mode_delivery` varchar(20) NOT NULL DEFAULT '',
  `courses` text DEFAULT NULL,
  `qual_cert1` tinyint(1) NOT NULL DEFAULT 0,
  `qual_cert2` tinyint(1) NOT NULL DEFAULT 0,
  `qual_cert3` tinyint(1) NOT NULL DEFAULT 0,
  `qual_cert4` tinyint(1) NOT NULL DEFAULT 0,
  `qual_diploma` tinyint(1) NOT NULL DEFAULT 0,
  `qual_adv_diploma` tinyint(1) NOT NULL DEFAULT 0,
  `qual_bachelor` tinyint(1) NOT NULL DEFAULT 0,
  `qual_other` tinyint(1) NOT NULL DEFAULT 0,
  `qual_none` tinyint(1) NOT NULL DEFAULT 0,
  `qualification_attained` varchar(20) NOT NULL DEFAULT '',
  `emp_status` tinyint(1) NOT NULL DEFAULT 0,
  `industry_of_work` varchar(255) NOT NULL DEFAULT '',
  `study_reason` tinyint(1) NOT NULL DEFAULT 0,
  `study_reason_other` varchar(255) NOT NULL DEFAULT '',
  `study_reason_text` text DEFAULT NULL,
  `cred_tansf` tinyint(1) NOT NULL DEFAULT 0,
  `computer_access` tinyint(1) NOT NULL DEFAULT 0,
  `computer_literacy` varchar(20) NOT NULL DEFAULT '',
  `numeracy_skills` varchar(20) NOT NULL DEFAULT '',
  `additional_support` tinyint(1) NOT NULL DEFAULT 0,
  `additional_support_specify` varchar(255) NOT NULL DEFAULT '',
  `usi_declaration` tinyint(1) NOT NULL DEFAULT 0,
  `privacy_declaration` tinyint(1) NOT NULL DEFAULT 0,
  `refund_declaration` tinyint(1) NOT NULL DEFAULT 0,
  `office_student_id` varchar(50) NOT NULL DEFAULT '',
  `office_coordinator_name` varchar(100) NOT NULL DEFAULT '',
  `office_invoice_provided` tinyint(1) NOT NULL DEFAULT 0,
  `office_receipt_collected` tinyint(1) NOT NULL DEFAULT 0,
  `office_lms_access` tinyint(1) NOT NULL DEFAULT 0,
  `office_resources_access` tinyint(1) NOT NULL DEFAULT 0,
  `office_uploaded_sms` tinyint(1) NOT NULL DEFAULT 0,
  `office_welcome_pack_sent` tinyint(1) NOT NULL DEFAULT 0,
  `candidate_declaration` tinyint(1) NOT NULL DEFAULT 0,
  `candidate_full_name` varchar(150) NOT NULL DEFAULT '',
  `candidate_date` date DEFAULT NULL,
  `candidate_signature` varchar(150) NOT NULL DEFAULT '',
  `enquiry_id` varchar(50) NOT NULL DEFAULT '',
  `rto_name` varchar(100) NOT NULL DEFAULT '',
  `branch_name` varchar(100) NOT NULL DEFAULT '',
  `photo_paths` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrolment_form_new`
--

INSERT INTO `enrolment_form_new` (`id`, `user_type`, `username`, `status`, `updated_status`, `form_source`, `qualification_code_title`, `usi_id`, `given_name`, `surname`, `age_declaration_18`, `dob`, `gender_check`, `street_details`, `post_code`, `sub_urb`, `stu_state`, `postal_same_as_above`, `postal_address`, `english_read_write`, `mobile_num`, `work_phone`, `home_phone`, `email_address`, `em_full_name`, `em_relation`, `em_mobile_num`, `birth_country`, `city_of_birth`, `lan_spoken`, `lan_spoken_other`, `origin`, `disability`, `st_disability_type`, `disability_type_other`, `highest_school`, `sec_school`, `year_completed_school`, `mode_delivery`, `courses`, `qual_cert1`, `qual_cert2`, `qual_cert3`, `qual_cert4`, `qual_diploma`, `qual_adv_diploma`, `qual_bachelor`, `qual_other`, `qual_none`, `qualification_attained`, `emp_status`, `industry_of_work`, `study_reason`, `study_reason_other`, `study_reason_text`, `cred_tansf`, `computer_access`, `computer_literacy`, `numeracy_skills`, `additional_support`, `additional_support_specify`, `usi_declaration`, `privacy_declaration`, `refund_declaration`, `office_student_id`, `office_coordinator_name`, `office_invoice_provided`, `office_receipt_collected`, `office_lms_access`, `office_resources_access`, `office_uploaded_sms`, `office_welcome_pack_sent`, `candidate_declaration`, `candidate_full_name`, `candidate_date`, `candidate_signature`, `enquiry_id`, `rto_name`, `branch_name`, `photo_paths`, `created_at`) VALUES
(1, 'admin', 'ujala', 'complete', 'complete', 'enrolment_form_new', '', '9874563214', 'Monika', 'Shinde', 1, '2026-05-12', 2, 'test', '412563', 'test', 'NSW', 1, '', 1, '9874563214', '5896321478', '8523698741', 'monikashinde8421@gmail.com', 'Monika Shinde', 'test2', '8956321452', 'india', 'ahilyanagar', 2, '', 1, 2, '', '', 1, 2, '2020', 'Classroom', '[\"11\"]', 0, 0, 0, 0, 1, 1, 1, 0, 0, '', 3, 'ANZSCO8457', 1, '', 'fdgsdfg', 1, 1, 'Good', 'Good', 1, '', 1, 1, 1, '2026CERTIFICATEIIIINALLIEDHEALTHASSISTANCE0001', '', 0, 0, 0, 0, 0, 0, 1, 'Monika Shinde', '2026-05-18', 'Monika Shinde', 'EQ00007', 'National College Australia', 'test', '[\"563228_1779090816.png\"]', '2026-05-18 07:53:36'),
(2, 'student', 'Monika Dighe', 'complete', 'completed', 'enrolment_form_new', 'CHC33015', '7896541236', 'Monika ', 'dighe', 1, '2026-05-07', 2, 'test', '456321', 'test', 'NSW', 1, '', 1, '9874563210', '78978978979879879', '9879879879', 'monikashinde8421@gmail.com', 'monika', 'self', '7896541236', 'India', 'nashik', 2, '', 1, 2, '', '', 1, 1, '2015', 'Classroom', '[\"9\"]', 1, 1, 1, 1, 0, 0, 0, 0, 0, '', 3, 'ANZSCO', 6, '', 'test', 1, 1, 'Good', 'Good', 1, '', 1, 1, 1, '2026CERTIFICATEIVINMENTALHEALTH0002', '', 0, 0, 0, 0, 0, 0, 1, 'Monika Dighe ', '2026-05-18', 'monika dighe', 'EQ00025', 'National College Australia', '', '[\"741931_1779165906.png\"]', '2026-05-18 13:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `enrolment_queries`
--

CREATE TABLE `enrolment_queries` (
  `id` int(10) UNSIGNED NOT NULL,
  `enrolment_id` int(10) UNSIGNED NOT NULL,
  `raised_by` varchar(150) NOT NULL DEFAULT '',
  `user_type` varchar(20) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrolment_queries`
--

INSERT INTO `enrolment_queries` (`id`, `enrolment_id`, `raised_by`, `user_type`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 1, 'ujala', 'admin', 'uploaded photo', 'uploaded photo', 'open', '2026-05-18 08:18:14'),
(2, 2, 'ujala', 'admin', 'missing doc', 'missing doc', 'resolved', '2026-05-18 18:06:06'),
(3, 2, 'ujala', 'admin', 'doc missing', 'test for doc', 'open', '2026-05-19 04:28:02');

-- --------------------------------------------------------

--
-- Table structure for table `followup_calls`
--

CREATE TABLE `followup_calls` (
  `flw_id` int(11) NOT NULL,
  `enquiry_id` varchar(255) DEFAULT NULL,
  `flw_name` varchar(255) DEFAULT NULL,
  `flw_phone` varchar(100) DEFAULT NULL,
  `flw_contacted_person` varchar(255) DEFAULT NULL,
  `flw_contacted_time` datetime DEFAULT NULL,
  `flw_date` date DEFAULT NULL,
  `flw_progress_state` varchar(255) DEFAULT NULL,
  `flw_remarks` text NOT NULL,
  `flw_comments` text NOT NULL,
  `flw_mode_contact` varchar(100) DEFAULT NULL,
  `flw_created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `flw_created_by` int(11) DEFAULT NULL,
  `flw_modified_date` timestamp NULL DEFAULT NULL,
  `flw_modifiedby` int(11) DEFAULT NULL,
  `flw_enquiry_status` tinyint(4) NOT NULL DEFAULT 0,
  `flw_delete_note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `followup_calls`
--

INSERT INTO `followup_calls` (`flw_id`, `enquiry_id`, `flw_name`, `flw_phone`, `flw_contacted_person`, `flw_contacted_time`, `flw_date`, `flw_progress_state`, `flw_remarks`, `flw_comments`, `flw_mode_contact`, `flw_created_date`, `flw_created_by`, `flw_modified_date`, `flw_modifiedby`, `flw_enquiry_status`, `flw_delete_note`) VALUES
(1, 'EQ00002', 'Jacob Shane', '8309603262', 'test person name', '2023-10-19 20:00:00', '2023-10-12', '', '', 'test', 'phone', '2023-10-01 20:01:28', 1, '2023-10-01 09:01:46', 1, 0, NULL),
(2, 'EQ00008', 'jaswanth kumar', '7306468658', 'regrg', '2023-10-11 22:19:00', '2023-10-05', NULL, '[\"1\",\"2\"]', 'vfbf', 'vfdf', '2023-10-03 12:49:46', 1, NULL, NULL, 0, NULL),
(3, 'EQ00010', 'Prathip', '9302265123', 'Sumanth', '2023-10-05 09:00:00', '2023-10-05', NULL, '[\"1\",\"14\"]', '', 'Phone', '2023-10-04 10:58:29', 1, NULL, NULL, 0, NULL),
(4, 'EQ00008', 'jaswanth kumar', '7306468658', 'Parry', '2023-10-05 15:38:00', '2023-10-05', NULL, '[\"1\",\"5\"]', '', 'phone', '2023-10-05 01:09:17', 1, NULL, NULL, 0, NULL),
(5, 'EQ00012', 'krishna', '0411439235', 'Shambhu', '2023-10-12 11:00:00', '2023-10-12', NULL, '[\"1\"]', 'He was busy doing his Uni work. Asked us to call him back in 2 hours time', '3cx', '2023-10-12 06:12:01', 1, NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `inv_id` int(11) NOT NULL,
  `inv_auto_id` varchar(255) NOT NULL,
  `st_unique_id` varchar(255) NOT NULL,
  `inv_std_name` varchar(255) NOT NULL,
  `inv_course` tinyint(1) NOT NULL,
  `inv_fee` varchar(255) NOT NULL,
  `inv_paid` varchar(255) NOT NULL,
  `inv_due` varchar(255) NOT NULL,
  `inv_payment_date` date NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `inv_status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`inv_id`, `inv_auto_id`, `st_unique_id`, `inv_std_name`, `inv_course`, `inv_fee`, `inv_paid`, `inv_due`, `inv_payment_date`, `created_date`, `inv_status`) VALUES
(1, 'INV00001', '082623DSB0001', 'Mike', 1, '5000', '2000', '300', '2023-08-11', '2023-08-26 15:55:39', 0),
(2, 'INV00002', '98798sdf', 'Kiran', 1, '500', '3030', '200', '2023-08-16', '2023-08-27 10:33:31', 0),
(3, 'INV202300003', '2023B10002', 'John Kotln', 1, '5000', '2000', '3000', '2023-08-18', '2023-08-28 02:23:45', 0);

-- --------------------------------------------------------

--
-- Table structure for table `login_otp_challenges`
--

CREATE TABLE `login_otp_challenges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `channel` enum('admin','student') NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_pk` bigint(20) UNSIGNED NOT NULL,
  `otp_code` varchar(10) NOT NULL COMMENT 'plain OTP (testing)',
  `session_bind` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `verify_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_verify_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `ip_request` varchar(45) DEFAULT NULL,
  `ip_last_verify` varchar(45) DEFAULT NULL,
  `user_agent` varchar(512) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_otp_challenges`
--

INSERT INTO `login_otp_challenges` (`id`, `channel`, `email`, `user_pk`, `otp_code`, `session_bind`, `expires_at`, `is_used`, `verified_at`, `verify_attempts`, `max_verify_attempts`, `ip_request`, `ip_last_verify`, `user_agent`, `created_at`) VALUES
(1, 'admin', 'monikashinde8421@gmail.com', 5, '390553', 'f5f9807fd31df637a0e82b5c3844fb3139423dd3fcf7ff0692f728b68b55e1e0', '2026-05-05 13:12:32', 1, '2026-05-05 16:33:21', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 16:32:32'),
(2, 'admin', 'monikashinde8421@gmail.com', 5, '845708', '2b4b4c14592824263c28028d29e499610f8688a66f933f906ab352140d822c77', '2026-05-05 13:17:34', 1, '2026-05-05 16:38:02', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-05-05 16:37:34'),
(3, 'admin', 'monikashinde8421@gmail.com', 5, '291970', 'f59cd1b4ec3a777bf58e025cbc990e4cca0f636ea265994485973098f6d9d432', '2026-05-13 12:43:59', 1, '2026-05-13 16:04:49', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 16:03:59'),
(4, 'student', 'monikashinde8421@gmail.com', 39, '858187', 'f05c9233ecc204e117c07bb36c6d3bfdf2b91644c53ffbd23108cc8e1f3e6e38', '2026-05-13 14:16:42', 2, NULL, 0, 5, '::1', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 17:36:42'),
(5, 'student', 'monikashinde8421@gmail.com', 39, '135328', '86b20bbed07524729d61431919c7ea788ceb884065a65df85abdd8cf747dc8d0', '2026-05-13 14:18:56', 2, NULL, 0, 5, '::1', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 17:38:56'),
(6, 'admin', 'monikashinde8421@gmail.com', 5, '847591', 'efcc8456c51d56e6904d32bb6189b37e0277b09c98533fc70e1f1744e4745aa3', '2026-05-13 14:31:04', 1, '2026-05-13 17:51:52', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 17:51:04'),
(7, 'student', 'monikashinde8421@gmail.com', 39, '946838', '031768931016fb56696252d3316325c8dfc19070d8d0598b9e30dadb46959ee9', '2026-05-13 14:33:02', 2, NULL, 0, 5, '::1', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 17:53:02'),
(8, 'student', 'monikashinde8421@gmail.com', 39, '280031', '52a1452d9d2c64f8a98f6654df72fd4f9cf3ff53e7b8e93016fa4f927e7820b1', '2026-05-13 14:38:25', 1, '2026-05-13 17:59:31', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 17:58:25'),
(9, 'admin', 'monikashinde8421@gmail.com', 5, '194294', '68ff10f95cc343e6d8d4d52380bbe625f1d43db288774c804109b868e68a26dc', '2026-05-13 16:36:49', 1, '2026-05-13 19:57:27', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 19:56:49'),
(10, 'admin', 'monikashinde8421@gmail.com', 5, '633667', '31c9a5d8510de0f1bde0f37cf9beb4608c1ef58b298bd35eeff6ee9b4198a2de', '2026-05-14 03:29:56', 2, NULL, 0, 5, '::1', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 06:49:56'),
(11, 'admin', 'monikashinde8421@gmail.com', 5, '338801', '2322da976131bc051f05238620364c79afce816bb028ae7e3055af0580ab97cc', '2026-05-14 03:30:39', 1, '2026-05-14 06:51:31', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 06:50:39'),
(12, 'student', 'monikashinde8421@gmail.com', 39, '220454', '4c06c01135ce9c6093cfa2933d6e5ef37b0da548f7408f9c577dc1c5c89a4e96', '2026-05-14 03:39:59', 1, '2026-05-14 07:00:30', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 06:59:59'),
(13, 'admin', 'monikashinde8421@gmail.com', 5, '218692', 'd24ba40e5ba5c179bf709dd9f4fac87fbc3160e0b9c737d5c2460091deea3b76', '2026-05-14 06:33:22', 1, '2026-05-14 09:54:22', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 09:53:22'),
(14, 'admin', 'monikashinde8421@gmail.com', 5, '123275', '87f0728c6016e7fd073c7f47358f0265d230a04a32c5842be96aa275913adde8', '2026-05-14 09:14:07', 2, NULL, 0, 5, '::1', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 12:34:07'),
(15, 'admin', 'monikashinde8421@gmail.com', 5, '434798', '38227e7cd9a19d758fbc87cec709395f536c9441efe323e90150aa8581f9b373', '2026-05-14 09:14:45', 1, '2026-05-14 12:35:40', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 12:34:45'),
(16, 'student', 'monikashinde8421@gmail.com', 39, '466318', '05ad04f321819293c23f67d7dc16dfeda398d91d17507493aa1260a811ae26e4', '2026-05-14 14:28:39', 1, '2026-05-14 17:49:13', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-14 17:48:39'),
(17, 'admin', 'monikashinde8421@gmail.com', 5, '990049', '362fc7c05018b1c12bb2262ca8b34168d4ee99de581e5bc583ad090536249ab1', '2026-05-15 03:08:58', 1, '2026-05-15 06:29:27', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 06:28:58'),
(18, 'student', 'monikashinde8421@gmail.com', 39, '674598', '365e962da5025a449bce7795fb7296a2f58b70f2f127fcf5dafdf6d9f75581fb', '2026-05-15 04:45:13', 1, '2026-05-15 08:05:40', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 08:05:13'),
(19, 'admin', 'monikashinde8421@gmail.com', 5, '920994', '3229647e335fcff2beea548d7b21e618766672ba94233a64a1b8c82eec81347a', '2026-05-15 12:48:16', 1, '2026-05-15 16:09:08', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 16:08:16'),
(20, 'student', 'monikashinde8421@gmail.com', 39, '236605', '718ae4c8f37057e4538965183bcb13f594400bd68ea724505368405f268bf93c', '2026-05-15 14:07:30', 1, '2026-05-15 17:28:01', 0, 5, '::1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-15 17:27:30');

-- --------------------------------------------------------

--
-- Table structure for table `payment_records`
--

CREATE TABLE `payment_records` (
  `id` int(11) NOT NULL,
  `given_name` varchar(255) NOT NULL,
  `contact_person` text DEFAULT NULL,
  `num_students` text DEFAULT NULL,
  `students_names` text DEFAULT NULL,
  `surname` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `course` varchar(255) NOT NULL,
  `totalFees` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,0) DEFAULT NULL,
  `paid_amount` decimal(10,0) DEFAULT NULL,
  `balance_amount` text DEFAULT NULL,
  `paymentDone` decimal(10,2) NOT NULL,
  `installment_no` varchar(255) DEFAULT NULL,
  `datePaid` date NOT NULL,
  `remainingDue` decimal(10,2) NOT NULL,
  `comments` text DEFAULT NULL,
  `instalmentPaid` decimal(10,2) DEFAULT NULL,
  `dateTime` datetime DEFAULT NULL,
  `whoTookPayment` varchar(255) DEFAULT NULL,
  `paymentMode` enum('EFTPOS','EFT','Cash','MOTO','Bank Deposit') NOT NULL,
  `fundsReceived` enum('Yes','No') NOT NULL,
  `whoChecked` varchar(255) DEFAULT NULL,
  `receiptEmailed` enum('Yes','No') NOT NULL,
  `invoice_number` text DEFAULT NULL,
  `invoice_type` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_records`
--

INSERT INTO `payment_records` (`id`, `given_name`, `contact_person`, `num_students`, `students_names`, `surname`, `address`, `phone`, `email`, `course`, `totalFees`, `total_amount`, `paid_amount`, `balance_amount`, `paymentDone`, `installment_no`, `datePaid`, `remainingDue`, `comments`, `instalmentPaid`, `dateTime`, `whoTookPayment`, `paymentMode`, `fundsReceived`, `whoChecked`, `receiptEmailed`, `invoice_number`, `invoice_type`, `created_at`) VALUES
(1, '', '{\"name\":\"dfgdfg\",\"email\":\"df@gmail.com\",\"role\":\"dfgdf\",\"phone\":\"5646546544\"}', '54654', '[\"654\"]', '', 'dfgdf', '5646546544', '', 'fgdfg', 0.00, 654, 654654, '654', 0.00, NULL, '0000-00-00', 0.00, NULL, NULL, '2026-01-01 00:00:00', NULL, 'EFTPOS', 'Yes', NULL, 'Yes', 'INV202500001', 2, '2025-03-15 06:06:35'),
(2, '', '{\"name\":\"dfgdfg\",\"email\":\"df@gmail.com\",\"role\":\"dfgdf\",\"phone\":\"5646546544\"}', '54654', '[\"654\"]', '', 'dfgdf', '5646546544', '', 'fgdfg', 0.00, 654, 654654, '654', 0.00, NULL, '0000-00-00', 0.00, NULL, NULL, '2026-01-01 00:00:00', NULL, 'EFTPOS', 'Yes', NULL, 'Yes', 'INV202500002', 2, '2025-03-15 06:23:33'),
(3, 'test', NULL, NULL, NULL, ' tes', 'ttest', 'sett', 'tes@gmail.com', 'ttest', 334.00, NULL, NULL, NULL, 3434.00, NULL, '2025-01-01', 3434.00, 'tset', 334.00, '2025-01-01 00:00:00', 'tsdfsdf', 'EFTPOS', 'Yes', 'test dfsdf', 'Yes', 'INV202500003', 1, '2025-03-18 14:48:37'),
(4, 'test', NULL, NULL, NULL, ' tes', 'ttest', 'sett', 'tes@gmail.com', 'ttest', 334.00, NULL, NULL, NULL, 3434.00, NULL, '2025-01-01', 3434.00, 'tset', 334.00, '2025-01-01 00:00:00', 'tsdfsdf', 'EFTPOS', 'Yes', 'test dfsdf', 'Yes', 'INV202500004', 1, '2025-03-18 14:57:52'),
(5, 'test', NULL, NULL, NULL, ' tes', 'ttest', 'sett', 'tes@gmail.com', 'ttest', 334.00, NULL, NULL, NULL, 3434.00, NULL, '2025-01-01', 3434.00, 'tset', 334.00, '2025-01-01 00:00:00', 'tsdfsdf', 'EFTPOS', 'Yes', 'test dfsdf', 'Yes', 'INV202500005', 1, '2025-03-18 14:59:21'),
(6, 'test', NULL, NULL, NULL, ' tes', 'ttest', 'sett', 'tes@gmail.com', 'ttest', 334.00, NULL, NULL, NULL, 3434.00, NULL, '2025-01-01', 3434.00, 'tset', 334.00, '2025-01-01 00:00:00', 'tsdfsdf', 'EFTPOS', 'Yes', 'test dfsdf', 'Yes', 'INV202500006', 1, '2025-03-18 15:00:06'),
(7, 'test', NULL, NULL, NULL, ' tes', 'ttest', 'sett', 'tes@gmail.com', 'ttest', 334.00, NULL, NULL, NULL, 3434.00, NULL, '2025-01-01', 3434.00, 'tset', 334.00, '2025-01-01 00:00:00', 'tsdfsdf', 'EFTPOS', 'Yes', 'test dfsdf', 'Yes', 'INV202500007', 1, '2025-03-18 15:00:24'),
(8, 'test', NULL, NULL, NULL, ' tes', 'ttest', 'sett', 'tes@gmail.com', 'ttest', 334.00, NULL, NULL, NULL, 3434.00, NULL, '2025-01-01', 3434.00, 'tset', 334.00, '2025-01-01 00:00:00', 'tsdfsdf', 'EFTPOS', 'Yes', 'test dfsdf', 'Yes', 'INV202500008', 1, '2025-03-18 15:04:33'),
(9, 'test', NULL, NULL, NULL, 'test', 'test', '08309603262', 'test@gmail.com', 'test', 3345.00, NULL, NULL, NULL, 345345.00, NULL, '2025-01-01', 45345.00, 'sdfsdf', 234.00, '0000-00-00 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500009', 1, '2025-03-20 14:43:27'),
(10, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'root@gmail.com', 'test', 345345.00, NULL, NULL, NULL, 345345.00, NULL, '2025-01-01', 4456.00, 'test', 345.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500010', 1, '2025-03-20 15:10:29'),
(11, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'root@gmail.com', 'test', 345345.00, NULL, NULL, NULL, 345345.00, NULL, '2025-01-01', 4456.00, 'test', 345.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500011', 1, '2025-03-20 15:10:48'),
(12, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'root@gmail.com', 'test', 3453.00, NULL, NULL, NULL, 34534.00, NULL, '2025-01-01', 34554.00, 'test', 345345.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500012', 1, '2025-03-20 15:11:49'),
(13, 'Test', NULL, NULL, NULL, 'Test', 'Test', '1231231230', 'test@gmail.com', 'Test', 12.00, NULL, NULL, NULL, 12.00, NULL, '2025-03-21', 0.00, 'No', 6.00, '2025-03-18 08:23:00', 'Hh', 'Bank Deposit', 'Yes', 'Yaga', 'Yes', 'INV202500013', 1, '2025-03-21 02:53:24'),
(14, 'Test', NULL, NULL, NULL, 'Test', 'Test', '7897897899', 'test@gmail.com', 'Test', 67.00, NULL, NULL, NULL, 67.00, NULL, '2025-03-22', 78.00, 'Test', 56.00, '2025-03-21 08:27:00', 'Test', 'EFTPOS', 'Yes', 'Test', 'Yes', 'INV202500014', 1, '2025-03-21 02:58:11'),
(15, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'saisatya51@gmail.com', 'test', 345.00, NULL, NULL, NULL, 345.00, NULL, '2025-01-01', 345.00, 'tes', 345.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500015', 1, '2025-03-21 03:27:26'),
(16, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'test@gmail.com', 'test', 123.00, NULL, NULL, NULL, 234.00, NULL, '2025-01-01', 243.00, 'test', 234.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500016', 1, '2025-03-21 03:32:01'),
(17, 'Test', NULL, NULL, NULL, 'Teat', 'Test', '6786786789', 'test@gmail.com', 'Test', 56.00, NULL, NULL, NULL, 56.00, NULL, '2025-03-21', 56.00, 'Test', 56.00, '2025-03-21 09:03:00', 'Test', 'EFTPOS', 'Yes', 'Test', 'Yes', 'INV202500017', 1, '2025-03-21 03:33:39'),
(18, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'test@gmail.com', 'test', 345.00, NULL, NULL, NULL, 345.00, NULL, '2025-01-01', 345.00, 'test', 345.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500018', 1, '2025-03-21 03:35:30'),
(19, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'test@gmail.com', 'test', 34.00, NULL, NULL, NULL, 345.00, NULL, '2025-01-01', 345.00, 'test', 345.00, '2025-01-01 00:00:00', 'tets', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500019', 1, '2025-03-21 03:37:15'),
(20, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'test@gmail.com', 'test', 34.00, NULL, NULL, NULL, 3434.00, NULL, '2025-01-01', 345.00, 'test', 245.00, '2025-01-01 00:00:00', 'tes', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500020', 1, '2025-03-21 03:38:06'),
(21, 'Rest', NULL, NULL, NULL, 'Test', 'Test', '5675675675', 'test@gmail.com', 'Test', 577.00, NULL, NULL, NULL, 567.00, NULL, '2025-03-21', 56.00, 'Test', 56.00, '2025-03-21 09:09:00', 'Test', 'EFTPOS', 'Yes', 'Test', 'Yes', 'INV202500021', 1, '2025-03-21 03:39:55'),
(22, 'Tets', NULL, NULL, NULL, 'Test', 'Test', '12312312309', 'test@gmail.com', 'Test', 5.00, NULL, NULL, NULL, 5.00, NULL, '2025-03-20', 12.00, 'Hsh', 12.00, '2025-03-21 09:11:00', 'H', 'EFTPOS', 'Yes', 'H', 'Yes', 'INV202500022', 1, '2025-03-21 03:41:33'),
(23, 'Parry', NULL, NULL, NULL, 'Singh', 'Adelaide', '123456789', 'parry@auztraining.com.au', 'Certificate III in Individual Support (Ageing & Disability)', 1799.00, NULL, NULL, NULL, 1799.00, NULL, '2025-03-21', 0.00, 'orientation booked', 0.00, '2025-03-21 16:17:00', 'Parry', 'EFTPOS', 'Yes', 'Parry', 'Yes', 'INV202500023', 1, '2025-03-21 05:48:01'),
(24, '', NULL, NULL, NULL, '', '', '', '', '', 0.00, NULL, NULL, NULL, 0.00, NULL, '0000-00-00', 0.00, '', 0.00, '0000-00-00 00:00:00', '', 'EFTPOS', 'Yes', '', 'Yes', 'INV202500024', 1, '2025-03-21 06:08:54'),
(25, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'saisatya51@gmail.com', '[\"certificate-iii-ageing-disability\",\"certificate-iv-aged-care\",\"sdfasdfasdf\"]', 34535.00, NULL, NULL, NULL, 3.00, 'Full Amount Paid', '2025-01-01', 34532.00, 'test', 0.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500025', 1, '2025-03-24 18:26:36'),
(26, 'Test', NULL, NULL, NULL, 'Test', 'Test', '1231231230', 'test@gmail.com', '[\"certificate-iii-disability\"]', 1500.00, NULL, NULL, NULL, 750.00, 'Fourth Installment', '2025-03-25', 250.00, 'Test', 0.00, '2025-03-25 12:33:00', 'Test', 'EFTPOS', 'Yes', 'Sh', 'Yes', 'INV202500026', 1, '2025-03-25 07:03:34'),
(27, 'Test', NULL, NULL, NULL, 'Test', 'Test', '6786786689', 'test@gmail.com', '[\"certificate-iii-disability\",\"certificate-iii-ageing-disability\",\"testing\"]', 567.00, NULL, NULL, NULL, 78.00, 'Full Amount Paid', '2025-03-24', 378.00, 'Test', 0.00, '2025-03-26 12:48:00', 'Test', 'EFTPOS', 'Yes', 'Test', 'Yes', 'INV202500027', 1, '2025-03-25 07:19:38'),
(28, 'test', NULL, NULL, NULL, 'test', 'adelaide', '123456789', 'parry@auztraining.com.au', '[\"certificate-iii-ageing\"]', 1749.00, NULL, NULL, NULL, 1749.00, 'First Installment(Down Payment)', '2025-03-27', 0.00, '', 0.00, '2025-03-27 13:22:00', 'Parry', 'EFTPOS', 'Yes', '', 'Yes', 'INV202500028', 1, '2025-03-27 02:52:57'),
(29, 'test', NULL, NULL, NULL, 'test', 'adelaide', '0469855123', 'parry@auztraining.com.au', '[\"certificate-iii-ageing-disability\"]', 1749.00, NULL, NULL, NULL, 1749.00, 'Full Amount Paid', '2025-03-27', 0.00, 'test comment', 0.00, '2025-03-27 13:24:00', 'Parry', 'EFTPOS', 'No', '', 'No', 'INV202500029', 1, '2025-03-27 02:55:01'),
(30, 'test', NULL, NULL, NULL, 'test', 'adelaide', '00000000', 'parry@auztraining.com.au', '[\"certificate-iii-disability\"]', 1749.00, NULL, NULL, NULL, 1749.00, 'Full Amount Paid', '2025-03-27', 0.00, '', 0.00, '0000-00-00 00:00:00', '', 'EFTPOS', 'Yes', '', 'Yes', 'INV202500030', 1, '2025-03-27 03:07:01'),
(31, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'saisatya51@gmail.com', '[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Disability)\",\"Certificate IV in Aged Care\",\"Certificate IV in Disability\"]', 65465.00, NULL, NULL, NULL, 545.00, 'Second Installment', '2025-01-01', 64920.00, 'test', 0.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500031', 1, '2025-03-30 11:45:25'),
(32, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'saisatya51@gmail.com', '[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Disability)\",\"Certificate IV in Aged Care\",\"Certificate IV in Disability\"]', 65465.00, NULL, NULL, NULL, 545.00, 'Second Installment', '2025-01-01', 64920.00, 'test', 0.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500032', 1, '2025-03-30 11:45:33'),
(33, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'saisatya51@gmail.com', '[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Disability)\",\"Certificate IV in Aged Care\",\"Certificate IV in Disability\"]', 65465.00, NULL, NULL, NULL, 545.00, 'Second Installment', '2025-01-01', 64920.00, 'test', 0.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500033', 1, '2025-03-30 11:46:19'),
(34, 'MANGIPUDI', NULL, NULL, NULL, 'KIRAN', 'Agraharam street', '08309603262', 'saisatya51@gmail.com', '[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Disability)\",\"Certificate III in Individual Support (Ageing & Disability)\"]', 654654.00, NULL, NULL, NULL, 5454.00, 'Second Installment', '2025-01-01', 649200.00, 'test', 0.00, '2025-01-01 00:00:00', 'test', 'EFTPOS', 'Yes', 'test', 'Yes', 'INV202500034', 1, '2025-03-30 11:48:08'),
(35, 'Jaswanth', NULL, NULL, NULL, 'Kumar', 'gandhinagar-2', '07306468658', 'jaswanthkumar431@gmail.com', '[\"Certificate III in Individual Support (Ageing)\"]', 100.00, NULL, NULL, NULL, 50.00, 'First Installment(Down Payment)', '2025-03-30', 50.00, 'no', 0.00, '2025-03-30 20:48:00', 'j', 'EFT', 'Yes', 'j', 'Yes', 'INV202500035', 1, '2025-03-30 15:18:38'),
(36, 'test', NULL, NULL, NULL, 'test', 'adelaide', '123456789', 'parry@auztraining.com.au', '[\"Certificate III in Individual Support (Ageing)\"]', 1749.00, NULL, NULL, NULL, 1749.00, 'Second Installment', '2025-04-02', 1349.00, '', 0.00, '2025-04-02 11:01:00', 'Parry', 'EFTPOS', 'Yes', '', 'Yes', 'INV202500036', 1, '2025-04-02 00:31:54'),
(37, 'test', NULL, NULL, NULL, 'test', 'adelaide', '123456789', 'parry@auztraining.com.au', '[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Ageing & Disability)\"]', 1749.00, NULL, NULL, NULL, 1000.00, 'Full Amount Paid', '2025-04-02', 1000.00, 'test', 0.00, '2025-04-02 11:06:00', 'Parry', 'EFTPOS', 'Yes', '', 'Yes', 'INV202500037', 1, '2025-04-02 00:36:29'),
(38, '', NULL, NULL, NULL, '', '', '', '', '', 0.00, NULL, NULL, NULL, 0.00, NULL, '0000-00-00', 0.00, '', 0.00, '0000-00-00 00:00:00', '', 'EFTPOS', 'Yes', '', 'Yes', 'INV202500038', 1, '2025-10-29 07:19:25'),
(39, 'Raj', NULL, NULL, NULL, '', 'SA', '411290111', '', 'A', 0.00, NULL, NULL, NULL, 0.00, NULL, '2025-11-10', 0.00, '', 0.00, '0000-00-00 00:00:00', '', 'Cash', 'Yes', '', 'Yes', 'INV202500039', 1, '2025-11-17 07:21:29'),
(40, '', NULL, NULL, NULL, '', '', '', '', '', 0.00, NULL, NULL, NULL, 0.00, NULL, '0000-00-00', 0.00, '', 0.00, '0000-00-00 00:00:00', '', 'EFTPOS', 'Yes', '', 'Yes', 'INV202600040', 1, '2026-01-04 16:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `qualifications`
--

CREATE TABLE `qualifications` (
  `qualification_id` int(11) NOT NULL,
  `qualification_name` varchar(255) NOT NULL,
  `qualification_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `qualifications`
--

INSERT INTO `qualifications` (`qualification_id`, `qualification_name`, `qualification_status`, `created_date`) VALUES
(1, 'Masters Degree', 0, '2023-08-23 06:07:08'),
(2, 'Bachelors Degree', 0, '2023-08-23 06:07:08'),
(3, 'MCA', 0, '2023-08-23 06:07:16');

-- --------------------------------------------------------

--
-- Table structure for table `regular_group_form`
--

CREATE TABLE `regular_group_form` (
  `reg_grp_id` int(11) NOT NULL,
  `reg_grp_names` text DEFAULT NULL,
  `enq_form_id` int(11) DEFAULT NULL,
  `reg_grp_status` tinyint(4) NOT NULL DEFAULT 0,
  `reg_grp_created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `regular_group_form`
--

INSERT INTO `regular_group_form` (`reg_grp_id`, `reg_grp_names`, `enq_form_id`, `reg_grp_status`, `reg_grp_created_date`) VALUES
(1, 'csef,dfs', 24, 0, '2025-11-07 07:09:34');

-- --------------------------------------------------------

--
-- Table structure for table `rpl_enquries`
--

CREATE TABLE `rpl_enquries` (
  `rpl_enq_id` int(11) NOT NULL,
  `enq_form_id` int(11) DEFAULT NULL,
  `rpl_exp_in` varchar(255) DEFAULT NULL,
  `rpl_exp_role` varchar(255) DEFAULT NULL,
  `rpl_exp_years` varchar(255) DEFAULT NULL,
  `rpl_exp_docs` varchar(1) DEFAULT '0',
  `rpl_exp_prev_qual` varchar(1) DEFAULT '0',
  `rpl_exp_qual_name` varchar(255) NOT NULL,
  `rpl_exp` varchar(1) NOT NULL DEFAULT '0',
  `rpl_exp_created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpl_enquries`
--

INSERT INTO `rpl_enquries` (`rpl_enq_id`, `enq_form_id`, `rpl_exp_in`, `rpl_exp_role`, `rpl_exp_years`, `rpl_exp_docs`, `rpl_exp_prev_qual`, `rpl_exp_qual_name`, `rpl_exp`, `rpl_exp_created_date`) VALUES
(1, 2, '2', 'test rolls', '5 months ', '1', '2', '', '1', '2023-10-01 14:26:04'),
(2, 3, '2', 'roles ntest', '5 months ', '1', '2', '', '1', '2023-10-01 14:29:22'),
(3, 5, '', '', '', '', '', '', '2', '2023-10-02 15:28:50'),
(4, 8, '1', 'test', '10', '1', '1', 'ffgngf', '1', '2023-10-03 16:49:07'),
(5, 10, '1', 'Senior Helper ', '2Years 5 Months', '1', '1', 'Post Diploma in Hospitality ', '1', '2023-10-04 14:44:50'),
(6, 13, '1', 'tester', '20', '1', '1', 'te', '1', '2024-11-24 13:37:12'),
(7, 23, '', '', '', '', '', '', '2', '2025-11-07 07:09:02'),
(8, 25, '', '', '', '', '', '', '2', '2026-05-14 02:43:52'),
(9, 26, '', '', '', '', '', '', '2', '2026-05-19 07:27:25');

-- --------------------------------------------------------

--
-- Table structure for table `short_group_form`
--

CREATE TABLE `short_group_form` (
  `sh_grp_id` int(11) NOT NULL,
  `enq_form_id` int(11) DEFAULT NULL,
  `sh_org_name` varchar(255) DEFAULT NULL,
  `sh_grp_org_type` tinyint(1) DEFAULT NULL,
  `sh_grp_campus` tinyint(1) DEFAULT NULL,
  `sh_grp_date` date DEFAULT NULL,
  `sh_grp_num_stds` int(11) DEFAULT NULL,
  `sh_grp_ind_exp` tinyint(1) DEFAULT NULL,
  `sh_grp_train_bef` tinyint(1) DEFAULT NULL,
  `sh_grp_con_us` varchar(255) DEFAULT NULL,
  `sh_grp_phone` varchar(255) DEFAULT NULL,
  `sh_grp_name` varchar(255) DEFAULT NULL,
  `sh_grp_email` varchar(255) DEFAULT NULL,
  `sh_grp_status` tinyint(1) NOT NULL DEFAULT 0,
  `sh_grp_created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `short_group_form`
--

INSERT INTO `short_group_form` (`sh_grp_id`, `enq_form_id`, `sh_org_name`, `sh_grp_org_type`, `sh_grp_campus`, `sh_grp_date`, `sh_grp_num_stds`, `sh_grp_ind_exp`, `sh_grp_train_bef`, `sh_grp_con_us`, `sh_grp_phone`, `sh_grp_name`, `sh_grp_email`, `sh_grp_status`, `sh_grp_created_date`) VALUES
(1, 1, '', 0, 0, '0000-00-00', 0, 0, 0, '', '', '', '', 0, '2023-10-01 14:24:25'),
(2, 6, '', 0, 0, '0000-00-00', 0, 0, 0, 'phone call', '', '', '', 0, '2023-10-01 14:52:01'),
(3, 11, 'MAXWELLL', 1, 2, '2023-11-19', 7, 1, 1, 'PHONE', '0466666677', 'JULIA', '', 0, '2023-10-12 09:42:17');

-- --------------------------------------------------------

--
-- Table structure for table `slot_book`
--

CREATE TABLE `slot_book` (
  `slot_bk_id` int(11) NOT NULL,
  `enq_form_id` int(11) NOT NULL,
  `slot_bk_datetime` timestamp NULL DEFAULT NULL,
  `slot_bk_purpose` varchar(255) NOT NULL,
  `slot_bk_on` datetime NOT NULL DEFAULT current_timestamp(),
  `slot_book_by` varchar(150) NOT NULL,
  `slot_bk_attend` tinyint(4) NOT NULL DEFAULT 1,
  `slot_book_email_link` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `slot_book`
--

INSERT INTO `slot_book` (`slot_bk_id`, `enq_form_id`, `slot_bk_datetime`, `slot_bk_purpose`, `slot_bk_on`, `slot_book_by`, `slot_bk_attend`, `slot_book_email_link`) VALUES
(1, 4, '2023-10-12 14:36:00', 'visiting', '2023-09-14 00:00:00', 'surya', 1, 1),
(2, 8, '2023-10-06 02:18:00', 'vcvc', '2023-10-19 00:00:00', 'vsdfd', 1, 1),
(3, 10, '2023-10-05 14:30:00', 'Inqury', '2023-10-05 00:00:00', 'Sumanth', 1, 1),
(4, 1, '2023-10-06 00:23:00', 'Inqury', '2023-10-05 00:00:00', 'Prathip', 1, 1),
(5, 11, '2023-10-13 08:25:00', 'counseling', '2023-10-12 00:00:00', 'raj', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `source`
--

CREATE TABLE `source` (
  `source_id` int(11) NOT NULL,
  `source_name` varchar(255) NOT NULL,
  `source_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `source`
--

INSERT INTO `source` (`source_id`, `source_name`, `source_status`, `created_date`) VALUES
(1, 'Friends', 0, '2023-08-23 11:39:15'),
(2, 'Google', 0, '2023-08-23 11:39:15'),
(3, 'Website', 0, '2023-08-23 11:39:19');

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `st_at_id` int(11) NOT NULL,
  `st_unique_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `st_course_unit` varchar(255) NOT NULL,
  `st_unit_date` date NOT NULL,
  `st_unit_status` tinyint(1) NOT NULL DEFAULT 0,
  `st_unit_created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`st_at_id`, `st_unique_id`, `st_course_unit`, `st_unit_date`, `st_unit_status`, `st_unit_created_date`) VALUES
(1, '082623DSB0001', 'units9', '2023-09-09', 0, '2023-08-26 15:57:14'),
(2, '082623DSB0001', 'units9', '2023-09-07', 0, '2023-08-26 15:57:14'),
(3, '082623DSB0001', 'units9', '2023-09-07', 0, '2023-08-26 15:57:14'),
(4, '082623DSB0001', 'units7', '2023-08-25', 0, '2023-08-27 04:07:52'),
(5, '082623DSB0001', 'units8', '2023-09-08', 0, '2023-08-27 04:07:52'),
(6, '2023B10003', 'units7', '2023-08-25', 0, '2023-08-27 04:07:52'),
(7, '2023B10002', 'units7', '2023-08-25', 0, '2023-08-27 04:07:52'),
(8, '2023B10002', 'units9', '2023-08-25', 0, '2023-08-27 04:10:27'),
(12, '2023B10002', 'units9', '2023-08-25', 0, '2023-08-27 04:12:11'),
(16, '2023B10002', 'units9', '2023-08-25', 0, '2023-08-27 04:12:44'),
(17, '2023B10002', 'units9', '2023-08-25', 0, '2023-08-27 04:16:10'),
(18, '270823AG0001', 'units9', '2023-08-25', 0, '2023-09-11 08:26:48'),
(19, '270823AG0001', 'units7', '2023-08-25', 0, '2023-09-11 08:26:48'),
(20, '270823AG0001', 'units8', '2023-08-25', 0, '2023-09-11 08:26:48'),
(21, '270823AG0001', 'units2', '2023-08-25', 0, '2023-09-11 08:26:48'),
(22, '270823AG0001', 'units9', '2023-08-22', 0, '2023-09-11 08:26:48'),
(23, '270823DSB0002', 'units9', '2023-08-22', 0, '2023-09-11 08:26:48'),
(24, '270823DSB0002', 'units2', '2023-08-25', 0, '2023-09-11 08:26:48'),
(25, '270823AG0001', 'units9', '2023-08-25', 0, '2023-09-11 08:36:37'),
(26, '270823AG0001', 'units7', '2023-08-25', 0, '2023-09-11 08:36:37'),
(27, '270823AG0001', 'units8', '2023-08-25', 0, '2023-09-11 08:36:37'),
(28, '270823AG0001', 'units2', '2023-08-25', 0, '2023-09-11 08:36:37'),
(29, '270823AG0001', 'units9', '2023-08-22', 0, '2023-09-11 08:36:37'),
(30, '270823DSB0002', 'units9', '2023-08-22', 0, '2023-09-11 08:36:37'),
(31, '270823DSB0002', 'units2', '2023-08-25', 0, '2023-09-11 08:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `student_docs`
--

CREATE TABLE `student_docs` (
  `st_doc_id` int(11) NOT NULL,
  `st_unique_id` varchar(255) NOT NULL,
  `st_doc_names` text NOT NULL,
  `st_doc_status` tinyint(1) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `st_modified_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_docs`
--

INSERT INTO `student_docs` (`st_doc_id`, `st_unique_id`, `st_doc_names`, `st_doc_status`, `created_date`, `st_modified_date`) VALUES
(1, '082623DSB0001', '[\"includes/uploads/ADHAAR_1693107526480.pdf||dob\"]', 0, '2023-08-27 03:08:04', '2023-08-27 00:00:00'),
(2, 'A566E63D', '[\"includes/uploads/PDF_1761640313516.pdf||dob\",\"includes/uploads/PDF_1761640316846.pdf||address\"]', 0, '2025-10-28 08:31:53', '2025-10-28 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `student_enquiry`
--

CREATE TABLE `student_enquiry` (
  `st_id` int(11) NOT NULL,
  `st_enquiry_id` varchar(255) DEFAULT NULL,
  `st_name` varchar(255) NOT NULL,
  `st_member_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_surname` varchar(255) NOT NULL,
  `st_phno` varchar(10) NOT NULL,
  `st_email` varchar(100) NOT NULL,
  `st_course` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_course_type` tinyint(1) NOT NULL DEFAULT 0,
  `st_street_details` varchar(255) NOT NULL,
  `st_suburb` varchar(255) NOT NULL,
  `st_state` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `st_post_code` varchar(10) NOT NULL,
  `st_visited` tinyint(1) NOT NULL,
  `st_heared` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `st_hearedby` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_refered` tinyint(1) NOT NULL,
  `st_refer_name` text DEFAULT NULL,
  `st_refer_alumni` tinyint(1) NOT NULL,
  `st_fee` varchar(255) NOT NULL,
  `st_remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_shore` tinyint(1) NOT NULL,
  `st_ethnicity` varchar(255) DEFAULT NULL,
  `st_comments` text NOT NULL,
  `st_pref_comments` text DEFAULT NULL,
  `st_appoint_book` tinyint(1) NOT NULL,
  `st_enquiry_for` tinyint(1) NOT NULL DEFAULT 1,
  `st_visa_status` tinyint(1) DEFAULT 0,
  `st_visa_condition` tinyint(4) DEFAULT 1,
  `st_visa_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_enquiry_status` tinyint(1) NOT NULL DEFAULT 0,
  `st_delete_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_startplan_date` datetime DEFAULT NULL,
  `st_enquiry_date` datetime NOT NULL DEFAULT current_timestamp(),
  `st_created_by` int(11) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `st_modified_by` int(11) DEFAULT NULL,
  `st_modified_date` datetime DEFAULT NULL,
  `st_gen_enq_type` tinyint(1) DEFAULT NULL,
  `st_enquiry_source` tinyint(4) DEFAULT NULL COMMENT '1=Website,2=Phone,3=Walk-in,4=Email,5=WhatsApp,6=FB/IG,7=Agent',
  `st_location` varchar(255) DEFAULT NULL COMMENT 'Location from website popup',
  `st_enquiry_college` tinyint(4) DEFAULT NULL COMMENT '1=Apt Training,2=Milton,3=NCA,4=Power Ed,5=Auz Training',
  `st_enquiry_flow_change_stage` varchar(8) DEFAULT NULL COMMENT 'PEFU or PCFU when enquiry status last set from follow-up outcome',
  `student_user_id` int(11) DEFAULT NULL,
  `st_contact_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_enquiry`
--

INSERT INTO `student_enquiry` (`st_id`, `st_enquiry_id`, `st_name`, `st_member_name`, `st_surname`, `st_phno`, `st_email`, `st_course`, `st_course_type`, `st_street_details`, `st_suburb`, `st_state`, `st_post_code`, `st_visited`, `st_heared`, `st_hearedby`, `st_refered`, `st_refer_name`, `st_refer_alumni`, `st_fee`, `st_remarks`, `st_shore`, `st_ethnicity`, `st_comments`, `st_pref_comments`, `st_appoint_book`, `st_enquiry_for`, `st_visa_status`, `st_visa_condition`, `st_visa_note`, `st_enquiry_status`, `st_delete_note`, `st_startplan_date`, `st_enquiry_date`, `st_created_by`, `created_date`, `st_modified_by`, `st_modified_date`, `st_gen_enq_type`, `st_enquiry_source`, `st_location`, `st_enquiry_college`, `st_enquiry_flow_change_stage`, `student_user_id`, `st_contact_notes`) VALUES
(1, 'EQ00001', 'test surya', 'John Kotln', 'mangs', '8309603262', 'saikiran.m.v.s.s@gmail.com', '[\"14\"]', 4, 'street test', 'subrub streets', '3', '535552', 2, '[\"9\"]', 'friends', 1, 'test1,tests2test3', 1, 'this  is discusseed 3000', '', 1, '', '', '', 1, 2, 0, 1, '', 0, NULL, '2023-10-20 00:00:00', '2023-10-14 00:00:00', 1, '2023-10-04 14:54:11', 1, '2023-10-04 14:54:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'EQ00002', 'Jacob Shane', 'Jacob Shane', 'test surnamsdf', '8309603262', 'saisatya51@gmail.com', '[\"7\"]', 1, 'stretasdf asdfa', 'test surbuasd ', '4', '538779', 1, '[\"3\"]', '', 2, '', 0, '988', '', 0, '', '', '', 0, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2023-10-05 00:00:00', 1, '2023-10-01 14:25:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'EQ00003', 'Mike Sheifen', 'Mike Sheifen', 'test surnamess s', '8309603262', 'saisatya51@gmail.com', '[\"9\"]', 1, 'test setset', 'test setset', '1', '535558', 1, '[\"2\"]', '', 2, '', 2, '7986', '', 1, '', '', '', 0, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2023-10-12 00:00:00', 1, '2023-10-03 15:06:24', 1, '2023-10-03 15:06:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'EQ00004', 'test surya', 'test surya', 'surnam test', '8309603263', 'testsai@gmail.com', '[\"6\"]', 1, 'test street', 'sub rubs ', '2', '598798', 1, '[\"3\"]', '', 2, '', 0, '987', '[\"5\",\"6\"]', 1, 'indian', 'no comments', 'test nothinh', 1, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2023-10-05 00:00:00', 1, '2023-10-01 14:44:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'EQ00005', 'test surya', 'test surya', 'test surasd', '8309603265', 'testsurya@gmail.com', '[\"2\",\"3\",\"4\"]', 1, 'street es', 'sub asdfa', '2', '535558', 1, '[\"2\"]', '', 2, '', 2, '98798', '[\"3\",\"4\"]', 2, 'test indian', '', '', 0, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2023-10-12 00:00:00', 1, '2023-10-02 15:28:50', 1, '2023-10-02 15:28:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'EQ00006', 'test suryas', 'test suryas', 'tes adasd', '8309603262', 'saikira@gmail.com', '[\"4\",\"5\"]', 5, 'strsf you', 'sub jjs', '1', '549897', 1, '[\"4\"]', '', 2, '', 2, 'ads 879879', '', 2, '', '', '', 0, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2023-10-20 00:00:00', 1, '2023-10-02 17:20:12', 1, '2023-10-02 17:20:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'EQ00007', 'test surya', 'test surya', 'asdf', '8309607987', 'asdfa@gmail.com', '[\"4\"]', 2, 'agraharam street', 'bobbili', '0', '535558', 2, '[\"3\"]', '', 2, '', 0, 'asdf', '', 0, '', '', '', 0, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2023-12-31 00:00:00', 1, '2023-10-02 15:28:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'EQ00008', 'jaswanth kumar', 'jaswanth kumar', 'kottugummada', '7306468658', 'jaswanthkumar431@gmail.com', '[\"1\",\"2\",\"3\",\"4\"]', 1, 'cdsfs', 'bfbfd', '1', '123456', 1, '[\"1\",\"3\",\"4\",\"8\"]', '', 1, 'bfdbfbf', 1, '455', '[\"1\",\"2\",\"3\",\"4\"]', 1, 'bfdbfdb', ' vcbb', 'bfbfd', 1, 1, 1, 1, '', 0, NULL, '2023-10-05 00:00:00', '2023-10-04 00:00:00', 1, '2023-10-03 16:49:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'EQ00009', 'jaswanth kumar', 'jaswanth kumar', 'kottugummada', '7306468657', 'jaswanthkumar431@gmail.com', '[\"1\"]', 0, 'fvfdb', 'vfdb', '1', '134556', 1, '[\"1\",\"2\"]', '', 1, 'cbcb', 2, '', NULL, 0, NULL, '', ' fv', 0, 1, 0, 1, NULL, 0, NULL, '2023-10-04 00:00:00', '2023-10-03 13:02:39', 0, '2023-10-03 17:02:39', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'EQ00010', 'Prathip', 'Prathip', 'Potnuru', '9302265123', 'ppk.eee@gmail.com', '[\"1\",\"7\",\"17\"]', 1, 'Matam', 'Narasannapeta', '2', '532421', 1, '[\"1\",\"4\",\"9\"]', 'Testing', 1, 'Kiran, Krishna', 1, '1500', '[\"1\",\"9\",\"13\",\"14\"]', 2, 'Indian', 'Test', 'TEST_2', 1, 1, 2, 1, '', 0, NULL, '2023-10-10 00:00:00', '2023-10-04 00:00:00', 1, '2023-10-04 14:44:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'EQ00011', 'bindu', 'bindu', 'jami', '0466978278', 'bindumadhavi.kottakota@gmail.com', '[\"1\"]', 2, '65A Fosters road', 'greenacres', '7', '5086', 1, '[\"1\",\"2\",\"4\"]', '', 1, 'krishna', 1, '1000', '[\"1\",\"4\",\"6\",\"7\"]', 1, 'middle east', 'she is a very tough lady', '', 1, 1, 2, 1, '', 0, NULL, '2023-10-16 00:00:00', '1977-05-30 00:00:00', 1, '2023-10-12 09:44:21', 1, '2023-10-12 09:44:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'EQ00012', 'krishna', 'krishna', 'jami', '0411439235', 'JAMI.KRISHNAKUMAR@GMAIL.COM', '[\"7\"]', 2, '', '', '0', '5086', 2, '[\"1\"]', '', 2, '', 0, '1', '', 0, '', '', '', 0, 1, 0, 1, '', 0, NULL, '2023-10-13 00:00:00', '2023-10-12 00:00:00', 1, '2023-10-12 10:01:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'EQ00013', 'test', 'test', 'test', '6546546544', 'saitest@gmail.com', '[\"6\",\"7\"]', 1, 'test', 'test', '1', '564654', 1, 'test', '', 1, '', 1, '20000', '', 0, '', '', '', 0, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2024-12-31 00:00:00', 1, '2024-11-24 13:37:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'EQ00014', 'Rizika', 'Rizika', 'Rizika', '0434034862', 'noemailgivenyet@gmail.com', '[\"3\"]', 2, '', '', '7', '5999', 2, 'Google', '', 2, '', 0, '1799/1899', '[\"11\"]', 2, '', 'Received calls , provided basic info and booked f2f', '', 1, 1, 7, 1, 'WHV', 0, NULL, '0000-00-00 00:00:00', '2025-10-16 00:00:00', 1, '2025-10-16 06:42:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'EQ00015', 'Thavy', 'Thavy', 'chheng', '0400613017', 'thavychheng1708@gmail.com', '[\"3\"]', 2, '', '', '2', '3004', 2, 'friend', '', 1, '', 1, '1749/1849', '', 2, '', '', '', 1, 1, 7, 1, 'student visa', 0, NULL, '0000-00-00 00:00:00', '2025-10-17 00:00:00', 1, '2025-10-17 06:44:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'EQ00016', 'Sulab', 'Sulab', 'Banjade', '0416576122', 'sulabbanjade2@gmail.com', '[\"3\"]', 2, '', '', '', '9999', 1, 'Shambhu', '', 1, 'Shambhu', 2, '1749/1849', '[\"2\"]', 2, '', '', '', 0, 1, 1, 1, '', 0, NULL, '0000-00-00 00:00:00', '2025-06-12 00:00:00', 4, '2025-10-22 01:32:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'EQ00017', 'Sony', 'Sony', 'Matthew', '0474327231', 'sonudonu@gmail.com', '[\"3\"]', 2, '', '', '2', '9999', 2, 'Friend', '', 1, 'Christine Thomas', 1, '1749/1849', '[\"1\",\"2\",\"3\"]', 2, '', '', '', 0, 1, 7, 1, '482', 0, NULL, '0000-00-00 00:00:00', '2025-10-21 00:00:00', 4, '2025-10-24 03:17:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'EQ00018', 'Prabjoth Kaur', 'Prabjoth Kaur', 'Aulakh', '0459574797', 'prabhjotkaur2802@gmail.com', '[\"3\"]', 2, '', '', '2', '9999', 1, 'Friend', '', 1, 'Navdeep', 1, '1799/1899', '[\"1\",\"2\",\"3\"]', 2, '', '', '', 0, 1, 1, 1, '', 0, NULL, '2025-10-30 00:00:00', '2025-10-29 00:00:00', 4, '2025-10-29 03:53:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'EQ00019', 'Sumesh', 'Raj', 'Yadav', '8108876878', 'Raj@gmail.com', '[\"12\"]', 4, '4,Prabhu Nivas,Shiv Tekdi', 'Thane', '', '400602', 1, 'TEST', '', 1, '', 2, '1000', '', 0, '', '', '', 0, 2, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2025-08-06 00:00:00', 4, '2025-10-31 09:03:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'EQ00020', 'mehedi hasan', 'mehedi hasan', 'md', '0414222840', 'mahdimahmud.dolu@gmail.com', '[\"3\"]', 2, '', '', '7', '5333', 2, 'Friend', '', 1, 'Sayek', 1, '1749/1849', '[\"2\",\"3\"]', 2, '', '', '', 0, 1, 7, 1, '500 student visa ', 0, NULL, '2025-11-14 00:00:00', '2025-11-04 00:00:00', 4, '2025-11-04 05:57:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'EQ00021', 'Suraiya akter', 'Suraiya akter', 'suravee', '0401167697', 'suravee@gmail.com', '[\"3\"]', 2, '', '', '', '5666', 2, 'friend', '', 1, 'tusher das', 1, '1749/1849', '[\"2\",\"8\"]', 2, '', '', '', 0, 1, 7, 1, '500 student visa', 0, NULL, '2025-11-07 00:00:00', '2025-11-04 00:00:00', 4, '2025-11-04 06:37:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'EQ00022', 'test', 'test', 'test', '5345345555', 'saisatya51@gmail.com', '[\"3\",\"4\"]', 0, '', '', '', '345345', 1, 'test', '0', 2, '', 0, '343434', '', 0, '', '', '', 0, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2025-01-01 00:00:00', 1, '2025-11-05 17:10:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'EQ00023', 'testing', 'testing', 'Testing', '7897897899', 'saisatya51@gmail.com', '[\"1\",\"2\"]', 1, '', '', '', '253698', 2, 'Facebook', '0', 2, '', 0, '678', '', 0, '', '', '', 0, 1, 0, 1, '', 0, NULL, '0000-00-00 00:00:00', '2025-11-14 00:00:00', 1, '2025-11-07 07:09:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 'EQ00024', 'j', 'j', 'k', '1231231234', 'test1@gmail.com', '[\"11\"]', 3, 'bd', 'vd', '4', '123123', 2, 'v', '0', 1, '', 1, 'dqwdw', '', 2, '', '', '', 0, 1, 0, 1, '', 0, NULL, '2025-11-12 00:00:00', '2025-11-07 00:00:00', 1, '2025-11-07 07:09:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'EQ00025', 'Monika Dighe', 'Monika Dighe', 'dighe', '9874563210', 'monikashinde8421@gmail.com', '[\"1\",\"2\"]', 1, 'test', 'test', '1', '422013', 1, '', '', 2, '', 0, '10.50', '', 1, 'ethnicity', '', 'test', 0, 1, 2, 1, '', 0, NULL, '2026-05-30 00:00:00', '1999-04-29 00:00:00', 39, '2026-05-14 16:00:34', NULL, NULL, NULL, 1, '', NULL, NULL, 39, ''),
(26, 'EQ00026', 'Rakesh', 'Rakesh', 'kandekar', '9874563214', 'shindemonika9284@gmail.com', '[\"1\",\"2\"]', 1, 'test', 'test', '6', '456321', 1, '', '', 2, '', 2, '10.50', '', 2, 'test', '', 'testdf', 0, 1, 2, 1, '', 0, NULL, '2026-05-30 00:00:00', '1999-05-01 00:00:00', 40, '2026-05-19 08:57:58', 0, '2026-05-19 10:57:58', NULL, 1, '', NULL, NULL, 40, '');

-- --------------------------------------------------------

--
-- Table structure for table `student_enrolment`
--

CREATE TABLE `student_enrolment` (
  `st_enrol_id` int(11) NOT NULL,
  `st_unique_id` varchar(255) DEFAULT NULL,
  `st_enquiry_id` varchar(255) DEFAULT NULL,
  `st_qualifications` varchar(1) DEFAULT NULL,
  `st_enrol_course` text DEFAULT NULL,
  `st_venue` varchar(15) NOT NULL,
  `st_middle_name` varchar(255) NOT NULL,
  `st_name` varchar(255) NOT NULL,
  `st_mobile` varchar(255) NOT NULL,
  `st_email` varchar(255) NOT NULL,
  `st_source` varchar(1) DEFAULT NULL,
  `st_given_name` varchar(255) NOT NULL,
  `st_enrol_status` tinyint(1) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_enrolment`
--

INSERT INTO `student_enrolment` (`st_enrol_id`, `st_unique_id`, `st_enquiry_id`, `st_qualifications`, `st_enrol_course`, `st_venue`, `st_middle_name`, `st_name`, `st_mobile`, `st_email`, `st_source`, `st_given_name`, `st_enrol_status`, `created_date`, `created_by`, `modified_date`, `modified_by`) VALUES
(1, '2026ENR0006', 'EQ00022', '', '0', '', '', 'test', '3453453455', 'saisatya51@gmail.com', '', 'test', 0, '2026-02-01 10:31:59', NULL, NULL, NULL),
(2, '2026ENR0007', 'EQ00022', '', '0', '', '', 'test', '3453453455', 'saisatya51@gmail.com', '', 'test', 0, '2026-02-01 10:33:35', NULL, NULL, NULL),
(3, '2026A40008', '', '', '4', '', '', 'sai', '3453453455', 'saisatya51@gmail.com', '', 'satya', 0, '2026-02-01 10:50:57', NULL, NULL, NULL),
(4, '2026CHC330210014', 'EQ00001', '', '1', '', '', 'dighe', '9874563214', 'monikashinde8421@gmail.com', '', 'monika', 0, '2026-05-17 12:24:53', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_enrolments`
--

CREATE TABLE `student_enrolments` (
  `st_enrol_id` int(11) NOT NULL,
  `st_unique_id` varchar(255) DEFAULT NULL,
  `st_enquiry_id` varchar(255) DEFAULT NULL,
  `st_rto_name` varchar(255) DEFAULT NULL,
  `st_courses` text DEFAULT NULL,
  `st_branch` varchar(255) DEFAULT NULL,
  `st_photo` varchar(255) DEFAULT NULL,
  `st_given_name` varchar(255) DEFAULT NULL,
  `st_surname` varchar(255) DEFAULT NULL,
  `st_dob` date DEFAULT NULL,
  `st_country_birth` varchar(255) DEFAULT NULL,
  `st_street` varchar(255) DEFAULT NULL,
  `st_suburb` varchar(255) DEFAULT NULL,
  `st_state` varchar(255) DEFAULT NULL,
  `st_post_code` varchar(255) DEFAULT NULL,
  `st_tel_num` varchar(255) DEFAULT NULL,
  `st_email` varchar(255) DEFAULT NULL,
  `st_mobile` varchar(255) DEFAULT NULL,
  `st_emerg_name` varchar(255) DEFAULT NULL,
  `st_emerg_relation` varchar(255) DEFAULT NULL,
  `st_emerg_mobile` varchar(255) DEFAULT NULL,
  `st_emerg_agree` varchar(1) DEFAULT NULL,
  `st_usi` varchar(255) DEFAULT NULL,
  `st_emp_status` varchar(1) DEFAULT NULL,
  `st_self_status` varchar(1) DEFAULT NULL,
  `st_citizenship` varchar(1) DEFAULT NULL,
  `st_gender` varchar(1) DEFAULT NULL,
  `st_credit_transfer` varchar(1) DEFAULT NULL,
  `st_highest_school` varchar(1) DEFAULT NULL,
  `st_secondary_school` varchar(1) DEFAULT NULL,
  `st_born_country` varchar(1) DEFAULT NULL,
  `st_born_country_other` varchar(255) DEFAULT NULL,
  `st_origin` varchar(1) DEFAULT NULL,
  `st_lan_spoken` varchar(1) DEFAULT NULL,
  `st_lan_spoken_other` varchar(255) DEFAULT NULL,
  `st_disability` varchar(1) DEFAULT NULL,
  `st_disability_type` text DEFAULT NULL,
  `st_disability_type_other` varchar(255) DEFAULT NULL,
  `st_study_reason` varchar(1) DEFAULT NULL,
  `st_study_reason_other` varchar(255) DEFAULT NULL,
  `st_qual_1` varchar(1) DEFAULT NULL,
  `st_qual_2` varchar(1) DEFAULT NULL,
  `st_qual_3` varchar(1) DEFAULT NULL,
  `st_qual_4` varchar(1) DEFAULT NULL,
  `st_qual_5` varchar(1) DEFAULT NULL,
  `st_qual_6` varchar(1) DEFAULT NULL,
  `st_qual_7` varchar(1) DEFAULT NULL,
  `st_qual_8` varchar(1) DEFAULT NULL,
  `st_qual_9` varchar(1) DEFAULT NULL,
  `st_qual_10` varchar(1) DEFAULT NULL,
  `st_qual_8_other` varchar(255) DEFAULT NULL,
  `st_qual_9_other` date DEFAULT NULL,
  `st_qual_10_other` varchar(255) DEFAULT NULL,
  `st_status` tinyint(1) DEFAULT 0,
  `st_created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `st_created_by` int(11) DEFAULT NULL,
  `st_modified_date` date DEFAULT NULL,
  `st_modified_by` int(11) DEFAULT NULL,
  `qualification_code_title` varchar(255) DEFAULT NULL,
  `age_declaration_18` tinyint(1) DEFAULT NULL,
  `city_of_birth` varchar(255) DEFAULT NULL,
  `postal_same_as_above` tinyint(1) DEFAULT NULL,
  `postal_address` text DEFAULT NULL,
  `english_read_write` tinyint(1) DEFAULT NULL,
  `work_phone` varchar(50) DEFAULT NULL,
  `home_phone` varchar(50) DEFAULT NULL,
  `year_completed_school` varchar(20) DEFAULT NULL,
  `mode_delivery` varchar(100) DEFAULT NULL,
  `qualification_attained` varchar(50) DEFAULT NULL,
  `industry_of_work` varchar(255) DEFAULT NULL,
  `computer_access` tinyint(1) DEFAULT NULL,
  `computer_literacy` varchar(20) DEFAULT NULL,
  `numeracy_skills` varchar(20) DEFAULT NULL,
  `additional_support` tinyint(1) DEFAULT NULL,
  `additional_support_specify` text DEFAULT NULL,
  `usi_declaration` tinyint(1) DEFAULT NULL,
  `privacy_declaration` tinyint(1) DEFAULT NULL,
  `refund_declaration` tinyint(1) DEFAULT NULL,
  `office_student_id` varchar(100) DEFAULT NULL,
  `office_coordinator_name` varchar(255) DEFAULT NULL,
  `office_invoice_provided` tinyint(1) DEFAULT NULL,
  `office_receipt_collected` tinyint(1) DEFAULT NULL,
  `office_lms_access` tinyint(1) DEFAULT NULL,
  `office_resources_access` tinyint(1) DEFAULT NULL,
  `office_uploaded_sms` tinyint(1) DEFAULT NULL,
  `office_welcome_pack_sent` tinyint(1) DEFAULT NULL,
  `candidate_declaration` tinyint(1) DEFAULT NULL,
  `candidate_full_name` varchar(255) DEFAULT NULL,
  `candidate_date` date DEFAULT NULL,
  `candidate_signature` varchar(500) DEFAULT NULL,
  `form_source` varchar(20) DEFAULT 'legacy'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_enrolments`
--

INSERT INTO `student_enrolments` (`st_enrol_id`, `st_unique_id`, `st_enquiry_id`, `st_rto_name`, `st_courses`, `st_branch`, `st_photo`, `st_given_name`, `st_surname`, `st_dob`, `st_country_birth`, `st_street`, `st_suburb`, `st_state`, `st_post_code`, `st_tel_num`, `st_email`, `st_mobile`, `st_emerg_name`, `st_emerg_relation`, `st_emerg_mobile`, `st_emerg_agree`, `st_usi`, `st_emp_status`, `st_self_status`, `st_citizenship`, `st_gender`, `st_credit_transfer`, `st_highest_school`, `st_secondary_school`, `st_born_country`, `st_born_country_other`, `st_origin`, `st_lan_spoken`, `st_lan_spoken_other`, `st_disability`, `st_disability_type`, `st_disability_type_other`, `st_study_reason`, `st_study_reason_other`, `st_qual_1`, `st_qual_2`, `st_qual_3`, `st_qual_4`, `st_qual_5`, `st_qual_6`, `st_qual_7`, `st_qual_8`, `st_qual_9`, `st_qual_10`, `st_qual_8_other`, `st_qual_9_other`, `st_qual_10_other`, `st_status`, `st_created_date`, `st_created_by`, `st_modified_date`, `st_modified_by`, `qualification_code_title`, `age_declaration_18`, `city_of_birth`, `postal_same_as_above`, `postal_address`, `english_read_write`, `work_phone`, `home_phone`, `year_completed_school`, `mode_delivery`, `qualification_attained`, `industry_of_work`, `computer_access`, `computer_literacy`, `numeracy_skills`, `additional_support`, `additional_support_specify`, `usi_declaration`, `privacy_declaration`, `refund_declaration`, `office_student_id`, `office_coordinator_name`, `office_invoice_provided`, `office_receipt_collected`, `office_lms_access`, `office_resources_access`, `office_uploaded_sms`, `office_welcome_pack_sent`, `candidate_declaration`, `candidate_full_name`, `candidate_date`, `candidate_signature`, `form_source`) VALUES
(1, '1', '', 'rto nam asdf', '[\"3\"]', 'branch babsdf', '930837test.png', 'agia test', 'surn asdf', '2023-12-31', 'adsfas', 'adfsd', 'dfsdfg', '1', '798798', '987987987', 'asdfa@gmail.com', '87788', 'asdfsafd', 'dsfgsdf', '989879879898', '1', 'asdfasdf', '1', '3', '1', '1', '1', '3', '1', '1', '', '1', '2', '', '2', '[]', '', '1', 'asdfas', '1', '1', '1', '1', '1', '1', '1', '2', '2', '2', '', '0000-00-00', '', 0, '2023-10-18 00:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(2, '1', 'EQ0002', 'rto nam asdf', '[\"3\"]', 'branch babsdf', '675734test.png', 'agia test', 'surn asdf', '2023-12-31', 'adsfas', 'adfsd', 'dfsdfg', '1', '798798', '987987987', 'asdfa@gmail.com', '87788', 'asdfsafd', 'dsfgsdf', '989879879898', '1', 'asdfasdf', '1', '3', '1', '1', '1', '3', '1', '1', '', '1', '2', '', '2', '[]', '', '1', 'asdfas', '1', '1', '1', '1', '1', '1', '1', '2', '2', '2', '', '0000-00-00', '', 0, '2023-10-18 00:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(3, '1', 'EQ0002', 'rto nam asdf', '[\"3\"]', 'branch babsdf', '509731test.png', 'agia test', 'surn asdf', '2023-12-31', 'adsfas', 'adfsd', 'dfsdfg', '1', '798798', '987987987', 'asdfa@gmail.com', '87788', 'asdfsafd', 'dsfgsdf', '989879879898', '1', 'asdfasdf', '1', '3', '1', '1', '1', '3', '1', '2', 'testsdfsd', '1', '2', '', '2', '[]', '', '1', 'asdfas', '1', '1', '1', '1', '1', '1', '1', '2', '2', '2', '', '0000-00-00', '', 0, '2023-10-18 00:00:00', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(4, '1', 'EQ00003', 'test', '[\"8\",\"10\",\"12\"]', 'testest', '[]', 'testtest', 'testtest', '2025-10-02', 'test', 'test', 'test', '2', '987987', '9879879877', 'testset@gmail.com', '9879879877', 'test', 'test', '9879879877', '1', 'ttestest', '4', '4', '2', '1', '1', '2', '1', '1', '', '1', '2', '', '2', '[]', '[]', '[', '', '1', '1', '1', '1', '1', '1', '1', '2', '2', '2', '', '0000-00-00', '', 0, '2025-10-21 17:46:06', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(5, '1', 'EQ00003', 'test', '[\"8\",\"10\",\"12\"]', 'testest', '[]', 'testtest', 'testtest', '2025-10-02', 'test', 'test', 'test', '2', '987987', '9879879877', 'testset@gmail.com', '9879879877', 'test', 'test', '9879879877', '1', 'ttestest', '4', '4', '2', '1', '1', '2', '1', '1', '', '1', '2', '', '2', '[]', '[]', '[', '', '1', '1', '1', '1', '1', '1', '1', '2', '2', '2', '', '0000-00-00', '', 0, '2025-10-21 17:48:41', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(6, '2026ENR0006', 'EQ00022', 'National Collegteste Australia', '[]', 'test', '[]', 'test', 'test', '1998-10-10', 'India', 'Agraharam', 'Bobbili', 'VIC', '535558', '3453453455', 'saisatya51@gmail.com', '3453453455', 'test', 'cousin', '4564564566', '1', '3453453455', '1', '', '', '1', '1', '1', '1', '', '', '1', '2', '', '2', '[]', '', '1', '', '', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 0, '2026-02-01 10:31:59', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(7, '2026ENR0007', 'EQ00022', 'National Collegteste Australia', '[]', 'test', '[]', 'test', 'test', '1998-10-10', 'India', 'Agraharam', 'Bobbili', 'VIC', '535558', '3453453455', 'saisatya51@gmail.com', '3453453455', 'test', 'cousin', '4564564566', '1', '3453453455', '1', '', '', '1', '1', '1', '1', '', '', '1', '2', '', '2', '[]', '', '1', '', '', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 0, '2026-02-01 10:33:35', 1, NULL, NULL, 'CHC434', 1, 'Bobbili', 1, '', 1, '3453453455', '3453453455', '', 'Classroom', 'Australia', 'INdustry', 2, 'Good', 'Excellent', 1, '', 1, 1, 1, NULL, 'test test', 1, 1, 0, 0, 0, 0, 1, 'satya sai', '2005-10-10', 'test', 'online'),
(8, '2026A40008', '', 'National College Australia', '[\"4\"]', 'test', '[]', 'satya', 'sai', '1998-10-10', 'India', 'Agraharam', 'Bobbili', 'VIC', '535558', '3453453455', 'saisatya51@gmail.com', '3453453455', 'satya sai', 'cousin', '4564564566', '1', '3453453455', '1', '', '', '1', '2', '1', '1', '', '', '2', '2', '', '1', '[\"1\"]', '', '1', '', '', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 0, '2026-02-01 10:50:57', 1, NULL, NULL, 'CHC434', 1, 'Bobbili', 1, '', 1, '3453453455', '3453453455', '233', 'Classroom', 'Equivalent', 'INdustry', 2, 'Excellent', 'Basic', 1, '', 1, 1, 1, NULL, 'test test', 0, 0, 0, 1, 1, 0, 1, 'satya sai', '2004-10-10', 'rwar', 'online'),
(9, NULL, 'EQ00002', '', '[\"7\"]', NULL, NULL, 'Jacob Shane', 'test surnamsdf', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'saisatya51@gmail.com', '8309603262', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 12:09:17', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(10, NULL, 'EQ00016', '', '[\"3\"]', NULL, NULL, 'Sulab', 'Banjade', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sulabbanjade2@gmail.com', '0416576122', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 12:15:31', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(11, NULL, 'EQ00001', '', '[\"14\"]', NULL, NULL, 'test surya', 'mangs', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'saikiran.m.v.s.s@gmail.com', '8309603262', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 12:16:00', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(12, NULL, 'EQ00004', '', '[\"6\"]', NULL, NULL, 'test surya', 'surnam test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'testsai@gmail.com', '8309603263', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 14:14:54', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(13, '', 'EQ00025', '', '[\"1\",\"2\"]', '', '', 'Monika Dighe', 'dighe', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'monikashinde8421@gmail.com', '9874563210', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-14 17:32:55', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy'),
(14, '2026CHC330210014', 'EQ00001', 'National College Australia', '[\"1\",\"3\"]', 'test', '[\"148562_1779020693.jpeg\"]', 'monika', 'dighe', '1999-04-30', 'india', 'test', 'test', 'VIC', '412563', '8523698741', 'monikashinde8421@gmail.com', '9874563214', 'test1', 'test2', '8956321452', '1', '9745665478', '3', '', '', '2', '1', '1', '1', '', '', '1', '1', 'test', '2', '[]', '', '1', '', '', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', 0, '2026-05-17 12:24:53', 5, NULL, NULL, 'CH13695', 1, 'ahilyanagar', 1, '', 1, '5896321478', '8523698741', '2020', 'Classroom', 'Equivalent', '', 1, '', 'Good', 1, '', 1, 1, 1, NULL, '', 0, 0, 0, 0, 0, 0, 1, 'Monika Shinde', '2026-05-17', 'monika', 'online'),
(15, '', 'EQ00026', '', '[\"1\",\"2\"]', '', '', 'Rakesh', 'kandekar', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'shindemonika9284@gmail.com', '9874563214', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2026-05-19 07:32:09', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy');

-- --------------------------------------------------------

--
-- Table structure for table `student_feedback`
--

CREATE TABLE `student_feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `student_name` varchar(200) NOT NULL DEFAULT '',
  `student_email` varchar(200) NOT NULL DEFAULT '',
  `category` varchar(100) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_invoices`
--

CREATE TABLE `student_invoices` (
  `id` int(11) NOT NULL,
  `enrolment_db_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `student_name` varchar(200) DEFAULT NULL,
  `student_id` varchar(100) DEFAULT NULL,
  `email_address` varchar(200) DEFAULT NULL,
  `course_enrolled` text DEFAULT NULL,
  `enrolment_ref` varchar(100) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `invoice_type` varchar(100) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `funding_type` varchar(100) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'AUD',
  `line_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`line_items`)),
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `gst_amount` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total_due` decimal(10,2) DEFAULT 0.00,
  `status` varchar(30) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_invoices`
--

INSERT INTO `student_invoices` (`id`, `enrolment_db_id`, `invoice_number`, `student_name`, `student_id`, `email_address`, `course_enrolled`, `enrolment_ref`, `branch`, `issue_date`, `due_date`, `payment_terms`, `invoice_type`, `payment_method`, `funding_type`, `currency`, `line_items`, `subtotal`, `gst_amount`, `discount`, `total_due`, `status`, `created_at`) VALUES
(1, 2, 'NCA-2026-001', 'Monika  dighe', '2026CERTIFICATEIVINMENTALHEALTH0002', 'monikashinde8421@gmail.com', 'Not specified', 'EQ00025', 'Adelaide', '2026-05-23', '2026-06-22', 'Net 30', 'Tuition Fee', 'Bank Transfer (EFT)', 'Fee-for-Service', 'AUD', '[{\"description\":\"test1\",\"unit\":\"Course\",\"qty\":1,\"unit_price\":5000,\"gst\":\"Yes\",\"amount\":5000},{\"description\":\"test2\",\"unit\":\"Course\",\"qty\":1,\"unit_price\":200,\"gst\":\"Yes\",\"amount\":200},{\"description\":\"test3\",\"unit\":\"Course\",\"qty\":1,\"unit_price\":355,\"gst\":\"Yes\",\"amount\":355}]', 5555.00, 555.50, 0.00, 6110.50, 'pending', '2026-05-23 00:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `student_users`
--

CREATE TABLE `student_users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_users`
--

INSERT INTO `student_users` (`id`, `email`, `password_hash`, `full_name`, `phone`, `status`, `created_date`) VALUES
(1, 'shindemonika1199@gmail.com', '$2y$10$oRXleEbSUHQLiRwH8HGGNu.Yo3t1zdisiayrmWmZD9GMXvRe3aGQC', 'Monika', NULL, 1, '2026-02-21 05:48:11'),
(6, 'monikashinde88421@gmail.com', '$2a$12$zK4thD08jxfjksNsnU52Ge4UZS5Rhwy2mnfWS1wiZdEQQhH.O7aDy', 'Monika Shinde', NULL, 1, '2026-04-20 15:27:28'),
(39, 'monikashinde8421@gmail.com', '$2y$10$qcgPBv.XoIK3fkATtIT8GOIOwQ1ITbcNTrLKi5j73HcBEyLxamz7S', 'Monika Dighe', NULL, 1, '2026-05-13 12:06:29'),
(40, 'shindemonika9284@gmail.com', '$2y$10$QVBKzogq/MrbPf0EAgk.he9A.LFjxQZAaAWGJDhj0GCWaRCbAVDQq', 'Rakesh', NULL, 1, '2026-05-19 07:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_log_id` varchar(255) NOT NULL DEFAULT '0',
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_type` tinyint(1) NOT NULL,
  `user_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_log_id`, `user_name`, `user_email`, `user_password`, `user_type`, `user_status`, `created_date`, `modified_date`) VALUES
(1, 'ST56F54', 'test1', 'test123@gmail.com', 'test', 1, 0, '2023-08-20 04:36:13', NULL),
(2, '082623DSB0001', 'test2', 'test234@gmail.com', 'test2', 0, 0, '2023-08-20 04:36:13', NULL),
(3, 'CDB4448E', 'testing123', 'testing123@gmail.com', 'testing123', 0, 0, '2025-10-21 17:49:05', '2025-10-22 06:29:40'),
(4, '36B81E75', 'Prasangi', 'prasangi@nca.edu.au', 'test123', 1, 0, '2025-10-22 00:47:58', '2025-10-22 07:42:19'),
(5, 'A566E63D', 'ujala', 'monikashinde8421@gmail.com', 'test1234', 1, 0, '2025-10-28 00:44:28', '2025-10-28 00:44:57');

-- --------------------------------------------------------

--
-- Table structure for table `venue`
--

CREATE TABLE `venue` (
  `venue_id` int(11) NOT NULL,
  `venue_name` varchar(255) NOT NULL,
  `venue_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `venue`
--

INSERT INTO `venue` (`venue_id`, `venue_name`, `venue_status`, `created_date`) VALUES
(1, 'Adeladie', 0, '2023-08-23 11:38:00'),
(2, 'New Jersey', 0, '2023-08-23 11:38:00'),
(3, 'Australia', 0, '2023-08-23 11:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `visa_statuses`
--

CREATE TABLE `visa_statuses` (
  `visa_id` int(11) NOT NULL,
  `visa_status_name` varchar(255) NOT NULL,
  `visa_state_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `visa_statuses`
--

INSERT INTO `visa_statuses` (`visa_id`, `visa_status_name`, `visa_state_status`, `created_date`) VALUES
(1, 'Dependent on subclass 500', 0, '2023-08-23 10:47:23'),
(2, '489 visa', 0, '2023-08-23 10:47:23'),
(3, '491', 0, '2023-08-23 10:47:29'),
(4, 'Visitor’s visa', 0, '2023-09-16 05:52:01'),
(5, 'Permanent resident', 0, '2023-09-16 05:52:01'),
(6, 'Citizen', 0, '2023-09-16 05:52:18'),
(7, 'Other', 0, '2023-09-16 05:52:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `purpose_id` (`purpose_id`),
  ADD KEY `attendee_type_id` (`attendee_type_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `platform_id` (`platform_id`),
  ADD KEY `appointment_date` (`appointment_date`),
  ADD KEY `appointment_status` (`appointment_status`);

--
-- Indexes for table `appointment_attendee_types`
--
ALTER TABLE `appointment_attendee_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `appointment_blocks`
--
ALTER TABLE `appointment_blocks`
  ADD PRIMARY KEY (`block_id`),
  ADD KEY `block_date` (`block_date`);

--
-- Indexes for table `appointment_locations`
--
ALTER TABLE `appointment_locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `appointment_platforms`
--
ALTER TABLE `appointment_platforms`
  ADD PRIMARY KEY (`platform_id`);

--
-- Indexes for table `appointment_purposes`
--
ALTER TABLE `appointment_purposes`
  ADD PRIMARY KEY (`purpose_id`);

--
-- Indexes for table `appointment_reminders`
--
ALTER TABLE `appointment_reminders`
  ADD PRIMARY KEY (`reminder_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `assessment`
--
ALTER TABLE `assessment`
  ADD PRIMARY KEY (`assessment_id`);

--
-- Indexes for table `assessment_answers`
--
ALTER TABLE `assessment_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `student_enrol_id` (`student_enrol_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `assessment_assignments`
--
ALTER TABLE `assessment_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `student_enrol_id` (`student_enrol_id`);

--
-- Indexes for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD UNIQUE KEY `assessment_student` (`assessment_id`,`student_enrol_id`),
  ADD KEY `student_enrol_id` (`student_enrol_id`);

--
-- Indexes for table `counseling_details`
--
ALTER TABLE `counseling_details`
  ADD PRIMARY KEY (`counsil_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `course_cancellations`
--
ALTER TABLE `course_cancellations`
  ADD PRIMARY KEY (`cancellation_id`);

--
-- Indexes for table `course_extensions`
--
ALTER TABLE `course_extensions`
  ADD PRIMARY KEY (`extension_id`);

--
-- Indexes for table `crm_email_log`
--
ALTER TABLE `crm_email_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_staff` (`sent_by_user_id`),
  ADD KEY `idx_enquiry` (`st_enquiry_id`),
  ADD KEY `idx_st_id` (`st_id`),
  ADD KEY `idx_category` (`email_category`),
  ADD KEY `idx_status` (`send_status`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`);

--
-- Indexes for table `enquiry_forms`
--
ALTER TABLE `enquiry_forms`
  ADD PRIMARY KEY (`enq_form_id`);

--
-- Indexes for table `enrolment_form_new`
--
ALTER TABLE `enrolment_form_new`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrolment_queries`
--
ALTER TABLE `enrolment_queries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enrolment` (`enrolment_id`);

--
-- Indexes for table `followup_calls`
--
ALTER TABLE `followup_calls`
  ADD PRIMARY KEY (`flw_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`inv_id`);

--
-- Indexes for table `login_otp_challenges`
--
ALTER TABLE `login_otp_challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_bind` (`session_bind`),
  ADD KEY `idx_channel_email_active` (`channel`,`email`,`is_used`,`expires_at`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD PRIMARY KEY (`qualification_id`);

--
-- Indexes for table `regular_group_form`
--
ALTER TABLE `regular_group_form`
  ADD PRIMARY KEY (`reg_grp_id`);

--
-- Indexes for table `rpl_enquries`
--
ALTER TABLE `rpl_enquries`
  ADD PRIMARY KEY (`rpl_enq_id`);

--
-- Indexes for table `short_group_form`
--
ALTER TABLE `short_group_form`
  ADD PRIMARY KEY (`sh_grp_id`);

--
-- Indexes for table `slot_book`
--
ALTER TABLE `slot_book`
  ADD PRIMARY KEY (`slot_bk_id`);

--
-- Indexes for table `source`
--
ALTER TABLE `source`
  ADD PRIMARY KEY (`source_id`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`st_at_id`);

--
-- Indexes for table `student_docs`
--
ALTER TABLE `student_docs`
  ADD PRIMARY KEY (`st_doc_id`);

--
-- Indexes for table `student_enquiry`
--
ALTER TABLE `student_enquiry`
  ADD PRIMARY KEY (`st_id`);

--
-- Indexes for table `student_enrolment`
--
ALTER TABLE `student_enrolment`
  ADD PRIMARY KEY (`st_enrol_id`);

--
-- Indexes for table `student_enrolments`
--
ALTER TABLE `student_enrolments`
  ADD PRIMARY KEY (`st_enrol_id`);

--
-- Indexes for table `student_feedback`
--
ALTER TABLE `student_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student` (`student_user_id`);

--
-- Indexes for table `student_invoices`
--
ALTER TABLE `student_invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_users`
--
ALTER TABLE `student_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_log_id` (`user_log_id`);

--
-- Indexes for table `venue`
--
ALTER TABLE `venue`
  ADD PRIMARY KEY (`venue_id`);

--
-- Indexes for table `visa_statuses`
--
ALTER TABLE `visa_statuses`
  ADD PRIMARY KEY (`visa_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointment_attendee_types`
--
ALTER TABLE `appointment_attendee_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointment_blocks`
--
ALTER TABLE `appointment_blocks`
  MODIFY `block_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointment_locations`
--
ALTER TABLE `appointment_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointment_platforms`
--
ALTER TABLE `appointment_platforms`
  MODIFY `platform_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointment_purposes`
--
ALTER TABLE `appointment_purposes`
  MODIFY `purpose_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `appointment_reminders`
--
ALTER TABLE `appointment_reminders`
  MODIFY `reminder_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `assessment_answers`
--
ALTER TABLE `assessment_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `assessment_assignments`
--
ALTER TABLE `assessment_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `assessment_submissions`
--
ALTER TABLE `assessment_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `counseling_details`
--
ALTER TABLE `counseling_details`
  MODIFY `counsil_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `course_cancellations`
--
ALTER TABLE `course_cancellations`
  MODIFY `cancellation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_extensions`
--
ALTER TABLE `course_extensions`
  MODIFY `extension_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `crm_email_log`
--
ALTER TABLE `crm_email_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enquiry_forms`
--
ALTER TABLE `enquiry_forms`
  MODIFY `enq_form_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrolment_form_new`
--
ALTER TABLE `enrolment_form_new`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `enrolment_queries`
--
ALTER TABLE `enrolment_queries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `followup_calls`
--
ALTER TABLE `followup_calls`
  MODIFY `flw_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `inv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_otp_challenges`
--
ALTER TABLE `login_otp_challenges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `payment_records`
--
ALTER TABLE `payment_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `qualifications`
--
ALTER TABLE `qualifications`
  MODIFY `qualification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `regular_group_form`
--
ALTER TABLE `regular_group_form`
  MODIFY `reg_grp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rpl_enquries`
--
ALTER TABLE `rpl_enquries`
  MODIFY `rpl_enq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `short_group_form`
--
ALTER TABLE `short_group_form`
  MODIFY `sh_grp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `slot_book`
--
ALTER TABLE `slot_book`
  MODIFY `slot_bk_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `source`
--
ALTER TABLE `source`
  MODIFY `source_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `st_at_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `student_docs`
--
ALTER TABLE `student_docs`
  MODIFY `st_doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_enquiry`
--
ALTER TABLE `student_enquiry`
  MODIFY `st_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `student_enrolment`
--
ALTER TABLE `student_enrolment`
  MODIFY `st_enrol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_enrolments`
--
ALTER TABLE `student_enrolments`
  MODIFY `st_enrol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `student_feedback`
--
ALTER TABLE `student_feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_invoices`
--
ALTER TABLE `student_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_users`
--
ALTER TABLE `student_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `venue`
--
ALTER TABLE `venue`
  MODIFY `venue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `visa_statuses`
--
ALTER TABLE `visa_statuses`
  MODIFY `visa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
