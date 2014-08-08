
INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  log_action, 'note', id, log_conn_id, log_user_id,
  entity_id, log_date, CONCAT_WS(CHAR(1), 'Note', subject)
FROM @LOGDB@.log_civicrm_note
WHERE log_action != 'Initialization' AND entity_table = 'civicrm_contact';


INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  a.log_action, 'note', a.id, a.log_conn_id, a.log_user_id,
  b_alias.entity_id, a.log_date, CONCAT_WS(CHAR(1), 'Comment', a.subject)
FROM @LOGDB@.log_civicrm_note a
INNER JOIN (SELECT DISTINCT b.id, b.entity_id FROM civicrm_note b
            WHERE b.entity_table='civicrm_contact') b_alias
ON a.entity_id = b_alias.id 
WHERE a.log_action != 'Initialization' AND a.entity_table = 'civicrm_note';

