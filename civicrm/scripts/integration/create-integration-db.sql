-- MySQL dump 10.13  Distrib 5.6.26-74.0, for Linux (x86_64)
--
-- Host: localhost    Database: senate_web_integration
-- ------------------------------------------------------
-- Server version	5.6.26-74.0-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accumulator`
--

DROP TABLE IF EXISTS `accumulator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accumulator` (
  `id` int(10) unsigned NOT NULL COMMENT 'EventID from website accumulator',
  `user_id` int(10) unsigned DEFAULT 0 COMMENT 'Website userID associated with this event',
  `user_is_verified` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'If the user is verified or not',
  `user_district` int(10) unsigned DEFAULT NULL COMMENT 'Website user''s senate district',
  `user_shortname` varchar(32) DEFAULT '' COMMENT 'Website user''s senator shortname',
  `target_district` int(10) unsigned DEFAULT NULL COMMENT 'Event target''s senate district',
  `target_shortname` varchar(32) DEFAULT '' COMMENT 'Event target''s senator shortname',
  `event_type` enum('account', 'bill', 'committee', 'issue', 'poll', 'senator') NOT NULL COMMENT 'Type of event being recorded',
  `event_action` enum('aye', 'nay', 'follow', 'unfollow', 'webform', 'created', 'edited', 'comment', 'message') NOT NULL COMMENT 'The specific action of the event',
  `event_data` text COMMENT 'JSON-formatted data specific to the type of event',
  `created_at` datetime DEFAULT NULL COMMENT 'Timestamp for when the event was recorded',
  `email_address` varchar(200) NOT NULL DEFAULT '' COMMENT 'User''s email address',
  `first_name` varchar(50) DEFAULT NULL COMMENT 'User''s first name',
  `last_name` varchar(50) DEFAULT NULL COMMENT 'User''s last name',
  `address1` varchar(200) NOT NULL DEFAULT '' COMMENT 'Street address, line 1',
  `address2` varchar(200) NOT NULL DEFAULT '' COMMENT 'Street address, line 2',
  `city` varchar(50) NOT NULL DEFAULT '' COMMENT 'City',
  `state` varchar(16) NOT NULL DEFAULT '' COMMENT 'State / Province code',
  `zip` varchar(16) NOT NULL DEFAULT '' COMMENT 'Postal / ZIP code',
  `dob` date DEFAULT NULL COMMENT 'User''s date of birth',
  `gender` varchar(16) DEFAULT NULL COMMENT 'User''s gender',
  `top_issue` varchar(255) NOT NULL DEFAULT '' COMMENT 'Top Issue selection',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Objects that have been flagged.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive`
--

