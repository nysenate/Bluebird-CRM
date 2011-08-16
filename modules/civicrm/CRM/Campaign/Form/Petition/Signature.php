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
require_once 'CRM/Campaign/BAO/Petition.php';
require_once 'CRM/Core/PseudoConstant.php';
       
/**
 * This class generates form components for processing a petition signature 
 * 
 */

class CRM_Campaign_Form_Petition_Signature extends CRM_Core_Form
{
    const
    	EMAIL_THANK = 1,
    	EMAIL_CONFIRM = 2,
      MODE_CREATE   = 4;
    	
    protected $_mode;        
        
    /**
     * the id of the contact associated with this signature
     *
     * @var int
     * @public
     */
    public $_contactId;    

    /** 
     * Is this a logged in user
     * 
     * @var int 
     */ 
    protected $_loggedIn = FALSE;
    
    /** 
     * The contact type
     * 
     * @var boolean 
     */ 
    protected $_ctype;   

    /** 
     * The contact profile id attached with this petition
     * 
     * @var int 
     */ 
    protected $_contactProfileId;  

    /** 
     * the contact profile fields used for this petition
     * 
     * @var array 
     */ 
    public $_contactProfileFields; 
    
    /** 
     * The activity profile id attached with this petition
     * 
     * @var int 
     */ 
    protected $_activityProfileId;    

    /** 
     * the activity profile fields used for this petition
     * 
     * @var array 
     */ 
    public $_activityProfileFields; 
    
    /**
     * the id of the survey (petition) we are proceessing
     *
     * @var int
     * @protected
     */
    public $_surveyId;

    /** 
     * The tag id used to set against contacts with unconfirmed email
     * 
     * @var int 
     */ 
    protected $_tagId;    
    
    /**
     * values to use for custom profiles
     *
     * @var array
     * @protected
     */
    public $_values;    

    /**
     * The params submitted by the form
     *
     * @var array
     * @protected
     */
    protected $_params;    

    
    /** 
     * which email send mode do we use
     * 
     * @var int 
     * EMAIL_THANK = 1,
   	 * 		connected user via login/pwd - thank you
	 * 	 	or dedupe contact matched who doesn't have a tag CIVICRM_TAG_UNCONFIRMED - thank you
	 * 		or login using fb connect - thank you + click to add msg to fb wall
	 * EMAIL_CONFIRM = 2;
	 *		send a confirmation request email         
     */ 
    protected $_sendEmailMode;    
    
    protected $_image_URL;
    
    protected $_defaults = null;

	function __construct() {
		parent::__construct();
		// this property used by civicrm_fb module and if true, forces thank you email to be sent
		// for users signing in via Facebook connect; also sets Fb email to check against
		$this->forceEmailConfirmed['flag'] = false; 
		$this->forceEmailConfirmed['email'] = ''; 
	}
	
    public function preProcess()
    {
      $this->bao = new CRM_Campaign_BAO_Petition();
	    $this->_mode = self::MODE_CREATE;

    	//get the survey id
       $this->_surveyId = CRM_Utils_Request::retrieve('sid', 'Positive', $this );
        
      //some sanity checks
      if (!$this->_surveyId ) {
	    	CRM_Core_Error::fatal( 'Petition id is not valid. (it needs a "sid" in the url).' );
        return; 
      }
			//check petition is valid and active
			require_once 'CRM/Campaign/BAO/Survey.php';
			$params['id'] = $this->_surveyId;
			$this->petition = array();
			CRM_Campaign_BAO_Survey::retrieve($params,$this->petition);
	        if (empty($this->petition)) {
				CRM_Core_Error::fatal( 'Petition doesn\'t exist.' );
			}
			if ($this->petition['is_active'] == 0) {
				CRM_Core_Error::fatal( 'Petition is no longer active.' );
			}

      //get userID from session
      $session = CRM_Core_Session::singleton( );   
    
	    //get the contact id for this user if logged in
      $this->_contactId =  $session->get( 'userID' );
      if (isset($this->_contactId)) {
	        $this->_loggedIn = TRUE;
    	}
      
        // add the custom contact and activity profile fields to the signature form
        require_once 'CRM/Core/BAO/UFJoin.php';         
	      require_once 'CRM/Core/BAO/UFGroup.php';

        $ufJoinParams = array( 'entity_id'    => $this->_surveyId,
                               'entity_table' => 'civicrm_survey',   
                               'module'       => 'CiviCampaign',
                               'weight'		  =>  2);        
        
        $this->_contactProfileId = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );  
        if ( $this->_contactProfileId ) {
	        $this->_contactProfileFields  = CRM_Core_BAO_UFGroup::getFields( $this->_contactProfileId, false, CRM_Core_Action::ADD);	        			       	     
    		}	   
        if ( !isset ($this->_contactProfileFields['email-Primary']) ) {
   				CRM_Core_Error::fatal( 'The contact profile needs to contain the primary email address field' );
        }
        

