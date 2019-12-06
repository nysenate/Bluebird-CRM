DELETE FROM civicrm_uf_group
WHERE name IN ('Contact_Summary_Individual', 'Contact_Summary_Demographics', 'Contact_Summary_Privacy_Notes', 'Contact_Summary_Additional_Constituent_Information');

INSERT INTO civicrm_uf_group (id, is_active, group_type, title, description, help_pre, help_post, limit_listings_group_id, post_URL, add_to_group_id, add_captcha, is_map, is_edit_link, is_uf_link, is_update_dupe, cancel_URL, is_cms_user, notify, is_reserved, name, created_id, created_date, is_proximity_search, cancel_button_text, submit_button_text, frontend_title, add_cancel_button)
VALUES
(21, 1, 'Individual,Contact', 'Contact Summary: Individual', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 1, NULL, 0, NULL, NULL, 'Contact_Summary_Individual', 1, '2019-12-07 00:00:00', 0, NULL, NULL, 'Contact Summary: Details', 1),
(22, 1, 'Individual', 'Contact Summary: Individual Demographics', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 1, NULL, 0, NULL, NULL, 'Contact_Summary_Demographics', 1, '2019-12-07 00:00:00', 0, NULL, NULL, 'Contact Summary: Details', 1),
(24, 1, 'Contact', 'Privacy Notes', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 1, NULL, 0, NULL, NULL, 'Contact_Summary_Privacy_Notes', 1, '2019-12-07 00:00:00', 0, NULL, NULL, 'Privacy Notes', 1),
(25, 1, 'Individual', 'Additional Constituent Information', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 1, NULL, 0, NULL, NULL, 'Contact_Summary_Additional_Constituent_Information', 1, '2019-12-07 00:00:00', 0, NULL, NULL, 'Additional Constituent Information', 1);

DELETE FROM civicrm_uf_join WHERE module IN ('Profile', 'Contact Summary') AND entity_id IN (21,22,24,25);

INSERT IGNORE INTO civicrm_uf_join (id, is_active, module, entity_table, entity_id, weight, uf_group_id, module_data) VALUES
(26, 1, 'Profile', NULL, NULL, 1, 21, NULL),
(27, 1, 'Contact Summary', NULL, NULL, 1, 21, NULL),
(28, 1, 'Profile', NULL, NULL, 11, 22, NULL),
(29, 1, 'Contact Summary', NULL, NULL, 11, 22, NULL),
(32, 1, 'Profile', NULL, NULL, 1, 24, NULL),
(33, 1, 'Contact Summary', NULL, NULL, 1, 24, NULL),
(34, 1, 'Profile', NULL, NULL, 1, 25, NULL),
(35, 1, 'Contact Summary', NULL, NULL, 1, 25, NULL);

DELETE FROM civicrm_uf_field WHERE uf_group_id IN (21,22,24,25);

