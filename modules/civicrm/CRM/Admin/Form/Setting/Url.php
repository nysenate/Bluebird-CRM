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
 * This class generates form components for Site Url
 * 
 */
class CRM_Admin_Form_Setting_Url extends CRM_Admin_Form_Setting
{
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        CRM_Utils_System::setTitle(ts('Settings - Resource URLs'));

        $this->addElement('text','userFrameworkResourceURL' ,ts('CiviCRM Resource URL'));  
        $this->addElement('text','imageUploadURL', ts('Image Upload URL'));  
        $this->addElement('text','customCSSURL', ts('Custom CiviCRM CSS URL'));  
        $this->addYesNo( 'enableSSL', ts( 'Force Secure URLs (SSL)' ));

        $this->addFormRule( array( 'CRM_Admin_Form_Setting_Url', 'formRule' ) );

        parent::buildQuickForm( );
    }

    static function formRule( $fields) {
        if ( isset( $fields['enableSSL'] ) &&
             $fields['enableSSL'] ) {
            $config = CRM_Core_Config::singleton( );
            $url = str_replace( 'http://', 'https://',
                                CRM_Utils_System::url( 'civicrm/dashboard', 'reset=1', true,
                                                       null, false, false ) );
            if ( ! CRM_Utils_System::checkURL( $url, true ) ) {
                $errors = array( 'enableSSL' =>
                                 ts( 'You need to set up a secure server before you can use the Force Secure URLs option' ) );
                return $errors;
            }
        }
        return true;
    }

    public function postProcess( ) {
        parent::postProcess( );

        parent::rebuildMenu( );
    }


}


