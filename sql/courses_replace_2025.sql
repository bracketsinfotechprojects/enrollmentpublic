-- Replace courses list with NCA course list (all over application - forms and fetching)
-- Run this once to update the courses table.

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `courses`;

INSERT INTO `courses` (`course_id`, `course_sname`, `course_name`, `course_status`, `created_date`) VALUES
(1, 'CHC33021', 'Certificate III in Individual Support (Ageing)', 0, NOW()),
(2, 'CHC33021', 'Certificate III in Individual Support (Disability)', 0, NOW()),
(3, 'CHC33021', 'Certificate III in Individual Support (Ageing & Disability)', 0, NOW()),
(4, 'CHC43015', 'Certificate IV in Ageing Support', 0, NOW()),
(5, 'CHC43121', 'Certificate IV in Disability', 0, NOW()),
(6, 'CHC32021', 'Certificate III in Community Services', 0, NOW()),
(7, 'CHC42021', 'Certificate IV in Community Services', 0, NOW()),
(8, 'CHC52021', 'Diploma of Community Services', 0, NOW()),
(9, 'CHC43315', 'Certificate IV in Mental Health', 0, NOW()),
(10, 'CHC53315', 'Diploma of Mental Health', 0, NOW()),
(11, 'HLT33021', 'Certificate III in Allied Health Assistance', 0, NOW()),
(12, 'CHC43415', 'Certificate IV in Leisure and Health', 0, NOW()),
(13, 'MHT-R', 'Manual Handling Training (Refresher)', 0, NOW()),
(14, 'MHT-F', 'Manual Handling Training (Full)', 0, NOW()),
(15, 'Med-R', 'Medication Training (Refresher)', 0, NOW()),
(16, 'Med-F', 'Medication Training (Full)', 0, NOW()),
(17, 'Insulin', 'Safely Injecting Insulin Training', 0, NOW());

SET FOREIGN_KEY_CHECKS = 1;
