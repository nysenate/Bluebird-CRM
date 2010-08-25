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

require_once "CRM/Core/BAO/Email.php";

/**
 * This class provides the common functionality for sending email to
 * one or a group of contact ids. This class is reused by all the search
 * components in CiviCRM (since they all have send email as a task)
 */
class CRM_Contact_Form_Task_EmailCommon
{
    const
        MAX_EMAILS_KILL_SWITCH = 50;
    
    public $_contactDetails    = array( );
    public $_allContactDetails = array( );
    public $_toContactEmails   = array( );

    static function preProcessFromAddress( &$form ) 
    {
        $form->_single  = false;
        $className = CRM_Utils_System::getClassName( $form );
        if ( $form->_context != 'search' &&
             $className == 'CRM_Contact_Form_Task_Email' ) {
            $form->_single = true;
        }

        $form->_emails  = $emails = array( );
        
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get( 'userID' );
        
        $form->_contactIds = array( $contactID );
        $contactEmails     = CRM_Core_BAO_Email::allEmails( $contactID );
        
        $form->_onHold     = array( );
        
        $fromDisplayName = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact',
                                                        $contactID,'display_name' );
        
        foreach ( $contactEmails as $emailId => $item ) {
            $email = $item['email'];
            if ( !$email &&
                ( count($emails) <= 1 ) ) {
                $emails[$emailId] = '"' . $fromDisplayName . '"';
                $form->_noEmails = true;
            } else {
                if ( $email ) {
                    if ( in_array( $email, $emails ) ) {
                        // CRM-3624
                        continue;
                    }

                    $emails[$emailId] = '"' . $fromDisplayName . '" <' . $email . '> ';
                    $form->_onHold[$emailId] = $item['on_hold'];
                }
            }

            $form->_emails[$emailId] = $emails[$emailId];
            
            $emails[$emailId] .= $item['locationType'];
            
            if ( $item['is_primary'] ) {
                $emails[$emailId] .= ' ' . ts('(preferred)');
            }
            $emails[$emailId] = htmlspecialchars( $emails[$emailId] );
        }
        
        $form->assign('noEmails', $form->_noEmails);
        
        if ( $form->_noEmails ) {
            CRM_Core_Error::statusBounce( ts('Your user record does not have a valid email address' ));
        }
        
        // now add domain from addresses
        $domainEmails = array( );
        $domainFrom   = CRM_Core_PseudoConstant::fromEmailAddress( );
        foreach ( array_keys( $domainFrom ) as $k ) {
            $domainEmail = $domainFrom[$k];
            $domainEmails[$domainEmail]  = htmlspecialchars( $domainEmail );
            $form->_emails[$domainEmail] = $domainEmail;
        }

