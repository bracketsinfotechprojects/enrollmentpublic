-- Counselling: Preferred Intake Date, Mode of Study (Face to Face / Online / Blended)
ALTER TABLE `counseling_details`
  ADD COLUMN `counsil_preferred_intake_date` DATE NULL DEFAULT NULL AFTER `counsil_type`,
  ADD COLUMN `counsil_mode_of_study` TINYINT(1) NULL DEFAULT NULL COMMENT '1=Face to Face, 2=Online, 3=Blended' AFTER `counsil_preferred_intake_date`;

-- Enquiry workflow status on main enquiry (default New when first enquiry)
ALTER TABLE `student_enquiry`
  ADD COLUMN `st_enquiry_flow_status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=New,2=Contacted,3=Follow-up Required,4=Interested,5=Documents Collected,6=Enrolled,7=Not Interested,8=Invalid/Duplicate' AFTER `st_enquiry_status`;

-- Follow-up: type (Call/Email), notes, next date, outcome
ALTER TABLE `followup_calls`
  ADD COLUMN `flw_followup_type` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Call, Email' AFTER `flw_mode_contact`,
  ADD COLUMN `flw_follow_up_notes` TEXT NULL DEFAULT NULL AFTER `flw_followup_type`,
  ADD COLUMN `flw_next_followup_date` DATETIME NULL DEFAULT NULL AFTER `flw_follow_up_notes`,
  ADD COLUMN `flw_follow_up_outcome` VARCHAR(100) NULL DEFAULT NULL COMMENT 'No answer, Call back later, Sent info, Enrolment in progress' AFTER `flw_next_followup_date`;

-- Enquiry status email templates (one per status, editable by staff)
CREATE TABLE IF NOT EXISTS `enquiry_status_email_templates` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `status_code` TINYINT(1) NOT NULL COMMENT '1=New,2=Contacted,3=Follow-up Required,4=Interested,5=Documents Collected,6=Enrolled,7=Not Interested,8=Invalid/Duplicate',
  `subject` VARCHAR(255) NOT NULL DEFAULT '',
  `body` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_code` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default templates for each of the 8 enquiry statuses
INSERT INTO `enquiry_status_email_templates` (`status_code`, `subject`, `body`) VALUES
(1, 'New Enquiry – Auto Acknowledgement', 'Dear {{FirstName}},\n\nThank you for your interest in studying {{CourseName}} at National College Australia.\nWe have received your enquiry and one of our admissions team members will contact you shortly to discuss your study options, entry requirements, and upcoming intakes.\n\nIf you would like immediate assistance, please feel free to contact us:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to assisting you with your study journey.\n\nKind regards,\nAdmissions Team\nNational College Australia'),
(2, 'Contacted – Follow-Up After Initial Contact', 'Dear {{FirstName}},\n\nIt was a pleasure speaking with you regarding {{CourseName}}.\nAs discussed, please let us know if you require any additional information about course structure, fees, entry requirements, or intake dates.\nIf you are ready to proceed, we can guide you through the application process.\n\nFor any questions, feel free to contact us:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to supporting you.\n\nKind regards,\n{{OfficerName}}\nNational College Australia'),
(3, 'Follow-Up Required – Gentle Reminder', 'Dear {{FirstName}},\n\nWe hope you are doing well.\nWe are following up regarding your enquiry about {{CourseName}}. We would be happy to assist you further and answer any questions you may have.\nPlease let us know a suitable time to contact you, or feel free to reach out directly using the details below.\n\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to hearing from you.\n\nKind regards,\nAdmissions Team\nNational College Australia'),
(4, 'Interested – Application Invitation', 'Dear {{FirstName}},\n\nThank you for confirming your interest in {{CourseName}}.\nThe next step is to submit your application along with the required supporting documents. Our admissions team is ready to assist you through the process.\nPlease reply to this email if you would like us to send the application form or guide you through the submission process.\n\nFor assistance:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to welcoming you to National College Australia.\n\nKind regards,\n{{OfficerName}}\nAdmissions Team'),
(5, 'Documents Collected – Under Assessment', 'Dear {{FirstName}},\n\nThank you for submitting your documents for {{CourseName}}.\nWe confirm that your application is currently under review. Our admissions team will assess your documents and contact you shortly regarding the outcome.\nIf any additional documents are required, we will inform you promptly.\n\nFor enquiries:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nThank you for choosing National College Australia.\n\nKind regards,\nAdmissions Team\nNational College Australia'),
(6, 'Enrolled – Welcome Email', 'Dear {{FirstName}},\n\nCongratulations on your successful enrolment in {{CourseName}} at National College Australia!\nWe are excited to welcome you to our college community. You will soon receive further information regarding orientation, timetable, and commencement details.\n\nIf you have any questions before your course begins, please contact us:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe look forward to supporting you throughout your studies.\n\nKind regards,\nAdmissions & Student Support Team\nNational College Australia'),
(7, 'Not Interested – Polite Closure', 'Dear {{FirstName}},\n\nThank you for considering National College Australia for your studies.\nWe understand that you have decided not to proceed at this time. Should you reconsider in the future, we would be happy to assist you.\n\nPlease feel free to contact us anytime:\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nWe wish you all the best in your future endeavours.\n\nKind regards,\nAdmissions Team\nNational College Australia'),
(8, 'Invalid / Duplicate Enquiry', 'Dear {{FirstName}},\n\nThank you for your recent enquiry.\nIt appears that we may already have your details in our system or that some information provided was incomplete. If this was submitted in error, no further action is required.\nIf you would still like assistance, please contact us directly so we can help you promptly.\n\nWebsite: www.nca.edu.au\nEmail: info@nca.edu.au\nPhone: 08 7119 6196\n\nKind regards,\nAdmissions Team\nNational College Australia')
ON DUPLICATE KEY UPDATE `subject` = VALUES(`subject`), `body` = VALUES(`body`);
