<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * This class generates form components for DedupeRules
 *
 */
class CRM_Dedupe_Form_RemoveDupeAddress extends CRM_Core_Form
{

  /**
   * Function to pre processing
   *
   * @return None
   * @access public
   */
  function preProcess()
  {
    $sql = "
      SELECT count(*)
      FROM (
        SELECT contact_id
        FROM civicrm_address
        GROUP BY contact_id, street_address, supplemental_address_1,
          supplemental_address_2, city, state_province_id, postal_code_suffix, postal_code
        HAVING count(*) > 1
        ) as dupes";
    $dupeCount = CRM_Core_DAO::singleValueQuery($sql);
    if (!$dupeCount) {
      $url = CRM_Utils_System::url( 'civicrm','reset=1');
      $msg = 'There are no duplicate addresses found in this database.';
      CRM_Core_Session::setStatus($msg, 'No Duplicate Addresses to Remove', 'info');
      CRM_Utils_System::redirect($url);
    }
    else {
      $this->_dupeCount = $dupeCount;
      $this->assign('dupeCount',$dupeCount);
    }
  }

  /**
   * Function to build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    $this->addButtons( array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => true
      ),
      array(
        'type' => 'submit',
        'name' => ts('Cancel')
      ),
    ));
  }

  /**
   * Function to process the form
   *
   * @access public
   * @param output_status Determines if the status will be output.  This should
   *                      be set to false when running from the CLI.
   * @return None
   */
  public function postProcess($output_status = true) {
    self::removeDuplicateAddresses($output_status);
  }

  static function removeDuplicateAddresses($output_status) {
    $sTime = microtime(true);
    $tmpTbl = 'nyss_temp_dedupe_address';

    //remove duplicate addresses; prefer removing address with larger id (newer)
    CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS $tmpTbl;");
    $sql = "
      CREATE TABLE $tmpTbl (id INT(10), PRIMARY KEY (id))
      SELECT ANY_VALUE(id) id
      FROM (
        SELECT *
        FROM civicrm_address
        ORDER BY id DESC 
        ) as addr1
      GROUP BY contact_id, street_address, supplemental_address_1,
        supplemental_address_2, city, state_province_id, postal_code_suffix, postal_code
      HAVING count(id) > 1;
    ";
    CRM_Core_DAO::executeQuery($sql);

    $sql = "
      DELETE FROM civicrm_address
      WHERE id IN (SELECT id FROM $tmpTbl);
    ";
    CRM_Core_DAO::executeQuery($sql);

    //also cleanup any orphaned district block sets
    $sql = "
      DELETE FROM civicrm_value_district_information_7
      WHERE entity_id IN (SELECT id FROM $tmpTbl);
    ";
    CRM_Core_DAO::executeQuery($sql);

    //ensure all contacts with an address have a primary address
    $sql = "
      SELECT a.contact_id
      FROM civicrm_address a
      LEFT JOIN (
        SELECT contact_id
        FROM civicrm_address
        WHERE is_primary = 1
        GROUP BY contact_id
      ) ap
        ON a.contact_id = ap.contact_id
      WHERE ap.contact_id IS NULL
      GROUP BY a.contact_id
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);

    while ($dao->fetch()) {
      if (empty($dao->contact_id)) {
        continue;
      }

      $addressId = CRM_Core_DAO::singleValueQuery("
        SELECT id
        FROM civicrm_address
        WHERE contact_id = {$dao->contact_id}
        ORDER BY CASE WHEN location_type_id = 6 THEN 0 ELSE location_type_id END
        LIMIT 1
      ");

      if ($addressId) {
        CRM_Core_DAO::executeQuery("
          UPDATE civicrm_address
          SET is_primary = 1
          WHERE id = {$addressId}
        ");
      }
    }

    CRM_Core_DAO::executeQuery("DROP TABLE $tmpTbl;");

    $eTime = microtime(true);
    $diffTime = $eTime - $sTime;

    if ($output_status === true) {
      $url = CRM_Utils_System::url( 'civicrm','reset=1');
      $msg = "Contacts with duplicate addresses have been cleaned up. The process took $diffTime seconds.";
      CRM_Core_Session::setStatus($msg, 'Duplicate Addresses Removed', 'success');
      CRM_Utils_System::redirect($url);
    }
  }
}
