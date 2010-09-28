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

class CRM_Core_Payment_Form {
    /** 
     * create all common fields needed for a credit card or direct debit transaction
     *                                                           
     * @return void 
     * @access protected
     */ 
    protected function _setPaymentFields( &$form) {
        $bltID = $form->_bltID;

        $form->_fields['billing_first_name'] = 
            array( 'htmlType'   => 'text', 
                   'name'       => 'billing_first_name', 
                   'title'      => ts('Billing First Name'),
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 30, 'maxlength' => 60, 'autocomplete' => 'off' ),
                   'is_required'=> true 
                   );
        
        $form->_fields['billing_middle_name'] = 
            array( 'htmlType'   => 'text', 
                   'name'       => 'billing_middle_name', 
                   'title'      => ts('Billing Middle Name'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 30, 'maxlength' => 60, 'autocomplete' => 'off' ), 
                   'is_required'=> false 
                   );
        
        $form->_fields['billing_last_name'] = 
            array( 'htmlType'   => 'text', 
                   'name'       => 'billing_last_name', 
                   'title'      => ts('Billing Last Name'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 30, 'maxlength' => 60, 'autocomplete' => 'off' ), 
                   'is_required'=> true 
                   );
                                         
        $form->_fields["billing_street_address-{$bltID}"] = 
            array( 'htmlType'   => 'text', 
                   'name'       => "billing_street_address-{$bltID}",
                   'title'      => ts('Street Address'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 30, 'maxlength' => 60, 'autocomplete' => 'off' ), 
                   'is_required'=> true 
                   );
                                         
        $form->_fields["billing_city-{$bltID}"] = 
            array( 'htmlType'   => 'text', 
                   'name'       => "billing_city-{$bltID}",
                   'title'      => ts('City'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 30, 'maxlength' => 60, 'autocomplete' => 'off' ), 
                   'is_required'=> true 
                   );
        
        $form->_fields["billing_state_province_id-{$bltID}"] = 
            array( 'htmlType'   => 'select', 
                   'name'       => "billing_state_province_id-{$bltID}",
                   'title'      => ts('State / Province'), 
                   'cc_field'   => true,
                   'attributes' => array( '' => ts( '- select -' ) ) +
                   CRM_Core_PseudoConstant::stateProvince( ),
                   'is_required'=> self::checkRequiredStateProvince( $form )
                   );
        
        $form->_fields["billing_postal_code-{$bltID}"] = 
            array( 'htmlType'   => 'text', 
                   'name'       => "billing_postal_code-{$bltID}",
                   'title'      => ts('Postal Code'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 30, 'maxlength' => 60, 'autocomplete' => 'off' ), 
                   'is_required'=> true 
                   );
        
