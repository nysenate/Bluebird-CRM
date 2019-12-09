<?php

/**
 * @file
 * Active Directory LDAP Implementation Details.
 */

require_once drupal_get_path('module', 'ldap_servers') . '/ldap_types/LdapTypeAbstract.class.php';
/**
 *
 */
class LdapTypeOpenDirectory extends LdapTypeAbstract {

  public $documentation = 'http://help.apple.com/advancedserveradmin/mac/10.7/#apdBF94D320-3293-41E0-B7DA-123F857C4032';
  public $name = 'Apple Open Directory LDAP';
  public $typeId = 'OpenDirectory';
  public $description = 'Apple Open Directory LDAP';

  public $port = 389;
  public $tls = FALSE;

  /**
   * User ldap entry properties.
   */
  public $user_dn_expression;
  public $user_attr = 'uid';
  /**
   * Lowercase.
   */
  public $account_name_attr;
  /**
   * Lowercase.
   */
  public $mail_attr = 'mail';
  public $unique_persistent_attr = 'apple-generateduid';
  public $unique_persistent_attr_binary = FALSE;
  public $userObjectClass = 'apple-user';

  public $groupObjectClass = 'apple-group';
  /**
   * Lowercase // members, uniquemember, memberUid.
   */
  public $groupMembershipsAttr = 'apple-group-memberguid';

  /**
   * Lowercase // dn, cn, etc contained in groupMembershipsAttr.
   */
  public $groupMembershipsAttrMatchingUserAttr = 'apple-generateduid';

  /**
   * Lowercase // members, uniquemember, memberUid.
   */
  public $groupMembersGroupsAttr = 'apple-group-nestedgroup';
  /**
   * Lowercase // dn, cn, etc contained in groupMembershipsAttr.
   */
  public $groupMembersGroupsAttrMatchingGroupAttr = 'apple-generateduid';

}
