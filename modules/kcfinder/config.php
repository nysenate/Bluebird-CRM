<?php

/** This file is part of KCFinder project
  *
  *      @desc Base configuration file
  *   @package KCFinder
  *   @version 2.53
  *    @author Pavel Tzonkov <sunhater@sunhater.com>
  * @copyright 2010-2014 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions

// Bootstrap Drupal in order to obtain paths and config.
$drupal_dir = preg_replace('#/drupal/sites/.*#', '/drupal', $_SERVER['SCRIPT_FILENAME']);
define('DRUPAL_ROOT', $drupal_dir);
// NYSS 7796: Set MAINTENANCE_MODE to true, which forces the Drupal bootstrap
// process to skip the menu_set_custom_theme() function.  This function
// was generating an error ever since nyss_mail_custom_theme() was added
// as a hook in the nyss_mail module.
define('MAINTENANCE_MODE', true);
require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

$bbconfig = get_bluebird_instance_config();
$pubpath = '/sites/'.$bbconfig['data_dirname'].'/pubfiles/';

$authenticated = user_access('access CiviCRM');


$_CONFIG = array(


// GENERAL SETTINGS

    'disabled' => !$authenticated,
    'theme' => "oxygen",
    'uploadURL' => 'http://'.$bbconfig['servername'].$pubpath,
    'uploadDir' => $drupal_dir.$pubpath,

    'types' => array(

    // (F)CKEditor types
        'files'   =>  "",
        'flash'   =>  "swf",
        'images'  =>  "*img",

    // TinyMCE types
        'file'    =>  "",
        'media'   =>  "swf flv avi mpg mpeg qt mov wmv asf rm",
        'image'   =>  "*img",
    ),


// IMAGE SETTINGS

    'imageDriversPriority' => "gd imagick gmagick",
    'jpegQuality' => 90,
    'thumbsDir' => ".thumbs",

    'maxImageWidth' => 640,
    'maxImageHeight' => 480,

    'thumbWidth' => 100,
    'thumbHeight' => 100,

    'watermark' => "",


// DISABLE / ENABLE SETTINGS

    'denyZipDownload' => false,
    'denyUpdateCheck' => true,
    'denyExtensionRename' => false,


// PERMISSION SETTINGS

    'dirPerms' => 0755,
    'filePerms' => 0644,

    'access' => array(

        'files' => array(
            'upload' => true,
            'delete' => true,
            'copy'   => true,
            'move'   => true,
            'rename' => true
        ),

        'dirs' => array(
            'create' => true,
            'delete' => true,
            'rename' => true
        )
    ),

    'deniedExts' => "exe com msi bat php phps phtml php3 php4 cgi pl",


// MISC SETTINGS

    'filenameChangeChars' => array(
        ' ' => '_'
    ),

    'dirnameChangeChars' => array(
        ' ' => '_'
    ),

    'mime_magic' => "",

    'cookieDomain' => "",
    'cookiePath' => "",
    'cookiePrefix' => 'KCFINDER_',


// THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION SETTINGS

    '_check4htaccess' => true,
    //'_tinyMCEPath' => "/tiny_mce",

    '_sessionVar' => &$_SESSION['KCFINDER'],
    //'_sessionLifetime' => 30,
    //'_sessionDir' => "/full/directory/path",

    //'_sessionDomain' => ".mysite.com",
    //'_sessionPath' => "/my/path",
);

?>
