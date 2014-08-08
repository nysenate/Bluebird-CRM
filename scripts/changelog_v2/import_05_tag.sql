
INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  a.log_action, 'entity_tag', a.id, a.log_conn_id, a.log_user_id,
  a.entity_id, a.log_date, CONCAT_WS(CHAR(1), 'Tag', b.name)
FROM @LOGDB@.log_civicrm_entity_tag a
LEFT JOIN @LOGDB@.nyss_temp_staging_tag b
ON a.tag_id=b.id AND a.log_date BETWEEN b.log_date AND b.log_end_date
WHERE a.log_action != 'Initialization' AND a.entity_table = 'civicrm_contact';

