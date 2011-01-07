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

/**
 * This class generates form components for Payment-Instrument
 * 
 */
class CRM_Contribute_Form_ContributionView extends CRM_Core_Form
{
    /**  
     * Function to set variables up before form is built  
     *                                                            
     * @return void  
     * @access public  
     */
    public function preProcess( ) 
    {
        $id     = $this->get( 'id' );
        $values = $ids = array( ); 
        $params = array( 'id' => $id ); 
        $context = CRM_Utils_Request::retrieve('context', 'String', $this );
        $this->assign('context', $context );

        require_once 'CRM/Contribute/BAO/Contribution.php';
        CRM_Contribute_BAO_Contribution::getValues( $params, $values, $ids );            

        $softParams = array( 'contribution_id' => $values['contribution_id'] );
        if( $softContribution = CRM_Contribute_BAO_Contribution::getSoftContribution( $softParams, true ) ) {
            $values = array_merge( $values, $softContribution );
        } 
        CRM_Contribute_BAO_Contribution::resolveDefaults( $values );
        
        if ( CRM_Utils_Array::value( 'contribution_page_id', $values ) ){
            $contribPages = CRM_Contribute_PseudoConstant::contributionPage( );
            $values["contribution_page_title"] = CRM_Utils_Array::value( CRM_Utils_Array::value( 'contribution_page_id', $values ) , $contribPages );
        }
        
        if ( CRM_Utils_Array::value( 'honor_contact_id', $values ) ) {
            $sql    = "SELECT display_name FROM civicrm_contact WHERE id = %1";
            $params = array( 1 => array( $values['honor_contact_id'], 'Integer' ) );
            $dao = CRM_Core_DAO::executeQuery( $sql, $params );
            if ( $dao->fetch() ) {
                $url = CRM_Utils_System::url( 'civicrm/contact/view', "reset=1&cid=$values[honor_contact_id]" );
                $values["honor_display"] = "<A href = $url>". $dao->display_name ."</A>"; 
            }
            $honor =CRM_Core_PseudoConstant::honor( );
            $values['honor_type'] = $honor[$values['honor_type_id']]; 
        }
        
        if ( CRM_Utils_Array::value( 'contribution_recur_id', $values ) ) {
            $sql    = "SELECT  installments, frequency_interval, frequency_unit FROM civicrm_contribution_recur WHERE id = %1";
            $params = array( 1 => array( $values['contribution_recur_id'], 'Integer' ) );
            $dao = CRM_Core_DAO::executeQuery( $sql, $params );
            if ( $dao->fetch() ) {
                $values["recur_installments"]       = $dao->installments  ;
                $values["recur_frequency_unit"]     = $dao->frequency_unit;
                $values["recur_frequency_interval"] = $dao->frequency_interval;
            }
        }

        $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Contribution', $this, $id, 0,$values['contribution_type_id'] );
		CRM_Core_BAO_CustomGroup::buildCustomDataView( $this, $groupTree );
        
        $premiumId = null;
        if ( $id ) {
            require_once 'CRM/Contribute/DAO/ContributionProduct.php';
            $dao = new CRM_Contribute_DAO_ContributionProduct();
            $dao->contribution_id = $id;
            if ( $dao->find(true) ) {
               $premiumId = $dao->id;
               $productID = $dao->product_id; 
            }
        }
        
        if ( $premiumId ) {
            require_once 'CRM/Contribute/DAO/Product.php';
            $productDAO = new CRM_Contribute_DAO_Product();
            $productDAO->id  = $productID;
            $productDAO->find(true);
           
            $this->assign('premium' , $productDAO->name );
            $this->assign('option',$dao->product_option);
            $this->assign('fulfilled',$dao->fulfilled_date);
        }

        // Get Note
        $noteValue = CRM_Core_BAO_Note::getNote( $values['id'], 'civicrm_contribution' );
        $values['note'] =  array_values($noteValue);

		// show billing address location details, if exists
		if ( CRM_Utils_Array::value( 'address_id', $values ) ) {
			$addressParams  = array( 'id' => CRM_Utils_Array::value( 'address_id', $values ) );	
			$addressDetails = CRM_Core_BAO_Address::getValues( $addressParams, false, 'id' );
			$addressDetails = array_values( $addressDetails );
            $values['billing_address'] = $addressDetails[0]['display'];
		}
       
        //get soft credit record if exists.
        if( $softContribution = CRM_Contribute_BAO_Contribution::getSoftContribution( $softParams ) ) {
            
            $softContribution['softCreditToName']   = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', 
                                                                                   $softContribution['soft_credit_to'], 'display_name' );
            //hack to avoid dispalyName conflict 
            //for viewing softcredit record.
            $softContribution['displayName']   =   CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', 
                                                                                $values['contact_id'], 'display_name' );
            $values = array_merge( $values, $softContribution );
        } 
        
        require_once 'CRM/Price/BAO/Set.php';
        $lineItems = array( );
        if ( $id && CRM_Price_BAO_Set::getFor( 'civicrm_contribution', $id ) ) {
            require_once 'CRM/Price/BAO/LineItem.php';
            $lineItems[] = CRM_Price_BAO_LineItem::getLineItems( $id, 'contribution' );
        }
        $this->assign( 'lineItem', empty( $lineItems ) ? false : $lineItems );
        $values['totalAmount'] = $values['total_amount'];
        
		// assign values to the template
        $this->assign( $values ); 
        
        // add viewed contribution to recent items list
        require_once 'CRM/Utils/Recent.php';
        require_once 'CRM/Utils/Money.php';
        require_once 'CRM/Contact/BAO/Contact.php';
        $url = CRM_Utils_System::url( 'civicrm/contact/view/contribution', 
                                      "action=view&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
                                      
        $displayName = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $values['contact_id'], 'display_name' );
        $this->assign( 'displayName', $displayName );
        
        $title = $displayName . 
            ' - (' . CRM_Utils_Money::format( $values['total_amount'] ) . ' ' . 
            ' - ' . $values['contribution_type'] . ')';
        
        $recentOther = array( );
        if ( CRM_Core_Permission::checkActionPermission('CiviContribute', CRM_Core_Action::UPDATE) ) {
            $recentOther['editUrl'] = CRM_Utils_System::url( 'civicrm/contact/view/contribution', 
                                                             "action=update&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
        }
        if ( CRM_Core_Permission::checkActionPermission('CiviContribute', CRM_Core_Action::DELETE) ) {
            $recentOther['deleteUrl'] = CRM_Utils_System::url( 'civicrm/contact/view/contribution', 
                                                               "action=delete&reset=1&id={$values['id']}&cid={$values['contact_id']}&context=home" );
        }
        CRM_Utils_Recent::add( $title,
                               $url,
                               $values['id'],
                               'Contribution',
                               $values['contact_id'],
                               null,
                               $recentOther
                               );
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


