-- CRM-5636
{if $addDeceasedStatus}
SELECT @maxWeight := MAX(ROUND(weight)) FROM civicrm_membership_status;
INSERT INTO  civicrm_membership_status ( name, is_current_member, is_admin, is_active, is_reserved, weight, is_default )
     VALUES  ( 'Deceased', 0, 1, 1, 1, (SELECT @maxWeight := @maxWeight + 1), 0 );
{/if}
