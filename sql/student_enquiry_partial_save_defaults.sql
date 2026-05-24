-- Optional: run once if INSERT fails on partial rows (only email required in app logic).
-- Ensures NOT NULL columns have sensible defaults for empty strings.
-- Backup your DB before running.

ALTER TABLE `student_enquiry`
  MODIFY `st_name` varchar(255) NOT NULL DEFAULT '',
  MODIFY `st_surname` varchar(255) NOT NULL DEFAULT '',
  MODIFY `st_phno` varchar(10) NOT NULL DEFAULT '',
  MODIFY `st_street_details` varchar(255) NOT NULL DEFAULT '',
  MODIFY `st_suburb` varchar(255) NOT NULL DEFAULT '',
  MODIFY `st_post_code` varchar(10) NOT NULL DEFAULT '',
  MODIFY `st_fee` varchar(255) NOT NULL DEFAULT '',
  MODIFY `st_shore` tinyint(1) NOT NULL DEFAULT '0',
  MODIFY `st_appoint_book` tinyint(1) NOT NULL DEFAULT '0',
  MODIFY `st_refered` tinyint(1) NOT NULL DEFAULT '0',
  MODIFY `st_refer_alumni` tinyint(1) NOT NULL DEFAULT '0';
