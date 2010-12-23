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
 * This class generates form components for Miscellaneous
 * 
 */
class CRM_Admin_Form_Setting_Miscellaneous extends  CRM_Admin_Form_Setting
{
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        CRM_Utils_System::setTitle(ts('Settings - Miscellaneous'));

        $this->addYesNo('contactUndelete', ts('Contact Trash & Undelete'));

        // FIXME: for now, disable logging for multilingual sites
        $domain = new CRM_Core_DAO_Domain;
        $domain->find(true);
        $attribs = $domain->locales ? array('disabled' => 'disabled') : null;
        $this->addYesNo('logging', ts('Logging'), null, null, $attribs);

        $this->addYesNo( 'versionCheck'           , ts( 'Version Check & Statistics Reporting' ));
        $this->addElement('text', 'maxAttachments' , ts('Maximum Attachments'),
                          array( 'size' => 2, 'maxlength' => 8 ) );
        $this->addElement('text', 'maxFileSize' , ts('Maximum File Size'),
                          array( 'size' => 2, 'maxlength' => 8 ) );
        $this->addElement('text','recaptchaPublicKey' , ts('Public Key'),
                          array( 'size' => 64, 'maxlength' => 64 ) );
        $this->addElement('text','recaptchaPrivateKey', ts('Private Key'),
                          array( 'size' => 64, 'maxlength' => 64 ) );

        $this->addElement('text', 'dashboardCacheTimeout', ts('Dashboard cache timeout'),
                          array( 'size' => 3, 'maxlength' => 5 ) );

        $this->addRule('maxAttachments', ts('Value should be a positive number') , 'positiveInteger');
        $this->addRule('maxFileSize', ts('Value should be a positive number') , 'positiveInteger');
       
        parent::buildQuickForm();    
    }

    public function postProcess()
    {
        parent::postProcess();

        // handle logging
        // FIXME: do it only if the setting changed
        require_once 'CRM/Logging/Schema.php';
        $values = $this->exportValues();
        $logging = new CRM_Logging_Schema;
        $values['logging'] ? $logging->enableLogging() : $logging->disableLogging();
    }
}
