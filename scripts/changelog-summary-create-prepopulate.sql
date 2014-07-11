DROP TABLE IF EXISTS {{CIVIDB}}.civicrm_changelog_sequence;
CREATE TABLE {{CIVIDB}}.civicrm_changelog_sequence (
  `seq` BIGINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

INSERT INTO {{CIVIDB}}.civicrm_changelog_sequence (`seq`) VALUES (1);

DROP FUNCTION IF EXISTS {{CIVIDB}}.fnGetChangelogSequence;
CREATE DEFINER=CURRENT_USER FUNCTION {{CIVIDB}}.`fnGetChangelogSequence`()
	RETURNS bigint(20)
	LANGUAGE SQL
	NOT DETERMINISTIC
	CONTAINS SQL
	SQL SECURITY DEFINER
	COMMENT ''
BEGIN
	IF @civicrm_changelog_sequence IS NULL THEN
	 	BEGIN
	 		SELECT `seq` INTO @civicrm_changelog_sequence FROM `civicrm_changelog_sequence` ORDER BY `seq` DESC LIMIT 1;
	 		UPDATE civicrm_changelog_sequence SET `seq`=`seq`+1;
	 	END;
	END IF;
	RETURN @civicrm_changelog_sequence;
END;

DROP TRIGGER IF EXISTS {{CIVIDB}}.civicrm_changelog_summary_before_insert;

DROP TABLE IF EXISTS {{CIVIDB}}.civicrm_changelog_summary;
CREATE TABLE {{CIVIDB}}.civicrm_changelog_summary (
 	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 	`log_id` INT(10) UNSIGNED NOT NULL,
	`log_action` ENUM('Initialization','Insert','Update','Delete') COLLATE utf8_unicode_ci DEFAULT NULL,
	`log_type` VARCHAR(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'perhaps make this enum',
	`log_type_label` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'for arbitrary grouping in summary',
	`log_user_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'contact id for who changed the record',
	`log_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`log_conn_id` INT(11) NULL DEFAULT NULL,
	`log_change_seq` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'unique-per-session value generated for each record',
	`altered_contact_id` INT(10) UNSIGNED NOT NULL COMMENT 'contact id for record being changed',
	INDEX `idx_altered_contact_id` (`altered_contact_id`),
	INDEX `idx_log_user_id` (`log_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* setting the delimiter is not necessary when passing the query through PHP's mysqli::multi_query() */
/* DELIMITER // */
CREATE
	DEFINER = CURRENT_USER
	TRIGGER {{CIVIDB}}.civicrm_changelog_summary_before_insert
	BEFORE INSERT
	ON {{CIVIDB}}.civicrm_changelog_summary FOR EACH ROW
	BEGIN
		CASE NEW.`log_type`
		  WHEN 'log_civicrm_email' THEN SET NEW.`log_type_label`='Contact';
		  WHEN 'log_civicrm_address' THEN SET NEW.`log_type_label`='Contact';
		  WHEN 'log_civicrm_value_constituent_information_1' THEN SET NEW.`log_type_label`='Contact';
		  WHEN 'log_civicrm_value_district_information_7' THEN SET NEW.`log_type_label`='Contact';
			WHEN 'log_civicrm_group_contact' THEN SET NEW.`log_type_label`='Group';
			WHEN 'log_civicrm_entity_tag' THEN SET NEW.`log_type_label`='Tag';
			ELSE SET NEW.`log_type_label`='';
		END CASE;
    SET NEW.`log_change_seq`=fnGetChangelogSequence();
	END;
/* setting the delimiter is not necessary when passing the query through PHP's mysqli::multi_query() */
/* //
DELIMITER ; */

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT id, log_action, 'log_civicrm_contact' AS log_type, log_user_id, log_date, id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_contact
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT id, log_action, 'log_civicrm_email' AS log_type, log_user_id, log_date, contact_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_email
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT id, log_action, 'log_civicrm_phone' AS log_type, log_user_id, log_date, contact_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_phone
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT id, log_action, 'log_civicrm_address' AS log_type, log_user_id, log_date, contact_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_address
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT
	  id,
		log_action,
		concat('log_civicrm_note',
				case entity_table
					when 'civicrm_contact' then ''
					when 'civicrm_note' then '_comment'
					else '_unknown' end
				) AS log_type,
		log_user_id,
		log_date,
		entity_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_note
	WHERE (log_action != 'Initialization') AND (entity_table IN ('civicrm_contact','civicrm_note'));

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT id, log_action, 'log_civicrm_group_contact' AS log_type, log_user_id, log_date, contact_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_group_contact
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT id, log_action, 'log_civicrm_entity_tag' AS log_type, log_user_id, log_date, entity_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_entity_tag
	WHERE (log_action != 'Initialization') AND (entity_table = 'civicrm_contact');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT id, log_action, 'log_civicrm_relationship' AS log_type, log_user_id, log_date, contact_id_a AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_relationship
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT
	  a.id,
		a.log_action,
		CONCAT('log_civicrm_activity_for_',
			case b.record_type_id
				when 1 then 'target'
				when 2 then 'source'
				when 3 then 'assignee'
				else 'unknown'
				end
			) AS log_type,
		a.log_user_id,
		a.log_date,
		b.contact_id as altered_contact_id,a.log_conn_id
	FROM
		{{LOGDB}}.log_civicrm_activity a INNER JOIN {{LOGDB}}.log_civicrm_activity_contact b
			ON a.id=b.activity_id AND b.record_type_id IN (1,2,3)
	WHERE (a.log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT a.id, a.log_action, 'log_civicrm_case' AS log_type, a.log_user_id, a.log_date, b.contact_id as altered_contact_id,a.`log_conn_id`
	FROM
		{{LOGDB}}.log_civicrm_case a
			INNER JOIN {{LOGDB}}.log_civicrm_case_contact b
				ON a.id=b.case_id
	WHERE (a.log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT
	  id,
		log_action,
		'log_civicrm_value_constituent_information_1' AS log_type,
		log_user_id,
		log_date,
		entity_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_value_constituent_information_1
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT
	  id,
		log_action,
		'log_civicrm_value_organization_constituent_informa_3' AS log_type,
		log_user_id,
		log_date,
		entity_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_value_organization_constituent_informa_3
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT
	  id,
		log_action,
		'log_civicrm_value_attachments_5' AS log_type,
		log_user_id,
		log_date,
		entity_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_value_attachments_5
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT
	  id,
		log_action,
		'log_civicrm_value_contact_details_8' AS log_type,
		log_user_id,
		log_date,
		entity_id AS altered_contact_id,`log_conn_id`
	FROM {{LOGDB}}.log_civicrm_value_contact_details_8
	WHERE (log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT
	  a.id,
		a.log_action,
		'log_civicrm_value_district_information_7' AS log_type,
		a.log_user_id,
		a.log_date,
		b.contact_id as altered_contact_id,a.`log_conn_id`
	FROM
		{{LOGDB}}.log_civicrm_value_district_information_7 a
			INNER JOIN {{LOGDB}}.log_civicrm_address b
				ON a.entity_id=b.id
	WHERE (a.log_action != 'Initialization');

INSERT IGNORE INTO {{CIVIDB}}.civicrm_changelog_summary
	(`log_id`,`log_action`,`log_type`,`log_user_id`,`log_date`,`altered_contact_id`,`log_conn_id`)
	SELECT
	  a.id,
		a.log_action,
		CONCAT('log_civicrm_value_activity_details_6_for_',
			case b.record_type_id
				when 1 then 'target'
				when 2 then 'source'
				when 3 then 'assignee'
				else 'unknown'
				end
			) AS log_type,
		a.log_user_id,
		a.log_date,
		b.contact_id as altered_contact_id,a.log_conn_id
	FROM
		{{LOGDB}}.log_civicrm_value_activity_details_6 a INNER JOIN {{LOGDB}}.log_civicrm_activity_contact b
			ON a.entity_id=b.activity_id AND b.record_type_id IN (1,2,3)
	WHERE (a.log_action != 'Initialization');
