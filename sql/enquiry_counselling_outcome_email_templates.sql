-- Counselling outcome emails (not enquiry flow statuses). Used from Counselling accordion + Settings.
-- Run once. Safe to re-run: only inserts missing status_code rows.

INSERT INTO `enquiry_status_email_templates` (`status_code`, `subject`, `body`)
SELECT v.status_code, v.subject, v.body FROM (
  SELECT 12 AS status_code, 'Counselling completed – {{CourseName}}' AS subject, 'Dear {{FirstName}},\n\nThank you for completing your counselling session regarding {{CourseName}} at National College Australia.\n\nSession date: {{CounsellingDate}}\nTime: {{CounsellingTime}}\n\nWe will be in touch with any next steps.\n\nKind regards,\n{{OfficerName}}\nNational College Australia' AS body
  UNION ALL SELECT 13, 'Counselling rescheduled – {{CourseName}}', 'Dear {{FirstName}},\n\nRegarding your enquiry about {{CourseName}}, your counselling session has been rescheduled.\n\nSession date: {{CounsellingDate}}\nTime: {{CounsellingTime}}\n\nIf you have questions, contact us:\nPhone: 08 7119 6196\nEmail: info@nca.edu.au\n\nKind regards,\n{{OfficerName}}\nNational College Australia'
  UNION ALL SELECT 14, 'Counselling outcome – {{CourseName}}', 'Dear {{FirstName}},\n\nThank you for attending your counselling regarding {{CourseName}} at National College Australia.\nAs discussed, we are unable to proceed with an application at this time.\n\nSession date: {{CounsellingDate}}\nTime: {{CounsellingTime}}\n\nWe appreciate your interest. Should your circumstances change, you are welcome to contact us in the future.\n\nKind regards,\n{{OfficerName}}\nNational College Australia'
) AS v
WHERE NOT EXISTS (SELECT 1 FROM enquiry_status_email_templates t WHERE t.status_code = v.status_code);
