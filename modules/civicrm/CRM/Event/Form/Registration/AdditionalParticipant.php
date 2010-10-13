<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Event/Form/Registration.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class CRM_Event_Form_Registration_AdditionalParticipant extends CRM_Event_Form_Registration
{
    /**
     * The defaults involved in this page
     *
     */
    public $_defaults = array( );
    
    /**
     * pre-registered additional participant id.
     *
     */
    public $additionalParticipantId = null;
    
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    {
        parent::preProcess( );

        $this->_lineItem = $this->get( 'lineItem' );
        $participantNo = substr( $this->_name, 12 );
        
        //lets process in-queue participants.
        if ( $this->_participantId && $this->_additionalParticipantIds ) {
            $this->_additionalParticipantId = CRM_Utils_Array::value( $participantNo, $this->_additionalParticipantIds );
        }
        
        $participantCnt = $participantNo + 1;
        $this->assign( 'formId', $participantNo );
        $this->_params = array( );
        $this->_params = $this->get( 'params' );
        $participantTot = $this->_params[0]['additional_participants'] + 1; 
        $skipCount = count( array_keys( $this->_params, "skip" ) );
        if( $skipCount ) {
            $this->assign('skipCount', $skipCount );
        }
        CRM_Utils_System::setTitle( ts('Register Participant %1 of %2', array( 1 => $participantCnt, 2 => $participantTot ) ) );
        
        //CRM-4320, hack to check last participant.
        $this->_lastParticipant = false;
        if ( $participantTot == $participantCnt ) {
            $this->_lastParticipant = true; 
        }
    }
   
    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $defaults = array( );
        $discountId = null;
        //fix for CRM-3088, default value for discount set.      
        if ( ! empty( $this->_values['discount'] ) ){
            require_once 'CRM/Core/BAO/Discount.php';
            $discountId = CRM_Core_BAO_Discount::findSet( $this->_eventId, 'civicrm_event' );
            if ( $discountId && CRM_Utils_Array::value( 'default_discount_fee_id', $this->_values['event'] ) ) {
                $discountKey = CRM_Core_DAO::getFieldValue( "CRM_Core_DAO_OptionValue", $this->_values['event']['default_discount_fee_id']
                                                            , 'weight', 'id' );
                $defaults['amount'] = key( array_slice( $this->_values['discount'][$discountId], $discountKey-1, $discountKey, true) );
            }
        }
        if ( $this->_priceSetId ) {
            foreach( $this->_priceSet['fields'] as $key => $val ) {
                foreach ( $val['options'] as $keys => $values ) {
                    if ( $values['is_default'] ) {
                        if ( $val['html_type'] == 'CheckBox') {
                            $defaults["price_{$key}"][$keys] = 1;
                        } else {
                            $defaults["price_{$key}"] = $keys;
                        }
                    }
                }
            }
        }
        
        //CRM-4320, setdefault additional participant values.
        if ( $this->_allowConfirmation && $this->_additionalParticipantId ) {
            require_once 'CRM/Event/Form/EventFees.php';
            //hack to get set default from eventFees.php
            $this->_discountId = $discountId;
            $this->_pId = $this->_additionalParticipantId;
            $this->_contactId = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Participant', $this->_additionalParticipantId, 'contact_id' );
            $participantDefaults = CRM_Event_Form_EventFees::setDefaultValues( $this ) ;
            $participantDefaults = array_merge( $this->_defaults, $participantDefaults );
            // use primary email address if billing email address is empty
            if ( empty( $this->_defaults["email-{$this->_bltID}"] ) &&
                 !empty( $this->_defaults["email-Primary"] ) ) {
                $participantDefaults["email-{$this->_bltID}"] = $this->_defaults["email-Primary"];
            }
            $defaults = array_merge( $defaults, $participantDefaults );
        }
        
        $defaults = array_merge( $this->_defaults, $defaults );
        return $defaults;  
    }  
    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( ) 
    {  
        $config = CRM_Core_Config::singleton( );
        $button = substr( $this->controller->getButtonName(), -4 );
        
        $this->add('hidden','scriptFee',null);
        $this->add('hidden','scriptArray',null);
        
        if ( $this->_values['event']['is_monetary'] ) {
            require_once 'CRM/Event/Form/Registration/Register.php';
            CRM_Event_Form_Registration_Register::buildAmount( $this );
        }
        $first_name = $last_name = null;
        foreach ( array( 'pre', 'post' ) as $keys ) {
            if ( isset ( $this->_values['additional_custom_'.$keys.'_id'] ) ) {
                $this->buildCustom( $this->_values['additional_custom_'.$keys.'_id'] , 'additionalCustom'.ucfirst($keys) , true );
                $$keys = CRM_Core_BAO_UFGroup::getFields($this->_values['additional_custom_'.$keys.'_id']);
            }
            foreach ( array( 'first_name', 'last_name' ) as $name ) {
                if( CRM_Utils_Array::value( $name, $$keys ) &&
                    CRM_Utils_Array::value( 'is_required', CRM_Utils_Array::value( $name, $$keys ) ) ) {
                    $$name = 1;
                }    
            }
        }
        
        $required = ( $button == 'skip' ||
                      $this->_values['event']['allow_same_participant_emails']  == 1 &&
                      ( $first_name && $last_name ) ) ? false : true;
        
        $this->add( 'text',
                    "email-{$this->_bltID}",
                    ts( 'Email Address' ),
                    array( 'size' => 30, 'maxlength' => 60 ),
                    $required );
        $this->addRule( "email-{$this->_bltID}", ts('Email is not valid.'), 'email' );
        //add buttons
        $js = null;
        if ( $this->isLastParticipant( true ) && !CRM_Utils_Array::value('is_monetary', $this->_values['event']) ) {
            $js = array( 'onclick' => "return submitOnce(this,'" . $this->_name . "','" . ts('Processing') ."');" );  
        }

        //handle case where user might sart with waiting by group
        //registration and skip some people and now group fit to
        //become registered so need to take payment from user.
        //this case only occurs at dynamic waiting status, CRM-4320
        $statusMessage = null;
        $allowToProceed = true;
        $includeSkipButton = true;
        $this->_resetAllowWaitlist = false;
        if ( $this->_lastParticipant && 
             !$this->_allowConfirmation && 
             CRM_Utils_Array::value( 'bypass_payment', $this->_params[0] ) ) {
            require_once 'CRM/Event/BAO/Participant.php';
            $spaces = CRM_Event_BAO_Participant::eventFull( $this->_values['event']['id'], true );
            $processedCnt = 0;
            //need to check current participant present in params.
            $currentParticipantNum = substr( $this->_name, 12 );
            foreach ( $this->_params as $key => $value ) {
                if ( $value == 'skip' || $key == $currentParticipantNum ) {
                    continue;
                }
                $processedCnt++;
            }
            
            //we might did reset allow waiting in case of dynamic calculation 
            if ( CRM_Utils_Array::value( 'bypass_payment', $this->_params[0] ) &&
                 is_numeric( $spaces ) && 
                 CRM_Utils_Array::value( 'additional_participants', $this->_params[0] ) >= $spaces ) {
                $this->_allowWaitlist = true;
                $this->set( 'allowWaitlist', true );
            }
            
            //lets allow to become a part of runtime waiting list, if primary selected pay later.
            $realPayLater = false;
            if ( CRM_Utils_Array::value( 'is_monetary', $this->_values['event'] ) &&
                 CRM_Utils_Array::value( 'is_pay_later', $this->_values['event'] ) ) {
                $realPayLater = CRM_Utils_Array::value( 'is_pay_later', $this->_params[0] );
            }
            
            //truly spaces are greater than required.
            if ( is_numeric( $spaces ) && $spaces >= ($processedCnt+1) ) {
                if ( CRM_Utils_Array::value( 'amount', $this->_params[0], 0 ) == 0 || $this->_requireApproval ) {
                    $this->_allowWaitlist = false;
                    $this->set( 'allowWaitlist', $this->_allowWaitlist );
                    if ( $this->_requireApproval ) {
                        $statusMessage = ts( "It looks like you are now registering a group of %1 participants. The event has %2 available spaces (you will not be wait listed). Registration for this event requires approval. You will receive an email once your registration has been reviewed.", array( 1 => ++$processedCnt, 2 =>  $spaces ) );
                    } else {
                        $statusMessage = ts( "It looks like you are now registering a group of %1 participants. The event has %2 available spaces (you will not be wait listed).", array( 1 => ++$processedCnt, 2 =>  $spaces ) );
                    }
                } else {
                    $statusMessage = ts( "It looks like you are now registering a group of %1 participants. The event has %2 available spaces (you will not be wait listed). Please go back to the main registration page and reduce the number of additional people. You will also need to complete payment information.", array( 1 => ++$processedCnt, 2 =>  $spaces ) );
                    $allowToProceed = false;
                }
                CRM_Core_Session::setstatus( $status );
            } else if ( ( $processedCnt == $spaces ) ) { 
                if ( CRM_Utils_Array::value( 'amount', $this->_params[0], 0 ) == 0 
                     || $realPayLater || $this->_requireApproval ) {
                    $this->_resetAllowWaitlist = true;
                    if ( $this->_requireApproval ) {
                        $statusMessage = ts( "If you skip this participant there will be enough spaces in the event for your group (you will not be wait listed). Registration for this event requires approval. You will receive an email once your registration has been reviewed." );
                    } else {
                        $statusMessage = ts( "If you skip this participant there will be enough spaces in the event for your group (you will not be wait listed)." );
                    }                    
                } else {
                    //hey there is enough space and we require payment.
                    $statusMessage = ts( "If you skip this participant there will be enough spaces in the event for your group (you will not be wait listed). Please go back to the main registration page and reduce the number of additional people. You will also need to complete payment information." );
                    $includeSkipButton = false;
                }
            }
        }
        $this->assign( 'statusMessage', $statusMessage );
        
        $buttons = array( array ( 'type'      => 'back',
                                  'name'      => ts('<< Go Back'),
                                  'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp',
                                  )
                          );
        
        //CRM-4320
        if ( $allowToProceed ) {
            $buttons = array_merge( $buttons, array( array ( 'type'      => 'next',
                                                             'name'      => ts('Continue >>'),
                                                             'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                                             'isDefault' => true,
                                                             'js'        => $js 
                                                             )
                                                     )
                                    );
            if ( $includeSkipButton ) {
                $buttons = array_merge( $buttons,  array( array ( 'type'       => 'next',
                                                                  'name'       => ts('Skip Participant >>|'),
                                                                  'subName'    => 'skip' 
                                                                  )
                                                          )
                                        );
            }
        }
        $this->addButtons( $buttons );
        $this->addFormRule( array( 'CRM_Event_Form_Registration_AdditionalParticipant', 'formRule' ), $this );
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
    static function formRule( $fields, $files, $self) 
    {
        $errors = array( );
        //get the button name.
        $button = substr( $self->controller->getButtonName(), -4 );
        if ( $button != 'skip' ) {
            //Additional Participant can also register for an event only once 
            require_once 'CRM/Event/Form/Registration/Register.php';
            $isRegistered =  CRM_Event_Form_Registration_Register::checkRegistration( $fields, $self, true );
            
            if ( $isRegistered ) {
                $errors["email-{$self->_bltID}"] = ts( 'A person with this email address is already registered for this event.');
            } 
            
            //get the complete params.
            $params = $self->get('params');
            
            //take the participant instance.
            $addParticipantNum = substr( $self->_name, 12 );
            if ( is_array( $params ) &&
                 $self->_values['event']['allow_same_participant_emails'] != 1 ) {
                foreach ( $params as $key => $value ) {
                    if ( ( $value["email-{$self->_bltID}"] == $fields["email-{$self->_bltID}"] ) &&
                         $key != $addParticipantNum  ) {
                        $errors["email-{$self->_bltID}"] = ts( 'The email address must be unique for each participant.' );
                        break;
                    }
                }
            }

            //check for atleast one pricefields should be selected
            if ( CRM_Utils_Array::value( 'priceSetId', $fields ) ) {
                $priceField = new CRM_Price_DAO_Field( );
                $priceField->price_set_id = $fields['priceSetId'];
                $priceField->find( );
                
                $check = array( );
                
                while ( $priceField->fetch( ) ) {
                    if ( ! empty( $fields["price_{$priceField->id}"] ) ) {
                        $check[] = $priceField->id; 
                    }
                }
                
                if ( empty( $check ) ) {
                    $errors['_qf_default'] = ts( "Select at least one option from Event Fee(s)." );
                }
            }
        }
        
        return $errors; 
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        //get the button name.
        $button = substr( $this->controller->getButtonName(), -4 );
        
        //take the participant instance.
        $addParticipantNum = substr( $this->_name, 12 );
        if ( $button == 'skip' ) {
            //hack for free/zero amount event.
            if ( $this->_resetAllowWaitlist ) {
                $this->_allowWaitlist = false;
                $this->set( 'allowWaitlist', false );
                if ( $this->_requireApproval ) {
                    $status = ts( "You have skipped last participant and which result into event having enough spaces, but your registration require approval, Once your registration has been reviewed, you will receive an email with a link to a web page where you can complete the registration process." );  
                } else {
                    $status = ts( "You have skipped last participant and which result into event having enough spaces, hence your group become as register participants though you selected on wait list." );
                }
                CRM_Core_Session::setStatus( $status );
            }
            
            $this->_params[$addParticipantNum] = 'skip';
            if ( isset( $this->_lineItem ) ) {
                $this->_lineItem[$addParticipantNum] = 'skip';
            }
        } else {
            $params = $this->controller->exportValues( $this->_name );
            
            $config = CRM_Core_Config::singleton( );
            $params['currencyID'] = $config->defaultCurrency;            
            
            if ( $this->_values['event']['is_monetary'] ) {

                //added for discount
                require_once 'CRM/Core/BAO/Discount.php';
                $discountId = CRM_Core_BAO_Discount::findSet( $this->_eventId, 'civicrm_event' );
                
                if ( ! empty( $this->_values['discount'][$discountId] ) ) {
                    $params['discount_id']  = $discountId;
                    $params['amount_level'] = $this->_values['discount'][$discountId][$params['amount']]['label'];
                    $params['amount']       = $this->_values['discount'][$discountId][$params['amount']]['value'];
                    
                } else if ( empty( $params['priceSetId'] ) ) {
                    $params['amount_level'] = $this->_values['fee'][$params['amount']]['label'];
                    $params['amount']       = $this->_values['fee'][$params['amount']]['value'];

                } else {
                    $lineItem = array( ); 
                    require_once 'CRM/Price/BAO/Set.php';
                    CRM_Price_BAO_Set::processAmount( $this->_values['fee']['fields'], 
                                                      $params, $lineItem );
                    //build the line item..
                    if ( array_key_exists( $addParticipantNum, $this->_lineItem ) ) {
                        $this->_lineItem[$addParticipantNum] = $lineItem;
                    } else {
                        $this->_lineItem[] = $lineItem;
                    }
                }
            }

            if ( ! CRM_Utils_Array::value( 'participant_role_id', $params ) && $this->_values['event']['default_role_id'] ) {
                $params['participant_role_id'] = $this->_values['event']['default_role_id'];
            }
            
            if ( CRM_Utils_Array::value( 'is_pay_later', $this->_params[0] ) ) {
                $params['is_pay_later']  = 1;
            }
            
            //carry additional participant id, contact id if pre-registered.
            if ( $this->_allowConfirmation && $this->_additionalParticipantId ) {
                $params['contact_id']     = $this->_contactId;
                $params['participant_id'] = $this->_additionalParticipantId;
            }
            
            //build the params array.
            if ( array_key_exists( $addParticipantNum, $this->_params ) ) {
                $this->_params[$addParticipantNum] = $params;
            } else {
                $this->_params[] = $params; 
            }
        }
        //finally set the params.
        $this->set( 'params', $this->_params );
        //set the line item.
        if ( $this->_lineItem ) {
            $this->set( 'lineItem', $this->_lineItem );
        }
        
        $participantNo = count( $this->_params );
        if ( $button != 'skip' ) {
            require_once "CRM/Core/Session.php";
            $statusMsg = ts('Registration information for participant %1 has been saved.', array( 1 => $participantNo )); 
            CRM_Core_Session::setStatus( "{$statusMsg}" );
        }
        
        //to check whether call processRegistration() 
        if ( !$this->_values['event']['is_monetary'] 
             && CRM_Utils_Array::value( 'additional_participants', $this->_params[0] ) 
             && $this->isLastParticipant( ) ) {
            require_once 'CRM/Event/Form/Registration/Register.php';
            CRM_Event_Form_Registration_Register::processRegistration(  $this->_params,  null );
        }
    }
    
    function &getPages( $additionalParticipant )
    {
        $details = array( );
        for ( $i = 1; $i <= $additionalParticipant; $i++ ) {
            $details["Participant_{$i}"] = array( 'className' => 'CRM_Event_Form_Registration_AdditionalParticipant', 
                                                  'title'     => "Register Additional Participant {$i}"
                                                  );
        }
        return $details;
    } 
    
    /**
     * check whether call current participant is last one
     *
     * @return boolean ture on success.
     * @access public
     */
    function isLastParticipant( $isButtonJs = false ) 
    {
        $participant =  $isButtonJs ? $this->_params[0]['additional_participants'] : $this->_params[0]['additional_participants'] + 1;
        if ( count($this->_params) == $participant ) {
            return true;
        }
        return false;
    } 

}
