-- Change the delimiter to make stored triggers/functions easier to write!
DELIMITER |

-- --------------------------------
-- Triggers for first name groups
-- --------------------------------
DROP TRIGGER IF EXISTS shadow_contact_insert_fn_trigger |
CREATE TRIGGER shadow_contact_insert_fn_trigger AFTER INSERT ON shadow_contact
    FOR EACH ROW BEGIN
        DECLARE new_fn_group_id INT;
        DECLARE new_insert INT DEFAULT 1;
        DECLARE not_found VARCHAR(5) DEFAULT 'False';

        DECLARE find_fn CURSOR FOR
            SELECT fn_group_id
            FROM fn_group_name
            WHERE name = NEW.first_name;

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET not_found = 'True';

        IF IFNULL(NEW.first_name,'') != '' THEN
            -- If the new name isn't empty, create new mappings from matching groups
            OPEN find_fn;
            insert_loop: LOOP
                FETCH find_fn INTO new_fn_group_id;

                IF not_found = 'True' THEN
                    LEAVE insert_loop;
                END IF;

                INSERT INTO fn_group_contact (fn_group_id, contact_id) VALUES (new_fn_group_id, NEW.contact_id);
                SET new_insert = 0;
            END LOOP;
            CLOSE find_fn;

            -- If we no matching groups are found, create a new one marked as new=1
            -- so that we can easily find names that weren't part of the original
            -- nickname mappings
            IF new_insert = 1 THEN
                INSERT INTO fn_group (given, new) VALUES (NEW.first_name, 1);
                SET new_fn_group_id = LAST_INSERT_ID();
                INSERT INTO fn_group_name (fn_group_id, name) VALUES (new_fn_group_id, NEW.first_name);
                INSERT INTO fn_group_contact (fn_group_id, contact_id) VALUES (new_fn_group_id, NEW.contact_id);
            END IF;

        END IF;
    END
|

DROP TRIGGER IF EXISTS shadow_contact_update_fn_trigger |
CREATE TRIGGER shadow_contact_update_fn_trigger AFTER UPDATE ON shadow_contact
    FOR EACH ROW BEGIN
        DECLARE new_fn_group_id INT;
        DECLARE new_insert INT DEFAULT 1;
        DECLARE not_found VARCHAR(5) DEFAULT 'False';

        DECLARE find_fn CURSOR FOR
            SELECT fn_group_id
            FROM fn_group_name
            WHERE name = NEW.first_name;

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET not_found = 'True';

        IF IFNULL(NEW.first_name,'') != IFNULL(OLD.first_name,'') THEN
            -- If the first name has changed, delete the old mappings
            DELETE FROM fn_group_contact WHERE contact_id = OLD.contact_id;

            IF IFNULL(NEW.first_name,'') != '' THEN
                -- If the new name isn't empty, create new mappings from matching groups
                OPEN find_fn;
                insert_loop: LOOP
                    FETCH find_fn INTO new_fn_group_id;

                    IF not_found = 'True' THEN
                        LEAVE insert_loop;
                    END IF;

                    INSERT INTO fn_group_contact (fn_group_id, contact_id) VALUES (new_fn_group_id, NEW.contact_id);
                    SET new_insert = 0;
                END LOOP;
                CLOSE find_fn;

                -- If we no matching groups are found, create a new one marked as new=1
                -- so that we can easily find names that weren't part of the original
                -- nickname mappings
                IF new_insert = 1 THEN
                    INSERT INTO fn_group (given, new) VALUES (NEW.first_name, 1);
                    SET new_fn_group_id = LAST_INSERT_ID();
                    INSERT INTO fn_group_name (fn_group_id, name) VALUES (new_fn_group_id, NEW.first_name);
                    INSERT INTO fn_group_contact (fn_group_id, contact_id) VALUES (new_fn_group_id, NEW.contact_id);
                END IF;

            END IF;

        END IF;
    END
|


DROP TRIGGER IF EXISTS shadow_contact_delete_fn_trigger |
CREATE TRIGGER shadow_contact_delete_fn_trigger AFTER DELETE ON shadow_contact
    FOR EACH ROW BEGIN
        DELETE FROM fn_group_contact WHERE contact_id = OLD.contact_id;
    END
|