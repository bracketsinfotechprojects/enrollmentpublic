# Student Portal & Public Enrol Feature

## Overview

- **Public Enrol page** (`Enrol.php`): NCA-style course application form. Visitors submit minimal enquiry details; data is stored and they receive an Enquiry ID. They are prompted to register in the CRM to access the full enquiry form.
- **Student Register** (`student_register.php`): Students register with the same email used on the Enrol form. Their enquiry is linked to their account (by email, or by Enquiry ID if provided).
- **Student Login** (`student_login.php`): Students log in and are taken to the student portal.
- **Student Portal** (`student_portal.php`): If an enquiry is linked, redirects to the full enquiry form. Otherwise shows a message and link to Enrol.
- **Full enquiry form** (same as CRM): When opened as a student (`student_enquiry.php?eq=...&student=1`), students see only the **Student Enquiry** section (no Counseling / Follow Up). They can view and update their saved details.

## Database Migration

Run the migration once so that student accounts and enquiry linking work:

1. Open phpMyAdmin or MySQL and select the `auztraining` database.
2. Execute the contents of `sql/student_portal_migration.sql`:
   - Creates table `student_users` (id, email, password_hash, full_name, phone, status, created_date).
   - Adds column `student_user_id` to `student_enquiry` and links it to `student_users`.

## URLs

| Page | URL |
|------|-----|
| Public Enrol | `Enrol.php` |
| Student Register | `student_register.php` |
| Student Login | `student_login.php` |
| Student Portal (after login) | `student_portal.php` |
| Student Logout | `student_logout.php` |

## Security

- Only the enquiry owner (matching `student_user_id`) can view/edit their enquiry when logged in as a student.
- CRM admin/staff flows are unchanged; students use separate login and cannot access admin areas via this flow.
