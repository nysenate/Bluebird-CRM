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
class CRM_Dedupe_RemoveDupeAddress extends CRM_Core_Form
{

  /**
   * Function to pre processing
   *
   * @return None
   * @access public
   */
  function preProcess()
  {
    $sql = "SELECT count(id)
            FROM ( 
			  SELECT id
              FROM civicrm_address
 			  GROUP BY contact_id, location_type_id, street_address, supplemental_address_1, 
          			   supplemental_address_2, city, state_province_id, postal_code_suffix, postal_code
 			  HAVING count(id) > 1 
			) as dupes";
	$dupeCount = CRM_Core_Error::singleValueQuery($sql);
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
   * @return None
   */
  public function postProcess() 
  {

    if ( CRM_Utils_Array::value( '_qf_DedupeFind_submit', $_POST ) ) {
      //used for cancel button
      CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contact/deduperules','reset=1') );
      return;
    }

    $sql = "DELETE FROM civicrm_address
			WHERE id = (
			  SELECT id
			  FROM (
			    SELECT * 
				FROM civicrm_address
				ORDER BY id DESC ) as addr1
			  GROUP BY contact_id, location_type_id, street_address, supplemental_address_1, 
                       supplemental_address_2, city, state_province_id, postal_code_suffix, postal_code
              HAVING count(id) > 1 );";
        
    CRM_Utils_System::redirect($url);
  }    
}
