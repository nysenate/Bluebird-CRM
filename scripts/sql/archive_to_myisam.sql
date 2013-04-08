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

