-- Counselling outcome (drives automatic enquiry status when saved from student_enquiry.php)
ALTER TABLE `counseling_details`
  ADD COLUMN `counsil_outcome` VARCHAR(64) NULL DEFAULT NULL AFTER `counsil_notes`;
