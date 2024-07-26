-- Functions and triggers for the shadow tables

SET NAMES UTF8MB4 COLLATE utf8mb4_unicode_ci;

-- Change the delimiter to make stored triggers/functions easier to write!
DELIMITER |

-- -----------------------------
-- Stored Utility Functions
-- -----------------------------

DROP FUNCTION IF EXISTS BB_ADDR_REPLACE |
CREATE FUNCTION BB_ADDR_REPLACE (address varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci)
    RETURNS varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DETERMINISTIC

    BEGIN
        -- Start with the first alpha word occurance and loop
        -- through the rest of them doing replacements.
        DECLARE occurence INT DEFAULT 1;
        DECLARE address_part VARCHAR(255);
        DECLARE abbreviation VARCHAR(255);
        DECLARE find_abbreviation CURSOR FOR
            SELECT normalized
            FROM address_abbreviations
            WHERE raw_value = address_part;

        DECLARE CONTINUE HANDLER FOR NOT FOUND SET abbreviation = NULL;

        SET address = LCASE(address);

        -- Loop through strings that might possibly be the street suffix
        replace_loop: LOOP

            -- This regex allows us to skip lookups for non-alphanumeric components
            SET address_part = REGEXP_SUBSTR(address, '[[:alpha:]]+', 1, occurence);

            -- Preg_capture will return null when it runs out of matches
            IF address_part IS NULL THEN
                LEAVE replace_loop;
            END IF;

            -- Find the abbreviation and do a replace.
            OPEN find_abbreviation;
            FETCH find_abbreviation INTO abbreviation;
            IF abbreviation IS NOT NULL THEN
                SET address = REPLACE(address, address_part, abbreviation);
            END IF;
            CLOSE find_abbreviation;

            -- Increment the occurance for the next round
            -- As long add the replacements don't have numbers this works.
            SET occurence = occurence + 1;

        END LOOP;

        RETURN address;
    END
|

DROP FUNCTION IF EXISTS BB_NORMALIZE |
CREATE FUNCTION BB_NORMALIZE (value VARCHAR(255))
    RETURNS VARCHAR(255) DETERMINISTIC

    BEGIN
        -- Compress '' values into null
        IF value IS NULL OR value = '' THEN
            RETURN NULL;
        END IF;

        -- Strip all  punctuation and spaces from strings
        RETURN LCASE(REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( value,
                    ',', ''),
                   '\'', ''),
                    '.', ''),
                    '-', ''),
                    ';', ''),
                    ':', ''),
                    '#', ''),
                    ' ', ''));
    END
|

DROP FUNCTION IF EXISTS BB_NORMALIZE_ADDR |
CREATE FUNCTION BB_NORMALIZE_ADDR (value VARCHAR(255))
    RETURNS VARCHAR(255) DETERMINISTIC

    BEGIN
        DECLARE address VARCHAR(255);

        -- Compress '' values into null
        IF value IS NULL OR value = '' THEN
            RETURN NULL;
        END IF;

        -- Lower the case and strip out all the ordinals from the street numbers
        SET address = REGEXP_REPLACE(TRIM(LCASE(value)), '(?<=[0-9])(?:st|nd|rd|th)','');

        -- Standardize spacing from the street numbers from 7B, 7-B, 7 B => 7 B
        SET address = REGEXP_REPLACE(address, '^(\d+)-?(\w+)\s', '$1 $2 ');

        -- Strip out all the different kinds of punctuation
        -- SPECIAL: Don't replace 's with spaces
        SET address = REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( address,
                    ',', ' '),
                    '\'', ''),
                    '.', ' '),
                    '-', ' '),
                    ';', ' '),
                    ':', ' '),
                    '#', ' ');

        SET address = BB_ADDR_REPLACE(address);

        -- Some other adhoc changes we need to make
        SET address = REPLACE( address, 'apt', '');
        SET address = REPLACE( address, 'floor', 'fl');
        SET address = REPLACE( address, 'east', 'e');
        SET address = REPLACE( address, 'north', 'n');
        SET address = REPLACE( address, 'west', 'w');
        SET address = REPLACE( address, 'south', 's');

        -- Normalize the spaces on the way out the door
        RETURN REGEXP_REPLACE(TRIM(address), ' +', ' ');
    END
|


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


DELIMITER ;
