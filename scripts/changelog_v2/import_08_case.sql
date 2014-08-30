
INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  a.log_action, 'case', a.id, a.log_conn_id, a.log_user_id,
  b.contact_id, a.log_date, CONCAT_WS(CHAR(1), 'Case', c.label)
FROM @LOGDB@.log_civicrm_case a
INNER JOIN @LOGDB@.log_civicrm_case_contact b
ON a.id=b.case_id
LEFT JOIN @LOGDB@.nyss_temp_staging_case c
ON a.id=c.id AND a.log_date BETWEEN c.log_date AND c.log_end_date
WHERE a.log_action != 'Initialization';

