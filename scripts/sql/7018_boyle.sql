-- trash all orgs that were created as current employers but should have been standalone org records
UPDATE civicrm_contact
SET is_deleted = 1
WHERE id IN (
  SELECT employer_id
  FROM (
    SELECT employer_id
    FROM civicrm_contact
    WHERE display_name IS NULL
      AND organization_name IS NOT NULL
      AND contact_type = 'Individual'
      AND external_identifier IS NULL
    ) indivOrg
  )
  AND contact_type = 'Organization';

-- convert Indiv records to Org records
UPDATE civicrm_contact
SET contact_type = 'Organization'
WHERE display_name IS NULL
  AND organization_name IS NOT NULL
  AND contact_type = 'Individual'
  AND external_identifier IS NULL;

-- reset sort/display name/greetings
UPDATE civicrm_contact
SET sort_name = organization_name,
  display_name = organization_name,
  email_greeting_id = 11,
  email_greeting_display = CONCAT('Dear ', organization_name),
  postal_greeting_id = 11,
  postal_greeting_display = CONCAT('Dear ', organization_name),
  addressee_id = 3,
  addressee_display = organization_name
WHERE employer_id IS NOT NULL
  AND contact_type = 'Organization'
  AND display_name IS NULL
  AND is_deleted != 1;
  
-- remove employer_id from org records
UPDATE civicrm_contact
SET employer_id = NULL
WHERE employer_id IS NOT NULL
  AND contact_type = 'Organization';