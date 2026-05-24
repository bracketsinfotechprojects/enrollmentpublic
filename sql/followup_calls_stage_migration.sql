-- Post Enquiry vs Post Counselling follow-up rows (student_enquiry.php accordions).
-- The app also adds this column on first follow-up save if missing.
ALTER TABLE `followup_calls` ADD COLUMN `flw_followup_stage` VARCHAR(32) NOT NULL DEFAULT 'enquiry';
