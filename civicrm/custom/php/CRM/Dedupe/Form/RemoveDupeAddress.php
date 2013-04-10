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

require_once 'CRM/Core/Form.php';

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
        SELECT id
        FROM civicrm_address
        GROUP BY contact_id, location_type_id, street_address, supplemental_address_1,
          supplemental_address_2, city, state_province_id, postal_code_suffix, postal_code
        HAVING count(*) > 1
        ) as dupes";
    $dupeCount = CRM_Core_DAO::singleValueQuery($sql);
    if ( !$dupeCount ) {
      $url = CRM_Utils_System::url( 'civicrm','reset=1' );
      CRM_Core_Error::statusBounce( 'There are no duplicate addresses in this database.', $url );
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
  public function buildQuickForm()
  {
    $this->addButtons( array(
                         array ( 'type'      => 'next',
                                 'name'      => ts('Continue'),
                                 'isDefault' => true   ),
                         array ( 'type'      => 'submit', 
                                 'name'      => ts('Cancel') ),
                         )
                       );
  }

  /**
   * Function to process the form
   *
   * @access public
   * @param output_status Determines if the status will be output.  This should
   *                      be set to false when running from the CLI.
   * @return None
   */
  public function postProcess($output_status = true)
  {
    $sTime = microtime(true);

    //remove duplicate addresses; prefer removing address with larger id (newer)
    CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS tmpAddressDedupe;");
    $sql = "CREATE TABLE tmpAddressDedupe ( id INT(10), PRIMARY KEY (id) )
            SELECT id
              FROM (
                SELECT * 
                FROM civicrm_address
                ORDER BY id DESC ) as addr1
              GROUP BY contact_id, location_type_id, street_address, supplemental_address_1, 
                       supplemental_address_2, city, state_province_id, postal_code_suffix, postal_code
              HAVING count(id) > 1;";
    CRM_Core_DAO::executeQuery($sql);

    $sql = "DELETE FROM civicrm_address
            WHERE id IN ( SELECT id FROM tmpAddressDedupe );";
    CRM_Core_DAO::executeQuery($sql);

    //also cleanup any orphaned district block sets
    $sql = "DELETE FROM civicrm_value_district_information_7
            WHERE entity_id IN ( SELECT id FROM tmpAddressDedupe );";
    CRM_Core_DAO::executeQuery($sql);

    $eTime = microtime(true);
    $diffTime = $eTime - $sTime;

    if ($output_status === true) {
      $url = CRM_Utils_System::url( 'civicrm','reset=1');
      CRM_Core_Error::statusBounce( "Contacts with duplicate addresses have been cleaned up. The process took $diffTime seconds.", $url );
    }
  }    
}
