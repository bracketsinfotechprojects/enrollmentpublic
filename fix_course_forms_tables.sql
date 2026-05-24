-- Fix column sizes for course forms tables
-- Run this SQL to update existing tables

ALTER TABLE `course_cancellations` MODIFY `refund_to_be_issued` varchar(10) DEFAULT NULL;
ALTER TABLE `course_extensions` MODIFY `extension_approved` varchar(10) DEFAULT NULL;
