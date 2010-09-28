<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Contribute/Form/ContributionPage.php';
require_once 'CRM/Contribute/PseudoConstant.php';

/**
 * form to process actions on Premiums
 */
class CRM_Contribute_Form_ContributionPage_Premium extends CRM_Contribute_Form_ContributionPage 
{
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return void
     */
    function setDefaultValues()
    {
        $defaults = array();
        if ( isset($this->_id ) ) {
            $title = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'title' );
            CRM_Utils_System::setTitle(ts('Premiums (%1)', array(1 => $title)));
            $dao = new CRM_Contribute_DAO_Premium();
            $dao->entity_table = 'civicrm_contribution_page';
            $dao->entity_id = $this->_id; 
            $dao->find(true);
            CRM_Core_DAO::storeValues( $dao,$defaults );
        } 
        return $defaults;
    }
    

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    { 
        $this->addElement('checkbox', 'premiums_active', ts('Premiums Section Enabled?'), null, array( 'onclick' => "premiumBlock(this);" ) );
        
        $this->addElement('text', 'premiums_intro_title', ts('Title'), CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_Premium', 'premiums_intro_title'));
        
        $this->add('textarea', 'premiums_intro_text', ts('Introductory Message'), 'rows=5, cols=50');

        $this->add('text','premiums_contact_email',ts('Contact Email') . ' ',CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_Premium', 'premiums_contact_email')); 
        
        $this->addRule('premiums_contact_email',ts('Please enter a valid email address for Contact Email') . ' ','email');
        
        $this->add('text','premiums_contact_phone',ts('Contact Phone'),CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_Premium', 'premiums_contact_phone'));
        
        $this->addRule('premiums_contact_phone',ts('Please enter a valid phone number.'),'phone');

        $this->addElement('checkbox', 'premiums_display_min_contribution', ts('Display Minimum Contribution Amount?') );
     
        $showForm = true;
  
        if ( $this->_single ) {
            if ( $this->_id ) {
                $daoPremium = new CRM_Contribute_DAO_Premium( );
                $daoPremium->entity_id    = $this->_id;
                $daoPremium->entity_table = 'civicrm_contribution_page';
                if ( $daoPremium->find( true ) ) {
                    $showForm = false;
                }
            }
        }
        $this->assign( 'showForm', $showForm );
        
        parent::buildQuickForm( );
    }

    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        // get the submitted form values.
        $params = $this->controller->exportValues( $this->_name );

        // we do this in case the user has hit the forward/back button

        $dao = new CRM_Contribute_DAO_Premium();
        $dao->entity_table = 'civicrm_contribution_page';
        $dao->entity_id = $this->_id; 
        $dao->find(true);
        $premiumID = $dao->id;
        if ( $premiumID ) {
            $params['id'] = $premiumID;
        }

        $params['premiums_active']                   =  CRM_Utils_Array::value( 'premiums_active', $params, false );
        $params['premiums_display_min_contribution'] =  CRM_Utils_Array::value( 'premiums_display_min_contribution', $params, false );
        $params['entity_table']                      = 'civicrm_contribution_page';
        $params['entity_id']                         =  $this->_id;
       
        $dao = new CRM_Contribute_DAO_Premium();
        $dao->copyValues($params);
        $dao->save();

    }

    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) {
        return ts( 'Premiums' );
    }
}

