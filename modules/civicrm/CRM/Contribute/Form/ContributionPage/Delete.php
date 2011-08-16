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

require_once 'CRM/Contribute/Form/ContributionPage.php';

/**
 * This class is to build the form for Deleting Group
 */
class CRM_Contribute_Form_ContributionPage_Delete extends CRM_Contribute_Form_ContributionPage {

    /**
     * page title
     *
     * @var string
     * @protected
     */
    protected $_title;

    /**
     * Check if there are any related contributions
     * 
     */
    protected $_relatedContributions;

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        //Check if there are contributions related to Contribution Page
        
        parent::preProcess();
        
        //check for delete
        if ( !CRM_Core_Permission::checkActionPermission( 'CiviContribute', $this->_action ) ) {
            CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );  
        }
        
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $dao = new CRM_Contribute_DAO_Contribution();
        $dao->contribution_page_id = $this->_id;
        
        if ( $dao->find(true) ) {
            $this->_relatedContributions = true;
            $this->assign('relatedContributions',true);
        }
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( )
    {
        $this->_title = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'title' );
        $this->assign( 'title', $this->_title );

        //if there are contributions related to Contribution Page 
        //then onle cancel button is displayed
        $buttons = array();
        if ( ! $this->_relatedContributions ) {
            $buttons[] = array ( 'type'      => 'next',
                                 'name'      => ts('Delete Contribution Page'),
                                 'isDefault' => true );
        }
        
        $buttons[] = array ( 'type' => 'cancel',
                             'name' => ts('Cancel') 
                             );
            
        $this->addButtons( $buttons );
    }

    /**
     * Process the form when submitted
     *
     * @return void
     * @access public
     */
    public function postProcess( )
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        // first delete the join entries associated with this contribution page
        require_once 'CRM/Core/DAO/UFJoin.php';
        $dao = new CRM_Core_DAO_UFJoin( );
        
        $params = array( 'entity_table' => 'civicrm_contribution_page',
                         'entity_id'    => $this->_id );
        $dao->copyValues( $params );
        $dao->delete( );
        
        require_once 'CRM/Core/OptionGroup.php';
        $groupName = "civicrm_contribution_page.amount.{$this->_id}";
        CRM_Core_OptionGroup::deleteAssoc($groupName);
        
        //next delete the membership block fields
        require_once 'CRM/Member/DAO/MembershipBlock.php';
        $dao = new CRM_Member_DAO_MembershipBlock( );
        $dao->entity_table = 'civicrm_contribution_page';
        $dao->entity_id    = $this->_id;
        $dao->delete( );

        //next delete the pcp block fields
        require_once 'CRM/Contribute/DAO/PCPBlock.php';
        $dao = new CRM_Contribute_DAO_PCPBlock( );
        $dao->entity_table = 'civicrm_contribution_page';
        $dao->entity_id    = $this->_id;
        $dao->delete( );
        
        // need to delete premiums. CRM-4586
        require_once 'CRM/Contribute/BAO/Premium.php';
        CRM_Contribute_BAO_Premium::deletePremium( $this->_id );
        
        // price set cleanup, CRM-5527 
        require_once 'CRM/Price/BAO/Set.php';
        CRM_Price_BAO_Set::removeFrom( 'civicrm_contribution_page', $this->_id );
        
        // finally delete the contribution page
        require_once 'CRM/Contribute/DAO/ContributionPage.php';
        $dao = new CRM_Contribute_DAO_ContributionPage( );
        $dao->id = $this->_id;
        $dao->delete( );

        $transaction->commit( );
        
        CRM_Core_Session::setStatus( ts('The contribution page \'%1\' has been deleted.', array( 1 => $this->_title ) ) );
    }
}


