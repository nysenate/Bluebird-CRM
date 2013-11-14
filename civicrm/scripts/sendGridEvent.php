<?php
 /*
 * New York State Senate
 * Brian Shaughnessy
 * November, 2011
 */

require_once 'script_utils.php';
define('DEFAULT_BOUNCE_TYPE', 6);

if ( $_GET['key'] == '') {
	switch( $_POST['event'] ) {
	  case 'click':
	    $emailText = $_POST['email'] . ' clicked on ' . $_POST['url'];
	    break;
	 
	  case 'open':
	    $emailText = $_POST['email'] . ' opened email';
	    break;
	}
}
