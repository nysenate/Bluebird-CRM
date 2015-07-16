-- BILL
-- fields: bill_number, bill_year
DROP TABLE IF EXISTS `archive_bill`;
CREATE TABLE IF NOT EXISTS `archive_bill` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived',
  `bill_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bill_year` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_bill`
  ADD PRIMARY KEY (`id`);

-- ISSUE
-- fields: issue_name
DROP TABLE IF EXISTS `archive_issue`;
CREATE TABLE IF NOT EXISTS `archive_issue` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived',
  `issue_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_issue`
ADD PRIMARY KEY (`id`);

-- COMMITTEE
-- fields: committee_name
DROP TABLE IF EXISTS `archive_committee`;
CREATE TABLE IF NOT EXISTS `archive_committee` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived',
  `committee_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_committee`
ADD PRIMARY KEY (`id`);

-- DIRECTMSG
DROP TABLE IF EXISTS `archive_directmsg`;
CREATE TABLE IF NOT EXISTS `archive_directmsg` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_directmsg`
ADD PRIMARY KEY (`id`);

-- CONTEXTMSG
-- fields: bill_number
DROP TABLE IF EXISTS `archive_contextmsg`;
CREATE TABLE IF NOT EXISTS `archive_contextmsg` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived',
  `bill_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_contextmsg`
ADD PRIMARY KEY (`id`);

-- PETITION
-- fields: petition_id
DROP TABLE IF EXISTS `archive_petition`;
CREATE TABLE IF NOT EXISTS `archive_petition` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived',
  `petition_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_petition`
ADD PRIMARY KEY (`id`);

-- SURVEY
-- fields: form_id
DROP TABLE IF EXISTS `archive_survey`;
CREATE TABLE IF NOT EXISTS `archive_survey` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived',
  `form_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_survey`
ADD PRIMARY KEY (`id`);

-- ACCOUNT
DROP TABLE IF EXISTS `archive_account`;
CREATE TABLE IF NOT EXISTS `archive_account` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_account`
ADD PRIMARY KEY (`id`);

-- PROFILE
DROP TABLE IF EXISTS `archive_profile`;
CREATE TABLE IF NOT EXISTS `archive_profile` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_profile`
ADD PRIMARY KEY (`id`);

-- OTHER
DROP TABLE IF EXISTS `archive_other`;
CREATE TABLE IF NOT EXISTS `archive_other` (
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
  `archive_date` datetime COLLATE utf8_unicode_ci NULL COMMENT 'Date/time record was archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `archive_other`
ADD PRIMARY KEY (`id`);
