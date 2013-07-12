CREATE TABLE IF NOT EXISTS `nyss_inbox_messages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `message_id` int(10) NOT NULL,
  `imap_id` int(10) NOT NULL,
  `sender_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sender_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `forwarder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(2) NOT NULL,
  `matcher` int(10) NOT NULL,
  `matched_to` int(10) NOT NULL,
  `activity_id` int(10) NOT NULL,
  `format` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `debug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email_date` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  INDEX sender_index (`sender_email`),
  INDEX status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `nyss_inbox_attachments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `email_id` int(10) NOT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_full` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rejection` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mime_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(8) NOT NULL,
  `ext` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
ALTER TABLE `nyss_inbox_attachments` ADD FOREIGN KEY (email_id) REFERENCES `nyss_inbox_messages` (`id`);
