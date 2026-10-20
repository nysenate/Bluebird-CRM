# gov.nysenate.dashboard

Customizes the CiviCRM dashboard for Bluebird CRM, providing custom layouts, intranet links, and Senate news feed notifications.

## Report dashlet role filter (NYSS 3439)

`dashboard_civicrm_selectWhereClause()` hides legacy report dashlets from users who aren't in one of the report's ACL Group/Roles. Core's own handling (dev/core#6290) joins the roles to the report permission with OR when more than one role is selected, and doesn't update dashlets saved before that change.

## Tests

End-to-end tests live in `tests/phpunit/CRM/NYSS/Dashboard/` and run against a live Bluebird instance. Run them **from the extension directory** so that `phpunit.xml.dist` is picked up (PHPUnit 9.x):

```bash
cd civicrm/custom/ext/gov.nysenate.dashboard
INSTANCE=<instance> HTTP_HOST=<instance> phpunit
```
