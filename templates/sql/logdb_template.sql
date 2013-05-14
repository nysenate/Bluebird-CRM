-- MySQL dump 10.13  Distrib 5.5.31, for Linux (x86_64)
--
-- Host: crmdbprod    Database: senate_prod_l_template
-- ------------------------------------------------------
-- Server version	5.5.30-log

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
-- Table structure for table `log_civicrm_acl`
--

DROP TABLE IF EXISTS `log_civicrm_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_acl` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique table ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ACL Name.',
  `deny` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Is this ACL entry Allow  (0) or Deny (1) ?',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Table of the object possessing this ACL entry (Contact, Group, or ACL Group)',
  `entity_id` int(10) unsigned DEFAULT NULL COMMENT 'ID of the object possessing this ACL',
  `operation` enum('All','View','Edit','Create','Delete','Grant','Revoke','Search') COLLATE utf8_unicode_ci NOT NULL COMMENT 'What operation does this ACL entry control?',
  `object_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The table of the object controlled by this ACL entry',
  `object_id` int(10) unsigned DEFAULT NULL COMMENT 'The ID of the object controlled by this ACL entry',
  `acl_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If this is a grant/revoke entry, what table are we granting?',
  `acl_id` int(10) unsigned DEFAULT NULL COMMENT 'ID of the ACL or ACL group being granted/revoked',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_acl`
--

LOCK TABLES `log_civicrm_acl` WRITE;
/*!40000 ALTER TABLE `log_civicrm_acl` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_acl_entity_role`
--

DROP TABLE IF EXISTS `log_civicrm_acl_entity_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_acl_entity_role` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique table ID',
  `acl_role_id` int(10) unsigned NOT NULL COMMENT 'Foreign Key to ACL Role (which is an option value pair and hence an implicit FK)',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Table of the object joined to the ACL Role (Contact or Group)',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'ID of the group/contact object being joined',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_acl_entity_role`
--

