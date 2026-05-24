-- Stage tag (st_enquiry_flow_change_stage): ENQ / CONS from follow-up or counselling outcomes; legacy PEFU / PCFU.
-- Applied automatically on save via includes/datacontrol when needed.

ALTER TABLE `student_enquiry`
  ADD COLUMN `st_enquiry_flow_change_stage` VARCHAR(8) NULL DEFAULT NULL
  COMMENT 'PEFU or PCFU when enquiry status last set from follow-up outcome';