        $ufJoinParams['weight'] = 1;
        $this->_activityProfileId = CRM_Core_BAO_UFJoin::findUFGroupId( $ufJoinParams );  

        if ( $this->_activityProfileId ) {
	        $this->_activityProfileFields  = CRM_Core_BAO_UFGroup::getFields( $this->_activityProfileId, false, CRM_Core_Action::ADD);	        			       	     
		}

        $this->setDefaultValues();
        CRM_Utils_System::setTitle( $this->petition['title'] );
    }
    
    /**
     * This function sets the default values for the form.
     *
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        require_once 'CRM/Core/BAO/UFGroup.php';         
        $this->_defaults = array( );   
        if ( $this->_contactId ) {
            CRM_Core_BAO_UFGroup::setProfileDefaults( $this->_contactId, $this->_contactProfileFields, $this->_defaults, true );        
			if ( $this->_activityProfileId ) {
            	CRM_Core_BAO_UFGroup::setProfileDefaults( $this->_contactId, $this->_activityProfileFields, $this->_defaults, true );
        	}
        }
        
        //set custom field defaults
        require_once "CRM/Core/BAO/CustomField.php";
    
		foreach ( $this->_contactProfileFields as $name => $field ) {
            if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID($name) ) {
                $htmlType = $field['html_type'];
                
                if ( !isset( $this->_defaults[$name] ) ) {
                    CRM_Core_BAO_CustomField::setProfileDefaults( $customFieldID,
                                                                  $name,
                                                                  $this->_defaults,
                                                                  $this->_contactId,
                                                                  $this->_mode );
                }
			}                
		}

		if ($this->_activityProfileFields) {		
			foreach ( $this->_activityProfileFields as $name => $field ) {
				if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID($name) ) {
					$htmlType = $field['html_type'];
					
					if ( !isset( $this->_defaults[$name] ) ) {
						CRM_Core_BAO_CustomField::setProfileDefaults( $customFieldID,
																	  $name,
																	  $this->_defaults,
																	  $this->_contactId,
																	  $this->_mode );
					}
				}                
			}
		}      
		
        $this->setDefaults( $this->_defaults );
    }
    
    public function buildQuickForm()
    {
    	$this->assign( 'survey_id', $this->_surveyId);
    
    	if (isset($_COOKIE['signed_'.$this->_surveyId])) {
			if (isset($_COOKIE['confirmed_'.$this->_surveyId])) { 		
    			$this->assign( 'duplicate', "confirmed");
    		} else {
    			$this->assign( 'duplicate', "unconfirmed");
    		}    		
    		return;
		}
    
        $this->applyFilter('__ALL__','trim');

        $this->buildCustom( $this->_contactProfileId , 'petitionContactProfile'  );      
        if ( $this->_activityProfileId ) {
	        $this->buildCustom( $this->_activityProfileId , 'petitionActivityProfile'  );
    	}           
		// add buttons
		$this->addButtons(array(
                                array ('type'      => 'next',
                                       'name'      => ts('Sign the Petition'),
                                       'isDefault' => true),  
                                )
                          );                                      
    }
    
    /**
     * This function is used to add the rules (mainly global rules) for form.
     * All local rules are added near the element
     *
     * @return None
     * @access public
     * @see valid_date
     */
    
    static function formRule( $fields, $files, $errors )
    {
        $errors = array( );
        
        return empty($errors) ? true : $errors;
    }

    /**
     * Form submission of petition signature
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {		
       
		if (defined('CIVICRM_TAG_UNCONFIRMED')) {
			// Check if contact 'email confirmed' tag exists, else create one
			// This should be in the petition module initialise code to create a default tag for this
			$tag_params['name'] = CIVICRM_TAG_UNCONFIRMED;
			$tag_params['version'] = 3;
			$tag = civicrm_api('tag','get',$tag_params); 
			if ($tag['count'] == 0) {				
				//create tag
				$tag_params['description'] = CIVICRM_TAG_UNCONFIRMED;
				$tag_params['is_reserved'] = 1;
				$tag_params['used_for'] = 'civicrm_contact';
				$tag = civicrm_api('tag', 'create', $tag_params); 
				$this->_tagId = $tag['tag_id'];
			} else {
				$this->_tagId = $tag['id'];
			}

		}
		
		// export the field values to be used for saving the profile form
		$params = $this->controller->exportValues( $this->_name );      
		
        $session = CRM_Core_Session::singleton( );
        // format params
        $params['last_modified_id'] = $session->get( 'userID' );
        $params['last_modified_date'] = date('YmdHis');
        
        if ( $this->_action & CRM_Core_Action::ADD ) { 
            $params['created_id']   = $session->get( 'userID' );
            $params['created_date'] = date('YmdHis');
        } 
        
        if ( isset( $this->_surveyId ) ) {
            $params['sid'] = $this->_surveyId;
        }
        
        if ( isset( $this->_contactId ) ) {
            $params['contactId'] = $this->_contactId;
        }
        
		// if logged in user, skip dedupe
		if ($this->_loggedIn) {
			$ids[0] = $this->_contactId;
		} else {
			// dupeCheck - check if contact record already exists
			// code modified from api/v2/Contact.php-function civicrm_contact_check_params()
			require_once 'CRM/Dedupe/Finder.php';
			$params['contact_type'] = $this->_ctype;    
			//TODO - current dedupe finds soft deleted contacts - adding param is_deleted not working
			//$params['is_deleted'] = 0;  // ignore soft deleted contacts
			$dedupeParams = CRM_Dedupe_Finder::formatParams($params, $params['contact_type']);
			$dedupeParams['check_permission'] = '';       
					
			//dupesByParams($params, $ctype, $level = 'Strict', $except = array())
			$ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, $params['contact_type']);		
		}
		
		switch (count($ids)) {
			case 0:
				//no matching contacts - create a new contact				
				require_once 'CRM/Campaign/BAO/Survey.php';
				$petition_params['id'] = $this->_surveyId;
				$petition = array();
				CRM_Campaign_BAO_Survey::retrieve($petition_params,$petition);

				// Add a source for this new contact
				$params['source'] = ts('Petition Signature'). ' ' . $this->petition['title'];
				$this->_sendEmailMode = self::EMAIL_CONFIRM;

				// Set status for signature activity to scheduled until email is verified
				$params['statusId'] = 1;
				break;
			case 1:
				$this->_contactId = $ids[0];

				// check if user has already signed this petition - redirects to Thank You if true
				$this->redirectIfSigned($params);
				
				// dedupe matched single contact, check for 'unconfirmed' tag				
				if (defined('CIVICRM_TAG_UNCONFIRMED')) {
					require_once 'CRM/Core/DAO/EntityTag.php';
					$tag = new CRM_Core_DAO_EntityTag( );
					$tag->entity_id  = $this->_contactId;
					$tag->tag_id     = $this->_tagId;
					
					if (  !($tag->find( )) ) {
						// send thank you email directly, the user is known and validated
						$this->_sendEmailMode = self::EMAIL_THANK;
						// Set status for signature activity to completed
						$params['statusId'] = 2;
					} else {
						// send email verification email
						$this->_sendEmailMode = self::EMAIL_CONFIRM;
						// Set status for signature activity to scheduled until email is verified
						$params['statusId'] = 1;
					}
				}   				
				break;
			default:
				// more than 1 matching contact
				// for time being, take the first matching contact (not sure that's the best strategy, but better than creating another duplicate)
				$this->_contactId = $ids[0];
				
				// check if user has already signed this petition - redirects to Thank You if true
				$this->redirectIfSigned($params);
				if (defined('CIVICRM_TAG_UNCONFIRMED')) {
					require_once 'CRM/Core/DAO/EntityTag.php';
					$tag = new CRM_Core_DAO_EntityTag( );
					$tag->entity_id  = $this->_contactId;
					$tag->tag_id     = $this->_tagId;
					
					if (  !($tag->find( )) ) {
						// send thank you email
						$this->_sendEmailMode = self::EMAIL_THANK;
						// Set status for signature activity to completed
						$params['statusId'] = 2;						
					} else {
						// send email verification email
						$this->_sendEmailMode = self::EMAIL_CONFIRM;
						// Set status for signature activity to scheduled until email is verified
						$params['statusId'] = 1;
					}
				}				
				break;
			}



			require_once 'CRM/Core/Transaction.php';
			$transaction = new CRM_Core_Transaction( );
			
            $addToGroupID = isset($this->_addToGroupID) ? $this->_addToGroupID : null;
			$this->_contactId = CRM_Contact_BAO_Contact::createProfileContact( $params, $this->_contactProfileFields,
                                                                               $this->_contactId, $addToGroupID,
                                                                               $this->_contactProfileId, $this->_ctype,
                                                                               true );

			// get additional custom activity profile field data 
			// to save with new signature activity record
			$surveyInfo = $this->bao->getSurveyInfo($this->_surveyId);
      		$customActivityFields     = 
                CRM_Core_BAO_CustomField::getFields( 'Activity', false, false, 
                                                     $surveyInfo['activity_type_id']  );
      		$customActivityFields     = 
                CRM_Utils_Array::crmArrayMerge( $customActivityFields, 
                                                CRM_Core_BAO_CustomField::getFields( 'Activity', false, false, 
                                                                                     null, null, true ) );
                                                                      
			$params['custom'] = CRM_Core_BAO_CustomField::postProcess( $params,
                                                                       $customActivityFields,
                                                                       null,
                                                                       'Activity' );                                                          
                                                                       
			// create the signature activity record																  
			$params['contactId'] = $this->_contactId;
			$result = $this->bao->createSignature( $params );

			// send thank you or email verification emails

			// if logged in using Facebook connect and email on form matches Fb email,
			// no need for email confirmation, send thank you email	
			if ($this->forceEmailConfirmed['flag'] && 
					($this->forceEmailConfirmed['email'] == $params['email-Primary'])) {
						$this->_sendEmailMode = self::EMAIL_THANK;
			}

			switch ($this->_sendEmailMode) {
				case self::EMAIL_THANK:			
					// mark the signature activity as completed and set confirmed cookie
					$this->bao->confirmSignature($result->id,$this->_contactId,$this->_surveyId);
					break;
				
				case self::EMAIL_CONFIRM:
					// set 'Unconfirmed' tag for this new contact
					if (defined('CIVICRM_TAG_UNCONFIRMED')) {
						unset($tag_params);
						$tag_params['contact_id'] = $this->_contactId;
						$tag_params['tag_id'] = $this->_tagId;
						$tag_value = civicrm_api('entity_tag', 'create', $tag_params);					
					}
					break;
			}
				
			//send email
			$params['activityId'] = $result->id;
			$params['tagId'] = $this->_tagId;						
			$this->bao->sendEmail( $params, $this->_sendEmailMode );

			$transaction->commit( );
	
			if ( $result ) {
				// set the template to thank you
				CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/petition/thankyou', 'pid='.$this->_surveyId.'&id='.$this->_sendEmailMode.'&reset=1'));
			}        
    }   
    
    /**  
     * Function to build the petition profile form
     *  
     * @return None  
     * @access public  
     */ 
    function buildCustom( $id, $name, $viewOnly = false ) 
    {

        if ( $id ) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            require_once 'CRM/Profile/Form.php';
            $session = CRM_Core_Session::singleton( );
            $this->assign( "petition" , $this->petition );
            $contactID = null; //$contactID = $this->_contactId;	   
            $this->assign( 'contact_id', $this->_contactId );

            $fields = null;
            if ( $contactID ) { //TODO: contactID is never set (commented above)
                require_once "CRM/Core/BAO/UFGroup.php";
                if ( CRM_Core_BAO_UFGroup::filterUFGroups($id, $contactID)  ) {
                    $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD );
                }
            } else {
                $fields = CRM_Core_BAO_UFGroup::getFields( $id, false,CRM_Core_Action::ADD ); 
            }

            if ( $fields ) {
                /*
                // unset any email-* fields since we already collect it, CRM-2888
                foreach ( array_keys( $fields ) as $fieldName ) {
                    if ( substr( $fieldName, 0, 6 ) == 'email-' ) {
                        unset( $fields[$fieldName] );
                    }
                }
                */
                 
                $this->assign( $name, $fields );
                
                $addCaptcha = false;
                foreach($fields as $key => $field) {
                    if ( $viewOnly &&
                         isset( $field['data_type'] ) &&
                         $field['data_type'] == 'File' || ( $viewOnly && $field['name'] == 'image_URL' ) ) {
                        // ignore file upload fields
                        continue;
                    }

                
                    CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE, $contactID, true );
                    $this->_fields[$key] = $field;
                    if ( $field['add_captcha'] ) {
                        $addCaptcha = true;
                    }
                }

                if ( $addCaptcha &&
                     ! $viewOnly ) {
                    require_once 'CRM/Utils/ReCAPTCHA.php';
                    $captcha =& CRM_Utils_ReCAPTCHA::singleton( );
                    $captcha->add( $this );
                    $this->assign( "isCaptcha" , true );
                }                
                
            }
        }
    }
       

    function getTemplateFileName() {
        if ( isset($this->thankyou) ) {
            return('CRM/Campaign/Page/Petition/ThankYou.tpl');
        } else {
        }
        return parent::getTemplateFileName();
    }
 
    // check if user has already signed this petition    
	function redirectIfSigned( $params )
	{		
		$signature = $this->bao->checkSignature( $this->_surveyId, $this->_contactId );
		//TODO: error case when more than one signature found for this petition and this contact
		if ( !empty($signature) && (count($signature) == 1)) {
			$signature_id = array_keys($signature);
			switch ($signature[$signature_id[0]]['status_id']) {
				case 1: //status is scheduled - email is unconfirmed
					CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/petition/thankyou', 'pid='.$this->_surveyId.'&id=4&reset=1'));
					break;
				
				case 2: //status is completed 
					$this->bao->sendEmail( $params, 1 );
					CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/petition/thankyou', 'pid='.$this->_surveyId.'&id=5&reset=1'));
					break;
			}
		}	
	}
    
    
}
    
?>