LOCK TABLES `log_civicrm_acl_entity_role` WRITE;
/*!40000 ALTER TABLE `log_civicrm_acl_entity_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_acl_entity_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_action_mapping`
--

DROP TABLE IF EXISTS `log_civicrm_action_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_action_mapping` (
  `id` int(10) unsigned NOT NULL,
  `entity` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity for which the reminder is created',
  `entity_value` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity value',
  `entity_value_label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity value label',
  `entity_status` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity status',
  `entity_status_label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity status label',
  `entity_date_start` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity date',
  `entity_date_end` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity date',
  `entity_recipient` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity recipient',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_action_mapping`
--

LOCK TABLES `log_civicrm_action_mapping` WRITE;
/*!40000 ALTER TABLE `log_civicrm_action_mapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_action_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_action_schedule`
--

DROP TABLE IF EXISTS `log_civicrm_action_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_action_schedule` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the action(reminder)',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title of the action(reminder)',
  `recipient` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Recipient',
  `entity_value` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity value',
  `entity_status` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity status',
  `start_action_offset` int(10) unsigned DEFAULT NULL COMMENT 'Reminder Interval.',
  `start_action_unit` enum('hour','day','week','month','year') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Time units for reminder.',
  `start_action_condition` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Reminder Action',
  `start_action_date` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity date',
  `is_repeat` tinyint(4) DEFAULT '0',
  `repetition_frequency_unit` enum('hour','day','week','month','year') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Time units for repetition of reminder.',
  `repetition_frequency_interval` int(10) unsigned DEFAULT NULL COMMENT 'Time interval for repeating the reminder.',
  `end_frequency_unit` enum('hour','day','week','month','year') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Time units till repetition of reminder.',
  `end_frequency_interval` int(10) unsigned DEFAULT NULL COMMENT 'Time interval till repeating the reminder.',
  `end_action` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Reminder Action till repeating the reminder.',
  `end_date` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity end date',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this option active?',
  `recipient_manual` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Contact IDs to which reminder should be sent.',
  `body_text` longtext COLLATE utf8_unicode_ci COMMENT 'Body of the mailing in text format.',
  `body_html` longtext COLLATE utf8_unicode_ci COMMENT 'Body of the mailing in html format.',
  `subject` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Subject of mailing',
  `record_activity` tinyint(4) DEFAULT NULL COMMENT 'Record Activity for this reminder?',
  `mapping_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to mapping which is being used by this reminder',
  `group_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Group',
  `msg_template_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the message template.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `absolute_date` date DEFAULT NULL COMMENT 'Date on which the reminder be sent.',
  `recipient_listing` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'listing based on recipient field.'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_action_schedule`
--

LOCK TABLES `log_civicrm_action_schedule` WRITE;
/*!40000 ALTER TABLE `log_civicrm_action_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_action_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_activity`
--

DROP TABLE IF EXISTS `log_civicrm_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_activity` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique  Other Activity ID',
  `source_contact_id` int(10) unsigned DEFAULT NULL COMMENT 'Contact ID of the person scheduling or logging this Activity. Usually the authenticated user.',
  `source_record_id` int(10) unsigned DEFAULT NULL COMMENT 'Artificial FK to original transaction (e.g. contribution) IF it is not an Activity. Table can be figured out through activity_type_id, and further through component registry.',
  `activity_type_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'FK to civicrm_option_value.id, that has to be valid, registered activity type.',
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The subject/purpose/short description of the activity.',
  `activity_date_time` datetime DEFAULT NULL COMMENT 'Date and time this activity is scheduled to occur. Formerly named scheduled_date_time.',
  `duration` int(10) unsigned DEFAULT NULL COMMENT 'Planned or actual duration of activity expressed in minutes. Conglomerate of former duration_hours and duration_minutes.',
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Location of the activity (optional, open text).',
  `phone_id` int(10) unsigned DEFAULT NULL COMMENT 'Phone ID of the number called (optional - used if an existing phone number is selected).',
  `phone_number` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Phone number in case the number does not exist in the civicrm_phone table.',
  `details` text COLLATE utf8_unicode_ci COMMENT 'Details about the activity (agenda, notes, etc).',
  `status_id` int(10) unsigned DEFAULT NULL COMMENT 'ID of the status this activity is currently in. Foreign key to civicrm_option_value.',
  `priority_id` int(10) unsigned DEFAULT NULL COMMENT 'ID of the priority given to this activity. Foreign key to civicrm_option_value.',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Parent meeting ID (if this is a follow-up item). This is not currently implemented',
  `is_test` tinyint(4) DEFAULT '0',
  `medium_id` int(10) unsigned DEFAULT NULL COMMENT 'Activity Medium, Implicit FK to civicrm_option_value where option_group = encounter_medium.',
  `is_auto` tinyint(4) DEFAULT '0',
  `relationship_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Relationship ID',
  `is_current_revision` tinyint(4) DEFAULT '1',
  `original_id` int(10) unsigned DEFAULT NULL COMMENT 'Activity ID of the first activity record in versioning chain.',
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Currently being used to store result for survey activity. FK to option value.',
  `is_deleted` tinyint(4) DEFAULT '0',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this activity has been triggered.',
  `engagement_level` int(10) unsigned DEFAULT NULL COMMENT 'Assign a specific level of engagement to this activity. Used for tracking constituents in ladder of engagement.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  KEY `id` (`id`),
  KEY `source_contact_id` (`source_contact_id`),
  KEY `source_record_id` (`source_record_id`),
  KEY `activity_type_id` (`activity_type_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_activity`
--

LOCK TABLES `log_civicrm_activity` WRITE;
/*!40000 ALTER TABLE `log_civicrm_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_activity_assignment`
--

DROP TABLE IF EXISTS `log_civicrm_activity_assignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_activity_assignment` (
  `id` int(10) unsigned NOT NULL COMMENT 'Activity assignment id',
  `activity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the activity for this assignment.',
  `assignee_contact_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the contact for this assignment.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `activity_id` (`activity_id`),
  KEY `assignee_contact_id` (`assignee_contact_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_activity_assignment`
--

LOCK TABLES `log_civicrm_activity_assignment` WRITE;
/*!40000 ALTER TABLE `log_civicrm_activity_assignment` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_activity_assignment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_activity_target`
--

DROP TABLE IF EXISTS `log_civicrm_activity_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_activity_target` (
  `id` int(10) unsigned NOT NULL COMMENT 'Activity target id',
  `activity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the activity for this target.',
  `target_contact_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the contact for this target.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `activity_id` (`activity_id`),
  KEY `target_contact_id` (`target_contact_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_activity_target`
--

LOCK TABLES `log_civicrm_activity_target` WRITE;
/*!40000 ALTER TABLE `log_civicrm_activity_target` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_activity_target` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_address`
--

DROP TABLE IF EXISTS `log_civicrm_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_address` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Address ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Location does this address belong to.',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary address.',
  `is_billing` tinyint(4) DEFAULT '0' COMMENT 'Is this the billing address.',
  `street_address` varchar(96) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Concatenation of all routable street address components (prefix, street number, street name, suffix, unit number OR P.O. Box). Apps should be able to determine physical location with this data (for mapping, mail delivery, etc.).',
  `street_number` int(11) DEFAULT NULL COMMENT 'Numeric portion of address number on the street, e.g. For 112A Main St, the street_number = 112.',
  `street_number_suffix` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Non-numeric portion of address number on the street, e.g. For 112A Main St, the street_number_suffix = A',
  `street_number_predirectional` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Directional prefix, e.g. SE Main St, SE is the prefix.',
  `street_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Actual street name, excluding St, Dr, Rd, Ave, e.g. For 112 Main St, the street_name = Main.',
  `street_type` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'St, Rd, Dr, etc.',
  `street_number_postdirectional` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Directional prefix, e.g. Main St S, S is the suffix.',
  `street_unit` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Secondary unit designator, e.g. Apt 3 or Unit # 14, or Bldg 1200',
  `supplemental_address_1` varchar(96) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Supplemental Address Information, Line 1',
  `supplemental_address_2` varchar(96) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Supplemental Address Information, Line 2',
  `supplemental_address_3` varchar(96) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Supplemental Address Information, Line 3',
  `city` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'City, Town or Village Name.',
  `county_id` int(10) unsigned DEFAULT NULL COMMENT 'Which County does this address belong to.',
  `state_province_id` int(10) unsigned DEFAULT NULL COMMENT 'Which State_Province does this address belong to.',
  `postal_code_suffix` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Store the suffix, like the +4 part in the USPS system.',
  `postal_code` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Store both US (zip5) AND international postal codes. App is responsible for country/region appropriate validation.',
  `usps_adc` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'USPS Bulk mailing code.',
  `country_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Country does this address belong to.',
  `geo_code_1` double DEFAULT NULL COMMENT 'Latitude',
  `geo_code_2` double DEFAULT NULL COMMENT 'Longitude',
  `timezone` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Timezone expressed as a UTC offset - e.g. United States CST would be written as "UTC-6".',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `master_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Address ID',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `id` (`id`),
  KEY `contact_id` (`contact_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_address`
--

LOCK TABLES `log_civicrm_address` WRITE;
/*!40000 ALTER TABLE `log_civicrm_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_address_format`
--

DROP TABLE IF EXISTS `log_civicrm_address_format`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_address_format` (
  `id` int(10) unsigned NOT NULL COMMENT 'Address Format Id',
  `format` text COLLATE utf8_unicode_ci COMMENT 'The format of an address',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_address_format`
--

LOCK TABLES `log_civicrm_address_format` WRITE;
/*!40000 ALTER TABLE `log_civicrm_address_format` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_address_format` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_batch`
--

DROP TABLE IF EXISTS `log_civicrm_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_batch` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Address ID.',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Variable name/programmatic handle for this batch.',
  `label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Friendly Name.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Description of this batch set.',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `created_date` datetime DEFAULT NULL COMMENT 'When was this item created',
  `modified_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `modified_date` datetime DEFAULT NULL COMMENT 'When was this item created',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Friendly Name.',
  `saved_search_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Saved Search ID',
  `status_id` int(10) unsigned NOT NULL COMMENT 'fk to Batch Status options in civicrm_option_values',
  `type_id` int(10) unsigned NOT NULL COMMENT 'fk to Batch Type options in civicrm_option_values',
  `mode_id` int(10) unsigned DEFAULT NULL COMMENT 'fk to Batch mode options in civicrm_option_values',
  `total` decimal(20,2) DEFAULT NULL COMMENT 'Total amount for this batch.',
  `item_count` int(10) unsigned NOT NULL COMMENT 'Number of items in a batch.'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_batch`
--

LOCK TABLES `log_civicrm_batch` WRITE;
/*!40000 ALTER TABLE `log_civicrm_batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_campaign`
--

DROP TABLE IF EXISTS `log_civicrm_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_campaign` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Campaign ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the Campaign.',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title of the Campaign.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Full description of Campaign.',
  `start_date` datetime DEFAULT NULL COMMENT 'Date and time that Campaign starts.',
  `end_date` datetime DEFAULT NULL COMMENT 'Date and time that Campaign ends.',
  `campaign_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Campaign Type ID.Implicit FK to civicrm_option_value where option_group = campaign_type',
  `status_id` int(10) unsigned DEFAULT NULL COMMENT 'Campaign status ID.Implicit FK to civicrm_option_value where option_group = campaign_status',
  `external_identifier` int(10) unsigned DEFAULT NULL COMMENT 'Unique trusted external ID (generally from a legacy app/datasource). Particularly useful for deduping operations.',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional parent id for this Campaign.',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is this Campaign enabled or disabled/cancelled?',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this Campaign.',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time that Campaign was created.',
  `last_modified_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who recently edited this Campaign.',
  `last_modified_date` datetime DEFAULT NULL COMMENT 'Date and time that Campaign was edited last time.',
  `goal_general` text COLLATE utf8_unicode_ci COMMENT 'General goals for Campaign.',
  `goal_revenue` decimal(20,2) DEFAULT NULL COMMENT 'The target revenue for this campaign.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_campaign`
--

LOCK TABLES `log_civicrm_campaign` WRITE;
/*!40000 ALTER TABLE `log_civicrm_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_campaign` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_campaign_group`
--

DROP TABLE IF EXISTS `log_civicrm_campaign_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_campaign_group` (
  `id` int(10) unsigned NOT NULL COMMENT 'Campaign Group id.',
  `campaign_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the activity Campaign.',
  `group_type` enum('Include','Exclude') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Type of Group.',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of table where item being referenced is stored.',
  `entity_id` int(10) unsigned DEFAULT NULL COMMENT 'Entity id of referenced table.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_campaign_group`
--

LOCK TABLES `log_civicrm_campaign_group` WRITE;
/*!40000 ALTER TABLE `log_civicrm_campaign_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_campaign_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_case`
--

DROP TABLE IF EXISTS `log_civicrm_case`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_case` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Case ID',
  `case_type_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Id of first case category.',
  `subject` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Short name of the case.',
  `start_date` date DEFAULT NULL COMMENT 'Date on which given case starts.',
  `end_date` date DEFAULT NULL COMMENT 'Date on which given case ends.',
  `details` text COLLATE utf8_unicode_ci COMMENT 'Details about the meeting (agenda, notes, etc).',
  `status_id` int(10) unsigned NOT NULL COMMENT 'Id of case status.',
  `is_deleted` tinyint(4) DEFAULT '0',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_case`
--

LOCK TABLES `log_civicrm_case` WRITE;
/*!40000 ALTER TABLE `log_civicrm_case` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_case` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_case_activity`
--

DROP TABLE IF EXISTS `log_civicrm_case_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_case_activity` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique case-activity association id',
  `case_id` int(10) unsigned NOT NULL COMMENT 'Case ID of case-activity association.',
  `activity_id` int(10) unsigned NOT NULL COMMENT 'Activity ID of case-activity association.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_case_activity`
--

LOCK TABLES `log_civicrm_case_activity` WRITE;
/*!40000 ALTER TABLE `log_civicrm_case_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_case_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_case_contact`
--

DROP TABLE IF EXISTS `log_civicrm_case_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_case_contact` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique case-contact association id',
  `case_id` int(10) unsigned NOT NULL COMMENT 'Case ID of case-contact association.',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Contact ID of contact record given case belongs to.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_case_contact`
--

LOCK TABLES `log_civicrm_case_contact` WRITE;
/*!40000 ALTER TABLE `log_civicrm_case_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_case_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_component`
--

DROP TABLE IF EXISTS `log_civicrm_component`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_component` (
  `id` int(10) unsigned NOT NULL COMMENT 'Component ID',
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the component.',
  `namespace` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Path to components main directory in a form of a class\nnamespace.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_component`
--

LOCK TABLES `log_civicrm_component` WRITE;
/*!40000 ALTER TABLE `log_civicrm_component` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_component` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contact`
--

DROP TABLE IF EXISTS `log_civicrm_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contact` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Contact ID',
  `contact_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Type of Contact.',
  `contact_sub_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'May be used to over-ride contact view and edit templates.',
  `do_not_email` tinyint(4) DEFAULT '0',
  `do_not_phone` tinyint(4) DEFAULT '0',
  `do_not_mail` tinyint(4) DEFAULT '0',
  `do_not_sms` tinyint(4) DEFAULT '0',
  `do_not_trade` tinyint(4) DEFAULT '0',
  `is_opt_out` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Has the contact opted out from receiving all bulk email from the organization or site domain?',
  `legal_identifier` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'May be used for SSN, EIN/TIN, Household ID (census) or other applicable unique legal/government ID.',
  `external_identifier` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unique trusted external ID (generally from a legacy app/datasource). Particularly useful for deduping operations.',
  `sort_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name used for sorting different contact types',
  `display_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Formatted name representing preferred format for display/print/other output.',
  `nick_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Nick Name.',
  `legal_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Legal Name.',
  `image_URL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'optional URL for preferred image (photo, logo, etc.) to display for this contact.',
  `preferred_communication_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What is the preferred mode of communication.',
  `preferred_language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Which language is preferred for communication. FK to languages in civicrm_option_value.',
  `preferred_mail_format` enum('Text','HTML','Both') COLLATE utf8_unicode_ci DEFAULT 'Both' COMMENT 'What is the preferred mode of sending an email.',
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Key for validating requests related to this contact.',
  `api_key` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'API Key for validating requests related to this contact.',
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'where contact come from, e.g. import, donate module insert...',
  `first_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'First Name.',
  `middle_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Middle Name.',
  `last_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Last Name.',
  `prefix_id` int(10) unsigned DEFAULT NULL COMMENT 'Prefix or Title for name (Ms, Mr...). FK to prefix ID',
  `suffix_id` int(10) unsigned DEFAULT NULL COMMENT 'Suffix for name (Jr, Sr...). FK to suffix ID',
  `email_greeting_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.id, that has to be valid registered Email Greeting.',
  `email_greeting_custom` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Custom Email Greeting.',
  `email_greeting_display` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Cache Email Greeting.',
  `postal_greeting_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.id, that has to be valid registered Postal Greeting.',
  `postal_greeting_custom` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Custom Postal greeting.',
  `postal_greeting_display` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Cache Postal greeting.',
  `addressee_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.id, that has to be valid registered Addressee.',
  `addressee_custom` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Custom Addressee.',
  `addressee_display` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Cache Addressee.',
  `job_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Job Title',
  `gender_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to gender ID',
  `birth_date` date DEFAULT NULL COMMENT 'Date of birth',
  `is_deceased` tinyint(4) DEFAULT '0',
  `deceased_date` date DEFAULT NULL COMMENT 'Date of deceased',
  `household_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Household Name.',
  `primary_contact_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional FK to Primary Contact for this household.',
  `organization_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Organization Name.',
  `sic_code` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Standard Industry Classification Code.',
  `user_unique_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'the OpenID (or OpenID-style http://username.domain/) unique identifier for this contact mainly used for logging in to CiviCRM',
  `employer_id` int(10) unsigned DEFAULT NULL COMMENT 'OPTIONAL FK to civicrm_contact record.',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `id` (`id`),
  KEY `sort_name` (`sort_name`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contact`
--

LOCK TABLES `log_civicrm_contact` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contact_type`
--

DROP TABLE IF EXISTS `log_civicrm_contact_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contact_type` (
  `id` int(10) unsigned NOT NULL COMMENT 'Contact Type ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Internal name of Contact Type (or Subtype).',
  `label` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'localized Name of Contact Type.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'localized Optional verbose description of the type.',
  `image_URL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL of image if any.',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional FK to parent contact type.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this entry active?',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this contact type a predefined system type',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contact_type`
--

LOCK TABLES `log_civicrm_contact_type` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contact_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contact_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contribution`
--

DROP TABLE IF EXISTS `log_civicrm_contribution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contribution` (
  `id` int(10) unsigned NOT NULL COMMENT 'Contribution ID',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID',
  `contribution_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contribution Type',
  `contribution_page_id` int(10) unsigned DEFAULT NULL COMMENT 'The Contribution Page which triggered this contribution',
  `payment_instrument_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Payment Instrument',
  `receive_date` datetime DEFAULT NULL COMMENT 'when was gift received',
  `non_deductible_amount` decimal(20,2) DEFAULT '0.00' COMMENT 'Portion of total amount which is NOT tax deductible. Equal to total_amount for non-deductible contribution types.',
  `total_amount` decimal(20,2) NOT NULL COMMENT 'Total amount of this contribution. Use market value for non-monetary gifts.',
  `fee_amount` decimal(20,2) DEFAULT NULL COMMENT 'actual processor fee if known - may be 0.',
  `net_amount` decimal(20,2) DEFAULT NULL COMMENT 'actual funds transfer amount. total less fees. if processor does not report actual fee during transaction, this is set to total_amount.',
  `trxn_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'unique transaction id. may be processor id, bank id + trans id, or account number + check number... depending on payment_method',
  `invoice_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'unique invoice id, system generated or passed in',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `cancel_date` datetime DEFAULT NULL COMMENT 'when was gift cancelled',
  `cancel_reason` text COLLATE utf8_unicode_ci,
  `receipt_date` datetime DEFAULT NULL COMMENT 'when (if) receipt was sent. populated automatically for online donations w/ automatic receipting',
  `thankyou_date` datetime DEFAULT NULL COMMENT 'when (if) was donor thanked',
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Origin of this Contribution.',
  `amount_level` text COLLATE utf8_unicode_ci,
  `contribution_recur_id` int(10) unsigned DEFAULT NULL COMMENT 'Conditional foreign key to civicrm_contribution_recur id. Each contribution made in connection with a recurring contribution carries a foreign key to the recurring contribution record. This assumes we can track these processor initiated events.',
  `honor_contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contact ID',
  `is_test` tinyint(4) DEFAULT '0',
  `is_pay_later` tinyint(4) DEFAULT '0',
  `contribution_status_id` int(10) unsigned DEFAULT '1',
  `honor_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Implicit FK to civicrm_option_value.',
  `address_id` int(10) unsigned DEFAULT NULL COMMENT 'Conditional foreign key to civicrm_address.id. We insert an address record for each contribution when we have associated billing name and address data.',
  `check_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this contribution has been triggered.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contribution`
--

LOCK TABLES `log_civicrm_contribution` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contribution` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contribution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contribution_page`
--

DROP TABLE IF EXISTS `log_civicrm_contribution_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contribution_page` (
  `id` int(10) unsigned NOT NULL COMMENT 'Contribution Id',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Contribution Page title. For top of page display',
  `intro_text` text COLLATE utf8_unicode_ci COMMENT 'Text and html allowed. Displayed below title.',
  `contribution_type_id` int(10) unsigned NOT NULL COMMENT 'default Contribution type assigned to contributions submitted via this page, e.g. Contribution, Campaign Contribution',
  `payment_processor_id` int(10) unsigned DEFAULT NULL COMMENT 'Payment Processor for this contribution Page ',
  `is_credit_card_only` tinyint(4) DEFAULT '0' COMMENT 'if true - processing logic must reject transaction at confirmation stage if pay method != credit card',
  `is_monetary` tinyint(4) DEFAULT '1' COMMENT 'if true - allows real-time monetary transactions otherwise non-monetary transactions',
  `is_recur` tinyint(4) DEFAULT '0' COMMENT 'if true - allows recurring contributions, valid only for PayPal_Standard',
  `recur_frequency_unit` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Supported recurring frequency units.',
  `is_recur_interval` tinyint(4) DEFAULT '0' COMMENT 'if true - supports recurring intervals',
  `is_pay_later` tinyint(4) DEFAULT '0' COMMENT 'if true - allows the user to send payment directly to the org later',
  `pay_later_text` text COLLATE utf8_unicode_ci COMMENT 'The text displayed to the user in the main form',
  `pay_later_receipt` text COLLATE utf8_unicode_ci COMMENT 'The receipt sent to the user instead of the normal receipt text',
  `is_allow_other_amount` tinyint(4) DEFAULT '0' COMMENT 'if true, page will include an input text field where user can enter their own amount',
  `default_amount_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.',
  `min_amount` decimal(20,2) DEFAULT NULL COMMENT 'if other amounts allowed, user can configure minimum allowed.',
  `max_amount` decimal(20,2) DEFAULT NULL COMMENT 'if other amounts allowed, user can configure maximum allowed.',
  `goal_amount` decimal(20,2) DEFAULT NULL COMMENT 'The target goal for this page, allows people to build a goal meter',
  `thankyou_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for Thank-you page (header title tag, and display at the top of the page).',
  `thankyou_text` text COLLATE utf8_unicode_ci COMMENT 'text and html allowed. displayed above result on success page',
  `thankyou_footer` text COLLATE utf8_unicode_ci COMMENT 'Text and html allowed. displayed at the bottom of the success page. Common usage is to include link(s) to other pages such as tell-a-friend, etc.',
  `is_for_organization` tinyint(4) DEFAULT '0' COMMENT 'if true, signup is done on behalf of an organization',
  `for_organization` text COLLATE utf8_unicode_ci COMMENT 'This text field is shown when is_for_organization is checked. For example - I am contributing on behalf on an organization.',
  `is_email_receipt` tinyint(4) DEFAULT '1' COMMENT 'if true, receipt is automatically emailed to contact on success',
  `receipt_from_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'FROM email name used for receipts generated by contributions to this contribution page.',
  `receipt_from_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'FROM email address used for receipts generated by contributions to this contribution page.',
  `cc_receipt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma-separated list of email addresses to cc each time a receipt is sent',
  `bcc_receipt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma-separated list of email addresses to bcc each time a receipt is sent',
  `receipt_text` text COLLATE utf8_unicode_ci COMMENT 'text to include above standard receipt info on receipt email. emails are text-only, so do not allow html for now',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `footer_text` text COLLATE utf8_unicode_ci COMMENT 'Text and html allowed. Displayed at the bottom of the first page of the contribution wizard.',
  `amount_block_is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this property active?',
  `honor_block_is_active` tinyint(4) DEFAULT NULL COMMENT 'Should this contribution have the honor  block enabled?',
  `honor_block_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for honor block.',
  `honor_block_text` text COLLATE utf8_unicode_ci COMMENT 'text for honor block.',
  `start_date` datetime DEFAULT NULL COMMENT 'Date and time that this page starts.',
  `end_date` datetime DEFAULT NULL COMMENT 'Date and time that this page ends. May be NULL if no defined end date/time',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this contribution page',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time that contribution page was created.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which we are collecting contributions with this page.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_processor` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processor for this contribution Page ',
  `is_share` tinyint(4) DEFAULT '1' COMMENT 'Can people share the contribution page through social media?',
  `is_confirm_enabled` tinyint(4) DEFAULT '1'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contribution_page`
--

LOCK TABLES `log_civicrm_contribution_page` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contribution_page` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contribution_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contribution_product`
--

DROP TABLE IF EXISTS `log_civicrm_contribution_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contribution_product` (
  `id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `contribution_id` int(10) unsigned NOT NULL,
  `product_option` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Option value selected if applicable - e.g. color, size etc.',
  `quantity` int(11) DEFAULT NULL,
  `fulfilled_date` date DEFAULT NULL COMMENT 'Optional. Can be used to record the date this product was fulfilled or shipped.',
  `start_date` date DEFAULT NULL COMMENT 'Actual start date for a time-delimited premium (subscription, service or membership)',
  `end_date` date DEFAULT NULL COMMENT 'Actual end date for a time-delimited premium (subscription, service or membership)',
  `comment` text COLLATE utf8_unicode_ci,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contribution_product`
--

LOCK TABLES `log_civicrm_contribution_product` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contribution_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contribution_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contribution_recur`
--

DROP TABLE IF EXISTS `log_civicrm_contribution_recur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contribution_recur` (
  `id` int(10) unsigned NOT NULL COMMENT 'Contribution Recur ID',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to civicrm_contact.id .',
  `amount` decimal(20,2) NOT NULL COMMENT 'Amount to be contributed or charged each recurrence.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `frequency_unit` enum('day','week','month','year') COLLATE utf8_unicode_ci DEFAULT 'month' COMMENT 'Time units for recurrence of payment.',
  `frequency_interval` int(10) unsigned NOT NULL COMMENT 'Number of time units for recurrence of payment.',
  `installments` int(10) unsigned DEFAULT NULL COMMENT 'Total number of payments to be made. Set this to 0 if this is an open-ended commitment i.e. no set end date.',
  `start_date` datetime NOT NULL COMMENT 'The date the first scheduled recurring contribution occurs.',
  `create_date` datetime NOT NULL COMMENT 'When this recurring contribution record was created.',
  `modified_date` datetime DEFAULT NULL COMMENT 'Last updated date for this record. mostly the last time a payment was received',
  `cancel_date` datetime DEFAULT NULL COMMENT 'Date this recurring contribution was cancelled by contributor- if we can get access to it',
  `end_date` datetime DEFAULT NULL COMMENT 'Date this recurring contribution finished successfully',
  `processor_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Possibly needed to store a unique identifier for this recurring payment order - if this is available from the processor??',
  `trxn_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'unique transaction id. may be processor id, bank id + trans id, or account number + check number... depending on payment_method',
  `invoice_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'unique invoice id, system generated or passed in',
  `contribution_status_id` int(10) unsigned DEFAULT '1',
  `is_test` tinyint(4) DEFAULT '0',
  `cycle_day` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Day in the period when the payment should be charged e.g. 1st of month, 15th etc.',
  `next_sched_contribution` datetime DEFAULT NULL COMMENT 'At Groundspring this was used by the cron job which triggered payments. If we''re not doing that but we know about payments, it might still be useful to store for display to org andor contributors.',
  `failure_count` int(10) unsigned DEFAULT '0' COMMENT 'Number of failed charge attempts since last success. Business rule could be set to deactivate on more than x failures.',
  `failure_retry_date` datetime DEFAULT NULL COMMENT 'At Groundspring we set a business rule to retry failed payments every 7 days - and stored the next scheduled attempt date there.',
  `auto_renew` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Some systems allow contributor to set a number of installments - but then auto-renew the subscription or commitment if they do not cancel.',
  `payment_processor_id` int(10) unsigned DEFAULT NULL COMMENT 'Foreign key to civicrm_payment_processor.id',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_email_receipt` tinyint(4) DEFAULT NULL COMMENT 'if true, receipt is automatically emailed to contact on each successful payment',
  `contribution_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contribution Type',
  `payment_instrument_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Payment Instrument',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this contribution has been triggered.'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contribution_recur`
--

LOCK TABLES `log_civicrm_contribution_recur` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contribution_recur` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contribution_recur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contribution_soft`
--

DROP TABLE IF EXISTS `log_civicrm_contribution_soft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contribution_soft` (
  `id` int(10) unsigned NOT NULL COMMENT 'Soft Contribution ID',
  `contribution_id` int(10) unsigned NOT NULL COMMENT 'FK to contribution table.',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID',
  `amount` decimal(20,2) NOT NULL COMMENT 'Amount of this soft contribution.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `pcp_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_pcp.id',
  `pcp_display_in_roll` tinyint(4) DEFAULT '0',
  `pcp_roll_nickname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pcp_personal_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contribution_soft`
--

LOCK TABLES `log_civicrm_contribution_soft` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contribution_soft` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contribution_soft` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contribution_type`
--

DROP TABLE IF EXISTS `log_civicrm_contribution_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contribution_type` (
  `id` int(10) unsigned NOT NULL COMMENT 'Contribution Type ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Contribution Type Name.',
  `accounting_code` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional value for mapping contributions to accounting system codes for each type/category of contribution.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Contribution Type Description.',
  `is_deductible` tinyint(4) DEFAULT '1' COMMENT 'Is this contribution type tax-deductible? If true, contributions of this type may be fully OR partially deductible - non-deductible amount is stored in the Contribution record.',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this a predefined system object?',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contribution_type`
--

LOCK TABLES `log_civicrm_contribution_type` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contribution_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contribution_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_contribution_widget`
--

DROP TABLE IF EXISTS `log_civicrm_contribution_widget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_contribution_widget` (
  `id` int(10) unsigned NOT NULL COMMENT 'Contribution Id',
  `contribution_page_id` int(10) unsigned DEFAULT NULL COMMENT 'The Contribution Page which triggered this contribution',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Widget title.',
  `url_logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL to Widget logo',
  `button_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Button title.',
  `about` text COLLATE utf8_unicode_ci COMMENT 'About description.',
  `url_homepage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL to Homepage.',
  `color_title` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_button` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_bar` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_main_text` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_main` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_main_bg` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_bg` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_about_link` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color_homepage_link` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_contribution_widget`
--

LOCK TABLES `log_civicrm_contribution_widget` WRITE;
/*!40000 ALTER TABLE `log_civicrm_contribution_widget` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_contribution_widget` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_country`
--

DROP TABLE IF EXISTS `log_civicrm_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_country` (
  `id` int(10) unsigned NOT NULL COMMENT 'Country Id',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Country Name',
  `iso_code` char(2) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ISO Code',
  `country_code` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'National prefix to be used when dialing TO this country.',
  `address_format_id` int(10) unsigned DEFAULT NULL COMMENT 'Foreign key to civicrm_address_format.id.',
  `idd_prefix` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'International direct dialing prefix from within the country TO another country',
  `ndd_prefix` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Access prefix to call within a country to a different area',
  `region_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to civicrm_worldregion.id.',
  `is_province_abbreviated` tinyint(4) DEFAULT '0' COMMENT 'Should state/province be displayed as abbreviation for contacts from this country?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_country`
--

LOCK TABLES `log_civicrm_country` WRITE;
/*!40000 ALTER TABLE `log_civicrm_country` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_county`
--

DROP TABLE IF EXISTS `log_civicrm_county`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_county` (
  `id` int(10) unsigned NOT NULL COMMENT 'County ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of County',
  `abbreviation` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '2-4 Character Abbreviation of County',
  `state_province_id` int(10) unsigned NOT NULL COMMENT 'ID of State / Province that County belongs',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_county`
--

LOCK TABLES `log_civicrm_county` WRITE;
/*!40000 ALTER TABLE `log_civicrm_county` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_county` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_currency`
--

DROP TABLE IF EXISTS `log_civicrm_currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_currency` (
  `id` int(10) unsigned NOT NULL COMMENT 'Currency Id',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Currency Name',
  `symbol` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Currency Symbol',
  `numeric_code` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Numeric currency code',
  `full_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Full currency name',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_currency`
--

LOCK TABLES `log_civicrm_currency` WRITE;
/*!40000 ALTER TABLE `log_civicrm_currency` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_currency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_custom_field`
--

DROP TABLE IF EXISTS `log_civicrm_custom_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_custom_field` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Custom Field ID',
  `custom_group_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_custom_group.',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Text for form field label (also friendly name for administering this custom property).',
  `data_type` enum('String','Int','Float','Money','Memo','Date','Boolean','StateProvince','Country','File','Link','ContactReference') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Controls location of data storage in extended_data table.',
  `html_type` enum('Text','TextArea','Select','Multi-Select','AdvMulti-Select','Radio','CheckBox','Select Date','Select State/Province','Select Country','Multi-Select Country','Multi-Select State/Province','File','Link','RichTextEditor','Autocomplete-Select') COLLATE utf8_unicode_ci NOT NULL COMMENT 'HTML types plus several built-in extended types.',
  `default_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Use form_options.is_default for field_types which use options.',
  `is_required` tinyint(4) DEFAULT NULL COMMENT 'Is a value required for this property.',
  `is_searchable` tinyint(4) DEFAULT NULL COMMENT 'Is this property searchable.',
  `is_search_range` tinyint(4) DEFAULT '0' COMMENT 'Is this property range searchable.',
  `weight` int(11) NOT NULL DEFAULT '1' COMMENT 'Controls field display order within an extended property group.',
  `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display before this field.',
  `help_post` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display after this field.',
  `mask` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional format instructions for specific field types, like date types.',
  `attributes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Store collection of type-appropriate attributes, e.g. textarea  needs rows/cols attributes',
  `javascript` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional scripting attributes for field.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `is_view` tinyint(4) DEFAULT NULL COMMENT 'Is this property set by PHP Code? A code field is viewable but not editable',
  `options_per_line` int(10) unsigned DEFAULT NULL COMMENT 'number of options per line for checkbox and radio',
  `text_length` int(10) unsigned DEFAULT NULL COMMENT 'field length if alphanumeric',
  `start_date_years` int(10) DEFAULT NULL,
  `end_date_years` int(10) DEFAULT NULL,
  `date_format` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'date format for custom date',
  `time_format` int(10) unsigned DEFAULT NULL COMMENT 'time format for custom date',
  `note_columns` int(10) unsigned DEFAULT NULL COMMENT ' Number of columns in Note Field ',
  `note_rows` int(10) unsigned DEFAULT NULL COMMENT ' Number of rows in Note Field ',
  `column_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the column that holds the values for this field.',
  `option_group_id` int(10) unsigned DEFAULT NULL COMMENT 'For elements with options, the option group id that is used',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Stores Contact Get API params contact reference custom fields. May be used for other filters in the future.'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_custom_field`
--

LOCK TABLES `log_civicrm_custom_field` WRITE;
/*!40000 ALTER TABLE `log_civicrm_custom_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_custom_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_custom_group`
--

DROP TABLE IF EXISTS `log_civicrm_custom_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_custom_group` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Custom Group ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Variable name/programmatic handle for this group.',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Friendly Name.',
  `extends` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'Contact' COMMENT 'Type of object this group extends (can add other options later e.g. contact_address, etc.).',
  `extends_entity_column_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.id (for option group custom_data_type.)',
  `extends_entity_column_value` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'linking custom group for dynamic object',
  `style` enum('Tab','Inline') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Visual relationship between this form and its parent.',
  `collapse_display` int(10) unsigned DEFAULT '0' COMMENT 'Will this group be in collapsed or expanded mode on initial display ?',
  `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display before fields in form.',
  `help_post` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display after fields in form.',
  `weight` int(11) NOT NULL DEFAULT '1' COMMENT 'Controls display order when multiple extended property groups are setup for the same class.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `table_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the table that holds the values for this group.',
  `is_multiple` tinyint(4) DEFAULT NULL COMMENT 'Does this group hold multiple values?',
  `min_multiple` int(10) unsigned DEFAULT NULL COMMENT 'minimum number of multiple records (typically 0?)',
  `max_multiple` int(10) unsigned DEFAULT NULL COMMENT 'maximum number of multiple records, if 0 - no max',
  `collapse_adv_display` int(10) unsigned DEFAULT '0' COMMENT 'Will this group be in collapsed or expanded mode on advanced search display ?',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this custom group',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time this custom group was created.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_custom_group`
--

LOCK TABLES `log_civicrm_custom_group` WRITE;
/*!40000 ALTER TABLE `log_civicrm_custom_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_custom_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_dashboard`
--

DROP TABLE IF EXISTS `log_civicrm_dashboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_dashboard` (
  `id` int(10) unsigned NOT NULL,
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Domain for dashboard',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'dashlet title',
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'url in case of external dashlet',
  `permission` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Permission for the dashlet',
  `permission_operator` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Permission Operator',
  `column_no` tinyint(4) DEFAULT '0' COMMENT 'column no for this dashlet',
  `is_minimized` tinyint(4) DEFAULT '0' COMMENT 'Is Minimized?',
  `is_fullscreen` tinyint(4) DEFAULT '1' COMMENT 'Is Fullscreen?',
  `is_active` tinyint(4) DEFAULT '0' COMMENT 'Is this dashlet active?',
  `is_reserved` tinyint(4) DEFAULT '0' COMMENT 'Is this dashlet reserved?',
  `weight` int(11) DEFAULT '0' COMMENT 'Ordering of the dashlets.',
  `fullscreen_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'fullscreen url for dashlet',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_dashboard`
--

LOCK TABLES `log_civicrm_dashboard` WRITE;
/*!40000 ALTER TABLE `log_civicrm_dashboard` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_dashboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_dashboard_contact`
--

DROP TABLE IF EXISTS `log_civicrm_dashboard_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_dashboard_contact` (
  `id` int(10) unsigned NOT NULL,
  `dashboard_id` int(10) unsigned NOT NULL COMMENT 'Dashboard ID',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Contact ID',
  `column_no` tinyint(4) DEFAULT '0' COMMENT 'column no for this widget',
  `is_minimized` tinyint(4) DEFAULT '0' COMMENT 'Is Minimized?',
  `is_fullscreen` tinyint(4) DEFAULT '1' COMMENT 'Is Fullscreen?',
  `is_active` tinyint(4) DEFAULT '0' COMMENT 'Is this widget active?',
  `weight` int(11) DEFAULT '0' COMMENT 'Ordering of the widgets.',
  `content` longtext COLLATE utf8_unicode_ci COMMENT 'dashlet content',
  `created_date` datetime DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `contact_id` (`contact_id`),
  KEY `dashboard_id` (`dashboard_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_dashboard_contact`
--

LOCK TABLES `log_civicrm_dashboard_contact` WRITE;
/*!40000 ALTER TABLE `log_civicrm_dashboard_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_dashboard_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_dedupe_exception`
--

DROP TABLE IF EXISTS `log_civicrm_dedupe_exception`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_dedupe_exception` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique dedupe exception id.',
  `contact_id1` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `contact_id2` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_dedupe_exception`
--

LOCK TABLES `log_civicrm_dedupe_exception` WRITE;
/*!40000 ALTER TABLE `log_civicrm_dedupe_exception` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_dedupe_exception` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_dedupe_rule`
--

DROP TABLE IF EXISTS `log_civicrm_dedupe_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_dedupe_rule` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique dedupe rule id',
  `dedupe_rule_group_id` int(10) unsigned NOT NULL COMMENT 'The id of the rule group this rule belongs to',
  `rule_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name of the table this rule is about',
  `rule_field` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name of the field of the table referenced in rule_table',
  `rule_length` int(10) unsigned DEFAULT NULL COMMENT 'The lenght of the matching substring',
  `rule_weight` int(11) NOT NULL COMMENT 'The weight of the rule',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_dedupe_rule`
--

LOCK TABLES `log_civicrm_dedupe_rule` WRITE;
/*!40000 ALTER TABLE `log_civicrm_dedupe_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_dedupe_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_dedupe_rule_group`
--

DROP TABLE IF EXISTS `log_civicrm_dedupe_rule_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_dedupe_rule_group` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique dedupe rule group id',
  `contact_type` enum('Individual','Organization','Household') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The type of contacts this group applies to',
  `threshold` int(11) NOT NULL COMMENT 'The weight threshold the sum of the rule weights has to cross to consider two contacts the same',
  `level` enum('Strict','Fuzzy') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Whether the rule should be used for cases where strict maching of the given contact type is required or a fuzzy one',
  `is_default` tinyint(4) DEFAULT NULL COMMENT 'Is this a default rule (one rule for every contact type + level combination should be default)',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the rule group',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Label of the rule group',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this a reserved rule - a rule group that has been optimized and cannot be changed by the admin'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_dedupe_rule_group`
--

LOCK TABLES `log_civicrm_dedupe_rule_group` WRITE;
/*!40000 ALTER TABLE `log_civicrm_dedupe_rule_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_dedupe_rule_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_discount`
--

DROP TABLE IF EXISTS `log_civicrm_discount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_discount` (
  `id` int(10) unsigned NOT NULL COMMENT 'primary key',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'physical tablename for entity being joined to discount, e.g. civicrm_event',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'FK to entity table specified in entity_table column.',
  `option_group_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_option_group',
  `start_date` date DEFAULT NULL COMMENT 'Date when discount starts.',
  `end_date` date DEFAULT NULL COMMENT 'Date when discount ends.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_discount`
--

LOCK TABLES `log_civicrm_discount` WRITE;
/*!40000 ALTER TABLE `log_civicrm_discount` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_discount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_domain`
--

DROP TABLE IF EXISTS `log_civicrm_domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_domain` (
  `id` int(10) unsigned NOT NULL COMMENT 'Domain ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of Domain / Organization',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Description of Domain.',
  `config_backend` text COLLATE utf8_unicode_ci COMMENT 'Backend configuration.',
  `version` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The civicrm version this instance is running',
  `loc_block_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Location Block ID. This is specifically not an FK to avoid circular constraints',
  `locales` text COLLATE utf8_unicode_ci COMMENT 'list of locales supported by the current db state (NULL for single-lang install)',
  `locale_custom_strings` text COLLATE utf8_unicode_ci COMMENT 'Locale specific string overrides',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_domain`
--

LOCK TABLES `log_civicrm_domain` WRITE;
/*!40000 ALTER TABLE `log_civicrm_domain` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_domain` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_email`
--

DROP TABLE IF EXISTS `log_civicrm_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_email` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Email ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Location does this email belong to.',
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Email address',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary?',
  `is_billing` tinyint(4) DEFAULT '0' COMMENT 'Is this the billing?',
  `on_hold` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Is this address on bounce hold?',
  `is_bulkmail` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Is this address for bulk mail ?',
  `hold_date` datetime DEFAULT NULL COMMENT 'When the address went on bounce hold',
  `reset_date` datetime DEFAULT NULL COMMENT 'When the address bounce status was last reset',
  `signature_text` text COLLATE utf8_unicode_ci COMMENT 'Text formatted signature for the email.',
  `signature_html` text COLLATE utf8_unicode_ci COMMENT 'HTML formatted signature for the email.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `contact_id` (`contact_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_email`
--

LOCK TABLES `log_civicrm_email` WRITE;
/*!40000 ALTER TABLE `log_civicrm_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_entity_batch`
--

DROP TABLE IF EXISTS `log_civicrm_entity_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_entity_batch` (
  `id` int(10) unsigned NOT NULL COMMENT 'primary key',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'physical tablename for entity being joined to file, e.g. civicrm_contact',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'FK to entity table specified in entity_table column.',
  `batch_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_batch',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_entity_batch`
--

LOCK TABLES `log_civicrm_entity_batch` WRITE;
/*!40000 ALTER TABLE `log_civicrm_entity_batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_entity_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_entity_file`
--

DROP TABLE IF EXISTS `log_civicrm_entity_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_entity_file` (
  `id` int(10) unsigned NOT NULL COMMENT 'primary key',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'physical tablename for entity being joined to file, e.g. civicrm_contact',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'FK to entity table specified in entity_table column.',
  `file_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_file',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_entity_file`
--

LOCK TABLES `log_civicrm_entity_file` WRITE;
/*!40000 ALTER TABLE `log_civicrm_entity_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_entity_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_entity_financial_trxn`
--

DROP TABLE IF EXISTS `log_civicrm_entity_financial_trxn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_entity_financial_trxn` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `financial_trxn_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(20,2) NOT NULL COMMENT 'allocated amount of transaction to this entity',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_entity_financial_trxn`
--

LOCK TABLES `log_civicrm_entity_financial_trxn` WRITE;
/*!40000 ALTER TABLE `log_civicrm_entity_financial_trxn` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_entity_financial_trxn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_entity_tag`
--

DROP TABLE IF EXISTS `log_civicrm_entity_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_entity_tag` (
  `id` int(10) unsigned NOT NULL COMMENT 'primary key',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'physical tablename for entity being joined to file, e.g. civicrm_contact',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'FK to entity table specified in entity_table column.',
  `tag_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_tag',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `entity_id` (`entity_id`),
  KEY `entity_table` (`entity_table`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_entity_tag`
--

LOCK TABLES `log_civicrm_entity_tag` WRITE;
/*!40000 ALTER TABLE `log_civicrm_entity_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_entity_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_event`
--

DROP TABLE IF EXISTS `log_civicrm_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_event` (
  `id` int(10) unsigned NOT NULL COMMENT 'Event',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Event Title (e.g. Fall Fundraiser Dinner)',
  `summary` text COLLATE utf8_unicode_ci COMMENT 'Brief summary of event. Text and html allowed. Displayed on Event Registration form and can be used on other CMS pages which need an event summary.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Full description of event. Text and html allowed. Displayed on built-in Event Information screens.',
  `event_type_id` int(10) unsigned DEFAULT '0' COMMENT 'Event Type ID.Implicit FK to civicrm_option_value where option_group = event_type.',
  `participant_listing_id` int(10) unsigned DEFAULT '0' COMMENT 'Should we expose the participant list? Implicit FK to civicrm_option_value where option_group = participant_listing.',
  `is_public` tinyint(4) DEFAULT '1' COMMENT 'Public events will be included in the iCal feeds. Access to private event information may be limited using ACLs.',
  `start_date` datetime DEFAULT NULL COMMENT 'Date and time that event starts.',
  `end_date` datetime DEFAULT NULL COMMENT 'Date and time that event ends. May be NULL if no defined end date/time',
  `is_online_registration` tinyint(4) DEFAULT '0' COMMENT 'If true, include registration link on Event Info page.',
  `registration_link_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Text for link to Event Registration form which is displayed on Event Information screen when is_online_registration is true.',
  `registration_start_date` datetime DEFAULT NULL COMMENT 'Date and time that online registration starts.',
  `registration_end_date` datetime DEFAULT NULL COMMENT 'Date and time that online registration ends.',
  `max_participants` int(10) unsigned DEFAULT NULL COMMENT 'Maximum number of registered participants to allow. After max is reached, a custom Event Full message is displayed. If NULL, allow unlimited number of participants.',
  `event_full_text` text COLLATE utf8_unicode_ci COMMENT 'Message to display on Event Information page and INSTEAD OF Event Registration form if maximum participants are signed up. Can include email address/info about getting on a waiting list, etc. Text and html allowed.',
  `is_monetary` tinyint(4) DEFAULT '0' COMMENT 'Is this a PAID event? If true, one or more fee amounts must be set and a Payment Processor must be configured for Online Event Registration.',
  `contribution_type_id` int(10) unsigned DEFAULT '0' COMMENT 'Contribution type assigned to paid event registrations for this event. Required if is_monetary is true.',
  `payment_processor_id` int(10) unsigned DEFAULT NULL COMMENT 'Payment Processor for this Event (if is_monetary is true)',
  `is_map` tinyint(4) DEFAULT '0' COMMENT 'Include a map block on the Event Information page when geocode info is available and a mapping provider has been specified?',
  `is_active` tinyint(4) DEFAULT '0' COMMENT 'Is this Event enabled or disabled/cancelled?',
  `fee_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_show_location` tinyint(4) DEFAULT '1' COMMENT 'If true, show event location.',
  `loc_block_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Location Block ID',
  `default_role_id` int(10) unsigned DEFAULT '1' COMMENT 'Participant role ID. Implicit FK to civicrm_option_value where option_group = participant_role.',
  `intro_text` text COLLATE utf8_unicode_ci COMMENT 'Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.',
  `footer_text` text COLLATE utf8_unicode_ci COMMENT 'Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.',
  `confirm_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for Confirmation page.',
  `confirm_text` text COLLATE utf8_unicode_ci COMMENT 'Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.',
  `confirm_footer_text` text COLLATE utf8_unicode_ci COMMENT 'Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.',
  `is_email_confirm` tinyint(4) DEFAULT '0' COMMENT 'If true, confirmation is automatically emailed to contact on successful registration.',
  `confirm_email_text` text COLLATE utf8_unicode_ci COMMENT 'text to include above standard event info on confirmation email. emails are text-only, so do not allow html for now',
  `confirm_from_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'FROM email name used for confirmation emails.',
  `confirm_from_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'FROM email address used for confirmation emails.',
  `cc_confirm` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma-separated list of email addresses to cc each time a confirmation is sent',
  `bcc_confirm` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma-separated list of email addresses to bcc each time a confirmation is sent',
  `default_fee_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.',
  `default_discount_fee_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.',
  `thankyou_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for ThankYou page.',
  `thankyou_text` text COLLATE utf8_unicode_ci COMMENT 'ThankYou Text.',
  `thankyou_footer_text` text COLLATE utf8_unicode_ci COMMENT 'Footer message.',
  `is_pay_later` tinyint(4) DEFAULT '0' COMMENT 'if true - allows the user to send payment directly to the org later',
  `pay_later_text` text COLLATE utf8_unicode_ci COMMENT 'The text displayed to the user in the main form',
  `pay_later_receipt` text COLLATE utf8_unicode_ci COMMENT 'The receipt sent to the user instead of the normal receipt text',
  `is_multiple_registrations` tinyint(4) DEFAULT '0' COMMENT 'if true - allows the user to register multiple participants for event',
  `allow_same_participant_emails` tinyint(4) DEFAULT '0' COMMENT 'if true - allows the user to register multiple registrations from same email address.',
  `has_waitlist` tinyint(4) DEFAULT NULL COMMENT 'Whether the event has waitlist support.',
  `requires_approval` tinyint(4) DEFAULT NULL COMMENT 'Whether participants require approval before they can finish registering.',
  `expiration_time` int(10) unsigned DEFAULT NULL COMMENT 'Expire pending but unconfirmed registrations after this many hours.',
  `waitlist_text` text COLLATE utf8_unicode_ci COMMENT 'Text to display when the event is full, but participants can signup for a waitlist.',
  `approval_req_text` text COLLATE utf8_unicode_ci COMMENT 'Text to display when the approval is required to complete registration for an event.',
  `is_template` tinyint(4) DEFAULT '0' COMMENT 'whether the event has template',
  `template_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Event Template Title',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this event',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time that event was created.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this event has been created.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_processor` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processor for this event ',
  `is_share` tinyint(4) DEFAULT '1' COMMENT 'Can people share the event through social media?',
  `parent_event_id` int(10) unsigned DEFAULT NULL COMMENT 'Implicit FK to civicrm_event: parent event',
  `slot_label_id` int(10) unsigned DEFAULT NULL COMMENT 'Subevent slot label. Implicit FK to civicrm_option_value where option_group = conference_slot.'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_event`
--

LOCK TABLES `log_civicrm_event` WRITE;
/*!40000 ALTER TABLE `log_civicrm_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_event_carts`
--

DROP TABLE IF EXISTS `log_civicrm_event_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_event_carts` (
  `id` int(10) unsigned NOT NULL COMMENT 'Cart Id',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact who created this cart',
  `coupon_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `completed` tinyint(4) DEFAULT '0',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_event_carts`
--

LOCK TABLES `log_civicrm_event_carts` WRITE;
/*!40000 ALTER TABLE `log_civicrm_event_carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_event_carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_events_in_carts`
--

DROP TABLE IF EXISTS `log_civicrm_events_in_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_events_in_carts` (
  `id` int(10) unsigned NOT NULL COMMENT 'Event In Cart Id',
  `event_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Event ID',
  `event_cart_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Event Cart ID',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_events_in_carts`
--

LOCK TABLES `log_civicrm_events_in_carts` WRITE;
/*!40000 ALTER TABLE `log_civicrm_events_in_carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_events_in_carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_extension`
--

DROP TABLE IF EXISTS `log_civicrm_extension`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_extension` (
  `id` int(10) unsigned NOT NULL COMMENT 'Local Extension ID',
  `type` enum('payment','search','report','module') COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Fully qualified extension name',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Short name',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Short, printable name',
  `file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Primary PHP file',
  `schema_version` varchar(63) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Revision code of the database schema; the format is module-defined',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this extension active?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_extension`
--

LOCK TABLES `log_civicrm_extension` WRITE;
/*!40000 ALTER TABLE `log_civicrm_extension` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_extension` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_file`
--

DROP TABLE IF EXISTS `log_civicrm_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_file` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique ID',
  `file_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Type of file (e.g. Transcript, Income Tax Return, etc). FK to civicrm_option_value.',
  `mime_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'mime type of the document',
  `uri` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'uri of the file on disk',
  `document` mediumblob COMMENT 'contents of the document',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Additional descriptive text regarding this attachment (optional).',
  `upload_date` datetime DEFAULT NULL COMMENT 'Date and time that this attachment was uploaded or written to server.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_file`
--

LOCK TABLES `log_civicrm_file` WRITE;
/*!40000 ALTER TABLE `log_civicrm_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_financial_account`
--

DROP TABLE IF EXISTS `log_civicrm_financial_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_financial_account` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_contact',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `account_type_id` int(10) unsigned NOT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_financial_account`
--

LOCK TABLES `log_civicrm_financial_account` WRITE;
/*!40000 ALTER TABLE `log_civicrm_financial_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_financial_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_financial_trxn`
--

DROP TABLE IF EXISTS `log_civicrm_financial_trxn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_financial_trxn` (
  `id` int(10) unsigned NOT NULL COMMENT 'Gift ID',
  `from_account_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to financial_account table.',
  `to_account_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to financial_account table.',
  `trxn_date` datetime NOT NULL,
  `trxn_type` enum('Debit','Credit') COLLATE utf8_unicode_ci NOT NULL,
  `total_amount` decimal(20,2) NOT NULL COMMENT 'amount of transaction',
  `fee_amount` decimal(20,2) DEFAULT NULL COMMENT 'actual processor fee if known - may be 0.',
  `net_amount` decimal(20,2) DEFAULT NULL COMMENT 'actual funds transfer amount. total less fees. if processor does not report actual fee during transaction, this is set to total_amount.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `payment_processor` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'derived from Processor setting in civicrm.settings.php.',
  `trxn_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'unique processor transaction id, bank id + trans id,... depending on payment_method',
  `trxn_result_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'processor result code',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_financial_trxn`
--

LOCK TABLES `log_civicrm_financial_trxn` WRITE;
/*!40000 ALTER TABLE `log_civicrm_financial_trxn` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_financial_trxn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_grant`
--

DROP TABLE IF EXISTS `log_civicrm_grant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_grant` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Grant id',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Contact ID of contact record given grant belongs to.',
  `application_received_date` date DEFAULT NULL COMMENT 'Date on which grant application was received by donor.',
  `decision_date` date DEFAULT NULL COMMENT 'Date on which grant decision was made.',
  `money_transfer_date` date DEFAULT NULL COMMENT 'Date on which grant money transfer was made.',
  `grant_due_date` date DEFAULT NULL COMMENT 'Date on which grant report is due.',
  `grant_report_received` tinyint(4) DEFAULT NULL COMMENT 'Yes/No field stating whether grant report was received by donor.',
  `grant_type_id` int(10) unsigned NOT NULL COMMENT 'Type of grant. Implicit FK to civicrm_option_value in grant_type option_group.',
  `amount_total` decimal(20,2) NOT NULL COMMENT 'Requested grant amount, in default currency.',
  `amount_requested` decimal(20,2) DEFAULT NULL COMMENT 'Requested grant amount, in original currency (optional).',
  `amount_granted` decimal(20,2) DEFAULT NULL COMMENT 'Granted amount, in default currency.',
  `currency` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `rationale` text COLLATE utf8_unicode_ci COMMENT 'Grant rationale.',
  `status_id` int(10) unsigned NOT NULL COMMENT 'Id of Grant status.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_grant`
--

LOCK TABLES `log_civicrm_grant` WRITE;
/*!40000 ALTER TABLE `log_civicrm_grant` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_grant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_group`
--

DROP TABLE IF EXISTS `log_civicrm_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_group` (
  `id` int(10) unsigned NOT NULL COMMENT 'Group ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Internal name of Group.',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of Group.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Optional verbose description of the group.',
  `source` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Module or process which created this group.',
  `saved_search_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to saved search table.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this entry active?',
  `visibility` enum('User and User Admin Only','Public Pages') COLLATE utf8_unicode_ci DEFAULT 'User and User Admin Only' COMMENT 'In what context(s) is this field visible.',
  `where_clause` text COLLATE utf8_unicode_ci COMMENT 'the sql where clause if a saved search acl',
  `select_tables` text COLLATE utf8_unicode_ci COMMENT 'the tables to be included in a select data',
  `where_tables` text COLLATE utf8_unicode_ci COMMENT 'the tables to be included in the count statement',
  `group_type` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'FK to group type',
  `cache_date` datetime DEFAULT NULL COMMENT 'Date when we created the cache for a smart group',
  `parents` text COLLATE utf8_unicode_ci COMMENT 'IDs of the parent(s)',
  `children` text COLLATE utf8_unicode_ci COMMENT 'IDs of the child(ren)',
  `is_hidden` tinyint(4) DEFAULT '0' COMMENT 'Is this group hidden?',
  `is_reserved` tinyint(4) NOT NULL DEFAULT '0',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `id` (`id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_group`
--

LOCK TABLES `log_civicrm_group` WRITE;
/*!40000 ALTER TABLE `log_civicrm_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_group_contact`
--

DROP TABLE IF EXISTS `log_civicrm_group_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_group_contact` (
  `id` int(10) unsigned NOT NULL COMMENT 'primary key',
  `group_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_group',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_contact',
  `status` enum('Added','Removed','Pending') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'status of contact relative to membership in group',
  `location_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional location to associate with this membership',
  `email_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional email to associate with this membership',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `group_id` (`group_id`),
  KEY `contact_id` (`contact_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_group_contact`
--

LOCK TABLES `log_civicrm_group_contact` WRITE;
/*!40000 ALTER TABLE `log_civicrm_group_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_group_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_group_nesting`
--

DROP TABLE IF EXISTS `log_civicrm_group_nesting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_group_nesting` (
  `id` int(10) unsigned NOT NULL COMMENT 'Relationship ID',
  `child_group_id` int(10) unsigned NOT NULL COMMENT 'ID of the child group',
  `parent_group_id` int(10) unsigned NOT NULL COMMENT 'ID of the parent group',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_group_nesting`
--

LOCK TABLES `log_civicrm_group_nesting` WRITE;
/*!40000 ALTER TABLE `log_civicrm_group_nesting` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_group_nesting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_group_organization`
--

DROP TABLE IF EXISTS `log_civicrm_group_organization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_group_organization` (
  `id` int(10) unsigned NOT NULL COMMENT 'Relationship ID',
  `group_id` int(10) unsigned NOT NULL COMMENT 'ID of the group',
  `organization_id` int(10) unsigned NOT NULL COMMENT 'ID of the Organization Contact',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_group_organization`
--

LOCK TABLES `log_civicrm_group_organization` WRITE;
/*!40000 ALTER TABLE `log_civicrm_group_organization` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_group_organization` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_im`
--

DROP TABLE IF EXISTS `log_civicrm_im`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_im` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique IM ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Location does this email belong to.',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'IM screen name',
  `provider_id` int(10) unsigned DEFAULT NULL COMMENT 'Which IM Provider does this screen name belong to.',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary IM for this contact and location.',
  `is_billing` tinyint(4) DEFAULT '0' COMMENT 'Is this the billing?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_im`
--

LOCK TABLES `log_civicrm_im` WRITE;
/*!40000 ALTER TABLE `log_civicrm_im` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_im` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_importer_jobs`
--

DROP TABLE IF EXISTS `log_civicrm_importer_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_importer_jobs` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `source_file` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `field_separator` varchar(10) NOT NULL,
  `contact_group_id` int(10) unsigned NOT NULL,
  `created_on` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') DEFAULT NULL,
  `log_job_id` varchar(64) DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_importer_jobs`
--

LOCK TABLES `log_civicrm_importer_jobs` WRITE;
/*!40000 ALTER TABLE `log_civicrm_importer_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_importer_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_job`
--

DROP TABLE IF EXISTS `log_civicrm_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_job` (
  `id` int(10) unsigned NOT NULL COMMENT 'Job Id',
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this scheduled job for',
  `run_frequency` enum('Hourly','Daily','Always') COLLATE utf8_unicode_ci DEFAULT 'Daily' COMMENT 'Scheduled job run frequency.',
  `last_run` datetime DEFAULT NULL COMMENT 'When was this cron entry last run',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title of the job',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Description of the job',
  `api_prefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Prefix of the job api call',
  `api_entity` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Entity of the job api call',
  `api_action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Action of the job api call',
  `parameters` text COLLATE utf8_unicode_ci COMMENT 'List of parameters to the command.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this job active?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_job`
--

LOCK TABLES `log_civicrm_job` WRITE;
/*!40000 ALTER TABLE `log_civicrm_job` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_line_item`
--

DROP TABLE IF EXISTS `log_civicrm_line_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_line_item` (
  `id` int(10) unsigned NOT NULL COMMENT 'Line Item',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'table which has the transaction',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'entry in table',
  `price_field_id` int(10) unsigned NOT NULL COMMENT 'FK to price_field',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'descriptive label for item - from custom_option.label',
  `qty` int(10) unsigned NOT NULL COMMENT 'How many items ordered',
  `unit_price` decimal(20,2) NOT NULL COMMENT 'price of each item',
  `line_total` decimal(20,2) NOT NULL COMMENT 'qty * unit_price',
  `participant_count` int(10) unsigned DEFAULT NULL COMMENT 'Participant count for field',
  `price_field_value_id` int(10) unsigned DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_line_item`
--

LOCK TABLES `log_civicrm_line_item` WRITE;
/*!40000 ALTER TABLE `log_civicrm_line_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_line_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_loc_block`
--

DROP TABLE IF EXISTS `log_civicrm_loc_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_loc_block` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique ID',
  `address_id` int(10) unsigned DEFAULT NULL,
  `email_id` int(10) unsigned DEFAULT NULL,
  `phone_id` int(10) unsigned DEFAULT NULL,
  `im_id` int(10) unsigned DEFAULT NULL,
  `address_2_id` int(10) unsigned DEFAULT NULL,
  `email_2_id` int(10) unsigned DEFAULT NULL,
  `phone_2_id` int(10) unsigned DEFAULT NULL,
  `im_2_id` int(10) unsigned DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_loc_block`
--

LOCK TABLES `log_civicrm_loc_block` WRITE;
/*!40000 ALTER TABLE `log_civicrm_loc_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_loc_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_location_type`
--

DROP TABLE IF EXISTS `log_civicrm_location_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_location_type` (
  `id` int(10) unsigned NOT NULL COMMENT 'Location Type ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Location Type Name.',
  `vcard_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'vCard Location Type Name.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Location Type Description.',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this location type a predefined system location?',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `is_default` tinyint(4) DEFAULT NULL COMMENT 'Is this location type the default?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Location Type Display Name.'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_location_type`
--

LOCK TABLES `log_civicrm_location_type` WRITE;
/*!40000 ALTER TABLE `log_civicrm_location_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_location_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mail_settings`
--

DROP TABLE IF EXISTS `log_civicrm_mail_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mail_settings` (
  `id` int(10) unsigned NOT NULL COMMENT 'primary key',
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this match entry for',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'name of this group of settings',
  `is_default` tinyint(4) DEFAULT NULL COMMENT 'whether this is the default set of settings for this domain',
  `domain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'email address domain (the part after @)',
  `localpart` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'optional local part (like civimail+ for addresses like civimail+s.1.2@example.com)',
  `return_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'contents of the Return-Path header',
  `protocol` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'name of the protocol to use for polling (like IMAP, POP3 or Maildir)',
  `server` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'server to use when polling',
  `port` int(10) unsigned DEFAULT NULL COMMENT 'port to use when polling',
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'username to use when polling',
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'password to use when polling',
  `is_ssl` tinyint(4) DEFAULT NULL COMMENT 'whether to use SSL or not',
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'folder to poll from when using IMAP, path to poll from when using Maildir, etc.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mail_settings`
--

LOCK TABLES `log_civicrm_mail_settings` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mail_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mail_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing`
--

DROP TABLE IF EXISTS `log_civicrm_mailing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing` (
  `id` int(10) unsigned NOT NULL,
  `domain_id` int(10) unsigned DEFAULT NULL COMMENT 'Which site is this mailing for.',
  `header_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the header component.',
  `footer_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the footer component.',
  `reply_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the auto-responder component.',
  `unsubscribe_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the unsubscribe component.',
  `resubscribe_id` int(10) unsigned DEFAULT NULL,
  `optout_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the opt-out component.',
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Mailing Name.',
  `from_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'From Header of mailing',
  `from_email` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'From Email of mailing',
  `replyto_email` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Reply-To Email of mailing',
  `subject` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Subject of mailing',
  `body_text` longtext COLLATE utf8_unicode_ci COMMENT 'Body of the mailing in text format.',
  `body_html` longtext COLLATE utf8_unicode_ci COMMENT 'Body of the mailing in html format.',
  `url_tracking` tinyint(4) DEFAULT NULL COMMENT 'Should we track URL click-throughs for this mailing?',
  `forward_replies` tinyint(4) DEFAULT NULL COMMENT 'Should we forward replies back to the author?',
  `auto_responder` tinyint(4) DEFAULT NULL COMMENT 'Should we enable the auto-responder?',
  `open_tracking` tinyint(4) DEFAULT NULL COMMENT 'Should we track when recipients open/read this mailing?',
  `is_completed` tinyint(4) DEFAULT NULL COMMENT 'Has at least one job associated with this mailing finished?',
  `msg_template_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to the message template.',
  `override_verp` tinyint(4) DEFAULT '0' COMMENT 'Should we overrite VERP address in Reply-To',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID who first created this mailing',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time this mailing was created.',
  `scheduled_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID who scheduled this mailing',
  `is_archived` tinyint(4) DEFAULT '0' COMMENT 'Is this mailing archived?',
  `scheduled_date` datetime DEFAULT NULL COMMENT 'Date and time this mailing was scheduled.',
  `approver_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID who approved this mailing',
  `approval_date` datetime DEFAULT NULL COMMENT 'Date and time this mailing was approved.',
  `approval_status_id` int(10) unsigned DEFAULT NULL COMMENT 'The status of this mailing. values: none, approved, rejected',
  `approval_note` longtext COLLATE utf8_unicode_ci COMMENT 'Note behind the decision.',
  `visibility` enum('User and User Admin Only','Public Pages') COLLATE utf8_unicode_ci DEFAULT 'User and User Admin Only' COMMENT 'In what context(s) is the mailing contents visible (online viewing)',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this mailing has been initiated.',
  `dedupe_email` tinyint(4) DEFAULT NULL,
  `all_emails` tinyint(4) DEFAULT NULL,
  `exclude_ood` tinyint(4) DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sms_provider_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_sms_provider id '
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing`
--

LOCK TABLES `log_civicrm_mailing` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing_bounce_pattern`
--

DROP TABLE IF EXISTS `log_civicrm_mailing_bounce_pattern`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing_bounce_pattern` (
  `id` int(10) unsigned NOT NULL,
  `bounce_type_id` int(10) unsigned NOT NULL COMMENT 'Type of bounce',
  `pattern` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A regexp to match a message to a bounce type',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing_bounce_pattern`
--

LOCK TABLES `log_civicrm_mailing_bounce_pattern` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing_bounce_pattern` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing_bounce_pattern` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing_bounce_type`
--

DROP TABLE IF EXISTS `log_civicrm_mailing_bounce_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing_bounce_type` (
  `id` int(10) unsigned NOT NULL,
  `name` enum('AOL','Away','DNS','Host','Inactive','Invalid','Loop','Quota','Relay','Spam','Syntax','Unknown') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Type of bounce',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A description of this bounce type',
  `hold_threshold` int(10) unsigned NOT NULL COMMENT 'Number of bounces of this type required before the email address is put on bounce hold',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing_bounce_type`
--

LOCK TABLES `log_civicrm_mailing_bounce_type` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing_bounce_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing_bounce_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing_component`
--

DROP TABLE IF EXISTS `log_civicrm_mailing_component`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing_component` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The name of this component',
  `component_type` enum('Header','Footer','Subscribe','Welcome','Unsubscribe','OptOut','Reply','Resubscribe') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Type of Component.',
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body_html` text COLLATE utf8_unicode_ci COMMENT 'Body of the component in html format.',
  `body_text` text COLLATE utf8_unicode_ci COMMENT 'Body of the component in text format.',
  `is_default` tinyint(4) DEFAULT '0' COMMENT 'Is this the default component for this component_type?',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing_component`
--

LOCK TABLES `log_civicrm_mailing_component` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing_component` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing_component` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing_group`
--

DROP TABLE IF EXISTS `log_civicrm_mailing_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing_group` (
  `id` int(10) unsigned NOT NULL,
  `mailing_id` int(10) unsigned NOT NULL COMMENT 'The ID of a previous mailing to include/exclude recipients.',
  `group_type` enum('Include','Exclude','Base') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Are the members of the group included or excluded?.',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of table where item being referenced is stored.',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the referenced item.',
  `search_id` int(11) DEFAULT NULL COMMENT 'The filtering search. custom search id or -1 for civicrm api search',
  `search_args` text COLLATE utf8_unicode_ci COMMENT 'The arguments to be sent to the search function',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing_group`
--

LOCK TABLES `log_civicrm_mailing_group` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing_job`
--

DROP TABLE IF EXISTS `log_civicrm_mailing_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing_job` (
  `id` int(10) unsigned NOT NULL,
  `mailing_id` int(10) unsigned NOT NULL COMMENT 'The ID of the mailing this Job will send.',
  `scheduled_date` datetime DEFAULT NULL COMMENT 'date on which this job was scheduled.',
  `start_date` datetime DEFAULT NULL COMMENT 'date on which this job was started.',
  `end_date` datetime DEFAULT NULL COMMENT 'date on which this job ended.',
  `status` enum('Scheduled','Running','Complete','Paused','Canceled') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The state of this job',
  `is_test` tinyint(4) DEFAULT '0' COMMENT 'Is this job for a test mail?',
  `job_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `job_offset` int(20) DEFAULT '0',
  `job_limit` int(20) DEFAULT '0',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing_job`
--

LOCK TABLES `log_civicrm_mailing_job` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing_job` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing_job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing_recipients`
--

DROP TABLE IF EXISTS `log_civicrm_mailing_recipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing_recipients` (
  `id` int(10) unsigned NOT NULL,
  `mailing_id` int(10) unsigned NOT NULL COMMENT 'The ID of the mailing this Job will send.',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact',
  `email_id` int(10) unsigned NOT NULL COMMENT 'FK to Email',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_id` int(10) unsigned DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing_recipients`
--

LOCK TABLES `log_civicrm_mailing_recipients` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing_recipients` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing_recipients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing_spool`
--

DROP TABLE IF EXISTS `log_civicrm_mailing_spool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing_spool` (
  `id` int(10) unsigned NOT NULL,
  `job_id` int(10) unsigned NOT NULL COMMENT 'The ID of the Job .',
  `recipient_email` text COLLATE utf8_unicode_ci COMMENT 'The email of the receipients this mail is to be sent.',
  `headers` text COLLATE utf8_unicode_ci COMMENT 'The header information of this mailing .',
  `body` text COLLATE utf8_unicode_ci COMMENT 'The body of this mailing.',
  `added_at` datetime DEFAULT NULL COMMENT 'date on which this job was added.',
  `removed_at` datetime DEFAULT NULL COMMENT 'date on which this job was removed.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing_spool`
--

LOCK TABLES `log_civicrm_mailing_spool` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing_spool` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing_spool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mailing_trackable_url`
--

DROP TABLE IF EXISTS `log_civicrm_mailing_trackable_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mailing_trackable_url` (
  `id` int(10) unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The URL to be tracked.',
  `mailing_id` int(10) unsigned NOT NULL COMMENT 'FK to the mailing',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mailing_trackable_url`
--

LOCK TABLES `log_civicrm_mailing_trackable_url` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mailing_trackable_url` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mailing_trackable_url` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_managed`
--

DROP TABLE IF EXISTS `log_civicrm_managed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_managed` (
  `id` int(10) unsigned NOT NULL COMMENT 'Surrogate Key',
  `module` varchar(127) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the module which declared this object',
  `name` varchar(127) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Symbolic name used by the module to identify the object',
  `entity_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'API entity type',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the referenced item.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_managed`
--

LOCK TABLES `log_civicrm_managed` WRITE;
/*!40000 ALTER TABLE `log_civicrm_managed` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_managed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mapping`
--

DROP TABLE IF EXISTS `log_civicrm_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mapping` (
  `id` int(10) unsigned NOT NULL COMMENT 'Mapping ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of Mapping',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Description of Mapping.',
  `mapping_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Mapping Type',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mapping`
--

LOCK TABLES `log_civicrm_mapping` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_mapping_field`
--

DROP TABLE IF EXISTS `log_civicrm_mapping_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_mapping_field` (
  `id` int(10) unsigned NOT NULL COMMENT 'Mapping Field ID',
  `mapping_id` int(10) unsigned NOT NULL COMMENT 'Mapping to which this field belongs',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Mapping field key',
  `contact_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Contact Type in mapping',
  `column_number` int(10) unsigned NOT NULL COMMENT 'Column number for mapping set',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Location type of this mapping, if required',
  `phone_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which type of phone does this number belongs.',
  `im_provider_id` int(10) unsigned DEFAULT NULL COMMENT 'Which type of IM Provider does this name belong.',
  `relationship_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Relationship type, if required',
  `relationship_direction` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grouping` int(10) unsigned DEFAULT '1' COMMENT 'Used to group mapping_field records into related sets (e.g. for criteria sets in search builder mappings).',
  `operator` enum('=','!=','>','<','>=','<=','IN','NOT IN','LIKE','NOT LIKE','IS NULL','IS NOT NULL') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'SQL WHERE operator for search-builder mapping fields (search criteria).',
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'SQL WHERE value for search-builder mapping fields.',
  `website_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which type of website does this site belong',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_mapping_field`
--

LOCK TABLES `log_civicrm_mapping_field` WRITE;
/*!40000 ALTER TABLE `log_civicrm_mapping_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_mapping_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_membership`
--

DROP TABLE IF EXISTS `log_civicrm_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_membership` (
  `id` int(10) unsigned NOT NULL COMMENT 'Membership Id',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID',
  `membership_type_id` int(10) unsigned NOT NULL COMMENT 'FK to Membership Type',
  `join_date` date DEFAULT NULL COMMENT 'Beginning of initial membership period (member since...).',
  `start_date` date DEFAULT NULL COMMENT 'Beginning of current uninterrupted membership period.',
  `end_date` date DEFAULT NULL COMMENT 'Current membership period expire date.',
  `source` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status_id` int(10) unsigned NOT NULL COMMENT 'FK to Membership Status',
  `is_override` tinyint(4) DEFAULT NULL COMMENT 'Admin users may set a manual status which overrides the calculated status. When this flag is true, automated status update scripts should NOT modify status for the record.',
  `reminder_date` date DEFAULT NULL COMMENT 'When should a reminder be sent.',
  `owner_membership_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional FK to Parent Membership.',
  `is_test` tinyint(4) DEFAULT '0',
  `is_pay_later` tinyint(4) DEFAULT '0',
  `contribution_recur_id` int(10) unsigned DEFAULT NULL COMMENT 'Conditional foreign key to civicrm_contribution_recur.id.',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this membership is attached.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_membership`
--

LOCK TABLES `log_civicrm_membership` WRITE;
/*!40000 ALTER TABLE `log_civicrm_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_membership_block`
--

DROP TABLE IF EXISTS `log_civicrm_membership_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_membership_block` (
  `id` int(10) unsigned NOT NULL COMMENT 'Membership Id',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name for Membership Status',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_contribution_page.id',
  `membership_types` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Membership types to be exposed by this block',
  `membership_type_default` int(10) unsigned DEFAULT NULL COMMENT 'Optional foreign key to membership_type',
  `display_min_fee` tinyint(4) DEFAULT '1' COMMENT 'Display minimum membership fee',
  `is_separate_payment` tinyint(4) DEFAULT '1' COMMENT 'Should membership transactions be processed separately',
  `new_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title to display at top of block',
  `new_text` text COLLATE utf8_unicode_ci COMMENT 'Text to display below title',
  `renewal_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for renewal',
  `renewal_text` text COLLATE utf8_unicode_ci COMMENT 'Text to display for member renewal',
  `is_required` tinyint(4) DEFAULT '0' COMMENT 'Is membership sign up optional',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this membership_block enabled',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_membership_block`
--

LOCK TABLES `log_civicrm_membership_block` WRITE;
/*!40000 ALTER TABLE `log_civicrm_membership_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_membership_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_membership_payment`
--

DROP TABLE IF EXISTS `log_civicrm_membership_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_membership_payment` (
  `id` int(10) unsigned NOT NULL,
  `membership_id` int(10) unsigned NOT NULL COMMENT 'FK to Membership table',
  `contribution_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contribution table.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_membership_payment`
--

LOCK TABLES `log_civicrm_membership_payment` WRITE;
/*!40000 ALTER TABLE `log_civicrm_membership_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_membership_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_membership_status`
--

DROP TABLE IF EXISTS `log_civicrm_membership_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_membership_status` (
  `id` int(10) unsigned NOT NULL COMMENT 'Membership Id',
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name for Membership Status',
  `start_event` enum('start_date','end_date','join_date') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Event when this status starts.',
  `start_event_adjust_unit` enum('day','month','year') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unit used for adjusting from start_event.',
  `start_event_adjust_interval` int(11) DEFAULT NULL COMMENT 'Status range begins this many units from start_event.',
  `end_event` enum('start_date','end_date','join_date') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Event after which this status ends.',
  `end_event_adjust_unit` enum('day','month','year') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unit used for adjusting from the ending event.',
  `end_event_adjust_interval` int(11) DEFAULT NULL COMMENT 'Status range ends this many units from end_event.',
  `is_current_member` tinyint(4) DEFAULT NULL COMMENT 'Does this status aggregate to current members (e.g. New, Renewed, Grace might all be TRUE... while Unrenewed, Lapsed, Inactive would be FALSE).',
  `is_admin` tinyint(4) DEFAULT NULL COMMENT 'Is this status for admin/manual assignment only.',
  `weight` int(11) DEFAULT NULL,
  `is_default` tinyint(4) DEFAULT NULL COMMENT 'Assign this status to a membership record if no other status match is found.',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this membership_status enabled.',
  `is_reserved` tinyint(4) DEFAULT '0' COMMENT 'Is this membership_status reserved.',
  `label` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Label for Membership Status',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_membership_status`
--

LOCK TABLES `log_civicrm_membership_status` WRITE;
/*!40000 ALTER TABLE `log_civicrm_membership_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_membership_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_membership_type`
--

DROP TABLE IF EXISTS `log_civicrm_membership_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_membership_type` (
  `id` int(10) unsigned NOT NULL COMMENT 'Membership Id',
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this match entry for',
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of Membership Type',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Description of Membership Type',
  `member_of_contact_id` int(10) unsigned NOT NULL COMMENT 'Owner organization for this membership type. FK to Contact ID',
  `contribution_type_id` int(10) unsigned NOT NULL COMMENT 'If membership is paid by a contribution - what contribution type should be used. FK to Contribution Type ID',
  `minimum_fee` decimal(20,2) DEFAULT '0.00' COMMENT 'Minimum fee for this membership (0 for free/complimentary memberships).',
  `duration_unit` enum('day','month','year','lifetime') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unit in which membership period is expressed.',
  `duration_interval` int(11) DEFAULT NULL COMMENT 'Number of duration units in membership period (e.g. 1 year, 12 months).',
  `period_type` enum('rolling','fixed') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Rolling membership period starts on signup date. Fixed membership periods start on fixed_period_start_day.',
  `fixed_period_start_day` int(11) DEFAULT NULL COMMENT 'For fixed period memberships, month and day (mmdd) on which subscription/membership will start. Period start is back-dated unless after rollover day.',
  `fixed_period_rollover_day` int(11) DEFAULT NULL COMMENT 'For fixed period memberships, signups after this day (mmdd) rollover to next period.',
  `relationship_type_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `relationship_direction` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `visibility` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `renewal_msg_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_msg_template.id',
  `renewal_reminder_day` int(11) DEFAULT NULL COMMENT 'Number of days prior to expiration to send renewal reminder',
  `receipt_text_signup` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Receipt Text for membership signup',
  `receipt_text_renewal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Receipt Text for membership renewal',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this membership_type enabled',
  `auto_renew` tinyint(4) DEFAULT '0',
  `autorenewal_msg_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_msg_template.id',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_membership_type`
--

LOCK TABLES `log_civicrm_membership_type` WRITE;
/*!40000 ALTER TABLE `log_civicrm_membership_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_membership_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_msg_template`
--

DROP TABLE IF EXISTS `log_civicrm_msg_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_msg_template` (
  `id` int(10) unsigned NOT NULL COMMENT 'Message Template ID',
  `msg_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Descriptive title of message',
  `msg_subject` text COLLATE utf8_unicode_ci COMMENT 'Subject for email message.',
  `msg_text` text COLLATE utf8_unicode_ci COMMENT 'Text formatted message',
  `msg_html` text COLLATE utf8_unicode_ci COMMENT 'HTML formatted message',
  `is_active` tinyint(4) DEFAULT '1',
  `workflow_id` int(10) unsigned DEFAULT NULL COMMENT 'a pseudo-FK to civicrm_option_value',
  `is_default` tinyint(4) DEFAULT '1' COMMENT 'is this the default message template for the workflow referenced by workflow_id?',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'is this the reserved message template which we ship for the workflow referenced by workflow_id?',
  `pdf_format_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value containing PDF Page Format.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_msg_template`
--

LOCK TABLES `log_civicrm_msg_template` WRITE;
/*!40000 ALTER TABLE `log_civicrm_msg_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_msg_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_navigation`
--

DROP TABLE IF EXISTS `log_civicrm_navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_navigation` (
  `id` int(10) unsigned NOT NULL,
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this navigation item for',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Navigation Title',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Internal Name',
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'url in case of custom navigation link',
  `permission` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Permission for menu item',
  `permission_operator` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Permission Operator',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Parent navigation item, used for grouping',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this navigation item active?',
  `has_separator` tinyint(4) DEFAULT NULL COMMENT 'If separator needs to be added after this menu item',
  `weight` int(11) DEFAULT NULL COMMENT 'Ordering of the navigation items in various blocks.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_navigation`
--

LOCK TABLES `log_civicrm_navigation` WRITE;
/*!40000 ALTER TABLE `log_civicrm_navigation` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_navigation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_note`
--

DROP TABLE IF EXISTS `log_civicrm_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_note` (
  `id` int(10) unsigned NOT NULL COMMENT 'Note ID',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of table where item being referenced is stored.',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the referenced item.',
  `note` text COLLATE utf8_unicode_ci COMMENT 'Note and/or Comment.',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID creator',
  `modified_date` date DEFAULT NULL COMMENT 'When was this note last modified/edited',
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'subject of note description',
  `privacy` int(10) NOT NULL COMMENT 'Foreign Key to Note Privacy Level (which is an option value pair and hence an implicit FK)',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `entity_id` (`entity_id`),
  KEY `entity_table` (`entity_table`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_note`
--

LOCK TABLES `log_civicrm_note` WRITE;
/*!40000 ALTER TABLE `log_civicrm_note` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_openid`
--

DROP TABLE IF EXISTS `log_civicrm_openid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_openid` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique OpenID ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Location does this email belong to.',
  `openid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'the OpenID (or OpenID-style http://username.domain/) unique identifier for this contact mainly used for logging in to CiviCRM',
  `allowed_to_login` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether or not this user is allowed to login',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary email for this contact and location.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_openid`
--

LOCK TABLES `log_civicrm_openid` WRITE;
/*!40000 ALTER TABLE `log_civicrm_openid` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_openid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_option_group`
--

DROP TABLE IF EXISTS `log_civicrm_option_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_option_group` (
  `id` int(10) unsigned NOT NULL COMMENT 'Option Group ID',
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Option group name. Used as selection key by class properties which lookup options in civicrm_option_value.',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Option label.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Option group description.',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this a predefined system option group (i.e. it can not be deleted)?',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this option group active?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_option_group`
--

LOCK TABLES `log_civicrm_option_group` WRITE;
/*!40000 ALTER TABLE `log_civicrm_option_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_option_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_option_value`
--

DROP TABLE IF EXISTS `log_civicrm_option_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_option_value` (
  `id` int(10) unsigned NOT NULL COMMENT 'Option ID',
  `option_group_id` int(10) unsigned NOT NULL COMMENT 'Group which this option belongs to.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Option string as displayed to users - e.g. the label in an HTML OPTION tag.',
  `value` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Stores a fixed (non-translated) name for this option value. Lookup functions should use the name as the key for the option value row.',
  `grouping` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Use to sort and/or set display properties for sub-set(s) of options within an option group. EXAMPLE: Use for college_interest field, to differentiate partners from non-partners.',
  `filter` int(10) unsigned DEFAULT NULL COMMENT 'Bitwise logic can be used to create subsets of options within an option_group for different uses.',
  `is_default` tinyint(4) DEFAULT '0' COMMENT 'Is this the default option for the group?',
  `weight` int(10) unsigned NOT NULL COMMENT 'Controls display sort order.',
  `description` text COLLATE utf8_unicode_ci,
  `is_optgroup` tinyint(4) DEFAULT '0' COMMENT 'Is this row simply a display header? Expected usage is to render these as OPTGROUP tags within a SELECT field list of options?',
  `is_reserved` tinyint(4) DEFAULT '0' COMMENT 'Is this a predefined system object?',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this option active?',
  `component_id` int(10) unsigned DEFAULT NULL COMMENT 'Component that this option value belongs/caters to.',
  `domain_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Domain is this option value for',
  `visibility_id` int(10) unsigned DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_option_value`
--

LOCK TABLES `log_civicrm_option_value` WRITE;
/*!40000 ALTER TABLE `log_civicrm_option_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_option_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_participant`
--

DROP TABLE IF EXISTS `log_civicrm_participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_participant` (
  `id` int(10) unsigned NOT NULL COMMENT 'Participant Id',
  `contact_id` int(10) unsigned DEFAULT '0' COMMENT 'FK to Contact ID',
  `event_id` int(10) unsigned DEFAULT '0' COMMENT 'FK to Event ID',
  `status_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Participant status ID. FK to civicrm_participant_status_type. Default of 1 should map to status = Registered.',
  `role_id` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Participant role ID. Implicit FK to civicrm_option_value where option_group = participant_role.',
  `register_date` datetime DEFAULT NULL COMMENT 'When did contact register for event?',
  `source` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Source of this event registration.',
  `fee_level` text COLLATE utf8_unicode_ci COMMENT 'Populate with the label (text) associated with a fee level for paid events with multiple levels. Note that we store the label value and not the key',
  `is_test` tinyint(4) DEFAULT '0',
  `is_pay_later` tinyint(4) DEFAULT '0',
  `fee_amount` decimal(20,2) DEFAULT NULL COMMENT 'actual processor fee if known - may be 0.',
  `registered_by_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Participant ID',
  `discount_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Discount ID',
  `fee_currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value derived from config setting.',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this participant has been registered.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `discount_amount` int(10) unsigned DEFAULT '0' COMMENT 'Discount Amount',
  `cart_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_event_carts',
  `must_wait` tinyint(4) DEFAULT '0' COMMENT 'On Waiting List'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_participant`
--

LOCK TABLES `log_civicrm_participant` WRITE;
/*!40000 ALTER TABLE `log_civicrm_participant` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_participant_payment`
--

DROP TABLE IF EXISTS `log_civicrm_participant_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_participant_payment` (
  `id` int(10) unsigned NOT NULL COMMENT 'Participant Payment Id',
  `participant_id` int(10) unsigned NOT NULL COMMENT 'Participant Id (FK)',
  `contribution_id` int(10) unsigned NOT NULL COMMENT 'FK to contribution table.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_participant_payment`
--

LOCK TABLES `log_civicrm_participant_payment` WRITE;
/*!40000 ALTER TABLE `log_civicrm_participant_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_participant_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_participant_status_type`
--

DROP TABLE IF EXISTS `log_civicrm_participant_status_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_participant_status_type` (
  `id` int(10) unsigned NOT NULL COMMENT 'unique participant status type id',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'non-localized name of the status type',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'localized label for display of this status type',
  `class` enum('Positive','Pending','Waiting','Negative') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'the general group of status type this one belongs to',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'whether this is a status type required by the system',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'whether this status type is active',
  `is_counted` tinyint(4) DEFAULT NULL COMMENT 'whether this status type is counted against event size limit',
  `weight` int(10) unsigned NOT NULL COMMENT 'controls sort order',
  `visibility_id` int(10) unsigned DEFAULT NULL COMMENT 'whether the status type is visible to the public, an implicit foreign key to option_value.value related to the `visibility` option_group',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_participant_status_type`
--

LOCK TABLES `log_civicrm_participant_status_type` WRITE;
/*!40000 ALTER TABLE `log_civicrm_participant_status_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_participant_status_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_payment_processor`
--

DROP TABLE IF EXISTS `log_civicrm_payment_processor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_payment_processor` (
  `id` int(10) unsigned NOT NULL COMMENT 'Payment Processor ID',
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this match entry for',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processor Name.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processor Description.',
  `payment_processor_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processor Type.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this processor active?',
  `is_default` tinyint(4) DEFAULT NULL COMMENT 'Is this processor the default?',
  `is_test` tinyint(4) DEFAULT NULL COMMENT 'Is this processor for a test site?',
  `user_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `signature` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_site` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_api` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_recur` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_button` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_mode` int(10) unsigned NOT NULL COMMENT 'Billing Mode',
  `is_recur` tinyint(4) DEFAULT NULL COMMENT 'Can process recurring contributions',
  `payment_type` int(10) unsigned DEFAULT '1' COMMENT 'Payment Type: Credit or Debit',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_payment_processor`
--

LOCK TABLES `log_civicrm_payment_processor` WRITE;
/*!40000 ALTER TABLE `log_civicrm_payment_processor` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_payment_processor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_payment_processor_type`
--

DROP TABLE IF EXISTS `log_civicrm_payment_processor_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_payment_processor_type` (
  `id` int(10) unsigned NOT NULL COMMENT 'Payment Processor Type ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processor Name.',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processor Name.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processor Description.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this processor active?',
  `is_default` tinyint(4) DEFAULT NULL COMMENT 'Is this processor the default?',
  `user_name_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `signature_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_site_default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_api_default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_recur_default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_button_default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_site_test_default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_api_test_default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_recur_test_default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_button_test_default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_mode` int(10) unsigned NOT NULL COMMENT 'Billing Mode',
  `is_recur` tinyint(4) DEFAULT NULL COMMENT 'Can process recurring contributions',
  `payment_type` int(10) unsigned DEFAULT '1' COMMENT 'Payment Type: Credit or Debit',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_payment_processor_type`
--

LOCK TABLES `log_civicrm_payment_processor_type` WRITE;
/*!40000 ALTER TABLE `log_civicrm_payment_processor_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_payment_processor_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_pcp`
--

DROP TABLE IF EXISTS `log_civicrm_pcp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_pcp` (
  `id` int(10) unsigned NOT NULL COMMENT 'Personal Campaign Page ID',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID',
  `status_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `intro_text` text COLLATE utf8_unicode_ci,
  `page_text` text COLLATE utf8_unicode_ci,
  `donate_link_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contribution_page_id` int(10) unsigned NOT NULL COMMENT 'The Contribution Page which triggered this pcp',
  `is_thermometer` int(10) unsigned DEFAULT '0',
  `is_honor_roll` int(10) unsigned DEFAULT '0',
  `goal_amount` decimal(20,2) DEFAULT NULL COMMENT 'Goal amount of this Personal Campaign Page.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `referer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT '0' COMMENT 'Is Personal Campaign Page enabled/active?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `page_id` int(10) unsigned NOT NULL COMMENT 'The Page which triggered this pcp',
  `page_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'contribute',
  `pcp_block_id` int(10) unsigned NOT NULL COMMENT 'The pcp block that this pcp page was created from'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_pcp`
--

LOCK TABLES `log_civicrm_pcp` WRITE;
/*!40000 ALTER TABLE `log_civicrm_pcp` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_pcp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_pcp_block`
--

DROP TABLE IF EXISTS `log_civicrm_pcp_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_pcp_block` (
  `id` int(10) unsigned NOT NULL COMMENT 'PCP block Id',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entity_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_contribution_page.id',
  `supporter_profile_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_uf_group.id. Does Personal Campaign Page require manual activation by administrator? (is inactive by default after setup)?',
  `is_approval_needed` tinyint(4) DEFAULT NULL COMMENT 'Does Personal Campaign Page require manual activation by administrator? (is inactive by default after setup)?',
  `is_tellfriend_enabled` tinyint(4) DEFAULT NULL COMMENT 'Does Personal Campaign Page allow using tell a friend?',
  `tellfriend_limit` int(10) unsigned DEFAULT NULL COMMENT 'Maximum recipient fields allowed in tell a friend',
  `link_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Link text for PCP.',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is Personal Campaign Page Block enabled/active?',
  `notify_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If set, notification is automatically emailed to this email-address on create/update Personal Campaign Page',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_entity_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'contribute',
  `target_entity_id` int(10) unsigned NOT NULL COMMENT 'The entity that this pcp targets'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_pcp_block`
--

LOCK TABLES `log_civicrm_pcp_block` WRITE;
/*!40000 ALTER TABLE `log_civicrm_pcp_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_pcp_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_persistent`
--

DROP TABLE IF EXISTS `log_civicrm_persistent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_persistent` (
  `id` int(10) unsigned NOT NULL COMMENT 'Persistent Record Id',
  `context` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Context for which name data pair is to be stored',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of Context',
  `data` longtext COLLATE utf8_unicode_ci COMMENT 'data associated with name',
  `is_config` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Config Settings',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_persistent`
--

LOCK TABLES `log_civicrm_persistent` WRITE;
/*!40000 ALTER TABLE `log_civicrm_persistent` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_persistent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_phone`
--

DROP TABLE IF EXISTS `log_civicrm_phone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_phone` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Phone ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Location does this phone belong to.',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary phone for this contact and location.',
  `is_billing` tinyint(4) DEFAULT '0' COMMENT 'Is this the billing?',
  `mobile_provider_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Mobile Provider does this phone belong to.',
  `phone` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Complete phone number.',
  `phone_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which type of phone does this number belongs.',
  `phone_ext` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `contact_id` (`contact_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_phone`
--

LOCK TABLES `log_civicrm_phone` WRITE;
/*!40000 ALTER TABLE `log_civicrm_phone` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_phone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_pledge`
--

DROP TABLE IF EXISTS `log_civicrm_pledge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_pledge` (
  `id` int(10) unsigned NOT NULL COMMENT 'Pledge ID',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to civicrm_contact.id .',
  `contribution_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contribution Type. This is propagated to contribution record when pledge payments are made.',
  `contribution_page_id` int(10) unsigned DEFAULT NULL COMMENT 'The Contribution Page which triggered this contribution',
  `amount` decimal(20,2) NOT NULL COMMENT 'Total pledged amount.',
  `original_installment_amount` decimal(20,2) NOT NULL COMMENT 'Original amount for each of the installments.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `frequency_unit` enum('day','week','month','year') COLLATE utf8_unicode_ci DEFAULT 'month' COMMENT 'Time units for recurrence of pledge payments.',
  `frequency_interval` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Number of time units for recurrence of pledge payments.',
  `frequency_day` int(10) unsigned NOT NULL DEFAULT '3' COMMENT 'Day in the period when the pledge payment is due e.g. 1st of month, 15th etc. Use this to set the scheduled dates for pledge payments.',
  `installments` int(10) unsigned DEFAULT '1' COMMENT 'Total number of payments to be made.',
  `start_date` datetime NOT NULL COMMENT 'The date the first scheduled pledge occurs.',
  `create_date` datetime NOT NULL COMMENT 'When this pledge record was created.',
  `acknowledge_date` datetime DEFAULT NULL COMMENT 'When a pledge acknowledgement message was sent to the contributor.',
  `modified_date` datetime DEFAULT NULL COMMENT 'Last updated date for this pledge record.',
  `cancel_date` datetime DEFAULT NULL COMMENT 'Date this pledge was cancelled by contributor.',
  `end_date` datetime DEFAULT NULL COMMENT 'Date this pledge finished successfully (total pledge payments equal to or greater than pledged amount).',
  `honor_contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contact ID. Used when pledge is made in honor of another contact. This is propagated to contribution records when pledge payments are made.',
  `honor_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Implicit FK to civicrm_option_value.',
  `max_reminders` int(10) unsigned DEFAULT '1' COMMENT 'The maximum number of payment reminders to send for any given payment.',
  `initial_reminder_day` int(10) unsigned DEFAULT '5' COMMENT 'Send initial reminder this many days prior to the payment due date.',
  `additional_reminder_day` int(10) unsigned DEFAULT '5' COMMENT 'Send additional reminder this many days after last one sent, up to maximum number of reminders.',
  `status_id` int(10) unsigned DEFAULT NULL COMMENT 'Implicit foreign key to civicrm_option_values in the contribution_status option group.',
  `is_test` tinyint(4) DEFAULT '0',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this pledge has been initiated.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_pledge`
--

LOCK TABLES `log_civicrm_pledge` WRITE;
/*!40000 ALTER TABLE `log_civicrm_pledge` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_pledge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_pledge_block`
--

DROP TABLE IF EXISTS `log_civicrm_pledge_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_pledge_block` (
  `id` int(10) unsigned NOT NULL COMMENT 'Pledge ID',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'physical tablename for entity being joined to pledge, e.g. civicrm_contact',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'FK to entity table specified in entity_table column.',
  `pledge_frequency_unit` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Delimited list of supported frequency units',
  `is_pledge_interval` tinyint(4) DEFAULT '0' COMMENT 'Is frequency interval exposed on the contribution form.',
  `max_reminders` int(10) unsigned DEFAULT '1' COMMENT 'The maximum number of payment reminders to send for any given payment.',
  `initial_reminder_day` int(10) unsigned DEFAULT '5' COMMENT 'Send initial reminder this many days prior to the payment due date.',
  `additional_reminder_day` int(10) unsigned DEFAULT '5' COMMENT 'Send additional reminder this many days after last one sent, up to maximum number of reminders.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_pledge_block`
--

LOCK TABLES `log_civicrm_pledge_block` WRITE;
/*!40000 ALTER TABLE `log_civicrm_pledge_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_pledge_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_pledge_payment`
--

DROP TABLE IF EXISTS `log_civicrm_pledge_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_pledge_payment` (
  `id` int(10) unsigned NOT NULL,
  `pledge_id` int(10) unsigned NOT NULL COMMENT 'FK to Pledge table',
  `contribution_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contribution table.',
  `scheduled_amount` decimal(20,2) NOT NULL COMMENT 'Pledged amount for this payment (the actual contribution amount might be different).',
  `actual_amount` decimal(20,2) DEFAULT NULL COMMENT 'Actual amount that is paid as the Pledged installment amount.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `scheduled_date` datetime NOT NULL COMMENT 'The date the pledge payment is supposed to happen.',
  `reminder_date` datetime DEFAULT NULL COMMENT 'The date that the most recent payment reminder was sent.',
  `reminder_count` int(10) unsigned DEFAULT '0' COMMENT 'The number of payment reminders sent.',
  `status_id` int(10) unsigned DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_pledge_payment`
--

LOCK TABLES `log_civicrm_pledge_payment` WRITE;
/*!40000 ALTER TABLE `log_civicrm_pledge_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_pledge_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_preferences`
--

DROP TABLE IF EXISTS `log_civicrm_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_preferences` (
  `id` int(10) unsigned NOT NULL,
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this menu item for',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `is_domain` tinyint(4) DEFAULT NULL COMMENT 'Is this the record for the domain setting?',
  `contact_view_options` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What tabs are displayed in the contact summary',
  `contact_edit_options` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What tabs are displayed in the contact edit',
  `advanced_search_options` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What tabs are displayed in the advanced search screen',
  `user_dashboard_options` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What tabs are displayed in the contact edit',
  `address_options` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What fields are displayed from the address table',
  `address_format` text COLLATE utf8_unicode_ci COMMENT 'Format to display the address',
  `mailing_format` text COLLATE utf8_unicode_ci COMMENT 'Format to display a mailing label',
  `display_name_format` text COLLATE utf8_unicode_ci COMMENT 'Format to display contact display name',
  `sort_name_format` text COLLATE utf8_unicode_ci COMMENT 'Format to display contact sort name',
  `address_standardization_provider` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'object name of provider for address standarization',
  `address_standardization_userid` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'user id for provider login',
  `address_standardization_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'url of address standardization service',
  `editor_id` int(10) unsigned DEFAULT NULL COMMENT 'ID of the editor',
  `mailing_backend` text COLLATE utf8_unicode_ci COMMENT 'Smtp Backend configuration.',
  `navigation` text COLLATE utf8_unicode_ci COMMENT 'Store navigation for the Contact',
  `contact_autocomplete_options` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What Autocomplete has to return',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_preferences`
--

LOCK TABLES `log_civicrm_preferences` WRITE;
/*!40000 ALTER TABLE `log_civicrm_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_preferences_date`
--

DROP TABLE IF EXISTS `log_civicrm_preferences_date`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_preferences_date` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The meta name for this date (fixed in code)',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Description of this date type.',
  `start` int(11) NOT NULL COMMENT 'The start offset relative to current year',
  `end` int(11) NOT NULL COMMENT 'The end offset relative to current year, can be negative',
  `date_format` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The date type',
  `time_format` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'time format',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_preferences_date`
--

LOCK TABLES `log_civicrm_preferences_date` WRITE;
/*!40000 ALTER TABLE `log_civicrm_preferences_date` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_preferences_date` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_premiums`
--

DROP TABLE IF EXISTS `log_civicrm_premiums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_premiums` (
  `id` int(10) unsigned NOT NULL,
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Joins these premium settings to another object. Always civicrm_contribution_page for now.',
  `entity_id` int(10) unsigned NOT NULL,
  `premiums_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Is the Premiums feature enabled for this page?',
  `premiums_intro_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for Premiums section.',
  `premiums_intro_text` text COLLATE utf8_unicode_ci COMMENT 'Displayed in <div> at top of Premiums section of page. Text and HTML allowed.',
  `premiums_contact_email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'This email address is included in receipts if it is populated and a premium has been selected.',
  `premiums_contact_phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'This phone number is included in receipts if it is populated and a premium has been selected.',
  `premiums_display_min_contribution` tinyint(4) NOT NULL COMMENT 'Boolean. Should we automatically display minimum contribution amount text after the premium descriptions.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_premiums`
--

LOCK TABLES `log_civicrm_premiums` WRITE;
/*!40000 ALTER TABLE `log_civicrm_premiums` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_premiums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_premiums_product`
--

DROP TABLE IF EXISTS `log_civicrm_premiums_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_premiums_product` (
  `id` int(10) unsigned NOT NULL COMMENT 'Contribution ID',
  `premiums_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to premiums settings record.',
  `product_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to each product object.',
  `weight` int(10) unsigned NOT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_premiums_product`
--

LOCK TABLES `log_civicrm_premiums_product` WRITE;
/*!40000 ALTER TABLE `log_civicrm_premiums_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_premiums_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_price_field`
--

DROP TABLE IF EXISTS `log_civicrm_price_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_price_field` (
  `id` int(10) unsigned NOT NULL COMMENT 'Price Field',
  `price_set_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_price_set',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Variable name/programmatic handle for this field.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text for form field label (also friendly name for administering this field).',
  `html_type` enum('Text','Select','Radio','CheckBox') COLLATE utf8_unicode_ci NOT NULL,
  `is_enter_qty` tinyint(4) DEFAULT '0' COMMENT 'Enter a quantity for this field?',
  `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display before this field.',
  `help_post` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display after this field.',
  `weight` int(11) DEFAULT '1' COMMENT 'Order in which the fields should appear',
  `is_display_amounts` tinyint(4) DEFAULT '1' COMMENT 'Should the price be displayed next to the label for each option?',
  `options_per_line` int(10) unsigned DEFAULT '1' COMMENT 'number of options per line for checkbox and radio',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this price field active',
  `is_required` tinyint(4) DEFAULT '1' COMMENT 'Is this price field required (value must be > 1)',
  `active_on` datetime DEFAULT NULL COMMENT 'If non-zero, do not show this field before the date specified',
  `expire_on` datetime DEFAULT NULL COMMENT 'If non-zero, do not show this field after the date specified',
  `javascript` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional scripting attributes for field',
  `visibility_id` int(10) unsigned DEFAULT '1' COMMENT 'Implicit FK to civicrm_option_group with name = ''visibility''',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_price_field`
--

LOCK TABLES `log_civicrm_price_field` WRITE;
/*!40000 ALTER TABLE `log_civicrm_price_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_price_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_price_field_value`
--

DROP TABLE IF EXISTS `log_civicrm_price_field_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_price_field_value` (
  `id` int(10) unsigned NOT NULL COMMENT 'Price Field Value',
  `price_field_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_price_field',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Price field option name',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Price field option label',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Price field option description.',
  `amount` varchar(512) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Price field option amount',
  `count` int(10) unsigned DEFAULT NULL COMMENT 'Number of participants per field option',
  `max_value` int(10) unsigned DEFAULT NULL COMMENT 'Max number of participants per field options',
  `weight` int(11) DEFAULT '1' COMMENT 'Order in which the field options should appear',
  `is_default` tinyint(4) DEFAULT '0' COMMENT 'Is this default price field option',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this price field option active',
  `membership_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Conditional foreign key to civicrm_membership_type.id.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_price_field_value`
--

LOCK TABLES `log_civicrm_price_field_value` WRITE;
/*!40000 ALTER TABLE `log_civicrm_price_field_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_price_field_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_price_set`
--

DROP TABLE IF EXISTS `log_civicrm_price_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_price_set` (
  `id` int(10) unsigned NOT NULL COMMENT 'Price Set',
  `domain_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Domain is this price-set for',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Variable name/programmatic handle for this set of price fields.',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Displayed title for the Price Set.',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this price set active',
  `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display before fields in form.',
  `help_post` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display after fields in form.',
  `javascript` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional Javascript script function(s) included on the form with this price_set. Can be used for conditional',
  `extends` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'What components are using this price set?',
  `contribution_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Conditional foreign key to civicrm_contribution_type.id.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_quick_config` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Is set if edited on Contribution or Event Page rather than through Manage Price Sets',
  `is_reserved` tinyint(4) DEFAULT '0' COMMENT 'Is this a predefined system price set  (i.e. it can not be deleted, edited)?'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_price_set`
--

LOCK TABLES `log_civicrm_price_set` WRITE;
/*!40000 ALTER TABLE `log_civicrm_price_set` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_price_set` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_price_set_entity`
--

DROP TABLE IF EXISTS `log_civicrm_price_set_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_price_set_entity` (
  `id` int(10) unsigned NOT NULL COMMENT 'Price Set Entity',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Table which uses this price set',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Item in table',
  `price_set_id` int(10) unsigned NOT NULL COMMENT 'price set being used',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_price_set_entity`
--

LOCK TABLES `log_civicrm_price_set_entity` WRITE;
/*!40000 ALTER TABLE `log_civicrm_price_set_entity` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_price_set_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_product`
--

DROP TABLE IF EXISTS `log_civicrm_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_product` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Required product/premium name',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Optional description of the product/premium.',
  `sku` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional product sku or code.',
  `options` text COLLATE utf8_unicode_ci COMMENT 'Store comma-delimited list of color, size, etc. options for the product.',
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Full or relative URL to uploaded image - fullsize.',
  `thumbnail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Full or relative URL to image thumbnail.',
  `price` decimal(20,2) DEFAULT NULL COMMENT 'Sell price or market value for premiums. For tax-deductible contributions, this will be stored as non_deductible_amount in the contribution record.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `min_contribution` decimal(20,2) DEFAULT NULL COMMENT 'Minimum contribution required to be eligible to select this premium.',
  `cost` decimal(20,2) DEFAULT NULL COMMENT 'Actual cost of this product. Useful to determine net return from sale or using this as an incentive.',
  `is_active` tinyint(4) NOT NULL COMMENT 'Disabling premium removes it from the premiums_premium join table below.',
  `period_type` enum('rolling','fixed') COLLATE utf8_unicode_ci DEFAULT 'rolling' COMMENT 'Rolling means we set start/end based on current day, fixed means we set start/end for current year or month\n(e.g. 1 year + fixed -> we would set start/end for 1/1/06 thru 12/31/06 for any premium chosen in 2006) ',
  `fixed_period_start_day` int(11) DEFAULT '101' COMMENT 'Month and day (MMDD) that fixed period type subscription or membership starts.',
  `duration_unit` enum('day','month','week','year') COLLATE utf8_unicode_ci DEFAULT 'year',
  `duration_interval` int(11) DEFAULT NULL COMMENT 'Number of units for total duration of subscription, service, membership (e.g. 12 Months).',
  `frequency_unit` enum('day','month','week','year') COLLATE utf8_unicode_ci DEFAULT 'month' COMMENT 'Frequency unit and interval allow option to store actual delivery frequency for a subscription or service.',
  `frequency_interval` int(11) DEFAULT NULL COMMENT 'Number of units for delivery frequency of subscription, service, membership (e.g. every 3 Months).',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_product`
--

LOCK TABLES `log_civicrm_product` WRITE;
/*!40000 ALTER TABLE `log_civicrm_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_project`
--

DROP TABLE IF EXISTS `log_civicrm_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_project` (
  `id` int(10) unsigned NOT NULL COMMENT 'Project ID',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Project name.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Optional verbose description of the project. May be used for display - HTML allowed.',
  `logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Full or relative URL to optional uploaded logo image for project.',
  `owner_entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of table where project owner being referenced is stored (e.g. civicrm_contact or civicrm_group).',
  `owner_entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to project owner (contact, group, etc.).',
  `start_date` datetime DEFAULT NULL COMMENT 'Project start date.',
  `end_date` datetime DEFAULT NULL COMMENT 'Project end date.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this record active? For Projects: can tasks be created for it, does it appear on project listings, etc.',
  `status_id` int(10) unsigned DEFAULT NULL COMMENT 'Configurable status value (e.g. Planned, Active, Closed...). FK to civicrm_option_value.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_project`
--

LOCK TABLES `log_civicrm_project` WRITE;
/*!40000 ALTER TABLE `log_civicrm_project` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_relationship`
--

DROP TABLE IF EXISTS `log_civicrm_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_relationship` (
  `id` int(10) unsigned NOT NULL COMMENT 'Relationship ID',
  `contact_id_a` int(10) unsigned NOT NULL COMMENT 'id of the first contact',
  `contact_id_b` int(10) unsigned NOT NULL COMMENT 'id of the second contact',
  `relationship_type_id` int(10) unsigned NOT NULL COMMENT 'id of the relationship',
  `start_date` date DEFAULT NULL COMMENT 'date when the relationship started',
  `end_date` date DEFAULT NULL COMMENT 'date when the relationship ended',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'is the relationship active ?',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional verbose description for the relationship.',
  `is_permission_a_b` tinyint(4) DEFAULT '0' COMMENT 'is contact a has permission to view / edit contact and\n  related data for contact b ?',
  `is_permission_b_a` tinyint(4) DEFAULT '0' COMMENT 'is contact b has permission to view / edit contact and\n  related data for contact a ?',
  `case_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_case',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `contact_id_a` (`contact_id_a`),
  KEY `contact_id_b` (`contact_id_b`),
  KEY `relationship_type_id` (`relationship_type_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_relationship`
--

LOCK TABLES `log_civicrm_relationship` WRITE;
/*!40000 ALTER TABLE `log_civicrm_relationship` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_relationship_type`
--

DROP TABLE IF EXISTS `log_civicrm_relationship_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_relationship_type` (
  `id` int(10) unsigned NOT NULL COMMENT 'Primary key',
  `name_a_b` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'name for relationship of contact_a to contact_b.',
  `label_a_b` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'label for relationship of contact_a to contact_b.',
  `name_b_a` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional name for relationship of contact_b to contact_a.',
  `label_b_a` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional label for relationship of contact_b to contact_a.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional verbose description of the relationship type.',
  `contact_type_a` enum('Individual','Organization','Household') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If defined, contact_a in a relationship of this type must be a specific contact_type.',
  `contact_type_b` enum('Individual','Organization','Household') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If defined, contact_b in a relationship of this type must be a specific contact_type.',
  `contact_sub_type_a` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If defined, contact_sub_type_a in a relationship of this type must be a specific contact_sub_type.',
  `contact_sub_type_b` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If defined, contact_sub_type_b in a relationship of this type must be a specific contact_sub_type.',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this relationship type a predefined system type (can not be changed or de-activated)?',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this relationship type currently active (i.e. can be used when creating or editing relationships)?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_relationship_type`
--

LOCK TABLES `log_civicrm_relationship_type` WRITE;
/*!40000 ALTER TABLE `log_civicrm_relationship_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_relationship_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_report_instance`
--

DROP TABLE IF EXISTS `log_civicrm_report_instance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_report_instance` (
  `id` int(10) unsigned NOT NULL COMMENT 'Report Instance ID',
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this instance for',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Report Instance Title.',
  `report_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'FK to civicrm_option_value for the report template',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Report Instance description.',
  `permission` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'permission required to be able to run this instance',
  `grouprole` varchar(1020) COLLATE utf8_unicode_ci DEFAULT NULL,
  `form_values` text COLLATE utf8_unicode_ci COMMENT 'Submitted form values for this report',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this entry active?',
  `email_subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Subject of email',
  `email_to` text COLLATE utf8_unicode_ci COMMENT 'comma-separated list of email addresses to send the report to',
  `email_cc` text COLLATE utf8_unicode_ci COMMENT 'comma-separated list of email addresses to send the report to',
  `header` text COLLATE utf8_unicode_ci COMMENT 'comma-separated list of email addresses to send the report to',
  `footer` text COLLATE utf8_unicode_ci COMMENT 'comma-separated list of email addresses to send the report to',
  `navigation_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to navigation ID',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_reserved` tinyint(4) DEFAULT '0'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_report_instance`
--

LOCK TABLES `log_civicrm_report_instance` WRITE;
/*!40000 ALTER TABLE `log_civicrm_report_instance` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_report_instance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_saved_search`
--

DROP TABLE IF EXISTS `log_civicrm_saved_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_saved_search` (
  `id` int(10) unsigned NOT NULL COMMENT 'Saved search ID',
  `form_values` text COLLATE utf8_unicode_ci COMMENT 'Submitted form values for this search',
  `mapping_id` int(10) unsigned DEFAULT NULL COMMENT 'Foreign key to civicrm_mapping used for saved search-builder searches.',
  `search_custom_id` int(10) unsigned DEFAULT NULL COMMENT 'Foreign key to civicrm_option value table used for saved custom searches.',
  `where_clause` text COLLATE utf8_unicode_ci COMMENT 'the sql where clause if a saved search acl',
  `select_tables` text COLLATE utf8_unicode_ci COMMENT 'the tables to be included in a select data',
  `where_tables` text COLLATE utf8_unicode_ci COMMENT 'the tables to be included in the count statement',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_saved_search`
--

LOCK TABLES `log_civicrm_saved_search` WRITE;
/*!40000 ALTER TABLE `log_civicrm_saved_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_saved_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_setting`
--

DROP TABLE IF EXISTS `log_civicrm_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_setting` (
  `id` int(10) unsigned NOT NULL,
  `group_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'group name for setting element, useful in caching setting elements',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unique name for setting',
  `value` text COLLATE utf8_unicode_ci COMMENT 'data associated with this group / name combo',
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this menu item for',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID if the setting is localized to a contact',
  `is_domain` tinyint(4) DEFAULT NULL COMMENT 'Is this setting a contact specific or site wide setting?',
  `component_id` int(10) unsigned DEFAULT NULL COMMENT 'Component that this menu item belongs to',
  `created_date` datetime DEFAULT NULL COMMENT 'When was the setting created',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this setting',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_setting`
--

LOCK TABLES `log_civicrm_setting` WRITE;
/*!40000 ALTER TABLE `log_civicrm_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_sms_provider`
--

DROP TABLE IF EXISTS `log_civicrm_sms_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_sms_provider` (
  `id` int(10) unsigned NOT NULL COMMENT 'SMS Provider ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Provider internal name points to option_value of option_group sms_provider_name',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Provider name visible to user',
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_type` int(10) unsigned NOT NULL COMMENT 'points to value in civicrm_option_value for group sms_api_type',
  `api_url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_params` text COLLATE utf8_unicode_ci COMMENT 'the api params in xml, http or smtp format',
  `is_default` tinyint(4) DEFAULT '0',
  `is_active` tinyint(4) DEFAULT '0',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_sms_provider`
--

LOCK TABLES `log_civicrm_sms_provider` WRITE;
/*!40000 ALTER TABLE `log_civicrm_sms_provider` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_sms_provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_state_province`
--

DROP TABLE IF EXISTS `log_civicrm_state_province`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_state_province` (
  `id` int(10) unsigned NOT NULL COMMENT 'State / Province ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of State / Province',
  `abbreviation` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '2-4 Character Abbreviation of State / Province',
  `country_id` int(10) unsigned NOT NULL COMMENT 'ID of Country that State / Province belong',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_state_province`
--

LOCK TABLES `log_civicrm_state_province` WRITE;
/*!40000 ALTER TABLE `log_civicrm_state_province` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_state_province` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_subscription_history`
--

DROP TABLE IF EXISTS `log_civicrm_subscription_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_subscription_history` (
  `id` int(10) unsigned NOT NULL COMMENT 'Internal Id',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Contact Id',
  `group_id` int(10) unsigned DEFAULT NULL COMMENT 'Group Id',
  `date` datetime NOT NULL COMMENT 'Date of the (un)subscription',
  `method` enum('Admin','Email','Web','API') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'How the (un)subscription was triggered',
  `status` enum('Added','Removed','Pending') COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The state of the contact within the group',
  `tracking` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'IP address or other tracking info',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_subscription_history`
--

LOCK TABLES `log_civicrm_subscription_history` WRITE;
/*!40000 ALTER TABLE `log_civicrm_subscription_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_subscription_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_survey`
--

DROP TABLE IF EXISTS `log_civicrm_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_survey` (
  `id` int(10) unsigned NOT NULL COMMENT 'Campaign Group id.',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Title of the Survey.',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'Foreign key to the activity Campaign.',
  `activity_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Implicit FK to civicrm_option_value where option_group = activity_type',
  `recontact_interval` text COLLATE utf8_unicode_ci COMMENT 'Recontact intervals for each status.',
  `instructions` text COLLATE utf8_unicode_ci COMMENT 'Script instructions for volunteers to use for the survey.',
  `release_frequency` int(10) unsigned DEFAULT NULL COMMENT 'Number of days for recurrence of release.',
  `max_number_of_contacts` int(10) unsigned DEFAULT NULL COMMENT 'Maximum number of contacts to allow for survey.',
  `default_number_of_contacts` int(10) unsigned DEFAULT NULL COMMENT 'Default number of contacts to allow for survey.',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Is this survey enabled or disabled/cancelled?',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is this default survey?',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this Survey.',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time that Survey was created.',
  `last_modified_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who recently edited this Survey.',
  `last_modified_date` datetime DEFAULT NULL COMMENT 'Date and time that Survey was edited last time.',
  `result_id` int(10) unsigned DEFAULT NULL COMMENT 'Used to store option group id.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `thankyou_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for Thank-you page (header title tag, and display at the top of the page).',
  `thankyou_text` text COLLATE utf8_unicode_ci COMMENT 'text and html allowed. displayed above result on success page',
  `bypass_confirm` tinyint(4) DEFAULT '0' COMMENT 'Used to store option group id.'
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_survey`
--

LOCK TABLES `log_civicrm_survey` WRITE;
/*!40000 ALTER TABLE `log_civicrm_survey` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_survey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_tag`
--

DROP TABLE IF EXISTS `log_civicrm_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_tag` (
  `id` int(10) unsigned NOT NULL COMMENT 'Tag ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of Tag.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional verbose description of the tag.',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional parent id for this tag.',
  `is_selectable` tinyint(4) DEFAULT '1' COMMENT 'Is this tag selectable / displayed',
  `is_reserved` tinyint(4) DEFAULT '0',
  `is_tagset` tinyint(4) DEFAULT '0',
  `used_for` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_id` int(10) unsigned DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_tag`
--

LOCK TABLES `log_civicrm_tag` WRITE;
/*!40000 ALTER TABLE `log_civicrm_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_task`
--

DROP TABLE IF EXISTS `log_civicrm_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_task` (
  `id` int(10) unsigned NOT NULL COMMENT 'Task ID',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Task name.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional verbose description of the Task. May be used for display - HTML allowed.',
  `task_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Configurable task type values (e.g. App Submit, App Review...). FK to civicrm_option_value.',
  `owner_entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of table where Task owner being referenced is stored (e.g. civicrm_contact or civicrm_group).',
  `owner_entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to Task owner (contact, group, etc.).',
  `parent_entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of table where optional Task parent is stored (e.g. civicrm_project, or civicrm_task for sub-tasks).',
  `parent_entity_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional foreign key to Task Parent (project, another task, etc.).',
  `due_date` datetime DEFAULT NULL COMMENT 'Task due date.',
  `priority_id` int(10) unsigned DEFAULT NULL COMMENT 'Configurable priority value (e.g. Critical, High, Medium...). FK to civicrm_option_value.',
  `task_class` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional key to a process class related to this task (e.g. CRM_Quest_PreApp).',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this record active? For tasks: can it be assigned, does it appear on open task listings, etc.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_task`
--

LOCK TABLES `log_civicrm_task` WRITE;
/*!40000 ALTER TABLE `log_civicrm_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_task_status`
--

DROP TABLE IF EXISTS `log_civicrm_task_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_task_status` (
  `id` int(10) unsigned NOT NULL COMMENT 'Task ID',
  `task_id` int(10) unsigned NOT NULL COMMENT 'Status is for which task.',
  `responsible_entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Entity responsible for this task_status instance (table where entity is stored e.g. civicrm_contact or civicrm_group).',
  `responsible_entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to responsible entity (contact, group, etc.).',
  `target_entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Optional target entity for this task_status instance, i.e. review this membership application-prospect member contact record is target (table where entity is stored e.g. civicrm_contact or civicrm_group).',
  `target_entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to target entity (contact, group, etc.).',
  `status_detail` text COLLATE utf8_unicode_ci COMMENT 'Encoded array of status details used for programmatic progress reporting and tracking.',
  `status_id` int(10) unsigned DEFAULT NULL COMMENT 'Configurable status value (e.g. Not Started, In Progress, Completed, Deferred...). FK to civicrm_option_value.',
  `create_date` datetime DEFAULT NULL COMMENT 'Date this record was created (date work on task started).',
  `modified_date` datetime DEFAULT NULL COMMENT 'Date-time of last update to this task_status record.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_task_status`
--

LOCK TABLES `log_civicrm_task_status` WRITE;
/*!40000 ALTER TABLE `log_civicrm_task_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_task_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_tell_friend`
--

DROP TABLE IF EXISTS `log_civicrm_tell_friend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_tell_friend` (
  `id` int(10) unsigned NOT NULL COMMENT 'Friend ID',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of table where item being referenced is stored.',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the referenced item.',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `intro` text COLLATE utf8_unicode_ci COMMENT 'Introductory message to contributor or participant displayed on the Tell a Friend form.',
  `suggested_message` text COLLATE utf8_unicode_ci COMMENT 'Suggested message to friends, provided as default on the Tell A Friend form.',
  `general_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'URL for general info about the organization - included in the email sent to friends.',
  `thankyou_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Text for Tell a Friend thank you page header and HTML title.',
  `thankyou_text` text COLLATE utf8_unicode_ci COMMENT 'Thank you message displayed on success page.',
  `is_active` tinyint(4) DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_tell_friend`
--

LOCK TABLES `log_civicrm_tell_friend` WRITE;
/*!40000 ALTER TABLE `log_civicrm_tell_friend` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_tell_friend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_timezone`
--

DROP TABLE IF EXISTS `log_civicrm_timezone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_timezone` (
  `id` int(10) unsigned NOT NULL COMMENT 'Timezone Id',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Timezone full name',
  `abbreviation` char(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ISO Code for timezone abbreviation',
  `gmt` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'GMT name of the timezone',
  `offset` int(11) DEFAULT NULL,
  `country_id` int(10) unsigned NOT NULL COMMENT 'Country Id',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_timezone`
--

LOCK TABLES `log_civicrm_timezone` WRITE;
/*!40000 ALTER TABLE `log_civicrm_timezone` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_timezone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_uf_field`
--

DROP TABLE IF EXISTS `log_civicrm_uf_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_uf_field` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique table ID',
  `uf_group_id` int(10) unsigned NOT NULL COMMENT 'Which form does this field belong to.',
  `field_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name for CiviCRM field which is being exposed for sharing.',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this field currently shareable? If false, hide the field for all sharing contexts.',
  `is_view` tinyint(4) DEFAULT '0' COMMENT 'the field is view only and not editable in user forms.',
  `is_required` tinyint(4) DEFAULT '0' COMMENT 'Is this field required when included in a user or registration form?',
  `weight` int(11) NOT NULL DEFAULT '1' COMMENT 'Controls field display order when user framework fields are displayed in registration and account editing forms.',
  `help_post` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display after this field.',
  `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display before this field.',
  `visibility` enum('User and User Admin Only','Public Pages','Public Pages and Listings') COLLATE utf8_unicode_ci DEFAULT 'User and User Admin Only' COMMENT 'In what context(s) is this field visible.',
  `in_selector` tinyint(4) DEFAULT '0' COMMENT 'Is this field included as a column in the selector table?',
  `is_searchable` tinyint(4) DEFAULT '0' COMMENT 'Is this field included search form of profile?',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Location type of this mapping, if required',
  `phone_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Phone Type Id, if required',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'To save label for fields.',
  `field_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'This field saves field type (ie individual,household.. field etc).',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this field reserved for use by some other CiviCRM functionality?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_uf_field`
--

LOCK TABLES `log_civicrm_uf_field` WRITE;
/*!40000 ALTER TABLE `log_civicrm_uf_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_uf_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_uf_group`
--

DROP TABLE IF EXISTS `log_civicrm_uf_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_uf_group` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique table ID',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this form currently active? If false, hide all related fields for all sharing contexts.',
  `group_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'This column will store a comma separated list of the type(s) of profile fields.',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Form title.',
  `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display before fields in form.',
  `help_post` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display after fields in form.',
  `limit_listings_group_id` int(10) unsigned DEFAULT NULL COMMENT 'Group id, foriegn key from civicrm_group',
  `post_URL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Redirect to URL.',
  `add_to_group_id` int(10) unsigned DEFAULT NULL COMMENT 'foreign key to civicrm_group_id',
  `add_captcha` tinyint(4) DEFAULT '0' COMMENT 'Should a CAPTCHA widget be included this Profile form.',
  `is_map` tinyint(4) DEFAULT '0' COMMENT 'Do we want to map results from this profile.',
  `is_edit_link` tinyint(4) DEFAULT '0' COMMENT 'Should edit link display in profile selector',
  `is_uf_link` tinyint(4) DEFAULT '0' COMMENT 'Should we display a link to the website profile in profile selector',
  `is_update_dupe` tinyint(4) DEFAULT '0' COMMENT 'Should we update the contact record if we find a duplicate',
  `cancel_URL` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Redirect to URL when Cancle button clik .',
  `is_cms_user` tinyint(4) DEFAULT '0' COMMENT 'Should we create a cms user for this profile ',
  `notify` text COLLATE utf8_unicode_ci,
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this group reserved for use by some other CiviCRM functionality?',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the UF group for directly addressing it in the codebase',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this UF group',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time this UF group was created.',
  `is_proximity_search` tinyint(4) DEFAULT '0' COMMENT 'Should we include proximity search feature in this profile search form?',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_uf_group`
--

LOCK TABLES `log_civicrm_uf_group` WRITE;
/*!40000 ALTER TABLE `log_civicrm_uf_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_uf_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_uf_join`
--

DROP TABLE IF EXISTS `log_civicrm_uf_join`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_uf_join` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique table ID',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this join currently active?',
  `module` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Module which owns this uf_join instance, e.g. User Registration, CiviDonate, etc.',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of table where item being referenced is stored. Modules which only need a single collection of uf_join instances may choose not to populate entity_table and entity_id.',
  `entity_id` int(10) unsigned DEFAULT NULL COMMENT 'Foreign key to the referenced item.',
  `weight` int(11) NOT NULL DEFAULT '1' COMMENT 'Controls display order when multiple user framework groups are setup for concurrent display.',
  `uf_group_id` int(10) unsigned NOT NULL COMMENT 'Which form does this field belong to.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_uf_join`
--

LOCK TABLES `log_civicrm_uf_join` WRITE;
/*!40000 ALTER TABLE `log_civicrm_uf_join` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_uf_join` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_uf_match`
--

DROP TABLE IF EXISTS `log_civicrm_uf_match`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_uf_match` (
  `id` int(10) unsigned NOT NULL COMMENT 'System generated ID.',
  `domain_id` int(10) unsigned NOT NULL COMMENT 'Which Domain is this match entry for',
  `uf_id` int(10) unsigned NOT NULL COMMENT 'UF ID',
  `uf_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'UF Name',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'UI language preferred by the given user/contact',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_uf_match`
--

LOCK TABLES `log_civicrm_uf_match` WRITE;
/*!40000 ALTER TABLE `log_civicrm_uf_match` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_uf_match` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_value_activity_details_6`
--

DROP TABLE IF EXISTS `log_civicrm_value_activity_details_6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_value_activity_details_6` (
  `id` int(10) unsigned NOT NULL COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `place_of_inquiry_43` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activity_category_44` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_value_activity_details_6`
--

LOCK TABLES `log_civicrm_value_activity_details_6` WRITE;
/*!40000 ALTER TABLE `log_civicrm_value_activity_details_6` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_value_activity_details_6` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_value_attachments_5`
--

DROP TABLE IF EXISTS `log_civicrm_value_attachments_5`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_value_attachments_5` (
  `id` int(10) unsigned NOT NULL COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `attachment_36` int(10) unsigned DEFAULT NULL,
  `attachment_2_37` int(10) unsigned DEFAULT NULL,
  `attachment_3_38` int(10) unsigned DEFAULT NULL,
  `attachment_4_39` int(10) unsigned DEFAULT NULL,
  `attachment_5_40` int(10) unsigned DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_value_attachments_5`
--

LOCK TABLES `log_civicrm_value_attachments_5` WRITE;
/*!40000 ALTER TABLE `log_civicrm_value_attachments_5` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_value_attachments_5` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_value_constituent_information_1`
--

DROP TABLE IF EXISTS `log_civicrm_value_constituent_information_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_value_constituent_information_1` (
  `id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `professional_accreditations_16` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `interest_in_volunteering__17` tinyint(4) DEFAULT NULL,
  `active_constituent__18` tinyint(4) DEFAULT NULL,
  `friend_of_the_senator__19` tinyint(4) DEFAULT NULL,
  `skills_areas_of_interest_20` text COLLATE utf8_unicode_ci,
  `honors_and_awards_21` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `voter_registration_status_23` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `boe_date_of_registration_24` datetime DEFAULT NULL,
  `individual_category_42` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `other_gender_45` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ethnicity1_58` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_source_60` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `record_type_61` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `other_ethnicity_62` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `religion_63` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `entity_id` (`entity_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_value_constituent_information_1`
--

LOCK TABLES `log_civicrm_value_constituent_information_1` WRITE;
/*!40000 ALTER TABLE `log_civicrm_value_constituent_information_1` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_value_constituent_information_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_value_contact_details_8`
--

DROP TABLE IF EXISTS `log_civicrm_value_contact_details_8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_value_contact_details_8` (
  `id` int(10) unsigned NOT NULL COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `privacy_options_note_64` text COLLATE utf8_unicode_ci,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_value_contact_details_8`
--

LOCK TABLES `log_civicrm_value_contact_details_8` WRITE;
/*!40000 ALTER TABLE `log_civicrm_value_contact_details_8` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_value_contact_details_8` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_value_district_information_7`
--

DROP TABLE IF EXISTS `log_civicrm_value_district_information_7`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_value_district_information_7` (
  `id` int(10) unsigned NOT NULL COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `congressional_district_46` smallint(6) DEFAULT NULL,
  `ny_senate_district_47` smallint(6) DEFAULT NULL,
  `ny_assembly_district_48` smallint(6) DEFAULT NULL,
  `election_district_49` smallint(6) DEFAULT NULL,
  `county_50` smallint(6) DEFAULT NULL,
  `county_legislative_district_51` smallint(6) DEFAULT NULL,
  `town_52` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ward_53` smallint(6) DEFAULT NULL,
  `school_district_54` smallint(6) DEFAULT NULL,
  `new_york_city_council_55` smallint(6) DEFAULT NULL,
  `neighborhood_56` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_import_57` datetime DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `entity_id` (`entity_id`),
  KEY `log_date` (`log_date`),
  KEY `log_conn_id` (`log_conn_id`),
  KEY `log_user_id` (`log_user_id`),
  KEY `log_action` (`log_action`),
  KEY `log_job_id` (`log_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_value_district_information_7`
--

LOCK TABLES `log_civicrm_value_district_information_7` WRITE;
/*!40000 ALTER TABLE `log_civicrm_value_district_information_7` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_value_district_information_7` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_value_organization_constituent_informa_3`
--

DROP TABLE IF EXISTS `log_civicrm_value_organization_constituent_informa_3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_value_organization_constituent_informa_3` (
  `id` int(10) unsigned NOT NULL COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `charity_registration__dos__25` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `employer_identification_number___26` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_category_41` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_value_organization_constituent_informa_3`
--

LOCK TABLES `log_civicrm_value_organization_constituent_informa_3` WRITE;
/*!40000 ALTER TABLE `log_civicrm_value_organization_constituent_informa_3` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_value_organization_constituent_informa_3` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_website`
--

DROP TABLE IF EXISTS `log_civicrm_website`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_website` (
  `id` int(10) unsigned NOT NULL COMMENT 'Unique Website ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Website',
  `website_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Website type does this website belong to.',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_website`
--

LOCK TABLES `log_civicrm_website` WRITE;
/*!40000 ALTER TABLE `log_civicrm_website` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_website` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_civicrm_worldregion`
--

DROP TABLE IF EXISTS `log_civicrm_worldregion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_civicrm_worldregion` (
  `id` int(10) unsigned NOT NULL COMMENT 'Country Id',
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Region name to be associated with countries',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_conn_id` int(11) DEFAULT NULL,
  `log_user_id` int(11) DEFAULT NULL,
  `log_action` enum('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log_job_id` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_civicrm_worldregion`
--

LOCK TABLES `log_civicrm_worldregion` WRITE;
/*!40000 ALTER TABLE `log_civicrm_worldregion` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_civicrm_worldregion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'senate_prod_l_template'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-05-14  3:28:10
