-- Outbound email audit log (populated automatically by send_mail / enrol_send_mail).

CREATE TABLE IF NOT EXISTS `crm_email_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `send_status` VARCHAR(16) NOT NULL DEFAULT 'sent',
  `error_message` TEXT NULL,
  `recipient_to` VARCHAR(512) NOT NULL,
  `subject` VARCHAR(998) NOT NULL,
  `body_html` MEDIUMTEXT NULL,
  `email_category` VARCHAR(64) NOT NULL DEFAULT 'general',
  `sent_by_user_id` INT NULL,
  `sent_by_user_name` VARCHAR(128) NULL,
  `st_enquiry_id` VARCHAR(32) NULL,
  `st_id` INT NULL,
  `meta_json` TEXT NULL,
  `request_uri` VARCHAR(512) NULL,
  `ip_address` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_staff` (`sent_by_user_id`),
  KEY `idx_enquiry` (`st_enquiry_id`),
  KEY `idx_st_id` (`st_id`),
  KEY `idx_category` (`email_category`),
  KEY `idx_status` (`send_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
