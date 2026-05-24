ALTER TABLE users
    ADD COLUMN user_phone VARCHAR(20) NULL DEFAULT NULL AFTER user_email,
    ADD COLUMN user_address TEXT NULL DEFAULT NULL AFTER user_phone;

