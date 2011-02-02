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


require_once 'CRM/Campaign/BAO/Survey.php';

Class CRM_Campaign_BAO_Petition extends CRM_Campaign_BAO_Survey
{
    	
    function __construct() {
       parent::__construct();
       $this->cookieExpire = (1 * 60 * 60 * 24); // expire cookie in one day
    }
		
     /**
     * Function to get Petition Details 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getPetition( $all = false, $id = false, $defaultOnly = false ) {

        $petitionTypeID = CRM_Core_OptionGroup::getValue( 'activity_type', 'petition',  'name' );

        $survey = array( );
        $dao = new CRM_Campaign_DAO_Survey( );

        if ( !$all ) {
            $dao->is_active = 1;
        } 
        if ( $id ) {
            $dao->id = $id;  
        }
        if ( $defaultOnly ) {
            $dao->is_default = 1;   
        }
       
        $dao->whereAdd ( "activity_type_id = $petitionTypeID");   
        $dao->find( );
        while ( $dao->fetch() ) {
            CRM_Core_DAO::storeValues($dao, $survey[$dao->id]);
        }
        
        return $survey;
    }

    /**
     * takes an associative array and creates a petition signature activity
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     *
     * @return object CRM_Campaign_BAO_Petition
     * @access public
     * @static
     */
    function createSignature( &$params ) 
    {
        if ( empty( $params ) ) {
            return;
        }
        
        if ( !isset( $params['sid'] ) ) {
            $statusMsg = ts( 'No survey sid parameter. Cannot process signature.' );
            CRM_Core_Session::setStatus( $statusMsg );			
			return;
		}
  
        if ( isset( $params['contactId'] ) ) {
        
        	// add signature as activity with survey id as source id
        	// get the activity type id associated with this survey        	
        	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo($params['sid']);

	        require_once 'CRM/Activity/BAO/Activity.php';
			// create activity 
			// activity status id (from /civicrm/admin/optionValue?reset=1&action=browse&gid=25)
			// 1-Schedule, 2-Completed
	        
			$activityParams = array ( 'source_contact_id'  => $params['contactId'],
									  'target_contact_id'  => $params['contactId'],	
			                          'source_record_id'   => $params['sid'],
                                      'subject'            => $surveyInfo['title'],
									  'activity_type_id'   => $surveyInfo['activity_type_id'],
									  'activity_date_time' => date("YmdHis"), 
									  'status_id'          => $params['statusId'] );

			//activity creation
        	// *** check for activity using source id - if already signed
			$activity = CRM_Activity_BAO_Activity::create( $activityParams );
			
			// save activity custom data
		    if ( CRM_Utils_Array::value( 'custom', $params ) &&
            		is_array( $params['custom'] ) ) {
            	require_once 'CRM/Core/BAO/CustomValueTable.php';
            	CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_activity', $activity->id );
        	}

 			// set permanent cookie to indicate this petition already signed on the computer
			setcookie('signed_'.$params['sid'], $activity->id, time() + $this->cookieExpire, '/');       	
			
		}
		
        return $activity;
    }

    function confirmSignature($activity_id,$contact_id,$petition_id) {
    	//change activity status to completed (status_id=2)	
        $query = "UPDATE civicrm_activity SET status_id = 2 
                WHERE 	id = $activity_id 
                AND  	source_contact_id = $contact_id";
        CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );

		// define constant CIVICRM_TAG_UNCONFIRMED, if not exist in civicrm.settings.php
		if ( !defined('CIVICRM_TAG_UNCONFIRMED') ) {
            define('CIVICRM_TAG_UNCONFIRMED', 'Unconfirmed');
        }
        
        // remove 'Unconfirmed' tag for this contact
        // Check if contact 'email confirmed' tag exists, else create one
        // This should be in the petition module initialise code to create a default tag for this
        require_once 'api/v2/Tag.php';	
        $tag_params['name'] = CIVICRM_TAG_UNCONFIRMED;
        $tag = civicrm_tag_get($tag_params); 
		
        require_once 'api/v2/EntityTag.php';				
        unset($tag_params);
        $tag_params['contact_id'] = $contact_id;
        $tag_params['tag_id'] = $tag['id'];			
        $tag_value = civicrm_entity_tag_remove($tag_params);	
		
		// set permanent cookie to indicate this users email address now confirmed
		setcookie('confirmed_'.$petition_id, $activity_id, time() + $this->cookieExpire, '/');						

		return true;
    }    


     /**
     * Function to get Petition Signature Total 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getPetitionSignatureTotalbyCountry ( $surveyId ) {
        $countries = array( );	
        $sql = "
SELECT count(civicrm_address.country_id) as total,
    IFNULL(country_id,'') as country_id,IFNULL(iso_code,'') as country_iso, IFNULL(civicrm_country.name,'') as country
 FROM  	civicrm_activity a, civicrm_survey, civicrm_contact
  LEFT JOIN civicrm_address ON civicrm_address.contact_id = civicrm_contact.id AND civicrm_address.is_primary = 1 
  LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
WHERE 
  a.source_contact_id = civicrm_contact.id AND
  a.activity_type_id = civicrm_survey.activity_type_id AND
  civicrm_survey.id =  $surveyId AND  
	a.source_record_id =  $surveyId  ";
     if ($status_id)
       $sql .= " AND status_id = ". (int) $status_id;
  $sql .= " GROUP BY civicrm_address.country_id";
     $fields = array ('total','country_id','country_iso','country');
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $row = array();
           foreach ($fields as $field) {
             $row[$field] = $dao->$field;
           }
           $countries [] = $row;
        }
        return $countries;
    }    

     /**
     * Function to get Petition Signature Total 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getPetitionSignatureTotal( $surveyId ) {
    	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo((int) $surveyId);
    	//$activityTypeID = $surveyInfo['activity_type_id'];
        $signature = array( );	
        
        $sql = "
SELECT 
		status_id,count(id) as total
 FROM  	civicrm_activity
WHERE 
	source_record_id = " . (int) $surveyId  . 
	" AND activity_type_id = " . (int)  $surveyInfo['activity_type_id'] . 
" GROUP BY status_id";
        require_once 'CRM/Contact/BAO/Contact.php'; 

        $statusTotal = array();$total =0;
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
          $total += $dao->total;
          $statusTotal['status'][$dao->status_id] = $dao->total;
        }
        $statusTotal['count']=$total;
        return $statusTotal;
    }    

    
    public function getSurveyInfo( $surveyId=null ) 
    {
		$surveyInfo = array( );

        $sql = "
SELECT 	activity_type_id, 
		campaign_id,
		title,
		ov.label AS activity_type
FROM  civicrm_survey s, civicrm_option_value ov, civicrm_option_group og
WHERE s.id = " . $surveyId ."
AND s.activity_type_id = ov.value
AND ov.option_group_id = og.id
AND og.name = 'activity_type'";
        
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {      
           //$survey['campaign_id'] = $dao->campaign_id;  
           //$survey['campaign_name'] = $dao->campaign_name; 
           $surveyInfo['activity_type'] = $dao->activity_type; 
           $surveyInfo['activity_type_id'] = $dao->activity_type_id;   
           $surveyInfo['title'] = $dao->title; 
        }
           	
        return $surveyInfo ;
    }
    
     /**
     * Function to get Petition Signature Details 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getPetitionSignature( $surveyId, $status_id=null ) {
    
      $surveyId = (int)$surveyId;// sql injection protection
        $signature = array( );	

        $sql = "
SELECT 	a.id,
		a.source_record_id as survey_id,
		a.activity_date_time,
		a.status_id,
		civicrm_contact.id as contact_id,
    civicrm_contact.contact_type,civicrm_contact.contact_sub_type,image_URL,
    first_name,last_name,sort_name,
    employer_id,organization_name,
    household_name,
    IFNULL(gender_id,'') AS gender_id,
    IFNULL(state_province_id,'') AS state_province_id,
    IFNULL(country_id,'') as country_id,IFNULL(iso_code,'') as country_iso, IFNULL(civicrm_country.name,'') as country
 FROM  	civicrm_activity a, civicrm_survey, civicrm_contact
  LEFT JOIN civicrm_address ON civicrm_address.contact_id = civicrm_contact.id  AND civicrm_address.is_primary = 1 
  LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
WHERE 
  a.source_contact_id = civicrm_contact.id AND
  a.activity_type_id = civicrm_survey.activity_type_id AND
  civicrm_survey.id =  $surveyId AND  
	a.source_record_id =  $surveyId  ";
     if ($status_id)
       $sql .= " AND status_id = ". (int) $status_id;
     $fields = array ('id','survey_id','contact_id','activity_date_time','activity_type_id','status_id','first_name','last_name', 'sort_name','gender_id','country_id','state_province_id','country_iso','country');
     $sql .= " ORDER BY  a.activity_date_time";

        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $row = array();
           foreach ($fields as $field) {
             $row[$field] = $dao->$field;
           }
           $signature [] = $row;
        }
        return $signature;
    }    

    /**
     * This function returns all entities assigned to a specific tag
     * 
     * @param object  $tag    an object of a tag.
     *
     * @return  array   $contactIds    array of contact ids
     * @access public
     */
    function getEntitiesByTag($tag)
    {
	    require_once 'CRM/Core/DAO/EntityTag.php';
        $contactIds = array();
        $entityTagDAO = new CRM_Core_DAO_EntityTag();
        $entityTagDAO->tag_id = $tag['id'];
        $entityTagDAO->find();

        while($entityTagDAO->fetch()) {
            $contactIds[] = $entityTagDAO->entity_id;
        }
        return $contactIds;
    }


     /**
     * Function to check if contact has signed this petition
     * 
     * @param int $surveyId
     * @param int $contactId
     * @static
     */
    static function checkSignature( $surveyId, $contactId ) {
    
    	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo($surveyId);
        $signature = array( );	

        $sql = "
SELECT 	a.id AS id,
		a.source_record_id AS source_record_id,
		a.source_contact_id AS source_contact_id,
		a.activity_date_time AS activity_date_time,
		a.activity_type_id AS activity_type_id,
		a.status_id AS status_id," .
		"'" . $surveyInfo['title'] . "'" ." AS survey_title 
FROM  	civicrm_activity a
WHERE 	a.source_record_id = " . $surveyId . " 
	AND a.activity_type_id = " . $surveyInfo['activity_type_id'] . "
	AND a.source_contact_id = " . $contactId;

        require_once 'CRM/Contact/BAO/Contact.php'; 

        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $signature[$dao->id]['id'] = $dao->id;     
           $signature[$dao->id]['source_record_id'] = $dao->source_record_id;
           $signature[$dao->id]['source_contact_id'] = CRM_Contact_BAO_Contact::displayName($dao->source_contact_id);
           $signature[$dao->id]['activity_date_time'] = $dao->activity_date_time;
           $signature[$dao->id]['activity_type_id'] = $dao->activity_type_id;   
           $signature[$dao->id]['status_id'] = $dao->status_id;
           $signature[$dao->id]['survey_title'] = $dao->survey_title;
           $signature[$dao->id]['contactId'] = $dao->source_contact_id;
        }
        
        return $signature;
    }


    /**
     * takes an associative array and sends a thank you or email verification email
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     *
     * @return 
     * @access public
     * @static
     */
    function sendEmail( $params, $sendEmailMode )
    {
    
    /* sendEmailMode
     * CRM_Campaign_Form_Petition_Signature::EMAIL_THANK
     * 		connected user via login/pwd - thank you
	 * 	 	or dedupe contact matched who doesn't have a tag CIVICRM_TAG_UNCONFIRMED - thank you
	 *  	or login using fb connect - thank you + click to add msg to fb wall
	 *
	 * CRM_Campaign_Form_Petition_Signature::EMAIL_CONFIRM
	 *		send a confirmation request email     
	 */
		require_once 'CRM/Campaign/Form/Petition/Signature.php';
		
        // define constant CIVICRM_PETITION_CONTACTS, if not exist in civicrm.settings.php
		if ( !defined('CIVICRM_PETITION_CONTACTS') ) {
            define('CIVICRM_PETITION_CONTACTS','Petition Contacts'); 
        }
			
        // check if the group defined by CIVICRM_PETITION_CONTACTS exists, else create it
        require_once 'api/v2/Group.php';
        $group_params['title'] = CIVICRM_PETITION_CONTACTS;
        $groups = civicrm_group_get($group_params);
        if ( ( CRM_Utils_Array::value('is_error', $groups) == 1 ) && ( CRM_Utils_Array::value('error_message', $groups) == 'No such group exists' ) ) {
            $group_params['is_active'] = 1;
            $group_params['visibility'] = 'Public Pages';
            $newgroup = civicrm_group_add($group_params);
            if ($newgroup['is_error'] == 0) {
                $group_id[0] = $newgroup['result'];
            }
        } else {
            $group_id = array_keys($groups);
        }
        
		// get petition info
		$petitionParams['id'] = $params['sid'];
		$petitionInfo = array();
		CRM_Campaign_BAO_Survey::retrieve($petitionParams,$petitionInfo);
		if (empty($petitionInfo)) {
			CRM_Core_Error::fatal( 'Petition doesn\'t exist.' );
		} 	

		require_once 'CRM/Core/BAO/Domain.php';
		//get the default domain email address.
		list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );		

		require_once 'CRM/Core/BAO/MailSettings.php';
		$emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();
		
		require_once 'CRM/Contact/BAO/Contact.php';
		$toName = CRM_Contact_BAO_Contact::displayName($params['contactId']);		
		
		$replyTo = "do-not-reply@$emailDomain";

		// set additional general message template params (custom tokens to use in email msg templates)
		// tokens then available in msg template as {$petition.title}, etc		
		$petitionTokens['title'] = $petitionInfo['title'];
		$petitionTokens['petitionId'] = $params['sid'];	
		$tplParams['petition'] = $petitionTokens;
				
		switch ($sendEmailMode) {
			case CRM_Campaign_Form_Petition_Signature::EMAIL_THANK:	

				//add this contact to the CIVICRM_PETITION_CONTACTS group
				require_once 'api/v2/GroupContact.php';
				$params['group_id'] = $group_id[0];
				$params['contact_id'] = $params['contactId'];
				civicrm_group_contact_add($params);
				
				require_once 'CRM/Core/BAO/MessageTemplates.php';
				if ($params['email-Primary']) {
					CRM_Core_BAO_MessageTemplates::sendTemplate(
						array(
							'groupName' => 'msg_tpl_workflow_petition',
							'valueName' => 'petition_sign',
							'contactId' => $params['contactId'],
							'tplParams' => $tplParams,
							'from'    => "\"{$domainEmailName}\" <{$domainEmailAddress}>",
							'toName'  => $toName,
							'toEmail' => $params['email-Primary'],
							'replyTo' => $replyTo,
							'petitionId' => $params['sid'],
							'petitionTitle' => $petitionInfo['title'],
						)
					);
				}							
    			break;
    			
			case CRM_Campaign_Form_Petition_Signature::EMAIL_CONFIRM:
				// create mailing event subscription record for this contact
				// this will allow using a hash key to confirm email address by sending a url link
				require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
				$se = CRM_Mailing_Event_BAO_Subscribe::subscribe( $group_id[0], 
																	$params['email-Primary'] , 
																	$params['contactId'] );			
		
//				require_once 'CRM/Core/BAO/Domain.php';
//				$domain =& CRM_Core_BAO_Domain::getDomain();
				$config = CRM_Core_Config::singleton();
				$localpart   = CRM_Core_BAO_MailSettings::defaultLocalpart();
		
				require_once 'CRM/Utils/Verp.php';
				$replyTo = implode($config->verpSeparator,
								   array($localpart . 'c',
										 $se->contact_id,
										 $se->id,
										 $se->hash)
								  ) . "@$emailDomain";			

				
				$confirmUrl = CRM_Utils_System::url( 'civicrm/petition/confirm',
											  "reset=1&cid={$se->contact_id}&sid={$se->id}&h={$se->hash}&a={$params['activityId']}&p={$params['sid']}",
											  true );				
				$confirmUrlPlainText = CRM_Utils_System::url( 'civicrm/petition/confirm',
											  "reset=1&cid={$se->contact_id}&sid={$se->id}&h={$se->hash}&a={$params['activityId']}&p={$params['sid']}",
											  true,
											  null,
											  false);
		
				// set email specific message template params and assign to tplParams
				$petitionTokens['confirmUrl'] = $confirmUrl;
				$petitionTokens['confirmUrlPlainText'] = $confirmUrlPlainText;
				$tplParams['petition'] = $petitionTokens;		
						
				require_once 'CRM/Core/BAO/MessageTemplates.php';
				if ($params['email-Primary']) {					
					CRM_Core_BAO_MessageTemplates::sendTemplate(
						array(
							'groupName' => 'msg_tpl_workflow_petition',
							'valueName' => 'petition_confirmation_needed',
							'contactId' => $params['contactId'],
							'tplParams' => $tplParams,
							'from'    => "\"{$domainEmailName}\" <{$domainEmailAddress}>",
							'toName'  => $toName,
							'toEmail' => $params['email-Primary'],
							'replyTo' => $replyTo,
							'petitionId' => $params['sid'],
							'petitionTitle' => $petitionInfo['title'],
							'confirmUrl' => $confirmUrl
						)
					);					
				}						
    			break;    			
    	}
	}



}


