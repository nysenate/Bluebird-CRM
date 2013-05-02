<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Contact_Page_View_Log extends CRM_Core_Page {

  /**
   * This function is called when action is browse
   *
   * return null
   * @access public
   */
  function browse() {
    $loggingReport = CRM_Core_BAO_Log::useLoggingReport();
    $this->assign('useLogging', $loggingReport);

    if ($loggingReport) {
      //NYSS 5184/5185 pass page number
      $this->_contactLog = true;
      $context = '&context=contact';
      $crmPID = '';
      if ( CRM_Utils_Request::retrieve('crmPID', 'Integer') ) {
        $crmPID  = '&crmPID='.CRM_Utils_Request::retrieve('crmPID', 'Integer');
      }

      $this->assign( 'instanceUrl', CRM_Utils_System::url( "civicrm/report/instance/{$loggingReport}", "reset=1&force=1&snippet=4&section=2&altered_contact_id_op=eq&altered_contact_id_value={$this->_contactId}&cid={$this->_contactId}{$crmPID}{$context}", FALSE, NULL, FALSE ) );
      return;
    }

    $log = new CRM_Core_DAO_Log();

    $log->entity_table = 'civicrm_contact';
    $log->entity_id = $this->_contactId;
    $log->orderBy('modified_date desc');
    $log->find();

    $logEntries = array();
    while ($log->fetch()) {
      list($displayName, $contactImage) = CRM_Contact_BAO_Contact::getDisplayAndImage($log->modified_id);
      $logEntries[] = array(
        'id' => $log->modified_id,
        'name' => $displayName,
        'image' => $contactImage,
        'date' => $log->modified_date,
        'description' => $log->data,//NYSS 2551
      );
    }

    //NYSS 2551 need to retrieve activity logs for the current record
    //NYSS 4592 remove bulk email activities from displaying
    require_once 'api/v2/ActivityContact.php';
    $params = array('contact_id' => $this->_contactId);
    $activities = civicrm_activity_contact_get($params);
    //CRM_Core_Error::debug($activities);

    $activityIDs = array();
    $activitySubject = array();
    $bulkEmailID = CRM_Core_OptionGroup::getValue( 'activity_type', 'Bulk Email', 'name' );

		foreach ( $activities['result'] as $activityID => $activityDetail ) {
			if ( $activityDetail['activity_type_id'] != $bulkEmailID ) {
			    $activityIDs[] = $activityID;
			    $activitySubject[$activityID] = $activityDetail['subject'];
			}
		}
		$activityIDlist = implode(',', $activityIDs);
		//CRM_Core_Error::debug($activityIDlist);

		$allContacts = 0;
		$alogEntries = array( );
		if ( !empty($activityIDlist) ) {
			$sqlAlogs = "
			  SELECT entity_id, data, modified_id, modified_date
				FROM civicrm_log
				WHERE entity_table = 'civicrm_activity' AND entity_id IN ($activityIDlist);
			";
			$dao = CRM_Core_DAO::executeQuery( $sqlAlogs );
		
			while ( $dao->fetch( ) ) {
        list( $displayName, $contactImage ) = CRM_Contact_BAO_Contact::getDisplayAndImage( $dao->modified_id );
				$alogEntries[] = array(
          'id'    => $dao->modified_id,
        	'name'  => $displayName,
        	'image' => $contactImage,
        	'date'  => $dao->modified_date,
					'description' => $dao->data
        );
      }
		}
		$logEntries = array_merge_recursive( $logEntries, $alogEntries );
		require_once 'CRM/Utils/Sort.php';
		usort( $logEntries, array('CRM_Utils_Sort', 'cmpDate') );
		
		$this->assign( 'logCount', count( $logEntries ) );
    $this->assign_by_ref( 'log', $logEntries );
		
		$currentContact = CRM_Contact_BAO_Contact::getDisplayAndImage( $this->_contactId ); //4458
		$this->assign( 'displayName', $currentContact[0] ); //NYSS 2551
  }

  function preProcess() {
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);
    $this->assign('contactId', $this->_contactId);

    $displayName = CRM_Contact_BAO_Contact::displayName($this->_contactId);
    $this->assign('displayName', $displayName);

    // check logged in url permission
    CRM_Contact_Page_View::checkUserPermission($this);

    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'browse');
    $this->assign('action', $this->_action);
  }

  /**
   * This function is the main function that is called when the page loads, it decides the which action has to be taken for the page.
   *
   * return null
   * @access public
   */
  function run() {
    $this->preProcess();

    $this->browse();

    return parent::run();
  }
}

