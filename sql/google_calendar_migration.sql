-- Single shared Google account for follow-up reminders (admin connects once; all staff get events as attendees)
CREATE TABLE IF NOT EXISTS `google_calendar_tokens` (
  `id` int NOT NULL DEFAULT 1,
  `access_token` text,
  `refresh_token` text,
  `token_expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: site-wide Google OAuth credentials (admin configures once)
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('google_calendar_client_id', ''),
('google_calendar_client_secret', '')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- If you previously had user_google_calendar_tokens, you can copy one row into the shared table then drop the old table:
-- INSERT IGNORE INTO google_calendar_tokens (id, access_token, refresh_token, token_expires_at) SELECT 1, access_token, refresh_token, token_expires_at FROM user_google_calendar_tokens LIMIT 1;
-- DROP TABLE IF EXISTS user_google_calendar_tokens;
