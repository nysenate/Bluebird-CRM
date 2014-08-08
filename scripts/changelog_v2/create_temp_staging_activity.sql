
DROP TABLE IF EXISTS nyss_temp_staging_activity;
CREATE TABLE nyss_temp_staging_activity (
  id INT(10) UNSIGNED NOT NULL,
  label VARCHAR(255) NOT NULL,
  log_action ENUM('Initialization','Insert','Update','Delete'),
  log_user_id INT(11) NULL DEFAULT NULL,
  log_conn_id INT(11) NULL DEFAULT NULL,
  log_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  log_end_date TIMESTAMP NULL DEFAULT NULL,
  INDEX idx__staging_date (log_date,log_end_date),
  INDEX idx__staging_id (id)
)
SELECT a.id, IFNULL(d.label,'NO LABEL') as label,
       a.log_action, a.log_user_id, a.log_conn_id, a.log_date,
       IFNULL((
         SELECT DATE_SUB(b.log_date,INTERVAL 1 SECOND) as log_end_date
         FROM log_civicrm_activity b
         WHERE b.log_date > a.log_date and a.id=b.id
         ORDER BY b.log_date LIMIT 1), NOW()) as log_end_date
FROM log_civicrm_activity a
INNER JOIN (
  @CIVIDB@.civicrm_option_group c
  INNER JOIN @CIVIDB@.civicrm_option_value d
  ON c.name='activity_type' AND c.id=d.option_group_id
)
ON a.activity_type_id = d.value
WHERE a.log_action != 'Initialization';
 
