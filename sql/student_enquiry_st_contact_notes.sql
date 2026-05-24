-- Optional: Notes field on Student contact (student_enquiry.php).
-- The app also auto-adds this column on first save if missing.
ALTER TABLE `student_enquiry` ADD COLUMN `st_contact_notes` TEXT NULL;
