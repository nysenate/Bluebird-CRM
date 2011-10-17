<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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
 * Field handler to provide amount raised by given Personal Campaign Page
 *
 * @ingroup civicrm_field_handlers
 */
class civicrm_handler_field_pcp_raised_amount extends views_handler_field_numeric {

  function construct( ) {
    if ( ! civicrm_initialize( ) ) {
      return;
    }
    require_once 'CRM/Contribute/BAO/PCP.php';
  }

  function render($values) {
    $pcp_id =  $values->{$this->field_alias}; // retrieve the value of the "real field"
    $value = CRM_Contribute_BAO_PCP::thermoMeter( $pcp_id ); // get the total amount raised
    if (!$value) {
      $value = '0'; // supply a numeric string if function returns NULL or undesirable result
    }

    $v = clone $values; // Prevent affecting the original
    $v->{$this->field_alias} = $value;
    return parent::render($v);
  } 
}
?>
