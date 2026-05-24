-- Migration: Enquiry Source + Location for website popup and CRM
-- Run on auztraining database. Safe to run (ADD COLUMN IF NOT EXISTS not in MySQL 5.7, so run once).

-- Enquiry Source: 1=Website form, 2=Phone call, 3=Walk-in, 4=Email, 5=WhatsApp, 6=Facebook/Instagram ads, 7=Agent/referral
ALTER TABLE `student_enquiry` ADD COLUMN `st_enquiry_source` TINYINT NULL DEFAULT NULL COMMENT '1=Website,2=Phone,3=Walk-in,4=Email,5=WhatsApp,6=FB/IG,7=Agent' AFTER `st_gen_enq_type`;
ALTER TABLE `student_enquiry` ADD COLUMN `st_location` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Location from website popup' AFTER `st_enquiry_source`;

-- Received Enquiry for Which college (admin only): 1=Apt Training College, 2=Milton College, 3=NCA, 4=Power Education, 5=Auz Training
ALTER TABLE `student_enquiry` ADD COLUMN `st_enquiry_college` TINYINT NULL DEFAULT NULL COMMENT '1=Apt Training,2=Milton,3=NCA,4=Power Ed,5=Auz Training' AFTER `st_location`;
