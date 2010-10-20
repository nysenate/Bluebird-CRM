SELECT @domainID        := min(id) FROM civicrm_domain;

-- CRM-6694, CRM-6716
SELECT @navid := id FROM civicrm_navigation WHERE name='Option Lists';
SELECT @wt := max(weight) FROM civicrm_navigation WHERE parent_id=@navid;
INSERT INTO civicrm_navigation
 ( domain_id, label, name, url, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
 ( @domainID, '{ts escape="sql"}Home{/ts}', 'Home', 'civicrm/dashboard&reset=1', NULL, '', NULL, 1, NULL, 0),
 ( @domainID, '{ts escape="sql"}Website Types{/ts}', 'Website Types', 'civicrm/admin/options/website_type&group=website_type&reset=1', 'administer CiviCRM', '', @navid, 1, NULL, @wt + 1);
 
 -- CRM-6726
 UPDATE  civicrm_option_value SET  filter =  0 WHERE  civicrm_option_value.name = 'Print PDF Letter';

--CRM-6655
 UPDATE civicrm_report_instance SET form_values = '{literal}a:37:{s:6:"fields";a:2:{s:12:"display_name";s:1:"1";s:25:"application_received_date";s:1:"1";}s:15:"display_name_op";s:3:"has";s:18:"display_name_value";s:0:"";s:12:"gender_id_op";s:2:"in";s:15:"gender_id_value";a:0:{}s:13:"country_id_op";s:2:"in";s:16:"country_id_value";a:0:{}s:20:"state_province_id_op";s:2:"in";s:23:"state_province_id_value";a:0:{}s:13:"grant_type_op";s:2:"in";s:16:"grant_type_value";a:0:{}s:12:"status_id_op";s:2:"in";s:15:"status_id_value";a:0:{}s:18:"amount_granted_min";s:0:"";s:18:"amount_granted_max";s:0:"";s:17:"amount_granted_op";s:3:"lte";s:20:"amount_granted_value";s:0:"";s:20:"amount_requested_min";s:0:"";s:20:"amount_requested_max";s:0:"";s:19:"amount_requested_op";s:3:"lte";s:22:"amount_requested_value";s:0:"";s:34:"application_received_date_relative";s:1:"0";s:30:"application_received_date_from";s:0:"";s:28:"application_received_date_to";s:0:"";s:28:"money_transfer_date_relative";s:1:"0";s:24:"money_transfer_date_from";s:0:"";s:22:"money_transfer_date_to";s:0:"";s:23:"grant_due_date_relative";s:1:"0";s:19:"grant_due_date_from";s:0:"";s:17:"grant_due_date_to";s:0:"";s:11:"description";s:12:"Grant Report";s:13:"email_subject";s:0:"";s:8:"email_to";s:0:"";s:8:"email_cc";s:0:"";s:10:"permission";s:16:"access CiviGrant";s:6:"groups";s:0:"";s:9:"domain_id";i:1;}{/literal}'  WHERE  report_id = 'grant';

-- CRM-6663
ALTER TABLE `civicrm_pledge_payment` 
      ADD `actual_amount` decimal(20,2) DEFAULT NULL COMMENT 'Actual amount that is paid as the Pledged installment amount.' AFTER `scheduled_amount`;
UPDATE `civicrm_pledge_payment` SET actual_amount = scheduled_amount WHERE contribution_id IS NOT NULL;

ALTER TABLE `civicrm_pledge` 
      ADD `original_installment_amount` decimal(20,2) NOT NULL COMMENT 'Original amount for each of the installments.' AFTER `amount`;
UPDATE `civicrm_pledge` SET `original_installment_amount` = `amount` / `installments`;

--CRM-6757
UPDATE `civicrm_option_value` 
 SET   {localize field='label'}label = name{/localize}
 WHERE  name IN ('day','month','week','year');


-- NYSS Upgrade v1.1

-- NYSS-Navigation Menu
UPDATE civicrm_navigation SET url = 'civicrm/contact/deduperules&reset=1' WHERE name = 'Merge Duplicate Contacts';
UPDATE civicrm_navigation SET url = 'civicrm/contact/deduperules&reset=1' WHERE name = 'Find and Merge Duplicate Contacts';

-- NYSS-Word Replacements
{literal}
UPDATE civicrm_domain SET locale_custom_strings = 'a:1:{s:5:"en_US";a:2:{s:7:"enabled";a:2:{s:13:"wildcardMatch";a:13:{s:7:"CiviCRM";s:8:"Bluebird";s:9:"Full-text";s:13:"Find Anything";s:16:"Addt\'l Address 1";s:15:"Mailing Address";s:16:"Addt\'l Address 2";s:8:"Building";s:73:"Supplemental address info, e.g. c/o, department name, building name, etc.";s:70:"Department name, building name, complex, or extension of company name.";s:7:"deatils";s:7:"details";s:11:"sucessfully";s:12:"successfully";s:40:"groups, contributions, memberships, etc.";s:27:"groups, relationships, etc.";s:18:"email OR an OpenID";s:5:"email";s:6:"Client";s:11:"Constituent";s:6:"client";s:11:"constituent";s:9:"Job title";s:9:"Job Title";s:9:"Nick Name";s:8:"Nickname";}s:10:"exactMatch";a:4:{s:8:"Position";s:9:"Job Title";s:2:"Id";s:2:"ID";s:6:"Client";s:11:"Constituent";s:6:"client";s:11:"constituent";}}s:8:"disabled";a:2:{s:13:"wildcardMatch";a:0:{}s:10:"exactMatch";a:0:{}}}}' 
WHERE id = 1;
{/literal}

-- NYSS-Dashboard
-- Twitter-disable fullscreen
UPDATE civicrm_dashboard SET is_fullscreen = 0 WHERE id = 4;

-- Fix all/my cases class path
UPDATE civicrm_dashboard SET url = 'civicrm/dashlet/MyCases&reset=1&snippet=4' WHERE id = 2;
UPDATE civicrm_dashboard SET url = 'civicrm/dashlet/AllCases&reset=1&snippet=4' WHERE id = 3;
UPDATE civicrm_menu SET path = 'civicrm/dashlet/AllCases', page_callback = 's:25:"CRM_Dashlet_Page_AllCases";' WHERE title = 'All Cases Dashlet';
UPDATE civicrm_menu SET path = 'civicrm/dashlet/MyCases', page_callback = 's:24:"CRM_Dashlet_Page_MyCases";' WHERE title = 'Case Dashlet';

-- NYSS-Include/Exclude search
UPDATE civicrm_navigation SET is_active = 1 WHERE id = 206;

-- NYSS-Activity email template
UPDATE civicrm_msg_template SET msg_subject = '[Bluebird]{if $idHash} [case #{$idHash}]{/if} {$activitySubject}' WHERE id = 1;

-- NYSS-Option values
INSERT INTO `civicrm_option_value` (`id`, `option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`) VALUES
(1064, 64, 'Satellite Office', 'Satellite Office', 'Satellite_Office', NULL, NULL, 0, 19, NULL, 0, 0, 1, NULL, NULL, NULL),
(1065, 71, 'Satellite Office', 'Satellite Office', 'Satellite_Office', NULL, NULL, 0, 8, NULL, 0, 0, 1, NULL, NULL, NULL);

-- NYSS-District Information custom group default open
UPDATE civicrm_custom_group SET collapse_display = 1, collapse_adv_display = 0, help_pre = NULL, help_post = NULL WHERE id = 7;




