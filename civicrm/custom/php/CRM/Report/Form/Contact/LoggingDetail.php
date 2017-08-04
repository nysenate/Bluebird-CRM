<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
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
 * @copyright CiviCRM LLC (c) 2004-2017
 * $Id$
 *
 */
class CRM_Report_Form_Contact_LoggingDetail extends CRM_Logging_ReportDetail {
  /**
   */
  public function __construct() {
    //NYSS
    /* Old table-search method relied on this list of tables
    $logging = new CRM_Logging_Schema();
    $this->tables[] = 'civicrm_contact';
    $this->tables = array_merge($this->tables, array_keys($logging->customDataLogTables()));
    $this->tables[] = 'civicrm_email';
    $this->tables[] = 'civicrm_phone';
    $this->tables[] = 'civicrm_im';
    $this->tables[] = 'civicrm_openid';
    $this->tables[] = 'civicrm_website';
    $this->tables[] = 'civicrm_address';
    $this->tables[] = 'civicrm_note';
    $this->tables[] = 'civicrm_relationship';
    $this->tables[] = 'civicrm_activity';
    $this->tables[] = 'civicrm_case';

    // allow tables to be extended by report hook query objects
    CRM_Report_BAO_Hook::singleton()->alterLogTables($this, $this->tables);
    */

    // NYSS report no longer searches all tables..  pull table manifest directly from nyss_changelog_detail
    $this->summary_id = CRM_Utils_Request::retrieve('summary_id', 'Integer', CRM_Core_DAO::$_nullObject);
    $this->populateTables();

    $this->detail = 'logging/contact/detail';
    $this->summary = 'logging/contact/summary';

    parent::__construct();
  }

  public function buildQuickForm() {
    $layout = CRM_Utils_Request::retrieve('layout', 'String', $this);
    $this->assign('layout', $layout);

    parent::buildQuickForm();

    if ($this->cid) {
      // link back to contact summary
      $this->assign('backURL', CRM_Utils_System::url('civicrm/contact/view', "reset=1&selectedChild=log&cid={$this->cid}", FALSE, NULL, FALSE));
      $this->assign('revertURL', self::$_template->get_template_vars('revertURL') . "&cid={$this->cid}");
    }
    else {
      // link back to summary report
      //NYSS preserve summary instance source
      $instanceID = CRM_Utils_Request::retrieve('instanceID', 'Integer');
      if ( $instanceID ) {
        $backURL = CRM_Utils_System::url('civicrm/report/instance/'.$instanceID, "reset=1", false, null, false);
      }
      else {
        $backURL = CRM_Report_Utils_Report::getNextUrl('logging/contact/summary', 'reset=1', false, false);//NYSS don't get instance id
      }
      $this->assign('backURL', $backURL);
    }
  }

  //NYSS ? 
  function buildRows($sql, &$rows) {
    if (empty($rows)) {
      $rows = array();
    }

    // if no summary_id was passed or if $this->tables is blank, no rows can be found
    if (!($this->summary_id && $this->tables)) {
      return;
    }

    foreach ($this->tables as $table) {
      $rows = array_merge($rows, $this->diffsInTable($table));
    }
  }

  // populate $this->tables based on a query to nyss_changelog_detail
  // i.e., setting up to search only the tables relevant to this change
  function populateTables($summary_id = NULL) {
    if (!$summary_id) { $summary_id = $this->summary_id; }
    $this->tables = array();
    if ($summary_id) {
      // we need to add the standard 'civicrm_' prefix.  The 'log_' prefix comes later
      $sql = "SELECT DISTINCT CONCAT('civicrm_', table_name) as table_name " .
             "FROM nyss_changelog_detail WHERE summary_id = %1";
      $rows = CRM_Core_DAO::executeQuery($sql, array( 1=>array($summary_id, 'Integer') ));
      while ($rows->fetch()) {
        $this->tables[] = $rows->table_name;
      }
    }
  }
}
