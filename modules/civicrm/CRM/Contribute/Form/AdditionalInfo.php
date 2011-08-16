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

class CRM_Contribute_Form_AdditionalInfo 
{
    /** 
     * Function to build the form for Premium Information. 
     * 
     * @access public 
     * @return None 
     */ 
    function buildPremium( &$form )
    { 
        //premium section
        $form->add( 'hidden', 'hidden_Premium', 1 );
        require_once 'CRM/Contribute/DAO/Product.php';
        $sel1 = $sel2 = array();
        
        $dao = new CRM_Contribute_DAO_Product();
        $dao->is_active = 1;
        $dao->find();
        $min_amount = array();
        $sel1[0] = '-select product-';
        while ( $dao->fetch() ) {
            $sel1[$dao->id] = $dao->name." ( ".$dao->sku." )";
            $min_amount[$dao->id] = $dao->min_contribution;
            $options = explode(',', $dao->options);
            foreach ($options as $k => $v ) {
                $options[$k] = trim($v);
            }
            if( $options [0] != '' ) {
                $sel2[$dao->id] = $options;
            }
            $form->assign('premiums', true );
            
        }
        $form->_options = $sel2;
        $form->assign('mincontribution',$min_amount);
        $sel =& $form->addElement('hierselect', "product_name", ts('Premium'),'onclick="showMinContrib();"');
        $js = "<script type='text/javascript'>\n";
        $formName = 'document.forms.' . $form->getName( );
        
        for ( $k = 1; $k < 2; $k++ ) {
            if ( ! isset ($defaults['product_name'][$k] )|| (! $defaults['product_name'][$k] ) )  {
                $js .= "{$formName}['product_name[$k]'].style.display = 'none';\n"; 
            }
        }
        
        $sel->setOptions(array($sel1, $sel2 ));
        $js .= "</script>\n";
        $form->assign('initHideBoxes', $js);
        
        $this->addDate( 'fulfilled_date', ts('Fulfilled'), false, array( 'formatType' => 'activityDate') );
        $form->addElement('text', 'min_amount', ts('Minimum Contribution Amount'));
    }
    
    /** 
     * Function to build the form for Additional Details. 
     * 
     * @access public 
     * @return None 
     */ 
    function buildAdditionalDetail( &$form )
    { 
        //Additional information section
        $form->add( 'hidden', 'hidden_AdditionalDetail', 1 );
        
        $attributes = CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_Contribution' );
        
        $this->addDateTime( 'thankyou_date', ts('Thank-you Sent'), false, array( 'formatType' => 'activityDateTime') );
        
        // add various amounts
        $element =& $form->add( 'text', 'non_deductible_amount', ts('Non-deductible Amount'),
                                $attributes['non_deductible_amount'] );
        $form->addRule('non_deductible_amount', ts('Please enter a valid amount.'), 'money');
        
        if ( $form->_online ) {
            $element->freeze( );
        }
        $element =& $form->add( 'text', 'fee_amount', ts('Fee Amount'), 
                                $attributes['fee_amount'] );
        $form->addRule('fee_amount', ts('Please enter a valid amount.'), 'money');
        if ( $form->_online ) {
            $element->freeze( );
        }
        $element =& $form->add( 'text', 'net_amount', ts('Net Amount'),
                                $attributes['net_amount'] );
        $form->addRule('net_amount', ts('Please enter a valid amount.'), 'money');
        if ( $form->_online ) {
            $element->freeze( );
        }
        $element =& $form->add( 'text', 'invoice_id', ts('Invoice ID'), 
                                $attributes['invoice_id'] );
        if ( $form->_online ) {
            $element->freeze( );
        } else {
            $form->addRule( 'invoice_id',
                            ts( 'This Invoice ID already exists in the database.' ),
                            'objectExists', 
                            array( 'CRM_Contribute_DAO_Contribution', $form->_id, 'invoice_id' ) );
        }

        $form->add('select', 'contribution_page_id', 
                   ts( 'Online Contribution Page' ),
                   array( '' => ts( '- select -' ) ) +
                   CRM_Contribute_PseudoConstant::contributionPage( ) );
        
        
        $form->add('textarea', 'note', ts('Notes'),array("rows"=>4,"cols"=>60) );
        
    }
    
