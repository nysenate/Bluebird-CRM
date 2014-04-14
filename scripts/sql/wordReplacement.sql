TRUNCATE TABLE civicrm_word_replacement;

INSERT INTO civicrm_word_replacement (find_word, replace_word, is_active, match_type, domain_id)
VALUES
  ('CiviCRM', 'Bluebird', 1, 'wildcardMatch', 1),
  ('Full-text', 'Find Anything', 1, 'wildcardMatch', 1),
  ('Addt''l Address 1', 'Mailing Address', 1, 'wildcardMatch', 1),
  ('Addt''l Address 2', 'Building', 1, 'wildcardMatch', 1),
  ('Supplemental address info, e.g. c/o, department name, building name, etc.', 'Department name, building name, complex, or extension of company name.', 1, 'wildcardMatch', 1),
  ('deatils', 'details', 1, 'wildcardMatch', 1),
  ('sucessfully', 'successfully', 1, 'wildcardMatch', 1),
  ('groups, contributions, memberships, etc.', 'groups, relationships, etc.', 1, 'wildcardMatch', 1),
  ('email OR an OpenID', 'email', 1, 'wildcardMatch', 1),
  ('client', 'constituent', 1, 'wildcardMatch', 1),
  ('Client', 'Constituent', 1, 'wildcardMatch', 1),
  ('Job title', 'Job Title', 1, 'wildcardMatch', 1),
  ('Nick Name', 'Nickname', 1, 'wildcardMatch', 1),
  ('CiviMail', 'BluebirdMail', 1, 'wildcardMatch', 1),
  ('CiviCase Dashboard', 'Case Dashboard', 1, 'wildcardMatch', 1),
  ('Position', 'Job Title', 1, 'exactMatch', 1),
  ('Id', 'ID', 1, 'exactMatch', 1),
  ('CiviReport', 'Reports', 1, 'exactMatch', 1),
  ('CiviCase', 'Cases', 1, 'exactMatch', 1),
  ('Do not trade', 'Undeliverable: Do not mail', 1, 'exactMatch', 1),
  ('Do not mail', 'Do not postal mail', 1, 'exactMatch', 1),
  ('Supplemental Address 1', 'Mailing Address', 1, 'wildcardMatch', 1),
  ('Supplemental Address 2', 'Building', 1, 'wildcardMatch', 1),
  ('Next/Previous buttons are not available for searches which are sorted by email, phone or address fields.', 'Next/Previous navigation buttons are not available when alternate sorting has been used.', 1, 'wildcardMatch', 1),
  ('Manage the rules used to identify potentially duplicate contact records.', ' ', 1, 'wildcardMatch', 1),
  ('Type in a partial or complete name of an existing tag.', 'Begin typing a tag name.', 1, 'exactMatch', 1),
  ('We recommend using BluebirdMail instead.', 'We recommend using mass email instead.', 1, 'wildcardMatch', 1),
  ('Send Email to Contacts', 'Send Email to Contacts (max 10)', 1, 'exactMatch', 1)
;
