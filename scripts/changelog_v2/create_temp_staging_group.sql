
DROP TABLE IF EXISTS nyss_temp_staging_group;
CREATE TABLE nyss_temp_staging_group (
  id INT(10) UNSIGNED NOT NULL,
  title VARCHAR(64) NULL DEFAULT NULL,
  log_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  log_end_date TIMESTAMP NULL DEFAULT NULL,
  INDEX idx__staging_date (log_date,log_end_date),
  INDEX idx__staging_id (id)
)
SELECT a.id, a.title, a.log_date,
  IFNULL((
    SELECT DATE_SUB(b.log_date,INTERVAL 1 SECOND) as log_end_date 
    FROM log_civicrm_group b
    WHERE b.log_date > a.log_date and a.id=b.id
    ORDER BY b.log_date LIMIT 1), NOW()) as log_end_date
FROM log_civicrm_group a
GROUP BY a.id, a.log_date, a.log_conn_id, a.log_user_id;

