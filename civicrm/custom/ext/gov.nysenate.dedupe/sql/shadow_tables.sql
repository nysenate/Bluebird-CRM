-- Deletes aren't cascaded (possibly a good thing) so we can't use foreign keys
-- here without running into referential integrity issues or rewriting a bunch
-- of civicrm core code...

-- --------------------------------------------
-- CREATE shadow tables
-- --------------------------------------------
DROP TABLE IF EXISTS shadow_contact;
CREATE TABLE shadow_contact (
    contact_id int(10) unsigned PRIMARY KEY,
    first_name varchar(255),
    middle_name varchar(255),
    last_name varchar(255),
    household_name varchar(255),
    organization_name varchar(255),
    suffix_id varchar(255),
    birth_date date,
    gender_id int(10) unsigned,
    contact_type varchar(255),
    INDEX (first_name, last_name, middle_name),
    INDEX (last_name),
    INDEX (gender_id),
    INDEX (contact_type),
    INDEX (household_name),
    INDEX (organization_name),
    INDEX (birth_date)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS shadow_address;
CREATE TABLE shadow_address (
    address_id int(10) unsigned PRIMARY KEY,
    contact_id int(10) unsigned,
    street_address varchar(255),
    country_id int(10) unsigned,
    state_province_id int(10) unsigned,
    supplemental_address_1 varchar(255),
    supplemental_address_2 varchar(255),
    postal_code varchar(255),
    city varchar(255),
    INDEX (street_address),
    INDEX (supplemental_address_1),
    INDEX (supplemental_address_2),
    INDEX (country_id),
    INDEX (state_province_id),
    INDEX (postal_code),
    INDEX (city),
    INDEX (contact_id)
) ENGINE=InnoDB;

