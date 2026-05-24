ALTER TABLE `appointments`
  ADD COLUMN `appointment_shared_with` TEXT NULL DEFAULT NULL AFTER `appointment_notes`;

