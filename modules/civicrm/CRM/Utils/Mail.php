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


class CRM_Utils_Mail
{
    /**
     * Wrapper function to send mail in CiviCRM. Hooks are called from this function. The input parameter
     * is an associateive array which holds the values of field needed to send an email. These are:
     *
     * from    : complete from envelope
     * toName  : name of person to send email
     * toEmail : email address to send to
     * cc      : email addresses to cc
     * bcc     : email addresses to bcc
     * subject : subject of the email
     * text    : text of the message
     * html    : html version of the message
     * replyTo : reply-to header in the email
     * attachments: an associative array of
     *   fullPath : complete pathname to the file
     *   mime_type: mime type of the attachment
     *   cleanName: the user friendly name of the attachmment
     *
     * @param array $params (by reference)
     * 
     * @access public
     * @return boolean true if a mail was sent, else false
     */
    static function send( &$params ) {
        require_once 'CRM/Core/BAO/MailSettings.php';
        $returnPath = CRM_Core_BAO_MailSettings::defaultReturnPath();
        $from       = CRM_Utils_Array::value( 'from', $params );
        if ( ! $returnPath ) {
            $returnPath = self::pluckEmailFromHeader($from);
        }
        $params['returnPath'] = $returnPath;

        // first call the mail alter hook
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::alterMailParams( $params );

        // check if any module has aborted mail sending
        if ( CRM_Utils_Array::value( 'abortMailSend', $params ) ||
             ! CRM_Utils_Array::value( 'toEmail', $params ) ) {
            return false;
        }

        $textMessage = CRM_Utils_Array::value( 'text'       , $params );
        $htmlMessage = CRM_Utils_Array::value( 'html'       , $params );
        $attachments = CRM_Utils_Array::value( 'attachments', $params );

        // CRM-6224
        if (trim(CRM_Utils_String::htmlToText($htmlMessage)) == '') {
            $htmlMessage = false;
        }

        $headers = array( );  
        $headers['From']                      = $params['from'];
        $headers['To']                        = "{$params['toName']} <{$params['toEmail']}>";
        $headers['Cc']                        = CRM_Utils_Array::value( 'cc', $params );
        $headers['Bcc']                       = CRM_Utils_Array::value( 'bcc', $params );
        $headers['Subject']                   = CRM_Utils_Array::value( 'subject', $params );
        $headers['Content-Type']              = $htmlMessage ? 'multipart/mixed; charset=utf-8' : 'text/plain; charset=utf-8';
        $headers['Content-Disposition']       = 'inline';  
        $headers['Content-Transfer-Encoding'] = '8bit';  
        $headers['Return-Path']               = CRM_Utils_Array::value( 'returnPath', $params );
        $headers['Reply-To']                  = CRM_Utils_Array::value( 'replyTo', $params, $from );
        $headers['Date']                      = date('r');
        if (CRM_Utils_Array::value( 'autoSubmitted', $params )) {
          $headers['Auto-Submitted']          = "Auto-Generated";
        }
        
        //make sure we has to have space, CRM-6977
        foreach ( array( 'From', 'To', 'Cc', 'Bcc', 'Reply-To', 'Return-Path' ) as $fld ) {
            $headers[$fld] = str_replace( '"<', '" <', $headers[$fld] );
        }

        // quote FROM, if comma is detected AND is not already quoted. CRM-7053
        if ( strpos( $headers['From'], ',' )  !== false ) {
            $from = explode( ' <', $headers['From'] );
            if ( substr( $from[0],  0,  1 ) != '"' || 
                 substr( $from[0], -1,  1 ) != '"' ) {
                $from[0] = str_replace( '"', '\"', $from[0] );
                $headers['From'] = "\"{$from[0]}\" <{$from[1]}";
            }
        }

        require_once 'Mail/mime.php';
        $msg = new Mail_mime("\n");
        if ( $textMessage ) {
            $msg->setTxtBody($textMessage);
        }

        if ( $htmlMessage ) {
            $msg->setHTMLBody($htmlMessage);
        }

        if ( ! empty( $attachments ) ) {
            foreach ( $attachments as $fileID => $attach ) {
                $msg->addAttachment( $attach['fullPath'],
                                     $attach['mime_type'],
                                     $attach['cleanName'] );
            }
        }
        
        $message =& self::setMimeParams( $msg );
        $headers =& $msg->headers($headers);
        
        $to = array( $params['toEmail'] );

        //get emails from headers, since these are 
        //combination of name and email addresses.
        if ( CRM_Utils_Array::value( 'Cc', $headers ) ) {
            $to[] = CRM_Utils_Array::value( 'Cc', $headers );
        }
        if ( CRM_Utils_Array::value( 'Bcc', $headers ) ) {
            $to[] = CRM_Utils_Array::value( 'Bcc', $headers );
            unset( $headers['Bcc'] );
        }
        
        $result = null;
        $mailer =& CRM_Core_Config::getMailer( );
        CRM_Core_Error::ignoreException( );
        if ( is_object( $mailer ) ) {
            $result = $mailer->send($to, $headers, $message);
            CRM_Core_Error::setCallback();
            if ( is_a( $result, 'PEAR_Error' ) ) {
                $message = self::errorMessage ($mailer, $result );
                CRM_Core_Session::setStatus( $message, false );
                return false;
            }
            return true;
        }
        return false;
    }

