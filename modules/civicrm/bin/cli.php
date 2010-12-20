<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright Tech To The People http:tttp.eu (c) 2008                 |
 +--------------------------------------------------------------------+
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 * A PHP shell script

On drupal if you have a symlink to your civi module, don't forget to create a new file - settings_location.php
Enter the following code (substitute the actual location of your <drupal root>/sites directory)
<?php
define( 'CIVICRM_CONFDIR', '/var/www/drupal.6/sites' );
?>

 */
$include_path = "packages/:" . get_include_path( );
set_include_path( $include_path );

class civicrm_CLI {
    /**
     * constructor
     */
 function __construct($authenticate = true ) {
//	$include_path = "packages/" . get_include_path( );
//	set_include_path( $include_path );
  if (!$authenticate) {
    $this->setEnv();
    return;
  }
	require_once 'Console/Getopt.php';
	$shortOptions = "s:u:p:";
	$longOptions  = array( 'site=','user','pass'  );

	$getopt  = new Console_Getopt( );
	$args = $getopt->readPHPArgv( );
	array_shift( $args );
	list( $valid, $this->args ) = $getopt->getopt2( $args, $shortOptions, $longOptions );

	$vars = array(
		  'user' => 'u',
		  'pass' => 'p',
		  'site' => 's'
		  );

	foreach ( $vars as $var => $short ) {
		$$var = null;
		foreach ( $valid as $v ) {
		    if ( $v[0] == $short || $v[0] == "--$var" ) {
			$$var = $v[1];
			break;
		    }
		}
		if ( ! $$var ) {
      $a = explode('/', $_SERVER["SCRIPT_NAME"]);
      $file = $a[count($a) - 1]; 
		    die ("\nUsage: \$cd /your/civicrm/root; \$php5 bin/". $file." -u user -p password -s yoursite.org (or default)\n");
		}
	 }
	 $this->site=$site;
	 $this->setEnv();
	 $this->authenticate($user,$pass);
    }

    function authenticate ($user,$pass) {
         session_start( );                               
         require_once 'CRM/Core/Config.php'; 
    
         $config =& CRM_Core_Config::singleton(); 

         // this does not return on failure
         // require_once 'CRM/Utils/System.php';
         CRM_Utils_System::authenticateScript( true,$user,$pass );

         // bootstrap CMS environment
         global $civicrm_root;
         $_SERVER['SCRIPT_FILENAME'] = "$civicrm_root/bin/cli.php";
         require_once 'CRM/Utils/System.php';
         CRM_Utils_System::loadBootStrap($user, $pass);
    }

    function setEnv() {
    global $civicrm_root;
		// so the configuration works with php-cli
		$_SERVER['PHP_SELF' ] ="/index.php";
		$_SERVER['HTTP_HOST']= $this->site;
		require_once ("./civicrm.config.php");
    require_once ("CRM/Core/Error.php");
         	$this->key= CIVICRM_SITE_KEY;
		$_REQUEST['key']= $this->key;
		$_SERVER['SCRIPT_FILENAME' ] = $civicrm_root . "/bin/cli.php";
    if (CIVICRM_CONFDIR) {
		  $_SERVER['SCRIPT_FILENAME' ] = CIVICRM_CONFDIR . "/sites/all/modules/civicrm/bin/cli.php";
    }

     CRM_Core_Error::setCallback( array( 'civicrm_CLI', 'fatal' ) );
    }

    static function fatal( $pearError ) {
        return civicrm_create_error($pearError->getMessage(),$pearError->getDebugInfo());
    }

}


//$cli=new civicrm_cli ();

?>
