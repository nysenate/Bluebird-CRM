CREATE TABLE IF NOT EXISTS `nyss_web_msg_activity`
(`id` INT(10) NOT NULL AUTO_INCREMENT , `note_id` INT(10) NOT NULL , `activity_id` INT(10) NOT NULL , PRIMARY KEY (`id`), INDEX `idx_note_id` (`note_id`), INDEX `idx_activity_id` (`activity_id`))
ENGINE = InnoDB;
