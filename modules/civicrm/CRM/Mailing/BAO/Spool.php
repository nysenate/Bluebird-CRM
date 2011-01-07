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

require_once 'CRM/Mailing/DAO/Spool.php';

class CRM_Mailing_BAO_Spool extends CRM_Mailing_DAO_Spool {
 
    /**
     * class constructor
     */
    
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * Store Mails into Spool table.
     *
     * @param mixed $recipients Either a comma-seperated list of recipients
     *              (RFC822 compliant), or an array of recipients,
     *              each RFC822 valid. This may contain recipients not
     *              specified in the headers, for Bcc:, resending
     *              messages, etc.
     *
     * @param array $headers The string of headers to send with the mail.
     *
     * @param string $body The full text of the message body, including any
     *               Mime parts, etc.
     *
     * @return mixed Returns true on success, or a CRM_Eore_Error
     *               containing a descriptive error message on
     *               failure.
     * @access public
     */

    function send($recipient, $headers, $body, $job_id) {

        $headerStr = array();
        foreach($headers as $name => $value){
          $headerStr[] = "$name: $value";
        }
        $headerStr = implode("\n", $headerStr);
        
        $session = CRM_Core_Session::singleton();
        
        $params = array(
                        'job_id'          => $job_id,
                        'recipient_email' => $recipient,
                        'headers'         => $headerStr,
                        'body'            => $body,
                        'added_at'        => date("YmdHis"),
                        'removed_at'      => null 
                        );

        $spoolMail = new CRM_Mailing_DAO_Spool();
        $spoolMail->copyValues($params);
        $spoolMail->save();
        
        return true;
    }
    
}
