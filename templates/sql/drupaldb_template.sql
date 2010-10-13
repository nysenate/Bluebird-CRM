-- MySQL dump 10.13  Distrib 5.1.41, for debian-linux-gnu (x86_64)
--
-- Host: crmdbprod    Database: senate_d_template
-- ------------------------------------------------------
-- Server version	5.1.41-3ubuntu12.6

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
  `parameters` longtext NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actions`
--

LOCK TABLES `actions` WRITE;
/*!40000 ALTER TABLE `actions` DISABLE KEYS */;
INSERT INTO `actions` VALUES ('comment_unpublish_action','comment','comment_unpublish_action','','Unpublish comment'),('node_make_sticky_action','node','node_make_sticky_action','','Make post sticky'),('node_make_unsticky_action','node','node_make_unsticky_action','','Make post unsticky'),('node_promote_action','node','node_promote_action','','Promote post to front page'),('node_publish_action','node','node_publish_action','','Publish post'),('node_save_action','node','node_save_action','','Save post'),('node_unpromote_action','node','node_unpromote_action','','Remove post from front page'),('node_unpublish_action','node','node_unpublish_action','','Unpublish post'),('user_block_ip_action','user','user_block_ip_action','','Ban IP address of current user'),('user_block_user_action','user','user_block_user_action','','Block current user');
/*!40000 ALTER TABLE `actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actions_aid`
--

DROP TABLE IF EXISTS `actions_aid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actions_aid` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actions_aid`
--

LOCK TABLES `actions_aid` WRITE;
/*!40000 ALTER TABLE `actions_aid` DISABLE KEYS */;
/*!40000 ALTER TABLE `actions_aid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apachesolr_search_node`
--

DROP TABLE IF EXISTS `apachesolr_search_node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apachesolr_search_node` (
  `nid` int(10) unsigned NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `changed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nid`),
  KEY `changed` (`changed`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apachesolr_search_node`
--

LOCK TABLES `apachesolr_search_node` WRITE;
/*!40000 ALTER TABLE `apachesolr_search_node` DISABLE KEYS */;
/*!40000 ALTER TABLE `apachesolr_search_node` ENABLE KEYS */;
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
  `bid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `batch` longtext,
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
-- Table structure for table `blocks`
--

DROP TABLE IF EXISTS `blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocks` (
  `bid` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(64) NOT NULL DEFAULT '',
  `delta` varchar(32) NOT NULL DEFAULT '0',
  `theme` varchar(64) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `weight` tinyint(4) NOT NULL DEFAULT '0',
  `region` varchar(64) NOT NULL DEFAULT '',
  `custom` tinyint(4) NOT NULL DEFAULT '0',
  `throttle` tinyint(4) NOT NULL DEFAULT '0',
  `visibility` tinyint(4) NOT NULL DEFAULT '0',
  `pages` text NOT NULL,
  `title` varchar(64) NOT NULL DEFAULT '',
  `cache` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`bid`),
  UNIQUE KEY `tmd` (`theme`,`module`,`delta`),
  KEY `list` (`theme`,`status`,`region`,`weight`,`module`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocks`
--

