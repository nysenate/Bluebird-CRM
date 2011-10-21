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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Event/Form/ManageEvent.php';
require_once 'CRM/Event/BAO/Event.php';
require_once 'CRM/Core/BAO/UFGroup.php';
require_once 'CRM/Contact/BAO/ContactType.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class CRM_Event_Form_ManageEvent_Registration extends CRM_Event_Form_ManageEvent
{
    /**
     * what blocks should we show and hide.
     *
     * @var CRM_Core_ShowHideBlocks
     */
    protected $_showHide;

    protected $_profilePostMultiple = array( );
    protected $_profilePostMultipleAdd = array( );
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( )
    {
        $this->_addProfileBottom = CRM_Utils_Array::value( 'addProfileBottom', $_GET, false );
        $this->_profileBottomNum = CRM_Utils_Array::value( 'addProfileNum', $_GET, 0 );
        $this->_addProfileBottomAdd = CRM_Utils_Array::value( 'addProfileBottomAdd', $_GET, false );
        $this->_profileBottomNumAdd = CRM_Utils_Array::value( 'addProfileNumAdd', $_GET, 0 );
        
        parent::preProcess( );
        
        $this->assign('addProfileBottom', $this->_addProfileBottom);
        $this->assign('profileBottomNum', $this->_profileBottomNum);

        $urlParams = "id={$this->_id}&addProfileBottom=1&qfKey={$this->controller->_key}";
        $this->assign( 'addProfileParams', $urlParams );

        if ($addProfileBottom = CRM_Utils_Array::value('custom_post_id_multiple', $_POST) ) {
            foreach( array_keys($addProfileBottom) as $profileNum ) {
                self::buildMultipleProfileBottom($this, $profileNum);
            }
        }
        
        $this->assign('addProfileBottomAdd', $this->_addProfileBottomAdd);
        $this->assign('profileBottomNumAdd', $this->_profileBottomNumAdd);

        $urlParamsAdd = "id={$this->_id}&addProfileBottomAdd=1&qfKey={$this->controller->_key}";
        $this->assign( 'addProfileParamsAdd', $urlParamsAdd );
        
        if ($addProfileBottomAdd = CRM_Utils_Array::value('additional_custom_post_id_multiple', $_POST) ) {
            foreach( array_keys($addProfileBottomAdd) as $profileNum ) {
                self::buildMultipleProfileBottom($this, $profileNum, 'additional_', ts('Profile for Additional Participants'));
            }
        }
    }

    /**
     * This function sets the default values for the form. 
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        if ( $this->_addProfileBottom || $this->_addProfileBottomAdd ) {
            return;
        }
        $eventId = $this->_id;

        $defaults = parent::setDefaultValues( );

        $this->setShowHide( $defaults );
        if ( isset( $eventId ) ) {
            $params = array( 'id' => $eventId );
            CRM_Event_BAO_Event::retrieve( $params, $defaults );
            
            require_once 'CRM/Core/BAO/UFJoin.php';
            $ufJoinParams = array( 'entity_table' => 'civicrm_event',
                                   'module'       => 'CiviEvent',
                                   'entity_id'    => $eventId );

            list( $defaults['custom_pre_id'],
                  $defaults['custom_post'] ) = 
                CRM_Core_BAO_UFJoin::getUFGroupIds( $ufJoinParams );
            
            if ( isset( $defaults['custom_post'] ) && is_numeric($defaults['custom_post'])) {
                $defaults['custom_post_id'] =  $defaults['custom_post']; 
            } else if (!empty($defaults['custom_post'])) {
                $defaults['custom_post_id'] =  $defaults['custom_post'][0];
                unset($defaults['custom_post'][0]);
                $this->_profilePostMultiple = $defaults['custom_post'];
                foreach ( $defaults['custom_post'] as $key => $value){
                    self::buildMultipleProfileBottom($this, $key);
                    $defaults["custom_post_id_multiple[$key]"] = $value;

                }
            }  
            
            $this->assign('profilePostMultiple', CRM_Utils_Array::value('custom_post', $defaults ));

            if ($defaults['is_multiple_registrations']) {
                // CRM-4377: set additional participants’ profiles – set to ‘none’ if explicitly unset (non-active)
                
                $ufJoinAddParams = array( 'entity_table' => 'civicrm_event',
                                          'module'       => 'CiviEvent_Additional',
                                          'entity_id'    => $eventId );
                
                list( $defaults['additional_custom_pre_id'],
                      $defaults['additional_custom_post'] ) = 
                    CRM_Core_BAO_UFJoin::getUFGroupIds( $ufJoinAddParams );
                
                if (isset( $defaults['additional_custom_post'] ) && is_numeric($defaults['additional_custom_post'])) {
                    $defaults['additional_custom_post_id'] = $defaults['additional_custom_post']; 
                } else  if (!empty($defaults['additional_custom_post'])) {
                    $defaults['additional_custom_post_id'] =  $defaults['additional_custom_post'][0];
                    unset($defaults['additional_custom_post'][0]);

                    $this->_profilePostMultipleAdd = $defaults['additional_custom_post'];
                    foreach ( $defaults['additional_custom_post'] as $key => $value){
                        self::buildMultipleProfileBottom($this, $key, 'additional_', ts('Profile for Additional Participants'));
                        $defaults["additional_custom_post_id_multiple[$key]"] = $value;
                    }
                }  
                $this->assign('profilePostMultipleAdd', CRM_Utils_Array::value( 'additional_custom_post', $defaults ));
            }
        } else {
            $defaults['is_email_confirm'] = 0;
        }

        // provide defaults for required fields if empty (and as a 'hint' for approval message field)
        $defaults['registration_link_text'] = CRM_Utils_Array::value('registration_link_text', $defaults, ts('Register Now') );
        $defaults['confirm_title']  = CRM_Utils_Array::value('confirm_title', $defaults, ts('Confirm Your Registration Information') );
        $defaults['thankyou_title'] = CRM_Utils_Array::value('thankyou_title', $defaults, ts('Thank You for Registering') );
        $defaults['approval_req_text'] = CRM_Utils_Array::value('approval_req_text', $defaults, ts( 'Participation in this event requires approval. Submit your registration request here. Once approved, you will receive an email with a link to a web page where you can complete the registration process.' ) ); 
        
        if ( CRM_Utils_Array::value( 'registration_start_date' , $defaults ) ) {
            list( $defaults['registration_start_date'], 
                  $defaults['registration_start_date_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['registration_start_date'], 
                                                                                                 'activityDateTime' );    
        }
        
        if ( CRM_Utils_Array::value( 'registration_end_date' , $defaults ) ) {                                                                                          
            list( $defaults['registration_end_date'], 
                  $defaults['registration_end_date_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['registration_end_date'], 
                                                                                               'activityDateTime' );
        }                

        return $defaults;

    }   
    
    /**
     * Fix what blocks to show/hide based on the default values set
     *
     * @param array   $defaults the array of default values
     * @param boolean $force    should we set show hide based on input defaults
     *
     * @return void
     */
    function setShowHide( &$defaults) 
    {
        require_once 'CRM/Core/ShowHideBlocks.php';
        $this->_showHide = new CRM_Core_ShowHideBlocks( array('registration' => 1 ),
                                                         '') ;
        if ( empty($defaults)) {
            $this->_showHide->addShow( 'registration_screen_show' );
            $this->_showHide->addShow( 'confirm_show' );
            $this->_showHide->addShow( 'mail_show' );
            $this->_showHide->addShow( 'thankyou_show' );
            $this->_showHide->addHide( 'registration' );
            $this->_showHide->addHide( 'registration_screen' );
            $this->_showHide->addHide( 'confirm' );
            $this->_showHide->addHide( 'mail' );
            $this->_showHide->addHide( 'thankyou' );
            $this->_showHide->addHide( 'additional_profile_pre' );
            $this->_showHide->addHide( 'additional_profile_post' );
            $this->_showHide->addHide( 'id-approval-text' );
        } else {
            $this->_showHide->addShow( 'confirm' );
            $this->_showHide->addShow( 'mail' );
            $this->_showHide->addShow( 'thankyou' );
            $this->_showHide->addHide( 'registration_screen_show' );
            $this->_showHide->addHide( 'confirm_show' );            
            $this->_showHide->addHide( 'mail_show' );
            $this->_showHide->addHide( 'thankyou_show' );
            if ( ! $defaults['is_multiple_registrations']) {
                $this->_showHide->addHide( 'additional_profile_pre' );
                $this->_showHide->addHide( 'additional_profile_post' );
            }
            if ( ! CRM_Utils_Array::value( 'requires_approval', $defaults ) ) {
                $this->_showHide->addHide( 'id-approval-text' );
            }
        }
        $this->_showHide->addToTemplate( );
    }

    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 
    public function buildQuickForm( )  
    { 
        if ( $this->_addProfileBottom ) {
            return self::buildMultipleProfileBottom($this, $this->_profileBottomNum);
        }

        if ( $this->_addProfileBottomAdd ) {
            return self::buildMultipleProfileBottom($this, $this->_profileBottomNumAdd, 'additional_', ts('Profile for Additional Participants'));
        }

        $this->applyFilter('__ALL__', 'trim');
        $attributes = CRM_Core_DAO::getAttribute('CRM_Event_DAO_Event');

        $this->addElement( 'checkbox', 
                           'is_online_registration', 
                           ts('Allow Online Registration?'), 
                           null, 
                           array( 'onclick' => "return showHideByValue('is_online_registration', 
                                                                       '', 
                                                                       'registration_blocks', 
                                                                       'block', 
                                                                       'radio', 
                                                                       false );"
                                ) 
                         );
   
        $this->add('text','registration_link_text',ts('Registration Link Text'));

        if (!$this->_isTemplate) {
            $this->addDateTime( 'registration_start_date', ts('Registration Start Date'), false, array( 'formatType' => 'activityDateTime' ) );
            $this->addDateTime( 'registration_end_date', ts('Registration End Date'), false, array( 'formatType' => 'activityDateTime' ) );
        }
     
        $this->addElement('checkbox',
                          'is_multiple_registrations',
                          ts('Register multiple participants?'),
                          null,
                          array('onclick' => "return showHideByValue('is_multiple_registrations', '', 'additional_profile_pre|additional_profile_post', 'table-row', 'radio', false);"));

        require_once 'CRM/Dedupe/BAO/Rule.php';
        $params           = array( 'level'        => 'Fuzzy',
                                   'contact_type' => 'Individual' );
        $dedupeRuleFields = CRM_Dedupe_BAO_Rule::dedupeRuleFields( $params );
        
        foreach ( $dedupeRuleFields as $key => $fields ) {
            $ruleFields[$key] = ucwords( str_replace( '_', ' ', $fields ) );
        }
        $this->addElement( 'checkbox',
                           'allow_same_participant_emails', 
                           ts('Allow multiple registrations from the same email address?'), 
                           null,
                           array( 'onclick' => "return showRuleFields( " . json_encode( $ruleFields ) ." );" ) );
        $this->assign( 'ruleFields', json_encode( $ruleFields ) );

        require_once 'CRM/Event/PseudoConstant.php';
        $participantStatuses =& CRM_Event_PseudoConstant::participantStatus();
        if (in_array('Awaiting approval', $participantStatuses) and in_array('Pending from approval', $participantStatuses) and in_array('Rejected', $participantStatuses)) {
            $this->addElement('checkbox',
                              'requires_approval',
                              ts('Require participant approval?'),
                              null,
                              array('onclick' => "return showHideByValue('requires_approval', '', 'id-approval-text', 'table-row', 'radio', false);"));
            $this->add('textarea', 'approval_req_text',   ts('Approval message'), $attributes['approval_req_text']);
        }

        $this->add('text', 'expiration_time', ts('Pending participant expiration (hours)'));
        $this->addRule('expiration_time', ts('Please enter the number of hours (as an integer).'), 'integer');

        self::buildRegistrationBlock( $this );
        self::buildConfirmationBlock( $this );
        self::buildMailBlock( $this );
        self::buildThankYouBlock( $this );

        parent::buildQuickForm();
    }
    
    /**
     * Function to build Registration Block  
     * 
     * @param int $pageId 
     * @static
     */
    function buildRegistrationBlock( &$form ) 
    {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Event_DAO_Event');
        $form->addWysiwyg('intro_text',ts('Introductory Text'), $attributes['intro_text']);
        // FIXME: This hack forces height of editor to 175px. Need to modify QF classes for editors to allow passing
        // explicit height and width.
        $form->addWysiwyg('footer_text',ts('Footer Text'), array( 'rows' => 2, 'cols' => 40 ));

        $types    = array_merge( array( 'Contact', 'Individual', 'Participant' ),
                                 CRM_Contact_BAO_ContactType::subTypes( 'Individual' ) );
              
        $profiles = CRM_Core_BAO_UFGroup::getProfiles( $types );
        
        $mainProfiles = array('' => ts('- select -')) + $profiles;
        $addtProfiles = array('' => ts('- same as for main contact -'), 'none' => ts('- no profile -')) + $profiles;

        $form->add('select', 'custom_pre_id',             ts('Include Profile') . '<br />' . ts('(top of page)'),    $mainProfiles);
        $form->add('select', 'custom_post_id',            ts('Include Profile') . '<br />' . ts('(bottom of page)'), $mainProfiles);

        $form->add('select', 'additional_custom_pre_id',  ts('Profile for Additional Participants') . '<br />' . ts('(top of page)'),    $addtProfiles);
        $form->add('select', 'additional_custom_post_id', ts('Profile for Additional Participants') . '<br />' . ts('(bottom of page)'), $addtProfiles);
    }

    function buildMultipleProfileBottom( &$form, $count, $prefix = '', $name = 'Include Profile' )
    {
        $types    = array_merge( array( 'Contact', 'Individual', 'Participant' ),
                                 CRM_Contact_BAO_ContactType::subTypes( 'Individual' ) );

        $profiles = CRM_Core_BAO_UFGroup::getProfiles( $types );
        
        if ( $prefix == 'additional_' ) {
            $mainProfiles = array('' => ts('- same as for main contact -'), 'none' => ts('- no profile -')) + $profiles;
        } else {
            $mainProfiles = array('' => ts('- select -')) + $profiles;
        }

        $element = $prefix ."custom_post_id_multiple[$count]";
        $form->add('select', $element, $name. '<br />' . ts('(bottom of page)'), $mainProfiles);
    }

    /**
     * Function to build Confirmation Block  
     * 
     * @param int $pageId 
     * @static
     */
    function buildConfirmationBlock( &$form ) 
    {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Event_DAO_Event');
        $form->add('text','confirm_title',ts('Title'), $attributes['confirm_title']);
        $form->addWysiwyg('confirm_text',ts('Introductory Text'), $attributes['confirm_text']);
        // FIXME: This hack forces height of editor to 175px. Need to modify QF classes for editors to allow passing
        // explicit height and width.
        $form->addWysiwyg('confirm_footer_text',ts('Footer Text'), array( 'rows' => 2, 'cols' => 40 ));
    }

    /**
     * Function to build Email Block  
     * 
     * @param int $pageId 
     * @static
     */
    function buildMailBlock( &$form ) 
    {
        $form->registerRule( 'emailList', 'callback', 'emailList', 'CRM_Utils_Rule' );
        $attributes = CRM_Core_DAO::getAttribute('CRM_Event_DAO_Event');
        $form->addYesNo( 'is_email_confirm', ts( 'Send Confirmation Email?' ) , null, null, array('onclick' =>"return showHideByValue('is_email_confirm','','confirmEmail','block','radio',false);"));
        $form->add('textarea','confirm_email_text',ts('Text'), $attributes['confirm_email_text']);
        $form->add('text','cc_confirm',ts('CC Confirmation To'), CRM_Core_DAO::getAttribute('CRM_Event_DAO_Event', 'cc_confirm'));
        $form->addRule( 'cc_confirm', ts('Please enter a valid list of comma delimited email addresses'), 'emailList' );  
        $form->add('text','bcc_confirm',ts('BCC Confirmation To'), CRM_Core_DAO::getAttribute('CRM_Event_DAO_Event', 'bcc_confirm') );  
        $form->addRule( 'bcc_confirm', ts('Please enter a valid list of comma delimited email addresses'), 'emailList' );          
        $form->add('text', 'confirm_from_name', ts('Confirm From Name') );
        $form->add('text', 'confirm_from_email', ts('Confirm From Email') );  
        $form->addRule( 'confirm_from_email', ts('Email is not valid.'), 'email' );
    }

    function buildThankYouBlock( &$form ) 
    {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Event_DAO_Event');
        $form->add('text','thankyou_title',ts('Title'), $attributes['thankyou_title']);
        $form->addWysiwyg('thankyou_text',ts('Introductory Text'), $attributes['thankyou_text']);
        // FIXME: This hack forces height of editor to 175px. Need to modify QF classes for editors to allow passing
        // explicit height and width.
        $form->addWysiwyg('thankyou_footer_text',ts('Footer Text'), array( 'rows' => 2, 'cols' => 40 ));
    }
    /**
     * Add local and global form rules
     *
     * @access protected
     * @return void
     */
    function addRules( ) 
    {
        if ( $this->_addProfileBottom || $this->_addProfileBottomAdd ) {
            return;
        }
        $this->addFormRule( array( 'CRM_Event_Form_ManageEvent_Registration', 'formRule' ), $this );
    }
    
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $values, $files, $form ) 
    {
        if ( CRM_Utils_Array::value( 'is_online_registration', $values ) ) {
            
            if ( !$values['confirm_title'] ) {
                $errorMsg['confirm_title'] = ts('Please enter a Title for the registration Confirmation Page');
            }
            if ( !$values['thankyou_title'] ) {
                $errorMsg['thankyou_title'] = ts('Please enter a Title for the registration Thank-you Page');
            }
            if ( $values['is_email_confirm'] ) { 
                if ( !$values['confirm_from_name'] ) {
                    $errorMsg['confirm_from_name'] = ts('Please enter Confirmation Email FROM Name.');
                } 
                
                if ( !$values['confirm_from_email'] ) {
                    $errorMsg['confirm_from_email'] = ts('Please enter Confirmation Email FROM Email Address.');
                }
            }
            $additionalCustomPreId = $additionalCustomPostId = null;
            $isPreError = $isPostError = true;
            if ( CRM_Utils_Array::value( 'allow_same_participant_emails', $values ) &&
                 CRM_Utils_Array::value( 'is_multiple_registrations', $values ) ) {
                $types     = array_merge( array( 'Individual' ), CRM_Contact_BAO_ContactType::subTypes( 'Individual' ) );
                $profiles  = CRM_Core_BAO_UFGroup::getProfiles( $types );

                //check for additional custom pre profile
                $additionalCustomPreId = CRM_Utils_Array::value( 'additional_custom_pre_id', $values );
                if ( !empty( $additionalCustomPreId ) ) {
                    if ( !( $additionalCustomPreId == 'none' ) ) {
                        $customPreId = $additionalCustomPreId;
                    } else {
                        $isPreError = false;
                    }
                } else { 
                    $customPreId = CRM_Utils_Array::value( 'custom_pre_id', $values ) ? $values['custom_pre_id'] : null; 
                }
                //check whether the additional custom pre profile is of type 'Individual' and its subtypes
                if ( !empty( $customPreId ) ) {
                    $profileTypes = CRM_Core_BAO_UFGroup::profileGroups( $customPreId );
                    foreach ( $types as $individualTypes ) { 
                        if ( in_array( $individualTypes, $profileTypes ) ) {
                            $isPreError = false;
                            break;
                        } 
                    }
                } else {
                    $isPreError = false;  
                }
                //check for additional custom post profile
                $additionalCustomPostId = CRM_Utils_Array::value( 'additional_custom_post_id', $values );
                if ( !empty( $additionalCustomPostId ) ) {
                    if ( !( $additionalCustomPostId == 'none' ) ) {
                        $customPostId = $additionalCustomPostId;
                    } else {
                        $isPostError = false;
                    }
                } else { 
                    $customPostId = CRM_Utils_Array::value( 'custom_post_id', $values ) ? $values['custom_post_id'] : null; 
                }
                //check whether the additional custom post profile is of type 'Individual' and its subtypes
                if ( !empty( $customPostId ) ) {
                    $profileTypes = CRM_Core_BAO_UFGroup::profileGroups( $customPostId );
                    foreach ( $types as $individualTypes ) { 
                        if ( in_array( $individualTypes, $profileTypes ) ) {
                            $isPostError = false;
                            break;
                        }
                    }
                } else {
                    $isPostError = false; 
                }
                if ( $isPreError || ( empty( $customPreId ) && empty( $customPostId ) ) ) {
                    $errorMsg['additional_custom_pre_id'] = ts("Allow multiple registrations from the same email address requires a profile of type 'Individual'");
                }
                if ( $isPostError ) {
                    $errorMsg['additional_custom_post_id'] = ts("Allow multiple registrations from the same email address requires a profile of type 'Individual'");
                }
            }  
            
            // // CRM-8485
            // $config = CRM_Core_Config::singleton();
            // if ( $config->doNotAttachPDFReceipt ) {
            //     if ( CRM_Utils_Array::value('custom_post_id_multiple', $values) ) {
            //         foreach( $values['custom_post_id_multiple'] as $count => $customPostMultiple ) {
            //             if ( $customPostMultiple ) {
            //                 $errorMsg["custom_post_id_multiple[{$count}]"] = ts('Please disable PDF receipt as an attachment in <a href="%1">Miscellaneous Settings</a> if you want to add additional profiles.', array( 1 => CRM_Utils_System::url( 'civicrm/admin/setting/misc', 'reset=1' ) ) );
            //                 break;
            //             }
            //         }
            //     }
            //    
            //     if ( CRM_Utils_Array::value('is_multiple_registrations', $values) &&
            //          CRM_Utils_Array::value('additional_custom_post_id_multiple',  $values) ) {
            //         foreach( $values['additional_custom_post_id_multiple'] as $count => $customPostMultiple ) {
            //             if ( $customPostMultiple ) {
            //                $errorMsg["additional_custom_post_id_multiple[{$count}]"] = ts('Please disable PDF receipt as an attachment in <a href="%1">Miscellaneous Settings</a> if you want to add additional profiles.', array( 1 => CRM_Utils_System::url( 'civicrm/admin/setting/misc', 'reset=1' ) ) );
            //                 break;
            //             }
            //         }
            //     }
            // }
            
            if ( !empty($errorMsg) ) {
                if ( CRM_Utils_Array::value('custom_post_id_multiple', $values) ) {
                    foreach( $values['custom_post_id_multiple'] as $count => $customPostMultiple ) {
                        self::buildMultipleProfileBottom($form, $count);
                    }
                    $form->assign( 'profilePostMultiple', $values['custom_post_id_multiple'] );
                }
                if ( CRM_Utils_Array::value('additional_custom_post_id_multiple',  $values) ) {
                    foreach( $values['additional_custom_post_id_multiple'] as $count => $customPostMultiple ) {
                        self::buildMultipleProfileBottom($form, $count, 'additional_', ts('Profile for Additional Participants')); 
                    }
                    $form->assign( 'profilePostMultipleAdd', $values['additional_custom_post_id_multiple'] );
                }
            }
            
        }
        
        if ( !empty($errorMsg) ) {
            return $errorMsg;
        }        
        
        return true;
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {   
        $params = array();
        $params = $this->exportValues();

        $params['id'] = $this->_id;

        //format params
        $params['is_online_registration']        = CRM_Utils_Array::value('is_online_registration', $params, false);
        $params['is_multiple_registrations']     = CRM_Utils_Array::value('is_multiple_registrations', $params, false);
        $params['allow_same_participant_emails'] = CRM_Utils_Array::value('allow_same_participant_emails', $params, false);
        $params['requires_approval']             = CRM_Utils_Array::value('requires_approval', $params, false);
        
        // reset is_email confirm if not online reg
        if ( ! $params['is_online_registration'] ) {
            $params['is_email_confirm'] = false;
        }
        
        if ( !$this->_isTemplate ) {
            $params['registration_start_date'] = CRM_Utils_Date::processDate( $params['registration_start_date'], 
                                                                              $params['registration_start_date_time'],
                                                                              true );
            $params['registration_end_date']   = CRM_Utils_Date::processDate( $params['registration_end_date'],
                                                                              $params['registration_end_date_time'],
                                                                              true );
        }
        
        require_once 'CRM/Event/BAO/Event.php';
        CRM_Event_BAO_Event::add( $params );
        
        // also update the ProfileModule tables 
        $ufJoinParams = array( 'is_active'    => 1, 
                               'module'       => 'CiviEvent',
                               'entity_table' => 'civicrm_event', 
                               'entity_id'    => $this->_id );
        
        require_once 'CRM/Core/BAO/UFJoin.php';

        // first delete all past entries
        CRM_Core_BAO_UFJoin::deleteAll( $ufJoinParams );

        $uf = array();
        $wt = 2;
        if ( ! empty( $params['custom_pre_id'] ) ) {
            $uf[1] = $params['custom_pre_id'];  
            $wt = 1;
        }
        
        if ( ! empty( $params['custom_post_id'] ) ) {
            $uf[2] = $params['custom_post_id'];
        }
        
        if (CRM_Utils_Array::value('custom_post_id_multiple', $params)){
            $uf = array_merge ($uf, $params['custom_post_id_multiple']);
        }
        $uf = array_values($uf);
        if ( ! empty( $uf ) ) {
            foreach ( $uf as $weight => $ufGroupId) {
                $ufJoinParams['weight'] = $weight+$wt;
                $ufJoinParams['uf_group_id'] = $ufGroupId;
                CRM_Core_BAO_UFJoin::create( $ufJoinParams );
                unset( $ufJoinParams['id'] );
            }
        }
        // also update the ProfileModule tables 
        $ufJoinParamsAdd = array( 'is_active'    => 1, 
                                  'module'       => 'CiviEvent_Additional',
                                  'entity_table' => 'civicrm_event', 
                                  'entity_id'    => $this->_id );
        
        // first delete all past entries
        CRM_Core_BAO_UFJoin::deleteAll( $ufJoinParamsAdd );
        if (CRM_Utils_Array::value('is_multiple_registrations', $params ) ) {
            $ufAdd = array();
            $wtAdd = 2;
            
            if ( array_key_exists('additional_custom_pre_id', $params)) {
                if ( !CRM_Utils_Array::value('additional_custom_pre_id', $params) ) {
                    $ufAdd[1] = $params['custom_pre_id'];  
                    $wtAdd = 1;
                } else if ( CRM_Utils_Array::value('additional_custom_pre_id', $params) == 'none') {
                    
                } else {            
                    $ufAdd[1] = $params['additional_custom_pre_id'];  
                    $wtAdd = 1;
                } 
            }
            
            if ( array_key_exists('additional_custom_post_id', $params)) {
                if ( !CRM_Utils_Array::value('additional_custom_post_id', $params) ) {
                    $ufAdd[2] = $params['custom_post_id'];  
                } else if ( CRM_Utils_Array::value('additional_custom_post_id', $params) == 'none') {
                    
                } else {            
                    $ufAdd[2] = $params['additional_custom_post_id'];

                }
            }

            if (CRM_Utils_Array::value('additional_custom_post_id_multiple', $params)) {
                $additionalPostMultiple = array( );
                foreach( $params['additional_custom_post_id_multiple'] as $key => $value ) {
                    if ( !$value && CRM_Utils_Array::value('custom_post_id', $params) ) {
                        $additionalPostMultiple[$key] = $params['custom_post_id'];
                    } else if ( $value == 'none' ) {
                        continue;
                    } else if ( $value ) {            
                        $additionalPostMultiple[$key] = $value;
                    }
                }
                $ufAdd = array_merge ($ufAdd, $additionalPostMultiple);
            }

            $ufAdd = array_values($ufAdd);            
            if ( ! empty( $ufAdd ) ) {
                foreach ( $ufAdd as $weightAdd => $ufGroupIdAdd) {

                    $ufJoinParamsAdd['weight'] = $weightAdd+$wtAdd;
                    $ufJoinParamsAdd['uf_group_id'] = $ufGroupIdAdd;
                    
                    CRM_Core_BAO_UFJoin::create( $ufJoinParamsAdd );
                    unset( $ufJoinParamsAdd['id'] );
                }
            }
        }

        parent::endPostProcess( );
    } //end of function
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) 
    {
        return ts('Online Registration');
    }

    
    
}

