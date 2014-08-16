/*
** The following temporary fields are used during the conversion process,
** but are later dropped from the DETAIL table:
**   tmp_conn_id
**   tmp_user_id
**   tmp_contact_id
**   tmp_entity_info
**   tmp_change_ts
**   tmp_date_extract
**
** entity_info is a parsed field that contains the the entity_type at a
** minimum, plus optional bracketed info (for all non-Contact entity
** types), plus optional Group status info (for Group entity type).
*/

DROP TRIGGER IF EXISTS nyss_changelog_detail_before_insert;

DELIMITER //
CREATE DEFINER = CURRENT_USER
TRIGGER nyss_changelog_detail_before_insert
BEFORE INSERT
ON nyss_changelog_detail FOR EACH ROW
BEGIN
  CASE NEW.db_op
    WHEN 'Insert' THEN SET @user_action = 'Added';
    WHEN 'Delete' THEN SET @user_action = 'Removed';
    ELSE SET @user_action = 'Updated';
  END CASE;

  SET @entity_type = SUBSTRING_INDEX(NEW.tmp_entity_info, CHAR(1), 1);

  IF @entity_type != 'Contact' THEN
    SET @entity_info = SUBSTRING_INDEX(SUBSTRING_INDEX(NEW.tmp_entity_info, CHAR(1), 2), CHAR(1), -1);
  ELSE
    SET @entity_info = NULL;
  END IF;

  IF @entity_type = 'Group' THEN
    SET @group_action = SUBSTRING_INDEX(NEW.tmp_entity_info, CHAR(1), -1);
    IF @group_action = 'Removed' THEN
      SET @user_action = 'Unjoined';
    ELSEIF @group_action = 'Added' THEN
      SET @user_action = 'Rejoined';
    END IF;
  END IF;

  SET @summary_id = NULL;
  /* Calculate the date_extract, used for grouping purposes */
  SET @date_extract = DATE_FORMAT(NEW.tmp_change_ts, '%Y%m%d%H');

  IF @entity_type IN ('Contact', 'Activity') THEN
    SELECT id INTO @summary_id
    FROM nyss_changelog_summary
    WHERE (user_id = NEW.tmp_user_id OR (user_id IS NULL AND NEW.tmp_user_id IS NULL))
      AND conn_id = NEW.tmp_conn_id
      AND entity_type = @entity_type
      AND contact_id = NEW.tmp_contact_id
      AND tmp_date_extract = @date_extract
    ORDER BY id DESC LIMIT 1;
  END IF;

  IF @summary_id IS NULL THEN
    BEGIN
      INSERT INTO nyss_changelog_summary
        (conn_id, user_id, contact_id, entity_type,
         change_ts, user_action, entity_info, tmp_date_extract)
      VALUES
        (NEW.tmp_conn_id, NEW.tmp_user_id, NEW.tmp_contact_id, @entity_type,
         NEW.tmp_change_ts, @user_action, @entity_info, @date_extract);
      SET NEW.summary_id = LAST_INSERT_ID();
    END;
  ELSE
    SET NEW.summary_id = @summary_id;
  END IF;
END;
//
DELIMITER ;
