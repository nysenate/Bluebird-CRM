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

require_once 'Mail/mime.php';
require_once 'CRM/Utils/Mail.php';

require_once 'CRM/Mailing/Event/DAO/Confirm.php';

class CRM_Mailing_Event_BAO_Confirm extends CRM_Mailing_Event_DAO_Confirm {

    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Confirm a pending subscription
     *
     * @param int $contact_id       The id of the contact
     * @param int $subscribe_id     The id of the subscription event
     * @param string $hash          The hash
     * @return boolean              True on success
     * @access public
     * @static
     */
    public static function confirm($contact_id, $subscribe_id, $hash) 
    {
        require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
        $se =& CRM_Mailing_Event_BAO_Subscribe::verify($contact_id,
                                                       $subscribe_id, $hash);
        
        if (! $se) {
            return false;
        }
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $ce = new CRM_Mailing_Event_BAO_Confirm();
        $ce->event_subscribe_id = $se->id;
        $ce->time_stamp = date('YmdHis');
        $ce->save();
        
        require_once 'CRM/Contact/BAO/GroupContact.php';
        CRM_Contact_BAO_GroupContact::updateGroupMembershipStatus( $contact_id, $se->group_id,
                                                                   'Email',$ce->id);
        
        $transaction->commit( );
        
        $config = CRM_Core_Config::singleton();
        
        require_once 'CRM/Core/BAO/Domain.php';
        $domain =& CRM_Core_BAO_Domain::getDomain( );
        list($domainEmailName, $_) = CRM_Core_BAO_Domain::getNameAndEmail();
        
        require_once 'CRM/Contact/BAO/Contact/Location.php';
        list($display_name, $email) =
            CRM_Contact_BAO_Contact_Location::getEmailDetails($se->contact_id);
        
        require_once 'CRM/Contact/DAO/Group.php';
        $group = new CRM_Contact_DAO_Group();
        $group->id = $se->group_id;
        $group->find(true);
        
        require_once 'CRM/Mailing/BAO/Component.php';
        $component = new CRM_Mailing_BAO_Component();
        $component->is_default = 1;
        $component->is_active = 1;
        $component->component_type = 'Welcome';
        
        $component->find(true);

        require_once 'CRM/Core/BAO/MailSettings.php';
        $emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();

        $headers = array(
                         'Subject'     => $component->subject,
                         'From'        => "\"$domainEmailName\" <do-not-reply@$emailDomain>",
                         'To'          => $email,
                         'Reply-To'    => "do-not-reply@$emailDomain",
                         'Return-Path' => "do-not-reply@$emailDomain",
                         );
        
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
        $html = CRM_Utils_Token::replaceWelcomeTokens($html, $group->title, true);
        
        $text = CRM_Utils_Token::replaceDomainTokens($text, $domain, false, $tokens['text'] );
        $text = CRM_Utils_Token::replaceWelcomeTokens($text, $group->title, false);
        
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
        return $group->title;
    }
}


