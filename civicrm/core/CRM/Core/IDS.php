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


class CRM_Core_IDS {

    /**
     * define the threshold for the ids reactions
     */
    private $threshold = array(
                               'log'      => 25,
                               'warn'     => 50,
                               'kick'     => 75
                               );


    /**
     * the init object
     */
    private $init = NULL;

    /**
     * This function includes the IDS vendor parts and runs the
     * detection routines on the request array.
     *
     * @param object cake controller object
     * @return boolean
     */
    public function check( &$args ) {

        // lets bypass a few civicrm urls from this check
      static $skip = array( 'civicrm/ajax', 'civicrm/admin/setting/updateConfigBackend', 'civicrm/admin/messageTemplates' );
      $path = implode( '/', $args );
      if ( in_array( $path, $skip ) ) {
          return;
      }

        #add request url and user agent
      $_REQUEST['IDS_request_uri'] = $_SERVER['REQUEST_URI'];
      if (isset($_SERVER['HTTP_USER_AGENT'])) {
          $_REQUEST['IDS_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
      }
      
      require_once 'IDS/Init.php';

      // init the PHPIDS and pass the REQUEST array
      $config = CRM_Core_Config::singleton( );
      $configFile = $config->configAndLogDir . 'Config.IDS.ini';
        if ( ! file_exists( $configFile ) ) {
            global $civicrm_root;
            $contents = "
[General]
    filter_type         = xml
    filter_path         = {$civicrm_root}/packages/IDS/default_filter.xml
    tmp_path            = $config->uploadDir
    HTML_Purifier_Path  = IDS/vendors/htmlpurifier/HTMLPurifier.auto.php
    HTML_Purifier_Cache = $config->uploadDir
    scan_keys           = false
    exceptions[]        = __utmz
    exceptions[]        = __utmc
    exceptions[]        = widget_code
    exceptions[]        = html_message
    exceptions[]        = body_html
    exceptions[]        = msg_html
    exceptions[]        = description
    html[]              = intro
    html[]              = thankyou_text
    html[]              = intro_text
    html[]              = body_text
    html[]              = footer_text
    html[]              = thankyou_text
    html[]              = thankyou_footer
    html[]              = new_text
    html[]              = renewal_text
    html[]              = help_pre
    html[]              = help_post
    html[]              = msg_html
    html[]              = confirm_title
    html[]              = confirm_text
    html[]              = confirm_footer_text
    html[]              = confirm_email_text
    html[]              = report_header
    html[]              = report_footer
";
            if ( file_put_contents( $configFile, $contents ) === false ) {
                require_once 'CRM/Core/Error.php';
                CRM_Core_Error::movedSiteError( $configFile );
            }


            // also create the .htaccess file so we prevent the reading of the log and ini files
            // via a browser, CRM-3875
            $htaccessFile = $config->configAndLogDir . '.htaccess';
            if ( ! file_exists( $htaccessFile ) ) {
                $contents = '
# Protect files and directories from prying eyes.
<FilesMatch "\.(log|ini)$">
 Order allow,deny
</FilesMatch>
';
                if ( file_put_contents( $htaccessFile, $contents ) === false ) {
                    require_once 'CRM/Core/Error.php';
                    CRM_Core_Error::movedSiteError( $htaccessFile );
                }
            }
        }

        $init    = IDS_Init::init( $configFile );
        $ids     = new IDS_Monitor($_REQUEST, $init);
        $result  = $ids->run();

        if ( ! $result->isEmpty( ) ) {
            $this->react($result);
        }

        return true;
    }

    /**
     * This function rects on the values in
     * the incoming results array.
     *
     * Depending on the impact value certain actions are
     * performed.
     *
     * @param IDS_Report $result
     * @return boolean
     */
    private function react(IDS_Report $result) {

        $impact = $result->getImpact( );
        if ($impact >= $this->threshold['kick']) {
            $this->log($result, 3, $impact);
            $this->kick($result);
            return true;
        } else if ($impact >= $this->threshold['warn']) {
            $this->log($result, 2, $impact);
            $this->warn($result);
            return true;
        } else if ($impact >= $this->threshold['log']) {
            $this->log($result, 0, $impact);
            return true;
        } else {
            return true;
        }
    }

    /**
     * This function writes an entry about the intrusion
     * to the intrusion database
     *
     * @param array $results
     * @return boolean
     */
    private function log($result, $reaction = 0) {

        $ip = ($_SERVER['SERVER_ADDR'] != '127.0.0.1') ?
            $_SERVER['SERVER_ADDR'] :
            (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?
             $_SERVER['HTTP_X_FORWARDED_FOR'] :
             '127.0.0.1');

        $data = array( );
        $session = CRM_Core_Session::singleton( );
        foreach ($result as $event) {
            $data[] = array(
                            'name'      => $event->getName(),
                            'value'     => stripslashes($event->getValue()),
                            'page'      => $_SERVER['REQUEST_URI'],
                            'userid'    => $session->get( 'userID' ),
                            'session'   => session_id() ? session_id() : '0',
                            'ip'        => $ip,
                            'reaction'  => $reaction,
                            'impact'    => $result->getImpact()
                            );
        }

        CRM_Core_Error::debug_var( 'IDS Detector Details', $data );
        return true;
    }

    /**
     * //todo
     *
     *
     */
    private function warn($result) {
        return $result;
    }

    /**
     *  //todo
     *
     *
     */
    private function kick($result) {
        $session = CRM_Core_Session::singleton( );
        $session->reset( 2 );

        CRM_Core_Error::fatal( ts( 'There is a validation error with your HTML input. Your activity is a bit suspicious, hence aborting' ) );
    }
}



