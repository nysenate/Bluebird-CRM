SELECT @option_group_id_activity_type := max(id) from civicrm_option_group where name = 'activity_type';
SELECT @max_val    := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  = @option_group_id_activity_type;
SELECT @max_wt     := max(weight) from civicrm_option_value where option_group_id=@option_group_id_activity_type;

INSERT INTO `civicrm_option_value`
    (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`)
VALUES
    (@option_group_id_activity_type, 'Letter of Inquiry', (SELECT @max_val := @max_val+1), 'Letter of Inquiry', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), NULL, 0, 0, 1, NULL, NULL),
    (@option_group_id_activity_type, 'Proposal', (SELECT @max_val := @max_val+1), 'Proposal', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), NULL, 0, 0, 1, NULL, NULL),
    (@option_group_id_activity_type, 'Report', (SELECT @max_val := @max_val+1), 'Report', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), NULL, 0, 0, 1, NULL, NULL),
    (@option_group_id_activity_type, 'Press Release', (SELECT @max_val := @max_val+1), 'Press Release', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), NULL, 0, 0, 1, NULL, NULL),
    (@option_group_id_activity_type, 'Volunteer Time', (SELECT @max_val := @max_val+1), 'Volunteer Time', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), 'Volunteer Hours', 0, 0, 1, NULL, NULL),
    (@option_group_id_activity_type, 'Door Knock', (SELECT @max_val := @max_val+1), 'Door Knock', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), 'Door Knock', 0, 0, 1, NULL, NULL),
    (@option_group_id_activity_type, 'Direct Action', (SELECT @max_val := @max_val+1), 'Direct Action', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), NULL, 0, 0, 1, NULL, NULL);

-- Add event types
SELECT @option_group_id_event_type := max(id) from civicrm_option_group where name = 'event_type';
SELECT @max_val    := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  = @option_group_id_event_type;
SELECT @max_wt     := max(weight) from civicrm_option_value where option_group_id=@option_group_id_event_type;

INSERT INTO `civicrm_option_value`
    (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`)
VALUES
(@option_group_id_event_type, 'Direct Action', (SELECT @max_val := @max_val+1), 'Performance', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), NULL, 0, 0, 1, NULL, NULL),
(@option_group_id_event_type, 'Lobby', (SELECT @max_val := @max_val+1), 'Workshop', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), NULL, 0, 0, 1, NULL, NULL),
(@option_group_id_event_type, 'Campaign', (SELECT @max_val := @max_val+1), 'Campaign', NULL, 0, 0, (SELECT @max_wt := @max_wt+1), NULL, 0, 0, 1, NULL, NULL);

-- Add inserts for reports
SELECT @option_group_id_report         := max(id) from civicrm_option_group where name = 'report_template';

INSERT INTO civicrm_option_value
    (option_group_id, label, value, name, weight, description, is_active, component_id)
VALUES
    (@option_group_id_report, 'Phonebank List', 'contact/phonebank', 'Canvass_Report_Form_CallList', 23, 'Phonebank List', 1, NULL),
    (@option_group_id_report, 'Canvass Walk List', 'contact/walklist', 'Canvass_Report_Form_WalkList', 23, 'Canvass Walk List', 1, NULL);

-- We will need to add inserts for an instance of each report once we can load the report and save off an instance and then dump the sql. dgg
-- INSERT INTO `civicrm_report_instance`
--    (title, report_id, description, permission, form_values)
-- VALUES
