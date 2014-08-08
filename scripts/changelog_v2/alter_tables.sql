
ALTER TABLE nyss_changelog_summary
DROP tmp_date_extract,
DROP INDEX tmp_idx__changelog_summary,
ADD INDEX idx__changelog_summary__user_id (user_id),
ADD INDEX idx__changelog_summary__contact_id (contact_id),
ADD INDEX idx__changelog_summary__change_ts (change_ts);
   

ALTER TABLE nyss_changelog_detail
DROP tmp_conn_id,
DROP tmp_user_id,
DROP tmp_contact_id,
DROP tmp_entity_info,
DROP tmp_change_ts,
DROP INDEX tmp_idx__changelog_detail,
ADD INDEX idx__changelog_detail__summary_id (summary_id);
 
