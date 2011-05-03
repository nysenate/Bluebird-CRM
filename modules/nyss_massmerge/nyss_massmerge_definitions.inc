<?

global $nyss_massmerge_defs;

$nyss_massmerge_defs['delete']['civicrm_group_contact_cache'][] = 'id';
$nyss_massmerge_defs['delete']['civicrm_dashboard_contact'][] = 'id';
$nyss_massmerge_defs['delete']['civicrm_preferences'][] = 'id';
$nyss_massmerge_defs['delete']['civicrm_cache'][] = 'id';
$nyss_massmerge_defs['delete']['civicrm_acl_cache'][] = 'id';
$nyss_massmerge_defs['delete']['civicrm_acl_contact_cache'][] = 'id';
$nyss_massmerge_defs['delete']['civicrm_uf_match'][] = 'contact_id';

//base contact table
//$nyss_massmerge_defs['map']['civicrm_contact'][] = 'id';

//process these by multiple matching since they have special rules
//$nyss_massmerge_defs['map']['civicrm_address'][] = 'contact_id';
//$nyss_massmerge_defs['map']['civicrm_phone'][] = 'contact_id';
//$nyss_massmerge_defs['map']['civicrm_email'][] = 'contact_id';
//$nyss_massmerge_defs['map']['civicrm_openid'][] = 'contact_id';
//$nyss_massmerge_defs['map']['civicrm_website'][] = 'contact_id';
//$nyss_massmerge_defs['map']['civicrm_im'][] = 'contact_id';

//notes
$nyss_massmerge_defs['map']['civicrm_note'][] = 'contact_id';

//tags
$nyss_massmerge_defs['map']['civicrm_entity_tag'][] = 'entity_id';

//cases
$nyss_massmerge_defs['map']['civicrm_case_contact'][] = 'contact_id';

//events
$nyss_massmerge_defs['map']['civicrm_participant'][] = 'contact_id';

//contributions
$nyss_massmerge_defs['map']['civicrm_contribution'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_contribution'][] = 'honor_contact_id';
$nyss_massmerge_defs['map']['civicrm_contribution_recur'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_contribution_soft'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_financial_account'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_grant'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_group_contact'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_pcp'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_pledge'][] = 'contact_id';

//relationships

//civimail
$nyss_massmerge_defs['map']['civicrm_mailing_event_queue'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_mailing_event_subscribe'][] = 'contact_id';
$nyss_massmerge_defs['map']['civicrm_subscription_history'][] = 'contact_id';

//custom tables
$nyss_massmerge_defs['map']['civicrm_membership'][] = 'contact_id';

