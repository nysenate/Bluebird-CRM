/*
** The following MySQL session variables are expected to already be set
** by the civicrm_* data table triggers upon activation of this trigger:
**   @civicrm_user_id
**   @nyss_contact_id
**   @nyss_entity_info
**
** Plus, CONNECTION_ID() is a function that returns the MySQL connection ID.
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

  SET @entity_type = SUBSTRING_INDEX(@nyss_entity_info, CHAR(1), 1);
  SET @entity_info = SUBSTRING_INDEX(SUBSTRING_INDEX(@nyss_entity_info, CHAR(1), 2), CHAR(1), -1);

  /* Special logic to detect group unjoin/rejoin and contact trash/restore. */
  IF @entity_type = 'Group' AND @user_action = 'Updated' THEN
    SET @group_action = SUBSTRING_INDEX(@nyss_entity_info, CHAR(1), -1);
    IF @group_action = 'Removed' THEN
      SET @user_action = 'Unjoined';
    ELSEIF @group_action = 'Added' THEN
      SET @user_action = 'Rejoined';
    END IF;
  ELSEIF NEW.table_name = 'contact' AND @entity_info = 1 THEN
    SET @contact_action = SUBSTRING_INDEX(@nyss_entity_info, CHAR(1), -1);
    IF @contact_action IN ('Trashed', 'Restored') THEN
      SET @user_action = @contact_action;
    END IF;
  END IF;

  SET @summary_id = NULL;

  IF @entity_type IN ('Contact', 'Activity') THEN
    SELECT id INTO @summary_id
    FROM nyss_changelog_summary
    WHERE (user_id = @civicrm_user_id OR (user_id IS NULL AND @civicrm_user_id IS NULL))
      AND conn_id = CONNECTION_ID()
      AND entity_type = @entity_type
      AND contact_id = @nyss_contact_id
    ORDER BY id DESC LIMIT 1;
    IF @entity_type='Activity' AND @user_action='Added' AND @summary_id>0 THEN
      UPDATE nyss_changelog_summary SET user_action='Updated' WHERE id=@summary_id AND user_action='Removed';
    END IF;
  END IF;

  IF @summary_id IS NULL THEN
    IF @entity_type = 'Contact' THEN
      SET @entity_info = NULL;
      IF NEW.table_name != 'contact' THEN
        SET @user_action = 'Updated';
      END IF;
    END IF;
    INSERT INTO nyss_changelog_summary
      (conn_id, user_id, contact_id, entity_type, user_action, entity_info)
    VALUES
      (CONNECTION_ID(), @civicrm_user_id, @nyss_contact_id,
       @entity_type, @user_action, @entity_info);
    SET NEW.summary_id = LAST_INSERT_ID();
  ELSE
    SET NEW.summary_id = @summary_id;
  END IF;
END;
//
DELIMITER ;
