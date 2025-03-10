INSERT INTO civicrm_dedupe_rule_group
  (id, contact_type, threshold, used, is_reserved, title, name)
VALUES
  (1, 'Individual', 15, 'Unsupervised', 1, 'Individual Strict (first + last + (street + zip | email))', 'individual_strict'),
  (2, 'Individual', 15, 'Supervised', 1, 'Individual Fuzzy (nick + last + (street | mail | email))', 'individual_fuzzy'),
  (3, 'Organization', 4, 'Unsupervised', 1, 'Organization 1 (name + street + city + email)', 'organization_1'),
  (4, 'Organization', 4, 'Supervised', 1, 'Organization 2 (name + street + city + zip)', 'organization_2'),
  (5, 'Organization', 3, 'General', 1, 'Organization 3 (name + street + city)', 'organization_3'),
  (6, 'Household', 4, 'Unsupervised', 1, 'Household 1 (name + street + city + email)', 'household_1'),
  (7, 'Household', 4, 'Supervised', 1, 'Household 2 (name + street + city + zip)', 'household_2'),
  (8, 'Household', 3, 'General', 1, 'Household 3 (name + street + city)', 'household_3');

INSERT INTO civicrm_dedupe_rule
  (dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight)
VALUES
  (1, 'civicrm_contact', 'first_name', NULL, 5),
  (1, 'civicrm_contact', 'middle_name', NULL, 2),
  (1, 'civicrm_contact', 'last_name', NULL, 5),
  (1, 'civicrm_contact', 'suffix_id', NULL, 2),
  (1, 'civicrm_address', 'street_address', NULL, 5),

  (2, 'civicrm_contact', 'first_name', NULL, 5),
  (2, 'civicrm_contact', 'middle_name', NULL, 2),
  (2, 'civicrm_contact', 'last_name', NULL, 5),
  (2, 'civicrm_contact', 'suffix_id', NULL, 2),
  (2, 'civicrm_address', 'street_address', NULL, 5),

  (3, 'civicrm_contact', 'organization_name', NULL, 1),
  (3, 'civicrm_address', 'street_address', NULL, 1),
  (3, 'civicrm_address', 'city', NULL, 1),
  (3,  'civicrm_email', 'email', NULL, 1),

  (4, 'civicrm_contact', 'organization_name', NULL, 1),
  (4, 'civicrm_address', 'street_address', NULL, 1),
  (4, 'civicrm_address', 'postal_code', NULL, 1),
  (4, 'civicrm_address', 'city', NULL, 1),

  (5, 'civicrm_contact', 'organization_name', NULL, 1),
  (5, 'civicrm_address', 'street_address', NULL, 1),
  (5, 'civicrm_address', 'city', NULL, 1),

  (6, 'civicrm_contact', 'household_name', NULL, 1),
  (6, 'civicrm_address', 'street_address', NULL, 1),
  (6, 'civicrm_address', 'city', NULL, 1),
  (6,  'civicrm_email', 'email', NULL, 1),

  (7, 'civicrm_contact', 'household_name', NULL, 1),
  (7, 'civicrm_address', 'street_address', NULL, 1),
  (7, 'civicrm_address', 'postal_code', NULL, 1),
  (7, 'civicrm_address', 'city', NULL, 1),

  (8, 'civicrm_contact', 'household_name', NULL, 1),
  (8, 'civicrm_address', 'street_address', NULL, 1),
  (8, 'civicrm_address', 'city', NULL, 1);
