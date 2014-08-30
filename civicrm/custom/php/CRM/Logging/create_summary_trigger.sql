DROP TRIGGER IF EXISTS nyss_changelog_summary_before_insert;

DELIMITER //
CREATE DEFINER = CURRENT_USER
TRIGGER nyss_changelog_summary_before_insert
BEFORE INSERT
ON nyss_changelog_summary FOR EACH ROW
BEGIN
  IF NEW.user_id IS NULL THEN
    SET NEW.user_id = @civicrm_user_id;
  END IF;
END;
//
DELIMITER ;