    /** 
     * Function to build the form for Honoree Information. 
     * 
     * @access public 
     * @return None 
     */ 
    function buildHonoree( &$form )
    { 
        //Honoree section
        $form->add( 'hidden', 'hidden_Honoree', 1 );
        $honor = CRM_Core_PseudoConstant::honor( );
        $extraOption = array('onclick' =>"return enableHonorType();");
        foreach ( $honor as $key => $var) {
            $honorTypes[$key] = HTML_QuickForm::createElement('radio', null, null, $var, $key, $extraOption);
        }
        $form->addGroup($honorTypes, 'honor_type_id', null);
        $form->add('select','honor_prefix_id',ts('Prefix') ,array('' => ts('- prefix -')) + CRM_Core_PseudoConstant::individualPrefix());
        $form->add('text','honor_first_name',ts('First Name'));
        $form->add('text','honor_last_name',ts('Last Name'));
        $form->add('text','honor_email',ts('Email'));
        $form->addRule( "honor_email", ts('Email is not valid.'), 'email' );
    }
    
    /** 
     * Function to build the form for PaymentReminders Information. 
     * 
     * @access public 
     * @return None 
     */ 
    function buildPaymentReminders( &$form )
    { 
        //PaymentReminders section
        $form->add( 'hidden', 'hidden_PaymentReminders', 1 );
        $form->add( 'text', 'initial_reminder_day', ts('Send Initial Reminder'), array('size'=>3) );
        $this->addRule('initial_reminder_day', ts('Please enter a valid reminder day.'), 'positiveInteger');
        $form->add( 'text', 'max_reminders', ts('Send up to'), array('size'=>3) );
        $this->addRule('max_reminders', ts('Please enter a valid No. of reminders.'), 'positiveInteger');
        $form->add( 'text', 'additional_reminder_day', ts('Send additional reminders'), array('size'=>3) );
        $this->addRule('additional_reminder_day', ts('Please enter a valid additional reminder day.'), 'positiveInteger');
    }
    
    /** 
     * Function to process the Premium Information 
     * 
     * @access public 
     * @return None 
     */ 
    function processPremium( &$params, $contributionID, $premiumID = null, &$options = null )
    {
        require_once 'CRM/Contribute/DAO/ContributionProduct.php';
        $dao = new CRM_Contribute_DAO_ContributionProduct();
        $dao->contribution_id = $contributionID;
        $dao->product_id      = $params['product_name'][0];
        $dao->fulfilled_date  = CRM_Utils_Date::processDate( $params['fulfilled_date'], null, true );
        if ( CRM_Utils_Array::value( $params['product_name'][0], $options ) ) {
            $dao->product_option  = $options[$params['product_name'][0]][$params['product_name'][1]];
        }
        if ($premiumID) {
            $premoumDAO = new CRM_Contribute_DAO_ContributionProduct();
            $premoumDAO->id  = $premiumID;
            $premoumDAO->find(true);
            if ( $premoumDAO->product_id == $params['product_name'][0] ) {
                $dao->id = $premiumID;
                $premium = $dao->save();
            } else {
                $premoumDAO->delete();
                $premium = $dao->save();
            }
        } else {
            $premium = $dao->save();
        } 
    }
    
    /** 
     * Function to process the Note 
     * 
     * @access public 
     * @return None 
     */ 
    function processNote( &$params, $contactID, $contributionID, $contributionNoteID = null )
    {
        //process note
        require_once 'CRM/Core/BAO/Note.php';
        $noteParams = array('entity_table' => 'civicrm_contribution', 
                            'note'         => $params['note'], 
                            'entity_id'    => $contributionID,
                            'contact_id'   => $contactID
                            );
        $noteID = array();
        if ( $contributionNoteID ) {
            $noteID = array( "id" => $contributionNoteID );
            $noteParams['note'] = $noteParams['note'] ? $noteParams['note'] : "null";
        } 
        CRM_Core_BAO_Note::add( $noteParams, $noteID );
    }
    
