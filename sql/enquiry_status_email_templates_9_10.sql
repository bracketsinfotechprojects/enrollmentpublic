-- Optional: default templates for enquiry flow statuses 9 (Booked Counselling) and 10 (Re-enquired)
-- Run once if your enquiry_status_email_templates table only has codes 1–8.

INSERT INTO `enquiry_status_email_templates` (`status_code`, `subject`, `body`) VALUES
(9, 'Counselling appointment – {{CourseName}}', 'Dear {{FirstName}},\n\nThis email confirms your counselling appointment for {{CourseName}} at National College Australia.\n\nDate: {{CounsellingDate}}\nTime: {{CounsellingTime}}\n\nIf you need to reschedule, please contact us:\nPhone: 08 7119 6196\nEmail: info@nca.edu.au\n\nKind regards,\n{{OfficerName}}\nNational College Australia'),
(10, 'Thank you for your renewed interest', 'Dear {{FirstName}},\n\nThank you for reaching out again regarding {{CourseName}}. We have noted your re-enquiry and a team member will contact you shortly.\n\nKind regards,\n{{OfficerName}}\nNational College Australia')
ON DUPLICATE KEY UPDATE `subject` = VALUES(`subject`), `body` = VALUES(`body`);