        $form->_fromEmails = CRM_Utils_Array::crmArrayMerge( $emails, $domainEmails);
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    static function buildQuickForm( &$form )
    {
		$toArray = $ccArray = $bccArray = array( );
		$suppressedEmails = 0;
        //here we are getting logged in user id as array but we need target contact id. CRM-5988
        $cid = $form->get( 'cid' );
        if( $cid ) {
            $form->_contactIds = array( $cid );
        }
        
        $to  = $form->add( 'text', 'to', ts('To'), '', true );
        $cc  = $form->add( 'text', 'cc_id', ts('CC') );
        $bcc = $form->add( 'text', 'bcc_id', ts('BCC') );
		
        $elements = array( 'cc', 'bcc' );
        foreach ( $elements as $element ) {
            if ( $$element->getValue( ) ) {
                preg_match_all('!"(.*?)"\s+<\s*(.*?)\s*>!', $$element->getValue( ), $matches);
                $elementValues = array( );
                for ( $i=0; $i< count( $matches[0] ); $i++ ) {
                    $name = '"'.$matches[1][$i]. '" &lt;'. $matches[2][$i] .'&gt;';
                    $elementValues[] = array(
                                              'name' => $name,
                                              'id'    => $matches[0][$i]
                                            );
                }

                $var = "{$element}Contact";
                $form->assign( $var, json_encode($elementValues) );
            }
        }
		
        $toSetDefault = true;
        if ( $form->_context == 'standalone' ) {
            $toSetDefault = false;
        }
    	// when form is submitted recompute contactIds
    	$allToEmails = array( );
    	if ( $to->getValue( ) ) {
    	    $allToEmails = explode( ',', $to->getValue( ) );
    	    $form->_contactIds = array( );
    	    foreach( $allToEmails as $value ) {
    	        list( $contactId, $email ) = explode( '::', $value );
    	        if ( $contactId ) {
    	            $form->_contactIds[]      =  $contactId;
    	            $form->_toContactEmails[] = $email;
	            }
    	    }
    	    $toSetDefault = true;
    	}

        //get the group of contacts as per selected by user in case of Find Activities
        if ( !empty( $form->_activityHolderIds ) ) {
            $contact = $form->get( 'contacts' );
            $form->_contactIds = $contact;
        }
        
    	if ( is_array ( $form->_contactIds ) && $toSetDefault ) {
            $returnProperties = array( 'sort_name' => 1, 'email' => 1, 'do_not_email' => 1,
                                       'on_hold' => 1, 'display_name' => 1, 'preferred_mail_format' => 1 );
        
            require_once 'CRM/Mailing/BAO/Mailing.php';
            
            list( $form->_contactDetails ) = CRM_Mailing_BAO_Mailing::getDetails( $form->_contactIds, $returnProperties, false, false );

            // make a copy of all contact details
            $form->_allContactDetails = $form->_contactDetails;
        
            foreach ( $form->_contactIds as $key => $contactId ) {
                $value = $form->_contactDetails[$contactId];
                if ( $value['do_not_email'] || empty( $value['email'] ) || CRM_Utils_Array::value( 'is_deceased', $value ) || $value['on_hold'] ) {
                    $suppressedEmails++;

                    // unset contact details for contacts that we won't be sending email. This is prevent extra computation 
                    // during token evaluation etc.
                    unset( $form->_contactDetails[$contactId] );
                } else {
                    if ( empty( $form->_toContactEmails ) ) {
                        $email = $value['email'];
                    } else {
                        $email = $form->_toContactEmails[$key];
                    }
                    $toArray[] = array( 'name' => '"'. $value['sort_name'] .'" &lt;' .$email .'&gt;',
                                        'id'   => "$contactId::{$email}" );
                }
            }

    		if ( empty( $toArray ) ) {
    			CRM_Core_Error::statusBounce( ts('Selected contact(s) do not have a valid email address, or communication preferences specify DO NOT EMAIL, or they are deceased or Primary email address is On Hold).' ));
    		}
    	}
	
		$form->assign('toContact', json_encode( $toArray ) );
		$form->assign('suppressedEmails', $suppressedEmails);
        
        $form->assign('totalSelectedContacts',count($form->_contactIds));
        
        $form->add('text', 'subject', ts('Subject'), 'size=50 maxlength=254', true);

        $form->add( 'select', 'fromEmailAddress', ts('From'), $form->_fromEmails, true );
        
        require_once "CRM/Mailing/BAO/Mailing.php";
        CRM_Mailing_BAO_Mailing::commonCompose( $form );
        
        // add attachments
        require_once 'CRM/Core/BAO/File.php';
        CRM_Core_BAO_File::buildAttachment( $form, null );

        if ( $form->_single ) {
            // also fix the user context stack
            if ( $form->_caseId ) {
                $ccid = CRM_Core_DAO::getFieldValue( 'CRM_Case_DAO_CaseContact', $form->_caseId,
                                                     'contact_id', 'case_id' );
                $url  = 
                    CRM_Utils_System::url('civicrm/contact/view/case',
                                          "&reset=1&action=view&cid={$ccid}&id={$form->_caseId}");
            } else if ( $form->_context ) { 
                $url = CRM_Utils_System::url( 'civicrm/dashboard', 'reset=1' );  
             } else {
                $url = 
                    CRM_Utils_System::url('civicrm/contact/view',
                                          "&show=1&action=browse&cid={$form->_contactIds[0]}&selectedChild=activity");
            }
            
            $session   = CRM_Core_Session::singleton( );
            $session->replaceUserContext( $url );
            $form->addDefaultButtons( ts('Send Email'), 'upload', 'cancel' );
        } else {
            $form->addDefaultButtons( ts('Send Email'), 'upload' );
        }
        
        $form->addFormRule( array( 'CRM_Contact_Form_Task_EmailCommon', 'formRule' ), $form );
    }

