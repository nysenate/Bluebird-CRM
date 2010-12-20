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

require_once 'CRM/Contribute/Form/ContributionPage.php';

/**
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Contribute_Form_ContributionPage_Custom extends CRM_Contribute_Form_ContributionPage 
{
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        require_once "CRM/Core/BAO/UFGroup.php";
        require_once "CRM/Contact/BAO/ContactType.php";
        $types    = array_merge( array( 'Contact', 'Individual','Contribution','Membership'),
                                 CRM_Contact_BAO_ContactType::subTypes( 'Individual' ) );
        
        $profiles = CRM_Core_BAO_UFGroup::getProfiles( $types ); 
        
        if ( empty( $profiles ) ) {
            $this->assign( 'noProfile', true );
        }

        $this->add( 'select', 'custom_pre_id' , ts('Include Profile') . '<br />' . ts('(top of page)'), array('' => ts('- select -')) + $profiles );
        $this->add( 'select', 'custom_post_id', ts('Include Profile') . '<br />' . ts('(bottom of page)'), array('' => ts('- select -')) + $profiles );

        $this->addFormRule( array( 'CRM_Contribute_Form_ContributionPage_Custom', 'formRule' ) , $this->_id);
        
        parent::buildQuickForm( );
    }

    /** 
     * This function sets the default values for the form. Note that in edit/view mode 
     * the default values are retrieved from the database 
     * 
     * @access public 
     * @return void 
     */ 
    function setDefaultValues() 
    { 
        $defaults = parent::setDefaultValues( );

         if ( $this->_id ) {
             $title = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'title' );
             CRM_Utils_System::setTitle(ts('Include Profiles (%1)', array(1 => $title)));
         }
            
        require_once 'CRM/Core/BAO/UFJoin.php';

        $ufJoinParams = array( 'entity_table' => 'civicrm_contribution_page',  
                               'entity_id'    => $this->_id );
        list( $defaults['custom_pre_id'],
              $defaults['custom_post_id'] ) = 
            CRM_Core_BAO_UFJoin::getUFGroupIds( $ufJoinParams ); 
        
        return $defaults;
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

        if ($this->_action & CRM_Core_Action::UPDATE) {
            $params['id'] = $this->_id;
        }

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
         
        // also update uf join table
        $ufJoinParams = array( 'is_active'    => 1, 
                               'module'       => 'CiviContribute',
                               'entity_table' => 'civicrm_contribution_page', 
                               'entity_id'    => $this->_id );

        require_once 'CRM/Core/BAO/UFJoin.php';
        // first delete all past entries
        CRM_Core_BAO_UFJoin::deleteAll( $ufJoinParams );

        if ( ! empty( $params['custom_pre_id'] ) ) {
            $ufJoinParams['weight'     ] = 1;
            $ufJoinParams['uf_group_id'] = $params['custom_pre_id'];
            CRM_Core_BAO_UFJoin::create( $ufJoinParams );
        }

        unset( $ufJoinParams['id'] );

        if ( ! empty( $params['custom_post_id'] ) ) {
            $ufJoinParams['weight'     ] = 2; 
            $ufJoinParams['uf_group_id'] = $params['custom_post_id'];  
            CRM_Core_BAO_UFJoin::create( $ufJoinParams ); 
        }

        $transaction->commit( ); 
    }

    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) 
    {
        return ts( 'Include Profiles' );
    }

    /**  
     * global form rule  
     *  
     * @param array $fields  the input form values  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * @static  
     */  
    static function formRule( $fields, $files, $contributionPageId ) 
    {  
        $errors = array( );  
        $preProfileType = $postProfileType = null;
        // for membership profile make sure Membership section is enabled
        // get membership section for this contribution page
        require_once 'CRM/Member/DAO/MembershipBlock.php';
        $dao = new CRM_Member_DAO_MembershipBlock();
        $dao->entity_table = 'civicrm_contribution_page';
        $dao->entity_id    = $contributionPageId; 
        
        $membershipEnable = false;
        
        if ( $dao->find(true) && $dao->is_active ) {
            $membershipEnable = true;
        }
        
        require_once "CRM/Core/BAO/UFField.php";
        if ( $fields['custom_pre_id'] ) {
            $preProfileType  = CRM_Core_BAO_UFField::getProfileType( $fields['custom_pre_id'] );
        }

        if ( $fields['custom_post_id'] ) {
            $postProfileType = CRM_Core_BAO_UFField::getProfileType( $fields['custom_post_id'] );
        }
        
        $errorMsg = ts('You must enable the Membership Block for this Contribution Page if you want to include a Profile with Membership fields.');

        if ( ( $preProfileType == 'Membership' ) && !$membershipEnable ) {
            $errors['custom_pre_id'] = $errorMsg;
        }
        
        if ( ( $postProfileType == 'Membership' ) && !$membershipEnable ) {
            $errors['custom_post_id'] = $errorMsg;
        }

        return empty($errors) ? true : $errors;
    }
}


