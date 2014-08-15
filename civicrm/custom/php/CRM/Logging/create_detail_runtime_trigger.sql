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

  SET @entity_type = SUBSTRING_INDEX(@nyss_entity_info, CHAR(1), 1);
  IF @entity_type != 'Contact' THEN
    SET @entity_info = SUBSTRING_INDEX(SUBSTRING_INDEX(@nyss_entity_info, CHAR(1), 2),CHAR(1),-1);
  ELSE
    SET @entity_info = NULL;
  END IF;
  IF @entity_type = 'Group' THEN
    SET @group_action = SUBSTRING_INDEX(@nyss_entity_info, CHAR(1), -1);
  ELSE
    SET @group_action = NULL;
  END IF;

  CASE NEW.db_op 
    WHEN 'Insert' THEN SET @tmp_user_action = 'Added';
    WHEN 'Delete' THEN SET @tmp_user_action = 'Removed';
    ELSE
      BEGIN
        IF @entity_type = 'Group' AND @group_action = 'Removed' THEN 
          SET @tmp_user_action = 'Unjoined';
        ELSEIF @entity_type = 'Group' AND @group_action = 'Added' THEN 
          SET @tmp_user_action = 'Rejoined';
        ELSE
          SET @tmp_user_action = 'Updated';
        END IF;
      END;
  END CASE;

  SET @summary_id = NULL;
  IF @entity_type IN ('Contact', 'Activity') THEN 
    BEGIN 
      SELECT id INTO @summary_id
      FROM nyss_changelog_summary 
      WHERE user_id = @civicrm_user_id 
        AND conn_id = CONNECTION_ID() 
        AND entity_type = @entity_type 
        AND contact_id = @nyss_altered_contact_id
      ORDER BY id DESC LIMIT 1; 
      IF @entity_type='Contact' AND @summary_id IS NULL THEN
        SET @nyss_session_contact_added = NULL;
      END IF;
    END; 
  END IF;  

  IF NEW.table_name='contact' AND NEW.db_op='INSERT' THEN
    SET @nyss_session_contact_added = 1;
  END IF;

  IF NEW.table_name!='contact' AND @entity_type='Contact'  AND @summary_id IS NOT NULL THEN
    SET @tmp_user_action='Updated';
  END IF;

  IF @summary_id IS NULL THEN 
    BEGIN 
      INSERT INTO nyss_changelog_summary
        (conn_id, user_id, contact_id, entity_type, user_action, entity_info)
        VALUES
        (CONNECTION_ID(), @civicrm_user_id, @nyss_altered_contact_id,
        @entity_type, @tmp_user_action, @entity_info);
      SET NEW.summary_id = LAST_INSERT_ID();
    END; 
  ELSE
    BEGIN 
      SET NEW.summary_id = @summary_id;
  		IF @entity_type != 'Contact' OR IFNULL(@nyss_session_contact_added,0) != 1 THEN 
  			BEGIN
  		      UPDATE nyss_changelog_summary SET user_action = 'Updated' WHERE id = @summary_id; 
  		   END;
  		END IF;
    END; 
  END IF;