DROP TABLE IF EXISTS `archive`;
CREATE TABLE `archive` (
   `id` int(10) unsigned NOT NULL COMMENT 'EventID from website accumulator',
   `user_id` int(10) unsigned DEFAULT 0 COMMENT 'Website userID associated with this event',
   `user_is_verified` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'If the user is verified or not',
   `user_district` int(10) unsigned DEFAULT NULL COMMENT 'Website user''s senate district',
   `user_shortname` varchar(32) DEFAULT '' COMMENT 'Website user''s senator shortname',
   `target_district` int(10) unsigned DEFAULT NULL COMMENT 'Event target''s senate district',
   `target_shortname` varchar(32) DEFAULT '' COMMENT 'Event target''s senator shortname',
   `event_type` enum('account', 'bill', 'committee', 'issue', 'poll', 'senator') NOT NULL COMMENT 'Type of event being recorded',
   `event_action` enum('aye', 'nay', 'follow', 'unfollow', 'webform', 'created', 'edited', 'comment', 'message') NOT NULL COMMENT 'The specific action of the event',
   `event_data` text COMMENT 'JSON-formatted data specific to the type of event',
   `created_at` datetime DEFAULT NULL COMMENT 'Timestamp for when the event was recorded',
   `email_address` varchar(200) NOT NULL DEFAULT '' COMMENT 'User''s email address',
   `first_name` varchar(50) DEFAULT NULL COMMENT 'User''s first name',
   `last_name` varchar(50) DEFAULT NULL COMMENT 'User''s last name',
   `address1` varchar(200) NOT NULL DEFAULT '' COMMENT 'Street address, line 1',
   `address2` varchar(200) NOT NULL DEFAULT '' COMMENT 'Street address, line 2',
   `city` varchar(50) NOT NULL DEFAULT '' COMMENT 'City',
   `state` varchar(16) NOT NULL DEFAULT '' COMMENT 'State / Province code',
   `zip` varchar(16) NOT NULL DEFAULT '' COMMENT 'Postal / ZIP code',
   `dob` date DEFAULT NULL COMMENT 'User''s date of birth',
   `gender` varchar(16) DEFAULT NULL COMMENT 'User''s gender',
   `top_issue` varchar(255) NOT NULL DEFAULT '' COMMENT 'Top Issue selection',
   `archive_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp archived',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `archive_error`
--

DROP TABLE IF EXISTS `archive_error`;
CREATE TABLE `archive_error` (
   `id` int(10) unsigned NOT NULL COMMENT 'EventID from website accumulator',
   `user_id` int(10) unsigned DEFAULT 0 COMMENT 'Website userID associated with this event',
   `user_is_verified` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'If the user is verified or not',
   `user_district` int(10) unsigned DEFAULT NULL COMMENT 'Website user''s senate district',
   `user_shortname` varchar(32) DEFAULT '' COMMENT 'Website user''s senator shortname',
   `target_district` int(10) unsigned DEFAULT NULL COMMENT 'Event target''s senate district',
   `target_shortname` varchar(32) DEFAULT '' COMMENT 'Event target''s senator shortname',
   `event_type` enum('account', 'bill', 'committee', 'issue', 'poll', 'senator') NOT NULL COMMENT 'Type of event being recorded',
   `event_action` enum('aye', 'nay', 'follow', 'unfollow', 'webform', 'created', 'edited', 'comment', 'message') NOT NULL COMMENT 'The specific action of the event',
   `event_data` text COMMENT 'JSON-formatted data specific to the type of event',
   `created_at` datetime DEFAULT NULL COMMENT 'Timestamp for when the event was recorded',
   `email_address` varchar(200) NOT NULL DEFAULT '' COMMENT 'User''s email address',
   `first_name` varchar(50) DEFAULT NULL COMMENT 'User''s first name',
   `last_name` varchar(50) DEFAULT NULL COMMENT 'User''s last name',
   `address1` varchar(200) NOT NULL DEFAULT '' COMMENT 'Street address, line 1',
   `address2` varchar(200) NOT NULL DEFAULT '' COMMENT 'Street address, line 2',
   `city` varchar(50) NOT NULL DEFAULT '' COMMENT 'City',
   `state` varchar(16) NOT NULL DEFAULT '' COMMENT 'State / Province code',
   `zip` varchar(16) NOT NULL DEFAULT '' COMMENT 'Postal / ZIP code',
   `dob` date DEFAULT NULL COMMENT 'User''s date of birth',
   `gender` varchar(16) DEFAULT NULL COMMENT 'User''s gender',
   `top_issue` varchar(255) NOT NULL DEFAULT '' COMMENT 'Top Issue selection',
   `archive_date` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp archived',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `archive_bill`
--

DROP TABLE IF EXISTS `archive_bill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_bill` (
  `archive_id` int(10) unsigned NOT NULL,
  `bill_number` varchar(32) DEFAULT NULL,
  `bill_year` varchar(8) DEFAULT NULL,
  `bill_sponsor` varchar(63) DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_committee`
--

DROP TABLE IF EXISTS `archive_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_committee` (
  `archive_id` int(10) unsigned NOT NULL,
  `committee_name` varchar(64) DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_issue`
--

DROP TABLE IF EXISTS `archive_issue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_issue` (
  `archive_id` int(10) unsigned NOT NULL,
  `issue_name` varchar(256) DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_poll`
--

DROP TABLE IF EXISTS `archive_poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_poll` (
  `archive_id` int(10) unsigned NOT NULL,
  `form_id` varchar(256) DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `option_name` varchar(64) NOT NULL,
  `option_value` varchar(256) DEFAULT '',
  PRIMARY KEY (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings VALUES ('last_update', NOW()), ('max_eventid', '0');


/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-10-04 20:59:06
