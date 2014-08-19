
DROP TABLE IF EXISTS nyss_temp_staging_contact;
CREATE TABLE nyss_temp_staging_contact (
  id INT(10) UNSIGNED NOT NULL,
  is_deleted_changed TINYINT(1) NOT NULL DEFAULT 0,
  log_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  log_end_date TIMESTAMP NULL DEFAULT NULL,
  INDEX idx__staging_date (log_date,log_end_date),
  INDEX idx__staging_id (id)
)
SELECT
  a.id,
  IFNULL(a.is_deleted XOR (
    SELECT b.is_deleted FROM log_civicrm_contact b
    WHERE b.log_date < a.log_date and a.id=b.id
    ORDER BY b.log_date DESC LIMIT 1
    ),0) as is_deleted_changed,
  a.log_date,
  IFNULL((
    SELECT DATE_SUB(b.log_date,INTERVAL 1 SECOND) as log_end_date
    FROM log_civicrm_contact b
    WHERE b.log_date > a.log_date and a.id=b.id
    ORDER BY b.log_date LIMIT 1), NOW()) as log_end_date
FROM log_civicrm_contact a;