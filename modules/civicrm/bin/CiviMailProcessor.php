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

class CiviMailProcessor {

    /**
     * Delete old files from a given directory (recursively)
     *
     * @param string $dir  directory to cleanup
     * @param int    $age  files older than this many seconds will be deleted (default: 60 days)
     * @return void
     */
    static function cleanupDir($dir, $age = 5184000)
    {
        // return early if we can’t read/write the dir
        if (!is_writable($dir) or !is_readable($dir) or !is_dir($dir)) return;

        foreach (scandir($dir) as $file) {

            // don’t go up the directory stack and skip new files/dirs
            if ($file == '.' or $file == '..')           continue;
            if (filemtime("$dir/$file") > time() - $age) continue;

            // it’s an old file/dir, so delete/recurse
            is_dir("$dir/$file") ? self::cleanupDir("$dir/$file", $age) : unlink("$dir/$file");
        }
    }

    /**
     * Process the mailbox defined by the named set of settings from civicrm_mail_settings
     *
     * @param string $name  name of the set of settings from civicrm_mail_settings (null for default set)
     * @return void
     */
    static function process($name = null) {

        require_once 'CRM/Core/DAO/MailSettings.php';
        $dao = new CRM_Core_DAO_MailSettings;
        $dao->domain_id = CRM_Core_Config::domainID( );
        
        $name ? $dao->name = $name : $dao->is_default = 1;
        if ( ! $dao->find(true) ) {
            throw new Exception("Could not find entry named $name in civicrm_mail_settings");
        }

        $config = CRM_Core_Config::singleton();
        $verpSeperator = preg_quote( $config->verpSeparator );
        $twoDigitStringMin = $verpSeperator . '(\d+)' . $verpSeperator . '(\d+)';
        $twoDigitString    = $twoDigitStringMin . $verpSeperator;
        $threeDigitString  = $twoDigitString . '(\d+)' . $verpSeperator;

        // FIXME: legacy regexen to handle CiviCRM 2.1 address patterns, with domain id and possible VERP part
        $commonRegex = '/^' . preg_quote($dao->localpart) . '(b|bounce|c|confirm|o|optOut|r|reply|re|e|resubscribe|u|unsubscribe)' . $threeDigitString . '([0-9a-f]{16})(-.*)?@' . preg_quote($dao->domain) . '$/';
        $subscrRegex = '/^' . preg_quote($dao->localpart) . '(s|subscribe)' . $twoDigitStringMin . '@' . preg_quote($dao->domain) . '$/';

        // a common-for-all-actions regex to handle CiviCRM 2.2 address patterns
        $regex = '/^' . preg_quote($dao->localpart) . '(b|c|e|o|r|u)' . $twoDigitString . '([0-9a-f]{16})@' . preg_quote($dao->domain) . '$/';

        // a tighter regex for finding bounce info in soft bounces’ mail bodies
        $rpRegex = '/Return-Path: ' . preg_quote($dao->localpart) . '(b)' . $twoDigitString . '([0-9a-f]{16})@' . preg_quote($dao->domain) . '/';

        // retrieve the emails
        require_once 'CRM/Mailing/MailStore.php';
        $store = CRM_Mailing_MailStore::getStore($name);

        require_once 'api/v2/Mailer.php';

        // process fifty at a time, CRM-4002
        while ($mails = $store->fetchNext(50)) {
            foreach ($mails as $key => $mail) {

                // for every addressee: match address elements if it's to CiviMail
                $matches = array();
                foreach ($mail->to as $address) {
                    if (preg_match($regex, $address->email, $matches)) {
                        list($match, $action, $job, $queue, $hash) = $matches;
                        break;
                    // FIXME: the below elseifs should be dropped when we drop legacy support
                    } elseif (preg_match($commonRegex, $address->email, $matches)) {
                        list($match, $action, $_, $job, $queue, $hash) = $matches;
                        break;
                    } elseif (preg_match($subscrRegex, $address->email, $matches)) {
                        list($match, $action, $_, $job) = $matches;
                        break;
                    }
                }

                // CRM-5471: if $matches is empty, it still might be a soft bounce sent
                // to another address, so scan the body for ‘Return-Path: …bounce-pattern…’
                if (!$matches and preg_match($rpRegex, $mail->generateBody(), $matches)) {
                    list($match, $action, $job, $queue, $hash) = $matches;
                }

                // if all else fails, check Delivered-To for possible pattern
                if (!$matches and preg_match($regex, $mail->getHeader('Delivered-To'), $matches)) {
                    list($match, $action, $job, $queue, $hash) = $matches;
                }

                // if $matches is empty, this email is not CiviMail-bound
                if (!$matches) {
                    $store->markIgnored($key);
                    continue;
                }

                // get $replyTo from either the Reply-To header or from From
                // FIXME: make sure it works with Reply-Tos containing non-email stuff
                $replyTo = $mail->getHeader('Reply-To') ? $mail->getHeader('Reply-To') : $mail->from->email;

                // handle the action by passing it to the proper API call
                // FIXME: leave only one-letter cases when dropping legacy support
                switch ($action) {
                case 'b':
                case 'bounce':
                    $text = '';
                    if ($mail->body instanceof ezcMailText) {
                        $text = $mail->body->text;
                    } elseif ($mail->body instanceof ezcMailMultipart) {
                        if ($mail->body instanceof ezcMailMultipartRelated) {
                           foreach ($mail->body->getRelatedParts() as $part) {
                               if (isset($part->subType) and $part->subType == 'plain') {
                                   $text = $part->text;
                                   break;
                               }
                           }                           
                        } else {   
                           foreach ($mail->body->getParts() as $part) {
                               if (isset($part->subType) and $part->subType == 'plain') {
                                   $text = $part->text;
                                   break;
                               }
                           }
                        }
                    }
                    $params = array ( 'job_id'         => $job,
                                      'event_queue_id' => $queue,
                                      'hash'           => $hash,
                                      'body'           => $text
                                      );
                    civicrm_mailer_event_bounce( $params );
                    break;
                case 'c':
                case 'confirm':
                    $params = array ( 'job_id'         => $job,
                                      'event_queue_id' => $queue,
                                      'hash'           => $hash
                                      );
                    civicrm_mailer_event_confirm( $params );
                    break;
                case 'o':
                case 'optOut':
                    $params = array ( 'job_id'         => $job,
                                      'event_queue_id' => $queue,
                                      'hash'           => $hash
                                      );
                    civicrm_mailer_event_domain_unsubscribe( $params );
                    break;
                case 'r':
                case 'reply':
                    // instead of text and HTML parts (4th and 6th params) send the whole email as the last param
                    $params = array ( 'job_id'         => $job,
                                      'event_queue_id' => $queue,
                                      'hash'           => $hash,
                                      'bodyTxt'        => null,
                                      'replyTo'        => $rt,
                                      'bodyHTML'       => null,
                                      'fullEmail'      => $mail->generate()
                                      );
                    civicrm_mailer_event_reply( $params );
                    break;
                case 'e':
                case 're':
                case 'resubscribe':
                    $params = array ( 'job_id'         => $job,
                                      'event_queue_id' => $queue,
                                      'hash'           => $hash
                                      );
                    civicrm_mailer_event_resubscribe( $params );
                    break;
                case 's':
                case 'subscribe':
                    $params = array ( 'email'          => $mail->from->email,
                                      'group_id'       => $job
                                      );
                    civicrm_mailer_event_subscribe( $params );
                    break;
                case 'u':
                case 'unsubscribe':
                    $params = array ( 'job_id'         => $job,
                                      'event_queue_id' => $queue,
                                      'hash'           => $hash
                                      );
                    civicrm_mailer_event_unsubscribe( $params );
                    break;
                }
                
                $store->markProcessed($key);
            }
        }
    }
  }

