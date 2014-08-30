
/*
** Handle the CONTACT tables, plus other associated contact tables:
**   EMAIL
**   PHONE
**   CONSTITUENT_INFORMATION
**   ORGANIZATION_CONSTITUENT_INFORMATION
**   ATTACHMENTS
**   CONTACT_DETAILS
*/

INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  a.log_action, 'contact', a.id, a.log_conn_id, a.log_user_id,
  a.id, a.log_date, CONCAT_WS(CHAR(1), 'Contact', b.is_deleted_changed, IF(a.is_deleted,'Trashed','Restored'))
FROM @LOGDB@.log_civicrm_contact a
INNER JOIN @LOGDB@.nyss_temp_staging_contact b
ON a.id=b.id AND a.log_date BETWEEN b.log_date AND b.log_end_date
WHERE log_action != 'Initialization';


INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  log_action, 'email', id, log_conn_id, log_user_id,
  contact_id, log_date, 'Contact'
FROM @LOGDB@.log_civicrm_email
WHERE log_action != 'Initialization';


INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  log_action, 'phone', id, log_conn_id, log_user_id,
  contact_id, log_date, 'Contact'
FROM @LOGDB@.log_civicrm_phone
WHERE log_action != 'Initialization';


INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  log_action, 'value_constituent_information_1', id, log_conn_id, log_user_id,
  entity_id, log_date, 'Contact'
FROM @LOGDB@.log_civicrm_value_constituent_information_1
WHERE log_action != 'Initialization';


INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  log_action, 'value_organization_constituent_informa_3', id, log_conn_id, log_user_id,
  entity_id, log_date, 'Contact'
FROM @LOGDB@.log_civicrm_value_organization_constituent_informa_3
WHERE log_action != 'Initialization';


INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  log_action, 'value_attachments_5', id, log_conn_id, log_user_id,
  entity_id, log_date, 'Contact'
FROM @LOGDB@.log_civicrm_value_attachments_5
WHERE log_action != 'Initialization';


INSERT IGNORE INTO nyss_changelog_detail
  (db_op, table_name, entity_id, tmp_conn_id, tmp_user_id,
   tmp_contact_id, tmp_change_ts, tmp_entity_info)
SELECT
  log_action, 'value_contact_details_8', id, log_conn_id, log_user_id,
  entity_id, log_date, 'Contact'
FROM @LOGDB@.log_civicrm_value_contact_details_8
WHERE log_action != 'Initialization';

