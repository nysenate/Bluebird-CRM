START TRANSACTION;

TRUNCATE shadow_contact;
TRUNCATE shadow_address;
TRUNCATE fn_group_contact;
TRUNCATE fn_group_name;
TRUNCATE fn_group;

INSERT INTO shadow_contact (contact_id, first_name, middle_name, last_name, suffix_id, birth_date, gender_id, contact_type, household_name, organization_name)
	SELECT id, BB_NORMALIZE(first_name), BB_NORMALIZE(middle_name), BB_NORMALIZE(last_name), suffix_id, birth_date, gender_id, contact_type, BB_NORMALIZE(household_name), BB_NORMALIZE(organization_name) FROM civicrm_contact;


INSERT INTO shadow_address (address_id, contact_id, street_address, postal_code, city, country_id, state_province_id, supplemental_address_1, supplemental_address_2)
	SELECT id, contact_id, BB_NORMALIZE_ADDR(street_address), IFNULL(postal_code,''), IFNULL(BB_NORMALIZE_ADDR(city),''), country_id, state_province_id, BB_NORMALIZE_ADDR(supplemental_address_1), BB_NORMALIZE_ADDR(supplemental_address_2) FROM civicrm_address;

COMMIT;
