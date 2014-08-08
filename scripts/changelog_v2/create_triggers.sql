
DROP TRIGGER IF EXISTS nyss_changelog_detail_before_insert;

DELIMITER //
CREATE DEFINER = CURRENT_USER
TRIGGER nyss_changelog_detail_before_insert
BEFORE INSERT
ON nyss_changelog_detail FOR EACH ROW
BEGIN
  SET @this_action = NEW.db_op;
  SET @this_entity_type = '';
  /* Calculate the log_type_label, used for grouping purposes */
  /* Also calculate the log_action field if looking at a group_contact record */

  /* Capitalize first letter of the type label for consistency */
  SET @this_log_type_label = CONCAT(UCASE(LEFT(@this_log_type_label,1)), SUBSTR(@this_log_type_label,2));
  /* check if this grouping already has a change sequence */
  IF @this_log_type_label = 'Activity' THEN 
    BEGIN 
      SET @this_change_seq = NULL; 
      SELECT log_change_seq INTO @this_change_seq
      FROM nyss_changelog_summary 
      WHERE altered_contact_id=@this_altered_contact_id  
        AND log_conn_id = CONNECTION_ID() 
        AND log_type_label = 'Activity' 
      ORDER BY log_change_seq DESC LIMIT 1; 
    END; 
  ELSEIF @this_log_type_label <> 'Contact' THEN  
    SET @this_change_seq = NULL;  
  END IF; 
    
  IF @this_change_seq IS NULL THEN
    /* If it doesn't, insert a new summary row and set the change sequence */
    INSERT INTO nyss_changelog_summary (log_action_label, log_type_label, altered_contact_id, log_conn_id, log_entity_info)
    VALUES (@this_log_action, @this_log_type_label, @this_altered_contact_id, CONNECTION_ID(), NEW.log_entity_info);
  ELSE
    /* if it does, this changeset includes multiple changes...the label should be 'Update' */
    UPDATE nyss_changelog_summary
    SET log_action_label='Update'
    WHERE log_change_seq=@this_change_seq;
  END IF;

  /* set the change sequence for this detail row */
  SET NEW.log_change_seq = @this_change_seq;
END;
//
DELIMITER ;

