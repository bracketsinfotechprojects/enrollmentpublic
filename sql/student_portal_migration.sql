-- Migration: Student portal (public enquiry + student register/login)
-- Run this on existing auztraining database. Does not drop or overwrite existing data.

-- 1. Student users table (for register/login)
DROP TABLE IF EXISTS `student_users`;
CREATE TABLE `student_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Link enquiry to student account (nullable for enquiries not yet linked)
ALTER TABLE `student_enquiry` ADD COLUMN `student_user_id` int DEFAULT NULL AFTER `st_gen_enq_type`;
ALTER TABLE `student_enquiry` ADD KEY `student_user_id` (`student_user_id`);
