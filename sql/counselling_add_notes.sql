-- Add Notes field to counselling (like appointment_notes in appointments)
ALTER TABLE `counseling_details`
  ADD COLUMN `counsil_notes` TEXT NULL DEFAULT NULL AFTER `counsil_remarks`;