// bootstrap the environment and run the processor
session_start();
require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();

CRM_Utils_System::authenticateScript(true);

//log the execution of script
CRM_Core_Error::debug_log_message( 'CiviMailProcessor.php' );

//load bootstrap to call hooks
require_once 'CRM/Utils/System.php';
CRM_Utils_System::loadBootStrap(  );

require_once 'CRM/Core/Lock.php';
$lock = new CRM_Core_Lock('CiviMailProcessor');

if ($lock->isAcquired()) {
    // try to unset any time limits
    if (!ini_get('safe_mode')) set_time_limit(0);
    
    // cleanup directories with old mail files (if they exist): CRM-4452
    CiviMailProcessor::cleanupDir($config->customFileUploadDir . DIRECTORY_SEPARATOR . 'CiviMail.ignored');
    CiviMailProcessor::cleanupDir($config->customFileUploadDir . DIRECTORY_SEPARATOR . 'CiviMail.processed');
    
    // if there are named sets of settings, use them - otherwise use the default (null)
    $names = isset($_REQUEST['names']) && is_array($_REQUEST['names']) ? $_REQUEST['names'] : array( null );
    
    foreach ($names as $name) {
        CiviMailProcessor::process($name);
    }
} else {
    throw new Exception('Could not acquire lock, another CiviMailProcessor process is running');
}

$lock->release();
