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

class CRM_Contribute_Form_ContributionPage_Settings extends CRM_Contribute_Form_ContributionPage 
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
        if ( $this->_id ) {
            $title = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage',
                                                  $this->_id,
                                                  'title' );
            CRM_Utils_System::setTitle( ts( 'Title and Settings (%1)',
                                            array( 1 => $title ) ) );
        } else {
            CRM_Utils_System::setTitle( ts( 'Title and Settings' ) );
        }
        return parent::setDefaultValues();
    }
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        require_once 'CRM/Utils/Money.php';

        $this->_first = true;
        $attributes = CRM_Core_DAO::getAttribute('CRM_Contribute_DAO_ContributionPage');
        
        // name
        $this->add('text', 'title', ts('Title'), $attributes['title'], true);

        $this->add('select', 'contribution_type_id',
                   ts( 'Contribution Type' ),
                   CRM_Contribute_PseudoConstant::contributionType( ),
                   true );
        
        $this->addWysiwyg( 'intro_text', ts('Introductory Message'), $attributes['intro_text'] );

        $this->addWysiwyg( 'footer_text', ts('Footer Message'), $attributes['footer_text'] );

        // is on behalf of an organization ?
        $this->addElement('checkbox', 'is_organization', ts('Allow individuals to contribute and / or signup for membership on behalf of an organization?'), null, array('onclick' =>"showHideByValue('is_organization',true,'for_org_text','table-row','radio',false);showHideByValue('is_organization',true,'for_org_option','table-row','radio',false);") );
        $options = array(); 
        $options[] = HTML_QuickForm::createElement('radio', null, null, ts('Optional'), 1 );
        $options[] = HTML_QuickForm::createElement('radio', null, null, ts('Required'), 2 );
        $this->addGroup($options, 'is_for_organization', ts('') ); 
        $this->add('textarea', 'for_organization', ts('On behalf of Label'), $attributes['for_organization'] );

        // collect goal amount
        $this->add('text', 'goal_amount', ts('Goal Amount'), array( 'size' => 8, 'maxlength' => 12 ) ); 
        $this->addRule('goal_amount', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');
        
        // is this page active ?
        $this->addElement('checkbox', 'is_active', ts('Is this Online Contribution Page Active?') );

        // should the honor be enabled
        $this->addElement('checkbox', 'honor_block_is_active', ts( 'Honoree Section Enabled' ),null,array('onclick' =>"showHonor()") );
        
        $this->add('text', 'honor_block_title', ts('Honoree Section Title'), $attributes['honor_block_title'] );

        $this->add('textarea', 'honor_block_text', ts('Honoree Introductory Message'), $attributes['honor_block_text'] );

        // add optional start and end dates
        $this->addDateTime( 'start_date', ts('Start Date') );
        $this->addDateTime( 'end_date', ts('End Date') );

        $this->addFormRule( array( 'CRM_Contribute_Form_ContributionPage_Settings', 'formRule' ) );
        
        parent::buildQuickForm( );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $values posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $values ) 
    {
        $errors = array( );
        
        //CRM-4286
        if ( strstr( $values['title'], '/' ) ) {
            $errors['title'] = ts( "Please do not use '/' in Title" );
        }
        
        return $errors;
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
        if ( $this->_id ) {
            $params['id'] = $this->_id;
        } else { 
            $session = CRM_Core_Session::singleton( );
            $params['created_id']   = $session->get( 'userID' );
            $params['created_date'] = date('YmdHis');
        }            
       
        $params['is_active']             = CRM_Utils_Array::value('is_active'            , $params, false);
        $params['is_credit_card_only']   = CRM_Utils_Array::value('is_credit_card_only'  , $params, false);
        $params['honor_block_is_active'] = CRM_Utils_Array::value('honor_block_is_active', $params, false);
        $params['is_for_organization']   = CRM_Utils_Array::value('is_organization', $params ) 
                                           ? CRM_Utils_Array::value('is_for_organization', $params, false) 
                                           : 0;

        $params['start_date']            = CRM_Utils_Date::processDate( $params['start_date'], $params['start_date_time'], true );
        $params['end_date'  ]            = CRM_Utils_Date::processDate( $params['end_date'], $params['end_date_time'], true );
        
        $params['goal_amount'] = CRM_Utils_Rule::cleanMoney( $params['goal_amount'] );

        if( !$params['honor_block_is_active'] ) {
            $params['honor_block_title'] = null;
            $params['honor_block_text'] = null;
        }

        require_once 'CRM/Contribute/BAO/ContributionPage.php';
        $dao =& CRM_Contribute_BAO_ContributionPage::create( $params );

        $this->set( 'id', $dao->id );
    }

    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) {
        return ts( 'Title and Settings' );
    }
}

