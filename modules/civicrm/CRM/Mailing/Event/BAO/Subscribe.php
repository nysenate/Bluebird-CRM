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


require_once 'Mail/mime.php';
require_once 'CRM/Utils/Mail.php';
require_once 'CRM/Utils/Verp.php';

require_once 'CRM/Mailing/Event/DAO/Subscribe.php';

class CRM_Mailing_Event_BAO_Subscribe extends CRM_Mailing_Event_DAO_Subscribe {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Register a subscription event.  Create a new contact if one does not
     * already exist.
     *
     * @param int $group_id         The group id to subscribe to
     * @param string $email         The email address of the (new) contact
     * @params int $contactId       Currently used during event registration/contribution. 
     *                              Specifically to avoid linking group to wrong duplicate contact 
     *                              during event registration.
     * @return int|null $se_id      The id of the subscription event, null on failure
     * @access public
     * @static
     */
    public static function &subscribe( $group_id, $email , $contactId = null , $context = null) {
        // CRM-1797 - allow subscription only to public groups
        $params = array('id' => (int) $group_id);
        $defaults = array();
        $contact_id = null;
        $success    = null;

        require_once 'CRM/Contact/BAO/Group.php';
        $bao = CRM_Contact_BAO_Group::retrieve($params, $defaults);
        if ( $bao && substr($bao->visibility, 0, 6) != 'Public' && $context != 'profile') {
          return $success;
        }
        
        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        $email = $strtolower( $email );

        // process the query only if no contactId
        if ( $contactId ) {
            $contact_id = $contactId;
        } else {
            /* First, find out if the contact already exists */  
            $query = "
   SELECT DISTINCT contact_a.id as contact_id 
     FROM civicrm_contact contact_a 
LEFT JOIN civicrm_email      ON contact_a.id = civicrm_email.contact_id
    WHERE civicrm_email.email = %1";
            
            $params = array( 1 => array( $email, 'String' ) );
            $dao =& CRM_Core_DAO::executeQuery( $query, $params );
            $id = array( );
            // lets just use the first contact id we got
            if ( $dao->fetch( ) ) {
                $contact_id = $dao->contact_id;
            }
            $dao->free( );
        }

        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );

        if ( ! $contact_id ) {
            require_once 'CRM/Core/BAO/LocationType.php';
            require_once 'api/v2/utils.v2.php';
            /* If the contact does not exist, create one. */
            $formatted = array('contact_type' => 'Individual');
            $locationType = CRM_Core_BAO_LocationType::getDefault( );
            $value = array('email' => $email,
                           'location_type_id' => $locationType->id );
            _civicrm_add_formatted_param($value, $formatted);
            
            require_once 'CRM/Import/Parser.php';
            $formatted['onDuplicate'] = CRM_Import_Parser::DUPLICATE_SKIP;
            $formatted['fixAddress'] = true;
            require_once 'api/v2/Contact.php';
            $contact =& civicrm_contact_format_create($formatted);
            if ( civicrm_error( $contact ) ) {
                return $success;
            }
            $contact_id = $contact['id'];
        } else if ( ! is_numeric( $contact_id ) &&
                    (int ) $contact_id > 0 ) {
            // make sure contact_id is numeric
            return $success;
        }

        require_once 'CRM/Core/BAO/Email.php';
        require_once 'CRM/Core/BAO/Location.php';
        require_once 'CRM/Contact/BAO/Contact.php';

        /* Get the primary email id from the contact to use as a hash input */
        $dao = new CRM_Core_DAO();

