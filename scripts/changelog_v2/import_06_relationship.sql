
INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  a.log_action, 'relationship', a.id, a.log_conn_id, a.log_user_id,
  a.contact_id_a, a.log_date, CONCAT_WS(CHAR(1), 'Relationship', b.label_a_b)
FROM @LOGDB@.log_civicrm_relationship a
LEFT JOIN @LOGDB@.nyss_temp_staging_relationship b
ON a.relationship_type_id=b.id AND a.log_date BETWEEN b.log_date AND b.log_end_date
WHERE a.log_action != 'Initialization';

INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  a.log_action, 'relationship', a.id, a.log_conn_id, a.log_user_id,
  a.contact_id_b, a.log_date, CONCAT_WS(CHAR(1), 'Relationship', b.label_b_a)
FROM @LOGDB@.log_civicrm_relationship a
LEFT JOIN @LOGDB@.nyss_temp_staging_relationship b
ON a.relationship_type_id=b.id AND a.log_date BETWEEN b.log_date AND b.log_end_date
WHERE a.log_action != 'Initialization';