    static function errorMessage( $mailer, $result ) {
        $message =
        '<p>'  . ts('An error occurred when CiviCRM attempted to send an email (via %1). If you received this error after submitting on online contribution or event registration - the transaction was completed, but we were unable to send the email receipt.', array(1 => 'SMTP')) . '</p>' .
        '<p>'  . ts('The mail library returned the following error message:') . '<br /><span class="font-red"><strong>' . $result->getMessage() . '</strong></span></p>' .
        '<p>'  . ts('This is probably related to a problem in your Outbound Email Settings (Administer CiviCRM &raquo; Global Settings &raquo; Outbound Email), OR the FROM email address specifically configured for your contribution page or event. Possible causes are:') . '</p>';

        if ( is_a( $mailer , 'Mail_smtp' ) ) {
            $message .=
            '<ul>' .
            '<li>' . ts('Your SMTP Username or Password are incorrect.')                                                                            . '</li>' .
            '<li>' . ts('Your SMTP Server (machine) name is incorrect.')                                                                            . '</li>' .
            '<li>' . ts('You need to use a Port other than the default port 25 in your environment.')                                               . '</li>' .
            '<li>' . ts('Your SMTP server is just not responding right now (it is down for some reason).')                                          . '</li>';
        } else {
            $message .=
            '<ul>' .
            '<li>' . ts('Your Sendmail path is incorrect.')     . '</li>' .
            '<li>' . ts('Your Sendmail argument is incorrect.') . '</li>';
        }
        
        $message .=
            '<li>' . ts('The FROM Email Address configured for this feature may not be a valid sender based on your email service provider rules.') . '</li>' .
            '</ul>' .
            '<p>' . ts('Check <a href="%1">this page</a> for more information.', array(1 => CRM_Utils_System::docURL2('Outbound Email (SMTP)', true))) . '</p>';
        
        return $message;
    }
    
    function logger( &$to, &$headers, &$message ) {
        if ( is_array( $to ) ) {
            $toString = implode( ', ', $to ); 
            $fileName = $to[0];
        } else {
            $toString = $fileName = $to;
        }
        $content = "To: " . $toString . "\n";
        foreach ( $headers as $key => $val ) {
            $content .= "$key: $val\n";
        }
        $content .= "\n" . $message . "\n";

        if ( is_numeric( CIVICRM_MAIL_LOG ) ) {
            $config = CRM_Core_Config::singleton( );
            // create the directory if not there
            $dirName = $config->configAndLogDir . 'mail' . DIRECTORY_SEPARATOR;
            CRM_Utils_File::createDir( $dirName );
            $fileName = md5( uniqid( CRM_Utils_String::munge( $fileName ) ) ) . '.txt';
            file_put_contents( $dirName . $fileName,
                               $content );
        } else {
            file_put_contents( CIVICRM_MAIL_LOG, $content, FILE_APPEND );
        }
    }

    /**
     * Get the email address itself from a formatted full name + address string
     *
     * Ugly but working.
     *
     * @param  string $header  the full name + email address string
     * @return string          the plucked email address
     */
    function pluckEmailFromHeader($header) {
        preg_match('/<([^<]*)>$/', $header, $matches);
        return $matches[1];
    }
    
    /**
     * Get the Active outBound email 
     * @return boolean true if valid outBound email configuration found, false otherwise
     * @access public
     * @static
     */
    static function validOutBoundMail() {
        require_once "CRM/Core/BAO/Preferences.php";
        $mailingInfo =& CRM_Core_BAO_Preferences::mailingPreferences();
        if ( $mailingInfo['outBound_option'] == 3 ) {
           return true;
        } else  if ( $mailingInfo['outBound_option'] == 0 ) {
            if ( !isset( $mailingInfo['smtpServer'] ) || $mailingInfo['smtpServer'] == '' || 
                 $mailingInfo['smtpServer'] == 'YOUR SMTP SERVER'|| 
                 ( $mailingInfo['smtpAuth'] && ( $mailingInfo['smtpUsername'] == '' || $mailingInfo['smtpPassword'] == '' ) ) ) {
                return false;
            }
            return true;
        } else if ( $mailingInfo['outBound_option'] == 1 ) {
            if ( ! $mailingInfo['sendmail_path'] || ! $mailingInfo['sendmail_args'] ) {
                return false;
            }
            return true;
        }
        return false;        
    }

    static function &setMimeParams( &$message, $params = null ) {
        static $mimeParams = null;
        if ( ! $params ) {
            if ( ! $mimeParams ) {
                $mimeParams = array(
                                    'text_encoding' => '8bit',
                                    'html_encoding' => '8bit',
                                    'head_charset'  => 'utf-8',
                                    'text_charset'  => 'utf-8',
                                    'html_charset'  => 'utf-8',
                                    );
            }
            $params = $mimeParams;
        }
        return $message->get( $params );
    }

}