        $query = "
SELECT     civicrm_email.id as email_id
  FROM     civicrm_email
     WHERE civicrm_email.email = %1
       AND civicrm_email.contact_id = %2";
        $params = array( 1 => array( $email     , 'String'  ),
                         2 => array( $contact_id, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        if ( ! $dao->fetch( ) ) {
            CRM_Core_Error::fatal( 'Please file an issue with the backtrace' );
            return $success;
        }

        $se = new CRM_Mailing_Event_BAO_Subscribe();
        $se->group_id = $group_id;
        $se->contact_id = $contact_id;
        $se->time_stamp = date('YmdHis');
        $se->hash = substr( sha1( "{$group_id}:{$contact_id}:{$dao->email_id}:" . time( ) ),
                            0, 16 );
        $se->save();
        
        $contacts = array($contact_id);
        require_once 'CRM/Contact/BAO/GroupContact.php'; 
        CRM_Contact_BAO_GroupContact::addContactsToGroup($contacts, $group_id,
                                                             'Email', 'Pending', $se->id);

        $transaction->commit( );
        return $se;
    }

    /**
     * Verify the hash of a subscription event
     * 
     * @param int $contact_id       ID of the contact
     * @param int $subscribe_id     ID of the subscription event
     * @param string $hash          Hash to verify
     *
     * @return object|null          The subscribe event object, or null on failure
     * @access public
     * @static
     */
    public static function &verify($contact_id, $subscribe_id, $hash) {
        $success = null;
        $se = new CRM_Mailing_Event_BAO_Subscribe();
        $se->contact_id = $contact_id;
        $se->id = $subscribe_id;
        $se->hash = $hash;
        if ($se->find(true)) {
            $success = $se;
        }
        return $success;
    }

    /**
     * Ask a contact for subscription confirmation (opt-in)
     *
     * @param string $email         The email address
     * @return void
     * @access public
     */
    public function send_confirm_request($email) {
        $config = CRM_Core_Config::singleton();

        require_once 'CRM/Core/BAO/Domain.php';
        $domain =& CRM_Core_BAO_Domain::getDomain();
        
        //get the default domain email address.
        list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );
       
        require_once 'CRM/Core/BAO/MailSettings.php';
        $localpart   = CRM_Core_BAO_MailSettings::defaultLocalpart();
        $emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();

        require_once 'CRM/Utils/Verp.php';
        $confirm = implode($config->verpSeparator,
                           array($localpart . 'c',
                                 $this->contact_id,
                                 $this->id,
                                 $this->hash)
                          ) . "@$emailDomain";
        
        require_once 'CRM/Contact/BAO/Group.php';
        $group = new CRM_Contact_BAO_Group();
        $group->id = $this->group_id;
        $group->find(true);
        
        require_once 'CRM/Mailing/BAO/Component.php';
        $component = new CRM_Mailing_BAO_Component();
        $component->is_default = 1;
        $component->is_active = 1;
        $component->component_type = 'Subscribe';
        
        $component->find(true);
        
        $headers = array(
                         'Subject'   => $component->subject,
                         'From'      => "\"{$domainEmailName}\" <{$domainEmailAddress}>",
                         'To'        => $email,
                         'Reply-To'  => $confirm,
                         'Return-Path'   => "do-not-reply@$emailDomain"
                         );
        
        $url = CRM_Utils_System::url( 'civicrm/mailing/confirm',
                                      "reset=1&cid={$this->contact_id}&sid={$this->id}&h={$this->hash}",
                                      true );
        
        $html = $component->body_html;

        if ($component->body_text) {
            $text = $component->body_text;
        } else {
            $text = CRM_Utils_String::htmlToText($component->body_html);
        }

        require_once 'CRM/Mailing/BAO/Mailing.php';
        $bao = new CRM_Mailing_BAO_Mailing();
        $bao->body_text = $text;
        $bao->body_html = $html;
        $tokens = $bao->getTokens();

        require_once 'CRM/Utils/Token.php';
        $html = CRM_Utils_Token::replaceDomainTokens($html, $domain, true, $tokens['html'] );
        $html = CRM_Utils_Token::replaceSubscribeTokens($html, 
                                                        $group->title,
                                                        $url, true);
        
        $text = CRM_Utils_Token::replaceDomainTokens($text, $domain, false, $tokens['text'] );
        $text = CRM_Utils_Token::replaceSubscribeTokens($text, 
                                                        $group->title,
                                                        $url, false);
        // render the &amp; entities in text mode, so that the links work
        $text = str_replace('&amp;', '&', $text);

        $message = new Mail_mime("\n");

        $message->setHTMLBody($html);
        $message->setTxtBody($text);
        $b =& CRM_Utils_Mail::setMimeParams( $message );
        $h =& $message->headers($headers);
        $mailer =& $config->getMailer();

        require_once 'CRM/Mailing/BAO/Mailing.php';
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK,
                               array('CRM_Core_Error', 'nullHandler' ) );
        if ( is_object( $mailer ) ) {
            $mailer->send($email, $h, $b);
            CRM_Core_Error::setCallback();
        }
    }

    /**
     * Get the domain object given a subscribe event
     * 
     * @param int $subscribe_id     ID of the subscribe event
     * @return object $domain       The domain owning the event
     * @access public
     * @static
     */
    public static function &getDomain($subscribe_id) {
        return CRM_Core_BAO_Domain::getDomain( );
    }

    /**
     * Get the group details to which given email belongs
     * 
     * @param string $email     email of the contact
     * @param int    $contactID contactID if we want an exact match
     *
     * @return array $groups    array of group ids
     * @access public
     */
    function getContactGroups($email, $contactID = null) {
        if ( $contactID ) {
            $query = "
                 SELECT DISTINCT group_a.group_id, group_a.status, civicrm_group.title 
                 FROM civicrm_group_contact group_a
                 LEFT JOIN civicrm_group ON civicrm_group.id = group_a.group_id
                 LEFT JOIN civicrm_contact ON ( group_a.contact_id = civicrm_contact.id )
                 WHERE civicrm_contact.id = %1";
        
            $params = array( 1 => array( $contactID, 'Integer' ) );
        } else {
            $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
            $email = $strtolower( $email );

            $query = "
                 SELECT DISTINCT group_a.group_id, group_a.status, civicrm_group.title 
                 FROM civicrm_group_contact group_a
                 LEFT JOIN civicrm_group ON civicrm_group.id = group_a.group_id
                 LEFT JOIN civicrm_contact ON ( group_a.contact_id = civicrm_contact.id )
                 LEFT JOIN civicrm_email ON civicrm_contact.id = civicrm_email.contact_id
                 WHERE civicrm_email.email = %1";
        
            $params = array( 1 => array( $email, 'String' ) );
        }

        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        $groups = array();
        while ( $dao->fetch( ) ) {
            $groups[$dao->group_id] = array(
                                            'id'      => $dao->group_id,
                                            'title'   => $dao->title,
                                            'status'  => $dao->status
                                            );
        }
        
        $dao->free( );
        return $groups;
    }

    /**
     * Function to send subscribe mail
     * @params  array  $groups the list of group ids for subscribe
     * @params  array  $params the list of email
     * @params  int    $contactId  Currently used during event registration/contribution. 
     *                             Specifically to avoid linking group to wrong duplicate contact 
     *                             during event registration.
     * @public
     * @return void
     */
    function commonSubscribe( &$groups, &$params, $contactId = null, $context = null) 
    {
        $contactGroups = CRM_Mailing_Event_BAO_Subscribe::getContactGroups($params['email'], $contactId);
        $group = array( );
        $success = null;
        foreach ( $groups as $groupID ) {
            $title = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group', $groupID, 'title');
            if ( array_key_exists( $groupID, $contactGroups ) && $contactGroups[$groupID]['status'] != 'Removed' ) {
                $group[$groupID]['title']  = $contactGroups[$groupID]['title'];
                
                $group[$groupID]['status'] = $contactGroups[$groupID]['status'];
                $status = ts('You are already subscribed in %1, your subscription is %2.', array(1 => $group[$groupID]['title'], 2 => $group[$groupID]['status']));
                CRM_Utils_System::setUFMessage( $status );
                continue;
            }
            
            $se = self::subscribe( $groupID,
                                   $params['email'], $contactId, $context );
            if ( $se !== null ) { 
                $success       = true;
                $groupAdded[]  = $title;
                
                /* Ask the contact for confirmation */
                $se->send_confirm_request($params['email']);
            } else {
                $success       = false;
                $groupFailed[] = $title;
            }
        }
        if ( $success ) {
            $groupTitle = implode( ',', $groupAdded );
            CRM_Utils_System::setUFMessage(ts('Your subscription request has been submitted for group %1. Check your inbox shortly for the confirmation email(s). If you do not see a confirmation email, please check your spam/junk mail folder.', array(1 => $groupTitle)));
        } else if ( $success === false ) {
            $groupTitle = implode( ',', $groupFailed );
            CRM_Utils_System::setUFMessage(ts('We had a problem processing your subscription request for group %1. You have tried to subscribe to a private group and/or we encountered a database error. Please contact the site administrator.', array(1 => $groupTitle)));
        }
    }//end of function
}


