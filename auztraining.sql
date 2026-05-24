-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: auztraining
-- ------------------------------------------------------
-- Server version	8.4.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `appointment_attendee_types`
--

DROP TABLE IF EXISTS `appointment_attendee_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_attendee_types` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  `type_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_attendee_types`
--

LOCK TABLES `appointment_attendee_types` WRITE;
/*!40000 ALTER TABLE `appointment_attendee_types` DISABLE KEYS */;
INSERT INTO `appointment_attendee_types` VALUES (1,'Student',0,'2025-12-26 07:10:25'),(2,'Business Purpose',0,'2025-12-26 07:10:25');
/*!40000 ALTER TABLE `appointment_attendee_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_blocks`
--

DROP TABLE IF EXISTS `appointment_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_blocks` (
  `block_id` int NOT NULL AUTO_INCREMENT,
  `block_date` date NOT NULL,
  `block_start_time` time NOT NULL,
  `block_end_time` time NOT NULL,
  `block_reason` varchar(255) DEFAULT NULL,
  `staff_member_id` int DEFAULT NULL COMMENT 'NULL means all staff',
  `block_status` tinyint(1) DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`block_id`),
  KEY `block_date` (`block_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_blocks`
--

LOCK TABLES `appointment_blocks` WRITE;
/*!40000 ALTER TABLE `appointment_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointment_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_locations`
--

DROP TABLE IF EXISTS `appointment_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_locations` (
  `location_id` int NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) NOT NULL,
  `location_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_locations`
--

LOCK TABLES `appointment_locations` WRITE;
/*!40000 ALTER TABLE `appointment_locations` DISABLE KEYS */;
INSERT INTO `appointment_locations` VALUES (1,'Adelaide Office',0,'2025-12-26 07:10:25'),(2,'Melbourne Office',0,'2025-12-26 07:10:25'),(3,'Online',0,'2025-12-26 07:10:25');
/*!40000 ALTER TABLE `appointment_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_platforms`
--

DROP TABLE IF EXISTS `appointment_platforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_platforms` (
  `platform_id` int NOT NULL AUTO_INCREMENT,
  `platform_name` varchar(255) NOT NULL,
  `platform_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`platform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_platforms`
--

LOCK TABLES `appointment_platforms` WRITE;
/*!40000 ALTER TABLE `appointment_platforms` DISABLE KEYS */;
INSERT INTO `appointment_platforms` VALUES (1,'Zoom',0,'2025-12-26 07:10:25'),(2,'Google Meet',0,'2025-12-26 07:10:25'),(3,'Outlook',0,'2025-12-26 07:10:25');
/*!40000 ALTER TABLE `appointment_platforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_purposes`
--

DROP TABLE IF EXISTS `appointment_purposes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_purposes` (
  `purpose_id` int NOT NULL AUTO_INCREMENT,
  `purpose_name` varchar(255) NOT NULL,
  `purpose_color` varchar(20) DEFAULT '#0bb197',
  `purpose_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`purpose_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_purposes`
--

LOCK TABLES `appointment_purposes` WRITE;
/*!40000 ALTER TABLE `appointment_purposes` DISABLE KEYS */;
INSERT INTO `appointment_purposes` VALUES (1,'Counselling','#0bb197',0,'2025-12-26 07:10:25'),(2,'Complaints','#ff3d60',0,'2025-12-26 07:10:25'),(3,'Course Withdrawal','#fcb92c',0,'2025-12-26 07:10:25'),(4,'Enrolment','#4aa3ff',0,'2025-12-26 07:10:25'),(5,'Assignments','#564ab1',0,'2025-12-26 07:10:25'),(6,'Logbook Submission','#0ac074',0,'2025-12-26 07:10:25');
/*!40000 ALTER TABLE `appointment_purposes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_reminders`
--

DROP TABLE IF EXISTS `appointment_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_reminders` (
  `reminder_id` int NOT NULL AUTO_INCREMENT,
  `appointment_id` int NOT NULL,
  `reminder_sent` tinyint(1) DEFAULT '0',
  `reminder_sent_date` datetime DEFAULT NULL,
  `reminder_type` varchar(50) DEFAULT NULL COMMENT 'email, notification',
  `reminder_to` int DEFAULT NULL COMMENT 'Staff member ID',
  `reminder_supervisor` int DEFAULT NULL COMMENT 'Supervisor ID',
  `missed_meeting_notification` tinyint(1) DEFAULT '0',
  `missed_meeting_sent_date` datetime DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reminder_id`),
  KEY `appointment_id` (`appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_reminders`
--

LOCK TABLES `appointment_reminders` WRITE;
/*!40000 ALTER TABLE `appointment_reminders` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointment_reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `appointment_id` int NOT NULL AUTO_INCREMENT,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_datetime` datetime NOT NULL,
  `booked_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `booked_by` int NOT NULL,
  `booked_by_name` varchar(255) DEFAULT NULL,
  `booking_comments` text,
  `purpose_id` int NOT NULL,
  `appointment_to_see` int DEFAULT NULL COMMENT 'Staff member ID',
  `appointment_status` varchar(50) DEFAULT 'scheduled' COMMENT 'scheduled, completed, cancelled, no-show, missed',
  `meeting_happened` tinyint(1) DEFAULT '0',
  `attendee_type_id` int NOT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `student_phone` varchar(20) DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `business_contact` varchar(255) DEFAULT NULL,
  `send_email` tinyint(1) DEFAULT '1',
  `staff_member_type` varchar(50) DEFAULT NULL COMMENT 'Admin, Trainers, Management',
  `staff_member_id` int DEFAULT NULL,
  `meeting_type` varchar(50) NOT NULL COMMENT 'Online, Face to Face, Phone',
  `location_id` int DEFAULT NULL,
  `platform_id` int DEFAULT NULL,
  `online_meeting_link` text,
  `timezone_state` varchar(100) DEFAULT NULL COMMENT 'State timezone (e.g., Melbourne, Adelaide)',
  `appointment_time_state` datetime DEFAULT NULL COMMENT 'Appointment time in state timezone',
  `appointment_time_adelaide` datetime DEFAULT NULL COMMENT 'Appointment time in Adelaide timezone',
  `appointment_time_india` datetime DEFAULT NULL COMMENT 'Appointment time in India timezone',
  `appointment_time_philippines` datetime DEFAULT NULL COMMENT 'Appointment time in Philippines timezone',
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `connected_enquiry_id` varchar(255) DEFAULT NULL COMMENT 'Link to student_enquiry.st_enquiry_id',
  `connected_enrolment_id` varchar(255) DEFAULT NULL COMMENT 'Link to student_enrolments.st_unique_id',
  `connected_counselling_id` int DEFAULT NULL COMMENT 'Link to counseling_details.counsil_id',
  `appointment_notes` text,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `modified_by` int DEFAULT NULL,
  `delete_status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`appointment_id`),
  KEY `purpose_id` (`purpose_id`),
  KEY `attendee_type_id` (`attendee_type_id`),
  KEY `location_id` (`location_id`),
  KEY `platform_id` (`platform_id`),
  KEY `appointment_date` (`appointment_date`),
  KEY `appointment_status` (`appointment_status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,'2025-12-28','05:22:00','2025-12-28 05:22:00','2025-12-26 12:42:17',1,'test1','this is testing',2,1,'scheduled',0,2,'','','','testing','testing@gmail.com',1,'Trainers',NULL,'Face to Face',2,NULL,'','Sydney','2025-12-28 05:22:00','2025-12-28 05:22:00','2025-12-28 05:22:00','2025-12-28 05:22:00',NULL,NULL,NULL,NULL,NULL,'testing','2025-12-26 07:12:17',1,NULL,NULL,0);
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `counseling_details`
--

DROP TABLE IF EXISTS `counseling_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `counseling_details` (
  `counsil_id` int NOT NULL AUTO_INCREMENT,
  `st_enquiry_id` varchar(255) DEFAULT NULL,
  `counsil_mem_name` varchar(255) DEFAULT NULL,
  `counsil_vaccine_status` tinyint(1) DEFAULT NULL,
  `counsil_job_nature` varchar(255) DEFAULT NULL,
  `counsil_module_result` varchar(100) DEFAULT NULL,
  `counsil_timing` timestamp NULL DEFAULT NULL,
  `counsil_end_time` timestamp NULL DEFAULT NULL,
  `counsil_pref_comments` text,
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
  `counsil_remarks` text,
  `counsil_created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `counsil_createdby` int DEFAULT NULL,
  `counsil_modified_date` date DEFAULT NULL,
  `counsil_modified_by` int DEFAULT NULL,
  `counsil_delete_note` varchar(255) DEFAULT NULL,
  `counsil_enquiry_status` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`counsil_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counseling_details`
--

LOCK TABLES `counseling_details` WRITE;
/*!40000 ALTER TABLE `counseling_details` DISABLE KEYS */;
INSERT INTO `counseling_details` VALUES (1,'EQ00002','surya',1,'','','2023-10-02 14:30:00','2023-10-02 16:30:00','nothing','2',2,'','','','nothing',1,'2 years',1,'nothing',2,2,'[\"1\"]','2023-10-01 20:02:58',1,'2023-10-10',1,NULL,0),(2,'EQ00004','test name',2,'','','2023-09-08 18:30:00','2023-09-08 20:30:00','','2',2,'','','','test adge',1,'2 years',1,'name edic',2,2,'','2023-10-02 19:58:16',1,'2023-10-10',1,NULL,0),(3,'EQ00003','fdvfcbf',1,'vfdgdf',' cbfbf','2023-10-06 00:19:00','2023-10-06 01:41:00','vvd','regrgr',1,' fcbcbc',' vbvgbg','bfff','bfbgf',1,'bcfbg',1,'cbvc b',1,1,'[\"1\",\"2\",\"3\"]','2023-10-03 12:50:42',1,'2023-10-07',1,NULL,0),(4,'EQ00010','Krishna',1,'Yes','5.2,6.2','2023-10-05 13:00:00','2023-10-05 14:30:00','Fast Track','8',1,'8.5','Aged Care','OXFORD','1',1,'1 Year',2,'BTECH',1,2,'[\"2\",\"3\",\"13\"]','2023-10-04 11:01:47',1,'2023-10-10',1,NULL,0),(5,'EQ00001','krishna',1,'YES','GOOD','2025-11-01 18:16:00','2025-10-30 18:16:00','','8',1,'8','IS','UNISA','1 YEAR',1,'2 months',3,'BE',1,1,'[\"10\"]','2025-10-29 07:36:45',4,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `counseling_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_cancellations`
--

DROP TABLE IF EXISTS `course_cancellations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_cancellations` (
  `cancellation_id` int NOT NULL AUTO_INCREMENT,
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
  `reason_other_details` text,
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
  `office_comments` text,
  `status` tinyint(1) DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_by` int DEFAULT NULL,
  PRIMARY KEY (`cancellation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_cancellations`
--

LOCK TABLES `course_cancellations` WRITE;
/*!40000 ALTER TABLE `course_cancellations` DISABLE KEYS */;
INSERT INTO `course_cancellations` VALUES (1,'CC00001','Ms','sai','satya','Agraharam','535558','8309603262','saisatya51@gmail.com','1998-06-10','M','DM-3434','Title','2026-01-29','Personal difficulties',NULL,'2026-01-16','Yes',NULL,NULL,NULL,NULL,'Full Name *','Signature *','2026-01-21','Yes','test','2026-01-01',4345.00,'2026-01-01','test',NULL,0,'2026-01-25 08:22:37',NULL,'2026-01-25',1),(2,'CC00002','Ms','sai','satya','Agraharam','535558','8309603262','saisatya51@gmail.com','1998-06-10','M','DM-3434','Title','1998-06-10','Personal difficulties',NULL,'2026-01-01','Yes',NULL,NULL,NULL,NULL,'Full Name *','Signature ','2026-01-21','Yes','test','2026-01-01',345345.00,'2026-01-01','test',NULL,0,'2026-01-25 08:25:55',NULL,'2026-01-25',1),(3,'CC00003','Mr','sai','satya','Agraharam','535558','8309603262','saisatya51@gmail.com','1998-06-10','M','DM-3434','Title','2026-01-01','Transfer to another RTO',NULL,'2026-01-01','Yes',NULL,NULL,NULL,NULL,'Full Name *','Signature *','2026-01-20','','tet','2026-01-01',34.00,'2026-01-01','test','test',0,'2026-01-25 08:28:32',NULL,'2026-01-25',1),(4,'CC00004','Mr','sai','satya','Agraharam','535558','test','saisatya51@gmail.com','1998-06-10','M','DM-3434','Title','1998-06-10','Increased workload',NULL,'2026-01-20','Yes',NULL,NULL,NULL,NULL,'Name','Signature ','0000-00-00','','tet','0026-01-01',345345.00,'2026-01-01','test',NULL,0,'2026-01-25 10:33:00',NULL,'2026-01-25',1);
/*!40000 ALTER TABLE `course_cancellations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_extensions`
--

DROP TABLE IF EXISTS `course_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_extensions` (
  `extension_id` int NOT NULL AUTO_INCREMENT,
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
  `reason_other_details` text,
  `extension_duration` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `extension_approved` varchar(10) DEFAULT NULL,
  `application_approved_by` varchar(255) DEFAULT NULL,
  `approval_initial` varchar(255) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `rollover_fee` decimal(10,2) DEFAULT NULL,
  `office_comments` text,
  `status` tinyint(1) DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_by` int DEFAULT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_extensions`
--

LOCK TABLES `course_extensions` WRITE;
/*!40000 ALTER TABLE `course_extensions` DISABLE KEYS */;
INSERT INTO `course_extensions` VALUES (1,'CE00001','Ms','Family Name *','Given Names *','Residential Address *','535558','8309603262','saisatya51@gmail.com','DM-3434','Title','1998-01-10','Bereavement',NULL,NULL,'Full Name *','Signature ','2026-01-01','Y','test','test','2026-01-01',344.00,NULL,0,'2026-01-25 08:30:46',NULL,'2026-01-25',1);
/*!40000 ALTER TABLE `course_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `course_id` int NOT NULL AUTO_INCREMENT,
  `course_name` varchar(255) NOT NULL,
  `course_sname` varchar(255) NOT NULL,
  `course_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'CHC33021','Certificate III in Individual Support (Ageing)',0,NOW()),(2,'CHC33021','Certificate III in Individual Support (Disability)',0,NOW()),(3,'CHC33021','Certificate III in Individual Support (Ageing & Disability)',0,NOW()),(4,'CHC43015','Certificate IV in Ageing Support',0,NOW()),(5,'CHC43121','Certificate IV in Disability',0,NOW()),(6,'CHC32021','Certificate III in Community Services',0,NOW()),(7,'CHC42021','Certificate IV in Community Services',0,NOW()),(8,'CHC52021','Diploma of Community Services',0,NOW()),(9,'CHC43315','Certificate IV in Mental Health',0,NOW()),(10,'CHC53315','Diploma of Mental Health',0,NOW()),(11,'HLT33021','Certificate III in Allied Health Assistance',0,NOW()),(12,'CHC43415','Certificate IV in Leisure and Health',0,NOW()),(13,'MHT-R','Manual Handling Training (Refresher)',0,NOW()),(14,'MHT-F','Manual Handling Training (Full)',0,NOW()),(15,'Med-R','Medication Training (Refresher)',0,NOW()),(16,'Med-F','Medication Training (Full)',0,NOW()),(17,'Insulin','Safely Injecting Insulin Training',0,NOW());
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `document_id` int NOT NULL AUTO_INCREMENT,
  `document_name` varchar(255) NOT NULL,
  `document_shortcode` varchar(255) NOT NULL,
  `document_status` tinyint(1) NOT NULL DEFAULT '0',
  `document_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`document_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,'Date of  Birth','dob',0,'2023-08-27 11:50:16'),(2,'Address','address',0,'2023-08-27 11:50:16');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enquiry_forms`
--

DROP TABLE IF EXISTS `enquiry_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enquiry_forms` (
  `enq_form_id` int NOT NULL AUTO_INCREMENT,
  `enq_admin_id` int DEFAULT NULL,
  `enq_status` tinyint(1) NOT NULL,
  `enq_created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`enq_form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enquiry_forms`
--

LOCK TABLES `enquiry_forms` WRITE;
/*!40000 ALTER TABLE `enquiry_forms` DISABLE KEYS */;
/*!40000 ALTER TABLE `enquiry_forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followup_calls`
--

DROP TABLE IF EXISTS `followup_calls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `followup_calls` (
  `flw_id` int NOT NULL AUTO_INCREMENT,
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
  `flw_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `flw_created_by` int DEFAULT NULL,
  `flw_modified_date` timestamp NULL DEFAULT NULL,
  `flw_modifiedby` int DEFAULT NULL,
  `flw_enquiry_status` tinyint NOT NULL DEFAULT '0',
  `flw_delete_note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`flw_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followup_calls`
--

LOCK TABLES `followup_calls` WRITE;
/*!40000 ALTER TABLE `followup_calls` DISABLE KEYS */;
INSERT INTO `followup_calls` VALUES (1,'EQ00002','Jacob Shane','8309603262','test person name','2023-10-19 20:00:00','2023-10-12','','','test','phone','2023-10-01 20:01:28',1,'2023-10-01 09:01:46',1,0,NULL),(2,'EQ00008','jaswanth kumar','7306468658','regrg','2023-10-11 22:19:00','2023-10-05',NULL,'[\"1\",\"2\"]','vfbf','vfdf','2023-10-03 12:49:46',1,NULL,NULL,0,NULL),(3,'EQ00010','Prathip','9302265123','Sumanth','2023-10-05 09:00:00','2023-10-05',NULL,'[\"1\",\"14\"]','','Phone','2023-10-04 10:58:29',1,NULL,NULL,0,NULL),(4,'EQ00008','jaswanth kumar','7306468658','Parry','2023-10-05 15:38:00','2023-10-05',NULL,'[\"1\",\"5\"]','','phone','2023-10-05 01:09:17',1,NULL,NULL,0,NULL),(5,'EQ00012','krishna','0411439235','Shambhu','2023-10-12 11:00:00','2023-10-12',NULL,'[\"1\"]','He was busy doing his Uni work. Asked us to call him back in 2 hours time','3cx','2023-10-12 06:12:01',1,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `followup_calls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `inv_id` int NOT NULL AUTO_INCREMENT,
  `inv_auto_id` varchar(255) NOT NULL,
  `st_unique_id` varchar(255) NOT NULL,
  `inv_std_name` varchar(255) NOT NULL,
  `inv_course` tinyint(1) NOT NULL,
  `inv_fee` varchar(255) NOT NULL,
  `inv_paid` varchar(255) NOT NULL,
  `inv_due` varchar(255) NOT NULL,
  `inv_payment_date` date NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `inv_status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`inv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,'INV00001','082623DSB0001','Mike',1,'5000','2000','300','2023-08-11','2023-08-26 15:55:39',0),(2,'INV00002','98798sdf','Kiran',1,'500','3030','200','2023-08-16','2023-08-27 10:33:31',0),(3,'INV202300003','2023B10002','John Kotln',1,'5000','2000','3000','2023-08-18','2023-08-28 02:23:45',0);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_records`
--

DROP TABLE IF EXISTS `payment_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `given_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` text COLLATE utf8mb4_unicode_ci,
  `num_students` text COLLATE utf8mb4_unicode_ci,
  `students_names` text COLLATE utf8mb4_unicode_ci,
  `surname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `totalFees` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,0) DEFAULT NULL,
  `paid_amount` decimal(10,0) DEFAULT NULL,
  `balance_amount` text COLLATE utf8mb4_unicode_ci,
  `paymentDone` decimal(10,2) NOT NULL,
  `installment_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datePaid` date NOT NULL,
  `remainingDue` decimal(10,2) NOT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `instalmentPaid` decimal(10,2) DEFAULT NULL,
  `dateTime` datetime DEFAULT NULL,
  `whoTookPayment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paymentMode` enum('EFTPOS','EFT','Cash','MOTO','Bank Deposit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fundsReceived` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
  `whoChecked` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiptEmailed` enum('Yes','No') COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number` text COLLATE utf8mb4_unicode_ci,
  `invoice_type` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_records`
--

LOCK TABLES `payment_records` WRITE;
/*!40000 ALTER TABLE `payment_records` DISABLE KEYS */;
INSERT INTO `payment_records` VALUES (1,'','{\"name\":\"dfgdfg\",\"email\":\"df@gmail.com\",\"role\":\"dfgdf\",\"phone\":\"5646546544\"}','54654','[\"654\"]','','dfgdf','5646546544','','fgdfg',0.00,654,654654,'654',0.00,NULL,'0000-00-00',0.00,NULL,NULL,'2026-01-01 00:00:00',NULL,'EFTPOS','Yes',NULL,'Yes','INV202500001',2,'2025-03-15 06:06:35'),(2,'','{\"name\":\"dfgdfg\",\"email\":\"df@gmail.com\",\"role\":\"dfgdf\",\"phone\":\"5646546544\"}','54654','[\"654\"]','','dfgdf','5646546544','','fgdfg',0.00,654,654654,'654',0.00,NULL,'0000-00-00',0.00,NULL,NULL,'2026-01-01 00:00:00',NULL,'EFTPOS','Yes',NULL,'Yes','INV202500002',2,'2025-03-15 06:23:33'),(3,'test',NULL,NULL,NULL,' tes','ttest','sett','tes@gmail.com','ttest',334.00,NULL,NULL,NULL,3434.00,NULL,'2025-01-01',3434.00,'tset',334.00,'2025-01-01 00:00:00','tsdfsdf','EFTPOS','Yes','test dfsdf','Yes','INV202500003',1,'2025-03-18 14:48:37'),(4,'test',NULL,NULL,NULL,' tes','ttest','sett','tes@gmail.com','ttest',334.00,NULL,NULL,NULL,3434.00,NULL,'2025-01-01',3434.00,'tset',334.00,'2025-01-01 00:00:00','tsdfsdf','EFTPOS','Yes','test dfsdf','Yes','INV202500004',1,'2025-03-18 14:57:52'),(5,'test',NULL,NULL,NULL,' tes','ttest','sett','tes@gmail.com','ttest',334.00,NULL,NULL,NULL,3434.00,NULL,'2025-01-01',3434.00,'tset',334.00,'2025-01-01 00:00:00','tsdfsdf','EFTPOS','Yes','test dfsdf','Yes','INV202500005',1,'2025-03-18 14:59:21'),(6,'test',NULL,NULL,NULL,' tes','ttest','sett','tes@gmail.com','ttest',334.00,NULL,NULL,NULL,3434.00,NULL,'2025-01-01',3434.00,'tset',334.00,'2025-01-01 00:00:00','tsdfsdf','EFTPOS','Yes','test dfsdf','Yes','INV202500006',1,'2025-03-18 15:00:06'),(7,'test',NULL,NULL,NULL,' tes','ttest','sett','tes@gmail.com','ttest',334.00,NULL,NULL,NULL,3434.00,NULL,'2025-01-01',3434.00,'tset',334.00,'2025-01-01 00:00:00','tsdfsdf','EFTPOS','Yes','test dfsdf','Yes','INV202500007',1,'2025-03-18 15:00:24'),(8,'test',NULL,NULL,NULL,' tes','ttest','sett','tes@gmail.com','ttest',334.00,NULL,NULL,NULL,3434.00,NULL,'2025-01-01',3434.00,'tset',334.00,'2025-01-01 00:00:00','tsdfsdf','EFTPOS','Yes','test dfsdf','Yes','INV202500008',1,'2025-03-18 15:04:33'),(9,'test',NULL,NULL,NULL,'test','test','08309603262','test@gmail.com','test',3345.00,NULL,NULL,NULL,345345.00,NULL,'2025-01-01',45345.00,'sdfsdf',234.00,'0000-00-00 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500009',1,'2025-03-20 14:43:27'),(10,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','root@gmail.com','test',345345.00,NULL,NULL,NULL,345345.00,NULL,'2025-01-01',4456.00,'test',345.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500010',1,'2025-03-20 15:10:29'),(11,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','root@gmail.com','test',345345.00,NULL,NULL,NULL,345345.00,NULL,'2025-01-01',4456.00,'test',345.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500011',1,'2025-03-20 15:10:48'),(12,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','root@gmail.com','test',3453.00,NULL,NULL,NULL,34534.00,NULL,'2025-01-01',34554.00,'test',345345.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500012',1,'2025-03-20 15:11:49'),(13,'Test',NULL,NULL,NULL,'Test','Test','1231231230','test@gmail.com','Test',12.00,NULL,NULL,NULL,12.00,NULL,'2025-03-21',0.00,'No',6.00,'2025-03-18 08:23:00','Hh','Bank Deposit','Yes','Yaga','Yes','INV202500013',1,'2025-03-21 02:53:24'),(14,'Test',NULL,NULL,NULL,'Test','Test','7897897899','test@gmail.com','Test',67.00,NULL,NULL,NULL,67.00,NULL,'2025-03-22',78.00,'Test',56.00,'2025-03-21 08:27:00','Test','EFTPOS','Yes','Test','Yes','INV202500014',1,'2025-03-21 02:58:11'),(15,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','saisatya51@gmail.com','test',345.00,NULL,NULL,NULL,345.00,NULL,'2025-01-01',345.00,'tes',345.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500015',1,'2025-03-21 03:27:26'),(16,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','test@gmail.com','test',123.00,NULL,NULL,NULL,234.00,NULL,'2025-01-01',243.00,'test',234.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500016',1,'2025-03-21 03:32:01'),(17,'Test',NULL,NULL,NULL,'Teat','Test','6786786789','test@gmail.com','Test',56.00,NULL,NULL,NULL,56.00,NULL,'2025-03-21',56.00,'Test',56.00,'2025-03-21 09:03:00','Test','EFTPOS','Yes','Test','Yes','INV202500017',1,'2025-03-21 03:33:39'),(18,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','test@gmail.com','test',345.00,NULL,NULL,NULL,345.00,NULL,'2025-01-01',345.00,'test',345.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500018',1,'2025-03-21 03:35:30'),(19,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','test@gmail.com','test',34.00,NULL,NULL,NULL,345.00,NULL,'2025-01-01',345.00,'test',345.00,'2025-01-01 00:00:00','tets','EFTPOS','Yes','test','Yes','INV202500019',1,'2025-03-21 03:37:15'),(20,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','test@gmail.com','test',34.00,NULL,NULL,NULL,3434.00,NULL,'2025-01-01',345.00,'test',245.00,'2025-01-01 00:00:00','tes','EFTPOS','Yes','test','Yes','INV202500020',1,'2025-03-21 03:38:06'),(21,'Rest',NULL,NULL,NULL,'Test','Test','5675675675','test@gmail.com','Test',577.00,NULL,NULL,NULL,567.00,NULL,'2025-03-21',56.00,'Test',56.00,'2025-03-21 09:09:00','Test','EFTPOS','Yes','Test','Yes','INV202500021',1,'2025-03-21 03:39:55'),(22,'Tets',NULL,NULL,NULL,'Test','Test','12312312309','test@gmail.com','Test',5.00,NULL,NULL,NULL,5.00,NULL,'2025-03-20',12.00,'Hsh',12.00,'2025-03-21 09:11:00','H','EFTPOS','Yes','H','Yes','INV202500022',1,'2025-03-21 03:41:33'),(23,'Parry',NULL,NULL,NULL,'Singh','Adelaide','123456789','parry@auztraining.com.au','Certificate III in Individual Support (Ageing & Disability)',1799.00,NULL,NULL,NULL,1799.00,NULL,'2025-03-21',0.00,'orientation booked',0.00,'2025-03-21 16:17:00','Parry','EFTPOS','Yes','Parry','Yes','INV202500023',1,'2025-03-21 05:48:01'),(24,'',NULL,NULL,NULL,'','','','','',0.00,NULL,NULL,NULL,0.00,NULL,'0000-00-00',0.00,'',0.00,'0000-00-00 00:00:00','','EFTPOS','Yes','','Yes','INV202500024',1,'2025-03-21 06:08:54'),(25,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','saisatya51@gmail.com','[\"certificate-iii-ageing-disability\",\"certificate-iv-aged-care\",\"sdfasdfasdf\"]',34535.00,NULL,NULL,NULL,3.00,'Full Amount Paid','2025-01-01',34532.00,'test',0.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500025',1,'2025-03-24 18:26:36'),(26,'Test',NULL,NULL,NULL,'Test','Test','1231231230','test@gmail.com','[\"certificate-iii-disability\"]',1500.00,NULL,NULL,NULL,750.00,'Fourth Installment','2025-03-25',250.00,'Test',0.00,'2025-03-25 12:33:00','Test','EFTPOS','Yes','Sh','Yes','INV202500026',1,'2025-03-25 07:03:34'),(27,'Test',NULL,NULL,NULL,'Test','Test','6786786689','test@gmail.com','[\"certificate-iii-disability\",\"certificate-iii-ageing-disability\",\"testing\"]',567.00,NULL,NULL,NULL,78.00,'Full Amount Paid','2025-03-24',378.00,'Test',0.00,'2025-03-26 12:48:00','Test','EFTPOS','Yes','Test','Yes','INV202500027',1,'2025-03-25 07:19:38'),(28,'test',NULL,NULL,NULL,'test','adelaide','123456789','parry@auztraining.com.au','[\"certificate-iii-ageing\"]',1749.00,NULL,NULL,NULL,1749.00,'First Installment(Down Payment)','2025-03-27',0.00,'',0.00,'2025-03-27 13:22:00','Parry','EFTPOS','Yes','','Yes','INV202500028',1,'2025-03-27 02:52:57'),(29,'test',NULL,NULL,NULL,'test','adelaide','0469855123','parry@auztraining.com.au','[\"certificate-iii-ageing-disability\"]',1749.00,NULL,NULL,NULL,1749.00,'Full Amount Paid','2025-03-27',0.00,'test comment',0.00,'2025-03-27 13:24:00','Parry','EFTPOS','No','','No','INV202500029',1,'2025-03-27 02:55:01'),(30,'test',NULL,NULL,NULL,'test','adelaide','00000000','parry@auztraining.com.au','[\"certificate-iii-disability\"]',1749.00,NULL,NULL,NULL,1749.00,'Full Amount Paid','2025-03-27',0.00,'',0.00,'0000-00-00 00:00:00','','EFTPOS','Yes','','Yes','INV202500030',1,'2025-03-27 03:07:01'),(31,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','saisatya51@gmail.com','[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Disability)\",\"Certificate IV in Aged Care\",\"Certificate IV in Disability\"]',65465.00,NULL,NULL,NULL,545.00,'Second Installment','2025-01-01',64920.00,'test',0.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500031',1,'2025-03-30 11:45:25'),(32,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','saisatya51@gmail.com','[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Disability)\",\"Certificate IV in Aged Care\",\"Certificate IV in Disability\"]',65465.00,NULL,NULL,NULL,545.00,'Second Installment','2025-01-01',64920.00,'test',0.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500032',1,'2025-03-30 11:45:33'),(33,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','saisatya51@gmail.com','[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Disability)\",\"Certificate IV in Aged Care\",\"Certificate IV in Disability\"]',65465.00,NULL,NULL,NULL,545.00,'Second Installment','2025-01-01',64920.00,'test',0.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500033',1,'2025-03-30 11:46:19'),(34,'MANGIPUDI',NULL,NULL,NULL,'KIRAN','Agraharam street','08309603262','saisatya51@gmail.com','[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Disability)\",\"Certificate III in Individual Support (Ageing & Disability)\"]',654654.00,NULL,NULL,NULL,5454.00,'Second Installment','2025-01-01',649200.00,'test',0.00,'2025-01-01 00:00:00','test','EFTPOS','Yes','test','Yes','INV202500034',1,'2025-03-30 11:48:08'),(35,'Jaswanth',NULL,NULL,NULL,'Kumar','gandhinagar-2','07306468658','jaswanthkumar431@gmail.com','[\"Certificate III in Individual Support (Ageing)\"]',100.00,NULL,NULL,NULL,50.00,'First Installment(Down Payment)','2025-03-30',50.00,'no',0.00,'2025-03-30 20:48:00','j','EFT','Yes','j','Yes','INV202500035',1,'2025-03-30 15:18:38'),(36,'test',NULL,NULL,NULL,'test','adelaide','123456789','parry@auztraining.com.au','[\"Certificate III in Individual Support (Ageing)\"]',1749.00,NULL,NULL,NULL,1749.00,'Second Installment','2025-04-02',1349.00,'',0.00,'2025-04-02 11:01:00','Parry','EFTPOS','Yes','','Yes','INV202500036',1,'2025-04-02 00:31:54'),(37,'test',NULL,NULL,NULL,'test','adelaide','123456789','parry@auztraining.com.au','[\"Certificate III in Individual Support (Ageing)\",\"Certificate III in Individual Support (Ageing & Disability)\"]',1749.00,NULL,NULL,NULL,1000.00,'Full Amount Paid','2025-04-02',1000.00,'test',0.00,'2025-04-02 11:06:00','Parry','EFTPOS','Yes','','Yes','INV202500037',1,'2025-04-02 00:36:29'),(38,'',NULL,NULL,NULL,'','','','','',0.00,NULL,NULL,NULL,0.00,NULL,'0000-00-00',0.00,'',0.00,'0000-00-00 00:00:00','','EFTPOS','Yes','','Yes','INV202500038',1,'2025-10-29 07:19:25'),(39,'Raj',NULL,NULL,NULL,'','SA','411290111','','A',0.00,NULL,NULL,NULL,0.00,NULL,'2025-11-10',0.00,'',0.00,'0000-00-00 00:00:00','','Cash','Yes','','Yes','INV202500039',1,'2025-11-17 07:21:29'),(40,'',NULL,NULL,NULL,'','','','','',0.00,NULL,NULL,NULL,0.00,NULL,'0000-00-00',0.00,'',0.00,'0000-00-00 00:00:00','','EFTPOS','Yes','','Yes','INV202600040',1,'2026-01-04 16:01:38');
/*!40000 ALTER TABLE `payment_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qualifications`
--

DROP TABLE IF EXISTS `qualifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `qualifications` (
  `qualification_id` int NOT NULL AUTO_INCREMENT,
  `qualification_name` varchar(255) NOT NULL,
  `qualification_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`qualification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qualifications`
--

LOCK TABLES `qualifications` WRITE;
/*!40000 ALTER TABLE `qualifications` DISABLE KEYS */;
INSERT INTO `qualifications` VALUES (1,'Masters Degree',0,'2023-08-23 06:07:08'),(2,'Bachelors Degree',0,'2023-08-23 06:07:08'),(3,'MCA',0,'2023-08-23 06:07:16');
/*!40000 ALTER TABLE `qualifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regular_group_form`
--

DROP TABLE IF EXISTS `regular_group_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regular_group_form` (
  `reg_grp_id` int NOT NULL AUTO_INCREMENT,
  `reg_grp_names` text,
  `enq_form_id` int DEFAULT NULL,
  `reg_grp_status` tinyint NOT NULL DEFAULT '0',
  `reg_grp_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reg_grp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regular_group_form`
--

LOCK TABLES `regular_group_form` WRITE;
/*!40000 ALTER TABLE `regular_group_form` DISABLE KEYS */;
INSERT INTO `regular_group_form` VALUES (1,'csef,dfs',24,0,'2025-11-07 07:09:34');
/*!40000 ALTER TABLE `regular_group_form` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rpl_enquries`
--

DROP TABLE IF EXISTS `rpl_enquries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpl_enquries` (
  `rpl_enq_id` int NOT NULL AUTO_INCREMENT,
  `enq_form_id` int DEFAULT NULL,
  `rpl_exp_in` varchar(255) DEFAULT NULL,
  `rpl_exp_role` varchar(255) DEFAULT NULL,
  `rpl_exp_years` varchar(255) DEFAULT NULL,
  `rpl_exp_docs` varchar(1) DEFAULT '0',
  `rpl_exp_prev_qual` varchar(1) DEFAULT '0',
  `rpl_exp_qual_name` varchar(255) NOT NULL,
  `rpl_exp` varchar(1) NOT NULL DEFAULT '0',
  `rpl_exp_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rpl_enq_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rpl_enquries`
--

LOCK TABLES `rpl_enquries` WRITE;
/*!40000 ALTER TABLE `rpl_enquries` DISABLE KEYS */;
INSERT INTO `rpl_enquries` VALUES (1,2,'2','test rolls','5 months ','1','2','','1','2023-10-01 14:26:04'),(2,3,'2','roles ntest','5 months ','1','2','','1','2023-10-01 14:29:22'),(3,5,'','','','','','','2','2023-10-02 15:28:50'),(4,8,'1','test','10','1','1','ffgngf','1','2023-10-03 16:49:07'),(5,10,'1','Senior Helper ','2Years 5 Months','1','1','Post Diploma in Hospitality ','1','2023-10-04 14:44:50'),(6,13,'1','tester','20','1','1','te','1','2024-11-24 13:37:12'),(7,23,'','','','','','','2','2025-11-07 07:09:02');
/*!40000 ALTER TABLE `rpl_enquries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `short_group_form`
--

DROP TABLE IF EXISTS `short_group_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `short_group_form` (
  `sh_grp_id` int NOT NULL AUTO_INCREMENT,
  `enq_form_id` int DEFAULT NULL,
  `sh_org_name` varchar(255) DEFAULT NULL,
  `sh_grp_org_type` tinyint(1) DEFAULT NULL,
  `sh_grp_campus` tinyint(1) DEFAULT NULL,
  `sh_grp_date` date DEFAULT NULL,
  `sh_grp_num_stds` int DEFAULT NULL,
  `sh_grp_ind_exp` tinyint(1) DEFAULT NULL,
  `sh_grp_train_bef` tinyint(1) DEFAULT NULL,
  `sh_grp_con_us` varchar(255) DEFAULT NULL,
  `sh_grp_phone` varchar(255) DEFAULT NULL,
  `sh_grp_name` varchar(255) DEFAULT NULL,
  `sh_grp_email` varchar(255) DEFAULT NULL,
  `sh_grp_status` tinyint(1) NOT NULL DEFAULT '0',
  `sh_grp_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sh_grp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `short_group_form`
--

LOCK TABLES `short_group_form` WRITE;
/*!40000 ALTER TABLE `short_group_form` DISABLE KEYS */;
INSERT INTO `short_group_form` VALUES (1,1,'',0,0,'0000-00-00',0,0,0,'','','','',0,'2023-10-01 14:24:25'),(2,6,'',0,0,'0000-00-00',0,0,0,'phone call','','','',0,'2023-10-01 14:52:01'),(3,11,'MAXWELLL',1,2,'2023-11-19',7,1,1,'PHONE','0466666677','JULIA','',0,'2023-10-12 09:42:17');
/*!40000 ALTER TABLE `short_group_form` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slot_book`
--

DROP TABLE IF EXISTS `slot_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slot_book` (
  `slot_bk_id` int NOT NULL AUTO_INCREMENT,
  `enq_form_id` int NOT NULL,
  `slot_bk_datetime` timestamp NULL DEFAULT NULL,
  `slot_bk_purpose` varchar(255) NOT NULL,
  `slot_bk_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `slot_book_by` varchar(150) NOT NULL,
  `slot_bk_attend` tinyint NOT NULL DEFAULT '1',
  `slot_book_email_link` tinyint NOT NULL,
  PRIMARY KEY (`slot_bk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slot_book`
--

LOCK TABLES `slot_book` WRITE;
/*!40000 ALTER TABLE `slot_book` DISABLE KEYS */;
INSERT INTO `slot_book` VALUES (1,4,'2023-10-12 14:36:00','visiting','2023-09-14 00:00:00','surya',1,1),(2,8,'2023-10-06 02:18:00','vcvc','2023-10-19 00:00:00','vsdfd',1,1),(3,10,'2023-10-05 14:30:00','Inqury','2023-10-05 00:00:00','Sumanth',1,1),(4,1,'2023-10-06 00:23:00','Inqury','2023-10-05 00:00:00','Prathip',1,1),(5,11,'2023-10-13 08:25:00','counseling','2023-10-12 00:00:00','raj',1,1);
/*!40000 ALTER TABLE `slot_book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `source`
--

DROP TABLE IF EXISTS `source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `source` (
  `source_id` int NOT NULL AUTO_INCREMENT,
  `source_name` varchar(255) NOT NULL,
  `source_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source`
--

LOCK TABLES `source` WRITE;
/*!40000 ALTER TABLE `source` DISABLE KEYS */;
INSERT INTO `source` VALUES (1,'Friends',0,'2023-08-23 11:39:15'),(2,'Google',0,'2023-08-23 11:39:15'),(3,'Website',0,'2023-08-23 11:39:19');
/*!40000 ALTER TABLE `source` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_attendance`
--

DROP TABLE IF EXISTS `student_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_attendance` (
  `st_at_id` int NOT NULL AUTO_INCREMENT,
  `st_unique_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `st_course_unit` varchar(255) NOT NULL,
  `st_unit_date` date NOT NULL,
  `st_unit_status` tinyint(1) NOT NULL DEFAULT '0',
  `st_unit_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`st_at_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_attendance`
--

LOCK TABLES `student_attendance` WRITE;
/*!40000 ALTER TABLE `student_attendance` DISABLE KEYS */;
INSERT INTO `student_attendance` VALUES (1,'082623DSB0001','units9','2023-09-09',0,'2023-08-26 15:57:14'),(2,'082623DSB0001','units9','2023-09-07',0,'2023-08-26 15:57:14'),(3,'082623DSB0001','units9','2023-09-07',0,'2023-08-26 15:57:14'),(4,'082623DSB0001','units7','2023-08-25',0,'2023-08-27 04:07:52'),(5,'082623DSB0001','units8','2023-09-08',0,'2023-08-27 04:07:52'),(6,'2023B10003','units7','2023-08-25',0,'2023-08-27 04:07:52'),(7,'2023B10002','units7','2023-08-25',0,'2023-08-27 04:07:52'),(8,'2023B10002','units9','2023-08-25',0,'2023-08-27 04:10:27'),(12,'2023B10002','units9','2023-08-25',0,'2023-08-27 04:12:11'),(16,'2023B10002','units9','2023-08-25',0,'2023-08-27 04:12:44'),(17,'2023B10002','units9','2023-08-25',0,'2023-08-27 04:16:10'),(18,'270823AG0001','units9','2023-08-25',0,'2023-09-11 08:26:48'),(19,'270823AG0001','units7','2023-08-25',0,'2023-09-11 08:26:48'),(20,'270823AG0001','units8','2023-08-25',0,'2023-09-11 08:26:48'),(21,'270823AG0001','units2','2023-08-25',0,'2023-09-11 08:26:48'),(22,'270823AG0001','units9','2023-08-22',0,'2023-09-11 08:26:48'),(23,'270823DSB0002','units9','2023-08-22',0,'2023-09-11 08:26:48'),(24,'270823DSB0002','units2','2023-08-25',0,'2023-09-11 08:26:48'),(25,'270823AG0001','units9','2023-08-25',0,'2023-09-11 08:36:37'),(26,'270823AG0001','units7','2023-08-25',0,'2023-09-11 08:36:37'),(27,'270823AG0001','units8','2023-08-25',0,'2023-09-11 08:36:37'),(28,'270823AG0001','units2','2023-08-25',0,'2023-09-11 08:36:37'),(29,'270823AG0001','units9','2023-08-22',0,'2023-09-11 08:36:37'),(30,'270823DSB0002','units9','2023-08-22',0,'2023-09-11 08:36:37'),(31,'270823DSB0002','units2','2023-08-25',0,'2023-09-11 08:36:37');
/*!40000 ALTER TABLE `student_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_docs`
--

DROP TABLE IF EXISTS `student_docs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_docs` (
  `st_doc_id` int NOT NULL AUTO_INCREMENT,
  `st_unique_id` varchar(255) NOT NULL,
  `st_doc_names` text NOT NULL,
  `st_doc_status` tinyint(1) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `st_modified_date` datetime DEFAULT NULL,
  PRIMARY KEY (`st_doc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_docs`
--

LOCK TABLES `student_docs` WRITE;
/*!40000 ALTER TABLE `student_docs` DISABLE KEYS */;
INSERT INTO `student_docs` VALUES (1,'082623DSB0001','[\"includes/uploads/ADHAAR_1693107526480.pdf||dob\"]',0,'2023-08-27 03:08:04','2023-08-27 00:00:00'),(2,'A566E63D','[\"includes/uploads/PDF_1761640313516.pdf||dob\",\"includes/uploads/PDF_1761640316846.pdf||address\"]',0,'2025-10-28 08:31:53','2025-10-28 00:00:00');
/*!40000 ALTER TABLE `student_docs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_enquiry`
--

DROP TABLE IF EXISTS `student_enquiry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_enquiry` (
  `st_id` int NOT NULL AUTO_INCREMENT,
  `st_enquiry_id` varchar(255) DEFAULT NULL,
  `st_name` varchar(255) NOT NULL,
  `st_member_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_surname` varchar(255) NOT NULL,
  `st_phno` varchar(10) NOT NULL,
  `st_email` varchar(100) NOT NULL,
  `st_course` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `st_course_type` tinyint(1) NOT NULL DEFAULT '0',
  `st_street_details` varchar(255) NOT NULL,
  `st_suburb` varchar(255) NOT NULL,
  `st_state` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `st_post_code` varchar(10) NOT NULL,
  `st_visited` tinyint(1) NOT NULL,
  `st_heared` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `st_hearedby` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `st_refered` tinyint(1) NOT NULL,
  `st_refer_name` text,
  `st_refer_alumni` tinyint(1) NOT NULL,
  `st_fee` varchar(255) NOT NULL,
  `st_remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `st_shore` tinyint(1) NOT NULL,
  `st_ethnicity` varchar(255) DEFAULT NULL,
  `st_comments` text NOT NULL,
  `st_pref_comments` text,
  `st_appoint_book` tinyint(1) NOT NULL,
  `st_enquiry_for` tinyint(1) NOT NULL DEFAULT '1',
  `st_visa_status` tinyint(1) DEFAULT '0',
  `st_visa_condition` tinyint DEFAULT '1',
  `st_visa_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `st_enquiry_status` tinyint(1) NOT NULL DEFAULT '0',
  `st_delete_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_startplan_date` datetime DEFAULT NULL,
  `st_enquiry_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `st_created_by` int NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `st_modified_by` int DEFAULT NULL,
  `st_modified_date` datetime DEFAULT NULL,
  `st_gen_enq_type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`st_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_enquiry`
--

LOCK TABLES `student_enquiry` WRITE;
/*!40000 ALTER TABLE `student_enquiry` DISABLE KEYS */;
INSERT INTO `student_enquiry` VALUES (1,'EQ00001','test surya','John Kotln','mangs','8309603262','saikiran.m.v.s.s@gmail.com','[\"14\"]',4,'street test','subrub streets','3','535552',2,'[\"9\"]','friends',1,'test1,tests2test3',1,'this  is discusseed 3000','',1,'','','',1,2,0,1,'',0,NULL,'2023-10-20 00:00:00','2023-10-14 00:00:00',1,'2023-10-04 14:54:11',1,'2023-10-04 14:54:11',NULL),(2,'EQ00002','Jacob Shane','Jacob Shane','test surnamsdf','8309603262','saisatya51@gmail.com','[\"7\"]',1,'stretasdf asdfa','test surbuasd ','4','538779',1,'[\"3\"]','',2,'',0,'988','',0,'','','',0,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2023-10-05 00:00:00',1,'2023-10-01 14:25:57',NULL,NULL,NULL),(3,'EQ00003','Mike Sheifen','Mike Sheifen','test surnamess s','8309603262','saisatya51@gmail.com','[\"9\"]',1,'test setset','test setset','1','535558',1,'[\"2\"]','',2,'',2,'7986','',1,'','','',0,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2023-10-12 00:00:00',1,'2023-10-03 15:06:24',1,'2023-10-03 15:06:24',NULL),(4,'EQ00004','test surya','test surya','surnam test','8309603263','testsai@gmail.com','[\"6\"]',1,'test street','sub rubs ','2','598798',1,'[\"3\"]','',2,'',0,'987','[\"5\",\"6\"]',1,'indian','no comments','test nothinh',1,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2023-10-05 00:00:00',1,'2023-10-01 14:44:06',NULL,NULL,NULL),(5,'EQ00005','test surya','test surya','test surasd','8309603265','testsurya@gmail.com','[\"2\",\"3\",\"4\"]',1,'street es','sub asdfa','2','535558',1,'[\"2\"]','',2,'',2,'98798','[\"3\",\"4\"]',2,'test indian','','',0,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2023-10-12 00:00:00',1,'2023-10-02 15:28:50',1,'2023-10-02 15:28:50',NULL),(6,'EQ00006','test suryas','test suryas','tes adasd','8309603262','saikira@gmail.com','[\"4\",\"5\"]',5,'strsf you','sub jjs','1','549897',1,'[\"4\"]','',2,'',2,'ads 879879','',2,'','','',0,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2023-10-20 00:00:00',1,'2023-10-02 17:20:12',1,'2023-10-02 17:20:12',NULL),(7,'EQ00007','test surya','test surya','asdf','8309607987','asdfa@gmail.com','[\"4\"]',2,'agraharam street','bobbili','0','535558',2,'[\"3\"]','',2,'',0,'asdf','',0,'','','',0,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2023-12-31 00:00:00',1,'2023-10-02 15:28:32',NULL,NULL,NULL),(8,'EQ00008','jaswanth kumar','jaswanth kumar','kottugummada','7306468658','jaswanthkumar431@gmail.com','[\"1\",\"2\",\"3\",\"4\"]',1,'cdsfs','bfbfd','1','123456',1,'[\"1\",\"3\",\"4\",\"8\"]','',1,'bfdbfbf',1,'455','[\"1\",\"2\",\"3\",\"4\"]',1,'bfdbfdb',' vcbb','bfbfd',1,1,1,1,'',0,NULL,'2023-10-05 00:00:00','2023-10-04 00:00:00',1,'2023-10-03 16:49:06',NULL,NULL,NULL),(9,'EQ00009','jaswanth kumar','jaswanth kumar','kottugummada','7306468657','jaswanthkumar431@gmail.com','[\"1\"]',0,'fvfdb','vfdb','1','134556',1,'[\"1\",\"2\"]','',1,'cbcb',2,'',NULL,0,NULL,'',' fv',0,1,0,1,NULL,0,NULL,'2023-10-04 00:00:00','2023-10-03 13:02:39',0,'2023-10-03 17:02:39',NULL,NULL,1),(10,'EQ00010','Prathip','Prathip','Potnuru','9302265123','ppk.eee@gmail.com','[\"1\",\"7\",\"17\"]',1,'Matam','Narasannapeta','2','532421',1,'[\"1\",\"4\",\"9\"]','Testing',1,'Kiran, Krishna',1,'1500','[\"1\",\"9\",\"13\",\"14\"]',2,'Indian','Test','TEST_2',1,1,2,1,'',0,NULL,'2023-10-10 00:00:00','2023-10-04 00:00:00',1,'2023-10-04 14:44:49',NULL,NULL,NULL),(11,'EQ00011','bindu','bindu','jami','0466978278','bindumadhavi.kottakota@gmail.com','[\"1\"]',2,'65A Fosters road','greenacres','7','5086',1,'[\"1\",\"2\",\"4\"]','',1,'krishna',1,'1000','[\"1\",\"4\",\"6\",\"7\"]',1,'middle east','she is a very tough lady','',1,1,2,1,'',0,NULL,'2023-10-16 00:00:00','1977-05-30 00:00:00',1,'2023-10-12 09:44:21',1,'2023-10-12 09:44:21',NULL),(12,'EQ00012','krishna','krishna','jami','0411439235','JAMI.KRISHNAKUMAR@GMAIL.COM','[\"7\"]',2,'','','0','5086',2,'[\"1\"]','',2,'',0,'1','',0,'','','',0,1,0,1,'',0,NULL,'2023-10-13 00:00:00','2023-10-12 00:00:00',1,'2023-10-12 10:01:03',NULL,NULL,NULL),(13,'EQ00013','test','test','test','6546546544','saitest@gmail.com','[\"6\",\"7\"]',1,'test','test','1','564654',1,'test','',1,'',1,'20000','',0,'','','',0,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2024-12-31 00:00:00',1,'2024-11-24 13:37:05',NULL,NULL,NULL),(14,'EQ00014','Rizika','Rizika','Rizika','0434034862','noemailgivenyet@gmail.com','[\"3\"]',2,'','','7','5999',2,'Google','',2,'',0,'1799/1899','[\"11\"]',2,'','Received calls , provided basic info and booked f2f','',1,1,7,1,'WHV',0,NULL,'0000-00-00 00:00:00','2025-10-16 00:00:00',1,'2025-10-16 06:42:34',NULL,NULL,NULL),(15,'EQ00015','Thavy','Thavy','chheng','0400613017','thavychheng1708@gmail.com','[\"3\"]',2,'','','2','3004',2,'friend','',1,'',1,'1749/1849','',2,'','','',1,1,7,1,'student visa',0,NULL,'0000-00-00 00:00:00','2025-10-17 00:00:00',1,'2025-10-17 06:44:22',NULL,NULL,NULL),(16,'EQ00016','Sulab','Sulab','Banjade','0416576122','sulabbanjade2@gmail.com','[\"3\"]',2,'','','','9999',1,'Shambhu','',1,'Shambhu',2,'1749/1849','[\"2\"]',2,'','','',0,1,1,1,'',0,NULL,'0000-00-00 00:00:00','2025-06-12 00:00:00',4,'2025-10-22 01:32:51',NULL,NULL,NULL),(17,'EQ00017','Sony','Sony','Matthew','0474327231','sonudonu@gmail.com','[\"3\"]',2,'','','2','9999',2,'Friend','',1,'Christine Thomas',1,'1749/1849','[\"1\",\"2\",\"3\"]',2,'','','',0,1,7,1,'482',0,NULL,'0000-00-00 00:00:00','2025-10-21 00:00:00',4,'2025-10-24 03:17:00',NULL,NULL,NULL),(18,'EQ00018','Prabjoth Kaur','Prabjoth Kaur','Aulakh','0459574797','prabhjotkaur2802@gmail.com','[\"3\"]',2,'','','2','9999',1,'Friend','',1,'Navdeep',1,'1799/1899','[\"1\",\"2\",\"3\"]',2,'','','',0,1,1,1,'',0,NULL,'2025-10-30 00:00:00','2025-10-29 00:00:00',4,'2025-10-29 03:53:51',NULL,NULL,NULL),(19,'EQ00019','Sumesh','Raj','Yadav','8108876878','Raj@gmail.com','[\"12\"]',4,'4,Prabhu Nivas,Shiv Tekdi','Thane','','400602',1,'TEST','',1,'',2,'1000','',0,'','','',0,2,0,1,'',0,NULL,'0000-00-00 00:00:00','2025-08-06 00:00:00',4,'2025-10-31 09:03:03',NULL,NULL,NULL),(20,'EQ00020','mehedi hasan','mehedi hasan','md','0414222840','mahdimahmud.dolu@gmail.com','[\"3\"]',2,'','','7','5333',2,'Friend','',1,'Sayek',1,'1749/1849','[\"2\",\"3\"]',2,'','','',0,1,7,1,'500 student visa ',0,NULL,'2025-11-14 00:00:00','2025-11-04 00:00:00',4,'2025-11-04 05:57:21',NULL,NULL,NULL),(21,'EQ00021','Suraiya akter','Suraiya akter','suravee','0401167697','suravee@gmail.com','[\"3\"]',2,'','','','5666',2,'friend','',1,'tusher das',1,'1749/1849','[\"2\",\"8\"]',2,'','','',0,1,7,1,'500 student visa',0,NULL,'2025-11-07 00:00:00','2025-11-04 00:00:00',4,'2025-11-04 06:37:45',NULL,NULL,NULL),(22,'EQ00022','test','test','test','5345345555','saisatya51@gmail.com','[\"3\",\"4\"]',0,'','','','345345',1,'test','0',2,'',0,'343434','',0,'','','',0,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2025-01-01 00:00:00',1,'2025-11-05 17:10:06',NULL,NULL,NULL),(23,'EQ00023','testing','testing','Testing','7897897899','saisatya51@gmail.com','[\"1\",\"2\"]',1,'','','','253698',2,'Facebook','0',2,'',0,'678','',0,'','','',0,1,0,1,'',0,NULL,'0000-00-00 00:00:00','2025-11-14 00:00:00',1,'2025-11-07 07:09:00',NULL,NULL,NULL),(24,'EQ00024','j','j','k','1231231234','test1@gmail.com','[\"11\"]',3,'bd','vd','4','123123',2,'v','0',1,'',1,'dqwdw','',2,'','','',0,1,0,1,'',0,NULL,'2025-11-12 00:00:00','2025-11-07 00:00:00',1,'2025-11-07 07:09:32',NULL,NULL,NULL);
/*!40000 ALTER TABLE `student_enquiry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_enrolment`
--

DROP TABLE IF EXISTS `student_enrolment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_enrolment` (
  `st_enrol_id` int NOT NULL AUTO_INCREMENT,
  `st_unique_id` varchar(255) DEFAULT NULL,
  `st_enquiry_id` varchar(255) DEFAULT NULL,
  `st_qualifications` varchar(1) DEFAULT NULL,
  `st_enrol_course` text,
  `st_venue` varchar(15) NOT NULL,
  `st_middle_name` varchar(255) NOT NULL,
  `st_name` varchar(255) NOT NULL,
  `st_mobile` varchar(255) NOT NULL,
  `st_email` varchar(255) NOT NULL,
  `st_source` varchar(1) DEFAULT NULL,
  `st_given_name` varchar(255) NOT NULL,
  `st_enrol_status` tinyint(1) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `modified_date` date DEFAULT NULL,
  `modified_by` int DEFAULT NULL,
  PRIMARY KEY (`st_enrol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_enrolment`
--

LOCK TABLES `student_enrolment` WRITE;
/*!40000 ALTER TABLE `student_enrolment` DISABLE KEYS */;
INSERT INTO `student_enrolment` VALUES (1,'2026ENR0006','EQ00022','','0','','','test','3453453455','saisatya51@gmail.com','','test',0,'2026-02-01 10:31:59',NULL,NULL,NULL),(2,'2026ENR0007','EQ00022','','0','','','test','3453453455','saisatya51@gmail.com','','test',0,'2026-02-01 10:33:35',NULL,NULL,NULL),(3,'2026A40008','','','4','','','sai','3453453455','saisatya51@gmail.com','','satya',0,'2026-02-01 10:50:57',NULL,NULL,NULL);
/*!40000 ALTER TABLE `student_enrolment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_enrolments`
--

DROP TABLE IF EXISTS `student_enrolments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_enrolments` (
  `st_enrol_id` int NOT NULL AUTO_INCREMENT,
  `st_unique_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_enquiry_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_rto_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_courses` text COLLATE utf8mb4_unicode_ci,
  `st_branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_given_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_surname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_dob` date DEFAULT NULL,
  `st_country_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_suburb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_post_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_tel_num` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_emerg_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_emerg_relation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_emerg_mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_emerg_agree` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_usi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_emp_status` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_self_status` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_citizenship` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_gender` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_credit_transfer` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_highest_school` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_secondary_school` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_born_country` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_born_country_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_origin` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_lan_spoken` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_lan_spoken_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_disability` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_disability_type` text COLLATE utf8mb4_unicode_ci,
  `st_disability_type_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_study_reason` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_study_reason_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_1` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_2` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_3` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_4` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_5` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_6` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_7` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_8` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_9` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_10` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_8_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_qual_9_other` date DEFAULT NULL,
  `st_qual_10_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `st_status` tinyint(1) DEFAULT '0',
  `st_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `st_created_by` int DEFAULT NULL,
  `st_modified_date` date DEFAULT NULL,
  `st_modified_by` int DEFAULT NULL,
  `qualification_code_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_declaration_18` tinyint(1) DEFAULT NULL,
  `city_of_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_same_as_above` tinyint(1) DEFAULT NULL,
  `postal_address` text COLLATE utf8mb4_unicode_ci,
  `english_read_write` tinyint(1) DEFAULT NULL,
  `work_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_completed_school` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode_delivery` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualification_attained` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry_of_work` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `computer_access` tinyint(1) DEFAULT NULL,
  `computer_literacy` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numeracy_skills` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_support` tinyint(1) DEFAULT NULL,
  `additional_support_specify` text COLLATE utf8mb4_unicode_ci,
  `usi_declaration` tinyint(1) DEFAULT NULL,
  `privacy_declaration` tinyint(1) DEFAULT NULL,
  `refund_declaration` tinyint(1) DEFAULT NULL,
  `office_student_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_coordinator_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_invoice_provided` tinyint(1) DEFAULT NULL,
  `office_receipt_collected` tinyint(1) DEFAULT NULL,
  `office_lms_access` tinyint(1) DEFAULT NULL,
  `office_resources_access` tinyint(1) DEFAULT NULL,
  `office_uploaded_sms` tinyint(1) DEFAULT NULL,
  `office_welcome_pack_sent` tinyint(1) DEFAULT NULL,
  `candidate_declaration` tinyint(1) DEFAULT NULL,
  `candidate_full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `candidate_date` date DEFAULT NULL,
  `candidate_signature` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `form_source` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'legacy',
  PRIMARY KEY (`st_enrol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_enrolments`
--

LOCK TABLES `student_enrolments` WRITE;
/*!40000 ALTER TABLE `student_enrolments` DISABLE KEYS */;
INSERT INTO `student_enrolments` VALUES (1,'1','','rto nam asdf','[\"3\"]','branch babsdf','930837test.png','agia test','surn asdf','2023-12-31','adsfas','adfsd','dfsdfg','1','798798','987987987','asdfa@gmail.com','87788','asdfsafd','dsfgsdf','989879879898','1','asdfasdf','1','3','1','1','1','3','1','1','','1','2','','2','[]','','1','asdfas','1','1','1','1','1','1','1','2','2','2','','0000-00-00','',0,'2023-10-18 00:00:00',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'legacy'),(2,'1','EQ0002','rto nam asdf','[\"3\"]','branch babsdf','675734test.png','agia test','surn asdf','2023-12-31','adsfas','adfsd','dfsdfg','1','798798','987987987','asdfa@gmail.com','87788','asdfsafd','dsfgsdf','989879879898','1','asdfasdf','1','3','1','1','1','3','1','1','','1','2','','2','[]','','1','asdfas','1','1','1','1','1','1','1','2','2','2','','0000-00-00','',0,'2023-10-18 00:00:00',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'legacy'),(3,'1','EQ0002','rto nam asdf','[\"3\"]','branch babsdf','509731test.png','agia test','surn asdf','2023-12-31','adsfas','adfsd','dfsdfg','1','798798','987987987','asdfa@gmail.com','87788','asdfsafd','dsfgsdf','989879879898','1','asdfasdf','1','3','1','1','1','3','1','2','testsdfsd','1','2','','2','[]','','1','asdfas','1','1','1','1','1','1','1','2','2','2','','0000-00-00','',0,'2023-10-18 00:00:00',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'legacy'),(4,'1','EQ00003','test','[\"8\",\"10\",\"12\"]','testest','[]','testtest','testtest','2025-10-02','test','test','test','2','987987','9879879877','testset@gmail.com','9879879877','test','test','9879879877','1','ttestest','4','4','2','1','1','2','1','1','','1','2','','2','[]','[]','[','','1','1','1','1','1','1','1','2','2','2','','0000-00-00','',0,'2025-10-21 17:46:06',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'legacy'),(5,'1','EQ00003','test','[\"8\",\"10\",\"12\"]','testest','[]','testtest','testtest','2025-10-02','test','test','test','2','987987','9879879877','testset@gmail.com','9879879877','test','test','9879879877','1','ttestest','4','4','2','1','1','2','1','1','','1','2','','2','[]','[]','[','','1','1','1','1','1','1','1','2','2','2','','0000-00-00','',0,'2025-10-21 17:48:41',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'legacy'),(6,'2026ENR0006','EQ00022','National Collegteste Australia','[]','test','[]','test','test','1998-10-10','India','Agraharam','Bobbili','VIC','535558','3453453455','saisatya51@gmail.com','3453453455','test','cousin','4564564566','1','3453453455','1','','','1','1','1','1','','','1','2','','2','[]','','1','','','','','','','','','','','','','0000-00-00','',0,'2026-02-01 10:31:59',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'legacy'),(7,'2026ENR0007','EQ00022','National Collegteste Australia','[]','test','[]','test','test','1998-10-10','India','Agraharam','Bobbili','VIC','535558','3453453455','saisatya51@gmail.com','3453453455','test','cousin','4564564566','1','3453453455','1','','','1','1','1','1','','','1','2','','2','[]','','1','','','','','','','','','','','','','0000-00-00','',0,'2026-02-01 10:33:35',1,NULL,NULL,'CHC434',1,'Bobbili',1,'',1,'3453453455','3453453455','','Classroom','Australia','INdustry',2,'Good','Excellent',1,'',1,1,1,NULL,'test test',1,1,0,0,0,0,1,'satya sai','2005-10-10','test','online'),(8,'2026A40008','','National College Australia','[\"4\"]','test','[]','satya','sai','1998-10-10','India','Agraharam','Bobbili','VIC','535558','3453453455','saisatya51@gmail.com','3453453455','satya sai','cousin','4564564566','1','3453453455','1','','','1','2','1','1','','','2','2','','1','[\"1\"]','','1','','','','','','','','','','','','','0000-00-00','',0,'2026-02-01 10:50:57',1,NULL,NULL,'CHC434',1,'Bobbili',1,'',1,'3453453455','3453453455','233','Classroom','Equivalent','INdustry',2,'Excellent','Basic',1,'',1,1,1,NULL,'test test',0,0,0,1,1,0,1,'satya sai','2004-10-10','rwar','online');
/*!40000 ALTER TABLE `student_enrolments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_log_id` varchar(255) NOT NULL DEFAULT '0',
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_type` tinyint(1) NOT NULL,
  `user_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_log_id` (`user_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ST56F54','test1','test123@gmail.com','test',1,0,'2023-08-20 04:36:13',NULL),(2,'082623DSB0001','test2','test234@gmail.com','test2',0,0,'2023-08-20 04:36:13',NULL),(3,'CDB4448E','testing123','testing123@gmail.com','testing123',0,0,'2025-10-21 17:49:05','2025-10-22 06:29:40'),(4,'36B81E75','Prasangi','prasangi@nca.edu.au','test123',1,0,'2025-10-22 00:47:58','2025-10-22 07:42:19'),(5,'A566E63D','ujala','ujala.sinha@nca.edu.au','test1234',0,0,'2025-10-28 00:44:28','2025-10-28 00:44:57');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `venue`
--

DROP TABLE IF EXISTS `venue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venue` (
  `venue_id` int NOT NULL AUTO_INCREMENT,
  `venue_name` varchar(255) NOT NULL,
  `venue_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`venue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venue`
--

LOCK TABLES `venue` WRITE;
/*!40000 ALTER TABLE `venue` DISABLE KEYS */;
INSERT INTO `venue` VALUES (1,'Adeladie',0,'2023-08-23 11:38:00'),(2,'New Jersey',0,'2023-08-23 11:38:00'),(3,'Australia',0,'2023-08-23 11:38:04');
/*!40000 ALTER TABLE `venue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visa_statuses`
--

DROP TABLE IF EXISTS `visa_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `visa_statuses` (
  `visa_id` int NOT NULL AUTO_INCREMENT,
  `visa_status_name` varchar(255) NOT NULL,
  `visa_state_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`visa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visa_statuses`
--

LOCK TABLES `visa_statuses` WRITE;
/*!40000 ALTER TABLE `visa_statuses` DISABLE KEYS */;
INSERT INTO `visa_statuses` VALUES (1,'Dependent on subclass 500',0,'2023-08-23 10:47:23'),(2,'489 visa',0,'2023-08-23 10:47:23'),(3,'491',0,'2023-08-23 10:47:29'),(4,'Visitor’s visa',0,'2023-09-16 05:52:01'),(5,'Permanent resident',0,'2023-09-16 05:52:01'),(6,'Citizen',0,'2023-09-16 05:52:18'),(7,'Other',0,'2023-09-16 05:52:18');
/*!40000 ALTER TABLE `visa_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'auztraining'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-08  9:48:34
