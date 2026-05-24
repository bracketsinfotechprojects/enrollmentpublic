-- Insert email template for Enquiry Status: Booked Counselling (status_code 9)
-- Placeholders: {{CounsellingDate}}, {{CounsellingTime}} (populated from linked appointment)

INSERT INTO `enquiry_status_email_templates` (`status_code`, `subject`, `body`) VALUES
(9, 'Counselling Appointment Confirmation – National College Australia',
'Dear Student,

Thank you for booking your counselling session with National College Australia. We are pleased to confirm your appointment and look forward to assisting you with your study plans.

Counselling Appointment Details:
📅 Date: {{CounsellingDate}}
⏰ Time: {{CounsellingTime}}

Venue:
National College Australia
1/118 King William Street
Adelaide SA 5000
🌐 Website: www.nca.edu.au
📞 Contact: 08 7119 6196

During this session, our counsellor will guide you through course options, admission requirements, and help answer any questions you may have about studying with us.

If you need to reschedule or require any additional information before your appointment, please feel free to contact us.

We look forward to meeting you and helping you take the next step in your education journey.

Warm regards,
Admissions Team
National College Australia
www.nca.edu.au
08 7119 6196')
ON DUPLICATE KEY UPDATE `subject` = VALUES(`subject`), `body` = VALUES(`body`);
