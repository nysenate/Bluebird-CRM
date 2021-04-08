SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicase_contactlock`;
DROP TABLE IF EXISTS `civicrm_case_category_instance`;
ALTER TABLE `civicrm_case_type` DROP COLUMN `case_type_category`;

SET FOREIGN_KEY_CHECKS=1;