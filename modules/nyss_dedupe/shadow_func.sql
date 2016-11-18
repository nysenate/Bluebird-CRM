-- Change the delimiter to make stored triggers/functions easier to write!
DELIMITER |

-- -----------------------------
-- Stored Utility Functions
-- -----------------------------

SET NAMES UTF8 COLLATE utf8_unicode_ci;
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

            -- This regex allows us to skip lookups for alphanumeric components
            SET address_part = preg_capture('/([A-Za-z]+)/', address, 1, occurence);

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
        SET address = preg_replace('/(?<=[0-9])(?:st|nd|rd|th)/','', TRIM(LCASE(value)));

        -- Standardize spacing from the street numbers from 7B, 7-B, 7 B => 7 B
        SET address = preg_replace('/^(\d+)-?(\w+)\s/', '$1 $2 ', address);

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
        RETURN preg_replace('/ +/', ' ', TRIM(address));
    END
|

DELIMITER ;
