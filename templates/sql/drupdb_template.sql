-- MySQL dump 10.13  Distrib 5.5.31, for Linux (x86_64)
--
-- Host: crmdbprod    Database: senate_prod_d_template
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
-- Table structure for table `access`
--

DROP TABLE IF EXISTS `access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `mask` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access`
--

LOCK TABLES `access` WRITE;
/*!40000 ALTER TABLE `access` DISABLE KEYS */;
/*!40000 ALTER TABLE `access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actions`
--

DROP TABLE IF EXISTS `actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actions` (
  `aid` varchar(255) NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL DEFAULT '',
  `callback` varchar(255) NOT NULL DEFAULT '',
  `parameters` longblob NOT NULL COMMENT 'Parameters to be passed to the callback function.',
  `label` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actions`
--

LOCK TABLES `actions` WRITE;
/*!40000 ALTER TABLE `actions` DISABLE KEYS */;
INSERT INTO `actions` VALUES ('1','system','system_goto_action','a:1:{s:3:\"url\";s:7:\"civicrm\";}','Redirect to CiviCRM Dashboard'),('comment_publish_action','comment','comment_publish_action','','Publish comment'),('comment_unpublish_action','comment','comment_unpublish_action','','Unpublish comment'),('node_make_sticky_action','node','node_make_sticky_action','','Make post sticky'),('node_make_unsticky_action','node','node_make_unsticky_action','','Make post unsticky'),('node_promote_action','node','node_promote_action','','Promote post to front page'),('node_publish_action','node','node_publish_action','','Publish post'),('node_save_action','node','node_save_action','','Save post'),('node_unpromote_action','node','node_unpromote_action','','Remove post from front page'),('node_unpublish_action','node','node_unpublish_action','','Unpublish post'),('rules_action_civicrm_add_to_group','','rules_action_civicrm_add_to_group','',''),('rules_action_civicrm_contact_send_email','','rules_action_civicrm_contact_send_email','',''),('rules_action_civicrm_mailing_send_email','','rules_action_civicrm_mailing_send_email','',''),('rules_action_civicrm_remove_from_group','','rules_action_civicrm_remove_from_group','',''),('system_block_ip_action','user','system_block_ip_action','','Ban IP address of current user'),('user_block_ip_action','user','user_block_ip_action','','Ban IP address of current user'),('user_block_user_action','user','user_block_user_action','','Block current user');
/*!40000 ALTER TABLE `actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apachesolr_environment`
--

DROP TABLE IF EXISTS `apachesolr_environment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apachesolr_environment` (
  `env_id` varchar(64) NOT NULL COMMENT 'Unique identifier for the environment',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT 'Human-readable name for the server',
  `url` varchar(1000) NOT NULL COMMENT 'Full url for the server',
  `service_class` varchar(255) NOT NULL DEFAULT '' COMMENT 'Optional class name to use for connection',
  PRIMARY KEY (`env_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The Solr server table.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apachesolr_environment`
--

LOCK TABLES `apachesolr_environment` WRITE;
/*!40000 ALTER TABLE `apachesolr_environment` DISABLE KEYS */;
INSERT INTO `apachesolr_environment` VALUES ('solr','Apache Solr server','http://localhost:8180/solr','');
/*!40000 ALTER TABLE `apachesolr_environment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apachesolr_environment_variable`
--

DROP TABLE IF EXISTS `apachesolr_environment_variable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apachesolr_environment_variable` (
  `env_id` varchar(64) NOT NULL COMMENT 'Unique identifier for the environment',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT 'The name of the variable.',
  `value` longblob NOT NULL COMMENT 'The value of the variable.',
  PRIMARY KEY (`env_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Variable values for each Solr server.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apachesolr_environment_variable`
--

LOCK TABLES `apachesolr_environment_variable` WRITE;
/*!40000 ALTER TABLE `apachesolr_environment_variable` DISABLE KEYS */;
/*!40000 ALTER TABLE `apachesolr_environment_variable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apachesolr_index_bundles`
--

DROP TABLE IF EXISTS `apachesolr_index_bundles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apachesolr_index_bundles` (
  `env_id` varchar(64) NOT NULL COMMENT 'Unique identifier for the environment',
  `entity_type` varchar(32) NOT NULL COMMENT 'The type of entity.',
  `bundle` varchar(128) NOT NULL COMMENT 'The bundle to index.',
  PRIMARY KEY (`env_id`,`entity_type`,`bundle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Records what bundles we should be indexing for a given...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apachesolr_index_bundles`
--

LOCK TABLES `apachesolr_index_bundles` WRITE;
/*!40000 ALTER TABLE `apachesolr_index_bundles` DISABLE KEYS */;
INSERT INTO `apachesolr_index_bundles` VALUES ('solr','node','page'),('solr','node','story');
/*!40000 ALTER TABLE `apachesolr_index_bundles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apachesolr_index_entities`
--

DROP TABLE IF EXISTS `apachesolr_index_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apachesolr_index_entities` (
  `entity_type` varchar(32) NOT NULL COMMENT 'The type of entity.',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'The primary identifier for an entity.',
  `bundle` varchar(128) NOT NULL COMMENT 'The bundle to which this entity belongs.',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT 'Boolean indicating whether the entity is visible to non-administrators (eg, published for nodes).',
  `changed` int(11) NOT NULL DEFAULT '0' COMMENT 'The Unix timestamp when an entity was changed.',
  PRIMARY KEY (`entity_id`,`entity_type`),
  KEY `bundle_changed` (`bundle`,`changed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores a record of when an entity changed to determine if...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apachesolr_index_entities`
--

LOCK TABLES `apachesolr_index_entities` WRITE;
/*!40000 ALTER TABLE `apachesolr_index_entities` DISABLE KEYS */;
/*!40000 ALTER TABLE `apachesolr_index_entities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apachesolr_index_entities_node`
--

DROP TABLE IF EXISTS `apachesolr_index_entities_node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apachesolr_index_entities_node` (
  `entity_type` varchar(32) NOT NULL COMMENT 'The type of entity.',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'The primary identifier for an entity.',
  `bundle` varchar(128) NOT NULL COMMENT 'The bundle to which this entity belongs.',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT 'Boolean indicating whether the entity is visible to non-administrators (eg, published for nodes).',
  `changed` int(11) NOT NULL DEFAULT '0' COMMENT 'The Unix timestamp when an entity was changed.',
  PRIMARY KEY (`entity_id`),
  KEY `bundle_changed` (`bundle`,`changed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores a record of when an entity changed to determine if...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apachesolr_index_entities_node`
--

LOCK TABLES `apachesolr_index_entities_node` WRITE;
/*!40000 ALTER TABLE `apachesolr_index_entities_node` DISABLE KEYS */;
INSERT INTO `apachesolr_index_entities_node` VALUES ('node',1,'page',1,1367678391),('node',2,'page',1,1367678391),('node',3,'page',1,1367678438);
/*!40000 ALTER TABLE `apachesolr_index_entities_node` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apachesolr_search_page`
--

DROP TABLE IF EXISTS `apachesolr_search_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apachesolr_search_page` (
  `page_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'The machine readable name of the search page.',
  `label` varchar(32) NOT NULL DEFAULT '' COMMENT 'The human readable name of the search page.',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT 'The description of the search page.',
  `search_path` varchar(255) NOT NULL DEFAULT '' COMMENT 'The path to the search page.',
  `page_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'The title of the search page.',
  `env_id` varchar(64) NOT NULL DEFAULT '' COMMENT 'The machine name of the search environment.',
  `settings` text COMMENT 'Serialized storage of general settings.',
  PRIMARY KEY (`page_id`),
  KEY `env_id` (`env_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Apache Solr Search search page settings.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apachesolr_search_page`
--

LOCK TABLES `apachesolr_search_page` WRITE;
/*!40000 ALTER TABLE `apachesolr_search_page` DISABLE KEYS */;
INSERT INTO `apachesolr_search_page` VALUES ('core_search','Core Search','Site search','search/site','Site','solr','a:6:{s:29:\"apachesolr_search_search_type\";s:6:\"custom\";s:26:\"apachesolr_search_per_page\";s:2:\"10\";s:24:\"apachesolr_search_browse\";s:6:\"browse\";s:28:\"apachesolr_search_spellcheck\";i:1;s:31:\"apachesolr_search_not_removable\";b:1;s:28:\"apachesolr_search_search_box\";b:1;}'),('taxonomy_search','Taxonomy Search','Search all items with given term','taxonomy/term/%','%value','','a:5:{s:29:\"apachesolr_search_search_type\";s:3:\"tid\";s:26:\"apachesolr_search_per_page\";i:10;s:24:\"apachesolr_search_browse\";s:7:\"results\";s:28:\"apachesolr_search_spellcheck\";b:0;s:28:\"apachesolr_search_search_box\";b:0;}');
/*!40000 ALTER TABLE `apachesolr_search_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authmap`
--

DROP TABLE IF EXISTS `authmap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authmap` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `authname` varchar(128) NOT NULL DEFAULT '',
  `module` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`aid`),
  UNIQUE KEY `authname` (`authname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authmap`
--

LOCK TABLES `authmap` WRITE;
/*!40000 ALTER TABLE `authmap` DISABLE KEYS */;
/*!40000 ALTER TABLE `authmap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batch`
--

DROP TABLE IF EXISTS `batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch` (
  `bid` int(10) unsigned NOT NULL COMMENT 'Primary Key: Unique batch ID.',
  `token` varchar(64) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `batch` longblob COMMENT 'A serialized array containing the processing data for the batch.',
  PRIMARY KEY (`bid`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batch`
--

LOCK TABLES `batch` WRITE;
/*!40000 ALTER TABLE `batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block`
--

DROP TABLE IF EXISTS `block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block` (
  `bid` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(64) NOT NULL DEFAULT '',
  `delta` varchar(32) NOT NULL DEFAULT '0',
  `theme` varchar(64) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT 'Block weight within region.',
  `region` varchar(64) NOT NULL DEFAULT '',
  `custom` tinyint(4) NOT NULL DEFAULT '0',
  `visibility` tinyint(4) NOT NULL DEFAULT '0',
  `pages` text NOT NULL,
  `title` varchar(64) NOT NULL DEFAULT '',
  `cache` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`bid`),
  UNIQUE KEY `tmd` (`theme`,`module`,`delta`),
  KEY `list` (`theme`,`status`,`region`,`weight`,`module`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block`
--

LOCK TABLES `block` WRITE;
/*!40000 ALTER TABLE `block` DISABLE KEYS */;
INSERT INTO `block` VALUES (1,'user','login','garland',1,0,'sidebar_first',0,0,'','',-1),(2,'system','navigation','garland',1,0,'sidebar_first',0,0,'','',-1),(3,'system','powered-by','garland',1,10,'footer',0,0,'','',-1),(4,'comment','recent','garland',0,0,'',0,0,'','',1),(5,'system','main-menu','garland',0,0,'-1',0,0,'','',-1),(6,'menu','secondary-menu','garland',0,0,'-1',0,0,'','',-1),(7,'node','syndicate','garland',0,0,'-1',0,0,'','',-1),(8,'search','form','garland',0,0,'-1',0,0,'','',-1),(9,'user','new','garland',0,0,'-1',0,0,'','',1),(10,'user','online','garland',0,0,'-1',0,0,'','',-1),(11,'apachesolr','mlt-001','garland',0,0,'',0,0,'','',4),(12,'apachesolr','sort','garland',0,0,'',0,0,'','',4),(15,'civicrm','2','garland',1,-99,'sidebar_first',0,1,'civicrm*','Recent Items',-1),(16,'civicrm','3','garland',1,-98,'sidebar_first',0,1,'civicrm*','<none>',-1),(18,'civicrm','5','garland',1,-96,'sidebar_first',0,1,'civicrm*','',-1),(20,'civicrm','7','garland',0,-94,'-1',0,1,'civicrm*','',8),(21,'apachesolr','mlt-001','tao',0,0,'sidebar_first',0,0,'','',5),(22,'apachesolr','sort','tao',0,0,'sidebar_first',0,0,'','',4),(24,'civicrm','1','tao',1,-100,'sidebar_first',0,1,'civicrm*','',8),(25,'civicrm','2','tao',1,-99,'sidebar_first',0,1,'civicrm*','Recent Items',8),(26,'civicrm','3','tao',1,-98,'sidebar_first',0,1,'civicrm*','<none>',8),(27,'civicrm','4','tao',1,-97,'sidebar_first',0,1,'civicrm*','',8),(28,'civicrm','5','tao',1,-96,'sidebar_first',0,1,'civicrm*','',8),(29,'civicrm','6','tao',0,-95,'sidebar_first',0,1,'civicrm*','',8),(30,'civicrm','7','tao',0,-94,'sidebar_first',0,1,'civicrm*','',8),(31,'comment','recent','tao',0,0,'sidebar_first',0,0,'','',1),(32,'system','main-menu','tao',0,0,'sidebar_first',0,0,'','',-1),(33,'menu','secondary-menu','tao',0,0,'sidebar_first',0,0,'','',-1),(34,'node','syndicate','tao',0,0,'sidebar_first',0,0,'','',-1),(35,'search','form','tao',0,0,'sidebar_first',0,0,'','',-1),(36,'system','powered-by','tao',1,10,'footer',0,0,'','',-1),(37,'user','login','tao',1,0,'sidebar_first',0,0,'','',-1),(38,'system','navigation','tao',1,0,'sidebar_first',0,0,'','',-1),(39,'user','new','tao',0,0,'sidebar_first',0,0,'','',1),(40,'user','online','tao',0,0,'sidebar_first',0,0,'','',-1),(41,'apachesolr','mlt-001','ginkgo',0,0,'sidebar_first',0,0,'','',5),(42,'apachesolr','sort','ginkgo',0,0,'sidebar_first',0,0,'','',4),(44,'civicrm','1','ginkgo',1,-100,'sidebar_first',0,1,'civicrm*','',8),(45,'civicrm','2','ginkgo',1,-99,'sidebar_first',0,1,'civicrm*','Recent Items',8),(46,'civicrm','3','ginkgo',1,-98,'sidebar_first',0,1,'civicrm*','<none>',8),(47,'civicrm','4','ginkgo',1,-97,'sidebar_first',0,1,'civicrm*','',8),(48,'civicrm','5','ginkgo',1,-96,'sidebar_first',0,1,'civicrm*','',8),(49,'civicrm','6','ginkgo',0,-95,'sidebar_first',0,1,'civicrm*','',8),(50,'civicrm','7','ginkgo',0,-94,'sidebar_first',0,1,'civicrm*','',8),(51,'comment','recent','ginkgo',0,0,'sidebar_first',0,0,'','',1),(52,'system','main-menu','ginkgo',0,0,'sidebar_first',0,0,'','',-1),(53,'menu','secondary-menu','ginkgo',0,0,'sidebar_first',0,0,'','',-1),(54,'node','syndicate','ginkgo',0,0,'sidebar_first',0,0,'','',-1),(55,'search','form','ginkgo',0,0,'sidebar_first',0,0,'','',-1),(56,'system','powered-by','ginkgo',1,10,'sidebar_first',0,0,'','',-1),(57,'user','login','ginkgo',1,0,'sidebar_first',0,0,'','',-1),(58,'system','navigation','ginkgo',1,0,'sidebar_first',0,0,'','',-1),(59,'user','new','ginkgo',0,0,'sidebar_first',0,0,'','',1),(60,'user','online','ginkgo',0,0,'sidebar_first',0,0,'','',-1),(61,'apachesolr','mlt-001','blueprint',0,-4,'',0,0,'','',5),(62,'apachesolr','sort','blueprint',0,-7,'',0,0,'','',4),(64,'civicrm','1','blueprint',0,-10,'',0,1,'civicrm*','',8),(65,'civicrm','2','blueprint',1,-10,'footer',0,1,'civicrm*','Recent Items',8),(66,'civicrm','3','blueprint',1,-10,'content',0,1,'civicrm*','<none>',8),(67,'civicrm','4','blueprint',1,-9,'header',0,1,'civicrm*','',8),(68,'civicrm','5','blueprint',0,-8,'',0,1,'civicrm*','',8),(70,'civicrm','7','blueprint',0,-8,'',0,1,'civicrm*','',8),(71,'comment','recent','blueprint',0,-2,'',0,0,'','',1),(72,'system','main-menu','blueprint',0,-3,'',0,0,'','',-1),(73,'menu','secondary-menu','blueprint',0,0,'',0,0,'','',-1),(74,'node','syndicate','blueprint',0,1,'',0,0,'','',-1),(75,'search','form','blueprint',0,-1,'',0,0,'','',-1),(76,'system','powered-by','blueprint',0,-5,'',0,0,'','',-1),(77,'user','login','blueprint',1,-7,'content',0,0,'','',-1),(78,'system','navigation','blueprint',0,-9,'',0,0,'','',-1),(79,'user','new','blueprint',0,2,'',0,0,'','',1),(80,'user','online','blueprint',0,3,'',0,0,'','',-1),(81,'apachesolr','mlt-001','rayCivicrm',0,-1,'',0,0,'','',4),(82,'apachesolr','sort','rayCivicrm',0,-3,'',0,0,'','',4),(85,'civicrm','2','rayCivicrm',0,-9,'-1',0,1,'civicrm*','Recent Items',-1),(86,'civicrm','3','rayCivicrm',0,-10,'-1',0,1,'civicrm*','<none>',-1),(88,'civicrm','5','rayCivicrm',0,-4,'-1',0,1,'civicrm*','',-1),(89,'civicrm','7','rayCivicrm',0,-5,'-1',0,1,'civicrm*','',8),(90,'comment','recent','rayCivicrm',0,1,'',0,0,'','',1),(91,'system','main-menu','rayCivicrm',0,0,'-1',0,0,'','',-1),(92,'menu','secondary-menu','rayCivicrm',0,2,'-1',0,0,'','',-1),(93,'node','syndicate','rayCivicrm',0,4,'-1',0,0,'','',-1),(94,'search','form','rayCivicrm',0,3,'-1',0,0,'','',-1),(95,'system','powered-by','rayCivicrm',0,-2,'-1',0,0,'','',-1),(96,'user','login','rayCivicrm',0,-7,'-1',0,0,'','',-1),(97,'system','navigation','rayCivicrm',0,-8,'-1',0,0,'','',-1),(98,'user','new','rayCivicrm',0,5,'-1',0,0,'','',1),(99,'user','online','rayCivicrm',0,6,'-1',0,0,'','',-1),(100,'system','help','garland',1,0,'help',0,0,'','',-1),(101,'system','main','garland',1,0,'content',0,0,'','',-1),(102,'node','recent','garland',0,0,'-1',0,0,'','',1),(103,'system','management','garland',1,0,'sidebar_first',0,0,'','',-1),(104,'system','user-menu','garland',1,0,'sidebar_first',0,0,'','',-1),(106,'apachesolr_search','sort','garland',0,0,'-1',0,0,'','',4),(107,'apachesolr_search','sort','rayCivicrm',0,0,'-1',0,0,'','',4),(108,'node','recent','rayCivicrm',0,0,'-1',0,0,'','',1),(109,'system','main','rayCivicrm',0,0,'-1',0,0,'','',-1),(110,'system','help','rayCivicrm',0,5,'-1',0,0,'','',-1),(111,'system','management','rayCivicrm',0,0,'-1',0,0,'','',-1),(112,'system','user-menu','rayCivicrm',0,0,'-1',0,0,'','',-1),(113,'apachesolr','mlt-001','Bluebird',0,-1,'',0,0,'','',4),(114,'apachesolr','sort','Bluebird',0,-3,'',0,0,'','',4),(115,'apachesolr_search','sort','Bluebird',0,0,'-1',0,0,'','',4),(116,'civicrm','2','Bluebird',1,-9,'footer',0,1,'civicrm*','Recent Items',-1),(117,'civicrm','3','Bluebird',0,-10,'-1',0,1,'civicrm*','<none>',-1),(118,'civicrm','5','Bluebird',0,-4,'-1',0,1,'civicrm*','',-1),(119,'civicrm','7','Bluebird',0,-5,'-1',0,1,'civicrm*','',8),(120,'comment','recent','Bluebird',0,1,'',0,0,'','',1),(121,'menu','secondary-menu','Bluebird',0,2,'-1',0,0,'','',-1),(122,'node','recent','Bluebird',0,0,'-1',0,0,'','',1),(123,'node','syndicate','Bluebird',0,4,'-1',0,0,'','',-1),(124,'search','form','Bluebird',0,3,'-1',0,0,'','',-1),(125,'system','help','Bluebird',0,5,'-1',0,0,'','',-1),(126,'system','main','Bluebird',1,0,'content',0,0,'','',-1),(127,'system','main-menu','Bluebird',0,0,'-1',0,0,'','',-1),(128,'system','management','Bluebird',0,0,'-1',0,0,'','',-1),(129,'system','navigation','Bluebird',0,-8,'-1',0,0,'','',-1),(130,'system','powered-by','Bluebird',0,-2,'-1',0,0,'','',-1),(131,'system','user-menu','Bluebird',0,0,'-1',0,0,'','',-1),(132,'user','login','Bluebird',1,-7,'content',0,0,'','',-1),(133,'user','new','Bluebird',0,5,'-1',0,0,'','',1),(134,'user','online','Bluebird',0,6,'-1',0,0,'','',-1);
/*!40000 ALTER TABLE `block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block_custom`
--

DROP TABLE IF EXISTS `block_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_custom` (
  `bid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `body` longtext,
  `info` varchar(128) NOT NULL DEFAULT '',
  `format` varchar(255) DEFAULT NULL COMMENT 'The filter_format.format of the block body.',
  PRIMARY KEY (`bid`),
  UNIQUE KEY `info` (`info`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_custom`
--

LOCK TABLES `block_custom` WRITE;
/*!40000 ALTER TABLE `block_custom` DISABLE KEYS */;
/*!40000 ALTER TABLE `block_custom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block_node_type`
--

DROP TABLE IF EXISTS `block_node_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_node_type` (
  `module` varchar(64) NOT NULL COMMENT 'The block’s origin module, from block.module.',
  `delta` varchar(32) NOT NULL COMMENT 'The block’s unique delta within module, from block.delta.',
  `type` varchar(32) NOT NULL COMMENT 'The machine-readable name of this type from node_type.type.',
  PRIMARY KEY (`module`,`delta`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sets up display criteria for blocks based on content types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_node_type`
--

LOCK TABLES `block_node_type` WRITE;
/*!40000 ALTER TABLE `block_node_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `block_node_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block_role`
--

DROP TABLE IF EXISTS `block_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_role` (
  `module` varchar(64) NOT NULL,
  `delta` varchar(32) NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`module`,`delta`,`rid`),
  KEY `rid` (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_role`
--

LOCK TABLES `block_role` WRITE;
/*!40000 ALTER TABLE `block_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `block_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocked_ips`
--

DROP TABLE IF EXISTS `blocked_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_ips` (
  `iid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: unique ID for IP addresses.',
  `ip` varchar(40) NOT NULL DEFAULT '' COMMENT 'IP address',
  PRIMARY KEY (`iid`),
  KEY `blocked_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores blocked IP addresses.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocked_ips`
--

LOCK TABLES `blocked_ips` WRITE;
/*!40000 ALTER TABLE `blocked_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Generic cache table for caching things not separated out...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_apachesolr`
--

DROP TABLE IF EXISTS `cache_apachesolr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_apachesolr` (
  `cid` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `expire` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `serialized` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_apachesolr`
--

LOCK TABLES `cache_apachesolr` WRITE;
/*!40000 ALTER TABLE `cache_apachesolr` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_apachesolr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_block`
--

DROP TABLE IF EXISTS `cache_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_block` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Generic cache table for caching things not separated out...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_block`
--

LOCK TABLES `cache_block` WRITE;
/*!40000 ALTER TABLE `cache_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_bootstrap`
--

DROP TABLE IF EXISTS `cache_bootstrap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_bootstrap` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache table for data required to bootstrap Drupal, may be...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_bootstrap`
--

LOCK TABLES `cache_bootstrap` WRITE;
/*!40000 ALTER TABLE `cache_bootstrap` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_bootstrap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_field`
--

DROP TABLE IF EXISTS `cache_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_field` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Generic cache table for caching things not separated out...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_field`
--

LOCK TABLES `cache_field` WRITE;
/*!40000 ALTER TABLE `cache_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_filter`
--

DROP TABLE IF EXISTS `cache_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_filter` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Generic cache table for caching things not separated out...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_filter`
--

LOCK TABLES `cache_filter` WRITE;
/*!40000 ALTER TABLE `cache_filter` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_filter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_form`
--

DROP TABLE IF EXISTS `cache_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_form` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache table for the form system to store recently built...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_form`
--

LOCK TABLES `cache_form` WRITE;
/*!40000 ALTER TABLE `cache_form` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_form` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_menu`
--

DROP TABLE IF EXISTS `cache_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_menu` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache table for the menu system to store router...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_menu`
--

LOCK TABLES `cache_menu` WRITE;
/*!40000 ALTER TABLE `cache_menu` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_page`
--

DROP TABLE IF EXISTS `cache_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_page` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache table used to store compressed pages for anonymous...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_page`
--

LOCK TABLES `cache_page` WRITE;
/*!40000 ALTER TABLE `cache_page` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_path`
--

DROP TABLE IF EXISTS `cache_path`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_path` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache table used for path alias lookups.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_path`
--

LOCK TABLES `cache_path` WRITE;
/*!40000 ALTER TABLE `cache_path` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_path` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_rules`
--

DROP TABLE IF EXISTS `cache_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_rules` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache table for the rules engine to store configured items.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_rules`
--

LOCK TABLES `cache_rules` WRITE;
/*!40000 ALTER TABLE `cache_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_update`
--

DROP TABLE IF EXISTS `cache_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_update` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Generic cache table for caching things not separated out...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_update`
--

LOCK TABLES `cache_update` WRITE;
/*!40000 ALTER TABLE `cache_update` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_update` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `civicrm_group_roles_rules`
--

DROP TABLE IF EXISTS `civicrm_group_roles_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `civicrm_group_roles_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `civicrm_group_roles_rules`
--

LOCK TABLES `civicrm_group_roles_rules` WRITE;
/*!40000 ALTER TABLE `civicrm_group_roles_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `civicrm_group_roles_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `civicrm_member_roles_rules`
--

DROP TABLE IF EXISTS `civicrm_member_roles_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `civicrm_member_roles_rules` (
  `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `type_id` int(10) unsigned NOT NULL,
  `status_codes` text NOT NULL,
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `civicrm_member_roles_rules`
--

LOCK TABLES `civicrm_member_roles_rules` WRITE;
/*!40000 ALTER TABLE `civicrm_member_roles_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `civicrm_member_roles_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `nid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(64) NOT NULL DEFAULT '',
  `hostname` varchar(128) NOT NULL DEFAULT '',
  `changed` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `thread` varchar(255) NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `mail` varchar(64) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  `language` varchar(12) NOT NULL DEFAULT '',
  `created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `comment_uid` (`uid`),
  KEY `comment_nid_language` (`nid`,`language`),
  KEY `comment_num_new` (`nid`,`status`,`created`,`cid`,`thread`),
  KEY `comment_created` (`created`),
  KEY `comment_status_pid` (`pid`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comment`
--

LOCK TABLES `comment` WRITE;
/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `d6_upgrade_filter`
--

DROP TABLE IF EXISTS `d6_upgrade_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `d6_upgrade_filter` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `format` int(11) NOT NULL DEFAULT '0',
  `module` varchar(64) NOT NULL DEFAULT '',
  `delta` tinyint(4) NOT NULL DEFAULT '0',
  `weight` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`),
  UNIQUE KEY `fmd` (`format`,`module`,`delta`),
  KEY `list` (`format`,`weight`,`module`,`delta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `d6_upgrade_filter`
--

LOCK TABLES `d6_upgrade_filter` WRITE;
/*!40000 ALTER TABLE `d6_upgrade_filter` DISABLE KEYS */;
/*!40000 ALTER TABLE `d6_upgrade_filter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `date_format_locale`
--

DROP TABLE IF EXISTS `date_format_locale`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `date_format_locale` (
  `format` varchar(100) NOT NULL COMMENT 'The date format string.',
  `type` varchar(64) NOT NULL COMMENT 'The date format type, e.g. medium.',
  `language` varchar(12) NOT NULL COMMENT 'A languages.language for this format to be used with.',
  PRIMARY KEY (`type`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores configured date formats for each locale.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `date_format_locale`
--

LOCK TABLES `date_format_locale` WRITE;
/*!40000 ALTER TABLE `date_format_locale` DISABLE KEYS */;
/*!40000 ALTER TABLE `date_format_locale` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `date_format_type`
--

DROP TABLE IF EXISTS `date_format_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `date_format_type` (
  `type` varchar(64) NOT NULL COMMENT 'The date format type, e.g. medium.',
  `title` varchar(255) NOT NULL COMMENT 'The human readable name of the format type.',
  `locked` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether or not this is a system provided format.',
  PRIMARY KEY (`type`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores configured date format types.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `date_format_type`
--

LOCK TABLES `date_format_type` WRITE;
/*!40000 ALTER TABLE `date_format_type` DISABLE KEYS */;
INSERT INTO `date_format_type` VALUES ('long','Long',1),('medium','Medium',1),('short','Short',1);
/*!40000 ALTER TABLE `date_format_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `date_formats`
--

DROP TABLE IF EXISTS `date_formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `date_formats` (
  `dfid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The date format identifier.',
  `format` varchar(100) NOT NULL COMMENT 'The date format string.',
  `type` varchar(64) NOT NULL COMMENT 'The date format type, e.g. medium.',
  `locked` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether or not this format can be modified.',
  PRIMARY KEY (`dfid`),
  UNIQUE KEY `formats` (`format`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='Stores configured date formats.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `date_formats`
--

LOCK TABLES `date_formats` WRITE;
/*!40000 ALTER TABLE `date_formats` DISABLE KEYS */;
INSERT INTO `date_formats` VALUES (1,'Y-m-d H:i','short',1),(2,'m/d/Y - H:i','short',1),(3,'d/m/Y - H:i','short',1),(4,'Y/m/d - H:i','short',1),(5,'d.m.Y - H:i','short',1),(6,'m/d/Y - g:ia','short',1),(7,'d/m/Y - g:ia','short',1),(8,'Y/m/d - g:ia','short',1),(9,'M j Y - H:i','short',1),(10,'j M Y - H:i','short',1),(11,'Y M j - H:i','short',1),(12,'M j Y - g:ia','short',1),(13,'j M Y - g:ia','short',1),(14,'Y M j - g:ia','short',1),(15,'D, Y-m-d H:i','medium',1),(16,'D, m/d/Y - H:i','medium',1),(17,'D, d/m/Y - H:i','medium',1),(18,'D, Y/m/d - H:i','medium',1),(19,'F j, Y - H:i','medium',1),(20,'j F, Y - H:i','medium',1),(21,'Y, F j - H:i','medium',1),(22,'D, m/d/Y - g:ia','medium',1),(23,'D, d/m/Y - g:ia','medium',1),(24,'D, Y/m/d - g:ia','medium',1),(25,'F j, Y - g:ia','medium',1),(26,'j F Y - g:ia','medium',1),(27,'Y, F j - g:ia','medium',1),(28,'j. F Y - G:i','medium',1),(29,'l, F j, Y - H:i','long',1),(30,'l, j F, Y - H:i','long',1),(31,'l, Y,  F j - H:i','long',1),(32,'l, F j, Y - g:ia','long',1),(33,'l, j F Y - g:ia','long',1),(34,'l, Y,  F j - g:ia','long',1),(35,'l, j. F Y - G:i','long',1);
/*!40000 ALTER TABLE `date_formats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_config`
--

DROP TABLE IF EXISTS `field_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The primary identifier for a field',
  `field_name` varchar(32) NOT NULL COMMENT 'The name of this field. Non-deleted field names are unique, but multiple deleted fields can have the same name.',
  `type` varchar(128) NOT NULL COMMENT 'The type of this field.',
  `module` varchar(128) NOT NULL DEFAULT '' COMMENT 'The module that implements the field type.',
  `active` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Boolean indicating whether the module that implements the field type is enabled.',
  `storage_type` varchar(128) NOT NULL COMMENT 'The storage backend for the field.',
  `storage_module` varchar(128) NOT NULL DEFAULT '' COMMENT 'The module that implements the storage backend.',
  `storage_active` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Boolean indicating whether the module that implements the storage backend is enabled.',
  `locked` tinyint(4) NOT NULL DEFAULT '0' COMMENT '@TODO',
  `data` longblob NOT NULL COMMENT 'Serialized data containing the field properties that do not warrant a dedicated column.',
  `cardinality` tinyint(4) NOT NULL DEFAULT '0',
  `translatable` tinyint(4) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_name` (`field_name`),
  KEY `active` (`active`),
  KEY `storage_active` (`storage_active`),
  KEY `deleted` (`deleted`),
  KEY `module` (`module`),
  KEY `storage_module` (`storage_module`),
  KEY `type` (`type`),
  KEY `storage_type` (`storage_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_config`
--

LOCK TABLES `field_config` WRITE;
/*!40000 ALTER TABLE `field_config` DISABLE KEYS */;
INSERT INTO `field_config` VALUES (1,'body','text_with_summary','text',1,'field_sql_storage','field_sql_storage',1,0,'a:5:{s:12:\"entity_types\";a:1:{i:0;s:4:\"node\";}s:12:\"translatable\";b:1;s:8:\"settings\";a:0:{}s:7:\"indexes\";a:1:{s:6:\"format\";a:1:{i:0;s:6:\"format\";}}s:7:\"storage\";a:4:{s:4:\"type\";s:17:\"field_sql_storage\";s:8:\"settings\";a:0:{}s:6:\"module\";s:17:\"field_sql_storage\";s:6:\"active\";i:1;}}',1,0,0),(3,'comment_body','text_long','text',1,'field_sql_storage','field_sql_storage',1,0,'a:5:{s:12:\"entity_types\";a:1:{i:0;s:7:\"comment\";}s:8:\"settings\";a:0:{}s:12:\"translatable\";b:0;s:7:\"indexes\";a:1:{s:6:\"format\";a:1:{i:0;s:6:\"format\";}}s:7:\"storage\";a:4:{s:4:\"type\";s:17:\"field_sql_storage\";s:8:\"settings\";a:0:{}s:6:\"module\";s:17:\"field_sql_storage\";s:6:\"active\";i:1;}}',1,0,0);
/*!40000 ALTER TABLE `field_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_config_instance`
--

DROP TABLE IF EXISTS `field_config_instance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_config_instance` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The primary identifier for a field instance',
  `field_id` int(11) NOT NULL COMMENT 'The identifier of the field attached by this instance',
  `field_name` varchar(32) NOT NULL DEFAULT '',
  `entity_type` varchar(32) NOT NULL DEFAULT '',
  `bundle` varchar(128) NOT NULL DEFAULT '',
  `data` longblob NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `field_name_bundle` (`field_name`,`entity_type`,`bundle`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_config_instance`
--

LOCK TABLES `field_config_instance` WRITE;
/*!40000 ALTER TABLE `field_config_instance` DISABLE KEYS */;
INSERT INTO `field_config_instance` VALUES (1,1,'body','node','page','a:6:{s:5:\"label\";s:4:\"Body\";s:11:\"description\";s:296:\"A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.\";s:8:\"required\";i:0;s:6:\"widget\";a:4:{s:4:\"type\";s:26:\"text_textarea_with_summary\";s:8:\"settings\";a:2:{s:4:\"rows\";i:20;s:12:\"summary_rows\";i:5;}s:6:\"weight\";i:-4;s:6:\"module\";s:4:\"text\";}s:8:\"settings\";a:1:{s:15:\"display_summary\";b:1;}s:7:\"display\";a:2:{s:7:\"default\";a:2:{s:5:\"label\";s:6:\"hidden\";s:4:\"type\";s:12:\"text_default\";}s:6:\"teaser\";a:3:{s:5:\"label\";s:6:\"hidden\";s:4:\"type\";s:23:\"text_summary_or_trimmed\";s:11:\"trim_length\";i:600;}}}',0),(2,1,'body','node','story','a:6:{s:5:\"label\";s:4:\"Body\";s:11:\"description\";s:392:\"A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.\";s:8:\"required\";i:0;s:6:\"widget\";a:4:{s:4:\"type\";s:26:\"text_textarea_with_summary\";s:8:\"settings\";a:2:{s:4:\"rows\";i:20;s:12:\"summary_rows\";i:5;}s:6:\"weight\";i:-4;s:6:\"module\";s:4:\"text\";}s:8:\"settings\";a:1:{s:15:\"display_summary\";b:1;}s:7:\"display\";a:2:{s:7:\"default\";a:2:{s:5:\"label\";s:6:\"hidden\";s:4:\"type\";s:12:\"text_default\";}s:6:\"teaser\";a:3:{s:5:\"label\";s:6:\"hidden\";s:4:\"type\";s:23:\"text_summary_or_trimmed\";s:11:\"trim_length\";i:600;}}}',0),(5,3,'comment_body','comment','comment_node_page','a:6:{s:5:\"label\";s:7:\"Comment\";s:8:\"settings\";a:1:{s:15:\"text_processing\";i:1;}s:8:\"required\";b:1;s:7:\"display\";a:1:{s:7:\"default\";a:5:{s:5:\"label\";s:6:\"hidden\";s:4:\"type\";s:12:\"text_default\";s:6:\"weight\";i:0;s:8:\"settings\";a:0:{}s:6:\"module\";s:4:\"text\";}}s:6:\"widget\";a:4:{s:4:\"type\";s:13:\"text_textarea\";s:8:\"settings\";a:1:{s:4:\"rows\";i:5;}s:6:\"weight\";i:0;s:6:\"module\";s:4:\"text\";}s:11:\"description\";s:0:\"\";}',0),(6,3,'comment_body','comment','comment_node_story','a:6:{s:5:\"label\";s:7:\"Comment\";s:8:\"settings\";a:1:{s:15:\"text_processing\";i:1;}s:8:\"required\";b:1;s:7:\"display\";a:1:{s:7:\"default\";a:5:{s:5:\"label\";s:6:\"hidden\";s:4:\"type\";s:12:\"text_default\";s:6:\"weight\";i:0;s:8:\"settings\";a:0:{}s:6:\"module\";s:4:\"text\";}}s:6:\"widget\";a:4:{s:4:\"type\";s:13:\"text_textarea\";s:8:\"settings\";a:1:{s:4:\"rows\";i:5;}s:6:\"weight\";i:0;s:6:\"module\";s:4:\"text\";}s:11:\"description\";s:0:\"\";}',0);
/*!40000 ALTER TABLE `field_config_instance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_data_body`
--

DROP TABLE IF EXISTS `field_data_body`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_data_body` (
  `entity_type` varchar(128) NOT NULL DEFAULT '' COMMENT 'The entity type this data is attached to',
  `bundle` varchar(128) NOT NULL DEFAULT '' COMMENT 'The field instance bundle to which this row belongs, used when deleting a field instance',
  `deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'A boolean indicating whether this data item has been deleted',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'The entity id this data is attached to',
  `revision_id` int(10) unsigned DEFAULT NULL COMMENT 'The entity revision id this data is attached to, or NULL if the entity type is not versioned',
  `language` varchar(32) NOT NULL DEFAULT '' COMMENT 'The language for this data item.',
  `delta` int(10) unsigned NOT NULL COMMENT 'The sequence number for this data item, used for multi-value fields',
  `body_value` longtext,
  `body_summary` longtext,
  `body_format` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`entity_type`,`entity_id`,`deleted`,`delta`,`language`),
  KEY `entity_type` (`entity_type`),
  KEY `bundle` (`bundle`),
  KEY `deleted` (`deleted`),
  KEY `entity_id` (`entity_id`),
  KEY `revision_id` (`revision_id`),
  KEY `language` (`language`),
  KEY `body_format` (`body_format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Data storage for field 1 (body)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_data_body`
--

LOCK TABLES `field_data_body` WRITE;
/*!40000 ALTER TABLE `field_data_body` DISABLE KEYS */;
INSERT INTO `field_data_body` VALUES ('node','page',0,1,1,'und',0,'',NULL,NULL),('node','page',0,2,2,'und',0,'<img src=\"/sites/default/themes/rayCivicrm/nyss_skin/images/seal-bluebird.png\" style=\"float:left;margin-left:10px;\" />\r\n<div style=\"float:left; margin-left:30px;width:700px;\">\r\n<p>The page you are trying to reach does not exist. Please check and make sure you have the correct URL. <br />\r\n<a href=\"/\" title=\"Bluebird Home\"><strong>Click here</strong></a> to return to the Bluebird Dashboard. </p>\r\n<p>If you feel this page was received in error, please copy the URL from your browser\'s address bar and email with additional details to your technical support staff.</p>\r\n</div>',NULL,'1');
/*!40000 ALTER TABLE `field_data_body` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_data_comment_body`
--

DROP TABLE IF EXISTS `field_data_comment_body`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_data_comment_body` (
  `entity_type` varchar(128) NOT NULL DEFAULT '' COMMENT 'The entity type this data is attached to',
  `bundle` varchar(128) NOT NULL DEFAULT '' COMMENT 'The field instance bundle to which this row belongs, used when deleting a field instance',
  `deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'A boolean indicating whether this data item has been deleted',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'The entity id this data is attached to',
  `revision_id` int(10) unsigned DEFAULT NULL COMMENT 'The entity revision id this data is attached to, or NULL if the entity type is not versioned',
  `language` varchar(32) NOT NULL DEFAULT '' COMMENT 'The language for this data item.',
  `delta` int(10) unsigned NOT NULL COMMENT 'The sequence number for this data item, used for multi-value fields',
  `comment_body_value` longtext,
  `comment_body_format` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`entity_type`,`entity_id`,`deleted`,`delta`,`language`),
  KEY `entity_type` (`entity_type`),
  KEY `bundle` (`bundle`),
  KEY `deleted` (`deleted`),
  KEY `entity_id` (`entity_id`),
  KEY `revision_id` (`revision_id`),
  KEY `language` (`language`),
  KEY `comment_body_format` (`comment_body_format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Data storage for field 3 (comment_body)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_data_comment_body`
--

LOCK TABLES `field_data_comment_body` WRITE;
/*!40000 ALTER TABLE `field_data_comment_body` DISABLE KEYS */;
/*!40000 ALTER TABLE `field_data_comment_body` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_revision_body`
--

DROP TABLE IF EXISTS `field_revision_body`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_revision_body` (
  `entity_type` varchar(128) NOT NULL DEFAULT '' COMMENT 'The entity type this data is attached to',
  `bundle` varchar(128) NOT NULL DEFAULT '' COMMENT 'The field instance bundle to which this row belongs, used when deleting a field instance',
  `deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'A boolean indicating whether this data item has been deleted',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'The entity id this data is attached to',
  `revision_id` int(10) unsigned NOT NULL COMMENT 'The entity revision id this data is attached to',
  `language` varchar(32) NOT NULL DEFAULT '' COMMENT 'The language for this data item.',
  `delta` int(10) unsigned NOT NULL COMMENT 'The sequence number for this data item, used for multi-value fields',
  `body_value` longtext,
  `body_summary` longtext,
  `body_format` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`entity_type`,`entity_id`,`revision_id`,`deleted`,`delta`,`language`),
  KEY `entity_type` (`entity_type`),
  KEY `bundle` (`bundle`),
  KEY `deleted` (`deleted`),
  KEY `entity_id` (`entity_id`),
  KEY `revision_id` (`revision_id`),
  KEY `language` (`language`),
  KEY `body_format` (`body_format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Revision archive storage for field 1 (body)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_revision_body`
--

LOCK TABLES `field_revision_body` WRITE;
/*!40000 ALTER TABLE `field_revision_body` DISABLE KEYS */;
INSERT INTO `field_revision_body` VALUES ('node','page',0,1,1,'und',0,'',NULL,NULL),('node','page',0,2,2,'und',0,'<img src=\"/sites/default/themes/rayCivicrm/nyss_skin/images/seal-bluebird.png\" style=\"float:left;margin-left:10px;\" />\r\n<div style=\"float:left; margin-left:30px;width:700px;\">\r\n<p>The page you are trying to reach does not exist. Please check and make sure you have the correct URL. <br />\r\n<a href=\"/\" title=\"Bluebird Home\"><strong>Click here</strong></a> to return to the Bluebird Dashboard. </p>\r\n<p>If you feel this page was received in error, please copy the URL from your browser\'s address bar and email with additional details to your technical support staff.</p>\r\n</div>',NULL,'1');
/*!40000 ALTER TABLE `field_revision_body` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_revision_comment_body`
--

DROP TABLE IF EXISTS `field_revision_comment_body`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_revision_comment_body` (
  `entity_type` varchar(128) NOT NULL DEFAULT '' COMMENT 'The entity type this data is attached to',
  `bundle` varchar(128) NOT NULL DEFAULT '' COMMENT 'The field instance bundle to which this row belongs, used when deleting a field instance',
  `deleted` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'A boolean indicating whether this data item has been deleted',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'The entity id this data is attached to',
  `revision_id` int(10) unsigned NOT NULL COMMENT 'The entity revision id this data is attached to',
  `language` varchar(32) NOT NULL DEFAULT '' COMMENT 'The language for this data item.',
  `delta` int(10) unsigned NOT NULL COMMENT 'The sequence number for this data item, used for multi-value fields',
  `comment_body_value` longtext,
  `comment_body_format` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`entity_type`,`entity_id`,`revision_id`,`deleted`,`delta`,`language`),
  KEY `entity_type` (`entity_type`),
  KEY `bundle` (`bundle`),
  KEY `deleted` (`deleted`),
  KEY `entity_id` (`entity_id`),
  KEY `revision_id` (`revision_id`),
  KEY `language` (`language`),
  KEY `comment_body_format` (`comment_body_format`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Revision archive storage for field 3 (comment_body)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_revision_comment_body`
--

LOCK TABLES `field_revision_comment_body` WRITE;
/*!40000 ALTER TABLE `field_revision_comment_body` DISABLE KEYS */;
/*!40000 ALTER TABLE `field_revision_comment_body` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_managed`
--

DROP TABLE IF EXISTS `file_managed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_managed` (
  `fid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'File ID.',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user.uid of the user who is associated with the file.',
  `filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Name of the file with no path components. This may differ from the basename of the URI if the file is renamed to avoid overwriting an existing file.',
  `uri` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'The URI to access the file (either local or remote).',
  `filemime` varchar(255) NOT NULL DEFAULT '' COMMENT 'The file’s MIME type.',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The size of the file in bytes.',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'A field indicating the status of the file. Two status are defined in core: temporary (0) and permanent (1). Temporary files older than DRUPAL_MAXIMUM_TEMP_FILE_AGE will be removed during a cron run.',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UNIX timestamp for when the file was added.',
  PRIMARY KEY (`fid`),
  UNIQUE KEY `uri` (`uri`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores information for uploaded files.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_managed`
--

LOCK TABLES `file_managed` WRITE;
/*!40000 ALTER TABLE `file_managed` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_managed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_usage`
--

DROP TABLE IF EXISTS `file_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_usage` (
  `fid` int(10) unsigned NOT NULL COMMENT 'File ID.',
  `module` varchar(255) NOT NULL DEFAULT '' COMMENT 'The name of the module that is using the file.',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT 'The name of the object type in which the file is used.',
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The primary key of the object using the file.',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The number of times this file is used by this object.',
  PRIMARY KEY (`fid`,`type`,`id`,`module`),
  KEY `type_id` (`type`,`id`),
  KEY `fid_count` (`fid`,`count`),
  KEY `fid_module` (`fid`,`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Track where a file is used.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_usage`
--

LOCK TABLES `file_usage` WRITE;
/*!40000 ALTER TABLE `file_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `fid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `filepath` varchar(255) NOT NULL DEFAULT '',
  `filemime` varchar(255) NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filter`
--

DROP TABLE IF EXISTS `filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filter` (
  `format` varchar(255) NOT NULL COMMENT 'Foreign key: The filter_format.format to which this filter is assigned.',
  `module` varchar(64) NOT NULL DEFAULT '' COMMENT 'The origin module of the filter.',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT 'Name of the filter being referenced.',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT 'Weight of filter within format.',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT 'Filter enabled status. (1 = enabled, 0 = disabled)',
  `settings` longblob COMMENT 'A serialized array of name value pairs that store the filter settings for the specific format.',
  PRIMARY KEY (`format`,`name`),
  KEY `list` (`weight`,`module`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table that maps filters (HTML corrector) to text formats ...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filter`
--

LOCK TABLES `filter` WRITE;
/*!40000 ALTER TABLE `filter` DISABLE KEYS */;
INSERT INTO `filter` VALUES ('1','filter','filter_autop',2,1,'a:0:{}'),('1','filter','filter_html',1,1,'a:0:{}'),('1','filter','filter_htmlcorrector',10,1,'a:0:{}'),('1','filter','filter_url',0,1,'a:0:{}'),('2','filter','filter_autop',1,1,'a:0:{}'),('2','filter','filter_htmlcorrector',10,1,'a:0:{}'),('2','filter','filter_url',0,1,'a:0:{}'),('3','filter','filter_autop',2,1,'a:0:{}'),('3','filter','filter_html_escape',0,1,'a:0:{}'),('3','filter','filter_url',1,1,'a:0:{}');
/*!40000 ALTER TABLE `filter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filter_format`
--

DROP TABLE IF EXISTS `filter_format`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filter_format` (
  `format` varchar(255) NOT NULL COMMENT 'Primary Key: Unique machine name of the format.',
  `name` varchar(255) NOT NULL DEFAULT '',
  `cache` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'The status of the text format. (1 = enabled, 0 = disabled)',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT 'Weight of text format to use when listing.',
  PRIMARY KEY (`format`),
  UNIQUE KEY `name` (`name`),
  KEY `status_weight` (`status`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filter_format`
--

LOCK TABLES `filter_format` WRITE;
/*!40000 ALTER TABLE `filter_format` DISABLE KEYS */;
INSERT INTO `filter_format` VALUES ('1','Filtered HTML',1,1,-1),('2','Full HTML',1,1,0),('3','Plain text',1,1,1);
/*!40000 ALTER TABLE `filter_format` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flood`
--

DROP TABLE IF EXISTS `flood`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flood` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(64) NOT NULL DEFAULT '',
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `expiration` int(11) NOT NULL DEFAULT '0' COMMENT 'Expiration timestamp. Expired events are purged on cron run.',
  PRIMARY KEY (`fid`),
  KEY `allow` (`event`,`identifier`,`timestamp`),
  KEY `purge` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flood`
--

LOCK TABLES `flood` WRITE;
/*!40000 ALTER TABLE `flood` DISABLE KEYS */;
/*!40000 ALTER TABLE `flood` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `front_page`
--

DROP TABLE IF EXISTS `front_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `front_page` (
  `rid` int(10) unsigned NOT NULL COMMENT 'Primary Key: Role ID.',
  `mode` varchar(10) NOT NULL DEFAULT '' COMMENT 'The mode the front page will operate in for the selected role.',
  `data` longtext NOT NULL COMMENT 'Contains the data for the selected mode. This could be a path or html to display.',
  `filter_format` varchar(255) NOT NULL DEFAULT '' COMMENT 'The filter format to apply to the data.',
  `weight` int(11) DEFAULT '0' COMMENT 'The weight of the front page setting.',
  PRIMARY KEY (`rid`),
  KEY `weight` (`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores Front Page settings.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `front_page`
--

LOCK TABLES `front_page` WRITE;
/*!40000 ALTER TABLE `front_page` DISABLE KEYS */;
INSERT INTO `front_page` VALUES (1,'themed','','1',-1),(2,'redirect','civicrm/dashboard?reset=1','',-2),(3,'redirect','civicrm/dashboard?reset=1','',-3);
/*!40000 ALTER TABLE `front_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `nid` int(11) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`nid`),
  KEY `nid` (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imce_files`
--

DROP TABLE IF EXISTS `imce_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imce_files` (
  `fid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imce_files`
--

LOCK TABLES `imce_files` WRITE;
/*!40000 ALTER TABLE `imce_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `imce_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ldap_authorization`
--

DROP TABLE IF EXISTS `ldap_authorization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ldap_authorization` (
  `numeric_consumer_conf_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary ID field for the table.  Only used internally.',
  `sid` varchar(20) NOT NULL,
  `consumer_type` varchar(20) NOT NULL,
  `consumer_module` varchar(30) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `only_ldap_authenticated` tinyint(4) NOT NULL DEFAULT '1',
  `derive_from_dn` tinyint(4) NOT NULL DEFAULT '0',
  `derive_from_dn_attr` text,
  `derive_from_attr` tinyint(4) NOT NULL DEFAULT '0',
  `derive_from_attr_attr` text,
  `derive_from_attr_use_first_attr` tinyint(4) NOT NULL DEFAULT '0',
  `derive_from_attr_nested` tinyint(4) NOT NULL DEFAULT '0',
  `derive_from_entry` tinyint(4) NOT NULL DEFAULT '0',
  `derive_from_entry_nested` tinyint(4) NOT NULL DEFAULT '0',
  `derive_from_entry_entries` text,
  `derive_from_entry_entries_attr` varchar(255) DEFAULT NULL,
  `derive_from_entry_attr` varchar(255) DEFAULT NULL,
  `derive_from_entry_search_all` tinyint(4) NOT NULL DEFAULT '0',
  `derive_from_entry_use_first_attr` tinyint(4) NOT NULL DEFAULT '0',
  `derive_from_entry_user_ldap_attr` varchar(255) DEFAULT NULL,
  `mappings` text,
  `use_filter` tinyint(4) NOT NULL DEFAULT '1',
  `synch_to_ldap` tinyint(4) NOT NULL DEFAULT '0',
  `synch_on_logon` tinyint(4) NOT NULL DEFAULT '0',
  `revoke_ldap_provisioned` tinyint(4) NOT NULL DEFAULT '0',
  `create_consumers` tinyint(4) NOT NULL DEFAULT '0',
  `regrant_ldap_provisioned` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`numeric_consumer_conf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Data used to map users ldap entry to authorization rights.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ldap_authorization`
--

LOCK TABLES `ldap_authorization` WRITE;
/*!40000 ALTER TABLE `ldap_authorization` DISABLE KEYS */;
INSERT INTO `ldap_authorization` VALUES (1,'nyss_ldap','drupal_role','ldap_authorization_drupal_role',1,1,0,'',0,'',0,0,1,0,'CRMAnalytics\nCRMAdministrator\nCRMOfficeAdministrator\nCRMOfficeDataEntry\nCRMOfficeManager\nCRMOfficeStaff\nCRMOfficeVolunteer\nCRMPrintProduction\nCRMSOS','cn','member',0,0,'dn','CRMAnalytics|Analytics User\nCRMAdministrator|Administrator\nCRMOfficeAdministrator|Office Administrator\nCRMOfficeDataEntry|Data Entry\nCRMOfficeManager|Office Manager\nCRMOfficeStaff|Staff\nCRMOfficeVolunteer|Volunteer\nCRMPrintProduction|Print Production\nCRMSOS|SOS\nCRMDConferenceServices|Conference Services\nCRMRConferenceServices|Conference Services\n',1,0,1,1,0,1);
/*!40000 ALTER TABLE `ldap_authorization` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ldap_servers`
--

DROP TABLE IF EXISTS `ldap_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ldap_servers` (
  `sid` varchar(20) NOT NULL,
  `numeric_sid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary ID field for the table.  Only used internally.',
  `name` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `ldap_type` varchar(20) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `port` int(11) NOT NULL DEFAULT '389',
  `tls` tinyint(4) NOT NULL DEFAULT '0',
  `bind_method` tinyint(4) NOT NULL DEFAULT '0',
  `binddn` varchar(511) DEFAULT NULL,
  `bindpw` varchar(255) DEFAULT NULL,
  `basedn` text,
  `user_attr` varchar(255) NOT NULL,
  `account_name_attr` varchar(255) DEFAULT '',
  `mail_attr` varchar(255) DEFAULT NULL,
  `mail_template` varchar(255) DEFAULT NULL,
  `allow_conflicting_drupal_accts` tinyint(4) DEFAULT '0',
  `unique_persistent_attr` varchar(64) DEFAULT NULL,
  `user_dn_expression` varchar(255) DEFAULT NULL,
  `ldap_to_drupal_user` varchar(1024) DEFAULT NULL,
  `testing_drupal_username` varchar(255) DEFAULT NULL,
  `group_object_category` varchar(64) DEFAULT NULL,
  `search_pagination` tinyint(4) DEFAULT '0',
  `search_page_size` mediumint(9) DEFAULT '1000',
  `weight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`numeric_sid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ldap_servers`
--

LOCK TABLES `ldap_servers` WRITE;
/*!40000 ALTER TABLE `ldap_servers` DISABLE KEYS */;
INSERT INTO `ldap_servers` VALUES ('nyss_ldap',1,'NY Senate LDAP Server',1,'openldap','webmail.nysenate.gov',389,0,4,'','','a:1:{i:0;s:0:\"\";}','uid','','mail','',0,'','','','','groupOfNames',0,1000,0);
/*!40000 ALTER TABLE `ldap_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_custom`
--

DROP TABLE IF EXISTS `menu_custom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_custom` (
  `menu_name` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  PRIMARY KEY (`menu_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_custom`
--

LOCK TABLES `menu_custom` WRITE;
/*!40000 ALTER TABLE `menu_custom` DISABLE KEYS */;
INSERT INTO `menu_custom` VALUES ('main-menu','Main menu','Primary links are often used at the theme layer to show the major sections of a site. A typical representation for primary links would be tabs along the top.'),('management','Management','The <em>Management</em> menu contains links for administrative tasks.'),('navigation','Navigation','The navigation menu is provided by Drupal and is the main interactive menu for any site. It is usually the only menu that contains personalized links for authenticated users, and is often not even visible to anonymous users.'),('secondary-menu','Secondary menu','Secondary links are often used for pages like legal notices, contact details, and other secondary navigation items that play a lesser role than primary links'),('user-menu','User Menu','The <em>User</em> menu contains links related to the user\'s account, as well as the \'Log out\' link.');
/*!40000 ALTER TABLE `menu_custom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_links`
--

DROP TABLE IF EXISTS `menu_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_links` (
  `menu_name` varchar(32) NOT NULL DEFAULT '',
  `mlid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plid` int(10) unsigned NOT NULL DEFAULT '0',
  `link_path` varchar(255) NOT NULL DEFAULT '',
  `router_path` varchar(255) NOT NULL DEFAULT '',
  `link_title` varchar(255) NOT NULL DEFAULT '',
  `options` blob COMMENT 'A serialized array of options to be passed to the url() or l() function, such as a query string or HTML attributes.',
  `module` varchar(255) NOT NULL DEFAULT 'system',
  `hidden` smallint(6) NOT NULL DEFAULT '0',
  `external` smallint(6) NOT NULL DEFAULT '0',
  `has_children` smallint(6) NOT NULL DEFAULT '0',
  `expanded` smallint(6) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '0',
  `depth` smallint(6) NOT NULL DEFAULT '0',
  `customized` smallint(6) NOT NULL DEFAULT '0',
  `p1` int(10) unsigned NOT NULL DEFAULT '0',
  `p2` int(10) unsigned NOT NULL DEFAULT '0',
  `p3` int(10) unsigned NOT NULL DEFAULT '0',
  `p4` int(10) unsigned NOT NULL DEFAULT '0',
  `p5` int(10) unsigned NOT NULL DEFAULT '0',
  `p6` int(10) unsigned NOT NULL DEFAULT '0',
  `p7` int(10) unsigned NOT NULL DEFAULT '0',
  `p8` int(10) unsigned NOT NULL DEFAULT '0',
  `p9` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mlid`),
  KEY `path_menu` (`link_path`(128),`menu_name`),
  KEY `menu_plid_expand_child` (`menu_name`,`plid`,`expanded`,`has_children`),
  KEY `menu_parents` (`menu_name`,`p1`,`p2`,`p3`,`p4`,`p5`,`p6`,`p7`,`p8`,`p9`),
  KEY `router_path` (`router_path`(128))
) ENGINE=InnoDB AUTO_INCREMENT=613 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_links`
--

LOCK TABLES `menu_links` WRITE;
/*!40000 ALTER TABLE `menu_links` DISABLE KEYS */;
INSERT INTO `menu_links` VALUES ('management',2,0,'admin','admin','Administration','a:0:{}','system',0,0,1,0,9,1,0,2,0,0,0,0,0,0,0,0,0),('user-menu',4,0,'user/logout','user/logout','Log out','a:0:{}','system',0,0,0,0,10,1,0,4,0,0,0,0,0,0,0,0,0),('navigation',9,0,'filter/tips','filter/tips','Compose tips','a:0:{}','system',1,0,0,0,0,1,0,9,0,0,0,0,0,0,0,0,0),('management',10,2,'admin/content','admin/content','Content','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:24:\"Find and manage content.\";}}','system',0,0,0,0,-10,2,0,2,10,0,0,0,0,0,0,0,0),('navigation',11,0,'node/add','node/add','Add content','a:0:{}','system',0,0,1,0,0,1,0,11,0,0,0,0,0,0,0,0,0),('management',16,2,'admin/reports','admin/reports','Reports','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:34:\"View reports, updates, and errors.\";}}','system',0,0,1,0,5,2,0,2,16,0,0,0,0,0,0,0,0),('navigation',21,0,'user/%','user/%','My account','a:0:{}','system',0,0,1,0,0,1,0,21,0,0,0,0,0,0,0,0,0),('management',28,10,'admin/content/node','admin/content/node','Content','a:0:{}','system',-1,0,0,0,-10,3,0,2,10,28,0,0,0,0,0,0,0),('management',48,16,'admin/reports/status','admin/reports/status','Status report','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:74:\"Get a status report about your site\'s operation and any detected problems.\";}}','system',0,0,0,0,-60,3,0,2,16,48,0,0,0,0,0,0,0),('navigation',106,11,'node/add/page','node/add/page','Page','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:296:\"A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.\";}}','system',0,0,0,0,0,2,0,11,106,0,0,0,0,0,0,0,0),('navigation',107,11,'node/add/story','node/add/story','Story','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:392:\"A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.\";}}','system',0,0,0,0,0,2,0,11,107,0,0,0,0,0,0,0,0),('navigation',114,0,'search','search','Search','a:0:{}','system',1,0,0,0,0,1,0,114,0,0,0,0,0,0,0,0,0),('navigation',127,0,'0','','','a:0:{}','system',0,1,0,0,0,1,0,127,0,0,0,0,0,0,0,0,0),('admin_menu',141,0,'<front>','','<img class=\"admin-menu-icon\" src=\"/nyss/misc/favicon.ico\" width=\"16\" height=\"16\" alt=\"Home\" />','a:3:{s:11:\"extra class\";s:15:\"admin-menu-icon\";s:4:\"html\";b:1;s:5:\"alter\";b:1;}','admin_menu',0,1,1,0,-100,1,0,141,0,0,0,0,0,0,0,0,0),('admin_menu',142,0,'user/logout','user/logout','Log out @username','a:3:{s:11:\"extra class\";s:35:\"admin-menu-action admin-menu-logout\";s:1:\"t\";a:0:{}s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-100,1,0,142,0,0,0,0,0,0,0,0,0),('admin_menu',143,0,'user','user','icon_users','a:3:{s:11:\"extra class\";s:50:\"admin-menu-action admin-menu-icon admin-menu-users\";s:4:\"html\";b:1;s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-90,1,0,143,0,0,0,0,0,0,0,0,0),('admin_menu',144,0,'admin/content','admin/content','Content management','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,-10,1,0,144,0,0,0,0,0,0,0,0,0),('admin_menu',146,0,'admin/reports','admin/reports','Reports','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,5,1,0,146,0,0,0,0,0,0,0,0,0),('admin_menu',160,144,'admin/content/node','admin/content/node','Content','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,144,160,0,0,0,0,0,0,0,0),('admin_menu',179,146,'admin/reports/status','admin/reports/status','Status report','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,10,2,0,146,179,0,0,0,0,0,0,0,0),('admin_menu',235,141,'admin/reports/status/run-cron','admin/reports/status/run-cron','Run cron','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,50,2,0,141,235,0,0,0,0,0,0,0,0),('admin_menu',238,141,'http://drupal.org','','Drupal.org','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,1,0,100,2,0,141,238,0,0,0,0,0,0,0,0),('admin_menu',239,238,'http://drupal.org/project/issues/drupal','','Drupal issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,-10,3,0,141,238,239,0,0,0,0,0,0,0),('admin_menu',240,238,'http://drupal.org/project/issues/admin_menu','','Administration menu issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,240,0,0,0,0,0,0,0),('admin_menu',241,238,'http://drupal.org/project/issues/apachesolr','','Apache Solr framework issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,241,0,0,0,0,0,0,0),('admin_menu',242,238,'http://drupal.org/project/issues/cacherouter','','CacheRouter issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,242,0,0,0,0,0,0,0),('admin_menu',243,144,'node/add','node/add','Create content','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,144,243,0,0,0,0,0,0,0,0),('admin_menu',244,243,'node/add/page','node/add/page','Page','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,243,244,0,0,0,0,0,0,0),('admin_menu',245,243,'node/add/story','node/add/story','Story','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,243,245,0,0,0,0,0,0,0),('navigation',277,0,'front_page','front_page','','a:0:{}','system',1,0,0,0,0,1,0,277,0,0,0,0,0,0,0,0,0),('admin_menu',287,238,'http://drupal.org/project/issues/front','','Front Page issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,287,0,0,0,0,0,0,0),('admin_menu',324,238,'http://drupal.org/project/issues/ldap_integration','','Authentication issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,324,0,0,0,0,0,0,0),('user-menu',374,0,'user','user','User account','a:1:{s:5:\"alter\";b:1;}','system',0,0,0,0,-10,1,0,374,0,0,0,0,0,0,0,0,0),('navigation',375,0,'node/%','node/%','','a:0:{}','system',0,0,0,0,0,1,0,375,0,0,0,0,0,0,0,0,0),('management',376,2,'admin/appearance','admin/appearance','Appearance','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:33:\"Select and configure your themes.\";}}','system',0,0,0,0,-6,2,0,2,376,0,0,0,0,0,0,0,0),('management',377,2,'admin/config','admin/config','Configuration','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:20:\"Administer settings.\";}}','system',0,0,1,0,0,2,0,2,377,0,0,0,0,0,0,0,0),('navigation',378,114,'search/node','search/node','Content','a:0:{}','system',-1,0,0,0,0,2,0,114,378,0,0,0,0,0,0,0,0),('user-menu',379,374,'user/register','user/register','Create new account','a:0:{}','system',-1,0,0,0,0,2,0,374,379,0,0,0,0,0,0,0,0),('management',380,2,'admin/index','admin/index','Index','a:0:{}','system',-1,0,0,0,-18,2,0,2,380,0,0,0,0,0,0,0,0),('user-menu',381,374,'user/login','user/login','Log in','a:0:{}','system',-1,0,0,0,0,2,0,374,381,0,0,0,0,0,0,0,0),('management',382,2,'admin/modules','admin/modules','Modules','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:26:\"Extend site functionality.\";}}','system',0,0,0,0,-2,2,0,2,382,0,0,0,0,0,0,0,0),('management',383,2,'admin/people','admin/people','People','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:45:\"Manage user accounts, roles, and permissions.\";}}','system',0,0,0,0,-4,2,0,2,383,0,0,0,0,0,0,0,0),('user-menu',384,374,'user/password','user/password','Request new password','a:0:{}','system',-1,0,0,0,0,2,0,374,384,0,0,0,0,0,0,0,0),('management',385,2,'admin/structure','admin/structure','Structure','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:45:\"Administer blocks, content types, menus, etc.\";}}','system',0,0,1,0,-8,2,0,2,385,0,0,0,0,0,0,0,0),('management',386,2,'admin/tasks','admin/tasks','Tasks','a:0:{}','system',-1,0,0,0,-20,2,0,2,386,0,0,0,0,0,0,0,0),('navigation',387,114,'search/user','search/user','Users','a:0:{}','system',-1,0,0,0,0,2,0,114,387,0,0,0,0,0,0,0,0),('management',388,383,'admin/people/create','admin/people/create','Add user','a:0:{}','system',-1,0,0,0,0,3,0,2,383,388,0,0,0,0,0,0,0),('management',389,385,'admin/structure/block','admin/structure/block','Blocks','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:79:\"Configure what block content appears in your site\'s sidebars and other regions.\";}}','system',0,0,1,0,0,3,0,2,385,389,0,0,0,0,0,0,0),('navigation',390,21,'user/%/cancel','user/%/cancel','Cancel account','a:0:{}','system',0,0,1,0,0,2,0,21,390,0,0,0,0,0,0,0,0),('management',392,377,'admin/config/content','admin/config/content','Content authoring','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:53:\"Settings related to formatting and authoring content.\";}}','system',0,0,1,0,-15,3,0,2,377,392,0,0,0,0,0,0,0),('management',393,385,'admin/structure/types','admin/structure/types','Content types','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:92:\"Manage content types, including default status, front page promotion, comment settings, etc.\";}}','system',0,0,1,0,0,3,0,2,385,393,0,0,0,0,0,0,0),('navigation',394,375,'node/%/delete','node/%/delete','Delete','a:0:{}','system',-1,0,0,0,1,2,0,375,394,0,0,0,0,0,0,0,0),('management',395,377,'admin/config/development','admin/config/development','Development','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:18:\"Development tools.\";}}','system',0,0,1,0,-10,3,0,2,377,395,0,0,0,0,0,0,0),('navigation',396,21,'user/%/edit','user/%/edit','Edit','a:0:{}','system',-1,0,0,0,0,2,0,21,396,0,0,0,0,0,0,0,0),('navigation',397,375,'node/%/edit','node/%/edit','Edit','a:0:{}','system',-1,0,0,0,0,2,0,375,397,0,0,0,0,0,0,0,0),('management',398,16,'admin/reports/fields','admin/reports/fields','Field list','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:39:\"Overview of fields on all entity types.\";}}','system',0,0,0,0,0,3,0,2,16,398,0,0,0,0,0,0,0),('management',399,377,'admin/config/front','admin/config/front','Front Page','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:21:\"Configure front page.\";}}','system',0,0,0,0,-15,3,0,2,377,399,0,0,0,0,0,0,0),('management',400,376,'admin/appearance/list','admin/appearance/list','List','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:31:\"Select and configure your theme\";}}','system',-1,0,0,0,-1,3,0,2,376,400,0,0,0,0,0,0,0),('management',401,382,'admin/modules/list','admin/modules/list','List','a:0:{}','system',-1,0,0,0,0,3,0,2,382,401,0,0,0,0,0,0,0),('management',402,383,'admin/people/people','admin/people/people','List','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:50:\"Find and manage people interacting with your site.\";}}','system',-1,0,0,0,-10,3,0,2,383,402,0,0,0,0,0,0,0),('management',403,377,'admin/config/media','admin/config/media','Media','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:12:\"Media tools.\";}}','system',0,0,1,0,-10,3,0,2,377,403,0,0,0,0,0,0,0),('management',404,385,'admin/structure/menu','admin/structure/menu','Menus','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:86:\"Add new menus to your site, edit existing menus, and rename and reorganize menu links.\";}}','system',0,0,1,0,0,3,0,2,385,404,0,0,0,0,0,0,0),('management',405,377,'admin/config/people','admin/config/people','People','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:24:\"Configure user accounts.\";}}','system',0,0,1,0,-20,3,0,2,377,405,0,0,0,0,0,0,0),('management',406,383,'admin/people/permissions','admin/people/permissions','Permissions','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:64:\"Determine access to features by selecting permissions for roles.\";}}','system',-1,0,1,0,0,3,0,2,383,406,0,0,0,0,0,0,0),('management',407,377,'admin/config/regional','admin/config/regional','Regional and language','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:48:\"Regional settings, localization and translation.\";}}','system',0,0,1,0,-5,3,0,2,377,407,0,0,0,0,0,0,0),('navigation',408,375,'node/%/revisions','node/%/revisions','Revisions','a:0:{}','system',-1,0,1,0,2,2,0,375,408,0,0,0,0,0,0,0,0),('management',409,377,'admin/config/search','admin/config/search','Search and metadata','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:36:\"Local site search, metadata and SEO.\";}}','system',0,0,1,0,-10,3,0,2,377,409,0,0,0,0,0,0,0),('management',410,376,'admin/appearance/settings','admin/appearance/settings','Settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:46:\"Configure default and theme specific settings.\";}}','system',-1,0,0,0,20,3,0,2,376,410,0,0,0,0,0,0,0),('management',411,377,'admin/config/system','admin/config/system','System','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:37:\"General system related configuration.\";}}','system',0,0,1,0,-20,3,0,2,377,411,0,0,0,0,0,0,0),('management',412,385,'admin/structure/trigger','admin/structure/trigger','Triggers','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:34:\"Configure when to execute actions.\";}}','system',0,0,0,0,0,3,0,2,385,412,0,0,0,0,0,0,0),('management',413,382,'admin/modules/uninstall','admin/modules/uninstall','Uninstall','a:0:{}','system',-1,0,0,0,20,3,0,2,382,413,0,0,0,0,0,0,0),('management',414,377,'admin/config/user-interface','admin/config/user-interface','User interface','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:38:\"Tools that enhance the user interface.\";}}','system',0,0,0,0,-15,3,0,2,377,414,0,0,0,0,0,0,0),('navigation',415,375,'node/%/view','node/%/view','View','a:0:{}','system',-1,0,0,0,-10,2,0,375,415,0,0,0,0,0,0,0,0),('navigation',416,21,'user/%/view','user/%/view','View','a:0:{}','system',-1,0,0,0,-10,2,0,21,416,0,0,0,0,0,0,0,0),('management',417,377,'admin/config/services','admin/config/services','Web services','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:30:\"Tools related to web services.\";}}','system',0,0,1,0,0,3,0,2,377,417,0,0,0,0,0,0,0),('management',418,377,'admin/config/workflow','admin/config/workflow','Workflow','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:43:\"Content workflow, editorial workflow tools.\";}}','system',0,0,1,0,5,3,0,2,377,418,0,0,0,0,0,0,0),('navigation',419,378,'search/node/%','search/node/%','Content','a:0:{}','system',-1,0,0,0,0,3,0,114,378,419,0,0,0,0,0,0,0),('navigation',420,387,'search/user/%','search/user/%','Users','a:0:{}','system',-1,0,0,0,0,3,0,114,387,420,0,0,0,0,0,0,0),('management',421,405,'admin/config/people/accounts','admin/config/people/accounts','Account settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:109:\"Configure default behavior of users, including registration requirements, e-mails, fields, and user pictures.\";}}','system',0,0,0,0,-10,4,0,2,377,405,421,0,0,0,0,0,0),('management',422,411,'admin/config/system/actions','admin/config/system/actions','Actions','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:41:\"Manage the actions defined for your site.\";}}','system',0,0,1,0,0,4,0,2,377,411,422,0,0,0,0,0,0),('management',423,389,'admin/structure/block/add','admin/structure/block/add','Add block','a:0:{}','system',-1,0,0,0,0,4,0,2,385,389,423,0,0,0,0,0,0),('management',424,393,'admin/structure/types/add','admin/structure/types/add','Add content type','a:0:{}','system',-1,0,0,0,0,4,0,2,385,393,424,0,0,0,0,0,0),('management',425,404,'admin/structure/menu/add','admin/structure/menu/add','Add menu','a:0:{}','system',-1,0,0,0,0,4,0,2,385,404,425,0,0,0,0,0,0),('management',426,399,'admin/config/front/arrange','admin/config/front/arrange','Arrange','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:64:\"Ability to re-arrange what order front page roles are processed.\";}}','system',-1,0,0,0,1,4,0,2,377,399,426,0,0,0,0,0,0),('management',427,410,'admin/appearance/settings/bartik','admin/appearance/settings/bartik','Bartik','a:0:{}','system',-1,0,0,0,0,4,0,2,376,410,427,0,0,0,0,0,0),('management',428,410,'admin/appearance/settings/Bluebird','admin/appearance/settings/Bluebird','Bluebird','a:0:{}','system',-1,0,0,0,0,4,0,2,376,410,428,0,0,0,0,0,0),('management',429,409,'admin/config/search/clean-urls','admin/config/search/clean-urls','Clean URLs','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:43:\"Enable or disable clean URLs for your site.\";}}','system',0,0,0,0,5,4,0,2,377,409,429,0,0,0,0,0,0),('management',430,411,'admin/config/system/cron','admin/config/system/cron','Cron','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:40:\"Manage automatic site maintenance tasks.\";}}','system',0,0,0,0,20,4,0,2,377,411,430,0,0,0,0,0,0),('management',431,407,'admin/config/regional/date-time','admin/config/regional/date-time','Date and time','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:44:\"Configure display formats for date and time.\";}}','system',0,0,0,0,-15,4,0,2,377,407,431,0,0,0,0,0,0),('management',432,403,'admin/config/media/file-system','admin/config/media/file-system','File system','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:68:\"Tell Drupal where to store uploaded files and how they are accessed.\";}}','system',0,0,0,0,-10,4,0,2,377,403,432,0,0,0,0,0,0),('management',433,410,'admin/appearance/settings/garland','admin/appearance/settings/garland','Garland','a:0:{}','system',-1,0,0,0,0,4,0,2,376,410,433,0,0,0,0,0,0),('management',434,410,'admin/appearance/settings/global','admin/appearance/settings/global','Global settings','a:0:{}','system',-1,0,0,0,-1,4,0,2,376,410,434,0,0,0,0,0,0),('management',435,399,'admin/config/front/home-links','admin/config/front/home-links','Home links','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:67:\"Allows you to change the location of the &lt;front&gt; placeholder.\";}}','system',-1,0,0,0,2,4,0,2,377,399,435,0,0,0,0,0,0),('management',436,405,'admin/config/people/ip-blocking','admin/config/people/ip-blocking','IP address blocking','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:28:\"Manage blocked IP addresses.\";}}','system',0,0,1,0,10,4,0,2,377,405,436,0,0,0,0,0,0),('management',437,403,'admin/config/media/image-toolkit','admin/config/media/image-toolkit','Image toolkit','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:74:\"Choose which image toolkit to use if you have installed optional toolkits.\";}}','system',0,0,0,0,20,4,0,2,377,403,437,0,0,0,0,0,0),('management',438,401,'admin/modules/list/confirm','admin/modules/list/confirm','List','a:0:{}','system',-1,0,0,0,0,4,0,2,382,401,438,0,0,0,0,0,0),('management',439,393,'admin/structure/types/list','admin/structure/types/list','List','a:0:{}','system',-1,0,0,0,-10,4,0,2,385,393,439,0,0,0,0,0,0),('management',440,404,'admin/structure/menu/list','admin/structure/menu/list','List menus','a:0:{}','system',-1,0,0,0,-10,4,0,2,385,404,440,0,0,0,0,0,0),('management',441,395,'admin/config/development/logging','admin/config/development/logging','Logging and errors','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:154:\"Settings for logging and alerts modules. Various modules can route Drupal\'s system events to different destinations, such as syslog, database, email, etc.\";}}','system',0,0,0,0,-15,4,0,2,377,395,441,0,0,0,0,0,0),('management',442,395,'admin/config/development/maintenance','admin/config/development/maintenance','Maintenance mode','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:62:\"Take the site offline for maintenance or bring it back online.\";}}','system',0,0,0,0,-10,4,0,2,377,395,442,0,0,0,0,0,0),('management',443,412,'admin/structure/trigger/node','admin/structure/trigger/node','Node','a:0:{}','system',-1,0,0,0,0,4,0,2,385,412,443,0,0,0,0,0,0),('management',444,395,'admin/config/development/performance','admin/config/development/performance','Performance','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:101:\"Enable or disable page caching for anonymous users and set CSS and JS bandwidth optimization options.\";}}','system',0,0,0,0,-20,4,0,2,377,395,444,0,0,0,0,0,0),('management',445,406,'admin/people/permissions/list','admin/people/permissions/list','Permissions','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:64:\"Determine access to features by selecting permissions for roles.\";}}','system',-1,0,0,0,-8,4,0,2,383,406,445,0,0,0,0,0,0),('management',446,417,'admin/config/services/rss-publishing','admin/config/services/rss-publishing','RSS publishing','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:114:\"Configure the site description, the number of items per feed and whether feeds should be titles/teasers/full-text.\";}}','system',0,0,0,0,0,4,0,2,377,417,446,0,0,0,0,0,0),('management',447,407,'admin/config/regional/settings','admin/config/regional/settings','Regional settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:54:\"Settings for the site\'s default time zone and country.\";}}','system',0,0,0,0,-20,4,0,2,377,407,447,0,0,0,0,0,0),('management',448,406,'admin/people/permissions/roleassign','admin/people/permissions/roleassign','Role assign','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:90:\"Define the set of roles that can be assigned by admins with the \'Assign roles\' permission.\";}}','system',0,0,0,0,0,4,0,2,383,406,448,0,0,0,0,0,0),('management',449,406,'admin/people/permissions/roles','admin/people/permissions/roles','Roles','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:30:\"List, edit, or add user roles.\";}}','system',-1,0,1,0,-5,4,0,2,383,406,449,0,0,0,0,0,0),('management',450,409,'admin/config/search/settings','admin/config/search/settings','Search settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:67:\"Configure relevance settings for search and other indexing options.\";}}','system',0,0,0,0,-10,4,0,2,377,409,450,0,0,0,0,0,0),('management',451,404,'admin/structure/menu/settings','admin/structure/menu/settings','Settings','a:0:{}','system',-1,0,0,0,5,4,0,2,385,404,451,0,0,0,0,0,0),('management',452,399,'admin/config/front/settings','admin/config/front/settings','Settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:38:\"Administer custom front page settings.\";}}','system',-1,0,0,0,0,4,0,2,377,399,452,0,0,0,0,0,0),('management',453,410,'admin/appearance/settings/seven','admin/appearance/settings/seven','Seven','a:0:{}','system',-1,0,0,0,0,4,0,2,376,410,453,0,0,0,0,0,0),('management',454,411,'admin/config/system/site-information','admin/config/system/site-information','Site information','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:104:\"Change site name, e-mail address, slogan, default front page, and number of posts per page, error pages.\";}}','system',0,0,0,0,-20,4,0,2,377,411,454,0,0,0,0,0,0),('management',455,410,'admin/appearance/settings/stark','admin/appearance/settings/stark','Stark','a:0:{}','system',-1,0,0,0,0,4,0,2,376,410,455,0,0,0,0,0,0),('management',456,412,'admin/structure/trigger/system','admin/structure/trigger/system','System','a:0:{}','system',-1,0,0,0,0,4,0,2,385,412,456,0,0,0,0,0,0),('management',457,392,'admin/config/content/formats','admin/config/content/formats','Text formats','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:127:\"Configure how content input by users is filtered, including allowed HTML tags. Also allows enabling of module-provided filters.\";}}','system',0,0,1,0,0,4,0,2,377,392,457,0,0,0,0,0,0),('management',458,412,'admin/structure/trigger/unassign','admin/structure/trigger/unassign','Unassign','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:34:\"Unassign an action from a trigger.\";}}','system',-1,0,0,0,0,4,0,2,385,412,458,0,0,0,0,0,0),('management',459,413,'admin/modules/uninstall/confirm','admin/modules/uninstall/confirm','Uninstall','a:0:{}','system',-1,0,0,0,0,4,0,2,382,413,459,0,0,0,0,0,0),('management',460,412,'admin/structure/trigger/user','admin/structure/trigger/user','User','a:0:{}','system',-1,0,0,0,0,4,0,2,385,412,460,0,0,0,0,0,0),('management',461,405,'admin/config/people/userprotect','admin/config/people/userprotect','User protect','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:63:\"Protect inidividual users and/or roles from editing operations.\";}}','system',0,0,0,0,0,4,0,2,377,405,461,0,0,0,0,0,0),('navigation',462,396,'user/%/edit/account','user/%/edit/account','Account','a:0:{}','system',-1,0,0,0,0,3,0,21,396,462,0,0,0,0,0,0,0),('management',463,457,'admin/config/content/formats/%','admin/config/content/formats/%','','a:0:{}','system',0,0,1,0,0,5,0,2,377,392,457,463,0,0,0,0,0),('management',464,457,'admin/config/content/formats/add','admin/config/content/formats/add','Add text format','a:0:{}','system',-1,0,0,0,1,5,0,2,377,392,457,464,0,0,0,0,0),('management',465,461,'admin/config/people/userprotect/administrator_bypass','admin/config/people/userprotect/administrator_bypass','Administrator bypass','a:0:{}','system',-1,0,0,0,3,5,0,2,377,405,461,465,0,0,0,0,0),('management',466,389,'admin/structure/block/list/bartik','admin/structure/block/list/bartik','Bartik','a:0:{}','system',-1,0,0,0,0,4,0,2,385,389,466,0,0,0,0,0,0),('management',467,389,'admin/structure/block/list/Bluebird','admin/structure/block/list/Bluebird','Bluebird','a:0:{}','system',-1,0,0,0,0,4,0,2,385,389,467,0,0,0,0,0,0),('management',468,450,'admin/config/search/settings/reindex','admin/config/search/settings/reindex','Clear index','a:0:{}','system',-1,0,0,0,0,5,0,2,377,409,450,468,0,0,0,0,0),('management',469,422,'admin/config/system/actions/configure','admin/config/system/actions/configure','Configure an advanced action','a:0:{}','system',-1,0,0,0,0,5,0,2,377,411,422,469,0,0,0,0,0),('management',470,404,'admin/structure/menu/manage/%','admin/structure/menu/manage/%','Customize menu','a:0:{}','system',0,0,1,0,0,4,0,2,385,404,470,0,0,0,0,0,0),('management',471,393,'admin/structure/types/manage/%','admin/structure/types/manage/%','Edit content type','a:0:{}','system',0,0,1,0,0,4,0,2,385,393,471,0,0,0,0,0,0),('management',472,431,'admin/config/regional/date-time/formats','admin/config/regional/date-time/formats','Formats','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:51:\"Configure display format strings for date and time.\";}}','system',-1,0,1,0,-9,5,0,2,377,407,431,472,0,0,0,0,0),('management',473,389,'admin/structure/block/list/garland','admin/structure/block/list/garland','Garland','a:0:{}','system',-1,0,0,0,0,4,0,2,385,389,473,0,0,0,0,0,0),('management',474,457,'admin/config/content/formats/list','admin/config/content/formats/list','List','a:0:{}','system',-1,0,0,0,0,5,0,2,377,392,457,474,0,0,0,0,0),('management',475,422,'admin/config/system/actions/manage','admin/config/system/actions/manage','Manage actions','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:41:\"Manage the actions defined for your site.\";}}','system',-1,0,0,0,-2,5,0,2,377,411,422,475,0,0,0,0,0),('management',476,421,'admin/config/people/accounts/display','admin/config/people/accounts/display','Manage display','a:0:{}','system',-1,0,0,0,2,5,0,2,377,405,421,476,0,0,0,0,0),('management',477,421,'admin/config/people/accounts/fields','admin/config/people/accounts/fields','Manage fields','a:0:{}','system',-1,0,1,0,1,5,0,2,377,405,421,477,0,0,0,0,0),('management',478,461,'admin/config/people/userprotect/protected_roles','admin/config/people/userprotect/protected_roles','Protected roles','a:0:{}','system',-1,0,0,0,2,5,0,2,377,405,461,478,0,0,0,0,0),('management',479,461,'admin/config/people/userprotect/protected_users','admin/config/people/userprotect/protected_users','Protected users','a:0:{}','system',-1,0,0,0,1,5,0,2,377,405,461,479,0,0,0,0,0),('management',480,461,'admin/config/people/userprotect/protection_defaults','admin/config/people/userprotect/protection_defaults','Protection defaults','a:0:{}','system',-1,0,0,0,4,5,0,2,377,405,461,480,0,0,0,0,0),('management',481,421,'admin/config/people/accounts/settings','admin/config/people/accounts/settings','Settings','a:0:{}','system',-1,0,0,0,-10,5,0,2,377,405,421,481,0,0,0,0,0),('management',482,389,'admin/structure/block/list/seven','admin/structure/block/list/seven','Seven','a:0:{}','system',-1,0,0,0,0,4,0,2,385,389,482,0,0,0,0,0,0),('management',483,389,'admin/structure/block/list/stark','admin/structure/block/list/stark','Stark','a:0:{}','system',-1,0,0,0,0,4,0,2,385,389,483,0,0,0,0,0,0),('management',484,431,'admin/config/regional/date-time/types','admin/config/regional/date-time/types','Types','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:44:\"Configure display formats for date and time.\";}}','system',-1,0,1,0,-10,5,0,2,377,407,431,484,0,0,0,0,0),('navigation',485,408,'node/%/revisions/%/delete','node/%/revisions/%/delete','Delete earlier revision','a:0:{}','system',0,0,0,0,0,3,0,375,408,485,0,0,0,0,0,0,0),('navigation',486,408,'node/%/revisions/%/revert','node/%/revisions/%/revert','Revert to earlier revision','a:0:{}','system',0,0,0,0,0,3,0,375,408,486,0,0,0,0,0,0,0),('navigation',487,408,'node/%/revisions/%/view','node/%/revisions/%/view','Revisions','a:0:{}','system',0,0,0,0,0,3,0,375,408,487,0,0,0,0,0,0,0),('management',488,467,'admin/structure/block/list/Bluebird/add','admin/structure/block/list/Bluebird/add','Add block','a:0:{}','system',-1,0,0,0,0,5,0,2,385,389,467,488,0,0,0,0,0),('management',489,466,'admin/structure/block/list/bartik/add','admin/structure/block/list/bartik/add','Add block','a:0:{}','system',-1,0,0,0,0,5,0,2,385,389,466,489,0,0,0,0,0),('management',490,473,'admin/structure/block/list/garland/add','admin/structure/block/list/garland/add','Add block','a:0:{}','system',-1,0,0,0,0,5,0,2,385,389,473,490,0,0,0,0,0),('management',491,482,'admin/structure/block/list/seven/add','admin/structure/block/list/seven/add','Add block','a:0:{}','system',-1,0,0,0,0,5,0,2,385,389,482,491,0,0,0,0,0),('management',492,483,'admin/structure/block/list/stark/add','admin/structure/block/list/stark/add','Add block','a:0:{}','system',-1,0,0,0,0,5,0,2,385,389,483,492,0,0,0,0,0),('management',493,484,'admin/config/regional/date-time/types/add','admin/config/regional/date-time/types/add','Add date type','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:18:\"Add new date type.\";}}','system',-1,0,0,0,-10,6,0,2,377,407,431,484,493,0,0,0,0),('management',494,472,'admin/config/regional/date-time/formats/add','admin/config/regional/date-time/formats/add','Add format','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:43:\"Allow users to add additional date formats.\";}}','system',-1,0,0,0,-10,6,0,2,377,407,431,472,494,0,0,0,0),('management',495,470,'admin/structure/menu/manage/%/add','admin/structure/menu/manage/%/add','Add link','a:0:{}','system',-1,0,0,0,0,5,0,2,385,404,470,495,0,0,0,0,0),('management',496,389,'admin/structure/block/manage/%/%','admin/structure/block/manage/%/%','Configure block','a:0:{}','system',0,0,0,0,0,4,0,2,385,389,496,0,0,0,0,0,0),('navigation',497,390,'user/%/cancel/confirm/%/%','user/%/cancel/confirm/%/%','Confirm account cancellation','a:0:{}','system',0,0,0,0,0,3,0,21,390,497,0,0,0,0,0,0,0),('management',498,476,'admin/config/people/accounts/display/default','admin/config/people/accounts/display/default','Default','a:0:{}','system',-1,0,0,0,-10,6,0,2,377,405,421,476,498,0,0,0,0),('management',499,471,'admin/structure/types/manage/%/delete','admin/structure/types/manage/%/delete','Delete','a:0:{}','system',0,0,0,0,0,5,0,2,385,393,471,499,0,0,0,0,0),('management',500,436,'admin/config/people/ip-blocking/delete/%','admin/config/people/ip-blocking/delete/%','Delete IP address','a:0:{}','system',0,0,0,0,0,5,0,2,377,405,436,500,0,0,0,0,0),('management',501,422,'admin/config/system/actions/delete/%','admin/config/system/actions/delete/%','Delete action','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:17:\"Delete an action.\";}}','system',0,0,0,0,0,5,0,2,377,411,422,501,0,0,0,0,0),('management',502,470,'admin/structure/menu/manage/%/delete','admin/structure/menu/manage/%/delete','Delete menu','a:0:{}','system',0,0,0,0,0,5,0,2,385,404,470,502,0,0,0,0,0),('management',503,404,'admin/structure/menu/item/%/delete','admin/structure/menu/item/%/delete','Delete menu link','a:0:{}','system',0,0,0,0,0,4,0,2,385,404,503,0,0,0,0,0,0),('management',504,449,'admin/people/permissions/roles/delete/%','admin/people/permissions/roles/delete/%','Delete role','a:0:{}','system',0,0,0,0,0,5,0,2,383,406,449,504,0,0,0,0,0),('management',505,463,'admin/config/content/formats/%/disable','admin/config/content/formats/%/disable','Disable text format','a:0:{}','system',0,0,0,0,0,6,0,2,377,392,457,463,505,0,0,0,0),('management',506,471,'admin/structure/types/manage/%/edit','admin/structure/types/manage/%/edit','Edit','a:0:{}','system',-1,0,0,0,0,5,0,2,385,393,471,506,0,0,0,0,0),('management',507,470,'admin/structure/menu/manage/%/edit','admin/structure/menu/manage/%/edit','Edit menu','a:0:{}','system',-1,0,0,0,0,5,0,2,385,404,470,507,0,0,0,0,0),('management',508,404,'admin/structure/menu/item/%/edit','admin/structure/menu/item/%/edit','Edit menu link','a:0:{}','system',0,0,0,0,0,4,0,2,385,404,508,0,0,0,0,0,0),('management',509,449,'admin/people/permissions/roles/edit/%','admin/people/permissions/roles/edit/%','Edit role','a:0:{}','system',0,0,0,0,0,5,0,2,383,406,449,509,0,0,0,0,0),('management',510,470,'admin/structure/menu/manage/%/list','admin/structure/menu/manage/%/list','List links','a:0:{}','system',-1,0,0,0,-10,5,0,2,385,404,470,510,0,0,0,0,0),('management',511,471,'admin/structure/types/manage/%/display','admin/structure/types/manage/%/display','Manage display','a:0:{}','system',-1,0,0,0,2,5,0,2,385,393,471,511,0,0,0,0,0),('management',512,471,'admin/structure/types/manage/%/fields','admin/structure/types/manage/%/fields','Manage fields','a:0:{}','system',-1,0,1,0,1,5,0,2,385,393,471,512,0,0,0,0,0),('management',513,404,'admin/structure/menu/item/%/reset','admin/structure/menu/item/%/reset','Reset menu link','a:0:{}','system',0,0,0,0,0,4,0,2,385,404,513,0,0,0,0,0,0),('management',514,476,'admin/config/people/accounts/display/full','admin/config/people/accounts/display/full','User account','a:0:{}','system',-1,0,0,0,0,6,0,2,377,405,421,476,514,0,0,0,0),('management',515,477,'admin/config/people/accounts/fields/%','admin/config/people/accounts/fields/%','','a:0:{}','system',0,0,0,0,0,6,0,2,377,405,421,477,515,0,0,0,0),('management',516,496,'admin/structure/block/manage/%/%/configure','admin/structure/block/manage/%/%/configure','Configure block','a:0:{}','system',-1,0,0,0,0,5,0,2,385,389,496,516,0,0,0,0,0),('management',517,511,'admin/structure/types/manage/%/display/default','admin/structure/types/manage/%/display/default','Default','a:0:{}','system',-1,0,0,0,-10,6,0,2,385,393,471,511,517,0,0,0,0),('management',518,496,'admin/structure/block/manage/%/%/delete','admin/structure/block/manage/%/%/delete','Delete block','a:0:{}','system',-1,0,0,0,0,5,0,2,385,389,496,518,0,0,0,0,0),('management',519,472,'admin/config/regional/date-time/formats/%/delete','admin/config/regional/date-time/formats/%/delete','Delete date format','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:47:\"Allow users to delete a configured date format.\";}}','system',0,0,0,0,0,6,0,2,377,407,431,472,519,0,0,0,0),('management',520,484,'admin/config/regional/date-time/types/%/delete','admin/config/regional/date-time/types/%/delete','Delete date type','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:45:\"Allow users to delete a configured date type.\";}}','system',0,0,0,0,0,6,0,2,377,407,431,484,520,0,0,0,0),('management',521,472,'admin/config/regional/date-time/formats/%/edit','admin/config/regional/date-time/formats/%/edit','Edit date format','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:45:\"Allow users to edit a configured date format.\";}}','system',0,0,0,0,0,6,0,2,377,407,431,472,521,0,0,0,0),('management',522,511,'admin/structure/types/manage/%/display/full','admin/structure/types/manage/%/display/full','Full content','a:0:{}','system',-1,0,0,0,0,6,0,2,385,393,471,511,522,0,0,0,0),('management',523,511,'admin/structure/types/manage/%/display/rss','admin/structure/types/manage/%/display/rss','RSS','a:0:{}','system',-1,0,0,0,2,6,0,2,385,393,471,511,523,0,0,0,0),('management',524,511,'admin/structure/types/manage/%/display/search_index','admin/structure/types/manage/%/display/search_index','Search index','a:0:{}','system',-1,0,0,0,3,6,0,2,385,393,471,511,524,0,0,0,0),('management',525,511,'admin/structure/types/manage/%/display/search_result','admin/structure/types/manage/%/display/search_result','Search result','a:0:{}','system',-1,0,0,0,4,6,0,2,385,393,471,511,525,0,0,0,0),('management',526,511,'admin/structure/types/manage/%/display/teaser','admin/structure/types/manage/%/display/teaser','Teaser','a:0:{}','system',-1,0,0,0,1,6,0,2,385,393,471,511,526,0,0,0,0),('management',527,512,'admin/structure/types/manage/%/fields/%','admin/structure/types/manage/%/fields/%','','a:0:{}','system',0,0,0,0,0,6,0,2,385,393,471,512,527,0,0,0,0),('management',528,515,'admin/config/people/accounts/fields/%/delete','admin/config/people/accounts/fields/%/delete','Delete','a:0:{}','system',-1,0,0,0,10,7,0,2,377,405,421,477,515,528,0,0,0),('management',529,515,'admin/config/people/accounts/fields/%/edit','admin/config/people/accounts/fields/%/edit','Edit','a:0:{}','system',-1,0,0,0,0,7,0,2,377,405,421,477,515,529,0,0,0),('management',530,515,'admin/config/people/accounts/fields/%/field-settings','admin/config/people/accounts/fields/%/field-settings','Field settings','a:0:{}','system',-1,0,0,0,0,7,0,2,377,405,421,477,515,530,0,0,0),('management',531,515,'admin/config/people/accounts/fields/%/widget-type','admin/config/people/accounts/fields/%/widget-type','Widget type','a:0:{}','system',-1,0,0,0,0,7,0,2,377,405,421,477,515,531,0,0,0),('management',532,527,'admin/structure/types/manage/%/fields/%/delete','admin/structure/types/manage/%/fields/%/delete','Delete','a:0:{}','system',-1,0,0,0,10,7,0,2,385,393,471,512,527,532,0,0,0),('management',533,527,'admin/structure/types/manage/%/fields/%/edit','admin/structure/types/manage/%/fields/%/edit','Edit','a:0:{}','system',-1,0,0,0,0,7,0,2,385,393,471,512,527,533,0,0,0),('management',534,527,'admin/structure/types/manage/%/fields/%/field-settings','admin/structure/types/manage/%/fields/%/field-settings','Field settings','a:0:{}','system',-1,0,0,0,0,7,0,2,385,393,471,512,527,534,0,0,0),('management',535,527,'admin/structure/types/manage/%/fields/%/widget-type','admin/structure/types/manage/%/fields/%/widget-type','Widget type','a:0:{}','system',-1,0,0,0,0,7,0,2,385,393,471,512,527,535,0,0,0),('management',536,418,'admin/config/workflow/rules','admin/config/workflow/rules','Rules','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:42:\"Manage reaction rules and rule components.\";}}','system',0,0,0,0,0,4,0,2,377,418,536,0,0,0,0,0,0),('management',537,536,'admin/config/workflow/rules/components','admin/config/workflow/rules/components','Components','a:0:{}','system',-1,0,0,0,0,5,0,2,377,418,536,537,0,0,0,0,0),('management',538,536,'admin/config/workflow/rules/reaction','admin/config/workflow/rules/reaction','Rules','a:0:{}','system',-1,0,0,0,-1,5,0,2,377,418,536,538,0,0,0,0,0),('management',539,536,'admin/config/workflow/rules/settings','admin/config/workflow/rules/settings','Settings','a:0:{}','system',-1,0,0,0,1,5,0,2,377,418,536,539,0,0,0,0,0),('management',540,537,'admin/config/workflow/rules/components/add','admin/config/workflow/rules/components/add','Add new component','a:0:{}','system',-1,0,0,0,0,6,0,2,377,418,536,537,540,0,0,0,0),('management',541,538,'admin/config/workflow/rules/reaction/add','admin/config/workflow/rules/reaction/add','Add new rule','a:0:{}','system',-1,0,0,0,0,6,0,2,377,418,536,538,541,0,0,0,0),('management',542,539,'admin/config/workflow/rules/settings/advanced','admin/config/workflow/rules/settings/advanced','Advanced','a:0:{}','system',-1,0,0,0,0,6,0,2,377,418,536,539,542,0,0,0,0),('management',543,539,'admin/config/workflow/rules/settings/basic','admin/config/workflow/rules/settings/basic','Basic','a:0:{}','system',-1,0,0,0,-10,6,0,2,377,418,536,539,543,0,0,0,0),('management',544,537,'admin/config/workflow/rules/components/import','admin/config/workflow/rules/components/import','Import component','a:0:{}','system',-1,0,0,0,0,6,0,2,377,418,536,537,544,0,0,0,0),('management',545,538,'admin/config/workflow/rules/reaction/import','admin/config/workflow/rules/reaction/import','Import rule','a:0:{}','system',-1,0,0,0,0,6,0,2,377,418,536,538,545,0,0,0,0),('management',546,537,'admin/config/workflow/rules/components/manage/%','admin/config/workflow/rules/components/manage/%','','a:0:{}','system',-1,0,1,0,0,6,0,2,377,418,536,537,546,0,0,0,0),('management',547,538,'admin/config/workflow/rules/reaction/manage/%','admin/config/workflow/rules/reaction/manage/%','','a:0:{}','system',-1,0,1,0,0,6,0,2,377,418,536,538,547,0,0,0,0),('management',548,546,'admin/config/workflow/rules/components/manage/%/clone','admin/config/workflow/rules/components/manage/%/clone','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,548,0,0,0),('management',549,547,'admin/config/workflow/rules/reaction/manage/%/clone','admin/config/workflow/rules/reaction/manage/%/clone','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,549,0,0,0),('management',550,546,'admin/config/workflow/rules/components/manage/%/execute','admin/config/workflow/rules/components/manage/%/execute','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,550,0,0,0),('management',551,547,'admin/config/workflow/rules/reaction/manage/%/execute','admin/config/workflow/rules/reaction/manage/%/execute','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,551,0,0,0),('management',552,546,'admin/config/workflow/rules/components/manage/%/export','admin/config/workflow/rules/components/manage/%/export','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,552,0,0,0),('management',553,547,'admin/config/workflow/rules/reaction/manage/%/export','admin/config/workflow/rules/reaction/manage/%/export','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,553,0,0,0),('management',554,546,'admin/config/workflow/rules/components/manage/%/%','admin/config/workflow/rules/components/manage/%/%','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,554,0,0,0),('management',555,547,'admin/config/workflow/rules/reaction/manage/%/%','admin/config/workflow/rules/reaction/manage/%/%','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,555,0,0,0),('management',556,546,'admin/config/workflow/rules/components/manage/%/delete/event','admin/config/workflow/rules/components/manage/%/delete/event','Remove event','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:37:\"Remove an event from a reaction rule.\";}}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,556,0,0,0),('management',557,547,'admin/config/workflow/rules/reaction/manage/%/delete/event','admin/config/workflow/rules/reaction/manage/%/delete/event','Remove event','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:37:\"Remove an event from a reaction rule.\";}}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,557,0,0,0),('management',558,546,'admin/config/workflow/rules/components/manage/%/add/event','admin/config/workflow/rules/components/manage/%/add/event','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,558,0,0,0),('management',559,547,'admin/config/workflow/rules/reaction/manage/%/add/event','admin/config/workflow/rules/reaction/manage/%/add/event','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,559,0,0,0),('management',560,546,'admin/config/workflow/rules/components/manage/%/delete/%','admin/config/workflow/rules/components/manage/%/delete/%','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,560,0,0,0),('management',561,547,'admin/config/workflow/rules/reaction/manage/%/delete/%','admin/config/workflow/rules/reaction/manage/%/delete/%','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,561,0,0,0),('management',562,546,'admin/config/workflow/rules/components/manage/%/edit/%','admin/config/workflow/rules/components/manage/%/edit/%','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,562,0,0,0),('management',563,547,'admin/config/workflow/rules/reaction/manage/%/edit/%','admin/config/workflow/rules/reaction/manage/%/edit/%','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,563,0,0,0),('management',564,546,'admin/config/workflow/rules/components/manage/%/add/%','admin/config/workflow/rules/components/manage/%/add/%','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,537,546,564,0,0,0),('management',565,547,'admin/config/workflow/rules/reaction/manage/%/add/%','admin/config/workflow/rules/reaction/manage/%/add/%','','a:0:{}','system',0,0,0,0,0,7,0,2,377,418,536,538,547,565,0,0,0),('management',566,16,'admin/reports/apachesolr','admin/reports/apachesolr','Apache Solr search index','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:54:\"Information about the contents of the index the server\";}}','system',0,0,1,0,0,3,0,2,16,566,0,0,0,0,0,0,0),('management',567,409,'admin/config/search/apachesolr','admin/config/search/apachesolr','Apache Solr search','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:23:\"Administer Apache Solr.\";}}','system',0,0,0,0,-8,4,0,2,377,409,567,0,0,0,0,0,0),('management',568,566,'admin/reports/apachesolr/%','admin/reports/apachesolr/%','Apache Solr search index','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:54:\"Information about the contents of the index the server\";}}','system',0,0,0,0,0,4,0,2,16,566,568,0,0,0,0,0,0),('management',569,568,'admin/reports/apachesolr/%/conf','admin/reports/apachesolr/%/conf','Configuration files','a:0:{}','system',-1,0,0,0,5,5,0,2,16,566,568,569,0,0,0,0,0),('management',570,567,'admin/config/search/apachesolr/index','admin/config/search/apachesolr/index','Default index','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:23:\"Administer Apache Solr.\";}}','system',-1,0,0,0,-8,5,0,2,377,409,567,570,0,0,0,0,0),('management',571,568,'admin/reports/apachesolr/%/index','admin/reports/apachesolr/%/index','Search index','a:0:{}','system',-1,0,0,0,0,5,0,2,16,566,568,571,0,0,0,0,0),('management',572,567,'admin/config/search/apachesolr/settings','admin/config/search/apachesolr/settings','Settings','a:0:{}','system',-1,0,1,0,10,5,0,2,377,409,567,572,0,0,0,0,0),('management',573,572,'admin/config/search/apachesolr/settings/add','admin/config/search/apachesolr/settings/add','Add search environment','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:28:\"Add Apache Solr environment.\";}}','system',-1,0,0,0,0,6,0,2,377,409,567,572,573,0,0,0,0),('management',574,572,'admin/config/search/apachesolr/settings/%/clone','admin/config/search/apachesolr/settings/%/clone','Apache Solr search environment clone','a:0:{}','system',0,0,0,0,0,6,0,2,377,409,567,572,574,0,0,0,0),('management',575,572,'admin/config/search/apachesolr/settings/%/delete','admin/config/search/apachesolr/settings/%/delete','Apache Solr search environment delete','a:0:{}','system',0,0,0,0,0,6,0,2,377,409,567,572,575,0,0,0,0),('management',576,572,'admin/config/search/apachesolr/settings/%/edit','admin/config/search/apachesolr/settings/%/edit','Edit','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:36:\"Edit Apache Solr search environment.\";}}','system',-1,0,0,0,10,6,0,2,377,409,567,572,576,0,0,0,0),('management',577,572,'admin/config/search/apachesolr/settings/%/index','admin/config/search/apachesolr/settings/%/index','Index','a:0:{}','system',-1,0,0,0,0,6,0,2,377,409,567,572,577,0,0,0,0),('navigation',578,114,'search/site','search/site','Site','a:0:{}','system',-1,0,0,0,0,2,0,114,578,0,0,0,0,0,0,0,0),('navigation',579,578,'search/site/%','search/site/%','Site','a:0:{}','system',-1,0,0,0,0,3,0,114,578,579,0,0,0,0,0,0,0),('management',580,567,'admin/config/search/apachesolr/search-pages','admin/config/search/apachesolr/search-pages','Pages/Blocks','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:22:\"Configure search pages\";}}','system',-1,0,1,0,0,5,0,2,377,409,567,580,0,0,0,0,0),('management',581,580,'admin/config/search/apachesolr/search-pages/addblock','admin/config/search/apachesolr/search-pages/addblock','Add search block \"More Like This\"','a:0:{}','system',-1,0,0,0,2,6,0,2,377,409,567,580,581,0,0,0,0),('management',582,580,'admin/config/search/apachesolr/search-pages/add','admin/config/search/apachesolr/search-pages/add','Add search page','a:0:{}','system',-1,0,0,0,1,6,0,2,377,409,567,580,582,0,0,0,0),('management',583,572,'admin/config/search/apachesolr/settings/%/bias','admin/config/search/apachesolr/settings/%/bias','Bias','a:0:{}','system',-1,0,0,0,4,6,0,2,377,409,567,572,583,0,0,0,0),('management',584,580,'admin/config/search/apachesolr/search-pages/%/clone','admin/config/search/apachesolr/search-pages/%/clone','Clone search page','a:0:{}','system',0,0,0,0,0,6,0,2,377,409,567,580,584,0,0,0,0),('management',585,580,'admin/config/search/apachesolr/search-pages/%/delete','admin/config/search/apachesolr/search-pages/%/delete','Delete search page','a:0:{}','system',0,0,0,0,0,6,0,2,377,409,567,580,585,0,0,0,0),('management',586,580,'admin/config/search/apachesolr/search-pages/%/edit','admin/config/search/apachesolr/search-pages/%/edit','Edit search page','a:0:{}','system',0,0,0,0,0,6,0,2,377,409,567,580,586,0,0,0,0),('management',587,405,'admin/config/people/ldap','admin/config/people/ldap','LDAP Configuration','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:54:\"LDAP authentication, authorization, provisioning, etc.\";}}','system',0,0,0,0,0,4,0,2,377,405,587,0,0,0,0,0,0),('management',588,587,'admin/config/people/ldap/settings','admin/config/people/ldap/settings','1. Settings','a:0:{}','system',-1,0,0,0,-2,5,0,2,377,405,587,588,0,0,0,0,0),('management',589,587,'admin/config/people/ldap/servers','admin/config/people/ldap/servers','2. Servers','a:0:{}','system',-1,0,1,0,-1,5,0,2,377,405,587,589,0,0,0,0,0),('management',590,589,'admin/config/people/ldap/servers/add','admin/config/people/ldap/servers/add','Add LDAP Server Configuration','a:0:{}','system',0,0,0,0,3,6,0,2,377,405,587,589,590,0,0,0,0),('management',591,589,'admin/config/people/ldap/servers/list','admin/config/people/ldap/servers/list','List','a:0:{}','system',-1,0,0,0,0,6,0,2,377,405,587,589,591,0,0,0,0),('management',592,589,'admin/config/people/ldap/servers/delete/%','admin/config/people/ldap/servers/delete/%','Delete LDAP Server','a:0:{}','system',0,0,0,0,0,6,0,2,377,405,587,589,592,0,0,0,0),('management',593,589,'admin/config/people/ldap/servers/edit/%','admin/config/people/ldap/servers/edit/%','Edit LDAP Server Configuration','a:0:{}','system',0,0,0,0,0,6,0,2,377,405,587,589,593,0,0,0,0),('management',594,589,'admin/config/people/ldap/servers/disable/%','admin/config/people/ldap/servers/disable/%','Enable LDAP Server','a:0:{}','system',0,0,0,0,0,6,0,2,377,405,587,589,594,0,0,0,0),('management',595,589,'admin/config/people/ldap/servers/enable/%','admin/config/people/ldap/servers/enable/%','Enable LDAP Server','a:0:{}','system',0,0,0,0,0,6,0,2,377,405,587,589,595,0,0,0,0),('management',596,589,'admin/config/people/ldap/servers/test/%','admin/config/people/ldap/servers/test/%','Test LDAP Server Configuraion','a:0:{}','system',0,0,0,0,0,6,0,2,377,405,587,589,596,0,0,0,0),('management',597,587,'admin/config/people/ldap/authorization','admin/config/people/ldap/authorization','Authorization','a:0:{}','system',-1,0,1,0,3,5,0,2,377,405,587,597,0,0,0,0,0),('management',598,597,'admin/config/people/ldap/authorization/list','admin/config/people/ldap/authorization/list','List','a:0:{}','system',-1,0,0,0,0,6,0,2,377,405,587,597,598,0,0,0,0),('management',599,597,'admin/config/people/ldap/authorization/add/%','admin/config/people/ldap/authorization/add/%','Add Authorization Configuration','a:0:{}','system',0,0,0,0,0,6,0,2,377,405,587,597,599,0,0,0,0),('management',600,597,'admin/config/people/ldap/authorization/delete/%','admin/config/people/ldap/authorization/delete/%','Delete LDAP Authorization Configuration','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:42:\"Delete an ldap authorization configuration\";}}','system',0,0,0,0,0,6,0,2,377,405,587,597,600,0,0,0,0),('management',601,597,'admin/config/people/ldap/authorization/edit/%','admin/config/people/ldap/authorization/edit/%','Edit LDAP Authorization Configuration','a:0:{}','system',0,0,0,0,0,6,0,2,377,405,587,597,601,0,0,0,0),('management',602,597,'admin/config/people/ldap/authorization/test/%','admin/config/people/ldap/authorization/test/%','Test LDAP Authorization Configuration','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:40:\"Test an ldap authorization configuration\";}}','system',0,0,0,0,0,6,0,2,377,405,587,597,602,0,0,0,0),('management',603,587,'admin/config/people/ldap/authentication','admin/config/people/ldap/authentication','Authentication','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:29:\"Configure LDAP Authentication\";}}','system',-1,0,0,0,2,5,0,2,377,405,587,603,0,0,0,0,0),('navigation',604,0,'civicrm','civicrm','CiviCRM','a:0:{}','system',-1,0,0,0,0,1,0,604,0,0,0,0,0,0,0,0,0),('management',605,377,'admin/config/civicrm','admin/config/civicrm','CiviCRM','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:38:\"Configure CiviCRM integration modules.\";}}','system',0,0,1,0,-10,3,0,2,377,605,0,0,0,0,0,0,0),('navigation',606,0,'civicrm/dashboard','civicrm','CiviCRM','a:1:{s:5:\"alter\";b:1;}','civicrm',0,0,0,0,0,1,0,606,0,0,0,0,0,0,0,0,0),('management',607,605,'admin/config/civicrm/rules','admin/config/civicrm/rules','CiviCRM Rules settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:27:\"CiviCRM Rules Configuration\";}}','system',0,0,0,0,0,4,0,2,377,605,607,0,0,0,0,0,0),('navigation',608,0,'backupdata','backupdata','Backup/Restore Instance','a:0:{}','system',0,0,0,0,0,1,0,608,0,0,0,0,0,0,0,0,0),('navigation',609,0,'importdata','importdata','Import Data','a:0:{}','system',1,0,0,0,0,1,0,609,0,0,0,0,0,0,0,0,0),('navigation',610,0,'nyss_getfile','nyss_getfile','NYSS Retrieve file','a:0:{}','system',1,0,0,0,0,1,0,610,0,0,0,0,0,0,0,0,0),('management',611,2,'admin/user/user/create','admin/user/user/create','','a:0:{}','system',0,0,0,0,0,2,0,2,611,0,0,0,0,0,0,0,0),('management',612,16,'admin/reports/civicrm_error','admin/reports/civicrm_error','CiviCRM Error Handler','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:30:\"Email critical CiviCRM errors.\";}}','system',0,0,0,0,0,3,0,2,16,612,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `menu_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_router`
--

DROP TABLE IF EXISTS `menu_router`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_router` (
  `path` varchar(255) NOT NULL DEFAULT '',
  `load_functions` blob NOT NULL COMMENT 'A serialized array of function names (like node_load) to be called to load an object corresponding to a part of the current path.',
  `to_arg_functions` blob NOT NULL COMMENT 'A serialized array of function names (like user_uid_optional_to_arg) to be called to replace a part of the router path with another string.',
  `access_callback` varchar(255) NOT NULL DEFAULT '',
  `access_arguments` blob COMMENT 'A serialized array of arguments for the access callback.',
  `page_callback` varchar(255) NOT NULL DEFAULT '',
  `page_arguments` blob COMMENT 'A serialized array of arguments for the page callback.',
  `fit` int(11) NOT NULL DEFAULT '0',
  `number_parts` smallint(6) NOT NULL DEFAULT '0',
  `tab_parent` varchar(255) NOT NULL DEFAULT '',
  `tab_root` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_callback` varchar(255) NOT NULL DEFAULT '',
  `title_arguments` varchar(255) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `position` varchar(255) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0',
  `include_file` mediumtext,
  `delivery_callback` varchar(255) NOT NULL DEFAULT '',
  `context` int(11) NOT NULL DEFAULT '0' COMMENT 'Only for local tasks (tabs) - the context of a local task to control its placement.',
  `theme_callback` varchar(255) NOT NULL DEFAULT '',
  `theme_arguments` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`path`),
  KEY `fit` (`fit`),
  KEY `tab_root_weight_title` (`tab_root`(64),`weight`,`title`),
  KEY `tab_parent` (`tab_parent`(64),`weight`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_router`
--

LOCK TABLES `menu_router` WRITE;
/*!40000 ALTER TABLE `menu_router` DISABLE KEYS */;
INSERT INTO `menu_router` VALUES ('admin','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',1,1,'','admin','Administration','t','',6,'','',9,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/appearance','','','user_access','a:1:{i:0;s:17:\"administer themes\";}','system_themes_page','a:0:{}',3,2,'','admin/appearance','Appearance','t','',6,'Select and configure your themes.','left',-6,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/appearance/default','','','user_access','a:1:{i:0;s:17:\"administer themes\";}','system_theme_default','a:0:{}',7,3,'','admin/appearance/default','Set default theme','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/appearance/disable','','','user_access','a:1:{i:0;s:17:\"administer themes\";}','system_theme_disable','a:0:{}',7,3,'','admin/appearance/disable','Disable theme','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/appearance/enable','','','user_access','a:1:{i:0;s:17:\"administer themes\";}','system_theme_enable','a:0:{}',7,3,'','admin/appearance/enable','Enable theme','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/appearance/list','','','user_access','a:1:{i:0;s:17:\"administer themes\";}','system_themes_page','a:0:{}',7,3,'admin/appearance','admin/appearance','List','t','',140,'Select and configure your theme','',-1,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/appearance/settings','','','user_access','a:1:{i:0;s:17:\"administer themes\";}','drupal_get_form','a:1:{i:0;s:21:\"system_theme_settings\";}',7,3,'admin/appearance','admin/appearance','Settings','t','',132,'Configure default and theme specific settings.','',20,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/appearance/settings/bartik','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:25:\"themes/bartik/bartik.info\";s:4:\"name\";s:6:\"bartik\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:6:\"Bartik\";s:11:\"description\";s:48:\"A flexible, recolorable theme with many regions.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:3:{s:14:\"css/layout.css\";s:28:\"themes/bartik/css/layout.css\";s:13:\"css/style.css\";s:27:\"themes/bartik/css/style.css\";s:14:\"css/colors.css\";s:28:\"themes/bartik/css/colors.css\";}s:5:\"print\";a:1:{s:13:\"css/print.css\";s:27:\"themes/bartik/css/print.css\";}}s:7:\"regions\";a:17:{s:6:\"header\";s:6:\"Header\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";s:11:\"highlighted\";s:11:\"Highlighted\";s:8:\"featured\";s:8:\"Featured\";s:7:\"content\";s:7:\"Content\";s:13:\"sidebar_first\";s:13:\"Sidebar first\";s:14:\"sidebar_second\";s:14:\"Sidebar second\";s:14:\"triptych_first\";s:14:\"Triptych first\";s:15:\"triptych_middle\";s:15:\"Triptych middle\";s:13:\"triptych_last\";s:13:\"Triptych last\";s:18:\"footer_firstcolumn\";s:19:\"Footer first column\";s:19:\"footer_secondcolumn\";s:20:\"Footer second column\";s:18:\"footer_thirdcolumn\";s:19:\"Footer third column\";s:19:\"footer_fourthcolumn\";s:20:\"Footer fourth column\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"settings\";a:1:{s:20:\"shortcut_module_link\";s:1:\"0\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:28:\"themes/bartik/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:3:{s:14:\"css/layout.css\";s:28:\"themes/bartik/css/layout.css\";s:13:\"css/style.css\";s:27:\"themes/bartik/css/style.css\";s:14:\"css/colors.css\";s:28:\"themes/bartik/css/colors.css\";}s:5:\"print\";a:1:{s:13:\"css/print.css\";s:27:\"themes/bartik/css/print.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:6:\"bartik\";}',15,4,'admin/appearance/settings','admin/appearance','Bartik','t','',132,'','',0,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/appearance/settings/Bluebird','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":13:{s:8:\"filename\";s:43:\"sites/default/themes/Bluebird/Bluebird.info\";s:4:\"name\";s:8:\"Bluebird\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:12:{s:4:\"name\";s:8:\"Bluebird\";s:7:\"project\";s:8:\"Bluebird\";s:11:\"description\";s:53:\"Drupal base theme built with Blueprint CSS framework.\";s:4:\"core\";s:3:\"7.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:17:\"screen,projection\";a:4:{s:16:\"css/Bluebird.css\";s:46:\"sites/default/themes/Bluebird/css/Bluebird.css\";s:18:\"nyss_skin/skin.css\";s:48:\"sites/default/themes/Bluebird/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:55:\"sites/default/themes/Bluebird/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:43:\"sites/default/themes/Bluebird/css/style.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:56:\"sites/default/themes/Bluebird/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:52:\"sites/default/themes/Bluebird/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:48:\"sites/default/themes/Bluebird/scripts/general.js\";}s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:4:\"help\";s:4:\"Help\";}s:8:\"features\";a:9:{i:0;s:4:\"name\";i:1;s:6:\"slogan\";i:2;s:7:\"mission\";i:3;s:17:\"node_user_picture\";i:4;s:20:\"comment_user_picture\";i:5;s:6:\"search\";i:6;s:7:\"favicon\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:44:\"sites/default/themes/Bluebird/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:17:\"screen,projection\";a:4:{s:16:\"css/Bluebird.css\";s:46:\"sites/default/themes/Bluebird/css/Bluebird.css\";s:18:\"nyss_skin/skin.css\";s:48:\"sites/default/themes/Bluebird/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:55:\"sites/default/themes/Bluebird/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:43:\"sites/default/themes/Bluebird/css/style.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:56:\"sites/default/themes/Bluebird/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:52:\"sites/default/themes/Bluebird/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:48:\"sites/default/themes/Bluebird/scripts/general.js\";}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:8:\"Bluebird\";}',15,4,'admin/appearance/settings','admin/appearance','Bluebird','t','',132,'','',0,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/appearance/settings/garland','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:27:\"themes/garland/garland.info\";s:4:\"name\";s:7:\"garland\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:7:\"Garland\";s:11:\"description\";s:111:\"A multi-column theme which can be configured to modify colors and switch between fixed and fluid width layouts.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:8:\"settings\";a:1:{s:13:\"garland_width\";s:5:\"fluid\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"regions\";a:9:{s:13:\"sidebar_first\";s:12:\"Left sidebar\";s:14:\"sidebar_second\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:11:\"highlighted\";s:11:\"Highlighted\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";}s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:29:\"themes/garland/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:7:\"garland\";}',15,4,'admin/appearance/settings','admin/appearance','Garland','t','',132,'','',0,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/appearance/settings/global','','','user_access','a:1:{i:0;s:17:\"administer themes\";}','drupal_get_form','a:1:{i:0;s:21:\"system_theme_settings\";}',15,4,'admin/appearance/settings','admin/appearance','Global settings','t','',140,'','',-1,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/appearance/settings/seven','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:23:\"themes/seven/seven.info\";s:4:\"name\";s:5:\"seven\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:5:\"Seven\";s:11:\"description\";s:65:\"A simple one-column, tableless, fluid width administration theme.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:1:{s:6:\"screen\";a:2:{s:9:\"reset.css\";s:22:\"themes/seven/reset.css\";s:9:\"style.css\";s:22:\"themes/seven/style.css\";}}s:8:\"settings\";a:1:{s:20:\"shortcut_module_link\";s:1:\"1\";}s:7:\"regions\";a:5:{s:7:\"content\";s:7:\"Content\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";s:13:\"sidebar_first\";s:13:\"First sidebar\";}s:14:\"regions_hidden\";a:3:{i:0;s:13:\"sidebar_first\";i:1;s:8:\"page_top\";i:2;s:11:\"page_bottom\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:27:\"themes/seven/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:6:\"screen\";a:2:{s:9:\"reset.css\";s:22:\"themes/seven/reset.css\";s:9:\"style.css\";s:22:\"themes/seven/style.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:5:\"seven\";}',15,4,'admin/appearance/settings','admin/appearance','Seven','t','',132,'','',0,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/appearance/settings/stark','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:23:\"themes/stark/stark.info\";s:4:\"name\";s:5:\"stark\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:15:{s:4:\"name\";s:5:\"Stark\";s:11:\"description\";s:208:\"This theme demonstrates Drupal\'s default HTML markup and CSS styles. To learn how to build your own theme and override Drupal\'s default code, see the <a href=\"http://drupal.org/theme-guide\">Theming Guide</a>.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:10:\"layout.css\";s:23:\"themes/stark/layout.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"regions\";a:9:{s:13:\"sidebar_first\";s:12:\"Left sidebar\";s:14:\"sidebar_second\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:11:\"highlighted\";s:11:\"Highlighted\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";}s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:27:\"themes/stark/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:10:\"layout.css\";s:23:\"themes/stark/layout.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:5:\"stark\";}',15,4,'admin/appearance/settings','admin/appearance','Stark','t','',132,'','',0,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/compact','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_compact_page','a:0:{}',3,2,'','admin/compact','Compact mode','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_config_page','a:0:{}',3,2,'','admin/config','Configuration','t','',6,'Administer settings.','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/civicrm','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/civicrm','CiviCRM','t','',6,'Configure CiviCRM integration modules.','left',-10,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/civicrm/rules','','','user_access','a:1:{i:0;s:29:\"access civicrm rules settings\";}','drupal_get_form','a:1:{i:0;s:28:\"civicrm_rules_admin_settings\";}',15,4,'','admin/config/civicrm/rules','CiviCRM Rules settings','t','',6,'CiviCRM Rules Configuration','',0,'sites/all/modules/civicrm/drupal/modules/civicrm_rules/civicrm_rules_admin_form.inc','',0,'','a:0:{}'),('admin/config/content','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/content','Content authoring','t','',6,'Settings related to formatting and authoring content.','left',-15,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/content/formats','','','user_access','a:1:{i:0;s:18:\"administer filters\";}','drupal_get_form','a:1:{i:0;s:21:\"filter_admin_overview\";}',15,4,'','admin/config/content/formats','Text formats','t','',6,'Configure how content input by users is filtered, including allowed HTML tags. Also allows enabling of module-provided filters.','',0,'modules/filter/filter.admin.inc','',0,'','a:0:{}'),('admin/config/content/formats/%','a:1:{i:4;s:18:\"filter_format_load\";}','','user_access','a:1:{i:0;s:18:\"administer filters\";}','filter_admin_format_page','a:1:{i:0;i:4;}',30,5,'','admin/config/content/formats/%','','filter_admin_format_title','a:1:{i:0;i:4;}',6,'','',0,'modules/filter/filter.admin.inc','',0,'','a:0:{}'),('admin/config/content/formats/%/disable','a:1:{i:4;s:18:\"filter_format_load\";}','','_filter_disable_format_access','a:1:{i:0;i:4;}','drupal_get_form','a:2:{i:0;s:20:\"filter_admin_disable\";i:1;i:4;}',61,6,'','admin/config/content/formats/%/disable','Disable text format','t','',6,'','',0,'modules/filter/filter.admin.inc','',0,'','a:0:{}'),('admin/config/content/formats/add','','','user_access','a:1:{i:0;s:18:\"administer filters\";}','filter_admin_format_page','a:0:{}',31,5,'admin/config/content/formats','admin/config/content/formats','Add text format','t','',388,'','',1,'modules/filter/filter.admin.inc','',1,'','a:0:{}'),('admin/config/content/formats/list','','','user_access','a:1:{i:0;s:18:\"administer filters\";}','drupal_get_form','a:1:{i:0;s:21:\"filter_admin_overview\";}',31,5,'admin/config/content/formats','admin/config/content/formats','List','t','',140,'','',0,'modules/filter/filter.admin.inc','',1,'','a:0:{}'),('admin/config/development','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/development','Development','t','',6,'Development tools.','right',-10,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/development/logging','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:23:\"system_logging_settings\";}',15,4,'','admin/config/development/logging','Logging and errors','t','',6,'Settings for logging and alerts modules. Various modules can route Drupal\'s system events to different destinations, such as syslog, database, email, etc.','',-15,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/development/maintenance','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:28:\"system_site_maintenance_mode\";}',15,4,'','admin/config/development/maintenance','Maintenance mode','t','',6,'Take the site offline for maintenance or bring it back online.','',-10,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/development/performance','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:27:\"system_performance_settings\";}',15,4,'','admin/config/development/performance','Performance','t','',6,'Enable or disable page caching for anonymous users and set CSS and JS bandwidth optimization options.','',-20,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/front','','','user_access','a:1:{i:0;s:21:\"administer front page\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/front','Front Page','t','',6,'Configure front page.','right',-15,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/front/arrange','','','user_access','a:1:{i:0;s:21:\"administer front page\";}','drupal_get_form','a:1:{i:0;s:29:\"front_page_admin_arrange_form\";}',15,4,'admin/config/front','admin/config/front','Arrange','t','',132,'Ability to re-arrange what order front page roles are processed.','',1,'sites/all/modules/front/front_page.admin.inc','',1,'','a:0:{}'),('admin/config/front/home-links','','','user_access','a:1:{i:0;s:21:\"administer front page\";}','drupal_get_form','a:1:{i:0;s:27:\"front_page_admin_home_links\";}',15,4,'admin/config/front','admin/config/front','Home links','t','',132,'Allows you to change the location of the &lt;front&gt; placeholder.','',2,'sites/all/modules/front/front_page.admin.inc','',1,'','a:0:{}'),('admin/config/front/settings','','','user_access','a:1:{i:0;s:21:\"administer front page\";}','drupal_get_form','a:1:{i:0;s:16:\"front_page_admin\";}',15,4,'admin/config/front','admin/config/front','Settings','t','',132,'Administer custom front page settings.','',0,'sites/all/modules/front/front_page.admin.inc','',1,'','a:0:{}'),('admin/config/media','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/media','Media','t','',6,'Media tools.','left',-10,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/media/file-system','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:27:\"system_file_system_settings\";}',15,4,'','admin/config/media/file-system','File system','t','',6,'Tell Drupal where to store uploaded files and how they are accessed.','',-10,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/media/image-toolkit','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:29:\"system_image_toolkit_settings\";}',15,4,'','admin/config/media/image-toolkit','Image toolkit','t','',6,'Choose which image toolkit to use if you have installed optional toolkits.','',20,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/people','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/people','People','t','',6,'Configure user accounts.','left',-20,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/people/accounts','','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:1:{i:0;s:19:\"user_admin_settings\";}',15,4,'','admin/config/people/accounts','Account settings','t','',6,'Configure default behavior of users, including registration requirements, e-mails, fields, and user pictures.','',-10,'modules/user/user.admin.inc','',0,'','a:0:{}'),('admin/config/people/accounts/display','','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"user\";i:2;s:4:\"user\";i:3;s:7:\"default\";}',31,5,'admin/config/people/accounts','admin/config/people/accounts','Manage display','t','',132,'','',2,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/config/people/accounts/display/default','','','_field_ui_view_mode_menu_access','a:5:{i:0;s:4:\"user\";i:1;s:4:\"user\";i:2;s:7:\"default\";i:3;s:11:\"user_access\";i:4;s:16:\"administer users\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"user\";i:2;s:4:\"user\";i:3;s:7:\"default\";}',63,6,'admin/config/people/accounts/display','admin/config/people/accounts','Default','t','',140,'','',-10,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/config/people/accounts/display/full','','','_field_ui_view_mode_menu_access','a:5:{i:0;s:4:\"user\";i:1;s:4:\"user\";i:2;s:4:\"full\";i:3;s:11:\"user_access\";i:4;s:16:\"administer users\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"user\";i:2;s:4:\"user\";i:3;s:4:\"full\";}',63,6,'admin/config/people/accounts/display','admin/config/people/accounts','User account','t','',132,'','',0,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/config/people/accounts/fields','','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:3:{i:0;s:28:\"field_ui_field_overview_form\";i:1;s:4:\"user\";i:2;s:4:\"user\";}',31,5,'admin/config/people/accounts','admin/config/people/accounts','Manage fields','t','',132,'','',1,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/config/people/accounts/fields/%','a:1:{i:5;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"user\";i:1;s:4:\"user\";i:2;s:1:\"0\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:2:{i:0;s:24:\"field_ui_field_edit_form\";i:1;i:5;}',62,6,'','admin/config/people/accounts/fields/%','','field_ui_menu_title','a:1:{i:0;i:5;}',6,'','',0,'modules/field_ui/field_ui.admin.inc','',0,'','a:0:{}'),('admin/config/people/accounts/fields/%/delete','a:1:{i:5;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"user\";i:1;s:4:\"user\";i:2;s:1:\"0\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:2:{i:0;s:26:\"field_ui_field_delete_form\";i:1;i:5;}',125,7,'admin/config/people/accounts/fields/%','admin/config/people/accounts/fields/%','Delete','t','',132,'','',10,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/config/people/accounts/fields/%/edit','a:1:{i:5;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"user\";i:1;s:4:\"user\";i:2;s:1:\"0\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:2:{i:0;s:24:\"field_ui_field_edit_form\";i:1;i:5;}',125,7,'admin/config/people/accounts/fields/%','admin/config/people/accounts/fields/%','Edit','t','',140,'','',0,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/config/people/accounts/fields/%/field-settings','a:1:{i:5;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"user\";i:1;s:4:\"user\";i:2;s:1:\"0\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:2:{i:0;s:28:\"field_ui_field_settings_form\";i:1;i:5;}',125,7,'admin/config/people/accounts/fields/%','admin/config/people/accounts/fields/%','Field settings','t','',132,'','',0,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/config/people/accounts/fields/%/widget-type','a:1:{i:5;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"user\";i:1;s:4:\"user\";i:2;s:1:\"0\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:2:{i:0;s:25:\"field_ui_widget_type_form\";i:1;i:5;}',125,7,'admin/config/people/accounts/fields/%','admin/config/people/accounts/fields/%','Widget type','t','',132,'','',0,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/config/people/accounts/settings','','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:1:{i:0;s:19:\"user_admin_settings\";}',31,5,'admin/config/people/accounts','admin/config/people/accounts','Settings','t','',140,'','',-10,'modules/user/user.admin.inc','',1,'','a:0:{}'),('admin/config/people/ip-blocking','','','user_access','a:1:{i:0;s:18:\"block IP addresses\";}','system_ip_blocking','a:0:{}',15,4,'','admin/config/people/ip-blocking','IP address blocking','t','',6,'Manage blocked IP addresses.','',10,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/people/ip-blocking/delete/%','a:1:{i:5;s:15:\"blocked_ip_load\";}','','user_access','a:1:{i:0;s:18:\"block IP addresses\";}','drupal_get_form','a:2:{i:0;s:25:\"system_ip_blocking_delete\";i:1;i:5;}',62,6,'','admin/config/people/ip-blocking/delete/%','Delete IP address','t','',6,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/people/ldap','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:21:\"ldap_servers_settings\";}',15,4,'','admin/config/people/ldap','LDAP Configuration','t','',6,'LDAP authentication, authorization, provisioning, etc.','',0,'sites/all/modules/ldap/ldap_servers/ldap_servers.settings.inc','',0,'','a:0:{}'),('admin/config/people/ldap/authentication','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:30:\"ldap_authentication_admin_form\";}',31,5,'admin/config/people/ldap','admin/config/people/ldap','Authentication','t','',132,'Configure LDAP Authentication','',2,'sites/all/modules/ldap/ldap_authentication/ldap_authentication.admin.inc','',1,'','a:0:{}'),('admin/config/people/ldap/authorization','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','ldap_authorizations_admin_index','a:0:{}',31,5,'admin/config/people/ldap','admin/config/people/ldap','Authorization','t','',132,'','',3,'sites/all/modules/ldap/ldap_authorization/ldap_authorization.admin.inc','',1,'','a:0:{}'),('admin/config/people/ldap/authorization/add/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:29:\"ldap_authorization_admin_form\";i:1;i:6;i:2;s:3:\"add\";}',126,7,'','admin/config/people/ldap/authorization/add/%','Add Authorization Configuration','t','',6,'','',0,'sites/all/modules/ldap/ldap_authorization/ldap_authorization.admin.inc','',0,'','a:0:{}'),('admin/config/people/ldap/authorization/delete/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:29:\"ldap_authorization_admin_form\";i:1;i:6;i:2;s:6:\"delete\";}',126,7,'','admin/config/people/ldap/authorization/delete/%','Delete LDAP Authorization Configuration','t','',6,'Delete an ldap authorization configuration','',0,'sites/all/modules/ldap/ldap_authorization/ldap_authorization.admin.inc','',0,'','a:0:{}'),('admin/config/people/ldap/authorization/edit/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:29:\"ldap_authorization_admin_form\";i:1;i:6;i:2;s:4:\"edit\";}',126,7,'','admin/config/people/ldap/authorization/edit/%','Edit LDAP Authorization Configuration','t','',6,'','',0,'sites/all/modules/ldap/ldap_authorization/ldap_authorization.admin.inc','',0,'','a:0:{}'),('admin/config/people/ldap/authorization/list','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','ldap_authorizations_admin_index','a:0:{}',63,6,'admin/config/people/ldap/authorization','admin/config/people/ldap','List','t','',140,'','',0,'sites/all/modules/ldap/ldap_authorization/ldap_authorization.admin.inc','',1,'','a:0:{}'),('admin/config/people/ldap/authorization/test/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:28:\"ldap_authorization_test_form\";i:1;i:6;i:2;s:4:\"test\";}',126,7,'','admin/config/people/ldap/authorization/test/%','Test LDAP Authorization Configuration','t','',6,'Test an ldap authorization configuration','',0,'sites/all/modules/ldap/ldap_authorization/ldap_authorization.admin.test.inc','',0,'','a:0:{}'),('admin/config/people/ldap/servers','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','ldap_servers_edit_index','a:0:{}',31,5,'admin/config/people/ldap','admin/config/people/ldap','2. Servers','t','',132,'','',-1,'sites/all/modules/ldap/ldap_servers/ldap_servers.admin.inc','',1,'','a:0:{}'),('admin/config/people/ldap/servers/add','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:2:{i:0;s:23:\"ldap_servers_admin_form\";i:1;s:3:\"add\";}',63,6,'admin/config/people/ldap/servers','admin/config/people/ldap','Add LDAP Server Configuration','t','',134,'','',3,'sites/all/modules/ldap/ldap_servers/ldap_servers.admin.inc','',1,'','a:0:{}'),('admin/config/people/ldap/servers/delete/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:25:\"ldap_servers_admin_delete\";i:1;i:5;i:2;i:6;}',126,7,'','admin/config/people/ldap/servers/delete/%','Delete LDAP Server','t','',6,'','',0,'sites/all/modules/ldap/ldap_servers/ldap_servers.admin.inc','',0,'','a:0:{}'),('admin/config/people/ldap/servers/disable/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:33:\"ldap_servers_admin_enable_disable\";i:1;i:5;i:2;i:6;}',126,7,'','admin/config/people/ldap/servers/disable/%','Enable LDAP Server','t','',6,'','',0,'sites/all/modules/ldap/ldap_servers/ldap_servers.admin.inc','',0,'','a:0:{}'),('admin/config/people/ldap/servers/edit/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:23:\"ldap_servers_admin_form\";i:1;s:4:\"edit\";i:2;i:6;}',126,7,'','admin/config/people/ldap/servers/edit/%','Edit LDAP Server Configuration','t','',6,'','',0,'sites/all/modules/ldap/ldap_servers/ldap_servers.admin.inc','',0,'','a:0:{}'),('admin/config/people/ldap/servers/enable/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:33:\"ldap_servers_admin_enable_disable\";i:1;i:5;i:2;i:6;}',126,7,'','admin/config/people/ldap/servers/enable/%','Enable LDAP Server','t','',6,'','',0,'sites/all/modules/ldap/ldap_servers/ldap_servers.admin.inc','',0,'','a:0:{}'),('admin/config/people/ldap/servers/list','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','ldap_servers_edit_index','a:0:{}',63,6,'admin/config/people/ldap/servers','admin/config/people/ldap','List','t','',140,'','',0,'sites/all/modules/ldap/ldap_servers/ldap_servers.admin.inc','',1,'','a:0:{}'),('admin/config/people/ldap/servers/test/%','a:1:{i:6;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:3:{i:0;s:22:\"ldap_servers_test_form\";i:1;i:5;i:2;i:6;}',126,7,'','admin/config/people/ldap/servers/test/%','Test LDAP Server Configuraion','t','',6,'','',0,'sites/all/modules/ldap/ldap_servers/ldap_servers.test_form.inc','',0,'','a:0:{}'),('admin/config/people/ldap/settings','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:21:\"ldap_servers_settings\";}',31,5,'admin/config/people/ldap','admin/config/people/ldap','1. Settings','t','',140,'','',-2,'sites/all/modules/ldap/ldap_servers/ldap_servers.settings.inc','',1,'','a:0:{}'),('admin/config/people/userprotect','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:27:\"userprotect_protected_users\";}',15,4,'','admin/config/people/userprotect','User protect','t','',6,'Protect inidividual users and/or roles from editing operations.','',0,'sites/all/modules/userprotect/userprotect.admin.inc','',0,'','a:0:{}'),('admin/config/people/userprotect/administrator_bypass','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:32:\"userprotect_administrator_bypass\";}',31,5,'admin/config/people/userprotect','admin/config/people/userprotect','Administrator bypass','t','',132,'','',3,'sites/all/modules/userprotect/userprotect.admin.inc','',1,'','a:0:{}'),('admin/config/people/userprotect/protected_roles','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:27:\"userprotect_protected_roles\";}',31,5,'admin/config/people/userprotect','admin/config/people/userprotect','Protected roles','t','',132,'','',2,'sites/all/modules/userprotect/userprotect.admin.inc','',1,'','a:0:{}'),('admin/config/people/userprotect/protected_users','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:27:\"userprotect_protected_users\";}',31,5,'admin/config/people/userprotect','admin/config/people/userprotect','Protected users','t','',140,'','',1,'sites/all/modules/userprotect/userprotect.admin.inc','',1,'','a:0:{}'),('admin/config/people/userprotect/protection_defaults','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:31:\"userprotect_protection_defaults\";}',31,5,'admin/config/people/userprotect','admin/config/people/userprotect','Protection defaults','t','',132,'','',4,'sites/all/modules/userprotect/userprotect.admin.inc','',1,'','a:0:{}'),('admin/config/regional','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/regional','Regional and language','t','',6,'Regional settings, localization and translation.','left',-5,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/regional/date-time','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:25:\"system_date_time_settings\";}',15,4,'','admin/config/regional/date-time','Date and time','t','',6,'Configure display formats for date and time.','',-15,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/regional/date-time/formats','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_date_time_formats','a:0:{}',31,5,'admin/config/regional/date-time','admin/config/regional/date-time','Formats','t','',132,'Configure display format strings for date and time.','',-9,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/config/regional/date-time/formats/%/delete','a:1:{i:5;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:2:{i:0;s:30:\"system_date_delete_format_form\";i:1;i:5;}',125,7,'','admin/config/regional/date-time/formats/%/delete','Delete date format','t','',6,'Allow users to delete a configured date format.','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/regional/date-time/formats/%/edit','a:1:{i:5;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:2:{i:0;s:34:\"system_configure_date_formats_form\";i:1;i:5;}',125,7,'','admin/config/regional/date-time/formats/%/edit','Edit date format','t','',6,'Allow users to edit a configured date format.','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/regional/date-time/formats/add','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:34:\"system_configure_date_formats_form\";}',63,6,'admin/config/regional/date-time/formats','admin/config/regional/date-time','Add format','t','',388,'Allow users to add additional date formats.','',-10,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/config/regional/date-time/formats/lookup','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_date_time_lookup','a:0:{}',63,6,'','admin/config/regional/date-time/formats/lookup','Date and time lookup','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/regional/date-time/types','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:25:\"system_date_time_settings\";}',31,5,'admin/config/regional/date-time','admin/config/regional/date-time','Types','t','',140,'Configure display formats for date and time.','',-10,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/config/regional/date-time/types/%/delete','a:1:{i:5;N;}','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:2:{i:0;s:35:\"system_delete_date_format_type_form\";i:1;i:5;}',125,7,'','admin/config/regional/date-time/types/%/delete','Delete date type','t','',6,'Allow users to delete a configured date type.','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/regional/date-time/types/add','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:32:\"system_add_date_format_type_form\";}',63,6,'admin/config/regional/date-time/types','admin/config/regional/date-time','Add date type','t','',388,'Add new date type.','',-10,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/config/regional/settings','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:24:\"system_regional_settings\";}',15,4,'','admin/config/regional/settings','Regional settings','t','',6,'Settings for the site\'s default time zone and country.','',-20,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/search','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/search','Search and metadata','t','',6,'Local site search, metadata and SEO.','left',-10,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr','','','user_access','a:1:{i:0;s:17:\"administer search\";}','apachesolr_status_page','a:0:{}',15,4,'','admin/config/search/apachesolr','Apache Solr search','t','',6,'Administer Apache Solr.','',-8,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/index','','','user_access','a:1:{i:0;s:17:\"administer search\";}','apachesolr_status_page','a:0:{}',31,5,'admin/config/search/apachesolr','admin/config/search/apachesolr','Default index','t','',140,'Administer Apache Solr.','',-8,'sites/all/modules/apachesolr/apachesolr.admin.inc','',1,'','a:0:{}'),('admin/config/search/apachesolr/index/confirm/clear','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:30:\"apachesolr_clear_index_confirm\";}',127,7,'','admin/config/search/apachesolr/index/confirm/clear','Confirm the re-indexing of all content','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/index/confirm/delete','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:31:\"apachesolr_delete_index_confirm\";}',127,7,'','admin/config/search/apachesolr/index/confirm/delete','Confirm index deletion','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/search-pages','','','user_access','a:1:{i:0;s:17:\"administer search\";}','apachesolr_search_page_list_all','a:0:{}',31,5,'admin/config/search/apachesolr','admin/config/search/apachesolr','Pages/Blocks','t','',132,'Configure search pages','',0,'sites/all/modules/apachesolr/apachesolr_search.admin.inc','',1,'','a:0:{}'),('admin/config/search/apachesolr/search-pages/%/clone','a:1:{i:5;s:27:\"apachesolr_search_page_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:43:\"apachesolr_search_clone_search_page_confirm\";i:1;i:5;}',125,7,'','admin/config/search/apachesolr/search-pages/%/clone','Clone search page','t','',6,'','',0,'sites/all/modules/apachesolr/apachesolr_search.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/search-pages/%/delete','a:1:{i:5;s:27:\"apachesolr_search_page_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:44:\"apachesolr_search_delete_search_page_confirm\";i:1;i:5;}',125,7,'','admin/config/search/apachesolr/search-pages/%/delete','Delete search page','t','',6,'','',0,'sites/all/modules/apachesolr/apachesolr_search.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/search-pages/%/edit','a:1:{i:5;s:27:\"apachesolr_search_page_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:36:\"apachesolr_search_page_settings_form\";i:1;i:5;}',125,7,'','admin/config/search/apachesolr/search-pages/%/edit','Edit search page','t','',6,'','',0,'sites/all/modules/apachesolr/apachesolr_search.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/search-pages/add','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:36:\"apachesolr_search_page_settings_form\";}',63,6,'admin/config/search/apachesolr/search-pages','admin/config/search/apachesolr','Add search page','t','',388,'','',1,'sites/all/modules/apachesolr/apachesolr_search.admin.inc','',1,'','a:0:{}'),('admin/config/search/apachesolr/search-pages/addblock','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:36:\"apachesolr_search_mlt_add_block_form\";}',63,6,'admin/config/search/apachesolr/search-pages','admin/config/search/apachesolr','Add search block \"More Like This\"','t','',388,'','',2,'sites/all/modules/apachesolr/apachesolr_search.admin.inc','',1,'','a:0:{}'),('admin/config/search/apachesolr/search-pages/block/%/delete','a:1:{i:6;s:32:\"apachesolr_search_mlt_block_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:39:\"apachesolr_search_mlt_delete_block_form\";i:1;i:6;}',253,8,'','admin/config/search/apachesolr/search-pages/block/%/delete','','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr_search.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/settings','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:19:\"apachesolr_settings\";}',31,5,'admin/config/search/apachesolr','admin/config/search/apachesolr','Settings','t','',132,'','',10,'sites/all/modules/apachesolr/apachesolr.admin.inc','',1,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/bias','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','apachesolr_bias_settings_page','a:1:{i:0;i:5;}',125,7,'admin/config/search/apachesolr/settings','admin/config/search/apachesolr','Bias','t','',132,'','',4,'sites/all/modules/apachesolr/apachesolr_search.admin.inc','',1,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/clone','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:33:\"apachesolr_environment_clone_form\";i:1;i:5;}',125,7,'','admin/config/search/apachesolr/settings/%/clone','Apache Solr search environment clone','t','',6,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/delete','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','apachesolr_environment_delete_page_access','a:2:{i:0;s:17:\"administer search\";i:1;i:5;}','drupal_get_form','a:2:{i:0;s:34:\"apachesolr_environment_delete_form\";i:1;i:5;}',125,7,'','admin/config/search/apachesolr/settings/%/delete','Apache Solr search environment delete','t','',6,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/edit','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:32:\"apachesolr_environment_edit_form\";i:1;i:5;}',125,7,'admin/config/search/apachesolr/settings','admin/config/search/apachesolr','Edit','t','',132,'Edit Apache Solr search environment.','',10,'sites/all/modules/apachesolr/apachesolr.admin.inc','',1,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/index','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','apachesolr_status_page','a:1:{i:0;i:5;}',125,7,'admin/config/search/apachesolr/settings','admin/config/search/apachesolr','Index','t','',132,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',1,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/index/delete','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:43:\"apachesolr_index_action_form_delete_confirm\";i:1;i:5;}',251,8,'','admin/config/search/apachesolr/settings/%/index/delete','Reindex','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/index/delete/confirm','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:31:\"apachesolr_delete_index_confirm\";i:1;i:5;}',503,9,'','admin/config/search/apachesolr/settings/%/index/delete/confirm','Confirm index deletion','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/index/remaining','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:46:\"apachesolr_index_action_form_remaining_confirm\";i:1;i:5;}',251,8,'','admin/config/search/apachesolr/settings/%/index/remaining','Remaining','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/index/reset','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:42:\"apachesolr_index_action_form_reset_confirm\";i:1;i:5;}',251,8,'','admin/config/search/apachesolr/settings/%/index/reset','Reindex','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/settings/%/index/reset/confirm','a:1:{i:5;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:30:\"apachesolr_clear_index_confirm\";i:1;i:5;}',503,9,'','admin/config/search/apachesolr/settings/%/index/reset/confirm','Confirm the re-indexing of all content','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/config/search/apachesolr/settings/add','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:32:\"apachesolr_environment_edit_form\";}',63,6,'admin/config/search/apachesolr/settings','admin/config/search/apachesolr','Add search environment','t','',388,'Add Apache Solr environment.','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',1,'','a:0:{}'),('admin/config/search/clean-urls','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:25:\"system_clean_url_settings\";}',15,4,'','admin/config/search/clean-urls','Clean URLs','t','',6,'Enable or disable clean URLs for your site.','',5,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/search/clean-urls/check','','','1','a:0:{}','drupal_json_output','a:1:{i:0;a:1:{s:6:\"status\";b:1;}}',31,5,'','admin/config/search/clean-urls/check','Clean URL check','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/search/settings','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:21:\"search_admin_settings\";}',15,4,'','admin/config/search/settings','Search settings','t','',6,'Configure relevance settings for search and other indexing options.','',-10,'modules/search/search.admin.inc','',0,'','a:0:{}'),('admin/config/search/settings/reindex','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:22:\"search_reindex_confirm\";}',31,5,'','admin/config/search/settings/reindex','Clear index','t','',4,'','',0,'modules/search/search.admin.inc','',0,'','a:0:{}'),('admin/config/services','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/services','Web services','t','',6,'Tools related to web services.','right',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/services/rss-publishing','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:25:\"system_rss_feeds_settings\";}',15,4,'','admin/config/services/rss-publishing','RSS publishing','t','',6,'Configure the site description, the number of items per feed and whether feeds should be titles/teasers/full-text.','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/system','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/system','System','t','',6,'General system related configuration.','right',-20,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/system/actions','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','system_actions_manage','a:0:{}',15,4,'','admin/config/system/actions','Actions','t','',6,'Manage the actions defined for your site.','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/system/actions/configure','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','drupal_get_form','a:1:{i:0;s:24:\"system_actions_configure\";}',31,5,'','admin/config/system/actions/configure','Configure an advanced action','t','',4,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/system/actions/delete/%','a:1:{i:5;s:12:\"actions_load\";}','','user_access','a:1:{i:0;s:18:\"administer actions\";}','drupal_get_form','a:2:{i:0;s:26:\"system_actions_delete_form\";i:1;i:5;}',62,6,'','admin/config/system/actions/delete/%','Delete action','t','',6,'Delete an action.','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/system/actions/manage','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','system_actions_manage','a:0:{}',31,5,'admin/config/system/actions','admin/config/system/actions','Manage actions','t','',140,'Manage the actions defined for your site.','',-2,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/config/system/actions/orphan','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','system_actions_remove_orphans','a:0:{}',31,5,'','admin/config/system/actions/orphan','Remove orphans','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/system/cron','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:20:\"system_cron_settings\";}',15,4,'','admin/config/system/cron','Cron','t','',6,'Manage automatic site maintenance tasks.','',20,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/system/site-information','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:32:\"system_site_information_settings\";}',15,4,'','admin/config/system/site-information','Site information','t','',6,'Change site name, e-mail address, slogan, default front page, and number of posts per page, error pages.','',-20,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/user-interface','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/user-interface','User interface','t','',6,'Tools that enhance the user interface.','right',-15,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/workflow','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',7,3,'','admin/config/workflow','Workflow','t','',6,'Content workflow, editorial workflow tools.','right',5,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/config/workflow/rules','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:2:{i:0;s:29:\"rules_admin_reaction_overview\";i:1;s:36:\"admin/config/workflow/rules/reaction\";}',15,4,'','admin/config/workflow/rules','Rules','t','',6,'Manage reaction rules and rule components.','right',0,'sites/all/modules/rules/rules_admin/rules_admin.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/autocomplete_tags','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','rules_autocomplete_tags','a:1:{i:0;i:5;}',31,5,'','admin/config/workflow/rules/autocomplete_tags','Rules tags autocomplete','t','',0,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:2:{i:0;s:31:\"rules_admin_components_overview\";i:1;s:38:\"admin/config/workflow/rules/components\";}',31,5,'admin/config/workflow/rules','admin/config/workflow/rules','Components','t','',132,'','',0,'sites/all/modules/rules/rules_admin/rules_admin.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/components/add','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:2:{i:0;s:25:\"rules_admin_add_component\";i:1;s:38:\"admin/config/workflow/rules/components\";}',63,6,'admin/config/workflow/rules/components','admin/config/workflow/rules','Add new component','t','',388,'','',0,'sites/all/modules/rules/rules_admin/rules_admin.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/components/import','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:2:{i:0;s:20:\"rules_ui_import_form\";i:1;s:38:\"admin/config/workflow/rules/components\";}',63,6,'admin/config/workflow/rules/components','admin/config/workflow/rules','Import component','t','',388,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:31:\"rules_ui_form_edit_rules_config\";i:1;i:6;i:2;s:38:\"admin/config/workflow/rules/components\";}',126,7,'','admin/config/workflow/rules/components/manage/%','','rules_get_title','a:2:{i:0;s:24:\"Editing !plugin \"!label\"\";i:1;i:6;}',4,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/%','a:2:{i:6;s:17:\"rules_config_load\";i:7;N;}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:4:{i:0;s:37:\"rules_ui_form_rules_config_confirm_op\";i:1;i:6;i:2;i:7;i:3;s:38:\"admin/config/workflow/rules/components\";}',252,8,'','admin/config/workflow/rules/components/manage/%/%','','t','',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/add/%','a:2:{i:6;a:1:{s:17:\"rules_config_load\";a:1:{i:0;i:6;}}i:8;a:1:{s:18:\"rules_element_load\";a:1:{i:0;i:6;}}}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:5:{i:0;s:20:\"rules_ui_add_element\";i:1;i:6;i:2;i:9;i:3;i:8;i:4;s:38:\"admin/config/workflow/rules/components\";}',506,9,'','admin/config/workflow/rules/components/manage/%/add/%','','rules_menu_add_element_title','a:1:{i:0;a:1:{i:0;i:9;}}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/add/event','a:1:{i:6;a:1:{s:17:\"rules_config_load\";a:1:{i:0;i:6;}}}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:18:\"rules_ui_add_event\";i:1;i:6;i:2;s:38:\"admin/config/workflow/rules/components\";}',507,9,'','admin/config/workflow/rules/components/manage/%/add/event','','rules_get_title','a:2:{i:0;s:32:\"Adding event to !plugin \"!label\"\";i:1;i:6;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/autocomplete','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','rules_ui_form_data_selection_auto_completion','a:3:{i:0;i:8;i:1;i:9;i:2;i:10;}',253,8,'','admin/config/workflow/rules/components/manage/%/autocomplete','','t','',0,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/clone','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:32:\"rules_ui_form_clone_rules_config\";i:1;i:6;i:2;s:38:\"admin/config/workflow/rules/components\";}',253,8,'','admin/config/workflow/rules/components/manage/%/clone','','rules_get_title','a:2:{i:0;s:24:\"Cloning !plugin \"!label\"\";i:1;i:6;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/delete/%','a:2:{i:6;a:1:{s:17:\"rules_config_load\";a:1:{i:0;i:6;}}i:8;a:1:{s:18:\"rules_element_load\";a:1:{i:0;i:6;}}}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:4:{i:0;s:23:\"rules_ui_delete_element\";i:1;i:6;i:2;i:8;i:3;s:38:\"admin/config/workflow/rules/components\";}',506,9,'','admin/config/workflow/rules/components/manage/%/delete/%','','rules_get_title','a:2:{i:0;s:24:\"Editing !plugin \"!label\"\";i:1;i:8;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/delete/event','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:4:{i:0;s:21:\"rules_ui_remove_event\";i:1;i:6;i:2;i:9;i:3;s:38:\"admin/config/workflow/rules/components\";}',507,9,'','admin/config/workflow/rules/components/manage/%/delete/event','Remove event','t','',6,'Remove an event from a reaction rule.','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/edit/%','a:2:{i:6;a:1:{s:17:\"rules_config_load\";a:1:{i:0;i:6;}}i:8;a:1:{s:18:\"rules_element_load\";a:1:{i:0;i:6;}}}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:4:{i:0;s:21:\"rules_ui_edit_element\";i:1;i:6;i:2;i:8;i:3;s:38:\"admin/config/workflow/rules/components\";}',506,9,'','admin/config/workflow/rules/components/manage/%/edit/%','','rules_get_title','a:2:{i:0;s:24:\"Editing !plugin \"!label\"\";i:1;i:8;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/execute','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:34:\"rules_ui_form_execute_rules_config\";i:1;i:6;i:2;s:38:\"admin/config/workflow/rules/components\";}',253,8,'','admin/config/workflow/rules/components/manage/%/execute','','rules_get_title','a:2:{i:0;s:26:\"Executing !plugin \"!label\"\";i:1;i:6;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/components/manage/%/export','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:4:\"view\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:33:\"rules_ui_form_export_rules_config\";i:1;i:6;i:2;s:38:\"admin/config/workflow/rules/components\";}',253,8,'','admin/config/workflow/rules/components/manage/%/export','','rules_get_title','a:2:{i:0;s:26:\"Export of !plugin \"!label\"\";i:1;i:6;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:2:{i:0;s:29:\"rules_admin_reaction_overview\";i:1;s:36:\"admin/config/workflow/rules/reaction\";}',31,5,'admin/config/workflow/rules','admin/config/workflow/rules','Rules','t','',140,'','',-1,'sites/all/modules/rules/rules_admin/rules_admin.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/reaction/add','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:2:{i:0;s:29:\"rules_admin_add_reaction_rule\";i:1;s:36:\"admin/config/workflow/rules/reaction\";}',63,6,'admin/config/workflow/rules/reaction','admin/config/workflow/rules','Add new rule','t','',388,'','',0,'sites/all/modules/rules/rules_admin/rules_admin.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/reaction/import','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:2:{i:0;s:20:\"rules_ui_import_form\";i:1;s:36:\"admin/config/workflow/rules/reaction\";}',63,6,'admin/config/workflow/rules/reaction','admin/config/workflow/rules','Import rule','t','',388,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:31:\"rules_ui_form_edit_rules_config\";i:1;i:6;i:2;s:36:\"admin/config/workflow/rules/reaction\";}',126,7,'','admin/config/workflow/rules/reaction/manage/%','','rules_get_title','a:2:{i:0;s:24:\"Editing !plugin \"!label\"\";i:1;i:6;}',4,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/%','a:2:{i:6;s:17:\"rules_config_load\";i:7;N;}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:4:{i:0;s:37:\"rules_ui_form_rules_config_confirm_op\";i:1;i:6;i:2;i:7;i:3;s:36:\"admin/config/workflow/rules/reaction\";}',252,8,'','admin/config/workflow/rules/reaction/manage/%/%','','t','',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/add/%','a:2:{i:6;a:1:{s:17:\"rules_config_load\";a:1:{i:0;i:6;}}i:8;a:1:{s:18:\"rules_element_load\";a:1:{i:0;i:6;}}}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:5:{i:0;s:20:\"rules_ui_add_element\";i:1;i:6;i:2;i:9;i:3;i:8;i:4;s:36:\"admin/config/workflow/rules/reaction\";}',506,9,'','admin/config/workflow/rules/reaction/manage/%/add/%','','rules_menu_add_element_title','a:1:{i:0;a:1:{i:0;i:9;}}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/add/event','a:1:{i:6;a:1:{s:17:\"rules_config_load\";a:1:{i:0;i:6;}}}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:18:\"rules_ui_add_event\";i:1;i:6;i:2;s:36:\"admin/config/workflow/rules/reaction\";}',507,9,'','admin/config/workflow/rules/reaction/manage/%/add/event','','rules_get_title','a:2:{i:0;s:32:\"Adding event to !plugin \"!label\"\";i:1;i:6;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/autocomplete','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','rules_ui_form_data_selection_auto_completion','a:3:{i:0;i:8;i:1;i:9;i:2;i:10;}',253,8,'','admin/config/workflow/rules/reaction/manage/%/autocomplete','','t','',0,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/clone','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:32:\"rules_ui_form_clone_rules_config\";i:1;i:6;i:2;s:36:\"admin/config/workflow/rules/reaction\";}',253,8,'','admin/config/workflow/rules/reaction/manage/%/clone','','rules_get_title','a:2:{i:0;s:24:\"Cloning !plugin \"!label\"\";i:1;i:6;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/delete/%','a:2:{i:6;a:1:{s:17:\"rules_config_load\";a:1:{i:0;i:6;}}i:8;a:1:{s:18:\"rules_element_load\";a:1:{i:0;i:6;}}}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:4:{i:0;s:23:\"rules_ui_delete_element\";i:1;i:6;i:2;i:8;i:3;s:36:\"admin/config/workflow/rules/reaction\";}',506,9,'','admin/config/workflow/rules/reaction/manage/%/delete/%','','rules_get_title','a:2:{i:0;s:24:\"Editing !plugin \"!label\"\";i:1;i:8;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/delete/event','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:4:{i:0;s:21:\"rules_ui_remove_event\";i:1;i:6;i:2;i:9;i:3;s:36:\"admin/config/workflow/rules/reaction\";}',507,9,'','admin/config/workflow/rules/reaction/manage/%/delete/event','Remove event','t','',6,'Remove an event from a reaction rule.','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/edit/%','a:2:{i:6;a:1:{s:17:\"rules_config_load\";a:1:{i:0;i:6;}}i:8;a:1:{s:18:\"rules_element_load\";a:1:{i:0;i:6;}}}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:4:{i:0;s:21:\"rules_ui_edit_element\";i:1;i:6;i:2;i:8;i:3;s:36:\"admin/config/workflow/rules/reaction\";}',506,9,'','admin/config/workflow/rules/reaction/manage/%/edit/%','','rules_get_title','a:2:{i:0;s:24:\"Editing !plugin \"!label\"\";i:1;i:8;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/execute','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:6:\"update\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:34:\"rules_ui_form_execute_rules_config\";i:1;i:6;i:2;s:36:\"admin/config/workflow/rules/reaction\";}',253,8,'','admin/config/workflow/rules/reaction/manage/%/execute','','rules_get_title','a:2:{i:0;s:26:\"Executing !plugin \"!label\"\";i:1;i:6;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/reaction/manage/%/export','a:1:{i:6;s:17:\"rules_config_load\";}','','rules_config_access','a:2:{i:0;s:4:\"view\";i:1;i:6;}','drupal_get_form','a:3:{i:0;s:33:\"rules_ui_form_export_rules_config\";i:1;i:6;i:2;s:36:\"admin/config/workflow/rules/reaction\";}',253,8,'','admin/config/workflow/rules/reaction/manage/%/export','','rules_get_title','a:2:{i:0;s:26:\"Export of !plugin \"!label\"\";i:1;i:6;}',6,'','',0,'sites/all/modules/rules/ui/ui.forms.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/settings','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:1:{i:0;s:20:\"rules_admin_settings\";}',31,5,'admin/config/workflow/rules','admin/config/workflow/rules','Settings','t','',132,'','',1,'sites/all/modules/rules/rules_admin/rules_admin.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/settings/advanced','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:1:{i:0;s:29:\"rules_admin_settings_advanced\";}',63,6,'admin/config/workflow/rules/settings','admin/config/workflow/rules','Advanced','t','',132,'','',0,'sites/all/modules/rules/rules_admin/rules_admin.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/settings/basic','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:1:{i:0;s:20:\"rules_admin_settings\";}',63,6,'admin/config/workflow/rules/settings','admin/config/workflow/rules','Basic','t','',140,'','',-10,'sites/all/modules/rules/rules_admin/rules_admin.inc','',1,'','a:0:{}'),('admin/config/workflow/rules/upgrade','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:1:{i:0;s:18:\"rules_upgrade_form\";}',31,5,'','admin/config/workflow/rules/upgrade','Upgrade','t','',0,'','',0,'sites/all/modules/rules/includes/rules.upgrade.inc','',0,'','a:0:{}'),('admin/config/workflow/rules/upgrade/clear','','','user_access','a:1:{i:0;s:16:\"administer rules\";}','drupal_get_form','a:1:{i:0;s:32:\"rules_upgrade_confirm_clear_form\";}',63,6,'','admin/config/workflow/rules/upgrade/clear','Clear','t','',0,'','',0,'sites/all/modules/rules/includes/rules.upgrade.inc','',0,'','a:0:{}'),('admin/content','','','user_access','a:1:{i:0;s:23:\"access content overview\";}','drupal_get_form','a:1:{i:0;s:18:\"node_admin_content\";}',3,2,'','admin/content','Content','t','',6,'Find and manage content.','',-10,'modules/node/node.admin.inc','',0,'','a:0:{}'),('admin/content/node','','','user_access','a:1:{i:0;s:23:\"access content overview\";}','drupal_get_form','a:1:{i:0;s:18:\"node_admin_content\";}',7,3,'admin/content','admin/content','Content','t','',140,'','',-10,'modules/node/node.admin.inc','',1,'','a:0:{}'),('admin/index','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_index','a:0:{}',3,2,'admin','admin','Index','t','',132,'','',-18,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/modules','','','user_access','a:1:{i:0;s:18:\"administer modules\";}','drupal_get_form','a:1:{i:0;s:14:\"system_modules\";}',3,2,'','admin/modules','Modules','t','',6,'Extend site functionality.','',-2,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/modules/list','','','user_access','a:1:{i:0;s:18:\"administer modules\";}','drupal_get_form','a:1:{i:0;s:14:\"system_modules\";}',7,3,'admin/modules','admin/modules','List','t','',140,'','',0,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/modules/list/confirm','','','user_access','a:1:{i:0;s:18:\"administer modules\";}','drupal_get_form','a:1:{i:0;s:14:\"system_modules\";}',15,4,'','admin/modules/list/confirm','List','t','',4,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/modules/uninstall','','','user_access','a:1:{i:0;s:18:\"administer modules\";}','drupal_get_form','a:1:{i:0;s:24:\"system_modules_uninstall\";}',7,3,'admin/modules','admin/modules','Uninstall','t','',132,'','',20,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/modules/uninstall/confirm','','','user_access','a:1:{i:0;s:18:\"administer modules\";}','drupal_get_form','a:1:{i:0;s:24:\"system_modules_uninstall\";}',15,4,'','admin/modules/uninstall/confirm','Uninstall','t','',4,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/people','','','user_access','a:1:{i:0;s:16:\"administer users\";}','user_admin','a:1:{i:0;s:4:\"list\";}',3,2,'','admin/people','People','t','',6,'Manage user accounts, roles, and permissions.','left',-4,'modules/user/user.admin.inc','',0,'','a:0:{}'),('admin/people/create','','','user_access','a:1:{i:0;s:12:\"create users\";}','user_admin','a:1:{i:0;s:6:\"create\";}',7,3,'admin/people','admin/people','Add user','t','',388,'','',0,'modules/user/user.admin.inc','',1,'','a:0:{}'),('admin/people/people','','','user_access','a:1:{i:0;s:16:\"administer users\";}','user_admin','a:1:{i:0;s:4:\"list\";}',7,3,'admin/people','admin/people','List','t','',140,'Find and manage people interacting with your site.','',-10,'modules/user/user.admin.inc','',1,'','a:0:{}'),('admin/people/permissions','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:22:\"user_admin_permissions\";}',7,3,'admin/people','admin/people','Permissions','t','',132,'Determine access to features by selecting permissions for roles.','',0,'modules/user/user.admin.inc','',1,'','a:0:{}'),('admin/people/permissions/list','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:22:\"user_admin_permissions\";}',15,4,'admin/people/permissions','admin/people','Permissions','t','',140,'Determine access to features by selecting permissions for roles.','',-8,'modules/user/user.admin.inc','',1,'','a:0:{}'),('admin/people/permissions/roleassign','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:21:\"roleassign_admin_form\";}',15,4,'admin/people/permissions','admin/people','Role assign','t','',134,'Define the set of roles that can be assigned by admins with the \'Assign roles\' permission.','',0,'sites/all/modules/roleassign/roleassign.admin.inc','',1,'','a:0:{}'),('admin/people/permissions/roles','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:16:\"user_admin_roles\";}',15,4,'admin/people/permissions','admin/people','Roles','t','',132,'List, edit, or add user roles.','',-5,'modules/user/user.admin.inc','',1,'','a:0:{}'),('admin/people/permissions/roles/delete/%','a:1:{i:5;s:14:\"user_role_load\";}','','user_role_edit_access','a:1:{i:0;i:5;}','drupal_get_form','a:2:{i:0;s:30:\"user_admin_role_delete_confirm\";i:1;i:5;}',62,6,'','admin/people/permissions/roles/delete/%','Delete role','t','',6,'','',0,'modules/user/user.admin.inc','',0,'','a:0:{}'),('admin/people/permissions/roles/edit/%','a:1:{i:5;s:14:\"user_role_load\";}','','user_role_edit_access','a:1:{i:0;i:5;}','drupal_get_form','a:2:{i:0;s:15:\"user_admin_role\";i:1;i:5;}',62,6,'','admin/people/permissions/roles/edit/%','Edit role','t','',6,'','',0,'modules/user/user.admin.inc','',0,'','a:0:{}'),('admin/reports','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','system_admin_menu_block_page','a:0:{}',3,2,'','admin/reports','Reports','t','',6,'View reports, updates, and errors.','left',5,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/reports/apachesolr','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','apachesolr_index_report','a:0:{}',7,3,'','admin/reports/apachesolr','Apache Solr search index','t','',6,'Information about the contents of the index the server','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/reports/apachesolr/%','a:1:{i:3;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:19:\"access site reports\";}','apachesolr_index_report','a:1:{i:0;i:3;}',14,4,'','admin/reports/apachesolr/%','Apache Solr search index','t','',6,'Information about the contents of the index the server','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/reports/apachesolr/%/conf','a:1:{i:3;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:19:\"access site reports\";}','apachesolr_config_files_overview','a:0:{}',29,5,'admin/reports/apachesolr/%','admin/reports/apachesolr/%','Configuration files','t','',132,'','',5,'sites/all/modules/apachesolr/apachesolr.admin.inc','',1,'','a:0:{}'),('admin/reports/apachesolr/%/conf/%','a:2:{i:3;s:27:\"apachesolr_environment_load\";i:5;N;}','','user_access','a:1:{i:0;s:19:\"access site reports\";}','apachesolr_config_file','a:2:{i:0;i:5;i:1;i:3;}',58,6,'','admin/reports/apachesolr/%/conf/%','Configuration file','t','',0,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',0,'','a:0:{}'),('admin/reports/apachesolr/%/index','a:1:{i:3;s:27:\"apachesolr_environment_load\";}','','user_access','a:1:{i:0;s:19:\"access site reports\";}','apachesolr_index_report','a:1:{i:0;i:3;}',29,5,'admin/reports/apachesolr/%','admin/reports/apachesolr/%','Search index','t','',140,'','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc','',1,'','a:0:{}'),('admin/reports/civicrm_error','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:22:\"civicrm_error_settings\";}',7,3,'','admin/reports/civicrm_error','CiviCRM Error Handler','t','',6,'Email critical CiviCRM errors.','',0,'','',0,'','a:0:{}'),('admin/reports/fields','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','field_ui_fields_list','a:0:{}',7,3,'','admin/reports/fields','Field list','t','',6,'Overview of fields on all entity types.','',0,'modules/field_ui/field_ui.admin.inc','',0,'','a:0:{}'),('admin/reports/status','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_status','a:0:{}',7,3,'','admin/reports/status','Status report','t','',6,'Get a status report about your site\'s operation and any detected problems.','',-60,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/reports/status/php','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_php','a:0:{}',15,4,'','admin/reports/status/php','PHP','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/reports/status/rebuild','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','drupal_get_form','a:1:{i:0;s:30:\"node_configure_rebuild_confirm\";}',15,4,'','admin/reports/status/rebuild','Rebuild permissions','t','',0,'','',0,'modules/node/node.admin.inc','',0,'','a:0:{}'),('admin/reports/status/run-cron','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_run_cron','a:0:{}',15,4,'','admin/reports/status/run-cron','Run cron','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/structure','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',3,2,'','admin/structure','Structure','t','',6,'Administer blocks, content types, menus, etc.','right',-8,'modules/system/system.admin.inc','',0,'','a:0:{}'),('admin/structure/block','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','block_admin_display','a:1:{i:0;s:10:\"rayCivicrm\";}',7,3,'','admin/structure/block','Blocks','t','',6,'Configure what block content appears in your site\'s sidebars and other regions.','',0,'modules/block/block.admin.inc','',0,'','a:0:{}'),('admin/structure/block/add','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:20:\"block_add_block_form\";}',15,4,'admin/structure/block','admin/structure/block','Add block','t','',388,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/demo/bartik','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:25:\"themes/bartik/bartik.info\";s:4:\"name\";s:6:\"bartik\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:6:\"Bartik\";s:11:\"description\";s:48:\"A flexible, recolorable theme with many regions.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:3:{s:14:\"css/layout.css\";s:28:\"themes/bartik/css/layout.css\";s:13:\"css/style.css\";s:27:\"themes/bartik/css/style.css\";s:14:\"css/colors.css\";s:28:\"themes/bartik/css/colors.css\";}s:5:\"print\";a:1:{s:13:\"css/print.css\";s:27:\"themes/bartik/css/print.css\";}}s:7:\"regions\";a:17:{s:6:\"header\";s:6:\"Header\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";s:11:\"highlighted\";s:11:\"Highlighted\";s:8:\"featured\";s:8:\"Featured\";s:7:\"content\";s:7:\"Content\";s:13:\"sidebar_first\";s:13:\"Sidebar first\";s:14:\"sidebar_second\";s:14:\"Sidebar second\";s:14:\"triptych_first\";s:14:\"Triptych first\";s:15:\"triptych_middle\";s:15:\"Triptych middle\";s:13:\"triptych_last\";s:13:\"Triptych last\";s:18:\"footer_firstcolumn\";s:19:\"Footer first column\";s:19:\"footer_secondcolumn\";s:20:\"Footer second column\";s:18:\"footer_thirdcolumn\";s:19:\"Footer third column\";s:19:\"footer_fourthcolumn\";s:20:\"Footer fourth column\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"settings\";a:1:{s:20:\"shortcut_module_link\";s:1:\"0\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:28:\"themes/bartik/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:3:{s:14:\"css/layout.css\";s:28:\"themes/bartik/css/layout.css\";s:13:\"css/style.css\";s:27:\"themes/bartik/css/style.css\";s:14:\"css/colors.css\";s:28:\"themes/bartik/css/colors.css\";}s:5:\"print\";a:1:{s:13:\"css/print.css\";s:27:\"themes/bartik/css/print.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_demo','a:1:{i:0;s:6:\"bartik\";}',31,5,'','admin/structure/block/demo/bartik','Bartik','t','',0,'','',0,'modules/block/block.admin.inc','',0,'_block_custom_theme','a:1:{i:0;s:6:\"bartik\";}'),('admin/structure/block/demo/Bluebird','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":13:{s:8:\"filename\";s:43:\"sites/default/themes/Bluebird/Bluebird.info\";s:4:\"name\";s:8:\"Bluebird\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:12:{s:4:\"name\";s:8:\"Bluebird\";s:7:\"project\";s:8:\"Bluebird\";s:11:\"description\";s:53:\"Drupal base theme built with Blueprint CSS framework.\";s:4:\"core\";s:3:\"7.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:17:\"screen,projection\";a:4:{s:16:\"css/Bluebird.css\";s:46:\"sites/default/themes/Bluebird/css/Bluebird.css\";s:18:\"nyss_skin/skin.css\";s:48:\"sites/default/themes/Bluebird/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:55:\"sites/default/themes/Bluebird/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:43:\"sites/default/themes/Bluebird/css/style.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:56:\"sites/default/themes/Bluebird/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:52:\"sites/default/themes/Bluebird/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:48:\"sites/default/themes/Bluebird/scripts/general.js\";}s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:4:\"help\";s:4:\"Help\";}s:8:\"features\";a:9:{i:0;s:4:\"name\";i:1;s:6:\"slogan\";i:2;s:7:\"mission\";i:3;s:17:\"node_user_picture\";i:4;s:20:\"comment_user_picture\";i:5;s:6:\"search\";i:6;s:7:\"favicon\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:44:\"sites/default/themes/Bluebird/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:17:\"screen,projection\";a:4:{s:16:\"css/Bluebird.css\";s:46:\"sites/default/themes/Bluebird/css/Bluebird.css\";s:18:\"nyss_skin/skin.css\";s:48:\"sites/default/themes/Bluebird/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:55:\"sites/default/themes/Bluebird/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:43:\"sites/default/themes/Bluebird/css/style.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:56:\"sites/default/themes/Bluebird/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:52:\"sites/default/themes/Bluebird/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:48:\"sites/default/themes/Bluebird/scripts/general.js\";}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_demo','a:1:{i:0;s:8:\"Bluebird\";}',31,5,'','admin/structure/block/demo/Bluebird','Bluebird','t','',0,'','',0,'modules/block/block.admin.inc','',0,'_block_custom_theme','a:1:{i:0;s:8:\"Bluebird\";}'),('admin/structure/block/demo/garland','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:27:\"themes/garland/garland.info\";s:4:\"name\";s:7:\"garland\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:7:\"Garland\";s:11:\"description\";s:111:\"A multi-column theme which can be configured to modify colors and switch between fixed and fluid width layouts.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:8:\"settings\";a:1:{s:13:\"garland_width\";s:5:\"fluid\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"regions\";a:9:{s:13:\"sidebar_first\";s:12:\"Left sidebar\";s:14:\"sidebar_second\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:11:\"highlighted\";s:11:\"Highlighted\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";}s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:29:\"themes/garland/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_demo','a:1:{i:0;s:7:\"garland\";}',31,5,'','admin/structure/block/demo/garland','Garland','t','',0,'','',0,'modules/block/block.admin.inc','',0,'_block_custom_theme','a:1:{i:0;s:7:\"garland\";}'),('admin/structure/block/demo/seven','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:23:\"themes/seven/seven.info\";s:4:\"name\";s:5:\"seven\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:5:\"Seven\";s:11:\"description\";s:65:\"A simple one-column, tableless, fluid width administration theme.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:1:{s:6:\"screen\";a:2:{s:9:\"reset.css\";s:22:\"themes/seven/reset.css\";s:9:\"style.css\";s:22:\"themes/seven/style.css\";}}s:8:\"settings\";a:1:{s:20:\"shortcut_module_link\";s:1:\"1\";}s:7:\"regions\";a:5:{s:7:\"content\";s:7:\"Content\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";s:13:\"sidebar_first\";s:13:\"First sidebar\";}s:14:\"regions_hidden\";a:3:{i:0;s:13:\"sidebar_first\";i:1;s:8:\"page_top\";i:2;s:11:\"page_bottom\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:27:\"themes/seven/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:6:\"screen\";a:2:{s:9:\"reset.css\";s:22:\"themes/seven/reset.css\";s:9:\"style.css\";s:22:\"themes/seven/style.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_demo','a:1:{i:0;s:5:\"seven\";}',31,5,'','admin/structure/block/demo/seven','Seven','t','',0,'','',0,'modules/block/block.admin.inc','',0,'_block_custom_theme','a:1:{i:0;s:5:\"seven\";}'),('admin/structure/block/demo/stark','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:23:\"themes/stark/stark.info\";s:4:\"name\";s:5:\"stark\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:15:{s:4:\"name\";s:5:\"Stark\";s:11:\"description\";s:208:\"This theme demonstrates Drupal\'s default HTML markup and CSS styles. To learn how to build your own theme and override Drupal\'s default code, see the <a href=\"http://drupal.org/theme-guide\">Theming Guide</a>.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:10:\"layout.css\";s:23:\"themes/stark/layout.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"regions\";a:9:{s:13:\"sidebar_first\";s:12:\"Left sidebar\";s:14:\"sidebar_second\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:11:\"highlighted\";s:11:\"Highlighted\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";}s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:27:\"themes/stark/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:10:\"layout.css\";s:23:\"themes/stark/layout.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_demo','a:1:{i:0;s:5:\"stark\";}',31,5,'','admin/structure/block/demo/stark','Stark','t','',0,'','',0,'modules/block/block.admin.inc','',0,'_block_custom_theme','a:1:{i:0;s:5:\"stark\";}'),('admin/structure/block/list/bartik','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:25:\"themes/bartik/bartik.info\";s:4:\"name\";s:6:\"bartik\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:6:\"Bartik\";s:11:\"description\";s:48:\"A flexible, recolorable theme with many regions.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:3:{s:14:\"css/layout.css\";s:28:\"themes/bartik/css/layout.css\";s:13:\"css/style.css\";s:27:\"themes/bartik/css/style.css\";s:14:\"css/colors.css\";s:28:\"themes/bartik/css/colors.css\";}s:5:\"print\";a:1:{s:13:\"css/print.css\";s:27:\"themes/bartik/css/print.css\";}}s:7:\"regions\";a:17:{s:6:\"header\";s:6:\"Header\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";s:11:\"highlighted\";s:11:\"Highlighted\";s:8:\"featured\";s:8:\"Featured\";s:7:\"content\";s:7:\"Content\";s:13:\"sidebar_first\";s:13:\"Sidebar first\";s:14:\"sidebar_second\";s:14:\"Sidebar second\";s:14:\"triptych_first\";s:14:\"Triptych first\";s:15:\"triptych_middle\";s:15:\"Triptych middle\";s:13:\"triptych_last\";s:13:\"Triptych last\";s:18:\"footer_firstcolumn\";s:19:\"Footer first column\";s:19:\"footer_secondcolumn\";s:20:\"Footer second column\";s:18:\"footer_thirdcolumn\";s:19:\"Footer third column\";s:19:\"footer_fourthcolumn\";s:20:\"Footer fourth column\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"settings\";a:1:{s:20:\"shortcut_module_link\";s:1:\"0\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:28:\"themes/bartik/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:3:{s:14:\"css/layout.css\";s:28:\"themes/bartik/css/layout.css\";s:13:\"css/style.css\";s:27:\"themes/bartik/css/style.css\";s:14:\"css/colors.css\";s:28:\"themes/bartik/css/colors.css\";}s:5:\"print\";a:1:{s:13:\"css/print.css\";s:27:\"themes/bartik/css/print.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:6:\"bartik\";}',31,5,'admin/structure/block','admin/structure/block','Bartik','t','',132,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/bartik/add','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:20:\"block_add_block_form\";}',63,6,'admin/structure/block/list/bartik','admin/structure/block','Add block','t','',388,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/Bluebird','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":13:{s:8:\"filename\";s:43:\"sites/default/themes/Bluebird/Bluebird.info\";s:4:\"name\";s:8:\"Bluebird\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:12:{s:4:\"name\";s:8:\"Bluebird\";s:7:\"project\";s:8:\"Bluebird\";s:11:\"description\";s:53:\"Drupal base theme built with Blueprint CSS framework.\";s:4:\"core\";s:3:\"7.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:17:\"screen,projection\";a:4:{s:16:\"css/Bluebird.css\";s:46:\"sites/default/themes/Bluebird/css/Bluebird.css\";s:18:\"nyss_skin/skin.css\";s:48:\"sites/default/themes/Bluebird/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:55:\"sites/default/themes/Bluebird/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:43:\"sites/default/themes/Bluebird/css/style.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:56:\"sites/default/themes/Bluebird/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:52:\"sites/default/themes/Bluebird/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:48:\"sites/default/themes/Bluebird/scripts/general.js\";}s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:4:\"help\";s:4:\"Help\";}s:8:\"features\";a:9:{i:0;s:4:\"name\";i:1;s:6:\"slogan\";i:2;s:7:\"mission\";i:3;s:17:\"node_user_picture\";i:4;s:20:\"comment_user_picture\";i:5;s:6:\"search\";i:6;s:7:\"favicon\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:44:\"sites/default/themes/Bluebird/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:17:\"screen,projection\";a:4:{s:16:\"css/Bluebird.css\";s:46:\"sites/default/themes/Bluebird/css/Bluebird.css\";s:18:\"nyss_skin/skin.css\";s:48:\"sites/default/themes/Bluebird/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:55:\"sites/default/themes/Bluebird/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:43:\"sites/default/themes/Bluebird/css/style.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:56:\"sites/default/themes/Bluebird/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:52:\"sites/default/themes/Bluebird/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:48:\"sites/default/themes/Bluebird/scripts/general.js\";}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:8:\"Bluebird\";}',31,5,'admin/structure/block','admin/structure/block','Bluebird','t','',132,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/Bluebird/add','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:20:\"block_add_block_form\";}',63,6,'admin/structure/block/list/Bluebird','admin/structure/block','Add block','t','',388,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/garland','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:27:\"themes/garland/garland.info\";s:4:\"name\";s:7:\"garland\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:7:\"Garland\";s:11:\"description\";s:111:\"A multi-column theme which can be configured to modify colors and switch between fixed and fluid width layouts.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:8:\"settings\";a:1:{s:13:\"garland_width\";s:5:\"fluid\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"regions\";a:9:{s:13:\"sidebar_first\";s:12:\"Left sidebar\";s:14:\"sidebar_second\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:11:\"highlighted\";s:11:\"Highlighted\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";}s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:29:\"themes/garland/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:7:\"garland\";}',31,5,'admin/structure/block','admin/structure/block','Garland','t','',132,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/garland/add','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:20:\"block_add_block_form\";}',63,6,'admin/structure/block/list/garland','admin/structure/block','Add block','t','',388,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/seven','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:23:\"themes/seven/seven.info\";s:4:\"name\";s:5:\"seven\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:16:{s:4:\"name\";s:5:\"Seven\";s:11:\"description\";s:65:\"A simple one-column, tableless, fluid width administration theme.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:1:{s:6:\"screen\";a:2:{s:9:\"reset.css\";s:22:\"themes/seven/reset.css\";s:9:\"style.css\";s:22:\"themes/seven/style.css\";}}s:8:\"settings\";a:1:{s:20:\"shortcut_module_link\";s:1:\"1\";}s:7:\"regions\";a:5:{s:7:\"content\";s:7:\"Content\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";s:13:\"sidebar_first\";s:13:\"First sidebar\";}s:14:\"regions_hidden\";a:3:{i:0;s:13:\"sidebar_first\";i:1;s:8:\"page_top\";i:2;s:11:\"page_bottom\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:27:\"themes/seven/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:6:\"screen\";a:2:{s:9:\"reset.css\";s:22:\"themes/seven/reset.css\";s:9:\"style.css\";s:22:\"themes/seven/style.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:5:\"seven\";}',31,5,'admin/structure/block','admin/structure/block','Seven','t','',132,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/seven/add','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:20:\"block_add_block_form\";}',63,6,'admin/structure/block/list/seven','admin/structure/block','Add block','t','',388,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/stark','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:23:\"themes/stark/stark.info\";s:4:\"name\";s:5:\"stark\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:15:{s:4:\"name\";s:5:\"Stark\";s:11:\"description\";s:208:\"This theme demonstrates Drupal\'s default HTML markup and CSS styles. To learn how to build your own theme and override Drupal\'s default code, see the <a href=\"http://drupal.org/theme-guide\">Theming Guide</a>.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:10:\"layout.css\";s:23:\"themes/stark/layout.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"regions\";a:9:{s:13:\"sidebar_first\";s:12:\"Left sidebar\";s:14:\"sidebar_second\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:11:\"highlighted\";s:11:\"Highlighted\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";}s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:27:\"themes/stark/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}s:6:\"prefix\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:10:\"layout.css\";s:23:\"themes/stark/layout.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:5:\"stark\";}',31,5,'admin/structure/block','admin/structure/block','Stark','t','',132,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/list/stark/add','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:20:\"block_add_block_form\";}',63,6,'admin/structure/block/list/stark','admin/structure/block','Add block','t','',388,'','',0,'modules/block/block.admin.inc','',1,'','a:0:{}'),('admin/structure/block/manage/%/%','a:2:{i:4;N;i:5;N;}','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:3:{i:0;s:21:\"block_admin_configure\";i:1;i:4;i:2;i:5;}',60,6,'','admin/structure/block/manage/%/%','Configure block','t','',6,'','',0,'modules/block/block.admin.inc','',0,'','a:0:{}'),('admin/structure/block/manage/%/%/configure','a:2:{i:4;N;i:5;N;}','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:3:{i:0;s:21:\"block_admin_configure\";i:1;i:4;i:2;i:5;}',121,7,'admin/structure/block/manage/%/%','admin/structure/block/manage/%/%','Configure block','t','',140,'','',0,'modules/block/block.admin.inc','',2,'','a:0:{}'),('admin/structure/block/manage/%/%/delete','a:2:{i:4;N;i:5;N;}','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:3:{i:0;s:25:\"block_custom_block_delete\";i:1;i:4;i:2;i:5;}',121,7,'admin/structure/block/manage/%/%','admin/structure/block/manage/%/%','Delete block','t','',132,'','',0,'modules/block/block.admin.inc','',0,'','a:0:{}'),('admin/structure/menu','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','menu_overview_page','a:0:{}',7,3,'','admin/structure/menu','Menus','t','',6,'Add new menus to your site, edit existing menus, and rename and reorganize menu links.','',0,'modules/menu/menu.admin.inc','',0,'','a:0:{}'),('admin/structure/menu/add','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:2:{i:0;s:14:\"menu_edit_menu\";i:1;s:3:\"add\";}',15,4,'admin/structure/menu','admin/structure/menu','Add menu','t','',388,'','',0,'modules/menu/menu.admin.inc','',1,'','a:0:{}'),('admin/structure/menu/item/%/delete','a:1:{i:4;s:14:\"menu_link_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','menu_item_delete_page','a:1:{i:0;i:4;}',61,6,'','admin/structure/menu/item/%/delete','Delete menu link','t','',6,'','',0,'modules/menu/menu.admin.inc','',0,'','a:0:{}'),('admin/structure/menu/item/%/edit','a:1:{i:4;s:14:\"menu_link_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:4:{i:0;s:14:\"menu_edit_item\";i:1;s:4:\"edit\";i:2;i:4;i:3;N;}',61,6,'','admin/structure/menu/item/%/edit','Edit menu link','t','',6,'','',0,'modules/menu/menu.admin.inc','',0,'','a:0:{}'),('admin/structure/menu/item/%/reset','a:1:{i:4;s:14:\"menu_link_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:2:{i:0;s:23:\"menu_reset_item_confirm\";i:1;i:4;}',61,6,'','admin/structure/menu/item/%/reset','Reset menu link','t','',6,'','',0,'modules/menu/menu.admin.inc','',0,'','a:0:{}'),('admin/structure/menu/list','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','menu_overview_page','a:0:{}',15,4,'admin/structure/menu','admin/structure/menu','List menus','t','',140,'','',-10,'modules/menu/menu.admin.inc','',1,'','a:0:{}'),('admin/structure/menu/manage/%','a:1:{i:4;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:2:{i:0;s:18:\"menu_overview_form\";i:1;i:4;}',30,5,'','admin/structure/menu/manage/%','Customize menu','menu_overview_title','a:1:{i:0;i:4;}',6,'','',0,'modules/menu/menu.admin.inc','',0,'','a:0:{}'),('admin/structure/menu/manage/%/add','a:1:{i:4;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:4:{i:0;s:14:\"menu_edit_item\";i:1;s:3:\"add\";i:2;N;i:3;i:4;}',61,6,'admin/structure/menu/manage/%','admin/structure/menu/manage/%','Add link','t','',388,'','',0,'modules/menu/menu.admin.inc','',1,'','a:0:{}'),('admin/structure/menu/manage/%/delete','a:1:{i:4;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','menu_delete_menu_page','a:1:{i:0;i:4;}',61,6,'','admin/structure/menu/manage/%/delete','Delete menu','t','',6,'','',0,'modules/menu/menu.admin.inc','',0,'','a:0:{}'),('admin/structure/menu/manage/%/edit','a:1:{i:4;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:3:{i:0;s:14:\"menu_edit_menu\";i:1;s:4:\"edit\";i:2;i:4;}',61,6,'admin/structure/menu/manage/%','admin/structure/menu/manage/%','Edit menu','t','',132,'','',0,'modules/menu/menu.admin.inc','',3,'','a:0:{}'),('admin/structure/menu/manage/%/list','a:1:{i:4;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:2:{i:0;s:18:\"menu_overview_form\";i:1;i:4;}',61,6,'admin/structure/menu/manage/%','admin/structure/menu/manage/%','List links','t','',140,'','',-10,'modules/menu/menu.admin.inc','',3,'','a:0:{}'),('admin/structure/menu/parents','','','user_access','a:1:{i:0;b:1;}','menu_parent_options_js','a:0:{}',15,4,'','admin/structure/menu/parents','Parent menu items','t','',0,'','',0,'','',0,'','a:0:{}'),('admin/structure/menu/settings','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:1:{i:0;s:14:\"menu_configure\";}',15,4,'admin/structure/menu','admin/structure/menu','Settings','t','',132,'','',5,'modules/menu/menu.admin.inc','',1,'','a:0:{}'),('admin/structure/trigger','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','trigger_assign','a:0:{}',7,3,'','admin/structure/trigger','Triggers','t','',6,'Configure when to execute actions.','',0,'modules/trigger/trigger.admin.inc','',0,'','a:0:{}'),('admin/structure/trigger/node','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','trigger_assign','a:1:{i:0;s:4:\"node\";}',15,4,'admin/structure/trigger','admin/structure/trigger','Node','t','',132,'','',0,'modules/trigger/trigger.admin.inc','',1,'','a:0:{}'),('admin/structure/trigger/system','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','trigger_assign','a:1:{i:0;s:6:\"system\";}',15,4,'admin/structure/trigger','admin/structure/trigger','System','t','',132,'','',0,'modules/trigger/trigger.admin.inc','',1,'','a:0:{}'),('admin/structure/trigger/unassign','','','trigger_menu_unassign_access','a:0:{}','drupal_get_form','a:1:{i:0;s:16:\"trigger_unassign\";}',15,4,'','admin/structure/trigger/unassign','Unassign','t','',4,'Unassign an action from a trigger.','',0,'modules/trigger/trigger.admin.inc','',0,'','a:0:{}'),('admin/structure/trigger/user','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','trigger_assign','a:1:{i:0;s:4:\"user\";}',15,4,'admin/structure/trigger','admin/structure/trigger','User','t','',132,'','',0,'modules/trigger/trigger.admin.inc','',1,'','a:0:{}'),('admin/structure/types','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','node_overview_types','a:0:{}',7,3,'','admin/structure/types','Content types','t','',6,'Manage content types, including default status, front page promotion, comment settings, etc.','',0,'modules/node/content_types.inc','',0,'','a:0:{}'),('admin/structure/types/add','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:1:{i:0;s:14:\"node_type_form\";}',15,4,'admin/structure/types','admin/structure/types','Add content type','t','',388,'','',0,'modules/node/content_types.inc','',1,'','a:0:{}'),('admin/structure/types/list','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','node_overview_types','a:0:{}',15,4,'admin/structure/types','admin/structure/types','List','t','',140,'','',-10,'modules/node/content_types.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%','a:1:{i:4;s:14:\"node_type_load\";}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:14:\"node_type_form\";i:1;i:4;}',30,5,'','admin/structure/types/manage/%','Edit content type','node_type_page_title','a:1:{i:0;i:4;}',6,'','',0,'modules/node/content_types.inc','',0,'','a:0:{}'),('admin/structure/types/manage/%/delete','a:1:{i:4;s:14:\"node_type_load\";}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:24:\"node_type_delete_confirm\";i:1;i:4;}',61,6,'','admin/structure/types/manage/%/delete','Delete','t','',6,'','',0,'modules/node/content_types.inc','',0,'','a:0:{}'),('admin/structure/types/manage/%/display','a:1:{i:4;s:14:\"node_type_load\";}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"node\";i:2;i:4;i:3;s:7:\"default\";}',61,6,'admin/structure/types/manage/%','admin/structure/types/manage/%','Manage display','t','',132,'','',2,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/display/default','a:1:{i:4;s:14:\"node_type_load\";}','','_field_ui_view_mode_menu_access','a:5:{i:0;s:4:\"node\";i:1;i:4;i:2;s:7:\"default\";i:3;s:11:\"user_access\";i:4;s:24:\"administer content types\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"node\";i:2;i:4;i:3;s:7:\"default\";}',123,7,'admin/structure/types/manage/%/display','admin/structure/types/manage/%','Default','t','',140,'','',-10,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/display/full','a:1:{i:4;s:14:\"node_type_load\";}','','_field_ui_view_mode_menu_access','a:5:{i:0;s:4:\"node\";i:1;i:4;i:2;s:4:\"full\";i:3;s:11:\"user_access\";i:4;s:24:\"administer content types\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"node\";i:2;i:4;i:3;s:4:\"full\";}',123,7,'admin/structure/types/manage/%/display','admin/structure/types/manage/%','Full content','t','',132,'','',0,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/display/rss','a:1:{i:4;s:14:\"node_type_load\";}','','_field_ui_view_mode_menu_access','a:5:{i:0;s:4:\"node\";i:1;i:4;i:2;s:3:\"rss\";i:3;s:11:\"user_access\";i:4;s:24:\"administer content types\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"node\";i:2;i:4;i:3;s:3:\"rss\";}',123,7,'admin/structure/types/manage/%/display','admin/structure/types/manage/%','RSS','t','',132,'','',2,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/display/search_index','a:1:{i:4;s:14:\"node_type_load\";}','','_field_ui_view_mode_menu_access','a:5:{i:0;s:4:\"node\";i:1;i:4;i:2;s:12:\"search_index\";i:3;s:11:\"user_access\";i:4;s:24:\"administer content types\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"node\";i:2;i:4;i:3;s:12:\"search_index\";}',123,7,'admin/structure/types/manage/%/display','admin/structure/types/manage/%','Search index','t','',132,'','',3,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/display/search_result','a:1:{i:4;s:14:\"node_type_load\";}','','_field_ui_view_mode_menu_access','a:5:{i:0;s:4:\"node\";i:1;i:4;i:2;s:13:\"search_result\";i:3;s:11:\"user_access\";i:4;s:24:\"administer content types\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"node\";i:2;i:4;i:3;s:13:\"search_result\";}',123,7,'admin/structure/types/manage/%/display','admin/structure/types/manage/%','Search result','t','',132,'','',4,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/display/teaser','a:1:{i:4;s:14:\"node_type_load\";}','','_field_ui_view_mode_menu_access','a:5:{i:0;s:4:\"node\";i:1;i:4;i:2;s:6:\"teaser\";i:3;s:11:\"user_access\";i:4;s:24:\"administer content types\";}','drupal_get_form','a:4:{i:0;s:30:\"field_ui_display_overview_form\";i:1;s:4:\"node\";i:2;i:4;i:3;s:6:\"teaser\";}',123,7,'admin/structure/types/manage/%/display','admin/structure/types/manage/%','Teaser','t','',132,'','',1,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/edit','a:1:{i:4;s:14:\"node_type_load\";}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:14:\"node_type_form\";i:1;i:4;}',61,6,'admin/structure/types/manage/%','admin/structure/types/manage/%','Edit','t','',140,'','',0,'modules/node/content_types.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/fields','a:1:{i:4;s:14:\"node_type_load\";}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:3:{i:0;s:28:\"field_ui_field_overview_form\";i:1;s:4:\"node\";i:2;i:4;}',61,6,'admin/structure/types/manage/%','admin/structure/types/manage/%','Manage fields','t','',132,'','',1,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/fields/%','a:2:{i:4;a:1:{s:14:\"node_type_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}i:6;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:24:\"field_ui_field_edit_form\";i:1;i:6;}',122,7,'','admin/structure/types/manage/%/fields/%','','field_ui_menu_title','a:1:{i:0;i:6;}',6,'','',0,'modules/field_ui/field_ui.admin.inc','',0,'','a:0:{}'),('admin/structure/types/manage/%/fields/%/delete','a:2:{i:4;a:1:{s:14:\"node_type_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}i:6;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:26:\"field_ui_field_delete_form\";i:1;i:6;}',245,8,'admin/structure/types/manage/%/fields/%','admin/structure/types/manage/%/fields/%','Delete','t','',132,'','',10,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/fields/%/edit','a:2:{i:4;a:1:{s:14:\"node_type_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}i:6;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:24:\"field_ui_field_edit_form\";i:1;i:6;}',245,8,'admin/structure/types/manage/%/fields/%','admin/structure/types/manage/%/fields/%','Edit','t','',140,'','',0,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/fields/%/field-settings','a:2:{i:4;a:1:{s:14:\"node_type_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}i:6;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:28:\"field_ui_field_settings_form\";i:1;i:6;}',245,8,'admin/structure/types/manage/%/fields/%','admin/structure/types/manage/%/fields/%','Field settings','t','',132,'','',0,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/structure/types/manage/%/fields/%/widget-type','a:2:{i:4;a:1:{s:14:\"node_type_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}i:6;a:1:{s:18:\"field_ui_menu_load\";a:4:{i:0;s:4:\"node\";i:1;i:4;i:2;s:1:\"4\";i:3;s:4:\"%map\";}}}','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:25:\"field_ui_widget_type_form\";i:1;i:6;}',245,8,'admin/structure/types/manage/%/fields/%','admin/structure/types/manage/%/fields/%','Widget type','t','',132,'','',0,'modules/field_ui/field_ui.admin.inc','',1,'','a:0:{}'),('admin/tasks','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',3,2,'admin','admin','Tasks','t','',140,'','',-20,'modules/system/system.admin.inc','',1,'','a:0:{}'),('admin/user/user/create','','','user_access','a:1:{i:0;s:12:\"create users\";}','system_admin_menu_block_page','a:0:{}',15,4,'','admin/user/user/create','','t','',6,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('backupdata','','','user_access','a:1:{i:0;s:29:\"export print production files\";}','nyss_backup_page','a:0:{}',1,1,'','backupdata','Backup/Restore Instance','t','',6,'','',0,'','',0,'','a:0:{}'),('batch','','','1','a:0:{}','system_batch_page','a:0:{}',1,1,'','batch','','t','',0,'','',0,'modules/system/system.admin.inc','',0,'_system_batch_theme','a:0:{}'),('civicrm','','','1','a:0:{}','civicrm_invoke','a:0:{}',1,1,'','civicrm','CiviCRM','t','',4,'','',0,'','',0,'','a:0:{}'),('filter/tips','','','1','a:0:{}','filter_tips_long','a:0:{}',3,2,'','filter/tips','Compose tips','t','',20,'','',0,'modules/filter/filter.pages.inc','',0,'','a:0:{}'),('front_page','','','1','a:0:{}','front_page','a:0:{}',1,1,'','front_page','','t','',20,'','',0,'','',0,'','a:0:{}'),('importdata','','','user_access','a:1:{i:0;s:23:\"import print production\";}','nyss_ioimportdata_page','a:0:{}',1,1,'','importdata','Import Data','t','',20,'','',0,'','',0,'','a:0:{}'),('node','','','user_access','a:1:{i:0;s:14:\"access content\";}','node_page_default','a:0:{}',1,1,'','node','','t','',0,'','',0,'','',0,'','a:0:{}'),('node/%','a:1:{i:1;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:4:\"view\";i:1;i:1;}','node_page_view','a:1:{i:0;i:1;}',2,2,'','node/%','','node_page_title','a:1:{i:0;i:1;}',6,'','',0,'','',0,'','a:0:{}'),('node/%/delete','a:1:{i:1;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:6:\"delete\";i:1;i:1;}','drupal_get_form','a:2:{i:0;s:19:\"node_delete_confirm\";i:1;i:1;}',5,3,'node/%','node/%','Delete','t','',132,'','',1,'modules/node/node.pages.inc','',2,'','a:0:{}'),('node/%/edit','a:1:{i:1;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:6:\"update\";i:1;i:1;}','node_page_edit','a:1:{i:0;i:1;}',5,3,'node/%','node/%','Edit','t','',132,'','',0,'modules/node/node.pages.inc','',3,'','a:0:{}'),('node/%/revisions','a:1:{i:1;s:9:\"node_load\";}','','_node_revision_access','a:1:{i:0;i:1;}','node_revision_overview','a:1:{i:0;i:1;}',5,3,'node/%','node/%','Revisions','t','',132,'','',2,'modules/node/node.pages.inc','',1,'','a:0:{}'),('node/%/revisions/%/delete','a:2:{i:1;a:1:{s:9:\"node_load\";a:1:{i:0;i:3;}}i:3;N;}','','_node_revision_access','a:2:{i:0;i:1;i:1;s:6:\"delete\";}','drupal_get_form','a:2:{i:0;s:28:\"node_revision_delete_confirm\";i:1;i:1;}',21,5,'','node/%/revisions/%/delete','Delete earlier revision','t','',6,'','',0,'modules/node/node.pages.inc','',0,'','a:0:{}'),('node/%/revisions/%/revert','a:2:{i:1;a:1:{s:9:\"node_load\";a:1:{i:0;i:3;}}i:3;N;}','','_node_revision_access','a:2:{i:0;i:1;i:1;s:6:\"update\";}','drupal_get_form','a:2:{i:0;s:28:\"node_revision_revert_confirm\";i:1;i:1;}',21,5,'','node/%/revisions/%/revert','Revert to earlier revision','t','',6,'','',0,'modules/node/node.pages.inc','',0,'','a:0:{}'),('node/%/revisions/%/view','a:2:{i:1;a:1:{s:9:\"node_load\";a:1:{i:0;i:3;}}i:3;N;}','','_node_revision_access','a:1:{i:0;i:1;}','node_show','a:2:{i:0;i:1;i:1;b:1;}',21,5,'','node/%/revisions/%/view','Revisions','t','',6,'','',0,'','',0,'','a:0:{}'),('node/%/view','a:1:{i:1;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:4:\"view\";i:1;i:1;}','node_page_view','a:1:{i:0;i:1;}',5,3,'node/%','node/%','View','t','',140,'','',-10,'','',1,'','a:0:{}'),('node/add','','','_node_add_access','a:0:{}','node_add_page','a:0:{}',3,2,'','node/add','Add content','t','',6,'','',0,'modules/node/node.pages.inc','',0,'','a:0:{}'),('node/add/page','','','node_access','a:2:{i:0;s:6:\"create\";i:1;s:4:\"page\";}','node_add','a:1:{i:0;s:4:\"page\";}',7,3,'','node/add/page','Page','check_plain','',6,'A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.','',0,'modules/node/node.pages.inc','',0,'','a:0:{}'),('node/add/story','','','node_access','a:2:{i:0;s:6:\"create\";i:1;s:5:\"story\";}','node_add','a:1:{i:0;s:5:\"story\";}',7,3,'','node/add/story','Story','check_plain','',6,'A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.','',0,'modules/node/node.pages.inc','',0,'','a:0:{}'),('nyss_getfile','','','user_access','a:1:{i:0;s:29:\"export print production files\";}','nyss_civihooks_getfile','a:0:{}',1,1,'','nyss_getfile','NYSS Retrieve file','t','',20,'','',0,'','',0,'','a:0:{}'),('rss.xml','','','user_access','a:1:{i:0;s:14:\"access content\";}','node_feed','a:0:{}',1,1,'','rss.xml','RSS feed','t','',0,'','',0,'','',0,'','a:0:{}'),('search','','','search_is_active','a:0:{}','search_view','a:0:{}',1,1,'','search','Search','t','',20,'','',0,'modules/search/search.pages.inc','',0,'','a:0:{}'),('search/node','','','_search_menu_access','a:1:{i:0;s:4:\"node\";}','search_view','a:2:{i:0;s:4:\"node\";i:1;s:0:\"\";}',3,2,'search','search','Content','t','',132,'','',0,'modules/search/search.pages.inc','',1,'','a:0:{}'),('search/node/%','a:1:{i:2;a:1:{s:14:\"menu_tail_load\";a:2:{i:0;s:4:\"%map\";i:1;s:6:\"%index\";}}}','a:1:{i:2;s:16:\"menu_tail_to_arg\";}','_search_menu_access','a:1:{i:0;s:4:\"node\";}','search_view','a:2:{i:0;s:4:\"node\";i:1;i:2;}',6,3,'search/site','search/site/%','Content','t','',132,'','',0,'modules/search/search.pages.inc','',1,'','a:0:{}'),('search/site','','','user_access','a:1:{i:0;s:14:\"search content\";}','apachesolr_search_custom_page','a:3:{i:0;s:11:\"core_search\";i:1;s:0:\"\";i:2;b:0;}',3,2,'search','search','Site','t','',132,'','',0,'sites/all/modules/apachesolr/apachesolr_search.pages.inc','',1,'','a:0:{}'),('search/site/%','a:1:{i:2;N;}','','user_access','a:1:{i:0;s:14:\"search content\";}','apachesolr_search_custom_page','a:3:{i:0;s:11:\"core_search\";i:1;i:2;i:2;b:0;}',6,3,'search/site','search/site/%','Site','t','',132,'','',0,'sites/all/modules/apachesolr/apachesolr_search.pages.inc','',1,'','a:0:{}'),('search/user','','','_search_menu_access','a:1:{i:0;s:4:\"user\";}','search_view','a:2:{i:0;s:4:\"user\";i:1;s:0:\"\";}',3,2,'search','search','Users','t','',132,'','',0,'modules/search/search.pages.inc','',1,'','a:0:{}'),('search/user/%','a:1:{i:2;a:1:{s:14:\"menu_tail_load\";a:2:{i:0;s:4:\"%map\";i:1;s:6:\"%index\";}}}','a:1:{i:2;s:16:\"menu_tail_to_arg\";}','_search_menu_access','a:1:{i:0;s:4:\"user\";}','search_view','a:2:{i:0;s:4:\"user\";i:1;i:2;}',6,3,'search/site','search/site/%','Users','t','',132,'','',0,'modules/search/search.pages.inc','',1,'','a:0:{}'),('system/ajax','','','1','a:0:{}','ajax_form_callback','a:0:{}',3,2,'','system/ajax','AHAH callback','t','',0,'','',0,'includes/form.inc','ajax_deliver',0,'ajax_base_page_theme','a:0:{}'),('system/files','','','1','a:0:{}','file_download','a:1:{i:0;s:7:\"private\";}',3,2,'','system/files','File download','t','',0,'','',0,'','',0,'','a:0:{}'),('system/temporary','','','1','a:0:{}','file_download','a:1:{i:0;s:9:\"temporary\";}',3,2,'','system/temporary','Temporary files','t','',0,'','',0,'','',0,'','a:0:{}'),('system/timezone','','','1','a:0:{}','system_timezone','a:0:{}',3,2,'','system/timezone','Time zone','t','',0,'','',0,'modules/system/system.admin.inc','',0,'','a:0:{}'),('user','','','1','a:0:{}','user_page','a:0:{}',1,1,'','user','User account','user_menu_title','',6,'','',-10,'modules/user/user.pages.inc','',0,'','a:0:{}'),('user/%','a:1:{i:1;s:9:\"user_load\";}','','user_view_access','a:1:{i:0;i:1;}','user_view_page','a:1:{i:0;i:1;}',2,2,'','user/%','My account','user_page_title','a:1:{i:0;i:1;}',6,'','',0,'','',0,'','a:0:{}'),('user/%/cancel','a:1:{i:1;s:9:\"user_load\";}','','userprotect_user_cancel_access','a:1:{i:0;i:1;}','drupal_get_form','a:2:{i:0;s:24:\"user_cancel_confirm_form\";i:1;i:1;}',5,3,'','user/%/cancel','Cancel account','t','',6,'','',0,'modules/user/user.pages.inc','',0,'','a:0:{}'),('user/%/cancel/confirm/%/%','a:3:{i:1;s:9:\"user_load\";i:4;N;i:5;N;}','','user_cancel_access','a:1:{i:0;i:1;}','user_cancel_confirm','a:3:{i:0;i:1;i:1;i:4;i:2;i:5;}',44,6,'','user/%/cancel/confirm/%/%','Confirm account cancellation','t','',6,'','',0,'modules/user/user.pages.inc','',0,'','a:0:{}'),('user/%/edit','a:1:{i:1;s:9:\"user_load\";}','','userprotect_user_edit_access','a:1:{i:0;i:1;}','drupal_get_form','a:2:{i:0;s:17:\"user_profile_form\";i:1;i:1;}',5,3,'user/%','user/%','Edit','t','',132,'','',0,'modules/user/user.pages.inc','',1,'','a:0:{}'),('user/%/edit/account','a:1:{i:1;a:1:{s:18:\"user_category_load\";a:2:{i:0;s:4:\"%map\";i:1;s:6:\"%index\";}}}','','userprotect_user_edit_access','a:1:{i:0;i:1;}','drupal_get_form','a:2:{i:0;s:17:\"user_profile_form\";i:1;i:1;}',11,4,'user/%/edit','user/%','Account','t','',140,'','',0,'modules/user/user.pages.inc','',1,'','a:0:{}'),('user/%/view','a:1:{i:1;s:9:\"user_load\";}','','user_view_access','a:1:{i:0;i:1;}','user_view_page','a:1:{i:0;i:1;}',5,3,'user/%','user/%','View','t','',140,'','',-10,'','',1,'','a:0:{}'),('user/autocomplete','','','user_access','a:1:{i:0;s:20:\"access user profiles\";}','user_autocomplete','a:0:{}',3,2,'','user/autocomplete','User autocomplete','t','',0,'','',0,'modules/user/user.pages.inc','',0,'','a:0:{}'),('user/login','','','user_is_anonymous','a:0:{}','user_page','a:0:{}',3,2,'user','user','Log in','t','',140,'','',0,'modules/user/user.pages.inc','',1,'','a:0:{}'),('user/logout','','','user_is_logged_in','a:0:{}','user_logout','a:0:{}',3,2,'','user/logout','Log out','t','',6,'','',10,'modules/user/user.pages.inc','',0,'','a:0:{}'),('user/password','','','ldap_authentication_show_reset_pwd','a:0:{}','drupal_get_form','a:1:{i:0;s:9:\"user_pass\";}',3,2,'user','user','Request new password','t','',132,'','',0,'modules/user/user.pages.inc','',1,'','a:0:{}'),('user/register','','','user_register_access','a:0:{}','drupal_get_form','a:1:{i:0;s:18:\"user_register_form\";}',3,2,'user','user','Create new account','t','',132,'','',0,'','',1,'','a:0:{}'),('user/reset/%/%/%','a:3:{i:2;N;i:3;N;i:4;N;}','','1','a:0:{}','drupal_get_form','a:4:{i:0;s:15:\"user_pass_reset\";i:1;i:2;i:2;i:3;i:3;i:4;}',24,5,'','user/reset/%/%/%','Reset password','t','',0,'','',0,'modules/user/user.pages.inc','',0,'','a:0:{}'),('userprotect/delete/%','a:1:{i:2;s:9:\"user_load\";}','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:3:{i:0;s:39:\"userprotect_protected_users_delete_form\";i:1;i:2;i:2;i:3;}',6,3,'','userprotect/delete/%','Delete protected user','t','',0,'','',0,'sites/all/modules/userprotect/userprotect.admin.inc','',0,'','a:0:{}');
/*!40000 ALTER TABLE `menu_router` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node`
--

DROP TABLE IF EXISTS `node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node` (
  `nid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vid` int(10) unsigned DEFAULT NULL COMMENT 'The current node_revision.vid version identifier.',
  `type` varchar(32) NOT NULL DEFAULT '',
  `language` varchar(12) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `created` int(11) NOT NULL DEFAULT '0',
  `changed` int(11) NOT NULL DEFAULT '0',
  `comment` int(11) NOT NULL DEFAULT '0',
  `promote` int(11) NOT NULL DEFAULT '0',
  `sticky` int(11) NOT NULL DEFAULT '0',
  `tnid` int(10) unsigned NOT NULL DEFAULT '0',
  `translate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nid`),
  UNIQUE KEY `vid` (`vid`),
  KEY `node_changed` (`changed`),
  KEY `node_created` (`created`),
  KEY `node_status_type` (`status`,`type`,`nid`),
  KEY `node_title_type` (`title`,`type`(4)),
  KEY `node_type` (`type`(4)),
  KEY `uid` (`uid`),
  KEY `tnid` (`tnid`),
  KEY `translate` (`translate`),
  KEY `node_frontpage` (`promote`,`status`,`sticky`,`created`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node`
--

LOCK TABLES `node` WRITE;
/*!40000 ALTER TABLE `node` DISABLE KEYS */;
INSERT INTO `node` VALUES (1,1,'page','und','Please Login',1,1,1289619175,1289619175,0,0,0,0,0),(2,2,'page','und','Page Not Found',1,1,1291653383,1291653383,0,0,0,0,0),(3,3,'page','','Please Login',0,1,1367678438,1367678438,0,0,0,0,0);
/*!40000 ALTER TABLE `node` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_access`
--

DROP TABLE IF EXISTS `node_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_access` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `realm` varchar(255) NOT NULL DEFAULT '',
  `grant_view` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `grant_update` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `grant_delete` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`nid`,`gid`,`realm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_access`
--

LOCK TABLES `node_access` WRITE;
/*!40000 ALTER TABLE `node_access` DISABLE KEYS */;
INSERT INTO `node_access` VALUES (0,0,'all',1,0,0);
/*!40000 ALTER TABLE `node_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_comment_statistics`
--

DROP TABLE IF EXISTS `node_comment_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_comment_statistics` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `last_comment_timestamp` int(11) NOT NULL DEFAULT '0' COMMENT 'The Unix timestamp of the last comment that was posted within this node, from comment.changed.',
  `last_comment_name` varchar(60) DEFAULT NULL,
  `last_comment_uid` int(11) NOT NULL DEFAULT '0',
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0',
  `cid` int(11) NOT NULL DEFAULT '0' COMMENT 'The comment.cid of the last comment.',
  PRIMARY KEY (`nid`),
  KEY `node_comment_timestamp` (`last_comment_timestamp`),
  KEY `last_comment_uid` (`last_comment_uid`),
  KEY `cid` (`cid`),
  KEY `comment_count` (`comment_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_comment_statistics`
--

LOCK TABLES `node_comment_statistics` WRITE;
/*!40000 ALTER TABLE `node_comment_statistics` DISABLE KEYS */;
INSERT INTO `node_comment_statistics` VALUES (1,1289619175,NULL,1,0,0),(2,1291653383,NULL,1,0,0);
/*!40000 ALTER TABLE `node_comment_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_revision`
--

DROP TABLE IF EXISTS `node_revision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_revision` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `log` longtext NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `comment` int(11) NOT NULL DEFAULT '0',
  `promote` int(11) NOT NULL DEFAULT '0',
  `sticky` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid`),
  KEY `nid` (`nid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_revision`
--

LOCK TABLES `node_revision` WRITE;
/*!40000 ALTER TABLE `node_revision` DISABLE KEYS */;
INSERT INTO `node_revision` VALUES (1,1,1,'Please Login','',1289619175,1,0,0,0),(2,2,1,'Page Not Found','',1291653383,1,0,0,0),(3,3,0,'Please Login','',1367678438,1,0,0,0);
/*!40000 ALTER TABLE `node_revision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_type`
--

DROP TABLE IF EXISTS `node_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_type` (
  `type` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `base` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `help` mediumtext NOT NULL,
  `has_title` tinyint(3) unsigned NOT NULL,
  `title_label` varchar(255) NOT NULL DEFAULT '',
  `custom` tinyint(4) NOT NULL DEFAULT '0',
  `modified` tinyint(4) NOT NULL DEFAULT '0',
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  `orig_type` varchar(255) NOT NULL DEFAULT '',
  `module` varchar(255) NOT NULL,
  `disabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'A boolean indicating whether the node type is disabled.',
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_type`
--

LOCK TABLES `node_type` WRITE;
/*!40000 ALTER TABLE `node_type` DISABLE KEYS */;
INSERT INTO `node_type` VALUES ('page','Page','node_content','A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.','',1,'Title',1,1,0,'page','node',0),('story','Story','node_content','A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.','',1,'Title',1,1,0,'story','node',0);
/*!40000 ALTER TABLE `node_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique item ID.',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT 'The queue name.',
  `data` longblob COMMENT 'The arbitrary data for the item.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp when the claim lease expires on the item.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp when the item was created.',
  PRIMARY KEY (`item_id`),
  KEY `name_created` (`name`,`created`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores items in queues.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue`
--

LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registry`
--

DROP TABLE IF EXISTS `registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(9) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`,`type`),
  KEY `hook` (`type`,`weight`,`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registry`
--

LOCK TABLES `registry` WRITE;
/*!40000 ALTER TABLE `registry` DISABLE KEYS */;
INSERT INTO `registry` VALUES ('AccessDeniedTestCase','class','modules/system/system.test','system',0),('AdministerUsersByRoleTestCase','class','sites/all/modules/administerusersbyrole/administerusersbyrole.test','administerusersbyrole',0),('AdminMetaTagTestCase','class','modules/system/system.test','system',0),('ApacheSolrDocument','class','sites/all/modules/apachesolr/Apache_Solr_Document.php','apachesolr',0),('ApacheSolrFacetapiAdapter','class','sites/all/modules/apachesolr/plugins/facetapi/adapter.inc','apachesolr',0),('ApacheSolrFacetapiDate','class','sites/all/modules/apachesolr/plugins/facetapi/query_type_date.inc','apachesolr',0),('ApacheSolrFacetapiNumericRange','class','sites/all/modules/apachesolr/plugins/facetapi/query_type_numeric_range.inc','apachesolr',0),('ApacheSolrFacetapiTerm','class','sites/all/modules/apachesolr/plugins/facetapi/query_type_term.inc','apachesolr',0),('ArchiverInterface','interface','includes/archiver.inc','',0),('ArchiverTar','class','modules/system/system.archiver.inc','system',0),('ArchiverZip','class','modules/system/system.archiver.inc','system',0),('Archive_Tar','class','modules/system/system.tar.inc','system',0),('BatchMemoryQueue','class','includes/batch.queue.inc','',0),('BatchQueue','class','includes/batch.queue.inc','',0),('BlockAdminThemeTestCase','class','modules/block/block.test','block',-5),('BlockCacheTestCase','class','modules/block/block.test','block',-5),('BlockHiddenRegionTestCase','class','modules/block/block.test','block',-5),('BlockHTMLIdTestCase','class','modules/block/block.test','block',-5),('BlockInvalidRegionTestCase','class','modules/block/block.test','block',-5),('BlockTemplateSuggestionsUnitTest','class','modules/block/block.test','block',-5),('BlockTestCase','class','modules/block/block.test','block',-5),('calendar_plugin_row_civicrm','class','sites/all/modules/civicrm/drupal/modules/views/plugins/calendar_plugin_row_civicrm.inc','civicrm',100),('calendar_plugin_row_civicrm_event','class','sites/all/modules/civicrm/drupal/modules/views/plugins/calendar_plugin_row_civicrm_event.inc','civicrm',100),('civicrm_handler_field','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field.inc','civicrm',100),('civicrm_handler_field_address','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_address.inc','civicrm',100),('civicrm_handler_field_contact_link','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_contact_link.inc','civicrm',100),('civicrm_handler_field_country','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_country.inc','civicrm',100),('civicrm_handler_field_custom','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_custom.inc','civicrm',100),('civicrm_handler_field_datetime','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_datetime.inc','civicrm',100),('civicrm_handler_field_drupalid','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_drupalid.inc','civicrm',100),('civicrm_handler_field_email','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_email.inc','civicrm',100),('civicrm_handler_field_encounter_medium','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_encounter_medium.inc','civicrm',100),('civicrm_handler_field_event','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_event.inc','civicrm',100),('civicrm_handler_field_event_link','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_event_link.inc','civicrm',100),('civicrm_handler_field_event_price_set','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_event_price_set.inc','civicrm',100),('civicrm_handler_field_file','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_file.inc','civicrm',100),('civicrm_handler_field_link','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link.inc','civicrm',100),('civicrm_handler_field_link_activity','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_activity.inc','civicrm',100),('civicrm_handler_field_link_contact','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_contact.inc','civicrm',100),('civicrm_handler_field_link_contribution','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_contribution.inc','civicrm',100),('civicrm_handler_field_link_delete','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_delete.inc','civicrm',100),('civicrm_handler_field_link_edit','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_edit.inc','civicrm',100),('civicrm_handler_field_link_event','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_event.inc','civicrm',100),('civicrm_handler_field_link_participant','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_participant.inc','civicrm',100),('civicrm_handler_field_link_pcp','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_pcp.inc','civicrm',100),('civicrm_handler_field_link_relationship','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_relationship.inc','civicrm',100),('civicrm_handler_field_location','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_location.inc','civicrm',100),('civicrm_handler_field_mail','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_mail.inc','civicrm',100),('civicrm_handler_field_markup','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_markup.inc','civicrm',100),('civicrm_handler_field_money','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_money.inc','civicrm',100),('civicrm_handler_field_option','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_option.inc','civicrm',100),('civicrm_handler_field_pcp_raised_amount','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_pcp_raised_amount.inc','civicrm',100),('civicrm_handler_field_phone','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_phone.inc','civicrm',100),('civicrm_handler_field_prefix','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_prefix.inc','civicrm',100),('civicrm_handler_field_pseudo_constant','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_pseudo_constant.inc','civicrm',100),('civicrm_handler_field_relationship_type','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_relationship_type.inc','civicrm',100),('civicrm_handler_field_state','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_state.inc','civicrm',100),('civicrm_handler_field_suffix','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_suffix.inc','civicrm',100),('civicrm_handler_filter_country_multi','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_country_multi.inc','civicrm',100),('civicrm_handler_filter_custom_option','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_custom_option.inc','civicrm',100),('civicrm_handler_filter_custom_single_option','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_custom_single_option.inc','civicrm',100),('civicrm_handler_filter_datetime','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_datetime.inc','civicrm',100),('civicrm_handler_filter_domain','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_domain.inc','civicrm',100),('civicrm_handler_filter_encounter_medium','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_encounter_medium.inc','civicrm',100),('civicrm_handler_filter_group_status','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_group_status.inc','civicrm',100),('civicrm_handler_filter_prefix','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_prefix.inc','civicrm',100),('civicrm_handler_filter_pseudo_constant','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_pseudo_constant.inc','civicrm',100),('civicrm_handler_filter_relationship_type','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_relationship_type.inc','civicrm',100),('civicrm_handler_filter_suffix','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_suffix.inc','civicrm',100),('civicrm_handler_relationship','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_relationship.inc','civicrm',100),('civicrm_handler_relationship_contact2users','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_relationship_contact2users.inc','civicrm',100),('civicrm_handler_sort_date','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_sort_date.inc','civicrm',100),('civicrm_handler_sort_pcp_raised_amount','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_sort_pcp_raised_amount.inc','civicrm',100),('civicrm_plugin_argument_default','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_plugin_argument_default.inc','civicrm',100),('CronRunTestCase','class','modules/system/system.test','system',0),('Database','class','includes/database/database.inc','',0),('DatabaseCondition','class','includes/database/query.inc','',0),('DatabaseConnection','class','includes/database/database.inc','',0),('DatabaseConnectionNotDefinedException','class','includes/database/database.inc','',0),('DatabaseConnection_mysql','class','includes/database/mysql/database.inc','',0),('DatabaseConnection_pgsql','class','includes/database/pgsql/database.inc','',0),('DatabaseConnection_sqlite','class','includes/database/sqlite/database.inc','',0),('DatabaseDriverNotSpecifiedException','class','includes/database/database.inc','',0),('DatabaseLog','class','includes/database/log.inc','',0),('DatabaseSchema','class','includes/database/schema.inc','',0),('DatabaseSchemaObjectDoesNotExistException','class','includes/database/schema.inc','',0),('DatabaseSchemaObjectExistsException','class','includes/database/schema.inc','',0),('DatabaseSchema_mysql','class','includes/database/mysql/schema.inc','',0),('DatabaseSchema_pgsql','class','includes/database/pgsql/schema.inc','',0),('DatabaseSchema_sqlite','class','includes/database/sqlite/schema.inc','',0),('DatabaseStatementBase','class','includes/database/database.inc','',0),('DatabaseStatementEmpty','class','includes/database/database.inc','',0),('DatabaseStatementInterface','interface','includes/database/database.inc','',0),('DatabaseStatementPrefetch','class','includes/database/prefetch.inc','',0),('DatabaseStatement_sqlite','class','includes/database/sqlite/database.inc','',0),('DatabaseTaskException','class','includes/install.inc','',0),('DatabaseTasks','class','includes/install.inc','',0),('DatabaseTasks_mysql','class','includes/database/mysql/install.inc','',0),('DatabaseTasks_pgsql','class','includes/database/pgsql/install.inc','',0),('DatabaseTasks_sqlite','class','includes/database/sqlite/install.inc','',0),('DatabaseTransaction','class','includes/database/database.inc','',0),('DatabaseTransactionCommitFailedException','class','includes/database/database.inc','',0),('DatabaseTransactionExplicitCommitNotAllowedException','class','includes/database/database.inc','',0),('DatabaseTransactionNameNonUniqueException','class','includes/database/database.inc','',0),('DatabaseTransactionNoActiveException','class','includes/database/database.inc','',0),('DatabaseTransactionOutOfOrderException','class','includes/database/database.inc','',0),('DateTimeFunctionalTest','class','modules/system/system.test','system',0),('DefaultMailSystem','class','modules/system/system.mail.inc','system',0),('DeleteQuery','class','includes/database/query.inc','',0),('DeleteQuery_sqlite','class','includes/database/sqlite/query.inc','',0),('DrupalApacheSolrService','class','sites/all/modules/apachesolr/Drupal_Apache_Solr_Service.php','apachesolr',0),('DrupalCacheArray','class','includes/bootstrap.inc','',0),('DrupalCacheInterface','interface','includes/cache.inc','',0),('DrupalDatabaseCache','class','includes/cache.inc','',0),('DrupalDefaultEntityController','class','includes/entity.inc','',0),('DrupalEntityControllerInterface','interface','includes/entity.inc','',0),('DrupalFakeCache','class','includes/cache-install.inc','',0),('DrupalLocalStreamWrapper','class','includes/stream_wrappers.inc','',0),('DrupalPrivateStreamWrapper','class','includes/stream_wrappers.inc','',0),('DrupalPublicStreamWrapper','class','includes/stream_wrappers.inc','',0),('DrupalQueue','class','modules/system/system.queue.inc','system',0),('DrupalQueueInterface','interface','modules/system/system.queue.inc','system',0),('DrupalReliableQueueInterface','interface','modules/system/system.queue.inc','system',0),('DrupalSolrDocumentTest','class','sites/all/modules/apachesolr/tests/solr_document.test','apachesolr',0),('DrupalSolrFilterSubQueryTests','class','sites/all/modules/apachesolr/tests/solr_base_subquery.test','apachesolr',0),('DrupalSolrMatchTestCase','class','sites/all/modules/apachesolr/tests/solr_index_and_search.test','apachesolr',0),('DrupalSolrNodeTestCase','class','sites/all/modules/apachesolr/tests/apachesolr_base.test','apachesolr',0),('DrupalSolrOfflineEnvironmentWebTestCase','class','sites/all/modules/apachesolr/tests/apachesolr_base.test','apachesolr',0),('DrupalSolrOfflineSearchPagesWebTestCase','class','sites/all/modules/apachesolr/tests/apachesolr_base.test','apachesolr',0),('DrupalSolrOfflineUnitTestCase','class','sites/all/modules/apachesolr/tests/apachesolr_base.test','apachesolr',0),('DrupalSolrOnlineWebTestCase','class','sites/all/modules/apachesolr/tests/solr_index_and_search.test','apachesolr',0),('DrupalSolrQueryInterface','interface','sites/all/modules/apachesolr/apachesolr.interface.inc','apachesolr',0),('DrupalStreamWrapperInterface','interface','includes/stream_wrappers.inc','',0),('DrupalTemporaryStreamWrapper','class','includes/stream_wrappers.inc','',0),('DrupalUpdateException','class','includes/update.inc','',0),('DrupalUpdaterInterface','interface','includes/updater.inc','',0),('DummySolr','class','sites/all/modules/apachesolr/tests/Dummy_Solr.php','apachesolr',0),('EnableDisableTestCase','class','modules/system/system.test','system',0),('Entity','class','sites/all/modules/entity/includes/entity.inc','entity',0),('EntityAPIController','class','sites/all/modules/entity/includes/entity.controller.inc','entity',0),('EntityAPIControllerExportable','class','sites/all/modules/entity/includes/entity.controller.inc','entity',0),('EntityAPIControllerInterface','interface','sites/all/modules/entity/includes/entity.controller.inc','entity',0),('EntityAPIi18nItegrationTestCase','class','sites/all/modules/entity/entity.test','entity',0),('EntityAPIRulesIntegrationTestCase','class','sites/all/modules/entity/entity.test','entity',0),('EntityAPITestCase','class','sites/all/modules/entity/entity.test','entity',0),('EntityDB','class','sites/all/modules/entity/includes/entity.inc','entity',0),('EntityDBExtendable','class','sites/all/modules/entity/includes/entity.inc','entity',0),('EntityDefaultFeaturesController','class','sites/all/modules/entity/entity.features.inc','entity',0),('EntityDefaultI18nStringController','class','sites/all/modules/entity/entity.i18n.inc','entity',0),('EntityDefaultMetadataController','class','sites/all/modules/entity/entity.info.inc','entity',0),('EntityDefaultRulesController','class','sites/all/modules/entity/entity.rules.inc','entity',0),('EntityDefaultUIController','class','sites/all/modules/entity/includes/entity.ui.inc','entity',0),('EntityDefaultViewsController','class','sites/all/modules/entity/views/entity.views.inc','entity',0),('EntityDrupalWrapper','class','sites/all/modules/entity/includes/entity.wrapper.inc','entity',0),('EntityExtendable','class','sites/all/modules/entity/includes/entity.inc','entity',0),('EntityFieldHandlerHelper','class','sites/all/modules/entity/views/handlers/entity_views_field_handler_helper.inc','entity',0),('EntityFieldQuery','class','includes/entity.inc','',0),('EntityFieldQueryException','class','includes/entity.inc','',0),('EntityListWrapper','class','sites/all/modules/entity/includes/entity.wrapper.inc','entity',0),('EntityMalformedException','class','includes/entity.inc','',0),('EntityMetadataArrayObject','class','sites/all/modules/entity/includes/entity.wrapper.inc','entity',0),('EntityMetadataIntegrationTestCase','class','sites/all/modules/entity/entity.test','entity',0),('EntityMetadataTestCase','class','sites/all/modules/entity/entity.test','entity',0),('EntityMetadataWrapper','class','sites/all/modules/entity/includes/entity.wrapper.inc','entity',0),('EntityMetadataWrapperException','class','sites/all/modules/entity/includes/entity.wrapper.inc','entity',0),('EntityMetadataWrapperIterator','class','sites/all/modules/entity/includes/entity.wrapper.inc','entity',0),('EntityPropertiesTestCase','class','modules/field/tests/field.test','field',0),('EntityStructureWrapper','class','sites/all/modules/entity/includes/entity.wrapper.inc','entity',0),('EntityTokenTestCase','class','sites/all/modules/entity/entity.test','entity',0),('EntityValueWrapper','class','sites/all/modules/entity/includes/entity.wrapper.inc','entity',0),('EntityWebTestCase','class','sites/all/modules/entity/entity.test','entity',0),('entity_views_handler_area_entity','class','sites/all/modules/entity/views/handlers/entity_views_handler_area_entity.inc','entity',0),('entity_views_handler_field_boolean','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_boolean.inc','entity',0),('entity_views_handler_field_date','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_date.inc','entity',0),('entity_views_handler_field_duration','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_duration.inc','entity',0),('entity_views_handler_field_entity','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_entity.inc','entity',0),('entity_views_handler_field_field','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_field.inc','entity',0),('entity_views_handler_field_numeric','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_numeric.inc','entity',0),('entity_views_handler_field_options','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_options.inc','entity',0),('entity_views_handler_field_text','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_text.inc','entity',0),('entity_views_handler_field_uri','class','sites/all/modules/entity/views/handlers/entity_views_handler_field_uri.inc','entity',0),('entity_views_handler_relationship','class','sites/all/modules/entity/views/handlers/entity_views_handler_relationship.inc','entity',0),('entity_views_handler_relationship_by_bundle','class','sites/all/modules/entity/views/handlers/entity_views_handler_relationship_by_bundle.inc','entity',0),('entity_views_plugin_row_entity_view','class','sites/all/modules/entity/views/plugins/entity_views_plugin_row_entity_view.inc','entity',0),('FacesExtendable','class','sites/all/modules/rules/includes/faces.inc','rules',20),('FacesExtendableException','class','sites/all/modules/rules/includes/faces.inc','rules',20),('FacesExtender','class','sites/all/modules/rules/includes/faces.inc','rules',20),('FacesExtenderInterface','interface','sites/all/modules/rules/includes/faces.inc','rules',20),('FieldAttachOtherTestCase','class','modules/field/tests/field.test','field',0),('FieldAttachStorageTestCase','class','modules/field/tests/field.test','field',0),('FieldAttachTestCase','class','modules/field/tests/field.test','field',0),('FieldBulkDeleteTestCase','class','modules/field/tests/field.test','field',0),('FieldCrudTestCase','class','modules/field/tests/field.test','field',0),('FieldDisplayAPITestCase','class','modules/field/tests/field.test','field',0),('FieldException','class','modules/field/field.module','field',0),('FieldFormTestCase','class','modules/field/tests/field.test','field',0),('FieldInfoTestCase','class','modules/field/tests/field.test','field',0),('FieldInstanceCrudTestCase','class','modules/field/tests/field.test','field',0),('FieldsOverlapException','class','includes/database/database.inc','',0),('FieldSqlStorageTestCase','class','modules/field/modules/field_sql_storage/field_sql_storage.test','field_sql_storage',0),('FieldTestCase','class','modules/field/tests/field.test','field',0),('FieldTranslationsTestCase','class','modules/field/tests/field.test','field',0),('FieldUIAlterTestCase','class','modules/field_ui/field_ui.test','field_ui',0),('FieldUIManageDisplayTestCase','class','modules/field_ui/field_ui.test','field_ui',0),('FieldUIManageFieldsTestCase','class','modules/field_ui/field_ui.test','field_ui',0),('FieldUITestCase','class','modules/field_ui/field_ui.test','field_ui',0),('FieldUpdateForbiddenException','class','modules/field/field.module','field',0),('FieldValidationException','class','modules/field/field.attach.inc','field',0),('FileTransfer','class','includes/filetransfer/filetransfer.inc','',0),('FileTransferChmodInterface','interface','includes/filetransfer/filetransfer.inc','',0),('FileTransferException','class','includes/filetransfer/filetransfer.inc','',0),('FileTransferFTP','class','includes/filetransfer/ftp.inc','',0),('FileTransferFTPExtension','class','includes/filetransfer/ftp.inc','',0),('FileTransferLocal','class','includes/filetransfer/local.inc','',0),('FileTransferSSH','class','includes/filetransfer/ssh.inc','',0),('FilterAdminTestCase','class','modules/filter/filter.test','filter',0),('FilterCRUDTestCase','class','modules/filter/filter.test','filter',0),('FilterDefaultFormatTestCase','class','modules/filter/filter.test','filter',0),('FilterFormatAccessTestCase','class','modules/filter/filter.test','filter',0),('FilterHooksTestCase','class','modules/filter/filter.test','filter',0),('FilterNoFormatTestCase','class','modules/filter/filter.test','filter',0),('FilterSecurityTestCase','class','modules/filter/filter.test','filter',0),('FilterSettingsTestCase','class','modules/filter/filter.test','filter',0),('FilterUnitTestCase','class','modules/filter/filter.test','filter',0),('FloodFunctionalTest','class','modules/system/system.test','system',0),('FrontPageTestCase','class','modules/system/system.test','system',0),('HookRequirementsTestCase','class','modules/system/system.test','system',0),('InfoFileParserTestCase','class','modules/system/system.test','system',0),('InsertQuery','class','includes/database/query.inc','',0),('InsertQuery_mysql','class','includes/database/mysql/query.inc','',0),('InsertQuery_pgsql','class','includes/database/pgsql/query.inc','',0),('InsertQuery_sqlite','class','includes/database/sqlite/query.inc','',0),('InvalidMergeQueryException','class','includes/database/database.inc','',0),('IPAddressBlockingTestCase','class','modules/system/system.test','system',0),('LdapAuthenticationConf','class','sites/all/modules/ldap/ldap_authentication/LdapAuthenticationConf.class.php','ldap_authentication',0),('LdapAuthenticationConfAdmin','class','sites/all/modules/ldap/ldap_authentication/LdapAuthenticationConfAdmin.class.php','ldap_authentication',0),('LdapAuthenticationTestCase','class','sites/all/modules/ldap/ldap_authentication/tests/ldap_authentication.test','ldap_authentication',0),('LdapAuthorizationBasicTests','class','sites/all/modules/ldap/ldap_authorization/tests/BasicTests/BasicTests.test','ldap_authorization',0),('LdapAuthorizationConsumerAbstract','class','sites/all/modules/ldap/ldap_authorization/LdapAuthorizationConsumerAbstract.class.php','ldap_authorization',0),('LdapAuthorizationConsumerDrupalRole','class','sites/all/modules/ldap/ldap_authorization/ldap_authorization_drupal_role/LdapAuthorizationConsumerRole.class.php','ldap_authorization_drupal_role',0),('LdapAuthorizationDerivationsTests','class','sites/all/modules/ldap/ldap_authorization/tests/DeriveFromEntry/DeriveFromEntry.test','ldap_authorization',0),('LdapAuthorizationDeriveEntry','class','sites/all/modules/ldap/ldap_authorization/tests/DeriveFromDN/DeriveFromDN.test','ldap_authorization',0),('LdapAuthorizationDeriveFromAttr','class','sites/all/modules/ldap/ldap_authorization/tests/DeriveFromAttr/DeriveFromAttr.test','ldap_authorization',0),('LdapAuthorizationOg2Tests','class','sites/all/modules/ldap/ldap_authorization/tests/Og/Og2.test','ldap_authorization',0),('LdapAuthorizationOgTests','class','sites/all/modules/ldap/ldap_authorization/tests/Og/Og.test','ldap_authorization',0),('LdapAuthorizationOtherAuthenticationTests','class','sites/all/modules/ldap/ldap_authorization/tests/Other/Other.test','ldap_authorization',0),('LdapAuthorizationTestCase1197636','class','sites/all/modules/ldap/ldap_authorization/tests/1197636/1197636.test','ldap_authorization',0),('LdapServer','class','sites/all/modules/ldap/ldap_servers/LdapServer.class.php','ldap_servers',0),('LdapServerAdmin','class','sites/all/modules/ldap/ldap_servers/LdapServerAdmin.class.php','ldap_servers',0),('LdapServersTestCase','class','sites/all/modules/ldap/ldap_servers/tests/ldap_servers.test','ldap_servers',0),('ListDynamicValuesTestCase','class','modules/field/modules/list/tests/list.test','list',0),('ListDynamicValuesValidationTestCase','class','modules/field/modules/list/tests/list.test','list',0),('ListFieldTestCase','class','modules/field/modules/list/tests/list.test','list',0),('ListFieldUITestCase','class','modules/field/modules/list/tests/list.test','list',0),('MailSystemInterface','interface','includes/mail.inc','',0),('MemoryQueue','class','modules/system/system.queue.inc','system',0),('MenuNodeTestCase','class','modules/menu/menu.test','menu',0),('MenuTestCase','class','modules/menu/menu.test','menu',0),('MergeQuery','class','includes/database/query.inc','',0),('ModuleDependencyTestCase','class','modules/system/system.test','system',0),('ModuleRequiredTestCase','class','modules/system/system.test','system',0),('ModuleTestCase','class','modules/system/system.test','system',0),('ModuleUpdater','class','modules/system/system.updater.inc','system',0),('ModuleVersionTestCase','class','modules/system/system.test','system',0),('MultiStepNodeFormBasicOptionsTest','class','modules/node/node.test','node',0),('NewDefaultThemeBlocks','class','modules/block/block.test','block',-5),('NodeAccessBaseTableTestCase','class','modules/node/node.test','node',0),('NodeAccessFieldTestCase','class','modules/node/node.test','node',0),('NodeAccessPagerTestCase','class','modules/node/node.test','node',0),('NodeAccessRebuildTestCase','class','modules/node/node.test','node',0),('NodeAccessRecordsTestCase','class','modules/node/node.test','node',0),('NodeAccessTestCase','class','modules/node/node.test','node',0),('NodeAdminTestCase','class','modules/node/node.test','node',0),('NodeBlockFunctionalTest','class','modules/node/node.test','node',0),('NodeBlockTestCase','class','modules/node/node.test','node',0),('NodeBuildContent','class','modules/node/node.test','node',0),('NodeController','class','modules/node/node.module','node',0),('NodeCreationTestCase','class','modules/node/node.test','node',0),('NodeEntityFieldQueryAlter','class','modules/node/node.test','node',0),('NodeFeedTestCase','class','modules/node/node.test','node',0),('NodeLoadHooksTestCase','class','modules/node/node.test','node',0),('NodeLoadMultipleTestCase','class','modules/node/node.test','node',0),('NodePostSettingsTestCase','class','modules/node/node.test','node',0),('NodeQueryAlter','class','modules/node/node.test','node',0),('NodeRevisionPermissionsTestCase','class','modules/node/node.test','node',0),('NodeRevisionsTestCase','class','modules/node/node.test','node',0),('NodeRSSContentTestCase','class','modules/node/node.test','node',0),('NodeSaveTestCase','class','modules/node/node.test','node',0),('NodeTitleTestCase','class','modules/node/node.test','node',0),('NodeTitleXSSTestCase','class','modules/node/node.test','node',0),('NodeTokenReplaceTestCase','class','modules/node/node.test','node',0),('NodeTypePersistenceTestCase','class','modules/node/node.test','node',0),('NodeTypeTestCase','class','modules/node/node.test','node',0),('NodeWebTestCase','class','modules/node/node.test','node',0),('NoFieldsException','class','includes/database/database.inc','',0),('NonDefaultBlockAdmin','class','modules/block/block.test','block',-5),('NumberFieldTestCase','class','modules/field/modules/number/number.test','number',0),('OptionsSelectDynamicValuesTestCase','class','modules/field/modules/options/options.test','options',0),('OptionsWidgetsTestCase','class','modules/field/modules/options/options.test','options',0),('PageEditTestCase','class','modules/node/node.test','node',0),('PageNotFoundTestCase','class','modules/system/system.test','system',0),('PagePreviewTestCase','class','modules/node/node.test','node',0),('PagerDefault','class','includes/pager.inc','',0),('PageTitleFiltering','class','modules/system/system.test','system',0),('PageViewTestCase','class','modules/node/node.test','node',0),('Query','class','includes/database/query.inc','',0),('QueryAlterableInterface','interface','includes/database/query.inc','',0),('QueryConditionInterface','interface','includes/database/query.inc','',0),('QueryExtendableInterface','interface','includes/database/select.inc','',0),('QueryPlaceholderInterface','interface','includes/database/query.inc','',0),('QueueTestCase','class','modules/system/system.test','system',0),('RetrieveFileTestCase','class','modules/system/system.test','system',0),('Rule','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesAbstractPlugin','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesAbstractPluginDefaults','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesAbstractPluginUI','class','sites/all/modules/rules/ui/ui.core.inc','rules',20),('RulesAction','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesActionContainer','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesActionContainerUI','class','sites/all/modules/rules/ui/ui.core.inc','rules',20),('RulesActionInterface','interface','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesActionSet','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesAnd','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesCondition','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesConditionContainer','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesConditionContainerUI','class','sites/all/modules/rules/ui/ui.core.inc','rules',20),('RulesConditionInterface','interface','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesContainerPlugin','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesContainerPluginUI','class','sites/all/modules/rules/ui/ui.core.inc','rules',20),('RulesData','class','sites/all/modules/rules/includes/rules.state.inc','rules',20),('RulesDataDirectInputFormInterface','interface','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataInputEvaluator','class','sites/all/modules/rules/includes/rules.processor.inc','rules',20),('RulesDataProcessor','class','sites/all/modules/rules/includes/rules.processor.inc','rules',20),('RulesDataUI','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIBoolean','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIDate','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIDecimal','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIDuration','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIEntity','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIEntityExportable','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIInteger','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIListEntity','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIListInteger','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIListText','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIListToken','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUITaxonomyVocabulary','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIText','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUITextFormatted','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUITextToken','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataUIURI','class','sites/all/modules/rules/ui/ui.data.inc','rules',20),('RulesDataWrapperSavableInterface','interface','sites/all/modules/rules/includes/rules.state.inc','rules',20),('RulesDateInputEvaluator','class','sites/all/modules/rules/modules/rules_core.eval.inc','rules',20),('RulesDateOffsetProcessor','class','sites/all/modules/rules/modules/rules_core.eval.inc','rules',20),('RulesDependencyException','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesElementMap','class','sites/all/modules/rules/ui/ui.core.inc','rules',20),('RulesEntityController','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesEvaluationException','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesEventSet','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesException','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesExtendable','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesFeaturesController','class','sites/all/modules/rules/rules.features.inc','rules',20),('RulesIdentifiableDataWrapper','class','sites/all/modules/rules/includes/rules.state.inc','rules',20),('RulesIntegrationTestCase','class','sites/all/modules/rules/tests/rules.test','rules',20),('RulesIntegrityException','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesLog','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesLoop','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesLoopUI','class','sites/all/modules/rules/ui/ui.plugins.inc','rules',20),('RulesNumericOffsetProcessor','class','sites/all/modules/rules/modules/rules_core.eval.inc','rules',20),('RulesOptimizationInterface','interface','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesOr','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesPHPDataProcessor','class','sites/all/modules/rules/modules/php.eval.inc','rules',20),('RulesPHPEvaluator','class','sites/all/modules/rules/modules/php.eval.inc','rules',20),('RulesPlugin','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesPluginFeaturesIntegrationInterace','interface','sites/all/modules/rules/rules.features.inc','rules',20),('RulesPluginImplInterface','interface','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesPluginUI','class','sites/all/modules/rules/ui/ui.core.inc','rules',20),('RulesPluginUIInterface','interface','sites/all/modules/rules/ui/ui.core.inc','rules',20),('RulesReactionRule','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesReactionRuleUI','class','sites/all/modules/rules/ui/ui.plugins.inc','rules',20),('RulesRecursiveElementIterator','class','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesRuleSet','class','sites/all/modules/rules/includes/rules.plugins.inc','rules',20),('RulesRuleSetUI','class','sites/all/modules/rules/ui/ui.plugins.inc','rules',20),('RulesRuleUI','class','sites/all/modules/rules/ui/ui.plugins.inc','rules',20),('RulesState','class','sites/all/modules/rules/includes/rules.state.inc','rules',20),('RulesTaxonomyVocabularyWrapper','class','sites/all/modules/rules/modules/rules_core.eval.inc','rules',20),('RulesTestCase','class','sites/all/modules/rules/tests/rules.test','rules',20),('RulesTestDataCase','class','sites/all/modules/rules/tests/rules.test','rules',20),('RulesTokenEvaluator','class','sites/all/modules/rules/modules/system.eval.inc','rules',20),('RulesTriggerableInterface','interface','sites/all/modules/rules/includes/rules.core.inc','rules',20),('RulesTriggerTestCase','class','sites/all/modules/rules/tests/rules.test','rules',20),('RulesUIController','class','sites/all/modules/rules/ui/ui.controller.inc','rules',20),('RulesURIInputEvaluator','class','sites/all/modules/rules/modules/rules_core.eval.inc','rules',20),('SchemaCache','class','includes/bootstrap.inc','',0),('SearchAdvancedSearchForm','class','modules/search/search.test','search',0),('SearchBlockTestCase','class','modules/search/search.test','search',0),('SearchCommentCountToggleTestCase','class','modules/search/search.test','search',0),('SearchCommentTestCase','class','modules/search/search.test','search',0),('SearchConfigSettingsForm','class','modules/search/search.test','search',0),('SearchEmbedForm','class','modules/search/search.test','search',0),('SearchExactTestCase','class','modules/search/search.test','search',0),('SearchExcerptTestCase','class','modules/search/search.test','search',0),('SearchExpressionInsertExtractTestCase','class','modules/search/search.test','search',0),('SearchKeywordsConditions','class','modules/search/search.test','search',0),('SearchLanguageTestCase','class','modules/search/search.test','search',0),('SearchMatchTestCase','class','modules/search/search.test','search',0),('SearchNodeAccessTest','class','modules/search/search.test','search',0),('SearchNumberMatchingTestCase','class','modules/search/search.test','search',0),('SearchNumbersTestCase','class','modules/search/search.test','search',0),('SearchPageOverride','class','modules/search/search.test','search',0),('SearchPageText','class','modules/search/search.test','search',0),('SearchQuery','class','modules/search/search.extender.inc','search',0),('SearchRankingTestCase','class','modules/search/search.test','search',0),('SearchSimplifyTestCase','class','modules/search/search.test','search',0),('SearchTokenizerTestCase','class','modules/search/search.test','search',0),('SelectQuery','class','includes/database/select.inc','',0),('SelectQueryExtender','class','includes/database/select.inc','',0),('SelectQueryInterface','interface','includes/database/select.inc','',0),('SelectQuery_pgsql','class','includes/database/pgsql/select.inc','',0),('SelectQuery_sqlite','class','includes/database/sqlite/select.inc','',0),('ShutdownFunctionsTest','class','modules/system/system.test','system',0),('SiteMaintenanceTestCase','class','modules/system/system.test','system',0),('SkipDotsRecursiveDirectoryIterator','class','includes/filetransfer/filetransfer.inc','',0),('SolrBaseQuery','class','sites/all/modules/apachesolr/Solr_Base_Query.php','apachesolr',0),('SolrBaseQueryTests','class','sites/all/modules/apachesolr/tests/solr_base_query.test','apachesolr',0),('SolrFilterSubQuery','class','sites/all/modules/apachesolr/Solr_Base_Query.php','apachesolr',0),('StreamWrapperInterface','interface','includes/stream_wrappers.inc','',0),('SummaryLengthTestCase','class','modules/node/node.test','node',0),('SystemAdminTestCase','class','modules/system/system.test','system',0),('SystemAuthorizeCase','class','modules/system/system.test','system',0),('SystemBlockTestCase','class','modules/system/system.test','system',0),('SystemIndexPhpTest','class','modules/system/system.test','system',0),('SystemInfoAlterTestCase','class','modules/system/system.test','system',0),('SystemMainContentFallback','class','modules/system/system.test','system',0),('SystemQueue','class','modules/system/system.queue.inc','system',0),('SystemThemeFunctionalTest','class','modules/system/system.test','system',0),('TableSort','class','includes/tablesort.inc','',0),('TestingMailSystem','class','modules/system/system.mail.inc','system',0),('TextFieldTestCase','class','modules/field/modules/text/text.test','text',0),('TextSummaryTestCase','class','modules/field/modules/text/text.test','text',0),('TextTranslationTestCase','class','modules/field/modules/text/text.test','text',0),('ThemeRegistry','class','includes/theme.inc','',0),('ThemeUpdater','class','modules/system/system.updater.inc','system',0),('TokenReplaceTestCase','class','modules/system/system.test','system',0),('TriggerActionTestCase','class','modules/trigger/trigger.test','trigger',0),('TriggerContentTestCase','class','modules/trigger/trigger.test','trigger',0),('TriggerCronTestCase','class','modules/trigger/trigger.test','trigger',0),('TriggerOrphanedActionsTestCase','class','modules/trigger/trigger.test','trigger',0),('TriggerOtherTestCase','class','modules/trigger/trigger.test','trigger',0),('TriggerUnassignTestCase','class','modules/trigger/trigger.test','trigger',0),('TriggerUserActionTestCase','class','modules/trigger/trigger.test','trigger',0),('TriggerUserTokenTestCase','class','modules/trigger/trigger.test','trigger',0),('TriggerWebTestCase','class','modules/trigger/trigger.test','trigger',0),('TruncateQuery','class','includes/database/query.inc','',0),('TruncateQuery_mysql','class','includes/database/mysql/query.inc','',0),('TruncateQuery_sqlite','class','includes/database/sqlite/query.inc','',0),('UpdateQuery','class','includes/database/query.inc','',0),('UpdateQuery_pgsql','class','includes/database/pgsql/query.inc','',0),('UpdateQuery_sqlite','class','includes/database/sqlite/query.inc','',0),('Updater','class','includes/updater.inc','',0),('UpdaterException','class','includes/updater.inc','',0),('UpdaterFileTransferException','class','includes/updater.inc','',0),('UpdateScriptFunctionalTest','class','modules/system/system.test','system',0),('UserAccountLinksUnitTests','class','modules/user/user.test','user',0),('UserAdminTestCase','class','modules/user/user.test','user',0),('UserAuthmapAssignmentTestCase','class','modules/user/user.test','user',0),('UserAutocompleteTestCase','class','modules/user/user.test','user',0),('UserBlocksUnitTests','class','modules/user/user.test','user',0),('UserCancelTestCase','class','modules/user/user.test','user',0),('UserController','class','modules/user/user.module','user',0),('UserCreateTestCase','class','modules/user/user.test','user',0),('UserEditedOwnAccountTestCase','class','modules/user/user.test','user',0),('UserEditTestCase','class','modules/user/user.test','user',0),('UserLoginTestCase','class','modules/user/user.test','user',0),('UserPasswordResetTestCase','class','modules/user/user.test','user',0),('UserPermissionsTestCase','class','modules/user/user.test','user',0),('UserPictureTestCase','class','modules/user/user.test','user',0),('UserRegistrationTestCase','class','modules/user/user.test','user',0),('UserRoleAdminTestCase','class','modules/user/user.test','user',0),('UserRolesAssignmentTestCase','class','modules/user/user.test','user',0),('UserSaveTestCase','class','modules/user/user.test','user',0),('UserSignatureTestCase','class','modules/user/user.test','user',0),('UserTimeZoneFunctionalTest','class','modules/user/user.test','user',0),('UserTokenReplaceTestCase','class','modules/user/user.test','user',0),('UserUserSearchTestCase','class','modules/user/user.test','user',0),('UserValidateCurrentPassCustomForm','class','modules/user/user.test','user',0),('UserValidationTestCase','class','modules/user/user.test','user',0),('views_handler_argument_civicrm_day','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_day.inc','civicrm',100),('views_handler_argument_civicrm_fulldate','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_fulldate.inc','civicrm',100),('views_handler_argument_civicrm_month','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_month.inc','civicrm',100),('views_handler_argument_civicrm_week','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_week.inc','civicrm',100),('views_handler_argument_civicrm_year','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_year.inc','civicrm',100),('views_handler_argument_civicrm_year_month','class','sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_year_month.inc','civicrm',100);
/*!40000 ALTER TABLE `registry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registry_file`
--

DROP TABLE IF EXISTS `registry_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registry_file` (
  `filename` varchar(255) NOT NULL,
  `hash` varchar(64) NOT NULL,
  PRIMARY KEY (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registry_file`
--

LOCK TABLES `registry_file` WRITE;
/*!40000 ALTER TABLE `registry_file` DISABLE KEYS */;
INSERT INTO `registry_file` VALUES ('includes/actions.inc','f36b066681463c7dfe189e0430cb1a89bf66f7e228cbb53cdfcd93987193f759'),('includes/ajax.inc','e9c0653a0df32a81577d64980d74ef4f2423573b15fc86e9d7aa65227b52f470'),('includes/archiver.inc','bdbb21b712a62f6b913590b609fd17cd9f3c3b77c0d21f68e71a78427ed2e3e9'),('includes/authorize.inc','a8f26e722ddba490cdc0fcf80238af45f0f88e977b2e0c90c0c21d9df3380748'),('includes/batch.inc','059da9e36e1f3717f27840aae73f10dea7d6c8daf16f6520401cc1ca3b4c0388'),('includes/batch.queue.inc','554b2e92e1dad0f7fd5a19cb8dff7e109f10fbe2441a5692d076338ec908de0f'),('includes/bootstrap.inc','ba99c074219b8d437784c79c353f693b179e89b43a4bfc961d558c62571a709d'),('includes/cache-install.inc','e7ed123c5805703c84ad2cce9c1ca46b3ce8caeeea0d8ef39a3024a4ab95fa0e'),('includes/cache.inc','0a70a291f7ce423d1aab4816ef06a6eaf58b454a03a1f419ff309c1147c4765b'),('includes/common.inc','5b2c5e7c61db64e89526811dd4d06ee040419c6cbc02dd26667ecc7d4b4621b5'),('includes/database/database.inc','a42754a5b98efe3092d0c9e1c3d965daeb8436c033b2baa3d41f564fc606a70c'),('includes/database/log.inc','4ecbdf9022d8c612310b41af575f10b0d4c041c0fbc41c6dc7e1f2ab6eacce6b'),('includes/database/mysql/database.inc','d4648a3212519b038654457b83466aabc1b928affdd56076c655ba3d8b79a54b'),('includes/database/mysql/install.inc','6ae316941f771732fbbabed7e1d6b4cbb41b1f429dd097d04b3345aa15e461a0'),('includes/database/mysql/query.inc','7d9ea18a7ff04b7aab6210abbd0313cb53325c19a47ff8ed6c0e591c6e7149c2'),('includes/database/mysql/schema.inc','d8d3904ea9c23a526c2f2a7acc8ba870b31c378aac2eb53e2e41a73c6209c5bd'),('includes/database/pgsql/database.inc','3c981696486d3ec21f508b47516fa078ffc885703179e5ef1ca5b894c89b2fe4'),('includes/database/pgsql/install.inc','585b80c5bbd6f134bff60d06397f15154657a577d4da8d1b181858905f09dea5'),('includes/database/pgsql/query.inc','cb4c84f8f1ffc73098ed71137248dcd078a505a7530e60d979d74b3a3cdaa658'),('includes/database/pgsql/schema.inc','8fd647e4557522283caef63e528c6e403fc0751a46e94aac867a281af85eac27'),('includes/database/pgsql/select.inc','fd4bba7887c1dc6abc8f080fc3a76c01d92ea085434e355dc1ecb50d8743c22d'),('includes/database/prefetch.inc','b5b207a66a69ecb52ee4f4459af16a7b5eabedc87254245f37cc33bebb61c0fb'),('includes/database/query.inc','128b5fdb90562d7f7a9e2662ff6f35251b1370ac215298e2c3297c87ebafd961'),('includes/database/schema.inc','7eb7251f331109757173353263d1031493c1198ae17a165a6f5a03d3f14f93e7'),('includes/database/select.inc','1c74fa55c7721a704f5ef3389032604bf7a60fced15c40d844aee3e1cead7dc6'),('includes/database/sqlite/database.inc','ed2b9981794239cdad2cd04cf4bcdc896ad4d6b66179a4fa487b0d1ec2150a10'),('includes/database/sqlite/install.inc','381f3db8c59837d961978ba3097bb6443534ed1659fd713aa563963fa0c42cc5'),('includes/database/sqlite/query.inc','523ff7c05aa2b2aca08cad3743b321868ec772856f2b1c7af908bb236c6919ad'),('includes/database/sqlite/schema.inc','238414785aa96dd27f10f48c961783f4d1091392beee8d0e7ca8ae774e917da2'),('includes/database/sqlite/select.inc','8d1c426dbd337733c206cce9f59a172546c6ed856d8ef3f1c7bef05a16f7bf68'),('includes/date.inc','18c047be64f201e16d189f1cc47ed9dcf0a145151b1ee187e90511b24e5d2b36'),('includes/entity.inc','0e11791647d4d216714ca11f3eb9702b09f8a617d70841c7efa8ff3a58342e0a'),('includes/errors.inc','395db372adfcfb0676d5a68b6843b6f141a7bb8f86cd36f2cfbd1e75de94e591'),('includes/file.inc','042fed00746880123a3e4d95f138a3d8bc169a77b9cbae98cd05c1840375297d'),('includes/file.mimetypes.inc','f88c967550576694b7a1ce2afd0f2f1bbc1a91d21cc2c20f86c44d39ff353867'),('includes/filetransfer/filetransfer.inc','4391b7228bd952fb351c9431a7c226b4e8a23d9a7307b9ffa63e097c17b25467'),('includes/filetransfer/ftp.inc','589ebf4b8bd4a2973aa56a156ac1fa83b6c73e703391361fb573167670e0d832'),('includes/filetransfer/local.inc','7cbfdb46abbdf539640db27e66fb30e5265128f31002bd0dfc3af16ae01a9492'),('includes/filetransfer/ssh.inc','002e24a24cac133d12728bd3843868ce378681237d7fad420761af84e6efe5ad'),('includes/form.inc','eb51334b963d875471481dc433266a80a3e0f467f35b931734407c5ca16e8393'),('includes/graph.inc','8e0e313a8bb33488f371df11fc1b58d7cf80099b886cd1003871e2c896d1b536'),('includes/image.inc','22c8ff48d46276b9bea1ad2cf4af9b65abcf69fb5ebca441259e774190fa5863'),('includes/install.core.inc','a56ff7412d5fb68221a9f3bdd9f710d9f331f2d2c4bbb62ed98cb3c79c6882d5'),('includes/install.inc','23455f095ebd75c49ef8bf2a18145f77b6633670a5eb9eab860d907b0ba25f35'),('includes/iso.inc','f53653843c75e12aa0b05e7197c5aebdf794e2517b9902fc07994fb7cb8f3ed6'),('includes/json-encode.inc','02a822a652d00151f79db9aa9e171c310b69b93a12f549bc2ce00533a8efa14e'),('includes/language.inc','2660a308eefd99f1aec300d1f8e51365a4331ca8af2b9056e5df528cd5b03a89'),('includes/locale.inc','8cc571c114587f2b30e4e24db17e97e51e81f9cc395fa01f348aba12cee8523e'),('includes/lock.inc','daa62e95528f6b986b85680b600a896452bf2ce6f38921242857dcc5a3460a1b'),('includes/mail.inc','bb50727f20717ffa17cbe11ddb27bd004bd814fc26115c3fca4b06f4cec179fa'),('includes/menu.inc','1fa6f6b0ffc711e352b48ef7f78e399536ed91ba56ad7ed333e34ed7a93228c1'),('includes/module.inc','0033c3a573aa14076002ac5d8659ad00fd60daf3a74ea550dc16058e9ad7f7b2'),('includes/pager.inc','6f9494b85c07a2cc3be4e54aff2d2757485238c476a7da084d25bde1d88be6d8'),('includes/password.inc','aba5df25a237c14cc69335c4cf72d57da130144410ab04d10917d9da21cd606c'),('includes/path.inc','1d939d6b59b07ef41e71c9d616c2e9a34712dd81f6110e1a1f280613b3228738'),('includes/registry.inc','4ffb8c9c8c179c1417ff01790f339edf50b5f7cc0c8bb976eef6858cc71e9bc8'),('includes/session.inc','8293e6cc9f081ecdc4e8cfcdec885a79df01b9007030fe6b467a85c87ebb5d82'),('includes/stream_wrappers.inc','323e418fda2fdd29a44d8618f8855a92172c377eb745412d58ca55fdcaa8f2d1'),('includes/tablesort.inc','3f3cb2820920f7edc0c3d046ffba4fc4e3b73a699a2492780371141cf501aa50'),('includes/theme.inc','1ba1bf14159e30803ed6298f693b8a9a969f7f5afc3e714d7c74ac37a36626d9'),('includes/theme.maintenance.inc','d110314b4d943c3e965fcefe452f6873b53cd6a8844154467dfcbb2b6142dc82'),('includes/token.inc','a975300558711bb49406a5c7f78294648baa2e5c912cb66f0c78bb2991c0f3c3'),('includes/unicode.entities.inc','2b858138596d961fbaa4c6e3986e409921df7f76b6ee1b109c4af5970f1e0f54'),('includes/unicode.inc','3d5a4756f7af1f9de84f7614b6cd87449af43f4bb1e2a77bb2c73589f5826f61'),('includes/update.inc','132a2e54d9a7008ce3d71fdbcdcf3653a143b011508004b977f3493328488090'),('includes/updater.inc','d2da0e74ed86e93c209f16069f3d32e1a134ceb6c06a0044f78e841a1b54e380'),('includes/utility.inc','9b834814fd3f5ef10ce1946be30ef1ddf3f283c749f1ef1a4ebf845ecd524d59'),('includes/xmlrpc.inc','c5b6ea78adeb135373d11aeaaea057d9fa8995faa4e8c0fec9b7c647f15cc4e0'),('includes/xmlrpcs.inc','79dc6e9882f4c506123d7dd8e228a61e22c46979c3aab21a5b1afa315ef6639c'),('modules/block/block.test','b8fc64b16b03fc49d44f2fe4b3711736323effc5e0addbc3e74be91ec2bb3872'),('modules/field/field.attach.inc','d1d0d7e63ccbe1e184bd137adfd0b17434f7a85ff97c579155bb88130fc1f3c5'),('modules/field/field.module','6b495222b9370a3ef9e395473a7a576737d8375c7657987d0713bf73dbd96a82'),('modules/field/modules/field_sql_storage/field_sql_storage.test','8ede9843d771e307dfd3d7e7562976b07e0e0a9310a5cf409413581f199c897f'),('modules/field/modules/list/tests/list.test','9f366469763beb3fe0571d66318bac6df293fd15f4eb5cfe4850b9fb9a509f38'),('modules/field/modules/number/number.test','cb55fbc3a1ceed154af673af727b4c5ee6ac2e7dc9d4e1cbc33f3f8e2269146c'),('modules/field/modules/options/options.test','8c6dd464fdb5cca90b0260bcfa5f56941b4b28edd879b23a795f0442f5368d4c'),('modules/field/modules/text/text.test','9d74c6d039f55dd7d6447a59186da8d48bf20617bfe58424612798f649797586'),('modules/field/tests/field.test','7a152e80654bd0a35ba216405cbbe867f547f1e157586072275eaa349578df6b'),('modules/field_ui/field_ui.test','ca549daa46206221863098c6ee5da53a4c647a3016ee5903687804224a44dc9d'),('modules/filter/filter.test','f439e0d529cae5089990c7f0c5059ece953ae14c56e8a753d6375acf0f873560'),('modules/menu/menu.test','b8ee602184584fab464900a946090dc1f3d81c15b8176004ee62022814632430'),('modules/node/node.module','c1324a338fd025592b879622e2c19c78e134e39666bfa1088b4dd4d1e51ba89b'),('modules/node/node.test','02479c7b9ec66baa6a9e12b049fb95e6adea0a07f530d92a7fa7665b23e443a0'),('modules/search/search.extender.inc','fea036745113dca3fea52ba956af605c4789f4acfa2ab1650a5843c6e173d7fe'),('modules/search/search.test','1fe9dfc982953f42f67d7eee9a855e7248373067ba55cfff001d8a750b83e695'),('modules/system/system.archiver.inc','faa849f3e646a910ab82fd6c8bbf0a4e6b8c60725d7ba81ec0556bd716616cd1'),('modules/system/system.mail.inc','3c2c06b55bded609e72add89db41af3bb405d42b9553793acba5fe51be8861d8'),('modules/system/system.queue.inc','caf4feda51bdf7ad62cf782bc23274d367154e51897f2732f07bd06982d85ab1'),('modules/system/system.tar.inc','8a31d91f7b3cd7eac25b3fa46e1ed9a8527c39718ba76c3f8c0bbbeaa3aa4086'),('modules/system/system.test','ca539539bea3d2c070552f79dbae279e2872ba9d48af386c89ab6c1fc95488da'),('modules/system/system.updater.inc','e2eeed65b833a6215f807b113f6fb4cc3cc487e93efcb1402ed87c536d2c9ea6'),('modules/trigger/trigger.test','84032a84cfdb2934025f679575b86925bf3b59acb74ad4da89ca75799e076320'),('modules/user/user.module','841c483c69dbe244f204fb139e0580151300dfad806ccb14b45ff72be89fd566'),('modules/user/user.test','3cc1cf5e5ea1b642ea1d306a330f8b054bac88b4af757048f3b1e816268c8cf5'),('sites/all/modules/administerusersbyrole/administerusersbyrole.test','c0e31c478fe3147c61e4f6836bee8dca915b35ccbd23651a4ab67687c0bfec2d'),('sites/all/modules/apachesolr/apachesolr.admin.inc','37aa0f40042fb746829dbf798da66ee3a0cf84bdec07708d3a200ca37b5dd68e'),('sites/all/modules/apachesolr/apachesolr.index.inc','c98d97feedcfa41e2c17ad88d2e65b7814116a3b2c550ca058c708005a4c442d'),('sites/all/modules/apachesolr/apachesolr.install','7f419eb6bfa7f8bc16984d660d6bd1dfd4ce92d920f9253a2295e9ef18b1d03f'),('sites/all/modules/apachesolr/apachesolr.interface.inc','13c047bb7175a450998123037d66a69b4bebbaaa525b07c60c7ff5187388115e'),('sites/all/modules/apachesolr/apachesolr.module','d5e9e43ac0bb67d7ac67ec7d25c38169c38905efb8a76cf6bd94ec9eeed84a8a'),('sites/all/modules/apachesolr/apachesolr_search.admin.inc','b0102c76eea4f61f7713a74400c7bd4334b58b08247aacf422eda7ef9a22e208'),('sites/all/modules/apachesolr/apachesolr_search.install','e9f082082d7cfde4f5bf323bef9d61adee2b0af17361181c01b42b97cc769995'),('sites/all/modules/apachesolr/apachesolr_search.module','66dcbd3c6591bb959356860aba83baf95fd189ccdbbe80ad9ffd5fcd5bc43f7d'),('sites/all/modules/apachesolr/apachesolr_search.pages.inc','896f62a8c59abd9d2cd8cbd38933dbca2aafa87026de4da57d62f5447754bd8a'),('sites/all/modules/apachesolr/Apache_Solr_Document.php','66f9ea29b133da185d7dc4b7c8acb10bcdf719509ef5ae752f647f1b579644cc'),('sites/all/modules/apachesolr/Drupal_Apache_Solr_Service.php','e93c0c090ae87ef762dceafe524f4bbe5de6836fb61717b9d1688e26ae28bbb9'),('sites/all/modules/apachesolr/plugins/facetapi/adapter.inc','33324f97bfdb86c63e76823dc4f2c040bea0477fa5fbb7bebbe5f0b7b42ecf75'),('sites/all/modules/apachesolr/plugins/facetapi/query_type_date.inc','cb51db69a7cd6fb167b1e34c7b81fd7eb14be94e1df5ad5ce45f721ede245120'),('sites/all/modules/apachesolr/plugins/facetapi/query_type_numeric_range.inc','e2425f1197a2a1a6288465b51c8f24f121b7aeb4499d2c907c128dde3fd05c65'),('sites/all/modules/apachesolr/plugins/facetapi/query_type_term.inc','28405f0d4d6a7c57dddc7b30d73dd0c62fafa958ad38f37fed4767767d719e51'),('sites/all/modules/apachesolr/Solr_Base_Query.php','9e649ac80cab6a05d3a98840689d925b372ec1719a71934effe854d1b4515b09'),('sites/all/modules/apachesolr/tests/apachesolr_base.test','9a8a12e2410c4b9b37bb19cdfdca6cde1fa8fbd1fd2f284b591856e3f9597886'),('sites/all/modules/apachesolr/tests/Dummy_Solr.php','dfaa998d55c7a4cfad6a206625e5b5045b9d486826f905cdab1a78a90f507236'),('sites/all/modules/apachesolr/tests/solr_base_query.test','db5104962df2f8eecf16ecff398a4a8b8c47562e5e038abe0f3e68c18aeda608'),('sites/all/modules/apachesolr/tests/solr_base_subquery.test','192783f376373bd06da3a5cb53cbc4251e0d4188c813212496ada50e0e68f919'),('sites/all/modules/apachesolr/tests/solr_document.test','ca63fa3e6312650b04e1db82bdac06ef6f456cafa91c629b1038f62f3b5a2e6e'),('sites/all/modules/apachesolr/tests/solr_index_and_search.test','39709390cab96cbbe8be23cb990f38572fb76b3e0d88863af343825ffe5f9817'),('sites/all/modules/civicrm/drupal/civicrm.install','00524b72ad03075a995a2da549ea057f33ca48dd5ce979d3f2d8e5397b906dd3'),('sites/all/modules/civicrm/drupal/civicrm.module','f557aaca6f0f1f85ae0b9fb50b4ce4c7cb2006b83d4832034ac306b2b3ce9169'),('sites/all/modules/civicrm/drupal/civicrm_user.inc','369771dfe8552770ca6a000605fbaba6cbd1879be37f1c707d726ee7d59b1a76'),('sites/all/modules/civicrm/drupal/modules/views/civicrm.views.inc','2a1bebc1f2674033040cc083255f6ec355a36c833350f121cf81bac15577e16f'),('sites/all/modules/civicrm/drupal/modules/views/civicrm.views_default.inc','62505a4a52ed3dd138b642ff10bf4219cc7339ab890ec54d9cd2a3104ed0ea7b'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field.inc','709a06b3d6ef089015a308c5f6b18ddf6ac34fced600606c6932a14bc6fdd908'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_address.inc','c108027291d7ae9cf3e955f443cfc710e3044c733e5e7357400ee426b917450f'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_contact_link.inc','235ce012cf31ad2097b0a58c5cac06dcf6d6d6a414535002746dc431503035a7'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_country.inc','d69d0844677cd89c88b906473d662f0a79a4982c500abeb5df892b47081da626'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_custom.inc','5494351695502988da82ab3eb9c59790fa41a85890f932125e88866f65e0b245'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_datetime.inc','10c34d27d15af6f99a1c9a11d194c01f81a3a74ab8f8c87ac28885e99fdd9fb7'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_drupalid.inc','25a83eaaa964ddc92fd1a15320924c0dca2832dc5d3fbf3009a5dcf44af1e8f4'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_email.inc','e93343c10de0df6bfd8fb2e68012c0ccfa7e6185a06ece3a64112fecdbc4d779'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_encounter_medium.inc','29b4da3125b11bee17122767b8cb47a5592ca5ff1d43f13e8be23a77e2366f25'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_event.inc','21737aa3210a382268ae47de1da692a24556b5d24b66ac7c1807f75ae4b03792'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_event_link.inc','fe7c4f6d5b36c5b2f8d33d2b66f03fe8dc2c88e04de5fd670ab6382544a7fc53'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_event_price_set.inc','959c737cb68308a8604252496b5137b1e5d8ebbf1239638abf2b6142a583ff93'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_file.inc','58421661896c9dd21286dd6da30db604ad803149e9fd14c6ed581b720759ed93'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link.inc','dcb1b88b99656f564fa09f5d8f64ab5d19bb4123ae229e20f83f7798aecabdbd'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_activity.inc','47d3d646da110490225e8a6597c7b62b7045ea682f31651df66b056d14eded28'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_contact.inc','3b5262e6946624aeabaf3682161b6c9546dc628ca54a80a7090f73bf7f54c27c'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_contribution.inc','0dee00d607a0b8981ad1c744137a63d792ca167d9576b65e96fbb6475b205283'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_delete.inc','91b0aa46e507cd35517b5cc5b72a4d897329df6e248c006e89ce0301d8652d65'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_edit.inc','b13d63a1cfcb9c6a45ead1297e6aa50524d8ec24db4f23c256df1a98dc5897c1'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_event.inc','6479c6e764879b40f84df5d27a4002f67e46420a66a67ab496cf774346b8dcf8'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_participant.inc','ac8f3692d494c3ddbb3923cbe5fc50d496f4910bb28e57d6abbab3451422c413'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_pcp.inc','408b86a4b2dc8af3f7fb78bd1a0347f18c62100ab0698e2039a98e8d778582b5'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_link_relationship.inc','d98ad42600f8b0c02cb67045f813e3fff78faadf9fb2e813deda88eb809c585e'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_location.inc','171fc35871ca9ce413e35ba19a87a488f24976f62751c60b9ed51806ecccb1ed'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_mail.inc','085485bfa7c53c53304dfbdb2a3d32448810c2a2724ac01917fbb0c020bed431'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_markup.inc','6c920c7c9d632a32d35d9128dc251cea78743cb9e762b5236bba3e5bc366c21c'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_money.inc','29c8df4b7dce943debeb8ec3d538b0aaba0dfd3c8d62530ad312a14ebe2bd81a'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_option.inc','50a470ac673259f7a1360b42b92012256cd8c7b0834d918facdf2b31720d81c7'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_pcp_raised_amount.inc','fe2a7efda577e2f543bbed664f54f495c3bb6aaba966710d5c05f85bedff8bb9'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_phone.inc','2e8289b422bf6b632c4481942682926f691a4d98654a46eeffdab6c34bf76b44'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_prefix.inc','45b61d4092be7e954af98ae06bd80eda8c7a4643717069502e8538861b129e66'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_pseudo_constant.inc','d1f83e51dd53d0e4bee4c9b238cccb6f7029dff9c98b9e854fb9b0e27362a661'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_relationship_type.inc','297c35d81d94796ade5c88d5e8bb0bd02b8986686a6d02c41f35e67299498c57'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_state.inc','9ae65a495b3e4129e69505a80c9b5827d47e408753450db7a083700a2f4eb514'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_field_suffix.inc','25a91001c96240cc780eb1857de86e74035de77b62e008d14db75795e82cbf05'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_country_multi.inc','610a9a086be11fe40df31820ee89afd51b54e934d8819822da5799fdd013c097'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_custom_option.inc','1a5bdb09e4f5072f1e8ed49dcbdaa1ca3196f75cacfca3d3a477c6e139e968a6'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_custom_single_option.inc','8d8f50cb83df9efc5079a05e4e11203bf527785dc3d5bca83ac58ad69036bfc4'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_datetime.inc','8b67ca6d832205bd16924e09caff7469ccf7693ba983abe00d54f65e5f46b6c6'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_domain.inc','9d1bf434c727c5aa69e6ddd0bbc758a5e8e8e50c7b35ea99dcd61aa4c1982d54'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_encounter_medium.inc','ac3ee092c2ec02a8878416181ce5ecf7f2845c1196a2cd08117ab46b9e90893c'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_group_status.inc','5a1773c1325df2fd64857b4be17e52096650a2a0b3830824c34e40be44c7bb17'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_prefix.inc','3162cb06e8ee216c4f6f43e1e69764370055b8e7fb5f6710bea1e58aff340a5c'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_pseudo_constant.inc','650e09d3ba2956b5c97ef22b8d301bad02499dae68f2baea20a40f828852a241'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_relationship_type.inc','47198369d0ba3905690c626c213f772437de183e489519d324635510b882a3e3'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_filter_suffix.inc','4dd6e84583bdd0072a499a0e9d29436c5ab3993c42c7f63b3d5cd76db9fc2f8d'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_relationship.inc','bb48de81befdfdbf76a8bbd3f236ed169cde17731086a504b4b3b9d192706490'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_relationship_contact2users.inc','81eb89799a6bf2d67c1ce574a1b3c43fb4dbfe0bfab954bc47af1f738c57c0f3'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_sort_date.inc','78d228352c0f042724a9a3b0c08ab5d8be9037692c377cff1bc2c7753276f54f'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_handler_sort_pcp_raised_amount.inc','b54f29574a0bf5078a91e5ac5236900c02e267437e3ea849ee2aa90f65f208dd'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/civicrm_plugin_argument_default.inc','624f6557636082b61570165907627dd0f062ee4776b8263e6c6b52a662d115bb'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_day.inc','122d225369366faaf77ab931267967148c0d05b54fa9e1cd8b3bece77e8fd5c6'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_fulldate.inc','7fa7238d220a17207d80f6e4f610482b4c016e3911103af0385f7c6da05654eb'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_month.inc','aa535be08d1a7c1ccf6fbaaa5632f860dbae6b7ce9e0ca431fb35b8d3bafd4a7'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_week.inc','722d502298b988baabbf001616d4e2a6a3f3bf5f15ae325cb6a27b7e1b2ab9a0'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_year.inc','a035995b9a0975c83def311e0f7774bd4568a9d8e4bbc05fa68487c9c08c778f'),('sites/all/modules/civicrm/drupal/modules/views/civicrm/views_handler_argument_civicrm_year_month.inc','2d5ef35716cc97bd61507feb3c51b7719671491cd43d051ce9ca85beedf4c963'),('sites/all/modules/civicrm/drupal/modules/views/plugins/calendar_plugin_row_civicrm.inc','662511e04a4fd4a2df393d3ea66e698adb719cfeed3e942f06b4eea6034e28ed'),('sites/all/modules/civicrm/drupal/modules/views/plugins/calendar_plugin_row_civicrm_event.inc','c5d1782bbbadbce2b8d53d37ea03e9b2ea360a8b8bdc6a5e33180927901161b9'),('sites/all/modules/entity/entity.features.inc','b77e91ea988218fd939a4e5f283b2c745d53e39159af5ab770eda6b66fcb33cb'),('sites/all/modules/entity/entity.i18n.inc','41e0e62af7e2774f62b162d597bb3244551c280296b692b29d039a2c243d7059'),('sites/all/modules/entity/entity.info.inc','962cf92c630a2954c4e430164e1d04b8125eb1103dbefb7b954182b4382d072d'),('sites/all/modules/entity/entity.rules.inc','774199059d1b3ebe6d3fe7a49dbb1550df489055a3d066b5de54edda8dd7ba84'),('sites/all/modules/entity/entity.test','80139f908ab540b4a30300c027d33b87ab250bfcf4fb44ec18adae7844b1d635'),('sites/all/modules/entity/entity_token.module','0c1ad6fb6f8c430e47a81be6d08180883c5a1ee728ce8b5dd0775713b34fb862'),('sites/all/modules/entity/entity_token.tokens.inc','d9246ed9a7d4cfdf16370d3c68f991fb103838b6e2c9682c385d2144629504ee'),('sites/all/modules/entity/includes/entity.controller.inc','26df053bd4866bf94f57d6ae57bf2d7e3230e74723956ad03fd606a05b41ecf5'),('sites/all/modules/entity/includes/entity.inc','71161b01ef9e007fd3d8e40b5ae8652194da7cb208c9f9538b63f90d8f0e6ac7'),('sites/all/modules/entity/includes/entity.ui.inc','b2bdd28eb3af34cb7c2ff1e58e0cf679d26cfd68cd9414dc3abfbd297443874d'),('sites/all/modules/entity/includes/entity.wrapper.inc','fb771c3dc1ea5d0444bc97246b4cf0d8a4609fc66d24ed2b0de507dcc8fb776c'),('sites/all/modules/entity/views/entity.views.inc','c2949770db351bc894ab5d715a3e9c9c97c4477e0c42cd90347160ddd228bbfa'),('sites/all/modules/entity/views/handlers/entity_views_field_handler_helper.inc','69d5ac25d8686ee43be607d682795b2cae0278500392f5f7970ac9f45913ee84'),('sites/all/modules/entity/views/handlers/entity_views_handler_area_entity.inc','e86aceccf21cbbca4bef7d2c049dc93a8f5a01c6bc73e7b2f92f5659a9eedd03'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_boolean.inc','b28b8eee8761ba7a6af35d97ab7aaee28406e6c227271f9769818560626c5791'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_date.inc','b0f5be5b399de94934b24e84c8cf6053a043f6b00c60dcffa752daeafdd38778'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_duration.inc','6a7f83e4ce141428d3d782db0c71f3cf4b141eff4f551b826fef7e52ac728e01'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_entity.inc','f0ea06a0d67b0f4f498414def4d0989aad56d97780107b2fbabdaafc807adbf2'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_field.inc','893121efbce2a7181e31147bade260c9cc657cbd33b0d254cb28b2650e57566d'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_numeric.inc','f14e2b063930e8820af381b4f5e83c7278440e7804ab88cfde865b6c94e7c0f6'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_options.inc','16317359cf59afb290d78eb61228f93dda408081e8c2f88db2f90a60d68d31d6'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_text.inc','ae26b8a9a86c36a166644a1f5a9bae0676f535345c092d882df0177ded305bdd'),('sites/all/modules/entity/views/handlers/entity_views_handler_field_uri.inc','79ecaa3eb17dfdd0ca077351b75a2c0adf411ebc04720e7cc0e2397674225f24'),('sites/all/modules/entity/views/handlers/entity_views_handler_relationship.inc','b69bc538d1e1e0f91f8485ca54c3b6e2be025caa47619734c467377cf89041b9'),('sites/all/modules/entity/views/handlers/entity_views_handler_relationship_by_bundle.inc','25aebf66cd2437bd5867fef8f0e0e25d4308b9ce491cc79801e9d3cbed68bcba'),('sites/all/modules/entity/views/plugins/entity_views_plugin_row_entity_view.inc','ba557790215f2658146424d933e0d17787a0b15180c5815f23428448ccf056a0'),('sites/all/modules/ldap/ldap_authentication/LdapAuthenticationConf.class.php','e7435b39654d75241a30a3aa75332cddbf52777e3b1d38eb90240c84fe5b049c'),('sites/all/modules/ldap/ldap_authentication/LdapAuthenticationConfAdmin.class.php','f2db8118f9788316fba869155c4e246e57823764f21c15c96e6f8e43a685148f'),('sites/all/modules/ldap/ldap_authentication/ldap_authentication.admin.inc','0f0c38235f22024ffc67b7fcdb6b075f3ed8b80c392b27f4758c1b1113797694'),('sites/all/modules/ldap/ldap_authentication/ldap_authentication.inc','3c48f00d1b9186ce384f670354216254831c645a6607a261f45406a953596010'),('sites/all/modules/ldap/ldap_authentication/ldap_authentication.install','d0060cacea947cbc33a9fdb2bc8b3b3a9c7bf0e1a0b1ac46569749d9ae9166bd'),('sites/all/modules/ldap/ldap_authentication/ldap_authentication.module','b8248358c9245978dd9534b426d27588897c19e22ab6ba1c2f9b60bdf91dbc9f'),('sites/all/modules/ldap/ldap_authentication/ldap_authentication.theme.inc','61f50af85f5355368cf323ef6a5a47184971b177e81fe9fb3439705c2bf2441f'),('sites/all/modules/ldap/ldap_authentication/tests/ldap_authentication.test','ed4334d06c01e6bae2c7bba77b01a1336f4add845722249a4a4d140f871ffef8'),('sites/all/modules/ldap/ldap_authorization/LdapAuthorizationConsumerAbstract.class.php','53ffa503c0fabe734076fc45947f82870b92bcf55e9bb3351da16ec28ea33d8c'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization.admin.inc','3ee6623a874c6e31229b151c3e5c548091ca4e03410ea67b2e09b9e7e53c41d9'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization.admin.test.inc','8d158bd0e1a8a9f57069e820cda506374a413413a804e1ea8dde7b279408d8bd'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization.install','61a445ff8d781fe94b29fa082e14f4487e9c44f39e2a1428700e864eadbc8668'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization.module','d5c8d412ea6d3a568bcbb6fa85da35d546312a2e02328853ccd41b6202d4db12'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization.theme.inc','bf8b29f184f646c4147c0fa6393b7875ed0dfd9907123cd59852d317742d8c5c'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization_drupal_role/LdapAuthorizationConsumerRole.class.php','ea9afef6ea83fd1dba532e5427bb844f85bca6fb8149af83bec3caf27fafd678'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization_drupal_role/ldap_authorization_drupal_role.module','aaf7e96b0c91d4237a8cf35db1dae9e91c0e6cce9ddf5dc597ff03d542a44343'),('sites/all/modules/ldap/ldap_authorization/tests/1197636/1197636.test','e6377906226f0b918d193226f457f96331f063db18e1bd0783e4027d5eee78c4'),('sites/all/modules/ldap/ldap_authorization/tests/BasicTests/BasicTests.test','e7c3780a60f9ffc66cecd8eab9730c29999e0efbba0931ac05b3292e42e34d51'),('sites/all/modules/ldap/ldap_authorization/tests/DeriveFromAttr/DeriveFromAttr.test','4517aab611e17160d735c2f8b765c52a0903484c2642a6b74fc7ed7547f197fc'),('sites/all/modules/ldap/ldap_authorization/tests/DeriveFromDN/DeriveFromDN.test','29677a4a74c8a56946a6a1352a0801953b2392206a524efaa6db25be22c1e2ac'),('sites/all/modules/ldap/ldap_authorization/tests/DeriveFromEntry/DeriveFromEntry.test','23fecb9dc8e1a7601854e465cb5e0357bd7a14a95d50ae96a8995dda4a817fd9'),('sites/all/modules/ldap/ldap_authorization/tests/Og/Og.test','ea10ea7742a6e6276ef7b85fa6cad240b07498cc1a42c9ce17a6470e88d721a6'),('sites/all/modules/ldap/ldap_authorization/tests/Og/Og2.test','44a2b76635cd74ff33a603e010ea1286f973d20c2b21eb56aaaf819d231da372'),('sites/all/modules/ldap/ldap_authorization/tests/Other/Other.test','5ea0a204dd771e1388897a0955f8005da9875cc91108b938c4168e0b3d16bcf9'),('sites/all/modules/ldap/ldap_servers/LdapServer.class.php','a706fcd493be24f6e7ea2fdb83afbee6f2f8a83cf8f8d3f78248413bf31ec594'),('sites/all/modules/ldap/ldap_servers/LdapServerAdmin.class.php','4274c5f95a347c2405acef294764160979b59f6d7606ad7797cfb23e5b7882ae'),('sites/all/modules/ldap/ldap_servers/ldap_servers.admin.inc','45146145a6791701d470f5b24436bc5ed7fdd4a0c920c4bf234711fd75fb97f2'),('sites/all/modules/ldap/ldap_servers/ldap_servers.encryption.inc','5031ac6c2af75eb723072423322ab5da7eeeec9a0baae7df1f1f8d764d48935a'),('sites/all/modules/ldap/ldap_servers/ldap_servers.functions.inc','bee846a002e482a2e9de3c20f123c8346397f2967aab0acbf96811067d7648d5'),('sites/all/modules/ldap/ldap_servers/ldap_servers.inc','5fb129b0b79769c43a7601d270a3b357869c28ca68b5b1df647e0ba33d5e5d18'),('sites/all/modules/ldap/ldap_servers/ldap_servers.install','86004221b41f849b165398edcf4e3bd4aad56bf67b22a24391d09418cecfe572'),('sites/all/modules/ldap/ldap_servers/ldap_servers.module','efe82879eb30350102194fb5e664531c10e63bb7d24d130c348d62e71d38866b'),('sites/all/modules/ldap/ldap_servers/ldap_servers.settings.inc','1af057b7c9cf24b2ee6e069426d12dbf1fa7a8ed221367f212e04d6d99526c6e'),('sites/all/modules/ldap/ldap_servers/ldap_servers.test_form.inc','70c20ff242567be12202e934119a617fbb07fb8191cdaf0a4074d9e877fba3e5'),('sites/all/modules/ldap/ldap_servers/ldap_servers.theme.inc','b7f4f9f3567e78db7f69324635e9219e52f60fa5f7d90de79a0d08209a8ccc40'),('sites/all/modules/ldap/ldap_servers/tests/ldap_servers.test','592d90b5ca05ded0ec2731a6298d0d8e08ec9527268c15c6ce0e295869f3ad67'),('sites/all/modules/rules/includes/faces.inc','72352c57a12e740b80e235ee5fe245b91c5bca9938f4751670acc8e719626bcb'),('sites/all/modules/rules/includes/rules.core.inc','c5493c262bdd5cc8f27450acf798a5aff55aeed4be475352763a639d84e30a76'),('sites/all/modules/rules/includes/rules.plugins.inc','a092c4f9b7c2914372306292f8447561bd0fa442efca57d557cfd1a3a24b1bde'),('sites/all/modules/rules/includes/rules.processor.inc','902d554987e019cfe9c8818a83a96db7e02c556efb4c63961ee9d091ee98c988'),('sites/all/modules/rules/includes/rules.state.inc','ba968f8fd908a8c19915426670084ecfcdb902a012f056e221ab955292f0f361'),('sites/all/modules/rules/modules/php.eval.inc','4d579f9d83912a1ff4e3e35b20a7792fb7e282d8d07c706e944ffbb6b20c42f6'),('sites/all/modules/rules/modules/rules_core.eval.inc','edf268c4e9a24d700ef08ca5cf8addc93a437d30cb0129aeb7aa93e76f8f855e'),('sites/all/modules/rules/modules/system.eval.inc','088e8815d8031ed54e7d43db9026b48b02f7413a4902fe8a5bf9a7c1e626f10a'),('sites/all/modules/rules/rules.features.inc','dc219fc6df253743cfe775503edffd6ba8b76fe9d8b549294242b0691cad0302'),('sites/all/modules/rules/rules_admin/rules_admin.inc','c41d8491b6e19849c99726f4e127e081dac4e92b9052b3446aafac1802e26e71'),('sites/all/modules/rules/rules_admin/rules_admin.module','38cd2e74c2d951d4a4d6025191cf4aa87be9ece9b2bf5b248ce0bfdfc62aa9cb'),('sites/all/modules/rules/tests/rules.test','f6703ec290f5181a7bd55dcc49aa9129be11bf2c18b2a367bdef1e259d2006ed'),('sites/all/modules/rules/ui/ui.controller.inc','659a87ecb456b5ce82f4fd1be13281e1a7f5fed16831413048e1be98895986ac'),('sites/all/modules/rules/ui/ui.core.inc','e66874620b2330dd36ac0c69cefa77cb78cdb98eb398fad28e48911a5e8689ff'),('sites/all/modules/rules/ui/ui.data.inc','3b6171d739e30ec472ee844d6118e0960f699434ce8b1e0032deffa3fea6fe16'),('sites/all/modules/rules/ui/ui.plugins.inc','62815f3d11555a9682b088bdacb3c4ece1350d5e8ca2c9a9330a07929dddba36');
/*!40000 ALTER TABLE `registry_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `name` (`name`),
  KEY `name_weight` (`name`,`weight`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (4,'Administrator',0),(8,'Analytics User',0),(1,'anonymous user',0),(2,'authenticated user',0),(5,'Conference Services',0),(12,'Data Entry',0),(16,'Mailing Approver',0),(14,'Mailing Creator',0),(15,'Mailing Scheduler',0),(17,'Mailing Viewer',0),(19,'Manage Bluebird Inbox',0),(9,'Office Administrator',0),(10,'Office Manager',0),(7,'Print Production',0),(18,'Print Production Staff',0),(6,'SOS',0),(11,'Staff',0),(3,'Superuser',0),(13,'Volunteer',0);
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permission`
--

DROP TABLE IF EXISTS `role_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permission` (
  `rid` int(10) unsigned NOT NULL,
  `permission` varchar(128) NOT NULL DEFAULT '',
  `module` varchar(255) NOT NULL DEFAULT '' COMMENT 'The module declaring the permission.',
  PRIMARY KEY (`rid`,`permission`),
  KEY `permission` (`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permission`
--

LOCK TABLES `role_permission` WRITE;
/*!40000 ALTER TABLE `role_permission` DISABLE KEYS */;
INSERT INTO `role_permission` VALUES (0,'cancel users with no custom roles','administerusersbyrole'),(0,'cancel users with role Administrator','administerusersbyrole'),(0,'cancel users with role Administrator and other roles','administerusersbyrole'),(0,'cancel users with role DataEntry','administerusersbyrole'),(0,'cancel users with role DataEntry and other roles','administerusersbyrole'),(0,'cancel users with role MailingApprover','administerusersbyrole'),(0,'cancel users with role MailingApprover and other roles','administerusersbyrole'),(0,'cancel users with role MailingCreator','administerusersbyrole'),(0,'cancel users with role MailingCreator and other roles','administerusersbyrole'),(0,'cancel users with role MailingScheduler','administerusersbyrole'),(0,'cancel users with role MailingScheduler and other roles','administerusersbyrole'),(0,'cancel users with role MailingViewer','administerusersbyrole'),(0,'cancel users with role MailingViewer and other roles','administerusersbyrole'),(0,'cancel users with role OfficeAdministrator','administerusersbyrole'),(0,'cancel users with role OfficeAdministrator and other roles','administerusersbyrole'),(0,'cancel users with role OfficeManager','administerusersbyrole'),(0,'cancel users with role OfficeManager and other roles','administerusersbyrole'),(0,'cancel users with role PrintProduction','administerusersbyrole'),(0,'cancel users with role PrintProduction and other roles','administerusersbyrole'),(0,'cancel users with role PrintProductionStaff','administerusersbyrole'),(0,'cancel users with role PrintProductionStaff and other roles','administerusersbyrole'),(0,'cancel users with role SOS','administerusersbyrole'),(0,'cancel users with role SOS and other roles','administerusersbyrole'),(0,'cancel users with role Staff','administerusersbyrole'),(0,'cancel users with role Staff and other roles','administerusersbyrole'),(0,'cancel users with role Volunteer','administerusersbyrole'),(0,'cancel users with role Volunteer and other roles','administerusersbyrole'),(0,'edit users with no custom roles','administerusersbyrole'),(0,'edit users with role Administrator','administerusersbyrole'),(0,'edit users with role Administrator and other roles','administerusersbyrole'),(0,'edit users with role DataEntry','administerusersbyrole'),(0,'edit users with role DataEntry and other roles','administerusersbyrole'),(0,'edit users with role MailingApprover','administerusersbyrole'),(0,'edit users with role MailingApprover and other roles','administerusersbyrole'),(0,'edit users with role MailingCreator','administerusersbyrole'),(0,'edit users with role MailingCreator and other roles','administerusersbyrole'),(0,'edit users with role MailingScheduler','administerusersbyrole'),(0,'edit users with role MailingScheduler and other roles','administerusersbyrole'),(0,'edit users with role MailingViewer','administerusersbyrole'),(0,'edit users with role MailingViewer and other roles','administerusersbyrole'),(0,'edit users with role OfficeAdministrator','administerusersbyrole'),(0,'edit users with role OfficeAdministrator and other roles','administerusersbyrole'),(0,'edit users with role OfficeManager','administerusersbyrole'),(0,'edit users with role OfficeManager and other roles','administerusersbyrole'),(0,'edit users with role PrintProduction','administerusersbyrole'),(0,'edit users with role PrintProduction and other roles','administerusersbyrole'),(0,'edit users with role PrintProductionStaff','administerusersbyrole'),(0,'edit users with role PrintProductionStaff and other roles','administerusersbyrole'),(0,'edit users with role SOS','administerusersbyrole'),(0,'edit users with role SOS and other roles','administerusersbyrole'),(0,'edit users with role Staff','administerusersbyrole'),(0,'edit users with role Staff and other roles','administerusersbyrole'),(0,'edit users with role Volunteer','administerusersbyrole'),(0,'edit users with role Volunteer and other roles','administerusersbyrole'),(1,'access content','node'),(1,'use text format 1','filter'),(2,'access content','node'),(2,'change own e-mail','userprotect'),(2,'change own openid','userprotect'),(2,'change own password','userprotect'),(2,'use text format 1','filter'),(2,'view own unpublished content','node'),(3,'access administration pages','system'),(3,'access all cases and activities','civicrm'),(3,'access all custom data','civicrm'),(3,'access CiviCRM','civicrm'),(3,'access CiviReport','civicrm'),(3,'access Contact Dashboard','civicrm'),(3,'access deleted contacts','civicrm'),(3,'access my cases and activities','civicrm'),(3,'access Report Criteria','civicrm'),(3,'access uploaded files','civicrm'),(3,'access user profiles','user'),(3,'add cases','civicrm'),(3,'add contacts','civicrm'),(3,'administer blocks','block'),(3,'administer CiviCase','civicrm'),(3,'administer CiviCRM','civicrm'),(3,'administer dedupe rules','civicrm'),(3,'administer inbox polling','nyss_civihooks'),(3,'administer permissions','user'),(3,'administer Reports','civicrm'),(3,'administer reserved groups','civicrm'),(3,'administer reserved tags','civicrm'),(3,'administer Tagsets','civicrm'),(3,'administer userprotect','userprotect'),(3,'administer users','user'),(3,'assign roles','roleassign'),(3,'create users','administerusersbyrole'),(3,'delete activities','civicrm'),(3,'delete contacts','civicrm'),(3,'delete contacts permanently','nyss_civihooks'),(3,'delete in CiviCase','civicrm'),(3,'edit all contacts','civicrm'),(3,'edit groups','civicrm'),(3,'edit users with role Administrator','administerusersbyrole'),(3,'edit users with role SOS','administerusersbyrole'),(3,'edit users with role Staff','administerusersbyrole'),(3,'edit users with role Volunteer','administerusersbyrole'),(3,'export print production files','nyss_civihooks'),(3,'import contacts','civicrm'),(3,'merge duplicate contacts','civicrm'),(3,'profile create','civicrm'),(3,'profile edit','civicrm'),(3,'profile listings','civicrm'),(3,'profile listings and forms','civicrm'),(3,'profile view','civicrm'),(3,'translate CiviCRM','civicrm'),(3,'use PHP for settings',''),(3,'use text format 1','filter'),(3,'view all activities','civicrm'),(3,'view all contacts','civicrm'),(3,'view the administration theme','system'),(4,'access administration pages','system'),(4,'access all cases and activities','civicrm'),(4,'access all custom data','civicrm'),(4,'access CiviCRM','civicrm'),(4,'access CiviMail','civicrm'),(4,'access CiviReport','civicrm'),(4,'access Contact Dashboard','civicrm'),(4,'access deleted contacts','civicrm'),(4,'access my cases and activities','civicrm'),(4,'access Report Criteria','civicrm'),(4,'access uploaded files','civicrm'),(4,'add cases','civicrm'),(4,'add contacts','civicrm'),(4,'administer CiviCRM','civicrm'),(4,'administer dedupe rules','civicrm'),(4,'administer district','nyss_civihooks'),(4,'administer inbox polling','nyss_civihooks'),(4,'administer Reports','civicrm'),(4,'administer reserved groups','civicrm'),(4,'administer reserved tags','civicrm'),(4,'administer users','user'),(4,'approve mailings','civicrm'),(4,'assign roles','roleassign'),(4,'create mailings','civicrm'),(4,'delete activities','civicrm'),(4,'delete contacts','civicrm'),(4,'delete contacts permanently','nyss_civihooks'),(4,'delete in CiviCase','civicrm'),(4,'delete in CiviMail','civicrm'),(4,'edit all contacts','civicrm'),(4,'edit groups','civicrm'),(4,'edit users with role ConferenceServices','administerusersbyrole'),(4,'edit users with role DataEntry','administerusersbyrole'),(4,'edit users with role MailingApprover','administerusersbyrole'),(4,'edit users with role MailingCreator','administerusersbyrole'),(4,'edit users with role MailingScheduler','administerusersbyrole'),(4,'edit users with role MailingViewer','administerusersbyrole'),(4,'edit users with role ManageBluebirdInbox','administerusersbyrole'),(4,'edit users with role ManageBluebirdInbox and other roles','administerusersbyrole'),(4,'edit users with role OfficeAdministrator','administerusersbyrole'),(4,'edit users with role OfficeManager','administerusersbyrole'),(4,'edit users with role SOS','administerusersbyrole'),(4,'edit users with role Staff','administerusersbyrole'),(4,'edit users with role Volunteer','administerusersbyrole'),(4,'export print production files','nyss_civihooks'),(4,'import contacts','civicrm'),(4,'merge duplicate contacts','civicrm'),(4,'profile listings','civicrm'),(4,'profile listings and forms','civicrm'),(4,'profile view','civicrm'),(4,'schedule mailings','civicrm'),(4,'use text format 1','filter'),(4,'view all activities','civicrm'),(4,'view all contacts','civicrm'),(4,'view all notes','civicrm'),(4,'view the administration theme','system'),(5,'access all custom data','civicrm'),(5,'access CiviCRM','civicrm'),(5,'access CiviReport','civicrm'),(5,'access Report Criteria','civicrm'),(5,'administer Reports','civicrm'),(5,'edit all contacts','civicrm'),(5,'profile listings','civicrm'),(5,'profile view','civicrm'),(5,'use text format 1','filter'),(5,'view all activities','civicrm'),(5,'view all contacts','civicrm'),(6,'access all custom data','civicrm'),(6,'access CiviCRM','civicrm'),(6,'access CiviReport','civicrm'),(6,'access Report Criteria','civicrm'),(6,'access uploaded files','civicrm'),(6,'add contacts','civicrm'),(6,'administer Reports','civicrm'),(6,'delete contacts','civicrm'),(6,'edit all contacts','civicrm'),(6,'edit groups','civicrm'),(6,'profile listings','civicrm'),(6,'profile view','civicrm'),(6,'use text format 1','filter'),(6,'view all activities','civicrm'),(6,'view all contacts','civicrm'),(7,'access all custom data','civicrm'),(7,'access CiviCRM','civicrm'),(7,'access CiviReport','civicrm'),(7,'access site in maintenance mode','system'),(7,'administer reserved groups','civicrm'),(7,'administer site configuration','system'),(7,'edit groups','civicrm'),(7,'export print production files','nyss_civihooks'),(7,'import contacts','civicrm'),(7,'import print production','nyss_civihooks'),(7,'profile listings','civicrm'),(7,'profile view','civicrm'),(7,'use text format 1','filter'),(7,'view all contacts','civicrm'),(8,'access all custom data','civicrm'),(8,'access CiviCRM','civicrm'),(8,'access CiviReport','civicrm'),(8,'access Report Criteria','civicrm'),(8,'administer Reports','civicrm'),(8,'profile listings','civicrm'),(8,'profile view','civicrm'),(8,'use text format 1','filter'),(8,'view all activities','civicrm'),(8,'view all contacts','civicrm'),(9,'access administration pages','system'),(9,'access all cases and activities','civicrm'),(9,'access all custom data','civicrm'),(9,'access CiviCRM','civicrm'),(9,'access CiviReport','civicrm'),(9,'access Contact Dashboard','civicrm'),(9,'access deleted contacts','civicrm'),(9,'access my cases and activities','civicrm'),(9,'access Report Criteria','civicrm'),(9,'access uploaded files','civicrm'),(9,'add cases','civicrm'),(9,'add contacts','civicrm'),(9,'administer district','nyss_civihooks'),(9,'administer inbox polling','nyss_civihooks'),(9,'administer Reports','civicrm'),(9,'administer reserved tags','civicrm'),(9,'administer users','user'),(9,'assign roles','roleassign'),(9,'delete activities','civicrm'),(9,'delete contacts','civicrm'),(9,'delete contacts permanently','nyss_civihooks'),(9,'delete in CiviCase','civicrm'),(9,'edit all contacts','civicrm'),(9,'edit groups','civicrm'),(9,'edit users with role DataEntry','administerusersbyrole'),(9,'edit users with role MailingApprover','administerusersbyrole'),(9,'edit users with role MailingCreator','administerusersbyrole'),(9,'edit users with role MailingScheduler','administerusersbyrole'),(9,'edit users with role MailingViewer','administerusersbyrole'),(9,'edit users with role ManageBluebirdInbox','administerusersbyrole'),(9,'edit users with role ManageBluebirdInbox and other roles','administerusersbyrole'),(9,'edit users with role OfficeManager','administerusersbyrole'),(9,'edit users with role SOS','administerusersbyrole'),(9,'edit users with role Staff','administerusersbyrole'),(9,'edit users with role Volunteer','administerusersbyrole'),(9,'merge duplicate contacts','civicrm'),(9,'profile listings','civicrm'),(9,'profile listings and forms','civicrm'),(9,'profile view','civicrm'),(9,'use text format 1','filter'),(9,'view all activities','civicrm'),(9,'view all contacts','civicrm'),(9,'view the administration theme','system'),(10,'access all cases and activities','civicrm'),(10,'access all custom data','civicrm'),(10,'access CiviCRM','civicrm'),(10,'access CiviReport','civicrm'),(10,'access Contact Dashboard','civicrm'),(10,'access deleted contacts','civicrm'),(10,'access my cases and activities','civicrm'),(10,'access Report Criteria','civicrm'),(10,'access uploaded files','civicrm'),(10,'add cases','civicrm'),(10,'add contacts','civicrm'),(10,'administer inbox polling','nyss_civihooks'),(10,'administer Reports','civicrm'),(10,'delete activities','civicrm'),(10,'delete contacts','civicrm'),(10,'delete in CiviCase','civicrm'),(10,'edit all contacts','civicrm'),(10,'edit groups','civicrm'),(10,'profile listings','civicrm'),(10,'profile listings and forms','civicrm'),(10,'profile view','civicrm'),(10,'use text format 1','filter'),(10,'view all activities','civicrm'),(10,'view all contacts','civicrm'),(11,'access all cases and activities','civicrm'),(11,'access all custom data','civicrm'),(11,'access CiviCRM','civicrm'),(11,'access CiviReport','civicrm'),(11,'access Contact Dashboard','civicrm'),(11,'access deleted contacts','civicrm'),(11,'access my cases and activities','civicrm'),(11,'access Report Criteria','civicrm'),(11,'access uploaded files','civicrm'),(11,'add cases','civicrm'),(11,'add contacts','civicrm'),(11,'administer Reports','civicrm'),(11,'delete activities','civicrm'),(11,'delete contacts','civicrm'),(11,'delete in CiviCase','civicrm'),(11,'edit all contacts','civicrm'),(11,'edit groups','civicrm'),(11,'profile listings','civicrm'),(11,'profile view','civicrm'),(11,'use text format 1','filter'),(11,'view all activities','civicrm'),(11,'view all contacts','civicrm'),(12,'access all custom data','civicrm'),(12,'access CiviCRM','civicrm'),(12,'access uploaded files','civicrm'),(12,'add contacts','civicrm'),(12,'edit all contacts','civicrm'),(12,'profile listings','civicrm'),(12,'profile listings and forms','civicrm'),(12,'profile view','civicrm'),(12,'use text format 1','filter'),(12,'view all activities','civicrm'),(12,'view all contacts','civicrm'),(13,'access all custom data','civicrm'),(13,'access CiviCRM','civicrm'),(13,'access my cases and activities','civicrm'),(13,'access uploaded files','civicrm'),(13,'add contacts','civicrm'),(13,'profile listings','civicrm'),(13,'profile view','civicrm'),(13,'use text format 1','filter'),(13,'view all activities','civicrm'),(13,'view all contacts','civicrm'),(14,'create mailings','civicrm'),(14,'use text format 1','filter'),(15,'schedule mailings','civicrm'),(15,'use text format 1','filter'),(16,'approve mailings','civicrm'),(16,'use text format 1','filter'),(17,'use text format 1','filter'),(17,'view mass email','nyss_civihooks'),(18,'access all custom data','civicrm'),(18,'access CiviCRM','civicrm'),(18,'access CiviReport','civicrm'),(18,'access site in maintenance mode','system'),(18,'administer reserved groups','civicrm'),(18,'administer site configuration','system'),(18,'edit groups','civicrm'),(18,'export print production files','nyss_civihooks'),(18,'import contacts','civicrm'),(18,'profile listings','civicrm'),(18,'profile view','civicrm'),(18,'use text format 1','filter'),(18,'view all contacts','civicrm'),(19,'administer inbox polling','nyss_civihooks');
/*!40000 ALTER TABLE `role_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rules_config`
--

DROP TABLE IF EXISTS `rules_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rules_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The internal identifier for any configuration.',
  `name` varchar(64) NOT NULL COMMENT 'The name of the configuration.',
  `label` varchar(255) NOT NULL DEFAULT 'unlabeled' COMMENT 'The label of the configuration.',
  `plugin` varchar(127) NOT NULL COMMENT 'The name of the plugin of this configuration.',
  `active` int(11) NOT NULL DEFAULT '1' COMMENT 'Boolean indicating whether the configuration is active. Usage depends on how the using module makes use of it.',
  `weight` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Weight of the configuration. Usage depends on how the using module makes use of it.',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'The exportable status of the entity.',
  `module` varchar(255) DEFAULT NULL COMMENT 'The name of the providing module if the entity has been defined in code.',
  `data` longblob COMMENT 'Everything else, serialized.',
  `dirty` tinyint(4) NOT NULL DEFAULT '0',
  `access_exposed` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether to use a permission to control access for using components.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `plugin` (`plugin`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rules_config`
--

LOCK TABLES `rules_config` WRITE;
/*!40000 ALTER TABLE `rules_config` DISABLE KEYS */;
INSERT INTO `rules_config` VALUES (1,'rules_notify_creator_of_approval','Notify Creator of Approval','reaction rule',1,0,1,'rules','O:17:\"RulesReactionRule\":14:{s:9:\"\0*\0parent\";N;s:2:\"id\";s:1:\"1\";s:12:\"\0*\0elementId\";i:1;s:6:\"weight\";s:1:\"0\";s:8:\"settings\";a:0:{}s:4:\"name\";s:32:\"rules_notify_creator_of_approval\";s:6:\"module\";s:5:\"rules\";s:6:\"status\";s:1:\"1\";s:5:\"label\";s:26:\"Notify Creator of Approval\";s:4:\"tags\";a:0:{}s:11:\"\0*\0children\";a:1:{i:0;O:11:\"RulesAction\":6:{s:9:\"\0*\0parent\";r:1;s:2:\"id\";N;s:12:\"\0*\0elementId\";i:4;s:6:\"weight\";i:0;s:8:\"settings\";a:4:{s:2:\"to\";s:22:\"{mailing.creatorEmail}\";s:7:\"subject\";s:52:\"Status: {mailing.approvalStatus} ({mailing.subject})\";s:7:\"message\";s:479:\"<p>The following email has been <strong>{mailing.approvalStatus}</strong>: {mailing.name}</p>\r\n\r\n<p>The following email approval/rejection message has been included:<br />\r\n{mailing.approvalNote}</p>\r\n\r\n<p>You have no further steps to take. The email will enter the mailing queue and be delivered shortly. Note that emails may experience some delay based on the size of the email and volume of recipients.</p>\r\n\r\n<p>The content of the email is:</p>\r\n<div>\r\n{mailing.html}\r\n</div>\";s:4:\"from\";N;}s:14:\"\0*\0elementName\";s:18:\"mailing_send_email\";}}s:7:\"\0*\0info\";a:0:{}s:13:\"\0*\0conditions\";O:8:\"RulesAnd\":8:{s:9:\"\0*\0parent\";r:1;s:2:\"id\";N;s:12:\"\0*\0elementId\";i:2;s:6:\"weight\";i:0;s:8:\"settings\";a:0:{}s:11:\"\0*\0children\";a:1:{i:0;O:14:\"RulesCondition\":7:{s:9:\"\0*\0parent\";r:25;s:2:\"id\";N;s:12:\"\0*\0elementId\";i:3;s:6:\"weight\";i:0;s:8:\"settings\";a:1:{s:21:\"approvalstatus:select\";s:7:\"mailing\";}s:14:\"\0*\0elementName\";s:40:\"civicrm_rules_condition_mailing_approved\";s:9:\"\0*\0negate\";b:0;}}s:7:\"\0*\0info\";a:0:{}s:9:\"\0*\0negate\";b:0;}s:9:\"\0*\0events\";a:1:{i:0;s:16:\"mailing_approved\";}}',0,0),(2,'rules_notify_creator_of_rejection','Notify Creator of Rejection','reaction rule',1,0,1,'rules','O:17:\"RulesReactionRule\":14:{s:9:\"\0*\0parent\";N;s:2:\"id\";s:1:\"2\";s:12:\"\0*\0elementId\";i:1;s:6:\"weight\";s:1:\"0\";s:8:\"settings\";a:0:{}s:4:\"name\";s:33:\"rules_notify_creator_of_rejection\";s:6:\"module\";s:5:\"rules\";s:6:\"status\";s:1:\"1\";s:5:\"label\";s:27:\"Notify Creator of Rejection\";s:4:\"tags\";a:0:{}s:11:\"\0*\0children\";a:1:{i:0;O:11:\"RulesAction\":6:{s:9:\"\0*\0parent\";r:1;s:2:\"id\";N;s:12:\"\0*\0elementId\";i:4;s:6:\"weight\";i:0;s:8:\"settings\";a:4:{s:2:\"to\";s:22:\"{mailing.creatorEmail}\";s:7:\"subject\";s:52:\"Status: {mailing.approvalStatus} ({mailing.subject})\";s:7:\"message\";s:534:\"<p>The following email has been <strong>{mailing.approvalStatus}</strong>: {mailing.name}</p>\r\n\r\n<p>The following email approval/rejection message has been included:<br />\r\n<em>{mailing.approvalNote}</em></p>\r\n\r\n<p>You will find the rejected email in Bluebird under the draft email management page. You can review and edit the mail here:</p>\r\n<ul><li>{mailing.editUrl}</li></ul>\r\n\r\n<p>Once you\'ve updated the email you will need to reschedule it and submit for approval. The content of the email is:</p>\r\n<div>\r\n{mailing.html}\r\n</div>\";s:11:\"from:select\";s:0:\"\";}s:14:\"\0*\0elementName\";s:18:\"mailing_send_email\";}}s:7:\"\0*\0info\";a:0:{}s:13:\"\0*\0conditions\";O:8:\"RulesAnd\":8:{s:9:\"\0*\0parent\";r:1;s:2:\"id\";N;s:12:\"\0*\0elementId\";i:2;s:6:\"weight\";i:0;s:8:\"settings\";a:0:{}s:11:\"\0*\0children\";a:1:{i:0;O:14:\"RulesCondition\":7:{s:9:\"\0*\0parent\";r:25;s:2:\"id\";N;s:12:\"\0*\0elementId\";i:5;s:6:\"weight\";i:0;s:8:\"settings\";a:1:{s:21:\"approvalstatus:select\";s:7:\"mailing\";}s:14:\"\0*\0elementName\";s:40:\"civicrm_rules_condition_mailing_rejected\";s:9:\"\0*\0negate\";b:0;}}s:7:\"\0*\0info\";a:0:{}s:9:\"\0*\0negate\";b:0;}s:9:\"\0*\0events\";a:1:{i:0;s:16:\"mailing_approved\";}}',0,0);
/*!40000 ALTER TABLE `rules_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rules_dependencies`
--

DROP TABLE IF EXISTS `rules_dependencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rules_dependencies` (
  `id` int(10) unsigned NOT NULL COMMENT 'The primary identifier of the configuration.',
  `module` varchar(255) NOT NULL COMMENT 'The name of the module that is required for the configuration.',
  PRIMARY KEY (`id`,`module`),
  KEY `module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rules_dependencies`
--

LOCK TABLES `rules_dependencies` WRITE;
/*!40000 ALTER TABLE `rules_dependencies` DISABLE KEYS */;
INSERT INTO `rules_dependencies` VALUES (1,'civicrm'),(2,'civicrm'),(1,'civicrm_rules'),(2,'civicrm_rules');
/*!40000 ALTER TABLE `rules_dependencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rules_tags`
--

DROP TABLE IF EXISTS `rules_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rules_tags` (
  `id` int(10) unsigned NOT NULL COMMENT 'The primary identifier of the configuration.',
  `tag` varchar(255) NOT NULL COMMENT 'The tag string associated with this configuration',
  PRIMARY KEY (`id`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rules_tags`
--

LOCK TABLES `rules_tags` WRITE;
/*!40000 ALTER TABLE `rules_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `rules_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rules_trigger`
--

DROP TABLE IF EXISTS `rules_trigger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rules_trigger` (
  `id` int(10) unsigned NOT NULL COMMENT 'The primary identifier of the configuration.',
  `event` varchar(127) NOT NULL DEFAULT '' COMMENT 'The name of the event on which the configuration should be triggered.',
  PRIMARY KEY (`id`,`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rules_trigger`
--

LOCK TABLES `rules_trigger` WRITE;
/*!40000 ALTER TABLE `rules_trigger` DISABLE KEYS */;
INSERT INTO `rules_trigger` VALUES (1,'mailing_approved'),(2,'mailing_approved');
/*!40000 ALTER TABLE `rules_trigger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_dataset`
--

DROP TABLE IF EXISTS `search_dataset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_dataset` (
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(16) NOT NULL COMMENT 'Type of item, e.g. node.',
  `data` longtext NOT NULL,
  `reindex` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_dataset`
--

LOCK TABLES `search_dataset` WRITE;
/*!40000 ALTER TABLE `search_dataset` DISABLE KEYS */;
INSERT INTO `search_dataset` VALUES (1,'node',' please login ',0),(2,'node',' page not found the page you are trying to reach does not exist please check and make sure you have the correct url click here to return to the bluebird dashboard if you feel this page was received in error please copy the url from your browser s address bar and email with additional details to your technical support staff  ',0),(3,'node',' please login ',0);
/*!40000 ALTER TABLE `search_dataset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_index`
--

DROP TABLE IF EXISTS `search_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_index` (
  `word` varchar(50) NOT NULL DEFAULT '',
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(16) NOT NULL COMMENT 'The search_dataset.type of the searchable item to which the word belongs.',
  `score` float DEFAULT NULL,
  PRIMARY KEY (`word`,`sid`,`type`),
  KEY `sid_type` (`sid`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_index`
--

LOCK TABLES `search_index` WRITE;
/*!40000 ALTER TABLE `search_index` DISABLE KEYS */;
INSERT INTO `search_index` VALUES ('additional',2,'node',1),('address',2,'node',1),('and',2,'node',2),('are',2,'node',1),('bar',2,'node',1),('bluebird',2,'node',1),('browser',2,'node',1),('check',2,'node',1),('click',2,'node',14),('copy',2,'node',1),('correct',2,'node',1),('dashboard',2,'node',1),('details',2,'node',1),('does',2,'node',1),('email',2,'node',1),('error',2,'node',1),('exist',2,'node',1),('feel',2,'node',1),('found',2,'node',26),('from',2,'node',1),('have',2,'node',1),('here',2,'node',14),('login',1,'node',26),('login',3,'node',26),('make',2,'node',1),('not',2,'node',27),('page',2,'node',28),('please',1,'node',26),('please',2,'node',2),('please',3,'node',26),('reach',2,'node',1),('received',2,'node',1),('return',2,'node',1),('staff',2,'node',1),('support',2,'node',1),('sure',2,'node',1),('technical',2,'node',1),('the',2,'node',4),('this',2,'node',1),('trying',2,'node',1),('url',2,'node',2),('was',2,'node',1),('with',2,'node',1),('you',2,'node',3),('your',2,'node',2);
/*!40000 ALTER TABLE `search_index` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_node_links`
--

DROP TABLE IF EXISTS `search_node_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_node_links` (
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(16) NOT NULL DEFAULT '',
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `caption` longtext,
  PRIMARY KEY (`sid`,`type`,`nid`),
  KEY `nid` (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_node_links`
--

LOCK TABLES `search_node_links` WRITE;
/*!40000 ALTER TABLE `search_node_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_node_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_total`
--

DROP TABLE IF EXISTS `search_total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_total` (
  `word` varchar(50) NOT NULL DEFAULT '',
  `count` float DEFAULT NULL,
  PRIMARY KEY (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_total`
--

LOCK TABLES `search_total` WRITE;
/*!40000 ALTER TABLE `search_total` DISABLE KEYS */;
INSERT INTO `search_total` VALUES ('additional',0.30103),('address',0.30103),('and',0.176091),('are',0.30103),('bar',0.30103),('bluebird',0.30103),('browser',0.30103),('check',0.30103),('click',0.0299632),('copy',0.30103),('correct',0.30103),('dashboard',0.30103),('details',0.30103),('does',0.30103),('email',0.30103),('error',0.30103),('exist',0.30103),('feel',0.30103),('found',0.0163904),('from',0.30103),('have',0.30103),('here',0.0299632),('login',0.00827253),('make',0.30103),('not',0.0157943),('page',0.01524),('please',0.00796893),('reach',0.30103),('received',0.30103),('return',0.30103),('staff',0.30103),('support',0.30103),('sure',0.30103),('technical',0.30103),('the',0.09691),('this',0.30103),('trying',0.30103),('url',0.176091),('was',0.30103),('with',0.30103),('you',0.124939),('your',0.176091);
/*!40000 ALTER TABLE `search_total` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semaphore`
--

DROP TABLE IF EXISTS `semaphore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `semaphore` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  `expire` double NOT NULL,
  PRIMARY KEY (`name`),
  KEY `expire` (`expire`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semaphore`
--

LOCK TABLES `semaphore` WRITE;
/*!40000 ALTER TABLE `semaphore` DISABLE KEYS */;
/*!40000 ALTER TABLE `semaphore` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sequences`
--

DROP TABLE IF EXISTS `sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sequences` (
  `value` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The value of the sequence.',
  PRIMARY KEY (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Stores IDs.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sequences`
--

LOCK TABLES `sequences` WRITE;
/*!40000 ALTER TABLE `sequences` DISABLE KEYS */;
INSERT INTO `sequences` VALUES (2);
/*!40000 ALTER TABLE `sequences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `uid` int(10) unsigned NOT NULL,
  `sid` varchar(128) NOT NULL COMMENT 'A session ID. The value is generated by Drupal’s session handlers.',
  `hostname` varchar(128) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `cache` int(11) NOT NULL DEFAULT '0',
  `session` longblob COMMENT 'The serialized contents of $_SESSION, an array of name/value pairs that persists across page requests by this session ID. Drupal loads $_SESSION from here at the start of each request and saves it at the end.',
  `ssid` varchar(128) NOT NULL DEFAULT '' COMMENT 'Secure session ID. The value is generated by Drupal’s session handlers.',
  PRIMARY KEY (`sid`,`ssid`),
  KEY `timestamp` (`timestamp`),
  KEY `uid` (`uid`),
  KEY `ssid` (`ssid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system` (
  `filename` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(12) NOT NULL DEFAULT '',
  `owner` varchar(255) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  `bootstrap` int(11) NOT NULL DEFAULT '0',
  `schema_version` smallint(6) NOT NULL DEFAULT '-1',
  `weight` int(11) NOT NULL DEFAULT '0',
  `info` blob COMMENT 'A serialized array containing information from the module’s .info file; keys can include name, description, package, version, core, dependencies, and php.',
  PRIMARY KEY (`filename`),
  KEY `type_name` (`type`,`name`),
  KEY `system_list` (`status`,`bootstrap`,`type`,`weight`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES ('modules/aggregator/aggregator.module','aggregator','module','',0,0,-1,0,'a:13:{s:4:\"name\";s:10:\"Aggregator\";s:11:\"description\";s:57:\"Aggregates syndicated content (RSS, RDF, and Atom feeds).\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:15:\"aggregator.test\";}s:9:\"configure\";s:41:\"admin/config/services/aggregator/settings\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:14:\"aggregator.css\";s:33:\"modules/aggregator/aggregator.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/aggregator/tests/aggregator_test.module','aggregator_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:23:\"Aggregator module tests\";s:11:\"description\";s:46:\"Support module for aggregator related testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/block/block.module','block','module','',1,0,7008,-5,'a:12:{s:4:\"name\";s:5:\"Block\";s:11:\"description\";s:140:\"Controls the visual building blocks a page is constructed with. Blocks are boxes of content rendered into an area, or region, of a web page.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:10:\"block.test\";}s:9:\"configure\";s:21:\"admin/structure/block\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/block/tests/block_test.module','block_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:10:\"Block test\";s:11:\"description\";s:21:\"Provides test blocks.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/blog/blog.module','blog','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:4:\"Blog\";s:11:\"description\";s:25:\"Enables multi-user blogs.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:9:\"blog.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/book/book.module','book','module','',0,0,-1,0,'a:13:{s:4:\"name\";s:4:\"Book\";s:11:\"description\";s:66:\"Allows users to create and organize related content in an outline.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:9:\"book.test\";}s:9:\"configure\";s:27:\"admin/content/book/settings\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:8:\"book.css\";s:21:\"modules/book/book.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/color/color.module','color','module','',0,0,7001,0,'a:11:{s:4:\"name\";s:5:\"Color\";s:11:\"description\";s:70:\"Allows administrators to change the color scheme of compatible themes.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:10:\"color.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/comment/comment.module','comment','module','',0,0,7009,0,'a:13:{s:4:\"name\";s:7:\"Comment\";s:11:\"description\";s:57:\"Allows users to comment on and discuss published content.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:4:\"text\";}s:5:\"files\";a:2:{i:0;s:14:\"comment.module\";i:1;s:12:\"comment.test\";}s:9:\"configure\";s:21:\"admin/content/comment\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:11:\"comment.css\";s:27:\"modules/comment/comment.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/contact/contact.module','contact','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:7:\"Contact\";s:11:\"description\";s:61:\"Enables the use of both personal and site-wide contact forms.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:12:\"contact.test\";}s:9:\"configure\";s:23:\"admin/structure/contact\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/contextual/contextual.module','contextual','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:16:\"Contextual links\";s:11:\"description\";s:75:\"Provides contextual links to perform actions related to elements on a page.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:15:\"contextual.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/dashboard/dashboard.module','dashboard','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:9:\"Dashboard\";s:11:\"description\";s:136:\"Provides a dashboard page in the administrative interface for organizing administrative tasks and tracking information within your site.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:5:\"files\";a:1:{i:0;s:14:\"dashboard.test\";}s:12:\"dependencies\";a:1:{i:0;s:5:\"block\";}s:9:\"configure\";s:25:\"admin/dashboard/customize\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/dblog/dblog.module','dblog','module','',0,0,7001,0,'a:11:{s:4:\"name\";s:16:\"Database logging\";s:11:\"description\";s:47:\"Logs and records system events to the database.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:10:\"dblog.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/field/field.module','field','module','',1,0,7002,0,'a:13:{s:4:\"name\";s:5:\"Field\";s:11:\"description\";s:57:\"Field API to add fields to entities like nodes and users.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:3:{i:0;s:12:\"field.module\";i:1;s:16:\"field.attach.inc\";i:2;s:16:\"tests/field.test\";}s:12:\"dependencies\";a:1:{i:0;s:17:\"field_sql_storage\";}s:8:\"required\";b:1;s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:15:\"theme/field.css\";s:29:\"modules/field/theme/field.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/field/modules/field_sql_storage/field_sql_storage.module','field_sql_storage','module','',1,0,7002,0,'a:12:{s:4:\"name\";s:17:\"Field SQL storage\";s:11:\"description\";s:37:\"Stores field data in an SQL database.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:5:\"field\";}s:5:\"files\";a:1:{i:0;s:22:\"field_sql_storage.test\";}s:8:\"required\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/field/modules/list/list.module','list','module','',1,0,7002,0,'a:11:{s:4:\"name\";s:4:\"List\";s:11:\"description\";s:69:\"Defines list field types. Use with Options to create selection lists.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:2:{i:0;s:5:\"field\";i:1;s:7:\"options\";}s:5:\"files\";a:1:{i:0;s:15:\"tests/list.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/field/modules/list/tests/list_test.module','list_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:9:\"List test\";s:11:\"description\";s:41:\"Support module for the List module tests.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/field/modules/number/number.module','number','module','',1,0,0,0,'a:11:{s:4:\"name\";s:6:\"Number\";s:11:\"description\";s:28:\"Defines numeric field types.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:5:\"field\";}s:5:\"files\";a:1:{i:0;s:11:\"number.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/field/modules/options/options.module','options','module','',1,0,0,0,'a:11:{s:4:\"name\";s:7:\"Options\";s:11:\"description\";s:82:\"Defines selection, check box and radio button widgets for text and numeric fields.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:5:\"field\";}s:5:\"files\";a:1:{i:0;s:12:\"options.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/field/modules/text/text.module','text','module','',1,0,7000,0,'a:13:{s:4:\"name\";s:4:\"Text\";s:11:\"description\";s:32:\"Defines simple text field types.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:5:\"field\";}s:5:\"files\";a:1:{i:0;s:9:\"text.test\";}s:8:\"required\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;s:11:\"explanation\";s:73:\"Field type(s) in use - see <a href=\"/admin/reports/fields\">Field list</a>\";}'),('modules/field/tests/field_test.module','field_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:14:\"Field API Test\";s:11:\"description\";s:39:\"Support module for the Field API tests.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:7:\"Testing\";s:5:\"files\";a:1:{i:0;s:21:\"field_test.entity.inc\";}s:7:\"version\";s:4:\"7.15\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/field_ui/field_ui.module','field_ui','module','',1,0,0,0,'a:11:{s:4:\"name\";s:8:\"Field UI\";s:11:\"description\";s:33:\"User interface for the Field API.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:5:\"field\";}s:5:\"files\";a:1:{i:0;s:13:\"field_ui.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/file/file.module','file','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:4:\"File\";s:11:\"description\";s:26:\"Defines a file field type.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:5:\"field\";}s:5:\"files\";a:1:{i:0;s:15:\"tests/file.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/file/tests/file_module_test.module','file_module_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:9:\"File test\";s:11:\"description\";s:53:\"Provides hooks for testing File module functionality.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/filter/filter.module','filter','module','',1,0,7010,0,'a:13:{s:4:\"name\";s:6:\"Filter\";s:11:\"description\";s:43:\"Filters content in preparation for display.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:11:\"filter.test\";}s:8:\"required\";b:1;s:9:\"configure\";s:28:\"admin/config/content/formats\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/forum/forum.module','forum','module','',0,0,-1,0,'a:13:{s:4:\"name\";s:5:\"Forum\";s:11:\"description\";s:27:\"Provides discussion forums.\";s:12:\"dependencies\";a:2:{i:0;s:8:\"taxonomy\";i:1;s:7:\"comment\";}s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:10:\"forum.test\";}s:9:\"configure\";s:21:\"admin/structure/forum\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"forum.css\";s:23:\"modules/forum/forum.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/help/help.module','help','module','',0,0,0,0,'a:11:{s:4:\"name\";s:4:\"Help\";s:11:\"description\";s:35:\"Manages the display of online help.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:9:\"help.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/image/image.module','image','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:5:\"Image\";s:11:\"description\";s:34:\"Provides image manipulation tools.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:4:\"file\";}s:5:\"files\";a:1:{i:0;s:10:\"image.test\";}s:9:\"configure\";s:31:\"admin/config/media/image-styles\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/image/tests/image_module_test.module','image_module_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:10:\"Image test\";s:11:\"description\";s:69:\"Provides hook implementations for testing Image module functionality.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:24:\"image_module_test.module\";}s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/locale/locale.module','locale','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:6:\"Locale\";s:11:\"description\";s:119:\"Adds language handling functionality and enables the translation of the user interface to languages other than English.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:11:\"locale.test\";}s:9:\"configure\";s:30:\"admin/config/regional/language\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/locale/tests/locale_test.module','locale_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:11:\"Locale Test\";s:11:\"description\";s:42:\"Support module for the locale layer tests.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/menu/menu.module','menu','module','',1,0,7003,0,'a:12:{s:4:\"name\";s:4:\"Menu\";s:11:\"description\";s:60:\"Allows administrators to customize the site navigation menu.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:9:\"menu.test\";}s:9:\"configure\";s:20:\"admin/structure/menu\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/node/node.module','node','module','',1,0,7013,0,'a:14:{s:4:\"name\";s:4:\"Node\";s:11:\"description\";s:66:\"Allows content to be submitted to the site and displayed on pages.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:2:{i:0;s:11:\"node.module\";i:1;s:9:\"node.test\";}s:8:\"required\";b:1;s:9:\"configure\";s:21:\"admin/structure/types\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:8:\"node.css\";s:21:\"modules/node/node.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/node/tests/node_access_test.module','node_access_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:24:\"Node module access tests\";s:11:\"description\";s:43:\"Support module for node permission testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/node/tests/node_test.module','node_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:17:\"Node module tests\";s:11:\"description\";s:40:\"Support module for node related testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/node/tests/node_test_exception.module','node_test_exception','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:27:\"Node module exception tests\";s:11:\"description\";s:50:\"Support module for node related exception testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/openid/openid.module','openid','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:6:\"OpenID\";s:11:\"description\";s:48:\"Allows users to log into your site using OpenID.\";s:7:\"version\";s:4:\"7.15\";s:7:\"package\";s:4:\"Core\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:11:\"openid.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/openid/tests/openid_test.module','openid_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:21:\"OpenID dummy provider\";s:11:\"description\";s:33:\"OpenID provider used for testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:6:\"openid\";}s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/overlay/overlay.module','overlay','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:7:\"Overlay\";s:11:\"description\";s:59:\"Displays the Drupal administration interface in an overlay.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/path/path.module','path','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:4:\"Path\";s:11:\"description\";s:28:\"Allows users to rename URLs.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:9:\"path.test\";}s:9:\"configure\";s:24:\"admin/config/search/path\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/php/php.module','php','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:10:\"PHP filter\";s:11:\"description\";s:50:\"Allows embedded PHP code/snippets to be evaluated.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:8:\"php.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/poll/poll.module','poll','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:4:\"Poll\";s:11:\"description\";s:95:\"Allows your site to capture votes on different topics in the form of multiple choice questions.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:9:\"poll.test\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:8:\"poll.css\";s:21:\"modules/poll/poll.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/profile/profile.module','profile','module','',0,0,-1,0,'a:13:{s:4:\"name\";s:7:\"Profile\";s:11:\"description\";s:36:\"Supports configurable user profiles.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:12:\"profile.test\";}s:9:\"configure\";s:27:\"admin/config/people/profile\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/rdf/rdf.module','rdf','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:3:\"RDF\";s:11:\"description\";s:148:\"Enriches your content with metadata to let other applications (e.g. search engines, aggregators) better understand its relationships and attributes.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:8:\"rdf.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/rdf/tests/rdf_test.module','rdf_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:16:\"RDF module tests\";s:11:\"description\";s:38:\"Support module for RDF module testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/search/search.module','search','module','',1,0,7000,0,'a:13:{s:4:\"name\";s:6:\"Search\";s:11:\"description\";s:36:\"Enables site-wide keyword searching.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:2:{i:0;s:19:\"search.extender.inc\";i:1;s:11:\"search.test\";}s:9:\"configure\";s:28:\"admin/config/search/settings\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:10:\"search.css\";s:25:\"modules/search/search.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/search/tests/search_embedded_form.module','search_embedded_form','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:20:\"Search embedded form\";s:11:\"description\";s:59:\"Support module for search module testing of embedded forms.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/search/tests/search_extra_type.module','search_extra_type','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:16:\"Test search type\";s:11:\"description\";s:41:\"Support module for search module testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/shortcut/shortcut.module','shortcut','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:8:\"Shortcut\";s:11:\"description\";s:60:\"Allows users to manage customizable lists of shortcut links.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:13:\"shortcut.test\";}s:9:\"configure\";s:36:\"admin/config/user-interface/shortcut\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/simpletest/simpletest.module','simpletest','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:7:\"Testing\";s:11:\"description\";s:53:\"Provides a framework for unit and functional testing.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:47:{i:0;s:15:\"simpletest.test\";i:1;s:24:\"drupal_web_test_case.php\";i:2;s:18:\"tests/actions.test\";i:3;s:15:\"tests/ajax.test\";i:4;s:16:\"tests/batch.test\";i:5;s:20:\"tests/bootstrap.test\";i:6;s:16:\"tests/cache.test\";i:7;s:17:\"tests/common.test\";i:8;s:24:\"tests/database_test.test\";i:9;s:32:\"tests/entity_crud_hook_test.test\";i:10;s:23:\"tests/entity_query.test\";i:11;s:16:\"tests/error.test\";i:12;s:15:\"tests/file.test\";i:13;s:23:\"tests/filetransfer.test\";i:14;s:15:\"tests/form.test\";i:15;s:16:\"tests/graph.test\";i:16;s:16:\"tests/image.test\";i:17;s:15:\"tests/lock.test\";i:18;s:15:\"tests/mail.test\";i:19;s:15:\"tests/menu.test\";i:20;s:17:\"tests/module.test\";i:21;s:16:\"tests/pager.test\";i:22;s:19:\"tests/password.test\";i:23;s:15:\"tests/path.test\";i:24;s:19:\"tests/registry.test\";i:25;s:17:\"tests/schema.test\";i:26;s:18:\"tests/session.test\";i:27;s:20:\"tests/tablesort.test\";i:28;s:16:\"tests/theme.test\";i:29;s:18:\"tests/unicode.test\";i:30;s:17:\"tests/update.test\";i:31;s:17:\"tests/xmlrpc.test\";i:32;s:26:\"tests/upgrade/upgrade.test\";i:33;s:34:\"tests/upgrade/upgrade.comment.test\";i:34;s:31:\"tests/upgrade/update.field.test\";i:35;s:33:\"tests/upgrade/upgrade.filter.test\";i:36;s:32:\"tests/upgrade/upgrade.forum.test\";i:37;s:33:\"tests/upgrade/upgrade.locale.test\";i:38;s:31:\"tests/upgrade/upgrade.menu.test\";i:39;s:31:\"tests/upgrade/upgrade.node.test\";i:40;s:35:\"tests/upgrade/upgrade.taxonomy.test\";i:41;s:34:\"tests/upgrade/upgrade.trigger.test\";i:42;s:39:\"tests/upgrade/upgrade.translatable.test\";i:43;s:33:\"tests/upgrade/update.trigger.test\";i:44;s:33:\"tests/upgrade/upgrade.upload.test\";i:45;s:30:\"tests/upgrade/update.user.test\";i:46;s:31:\"tests/upgrade/upgrade.user.test\";}s:9:\"configure\";s:41:\"admin/config/development/testing/settings\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/actions_loop_test.module','actions_loop_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:17:\"Actions loop test\";s:11:\"description\";s:39:\"Support module for action loop testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/ajax_forms_test.module','ajax_forms_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:26:\"AJAX form test mock module\";s:11:\"description\";s:25:\"Test for AJAX form calls.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/ajax_test.module','ajax_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:9:\"AJAX Test\";s:11:\"description\";s:40:\"Support module for AJAX framework tests.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/batch_test.module','batch_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:14:\"Batch API test\";s:11:\"description\";s:35:\"Support module for Batch API tests.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/common_test.module','common_test','module','',0,0,-1,0,'a:13:{s:4:\"name\";s:11:\"Common Test\";s:11:\"description\";s:32:\"Support module for Common tests.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:15:\"common_test.css\";s:40:\"modules/simpletest/tests/common_test.css\";}s:5:\"print\";a:1:{s:21:\"common_test.print.css\";s:46:\"modules/simpletest/tests/common_test.print.css\";}}s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/common_test_cron_helper.module','common_test_cron_helper','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:23:\"Common Test Cron Helper\";s:11:\"description\";s:56:\"Helper module for CronRunTestCase::testCronExceptions().\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/database_test.module','database_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:13:\"Database Test\";s:11:\"description\";s:40:\"Support module for Database layer tests.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/drupal_system_listing_compatible_test/drupal_system_listing_compatible_test.module','drupal_system_listing_compatible_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:37:\"Drupal system listing compatible test\";s:11:\"description\";s:62:\"Support module for testing the drupal_system_listing function.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/drupal_system_listing_incompatible_test/drupal_system_listing_incompatible_test.module','drupal_system_listing_incompatible_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:39:\"Drupal system listing incompatible test\";s:11:\"description\";s:62:\"Support module for testing the drupal_system_listing function.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/entity_cache_test.module','entity_cache_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:17:\"Entity cache test\";s:11:\"description\";s:40:\"Support module for testing entity cache.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:28:\"entity_cache_test_dependency\";}s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/entity_cache_test_dependency.module','entity_cache_test_dependency','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:28:\"Entity cache test dependency\";s:11:\"description\";s:51:\"Support dependency module for testing entity cache.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/entity_crud_hook_test.module','entity_crud_hook_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:22:\"Entity CRUD Hooks Test\";s:11:\"description\";s:35:\"Support module for CRUD hook tests.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/entity_query_access_test.module','entity_query_access_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:24:\"Entity query access test\";s:11:\"description\";s:49:\"Support module for checking entity query results.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/error_test.module','error_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:10:\"Error test\";s:11:\"description\";s:47:\"Support module for error and exception testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/file_test.module','file_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:9:\"File test\";s:11:\"description\";s:39:\"Support module for file handling tests.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:16:\"file_test.module\";}s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/filter_test.module','filter_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:18:\"Filter test module\";s:11:\"description\";s:33:\"Tests filter hooks and functions.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/form_test.module','form_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:12:\"FormAPI Test\";s:11:\"description\";s:34:\"Support module for Form API tests.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/image_test.module','image_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:10:\"Image test\";s:11:\"description\";s:39:\"Support module for image toolkit tests.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/menu_test.module','menu_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:15:\"Hook menu tests\";s:11:\"description\";s:37:\"Support module for menu hook testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/module_test.module','module_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:11:\"Module test\";s:11:\"description\";s:41:\"Support module for module system testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/path_test.module','path_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:15:\"Hook path tests\";s:11:\"description\";s:37:\"Support module for path hook testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/requirements1_test.module','requirements1_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:19:\"Requirements 1 Test\";s:11:\"description\";s:80:\"Tests that a module is not installed when it fails hook_requirements(\'install\').\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/requirements2_test.module','requirements2_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:19:\"Requirements 2 Test\";s:11:\"description\";s:98:\"Tests that a module is not installed when the one it depends on fails hook_requirements(\'install).\";s:12:\"dependencies\";a:2:{i:0;s:18:\"requirements1_test\";i:1;s:7:\"comment\";}s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/session_test.module','session_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:12:\"Session test\";s:11:\"description\";s:40:\"Support module for session data testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/system_dependencies_test.module','system_dependencies_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:22:\"System dependency test\";s:11:\"description\";s:47:\"Support module for testing system dependencies.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:12:\"dependencies\";a:1:{i:0;s:19:\"_missing_dependency\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/system_incompatible_core_version_dependencies_test.module','system_incompatible_core_version_dependencies_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:50:\"System incompatible core version dependencies test\";s:11:\"description\";s:47:\"Support module for testing system dependencies.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:12:\"dependencies\";a:1:{i:0;s:37:\"system_incompatible_core_version_test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/system_incompatible_core_version_test.module','system_incompatible_core_version_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:37:\"System incompatible core version test\";s:11:\"description\";s:47:\"Support module for testing system dependencies.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"5.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/system_incompatible_module_version_dependencies_test.module','system_incompatible_module_version_dependencies_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:52:\"System incompatible module version dependencies test\";s:11:\"description\";s:47:\"Support module for testing system dependencies.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:12:\"dependencies\";a:1:{i:0;s:46:\"system_incompatible_module_version_test (>2.0)\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/system_incompatible_module_version_test.module','system_incompatible_module_version_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:39:\"System incompatible module version test\";s:11:\"description\";s:47:\"Support module for testing system dependencies.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/system_test.module','system_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:11:\"System test\";s:11:\"description\";s:34:\"Support module for system testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:18:\"system_test.module\";}s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/taxonomy_test.module','taxonomy_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:20:\"Taxonomy test module\";s:11:\"description\";s:45:\"\"Tests functions and hooks not used in core\".\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:12:\"dependencies\";a:1:{i:0;s:8:\"taxonomy\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/theme_test.module','theme_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:10:\"Theme test\";s:11:\"description\";s:40:\"Support module for theme system testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/update_script_test.module','update_script_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:18:\"Update script test\";s:11:\"description\";s:41:\"Support module for update script testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/update_test_1.module','update_test_1','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:11:\"Update test\";s:11:\"description\";s:34:\"Support module for update testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/update_test_2.module','update_test_2','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:11:\"Update test\";s:11:\"description\";s:34:\"Support module for update testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/update_test_3.module','update_test_3','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:11:\"Update test\";s:11:\"description\";s:34:\"Support module for update testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/url_alter_test.module','url_alter_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:15:\"Url_alter tests\";s:11:\"description\";s:45:\"A support modules for url_alter hook testing.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/simpletest/tests/xmlrpc_test.module','xmlrpc_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:12:\"XML-RPC Test\";s:11:\"description\";s:75:\"Support module for XML-RPC tests according to the validator1 specification.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/statistics/statistics.module','statistics','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:10:\"Statistics\";s:11:\"description\";s:37:\"Logs access statistics for your site.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:15:\"statistics.test\";}s:9:\"configure\";s:30:\"admin/config/system/statistics\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/syslog/syslog.module','syslog','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:6:\"Syslog\";s:11:\"description\";s:41:\"Logs and records system events to syslog.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:11:\"syslog.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/system/system.module','system','module','',1,1,7074,0,'a:13:{s:4:\"name\";s:6:\"System\";s:11:\"description\";s:54:\"Handles general site configuration for administrators.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:6:{i:0;s:19:\"system.archiver.inc\";i:1;s:15:\"system.mail.inc\";i:2;s:16:\"system.queue.inc\";i:3;s:14:\"system.tar.inc\";i:4;s:18:\"system.updater.inc\";i:5;s:11:\"system.test\";}s:8:\"required\";b:1;s:9:\"configure\";s:19:\"admin/config/system\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/taxonomy/taxonomy.module','taxonomy','module','',0,0,7010,0,'a:12:{s:4:\"name\";s:8:\"Taxonomy\";s:11:\"description\";s:38:\"Enables the categorization of content.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:7:\"options\";}s:5:\"files\";a:2:{i:0;s:15:\"taxonomy.module\";i:1;s:13:\"taxonomy.test\";}s:9:\"configure\";s:24:\"admin/structure/taxonomy\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/toolbar/toolbar.module','toolbar','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:7:\"Toolbar\";s:11:\"description\";s:99:\"Provides a toolbar that shows the top-level administration menu items and links from other modules.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/tracker/tracker.module','tracker','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:7:\"Tracker\";s:11:\"description\";s:45:\"Enables tracking of recent content for users.\";s:12:\"dependencies\";a:1:{i:0;s:7:\"comment\";}s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:12:\"tracker.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/translation/tests/translation_test.module','translation_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:24:\"Content Translation Test\";s:11:\"description\";s:49:\"Support module for the content translation tests.\";s:4:\"core\";s:3:\"7.x\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/translation/translation.module','translation','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:19:\"Content translation\";s:11:\"description\";s:57:\"Allows content to be translated into different languages.\";s:12:\"dependencies\";a:1:{i:0;s:6:\"locale\";}s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:16:\"translation.test\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/trigger/tests/trigger_test.module','trigger_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:12:\"Trigger Test\";s:11:\"description\";s:33:\"Support module for Trigger tests.\";s:7:\"package\";s:7:\"Testing\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"version\";s:4:\"7.15\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/trigger/trigger.module','trigger','module','',1,0,7002,0,'a:12:{s:4:\"name\";s:7:\"Trigger\";s:11:\"description\";s:90:\"Enables actions to be fired on certain system events, such as when new content is created.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:12:\"trigger.test\";}s:9:\"configure\";s:23:\"admin/structure/trigger\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/update/tests/aaa_update_test.module','aaa_update_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:15:\"AAA Update test\";s:11:\"description\";s:41:\"Support module for update module testing.\";s:7:\"package\";s:7:\"Testing\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"version\";s:4:\"7.15\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/update/tests/bbb_update_test.module','bbb_update_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:15:\"BBB Update test\";s:11:\"description\";s:41:\"Support module for update module testing.\";s:7:\"package\";s:7:\"Testing\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"version\";s:4:\"7.15\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/update/tests/ccc_update_test.module','ccc_update_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:15:\"CCC Update test\";s:11:\"description\";s:41:\"Support module for update module testing.\";s:7:\"package\";s:7:\"Testing\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"version\";s:4:\"7.15\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/update/tests/update_test.module','update_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:11:\"Update test\";s:11:\"description\";s:41:\"Support module for update module testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/update/update.module','update','module','',0,0,7001,0,'a:12:{s:4:\"name\";s:14:\"Update manager\";s:11:\"description\";s:104:\"Checks for available updates, and can securely install or update modules and themes via a web interface.\";s:7:\"version\";s:4:\"7.15\";s:7:\"package\";s:4:\"Core\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:11:\"update.test\";}s:9:\"configure\";s:30:\"admin/reports/updates/settings\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('modules/user/tests/user_form_test.module','user_form_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:22:\"User module form tests\";s:11:\"description\";s:37:\"Support module for user form testing.\";s:7:\"package\";s:7:\"Testing\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('modules/user/user.module','user','module','',1,0,7018,0,'a:14:{s:4:\"name\";s:4:\"User\";s:11:\"description\";s:47:\"Manages the user registration and login system.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:2:{i:0;s:11:\"user.module\";i:1;s:9:\"user.test\";}s:8:\"required\";b:1;s:9:\"configure\";s:19:\"admin/config/people\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:8:\"user.css\";s:21:\"modules/user/user.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('profiles/default/default.profile','default','module','',0,0,0,1000,'a:6:{s:12:\"dependencies\";a:0:{}s:11:\"description\";s:0:\"\";s:7:\"package\";s:5:\"Other\";s:7:\"version\";N;s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}}'),('profiles/standard/standard.profile','standard','module','',0,0,-1,1000,'a:14:{s:4:\"name\";s:8:\"Standard\";s:11:\"description\";s:51:\"Install with commonly used features pre-configured.\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:21:{i:0;s:5:\"block\";i:1;s:5:\"color\";i:2;s:7:\"comment\";i:3;s:10:\"contextual\";i:4;s:9:\"dashboard\";i:5;s:4:\"help\";i:6;s:5:\"image\";i:7;s:4:\"list\";i:8;s:4:\"menu\";i:9;s:6:\"number\";i:10;s:7:\"options\";i:11;s:4:\"path\";i:12;s:8:\"taxonomy\";i:13;s:5:\"dblog\";i:14;s:6:\"search\";i:15;s:8:\"shortcut\";i:16;s:7:\"toolbar\";i:17;s:7:\"overlay\";i:18;s:8:\"field_ui\";i:19;s:4:\"file\";i:20;s:3:\"rdf\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:7:\"package\";s:5:\"Other\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;s:6:\"hidden\";b:1;s:8:\"required\";b:1;s:17:\"distribution_name\";s:6:\"Drupal\";}'),('sites/all/modules/administerusersbyrole/administerusersbyrole.module','administerusersbyrole','module','',1,0,0,0,'a:11:{s:4:\"name\";s:24:\"Administer Users by Role\";s:11:\"description\";s:180:\"Allows users with \'administer users\' permission and a role (specified in \'Permissions\') to edit/delete other users with a specified role.  Also provides control over user creation.\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:26:\"administerusersbyrole.test\";}s:7:\"version\";s:13:\"7.x-1.0-beta1\";s:7:\"project\";s:21:\"administerusersbyrole\";s:9:\"datestamp\";s:10:\"1341660376\";s:12:\"dependencies\";a:0:{}s:7:\"package\";s:5:\"Other\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/admin_menu/admin_menu.module','admin_menu','module','',0,0,6001,0,'a:10:{s:4:\"name\";s:19:\"Administration menu\";s:11:\"description\";s:123:\"Provides a dropdown menu to most administrative tasks and other common destinations (to users with the proper permissions).\";s:7:\"package\";s:14:\"Administration\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:7:\"6.x-1.5\";s:7:\"project\";s:10:\"admin_menu\";s:9:\"datestamp\";s:10:\"1246537502\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/apachesolr/apachesolr.module','apachesolr','module','',1,0,7015,0,'a:12:{s:4:\"name\";s:21:\"Apache Solr framework\";s:11:\"description\";s:33:\"Framework for searching with Solr\";s:7:\"package\";s:14:\"Search Toolkit\";s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:39:\"admin/config/search/apachesolr/settings\";s:5:\"files\";a:18:{i:0;s:18:\"apachesolr.install\";i:1;s:17:\"apachesolr.module\";i:2;s:20:\"apachesolr.admin.inc\";i:3;s:20:\"apachesolr.index.inc\";i:4;s:24:\"apachesolr.interface.inc\";i:5;s:30:\"Drupal_Apache_Solr_Service.php\";i:6;s:24:\"Apache_Solr_Document.php\";i:7;s:19:\"Solr_Base_Query.php\";i:8;s:28:\"plugins/facetapi/adapter.inc\";i:9;s:36:\"plugins/facetapi/query_type_date.inc\";i:10;s:36:\"plugins/facetapi/query_type_term.inc\";i:11;s:45:\"plugins/facetapi/query_type_numeric_range.inc\";i:12;s:20:\"tests/Dummy_Solr.php\";i:13;s:26:\"tests/apachesolr_base.test\";i:14;s:32:\"tests/solr_index_and_search.test\";i:15;s:26:\"tests/solr_base_query.test\";i:16;s:29:\"tests/solr_base_subquery.test\";i:17;s:24:\"tests/solr_document.test\";}s:7:\"version\";s:11:\"7.x-1.0-rc2\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1340280372\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/apachesolr/apachesolr_access/apachesolr_access.module','apachesolr_access','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:18:\"Apache Solr Access\";s:11:\"description\";s:68:\"Integrates node access and other permissions with Apache Solr search\";s:12:\"dependencies\";a:1:{i:0;s:10:\"apachesolr\";}s:7:\"package\";s:14:\"Search Toolkit\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:2:{i:0;s:24:\"apachesolr_access.module\";i:1;s:28:\"tests/apachesolr_access.test\";}s:7:\"version\";s:11:\"7.x-1.0-rc2\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1340280372\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/apachesolr/apachesolr_search.module','apachesolr_search','module','',1,0,7005,0,'a:12:{s:4:\"name\";s:18:\"Apache Solr search\";s:11:\"description\";s:16:\"Search with Solr\";s:12:\"dependencies\";a:2:{i:0;s:6:\"search\";i:1;s:10:\"apachesolr\";}s:7:\"package\";s:14:\"Search Toolkit\";s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:43:\"admin/config/search/apachesolr/search-pages\";s:5:\"files\";a:4:{i:0;s:25:\"apachesolr_search.install\";i:1;s:24:\"apachesolr_search.module\";i:2;s:27:\"apachesolr_search.admin.inc\";i:3;s:27:\"apachesolr_search.pages.inc\";}s:7:\"version\";s:11:\"7.x-1.0-rc2\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1340280372\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/apachesolr/contrib/apachesolr_nodeaccess/apachesolr_nodeaccess.module','apachesolr_nodeaccess','module','',0,0,0,0,'a:10:{s:4:\"name\";s:23:\"Apache Solr node access\";s:11:\"description\";s:57:\"Integrates the node access system with Apache Solr search\";s:12:\"dependencies\";a:1:{i:0;s:10:\"apachesolr\";}s:7:\"package\";s:11:\"Apache Solr\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:7:\"6.x-1.5\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1306336914\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/apachesolr/tests/apachesolr_test/apachesolr_test.module','apachesolr_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:35:\"Apache Solr helper module for tests\";s:11:\"description\";s:45:\"Support module for apachesolr module testing.\";s:7:\"package\";s:14:\"Search Toolkit\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"version\";s:11:\"7.x-1.0-rc2\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1340280372\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/apc/apc.module','apc','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:21:\"Alternative PHP Cache\";s:11:\"description\";s:34:\"Enables the Alternative PHP Cache.\";s:7:\"package\";s:27:\"Performance and scalability\";s:7:\"version\";s:13:\"7.x-1.0-beta4\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:3:{i:0;s:10:\"apc.module\";i:1;s:20:\"drupal_apc_cache.inc\";i:2;s:14:\"tests/apc.test\";}s:7:\"project\";s:3:\"apc\";s:9:\"datestamp\";s:10:\"1335297971\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/cacherouter/cacherouter.module','cacherouter','module','',0,0,0,0,'a:10:{s:4:\"name\";s:11:\"CacheRouter\";s:11:\"description\";s:75:\"Controls access to split caching functionality into self contained objects.\";s:7:\"package\";s:27:\"Performance and scalability\";s:7:\"version\";s:11:\"6.x-1.0-rc2\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:11:\"cacherouter\";s:9:\"datestamp\";s:10:\"1304796714\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/civicrm/drupal/civicrm.module','civicrm','module','',1,0,7400,100,'a:9:{s:4:\"name\";s:7:\"CiviCRM\";s:11:\"description\";s:175:\"Constituent Relationship Management CRM - v4.2. Allows sites to manage contacts, relationships and groups, and track contact activities, contributions, memberships and events.\";s:7:\"version\";s:3:\"4.2\";s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:3:\"php\";s:3:\"5.2\";s:5:\"files\";a:65:{i:0;s:14:\"civicrm.module\";i:1;s:15:\"civicrm.install\";i:2;s:16:\"civicrm_user.inc\";i:3;s:31:\"modules/views/civicrm.views.inc\";i:4;s:39:\"modules/views/civicrm.views_default.inc\";i:5;s:47:\"modules/views/civicrm/civicrm_handler_field.inc\";i:6;s:63:\"modules/views/civicrm/civicrm_handler_field_pseudo_constant.inc\";i:7;s:55:\"modules/views/civicrm/civicrm_handler_field_address.inc\";i:8;s:60:\"modules/views/civicrm/civicrm_handler_field_contact_link.inc\";i:9;s:55:\"modules/views/civicrm/civicrm_handler_field_country.inc\";i:10;s:54:\"modules/views/civicrm/civicrm_handler_field_custom.inc\";i:11;s:56:\"modules/views/civicrm/civicrm_handler_field_datetime.inc\";i:12;s:56:\"modules/views/civicrm/civicrm_handler_field_drupalid.inc\";i:13;s:53:\"modules/views/civicrm/civicrm_handler_field_email.inc\";i:14;s:64:\"modules/views/civicrm/civicrm_handler_field_encounter_medium.inc\";i:15;s:53:\"modules/views/civicrm/civicrm_handler_field_event.inc\";i:16;s:58:\"modules/views/civicrm/civicrm_handler_field_event_link.inc\";i:17;s:63:\"modules/views/civicrm/civicrm_handler_field_event_price_set.inc\";i:18;s:52:\"modules/views/civicrm/civicrm_handler_field_file.inc\";i:19;s:52:\"modules/views/civicrm/civicrm_handler_field_link.inc\";i:20;s:61:\"modules/views/civicrm/civicrm_handler_field_link_activity.inc\";i:21;s:60:\"modules/views/civicrm/civicrm_handler_field_link_contact.inc\";i:22;s:65:\"modules/views/civicrm/civicrm_handler_field_link_contribution.inc\";i:23;s:59:\"modules/views/civicrm/civicrm_handler_field_link_delete.inc\";i:24;s:57:\"modules/views/civicrm/civicrm_handler_field_link_edit.inc\";i:25;s:58:\"modules/views/civicrm/civicrm_handler_field_link_event.inc\";i:26;s:64:\"modules/views/civicrm/civicrm_handler_field_link_participant.inc\";i:27;s:56:\"modules/views/civicrm/civicrm_handler_field_link_pcp.inc\";i:28;s:65:\"modules/views/civicrm/civicrm_handler_field_link_relationship.inc\";i:29;s:56:\"modules/views/civicrm/civicrm_handler_field_location.inc\";i:30;s:52:\"modules/views/civicrm/civicrm_handler_field_mail.inc\";i:31;s:54:\"modules/views/civicrm/civicrm_handler_field_markup.inc\";i:32;s:53:\"modules/views/civicrm/civicrm_handler_field_money.inc\";i:33;s:54:\"modules/views/civicrm/civicrm_handler_field_option.inc\";i:34;s:65:\"modules/views/civicrm/civicrm_handler_field_pcp_raised_amount.inc\";i:35;s:53:\"modules/views/civicrm/civicrm_handler_field_phone.inc\";i:36;s:54:\"modules/views/civicrm/civicrm_handler_field_prefix.inc\";i:37;s:65:\"modules/views/civicrm/civicrm_handler_field_relationship_type.inc\";i:38;s:53:\"modules/views/civicrm/civicrm_handler_field_state.inc\";i:39;s:54:\"modules/views/civicrm/civicrm_handler_field_suffix.inc\";i:40;s:64:\"modules/views/civicrm/civicrm_handler_filter_pseudo_constant.inc\";i:41;s:62:\"modules/views/civicrm/civicrm_handler_filter_custom_option.inc\";i:42;s:57:\"modules/views/civicrm/civicrm_handler_filter_datetime.inc\";i:43;s:65:\"modules/views/civicrm/civicrm_handler_filter_encounter_medium.inc\";i:44;s:61:\"modules/views/civicrm/civicrm_handler_filter_group_status.inc\";i:45;s:62:\"modules/views/civicrm/civicrm_handler_filter_custom_option.inc\";i:46;s:69:\"modules/views/civicrm/civicrm_handler_filter_custom_single_option.inc\";i:47;s:55:\"modules/views/civicrm/civicrm_handler_filter_prefix.inc\";i:48;s:66:\"modules/views/civicrm/civicrm_handler_filter_relationship_type.inc\";i:49;s:55:\"modules/views/civicrm/civicrm_handler_filter_suffix.inc\";i:50;s:62:\"modules/views/civicrm/civicrm_handler_filter_country_multi.inc\";i:51;s:55:\"modules/views/civicrm/civicrm_handler_filter_domain.inc\";i:52;s:51:\"modules/views/civicrm/civicrm_handler_sort_date.inc\";i:53;s:64:\"modules/views/civicrm/civicrm_handler_sort_pcp_raised_amount.inc\";i:54;s:57:\"modules/views/civicrm/civicrm_plugin_argument_default.inc\";i:55;s:60:\"modules/views/civicrm/views_handler_argument_civicrm_day.inc\";i:56;s:65:\"modules/views/civicrm/views_handler_argument_civicrm_fulldate.inc\";i:57;s:62:\"modules/views/civicrm/views_handler_argument_civicrm_month.inc\";i:58;s:61:\"modules/views/civicrm/views_handler_argument_civicrm_week.inc\";i:59;s:61:\"modules/views/civicrm/views_handler_argument_civicrm_year.inc\";i:60;s:67:\"modules/views/civicrm/views_handler_argument_civicrm_year_month.inc\";i:61;s:54:\"modules/views/civicrm/civicrm_handler_relationship.inc\";i:62;s:68:\"modules/views/civicrm/civicrm_handler_relationship_contact2users.inc\";i:63;s:53:\"modules/views/plugins/calendar_plugin_row_civicrm.inc\";i:64;s:59:\"modules/views/plugins/calendar_plugin_row_civicrm_event.inc\";}s:12:\"dependencies\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/civicrm/drupal/modules/civicrmtheme/civicrmtheme.module','civicrmtheme','module','',0,0,-1,0,'a:9:{s:4:\"name\";s:13:\"CiviCRM Theme\";s:11:\"description\";s:36:\"Define alternate themes for CiviCRM.\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:7:\"package\";s:7:\"CiviCRM\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:5:\"files\";a:2:{i:0;s:19:\"civicrmtheme.module\";i:1;s:20:\"civicrmtheme.install\";}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/civicrm/drupal/modules/civicrm_contact_ref/civicrm_contact_ref.module','civicrm_contact_ref','module','',0,0,-1,0,'a:9:{s:4:\"name\";s:31:\"CiviCRM Contact Reference Field\";s:11:\"description\";s:39:\"Makes a CiviCRM Contact Reference Field\";s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:3:\"5.2\";s:12:\"dependencies\";a:3:{i:0;s:7:\"civicrm\";i:1;s:4:\"text\";i:2;s:4:\"list\";}s:5:\"files\";a:3:{i:0;s:27:\"civicrm_contact_ref.install\";i:1;s:26:\"civicrm_contact_ref.module\";i:2;s:29:\"civicrm_contact_ref.feeds.inc\";}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/civicrm/drupal/modules/civicrm_engage/civicrm_engage.module','civicrm_engage','module','',0,0,7001,0,'a:9:{s:4:\"name\";s:10:\"CiviEngage\";s:11:\"description\";s:46:\"Walklist and Phone-banking support for CiviCRM\";s:7:\"version\";s:3:\"4.2\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:3:\"php\";s:3:\"5.2\";s:5:\"files\";a:1:{i:0;s:21:\"civicrm_engage.module\";}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/civicrm/drupal/modules/civicrm_group_roles/civicrm_group_roles.module','civicrm_group_roles','module','',0,0,7400,101,'a:10:{s:4:\"name\";s:20:\"CiviGroup Roles Sync\";s:11:\"description\";s:36:\"Sync Drupal Roles to CiviCRM Groups.\";s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:9:\"configure\";s:40:\"admin/config/civicrm/civicrm_group_roles\";s:5:\"files\";a:2:{i:0;s:26:\"civicrm_group_roles.module\";i:1;s:27:\"civicrm_group_roles.install\";}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/civicrm/drupal/modules/civicrm_member_roles/civicrm_member_roles.module','civicrm_member_roles','module','',0,0,0,0,'a:10:{s:4:\"name\";s:21:\"CiviMember Roles Sync\";s:11:\"description\";s:111:\"Synchronize CiviCRM Contacts with Membership Status to a specified Drupal Role both automatically and manually.\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:9:\"configure\";s:41:\"admin/config/civicrm/civicrm_member_roles\";s:5:\"files\";a:2:{i:0;s:27:\"civicrm_member_roles.module\";i:1;s:28:\"civicrm_member_roles.install\";}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/civicrm/drupal/modules/civicrm_og_sync/civicrm_og_sync.module','civicrm_og_sync','module','',0,0,-1,0,'a:9:{s:4:\"name\";s:15:\"CiviCRM OG Sync\";s:11:\"description\";s:154:\"Synchronize Organic Groups and CiviCRM Groups and ACL\'s. More information at: http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+vs.+Organic+Groups\";s:7:\"version\";s:3:\"4.2\";s:12:\"dependencies\";a:2:{i:0;s:7:\"civicrm\";i:1;s:2:\"og\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:3:\"php\";s:3:\"5.2\";s:5:\"files\";a:1:{i:0;s:22:\"civicrm_og_sync.module\";}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/civicrm/drupal/modules/civicrm_rules/civicrm_rules.module','civicrm_rules','module','',1,0,0,0,'a:9:{s:4:\"name\";s:25:\"CiviCRM Rules Integration\";s:11:\"description\";s:123:\"Integrate CiviCRM and Drupal Rules Module. Expose Contact, Contribution and other Objects along with Form / Page Operations\";s:12:\"dependencies\";a:2:{i:0;s:7:\"civicrm\";i:1;s:5:\"rules\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/civicrm/tools/drupal/modules/civicrm_van/civicrm_van.module','civicrm_van','module','',0,0,0,0,'a:8:{s:4:\"name\";s:27:\"CiviCRM <-> VAN Integration\";s:11:\"description\";s:27:\"CiviCRM <-> VAN Integration\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.2\";s:10:\"dependents\";a:0:{}}'),('sites/all/modules/civicrm/tools/drupal/modules/multisite/multisite.module','multisite','module','',0,0,0,0,'a:8:{s:4:\"name\";s:30:\"Multi Site support for CiviCRM\";s:11:\"description\";s:48:\"Multi Site Support to support a PIRG like system\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.2\";s:10:\"dependents\";a:0:{}}'),('sites/all/modules/civicrm_error/civicrm_error.module','civicrm_error','module','',1,0,0,0,'a:11:{s:4:\"name\";s:21:\"CiviCRM Error Handler\";s:11:\"description\";s:70:\"Custom error handling for CiviCRM.  Will email critical errors to you.\";s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"version\";s:11:\"7.x-2.0-rc2\";s:7:\"project\";s:13:\"civicrm_error\";s:9:\"datestamp\";s:10:\"1330454147\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/devel/devel.module','devel','module','',0,0,-1,0,'a:13:{s:4:\"name\";s:5:\"Devel\";s:11:\"description\";s:52:\"Various blocks, pages, and functions for developers.\";s:7:\"package\";s:11:\"Development\";s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:30:\"admin/config/development/devel\";s:4:\"tags\";a:1:{i:0;s:9:\"developer\";}s:5:\"files\";a:2:{i:0;s:10:\"devel.test\";i:1;s:14:\"devel.mail.inc\";}s:7:\"version\";s:7:\"7.x-1.3\";s:7:\"project\";s:5:\"devel\";s:9:\"datestamp\";s:10:\"1338940281\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/devel/devel_generate/devel_generate.module','devel_generate','module','',0,0,-1,0,'a:13:{s:4:\"name\";s:14:\"Devel generate\";s:11:\"description\";s:48:\"Generate dummy users, nodes, and taxonomy terms.\";s:7:\"package\";s:11:\"Development\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:5:\"devel\";}s:4:\"tags\";a:1:{i:0;s:9:\"developer\";}s:9:\"configure\";s:33:\"admin/config/development/generate\";s:7:\"version\";s:7:\"7.x-1.3\";s:7:\"project\";s:5:\"devel\";s:9:\"datestamp\";s:10:\"1338940281\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/devel/devel_node_access.module','devel_node_access','module','',0,0,-1,0,'a:13:{s:4:\"name\";s:17:\"Devel node access\";s:11:\"description\";s:68:\"Developer blocks and page illustrating relevant node_access records.\";s:7:\"package\";s:11:\"Development\";s:12:\"dependencies\";a:1:{i:0;s:4:\"menu\";}s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:30:\"admin/config/development/devel\";s:4:\"tags\";a:1:{i:0;s:9:\"developer\";}s:7:\"version\";s:7:\"7.x-1.3\";s:7:\"project\";s:5:\"devel\";s:9:\"datestamp\";s:10:\"1338940281\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/entity/entity.module','entity','module','',1,0,7002,0,'a:11:{s:4:\"name\";s:10:\"Entity API\";s:11:\"description\";s:69:\"Enables modules to work with any entity type and to provide entities.\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:24:{i:0;s:19:\"entity.features.inc\";i:1;s:15:\"entity.i18n.inc\";i:2;s:15:\"entity.info.inc\";i:3;s:16:\"entity.rules.inc\";i:4;s:11:\"entity.test\";i:5;s:19:\"includes/entity.inc\";i:6;s:30:\"includes/entity.controller.inc\";i:7;s:22:\"includes/entity.ui.inc\";i:8;s:27:\"includes/entity.wrapper.inc\";i:9;s:22:\"views/entity.views.inc\";i:10;s:52:\"views/handlers/entity_views_field_handler_helper.inc\";i:11;s:51:\"views/handlers/entity_views_handler_area_entity.inc\";i:12;s:53:\"views/handlers/entity_views_handler_field_boolean.inc\";i:13;s:50:\"views/handlers/entity_views_handler_field_date.inc\";i:14;s:54:\"views/handlers/entity_views_handler_field_duration.inc\";i:15;s:52:\"views/handlers/entity_views_handler_field_entity.inc\";i:16;s:51:\"views/handlers/entity_views_handler_field_field.inc\";i:17;s:53:\"views/handlers/entity_views_handler_field_numeric.inc\";i:18;s:53:\"views/handlers/entity_views_handler_field_options.inc\";i:19;s:50:\"views/handlers/entity_views_handler_field_text.inc\";i:20;s:49:\"views/handlers/entity_views_handler_field_uri.inc\";i:21;s:62:\"views/handlers/entity_views_handler_relationship_by_bundle.inc\";i:22;s:52:\"views/handlers/entity_views_handler_relationship.inc\";i:23;s:53:\"views/plugins/entity_views_plugin_row_entity_view.inc\";}s:7:\"version\";s:11:\"7.x-1.0-rc3\";s:7:\"project\";s:6:\"entity\";s:9:\"datestamp\";s:10:\"1337981155\";s:12:\"dependencies\";a:0:{}s:7:\"package\";s:5:\"Other\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/entity/entity_token.module','entity_token','module','',1,0,0,0,'a:11:{s:4:\"name\";s:13:\"Entity tokens\";s:11:\"description\";s:99:\"Provides token replacements for all properties that have no tokens and are known to the entity API.\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:2:{i:0;s:23:\"entity_token.tokens.inc\";i:1;s:19:\"entity_token.module\";}s:12:\"dependencies\";a:1:{i:0;s:6:\"entity\";}s:7:\"version\";s:11:\"7.x-1.0-rc3\";s:7:\"project\";s:6:\"entity\";s:9:\"datestamp\";s:10:\"1337981155\";s:7:\"package\";s:5:\"Other\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/entity/tests/entity_feature.module','entity_feature','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:21:\"Entity feature module\";s:11:\"description\";s:31:\"Provides some entities in code.\";s:7:\"version\";s:11:\"7.x-1.0-rc3\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:1:{i:0;s:21:\"entity_feature.module\";}s:12:\"dependencies\";a:1:{i:0;s:11:\"entity_test\";}s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"entity\";s:9:\"datestamp\";s:10:\"1337981155\";s:7:\"package\";s:5:\"Other\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/entity/tests/entity_test.module','entity_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:23:\"Entity CRUD test module\";s:11:\"description\";s:46:\"Provides entity types based upon the CRUD API.\";s:7:\"version\";s:11:\"7.x-1.0-rc3\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:2:{i:0;s:18:\"entity_test.module\";i:1;s:19:\"entity_test.install\";}s:12:\"dependencies\";a:1:{i:0;s:6:\"entity\";}s:6:\"hidden\";b:1;s:7:\"project\";s:6:\"entity\";s:9:\"datestamp\";s:10:\"1337981155\";s:7:\"package\";s:5:\"Other\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/entity/tests/entity_test_i18n.module','entity_test_i18n','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:28:\"Entity-test type translation\";s:11:\"description\";s:37:\"Allows translating entity-test types.\";s:12:\"dependencies\";a:2:{i:0;s:11:\"entity_test\";i:1;s:11:\"i18n_string\";}s:7:\"package\";s:35:\"Multilingual - Internationalization\";s:4:\"core\";s:3:\"7.x\";s:6:\"hidden\";b:1;s:7:\"version\";s:11:\"7.x-1.0-rc3\";s:7:\"project\";s:6:\"entity\";s:9:\"datestamp\";s:10:\"1337981155\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/front/front_page.module','front_page','module','',1,0,7201,0,'a:12:{s:4:\"name\";s:10:\"Front Page\";s:11:\"description\";s:57:\"Allows site admins setup custom front pages for the site.\";s:7:\"package\";s:14:\"Administration\";s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:27:\"admin/config/front/settings\";s:7:\"version\";s:7:\"7.x-2.1\";s:7:\"project\";s:5:\"front\";s:9:\"datestamp\";s:10:\"1319746532\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/imce/imce.module','imce','module','',0,0,6201,0,'a:9:{s:4:\"name\";s:4:\"IMCE\";s:11:\"description\";s:82:\"An image/file uploader and browser supporting personal directories and user quota.\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:7:\"6.x-2.1\";s:7:\"project\";s:4:\"imce\";s:9:\"datestamp\";s:10:\"1293481277\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/ldap/ldap_authentication/ldap_authentication.module','ldap_authentication','module','',1,0,7100,0,'a:12:{s:4:\"name\";s:19:\"LDAP Authentication\";s:11:\"description\";s:30:\"Implements LDAP authentication\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:1:{i:0;s:12:\"ldap_servers\";}s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:39:\"admin/config/people/ldap/authentication\";s:5:\"files\";a:8:{i:0;s:32:\"LdapAuthenticationConf.class.php\";i:1;s:37:\"LdapAuthenticationConfAdmin.class.php\";i:2;s:26:\"ldap_authentication.module\";i:3;s:27:\"ldap_authentication.install\";i:4;s:23:\"ldap_authentication.inc\";i:5;s:29:\"ldap_authentication.theme.inc\";i:6;s:29:\"ldap_authentication.admin.inc\";i:7;s:30:\"tests/ldap_authentication.test\";}s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization.module','ldap_authorization','module','',1,0,7108,0,'a:12:{s:4:\"name\";s:18:\"LDAP Authorization\";s:11:\"description\";s:54:\"Implements LDAP authorization (previously LDAP Groups)\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:1:{i:0;s:12:\"ldap_servers\";}s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:16:{i:0;s:43:\"LdapAuthorizationConsumerAbstract.class.php\";i:1;s:35:\"LdapAuthorizationConsumer.class.php\";i:2;s:40:\"LdapAuthorizationConsumerAdmin.class.php\";i:3;s:26:\"ldap_authorization.install\";i:4;s:25:\"ldap_authorization.module\";i:5;s:28:\"ldap_authorization.admin.inc\";i:6;s:33:\"ldap_authorization.admin.test.inc\";i:7;s:28:\"ldap_authorization.theme.inc\";i:8;s:32:\"tests/BasicTests/BasicTests.test\";i:9;s:36:\"tests/DeriveFromDN/DeriveFromDN.test\";i:10;s:40:\"tests/DeriveFromAttr/DeriveFromAttr.test\";i:11;s:42:\"tests/DeriveFromEntry/DeriveFromEntry.test\";i:12;s:26:\"tests/1197636/1197636.test\";i:13;s:22:\"tests/Other/Other.test\";i:14;s:16:\"tests/Og/Og.test\";i:15;s:17:\"tests/Og/Og2.test\";}s:9:\"configure\";s:38:\"admin/config/people/ldap/authorization\";s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization_drupal_role/ldap_authorization_drupal_role.module','ldap_authorization_drupal_role','module','',1,0,0,0,'a:12:{s:4:\"name\";s:33:\"LDAP Authorization - Drupal Roles\";s:11:\"description\";s:46:\"Implements LDAP authorization for Drupal roles\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:1:{i:0;s:18:\"ldap_authorization\";}s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:38:\"admin/config/people/ldap/authorization\";s:5:\"files\";a:3:{i:0;s:39:\"LdapAuthorizationConsumerRole.class.php\";i:1;s:37:\"ldap_authorization_drupal_role.module\";i:2;s:34:\"ldap_authorization_drupal_role.inc\";}s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_authorization/ldap_authorization_og/ldap_authorization_og.module','ldap_authorization_og','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:40:\"LDAP Authorization - OG (Organic Groups)\";s:11:\"description\";s:48:\"Implements LDAP authorization for Organic Groups\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:2:{i:0;s:18:\"ldap_authorization\";i:1;s:2:\"og\";}s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:41:\"admin/config/people/ldap_authorization_og\";s:5:\"files\";a:3:{i:0;s:37:\"LdapAuthorizationConsumerOG.class.php\";i:1;s:28:\"ldap_authorization_og.module\";i:2;s:25:\"ldap_authorization_og.inc\";}s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_feeds/ldap_feeds.module','ldap_feeds','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:10:\"LDAP Feeds\";s:11:\"description\";s:210:\"VERY MUCH IN ALPHA STATE. Included feeds fetcher for a generic ldap query and ldap entry parser to turn fetcher data into feeds compatible parser result. Used to automate content creation based on ldap queries.\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:3:{i:0;s:5:\"feeds\";i:1;s:12:\"ldap_servers\";i:2;s:10:\"ldap_query\";}s:9:\"configure\";s:21:\"admin/structure/feeds\";s:4:\"core\";s:3:\"7.x\";s:3:\"php\";s:3:\"5.2\";s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_help/ldap_help.module','ldap_help','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:9:\"LDAP Help\";s:11:\"description\";s:49:\"LDAP Help for configuration and reporting issues.\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:4:\"core\";s:3:\"7.x\";s:12:\"dependencies\";a:1:{i:0;s:12:\"ldap_servers\";}s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_profile/ldap_profile.module','ldap_profile','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:12:\"LDAP Profile\";s:11:\"description\";s:87:\"Implements LDAP Profile. Allows you to map Drupal profile fields to LDAP profile fields\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:1:{i:0;s:12:\"ldap_servers\";}s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:32:\"admin/config/people/ldap/profile\";s:5:\"files\";a:5:{i:0;s:19:\"ldap_profile.module\";i:1;s:20:\"ldap_profile.install\";i:2;s:22:\"ldap_profile.admin.inc\";i:3;s:25:\"LdapProfileConf.class.php\";i:4;s:30:\"LdapProfileConfAdmin.class.php\";}s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_query/ldap_query.module','ldap_query','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:10:\"LDAP Query\";s:11:\"description\";s:109:\"LDAP Query Builder and Storage for queries used by other ldap modules such as ldap feeds, ldap provision, etc\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:1:{i:0;s:12:\"ldap_servers\";}s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:9:{i:0;s:19:\"LdapQuery.class.php\";i:1;s:24:\"LdapQueryAdmin.class.php\";i:2;s:20:\"ldap_query.admin.inc\";i:3;s:14:\"ldap_query.inc\";i:4;s:18:\"ldap_query.install\";i:5;s:17:\"ldap_query.module\";i:6;s:23:\"ldap_query.settings.inc\";i:7;s:22:\"ldap_servers.theme.inc\";i:8;s:21:\"tests/ldap_query.test\";}s:9:\"configure\";s:30:\"admin/config/people/ldap/query\";s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_servers/ldap_servers.module','ldap_servers','module','',1,0,7105,0,'a:12:{s:4:\"name\";s:12:\"LDAP Servers\";s:11:\"description\";s:36:\"Implements LDAP Server Configuration\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:12:{i:0;s:20:\"LdapServer.class.php\";i:1;s:25:\"LdapServerAdmin.class.php\";i:2;s:22:\"ldap_servers.admin.inc\";i:3;s:27:\"ldap_servers.encryption.inc\";i:4;s:26:\"ldap_servers.functions.inc\";i:5;s:16:\"ldap_servers.inc\";i:6;s:20:\"ldap_servers.install\";i:7;s:19:\"ldap_servers.module\";i:8;s:25:\"ldap_servers.settings.inc\";i:9;s:26:\"ldap_servers.test_form.inc\";i:10;s:22:\"ldap_servers.theme.inc\";i:11;s:23:\"tests/ldap_servers.test\";}s:9:\"configure\";s:32:\"admin/config/people/ldap/servers\";s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_sso/ldap_sso.module','ldap_sso','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:8:\"LDAP SSO\";s:11:\"description\";s:51:\"Implements Single Sign On (SSO) LDAP Authentication\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:2:{i:0;s:12:\"ldap_servers\";i:1;s:19:\"ldap_authentication\";}s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:39:\"admin/config/people/ldap/authentication\";s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/ldap/ldap_views/ldap_views.module','ldap_views','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:10:\"LDAP Views\";s:11:\"description\";s:38:\"Implements LDAP integration with Views\";s:7:\"package\";s:37:\"Lightweight Directory Access Protocol\";s:12:\"dependencies\";a:2:{i:0;s:10:\"ldap_query\";i:1;s:5:\"views\";}s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:11:{i:0;s:17:\"ldap_views.module\";i:1;s:18:\"ldap_views.install\";i:2;s:40:\"plugins/ldap_views_plugin_query_ldap.inc\";i:3;s:40:\"handlers/ldap_views_handler_argument.inc\";i:4;s:50:\"handlers/ldap_views_handler_argument_attribute.inc\";i:5;s:37:\"handlers/ldap_views_handler_field.inc\";i:6;s:47:\"handlers/ldap_views_handler_field_attribute.inc\";i:7;s:38:\"handlers/ldap_views_handler_filter.inc\";i:8;s:48:\"handlers/ldap_views_handler_filter_attribute.inc\";i:9;s:36:\"handlers/ldap_views_handler_sort.inc\";i:10;s:46:\"handlers/ldap_views_handler_sort_attribute.inc\";}s:7:\"version\";s:14:\"7.x-1.0-beta12\";s:7:\"project\";s:4:\"ldap\";s:9:\"datestamp\";s:10:\"1345503423\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_403/nyss_403.module','nyss_403','module','',1,0,0,0,'a:9:{s:4:\"name\";s:8:\"NYSS 403\";s:11:\"description\";s:62:\"Creates Custom 403 page, and then adds that as default 403 url\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_backup/nyss_backup.module','nyss_backup','module','',1,0,0,0,'a:9:{s:4:\"name\";s:19:\"NYSS Backup/Restore\";s:11:\"description\";s:27:\"NYSS Backup/Restore module.\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_boe/nyss_boe.module','nyss_boe','module','',1,0,0,0,'a:9:{s:4:\"name\";s:27:\"NYSS Board of Election Lock\";s:11:\"description\";s:102:\"Do not allow user to edit the addresses of location type \'Board Of Election\' through contact edit form\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_civihooks/nyss_civihooks.module','nyss_civihooks','module','',1,0,0,0,'a:9:{s:4:\"name\";s:25:\"NYSS CiviCRM hooks module\";s:11:\"description\";s:46:\"Contains various hooks for NYSS customizations\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_contact/nyss_contact.module','nyss_contact','module','',1,0,0,0,'a:9:{s:4:\"name\";s:19:\"NYSS Contact module\";s:11:\"description\";s:54:\"Handle various modifications to the contact form/page.\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_dashboards/nyss_dashboards.module','nyss_dashboards','module','',1,0,0,0,'a:9:{s:4:\"name\";s:22:\"NYSS Dashboards module\";s:11:\"description\";s:27:\"Register dashboards in menu\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_dedupe/nyss_dedupe.module','nyss_dedupe','module','',1,0,0,0,'a:9:{s:4:\"name\";s:26:\"NYSS CiviCRM dedupe module\";s:11:\"description\";s:40:\"Contains various hooks for NYSS deduping\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_export/nyss_export.module','nyss_export','module','',1,0,0,0,'a:9:{s:4:\"name\";s:11:\"NYSS Export\";s:11:\"description\";s:32:\"Modifications to standard export\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_imapper/nyss_imapper.module','nyss_imapper','module','',1,0,0,0,'a:9:{s:4:\"name\";s:19:\"NYSS IMapper module\";s:11:\"description\";s:71:\"Implements an interface for mapping emails to contacts for IMAP routing\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_import/nyss_import.module','nyss_import','module','',1,0,0,0,'a:9:{s:4:\"name\";s:20:\"NYSS Standard Import\";s:11:\"description\";s:32:\"Modifications to standard import\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_io/nyss_io.module','nyss_io','module','',1,0,0,0,'a:9:{s:4:\"name\";s:11:\"NYSS Import\";s:11:\"description\";s:19:\"NYSS Import module.\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_mail/nyss_mail.module','nyss_mail','module','',1,0,0,0,'a:9:{s:4:\"name\";s:19:\"NYSS Mailing module\";s:11:\"description\";s:49:\"Customizations related to CiviMail implementation\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_massmerge/nyss_massmerge.module','nyss_massmerge','module','',1,0,0,0,'a:9:{s:4:\"name\";s:29:\"NYSS CiviCRM massmerge module\";s:11:\"description\";s:22:\"NYSS Mass Merge Module\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_sage/nyss_sage.module','nyss_sage','module','',1,0,0,0,'a:9:{s:4:\"name\";s:16:\"NYSS Sage Module\";s:11:\"description\";s:96:\"Provides integrated address the the SAGE validation, geocoding, and district assignment service.\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_tags/nyss_tags.module','nyss_tags','module','',1,0,0,0,'a:9:{s:4:\"name\";s:9:\"NYSS Tags\";s:11:\"description\";s:20:\"Log actions for tags\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/nyss_testing/nyss_testing.module','nyss_testing','module','',0,0,-1,0,'a:9:{s:4:\"name\";s:19:\"NYSS Testing Module\";s:11:\"description\";s:26:\"Register pages for testing\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"7.x\";s:7:\"version\";s:3:\"4.2\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/roleassign/roleassign.module','roleassign','module','',1,0,0,0,'a:12:{s:4:\"name\";s:10:\"RoleAssign\";s:11:\"description\";s:81:\"Allows site administrators to further delegate the task of managing user\'s roles.\";s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:35:\"admin/people/permissions/roleassign\";s:7:\"version\";s:11:\"7.x-1.0-rc1\";s:7:\"project\";s:10:\"roleassign\";s:9:\"datestamp\";s:10:\"1322306143\";s:12:\"dependencies\";a:0:{}s:7:\"package\";s:5:\"Other\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/all/modules/rules/rules.module','rules','module','',1,1,7209,20,'a:12:{s:4:\"name\";s:5:\"Rules\";s:11:\"description\";s:51:\"React on events and conditionally evaluate actions.\";s:7:\"package\";s:5:\"Rules\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:14:{i:0;s:18:\"rules.features.inc\";i:1;s:16:\"tests/rules.test\";i:2;s:18:\"includes/faces.inc\";i:3;s:23:\"includes/rules.core.inc\";i:4;s:28:\"includes/rules.processor.inc\";i:5;s:26:\"includes/rules.plugins.inc\";i:6;s:24:\"includes/rules.state.inc\";i:7;s:20:\"modules/php.eval.inc\";i:8;s:27:\"modules/rules_core.eval.inc\";i:9;s:23:\"modules/system.eval.inc\";i:10;s:20:\"ui/ui.controller.inc\";i:11;s:14:\"ui/ui.core.inc\";i:12;s:14:\"ui/ui.data.inc\";i:13;s:17:\"ui/ui.plugins.inc\";}s:12:\"dependencies\";a:2:{i:0;s:12:\"entity_token\";i:1;s:6:\"entity\";}s:7:\"version\";s:7:\"7.x-2.2\";s:7:\"project\";s:5:\"rules\";s:9:\"datestamp\";s:10:\"1343980733\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;s:9:\"configure\";s:27:\"admin/config/workflow/rules\";}'),('sites/all/modules/rules/rules_admin/rules_admin.module','rules_admin','module','',1,0,6002,0,'a:11:{s:4:\"name\";s:8:\"Rules UI\";s:11:\"description\";s:44:\"Administrative interface for managing rules.\";s:7:\"package\";s:5:\"Rules\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:2:{i:0;s:18:\"rules_admin.module\";i:1;s:15:\"rules_admin.inc\";}s:12:\"dependencies\";a:1:{i:0;s:5:\"rules\";}s:7:\"version\";s:7:\"7.x-2.2\";s:7:\"project\";s:5:\"rules\";s:9:\"datestamp\";s:10:\"1343980733\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/rules/rules_forms/rules_forms.module','rules_forms','module','',0,0,6001,20,'a:10:{s:4:\"name\";s:19:\"Rules Forms support\";s:11:\"description\";s:74:\"Provides events, conditions and actions for rule-based form customization.\";s:12:\"dependencies\";a:1:{i:0;s:5:\"rules\";}s:7:\"package\";s:5:\"Rules\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:7:\"6.x-1.4\";s:7:\"project\";s:5:\"rules\";s:9:\"datestamp\";s:10:\"1294236219\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/rules/rules_i18n/rules_i18n.module','rules_i18n','module','',0,0,-1,0,'a:11:{s:4:\"name\";s:17:\"Rules translation\";s:11:\"description\";s:25:\"Allows translating rules.\";s:12:\"dependencies\";a:2:{i:0;s:5:\"rules\";i:1;s:11:\"i18n_string\";}s:7:\"package\";s:35:\"Multilingual - Internationalization\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:3:{i:0;s:19:\"rules_i18n.i18n.inc\";i:1;s:20:\"rules_i18n.rules.inc\";i:2;s:15:\"rules_i18n.test\";}s:7:\"version\";s:7:\"7.x-2.2\";s:7:\"project\";s:5:\"rules\";s:9:\"datestamp\";s:10:\"1343980733\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/rules/rules_scheduler/rules_scheduler.module','rules_scheduler','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:15:\"Rules Scheduler\";s:11:\"description\";s:57:\"Schedule the execution of Rules components using actions.\";s:12:\"dependencies\";a:1:{i:0;s:5:\"rules\";}s:7:\"package\";s:5:\"Rules\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:8:{i:0;s:25:\"rules_scheduler.admin.inc\";i:1;s:22:\"rules_scheduler.module\";i:2;s:23:\"rules_scheduler.install\";i:3;s:25:\"rules_scheduler.rules.inc\";i:4;s:20:\"rules_scheduler.test\";i:5;s:42:\"includes/rules_scheduler.views_default.inc\";i:6;s:34:\"includes/rules_scheduler.views.inc\";i:7;s:41:\"includes/rules_scheduler_views_filter.inc\";}s:7:\"version\";s:7:\"7.x-2.2\";s:7:\"project\";s:5:\"rules\";s:9:\"datestamp\";s:10:\"1343980733\";s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;s:9:\"configure\";s:36:\"admin/config/workflow/rules/schedule\";}'),('sites/all/modules/rules/tests/rules_test.module','rules_test','module','',0,0,-1,0,'a:12:{s:4:\"name\";s:11:\"Rules Tests\";s:11:\"description\";s:35:\"Support module for the Rules tests.\";s:7:\"package\";s:7:\"Testing\";s:4:\"core\";s:3:\"7.x\";s:5:\"files\";a:2:{i:0;s:20:\"rules_test.rules.inc\";i:1;s:29:\"rules_test.rules_defaults.inc\";}s:6:\"hidden\";b:1;s:7:\"version\";s:7:\"7.x-2.2\";s:7:\"project\";s:5:\"rules\";s:9:\"datestamp\";s:10:\"1343980733\";s:12:\"dependencies\";a:0:{}s:3:\"php\";s:5:\"5.2.4\";s:9:\"bootstrap\";i:0;}'),('sites/all/modules/userprotect/userprotect.module','userprotect','module','',1,0,7000,0,'a:12:{s:4:\"name\";s:12:\"User protect\";s:11:\"description\";s:83:\"Allows admins to protect users from being edited or cancelled, on a per-user basis.\";s:4:\"core\";s:3:\"7.x\";s:9:\"configure\";s:31:\"admin/config/people/userprotect\";s:7:\"version\";s:7:\"7.x-1.0\";s:7:\"project\";s:11:\"userprotect\";s:9:\"datestamp\";s:10:\"1294210272\";s:12:\"dependencies\";a:0:{}s:7:\"package\";s:5:\"Other\";s:3:\"php\";s:5:\"5.2.4\";s:5:\"files\";a:0:{}s:9:\"bootstrap\";i:0;}'),('sites/default/themes/Bluebird/Bluebird.info','Bluebird','theme','themes/engines/phptemplate/phptemplate.engine',1,0,-1,0,'a:12:{s:4:\"name\";s:8:\"Bluebird\";s:7:\"project\";s:8:\"Bluebird\";s:11:\"description\";s:53:\"Drupal base theme built with Blueprint CSS framework.\";s:4:\"core\";s:3:\"7.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:1:{s:17:\"screen,projection\";a:4:{s:16:\"css/Bluebird.css\";s:46:\"sites/default/themes/Bluebird/css/Bluebird.css\";s:18:\"nyss_skin/skin.css\";s:48:\"sites/default/themes/Bluebird/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:55:\"sites/default/themes/Bluebird/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:43:\"sites/default/themes/Bluebird/css/style.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:56:\"sites/default/themes/Bluebird/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:52:\"sites/default/themes/Bluebird/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:48:\"sites/default/themes/Bluebird/scripts/general.js\";}s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:4:\"help\";s:4:\"Help\";}s:8:\"features\";a:9:{i:0;s:4:\"name\";i:1;s:6:\"slogan\";i:2;s:7:\"mission\";i:3;s:17:\"node_user_picture\";i:4;s:20:\"comment_user_picture\";i:5;s:6:\"search\";i:6;s:7:\"favicon\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:44:\"sites/default/themes/Bluebird/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}'),('themes/bartik/bartik.info','bartik','theme','themes/engines/phptemplate/phptemplate.engine',0,0,-1,0,'a:16:{s:4:\"name\";s:6:\"Bartik\";s:11:\"description\";s:48:\"A flexible, recolorable theme with many regions.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:3:{s:14:\"css/layout.css\";s:28:\"themes/bartik/css/layout.css\";s:13:\"css/style.css\";s:27:\"themes/bartik/css/style.css\";s:14:\"css/colors.css\";s:28:\"themes/bartik/css/colors.css\";}s:5:\"print\";a:1:{s:13:\"css/print.css\";s:27:\"themes/bartik/css/print.css\";}}s:7:\"regions\";a:17:{s:6:\"header\";s:6:\"Header\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";s:11:\"highlighted\";s:11:\"Highlighted\";s:8:\"featured\";s:8:\"Featured\";s:7:\"content\";s:7:\"Content\";s:13:\"sidebar_first\";s:13:\"Sidebar first\";s:14:\"sidebar_second\";s:14:\"Sidebar second\";s:14:\"triptych_first\";s:14:\"Triptych first\";s:15:\"triptych_middle\";s:15:\"Triptych middle\";s:13:\"triptych_last\";s:13:\"Triptych last\";s:18:\"footer_firstcolumn\";s:19:\"Footer first column\";s:19:\"footer_secondcolumn\";s:20:\"Footer second column\";s:18:\"footer_thirdcolumn\";s:19:\"Footer third column\";s:19:\"footer_fourthcolumn\";s:20:\"Footer fourth column\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"settings\";a:1:{s:20:\"shortcut_module_link\";s:1:\"0\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:28:\"themes/bartik/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}'),('themes/garland/garland.info','garland','theme','themes/engines/phptemplate/phptemplate.engine',1,0,-1,0,'a:16:{s:4:\"name\";s:7:\"Garland\";s:11:\"description\";s:111:\"A multi-column theme which can be configured to modify colors and switch between fixed and fluid width layouts.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:8:\"settings\";a:1:{s:13:\"garland_width\";s:5:\"fluid\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"regions\";a:9:{s:13:\"sidebar_first\";s:12:\"Left sidebar\";s:14:\"sidebar_second\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:11:\"highlighted\";s:11:\"Highlighted\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";}s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:29:\"themes/garland/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}'),('themes/seven/seven.info','seven','theme','themes/engines/phptemplate/phptemplate.engine',0,0,-1,0,'a:16:{s:4:\"name\";s:5:\"Seven\";s:11:\"description\";s:65:\"A simple one-column, tableless, fluid width administration theme.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:1:{s:6:\"screen\";a:2:{s:9:\"reset.css\";s:22:\"themes/seven/reset.css\";s:9:\"style.css\";s:22:\"themes/seven/style.css\";}}s:8:\"settings\";a:1:{s:20:\"shortcut_module_link\";s:1:\"1\";}s:7:\"regions\";a:5:{s:7:\"content\";s:7:\"Content\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";s:13:\"sidebar_first\";s:13:\"First sidebar\";}s:14:\"regions_hidden\";a:3:{i:0;s:13:\"sidebar_first\";i:1;s:8:\"page_top\";i:2;s:11:\"page_bottom\";}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:27:\"themes/seven/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}}'),('themes/stark/stark.info','stark','theme','themes/engines/phptemplate/phptemplate.engine',0,0,-1,0,'a:15:{s:4:\"name\";s:5:\"Stark\";s:11:\"description\";s:208:\"This theme demonstrates Drupal\'s default HTML markup and CSS styles. To learn how to build your own theme and override Drupal\'s default code, see the <a href=\"http://drupal.org/theme-guide\">Theming Guide</a>.\";s:7:\"package\";s:4:\"Core\";s:7:\"version\";s:4:\"7.15\";s:4:\"core\";s:3:\"7.x\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:10:\"layout.css\";s:23:\"themes/stark/layout.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1343839327\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"regions\";a:9:{s:13:\"sidebar_first\";s:12:\"Left sidebar\";s:14:\"sidebar_second\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";s:11:\"highlighted\";s:11:\"Highlighted\";s:4:\"help\";s:4:\"Help\";s:8:\"page_top\";s:8:\"Page top\";s:11:\"page_bottom\";s:11:\"Page bottom\";}s:8:\"features\";a:9:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";i:4;s:17:\"node_user_picture\";i:5;s:20:\"comment_user_picture\";i:6;s:25:\"comment_user_verification\";i:7;s:9:\"main_menu\";i:8;s:14:\"secondary_menu\";}s:10:\"screenshot\";s:27:\"themes/stark/screenshot.png\";s:3:\"php\";s:5:\"5.2.4\";s:7:\"scripts\";a:0:{}s:14:\"regions_hidden\";a:2:{i:0;s:8:\"page_top\";i:1;s:11:\"page_bottom\";}}');
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxonomy_index`
--

DROP TABLE IF EXISTS `taxonomy_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxonomy_index` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The node.nid this record tracks.',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The term ID.',
  `sticky` tinyint(4) DEFAULT '0' COMMENT 'Boolean indicating whether the node is sticky.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'The Unix timestamp when the node was created.',
  KEY `term_node` (`tid`,`sticky`,`created`),
  KEY `nid` (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Maintains denormalized information about node/term...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxonomy_index`
--

LOCK TABLES `taxonomy_index` WRITE;
/*!40000 ALTER TABLE `taxonomy_index` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxonomy_index` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxonomy_term_data`
--

DROP TABLE IF EXISTS `taxonomy_term_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxonomy_term_data` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext,
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT 'The weight of this term in relation to other terms.',
  `format` varchar(255) DEFAULT NULL COMMENT 'The filter_format.format of the description.',
  PRIMARY KEY (`tid`),
  KEY `vid_name` (`vid`,`name`),
  KEY `name` (`name`),
  KEY `taxonomy_tree` (`vid`,`weight`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxonomy_term_data`
--

LOCK TABLES `taxonomy_term_data` WRITE;
/*!40000 ALTER TABLE `taxonomy_term_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxonomy_term_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxonomy_term_hierarchy`
--

DROP TABLE IF EXISTS `taxonomy_term_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxonomy_term_hierarchy` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`,`parent`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxonomy_term_hierarchy`
--

LOCK TABLES `taxonomy_term_hierarchy` WRITE;
/*!40000 ALTER TABLE `taxonomy_term_hierarchy` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxonomy_term_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxonomy_term_relation`
--

DROP TABLE IF EXISTS `taxonomy_term_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxonomy_term_relation` (
  `trid` int(11) NOT NULL AUTO_INCREMENT,
  `tid1` int(10) unsigned NOT NULL DEFAULT '0',
  `tid2` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`trid`),
  UNIQUE KEY `tid1_tid2` (`tid1`,`tid2`),
  KEY `tid2` (`tid2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxonomy_term_relation`
--

LOCK TABLES `taxonomy_term_relation` WRITE;
/*!40000 ALTER TABLE `taxonomy_term_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxonomy_term_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxonomy_term_synonym`
--

DROP TABLE IF EXISTS `taxonomy_term_synonym`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxonomy_term_synonym` (
  `tsid` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tsid`),
  KEY `tid` (`tid`),
  KEY `name_tid` (`name`,`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxonomy_term_synonym`
--

LOCK TABLES `taxonomy_term_synonym` WRITE;
/*!40000 ALTER TABLE `taxonomy_term_synonym` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxonomy_term_synonym` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxonomy_vocabulary`
--

DROP TABLE IF EXISTS `taxonomy_vocabulary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxonomy_vocabulary` (
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext,
  `hierarchy` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `module` varchar(255) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT 'The weight of this vocabulary in relation to other vocabularies.',
  `machine_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'The vocabulary machine name.',
  PRIMARY KEY (`vid`),
  UNIQUE KEY `machine_name` (`machine_name`),
  KEY `list` (`weight`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxonomy_vocabulary`
--

LOCK TABLES `taxonomy_vocabulary` WRITE;
/*!40000 ALTER TABLE `taxonomy_vocabulary` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxonomy_vocabulary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trigger_assignments`
--

DROP TABLE IF EXISTS `trigger_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trigger_assignments` (
  `hook` varchar(78) NOT NULL DEFAULT '' COMMENT 'Primary Key: The name of the internal Drupal hook; for example, node_insert.',
  `aid` varchar(255) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hook`,`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trigger_assignments`
--

LOCK TABLES `trigger_assignments` WRITE;
/*!40000 ALTER TABLE `trigger_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `trigger_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `url_alias`
--

DROP TABLE IF EXISTS `url_alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `url_alias` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `language` varchar(12) NOT NULL DEFAULT '' COMMENT 'The language this alias is for; if ’und’, the alias will be used for unknown languages. Each Drupal path can have an alias for each supported language.',
  PRIMARY KEY (`pid`),
  KEY `source_language_pid` (`source`,`language`,`pid`),
  KEY `alias_language_pid` (`alias`,`language`,`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `url_alias`
--

LOCK TABLES `url_alias` WRITE;
/*!40000 ALTER TABLE `url_alias` DISABLE KEYS */;
/*!40000 ALTER TABLE `url_alias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userprotect`
--

DROP TABLE IF EXISTS `userprotect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userprotect` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `up_name` smallint(6) NOT NULL DEFAULT '0',
  `up_mail` smallint(6) NOT NULL DEFAULT '0',
  `up_pass` smallint(6) NOT NULL DEFAULT '0',
  `up_status` smallint(6) NOT NULL DEFAULT '0',
  `up_roles` smallint(6) NOT NULL DEFAULT '0',
  `up_cancel` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Cancellation protection.',
  `up_edit` smallint(6) NOT NULL DEFAULT '0',
  `up_type` char(20) NOT NULL DEFAULT '',
  `up_openid` smallint(6) NOT NULL DEFAULT '0',
  UNIQUE KEY `uid_up_type` (`uid`,`up_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userprotect`
--

LOCK TABLES `userprotect` WRITE;
/*!40000 ALTER TABLE `userprotect` DISABLE KEYS */;
INSERT INTO `userprotect` VALUES (0,0,0,0,0,0,1,1,'user',1),(1,0,0,0,0,0,0,0,'admin',0),(1,1,1,1,1,1,1,1,'user',1);
/*!40000 ALTER TABLE `userprotect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(60) NOT NULL DEFAULT '',
  `pass` varchar(128) NOT NULL DEFAULT '',
  `mail` varchar(254) DEFAULT '' COMMENT 'User’s e-mail address.',
  `theme` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `signature_format` varchar(255) DEFAULT NULL COMMENT 'The filter_format.format of the signature.',
  `created` int(11) NOT NULL DEFAULT '0',
  `access` int(11) NOT NULL DEFAULT '0',
  `login` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `timezone` varchar(32) DEFAULT NULL,
  `language` varchar(12) NOT NULL DEFAULT '',
  `init` varchar(254) DEFAULT '' COMMENT 'E-mail address used for initial account creation.',
  `data` longblob COMMENT 'A serialized array of name value pairs that are related to the user. Any form values posted during user edit are stored and are loaded into the $user object during user_load(). Use of this field is discouraged and it will likely disappear in a future...',
  `picture` int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key: file_managed.fid of user’s picture.',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`),
  KEY `access` (`access`),
  KEY `created` (`created`),
  KEY `mail` (`mail`),
  KEY `picture` (`picture`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (0,'','','','','',NULL,0,0,0,0,'America/New_York','','',NULL,0),(1,'senateroot','U$S$9EIL3rLpcwiDjD4p65QmxjiJvKgauKtuFLXgK2Yfy9c//fJU.bGD','bluebird.admin@nysenate.gov','','',NULL,1262186593,1325122045,1325122045,1,'America/New_York','','zalewski@nysenate.gov',NULL,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_roles` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `rid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`rid`),
  KEY `rid` (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_roles`
--

LOCK TABLES `users_roles` WRITE;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` VALUES (1,3);
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variable`
--

DROP TABLE IF EXISTS `variable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variable` (
  `name` varchar(128) NOT NULL DEFAULT '',
  `value` longblob NOT NULL COMMENT 'The value of the variable.',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variable`
--

LOCK TABLES `variable` WRITE;
/*!40000 ALTER TABLE `variable` DISABLE KEYS */;
INSERT INTO `variable` VALUES ('anonymous','s:9:\"Anonymous\";'),('apachesolr_cron_limit','s:2:\"50\";'),('apachesolr_default_environment','s:4:\"solr\";'),('apachesolr_failure','s:21:\"apachesolr:show_error\";'),('apachesolr_index_last','a:0:{}'),('apachesolr_index_updated','a:1:{s:4:\"solr\";i:1270473640;}'),('apachesolr_last_optimize','i:1270473640;'),('apachesolr_mlt_blocks','a:1:{s:7:\"mlt-001\";a:8:{s:4:\"name\";s:14:\"More like this\";s:11:\"num_results\";s:1:\"5\";s:6:\"mlt_fl\";a:2:{s:5:\"title\";s:5:\"title\";s:14:\"taxonomy_names\";s:14:\"taxonomy_names\";}s:9:\"mlt_mintf\";s:1:\"1\";s:9:\"mlt_mindf\";s:1:\"1\";s:9:\"mlt_minwl\";s:1:\"3\";s:9:\"mlt_maxwl\";s:2:\"15\";s:9:\"mlt_maxqt\";s:2:\"20\";}}'),('apachesolr_read_only','s:1:\"0\";'),('apachesolr_rows','s:2:\"10\";'),('apachesolr_search_default_previous','i:0;'),('apachesolr_search_taxonomy_previous','i:0;'),('apachesolr_set_nodeapi_messages','s:1:\"1\";'),('apachesolr_site_hash','s:12:\"886ccf6f5ab2\";'),('block_cache','s:1:\"0\";'),('cache','s:1:\"1\";'),('cache_flush','i:1259776801;'),('cache_flush_cache_block','i:1259726411;'),('cache_flush_cache_page','i:1259726411;'),('cache_lifetime','s:1:\"0\";'),('civicrm_error_to','s:43:\"brian@lcdservices.biz,zalewski@nysenate.gov\";'),('clean_url','s:1:\"1\";'),('clear','s:17:\"Clear cached data\";'),('comment_default_mode_page','i:1;'),('comment_default_mode_story','i:1;'),('comment_page','i:0;'),('comment_preview_page','i:2;'),('comment_preview_story','i:2;'),('configurable_timezones','i:0;'),('cron_key','s:43:\"H_UKYncMO4a7xiEGb1mW7oq8LRE7PVo5TtVw8N9prLM\";'),('cron_last','i:1368514571;'),('css_js_query_string','s:6:\"mms20u\";'),('date_default_timezone','s:16:\"America/New_York\";'),('drupal_http_request_fails','b:1;'),('drupal_private_key','s:64:\"20def726aacf6c85cda4ddb3eba410c9fb8ccd84a55bd1e777ee6a385592b79f\";'),('empty_timezone_message','i:0;'),('error_level','s:1:\"0\";'),('file_directory_path','s:37:\"sites/template.crm.nysenate.gov/files\";'),('file_public_path','s:37:\"sites/template.crm.nysenate.gov/files\";'),('file_temporary_path','s:4:\"/tmp\";'),('filter_fallback_format','s:1:\"3\";'),('front_page_home_link_path','s:0:\"\";'),('image_toolkit','s:2:\"gd\";'),('imce_profiles','a:2:{i:1;a:10:{s:4:\"name\";s:6:\"User-1\";s:7:\"usertab\";i:1;s:8:\"filesize\";i:0;s:5:\"quota\";i:0;s:7:\"tuquota\";i:0;s:10:\"extensions\";s:1:\"*\";s:10:\"dimensions\";s:9:\"1200x1200\";s:7:\"filenum\";i:0;s:11:\"directories\";a:1:{i:0;a:7:{s:4:\"name\";s:1:\".\";s:6:\"subnav\";i:1;s:6:\"browse\";i:1;s:6:\"upload\";i:1;s:5:\"thumb\";i:1;s:6:\"delete\";i:1;s:6:\"resize\";i:1;}}s:10:\"thumbnails\";a:3:{i:0;a:4:{s:4:\"name\";s:5:\"Small\";s:10:\"dimensions\";s:5:\"90x90\";s:6:\"prefix\";s:6:\"small_\";s:6:\"suffix\";s:0:\"\";}i:1;a:4:{s:4:\"name\";s:6:\"Medium\";s:10:\"dimensions\";s:7:\"120x120\";s:6:\"prefix\";s:7:\"medium_\";s:6:\"suffix\";s:0:\"\";}i:2;a:4:{s:4:\"name\";s:5:\"Large\";s:10:\"dimensions\";s:7:\"180x180\";s:6:\"prefix\";s:6:\"large_\";s:6:\"suffix\";s:0:\"\";}}}i:2;a:10:{s:4:\"name\";s:14:\"Sample profile\";s:7:\"usertab\";i:1;s:8:\"filesize\";i:1;s:5:\"quota\";i:2;s:7:\"tuquota\";i:0;s:10:\"extensions\";s:16:\"gif png jpg jpeg\";s:10:\"dimensions\";s:7:\"800x600\";s:7:\"filenum\";i:1;s:11:\"directories\";a:1:{i:0;a:7:{s:4:\"name\";s:5:\"u%uid\";s:6:\"subnav\";i:0;s:6:\"browse\";i:1;s:6:\"upload\";i:1;s:5:\"thumb\";i:1;s:6:\"delete\";i:0;s:6:\"resize\";i:0;}}s:10:\"thumbnails\";a:1:{i:0;a:4:{s:4:\"name\";s:5:\"Thumb\";s:10:\"dimensions\";s:5:\"90x90\";s:6:\"prefix\";s:6:\"thumb_\";s:6:\"suffix\";s:0:\"\";}}}}'),('imce_roles_profiles','a:0:{}'),('install_profile','s:8:\"standard\";'),('install_task','s:4:\"done\";'),('install_time','i:1259725924;'),('ldapauth_alter_email_field','s:1:\"0\";'),('ldapauth_disable_pass_change','i:0;'),('ldapauth_forget_passwords','i:1;'),('ldapauth_login_conflict','s:1:\"1\";'),('ldapauth_login_process','s:1:\"1\";'),('ldapauth_sync_passwords','i:0;'),('ldap_authentication_conf','a:18:{s:4:\"sids\";a:1:{s:9:\"nyss_ldap\";s:9:\"nyss_ldap\";}s:18:\"authenticationMode\";i:2;s:20:\"loginConflictResolve\";i:2;s:12:\"acctCreation\";i:4;s:18:\"loginUIUsernameTxt\";N;s:18:\"loginUIPasswordTxt\";N;s:19:\"ldapUserHelpLinkUrl\";N;s:20:\"ldapUserHelpLinkText\";s:10:\"Logon Help\";s:11:\"emailOption\";i:3;s:11:\"emailUpdate\";i:1;s:19:\"allowOnlyIfTextInDn\";a:0:{}s:17:\"excludeIfTextInDn\";a:0:{}s:12:\"allowTestPhp\";s:0:\"\";s:25:\"excludeIfNoAuthorizations\";N;s:28:\"ssoRemoteUserStripDomainName\";N;s:13:\"seamlessLogin\";N;s:18:\"ldapImplementation\";N;s:12:\"cookieExpire\";N;}'),('ldap_servers_encryption','i:10;'),('ldap_servers_encrypt_key','s:10:\"C1qFAu9TRe\";'),('ldap_servers_require_ssl_for_credentails','i:0;'),('maintenance_mode','s:1:\"0\";'),('maintenance_mode_message','s:111:\"dev.senate.rayogram.com is currently under maintenance. We should be back shortly. Thank you for your patience.\";'),('menu_expanded','a:0:{}'),('menu_masks','a:33:{i:0;i:507;i:1;i:506;i:2;i:503;i:3;i:253;i:4;i:252;i:5;i:251;i:6;i:245;i:7;i:127;i:8;i:126;i:9;i:125;i:10;i:123;i:11;i:122;i:12;i:121;i:13;i:63;i:14;i:62;i:15;i:61;i:16;i:60;i:17;i:58;i:18;i:44;i:19;i:31;i:20;i:30;i:21;i:29;i:22;i:24;i:23;i:21;i:24;i:15;i:25;i:14;i:26;i:11;i:27;i:7;i:28;i:6;i:29;i:5;i:30;i:3;i:31;i:2;i:32;i:1;}'),('minimum_word_size','s:1:\"3\";'),('node_cron_comments_scale','d:1;'),('node_cron_last','s:10:\"1367678438\";'),('node_cron_views_scale','d:1;'),('node_options_forum','a:1:{i:0;s:6:\"status\";}'),('node_options_page','a:1:{i:0;s:6:\"status\";}'),('node_preview_page','i:1;'),('node_preview_story','i:1;'),('node_rank_comments','s:1:\"5\";'),('node_rank_recent','s:1:\"5\";'),('node_rank_relevance','s:1:\"5\";'),('node_submitted_page','b:0;'),('overlap_cjk','i:1;'),('page_cache_max_age','s:3:\"600\";'),('page_compression','s:1:\"1\";'),('path_alias_whitelist','a:0:{}'),('preprocess_css','i:1;'),('preprocess_js','i:1;'),('roleassign_roles','a:17:{i:8;s:1:\"8\";i:5;s:1:\"5\";i:12;s:2:\"12\";i:16;s:2:\"16\";i:14;s:2:\"14\";i:15;s:2:\"15\";i:17;s:2:\"17\";i:19;s:2:\"19\";i:9;s:1:\"9\";i:10;s:2:\"10\";i:7;s:1:\"7\";i:6;s:1:\"6\";i:11;s:2:\"11\";i:13;s:2:\"13\";i:4;i:0;i:18;i:0;i:3;i:0;}'),('rules_empty_sets','a:26:{s:14:\"contact_create\";i:0;s:12:\"contact_edit\";i:1;s:12:\"contact_view\";i:2;s:14:\"contact_delete\";i:3;s:14:\"mailing_create\";i:4;s:12:\"mailing_edit\";i:5;s:16:\"mailing_uploaded\";i:6;s:17:\"mailing_scheduled\";i:7;s:14:\"mailing_inform\";i:8;s:14:\"mailing_queued\";i:9;s:16:\"mailing_complete\";i:10;s:11:\"node_insert\";i:11;s:11:\"node_update\";i:12;s:12:\"node_presave\";i:13;s:9:\"node_view\";i:14;s:11:\"node_delete\";i:15;s:4:\"init\";i:16;s:4:\"cron\";i:17;s:8:\"watchdog\";i:18;s:11:\"user_insert\";i:19;s:11:\"user_update\";i:20;s:12:\"user_presave\";i:21;s:9:\"user_view\";i:22;s:11:\"user_delete\";i:23;s:10:\"user_login\";i:24;s:11:\"user_logout\";i:25;}'),('search_active_modules','a:3:{i:0;s:4:\"node\";i:1;s:4:\"user\";i:2;s:17:\"apachesolr_search\";}'),('search_cron_limit','s:2:\"50\";'),('search_default_module','s:17:\"apachesolr_search\";'),('site_403','s:6:\"node/1\";'),('site_404','s:6:\"node/2\";'),('site_frontpage','s:25:\"civicrm/dashboard?reset=1\";'),('site_mail','s:27:\"bluebird.admin@nysenate.gov\";'),('site_name','s:8:\"Bluebird\";'),('site_slogan','s:0:\"\";'),('theme_default','s:8:\"Bluebird\";'),('theme_settings','a:0:{}'),('update_d6','b:1;'),('update_d7_requirements','b:1;'),('update_last_check','i:1321450196;'),('userprotect_administrator_bypass_defaults','a:8:{s:7:\"up_name\";s:7:\"up_name\";s:7:\"up_mail\";s:7:\"up_mail\";s:7:\"up_pass\";s:7:\"up_pass\";s:9:\"up_status\";s:9:\"up_status\";s:8:\"up_roles\";s:8:\"up_roles\";s:9:\"up_openid\";s:9:\"up_openid\";s:9:\"up_delete\";s:9:\"up_delete\";s:7:\"up_edit\";s:7:\"up_edit\";}'),('userprotect_autoprotect','i:0;'),('userprotect_protection_defaults','a:8:{s:9:\"up_status\";s:9:\"up_status\";s:9:\"up_delete\";s:9:\"up_delete\";s:7:\"up_name\";i:0;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:0;s:7:\"up_edit\";i:0;}'),('userprotect_role_protections','a:12:{i:4;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:1;s:7:\"up_pass\";i:1;s:9:\"up_status\";i:1;s:8:\"up_roles\";i:1;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:1;}i:8;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:2;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:5;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:12;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:9;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:10;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:7;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:6;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:11;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:3;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:1;s:7:\"up_pass\";i:1;s:9:\"up_status\";i:1;s:8:\"up_roles\";i:1;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:1;s:7:\"up_edit\";i:1;}i:13;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}}'),('user_cancel_method','s:20:\"user_cancel_reassign\";'),('user_email_verification','i:1;'),('user_mail_password_reset_body','s:454:\"[user:name],\r\n\r\nA request to reset the password for your account has been made at [site:name].\r\n\r\nYou may now log in to [site:url-brief] by clicking on this link or copying and pasting it in your browser:\r\n\r\n[user:one-time-login-url]\r\n\r\nThis is a one-time login, so it can be used only once. It expires after one day and nothing will happen if it\'s not used.\r\n\r\nAfter logging in, you will be redirected to [user:edit-url] so you can change your password.\";'),('user_mail_password_reset_subject','s:60:\"Replacement login information for [user:name] at [site:name]\";'),('user_mail_register_admin_created_body','s:502:\"[user:name],\r\n\r\nA site administrator at [site:name] has created an account for you. You may now log in to [site:login-url] using the following username and password:\r\n\r\nusername: [user:name]\r\npassword: \r\n\r\nYou may also log in by clicking on this link or copying and pasting it in your browser:\r\n\r\n[user:one-time-login-url]\r\n\r\nThis is a one-time login, so it can be used only once.\r\n\r\nAfter logging in, you will be redirected to [user:edit-url] so you can change your password.\r\n\r\n\r\n--  [site:name] team\";'),('user_mail_register_admin_created_subject','s:58:\"An administrator created an account for you at [site:name]\";'),('user_mail_register_no_approval_required_body','s:476:\"[user:name],\r\n\r\nThank you for registering at [site:name]. You may now log in to [site:login-url] using the following username and password:\r\n\r\nusername: [user:name]\r\npassword: \r\n\r\nYou may also log in by clicking on this link or copying and pasting it in your browser:\r\n\r\n[user:one-time-login-url]\r\n\r\nThis is a one-time login, so it can be used only once.\r\n\r\nAfter logging in, you will be redirected to [user:edit-url] so you can change your password.\r\n\r\n\r\n--  [site:name] team\";'),('user_mail_register_no_approval_required_subject','s:46:\"Account details for [user:name] at [site:name]\";'),('user_mail_register_pending_approval_body','s:287:\"[user:name],\r\n\r\nThank you for registering at [site:name]. Your application for an account is currently pending approval. Once it has been approved, you will receive another e-mail containing information about how to log in, set your password, and other details.\r\n\r\n\r\n--  [site:name] team\";'),('user_mail_register_pending_approval_subject','s:71:\"Account details for [user:name] at [site:name] (pending admin approval)\";'),('user_mail_status_activated_body','s:471:\"[user:name],\r\n\r\nYour account at [site:name] has been activated.\r\n\r\nYou may now log in by clicking on this link or copying and pasting it in your browser:\r\n\r\n[user:one-time-login-url]\r\n\r\nThis is a one-time login, so it can be used only once.\r\n\r\nAfter logging in, you will be redirected to [user:edit-url] so you can change your password.\r\n\r\nOnce you have set your own password, you will be able to log in to [site:login-url] in the future using:\r\n\r\nusername: [user:name]\r\n\";'),('user_mail_status_activated_notify','s:1:\"1\";'),('user_mail_status_activated_subject','s:57:\"Account details for [user:name] at [site:name] (approved)\";'),('user_mail_status_blocked_body','s:61:\"[user:name],\r\n\r\nYour account on [site:name] has been blocked.\";'),('user_mail_status_blocked_notify','i:0;'),('user_mail_status_blocked_subject','s:56:\"Account details for [user:name] at [site:name] (blocked)\";'),('user_mail_status_canceled_body','s:61:\"[user:name],\r\n\r\nYour account on [site:name] has been deleted.\";'),('user_mail_status_canceled_subject','s:56:\"Account details for [user:name] at [site:name] (deleted)\";'),('user_mail_status_deleted_notify','i:0;'),('user_pictures','s:1:\"0\";'),('user_picture_default','s:0:\"\";'),('user_picture_dimensions','s:5:\"85x85\";'),('user_picture_file_size','s:2:\"30\";'),('user_picture_guidelines','s:0:\"\";'),('user_picture_path','s:8:\"pictures\";'),('user_register','s:1:\"0\";'),('user_signatures','s:1:\"0\";'),('wipe','s:13:\"Re-index site\";');
/*!40000 ALTER TABLE `variable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `watchdog`
--

DROP TABLE IF EXISTS `watchdog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watchdog` (
  `wid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT 'Type of log message, for example "user" or "page not found."',
  `message` longtext NOT NULL,
  `variables` longblob NOT NULL COMMENT 'Serialized array of variables that match the message string and that is passed into the t() function.',
  `severity` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `link` varchar(255) DEFAULT '' COMMENT 'Link to view the result of the event.',
  `location` text NOT NULL,
  `referer` text,
  `hostname` varchar(128) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wid`),
  KEY `type` (`type`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `watchdog`
--

LOCK TABLES `watchdog` WRITE;
/*!40000 ALTER TABLE `watchdog` DISABLE KEYS */;
/*!40000 ALTER TABLE `watchdog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'senate_prod_d_template'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-05-14  3:28:09
