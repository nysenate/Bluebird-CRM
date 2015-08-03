SET FOREIGN_KEY_CHECKS=0;

-- drop all tables
DROP TABLE IF EXISTS `archive`;
DROP TABLE IF EXISTS `archive_bill`;
DROP TABLE IF EXISTS `archive_issue`;
DROP TABLE IF EXISTS `archive_committee`;
DROP TABLE IF EXISTS `archive_contextmsg`;
DROP TABLE IF EXISTS `archive_petition`;
DROP TABLE IF EXISTS `archive_survey`;

-- archive (common)
CREATE TABLE IF NOT EXISTS `archive` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique ID, as noted from remote source',
  `user_id` int(10) unsigned DEFAULT '0' COMMENT 'The user ID for whom this action was created.',
  `user_is_verified` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'If the user is verified or not',
  `target_shortname` varchar(32) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'The target Senator’s shortname',
  `target_district` int(10) unsigned DEFAULT NULL COMMENT 'The target Senator’s district',
  `user_shortname` varchar(32) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'The user’s Senator’s shortname',
  `user_district` int(10) unsigned DEFAULT NULL COMMENT 'The user’s district',
  `msg_type` enum('BILL','ISSUE','COMMITTEE','DIRECTMSG','CONTEXTMSG','PETITION','ACCOUNT','PROFILE','MISC') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'MISC' COMMENT 'The type of message being recorded',
  `msg_action` varchar(32) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'The specific action of the message',
  `msg_info` text COLLATE utf8_unicode_ci COMMENT 'JSON-formatted data specific to the type of message',
  `created_at` int(10) unsigned DEFAULT '0' COMMENT 'When the message was recorded',
  `email_address` varchar(254) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_me` tinyint(4) NOT NULL DEFAULT '0',
  `address1` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Street address, line 1.',
  `address2` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Street address, line 2.',
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'City.',
  `state` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'State / Province code.',
  `zip` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Postal / ZIP code.',
  `dob` int(11) DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `top_issue` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Top Issue selection',
  `archive_date` datetime DEFAULT NULL COMMENT 'Date/time record was archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive`
  ADD PRIMARY KEY (`id`);

-- BILL
-- fields: bill_number, bill_year
CREATE TABLE IF NOT EXISTS `archive_bill` (
  `archive_id` int(10) unsigned NOT NULL,
  `bill_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bill_year` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_bill`
  ADD KEY `idx_archive_id` (`archive_id`);

ALTER TABLE `archive_bill`
  ADD CONSTRAINT `fk_archive_id_bill` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- ISSUE
-- fields: issue_name
CREATE TABLE IF NOT EXISTS `archive_issue` (
  `archive_id` int(10) unsigned NOT NULL,
  `issue_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_issue`
  ADD KEY `idx_archive_id` (`archive_id`);

ALTER TABLE `archive_issue`
  ADD CONSTRAINT `fk_archive_id_issue` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- COMMITTEE
-- fields: committee_name
CREATE TABLE IF NOT EXISTS `archive_committee` (
  `archive_id` int(10) unsigned NOT NULL,
  `committee_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_committee`
  ADD KEY `idx_archive_id` (`archive_id`);

ALTER TABLE `archive_committee`
  ADD CONSTRAINT `fk_archive_id_committee` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- CONTEXTMSG
-- fields: bill_number
CREATE TABLE IF NOT EXISTS `archive_contextmsg` (
  `archive_id` int(10) unsigned NOT NULL,
  `bill_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_contextmsg`
  ADD KEY `idx_archive_id` (`archive_id`);

ALTER TABLE `archive_contextmsg`
  ADD CONSTRAINT `fk_archive_id_contextmsg` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- PETITION
-- fields: petition_id
CREATE TABLE IF NOT EXISTS `archive_petition` (
  `archive_id` int(10) unsigned NOT NULL,
  `petition_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_petition`
  ADD KEY `idx_archive_id` (`archive_id`);

ALTER TABLE `archive_petition`
  ADD CONSTRAINT `fk_archive_id_petition` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- SURVEY
-- fields: form_id
CREATE TABLE IF NOT EXISTS `archive_survey` (
  `archive_id` int(10) unsigned NOT NULL,
  `form_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_survey`
  ADD KEY `idx_archive_id` (`archive_id`);

ALTER TABLE `archive_survey`
  ADD CONSTRAINT `fk_archive_id_survey` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

SET FOREIGN_KEY_CHECKS=0;
