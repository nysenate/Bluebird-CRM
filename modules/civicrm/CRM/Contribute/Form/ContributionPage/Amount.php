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
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Contribute_Form_ContributionPage_Amount extends CRM_Contribute_Form_ContributionPage 
{
    /**
     * contribution amount block.
     *
     * @var array
     * @access protected
     */
    protected $_amountBlock = array( );
    
    /** 
     * Constants for number of options for data types of multiple option. 
     */ 
    const NUM_OPTION = 11;
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        require_once 'CRM/Utils/Money.php';

        // do u want to allow a free form text field for amount 
        $this->addElement('checkbox', 'is_allow_other_amount', ts('Allow other amounts' ), null, array( 'onclick' => "minMax(this);showHideAmountBlock( this, 'is_allow_other_amount' );" ) );  
        $this->add('text', 'min_amount', ts('Minimum Amount'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        $this->addRule('min_amount', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');

        $this->add('text', 'max_amount', ts('Maximum Amount'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        $this->addRule('max_amount', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

        $default = array( );
        for ( $i = 1; $i <= self::NUM_OPTION; $i++ ) {
            // label 
            $this->add('text', "label[$i]", ts('Label'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue', 'label')); 
 
            // value 
            $this->add('text', "value[$i]", ts('Value'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue', 'value')); 
            $this->addRule("value[$i]", ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

            // default
            $default[] = $this->createElement('radio', null, null, null, $i); 
        }

        $this->addGroup( $default, 'default' );
        
        $this->addElement('checkbox', 'amount_block_is_active', ts('Contribution Amounts section enabled'), null, array( 'onclick' => "showHideAmountBlock( this, 'amount_block_is_active' );" ) );

        $this->addElement('checkbox', 'is_monetary', ts('Execute real-time monetary transactions') );
        
        $paymentProcessor =& CRM_Core_PseudoConstant::paymentProcessor( );
        $recurringPaymentProcessor = array( );

        if ( !empty( $paymentProcessor ) ) {
            $paymentProcessorIds = implode( ',', array_keys( $paymentProcessor ) );
            $query = "
SELECT id
  FROM civicrm_payment_processor
 WHERE id IN ({$paymentProcessorIds})
   AND is_recur = 1";
            $dao =& CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch( ) ) {
                $recurringPaymentProcessor[] = $dao->id;
            } 
        }
        $this->assign( 'recurringPaymentProcessor', $recurringPaymentProcessor );
        if ( count($paymentProcessor) ) {
            $this->assign('paymentProcessor',$paymentProcessor);
        }
        $this->add( 'select', 'payment_processor_id', ts( 'Payment Processor' ),
                    array(''=>ts( '- select -' )) + $paymentProcessor, null, array( 'onchange' => "showRecurring( this.value );" ) );
        
        require_once 'CRM/Contribute/BAO/ContributionPage.php';
        
        //check if selected payment processor supports recurring payment
        if ( !empty( $recurringPaymentProcessor ) ) {
            $this->addElement( 'checkbox', 'is_recur', ts('Recurring contributions'), null, 
                               array('onclick' => "showHideByValue('is_recur',true,'recurFields','table-row','radio',false); showRecurInterval( );") );
            require_once 'CRM/Core/OptionGroup.php';
            $this->addCheckBox( 'recur_frequency_unit', ts('Supported recurring units'), 
                                CRM_Core_OptionGroup::values( 'recur_frequency_units', false, false, false, null, 'name' ),
                                null, null, null, null,
                                array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>' ) );
            $this->addElement('checkbox', 'is_recur_interval', ts('Support recurring intervals') );
        }
        
        // add pay later options
        $this->addElement('checkbox', 'is_pay_later', ts( 'Pay later option' ),
                          null, array( 'onclick' => "payLater(this);" ) );
        $this->addElement('textarea', 'pay_later_text', ts( 'Pay later label' ),  
                          CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_ContributionPage', 'pay_later_text' ),
                          false );
        $this->addElement('textarea', 'pay_later_receipt', ts( 'Pay later instructions' ),  
                          CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_ContributionPage', 'pay_later_receipt' ),
                          false );
        // add price set fields
        require_once 'CRM/Price/BAO/Set.php';
        $price = CRM_Price_BAO_Set::getAssoc( false, 'CiviContribute');
        if (CRM_Utils_System::isNull($price)) {
            $this->assign('price', false );
        } else {
            $this->assign('price', true );
        }
        $this->add('select', 'price_set_id', ts( 'Price Set' ),
                   array( '' => ts( '- none -' )) + $price,
                   null, array('onchange' => "showHideAmountBlock( this.value, 'price_set_id' );")
                   );
        //CiviPledge fields.
        $config = CRM_Core_Config::singleton( );
        if ( in_array('CiviPledge', $config->enableComponents) ) {
            $this->assign('civiPledge', true );
            require_once 'CRM/Core/OptionGroup.php';
            $this->addElement( 'checkbox', 'is_pledge_active', ts('Pledges') , 
                               null, array('onclick' => "showHideAmountBlock( this, 'is_pledge_active' ); return showHideByValue('is_pledge_active',true,'pledgeFields','table-row','radio',false);") );
            $this->addCheckBox( 'pledge_frequency_unit', ts( 'Supported pledge frequencies' ), 
                                CRM_Core_OptionGroup::values( 'recur_frequency_units', false, false, false, null, 'name' ),
                                null, null, null, null,
                                array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>' ));
            $this->addElement( 'checkbox', 'is_pledge_interval', ts('Allow frequency intervals') );
            $this->addElement( 'text', 'initial_reminder_day', ts('Send payment reminder'), array('size'=>3) );
            $this->addElement( 'text', 'max_reminders', ts('Send up to'), array('size'=>3) );
            $this->addElement( 'text', 'additional_reminder_day', ts('Send additional reminders'), array('size'=>3) );
        }
        
        //add currency element.
        $this->addCurrency( 'currency', ts( 'Currency' ) );
        
        $this->addFormRule( array( 'CRM_Contribute_Form_ContributionPage_Amount', 'formRule' ), $this );
        
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
        
        $title = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'title' );
        CRM_Utils_System::setTitle(ts('Contribution Amounts (%1)', array(1 => $title)));
        
        if ( !CRM_Utils_Array::value( 'pay_later_text', $defaults ) ) {
            $defaults['pay_later_text'] = ts( 'I will send payment by check' );
        }
        
        if ( CRM_Utils_Array::value( 'amount_block_is_active', $defaults ) ) {
            
            // don't allow other amount option when price set present.
            $this->assign( 'priceSetID', $this->_priceSetID );
            if ( $this->_priceSetID ) return $defaults;
            
            require_once 'CRM/Core/OptionGroup.php'; 
            CRM_Core_OptionGroup::getAssoc( "civicrm_contribution_page.amount.{$this->_id}", $this->_amountBlock );
            $hasAmountBlock = false;
            if ( !empty( $this->_amountBlock ) ) {
                $hasAmountBlock = true;
                $defaults = array_merge( $defaults, $this->_amountBlock );
            }
                        
            if ( CRM_Utils_Array::value( 'value', $defaults ) && is_array( $defaults['value'] ) ) { 
                if ( CRM_Utils_Array::value( 'default_amount_id', $defaults ) && 
                     CRM_Utils_Array::value( 'amount_id', $defaults ) && 
                     is_array( $defaults['amount_id'] ) ) {
                    foreach ( $defaults['value'] as $i => $v ) {
                        if ( $defaults['amount_id'][$i] == $defaults['default_amount_id'] ) {
                            $defaults['default'] = $i;
                            break;
                        }
                    }
                }
                
                // CRM-4038: fix value display
                foreach ($defaults['value'] as &$amount) {
                    $amount = trim(CRM_Utils_Money::format($amount, ' '));
                }
            }
        }
        
        // fix the display of the monetary value, CRM-4038 
        require_once 'CRM/Utils/Money.php';
        if (isset($defaults['min_amount'])) {
            $defaults['min_amount'] = CRM_Utils_Money::format($defaults['min_amount'], null, '%a');
        }
        if (isset($defaults['max_amount'])) {
            $defaults['max_amount'] = CRM_Utils_Money::format($defaults['max_amount'], null, '%a');
        }
        
        return $defaults;
    }
    
    /**  
     * global form rule  
     *  
     * @param array $fields  the input form values  
     * @param array $files   the uploaded files if any  
     * @param array $options additional user data  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * @static  
     */  
    static function formRule( $fields, $files, $self ) 
    {  
        $errors = array( );

        //as for separate membership payment we has to have
        //contribution amount section enabled, hence to disable it need to
        //check if separate membership payment enabled, 
        //if so disable first separate membership payment option  
        //then disable contribution amount section. CRM-3801,
        
        require_once 'CRM/Member/DAO/MembershipBlock.php';
        $membershipBlock = new CRM_Member_DAO_MembershipBlock( );
        $membershipBlock->entity_table = 'civicrm_contribution_page';
        $membershipBlock->entity_id = $self->_id;
        $membershipBlock->is_active = 1;
        $hasMembershipBlk = false;
        if ( $membershipBlock->find( true ) ) {
            if ( CRM_Utils_Array::value('amount_block_is_active', $fields) &&
                 ($setID = CRM_Price_BAO_Set::getFor('civicrm_contribution_page',  $self->_id)) ) {
                $extends = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Set', $setID, 'extends' );
                if ( $extends && $extends == CRM_Core_Component::getComponentID( 'CiviMember' ) ) {
                    $errors['amount_block_is_active'] = ts( 'You cannot use a Membership Price Set when the Contribution Amounts section is enabled. Click the Memberships tab above, and select your Membership Price Set on that form. Membership Price Sets may include additional fields for non-membership options that require an additional fee (e.g. magazine subscription) or an additional voluntary contribution.' );
                    return $errors;
                }
            }
            $hasMembershipBlk = true;
            if ( $membershipBlock->is_separate_payment && !$fields['amount_block_is_active'] ) {
                $errors['amount_block_is_active'] = ts( 'To disable Contribution Amounts section you need to first disable Separate Membership Payment option from Membership Settings.' );
            }
        }

        $minAmount = CRM_Utils_Array::value( 'min_amount', $fields );
        $maxAmount = CRM_Utils_Array::value( 'max_amount', $fields );
        if ( ! empty( $minAmount) && ! empty( $maxAmount ) ) {
            $minAmount = CRM_Utils_Rule::cleanMoney( $minAmount );
            $maxAmount = CRM_Utils_Rule::cleanMoney( $maxAmount );
            if ( (float ) $minAmount > (float ) $maxAmount ) {
                $errors['min_amount'] = ts( 'Minimum Amount should be less than Maximum Amount' );
            }
        }
        
        if ( isset( $fields['is_pay_later'] ) ) {
            if ( empty( $fields['pay_later_text'] ) ) {
                $errors['pay_later_text'] = ts( 'Please enter the text for the \'pay later\' checkbox displayed on the contribution form.' );
            }
            if ( empty( $fields['pay_later_receipt'] ) ) {
                $errors['pay_later_receipt'] = ts( 'Please enter the instructions to be sent to the contributor when they choose to \'pay later\'.' );
            }
        }      
        
        // don't allow price set w/ membership signup, CRM-5095
        if ( $priceSetId = CRM_Utils_Array::value( 'price_set_id', $fields ) ) {
            // don't allow price set w/ membership.
            if ( $hasMembershipBlk ) {
                $errors['price_set_id'] = ts( 'You cannot enable both a Contribution Price Set and Membership Signup on the same online contribution page.' );  
            }
        } else {
            if ( isset( $fields['is_recur'] ) ) {
                if ( empty( $fields['recur_frequency_unit'] ) ) {
                    $errors['recur_frequency_unit'] = ts( 'At least one recurring frequency option needs to be checked.' );
                }
            }     
            
            // validation for pledge fields.
            if ( CRM_Utils_array::value( 'is_pledge_active', $fields ) ) {
                if ( empty( $fields['pledge_frequency_unit'] ) ) {
                    $errors['pledge_frequency_unit'] = ts( 'At least one pledge frequency option needs to be checked.' );
                }
                if ( CRM_Utils_array::value( 'is_recur', $fields ) ) {
                    $errors['is_recur'] = ts( 'You cannot enable both Recurring Contributions AND Pledges on the same online contribution page.' ); 
                }
            }
            
            // If Contribution amount section is enabled, then 
            // Allow other amounts must be enabeld OR the Fixed Contribution
            // Contribution options must contain at least one set of values.
            if ( CRM_Utils_Array::value( 'amount_block_is_active', $fields ) ) {
                if ( !CRM_Utils_Array::value( 'is_allow_other_amount', $fields ) &&
                     !$priceSetId ) {
                    //get the values of amount block
                    $values  = CRM_Utils_Array::value( 'value'  , $fields );
                    $isSetRow = false;
                    for ( $i = 1; $i < self::NUM_OPTION; $i++ ) {
                        if ( ( isset( $values[$i] ) && ( strlen( trim( $values[$i] ) ) > 0 ) ) ) { 
                            $isSetRow = true;
                        }
                    }
                    if ( !$isSetRow ) {
                        $errors['amount_block_is_active'] = 
                            ts ( 'If you want to enable the \'Contribution Amounts section\', you need to either \'Allow Other Amounts\' and/or enter at least one row in the \'Fixed Contribution Amounts\' table.' );
                    }
                }
            }
        }

        if ( CRM_Utils_Array::value( 'is_recur_interval', $fields ) ) {
            $paymentProcessorType = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_PaymentProcessor', 
                                                                 $fields['payment_processor_id'], 
                                                                 'payment_processor_type' );
            if ( $paymentProcessorType == 'Google_Checkout' ) {
                $errors['is_recur_interval'] = ts( 'Google Checkout does not support recurring intervals' );
            }
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
       
        if ( CRM_Utils_Array::value( 'payment_processor_id', $params) == 
             CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_PaymentProcessor', 'AuthNet', 
                                          'id', 'payment_processor_type') ) {
            CRM_Core_Session::setStatus( ts( ' Please note that the Authorize.net payment processor only allows recurring contributions and auto-renew memberships with payment intervals from 7-365 days or 1-12 months (i.e. not greater than 1 year).' ) );
        }
        // check for price set.
        $priceSetID = CRM_Utils_Array::value( 'price_set_id', $params );
        
        // get required fields.
        $fields = array( 'id'                     => $this->_id, 
                         'is_recur'               => false,
                         'min_amount'             => "null",
                         'max_amount'             => "null",
                         'is_monetary'            => false,
                         'is_pay_later'           => false,
                         'is_recur_interval'      => false,
                         'recur_frequency_unit'   => "null",
                         'default_amount_id'      => "null",
                         'is_allow_other_amount'  => false,
                         'amount_block_is_active' => false
                         );
        $resetFields = array( );
        if ( $priceSetID ) {
            $resetFields = array( 'min_amount', 'max_amount', 'is_allow_other_amount' );
        }
        
        if ( !CRM_Utils_Array::value( 'is_recur', $params ) ) {
            $resetFields = array_merge( $resetFields, array( 'is_recur_interval', 'recur_frequency_unit' ) );
        }

        foreach ( $fields as $field => $defaultVal ) {
            $val = CRM_Utils_Array::value( $field, $params, $defaultVal );
            if ( in_array( $field, $resetFields ) ) $val = $defaultVal;
            
            if ( in_array( $field, array( 'min_amount', 'max_amount' ) ) ) {
                $val = CRM_Utils_Rule::cleanMoney( $val );
            }
             
            $params[$field] = $val;
        }
        
        if ( $params['is_recur'] ) {
            require_once 'CRM/Core/BAO/CustomOption.php';
            $params['recur_frequency_unit'] = 
                implode( CRM_Core_DAO::VALUE_SEPARATOR,
                         array_keys( $params['recur_frequency_unit'] ) );
            $params['is_recur_interval'] = CRM_Utils_Array::value( 'is_recur_interval', $params ,false );
        }
        
        require_once 'CRM/Contribute/BAO/ContributionPage.php';
        $contributionPage   = CRM_Contribute_BAO_ContributionPage::create( $params );
        $contributionPageID = $contributionPage->id;
        
        // prepare for data cleanup.
        $deleteAmountBlk = $deletePledgeBlk = $deletePriceSet = false;
        if ( $this->_priceSetID    )         $deletePriceSet  = true;
        if ( $this->_pledgeBlockID )         $deletePledgeBlk = true;
        if ( !empty( $this->_amountBlock ) ) $deleteAmountBlk = true;
        
        if ( $contributionPageID ) {
            require_once 'CRM/Price/BAO/Set.php';
            require_once 'CRM/Core/OptionGroup.php';
            require_once 'CRM/Pledge/BAO/PledgeBlock.php';
            
            if ( CRM_Utils_Array::value('amount_block_is_active', $params ) ) {
                // handle price set.
                if ( $priceSetID ) {
                    // add/update price set.
                    $deletePriceSet = false;
                    CRM_Price_BAO_Set::addTo( 'civicrm_contribution_page', $contributionPageID, $priceSetID );
                } else {
                    
                    // process contribution amount block
                    $deleteAmountBlk = false; 
                    
                    $labels  = CRM_Utils_Array::value( 'label', $params );
                    $values  = CRM_Utils_Array::value( 'value', $params );
                    $default = CRM_Utils_Array::value( 'default', $params ); 
                    
                    $options = array( );
                    for ( $i = 1; $i < self::NUM_OPTION; $i++ ) {
                        if ( isset( $values[$i] ) &&
                             ( strlen( trim( $values[$i] ) ) > 0 ) ) {
                            $options[] = array( 'label'      => trim( $labels[$i] ),
                                                'value'      => CRM_Utils_Rule::cleanMoney( trim( $values[$i] ) ),
                                                'weight'     => $i,
                                                'is_active'  => 1,
                                                'is_default' => $default == $i );
                        }
                    }
                    CRM_Core_OptionGroup::createAssoc( "civicrm_contribution_page.amount.{$contributionPageID}",
                                                       $options,
                                                       $params['default_amount_id'] );
                    if ( $params['default_amount_id'] ) {
                        CRM_Core_DAO::setFieldValue( 'CRM_Contribute_DAO_ContributionPage', 
                                                     $contributionPageID, 'default_amount_id', 
                                                     $params['default_amount_id'] );
                    }
                    
                    if ( CRM_Utils_Array::value('is_pledge_active', $params ) ) {
                        $deletePledgeBlk = false; 
                        $pledgeBlockParams = array( 'entity_id'    => $contributionPageID,
                                                    'entity_table' => ts( 'civicrm_contribution_page' ) );
                        if ( $this->_pledgeBlockID ) {
                            $pledgeBlockParams['id'] = $this->_pledgeBlockID;
                        }
                        $pledgeBlock = array( 'pledge_frequency_unit', 'max_reminders', 
                                              'initial_reminder_day', 'additional_reminder_day' );
                        foreach ( $pledgeBlock  as $key ) {
                            $pledgeBlockParams[$key] = CRM_Utils_Array::value( $key, $params );    
                        }
                        $pledgeBlockParams['is_pledge_interval'] = CRM_Utils_Array::value( 'is_pledge_interval', 
                                                                                           $params, false );
                        // create pledge block.
                        require_once 'CRM/Pledge/BAO/PledgeBlock.php';
                        CRM_Pledge_BAO_PledgeBlock::create( $pledgeBlockParams );
                    }
                }
            }
            
            // delete pledge block.
            if ( $deletePledgeBlk ) {
                CRM_Pledge_BAO_PledgeBlock::deletePledgeBlock( $this->_pledgeBlockID );
            }
            
            // delete previous price set.
            if ( $deletePriceSet ) {
                CRM_Price_BAO_Set::removeFrom( 'civicrm_contribution_page', $contributionPageID ); 
            }
            
            // delete amount block.
            if ( $deleteAmountBlk ) {
                CRM_Core_OptionGroup::deleteAssoc( "civicrm_contribution_page.amount.{$contributionPageID}" );
            }
        }
        parent::endPostProcess( );
    }
    
    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) 
    {
        return ts( 'Amounts' );
    }
    
}

