CREATE TABLE IF NOT EXISTS `civicrm_mosaico_msg_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `msg_tpl_id` int(10) unsigned NOT NULL,
  `hash_key` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `html` longtext NOT NULL,
  `metadata` longtext NOT NULL,
  `template` longtext NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_civicrm_mosaico_msg_template_msg_tpl_id` FOREIGN KEY (`msg_tpl_id`) REFERENCES `civicrm_msg_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `civicrm_mosaico_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Template ID',
  `title` varchar(255)    COMMENT 'Title',
  `base` varchar(64)    COMMENT 'Name of the Mosaico base template (e.g. versafix-1)',
  `html` longtext    COMMENT 'Fully renderd HTML',
  `metadata` longtext    COMMENT 'Mosaico metadata (JSON)',
  `content` longtext    COMMENT 'Mosaico content (JSON)' ,
  `msg_tpl_id` int unsigned NULL COMMENT 'FK to civicrm_msg_template.',
   PRIMARY KEY ( `id` ),
   CONSTRAINT FK_civicrm_mosaico_template_msg_tpl_id FOREIGN KEY (`msg_tpl_id`) REFERENCES `civicrm_msg_template`(`id`) ON DELETE SET NULL
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
