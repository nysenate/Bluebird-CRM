-- /*******************************************************
-- *
-- * civicase_contactlock
-- *
-- * This table implements a list of contacts that have been locked out of
-- * specific cases, to which they will only have basic view access.
-- *
-- *******************************************************/
CREATE TABLE IF NOT EXISTS `civicase_contactlock` (
  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique CaseContactLock ID',
  `case_id` int unsigned    COMMENT 'Case ID that is locked.',
  `contact_id` int unsigned    COMMENT 'Contact for which the case is locked.' ,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicase_contactlock_case_id FOREIGN KEY (`case_id`) REFERENCES `civicrm_case`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicase_contactlock_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- /*******************************************************
-- *
-- * civicrm_case_category_instance
-- *
-- * Stores Case Category Instance Details
-- *
-- *******************************************************/
CREATE TABLE IF NOT EXISTS `civicrm_case_category_instance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique CaseCategoryInstance Id',
  `category_id` int unsigned NOT NULL   COMMENT 'One of the values of the case_type_categories option group',
  `instance_id` int unsigned NOT NULL   COMMENT 'One of the values of the case_category_instance_type option group',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_category`(category_id)
 ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
