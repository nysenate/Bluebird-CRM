/*
 * Convert ARCHIVE tables to MyISAM and add indexes.
 */

ALTER TABLE `log_civicrm_address` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_contact` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_dashboard_contact` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_email` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_entity_tag` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_group` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_group_contact` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_note` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_phone` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_relationship` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_value_constituent_information_1` ENGINE=MyISAM;
ALTER TABLE `log_civicrm_value_district_information_7` ENGINE=MyISAM;

ALTER TABLE `log_civicrm_address`
    ADD INDEX `id` (`id` ASC),
    ADD INDEX `contact_id` (`contact_id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_contact`
    ADD INDEX `id` (`id` ASC),
    ADD INDEX `sort_name` (`sort_name` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_dashboard_contact`
    ADD INDEX `contact_id` (`contact_id` ASC),
    ADD INDEX `dashboard_id` (`dashboard_id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_email`
    ADD INDEX `contact_id` (`contact_id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_entity_tag`
    ADD INDEX `entity_id` (`entity_id` ASC),
    ADD INDEX `entity_table` (`entity_table` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_group`
    ADD INDEX `id` (`id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_group_contact`
    ADD INDEX `group_id` (`group_id` ASC),
    ADD INDEX `contact_id` (`contact_id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_note`
    ADD INDEX `entity_id` (`entity_id` ASC),
    ADD INDEX `entity_table` (`entity_table` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_phone`
    ADD INDEX `contact_id` (`contact_id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_relationship`
    ADD INDEX `contact_id_a` (`contact_id_a` ASC),
    ADD INDEX `contact_id_b` (`contact_id_b` ASC),
    ADD INDEX `relationship_type_id` (`relationship_type_id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_value_constituent_information_1`
    ADD INDEX `entity_id` (`entity_id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);

ALTER TABLE `log_civicrm_value_district_information_7`
    ADD INDEX `entity_id` (`entity_id` ASC),
    ADD INDEX `log_date` (`log_date` ASC),
    ADD INDEX `log_conn_id` (`log_conn_id` ASC),
    ADD INDEX `log_user_id` (`log_user_id` ASC),
    ADD INDEX `log_action` (`log_action` ASC),
    ADD INDEX `log_job_id` (`log_job_id` ASC);
