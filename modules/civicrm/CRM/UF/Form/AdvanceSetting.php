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

require_once 'CRM/UF/Form/Group.php';

class CRM_UF_Form_AdvanceSetting extends CRM_UF_Form_Group {
    
      /** 
     * Function to build the form for Advance Settings. 
     * 
     * @access public 
     * @return None 
     */ 
    function buildAdvanceSetting( &$form )
    { 
        // should mapping be enabled for this group
        $form->addElement('checkbox', 'is_map', ts('Enable mapping for this profile?') );
        
        // should we allow updates on a exisitng contact
        $options = array(); 
        $options[] = HTML_QuickForm::createElement('radio', null, null, ts('Issue warning and do not save'), 0 );
        $options[] = HTML_QuickForm::createElement('radio', null, null, ts('Update the matching contact'), 1 );
        $options[] = HTML_QuickForm::createElement('radio', null, null, ts('Allow duplicate contact to be created'), 2 );
        
        $this->addGroup($options, 'is_update_dupe', ts('What to do upon duplicate match'));
        // we do not have any url checks to allow relative urls
        $form->addElement('text', 'post_URL', ts('Redirect URL'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_UFGroup', 'post_URL') );
        $form->addElement('text', 'cancel_URL', ts('Cancel Redirect URL'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_UFGroup', 'cancel_URL') );
        
        // add select for groups
        $group               = array('' => ts('- select -')) + $form->_group;
        $form->_groupElement =& $form->addElement('select', 'group', ts('Limit listings to a specific Group?'), $group);

        //add notify field
        $form->addElement('text','notify',ts('Notify when profile form is submitted?'));
        
        //group where new contacts are directed.
        $form->addElement('select', 'add_contact_to_group', ts('Add new contacts to a Group?'), $group);
        
        // add CAPTCHA To this group ?
        $form->addElement('checkbox', 'add_captcha', ts('Include reCAPTCHA?') );
        
        // should we display an edit link
        $form->addElement('checkbox', 'is_edit_link', ts('Include profile edit links in search results?'));

        // should we display a link to the website profile
        $config = CRM_Core_Config::singleton( );
        $form->addElement('checkbox', 'is_uf_link', ts('Include %1 user account information links in search results?', array( 1 => $config->userFramework )));
        
        // want to create cms user
        $session = CRM_Core_Session::singleton( );
        $cmsId = false;
        if ( $form->_cId = $session->get( 'userID' ) ){
            $form->_cmsId = true;
        }

        $options = array(); 
        $options[] = HTML_QuickForm::createElement('radio', null, null, ts('No account create option'), 0 );
        $options[] = HTML_QuickForm::createElement('radio', null, null, ts('Give option, but not required'), 1 );
        $options[] = HTML_QuickForm::createElement('radio', null, null, ts('Account creation required'), 2 );
        
        $this->addGroup($options, 'is_cms_user', ts('%1 user account registration option?', array( 1=>$config->userFramework )));

        // options for including Proximity Search in the profile search form
        $proxOptions = array(); 
        $proxOptions[] = HTML_QuickForm::createElement('radio', null, null, ts('None'), 0 );
        $proxOptions[] = HTML_QuickForm::createElement('radio', null, null, ts('Optional'), 1 );
        $proxOptions[] = HTML_QuickForm::createElement('radio', null, null, ts('Required'), 2 );
        
        $this->addGroup($proxOptions, 'is_proximity_search', ts('Proximity search'));
    }
}