LOCK TABLES `blocks` WRITE;
/*!40000 ALTER TABLE `blocks` DISABLE KEYS */;
INSERT INTO `blocks` VALUES (1,'user','0','garland',1,0,'left',0,0,0,'','',-1),(2,'user','1','garland',1,0,'left',0,0,0,'','',-1),(3,'system','0','garland',1,10,'footer',0,0,0,'','',-1),(4,'comment','0','garland',0,0,'',0,0,0,'','',1),(5,'menu','primary-links','garland',0,0,'',0,0,0,'','',-1),(6,'menu','secondary-links','garland',0,0,'',0,0,0,'','',-1),(7,'node','0','garland',0,0,'',0,0,0,'','',-1),(8,'search','0','garland',0,0,'',0,0,0,'','',-1),(9,'user','2','garland',0,0,'',0,0,0,'','',1),(10,'user','3','garland',0,0,'',0,0,0,'','',-1),(11,'apachesolr','mlt-001','garland',0,0,'',0,0,0,'','',4),(12,'apachesolr','sort','garland',0,0,'',0,0,0,'','',4),(13,'apachesolr_search','currentsearch','garland',0,0,'',0,0,0,'','',4),(14,'civicrm','1','garland',1,-100,'left',0,0,1,'civicrm*','',8),(15,'civicrm','2','garland',1,-99,'left',0,0,1,'civicrm*','Recent Items',8),(16,'civicrm','3','garland',1,-98,'left',0,0,1,'civicrm*','<none>',8),(17,'civicrm','4','garland',1,-97,'left',0,0,1,'civicrm*','',8),(18,'civicrm','5','garland',1,-96,'left',0,0,1,'civicrm*','',8),(19,'civicrm','6','garland',0,-95,'left',0,0,1,'civicrm*','',8),(20,'civicrm','7','garland',0,-94,'left',0,0,1,'civicrm*','',8),(21,'apachesolr','mlt-001','tao',0,0,'left',0,0,0,'','',4),(22,'apachesolr','sort','tao',0,0,'left',0,0,0,'','',4),(23,'apachesolr_search','currentsearch','tao',0,0,'left',0,0,0,'','',4),(24,'civicrm','1','tao',1,-100,'left',0,0,1,'civicrm*','',8),(25,'civicrm','2','tao',1,-99,'left',0,0,1,'civicrm*','Recent Items',8),(26,'civicrm','3','tao',1,-98,'left',0,0,1,'civicrm*','<none>',8),(27,'civicrm','4','tao',1,-97,'left',0,0,1,'civicrm*','',8),(28,'civicrm','5','tao',1,-96,'left',0,0,1,'civicrm*','',8),(29,'civicrm','6','tao',0,-95,'left',0,0,1,'civicrm*','',8),(30,'civicrm','7','tao',0,-94,'left',0,0,1,'civicrm*','',8),(31,'comment','0','tao',0,0,'left',0,0,0,'','',1),(32,'menu','primary-links','tao',0,0,'left',0,0,0,'','',-1),(33,'menu','secondary-links','tao',0,0,'left',0,0,0,'','',-1),(34,'node','0','tao',0,0,'left',0,0,0,'','',-1),(35,'search','0','tao',0,0,'left',0,0,0,'','',-1),(36,'system','0','tao',1,10,'footer',0,0,0,'','',-1),(37,'user','0','tao',1,0,'left',0,0,0,'','',-1),(38,'user','1','tao',1,0,'left',0,0,0,'','',-1),(39,'user','2','tao',0,0,'left',0,0,0,'','',1),(40,'user','3','tao',0,0,'left',0,0,0,'','',-1),(41,'apachesolr','mlt-001','ginkgo',0,0,'left',0,0,0,'','',4),(42,'apachesolr','sort','ginkgo',0,0,'left',0,0,0,'','',4),(43,'apachesolr_search','currentsearch','ginkgo',0,0,'left',0,0,0,'','',4),(44,'civicrm','1','ginkgo',1,-100,'left',0,0,1,'civicrm*','',8),(45,'civicrm','2','ginkgo',1,-99,'left',0,0,1,'civicrm*','Recent Items',8),(46,'civicrm','3','ginkgo',1,-98,'left',0,0,1,'civicrm*','<none>',8),(47,'civicrm','4','ginkgo',1,-97,'left',0,0,1,'civicrm*','',8),(48,'civicrm','5','ginkgo',1,-96,'left',0,0,1,'civicrm*','',8),(49,'civicrm','6','ginkgo',0,-95,'left',0,0,1,'civicrm*','',8),(50,'civicrm','7','ginkgo',0,-94,'left',0,0,1,'civicrm*','',8),(51,'comment','0','ginkgo',0,0,'left',0,0,0,'','',1),(52,'menu','primary-links','ginkgo',0,0,'left',0,0,0,'','',-1),(53,'menu','secondary-links','ginkgo',0,0,'left',0,0,0,'','',-1),(54,'node','0','ginkgo',0,0,'left',0,0,0,'','',-1),(55,'search','0','ginkgo',0,0,'left',0,0,0,'','',-1),(56,'system','0','ginkgo',1,10,'left',0,0,0,'','',-1),(57,'user','0','ginkgo',1,0,'left',0,0,0,'','',-1),(58,'user','1','ginkgo',1,0,'left',0,0,0,'','',-1),(59,'user','2','ginkgo',0,0,'left',0,0,0,'','',1),(60,'user','3','ginkgo',0,0,'left',0,0,0,'','',-1),(61,'apachesolr','mlt-001','blueprint',0,-4,'',0,0,0,'','',4),(62,'apachesolr','sort','blueprint',0,-7,'',0,0,0,'','',4),(63,'apachesolr_search','currentsearch','blueprint',0,-6,'',0,0,0,'','',4),(64,'civicrm','1','blueprint',0,-10,'',0,0,1,'civicrm*','',8),(65,'civicrm','2','blueprint',1,-10,'footer',0,0,1,'civicrm*','Recent Items',8),(66,'civicrm','3','blueprint',1,-10,'content',0,0,1,'civicrm*','<none>',8),(67,'civicrm','4','blueprint',1,-9,'header',0,0,1,'civicrm*','',8),(68,'civicrm','5','blueprint',0,-8,'',0,0,1,'civicrm*','',8),(70,'civicrm','7','blueprint',0,-8,'',0,0,1,'civicrm*','',8),(71,'comment','0','blueprint',0,-2,'',0,0,0,'','',1),(72,'menu','primary-links','blueprint',0,-3,'',0,0,0,'','',-1),(73,'menu','secondary-links','blueprint',0,0,'',0,0,0,'','',-1),(74,'node','0','blueprint',0,1,'',0,0,0,'','',-1),(75,'search','0','blueprint',0,-1,'',0,0,0,'','',-1),(76,'system','0','blueprint',0,-5,'',0,0,0,'','',-1),(77,'user','0','blueprint',1,-7,'content',0,0,0,'','',-1),(78,'user','1','blueprint',0,-9,'',0,0,0,'','',-1),(79,'user','2','blueprint',0,2,'',0,0,0,'','',1),(80,'user','3','blueprint',0,3,'',0,0,0,'','',-1),(81,'apachesolr','mlt-001','rayCivicrm',0,-1,'',0,0,0,'','',4),(82,'apachesolr','sort','rayCivicrm',0,-3,'',0,0,0,'','',4),(83,'apachesolr_search','currentsearch','rayCivicrm',0,-7,'',0,0,0,'','',4),(84,'civicrm','1','rayCivicrm',0,-10,'',0,0,1,'civicrm*','',8),(85,'civicrm','2','rayCivicrm',1,-9,'footer',0,0,1,'civicrm*','Recent Items',8),(86,'civicrm','3','rayCivicrm',0,-10,'',0,0,1,'civicrm*','<none>',8),(87,'civicrm','4','rayCivicrm',0,-6,'',0,0,1,'civicrm*','',8),(88,'civicrm','5','rayCivicrm',0,-4,'',0,0,1,'civicrm*','',8),(89,'civicrm','7','rayCivicrm',0,-5,'',0,0,1,'civicrm*','',8),(90,'comment','0','rayCivicrm',0,1,'',0,0,0,'','',1),(91,'menu','primary-links','rayCivicrm',0,0,'',0,0,0,'','',-1),(92,'menu','secondary-links','rayCivicrm',0,2,'',0,0,0,'','',-1),(93,'node','0','rayCivicrm',0,4,'',0,0,0,'','',-1),(94,'search','0','rayCivicrm',0,3,'',0,0,0,'','',-1),(95,'system','0','rayCivicrm',0,-2,'',0,0,0,'','',-1),(96,'user','0','rayCivicrm',1,-7,'content',0,0,0,'','',-1),(97,'user','1','rayCivicrm',0,-8,'',0,0,0,'','',-1),(98,'user','2','rayCivicrm',0,5,'',0,0,0,'','',1),(99,'user','3','rayCivicrm',0,6,'',0,0,0,'','',-1);
/*!40000 ALTER TABLE `blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocks_roles`
--

DROP TABLE IF EXISTS `blocks_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocks_roles` (
  `module` varchar(64) NOT NULL,
  `delta` varchar(32) NOT NULL,
  `rid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`module`,`delta`,`rid`),
  KEY `rid` (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocks_roles`
--

LOCK TABLES `blocks_roles` WRITE;
/*!40000 ALTER TABLE `blocks_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocks_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `boxes`
--

DROP TABLE IF EXISTS `boxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `boxes` (
  `bid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `body` longtext,
  `info` varchar(128) NOT NULL DEFAULT '',
  `format` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`bid`),
  UNIQUE KEY `info` (`info`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `boxes`
--

LOCK TABLES `boxes` WRITE;
/*!40000 ALTER TABLE `boxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `boxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `cid` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `expire` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `headers` text,
  `serialized` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_block`
--

DROP TABLE IF EXISTS `cache_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_block` (
  `cid` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `expire` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `headers` text,
  `serialized` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_block`
--

LOCK TABLES `cache_block` WRITE;
/*!40000 ALTER TABLE `cache_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_filter`
--

DROP TABLE IF EXISTS `cache_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_filter` (
  `cid` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `expire` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `headers` text,
  `serialized` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `cid` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `expire` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `headers` text,
  `serialized` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `cid` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `expire` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `headers` text,
  `serialized` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `cid` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `expire` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `headers` text,
  `serialized` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_page`
--

LOCK TABLES `cache_page` WRITE;
/*!40000 ALTER TABLE `cache_page` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_update`
--

DROP TABLE IF EXISTS `cache_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_update` (
  `cid` varchar(255) NOT NULL DEFAULT '',
  `data` longblob,
  `expire` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `headers` text,
  `serialized` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `nid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(64) NOT NULL DEFAULT '',
  `comment` longtext NOT NULL,
  `hostname` varchar(128) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `format` smallint(6) NOT NULL DEFAULT '0',
  `thread` varchar(255) NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `mail` varchar(64) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cid`),
  KEY `pid` (`pid`),
  KEY `nid` (`nid`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
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
-- Table structure for table `filter_formats`
--

DROP TABLE IF EXISTS `filter_formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filter_formats` (
  `format` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `roles` varchar(255) NOT NULL DEFAULT '',
  `cache` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`format`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filter_formats`
--

LOCK TABLES `filter_formats` WRITE;
/*!40000 ALTER TABLE `filter_formats` DISABLE KEYS */;
INSERT INTO `filter_formats` VALUES (1,'Filtered HTML',',1,2,',1),(2,'Full HTML','',1);
/*!40000 ALTER TABLE `filter_formats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filters`
--

DROP TABLE IF EXISTS `filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filters` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `format` int(11) NOT NULL DEFAULT '0',
  `module` varchar(64) NOT NULL DEFAULT '',
  `delta` tinyint(4) NOT NULL DEFAULT '0',
  `weight` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`),
  UNIQUE KEY `fmd` (`format`,`module`,`delta`),
  KEY `list` (`format`,`weight`,`module`,`delta`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filters`
--

LOCK TABLES `filters` WRITE;
/*!40000 ALTER TABLE `filters` DISABLE KEYS */;
INSERT INTO `filters` VALUES (1,1,'filter',2,0),(2,1,'filter',0,1),(3,1,'filter',1,2),(4,1,'filter',3,10),(5,2,'filter',2,0),(6,2,'filter',1,1),(7,2,'filter',3,10);
/*!40000 ALTER TABLE `filters` ENABLE KEYS */;
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
  `hostname` varchar(128) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`),
  KEY `allow` (`event`,`hostname`,`timestamp`)
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
-- Table structure for table `ldapauth`
--

DROP TABLE IF EXISTS `ldapauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ldapauth` (
  `sid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `server` varchar(255) NOT NULL,
  `port` int(11) NOT NULL DEFAULT '389',
  `tls` tinyint(4) NOT NULL DEFAULT '0',
  `encrypted` tinyint(4) NOT NULL DEFAULT '0',
  `basedn` text,
  `user_attr` varchar(255) DEFAULT NULL,
  `mail_attr` varchar(255) DEFAULT NULL,
  `binddn` varchar(255) DEFAULT NULL,
  `bindpw` varchar(255) DEFAULT NULL,
  `login_php` text,
  `filter_php` text,
  `weight` int(11) NOT NULL DEFAULT '0',
  `ldapgroups_in_dn` tinyint(4) NOT NULL DEFAULT '0',
  `ldapgroups_dn_attribute` varchar(255) DEFAULT NULL,
  `ldapgroups_attr` varchar(255) DEFAULT NULL,
  `ldapgroups_in_attr` tinyint(4) NOT NULL DEFAULT '0',
  `ldapgroups_as_entries` tinyint(4) NOT NULL DEFAULT '0',
  `ldapgroups_entries` text,
  `ldapgroups_entries_attribute` varchar(255) DEFAULT NULL,
  `ldapgroups_mappings` text,
  `ldapgroups_mappings_filter` tinyint(4) NOT NULL DEFAULT '0',
  `ldapgroups_filter_php` text,
  `ldapgroups_groups` text,
  PRIMARY KEY (`sid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ldapauth`
--

LOCK TABLES `ldapauth` WRITE;
/*!40000 ALTER TABLE `ldapauth` DISABLE KEYS */;
INSERT INTO `ldapauth` VALUES (1,'NY Senate LDAP Server',1,'webmail.senate.state.ny.us',389,0,0,'o=senate','','mail','','','','',0,0,'','',0,1,'a:10:{i:0;s:15:\"cn=CRMAnalytics\";i:1;s:19:\"cn=CRMAdministrator\";i:2;s:24:\"cn=CRMConferenceServices\";i:3;s:25:\"cn=CRMOfficeAdministrator\";i:4;s:21:\"cn=CRMOfficeDataEntry\";i:5;s:19:\"cn=CRMOfficeManager\";i:6;s:17:\"cn=CRMOfficeStaff\";i:7;s:21:\"cn=CRMOfficeVolunteer\";i:8;s:21:\"cn=CRMPrintProduction\";i:9;s:9:\"cn=CRMSOS\";}','member','a:10:{s:15:\"cn=CRMAnalytics\";s:14:\"Analytics User\";s:19:\"cn=CRMAdministrator\";s:13:\"Administrator\";s:24:\"cn=CRMConferenceServices\";s:19:\"Conference Services\";s:25:\"cn=CRMOfficeAdministrator\";s:20:\"Office Administrator\";s:21:\"cn=CRMOfficeDataEntry\";s:10:\"Data Entry\";s:19:\"cn=CRMOfficeManager\";s:14:\"Office Manager\";s:17:\"cn=CRMOfficeStaff\";s:5:\"Staff\";s:21:\"cn=CRMOfficeVolunteer\";s:9:\"Volunteer\";s:21:\"cn=CRMPrintProduction\";s:16:\"Print Production\";s:9:\"cn=CRMSOS\";s:3:\"SOS\";}',1,'','a:5:{i:0;s:15:\"CN=CRMAnalytics\";i:1;s:19:\"CN=CRMAdministrator\";i:2;s:24:\"CN=CRMConferenceServices\";i:3;s:21:\"CN=CRMPrintProduction\";i:4;s:9:\"CN=CRMSOS\";}');
/*!40000 ALTER TABLE `ldapauth` ENABLE KEYS */;
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
INSERT INTO `menu_custom` VALUES ('navigation','Navigation','The navigation menu is provided by Drupal and is the main interactive menu for any site. It is usually the only menu that contains personalized links for authenticated users, and is often not even visible to anonymous users.'),('primary-links','Primary links','Primary links are often used at the theme layer to show the major sections of a site. A typical representation for primary links would be tabs along the top.'),('secondary-links','Secondary links','Secondary links are often used for pages like legal notices, contact details, and other secondary navigation items that play a lesser role than primary links');
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
  `options` text,
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
) ENGINE=InnoDB AUTO_INCREMENT=337 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_links`
--

LOCK TABLES `menu_links` WRITE;
/*!40000 ALTER TABLE `menu_links` DISABLE KEYS */;
INSERT INTO `menu_links` VALUES ('navigation',1,0,'batch','batch','','a:0:{}','system',-1,0,0,0,0,1,0,1,0,0,0,0,0,0,0,0,0),('navigation',2,0,'admin','admin','Administer','a:0:{}','system',0,0,1,0,9,1,0,2,0,0,0,0,0,0,0,0,0),('navigation',3,0,'node','node','Content','a:0:{}','system',-1,0,0,0,0,1,0,3,0,0,0,0,0,0,0,0,0),('navigation',4,0,'logout','logout','Log out','a:0:{}','system',0,0,0,0,10,1,0,4,0,0,0,0,0,0,0,0,0),('navigation',5,0,'rss.xml','rss.xml','RSS feed','a:0:{}','system',-1,0,0,0,0,1,0,5,0,0,0,0,0,0,0,0,0),('navigation',6,0,'user','user','User account','a:0:{}','system',-1,0,0,0,0,1,0,6,0,0,0,0,0,0,0,0,0),('navigation',7,0,'node/%','node/%','','a:0:{}','system',-1,0,0,0,0,1,0,7,0,0,0,0,0,0,0,0,0),('navigation',8,2,'admin/compact','admin/compact','Compact mode','a:0:{}','system',-1,0,0,0,0,2,0,2,8,0,0,0,0,0,0,0,0),('navigation',9,0,'filter/tips','filter/tips','Compose tips','a:0:{}','system',1,0,0,0,0,1,0,9,0,0,0,0,0,0,0,0,0),('navigation',10,2,'admin/content','admin/content','Content management','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:27:\"Manage your site\'s content.\";}}','system',0,0,1,0,-10,2,0,2,10,0,0,0,0,0,0,0,0),('navigation',11,0,'node/add','node/add','Create content','a:0:{}','system',0,0,1,0,1,1,0,11,0,0,0,0,0,0,0,0,0),('navigation',12,0,'comment/delete','comment/delete','Delete comment','a:0:{}','system',-1,0,0,0,0,1,0,12,0,0,0,0,0,0,0,0,0),('navigation',13,0,'comment/edit','comment/edit','Edit comment','a:0:{}','system',-1,0,0,0,0,1,0,13,0,0,0,0,0,0,0,0,0),('navigation',14,0,'system/files','system/files','File download','a:0:{}','system',-1,0,0,0,0,1,0,14,0,0,0,0,0,0,0,0,0),('navigation',15,2,'admin/help','admin/help','Help','a:0:{}','system',0,0,0,0,9,2,0,2,15,0,0,0,0,0,0,0,0),('navigation',16,2,'admin/reports','admin/reports','Reports','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:59:\"View reports from system logs and other status information.\";}}','system',0,0,1,0,5,2,0,2,16,0,0,0,0,0,0,0,0),('navigation',17,2,'admin/build','admin/build','Site building','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:38:\"Control how your site looks and feels.\";}}','system',0,0,1,0,-10,2,0,2,17,0,0,0,0,0,0,0,0),('navigation',18,2,'admin/settings','admin/settings','Site configuration','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:40:\"Adjust basic site configuration options.\";}}','system',0,0,1,0,-5,2,0,2,18,0,0,0,0,0,0,0,0),('navigation',19,0,'user/autocomplete','user/autocomplete','User autocomplete','a:0:{}','system',-1,0,0,0,0,1,0,19,0,0,0,0,0,0,0,0,0),('navigation',20,2,'admin/user','admin/user','User management','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:61:\"Manage your site\'s users, groups and access to site features.\";}}','system',0,0,1,0,0,2,0,2,20,0,0,0,0,0,0,0,0),('navigation',21,0,'user/%','user/%','My account','a:0:{}','system',0,0,0,0,0,1,0,21,0,0,0,0,0,0,0,0,0),('navigation',22,20,'admin/user/rules','admin/user/rules','Access rules','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:80:\"List and create rules to disallow usernames, e-mail addresses, and IP addresses.\";}}','system',0,0,0,0,0,3,0,2,20,22,0,0,0,0,0,0,0),('navigation',23,18,'admin/settings/actions','admin/settings/actions','Actions','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:41:\"Manage the actions defined for your site.\";}}','system',0,0,0,0,0,3,0,2,18,23,0,0,0,0,0,0,0),('navigation',24,18,'admin/settings/admin','admin/settings/admin','Administration theme','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:55:\"Settings for how your administrative pages should look.\";}}','system',0,0,0,0,0,3,0,2,18,24,0,0,0,0,0,0,0),('navigation',25,17,'admin/build/block','admin/build/block','Blocks','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:79:\"Configure what block content appears in your site\'s sidebars and other regions.\";}}','system',0,0,0,0,0,3,0,2,17,25,0,0,0,0,0,0,0),('navigation',26,18,'admin/settings/clean-urls','admin/settings/clean-urls','Clean URLs','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:43:\"Enable or disable clean URLs for your site.\";}}','system',0,0,0,0,0,3,0,2,18,26,0,0,0,0,0,0,0),('navigation',27,10,'admin/content/comment','admin/content/comment','Comments','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:61:\"List and edit site comments and the comment moderation queue.\";}}','system',0,0,0,0,0,3,0,2,10,27,0,0,0,0,0,0,0),('navigation',28,10,'admin/content/node','admin/content/node','Content','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:43:\"View, edit, and delete your site\'s content.\";}}','system',0,0,0,0,0,3,0,2,10,28,0,0,0,0,0,0,0),('navigation',29,10,'admin/content/types','admin/content/types','Content types','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:82:\"Manage posts by content type, including default status, front page promotion, etc.\";}}','system',0,0,0,0,0,3,0,2,10,29,0,0,0,0,0,0,0),('navigation',30,18,'admin/settings/date-time','admin/settings/date-time','Date and time','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:89:\"Settings for how Drupal displays date and time, as well as the system\'s default timezone.\";}}','system',0,0,0,0,0,3,0,2,18,30,0,0,0,0,0,0,0),('navigation',31,0,'node/%/delete','node/%/delete','Delete','a:0:{}','system',-1,0,0,0,1,1,0,31,0,0,0,0,0,0,0,0,0),('navigation',32,21,'user/%/delete','user/%/delete','Delete','a:0:{}','system',-1,0,0,0,0,2,0,21,32,0,0,0,0,0,0,0,0),('navigation',33,18,'admin/settings/error-reporting','admin/settings/error-reporting','Error reporting','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:93:\"Control how Drupal deals with errors including 403/404 errors as well as PHP error reporting.\";}}','system',0,0,0,0,0,3,0,2,18,33,0,0,0,0,0,0,0),('navigation',34,18,'admin/settings/file-system','admin/settings/file-system','File system','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:68:\"Tell Drupal where to store uploaded files and how they are accessed.\";}}','system',0,0,0,0,0,3,0,2,18,34,0,0,0,0,0,0,0),('navigation',35,18,'admin/settings/image-toolkit','admin/settings/image-toolkit','Image toolkit','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:74:\"Choose which image toolkit to use if you have installed optional toolkits.\";}}','system',0,0,0,0,0,3,0,2,18,35,0,0,0,0,0,0,0),('navigation',36,18,'admin/settings/filters','admin/settings/filters','Input formats','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:127:\"Configure how content input by users is filtered, including allowed HTML tags. Also allows enabling of module-provided filters.\";}}','system',0,0,0,0,0,3,0,2,18,36,0,0,0,0,0,0,0),('navigation',37,18,'admin/settings/logging','admin/settings/logging','Logging and alerts','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:156:\"Settings for logging and alerts modules. Various modules can route Drupal\'s system events to different destination, such as syslog, database, email, ...etc.\";}}','system',0,0,1,0,0,3,0,2,18,37,0,0,0,0,0,0,0),('navigation',38,17,'admin/build/menu','admin/build/menu','Menus','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:116:\"Control your site\'s navigation menu, primary links and secondary links. as well as rename and reorganize menu items.\";}}','system',0,0,1,0,0,3,0,2,17,38,0,0,0,0,0,0,0),('navigation',39,17,'admin/build/modules','admin/build/modules','Modules','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:47:\"Enable or disable add-on modules for your site.\";}}','system',0,0,0,0,0,3,0,2,17,39,0,0,0,0,0,0,0),('navigation',40,18,'admin/settings/performance','admin/settings/performance','Performance','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:101:\"Enable or disable page caching for anonymous users and set CSS and JS bandwidth optimization options.\";}}','system',0,0,0,0,0,3,0,2,18,40,0,0,0,0,0,0,0),('navigation',41,20,'admin/user/permissions','admin/user/permissions','Permissions','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:64:\"Determine access to features by selecting permissions for roles.\";}}','system',0,0,0,0,0,3,0,2,20,41,0,0,0,0,0,0,0),('navigation',42,10,'admin/content/node-settings','admin/content/node-settings','Post settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:126:\"Control posting behavior, such as teaser length, requiring previews before posting, and the number of posts on the front page.\";}}','system',0,0,0,0,0,3,0,2,10,42,0,0,0,0,0,0,0),('navigation',43,10,'admin/content/rss-publishing','admin/content/rss-publishing','RSS publishing','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:92:\"Configure the number of items per feed and whether feeds should be titles/teasers/full-text.\";}}','system',0,0,0,0,0,3,0,2,10,43,0,0,0,0,0,0,0),('navigation',44,0,'comment/reply/%','comment/reply/%','Reply to comment','a:0:{}','system',-1,0,0,0,0,1,0,44,0,0,0,0,0,0,0,0,0),('navigation',45,20,'admin/user/roles','admin/user/roles','Roles','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:30:\"List, edit, or add user roles.\";}}','system',0,0,0,0,0,3,0,2,20,45,0,0,0,0,0,0,0),('navigation',46,18,'admin/settings/site-information','admin/settings/site-information','Site information','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:107:\"Change basic site information, such as the site name, slogan, e-mail address, mission, front page and more.\";}}','system',0,0,0,0,0,3,0,2,18,46,0,0,0,0,0,0,0),('navigation',47,18,'admin/settings/site-maintenance','admin/settings/site-maintenance','Site maintenance','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:63:\"Take the site off-line for maintenance or bring it back online.\";}}','system',0,0,0,0,0,3,0,2,18,47,0,0,0,0,0,0,0),('navigation',48,16,'admin/reports/status','admin/reports/status','Status report','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:74:\"Get a status report about your site\'s operation and any detected problems.\";}}','system',0,0,0,0,10,3,0,2,16,48,0,0,0,0,0,0,0),('navigation',49,17,'admin/build/themes','admin/build/themes','Themes','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:57:\"Change which theme your site uses or allows users to set.\";}}','system',0,0,0,0,0,3,0,2,17,49,0,0,0,0,0,0,0),('navigation',50,20,'admin/user/settings','admin/user/settings','User settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:101:\"Configure default behavior of users, including registration requirements, e-mails, and user pictures.\";}}','system',0,0,0,0,0,3,0,2,20,50,0,0,0,0,0,0,0),('navigation',51,20,'admin/user/user','admin/user/user','Users','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:26:\"List, add, and edit users.\";}}','system',0,0,0,0,0,3,0,2,20,51,0,0,0,0,0,0,0),('navigation',52,15,'admin/help/block','admin/help/block','block','a:0:{}','system',-1,0,0,0,0,3,0,2,15,52,0,0,0,0,0,0,0),('navigation',53,15,'admin/help/color','admin/help/color','color','a:0:{}','system',-1,0,0,0,0,3,0,2,15,53,0,0,0,0,0,0,0),('navigation',54,15,'admin/help/comment','admin/help/comment','comment','a:0:{}','system',-1,0,0,0,0,3,0,2,15,54,0,0,0,0,0,0,0),('navigation',55,15,'admin/help/filter','admin/help/filter','filter','a:0:{}','system',-1,0,0,0,0,3,0,2,15,55,0,0,0,0,0,0,0),('navigation',56,15,'admin/help/help','admin/help/help','help','a:0:{}','system',-1,0,0,0,0,3,0,2,15,56,0,0,0,0,0,0,0),('navigation',57,15,'admin/help/menu','admin/help/menu','menu','a:0:{}','system',-1,0,0,0,0,3,0,2,15,57,0,0,0,0,0,0,0),('navigation',58,15,'admin/help/node','admin/help/node','node','a:0:{}','system',-1,0,0,0,0,3,0,2,15,58,0,0,0,0,0,0,0),('navigation',59,15,'admin/help/system','admin/help/system','system','a:0:{}','system',-1,0,0,0,0,3,0,2,15,59,0,0,0,0,0,0,0),('navigation',60,15,'admin/help/user','admin/help/user','user','a:0:{}','system',-1,0,0,0,0,3,0,2,15,60,0,0,0,0,0,0,0),('navigation',61,36,'admin/settings/filters/%','admin/settings/filters/%','','a:0:{}','system',-1,0,0,0,0,4,0,2,18,36,61,0,0,0,0,0,0),('navigation',62,26,'admin/settings/clean-urls/check','admin/settings/clean-urls/check','Clean URL check','a:0:{}','system',-1,0,0,0,0,4,0,2,18,26,62,0,0,0,0,0,0),('navigation',63,23,'admin/settings/actions/configure','admin/settings/actions/configure','Configure an advanced action','a:0:{}','system',-1,0,0,0,0,4,0,2,18,23,63,0,0,0,0,0,0),('navigation',64,25,'admin/build/block/configure','admin/build/block/configure','Configure block','a:0:{}','system',-1,0,0,0,0,4,0,2,17,25,64,0,0,0,0,0,0),('navigation',65,17,'admin/build/menu-customize/%','admin/build/menu-customize/%','Customize menu','a:0:{}','system',-1,0,0,0,0,3,0,2,17,65,0,0,0,0,0,0,0),('navigation',66,30,'admin/settings/date-time/lookup','admin/settings/date-time/lookup','Date and time lookup','a:0:{}','system',-1,0,0,0,0,4,0,2,18,30,66,0,0,0,0,0,0),('navigation',67,25,'admin/build/block/delete','admin/build/block/delete','Delete block','a:0:{}','system',-1,0,0,0,0,4,0,2,17,25,67,0,0,0,0,0,0),('navigation',68,36,'admin/settings/filters/delete','admin/settings/filters/delete','Delete input format','a:0:{}','system',-1,0,0,0,0,4,0,2,18,36,68,0,0,0,0,0,0),('navigation',69,22,'admin/user/rules/delete','admin/user/rules/delete','Delete rule','a:0:{}','system',-1,0,0,0,0,4,0,2,20,22,69,0,0,0,0,0,0),('navigation',70,45,'admin/user/roles/edit','admin/user/roles/edit','Edit role','a:0:{}','system',-1,0,0,0,0,4,0,2,20,45,70,0,0,0,0,0,0),('navigation',71,22,'admin/user/rules/edit','admin/user/rules/edit','Edit rule','a:0:{}','system',-1,0,0,0,0,4,0,2,20,22,71,0,0,0,0,0,0),('navigation',72,48,'admin/reports/status/php','admin/reports/status/php','PHP','a:0:{}','system',-1,0,0,0,0,4,0,2,16,48,72,0,0,0,0,0,0),('navigation',73,42,'admin/content/node-settings/rebuild','admin/content/node-settings/rebuild','Rebuild permissions','a:0:{}','system',-1,0,0,0,0,4,0,2,10,42,73,0,0,0,0,0,0),('navigation',74,23,'admin/settings/actions/orphan','admin/settings/actions/orphan','Remove orphans','a:0:{}','system',-1,0,0,0,0,4,0,2,18,23,74,0,0,0,0,0,0),('navigation',75,48,'admin/reports/status/run-cron','admin/reports/status/run-cron','Run cron','a:0:{}','system',-1,0,0,0,0,4,0,2,16,48,75,0,0,0,0,0,0),('navigation',76,48,'admin/reports/status/sql','admin/reports/status/sql','SQL','a:0:{}','system',-1,0,0,0,0,4,0,2,16,48,76,0,0,0,0,0,0),('navigation',77,23,'admin/settings/actions/delete/%','admin/settings/actions/delete/%','Delete action','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:17:\"Delete an action.\";}}','system',-1,0,0,0,0,4,0,2,18,23,77,0,0,0,0,0,0),('navigation',78,0,'admin/build/menu-customize/%/delete','admin/build/menu-customize/%/delete','Delete menu','a:0:{}','system',-1,0,0,0,0,1,0,78,0,0,0,0,0,0,0,0,0),('navigation',79,25,'admin/build/block/list/js','admin/build/block/list/js','JavaScript List Form','a:0:{}','system',-1,0,0,0,0,4,0,2,17,25,79,0,0,0,0,0,0),('navigation',80,39,'admin/build/modules/list/confirm','admin/build/modules/list/confirm','List','a:0:{}','system',-1,0,0,0,0,4,0,2,17,39,80,0,0,0,0,0,0),('navigation',81,0,'user/reset/%/%/%','user/reset/%/%/%','Reset password','a:0:{}','system',-1,0,0,0,0,1,0,81,0,0,0,0,0,0,0,0,0),('navigation',82,39,'admin/build/modules/uninstall/confirm','admin/build/modules/uninstall/confirm','Uninstall','a:0:{}','system',-1,0,0,0,0,4,0,2,17,39,82,0,0,0,0,0,0),('navigation',83,0,'node/%/revisions/%/delete','node/%/revisions/%/delete','Delete earlier revision','a:0:{}','system',-1,0,0,0,0,1,0,83,0,0,0,0,0,0,0,0,0),('navigation',84,0,'node/%/revisions/%/revert','node/%/revisions/%/revert','Revert to earlier revision','a:0:{}','system',-1,0,0,0,0,1,0,84,0,0,0,0,0,0,0,0,0),('navigation',85,0,'node/%/revisions/%/view','node/%/revisions/%/view','Revisions','a:0:{}','system',-1,0,0,0,0,1,0,85,0,0,0,0,0,0,0,0,0),('navigation',86,38,'admin/build/menu/item/%/delete','admin/build/menu/item/%/delete','Delete menu item','a:0:{}','system',-1,0,0,0,0,4,0,2,17,38,86,0,0,0,0,0,0),('navigation',87,38,'admin/build/menu/item/%/edit','admin/build/menu/item/%/edit','Edit menu item','a:0:{}','system',-1,0,0,0,0,4,0,2,17,38,87,0,0,0,0,0,0),('navigation',88,38,'admin/build/menu/item/%/reset','admin/build/menu/item/%/reset','Reset menu item','a:0:{}','system',-1,0,0,0,0,4,0,2,17,38,88,0,0,0,0,0,0),('navigation',89,38,'admin/build/menu-customize/navigation','admin/build/menu-customize/%','Navigation','a:0:{}','menu',0,0,0,0,0,4,0,2,17,38,89,0,0,0,0,0,0),('navigation',90,38,'admin/build/menu-customize/primary-links','admin/build/menu-customize/%','Primary links','a:0:{}','menu',0,0,0,0,0,4,0,2,17,38,90,0,0,0,0,0,0),('navigation',91,38,'admin/build/menu-customize/secondary-links','admin/build/menu-customize/%','Secondary links','a:0:{}','menu',0,0,0,0,0,4,0,2,17,38,91,0,0,0,0,0,0),('navigation',92,0,'taxonomy/autocomplete','taxonomy/autocomplete','Autocomplete taxonomy','a:0:{}','system',-1,0,0,0,0,1,0,92,0,0,0,0,0,0,0,0,0),('navigation',93,16,'admin/reports/dblog','admin/reports/dblog','Recent log entries','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:43:\"View events that have recently been logged.\";}}','system',0,0,0,0,-1,3,0,2,16,93,0,0,0,0,0,0,0),('navigation',94,10,'admin/content/taxonomy','admin/content/taxonomy','Taxonomy','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:67:\"Manage tagging, categorization, and classification of your content.\";}}','system',0,0,0,0,0,3,0,2,10,94,0,0,0,0,0,0,0),('navigation',95,0,'taxonomy/term/%','taxonomy/term/%','Taxonomy term','a:0:{}','system',-1,0,0,0,0,1,0,95,0,0,0,0,0,0,0,0,0),('navigation',96,16,'admin/reports/access-denied','admin/reports/access-denied','Top \'access denied\' errors','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:35:\"View \'access denied\' errors (403s).\";}}','system',0,0,0,0,0,3,0,2,16,96,0,0,0,0,0,0,0),('navigation',97,16,'admin/reports/page-not-found','admin/reports/page-not-found','Top \'page not found\' errors','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:36:\"View \'page not found\' errors (404s).\";}}','system',0,0,0,0,0,3,0,2,16,97,0,0,0,0,0,0,0),('navigation',98,15,'admin/help/dblog','admin/help/dblog','dblog','a:0:{}','system',-1,0,0,0,0,3,0,2,15,98,0,0,0,0,0,0,0),('navigation',99,15,'admin/help/taxonomy','admin/help/taxonomy','taxonomy','a:0:{}','system',-1,0,0,0,0,3,0,2,15,99,0,0,0,0,0,0,0),('navigation',100,37,'admin/settings/logging/dblog','admin/settings/logging/dblog','Database logging','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:169:\"Settings for logging to the Drupal database logs. This is the most common method for small to medium sites on shared hosting. The logs are viewable from the admin pages.\";}}','system',0,0,0,0,0,4,0,2,18,37,100,0,0,0,0,0,0),('navigation',101,16,'admin/reports/event/%','admin/reports/event/%','Details','a:0:{}','system',-1,0,0,0,0,3,0,2,16,101,0,0,0,0,0,0,0),('navigation',102,94,'admin/content/taxonomy/%','admin/content/taxonomy/%','List terms','a:0:{}','system',-1,0,0,0,0,4,0,2,10,94,102,0,0,0,0,0,0),('navigation',103,94,'admin/content/taxonomy/edit/term','admin/content/taxonomy/edit/term','Edit term','a:0:{}','system',-1,0,0,0,0,4,0,2,10,94,103,0,0,0,0,0,0),('navigation',104,94,'admin/content/taxonomy/edit/vocabulary/%','admin/content/taxonomy/edit/vocabulary/%','Edit vocabulary','a:0:{}','system',-1,0,0,0,0,4,0,2,10,94,104,0,0,0,0,0,0),('navigation',105,16,'admin/reports/updates','admin/reports/updates','Available updates','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:82:\"Get a status report about available updates for your installed modules and themes.\";}}','system',0,0,0,0,10,3,0,2,16,105,0,0,0,0,0,0,0),('navigation',106,11,'node/add/page','node/add/page','Page','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:296:\"A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.\";}}','system',0,0,0,0,0,2,0,11,106,0,0,0,0,0,0,0,0),('navigation',107,11,'node/add/story','node/add/story','Story','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:392:\"A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.\";}}','system',0,0,0,0,0,2,0,11,107,0,0,0,0,0,0,0,0),('navigation',108,15,'admin/help/update','admin/help/update','update','a:0:{}','system',-1,0,0,0,0,3,0,2,15,108,0,0,0,0,0,0,0),('navigation',109,105,'admin/reports/updates/check','admin/reports/updates/check','Manual update check','a:0:{}','system',-1,0,0,0,0,4,0,2,16,105,109,0,0,0,0,0,0),('navigation',110,10,'admin/content/node-type/page','admin/content/node-type/page','Page','a:0:{}','system',-1,0,0,0,0,3,0,2,10,110,0,0,0,0,0,0,0),('navigation',111,10,'admin/content/node-type/story','admin/content/node-type/story','Story','a:0:{}','system',-1,0,0,0,0,3,0,2,10,111,0,0,0,0,0,0,0),('navigation',112,0,'admin/content/node-type/page/delete','admin/content/node-type/page/delete','Delete','a:0:{}','system',-1,0,0,0,0,1,0,112,0,0,0,0,0,0,0,0,0),('navigation',113,0,'admin/content/node-type/story/delete','admin/content/node-type/story/delete','Delete','a:0:{}','system',-1,0,0,0,0,1,0,113,0,0,0,0,0,0,0,0,0),('navigation',114,0,'search','search','Search','a:0:{}','system',1,0,0,0,0,1,0,114,0,0,0,0,0,0,0,0,0),('navigation',115,18,'admin/settings/apachesolr','admin/settings/apachesolr','Apache Solr','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:23:\"Administer Apache Solr.\";}}','system',0,0,0,0,0,3,0,2,18,115,0,0,0,0,0,0,0),('navigation',116,16,'admin/reports/apachesolr','admin/reports/apachesolr','Apache Solr search index','a:0:{}','system',0,0,0,0,0,3,0,2,16,116,0,0,0,0,0,0,0),('navigation',117,18,'admin/settings/search','admin/settings/search','Search settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:66:\"Configure relevance settings for search and other indexing options\";}}','system',0,0,0,0,0,3,0,2,18,117,0,0,0,0,0,0,0),('navigation',118,16,'admin/reports/search','admin/reports/search','Top search phrases','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:33:\"View most popular search phrases.\";}}','system',0,0,0,0,0,3,0,2,16,118,0,0,0,0,0,0,0),('navigation',119,15,'admin/help/apachesolr_search','admin/help/apachesolr_search','apachesolr_search','a:0:{}','system',-1,0,0,0,0,3,0,2,15,119,0,0,0,0,0,0,0),('navigation',120,15,'admin/help/search','admin/help/search','search','a:0:{}','system',-1,0,0,0,0,3,0,2,15,120,0,0,0,0,0,0,0),('navigation',121,117,'admin/settings/search/wipe','admin/settings/search/wipe','Clear index','a:0:{}','system',-1,0,0,0,0,4,0,2,18,117,121,0,0,0,0,0,0),('navigation',122,115,'admin/settings/apachesolr/mlt/add_block','admin/settings/apachesolr/mlt/add_block','','a:0:{}','system',-1,0,0,0,0,4,0,2,18,115,122,0,0,0,0,0,0),('navigation',123,115,'admin/settings/apachesolr/index/delete/confirm','admin/settings/apachesolr/index/delete/confirm','Confirm index deletion','a:0:{}','system',-1,0,0,0,0,4,0,2,18,115,123,0,0,0,0,0,0),('navigation',124,115,'admin/settings/apachesolr/mlt/delete_block/%','admin/settings/apachesolr/mlt/delete_block/%','','a:0:{}','system',-1,0,0,0,0,4,0,2,18,115,124,0,0,0,0,0,0),('navigation',125,115,'admin/settings/apachesolr/index/clear/confirm','admin/settings/apachesolr/index/clear/confirm','Confirm the re-indexing of all content','a:0:{}','system',-1,0,0,0,0,4,0,2,18,115,125,0,0,0,0,0,0),('navigation',126,114,'search/node/%','search/node/%','Search','a:0:{}','system',-1,0,0,0,0,2,0,114,126,0,0,0,0,0,0,0,0),('navigation',127,0,'0','','','a:0:{}','system',0,1,0,0,0,1,0,127,0,0,0,0,0,0,0,0,0),('admin_menu',141,0,'<front>','','<img class=\"admin-menu-icon\" src=\"/nyss/misc/favicon.ico\" width=\"16\" height=\"16\" alt=\"Home\" />','a:3:{s:11:\"extra class\";s:15:\"admin-menu-icon\";s:4:\"html\";b:1;s:5:\"alter\";b:1;}','admin_menu',0,1,1,0,-100,1,0,141,0,0,0,0,0,0,0,0,0),('admin_menu',142,0,'logout','logout','Log out @username','a:3:{s:11:\"extra class\";s:35:\"admin-menu-action admin-menu-logout\";s:1:\"t\";a:0:{}s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-100,1,0,142,0,0,0,0,0,0,0,0,0),('admin_menu',143,0,'user','user','icon_users','a:3:{s:11:\"extra class\";s:50:\"admin-menu-action admin-menu-icon admin-menu-users\";s:4:\"html\";b:1;s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-90,1,0,143,0,0,0,0,0,0,0,0,0),('admin_menu',144,0,'admin/content','admin/content','Content management','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,-10,1,0,144,0,0,0,0,0,0,0,0,0),('admin_menu',145,0,'admin/help','admin/help','Help','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,9,1,0,145,0,0,0,0,0,0,0,0,0),('admin_menu',146,0,'admin/reports','admin/reports','Reports','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,5,1,0,146,0,0,0,0,0,0,0,0,0),('admin_menu',147,0,'admin/build','admin/build','Site building','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,-10,1,0,147,0,0,0,0,0,0,0,0,0),('admin_menu',148,0,'admin/settings','admin/settings','Site configuration','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,-5,1,0,148,0,0,0,0,0,0,0,0,0),('admin_menu',149,0,'admin/user','admin/user','User management','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,1,0,149,0,0,0,0,0,0,0,0,0),('admin_menu',150,149,'admin/user/rules','admin/user/rules','Access rules','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,149,150,0,0,0,0,0,0,0,0),('admin_menu',151,148,'admin/settings/actions','admin/settings/actions','Actions','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,148,151,0,0,0,0,0,0,0,0),('admin_menu',153,148,'admin/settings/admin','admin/settings/admin','Administration theme','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,153,0,0,0,0,0,0,0,0),('admin_menu',154,148,'admin/settings/apachesolr','admin/settings/apachesolr','Apache Solr','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,148,154,0,0,0,0,0,0,0,0),('admin_menu',155,146,'admin/reports/apachesolr','admin/reports/apachesolr','Apache Solr search index','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,146,155,0,0,0,0,0,0,0,0),('admin_menu',156,146,'admin/reports/updates','admin/reports/updates','Available updates','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,10,2,0,146,156,0,0,0,0,0,0,0,0),('admin_menu',157,147,'admin/build/block','admin/build/block','Blocks','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,147,157,0,0,0,0,0,0,0,0),('admin_menu',158,148,'admin/settings/clean-urls','admin/settings/clean-urls','Clean URLs','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,158,0,0,0,0,0,0,0,0),('admin_menu',159,144,'admin/content/comment','admin/content/comment','Comments','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,144,159,0,0,0,0,0,0,0,0),('admin_menu',160,144,'admin/content/node','admin/content/node','Content','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,144,160,0,0,0,0,0,0,0,0),('admin_menu',161,144,'admin/content/types','admin/content/types','Content types','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,144,161,0,0,0,0,0,0,0,0),('admin_menu',162,148,'admin/settings/date-time','admin/settings/date-time','Date and time','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,162,0,0,0,0,0,0,0,0),('admin_menu',163,148,'admin/settings/error-reporting','admin/settings/error-reporting','Error reporting','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,163,0,0,0,0,0,0,0,0),('admin_menu',164,148,'admin/settings/file-system','admin/settings/file-system','File system','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,164,0,0,0,0,0,0,0,0),('admin_menu',165,148,'admin/settings/image-toolkit','admin/settings/image-toolkit','Image toolkit','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,165,0,0,0,0,0,0,0,0),('admin_menu',166,148,'admin/settings/filters','admin/settings/filters','Input formats','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,148,166,0,0,0,0,0,0,0,0),('admin_menu',167,148,'admin/settings/logging','admin/settings/logging','Logging and alerts','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,148,167,0,0,0,0,0,0,0,0),('admin_menu',168,147,'admin/build/menu','admin/build/menu','Menus','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,147,168,0,0,0,0,0,0,0,0),('admin_menu',169,147,'admin/build/modules','admin/build/modules','Modules','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,147,169,0,0,0,0,0,0,0,0),('admin_menu',170,148,'admin/settings/performance','admin/settings/performance','Performance','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,170,0,0,0,0,0,0,0,0),('admin_menu',171,149,'admin/user/permissions','admin/user/permissions','Permissions','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,149,171,0,0,0,0,0,0,0,0),('admin_menu',172,144,'admin/content/node-settings','admin/content/node-settings','Post settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,144,172,0,0,0,0,0,0,0,0),('admin_menu',173,144,'admin/content/rss-publishing','admin/content/rss-publishing','RSS publishing','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,144,173,0,0,0,0,0,0,0,0),('admin_menu',174,146,'admin/reports/dblog','admin/reports/dblog','Recent log entries','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-1,2,0,146,174,0,0,0,0,0,0,0,0),('admin_menu',175,149,'admin/user/roles','admin/user/roles','Roles','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,149,175,0,0,0,0,0,0,0,0),('admin_menu',176,148,'admin/settings/search','admin/settings/search','Search settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,176,0,0,0,0,0,0,0,0),('admin_menu',177,148,'admin/settings/site-information','admin/settings/site-information','Site information','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,177,0,0,0,0,0,0,0,0),('admin_menu',178,148,'admin/settings/site-maintenance','admin/settings/site-maintenance','Site maintenance','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,178,0,0,0,0,0,0,0,0),('admin_menu',179,146,'admin/reports/status','admin/reports/status','Status report','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,10,2,0,146,179,0,0,0,0,0,0,0,0),('admin_menu',180,144,'admin/content/taxonomy','admin/content/taxonomy','Taxonomy','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,144,180,0,0,0,0,0,0,0,0),('admin_menu',181,147,'admin/build/themes','admin/build/themes','Themes','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,147,181,0,0,0,0,0,0,0,0),('admin_menu',182,146,'admin/reports/access-denied','admin/reports/access-denied','Top \'access denied\' errors','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,146,182,0,0,0,0,0,0,0,0),('admin_menu',183,146,'admin/reports/page-not-found','admin/reports/page-not-found','Top \'page not found\' errors','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,146,183,0,0,0,0,0,0,0,0),('admin_menu',184,146,'admin/reports/search','admin/reports/search','Top search phrases','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,146,184,0,0,0,0,0,0,0,0),('admin_menu',185,149,'admin/user/settings','admin/user/settings','User settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,149,185,0,0,0,0,0,0,0,0),('admin_menu',186,149,'admin/user/user','admin/user/user','Users','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,149,186,0,0,0,0,0,0,0,0),('admin_menu',187,157,'admin/build/block/add','admin/build/block/add','Add block','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,147,157,187,0,0,0,0,0,0,0),('admin_menu',188,161,'admin/content/types/add','admin/content/types/add','Add content type','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,161,188,0,0,0,0,0,0,0),('admin_menu',189,166,'admin/settings/filters/add','admin/settings/filters/add','Add input format','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,1,3,0,148,166,189,0,0,0,0,0,0,0),('admin_menu',190,168,'admin/build/menu/add','admin/build/menu/add','Add menu','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,147,168,190,0,0,0,0,0,0,0),('admin_menu',191,150,'admin/user/rules/add','admin/user/rules/add','Add rule','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,149,150,191,0,0,0,0,0,0,0),('admin_menu',192,186,'admin/user/user/create','admin/user/user/create','Add user','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,149,186,192,0,0,0,0,0,0,0),('admin_menu',193,159,'admin/content/comment/approval','admin/content/comment/approval','Approval queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,159,193,0,0,0,0,0,0,0),('admin_menu',194,150,'admin/user/rules/check','admin/user/rules/check','Check rules','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,149,150,194,0,0,0,0,0,0,0),('admin_menu',195,181,'admin/build/themes/settings','admin/build/themes/settings','Configure','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,3,0,147,181,195,0,0,0,0,0,0,0),('admin_menu',196,154,'admin/settings/apachesolr/content-bias','admin/settings/apachesolr/content-bias','Content bias settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,1,3,0,148,154,196,0,0,0,0,0,0,0),('admin_menu',197,167,'admin/settings/logging/dblog','admin/settings/logging/dblog','Database logging','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,148,167,197,0,0,0,0,0,0,0),('admin_menu',198,154,'admin/settings/apachesolr/enabled-filters','admin/settings/apachesolr/enabled-filters','Enabled filters','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-7,3,0,148,154,198,0,0,0,0,0,0,0),('admin_menu',199,157,'admin/build/block/list','admin/build/block/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,-10,3,0,147,157,199,0,0,0,0,0,0,0),('admin_menu',200,160,'admin/content/node/overview','admin/content/node/overview','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,3,0,144,160,200,0,0,0,0,0,0,0),('admin_menu',201,180,'admin/content/taxonomy/list','admin/content/taxonomy/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,3,0,144,180,201,0,0,0,0,0,0,0),('admin_menu',202,161,'admin/content/types/list','admin/content/types/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,3,0,144,161,202,0,0,0,0,0,0,0),('admin_menu',203,186,'admin/user/user/list','admin/user/user/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,3,0,149,186,203,0,0,0,0,0,0,0),('admin_menu',204,169,'admin/build/modules/list','admin/build/modules/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,147,169,204,0,0,0,0,0,0,0),('admin_menu',205,156,'admin/reports/updates/list','admin/reports/updates/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,146,156,205,0,0,0,0,0,0,0),('admin_menu',206,150,'admin/user/rules/list','admin/user/rules/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,3,0,149,150,206,0,0,0,0,0,0,0),('admin_menu',207,166,'admin/settings/filters/list','admin/settings/filters/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,148,166,207,0,0,0,0,0,0,0),('admin_menu',208,181,'admin/build/themes/select','admin/build/themes/select','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-1,3,0,147,181,208,0,0,0,0,0,0,0),('admin_menu',209,168,'admin/build/menu/list','admin/build/menu/list','List menus','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,3,0,147,168,209,0,0,0,0,0,0,0),('admin_menu',210,151,'admin/settings/actions/manage','admin/settings/actions/manage','Manage actions','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-2,3,0,148,151,210,0,0,0,0,0,0,0),('admin_menu',211,159,'admin/content/comment/new','admin/content/comment/new','Published comments','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,3,0,144,159,211,0,0,0,0,0,0,0),('admin_menu',212,154,'admin/settings/apachesolr/query-fields','admin/settings/apachesolr/query-fields','Search fields','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,1,3,0,148,154,212,0,0,0,0,0,0,0),('admin_menu',213,154,'admin/settings/apachesolr/index','admin/settings/apachesolr/index','Search index','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-8,3,0,148,154,213,0,0,0,0,0,0,0),('admin_menu',214,155,'admin/reports/apachesolr/index','admin/reports/apachesolr/index','Search index','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,146,155,214,0,0,0,0,0,0,0),('admin_menu',215,154,'admin/settings/apachesolr/settings','admin/settings/apachesolr/settings','Settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,3,0,148,154,215,0,0,0,0,0,0,0),('admin_menu',216,168,'admin/build/menu/settings','admin/build/menu/settings','Settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,5,3,0,147,168,216,0,0,0,0,0,0,0),('admin_menu',217,156,'admin/reports/updates/settings','admin/reports/updates/settings','Settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,146,156,217,0,0,0,0,0,0,0),('admin_menu',218,169,'admin/build/modules/uninstall','admin/build/modules/uninstall','Uninstall','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,147,169,218,0,0,0,0,0,0,0),('admin_menu',219,199,'admin/build/block/list/bluemarine','admin/build/block/list/bluemarine','Bluemarine','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,157,199,219,0,0,0,0,0,0),('admin_menu',220,195,'admin/build/themes/settings/bluemarine','admin/build/themes/settings/bluemarine','Bluemarine','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,181,195,220,0,0,0,0,0,0),('admin_menu',223,199,'admin/build/block/list/chameleon','admin/build/block/list/chameleon','Chameleon','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,157,199,223,0,0,0,0,0,0),('admin_menu',224,195,'admin/build/themes/settings/chameleon','admin/build/themes/settings/chameleon','Chameleon','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,181,195,224,0,0,0,0,0,0),('admin_menu',225,199,'admin/build/block/list/garland','admin/build/block/list/garland','Garland','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,157,199,225,0,0,0,0,0,0),('admin_menu',226,195,'admin/build/themes/settings/garland','admin/build/themes/settings/garland','Garland','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,181,195,226,0,0,0,0,0,0),('admin_menu',227,195,'admin/build/themes/settings/global','admin/build/themes/settings/global','Global settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-1,4,0,147,181,195,227,0,0,0,0,0,0),('admin_menu',228,199,'admin/build/block/list/marvin','admin/build/block/list/marvin','Marvin','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,157,199,228,0,0,0,0,0,0),('admin_menu',229,195,'admin/build/themes/settings/marvin','admin/build/themes/settings/marvin','Marvin','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,181,195,229,0,0,0,0,0,0),('admin_menu',230,199,'admin/build/block/list/minnelli','admin/build/block/list/minnelli','Minnelli','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,157,199,230,0,0,0,0,0,0),('admin_menu',231,195,'admin/build/themes/settings/minnelli','admin/build/themes/settings/minnelli','Minnelli','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,181,195,231,0,0,0,0,0,0),('admin_menu',232,199,'admin/build/block/list/pushbutton','admin/build/block/list/pushbutton','Pushbutton','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,157,199,232,0,0,0,0,0,0),('admin_menu',233,195,'admin/build/themes/settings/pushbutton','admin/build/themes/settings/pushbutton','Pushbutton','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,181,195,233,0,0,0,0,0,0),('admin_menu',234,180,'admin/content/taxonomy/add/vocabulary','admin/content/taxonomy/add/vocabulary','Add vocabulary','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,180,234,0,0,0,0,0,0,0),('admin_menu',235,141,'admin/reports/status/run-cron','admin/reports/status/run-cron','Run cron','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,50,2,0,141,235,0,0,0,0,0,0,0,0),('admin_menu',237,148,'admin/by-module','admin/by-module','By module','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,2,0,148,237,0,0,0,0,0,0,0,0),('admin_menu',238,141,'http://drupal.org','','Drupal.org','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,1,0,100,2,0,141,238,0,0,0,0,0,0,0,0),('admin_menu',239,238,'http://drupal.org/project/issues/drupal','','Drupal issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,-10,3,0,141,238,239,0,0,0,0,0,0,0),('admin_menu',240,238,'http://drupal.org/project/issues/admin_menu','','Administration menu issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,240,0,0,0,0,0,0,0),('admin_menu',241,238,'http://drupal.org/project/issues/apachesolr','','Apache Solr framework issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,241,0,0,0,0,0,0,0),('admin_menu',242,238,'http://drupal.org/project/issues/cacherouter','','CacheRouter issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,242,0,0,0,0,0,0,0),('admin_menu',243,144,'node/add','node/add','Create content','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,144,243,0,0,0,0,0,0,0,0),('admin_menu',244,243,'node/add/page','node/add/page','Page','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,243,244,0,0,0,0,0,0,0),('admin_menu',245,243,'node/add/story','node/add/story','Story','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,243,245,0,0,0,0,0,0,0),('admin_menu',246,161,'admin/content/node-type/page','admin/content/node-type/page','Edit !content-type','a:2:{s:1:\"t\";a:1:{s:13:\"!content-type\";s:4:\"Page\";}s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,161,246,0,0,0,0,0,0,0),('admin_menu',247,161,'admin/content/node-type/story','admin/content/node-type/story','Edit !content-type','a:2:{s:1:\"t\";a:1:{s:13:\"!content-type\";s:5:\"Story\";}s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,3,0,144,161,247,0,0,0,0,0,0,0),('admin_menu',256,199,'admin/build/block/list/rayCivicrm','admin/build/block/list/rayCivicrm','rayCivicrm','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,-10,4,0,147,157,199,256,0,0,0,0,0,0),('admin_menu',257,195,'admin/build/themes/settings/rayCivicrm','admin/build/themes/settings/rayCivicrm','rayCivicrm','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,147,181,195,257,0,0,0,0,0,0),('navigation',277,0,'front_page','front_page','','a:0:{}','system',1,0,0,0,0,1,0,277,0,0,0,0,0,0,0,0,0),('navigation',278,18,'admin/settings/front','admin/settings/front','Advanced front page settings','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:240:\"Specify a unique layout or splash page based on role type - override your HOME and breadcrumb links - display a custom mission style notice for users who haven\'t visited in a while - disable site and display a \'temporarily offline\' message.\";}}','system',0,0,0,0,0,3,0,2,18,278,0,0,0,0,0,0,0),('navigation',279,15,'admin/help/front_page','admin/help/front_page','front_page','a:0:{}','system',-1,0,0,0,0,3,0,2,15,279,0,0,0,0,0,0,0),('admin_menu',285,148,'admin/settings/front','admin/settings/front','Advanced front page settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,2,0,148,285,0,0,0,0,0,0,0,0),('admin_menu',287,238,'http://drupal.org/project/issues/front','','Front Page issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,287,0,0,0,0,0,0,0),('navigation',310,18,'admin/settings/ldap','admin/settings/ldap','LDAP','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:36:\"Configure LDAP integration settings.\";}}','system',0,0,1,0,0,3,0,2,18,310,0,0,0,0,0,0,0),('navigation',311,15,'admin/help/ldapauth','admin/help/ldapauth','ldapauth','a:0:{}','system',-1,0,0,0,0,3,0,2,15,311,0,0,0,0,0,0,0),('navigation',312,310,'admin/settings/ldap/ldapauth','admin/settings/ldap/ldapauth','Authentication','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:39:\"Configure LDAP authentication settings.\";}}','system',0,0,0,0,0,4,0,2,18,310,312,0,0,0,0,0,0),('navigation',313,312,'admin/settings/ldap/ldapauth/activate','admin/settings/ldap/ldapauth/activate','Activate LDAP Source','a:0:{}','system',-1,0,0,0,0,5,0,2,18,310,312,313,0,0,0,0,0),('navigation',314,312,'admin/settings/ldap/ldapauth/edit','admin/settings/ldap/ldapauth/edit','Configure LDAP Server','a:0:{}','system',-1,0,0,0,0,5,0,2,18,310,312,314,0,0,0,0,0),('navigation',315,312,'admin/settings/ldap/ldapauth/deactivate','admin/settings/ldap/ldapauth/deactivate','De-activate LDAP Source','a:0:{}','system',-1,0,0,0,0,5,0,2,18,310,312,315,0,0,0,0,0),('navigation',316,312,'admin/settings/ldap/ldapauth/delete','admin/settings/ldap/ldapauth/delete','Delete LDAP Server','a:0:{}','system',-1,0,0,0,0,5,0,2,18,310,312,316,0,0,0,0,0),('navigation',317,0,'admin/settings/ldap/ldapauth/edit/%/test','admin/settings/ldap/ldapauth/edit/%/test','Test LDAP Server','a:0:{}','system',-1,0,0,0,0,1,0,317,0,0,0,0,0,0,0,0,0),('admin_menu',318,148,'admin/settings/ldap','admin/settings/ldap','LDAP','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,2,0,148,318,0,0,0,0,0,0,0,0),('admin_menu',319,318,'admin/settings/ldap/ldapauth','admin/settings/ldap/ldapauth','Authentication','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,1,0,0,3,0,148,318,319,0,0,0,0,0,0,0),('admin_menu',320,319,'admin/settings/ldap/ldapauth/add','admin/settings/ldap/ldapauth/add','Add Server','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,2,4,0,148,318,319,320,0,0,0,0,0,0),('admin_menu',321,319,'admin/settings/ldap/ldapauth/list','admin/settings/ldap/ldapauth/list','List','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,1,4,0,148,318,319,321,0,0,0,0,0,0),('admin_menu',322,319,'admin/settings/ldap/ldapauth/configure','admin/settings/ldap/ldapauth/configure','Settings','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,0,0,0,0,4,0,148,318,319,322,0,0,0,0,0,0),('admin_menu',324,238,'http://drupal.org/project/issues/ldap_integration','','Authentication issue queue','a:1:{s:5:\"alter\";b:1;}','admin_menu',0,1,0,0,0,3,0,141,238,324,0,0,0,0,0,0,0),('navigation',326,310,'admin/settings/ldap/ldapgroups','admin/settings/ldap/ldapgroups','Groups','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:55:\"Configure LDAP groups to Drupal roles mapping settings.\";}}','system',0,0,0,0,0,4,0,2,18,310,326,0,0,0,0,0,0),('navigation',327,326,'admin/settings/ldap/ldapgroups/edit','admin/settings/ldap/ldapgroups/edit','Groups','a:0:{}','system',-1,0,0,0,0,5,0,2,18,310,326,327,0,0,0,0,0),('navigation',328,326,'admin/settings/ldap/ldapgroups/reset','admin/settings/ldap/ldapgroups/reset','Groups','a:0:{}','system',-1,0,0,0,1,5,0,2,18,310,326,328,0,0,0,0,0),('navigation',329,0,'civicrm','civicrm','CiviCRM','a:0:{}','system',-1,0,0,0,0,1,0,329,0,0,0,0,0,0,0,0,0),('navigation',330,15,'admin/help/civicrm','admin/help/civicrm','civicrm','a:0:{}','system',-1,0,0,0,0,3,0,2,15,330,0,0,0,0,0,0,0),('navigation',331,0,'civicrm/dashboard','civicrm','CiviCRM','a:1:{s:5:\"alter\";b:1;}','civicrm',0,0,0,0,0,1,0,331,0,0,0,0,0,0,0,0,0),('navigation',332,0,'userprotect/delete/%','userprotect/delete/%','Delete protected user','a:0:{}','system',-1,0,0,0,0,1,0,332,0,0,0,0,0,0,0,0,0),('navigation',333,20,'admin/user/userprotect','admin/user/userprotect','User Protect','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:63:\"Protect inidividual users and/or roles from editing operations.\";}}','system',0,0,0,0,0,3,0,2,20,333,0,0,0,0,0,0,0),('navigation',334,15,'admin/help/userprotect','admin/help/userprotect','userprotect','a:0:{}','system',-1,0,0,0,0,3,0,2,15,334,0,0,0,0,0,0,0),('navigation',335,20,'admin/user/roleassign','admin/user/roleassign','Role assign','a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:81:\"Allows site administrators to further delegate the task of managing user\'s roles.\";}}','system',0,0,0,0,0,3,0,2,20,335,0,0,0,0,0,0,0),('navigation',336,15,'admin/help/roleassign','admin/help/roleassign','roleassign','a:0:{}','system',-1,0,0,0,0,3,0,2,15,336,0,0,0,0,0,0,0);
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
  `load_functions` text NOT NULL,
  `to_arg_functions` text NOT NULL,
  `access_callback` varchar(255) NOT NULL DEFAULT '',
  `access_arguments` text,
  `page_callback` varchar(255) NOT NULL DEFAULT '',
  `page_arguments` text,
  `fit` int(11) NOT NULL DEFAULT '0',
  `number_parts` smallint(6) NOT NULL DEFAULT '0',
  `tab_parent` varchar(255) NOT NULL DEFAULT '',
  `tab_root` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_callback` varchar(255) NOT NULL DEFAULT '',
  `title_arguments` varchar(255) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `block_callback` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `position` varchar(255) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0',
  `file` mediumtext,
  PRIMARY KEY (`path`),
  KEY `fit` (`fit`),
  KEY `tab_parent` (`tab_parent`),
  KEY `tab_root_weight_title` (`tab_root`(64),`weight`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_router`
--

LOCK TABLES `menu_router` WRITE;
/*!40000 ALTER TABLE `menu_router` DISABLE KEYS */;
INSERT INTO `menu_router` VALUES ('admin','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_main_admin_page','a:0:{}',1,1,'','admin','Administer','t','',6,'','','',9,'modules/system/system.admin.inc'),('admin/build','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',3,2,'','admin/build','Site building','t','',6,'','Control how your site looks and feels.','right',-10,'modules/system/system.admin.inc'),('admin/build/block','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','block_admin_display','a:0:{}',7,3,'','admin/build/block','Blocks','t','',6,'','Configure what block content appears in your site\'s sidebars and other regions.','',0,'modules/block/block.admin.inc'),('admin/build/block/add','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:20:\"block_add_block_form\";}',15,4,'admin/build/block','admin/build/block','Add block','t','',128,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/configure','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:21:\"block_admin_configure\";}',15,4,'','admin/build/block/configure','Configure block','t','',4,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/delete','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','drupal_get_form','a:1:{i:0;s:16:\"block_box_delete\";}',15,4,'','admin/build/block/delete','Delete block','t','',4,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/list','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','block_admin_display','a:0:{}',15,4,'admin/build/block','admin/build/block','List','t','',136,'','','',-10,'modules/block/block.admin.inc'),('admin/build/block/list/bluemarine','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:33:\"themes/bluemarine/bluemarine.info\";s:4:\"name\";s:10:\"bluemarine\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:10:\"Bluemarine\";s:11:\"description\";s:66:\"Table-based multi-column theme with a marine and ash color scheme.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/bluemarine/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:27:\"themes/bluemarine/script.js\";}s:10:\"screenshot\";s:32:\"themes/bluemarine/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/bluemarine/style.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:10:\"bluemarine\";}',31,5,'admin/build/block/list','admin/build/block','Bluemarine','t','',128,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/list/chameleon','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":11:{s:8:\"filename\";s:31:\"themes/chameleon/chameleon.info\";s:4:\"name\";s:9:\"chameleon\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:32:\"themes/chameleon/chameleon.theme\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:12:{s:4:\"name\";s:9:\"Chameleon\";s:11:\"description\";s:42:\"Minimalist tabled theme with light colors.\";s:7:\"regions\";a:2:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";}s:8:\"features\";a:4:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:2:{s:9:\"style.css\";s:26:\"themes/chameleon/style.css\";s:10:\"common.css\";s:27:\"themes/chameleon/common.css\";}}s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"scripts\";a:1:{s:9:\"script.js\";s:26:\"themes/chameleon/script.js\";}s:10:\"screenshot\";s:31:\"themes/chameleon/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:2:{s:9:\"style.css\";s:26:\"themes/chameleon/style.css\";s:10:\"common.css\";s:27:\"themes/chameleon/common.css\";}}}}','block_admin_display','a:1:{i:0;s:9:\"chameleon\";}',31,5,'admin/build/block/list','admin/build/block','Chameleon','t','',128,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/list/garland','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:27:\"themes/garland/garland.info\";s:4:\"name\";s:7:\"garland\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:7:\"Garland\";s:11:\"description\";s:66:\"Tableless, recolorable, multi-column, fluid width theme (default).\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:24:\"themes/garland/script.js\";}s:10:\"screenshot\";s:29:\"themes/garland/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:7:\"garland\";}',31,5,'admin/build/block/list','admin/build/block','Garland','t','',128,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/list/js','','','user_access','a:1:{i:0;s:17:\"administer blocks\";}','block_admin_display_js','a:0:{}',31,5,'','admin/build/block/list/js','JavaScript List Form','t','',4,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/list/marvin','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:35:\"themes/chameleon/marvin/marvin.info\";s:4:\"name\";s:6:\"marvin\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:0:\"\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:6:\"Marvin\";s:11:\"description\";s:31:\"Boxy tabled theme in all grays.\";s:7:\"regions\";a:2:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";}s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:10:\"base theme\";s:9:\"chameleon\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:33:\"themes/chameleon/marvin/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:33:\"themes/chameleon/marvin/script.js\";}s:10:\"screenshot\";s:38:\"themes/chameleon/marvin/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:33:\"themes/chameleon/marvin/style.css\";}}s:10:\"base_theme\";s:9:\"chameleon\";}}','block_admin_display','a:1:{i:0;s:6:\"marvin\";}',31,5,'admin/build/block/list','admin/build/block','Marvin','t','',128,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/list/minnelli','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":13:{s:8:\"filename\";s:37:\"themes/garland/minnelli/minnelli.info\";s:4:\"name\";s:8:\"minnelli\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:14:{s:4:\"name\";s:8:\"Minnelli\";s:11:\"description\";s:56:\"Tableless, recolorable, multi-column, fixed width theme.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:10:\"base theme\";s:7:\"garland\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:12:\"minnelli.css\";s:36:\"themes/garland/minnelli/minnelli.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:33:\"themes/garland/minnelli/script.js\";}s:10:\"screenshot\";s:38:\"themes/garland/minnelli/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";s:6:\"engine\";s:11:\"phptemplate\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:12:\"minnelli.css\";s:36:\"themes/garland/minnelli/minnelli.css\";}}s:6:\"engine\";s:11:\"phptemplate\";s:10:\"base_theme\";s:7:\"garland\";}}','block_admin_display','a:1:{i:0;s:8:\"minnelli\";}',31,5,'admin/build/block/list','admin/build/block','Minnelli','t','',128,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/list/pushbutton','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:33:\"themes/pushbutton/pushbutton.info\";s:4:\"name\";s:10:\"pushbutton\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:10:\"Pushbutton\";s:11:\"description\";s:52:\"Tabled, multi-column theme in blue and orange tones.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/pushbutton/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:27:\"themes/pushbutton/script.js\";}s:10:\"screenshot\";s:32:\"themes/pushbutton/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/pushbutton/style.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:10:\"pushbutton\";}',31,5,'admin/build/block/list','admin/build/block','Pushbutton','t','',128,'','','',0,'modules/block/block.admin.inc'),('admin/build/block/list/rayCivicrm','','','_block_themes_access','a:1:{i:0;O:8:\"stdClass\":13:{s:8:\"filename\";s:47:\"sites/default/themes/rayCivicrm/rayCivicrm.info\";s:4:\"name\";s:10:\"rayCivicrm\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:10:\"rayCivicrm\";s:11:\"description\";s:53:\"Drupal base theme built with Blueprint CSS framework.\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:17:\"screen,projection\";a:5:{s:32:\"rayCivicrm/rayCivicrm/screen.css\";s:64:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/screen.css\";s:18:\"css/rayCivicrm.css\";s:50:\"sites/default/themes/rayCivicrm/css/rayCivicrm.css\";s:18:\"nyss_skin/skin.css\";s:50:\"sites/default/themes/rayCivicrm/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:57:\"sites/default/themes/rayCivicrm/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:45:\"sites/default/themes/rayCivicrm/css/style.css\";}s:5:\"print\";a:1:{s:31:\"rayCivicrm/rayCivicrm/print.css\";s:63:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/print.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:58:\"sites/default/themes/rayCivicrm/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:54:\"sites/default/themes/rayCivicrm/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:50:\"sites/default/themes/rayCivicrm/scripts/general.js\";}s:7:\"regions\";a:4:{s:4:\"left\";s:12:\"Left sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:9:{i:0;s:4:\"name\";i:1;s:6:\"slogan\";i:2;s:7:\"mission\";i:3;s:17:\"node_user_picture\";i:4;s:20:\"comment_user_picture\";i:5;s:6:\"search\";i:6;s:7:\"favicon\";i:7;s:13:\"primary_links\";i:8;s:15:\"secondary_links\";}s:7:\"version\";s:7:\"6.x-1.6\";s:7:\"project\";s:10:\"rayCivicrm\";s:9:\"datestamp\";s:10:\"1265139005\";s:10:\"screenshot\";s:46:\"sites/default/themes/rayCivicrm/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:2:{s:17:\"screen,projection\";a:5:{s:32:\"rayCivicrm/rayCivicrm/screen.css\";s:64:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/screen.css\";s:18:\"css/rayCivicrm.css\";s:50:\"sites/default/themes/rayCivicrm/css/rayCivicrm.css\";s:18:\"nyss_skin/skin.css\";s:50:\"sites/default/themes/rayCivicrm/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:57:\"sites/default/themes/rayCivicrm/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:45:\"sites/default/themes/rayCivicrm/css/style.css\";}s:5:\"print\";a:1:{s:31:\"rayCivicrm/rayCivicrm/print.css\";s:63:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/print.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:58:\"sites/default/themes/rayCivicrm/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:54:\"sites/default/themes/rayCivicrm/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:50:\"sites/default/themes/rayCivicrm/scripts/general.js\";}s:6:\"engine\";s:11:\"phptemplate\";}}','block_admin_display','a:1:{i:0;s:10:\"rayCivicrm\";}',31,5,'admin/build/block/list','admin/build/block','rayCivicrm','t','',136,'','','',-10,'modules/block/block.admin.inc'),('admin/build/menu','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','menu_overview_page','a:0:{}',7,3,'','admin/build/menu','Menus','t','',6,'','Control your site\'s navigation menu, primary links and secondary links. as well as rename and reorganize menu items.','',0,'modules/menu/menu.admin.inc'),('admin/build/menu-customize/%','a:1:{i:3;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:2:{i:0;s:18:\"menu_overview_form\";i:1;i:3;}',14,4,'','admin/build/menu-customize/%','Customize menu','menu_overview_title','a:1:{i:0;i:3;}',4,'','','',0,'modules/menu/menu.admin.inc'),('admin/build/menu-customize/%/add','a:1:{i:3;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:4:{i:0;s:14:\"menu_edit_item\";i:1;s:3:\"add\";i:2;N;i:3;i:3;}',29,5,'admin/build/menu-customize/%','admin/build/menu-customize/%','Add item','t','',128,'','','',0,'modules/menu/menu.admin.inc'),('admin/build/menu-customize/%/delete','a:1:{i:3;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','menu_delete_menu_page','a:1:{i:0;i:3;}',29,5,'','admin/build/menu-customize/%/delete','Delete menu','t','',4,'','','',0,'modules/menu/menu.admin.inc'),('admin/build/menu-customize/%/edit','a:1:{i:3;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:3:{i:0;s:14:\"menu_edit_menu\";i:1;s:4:\"edit\";i:2;i:3;}',29,5,'admin/build/menu-customize/%','admin/build/menu-customize/%','Edit menu','t','',128,'','','',0,'modules/menu/menu.admin.inc'),('admin/build/menu-customize/%/list','a:1:{i:3;s:9:\"menu_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:2:{i:0;s:18:\"menu_overview_form\";i:1;i:3;}',29,5,'admin/build/menu-customize/%','admin/build/menu-customize/%','List items','t','',136,'','','',-10,'modules/menu/menu.admin.inc'),('admin/build/menu/add','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:2:{i:0;s:14:\"menu_edit_menu\";i:1;s:3:\"add\";}',15,4,'admin/build/menu','admin/build/menu','Add menu','t','',128,'','','',0,'modules/menu/menu.admin.inc'),('admin/build/menu/item/%/delete','a:1:{i:4;s:14:\"menu_link_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','menu_item_delete_page','a:1:{i:0;i:4;}',61,6,'','admin/build/menu/item/%/delete','Delete menu item','t','',4,'','','',0,'modules/menu/menu.admin.inc'),('admin/build/menu/item/%/edit','a:1:{i:4;s:14:\"menu_link_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:4:{i:0;s:14:\"menu_edit_item\";i:1;s:4:\"edit\";i:2;i:4;i:3;N;}',61,6,'','admin/build/menu/item/%/edit','Edit menu item','t','',4,'','','',0,'modules/menu/menu.admin.inc'),('admin/build/menu/item/%/reset','a:1:{i:4;s:14:\"menu_link_load\";}','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:2:{i:0;s:23:\"menu_reset_item_confirm\";i:1;i:4;}',61,6,'','admin/build/menu/item/%/reset','Reset menu item','t','',4,'','','',0,'modules/menu/menu.admin.inc'),('admin/build/menu/list','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','menu_overview_page','a:0:{}',15,4,'admin/build/menu','admin/build/menu','List menus','t','',136,'','','',-10,'modules/menu/menu.admin.inc'),('admin/build/menu/settings','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:1:{i:0;s:14:\"menu_configure\";}',15,4,'admin/build/menu','admin/build/menu','Settings','t','',128,'','','',5,'modules/menu/menu.admin.inc'),('admin/build/modules','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:14:\"system_modules\";}',7,3,'','admin/build/modules','Modules','t','',6,'','Enable or disable add-on modules for your site.','',0,'modules/system/system.admin.inc'),('admin/build/modules/list','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:14:\"system_modules\";}',15,4,'admin/build/modules','admin/build/modules','List','t','',136,'','','',0,'modules/system/system.admin.inc'),('admin/build/modules/list/confirm','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:14:\"system_modules\";}',31,5,'','admin/build/modules/list/confirm','List','t','',4,'','','',0,'modules/system/system.admin.inc'),('admin/build/modules/uninstall','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:24:\"system_modules_uninstall\";}',15,4,'admin/build/modules','admin/build/modules','Uninstall','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/build/modules/uninstall/confirm','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:24:\"system_modules_uninstall\";}',31,5,'','admin/build/modules/uninstall/confirm','Uninstall','t','',4,'','','',0,'modules/system/system.admin.inc'),('admin/build/themes','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:2:{i:0;s:18:\"system_themes_form\";i:1;N;}',7,3,'','admin/build/themes','Themes','t','',6,'','Change which theme your site uses or allows users to set.','',0,'modules/system/system.admin.inc'),('admin/build/themes/select','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:2:{i:0;s:18:\"system_themes_form\";i:1;N;}',15,4,'admin/build/themes','admin/build/themes','List','t','',136,'','Select the default theme.','',-1,'modules/system/system.admin.inc'),('admin/build/themes/settings','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:21:\"system_theme_settings\";}',15,4,'admin/build/themes','admin/build/themes','Configure','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/build/themes/settings/bluemarine','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:33:\"themes/bluemarine/bluemarine.info\";s:4:\"name\";s:10:\"bluemarine\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:10:\"Bluemarine\";s:11:\"description\";s:66:\"Table-based multi-column theme with a marine and ash color scheme.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/bluemarine/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:27:\"themes/bluemarine/script.js\";}s:10:\"screenshot\";s:32:\"themes/bluemarine/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/bluemarine/style.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:10:\"bluemarine\";}',31,5,'admin/build/themes/settings','admin/build/themes','Bluemarine','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/build/themes/settings/chameleon','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":11:{s:8:\"filename\";s:31:\"themes/chameleon/chameleon.info\";s:4:\"name\";s:9:\"chameleon\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:32:\"themes/chameleon/chameleon.theme\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:12:{s:4:\"name\";s:9:\"Chameleon\";s:11:\"description\";s:42:\"Minimalist tabled theme with light colors.\";s:7:\"regions\";a:2:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";}s:8:\"features\";a:4:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:2:{s:9:\"style.css\";s:26:\"themes/chameleon/style.css\";s:10:\"common.css\";s:27:\"themes/chameleon/common.css\";}}s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"scripts\";a:1:{s:9:\"script.js\";s:26:\"themes/chameleon/script.js\";}s:10:\"screenshot\";s:31:\"themes/chameleon/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:2:{s:9:\"style.css\";s:26:\"themes/chameleon/style.css\";s:10:\"common.css\";s:27:\"themes/chameleon/common.css\";}}}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:9:\"chameleon\";}',31,5,'admin/build/themes/settings','admin/build/themes','Chameleon','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/build/themes/settings/garland','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:27:\"themes/garland/garland.info\";s:4:\"name\";s:7:\"garland\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:7:\"Garland\";s:11:\"description\";s:66:\"Tableless, recolorable, multi-column, fluid width theme (default).\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:24:\"themes/garland/script.js\";}s:10:\"screenshot\";s:29:\"themes/garland/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:7:\"garland\";}',31,5,'admin/build/themes/settings','admin/build/themes','Garland','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/build/themes/settings/global','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:21:\"system_theme_settings\";}',31,5,'admin/build/themes/settings','admin/build/themes','Global settings','t','',136,'','','',-1,'modules/system/system.admin.inc'),('admin/build/themes/settings/marvin','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:35:\"themes/chameleon/marvin/marvin.info\";s:4:\"name\";s:6:\"marvin\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:0:\"\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:6:\"Marvin\";s:11:\"description\";s:31:\"Boxy tabled theme in all grays.\";s:7:\"regions\";a:2:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";}s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:10:\"base theme\";s:9:\"chameleon\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:33:\"themes/chameleon/marvin/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:33:\"themes/chameleon/marvin/script.js\";}s:10:\"screenshot\";s:38:\"themes/chameleon/marvin/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:33:\"themes/chameleon/marvin/style.css\";}}s:10:\"base_theme\";s:9:\"chameleon\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:6:\"marvin\";}',31,5,'admin/build/themes/settings','admin/build/themes','Marvin','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/build/themes/settings/minnelli','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":13:{s:8:\"filename\";s:37:\"themes/garland/minnelli/minnelli.info\";s:4:\"name\";s:8:\"minnelli\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:14:{s:4:\"name\";s:8:\"Minnelli\";s:11:\"description\";s:56:\"Tableless, recolorable, multi-column, fixed width theme.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:10:\"base theme\";s:7:\"garland\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:12:\"minnelli.css\";s:36:\"themes/garland/minnelli/minnelli.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:33:\"themes/garland/minnelli/script.js\";}s:10:\"screenshot\";s:38:\"themes/garland/minnelli/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";s:6:\"engine\";s:11:\"phptemplate\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:12:\"minnelli.css\";s:36:\"themes/garland/minnelli/minnelli.css\";}}s:6:\"engine\";s:11:\"phptemplate\";s:10:\"base_theme\";s:7:\"garland\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:8:\"minnelli\";}',31,5,'admin/build/themes/settings','admin/build/themes','Minnelli','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/build/themes/settings/pushbutton','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":12:{s:8:\"filename\";s:33:\"themes/pushbutton/pushbutton.info\";s:4:\"name\";s:10:\"pushbutton\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"0\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:10:\"Pushbutton\";s:11:\"description\";s:52:\"Tabled, multi-column theme in blue and orange tones.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/pushbutton/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:27:\"themes/pushbutton/script.js\";}s:10:\"screenshot\";s:32:\"themes/pushbutton/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/pushbutton/style.css\";}}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:10:\"pushbutton\";}',31,5,'admin/build/themes/settings','admin/build/themes','Pushbutton','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/build/themes/settings/rayCivicrm','','','_system_themes_access','a:1:{i:0;O:8:\"stdClass\":13:{s:8:\"filename\";s:47:\"sites/default/themes/rayCivicrm/rayCivicrm.info\";s:4:\"name\";s:10:\"rayCivicrm\";s:4:\"type\";s:5:\"theme\";s:5:\"owner\";s:45:\"themes/engines/phptemplate/phptemplate.engine\";s:6:\"status\";s:1:\"1\";s:8:\"throttle\";s:1:\"0\";s:9:\"bootstrap\";s:1:\"0\";s:14:\"schema_version\";s:2:\"-1\";s:6:\"weight\";s:1:\"0\";s:4:\"info\";a:13:{s:4:\"name\";s:10:\"rayCivicrm\";s:11:\"description\";s:53:\"Drupal base theme built with Blueprint CSS framework.\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:17:\"screen,projection\";a:5:{s:32:\"rayCivicrm/rayCivicrm/screen.css\";s:64:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/screen.css\";s:18:\"css/rayCivicrm.css\";s:50:\"sites/default/themes/rayCivicrm/css/rayCivicrm.css\";s:18:\"nyss_skin/skin.css\";s:50:\"sites/default/themes/rayCivicrm/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:57:\"sites/default/themes/rayCivicrm/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:45:\"sites/default/themes/rayCivicrm/css/style.css\";}s:5:\"print\";a:1:{s:31:\"rayCivicrm/rayCivicrm/print.css\";s:63:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/print.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:58:\"sites/default/themes/rayCivicrm/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:54:\"sites/default/themes/rayCivicrm/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:50:\"sites/default/themes/rayCivicrm/scripts/general.js\";}s:7:\"regions\";a:4:{s:4:\"left\";s:12:\"Left sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:9:{i:0;s:4:\"name\";i:1;s:6:\"slogan\";i:2;s:7:\"mission\";i:3;s:17:\"node_user_picture\";i:4;s:20:\"comment_user_picture\";i:5;s:6:\"search\";i:6;s:7:\"favicon\";i:7;s:13:\"primary_links\";i:8;s:15:\"secondary_links\";}s:7:\"version\";s:7:\"6.x-1.6\";s:7:\"project\";s:10:\"rayCivicrm\";s:9:\"datestamp\";s:10:\"1265139005\";s:10:\"screenshot\";s:46:\"sites/default/themes/rayCivicrm/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}s:11:\"stylesheets\";a:2:{s:17:\"screen,projection\";a:5:{s:32:\"rayCivicrm/rayCivicrm/screen.css\";s:64:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/screen.css\";s:18:\"css/rayCivicrm.css\";s:50:\"sites/default/themes/rayCivicrm/css/rayCivicrm.css\";s:18:\"nyss_skin/skin.css\";s:50:\"sites/default/themes/rayCivicrm/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:57:\"sites/default/themes/rayCivicrm/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:45:\"sites/default/themes/rayCivicrm/css/style.css\";}s:5:\"print\";a:1:{s:31:\"rayCivicrm/rayCivicrm/print.css\";s:63:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/print.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:58:\"sites/default/themes/rayCivicrm/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:54:\"sites/default/themes/rayCivicrm/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:50:\"sites/default/themes/rayCivicrm/scripts/general.js\";}s:6:\"engine\";s:11:\"phptemplate\";}}','drupal_get_form','a:2:{i:0;s:21:\"system_theme_settings\";i:1;s:10:\"rayCivicrm\";}',31,5,'admin/build/themes/settings','admin/build/themes','rayCivicrm','t','',128,'','','',0,'modules/system/system.admin.inc'),('admin/by-module','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_by_module','a:0:{}',3,2,'admin','admin','By module','t','',128,'','','',2,'modules/system/system.admin.inc'),('admin/by-task','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_main_admin_page','a:0:{}',3,2,'admin','admin','By task','t','',136,'','','',0,'modules/system/system.admin.inc'),('admin/compact','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_compact_page','a:0:{}',3,2,'','admin/compact','Compact mode','t','',4,'','','',0,'modules/system/system.admin.inc'),('admin/content','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',3,2,'','admin/content','Content management','t','',6,'','Manage your site\'s content.','left',-10,'modules/system/system.admin.inc'),('admin/content/comment','','','user_access','a:1:{i:0;s:19:\"administer comments\";}','comment_admin','a:0:{}',7,3,'','admin/content/comment','Comments','t','',6,'','List and edit site comments and the comment moderation queue.','',0,'modules/comment/comment.admin.inc'),('admin/content/comment/approval','','','user_access','a:1:{i:0;s:19:\"administer comments\";}','comment_admin','a:1:{i:0;s:8:\"approval\";}',15,4,'admin/content/comment','admin/content/comment','Approval queue','t','',128,'','','',0,'modules/comment/comment.admin.inc'),('admin/content/comment/new','','','user_access','a:1:{i:0;s:19:\"administer comments\";}','comment_admin','a:0:{}',15,4,'admin/content/comment','admin/content/comment','Published comments','t','',136,'','','',-10,'modules/comment/comment.admin.inc'),('admin/content/node','','','user_access','a:1:{i:0;s:16:\"administer nodes\";}','drupal_get_form','a:1:{i:0;s:18:\"node_admin_content\";}',7,3,'','admin/content/node','Content','t','',6,'','View, edit, and delete your site\'s content.','',0,'modules/node/node.admin.inc'),('admin/content/node-settings','','','user_access','a:1:{i:0;s:16:\"administer nodes\";}','drupal_get_form','a:1:{i:0;s:14:\"node_configure\";}',7,3,'','admin/content/node-settings','Post settings','t','',6,'','Control posting behavior, such as teaser length, requiring previews before posting, and the number of posts on the front page.','',0,'modules/node/node.admin.inc'),('admin/content/node-settings/rebuild','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','drupal_get_form','a:1:{i:0;s:30:\"node_configure_rebuild_confirm\";}',15,4,'','admin/content/node-settings/rebuild','Rebuild permissions','t','',4,'','','',0,'modules/node/node.admin.inc'),('admin/content/node-type/page','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:14:\"node_type_form\";i:1;O:8:\"stdClass\":14:{s:4:\"type\";s:4:\"page\";s:4:\"name\";s:4:\"Page\";s:6:\"module\";s:4:\"node\";s:11:\"description\";s:296:\"A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.\";s:4:\"help\";s:0:\"\";s:9:\"has_title\";s:1:\"1\";s:11:\"title_label\";s:5:\"Title\";s:8:\"has_body\";s:1:\"1\";s:10:\"body_label\";s:4:\"Body\";s:14:\"min_word_count\";s:1:\"0\";s:6:\"custom\";s:1:\"1\";s:8:\"modified\";s:1:\"1\";s:6:\"locked\";s:1:\"0\";s:9:\"orig_type\";s:4:\"page\";}}',15,4,'','admin/content/node-type/page','Page','t','',4,'','','',0,'modules/node/content_types.inc'),('admin/content/node-type/page/delete','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:24:\"node_type_delete_confirm\";i:1;O:8:\"stdClass\":14:{s:4:\"type\";s:4:\"page\";s:4:\"name\";s:4:\"Page\";s:6:\"module\";s:4:\"node\";s:11:\"description\";s:296:\"A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.\";s:4:\"help\";s:0:\"\";s:9:\"has_title\";s:1:\"1\";s:11:\"title_label\";s:5:\"Title\";s:8:\"has_body\";s:1:\"1\";s:10:\"body_label\";s:4:\"Body\";s:14:\"min_word_count\";s:1:\"0\";s:6:\"custom\";s:1:\"1\";s:8:\"modified\";s:1:\"1\";s:6:\"locked\";s:1:\"0\";s:9:\"orig_type\";s:4:\"page\";}}',31,5,'','admin/content/node-type/page/delete','Delete','t','',4,'','','',0,'modules/node/content_types.inc'),('admin/content/node-type/page/edit','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:14:\"node_type_form\";i:1;O:8:\"stdClass\":14:{s:4:\"type\";s:4:\"page\";s:4:\"name\";s:4:\"Page\";s:6:\"module\";s:4:\"node\";s:11:\"description\";s:296:\"A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.\";s:4:\"help\";s:0:\"\";s:9:\"has_title\";s:1:\"1\";s:11:\"title_label\";s:5:\"Title\";s:8:\"has_body\";s:1:\"1\";s:10:\"body_label\";s:4:\"Body\";s:14:\"min_word_count\";s:1:\"0\";s:6:\"custom\";s:1:\"1\";s:8:\"modified\";s:1:\"1\";s:6:\"locked\";s:1:\"0\";s:9:\"orig_type\";s:4:\"page\";}}',31,5,'admin/content/node-type/page','admin/content/node-type/page','Edit','t','',136,'','','',0,'modules/node/content_types.inc'),('admin/content/node-type/story','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:14:\"node_type_form\";i:1;O:8:\"stdClass\":14:{s:4:\"type\";s:5:\"story\";s:4:\"name\";s:5:\"Story\";s:6:\"module\";s:4:\"node\";s:11:\"description\";s:392:\"A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.\";s:4:\"help\";s:0:\"\";s:9:\"has_title\";s:1:\"1\";s:11:\"title_label\";s:5:\"Title\";s:8:\"has_body\";s:1:\"1\";s:10:\"body_label\";s:4:\"Body\";s:14:\"min_word_count\";s:1:\"0\";s:6:\"custom\";s:1:\"1\";s:8:\"modified\";s:1:\"1\";s:6:\"locked\";s:1:\"0\";s:9:\"orig_type\";s:5:\"story\";}}',15,4,'','admin/content/node-type/story','Story','t','',4,'','','',0,'modules/node/content_types.inc'),('admin/content/node-type/story/delete','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:24:\"node_type_delete_confirm\";i:1;O:8:\"stdClass\":14:{s:4:\"type\";s:5:\"story\";s:4:\"name\";s:5:\"Story\";s:6:\"module\";s:4:\"node\";s:11:\"description\";s:392:\"A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.\";s:4:\"help\";s:0:\"\";s:9:\"has_title\";s:1:\"1\";s:11:\"title_label\";s:5:\"Title\";s:8:\"has_body\";s:1:\"1\";s:10:\"body_label\";s:4:\"Body\";s:14:\"min_word_count\";s:1:\"0\";s:6:\"custom\";s:1:\"1\";s:8:\"modified\";s:1:\"1\";s:6:\"locked\";s:1:\"0\";s:9:\"orig_type\";s:5:\"story\";}}',31,5,'','admin/content/node-type/story/delete','Delete','t','',4,'','','',0,'modules/node/content_types.inc'),('admin/content/node-type/story/edit','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:2:{i:0;s:14:\"node_type_form\";i:1;O:8:\"stdClass\":14:{s:4:\"type\";s:5:\"story\";s:4:\"name\";s:5:\"Story\";s:6:\"module\";s:4:\"node\";s:11:\"description\";s:392:\"A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.\";s:4:\"help\";s:0:\"\";s:9:\"has_title\";s:1:\"1\";s:11:\"title_label\";s:5:\"Title\";s:8:\"has_body\";s:1:\"1\";s:10:\"body_label\";s:4:\"Body\";s:14:\"min_word_count\";s:1:\"0\";s:6:\"custom\";s:1:\"1\";s:8:\"modified\";s:1:\"1\";s:6:\"locked\";s:1:\"0\";s:9:\"orig_type\";s:5:\"story\";}}',31,5,'admin/content/node-type/story','admin/content/node-type/story','Edit','t','',136,'','','',0,'modules/node/content_types.inc'),('admin/content/node/overview','','','user_access','a:1:{i:0;s:16:\"administer nodes\";}','drupal_get_form','a:1:{i:0;s:18:\"node_admin_content\";}',15,4,'admin/content/node','admin/content/node','List','t','',136,'','','',-10,'modules/node/node.admin.inc'),('admin/content/rss-publishing','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:25:\"system_rss_feeds_settings\";}',7,3,'','admin/content/rss-publishing','RSS publishing','t','',6,'','Configure the number of items per feed and whether feeds should be titles/teasers/full-text.','',0,'modules/system/system.admin.inc'),('admin/content/taxonomy','','','user_access','a:1:{i:0;s:19:\"administer taxonomy\";}','drupal_get_form','a:1:{i:0;s:30:\"taxonomy_overview_vocabularies\";}',7,3,'','admin/content/taxonomy','Taxonomy','t','',6,'','Manage tagging, categorization, and classification of your content.','',0,'modules/taxonomy/taxonomy.admin.inc'),('admin/content/taxonomy/%','a:1:{i:3;s:24:\"taxonomy_vocabulary_load\";}','','user_access','a:1:{i:0;s:19:\"administer taxonomy\";}','drupal_get_form','a:2:{i:0;s:23:\"taxonomy_overview_terms\";i:1;i:3;}',14,4,'','admin/content/taxonomy/%','List terms','t','',4,'','','',0,'modules/taxonomy/taxonomy.admin.inc'),('admin/content/taxonomy/%/add/term','a:1:{i:3;s:24:\"taxonomy_vocabulary_load\";}','','user_access','a:1:{i:0;s:19:\"administer taxonomy\";}','taxonomy_add_term_page','a:1:{i:0;i:3;}',59,6,'admin/content/taxonomy/%','admin/content/taxonomy/%','Add term','t','',128,'','','',0,'modules/taxonomy/taxonomy.admin.inc'),('admin/content/taxonomy/%/list','a:1:{i:3;s:24:\"taxonomy_vocabulary_load\";}','','user_access','a:1:{i:0;s:19:\"administer taxonomy\";}','drupal_get_form','a:2:{i:0;s:23:\"taxonomy_overview_terms\";i:1;i:3;}',29,5,'admin/content/taxonomy/%','admin/content/taxonomy/%','List','t','',136,'','','',-10,'modules/taxonomy/taxonomy.admin.inc'),('admin/content/taxonomy/add/vocabulary','','','user_access','a:1:{i:0;s:19:\"administer taxonomy\";}','drupal_get_form','a:1:{i:0;s:24:\"taxonomy_form_vocabulary\";}',31,5,'admin/content/taxonomy','admin/content/taxonomy','Add vocabulary','t','',128,'','','',0,'modules/taxonomy/taxonomy.admin.inc'),('admin/content/taxonomy/edit/term','','','user_access','a:1:{i:0;s:19:\"administer taxonomy\";}','taxonomy_admin_term_edit','a:0:{}',31,5,'','admin/content/taxonomy/edit/term','Edit term','t','',4,'','','',0,'modules/taxonomy/taxonomy.admin.inc'),('admin/content/taxonomy/edit/vocabulary/%','a:1:{i:5;s:24:\"taxonomy_vocabulary_load\";}','','user_access','a:1:{i:0;s:19:\"administer taxonomy\";}','taxonomy_admin_vocabulary_edit','a:1:{i:0;i:5;}',62,6,'','admin/content/taxonomy/edit/vocabulary/%','Edit vocabulary','t','',4,'','','',0,'modules/taxonomy/taxonomy.admin.inc'),('admin/content/taxonomy/list','','','user_access','a:1:{i:0;s:19:\"administer taxonomy\";}','drupal_get_form','a:1:{i:0;s:30:\"taxonomy_overview_vocabularies\";}',15,4,'admin/content/taxonomy','admin/content/taxonomy','List','t','',136,'','','',-10,'modules/taxonomy/taxonomy.admin.inc'),('admin/content/types','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','node_overview_types','a:0:{}',7,3,'','admin/content/types','Content types','t','',6,'','Manage posts by content type, including default status, front page promotion, etc.','',0,'modules/node/content_types.inc'),('admin/content/types/add','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','drupal_get_form','a:1:{i:0;s:14:\"node_type_form\";}',15,4,'admin/content/types','admin/content/types','Add content type','t','',128,'','','',0,'modules/node/content_types.inc'),('admin/content/types/list','','','user_access','a:1:{i:0;s:24:\"administer content types\";}','node_overview_types','a:0:{}',15,4,'admin/content/types','admin/content/types','List','t','',136,'','','',-10,'modules/node/content_types.inc'),('admin/help','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_main','a:0:{}',3,2,'','admin/help','Help','t','',6,'','','',9,'modules/help/help.admin.inc'),('admin/help/apachesolr_search','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/apachesolr_search','apachesolr_search','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/block','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/block','block','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/civicrm','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/civicrm','civicrm','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/color','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/color','color','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/comment','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/comment','comment','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/dblog','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/dblog','dblog','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/filter','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/filter','filter','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/front_page','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/front_page','front_page','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/help','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/help','help','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/ldapauth','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/ldapauth','ldapauth','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/menu','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/menu','menu','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/node','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/node','node','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/roleassign','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/roleassign','roleassign','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/search','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/search','search','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/system','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/system','system','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/taxonomy','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/taxonomy','taxonomy','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/update','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/update','update','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/user','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/user','user','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/help/userprotect','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','help_page','a:1:{i:0;i:2;}',7,3,'','admin/help/userprotect','userprotect','t','',4,'','','',0,'modules/help/help.admin.inc'),('admin/reports','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','system_admin_menu_block_page','a:0:{}',3,2,'','admin/reports','Reports','t','',6,'','View reports from system logs and other status information.','left',5,'modules/system/system.admin.inc'),('admin/reports/access-denied','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','dblog_top','a:1:{i:0;s:13:\"access denied\";}',7,3,'','admin/reports/access-denied','Top \'access denied\' errors','t','',6,'','View \'access denied\' errors (403s).','',0,'modules/dblog/dblog.admin.inc'),('admin/reports/apachesolr','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','apachesolr_index_report','a:0:{}',7,3,'','admin/reports/apachesolr','Apache Solr search index','t','',6,'','','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/reports/apachesolr/index','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','apachesolr_index_report','a:0:{}',15,4,'admin/reports/apachesolr','admin/reports/apachesolr','Search index','t','',136,'','','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/reports/dblog','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','dblog_overview','a:0:{}',7,3,'','admin/reports/dblog','Recent log entries','t','',6,'','View events that have recently been logged.','',-1,'modules/dblog/dblog.admin.inc'),('admin/reports/event/%','a:1:{i:3;N;}','','user_access','a:1:{i:0;s:19:\"access site reports\";}','dblog_event','a:1:{i:0;i:3;}',14,4,'','admin/reports/event/%','Details','t','',4,'','','',0,'modules/dblog/dblog.admin.inc'),('admin/reports/page-not-found','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','dblog_top','a:1:{i:0;s:14:\"page not found\";}',7,3,'','admin/reports/page-not-found','Top \'page not found\' errors','t','',6,'','View \'page not found\' errors (404s).','',0,'modules/dblog/dblog.admin.inc'),('admin/reports/search','','','user_access','a:1:{i:0;s:19:\"access site reports\";}','dblog_top','a:1:{i:0;s:6:\"search\";}',7,3,'','admin/reports/search','Top search phrases','t','',6,'','View most popular search phrases.','',0,'modules/dblog/dblog.admin.inc'),('admin/reports/status','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_status','a:0:{}',7,3,'','admin/reports/status','Status report','t','',6,'','Get a status report about your site\'s operation and any detected problems.','',10,'modules/system/system.admin.inc'),('admin/reports/status/php','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_php','a:0:{}',15,4,'','admin/reports/status/php','PHP','t','',4,'','','',0,'modules/system/system.admin.inc'),('admin/reports/status/run-cron','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_run_cron','a:0:{}',15,4,'','admin/reports/status/run-cron','Run cron','t','',4,'','','',0,'modules/system/system.admin.inc'),('admin/reports/status/sql','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_sql','a:0:{}',15,4,'','admin/reports/status/sql','SQL','t','',4,'','','',0,'modules/system/system.admin.inc'),('admin/reports/updates','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','update_status','a:0:{}',7,3,'','admin/reports/updates','Available updates','t','',6,'','Get a status report about available updates for your installed modules and themes.','',10,'modules/update/update.report.inc'),('admin/reports/updates/check','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','update_manual_status','a:0:{}',15,4,'','admin/reports/updates/check','Manual update check','t','',4,'','','',0,'modules/update/update.fetch.inc'),('admin/reports/updates/list','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','update_status','a:0:{}',15,4,'admin/reports/updates','admin/reports/updates','List','t','',136,'','','',0,'modules/update/update.report.inc'),('admin/reports/updates/settings','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:15:\"update_settings\";}',15,4,'admin/reports/updates','admin/reports/updates','Settings','t','',128,'','','',0,'modules/update/update.settings.inc'),('admin/settings','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_settings_overview','a:0:{}',3,2,'','admin/settings','Site configuration','t','',6,'','Adjust basic site configuration options.','right',-5,'modules/system/system.admin.inc'),('admin/settings/actions','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','system_actions_manage','a:0:{}',7,3,'','admin/settings/actions','Actions','t','',6,'','Manage the actions defined for your site.','',0,''),('admin/settings/actions/configure','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','drupal_get_form','a:1:{i:0;s:24:\"system_actions_configure\";}',15,4,'','admin/settings/actions/configure','Configure an advanced action','t','',4,'','','',0,''),('admin/settings/actions/delete/%','a:1:{i:4;s:12:\"actions_load\";}','','user_access','a:1:{i:0;s:18:\"administer actions\";}','drupal_get_form','a:2:{i:0;s:26:\"system_actions_delete_form\";i:1;i:4;}',30,5,'','admin/settings/actions/delete/%','Delete action','t','',4,'','Delete an action.','',0,''),('admin/settings/actions/manage','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','system_actions_manage','a:0:{}',15,4,'admin/settings/actions','admin/settings/actions','Manage actions','t','',136,'','Manage the actions defined for your site.','',-2,''),('admin/settings/actions/orphan','','','user_access','a:1:{i:0;s:18:\"administer actions\";}','system_actions_remove_orphans','a:0:{}',15,4,'','admin/settings/actions/orphan','Remove orphans','t','',4,'','','',0,''),('admin/settings/admin','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:27:\"system_admin_theme_settings\";}',7,3,'','admin/settings/admin','Administration theme','t','',6,'system_admin_theme_settings','Settings for how your administrative pages should look.','left',0,'modules/system/system.admin.inc'),('admin/settings/apachesolr','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:19:\"apachesolr_settings\";}',7,3,'','admin/settings/apachesolr','Apache Solr','t','',6,'','Administer Apache Solr.','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/settings/apachesolr/content-bias','','','user_access','a:1:{i:0;s:17:\"administer search\";}','apachesolr_boost_settings_page','a:0:{}',15,4,'admin/settings/apachesolr','admin/settings/apachesolr','Content bias settings','t','',128,'','','',1,'sites/all/modules/apachesolr/apachesolr_search.admin.inc'),('admin/settings/apachesolr/enabled-filters','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:30:\"apachesolr_enabled_facets_form\";}',15,4,'admin/settings/apachesolr','admin/settings/apachesolr','Enabled filters','t','',128,'','','',-7,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/settings/apachesolr/index','','','user_access','a:1:{i:0;s:17:\"administer search\";}','apachesolr_index_page','a:0:{}',15,4,'admin/settings/apachesolr','admin/settings/apachesolr','Search index','t','',128,'','','',-8,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/settings/apachesolr/index/clear/confirm','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:30:\"apachesolr_clear_index_confirm\";}',63,6,'','admin/settings/apachesolr/index/clear/confirm','Confirm the re-indexing of all content','t','',4,'','','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/settings/apachesolr/index/delete/confirm','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:31:\"apachesolr_delete_index_confirm\";}',63,6,'','admin/settings/apachesolr/index/delete/confirm','Confirm index deletion','t','',4,'','','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/settings/apachesolr/mlt/add_block','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:29:\"apachesolr_mlt_add_block_form\";}',31,5,'','admin/settings/apachesolr/mlt/add_block','','t','',4,'','','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/settings/apachesolr/mlt/delete_block/%','a:1:{i:5;N;}','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:2:{i:0;s:32:\"apachesolr_mlt_delete_block_form\";i:1;i:5;}',62,6,'','admin/settings/apachesolr/mlt/delete_block/%','','t','',4,'','','',0,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/settings/apachesolr/query-fields','','','user_access','a:1:{i:0;s:17:\"administer search\";}','apachesolr_search_settings_page','a:0:{}',15,4,'admin/settings/apachesolr','admin/settings/apachesolr','Search fields','t','',128,'','','',1,'sites/all/modules/apachesolr/apachesolr_search.admin.inc'),('admin/settings/apachesolr/settings','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:19:\"apachesolr_settings\";}',15,4,'admin/settings/apachesolr','admin/settings/apachesolr','Settings','t','',136,'','','',-10,'sites/all/modules/apachesolr/apachesolr.admin.inc'),('admin/settings/clean-urls','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:25:\"system_clean_url_settings\";}',7,3,'','admin/settings/clean-urls','Clean URLs','t','',6,'','Enable or disable clean URLs for your site.','',0,'modules/system/system.admin.inc'),('admin/settings/clean-urls/check','','','1','a:0:{}','drupal_json','a:1:{i:0;a:1:{s:6:\"status\";b:1;}}',15,4,'','admin/settings/clean-urls/check','Clean URL check','t','',4,'','','',0,''),('admin/settings/date-time','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:25:\"system_date_time_settings\";}',7,3,'','admin/settings/date-time','Date and time','t','',6,'','Settings for how Drupal displays date and time, as well as the system\'s default timezone.','',0,'modules/system/system.admin.inc'),('admin/settings/date-time/lookup','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_date_time_lookup','a:0:{}',15,4,'','admin/settings/date-time/lookup','Date and time lookup','t','',4,'','','',0,'modules/system/system.admin.inc'),('admin/settings/error-reporting','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:31:\"system_error_reporting_settings\";}',7,3,'','admin/settings/error-reporting','Error reporting','t','',6,'','Control how Drupal deals with errors including 403/404 errors as well as PHP error reporting.','',0,'modules/system/system.admin.inc'),('admin/settings/file-system','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:27:\"system_file_system_settings\";}',7,3,'','admin/settings/file-system','File system','t','',6,'','Tell Drupal where to store uploaded files and how they are accessed.','',0,'modules/system/system.admin.inc'),('admin/settings/filters','','','user_access','a:1:{i:0;s:18:\"administer filters\";}','drupal_get_form','a:1:{i:0;s:21:\"filter_admin_overview\";}',7,3,'','admin/settings/filters','Input formats','t','',6,'','Configure how content input by users is filtered, including allowed HTML tags. Also allows enabling of module-provided filters.','',0,'modules/filter/filter.admin.inc'),('admin/settings/filters/%','a:1:{i:3;s:18:\"filter_format_load\";}','','user_access','a:1:{i:0;s:18:\"administer filters\";}','filter_admin_format_page','a:1:{i:0;i:3;}',14,4,'','admin/settings/filters/%','','filter_admin_format_title','a:1:{i:0;i:3;}',4,'','','',0,'modules/filter/filter.admin.inc'),('admin/settings/filters/%/configure','a:1:{i:3;s:18:\"filter_format_load\";}','','user_access','a:1:{i:0;s:18:\"administer filters\";}','filter_admin_configure_page','a:1:{i:0;i:3;}',29,5,'admin/settings/filters/%','admin/settings/filters/%','Configure','t','',128,'','','',1,'modules/filter/filter.admin.inc'),('admin/settings/filters/%/edit','a:1:{i:3;s:18:\"filter_format_load\";}','','user_access','a:1:{i:0;s:18:\"administer filters\";}','filter_admin_format_page','a:1:{i:0;i:3;}',29,5,'admin/settings/filters/%','admin/settings/filters/%','Edit','t','',136,'','','',0,'modules/filter/filter.admin.inc'),('admin/settings/filters/%/order','a:1:{i:3;s:18:\"filter_format_load\";}','','user_access','a:1:{i:0;s:18:\"administer filters\";}','filter_admin_order_page','a:1:{i:0;i:3;}',29,5,'admin/settings/filters/%','admin/settings/filters/%','Rearrange','t','',128,'','','',2,'modules/filter/filter.admin.inc'),('admin/settings/filters/add','','','user_access','a:1:{i:0;s:18:\"administer filters\";}','filter_admin_format_page','a:0:{}',15,4,'admin/settings/filters','admin/settings/filters','Add input format','t','',128,'','','',1,'modules/filter/filter.admin.inc'),('admin/settings/filters/delete','','','user_access','a:1:{i:0;s:18:\"administer filters\";}','drupal_get_form','a:1:{i:0;s:19:\"filter_admin_delete\";}',15,4,'','admin/settings/filters/delete','Delete input format','t','',4,'','','',0,'modules/filter/filter.admin.inc'),('admin/settings/filters/list','','','user_access','a:1:{i:0;s:18:\"administer filters\";}','drupal_get_form','a:1:{i:0;s:21:\"filter_admin_overview\";}',15,4,'admin/settings/filters','admin/settings/filters','List','t','',136,'','','',0,'modules/filter/filter.admin.inc'),('admin/settings/front','','','user_access','a:1:{i:0;s:15:\"administer menu\";}','drupal_get_form','a:1:{i:0;s:16:\"front_page_admin\";}',7,3,'','admin/settings/front','Advanced front page settings','t','',6,'','Specify a unique layout or splash page based on role type - override your HOME and breadcrumb links - display a custom mission style notice for users who haven\'t visited in a while - disable site and display a \'temporarily offline\' message.','',0,''),('admin/settings/image-toolkit','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:29:\"system_image_toolkit_settings\";}',7,3,'','admin/settings/image-toolkit','Image toolkit','t','',6,'','Choose which image toolkit to use if you have installed optional toolkits.','',0,'modules/system/system.admin.inc'),('admin/settings/ldap','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','ldapauth_admin_menu_block_page','a:0:{}',7,3,'','admin/settings/ldap','LDAP','t','',6,'','Configure LDAP integration settings.','',0,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:1:{i:0;s:23:\"ldapauth_admin_settings\";}',15,4,'','admin/settings/ldap/ldapauth','Authentication','t','',6,'','Configure LDAP authentication settings.','',0,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth/activate','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:1:{i:0;s:23:\"ldapauth_admin_activate\";}',31,5,'','admin/settings/ldap/ldapauth/activate','Activate LDAP Source','t','',4,'','','',0,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth/add','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:2:{i:0;s:19:\"ldapauth_admin_form\";i:1;i:4;}',31,5,'admin/settings/ldap/ldapauth','admin/settings/ldap/ldapauth','Add Server','t','',128,'','','',2,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth/configure','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:1:{i:0;s:23:\"ldapauth_admin_settings\";}',31,5,'admin/settings/ldap/ldapauth','admin/settings/ldap/ldapauth','Settings','t','',136,'','','',0,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth/deactivate','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:1:{i:0;s:25:\"ldapauth_admin_deactivate\";}',31,5,'','admin/settings/ldap/ldapauth/deactivate','De-activate LDAP Source','t','',4,'','','',0,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth/delete','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:2:{i:0;s:21:\"ldapauth_admin_delete\";i:1;i:5;}',31,5,'','admin/settings/ldap/ldapauth/delete','Delete LDAP Server','t','',4,'','','',0,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth/edit','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:3:{i:0;s:19:\"ldapauth_admin_form\";i:1;i:4;i:2;i:5;}',31,5,'','admin/settings/ldap/ldapauth/edit','Configure LDAP Server','t','',4,'','','',0,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth/edit/%/test','a:1:{i:5;N;}','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','_ldapauth_ajax_test','a:1:{i:0;i:5;}',125,7,'','admin/settings/ldap/ldapauth/edit/%/test','Test LDAP Server','t','',4,'','','',0,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapauth/list','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:1:{i:0;s:19:\"ldapauth_admin_list\";}',31,5,'admin/settings/ldap/ldapauth','admin/settings/ldap/ldapauth','List','t','',128,'','','',1,'sites/all/modules/ldap_integration/ldapauth.admin.inc'),('admin/settings/ldap/ldapgroups','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:1:{i:0;s:25:\"ldapgroups_admin_settings\";}',15,4,'','admin/settings/ldap/ldapgroups','Groups','t','',6,'','Configure LDAP groups to Drupal roles mapping settings.','',0,'sites/all/modules/ldap_integration/ldapgroups.admin.inc'),('admin/settings/ldap/ldapgroups/edit','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:3:{i:0;s:21:\"ldapgroups_admin_edit\";i:1;i:4;i:2;i:5;}',31,5,'','admin/settings/ldap/ldapgroups/edit','Groups','t','',4,'','','',0,'sites/all/modules/ldap_integration/ldapgroups.admin.inc'),('admin/settings/ldap/ldapgroups/reset','','','user_access','a:1:{i:0;s:23:\"administer ldap modules\";}','drupal_get_form','a:3:{i:0;s:21:\"ldapgroups_admin_edit\";i:1;i:4;i:2;i:5;}',31,5,'','admin/settings/ldap/ldapgroups/reset','Groups','t','',4,'','','',1,'sites/all/modules/ldap_integration/ldapgroups.admin.inc'),('admin/settings/logging','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','system_logging_overview','a:0:{}',7,3,'','admin/settings/logging','Logging and alerts','t','',6,'','Settings for logging and alerts modules. Various modules can route Drupal\'s system events to different destination, such as syslog, database, email, ...etc.','',0,'modules/system/system.admin.inc'),('admin/settings/logging/dblog','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:20:\"dblog_admin_settings\";}',15,4,'','admin/settings/logging/dblog','Database logging','t','',6,'','Settings for logging to the Drupal database logs. This is the most common method for small to medium sites on shared hosting. The logs are viewable from the admin pages.','',0,'modules/dblog/dblog.admin.inc'),('admin/settings/performance','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:27:\"system_performance_settings\";}',7,3,'','admin/settings/performance','Performance','t','',6,'','Enable or disable page caching for anonymous users and set CSS and JS bandwidth optimization options.','',0,'modules/system/system.admin.inc'),('admin/settings/search','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:21:\"search_admin_settings\";}',7,3,'','admin/settings/search','Search settings','t','',6,'','Configure relevance settings for search and other indexing options','',0,'modules/search/search.admin.inc'),('admin/settings/search/wipe','','','user_access','a:1:{i:0;s:17:\"administer search\";}','drupal_get_form','a:1:{i:0;s:19:\"search_wipe_confirm\";}',15,4,'','admin/settings/search/wipe','Clear index','t','',4,'','','',0,'modules/search/search.admin.inc'),('admin/settings/site-information','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:32:\"system_site_information_settings\";}',7,3,'','admin/settings/site-information','Site information','t','',6,'','Change basic site information, such as the site name, slogan, e-mail address, mission, front page and more.','',0,'modules/system/system.admin.inc'),('admin/settings/site-maintenance','','','user_access','a:1:{i:0;s:29:\"administer site configuration\";}','drupal_get_form','a:1:{i:0;s:32:\"system_site_maintenance_settings\";}',7,3,'','admin/settings/site-maintenance','Site maintenance','t','',6,'','Take the site off-line for maintenance or bring it back online.','',0,'modules/system/system.admin.inc'),('admin/user','','','user_access','a:1:{i:0;s:27:\"access administration pages\";}','system_admin_menu_block_page','a:0:{}',3,2,'','admin/user','User management','t','',6,'','Manage your site\'s users, groups and access to site features.','left',0,'modules/system/system.admin.inc'),('admin/user/permissions','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:15:\"user_admin_perm\";}',7,3,'','admin/user/permissions','Permissions','t','',6,'','Determine access to features by selecting permissions for roles.','',0,'modules/user/user.admin.inc'),('admin/user/roleassign','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:16:\"roleassign_admin\";}',7,3,'','admin/user/roleassign','Role assign','t','',6,'','Allows site administrators to further delegate the task of managing user\'s roles.','',0,''),('admin/user/roles','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:19:\"user_admin_new_role\";}',7,3,'','admin/user/roles','Roles','t','',6,'','List, edit, or add user roles.','',0,'modules/user/user.admin.inc'),('admin/user/roles/edit','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:15:\"user_admin_role\";}',15,4,'','admin/user/roles/edit','Edit role','t','',4,'','','',0,'modules/user/user.admin.inc'),('admin/user/rules','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','user_admin_access','a:0:{}',7,3,'','admin/user/rules','Access rules','t','',6,'','List and create rules to disallow usernames, e-mail addresses, and IP addresses.','',0,'modules/user/user.admin.inc'),('admin/user/rules/add','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','user_admin_access_add','a:0:{}',15,4,'admin/user/rules','admin/user/rules','Add rule','t','',128,'','','',0,'modules/user/user.admin.inc'),('admin/user/rules/check','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','user_admin_access_check','a:0:{}',15,4,'admin/user/rules','admin/user/rules','Check rules','t','',128,'','','',0,'modules/user/user.admin.inc'),('admin/user/rules/delete','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','drupal_get_form','a:1:{i:0;s:32:\"user_admin_access_delete_confirm\";}',15,4,'','admin/user/rules/delete','Delete rule','t','',4,'','','',0,'modules/user/user.admin.inc'),('admin/user/rules/edit','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','user_admin_access_edit','a:0:{}',15,4,'','admin/user/rules/edit','Edit rule','t','',4,'','','',0,'modules/user/user.admin.inc'),('admin/user/rules/list','','','user_access','a:1:{i:0;s:22:\"administer permissions\";}','user_admin_access','a:0:{}',15,4,'admin/user/rules','admin/user/rules','List','t','',136,'','','',-10,'modules/user/user.admin.inc'),('admin/user/settings','','','user_access','a:1:{i:0;s:16:\"administer users\";}','drupal_get_form','a:1:{i:0;s:19:\"user_admin_settings\";}',7,3,'','admin/user/settings','User settings','t','',6,'','Configure default behavior of users, including registration requirements, e-mails, and user pictures.','',0,'modules/user/user.admin.inc'),('admin/user/user','','','user_access','a:1:{i:0;s:16:\"administer users\";}','user_admin','a:1:{i:0;s:4:\"list\";}',7,3,'','admin/user/user','Users','t','',6,'','List, add, and edit users.','',0,'modules/user/user.admin.inc'),('admin/user/user/create','','','user_access','a:1:{i:0;s:16:\"administer users\";}','user_admin','a:1:{i:0;s:6:\"create\";}',15,4,'admin/user/user','admin/user/user','Add user','t','',128,'','','',0,'modules/user/user.admin.inc'),('admin/user/user/list','','','user_access','a:1:{i:0;s:16:\"administer users\";}','user_admin','a:1:{i:0;s:4:\"list\";}',15,4,'admin/user/user','admin/user/user','List','t','',136,'','','',-10,'modules/user/user.admin.inc'),('admin/user/userprotect','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:27:\"userprotect_protected_users\";}',7,3,'','admin/user/userprotect','User Protect','t','',6,'','Protect inidividual users and/or roles from editing operations.','',0,''),('admin/user/userprotect/administrator_bypass','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:32:\"userprotect_administrator_bypass\";}',15,4,'admin/user/userprotect','admin/user/userprotect','Administrator bypass','t','',128,'','','',3,''),('admin/user/userprotect/protected_roles','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:27:\"userprotect_protected_roles\";}',15,4,'admin/user/userprotect','admin/user/userprotect','Protected roles','t','',128,'','','',2,''),('admin/user/userprotect/protected_users','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:27:\"userprotect_protected_users\";}',15,4,'admin/user/userprotect','admin/user/userprotect','Protected users','t','',136,'','','',1,''),('admin/user/userprotect/protection_defaults','','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:1:{i:0;s:31:\"userprotect_protection_defaults\";}',15,4,'admin/user/userprotect','admin/user/userprotect','Protection defaults','t','',128,'','','',4,''),('batch','','','1','a:0:{}','system_batch_page','a:0:{}',1,1,'','batch','','t','',4,'','','',0,'modules/system/system.admin.inc'),('civicrm','','','1','a:0:{}','civicrm_invoke','a:0:{}',1,1,'','civicrm','CiviCRM','t','',4,'','','',0,''),('comment/delete','','','user_access','a:1:{i:0;s:19:\"administer comments\";}','comment_delete','a:0:{}',3,2,'','comment/delete','Delete comment','t','',4,'','','',0,'modules/comment/comment.admin.inc'),('comment/edit','','','user_access','a:1:{i:0;s:13:\"post comments\";}','comment_edit','a:0:{}',3,2,'','comment/edit','Edit comment','t','',4,'','','',0,'modules/comment/comment.pages.inc'),('comment/reply/%','a:1:{i:2;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:4:\"view\";i:1;i:2;}','comment_reply','a:1:{i:0;i:2;}',6,3,'','comment/reply/%','Reply to comment','t','',4,'','','',0,'modules/comment/comment.pages.inc'),('filter/tips','','','1','a:0:{}','filter_tips_long','a:0:{}',3,2,'','filter/tips','Compose tips','t','',20,'','','',0,'modules/filter/filter.pages.inc'),('front_page','','','user_access','a:1:{i:0;s:16:\"access frontpage\";}','front_page','a:0:{}',1,1,'','front_page','','t','',20,'','','',0,''),('logout','','','user_is_logged_in','a:0:{}','user_logout','a:0:{}',1,1,'','logout','Log out','t','',6,'','','',10,'modules/user/user.pages.inc'),('node','','','user_access','a:1:{i:0;s:14:\"access content\";}','node_page_default','a:0:{}',1,1,'','node','Content','t','',4,'','','',0,''),('node/%','a:1:{i:1;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:4:\"view\";i:1;i:1;}','node_page_view','a:1:{i:0;i:1;}',2,2,'','node/%','','node_page_title','a:1:{i:0;i:1;}',4,'','','',0,''),('node/%/delete','a:1:{i:1;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:6:\"delete\";i:1;i:1;}','drupal_get_form','a:2:{i:0;s:19:\"node_delete_confirm\";i:1;i:1;}',5,3,'','node/%/delete','Delete','t','',4,'','','',1,'modules/node/node.pages.inc'),('node/%/edit','a:1:{i:1;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:6:\"update\";i:1;i:1;}','node_page_edit','a:1:{i:0;i:1;}',5,3,'node/%','node/%','Edit','t','',128,'','','',1,'modules/node/node.pages.inc'),('node/%/revisions','a:1:{i:1;s:9:\"node_load\";}','','_node_revision_access','a:1:{i:0;i:1;}','node_revision_overview','a:1:{i:0;i:1;}',5,3,'node/%','node/%','Revisions','t','',128,'','','',2,'modules/node/node.pages.inc'),('node/%/revisions/%/delete','a:2:{i:1;a:1:{s:9:\"node_load\";a:1:{i:0;i:3;}}i:3;N;}','','_node_revision_access','a:2:{i:0;i:1;i:1;s:6:\"delete\";}','drupal_get_form','a:2:{i:0;s:28:\"node_revision_delete_confirm\";i:1;i:1;}',21,5,'','node/%/revisions/%/delete','Delete earlier revision','t','',4,'','','',0,'modules/node/node.pages.inc'),('node/%/revisions/%/revert','a:2:{i:1;a:1:{s:9:\"node_load\";a:1:{i:0;i:3;}}i:3;N;}','','_node_revision_access','a:2:{i:0;i:1;i:1;s:6:\"update\";}','drupal_get_form','a:2:{i:0;s:28:\"node_revision_revert_confirm\";i:1;i:1;}',21,5,'','node/%/revisions/%/revert','Revert to earlier revision','t','',4,'','','',0,'modules/node/node.pages.inc'),('node/%/revisions/%/view','a:2:{i:1;a:1:{s:9:\"node_load\";a:1:{i:0;i:3;}}i:3;N;}','','_node_revision_access','a:1:{i:0;i:1;}','node_show','a:3:{i:0;i:1;i:1;N;i:2;b:1;}',21,5,'','node/%/revisions/%/view','Revisions','t','',4,'','','',0,''),('node/%/view','a:1:{i:1;s:9:\"node_load\";}','','node_access','a:2:{i:0;s:4:\"view\";i:1;i:1;}','node_page_view','a:1:{i:0;i:1;}',5,3,'node/%','node/%','View','t','',136,'','','',-10,''),('node/add','','','_node_add_access','a:0:{}','node_add_page','a:0:{}',3,2,'','node/add','Create content','t','',6,'','','',1,'modules/node/node.pages.inc'),('node/add/page','','','node_access','a:2:{i:0;s:6:\"create\";i:1;s:4:\"page\";}','node_add','a:1:{i:0;i:2;}',7,3,'','node/add/page','Page','check_plain','',6,'','A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.','',0,'modules/node/node.pages.inc'),('node/add/story','','','node_access','a:2:{i:0;s:6:\"create\";i:1;s:5:\"story\";}','node_add','a:1:{i:0;i:2;}',7,3,'','node/add/story','Story','check_plain','',6,'','A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.','',0,'modules/node/node.pages.inc'),('rss.xml','','','user_access','a:1:{i:0;s:14:\"access content\";}','node_feed','a:0:{}',1,1,'','rss.xml','RSS feed','t','',4,'','','',0,''),('search','','','user_access','a:1:{i:0;s:14:\"search content\";}','apachesolr_search_view','a:0:{}',1,1,'','search','Search','t','',20,'','','',0,'modules/search/search.pages.inc'),('search/apachesolr_search/%','a:1:{i:2;N;}','a:1:{i:2;s:16:\"menu_tail_to_arg\";}','_search_menu','a:1:{i:0;s:17:\"apachesolr_search\";}','apachesolr_search_view','a:1:{i:0;s:17:\"apachesolr_search\";}',6,3,'search','search','Content','t','',128,'','','',-10,'modules/search/search.pages.inc'),('search/node/%','a:1:{i:2;N;}','a:1:{i:2;s:16:\"menu_tail_to_arg\";}','_search_menu','a:1:{i:0;s:4:\"node\";}','search_view','a:1:{i:0;s:4:\"node\";}',6,3,'','search/node/%','Search','t','',4,'','','',0,'modules/search/search.pages.inc'),('search/user/%','a:1:{i:2;N;}','a:1:{i:2;s:16:\"menu_tail_to_arg\";}','_search_menu','a:1:{i:0;s:4:\"user\";}','search_view','a:1:{i:0;s:4:\"user\";}',6,3,'search','search','','module_invoke','a:4:{i:0;s:4:\"user\";i:1;s:6:\"search\";i:2;s:4:\"name\";i:3;b:1;}',128,'','','',0,'modules/search/search.pages.inc'),('system/files','','','1','a:0:{}','file_download','a:0:{}',3,2,'','system/files','File download','t','',4,'','','',0,''),('taxonomy/autocomplete','','','user_access','a:1:{i:0;s:14:\"access content\";}','taxonomy_autocomplete','a:0:{}',3,2,'','taxonomy/autocomplete','Autocomplete taxonomy','t','',4,'','','',0,'modules/taxonomy/taxonomy.pages.inc'),('taxonomy/term/%','a:1:{i:2;N;}','','user_access','a:1:{i:0;s:14:\"access content\";}','taxonomy_term_page','a:1:{i:0;i:2;}',6,3,'','taxonomy/term/%','Taxonomy term','t','',4,'','','',0,'modules/taxonomy/taxonomy.pages.inc'),('user','','','1','a:0:{}','user_page','a:0:{}',1,1,'','user','User account','t','',4,'','','',0,'modules/user/user.pages.inc'),('user/%','a:1:{i:1;s:22:\"user_uid_optional_load\";}','a:1:{i:1;s:24:\"user_uid_optional_to_arg\";}','user_view_access','a:1:{i:0;i:1;}','user_view','a:1:{i:0;i:1;}',2,2,'','user/%','My account','user_page_title','a:1:{i:0;i:1;}',6,'','','',0,'modules/user/user.pages.inc'),('user/%/delete','a:1:{i:1;s:9:\"user_load\";}','','userprotect_user_delete_access','a:1:{i:0;i:1;}','drupal_get_form','a:2:{i:0;s:19:\"user_confirm_delete\";i:1;i:1;}',5,3,'','user/%/delete','Delete','t','',4,'','','',0,'modules/user/user.pages.inc'),('user/%/edit','a:1:{i:1;a:1:{s:18:\"user_category_load\";a:2:{i:0;s:4:\"%map\";i:1;s:6:\"%index\";}}}','','userprotect_user_edit_access','a:1:{i:0;i:1;}','user_edit','a:1:{i:0;i:1;}',5,3,'user/%','user/%','Edit','t','',128,'','','',0,'modules/user/user.pages.inc'),('user/%/edit/account','a:1:{i:1;a:1:{s:18:\"user_category_load\";a:2:{i:0;s:4:\"%map\";i:1;s:6:\"%index\";}}}','','userprotect_user_edit_access','a:1:{i:0;i:1;}','user_edit','a:1:{i:0;i:1;}',11,4,'user/%/edit','user/%','Account','t','',136,'','','',0,'modules/user/user.pages.inc'),('user/%/view','a:1:{i:1;s:9:\"user_load\";}','','user_view_access','a:1:{i:0;i:1;}','user_view','a:1:{i:0;i:1;}',5,3,'user/%','user/%','View','t','',136,'','','',-10,'modules/user/user.pages.inc'),('user/autocomplete','','','user_access','a:1:{i:0;s:20:\"access user profiles\";}','user_autocomplete','a:0:{}',3,2,'','user/autocomplete','User autocomplete','t','',4,'','','',0,'modules/user/user.pages.inc'),('user/login','','','user_is_anonymous','a:0:{}','user_page','a:0:{}',3,2,'user','user','Log in','t','',136,'','','',0,'modules/user/user.pages.inc'),('user/password','','','user_is_anonymous','a:0:{}','drupal_get_form','a:1:{i:0;s:9:\"user_pass\";}',3,2,'user','user','Request new password','t','',128,'','','',0,'modules/user/user.pages.inc'),('user/register','','','user_register_access','a:0:{}','drupal_get_form','a:1:{i:0;s:13:\"user_register\";}',3,2,'user','user','Create new account','t','',128,'','','',0,'modules/user/user.pages.inc'),('user/reset/%/%/%','a:3:{i:2;N;i:3;N;i:4;N;}','','1','a:0:{}','drupal_get_form','a:4:{i:0;s:15:\"user_pass_reset\";i:1;i:2;i:2;i:3;i:3;i:4;}',24,5,'','user/reset/%/%/%','Reset password','t','',4,'','','',0,'modules/user/user.pages.inc'),('userprotect/delete/%','a:1:{i:2;s:9:\"user_load\";}','','user_access','a:1:{i:0;s:22:\"administer userprotect\";}','drupal_get_form','a:3:{i:0;s:39:\"userprotect_protected_users_delete_form\";i:1;i:2;i:2;i:3;}',6,3,'','userprotect/delete/%','Delete protected user','t','',4,'','','',0,'');
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
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL DEFAULT '',
  `language` varchar(12) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `created` int(11) NOT NULL DEFAULT '0',
  `changed` int(11) NOT NULL DEFAULT '0',
  `comment` int(11) NOT NULL DEFAULT '0',
  `promote` int(11) NOT NULL DEFAULT '0',
  `moderate` int(11) NOT NULL DEFAULT '0',
  `sticky` int(11) NOT NULL DEFAULT '0',
  `tnid` int(10) unsigned NOT NULL DEFAULT '0',
  `translate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nid`),
  UNIQUE KEY `vid` (`vid`),
  KEY `node_changed` (`changed`),
  KEY `node_created` (`created`),
  KEY `node_moderate` (`moderate`),
  KEY `node_promote_status` (`promote`,`status`),
  KEY `node_status_type` (`status`,`type`,`nid`),
  KEY `node_title_type` (`title`,`type`(4)),
  KEY `node_type` (`type`(4)),
  KEY `uid` (`uid`),
  KEY `tnid` (`tnid`),
  KEY `translate` (`translate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node`
--

LOCK TABLES `node` WRITE;
/*!40000 ALTER TABLE `node` DISABLE KEYS */;
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
  `last_comment_timestamp` int(11) NOT NULL DEFAULT '0',
  `last_comment_name` varchar(60) DEFAULT NULL,
  `last_comment_uid` int(11) NOT NULL DEFAULT '0',
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`nid`),
  KEY `node_comment_timestamp` (`last_comment_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_comment_statistics`
--

LOCK TABLES `node_comment_statistics` WRITE;
/*!40000 ALTER TABLE `node_comment_statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_comment_statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_counter`
--

DROP TABLE IF EXISTS `node_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_counter` (
  `nid` int(11) NOT NULL DEFAULT '0',
  `totalcount` bigint(20) unsigned NOT NULL DEFAULT '0',
  `daycount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_counter`
--

LOCK TABLES `node_counter` WRITE;
/*!40000 ALTER TABLE `node_counter` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_counter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `node_revisions`
--

DROP TABLE IF EXISTS `node_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `node_revisions` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` longtext NOT NULL,
  `teaser` longtext NOT NULL,
  `log` longtext NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `format` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid`),
  KEY `nid` (`nid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_revisions`
--

LOCK TABLES `node_revisions` WRITE;
/*!40000 ALTER TABLE `node_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `node_revisions` ENABLE KEYS */;
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
  `module` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `help` mediumtext NOT NULL,
  `has_title` tinyint(3) unsigned NOT NULL,
  `title_label` varchar(255) NOT NULL DEFAULT '',
  `has_body` tinyint(3) unsigned NOT NULL,
  `body_label` varchar(255) NOT NULL DEFAULT '',
  `min_word_count` smallint(5) unsigned NOT NULL,
  `custom` tinyint(4) NOT NULL DEFAULT '0',
  `modified` tinyint(4) NOT NULL DEFAULT '0',
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  `orig_type` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `node_type`
--

LOCK TABLES `node_type` WRITE;
/*!40000 ALTER TABLE `node_type` DISABLE KEYS */;
INSERT INTO `node_type` VALUES ('page','Page','node','A <em>page</em>, similar in form to a <em>story</em>, is a simple method for creating and displaying information that rarely changes, such as an \"About us\" section of a website. By default, a <em>page</em> entry does not allow visitor comments and is not featured on the site\'s initial home page.','',1,'Title',1,'Body',0,1,1,0,'page'),('story','Story','node','A <em>story</em>, similar in form to a <em>page</em>, is ideal for creating and displaying content that informs or engages website visitors. Press releases, site announcements, and informal blog-like entries may all be created with a <em>story</em> entry. By default, a <em>story</em> entry is automatically featured on the site\'s initial home page, and provides the ability to post comments.','',1,'Title',1,'Body',0,1,1,0,'story');
/*!40000 ALTER TABLE `node_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL DEFAULT '0',
  `perm` longtext,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`),
  KEY `rid` (`rid`)
) ENGINE=InnoDB AUTO_INCREMENT=451 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission`
--

LOCK TABLES `permission` WRITE;
/*!40000 ALTER TABLE `permission` DISABLE KEYS */;
INSERT INTO `permission` VALUES (438,4,'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, assign roles, access user profiles, administer users',0),(439,8,'access CiviCRM, access CiviReport, access Report Criteria, access all custom data, administer Reports, profile listings, profile view, view all activities, view all contacts',0),(440,1,'access content',0),(441,2,'access content, change own e-mail, change own openid, change own password',0),(442,5,'access CiviCRM, access CiviReport, access Report Criteria, access all custom data, administer Reports, edit all contacts, profile listings, profile view, view all activities, view all contacts',0),(443,12,'access CiviCRM, access all custom data, access uploaded files, add contacts, edit all contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts',0),(444,9,'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, assign roles, access user profiles, administer users',0),(445,10,'access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts',0),(446,7,'access CiviCRM, access CiviReport, access Report Criteria, access all custom data, edit groups, import contacts, profile listings, profile view, view all contacts',0),(447,6,'access CiviCRM, access all custom data, access uploaded files, add contacts, delete contacts, edit all contacts, profile listings, profile view, view all activities, view all contacts',0),(448,11,'access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, profile listings, profile view, view all activities, view all contacts',0),(449,3,'create users, delete users with role Administrator, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Administrator, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, administer blocks, use PHP for block visibility, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer CiviCase, administer Reports, administer Tagsets, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, profile create, profile edit, profile listings, profile listings and forms, profile view, translate CiviCRM, view all activities, view all contacts, assign roles, access user profiles, administer permissions, administer users, administer userprotect',0),(450,13,'access CiviCRM, access all custom data, access my cases and activities, access uploaded files, add contacts, profile listings, profile view, view all activities, view all contacts',0);
/*!40000 ALTER TABLE `permission` ENABLE KEYS */;
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
  PRIMARY KEY (`rid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (4,'Administrator'),(8,'Analytics User'),(1,'anonymous user'),(2,'authenticated user'),(5,'Conference Services'),(12,'Data Entry'),(9,'Office Administrator'),(10,'Office Manager'),(7,'Print Production'),(6,'SOS'),(11,'Staff'),(3,'Superuser'),(13,'Volunteer');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_dataset`
--

DROP TABLE IF EXISTS `search_dataset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_dataset` (
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(16) DEFAULT NULL,
  `data` longtext NOT NULL,
  `reindex` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `sid_type` (`sid`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_dataset`
--

LOCK TABLES `search_dataset` WRITE;
/*!40000 ALTER TABLE `search_dataset` DISABLE KEYS */;
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
  `type` varchar(16) DEFAULT NULL,
  `score` float DEFAULT NULL,
  UNIQUE KEY `word_sid_type` (`word`,`sid`,`type`),
  KEY `sid_type` (`sid`,`type`),
  KEY `word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_index`
--

LOCK TABLES `search_index` WRITE;
/*!40000 ALTER TABLE `search_index` DISABLE KEYS */;
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
/*!40000 ALTER TABLE `search_total` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `uid` int(10) unsigned NOT NULL,
  `sid` varchar(64) NOT NULL DEFAULT '',
  `hostname` varchar(128) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `cache` int(11) NOT NULL DEFAULT '0',
  `session` longtext,
  PRIMARY KEY (`sid`),
  KEY `timestamp` (`timestamp`),
  KEY `uid` (`uid`)
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
  `type` varchar(255) NOT NULL DEFAULT '',
  `owner` varchar(255) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  `throttle` tinyint(4) NOT NULL DEFAULT '0',
  `bootstrap` int(11) NOT NULL DEFAULT '0',
  `schema_version` smallint(6) NOT NULL DEFAULT '-1',
  `weight` int(11) NOT NULL DEFAULT '0',
  `info` text,
  PRIMARY KEY (`filename`),
  KEY `modules` (`type`(12),`status`,`weight`,`filename`),
  KEY `bootstrap` (`type`(12),`status`,`bootstrap`,`weight`,`filename`),
  KEY `type_name` (`type`(12),`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES ('modules/aggregator/aggregator.module','aggregator','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:10:\"Aggregator\";s:11:\"description\";s:57:\"Aggregates syndicated content (RSS, RDF, and Atom feeds).\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/block/block.module','block','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:5:\"Block\";s:11:\"description\";s:62:\"Controls the boxes that are displayed around the main content.\";s:7:\"package\";s:15:\"Core - required\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/blog/blog.module','blog','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:4:\"Blog\";s:11:\"description\";s:69:\"Enables keeping easily and regularly updated user web pages or blogs.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/blogapi/blogapi.module','blogapi','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:8:\"Blog API\";s:11:\"description\";s:79:\"Allows users to post content using applications that support XML-RPC blog APIs.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/book/book.module','book','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:4:\"Book\";s:11:\"description\";s:63:\"Allows users to structure site pages in a hierarchy or outline.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/color/color.module','color','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:5:\"Color\";s:11:\"description\";s:61:\"Allows the user to change the color scheme of certain themes.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/comment/comment.module','comment','module','',1,0,0,6003,0,'a:10:{s:4:\"name\";s:7:\"Comment\";s:11:\"description\";s:57:\"Allows users to comment on and discuss published content.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/contact/contact.module','contact','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:7:\"Contact\";s:11:\"description\";s:61:\"Enables the use of both personal and site-wide contact forms.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/cookie_cache_bypass/cookie_cache_bypass.module','cookie_cache_bypass','module','',0,0,0,-1,0,'a:7:{s:4:\"name\";s:19:\"Cookie Cache Bypass\";s:11:\"description\";s:147:\"Sets a cookie on form submission directing a reverse proxy to temporarily not serve cached pages for an anonymous user that just submitted content.\";s:4:\"core\";s:3:\"6.x\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:7:\"version\";N;s:3:\"php\";s:5:\"4.3.5\";}'),('modules/dblog/dblog.module','dblog','module','',1,0,0,6000,0,'a:10:{s:4:\"name\";s:16:\"Database logging\";s:11:\"description\";s:47:\"Logs and records system events to the database.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/filter/filter.module','filter','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:6:\"Filter\";s:11:\"description\";s:60:\"Handles the filtering of content in preparation for display.\";s:7:\"package\";s:15:\"Core - required\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/forum/forum.module','forum','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:5:\"Forum\";s:11:\"description\";s:50:\"Enables threaded discussions about general topics.\";s:12:\"dependencies\";a:2:{i:0;s:8:\"taxonomy\";i:1;s:7:\"comment\";}s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/help/help.module','help','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:4:\"Help\";s:11:\"description\";s:35:\"Manages the display of online help.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/locale/locale.module','locale','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:6:\"Locale\";s:11:\"description\";s:119:\"Adds language handling functionality and enables the translation of the user interface to languages other than English.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/menu/menu.module','menu','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:4:\"Menu\";s:11:\"description\";s:60:\"Allows administrators to customize the site navigation menu.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/node/node.module','node','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:4:\"Node\";s:11:\"description\";s:66:\"Allows content to be submitted to the site and displayed on pages.\";s:7:\"package\";s:15:\"Core - required\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/openid/openid.module','openid','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:6:\"OpenID\";s:11:\"description\";s:48:\"Allows users to log into your site using OpenID.\";s:7:\"version\";s:4:\"6.15\";s:7:\"package\";s:15:\"Core - optional\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/path/path.module','path','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:4:\"Path\";s:11:\"description\";s:28:\"Allows users to rename URLs.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/php/php.module','php','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:10:\"PHP filter\";s:11:\"description\";s:50:\"Allows embedded PHP code/snippets to be evaluated.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/ping/ping.module','ping','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:4:\"Ping\";s:11:\"description\";s:51:\"Alerts other sites when your site has been updated.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/poll/poll.module','poll','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:4:\"Poll\";s:11:\"description\";s:95:\"Allows your site to capture votes on different topics in the form of multiple choice questions.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/profile/profile.module','profile','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:7:\"Profile\";s:11:\"description\";s:36:\"Supports configurable user profiles.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/search/search.module','search','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:6:\"Search\";s:11:\"description\";s:36:\"Enables site-wide keyword searching.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/simpletest/simpletest.module','simpletest','module','',0,0,0,-1,0,'a:11:{s:4:\"name\";s:10:\"SimpleTest\";s:11:\"description\";s:53:\"Provides a framework for unit and functional testing.\";s:7:\"package\";s:11:\"Development\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:12:\"5 ; Drupal 6\";s:5:\"files\";a:6:{i:0;s:17:\"simpletest.module\";i:1;s:20:\"simpletest.pages.inc\";i:2;s:18:\"simpletest.install\";i:3;s:15:\"simpletest.test\";i:4;s:24:\"drupal_web_test_case.php\";i:5;s:16:\"tests/block.test\";}s:7:\"version\";s:7:\"6.x-2.9\";s:7:\"project\";s:10:\"simpletest\";s:9:\"datestamp\";s:10:\"1252971974\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}}'),('modules/statistics/statistics.module','statistics','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:10:\"Statistics\";s:11:\"description\";s:37:\"Logs access statistics for your site.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/syslog/syslog.module','syslog','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:6:\"Syslog\";s:11:\"description\";s:41:\"Logs and records system events to syslog.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/system/system.module','system','module','',1,0,0,6053,0,'a:10:{s:4:\"name\";s:6:\"System\";s:11:\"description\";s:54:\"Handles general site configuration for administrators.\";s:7:\"package\";s:15:\"Core - required\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/taxonomy/taxonomy.module','taxonomy','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:8:\"Taxonomy\";s:11:\"description\";s:38:\"Enables the categorization of content.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/throttle/throttle.module','throttle','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:8:\"Throttle\";s:11:\"description\";s:66:\"Handles the auto-throttling mechanism, to control site congestion.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/tracker/tracker.module','tracker','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:7:\"Tracker\";s:11:\"description\";s:43:\"Enables tracking of recent posts for users.\";s:12:\"dependencies\";a:1:{i:0;s:7:\"comment\";}s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/translation/translation.module','translation','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:19:\"Content translation\";s:11:\"description\";s:57:\"Allows content to be translated into different languages.\";s:12:\"dependencies\";a:1:{i:0;s:6:\"locale\";}s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/trigger/trigger.module','trigger','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:7:\"Trigger\";s:11:\"description\";s:90:\"Enables actions to be fired on certain system events, such as when new content is created.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/update/update.module','update','module','',1,0,0,6000,0,'a:10:{s:4:\"name\";s:13:\"Update status\";s:11:\"description\";s:88:\"Checks the status of available updates for Drupal and your installed modules and themes.\";s:7:\"version\";s:4:\"6.15\";s:7:\"package\";s:15:\"Core - optional\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/upload/upload.module','upload','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:6:\"Upload\";s:11:\"description\";s:51:\"Allows users to upload and attach files to content.\";s:7:\"package\";s:15:\"Core - optional\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('modules/user/user.module','user','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:4:\"User\";s:11:\"description\";s:47:\"Manages the user registration and login system.\";s:7:\"package\";s:15:\"Core - required\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/administerusersbyrole/administerusersbyrole.module','administerusersbyrole','module','',1,0,0,0,0,'a:9:{s:4:\"name\";s:24:\"Administer Users by Role\";s:11:\"description\";s:180:\"Allows users with \'administer users\' permission and a role (specified in \'Permissions\') to edit/delete other users with a specified role.  Also provides control over user creation.\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:7:\"6.x-1.4\";s:7:\"project\";s:21:\"administerusersbyrole\";s:9:\"datestamp\";s:10:\"1246292114\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/admin_menu/admin_menu.module','admin_menu','module','',0,0,0,6001,0,'a:10:{s:4:\"name\";s:19:\"Administration menu\";s:11:\"description\";s:123:\"Provides a dropdown menu to most administrative tasks and other common destinations (to users with the proper permissions).\";s:7:\"package\";s:14:\"Administration\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:7:\"6.x-1.5\";s:7:\"project\";s:10:\"admin_menu\";s:9:\"datestamp\";s:10:\"1246537502\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/apachesolr/apachesolr.module','apachesolr','module','',1,0,0,6004,0,'a:10:{s:4:\"name\";s:21:\"Apache Solr framework\";s:11:\"description\";s:33:\"Framework for searching with Solr\";s:12:\"dependencies\";a:1:{i:0;s:6:\"search\";}s:7:\"package\";s:11:\"Apache Solr\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:5:\"5.1.4\";s:7:\"version\";s:11:\"6.x-1.0-rc3\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1255630506\";s:10:\"dependents\";a:0:{}}'),('sites/all/modules/apachesolr/apachesolr_search.module','apachesolr_search','module','',1,0,0,6003,0,'a:10:{s:4:\"name\";s:18:\"Apache Solr search\";s:11:\"description\";s:16:\"Search with Solr\";s:12:\"dependencies\";a:2:{i:0;s:6:\"search\";i:1;s:10:\"apachesolr\";}s:7:\"package\";s:11:\"Apache Solr\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:5:\"5.1.4\";s:7:\"version\";s:11:\"6.x-1.0-rc3\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1255630506\";s:10:\"dependents\";a:0:{}}'),('sites/all/modules/apachesolr/contrib/apachesolr_image/apachesolr_image.module','apachesolr_image','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:36:\"Apache Solr image module integration\";s:11:\"description\";s:44:\"Integrates the Apache Solr and Image modules\";s:12:\"dependencies\";a:2:{i:0;s:5:\"image\";i:1;s:10:\"apachesolr\";}s:7:\"package\";s:11:\"Apache Solr\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:11:\"6.x-1.0-rc3\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1255630506\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/apachesolr/contrib/apachesolr_nodeaccess/apachesolr_nodeaccess.module','apachesolr_nodeaccess','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:23:\"Apache Solr node access\";s:11:\"description\";s:57:\"Integrates the node access system with Apache Solr search\";s:12:\"dependencies\";a:1:{i:0;s:10:\"apachesolr\";}s:7:\"package\";s:11:\"Apache Solr\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:11:\"6.x-1.0-rc3\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1255630506\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/apachesolr/contrib/apachesolr_og/apachesolr_og.module','apachesolr_og','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:26:\"Apache Solr Organic Groups\";s:11:\"description\";s:48:\"Integrates Organic Groups and Apache Solr Search\";s:12:\"dependencies\";a:2:{i:0;s:10:\"apachesolr\";i:1;s:2:\"og\";}s:7:\"package\";s:11:\"Apache Solr\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:11:\"6.x-1.0-rc3\";s:7:\"project\";s:10:\"apachesolr\";s:9:\"datestamp\";s:10:\"1255630506\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/cacherouter/cacherouter.module','cacherouter','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:11:\"CacheRouter\";s:11:\"description\";s:75:\"Controls access to split caching functionality into self contained objects.\";s:7:\"package\";s:7:\"Caching\";s:7:\"version\";s:11:\"6.x-1.0-rc1\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:11:\"cacherouter\";s:9:\"datestamp\";s:10:\"1252157422\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/civicrm/drupal/civicrm.module','civicrm','module','',1,0,0,0,0,'a:8:{s:4:\"name\";s:7:\"CiviCRM\";s:11:\"description\";s:175:\"Constituent Relationship Management CRM - v3.2. Allows sites to manage contacts, relationships and groups, and track contact activities, contributions, memberships and events.\";s:7:\"version\";s:3:\"3.2\";s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.2\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}}'),('sites/all/modules/civicrm/drupal/modules/civicrm_cck/civicrm_cck.module','civicrm_cck','module','',0,0,0,-1,0,'a:8:{s:4:\"name\";s:29:\"CiviCRM CCK Contact Reference\";s:11:\"description\";s:43:\"Makes a CiviCRM Contact Reference CCK Field\";s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:4:{i:0;s:7:\"content\";i:1;s:7:\"civicrm\";i:2;s:13:\"optionwidgets\";i:3;s:4:\"text\";}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/civicrm/drupal/modules/civicrm_group_roles/civicrm_group_roles.module','civicrm_group_roles','module','',0,0,0,0,0,'a:8:{s:4:\"name\";s:20:\"CiviGroup Roles Sync\";s:11:\"description\";s:36:\"Sync Drupal Roles to CiviCRM Groups.\";s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/civicrm/drupal/modules/civicrm_member_roles/civicrm_member_roles.module','civicrm_member_roles','module','',0,0,0,0,0,'a:8:{s:4:\"name\";s:21:\"CiviMember Roles Sync\";s:11:\"description\";s:111:\"Synchronize CiviCRM Contacts with Membership Status to a specified Drupal Role both automatically and manually.\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.1\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/civicrm/drupal/modules/civicrm_NYSS/civicrm_NYSS.module','civicrm_NYSS','module','',1,0,0,0,0,'a:8:{s:4:\"name\";s:12:\"CiviCRM NYSS\";s:11:\"description\";s:102:\"Do not allow user to edit the addresses of location type \'Board Of Election\' through contact edit form\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.1\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/civicrm/drupal/modules/civicrm_og_sync/civicrm_og_sync.module','civicrm_og_sync','module','',0,0,0,-1,0,'a:8:{s:4:\"name\";s:15:\"CiviCRM OG Sync\";s:11:\"description\";s:119:\"Synchronize Organic Groups and CiviCRM Groups and ACL\'s. More information at: http://wiki.civicrm.org/confluence//x/nDw\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:2:{i:0;s:7:\"civicrm\";i:1;s:2:\"og\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.2\";s:10:\"dependents\";a:0:{}}'),('sites/all/modules/civicrm/tools/drupal/modules/civicrm_cck_activity/civicrm_cck_activity.module','civicrm_cck_activity','module','',0,0,0,-1,0,'a:9:{s:4:\"name\";s:20:\"CiviCRM CCK Activity\";s:11:\"description\";s:33:\"Create node for civicrm Activity.\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:8:{i:0;s:7:\"civicrm\";i:1;s:7:\"content\";i:2;s:12:\"content_copy\";i:3;s:8:\"taxonomy\";i:4;s:4:\"date\";i:5;s:6:\"number\";i:6;s:13:\"userreference\";i:7;s:6:\"upload\";}s:7:\"package\";s:7:\"CiviCRM\";s:7:\"project\";s:20:\"CiviCRM CCK Activity\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/civicrm/tools/drupal/modules/civicrm_engage/civicrm_engage.module','civicrm_engage','module','',0,0,0,0,0,'a:8:{s:4:\"name\";s:46:\"Engaging and Phone banking support for CiviCRM\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.2\";s:10:\"dependents\";a:0:{}s:11:\"description\";s:0:\"\";}'),('sites/all/modules/civicrm/tools/drupal/modules/civicrm_rules/civicrm_rules.module','civicrm_rules','module','',0,0,0,-1,0,'a:8:{s:4:\"name\";s:25:\"CiviCRM Rules Integration\";s:11:\"description\";s:123:\"Integrate CiviCRM and Drupal Rules Module. Expose Contact, Contribution and other Objects along with Form / Page Operations\";s:12:\"dependencies\";a:2:{i:0;s:7:\"civicrm\";i:1;s:5:\"rules\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.1\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/civicrm/tools/drupal/modules/civicrm_van/civicrm_van.module','civicrm_van','module','',0,0,0,0,0,'a:8:{s:4:\"name\";s:27:\"CiviCRM <-> VAN Integration\";s:11:\"description\";s:27:\"CiviCRM <-> VAN Integration\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.2\";s:10:\"dependents\";a:0:{}}'),('sites/all/modules/civicrm/tools/drupal/modules/multicurrency/multicurrency.module','multicurrency','module','',0,0,0,-1,0,'a:8:{s:4:\"name\";s:34:\"Multi Currency Support for CiviCRM\";s:11:\"description\";s:43:\"Multi Currency Support for a specific Event\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.2\";s:10:\"dependents\";a:0:{}}'),('sites/all/modules/civicrm/tools/drupal/modules/multisite/multisite.module','multisite','module','',0,0,0,0,0,'a:8:{s:4:\"name\";s:30:\"Multi Site support for CiviCRM\";s:11:\"description\";s:48:\"Multi Site Support to support a PIRG like system\";s:7:\"version\";s:3:\"3.1\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.2\";s:10:\"dependents\";a:0:{}}'),('sites/all/modules/front/front_page.module','front_page','module','',1,0,0,0,0,'a:10:{s:4:\"name\";s:10:\"Front Page\";s:11:\"description\";s:57:\"Allows site admins setup custom front pages for the site.\";s:7:\"package\";s:14:\"Administration\";s:7:\"project\";s:5:\"front\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:7:\"6.x-1.2\";s:9:\"datestamp\";s:10:\"1209458407\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/ldap_integration/ldapauth.module','ldapauth','module','',1,0,1,6003,0,'a:10:{s:4:\"name\";s:14:\"Authentication\";s:11:\"description\";s:31:\"Implements LDAP authentication.\";s:7:\"package\";s:16:\"LDAP integration\";s:4:\"core\";s:3:\"6.x\";s:3:\"php\";s:3:\"5.0\";s:7:\"version\";s:13:\"6.x-1.0-beta2\";s:7:\"project\";s:16:\"ldap_integration\";s:9:\"datestamp\";s:10:\"1256654469\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}}'),('sites/all/modules/ldap_integration/ldapdata.module','ldapdata','module','',0,0,0,-1,0,'a:10:{s:4:\"name\";s:4:\"Data\";s:11:\"description\";s:56:\"Implements LDAP data to Drupal profiles synchronization.\";s:7:\"package\";s:16:\"LDAP integration\";s:12:\"dependencies\";a:1:{i:0;s:8:\"ldapauth\";}s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:13:\"6.x-1.0-beta2\";s:7:\"project\";s:16:\"ldap_integration\";s:9:\"datestamp\";s:10:\"1256654469\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/ldap_integration/ldapgroups.module','ldapgroups','module','',1,0,0,6001,0,'a:10:{s:4:\"name\";s:6:\"Groups\";s:11:\"description\";s:47:\"Implements LDAP groups to Drupal roles mapping.\";s:7:\"package\";s:16:\"LDAP integration\";s:12:\"dependencies\";a:1:{i:0;s:8:\"ldapauth\";}s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:13:\"6.x-1.0-beta2\";s:7:\"project\";s:16:\"ldap_integration\";s:9:\"datestamp\";s:10:\"1256654469\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/limit_contacts_by_role/limit_contacts_by_role.module','limit_contacts_by_role','module','',0,0,0,-1,0,'a:8:{s:4:\"name\";s:26:\"NYSS Limit Contacts module\";s:11:\"description\";s:58:\"Limits contacts by role when doing a search for Case Roles\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.1\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/nyss_civihooks/nyss_civihooks.module','nyss_civihooks','module','',1,0,0,0,0,'a:8:{s:4:\"name\";s:25:\"NYSS CiviCRM hooks module\";s:11:\"description\";s:46:\"Contains various hooks for NYSS customizations\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.2\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/nyss_contactlistquery/nyss_contactlistquery.module','nyss_contactlistquery','module','',1,0,0,0,0,'a:8:{s:4:\"name\";s:30:\"NYSS Contact List Query module\";s:11:\"description\";s:78:\"Limit contactlist queries to staff group for activity assignees and case roles\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.2\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/nyss_dashboards/nyss_dashboards.module','nyss_dashboards','module','',1,0,0,0,0,'a:8:{s:4:\"name\";s:22:\"NYSS Dashboards module\";s:11:\"description\";s:27:\"Register dashboards in menu\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.1\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/ray_civihooks/ray_civihooks.module','ray_civihooks','module','',0,0,0,-1,0,'a:8:{s:4:\"name\";s:22:\"Rayogram CiviCRM Hooks\";s:11:\"description\";s:13:\"CiviCRM Hooks\";s:12:\"dependencies\";a:1:{i:0;s:7:\"civicrm\";}s:7:\"package\";s:7:\"CiviCRM\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:3:\"3.2\";s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/roleassign/roleassign.module','roleassign','module','',1,0,0,0,0,'a:9:{s:4:\"name\";s:10:\"RoleAssign\";s:11:\"description\";s:81:\"Allows site administrators to further delegate the task of managing user\'s roles.\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:13:\"6.x-1.0-beta3\";s:7:\"project\";s:10:\"roleassign\";s:9:\"datestamp\";s:10:\"1240342368\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/all/modules/userprotect/userprotect.module','userprotect','module','',1,0,0,6002,0,'a:9:{s:4:\"name\";s:12:\"User Protect\";s:11:\"description\";s:81:\"Allows admins to protect users from being edited or deleted, on a per-user basis.\";s:4:\"core\";s:3:\"6.x\";s:7:\"version\";s:7:\"6.x-1.4\";s:7:\"project\";s:11:\"userprotect\";s:9:\"datestamp\";s:10:\"1257475912\";s:12:\"dependencies\";a:0:{}s:10:\"dependents\";a:0:{}s:3:\"php\";s:5:\"4.3.5\";}'),('sites/default/themes/rayCivicrm/rayCivicrm.info','rayCivicrm','theme','themes/engines/phptemplate/phptemplate.engine',1,0,0,-1,0,'a:13:{s:4:\"name\";s:10:\"rayCivicrm\";s:11:\"description\";s:53:\"Drupal base theme built with Blueprint CSS framework.\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:17:\"screen,projection\";a:5:{s:32:\"rayCivicrm/rayCivicrm/screen.css\";s:64:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/screen.css\";s:18:\"css/rayCivicrm.css\";s:50:\"sites/default/themes/rayCivicrm/css/rayCivicrm.css\";s:18:\"nyss_skin/skin.css\";s:50:\"sites/default/themes/rayCivicrm/nyss_skin/skin.css\";s:25:\"nyss_skin/civi-header.css\";s:57:\"sites/default/themes/rayCivicrm/nyss_skin/civi-header.css\";s:13:\"css/style.css\";s:45:\"sites/default/themes/rayCivicrm/css/style.css\";}s:5:\"print\";a:1:{s:31:\"rayCivicrm/rayCivicrm/print.css\";s:63:\"sites/default/themes/rayCivicrm/rayCivicrm/rayCivicrm/print.css\";}}s:7:\"scripts\";a:3:{s:26:\"scripts/message-manager.js\";s:58:\"sites/default/themes/rayCivicrm/scripts/message-manager.js\";s:22:\"scripts/civi-header.js\";s:54:\"sites/default/themes/rayCivicrm/scripts/civi-header.js\";s:18:\"scripts/general.js\";s:50:\"sites/default/themes/rayCivicrm/scripts/general.js\";}s:7:\"regions\";a:4:{s:4:\"left\";s:12:\"Left sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:9:{i:0;s:4:\"name\";i:1;s:6:\"slogan\";i:2;s:7:\"mission\";i:3;s:17:\"node_user_picture\";i:4;s:20:\"comment_user_picture\";i:5;s:6:\"search\";i:6;s:7:\"favicon\";i:7;s:13:\"primary_links\";i:8;s:15:\"secondary_links\";}s:7:\"version\";s:7:\"6.x-1.6\";s:7:\"project\";s:10:\"rayCivicrm\";s:9:\"datestamp\";s:10:\"1265139005\";s:10:\"screenshot\";s:46:\"sites/default/themes/rayCivicrm/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}'),('themes/bluemarine/bluemarine.info','bluemarine','theme','themes/engines/phptemplate/phptemplate.engine',0,0,0,-1,0,'a:13:{s:4:\"name\";s:10:\"Bluemarine\";s:11:\"description\";s:66:\"Table-based multi-column theme with a marine and ash color scheme.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/bluemarine/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:27:\"themes/bluemarine/script.js\";}s:10:\"screenshot\";s:32:\"themes/bluemarine/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}'),('themes/chameleon/chameleon.info','chameleon','theme','themes/chameleon/chameleon.theme',0,0,0,-1,0,'a:12:{s:4:\"name\";s:9:\"Chameleon\";s:11:\"description\";s:42:\"Minimalist tabled theme with light colors.\";s:7:\"regions\";a:2:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";}s:8:\"features\";a:4:{i:0;s:4:\"logo\";i:1;s:7:\"favicon\";i:2;s:4:\"name\";i:3;s:6:\"slogan\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:2:{s:9:\"style.css\";s:26:\"themes/chameleon/style.css\";s:10:\"common.css\";s:27:\"themes/chameleon/common.css\";}}s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"scripts\";a:1:{s:9:\"script.js\";s:26:\"themes/chameleon/script.js\";}s:10:\"screenshot\";s:31:\"themes/chameleon/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}'),('themes/chameleon/marvin/marvin.info','marvin','theme','',0,0,0,-1,0,'a:13:{s:4:\"name\";s:6:\"Marvin\";s:11:\"description\";s:31:\"Boxy tabled theme in all grays.\";s:7:\"regions\";a:2:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";}s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:10:\"base theme\";s:9:\"chameleon\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:33:\"themes/chameleon/marvin/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:33:\"themes/chameleon/marvin/script.js\";}s:10:\"screenshot\";s:38:\"themes/chameleon/marvin/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}'),('themes/garland/garland.info','garland','theme','themes/engines/phptemplate/phptemplate.engine',1,0,0,-1,0,'a:13:{s:4:\"name\";s:7:\"Garland\";s:11:\"description\";s:66:\"Tableless, recolorable, multi-column, fluid width theme (default).\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:11:\"stylesheets\";a:2:{s:3:\"all\";a:1:{s:9:\"style.css\";s:24:\"themes/garland/style.css\";}s:5:\"print\";a:1:{s:9:\"print.css\";s:24:\"themes/garland/print.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:24:\"themes/garland/script.js\";}s:10:\"screenshot\";s:29:\"themes/garland/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}'),('themes/garland/minnelli/minnelli.info','minnelli','theme','themes/engines/phptemplate/phptemplate.engine',0,0,0,-1,0,'a:14:{s:4:\"name\";s:8:\"Minnelli\";s:11:\"description\";s:56:\"Tableless, recolorable, multi-column, fixed width theme.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:10:\"base theme\";s:7:\"garland\";s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:12:\"minnelli.css\";s:36:\"themes/garland/minnelli/minnelli.css\";}}s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:33:\"themes/garland/minnelli/script.js\";}s:10:\"screenshot\";s:38:\"themes/garland/minnelli/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";s:6:\"engine\";s:11:\"phptemplate\";}'),('themes/pushbutton/pushbutton.info','pushbutton','theme','themes/engines/phptemplate/phptemplate.engine',0,0,0,-1,0,'a:13:{s:4:\"name\";s:10:\"Pushbutton\";s:11:\"description\";s:52:\"Tabled, multi-column theme in blue and orange tones.\";s:7:\"version\";s:4:\"6.15\";s:4:\"core\";s:3:\"6.x\";s:6:\"engine\";s:11:\"phptemplate\";s:7:\"project\";s:6:\"drupal\";s:9:\"datestamp\";s:10:\"1260996916\";s:7:\"regions\";a:5:{s:4:\"left\";s:12:\"Left sidebar\";s:5:\"right\";s:13:\"Right sidebar\";s:7:\"content\";s:7:\"Content\";s:6:\"header\";s:6:\"Header\";s:6:\"footer\";s:6:\"Footer\";}s:8:\"features\";a:10:{i:0;s:20:\"comment_user_picture\";i:1;s:7:\"favicon\";i:2;s:7:\"mission\";i:3;s:4:\"logo\";i:4;s:4:\"name\";i:5;s:17:\"node_user_picture\";i:6;s:6:\"search\";i:7;s:6:\"slogan\";i:8;s:13:\"primary_links\";i:9;s:15:\"secondary_links\";}s:11:\"stylesheets\";a:1:{s:3:\"all\";a:1:{s:9:\"style.css\";s:27:\"themes/pushbutton/style.css\";}}s:7:\"scripts\";a:1:{s:9:\"script.js\";s:27:\"themes/pushbutton/script.js\";}s:10:\"screenshot\";s:32:\"themes/pushbutton/screenshot.png\";s:3:\"php\";s:5:\"4.3.5\";}');
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `term_data`
--

DROP TABLE IF EXISTS `term_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `term_data` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext,
  `weight` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  KEY `taxonomy_tree` (`vid`,`weight`,`name`),
  KEY `vid_name` (`vid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `term_data`
--

LOCK TABLES `term_data` WRITE;
/*!40000 ALTER TABLE `term_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `term_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `term_hierarchy`
--

DROP TABLE IF EXISTS `term_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `term_hierarchy` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`,`parent`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `term_hierarchy`
--

LOCK TABLES `term_hierarchy` WRITE;
/*!40000 ALTER TABLE `term_hierarchy` DISABLE KEYS */;
/*!40000 ALTER TABLE `term_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `term_node`
--

DROP TABLE IF EXISTS `term_node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `term_node` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`,`vid`),
  KEY `vid` (`vid`),
  KEY `nid` (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `term_node`
--

LOCK TABLES `term_node` WRITE;
/*!40000 ALTER TABLE `term_node` DISABLE KEYS */;
/*!40000 ALTER TABLE `term_node` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `term_relation`
--

DROP TABLE IF EXISTS `term_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `term_relation` (
  `trid` int(11) NOT NULL AUTO_INCREMENT,
  `tid1` int(10) unsigned NOT NULL DEFAULT '0',
  `tid2` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`trid`),
  UNIQUE KEY `tid1_tid2` (`tid1`,`tid2`),
  KEY `tid2` (`tid2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `term_relation`
--

LOCK TABLES `term_relation` WRITE;
/*!40000 ALTER TABLE `term_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `term_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `term_synonym`
--

DROP TABLE IF EXISTS `term_synonym`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `term_synonym` (
  `tsid` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tsid`),
  KEY `tid` (`tid`),
  KEY `name_tid` (`name`,`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `term_synonym`
--

LOCK TABLES `term_synonym` WRITE;
/*!40000 ALTER TABLE `term_synonym` DISABLE KEYS */;
/*!40000 ALTER TABLE `term_synonym` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `url_alias`
--

DROP TABLE IF EXISTS `url_alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `url_alias` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `src` varchar(128) NOT NULL DEFAULT '',
  `dst` varchar(128) NOT NULL DEFAULT '',
  `language` varchar(12) NOT NULL DEFAULT '',
  PRIMARY KEY (`pid`),
  UNIQUE KEY `dst_language` (`dst`,`language`),
  KEY `src_language` (`src`,`language`)
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
  `up_delete` smallint(6) NOT NULL DEFAULT '0',
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
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `pass` varchar(32) NOT NULL DEFAULT '',
  `mail` varchar(64) DEFAULT '',
  `mode` tinyint(4) NOT NULL DEFAULT '0',
  `sort` tinyint(4) DEFAULT '0',
  `threshold` tinyint(4) DEFAULT '0',
  `theme` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `signature_format` smallint(6) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `access` int(11) NOT NULL DEFAULT '0',
  `login` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `timezone` varchar(8) DEFAULT NULL,
  `language` varchar(12) NOT NULL DEFAULT '',
  `picture` varchar(255) NOT NULL DEFAULT '',
  `init` varchar(64) DEFAULT '',
  `data` longtext,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`),
  KEY `access` (`access`),
  KEY `created` (`created`),
  KEY `mail` (`mail`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (0,'','','',0,0,0,'','',0,0,0,0,0,NULL,'','','',NULL),(1,'senateroot','29269fbe47b581385cb1578094b7a152','bluebird.admin@nysenate.gov',0,0,0,'','',0,1262186593,1286904393,1286903425,1,'-18000','','','sacha@rayogram.com','a:1:{s:13:\"form_build_id\";s:37:\"form-076e62d74d27b984ffd51b68c34c44db\";}');
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
  `value` longtext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variable`
--

LOCK TABLES `variable` WRITE;
/*!40000 ALTER TABLE `variable` DISABLE KEYS */;
INSERT INTO `variable` VALUES ('anonymous','s:9:\"Anonymous\";'),('apachesolr_cron_limit','s:2:\"50\";'),('apachesolr_failure','s:10:\"show_error\";'),('apachesolr_host','s:9:\"localhost\";'),('apachesolr_index_updated','i:1270473640;'),('apachesolr_last_optimize','i:1270473640;'),('apachesolr_mlt_blocks','a:1:{s:7:\"mlt-001\";a:8:{s:4:\"name\";s:14:\"More like this\";s:11:\"num_results\";s:1:\"5\";s:6:\"mlt_fl\";a:2:{s:5:\"title\";s:5:\"title\";s:14:\"taxonomy_names\";s:14:\"taxonomy_names\";}s:9:\"mlt_mintf\";s:1:\"1\";s:9:\"mlt_mindf\";s:1:\"1\";s:9:\"mlt_minwl\";s:1:\"3\";s:9:\"mlt_maxwl\";s:2:\"15\";s:9:\"mlt_maxqt\";s:2:\"20\";}}'),('apachesolr_path','s:5:\"/solr\";'),('apachesolr_port','s:4:\"8180\";'),('apachesolr_read_only','s:1:\"0\";'),('apachesolr_rows','s:2:\"10\";'),('apachesolr_search_default_previous','i:0;'),('apachesolr_search_make_default','s:1:\"1\";'),('apachesolr_search_spellcheck','i:1;'),('apachesolr_search_taxonomy_links','s:1:\"0\";'),('apachesolr_search_taxonomy_previous','i:0;'),('apachesolr_set_nodeapi_messages','s:1:\"1\";'),('apachesolr_site_hash','s:12:\"886ccf6f5ab2\";'),('block_cache','s:1:\"0\";'),('cache','s:1:\"3\";'),('cache_flush','i:1259776801;'),('cache_flush_cache_block','i:1259726411;'),('cache_flush_cache_page','i:1259726411;'),('cache_lifetime','s:1:\"0\";'),('clean_url','s:1:\"1\";'),('clear','s:17:\"Clear cached data\";'),('comment_page','i:0;'),('cron_last','i:1270474005;'),('css_js_query_string','s:20:\"gInP1Sx4oRNEemfCh9aj\";'),('date_default_timezone','s:6:\"-28800\";'),('drupal_http_request_fails','b:1;'),('drupal_private_key','s:64:\"20def726aacf6c85cda4ddb3eba410c9fb8ccd84a55bd1e777ee6a385592b79f\";'),('file_directory_temp','s:4:\"/tmp\";'),('filter_html_1','i:1;'),('front_1_php','i:0;'),('front_1_redirect','s:0:\"\";'),('front_1_text','s:0:\"\";'),('front_1_type','s:6:\"themed\";'),('front_2_php','i:0;'),('front_2_redirect','s:25:\"civicrm/dashboard?reset=1\";'),('front_2_text','s:0:\"\";'),('front_2_type','s:8:\"redirect\";'),('front_3_php','i:0;'),('front_3_redirect','s:25:\"civicrm/dashboard?reset=1\";'),('front_3_text','s:0:\"\";'),('front_3_type','s:8:\"redirect\";'),('front_page_breadcrumb','i:0;'),('front_page_breadcrumb_redirect','s:0:\"\";'),('image_toolkit','s:2:\"gd\";'),('install_profile','s:7:\"default\";'),('install_task','s:4:\"done\";'),('install_time','i:1259725924;'),('javascript_parsed','a:0:{}'),('ldapauth_alter_email_field','s:1:\"0\";'),('ldapauth_disable_pass_change','i:0;'),('ldapauth_forget_passwords','i:1;'),('ldapauth_login_conflict','s:1:\"0\";'),('ldapauth_login_process','s:1:\"1\";'),('ldapauth_sync_passwords','i:0;'),('menu_expanded','a:0:{}'),('menu_masks','a:19:{i:0;i:125;i:1;i:63;i:2;i:62;i:3;i:61;i:4;i:59;i:5;i:31;i:6;i:30;i:7;i:29;i:8;i:24;i:9;i:21;i:10;i:15;i:11;i:14;i:12;i:11;i:13;i:7;i:14;i:6;i:15;i:5;i:16;i:3;i:17;i:2;i:18;i:1;}'),('minimum_word_size','s:1:\"3\";'),('node_cron_comments_scale','d:1;'),('node_cron_views_scale','d:1;'),('node_options_forum','a:1:{i:0;s:6:\"status\";}'),('node_options_page','a:1:{i:0;s:6:\"status\";}'),('node_rank_comments','s:1:\"5\";'),('node_rank_recent','s:1:\"5\";'),('node_rank_relevance','s:1:\"5\";'),('overlap_cjk','i:1;'),('page_cache_max_age','s:3:\"600\";'),('page_compression','s:1:\"1\";'),('preprocess_css','s:1:\"0\";'),('preprocess_js','s:1:\"0\";'),('roleassign_roles','a:11:{i:8;i:8;i:5;i:5;i:12;i:12;i:9;i:9;i:10;i:10;i:7;i:7;i:6;i:6;i:11;i:11;i:13;i:13;i:4;i:0;i:3;i:0;}'),('search_cron_limit','s:2:\"50\";'),('site_footer','s:0:\"\";'),('site_frontpage','s:25:\"civicrm/dashboard?reset=1\";'),('site_mail','s:27:\"bluebird.admin@nysenate.gov\";'),('site_mission','s:0:\"\";'),('site_name','s:8:\"Bluebird\";'),('site_offline','s:1:\"0\";'),('site_offline_message','s:111:\"dev.senate.rayogram.com is currently under maintenance. We should be back shortly. Thank you for your patience.\";'),('site_slogan','s:0:\"\";'),('special_notice_text','s:0:\"\";'),('special_notice_time','s:7:\"one day\";'),('theme_default','s:10:\"rayCivicrm\";'),('theme_settings','a:1:{s:21:\"toggle_node_info_page\";b:0;}'),('update_last_check','i:1284410860;'),('userprotect_administrator_bypass_defaults','a:8:{s:7:\"up_name\";s:7:\"up_name\";s:7:\"up_mail\";s:7:\"up_mail\";s:7:\"up_pass\";s:7:\"up_pass\";s:9:\"up_status\";s:9:\"up_status\";s:8:\"up_roles\";s:8:\"up_roles\";s:9:\"up_openid\";s:9:\"up_openid\";s:9:\"up_delete\";s:9:\"up_delete\";s:7:\"up_edit\";s:7:\"up_edit\";}'),('userprotect_autoprotect','i:0;'),('userprotect_protection_defaults','a:8:{s:9:\"up_status\";s:9:\"up_status\";s:9:\"up_delete\";s:9:\"up_delete\";s:7:\"up_name\";i:0;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:0;s:7:\"up_edit\";i:0;}'),('userprotect_role_protections','a:12:{i:4;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:1;s:7:\"up_pass\";i:1;s:9:\"up_status\";i:1;s:8:\"up_roles\";i:1;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:1;s:7:\"up_edit\";i:1;}i:8;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:2;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:5;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:12;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:9;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:10;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:7;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:6;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:11;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}i:3;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:1;s:7:\"up_pass\";i:1;s:9:\"up_status\";i:1;s:8:\"up_roles\";i:1;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:1;s:7:\"up_edit\";i:1;}i:13;a:8:{s:7:\"up_name\";i:1;s:7:\"up_mail\";i:0;s:7:\"up_pass\";i:0;s:9:\"up_status\";i:0;s:8:\"up_roles\";i:0;s:9:\"up_openid\";i:1;s:9:\"up_delete\";i:0;s:7:\"up_edit\";i:0;}}'),('user_email_verification','i:1;'),('user_mail_password_reset_body','s:419:\"!username,\r\n\r\nA request to reset the password for your account has been made at !site.\r\n\r\nYou may now log in to !uri_brief by clicking on this link or copying and pasting it in your browser:\r\n\r\n!login_url\r\n\r\nThis is a one-time login, so it can be used only once. It expires after one day and nothing will happen if it\'s not used.\r\n\r\nAfter logging in, you will be redirected to !edit_uri so you can change your password.\";'),('user_mail_password_reset_subject','s:52:\"Replacement login information for !username at !site\";'),('user_mail_register_admin_created_body','s:468:\"!username,\r\n\r\nA site administrator at !site has created an account for you. You may now log in to !login_uri using the following username and password:\r\n\r\nusername: !username\r\npassword: !password\r\n\r\nYou may also log in by clicking on this link or copying and pasting it in your browser:\r\n\r\n!login_url\r\n\r\nThis is a one-time login, so it can be used only once.\r\n\r\nAfter logging in, you will be redirected to !edit_uri so you can change your password.\r\n\r\n\r\n--  !site team\";'),('user_mail_register_admin_created_subject','s:52:\"An administrator created an account for you at !site\";'),('user_mail_register_no_approval_required_body','s:442:\"!username,\r\n\r\nThank you for registering at !site. You may now log in to !login_uri using the following username and password:\r\n\r\nusername: !username\r\npassword: !password\r\n\r\nYou may also log in by clicking on this link or copying and pasting it in your browser:\r\n\r\n!login_url\r\n\r\nThis is a one-time login, so it can be used only once.\r\n\r\nAfter logging in, you will be redirected to !edit_uri so you can change your password.\r\n\r\n\r\n--  !site team\";'),('user_mail_register_no_approval_required_subject','s:38:\"Account details for !username at !site\";'),('user_mail_register_pending_approval_body','s:273:\"!username,\r\n\r\nThank you for registering at !site. Your application for an account is currently pending approval. Once it has been approved, you will receive another e-mail containing information about how to log in, set your password, and other details.\r\n\r\n\r\n--  !site team\";'),('user_mail_register_pending_approval_subject','s:63:\"Account details for !username at !site (pending admin approval)\";'),('user_mail_status_activated_body','s:434:\"!username,\r\n\r\nYour account at !site has been activated.\r\n\r\nYou may now log in by clicking on this link or copying and pasting it in your browser:\r\n\r\n!login_url\r\n\r\nThis is a one-time login, so it can be used only once.\r\n\r\nAfter logging in, you will be redirected to !edit_uri so you can change your password.\r\n\r\nOnce you have set your own password, you will be able to log in to !login_uri in the future using:\r\n\r\nusername: !username\r\n\";'),('user_mail_status_activated_notify','i:1;'),('user_mail_status_activated_subject','s:49:\"Account details for !username at !site (approved)\";'),('user_mail_status_blocked_body','s:53:\"!username,\r\n\r\nYour account on !site has been blocked.\";'),('user_mail_status_blocked_notify','i:0;'),('user_mail_status_blocked_subject','s:48:\"Account details for !username at !site (blocked)\";'),('user_mail_status_deleted_body','s:53:\"!username,\r\n\r\nYour account on !site has been deleted.\";'),('user_mail_status_deleted_notify','i:0;'),('user_mail_status_deleted_subject','s:48:\"Account details for !username at !site (deleted)\";'),('user_pictures','s:1:\"0\";'),('user_picture_default','s:0:\"\";'),('user_picture_dimensions','s:5:\"85x85\";'),('user_picture_file_size','s:2:\"30\";'),('user_picture_guidelines','s:0:\"\";'),('user_picture_path','s:8:\"pictures\";'),('user_register','s:1:\"0\";'),('user_registration_help','s:0:\"\";'),('user_signatures','s:1:\"0\";'),('wipe','s:13:\"Re-index site\";');
/*!40000 ALTER TABLE `variable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vocabulary`
--

DROP TABLE IF EXISTS `vocabulary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocabulary` (
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext,
  `help` varchar(255) NOT NULL DEFAULT '',
  `relations` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hierarchy` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `multiple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `required` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `module` varchar(255) NOT NULL DEFAULT '',
  `weight` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid`),
  KEY `list` (`weight`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vocabulary`
--

LOCK TABLES `vocabulary` WRITE;
/*!40000 ALTER TABLE `vocabulary` DISABLE KEYS */;
/*!40000 ALTER TABLE `vocabulary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vocabulary_node_types`
--

DROP TABLE IF EXISTS `vocabulary_node_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocabulary_node_types` (
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`type`,`vid`),
  KEY `vid` (`vid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vocabulary_node_types`
--

LOCK TABLES `vocabulary_node_types` WRITE;
/*!40000 ALTER TABLE `vocabulary_node_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `vocabulary_node_types` ENABLE KEYS */;
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
  `type` varchar(16) NOT NULL DEFAULT '',
  `message` longtext NOT NULL,
  `variables` longtext NOT NULL,
  `severity` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL DEFAULT '',
  `location` text NOT NULL,
  `referer` text,
  `hostname` varchar(128) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wid`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `watchdog`
--

LOCK TABLES `watchdog` WRITE;
/*!40000 ALTER TABLE `watchdog` DISABLE KEYS */;
/*!40000 ALTER TABLE `watchdog` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-10-12 13:35:08
