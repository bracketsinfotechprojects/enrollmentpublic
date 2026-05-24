CREATE TABLE IF NOT EXISTS `appointment_email_templates` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `template_code` VARCHAR(64) NOT NULL,
  `template_name` VARCHAR(128) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_template_code` (`template_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `appointment_email_templates` (`template_code`,`template_name`,`subject`,`body`,`updated_at`)
VALUES
('standard_booking','Standard appointment confirmation','Your appointment confirmation – National College Australia','Hi {{FirstName}},\n\nThis email confirms your appointment with National College Australia.\n\nYour booking details:\n- Purpose: {{PurposeName}}\n- Date: {{AppointmentDate}}\n- Time: {{AppointmentTime}}\n- Format: {{MeetingType}}\n- Team member: {{StaffName}}\n- Enquiry reference: {{EnquiryID}}\n\nIf you have any questions, please contact us.',NOW()),
('phone_call_booking','Phone call booking confirmation','Your scheduled call with us – National College Australia','Hi {{FirstName}},\n\nThank you for your interest in studying with us. A member of our team will contact you at the time below.\n\nCall details:\n- Date: {{AppointmentDate}}\n- Time: {{AppointmentTime}}\n- Team member: {{StaffName}}\n- Contact number: {{StudentPhone}}\n- Enquiry reference: {{EnquiryID}}\n\nPlease keep your phone available. If this time no longer suits you, reply to this email and we will arrange another time.',NOW()),
('counselling_rescheduled','Counselling rescheduled confirmation','Your rescheduled counselling session – National College Australia','Hi {{FirstName}},\n\nYour counselling session has been rescheduled. Here are your confirmed details:\n\n- Purpose: {{PurposeName}}\n- Date: {{AppointmentDate}}\n- Time: {{AppointmentTime}}\n- Format: {{MeetingType}}\n- Team member: {{StaffName}}\n- Enquiry reference: {{EnquiryID}}\n\nWe look forward to meeting you at the scheduled time.',NOW())
ON DUPLICATE KEY UPDATE template_name=VALUES(template_name);
