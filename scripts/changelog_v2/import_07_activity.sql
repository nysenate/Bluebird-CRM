
INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT 
  a.log_action, 'activity', a.id, a.log_conn_id, a.log_user_id,
  b.contact_id, a.log_date,
  CONCAT('Activity', CHAR(1),
         CASE b.record_type_id
           WHEN 1 THEN '<= ' /* Assignee */
           WHEN 2 THEN '=> ' /* Source */
           WHEN 3 THEN ''    /* Target */
           ELSE '?'
         END, a.label
  )
FROM @LOGDB@.nyss_temp_staging_activity a
INNER JOIN @LOGDB@.log_civicrm_activity_contact b 
ON a.id=b.activity_id
WHERE a.log_action != 'Initialization' AND b.log_date BETWEEN a.log_date AND a.log_end_date;

