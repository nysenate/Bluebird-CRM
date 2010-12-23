<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Admin/Form/Setting.php';

/**
 * This class generates form components for Mapping and Geocoding
 * 
 */
class CRM_Admin_Form_Setting_Mapping extends CRM_Admin_Form_Setting
{
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        CRM_Utils_System::setTitle(ts('Settings - Mapping Provider'));

        $map = CRM_Core_SelectValues::mapProvider();
        $this->addElement('select','mapProvider', ts('Map Provider'),array('' => '- select -') + $map);  
        $this->add('text','mapAPIKey', ts('Provider Key'), null);  
    
        parent::buildQuickForm();
    }


    /**
     * global form rule
     *
     * @param array $fields  the input form values

     * @return true if no errors, else array of errors
     * @access public
     * @static
     */
    static function formRule( $fields) {
        $errors = array();

        if ( ! CRM_Utils_System::checkPHPVersion( 5, false ) ) {
            $errors['_qf_default'] = ts( 'Mapping features require PHP version 5 or greater' );
        }

        if ( !$fields['mapAPIKey'] && $fields['mapProvider'] != '' ) {
            $errors['mapAPIKey'] = "API key is a required field";
        } 

        return $errors;
    }

    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @param null
     * 
     * @return void
     * @access public
     */
    function addRules( )
    {
        $this->addFormRule( array( 'CRM_Admin_Form_Setting_Mapping', 'formRule' ) );
    }
    
 
}


