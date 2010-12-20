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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Contribute/PseudoConstant.php';
/**
 * This class generates form components for Pledge
 * 
 */
class CRM_Pledge_Form_PledgeView extends CRM_Core_Form
{
    /**  
     * Function to set variables up before form is built  
     *                                                            
     * @return void  
     * @access public  
     */
    public function preProcess( ) 
    {
        require_once 'CRM/Pledge/BAO/Pledge.php';
            
        $values = $ids = array( ); 
        $params = array( 'id' => $this->get( 'id' ) ); 
        CRM_Pledge_BAO_Pledge::getValues( $params, 
                                          $values,  
                                          $ids );

        $values['frequencyUnit'] = ts( '%1(s)', array( 1 => $values['frequency_unit'] ) );
        
        if (isset( $values["honor_contact_id"] ) && $values["honor_contact_id"] ) {
            $sql = "SELECT display_name FROM civicrm_contact WHERE id = " . $values["honor_contact_id"];
            $dao = new CRM_Core_DAO();
            $dao->query($sql);
            if ( $dao->fetch() ) {
                $url = CRM_Utils_System::url( 'civicrm/contact/view', "reset=1&cid=$values[honor_contact_id]" );
                $values["honor_display"] = "<A href = $url>". $dao->display_name ."</A>"; 
            }
            $honor =CRM_Core_PseudoConstant::honor( );
            $values['honor_type'] = $honor[$values['honor_type_id']]; 
        }
        
        //handle custom data.
        $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Pledge', $this, $params['id'] );
		CRM_Core_BAO_CustomGroup::buildCustomDataView( $this, $groupTree );
        
        if ( CRM_Utils_Array::value( 'contribution_page_id', $values ) ) { 
            $values['contribution_page'] = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $values['contribution_page_id'], 'title' );
        }
        
        $values['contribution_type'] = CRM_Utils_Array::value( $values['contribution_type_id'], CRM_Contribute_PseudoConstant::contributionType() );
        
        if ( $values['status_id'] ) { 
            $values['pledge_status'] = CRM_Utils_Array::value( $values['status_id'], CRM_Contribute_PseudoConstant::contributionStatus() );
        }
        
        require_once 'CRM/Utils/Recent.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        $url = CRM_Utils_System::url( 'civicrm/contact/view/pledge', 
               "action=view&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
        
        $recentOther = array( );
        if ( CRM_Core_Permission::checkActionPermission( 'CiviPledge', CRM_Core_Action::UPDATE ) ) {
            $recentOther['editUrl'] = CRM_Utils_System::url( 'civicrm/contact/view/pledge', 
                                                             "action=update&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
        } 
        if ( CRM_Core_Permission::checkActionPermission( 'CiviPledge', CRM_Core_Action::DELETE ) ) {
            $recentOther['deleteUrl'] = CRM_Utils_System::url( 'civicrm/contact/view/pledge', 
                                                               "action=delete&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
        }
        
        require_once 'CRM/Utils/Money.php';
        $displayName = CRM_Contact_BAO_Contact::displayName( $values['contact_id'] );
        $this->assign( 'displayName', $displayName );
        
        $title = $displayName . 
                 ' - (' . ts('Pledged') . ' ' . CRM_Utils_Money::format( $values['pledge_amount'] ) . 
                 ' - ' . $values['contribution_type'] . ')';

        // add Pledge to Recent Items
        CRM_Utils_Recent::add( $title,
                               $url,
                               $values['id'],
                               'Pledge',
                               $values['contact_id'],
                               null,
                               $recentOther
                               );
             
        $this->assign( $values );
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addButtons(array(  
                                array ( 'type'      => 'next',  
                                        'name'      => ts('Done'),  
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                        'isDefault' => true   )
                                )
                          );
    }

}


