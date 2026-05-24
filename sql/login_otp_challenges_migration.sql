-- Login OTP challenges (admin/staff + student portal)
-- OTP is stored in plain text (dev / dummy-mail testing). Do not use this pattern in production without strict access control.

CREATE TABLE IF NOT EXISTS `login_otp_challenges` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `channel` enum('admin','student') NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_pk` bigint(20) UNSIGNED NOT NULL COMMENT 'users.user_id or student_users.id',
  `otp_code` varchar(10) NOT NULL COMMENT 'plain OTP (testing)',
  `session_bind` char(64) NOT NULL COMMENT 'server-only random token; binds browser session to this row',
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=active,1=verified_ok,2=superseded,3=locked,4=expired',
  `verified_at` datetime DEFAULT NULL,
  `verify_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `max_verify_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `ip_request` varchar(45) DEFAULT NULL,
  `ip_last_verify` varchar(45) DEFAULT NULL,
  `user_agent` varchar(512) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_bind` (`session_bind`),
  KEY `idx_channel_email_active` (`channel`,`email`,`is_used`,`expires_at`),
  KEY `idx_expires` (`expires_at`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- If you already had login_otp_challenges with otp_hash, run after TRUNCATE or on empty table:
-- ALTER TABLE `login_otp_challenges` CHANGE COLUMN `otp_hash` `otp_code` varchar(10) NOT NULL COMMENT 'plain OTP (testing)';
