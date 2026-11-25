<?php

/**
 * Override core's default mail action permissions to allow:
 * 1) mailing approvers to cancel mailings
 * 2) mailing creators to archive mailings
 * @note NYSS #18870, NYSS #19344
 */
#[CRM_NYSS_Attribute_IssueRef(18870, 19344)]
#[CRM_NYSS_Attribute_AlterMenuOverride('civicrm/mailing/action', CRM_Mailing_Form_Action::class)]
class CRM_NYSS_Mail_Form_Action extends CRM_Mailing_Form_Action {

  /**
   * Permission needed for each action, in CRM_Core_Permission::check()
   * format (a nested array is an OR list).
   */
  private const ACTION_PERMISSIONS = [
    CRM_Core_Action::DISABLE => 'cancel mailings',
    CRM_Core_Action::CLOSE => 'access CiviMail',
    CRM_Core_Action::REOPEN => 'access CiviMail',
    // Same as the classic list's Archive link
    CRM_Core_Action::RENEW => [['access CiviMail', 'create mailings']],
    CRM_Core_Action::DELETE => 'delete in CiviMail',
  ];

  /**
   * checks if the current user may perform a mailing list action.
   *
   * @param int $action
   *   One CRM_Core_Action constant.
   */
  public static function isActionPermitted(int $action): bool {
    $permission = self::ACTION_PERMISSIONS[$action] ?? NULL;
    return $permission !== NULL && CRM_Core_Permission::check($permission);
  }

  /**
   * Same as CRM_Mailing_Form_Action::preProcess(), except that the
   * checkActionPermission() call is replaced by isActionPermitted().
   * Keep in sync with core when upgrading.
   */
  public function preProcess() {
    $this->_mailingId = CRM_Utils_Request::retrieve('mid', 'Positive', $this);
    $this->_sms = CRM_Utils_Request::retrieve('sms', 'Positive', $this);
    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this);

    // @todo Duplicates code with CRM_Mailing_Page_Browse
    if ($this->_sms) {
      // if this is an SMS page, check that the user has permission to browse SMS
      if (!CRM_Core_Permission::check('send SMS')) {
        CRM_Core_Error::statusBounce(ts('You do not have permission to send SMS'));
      }
    }
    else {
      // If this is not an SMS page, check that the user has an appropriate
      // permission (specific permissions have been copied from
      // CRM/Mailing/xml/Menu/Mailing.xml)
      if (!CRM_Core_Permission::check([['access CiviMail', 'approve mailings', 'create mailings', 'schedule mailings']])) {
        CRM_Core_Error::statusBounce(ts('You do not have permission to view this page.'));
      }
    }

    //NYSS #18870, #19344 per-action permissions; replaces core's checkActionPermission('CiviMail', $this->_action)
    if (!self::isActionPermitted((int) $this->_action)) {
      CRM_Core_Error::statusBounce(ts('You do not have permission to access this page.'));
    }

    // check that the user has permission to access mailing id
    CRM_Mailing_BAO_Mailing::checkPermission($this->_mailingId);

    // Skip the parent's preProcess(), whose permission check this replaces
    CRM_Core_Form::preProcess();
  }

  /**
   * Use core's template; the default would look for one named after this
   * class.
   */
  public function getTemplateFileName() {
    return 'CRM/Mailing/Form/Action.tpl';
  }

}
