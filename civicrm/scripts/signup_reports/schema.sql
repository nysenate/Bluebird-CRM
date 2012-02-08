CREATE TABLE `list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `senator` (
  `nid` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `district` int(10) unsigned DEFAULT NULL,
  `list_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`nid`),
  KEY `title` (`title`),
  KEY `district` (`district`),
  KEY `list_id` (`list_id`),
  CONSTRAINT `senator_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `list` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `committee` (
  `nid` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `chair_nid` int(10) unsigned DEFAULT NULL,
  `list_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`nid`),
  KEY `title` (`title`),
  KEY `list_id` (`list_id`),
  KEY `chair_nid` (`chair_nid`),
  CONSTRAINT `committee_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `list` (`id`),
  CONSTRAINT `committee_ibfk_2` FOREIGN KEY (`chair_nid`) REFERENCES `senator` (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `person` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nid` int(10) unsigned DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `district` int(10) unsigned DEFAULT NULL,
  `bronto` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `first_name` (`first_name`),
  KEY `last_name` (`last_name`),
  KEY `address1` (`address1`),
  KEY `address2` (`address2`),
  KEY `city` (`city`),
  KEY `state` (`state`),
  KEY `zip` (`zip`),
  KEY `email` (`email`),
  KEY `status` (`status`),
  KEY `created` (`created`),
  KEY `modified` (`modified`),
  KEY `district` (`district`),
  KEY `nid` (`nid`),
  KEY `bronto` (`bronto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `signup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `list_id` int(10) unsigned DEFAULT NULL,
  `person_id` int(10) unsigned DEFAULT NULL,
  `reported` tinyint(1) DEFAULT '0',
  `dt_reported` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unqiue_signup` (`list_id`,`person_id`),
  KEY `person_id` (`person_id`),
  KEY `reported` (`reported`),
  KEY `dt_reported` (`dt_reported`),
  CONSTRAINT `signup_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE,
  CONSTRAINT `signup_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `list` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `issue` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `subscription` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int(10) unsigned DEFAULT NULL,
  `issue_id` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `person_id` (`person_id`,`issue_id`),
  KEY `issue_id` (`issue_id`),
  CONSTRAINT `subscription_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_ibfk_1` FOREIGN KEY (`issue_id`) REFERENCES `issue` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;