INSERT INTO civicrm_dedupe_rule_group
    (id, contact_type, threshold, level, is_default, name)
VALUES
    (1,   'Individual', 15, 'Strict', 1, 'Individual Strict (first + last + (street + zip | email))'),
    (2,   'Individual', 15,  'Fuzzy', 1, 'Individual Fuzzy (nick + last + (street | mail | email))'),
    (3, 'Organization',  4, 'Strict', 1, 'Organization 1 (name + street + city + email)'),
    (4, 'Organization',  4,  'Fuzzy', 1, 'Organization 2 (name + street + city + zip)'),
    (5, 'Organization',  3,  'Fuzzy', 0, 'Organization 3 (name + street + city)'),
    (6,    'Household',  4, 'Strict', 1, 'Household 1 (name + street + city + email)'),
    (7,    'Household',  4, 'Fuzzy', 1, 'Household 2 (name + street + city + zip)'),
    (8,    'Household',  3, 'Fuzzy', 0, 'Household 3 (name + street + city)');

INSERT INTO civicrm_dedupe_rule
    (dedupe_rule_group_id, rule_table, rule_field, rule_length, rule_weight)
VALUES
    (1, 'civicrm_contact',     'first_name', NULL, 5),
    (1, 'civicrm_contact',    'middle_name', NULL, 2),
    (1, 'civicrm_contact',      'last_name', NULL, 5),
    (1, 'civicrm_contact',      'suffix_id', NULL, 2),
    (1, 'civicrm_address', 'street_address', NULL, 5),

    (2, 'civicrm_contact',     'first_name', NULL, 5),
    (2, 'civicrm_contact',    'middle_name', NULL, 2),
    (2, 'civicrm_contact',      'last_name', NULL, 5),
    (2, 'civicrm_contact',      'suffix_id', NULL, 2),
    (2, 'civicrm_address', 'street_address', NULL, 5),

    (3, 'civicrm_contact', 'organization_name', NULL, 1),
    (3, 'civicrm_address',    'street_address', NULL, 1),
    (3, 'civicrm_address',              'city', NULL, 1),
    (3,   'civicrm_email',             'email', NULL, 1),

    (4, 'civicrm_contact', 'organization_name', NULL, 1),
    (4, 'civicrm_address',    'street_address', NULL, 1),
    (4, 'civicrm_address',       'postal_code', NULL, 1),
    (4, 'civicrm_address',              'city', NULL, 1),

    (5, 'civicrm_contact', 'organization_name', NULL, 1),
    (5, 'civicrm_address',    'street_address', NULL, 1),
    (5, 'civicrm_address',              'city', NULL, 1),

    (6, 'civicrm_contact', 'household_name', NULL, 1),
    (6, 'civicrm_address', 'street_address', NULL, 1),
    (6, 'civicrm_address',           'city', NULL, 1),
    (6,   'civicrm_email',          'email', NULL, 1),

    (7, 'civicrm_contact', 'household_name', NULL, 1),
    (7, 'civicrm_address', 'street_address', NULL, 1),
    (7, 'civicrm_address',    'postal_code', NULL, 1),
    (7, 'civicrm_address',           'city', NULL, 1),

    (8, 'civicrm_contact', 'household_name', NULL, 1),
    (8, 'civicrm_address', 'street_address', NULL, 1),
    (8, 'civicrm_address',           'city', NULL, 1);