INSERT INTO civicrm_uf_field (id, uf_group_id, field_name, is_active, is_view, is_required, weight, help_post, help_pre, visibility, in_selector, is_searchable, location_type_id, phone_type_id, website_type_id, label, field_type, is_reserved, is_multi_summary) VALUES
(158, 21, 'current_employer', 1, 0, 0, 1, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Employer', 'Individual', NULL, 0),
(159, 21, 'job_title', 1, 0, 0, 2, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Job Title', 'Individual', NULL, 0),
(160, 21, 'nick_name', 1, 0, 0, 3, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Nickname', 'Contact', NULL, 0),
(161, 21, 'custom_60', 1, 0, 0, 4, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Contact Source', 'Individual', NULL, 0),
(162, 21, 'contact_source', 1, 0, 0, 5, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Other Source', 'Contact', NULL, 0),
(163, 21, 'custom_42', 1, 0, 0, 6, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Individual Category', 'Individual', NULL, 0),
(178, 22, 'gender_id', 1, 0, 0, 1, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Gender', 'Individual', NULL, 0),
(179, 22, 'custom_45', 1, 0, 0, 2, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Other Gender', 'Individual', NULL, 0),
(180, 22, 'birth_date', 1, 0, 0, 3, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Birth Date', 'Individual', NULL, 0),
(181, 22, 'is_deceased', 1, 0, 0, 4, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Deceased', 'Individual', NULL, 0),
(182, 22, 'deceased_date', 1, 0, 0, 5, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Deceased Date', 'Individual', NULL, 0),
(183, 22, 'custom_63', 1, 0, 0, 6, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Religion', 'Individual', NULL, 0),
(184, 22, 'custom_58', 1, 0, 0, 7, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Ethnicity', 'Individual', NULL, 0),
(185, 22, 'custom_62', 1, 0, 0, 8, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Other Ethnicity', 'Individual', NULL, 0),
(201, 24, 'custom_64', 1, 0, 0, 1, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Privacy Options Note', 'Contact', NULL, 0),
(202, 25, 'custom_18', 1, 0, 0, 1, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Active Constituent?', 'Individual', NULL, 0),
(203, 25, 'custom_17', 1, 0, 0, 2, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Interest in Volunteering?', 'Individual', NULL, 0),
(204, 25, 'custom_19', 1, 0, 0, 3, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Friend of the Senator?', 'Individual', NULL, 0),
(205, 25, 'custom_23', 1, 0, 0, 4, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Voter Registration Status', 'Individual', NULL, 0),
(206, 25, 'custom_24', 1, 0, 0, 5, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'BOE Date of Registration', 'Individual', NULL, 0),
(207, 25, 'custom_16', 1, 0, 0, 6, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Professional Accreditations', 'Individual', NULL, 0),
(208, 25, 'custom_20', 1, 0, 0, 7, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Skills/Areas of Interest', 'Individual', NULL, 0),
(209, 25, 'custom_21', 1, 0, 0, 8, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Honors and Awards', 'Individual', NULL, 0),
(210, 25, 'custom_61', 1, 0, 0, 9, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, NULL, 'Record Type', 'Individual', NULL, 0);


TRUNCATE TABLE civicrm_contact_layout;
INSERT INTO civicrm_contact_layout (id, label, contact_type, contact_sub_type, groups, weight, blocks, tabs)
VALUES (1, 'Default: Individual', 'Individual', NULL, NULL, 3, '[[[{\"name\":\"profile.Contact_Summary_Individual\",\"title\":\"Contact Summary: Individual\",\"collapsible\":false,\"collapsed\":false,\"showTitle\":false},{\"name\":\"core.Address\",\"title\":\"Address\"},{\"name\":\"profile.Contact_Summary_Additional_Constituent_Information\",\"title\":\"Additional Constituent Information\",\"collapsible\":true}],[{\"name\":\"profile.Contact_Summary_Demographics\",\"title\":\"Demographics\",\"collapsible\":false,\"collapsed\":false,\"showTitle\":false},{\"name\":\"core.Email\",\"title\":\"Email\"},{\"name\":\"core.Phone\",\"title\":\"Phone\"},{\"name\":\"core.CommunicationPreferences\",\"title\":\"Communication Preferences\"},{\"name\":\"profile.Contact_Summary_Privacy_Notes\",\"title\":\"Privacy Notes\",\"collapsible\":false,\"collapsed\":false,\"showTitle\":false},{\"name\":\"custom.Attachments\",\"title\":\"File Attachments\",\"collapsible\":true,\"collapsed\":false}]]]', '[{\"id\":\"summary\",\"is_active\":true},{\"id\":\"activity\",\"is_active\":true},{\"id\":\"case\",\"is_active\":true},{\"id\":\"mailing\",\"is_active\":true},{\"id\":\"rel\",\"is_active\":true},{\"id\":\"group\",\"is_active\":true},{\"id\":\"note\",\"is_active\":true},{\"id\":\"tag\",\"is_active\":true},{\"id\":\"custom_9\",\"is_active\":true,\"icon\":\"crm-i fa-user\"},{\"id\":\"nyss_web_activitystream\",\"is_active\":true,\"icon\":\"crm-i fa-globe\"},{\"id\":\"nyss_web_tags\",\"is_active\":true,\"icon\":\"crm-i fa-tags\"},{\"id\":\"log\",\"is_active\":true}]'),
(2, 'Default: Household', 'Household', NULL, NULL, 1, '[[[{\"name\":\"core.ContactInfo\",\"title\":\"Employer, Nickname, Source\"},{\"name\":\"core.Address\",\"title\":\"Address\"},{\"name\":\"custom.Attachments\",\"title\":\"File Attachments\",\"collapsible\":true,\"collapsed\":false}],[{\"name\":\"core.Email\",\"title\":\"Email\"},{\"name\":\"core.Phone\",\"title\":\"Phone\"},{\"name\":\"core.CommunicationPreferences\",\"title\":\"Communication Preferences\"}]]]', '[{\"id\":\"summary\",\"is_active\":true},{\"id\":\"activity\",\"is_active\":true},{\"id\":\"case\",\"is_active\":true},{\"id\":\"mailing\",\"is_active\":true},{\"id\":\"rel\",\"is_active\":true},{\"id\":\"group\",\"is_active\":true},{\"id\":\"note\",\"is_active\":true},{\"id\":\"tag\",\"is_active\":true},{\"id\":\"log\",\"is_active\":true},{\"id\":\"custom_9\",\"is_active\":true},{\"id\":\"nyss_web_activitystream\",\"is_active\":true,\"icon\":\"crm-i fa-globe\"},{\"id\":\"nyss_web_tags\",\"is_active\":true,\"icon\":\"crm-i fa-tags\"}]'),
(3, 'Default: Organization', 'Organization', NULL, NULL, 2, '[[[{\"name\":\"core.ContactInfo\",\"title\":\"Employer, Nickname, Source\"},{\"name\":\"core.Address\",\"title\":\"Address\"},{\"name\":\"custom.Organization_Constituent_Information\",\"title\":\"Organization Constituent Information\",\"collapsible\":true,\"collapsed\":false}],[{\"name\":\"core.Email\",\"title\":\"Email\"},{\"name\":\"core.Phone\",\"title\":\"Phone\"},{\"name\":\"core.CommunicationPreferences\",\"title\":\"Communication Preferences\"},{\"name\":\"custom.Attachments\",\"title\":\"File Attachments\",\"collapsible\":true,\"collapsed\":false}]]]', '[{\"id\":\"summary\",\"is_active\":true},{\"id\":\"activity\",\"is_active\":true},{\"id\":\"case\",\"is_active\":true},{\"id\":\"mailing\",\"is_active\":true},{\"id\":\"rel\",\"is_active\":true},{\"id\":\"group\",\"is_active\":true},{\"id\":\"note\",\"is_active\":true},{\"id\":\"tag\",\"is_active\":true},{\"id\":\"log\",\"is_active\":true},{\"id\":\"custom_9\",\"is_active\":true},{\"id\":\"nyss_web_activitystream\",\"is_active\":true,\"icon\":\"crm-i fa-globe\"},{\"id\":\"nyss_web_tags\",\"is_active\":true,\"icon\":\"crm-i fa-tags\"}]');
