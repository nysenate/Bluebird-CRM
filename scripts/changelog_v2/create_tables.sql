
/* Drop deprecated tables */
DROP TABLE IF EXISTS nyss_changelog_sequence;
DROP TABLE IF EXISTS nyss_debug;

/* Drop the DETAIL table first, since it has a foreign key ref to SUMMARY */
DROP TABLE IF EXISTS nyss_changelog_detail;
DROP TABLE IF EXISTS nyss_changelog_summary;


/* create the summary table
   NOTES:
     1. This table is created as a staging table initially to speed up the
        prepopulation routines
     2. This table will be altered at the end to drop any irrelevant columns
*/

CREATE TABLE nyss_changelog_summary (
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  conn_id INT(11) NULL DEFAULT NULL,
  user_id INT(10) UNSIGNED DEFAULT NULL,
  contact_id INT(10) UNSIGNED NOT NULL,
  entity_type ENUM('Contact', 'Group', 'Tag', 'Activity', 'Relationship', 'Case', 'Note', 'Comment') NOT NULL,
  change_ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_action ENUM('Added', 'Updated', 'Removed', 'Unjoined', 'Rejoined') NOT NULL DEFAULT 'Updated',
  entity_info VARCHAR(255) NULL DEFAULT NULL,
  tmp_date_extract INT(10) NOT NULL,
  INDEX tmp_idx__changelog_summary (conn_id, contact_id, entity_type, tmp_date_extract)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


/* create the detail table
   NOTE:  This table stages all 17 log tables into a single location.  As each
          row is inserted, the summary table is built using this data.
*/

CREATE TABLE nyss_changelog_detail (
  summary_id BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'references the corresponding summary table record',
  db_op ENUM('INSERT', 'UPDATE', 'DELETE'),
  table_name VARCHAR(64) DEFAULT NULL COMMENT 'the original log table name',
  entity_id INT(10) UNSIGNED NOT NULL COMMENT 'original log table id being changed',
  tmp_conn_id INT(11) NULL DEFAULT NULL COMMENT 'This field is obsolete, and will be removed after staging',
  tmp_user_id INT(10) UNSIGNED DEFAULT NULL,
  tmp_contact_id INT(10) UNSIGNED NOT NULL,
  tmp_change_ts TIMESTAMP NOT NULL DEFAULT 0,
  tmp_entity_info VARCHAR(255) NULL DEFAULT NULL,
  CONSTRAINT FK_nyss_changelog_summary_id FOREIGN KEY (summary_id) REFERENCES nyss_changelog_summary (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

