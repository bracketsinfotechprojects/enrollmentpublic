-- Appointment System Database Tables

-- Table for appointment purposes (Counselling, complaints, course withdrawal, etc.)
DROP TABLE IF EXISTS `appointment_purposes`;
CREATE TABLE `appointment_purposes` (
  `purpose_id` int NOT NULL AUTO_INCREMENT,
  `purpose_name` varchar(255) NOT NULL,
  `purpose_color` varchar(20) DEFAULT '#0bb197',
  `purpose_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`purpose_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Insert default purposes
INSERT INTO `appointment_purposes` (`purpose_name`, `purpose_color`, `purpose_status`) VALUES
('Counselling', '#0bb197', 0),
('Complaints', '#ff3d60', 0),
('Course Withdrawal', '#fcb92c', 0),
('Enrolment', '#4aa3ff', 0),
('Assignments', '#564ab1', 0),
('Logbook Submission', '#0ac074', 0);

-- Table for appointment attendee types (Student, Business purpose, etc.)
DROP TABLE IF EXISTS `appointment_attendee_types`;
CREATE TABLE `appointment_attendee_types` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  `type_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Insert default attendee types
INSERT INTO `appointment_attendee_types` (`type_name`, `type_status`) VALUES
('Student', 0),
('Business Purpose', 0);

-- Table for meeting locations
DROP TABLE IF EXISTS `appointment_locations`;
CREATE TABLE `appointment_locations` (
  `location_id` int NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) NOT NULL,
  `location_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Insert default locations
INSERT INTO `appointment_locations` (`location_name`, `location_status`) VALUES
('Adelaide Office', 0),
('Melbourne Office', 0),
('Online', 0);

-- Table for online meeting platforms
DROP TABLE IF EXISTS `appointment_platforms`;
CREATE TABLE `appointment_platforms` (
  `platform_id` int NOT NULL AUTO_INCREMENT,
  `platform_name` varchar(255) NOT NULL,
  `platform_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`platform_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Insert default platforms
INSERT INTO `appointment_platforms` (`platform_name`, `platform_status`) VALUES
('Zoom', 0),
('Google Meet', 0),
('Outlook', 0);

-- Main appointments table
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `appointment_id` int NOT NULL AUTO_INCREMENT,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_end_time` time DEFAULT NULL,
  `appointment_datetime` datetime NOT NULL,
  `appointment_end_datetime` datetime DEFAULT NULL,
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
  `appointment_shared_with` text DEFAULT NULL COMMENT 'Comma-separated user IDs or ALL',
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table for blocking appointment slots
DROP TABLE IF EXISTS `appointment_blocks`;
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

-- Table for appointment reminders
DROP TABLE IF EXISTS `appointment_reminders`;
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