    /** 
     * form rule  
     *  
     * @param array $fields    the input form values  
     * @param array $dontCare   
     * @param array $self      additional values form 'this'  
     *  
     * @return true if no errors, else array of errors  
     * @access public  
     * 
     */  
    static function formRule($fields, $dontCare, $self) 
    {
        $errors = array( );
        $template = CRM_Core_Smarty::singleton( );
        
        if ( isset( $fields['html_message'] ) ) {
            $htmlMessage = str_replace( array("\n","\r"), ' ', $fields['html_message']);
            $htmlMessage = str_replace( '"', '\"', $htmlMessage);
            $template->assign('htmlContent',$htmlMessage );
        }

        //Added for CRM-1393
        if ( CRM_Utils_Array::value( 'saveTemplate', $fields ) && empty( $fields['saveTemplateName'] ) ) {
            $errors['saveTemplateName'] = ts("Enter name to save message template");
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    static function postProcess( &$form ) 
    {
        if ( count( $form->_contactIds ) > self::MAX_EMAILS_KILL_SWITCH ) {
            CRM_Core_Error::fatal( ts( 'Please do not use this task to send a lot of emails (greater than %1). We recommend using CiviMail instead.',
                                       array( 1 => self::MAX_EMAILS_KILL_SWITCH ) ) );
        }

        // check and ensure that 
        $formValues = $form->controller->exportValues( $form->getName( ) );
        
        $fromEmail  = $formValues['fromEmailAddress'];
        $from       = CRM_Utils_Array::value( $fromEmail, $form->_emails );
        $cc         = CRM_Utils_Array::value( 'cc_id' , $formValues );
        $bcc        = CRM_Utils_Array::value( 'bcc_id', $formValues );
        $subject    = $formValues['subject'];

        // CRM-5916: prepend case id hash to CiviCase-originating emails’ subjects
        if ($form->_caseId) {
            $hash = substr(sha1(CIVICRM_SITE_KEY . $form->_caseId), 0, 7);
            $subject = "[case #$hash] $subject";
        }
        
        // process message template
        require_once 'CRM/Core/BAO/MessageTemplates.php';
        if ( CRM_Utils_Array::value( 'saveTemplate', $formValues ) || CRM_Utils_Array::value( 'updateTemplate', $formValues ) ) {
            $messageTemplate = array( 'msg_text'    => $formValues['text_message'],
                                      'msg_html'    => $formValues['html_message'],
                                      'msg_subject' => $formValues['subject'],
                                      'is_active'   => true );
            
            if ( $formValues['saveTemplate'] ) {
                $messageTemplate['msg_title'] = $formValues['saveTemplateName'];
                CRM_Core_BAO_MessageTemplates::add( $messageTemplate );
            }
            
            if ( $formValues['template'] && $formValues['updateTemplate']  ) {
                $messageTemplate['id'] = $formValues['template'];
                unset($messageTemplate['msg_title']);
                CRM_Core_BAO_MessageTemplates::add( $messageTemplate );
            } 
        }

        $attachments = array( );
        CRM_Core_BAO_File::formatAttachment( $formValues,
                                             $attachments,
                                             null, null );
        
        // format contact details array to handle multiple emails from same contact
        $formattedContactDetails = array( );
        $tempEmails = array( );
        
        foreach( $form->_contactIds as $key => $contactId ) {
            $email = $form->_toContactEmails[ $key ];
            // prevent duplicate emails if same email address is selected CRM-4067
            // we should allow same emails for different contacts
            $emailKey = "{$contactId}::{$email}";
            if ( !in_array( $emailKey, $tempEmails ) ) {
                $tempEmails[] = $emailKey; 
                $details          = $form->_contactDetails[$contactId];
                $details['email'] = $email;
                unset( $details['email_id'] );
                $formattedContactDetails[] = $details;
            }
        }

        // send the mail
        require_once 'CRM/Activity/BAO/Activity.php';
        list( $sent, $activityId ) = 
            CRM_Activity_BAO_Activity::sendEmail( $formattedContactDetails,
                                                  $subject,
                                                  $formValues['text_message'],
                                                  $formValues['html_message'],
                                                  null,
                                                  null,
                                                  $from,
                                                  $attachments,
                                                  $cc,
                                                  $bcc,
                                                  array_keys( $form->_contactDetails ) );

        if ( $sent ) {
            $status = array( '', ts('Your message has been sent.') );
        }
                
        //Display the name and number of contacts for those email is not sent.
        $emailsNotSent = array_diff_assoc( $form->_allContactDetails, $form->_contactDetails );
        
        if ( !empty( $emailsNotSent ) ) {
            $statusOnHold  = '';
            $statusDisplay = ts('Email not sent to contact(s) (no email address on file or communication preferences specify DO NOT EMAIL or Contact is deceased or Primary email address is On Hold): %1', array(1 => count($emailsNotSent))) . '<br />' . ts('Details') . ': ';
            foreach( $emailsNotSent as $contactId => $values ) {
                $displayName    = $values['display_name'];
                $email          = $values['email'];
                $contactViewUrl = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$contactId}");
                $statusDisplay .= "<a href='{$contactViewUrl}'>{$displayName}</a>, ";
                
                // build separate status for on hold messages
                if ( $values['on_hold'] ) {
                    $statusOnHold .= ts( 'Email was not sent to %1 because primary email address (%2) is On Hold.',
                                          array( 1 => "<a href='{$contactViewUrl}'>{$displayName}</a>", 2 => "<strong>{$email}</strong>")) . '<br />';
                }
            }
            $status[] = $statusDisplay;
        }
        
        if ( $form->_caseId ) {
            // if case-id is found in the url, create case activity record
            $caseParams = array( 'activity_id' => $activityId,
                                 'case_id'     => $form->_caseId );
            require_once 'CRM/Case/BAO/Case.php';
            CRM_Case_BAO_Case::processCaseActivity( $caseParams );
        }

        if ( strlen($statusOnHold) ) {
            $status[] = $statusOnHold;
        }
        
        CRM_Core_Session::setStatus( $status );
        
    }//end of function
}