    /** 
     * Function to process the Common data 
     *  
     * @access public 
     * @return None 
     */ 
    function postProcessCommon( &$params, &$formatted )
    {
        $fields = array( 'non_deductible_amount',
                         'total_amount',
                         'fee_amount',
                         'net_amount',
                         'trxn_id',
                         'invoice_id',
                         'campaign_id',
                         'honor_type_id',
                         'contribution_page_id'
                         );
        foreach ( $fields as $f ) {
            $formatted[$f] = CRM_Utils_Array::value( $f, $params );
        }
        
        if ( CRM_Utils_Array::value('thankyou_date', $params ) && ! CRM_Utils_System::isNull( $params['thankyou_date'] ) ) {
            $formatted['thankyou_date'] = CRM_Utils_Date::processDate( $params['thankyou_date'], $params['thankyou_date_time'] );
        } else {
            $formatted['thankyou_date'] = 'null';
        }
        
        if ( CRM_Utils_Array::value( 'is_email_receipt', $params ) ) {
            $params['receipt_date'] = $formatted['receipt_date'] = date( 'YmdHis' );
        }

        if ( CRM_Utils_Array::value( 'honor_type_id', $params ) ) {
            require_once 'CRM/Contribute/BAO/Contribution.php';
            if ( $this->_honorID ) {
                $honorId = CRM_Contribute_BAO_Contribution::createHonorContact( $params , $this->_honorID );
            } else {
                $honorId = CRM_Contribute_BAO_Contribution::createHonorContact( $params );
            }
            $formatted["honor_contact_id"] = $honorId;
        } else {
            $formatted["honor_contact_id"] = 'null';
        }

        //special case to handle if all checkboxes are unchecked
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Contribution',
                                                             false,
                                                             false, 
                                                             CRM_Utils_Array::value('contribution_type_id',
                                                                                    $params ) );
        $formatted['custom'] = CRM_Core_BAO_CustomField::postProcess( $params,
                                                                      $customFields,
                                                                      CRM_Utils_Array::value( 'id',$params, null ),
                                                                      'Contribution' );
    }
    
    /** 
     * Function to send email receipt.
     * 
     * @form object  of Contribution form.
     * @param array  $params (reference ) an assoc array of name/value pairs.
     * @$ccContribution boolen,  is it credit card contribution.
     * @access public. 
     * @return None.
     */ 
    function emailReceipt( &$form, &$params, $ccContribution = false )
    {
        $this->assign('receiptType', 'contribution');
        // Retrieve Contribution Type Name from contribution_type_id
        $params['contributionType_name'] = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionType',
                                                                        $params['contribution_type_id'] );
        if ( CRM_Utils_Array::value( 'payment_instrument_id', $params ) ) {
            require_once 'CRM/Contribute/PseudoConstant.php';
            $paymentInstrument = CRM_Contribute_PseudoConstant::paymentInstrument( );
            $params['paidBy']  = $paymentInstrument[$params['payment_instrument_id']];
        }

        // retrieve individual prefix value for honoree
        if ( CRM_Utils_Array::value( 'hidden_Honoree', $params ) ) {
            $individualPrefix       = CRM_Core_PseudoConstant::individualPrefix();
            $honor                  = CRM_Core_PseudoConstant::honor( ); 
            $params['honor_prefix'] = CRM_Utils_Array::value(  CRM_Utils_Array::value( 'honor_prefix_id',
                                                                                       $params ),
                                                               $individualPrefix );
            $params["honor_type"]   = CRM_Utils_Array::value( CRM_Utils_Array::value( 'honor_type_id',
                                                                                      $params ),
                                                              $honor );
        }
        
        // retrieve premium product name and assigned fulfilled
        // date to template
        if ( CRM_Utils_Array::value( 'hidden_Premium', $params ) ) {
            if (  CRM_Utils_Array::value( $params['product_name'][0], $form->_options ) ) {
                $params['product_option'] = $form->_options[$params['product_name'][0]][$params['product_name'][1]];
            }
            //fix for crm-4584
            if(!empty($params['product_name'])){
                require_once 'CRM/Contribute/DAO/Product.php';
                $productDAO = new CRM_Contribute_DAO_Product();
                $productDAO->id = $params['product_name'][0];
                $productDAO->find(true);
                $params['product_name'] = $productDAO->name;
                $params['product_sku']  = $productDAO->sku;
            }
            $this->assign('fulfilled_date', CRM_Utils_Date::processDate( $params['fulfilled_date'] ) );
        }
        
        $this->assign( 'ccContribution', $ccContribution );
        if ( $ccContribution ) {
            //build the name.
            $name = CRM_Utils_Array::value( 'billing_first_name', $params );
            if ( CRM_Utils_Array::value( 'billing_middle_name', $params ) ) {
                $name .= " {$params['billing_middle_name']}";
            }
            $name .= ' ' . CRM_Utils_Array::value( 'billing_last_name', $params );
            $name = trim( $name );
            $this->assign( 'billingName', $name );
            
            //assign the address formatted up for display
            $addressParts  = array( "street_address" => "billing_street_address-{$form->_bltID}",
                                    "city"           => "billing_city-{$form->_bltID}",
                                    "postal_code"    => "billing_postal_code-{$form->_bltID}",
                                    "state_province" => "state_province-{$form->_bltID}",
                                    "country"        => "country-{$form->_bltID}");
            
            $addressFields = array( );
            foreach ( $addressParts as $name => $field ) {
                $addressFields[$name] = CRM_Utils_Array::value( $field, $params );
            }
            require_once 'CRM/Utils/Address.php';
            $this->assign('address', CRM_Utils_Address::format( $addressFields ) );
            
            $date = CRM_Utils_Date::format( $params['credit_card_exp_date'] );  
            $date = CRM_Utils_Date::mysqlToIso( $date ); 
            $this->assign( 'credit_card_type', CRM_Utils_Array::value( 'credit_card_type', $params ) );
            $this->assign( 'credit_card_exp_date', $date );
            $this->assign( 'credit_card_number',
                           CRM_Utils_System::mungeCreditCard( $params['credit_card_number'] ) );
        } else {
            //offline contribution
            // assigned various dates to the templates
            $form->assign('receipt_date',  CRM_Utils_Date::processDate( $params['receipt_date'] ) );
            $form->assign('cancel_date',   CRM_Utils_Date::processDate( $params['cancel_date']  ) );
            if ( CRM_Utils_Array::value( 'thankyou_date', $params ) ) {
                $form->assign('thankyou_date', CRM_Utils_Date::processDate( $params['thankyou_date'] ) );
            }
            if ( $form->_action & CRM_Core_Action::UPDATE ) {
                $form->assign( 'lineItem', empty( $form->_lineItems ) ? false : $form->_lineItems );
            }
        }
        
        //handle custom data
        if ( CRM_Utils_Array::value( 'hidden_custom', $params ) ) {
            $contribParams = array( array( 'contribution_id', '=', $params['contribution_id'], 0, 0 ) );
            if ( $form->_mode == 'test' ) {
                $contribParams[] = array( 'contribution_test', '=', 1, 0, 0 );
            }
            
            //retrieve custom data
            require_once "CRM/Core/BAO/UFGroup.php";
            $customGroup = array( ); 
            
            foreach ( $form->_groupTree as $groupID => $group ) {
                $customFields = $customValues = array( );
                if ( $groupID == 'info' ) {
                    continue;
                } 
                foreach ( $group['fields'] as $k => $field ) {
                    $field['title'] = $field['label'];
                    $customFields["custom_{$k}"] = $field;
                }
                
                //build the array of customgroup contain customfields.
                CRM_Core_BAO_UFGroup::getValues( $params['contact_id'], $customFields, $customValues , false, $contribParams );
                $customGroup[$group['title']] = $customValues;
            }
            //assign all custom group and corresponding fields to template.
            $form->assign( 'customGroup', $customGroup );
        }
        
        $form->assign_by_ref( 'formValues', $params );
        require_once 'CRM/Contact/BAO/Contact/Location.php';
        require_once 'CRM/Utils/Mail.php';
        list( $contributorDisplayName, 
              $contributorEmail ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $params['contact_id'] );
        $this->assign( 'contactID', $params['contact_id'] );
        $this->assign( 'contributionID', $params['contribution_id'] );
        $this->assign( 'currency', $params['currency']);
        $this->assign( 'receive_date',  CRM_Utils_Date::processDate( $params['receive_date'] ) );

        require_once 'CRM/Core/BAO/MessageTemplates.php';
        list ($sendReceipt, $subject, $message, $html) = CRM_Core_BAO_MessageTemplates::sendTemplate(
            array(
                'groupName' => 'msg_tpl_workflow_contribution',
                'valueName' => 'contribution_offline_receipt',
                'contactId' => $params['contact_id'],
                'from'      => $params['from_email_address'],
                'toName'    => $contributorDisplayName,
                'toEmail'   => $contributorEmail,
                'isTest'    => $form->_mode == 'test',
            )
        );

        return $sendReceipt;
    }
    
    /** 
     * Function to process price set and line items. 
     * 
     * @access public 
     * @return None 
     */ 
    function processPriceSet( $contributionId, $lineItem )
    {
        if ( !$contributionId || !is_array( $lineItem )
             || CRM_Utils_system::isNull( $lineItem ) ) {
            return;
        }
        
        require_once 'CRM/Price/BAO/Set.php';
        require_once 'CRM/Price/BAO/LineItem.php';
        foreach ( $lineItem as $priceSetId => $values ) {
            if ( !$priceSetId ) continue;
            foreach( $values as $line ) {
                $line['entity_table'] = 'civicrm_contribution';
                $line['entity_id'] = $contributionId;
                CRM_Price_BAO_LineItem::create( $line );
            }
            CRM_Price_BAO_Set::addTo( 'civicrm_contribution', $contributionId, $priceSetId );
        }
    }
    
}