        $form->_fields["billing_country_id-{$bltID}"] = 
            array( 'htmlType'   => 'select', 
                   'name'       => "billing_country_id-{$bltID}", 
                   'title'      => ts('Country'), 
                   'cc_field'   => true,
                   'attributes' => array( '' => ts( '- select -' ) ) + 
                   CRM_Core_PseudoConstant::country( ),
                   'is_required'=> true 
                   );
    }
    
    
    /** 
     * create all fields needed for a credit card transaction
     *                                                           
     * @return void 
     * @access public 
     */ 
    function setCreditCardFields( &$form ) {
        CRM_Core_Payment_Form::_setPaymentFields( $form );
                             
        $form->_fields['credit_card_number'] = 
            array( 'htmlType'   => 'text', 
                   'name'       => 'credit_card_number', 
                   'title'      => ts('Card Number'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 20, 'maxlength' => 20, 'autocomplete' => 'off' ), 
                   'is_required'=> true 
                   );
                                         
        $form->_fields['cvv2'] = 
            array( 'htmlType'   => 'text', 
                   'name'       => 'cvv2', 
                   'title'      => ts('Security Code'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 5, 'maxlength' => 10, 'autocomplete' => 'off' ), 
                   'is_required'=> true 
                   );
                                         
        $form->_fields['credit_card_exp_date'] = 
            array( 'htmlType'   => 'date', 
                   'name'       => 'credit_card_exp_date', 
                   'title'      => ts('Expiration Date'), 
                   'cc_field'   => true,
                   'attributes' => CRM_Core_SelectValues::date( 'creditCard' ),
                   'is_required'=> true 
                   );

        require_once 'CRM/Contribute/PseudoConstant.php';
        $creditCardType = 
            array( '' => ts('- select -')) + CRM_Contribute_PseudoConstant::creditCard( );
        $form->_fields['credit_card_type'] = 
            array( 'htmlType'   => 'select', 
                   'name'       => 'credit_card_type', 
                   'title'      => ts('Card Type'), 
                   'cc_field'   => true,
                   'attributes' => $creditCardType,
                   'is_required'=> true 
                   );
    }

    /** create all fields needed for direct debit transaction
     *                                                           
     * @return void 
     * @access public 
     */ 
    function setDirectDebitFields( &$form ) {
        CRM_Core_Payment_Form::_setPaymentFields( $form );

        $form->_fields['account_holder'] = 
            array( 'htmlType'   => 'text', 
                   'name'       => 'account_holder', 
                   'title'      => ts('Account Holder'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 20, 'maxlength' => 34, 'autocomplete' => 'on' ), 
                   'is_required'=> true 
                   );
       
        //e.g. IBAN can have maxlength of 34 digits
        $form->_fields['bank_account_number'] = 
            array( 'htmlType'   => 'text', 
                   'name'       => 'bank_account_number', 
                   'title'      => ts('Bank Account Number'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 20, 'maxlength' => 34, 'autocomplete' => 'off' ), 
                   'is_required'=> true 
                   );
         
        //e.g. SWIFT-BIC can have maxlength of 11 digits
        $form->_fields['bank_identification_number'] = 
            array( 'htmlType'   => 'text', 
                   'name'       => 'bank_identification_number', 
                   'title'      => ts('Bank Identification Number'), 
                   'cc_field'   => true,
                   'attributes' => array( 'size' => 20, 'maxlength' => 11, 'autocomplete' => 'off' ), 
                   'is_required'=> true 
                   );
        
         $form->_fields['bank_name'] = 
             array( 'htmlType'   => 'text', 
                    'name'       => 'bank_name', 
                    'title'      => ts('Bank Name'), 
                    'cc_field'   => true,
                    'attributes' => array( 'size' => 20, 'maxlength' => 64, 'autocomplete' => 'off' ), 
                    'is_required'=> true 
                    );
     }

    /** 
     * Function to add all the credit card fields
     * 
     * @return None 
     * @access public 
     */
    function buildCreditCard( &$form, $useRequired = false ) {
        require_once 'CRM/Core/Payment.php';
        if ( $form->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_FORM) {
            foreach ( $form->_fields as $name => $field ) {
                if ( isset( $field['cc_field'] ) &&
                     $field['cc_field'] ) {
                    $form->add( $field['htmlType'],
                                $field['name'],
                                $field['title'],
                                $field['attributes'],
                                $useRequired ? $field['is_required'] : false );
                }
            }

            $form->addRule( 'cvv2',
                            ts( 'Please enter a valid value for your card security code. This is usually the last 3-4 digits on the card\'s signature panel.' ),
                            'integer' );

            $form->addRule( 'credit_card_exp_date',
                            ts('Credit card expiration date cannot be a past date.'),
                            'currentDate', true );

            // also take care of state country widget
            require_once 'CRM/Core/BAO/Address.php';
            $stateCountryMap = array( 1 => array( 'country'        => "billing_country_id-{$form->_bltID}"       ,
                                                  'state_province' => "billing_state_province_id-{$form->_bltID}" ) );
            CRM_Core_BAO_Address::addStateCountryMap( $stateCountryMap );
        }
            
        if ( $form->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_BUTTON ) {
            $form->_expressButtonName = $form->getButtonName( 'upload', 'express' );
            $form->assign( 'expressButtonName', $form->_expressButtonName );
            $form->add('image',
                       $form->_expressButtonName,
                       $form->_paymentProcessor['url_button'],
                       array( 'class' => 'form-submit' ) );
        }
    }

    /** 
     * Function to add all the direct debit fields
     * 
     * @return None 
     * @access public 
     */
    function buildDirectDebit( &$form, $useRequired = false ) {
        require_once 'CRM/Core/Payment.php';

        if ( $form->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_FORM) {
            foreach ( $form->_fields as $name => $field ) {
                if ( isset( $field['cc_field'] ) &&
                     $field['cc_field'] ) {
                    $form->add( $field['htmlType'],
                                $field['name'],
                                $field['title'],
                                $field['attributes'],
                                $useRequired ? $field['is_required'] : false );
                }
            }

            $form->addRule( 'bank_identification_number',
                            ts( 'Please enter a valid Bank Identification Number (value must not contain punctuation characters).' ),
                            'nopunctuation' );

            $form->addRule( 'bank_account_number',
                            ts('Please enter a valid Bank Account Number (value must not contain punctuation characters).'),
                            'nopunctuation' );
        }            
            
        if ( $form->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_BUTTON ) {
            $form->_expressButtonName = $form->getButtonName( $form->buttonType( ), 'express' );
            $form->add('image',
                       $form->_expressButtonName,
                       $form->_paymentProcessor['url_button'],
                       array( 'class' => 'form-submit' ) );
        }
    }

    /**
     * function to map address fields
     *
     * @return void
     * @static
     */
    static function mapParams( $id, &$src, &$dst, $reverse = false ) {
        static $map = null;
        if ( ! $map ) {
            $map = array( 'first_name'             => 'billing_first_name'        ,
                          'middle_name'            => 'billing_middle_name'       ,
                          'last_name'              => 'billing_last_name'         ,
                          'email'                  => "email-$id"                 ,
                          'street_address'         => "billing_street_address-$id"        ,
                          'supplemental_address_1' => "billing_supplemental_address_1-$id",
                          'city'                   => "billing_city-$id"                  ,
                          'state_province'         => "billing_state_province-$id"        ,
                          'postal_code'            => "billing_postal_code-$id"           ,
                          'country'                => "billing_country-$id"               ,
                          );
        }
        
        foreach ( $map as $n => $v ) {
            if ( ! $reverse ) {
                if ( isset( $src[$n] ) ) {
                    $dst[$v] = $src[$n];
                }
            } else {
                if ( isset( $src[$v] ) ) {
                    $dst[$n] = $src[$v];
                }
            }
        }
    }
    
    /**
     * function to return state/province is_required = true/false 
     *
     */
    function checkRequiredStateProvince( $form ) 
    {     
        // If selected country has possible values for state/province mark the
        // state/province field as required.
        require_once 'CRM/Core/DAO/StateProvince.php';
        $config = CRM_Core_Config::singleton( );
        $stateProvince = new CRM_Core_DAO_StateProvince( );
        $stateProvince->country_id = CRM_Utils_Array::value( "billing_country_id-{$form->_bltID}", $form->_submitValues );
        
        if ( $stateProvince->count( ) > 0 ) {
            // check that the state/province data is not excluded by a
            // limitation in the localisation settings.
            require_once 'CRM/Core/PseudoConstant.php';
            $countryIsoCodes = CRM_Core_PseudoConstant::countryIsoCode( );
            $limitCodes      = $config->provinceLimit( );
            $limitIds        = array( );
            foreach ( $limitCodes as $code ) {
                $limitIds = array_merge( $limitIds, array_keys( $countryIsoCodes, $code ) );
            }
            
            $limitCountryId = CRM_Utils_Array::value( "billing_country_id-{$form->_bltID}", $form->_submitValues );
            if ( $limitCountryId && in_array( $limitCountryId, $limitIds ) ) {
                return true;    
            }
            return false;
        } 
        return false;    
    }
  }
