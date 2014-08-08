
DROP TABLE IF EXISTS nyss_temp_staging_case;
CREATE TABLE nyss_temp_staging_case (
  id INT(10) UNSIGNED NOT NULL,
  label VARCHAR(255) NOT NULL,
  log_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  log_end_date TIMESTAMP NULL DEFAULT NULL,
  INDEX idx__staging_date (log_date,log_end_date),
  INDEX idx__staging_id (id)
)
SELECT a.id, d.label, a.log_date,
       IFNULL((
         SELECT DATE_SUB(b.log_date,INTERVAL 1 SECOND) as log_end_date
         FROM log_civicrm_case b
         WHERE b.log_date > a.log_date and a.id=b.id
         ORDER BY b.log_date LIMIT 1), NOW()) as log_end_date
FROM log_civicrm_case a
INNER JOIN (
  @CIVIDB@.civicrm_option_group c
  INNER JOIN @CIVIDB@.civicrm_option_value d
  ON c.name='case_type' AND c.id=d.option_group_id
)
ON a.case_type_id=d.value;


