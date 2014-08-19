INSERT INTO @LOGDB@.`log_civicrm_group` (
  `id`, `name`, `title`, `description`, `source`, `saved_search_id`,
  `is_active`, `visibility`, `where_clause`, `select_tables`,
  `where_tables`, `group_type`, `cache_date`, `parents`, `children`,
  `is_hidden`, `is_reserved`, `log_date`, `log_conn_id`, `log_user_id`,
  `log_action`, `log_job_id`, `refresh_date`, `created_id`
)
SELECT
  `id`, `name`, `title`, `description`, `source`, `saved_search_id`,
  `is_active`, `visibility`, `where_clause`, `select_tables`,
  `where_tables`, `group_type`, `cache_date`, `parents`, `children`,
  `is_hidden`, `is_reserved`, '2012-05-31 17:55:40', CONNECTION_ID(), 1,
  'Initialization', NULL, `refresh_date`, `created_id`
FROM @CIVIDB@.civicrm_group a
  WHERE NOT EXISTS (SELECT b.id FROM @LOGDB@.log_civicrm_group b WHERE b.id=a.id);

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

