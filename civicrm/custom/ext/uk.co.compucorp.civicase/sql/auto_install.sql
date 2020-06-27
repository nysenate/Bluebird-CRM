DROP TABLE IF EXISTS `civicase_contactlock`;
-- /*******************************************************
-- *
-- * civicase_contactlock
-- *
-- * This table implements a list of contacts that have been locked out of
-- * specific cases, to which they will only have basic view access.
-- *
-- *******************************************************/
CREATE TABLE `civicase_contactlock` (
  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique CaseContactLock ID',
  `case_id` int unsigned    COMMENT 'Case ID that is locked.',
  `contact_id` int unsigned    COMMENT 'Contact for which the case is locked.' ,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicase_contactlock_case_id FOREIGN KEY (`case_id`) REFERENCES `civicrm_case`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicase_contactlock_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
