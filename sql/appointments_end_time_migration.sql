ALTER TABLE `appointments`
  ADD COLUMN `appointment_end_time` TIME NULL DEFAULT NULL AFTER `appointment_time`,
  ADD COLUMN `appointment_end_datetime` DATETIME NULL DEFAULT NULL AFTER `appointment_datetime`;

ALTER TABLE appointment_blocks
  ADD COLUMN block_status TINYINT(1) NOT NULL DEFAULT 0 AFTER block_reason;