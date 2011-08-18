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

/**
 * This class generates form components for Payment-Instrument
 * 
 */
class CRM_Member_Form_MembershipView extends CRM_Core_Form
{
    /**  
     * Function to set variables up before form is built  
     *                                                            
     * @return void  
     * @access public  
     */
    public function preProcess( ) {
        require_once 'CRM/Member/BAO/Membership.php';
        require_once 'CRM/Member/BAO/MembershipType.php';
        require_once 'CRM/Core/BAO/CustomGroup.php';

        $values = array( ); 
        $id = CRM_Utils_Request::retrieve('id', 'Positive', $this );
        
        // Make sure context is assigned to template for condition where we come here view civicrm/membership/view
        $context    = CRM_Utils_Request::retrieve('context', 'String', $this );
        $this->assign( 'context', $context );
        
        if ( $id ) {
            $params = array( 'id' => $id ); 
            
            CRM_Member_BAO_Membership::retrieve( $params, $values );

            // build associated contributions
            require_once 'CRM/Member/Page/Tab.php';
            CRM_Member_Page_Tab::associatedContribution( $values['contact_id'], $id );


            //Provide information about membership source when it is the result of a relationship (CRM-1901)
            $values['owner_membership_id']       = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', 
                                                                                $id, 
                                                                                'owner_membership_id' );
            
            if ( isset($values['owner_membership_id']) ) {
                $values['owner_contact_id']      = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', 
                                                                                $values['owner_membership_id'], 
                                                                                'contact_id',
                                                                                'id' );
                
                $values['owner_display_name']    = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', 
                                                                                $values['owner_contact_id'], 
                                                                                'display_name',
                                                                                'id' );

                $membershipType = CRM_Member_BAO_MembershipType::getMembershipTypeDetails($values['membership_type_id']);
                $direction =  strrev($membershipType['relationship_direction']);
                // To display relationship type in view membership page
                $relTypeIds = str_replace( CRM_Core_DAO::VALUE_SEPARATOR, ",", $membershipType['relationship_type_id'] );
                $sql = "
SELECT relationship_type_id,
  CASE    
  WHEN  contact_id_a = {$values['owner_contact_id']} AND contact_id_b = {$values['contact_id']} THEN 'b_a'
  WHEN  contact_id_b = {$values['owner_contact_id']} AND contact_id_a = {$values['contact_id']} THEN 'a_b'
END AS 'relType'
  FROM civicrm_relationship 
 WHERE relationship_type_id IN ($relTypeIds)";
                $dao = CRM_Core_DAO::executeQuery( $sql );
                $values['relationship'] = null; 
                while ( $dao->fetch( ) ) {
                    $membershipType['relationship_type_id'] = $dao->relationship_type_id;
                    $direction = $dao->relType;
                    if ( $direction && $membershipType['relationship_type_id'] ) {
                        if ( $values['relationship'] ) {
                            $values['relationship'] .= ',';
                        }
                        $values['relationship'] .= CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_RelationshipType', 
                                                                                $membershipType['relationship_type_id'],
                                                                                "name_$direction",
                                                                                'id');
                    }
                }
            }

            $displayName = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', 
                                                         $values['contact_id'], 
                                                          'display_name' );
            $this->assign( 'displayName', $displayName );        

            // add viewed membership to recent items list
            require_once 'CRM/Utils/Recent.php';
            $url = CRM_Utils_System::url( 'civicrm/contact/view/membership', 
                                          "action=view&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
            
            $title = $displayName . ' - ' . ts('Membership Type:') . ' ' . $values['membership_type'];
            
            require_once 'CRM/Core/Permission.php';
            $recentOther = array( );
            if ( CRM_Core_Permission::checkActionPermission( 'CiviMember', CRM_Core_Action::UPDATE ) ) {
                $recentOther['editUrl'] = CRM_Utils_System::url( 'civicrm/contact/view/membership', 
                                                                 "action=update&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
            }
            if ( CRM_Core_Permission::checkActionPermission( 'CiviMember', CRM_Core_Action::DELETE ) ) {
                $recentOther['deleteUrl'] = CRM_Utils_System::url( 'civicrm/contact/view/membership', 
                                                                   "action=delete&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
            }
            CRM_Utils_Recent::add( $title,
                                   $url,
                                   $values['id'],
                                   'Membership',
                                   $values['contact_id'],
                                   null,
                                   $recentOther
                                   );
            
            CRM_Member_Page_Tab::setContext($values['contact_id']);

            $memType = CRM_Core_DAO::getFieldValue("CRM_Member_DAO_Membership",$id,"membership_type_id");
            
            $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Membership',$this, $id,0,$memType);
			CRM_Core_BAO_CustomGroup::buildCustomDataView( $this, $groupTree );
            
            $isRecur = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership' , $id, 'contribution_recur_id' );
            
            $autoRenew = $isRecur ? true : false;
        }
        
        if ( CRM_Utils_Array::value('is_test', $values ) )  {
            $values['membership_type'] .= ' (test) ';
        }

        $subscriptionCancelled = CRM_Member_BAO_Membership::isSubscriptionCancelled( $id );
        $values['auto_renew'] = ( $autoRenew && !$subscriptionCancelled ) ? 'Yes' : 'No';

        //do check for campaigns
        if ( $campaignId = CRM_Utils_Array::value( 'campaign_id', $values ) ) {
            require_once 'CRM/Campaign/BAO/Campaign.php';
            $campaigns = CRM_Campaign_BAO_Campaign::getCampaigns( $campaignId );
            $values['campaign'] = $campaigns[$campaignId];
        }
        
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
                                array ( 'type'      => 'cancel',  
                                        'name'      => ts('Done'),  
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                        'isDefault' => true   )
                                )
                          );
    }

}


