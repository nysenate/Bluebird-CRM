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
require_once 'CRM/Core/DAO/MessageTemplates.php';


class CRM_Core_BAO_MessageTemplates extends CRM_Core_DAO_MessageTemplates 
{
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_BAO_MessageTemplates object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $messageTemplates = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->copyValues( $params );
        if ( $messageTemplates->find( true ) ) {
            CRM_Core_DAO::storeValues( $messageTemplates, $defaults );
            return $messageTemplates;
        }
        return null;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_MessageTemplates', $id, 'is_active', $is_active );
    }

    /**
     * function to add the Message Templates
     *
     * @param array $params reference array contains the values submitted by the form
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add( &$params ) 
    {
        $params['is_active']            =  CRM_Utils_Array::value( 'is_active', $params, false );

        $messageTemplates               = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->copyValues( $params );
        
        $messageTemplates->save( );
        return $messageTemplates;
    }

    /**
     * function to delete the Message Templates
     *
     * @access public
     * @static 
     * @return object
     */
    static function del( $messageTemplatesID ) 
    {
        // make sure messageTemplatesID is an integer
        if ( ! CRM_Utils_Rule::positiveInteger( $messageTemplatesID ) ) {
            CRM_Core_Error::fatal( ts( 'Invalid Message template' ) );
        }
        
        // set membership_type to null
        $query = "UPDATE civicrm_membership_type
                  SET renewal_msg_id = NULL
                  WHERE renewal_msg_id = %1";
        $params = array( 1 => array( $messageTemplatesID, 'Integer' ) );
        CRM_Core_DAO::executeQuery( $query, $params );
        
        $query = "UPDATE civicrm_mailing
                  SET msg_template_id = NULL
                  WHERE msg_template_id = %1";
        CRM_Core_DAO::executeQuery( $query, $params );
        
        $messageTemplates = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->id = $messageTemplatesID;
        $messageTemplates->delete();
        CRM_Core_Session::setStatus( ts('Selected message templates has been deleted.') );
    }
    
    /**
     * function to get the Message Templates
     *
     * @access public
     * @static 
     * @return object
     */
    static function getMessageTemplates( $all = true) {
        $msgTpls =array();

        $messageTemplates = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->is_active = 1;
        
        if ( ! $all ) {
            $messageTemplates->workflow_id = 'NULL';
        } 
        $messageTemplates->find();
        while ( $messageTemplates->fetch() ) {
            $msgTpls[$messageTemplates->id] = $messageTemplates->msg_title;
        }
        asort($msgTpls);
        return $msgTpls;
    }

    static function sendReminder( $contactId, $email, $messageTemplateID ,$from) {
        require_once "CRM/Core/BAO/Domain.php";
        require_once "CRM/Utils/String.php";
        require_once "CRM/Utils/Token.php";

        $messageTemplates = new CRM_Core_DAO_MessageTemplates( );
        $messageTemplates->id = $messageTemplateID;

        $domain = CRM_Core_BAO_Domain::getDomain( );
        $result = null;
        $hookTokens = array();
        
        if ( $messageTemplates->find(true) ) {
            $body_text = $messageTemplates->msg_text;
            $body_html = $messageTemplates->msg_html;
            $body_subject = $messageTemplates->msg_subject;
            if (!$body_text) {
                $body_text = CRM_Utils_String::htmlToText($body_html);
            }
            
            $params  = array( 'contact_id' => $contactId );
            require_once 'api/v2/Contact.php';
            $contact =& civicrm_contact_get( $params );

            //CRM-4524
            $contact = reset( $contact );
            
            if ( !$contact || is_a( $contact, 'CRM_Core_Error' ) ) {
                return null;
            }

            //CRM-5734
            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::tokenValues( $contact, $contactId );
            
            CRM_Utils_Hook::tokens( $hookTokens );
            $categories = array_keys( $hookTokens );
            
            $type = array('html', 'text');
            
            foreach( $type as $key => $value ) {
                require_once 'CRM/Mailing/BAO/Mailing.php';
                $dummy_mail = new CRM_Mailing_BAO_Mailing();
                $bodyType = "body_{$value}";
                $dummy_mail->$bodyType = $$bodyType;
                $tokens = $dummy_mail->getTokens();
                
                if ( $$bodyType ) {
                    $$bodyType = CRM_Utils_Token::replaceDomainTokens($$bodyType, $domain, true, $tokens[$value], true );
                    $$bodyType = CRM_Utils_Token::replaceContactTokens($$bodyType, $contact, false, $tokens[$value], false, true );
                    $$bodyType = CRM_Utils_Token::replaceComponentTokens($$bodyType, $contact, $tokens[$value], true );
                    $$bodyType = CRM_Utils_Token::replaceHookTokens ( $$bodyType, $contactId , $categories, true );
                }
            }
            $html = $body_html;
            $text = $body_text;
            
            require_once 'CRM/Core/Smarty/resources/String.php';
            civicrm_smarty_register_string_resource( );
            $smarty =& CRM_Core_Smarty::singleton( );
            foreach( array( 'text', 'html') as $elem) {
                $$elem = $smarty->fetch("string:{$$elem}");
            }
            
            $message = new Mail_mime("\n");
            
            /* Do contact-specific token replacement in text mode, and add to the
             * message if necessary */
            if ( !$html || $contact['preferred_mail_format'] == 'Text' ||
                 $contact['preferred_mail_format'] == 'Both') 
                {
                    // render the &amp; entities in text mode, so that the links work
                    $text = str_replace('&amp;', '&', $text);
                    $message->setTxtBody($text);
                    
                    unset( $text );
                }
            
            if ($html && ( $contact['preferred_mail_format'] == 'HTML' ||
                           $contact['preferred_mail_format'] == 'Both'))
                {
                    $message->setHTMLBody($html);
                    
                    unset( $html );
                }
            $recipient = "\"{$contact['display_name']}\" <$email>";
            
            $matches = array();
            preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                            $body_subject,
                            $matches,
                            PREG_PATTERN_ORDER);
            
            $subjectToken = null;
            if ( $matches[1] ) {
                foreach ( $matches[1] as $token ) {
                    list($type,$name) = preg_split( '/\./', $token, 2 );
                    if ( $name ) {
                        if ( ! isset( $subjectToken['contact'] ) ) {
                            $subjectToken['contact'] = array( );
                        }
                        $subjectToken['contact'][] = $name;
                    }
                }
            }
            
            $messageSubject = CRM_Utils_Token::replaceContactTokens($body_subject, $contact, false, $subjectToken);
            $messageSubject = CRM_Utils_Token::replaceDomainTokens($messageSubject, $domain, true, $tokens[$value] );
            $messageSubject = CRM_Utils_Token::replaceComponentTokens($$bodyType, $contact, $tokens[$value], true );
            $messageSubject = CRM_Utils_Token::replaceHookTokens ( $messageSubject,$contactId , $categories, true );
          
            $messageSubject = $smarty->fetch("string:{$messageSubject}");

            $headers = array(
                             'From'      => $from,
                             'Subject'   => $messageSubject,
                             );
            $headers['To'] = $recipient;
            
            $mailMimeParams = array(
                                    'text_encoding' => '8bit',
                                    'html_encoding' => '8bit',
                                    'head_charset'  => 'utf-8',
                                    'text_charset'  => 'utf-8',
                                    'html_charset'  => 'utf-8',
                                    );
            $message->get($mailMimeParams);
            $message->headers($headers);

            $config = CRM_Core_Config::singleton();
            $mailer =& $config->getMailer();
            
            $body = $message->get();
            $headers = $message->headers();
            
            CRM_Core_Error::ignoreException( );
            $result = $mailer->send($recipient, $headers, $body);
            CRM_Core_Error::setCallback();
        }

        $messageTemplates->free( );
        
        return $result;
    }

    /**
     * Revert a message template to its default subject+text+HTML state
     *
     * @param integer id  id of the template
     *
     * @return void
     */
    static function revert($id)
    {
        $diverted = new self;
        $diverted->id = (int) $id;
        $diverted->find(1);

        if ($diverted->N != 1) {
            CRM_Core_Error::fatal(ts('Did not find a message template with id of %1.', array(1 => $id)));
        }

        $orig = new self;
        $orig->workflow_id = $diverted->workflow_id;
        $orig->is_reserved = 1;
        $orig->find(1);

        if ($orig->N != 1) {
            CRM_Core_Error::fatal(ts('Message template with id of %1 does not have a default to revert to.', array(1 => $id)));
        }

        $diverted->msg_subject = $orig->msg_subject;
        $diverted->msg_text    = $orig->msg_text;
        $diverted->msg_html    = $orig->msg_html;
        $diverted->save();
    }

    /**
     * Send an email from the specified template based on an array of params
     *
     * @param array $params  a string-keyed array of function params, see function body for details
     *
     * @return array  of four parameters: a boolean whether the email was sent, and the subject, text and HTML templates
     */
    static function sendTemplate($params)
    {
        $defaults = array(
            'groupName'   => null,    // option group name of the template
            'valueName'   => null,    // option value name of the template
            'contactId'   => null,    // contact id if contact tokens are to be replaced
            'tplParams'   => array(), // additional template params (other than the ones already set in the template singleton)
            'from'        => null,    // the From: header
            'toName'      => null,    // the recipient’s name
            'toEmail'     => null,    // the recipient’s email - mail is sent only if set
            'cc'          => null,    // the Cc: header
            'bcc'         => null,    // the Bcc: header
            'replyTo'     => null,    // the Reply-To: header
            'attachments' => null,    // email attachments
            'isTest'      => false,   // whether this is a test email (and hence should include the test banner)
            'PDFFilename' => null,    // filename of optional PDF version to add as attachment (do not include path)
        );
        $params = array_merge($defaults, $params);

        if (!$params['groupName'] or !$params['valueName']) {
            CRM_Core_Error::fatal(ts("Message template's option group and/or option value missing."));
        }

        // fetch the three elements from the db based on option_group and option_value names
        $query = 'SELECT msg_subject subject, msg_text text, msg_html html
                  FROM civicrm_msg_template mt
                  JOIN civicrm_option_value ov ON workflow_id = ov.id
                  JOIN civicrm_option_group og ON ov.option_group_id = og.id
                  WHERE og.name = %1 AND ov.name = %2 AND mt.is_default = 1';
        $sqlParams = array(1 => array($params['groupName'], 'String'), 2 => array($params['valueName'], 'String'));
        $dao = CRM_Core_DAO::executeQuery($query, $sqlParams);
        $dao->fetch();

        if (!$dao->N) {
            CRM_Core_Error::fatal(ts('No such message template: option group %1, option value %2.', array(1 => $params['groupName'], 2 => $params['valueName'])));
        }

        $subject = $dao->subject;
        $text    = $dao->text;
        $html    = $dao->html;
        $dao->free( );

        // add the test banner (if requested)
        if ($params['isTest']) {
            $query = "SELECT msg_subject subject, msg_text text, msg_html html
                      FROM civicrm_msg_template mt
                      JOIN civicrm_option_value ov ON workflow_id = ov.id
                      JOIN civicrm_option_group og ON ov.option_group_id = og.id
                      WHERE og.name = 'msg_tpl_workflow_meta' AND ov.name = 'test_preview' AND mt.is_default = 1";
            $testDao = CRM_Core_DAO::executeQuery($query);
            $testDao->fetch();

            $subject = $testDao->subject . $subject;
            $text    = $testDao->text    . $text;
            $html    = preg_replace('/<body(.*)$/im', "<body\\1\n{$testDao->html}", $html);
            $testDao->free( );
        }

        // replace tokens in the three elements (in subject as if it was the text body)
        require_once 'CRM/Utils/Token.php';
        require_once 'CRM/Core/BAO/Domain.php';
        require_once 'api/v2/Contact.php';
        require_once 'CRM/Mailing/BAO/Mailing.php';

        $domain = CRM_Core_BAO_Domain::getDomain();
        $hookTokens = array();
        $mailing = new CRM_Mailing_BAO_Mailing;
        $mailing->body_text = $text;
        $mailing->body_html = $html;
        $tokens = $mailing->getTokens();
        CRM_Utils_Hook::tokens( $hookTokens );
        $categories = array_keys( $hookTokens );
        
        if ($params['contactId']) {
            $contactParams = array('contact_id' => $params['contactId']);
            $returnProperties = array( );

            if ( isset( $tokens['text']['contact'] ) ) {
                foreach ( $tokens['text']['contact'] as $name ) {
                    $returnProperties[$name] = 1;
                }
            }

            if ( isset( $tokens['html']['contact'] ) ) {
                foreach ( $tokens['html']['contact'] as $name ) {
                    $returnProperties[$name] = 1;
                }
            }
            list( $contact ) = $mailing->getDetails($contactParams, $returnProperties, false );
            $contact = $contact[$params['contactId']];
            
            
        }

        $subject = CRM_Utils_Token::replaceDomainTokens($subject, $domain, true, $tokens['text'], true);
        $text    = CRM_Utils_Token::replaceDomainTokens($text,    $domain, true, $tokens['text'], true);
        $html    = CRM_Utils_Token::replaceDomainTokens($html,    $domain, true, $tokens['html'], true);
        if ($params['contactId']) {
            $subject = CRM_Utils_Token::replaceContactTokens($subject, $contact, false, $tokens['text'], false, true);
            $text    = CRM_Utils_Token::replaceContactTokens($text,    $contact, false, $tokens['text'], false, true);
            $html    = CRM_Utils_Token::replaceContactTokens($html,    $contact, false, $tokens['html'], false, true);
            CRM_Utils_Hook::tokenValues( $contact[$params['contactId']], $params['contactId']);
            $subject = CRM_Utils_Token::replaceHookTokens ( $subject,$contact[$params['contactId']] , $categories, true );
            $text = CRM_Utils_Token::replaceHookTokens ( $text,$contact[$params['contactId']] , $categories, true );
            $html = CRM_Utils_Token::replaceHookTokens ( $html,$contact[$params['contactId']] , $categories, true );
      
        }

        // strip whitespace from ends and turn into a single line
        $subject = "{strip}$subject{/strip}";

        // parse the three elements with Smarty
        require_once 'CRM/Core/Smarty/resources/String.php';
        civicrm_smarty_register_string_resource();
        $smarty =& CRM_Core_Smarty::singleton();
        foreach ($params['tplParams'] as $name => $value) {
            $smarty->assign($name, $value);
        }
        foreach (array('subject', 'text', 'html') as $elem) {
            $$elem = $smarty->fetch("string:{$$elem}");
        }

        // send the template, honouring the target user’s preferences (if any)
        $sent = false;

        // create the params array
        $params['subject'] = $subject;
        $params['text'   ] = $text;
        $params['html'   ] = $html;

        if ($params['toEmail']) {
            $contactParams = array('email' => $params['toEmail']);
            $contact =& civicrm_contact_get($contactParams);
            $prefs = array_pop($contact);

            if ( isset($prefs['preferred_mail_format']) and $prefs['preferred_mail_format'] == 'HTML' ) {
                $params['text'] = null;
            }

            if ( isset($prefs['preferred_mail_format']) and $prefs['preferred_mail_format'] == 'Text' ) {
                $params['html'] = null;
            }

            $pdf_filename = '';
            if ( $params['PDFFilename'] && $params['html'] ) {
                require_once 'CRM/Utils/PDF/Utils.php';
                require_once 'CRM/Utils/File.php';
                $config = CRM_Core_Config::singleton();
                $pdf_filename = $config->templateCompileDir . CRM_Utils_File::makeFileName( $params['PDFFilename'] );
                file_put_contents( $pdf_filename, CRM_Utils_PDF_Utils::html2pdf( $params['html'],
                                                                                 $params['PDFFilename'],
                                                                                 null,
                                                                                 null,
                                                                                 true
                                                                               )
                                 );
                                 
			    if ( empty( $params['attachments'] ) ) {
			        $params['attachments'] = array();
			    }
			    $params['attachments'][] = array(
			        'fullPath' => $pdf_filename,
			        'mime_type' => 'application/pdf',
			        'cleanName' => $params['PDFFilename'],
			    );
            }
            
            require_once 'CRM/Utils/Mail.php';
            $sent = CRM_Utils_Mail::send( $params );

            if ( $pdf_filename ) {
                unlink($pdf_filename);
            }
        }

        return array($sent, $subject, $text, $html);
    }
}
