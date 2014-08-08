IF NEW.user_id IS NULL THEN
  SET NEW.user_id = @civicrm_user_id;
END IF;
