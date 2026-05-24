# Appointment Booking System - Implementation Guide

## Overview
A comprehensive appointment booking system has been implemented with all requested features including calendar view, timezone handling, reporting, and integration with existing CRM systems.

## Installation Steps

### 1. Database Setup
Run the SQL file to create all necessary tables:
```sql
-- Execute: appointment_tables.sql
```
This will create the following tables:
- `appointment_purposes` - Purpose types (Counselling, Complaints, etc.)
- `appointment_attendee_types` - Attendee types (Student, Business Purpose, etc.)
- `appointment_locations` - Meeting locations
- `appointment_platforms` - Online meeting platforms
- `appointments` - Main appointments table
- `appointment_blocks` - Blocked time slots
- `appointment_reminders` - Reminder tracking

### 2. Files Created
- `appointment_booking.php` - Book/Edit appointments
- `appointment_calendar.php` - Calendar view with FullCalendar
- `appointment_reports.php` - Reports and analytics
- `appointment_blocks.php` - Manage blocked time slots
- `appointment_tables.sql` - Database schema

### 3. Files Modified
- `includes/sidebar.php` - Added Appointments menu
- `includes/datacontrol` - Added all appointment backend functions

## Features Implemented

### ✅ Core Features
1. **Appointment Booking**
   - Date and time selection
   - Purpose dropdown (with add/remove options)
   - Staff member selection
   - Attendee information (Student/Business)
   - Meeting type (Online/Face to Face/Phone)
   - Location and platform selection
   - Booking comments
   - Automatic email invitations (optional)

2. **Timezone Handling**
   - Book appointments in any Australian state timezone
   - Automatic conversion to:
     - Adelaide time
     - India time (IST)
     - Philippines time (PHT)
   - Real-time display of all timezones

3. **Calendar View**
   - FullCalendar integration
   - Color-coded by purpose
   - Click to view details
   - Multiple view options (Month/Week/Day/List)

4. **Appointment Management**
   - Edit appointments
   - Mark as completed
   - Mark as no-show
   - Cancel appointments
   - Time in/Time out tracking
   - Status tracking

5. **Integration**
   - Link to Student Enquiries (CRM)
   - Link to Enrolments
   - Link to Counselling records
   - Connected with timesheet system

6. **Blocked Slots**
   - Block time slots for specific dates/times
   - Block for all staff or specific staff member
   - Add reason for blocking

7. **Reports & Analytics**
   - Total appointments (daily/weekly/monthly)
   - Attended vs Missed
   - Cancelled appointments
   - Appointments by purpose
   - Appointments by staff member
   - Daily trends
   - Detailed appointment list

8. **Email Notifications**
   - Automatic email invitations (optional)
   - Email sent to student/attendee when appointment is booked

9. **Management Options**
   - Add/Remove appointment purposes
   - Add/Remove attendee types
   - Add/Remove locations
   - Add/Remove platforms

## Usage

### Booking an Appointment
1. Navigate to **Appointments > Book Appointment**
2. Fill in all required fields
3. Select timezone (state)
4. View timezone conversions
5. Submit form

### Viewing Calendar
1. Navigate to **Appointments > Calendar View**
2. View appointments by month/week/day
3. Click on appointment to view details
4. Use action buttons to manage appointment

### Generating Reports
1. Navigate to **Appointments > Reports**
2. Select date range
3. View charts and statistics
4. Export data if needed

### Blocking Time Slots
1. Navigate to **Appointments > Block Slots**
2. Select date and time range
3. Optionally select staff member
4. Add reason
5. Save block

## Database Schema

### Main Tables

**appointments**
- Stores all appointment data
- Links to purposes, locations, platforms
- Tracks timezone conversions
- Links to enquiries, enrolments, counselling

**appointment_purposes**
- Purpose types with color coding
- Default purposes: Counselling, Complaints, Course Withdrawal, Enrolment, Assignments, Logbook Submission

**appointment_blocks**
- Blocked time slots
- Can be for all staff or specific staff member

## Notes

### Reminder System
The reminder system structure is in place (`appointment_reminders` table). For full implementation, you would need to:
1. Set up a cron job to check for upcoming appointments
2. Send email notifications to staff and supervisors
3. Track reminder status

### Email Configuration
Make sure `includes/mail_function.php` is properly configured for sending emails.

### Timezone Calculations
The system uses Moment.js with timezone support for accurate timezone conversions. All times are stored in the database for reference.

## Access Control
- All appointment features are restricted to `user_type == 1` (Admin users)
- Regular users cannot access appointment system

## Future Enhancements
- Automated reminder emails (cron job)
- SMS notifications
- Recurring appointments
- Appointment templates
- Staff availability calendar
- Integration with external calendar systems (Google Calendar, Outlook)

