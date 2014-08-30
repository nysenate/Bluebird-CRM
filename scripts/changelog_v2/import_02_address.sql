
INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  log_action, 'address', id, log_conn_id, log_user_id,
  contact_id, log_date, 'Contact'
FROM @LOGDB@.log_civicrm_address
WHERE log_action != 'Initialization';


INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  a.log_action, 'value_district_information_7', a.id, a.log_conn_id, a.log_user_id,
  b.contact_id, a.log_date, 'Contact'
FROM @LOGDB@.log_civicrm_value_district_information_7 a
INNER JOIN @LOGDB@.log_civicrm_address b
ON a.entity_id=b.id
WHERE a.log_action != 'Initialization';

