<?php
/**
 * The Authenticate package is used to define helper methods related to authentication.
 * 
 * Authentication will only work if sessions are enabled.  Currently there is no
 * testing error reporting of this and probably won't be until the PHP5 version.  Complex
 * error handling is just too cumbersome in php < 5.
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage util
 * @version $Id: Authenticate.php,v 1.11 2005/03/24 22:19:48 pmineault Exp $
 */
 
class Authenticate {
	/**
	 * isAuthenticated hides the session implementation for tracking user access.
	 * 
	 * @return bool Whether the current user has been authenticated
	 */
	function isAuthenticated () {
		if (isset($_SESSION['amfphp_username'])) {
			return true;
		} else {
			return false;
		} 
	} 

	/**
	 * getAuthUser returns the current user name of the user that is logged in with the session.
	 * @return string the name of the authenticated user
	 */
	function getAuthUser () 
	{
		if(isset($_SESSION['amfphp_username']))
		{
		  return $_SESSION['amfphp_username'];
		}
		else
		{
		  return false;
		}
	} 
	/**
	 * Returns true if the client is authenticated and the requested roles
	 * passed match.
	 * 
	 * Every service method can have a comman delimited list of roles that are
	 * required to access a service.  Every user can also be assigned to a seperate
	 * comma delimited list to roles they belong to.  This method compares those two 
	 * strings (lists) and makes sure there is atleast one match.
	 * 
	 * @param string $roles comma delimited list of the methods roles
	 * @return bool Whether the user is in the proper role.
	 */
	function isUserInRole($roles) {
		$methodRoles = explode(",", $roles); // split the method roles into an array
		foreach($methodRoles as $key => $role) {
			$methodRoles[$key] = strtolower(trim($role));
		}
		if(!isset($_SESSION['amfphp_roles']))
		{
			$_SESSION['amfphp_roles'] = "";
			
		}
		$userRoles = explode(",", $_SESSION['amfphp_roles']); // split the users session roles into an array
		
		foreach($userRoles as $key => $role) {
			$userRoles[$key] = strtolower(trim($role));
			if (in_array($userRoles[$key], $methodRoles)) {
				return true;
			} 
		} 
		return false;
	} 

	/**
	 * login assumes that the user has verified the credentials and logs in the user.
	 * 
	 * The login method hides the session implementation for storing the user credentials
	 * 
	 * @param string $name The user name
	 * @param string $roles The comma delimited list of roles for the user
	 */
	function login($name, $roles) {
		if(!session_id())
		{
			session_start();
		}
		$_SESSION['amfphp_username'] = $name;
		$_SESSION['amfphp_roles'] = $roles;
	} 

	/**
	 * logout kills the user session and terminates the login properties
	 */
	function logout() {
		$_SESSION['amfphp_username'] = null;
		$_SESSION['amfphp_roles'] = null;
		if(isset($_SESSION['amfphp_username']))
		{
			unset($_SESSION['amfphp_username']);
		}
		if(isset($_SESSION['amfphp_roles']))
		{
			unset($_SESSION['amfphp_roles']);
		}
		return true;
	} 
} 

?>