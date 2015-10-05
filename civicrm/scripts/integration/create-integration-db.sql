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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Objects that have been flagged.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive`
--

DROP TABLE IF EXISTS `archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive` (
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
  `archive_date` datetime DEFAULT NULL COMMENT 'Date/time record was archived',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_bill`
--

DROP TABLE IF EXISTS `archive_bill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_bill` (
  `archive_id` int(10) unsigned NOT NULL,
  `bill_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bill_year` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `fk_archive_id_bill` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_committee`
--

DROP TABLE IF EXISTS `archive_committee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_committee` (
  `archive_id` int(10) unsigned NOT NULL,
  `committee_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `fk_archive_id_committee` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_contextmsg`
--

DROP TABLE IF EXISTS `archive_contextmsg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_contextmsg` (
  `archive_id` int(10) unsigned NOT NULL,
  `bill_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `fk_archive_id_contextmsg` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_issue`
--

DROP TABLE IF EXISTS `archive_issue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_issue` (
  `archive_id` int(10) unsigned NOT NULL,
  `issue_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `fk_archive_id_issue` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_petition`
--

DROP TABLE IF EXISTS `archive_petition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_petition` (
  `archive_id` int(10) unsigned NOT NULL,
  `petition_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `fk_archive_id_petition` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archive_survey`
--

DROP TABLE IF EXISTS `archive_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archive_survey` (
  `archive_id` int(10) unsigned NOT NULL,
  `form_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `fk_archive_id_survey` FOREIGN KEY (`archive_id`) REFERENCES `archive` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `option_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `option_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
