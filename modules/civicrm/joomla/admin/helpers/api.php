<?
/**
 * @version		
 * @package		
 * @copyright   @copyright CiviCRM LLC (c) 2004-2010	
 * @license		GNU/GPL v2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

class CivicrmHelperApi { 

    /*Sets the file paths and other constants for civicrm use.*/
	function setPaths() {
		define('CIVICRM_UF', 'Joomla');
		define ('CIVICRM_ROOT', JPATH_BASE.DS.'administrator'.DS.'components'. DS.'com_civicrm'.DS.'civicrm');
		$lc123config = & JFactory :: getConfig();

		define('CIVICRM_UF_DSN', 'mysql://'.$lc123config->getValue('config.user').':'.$lc123config->getValue('config.password').'@'.$lc123config->getValue('config.host').'/'.$lc123config->getValue('config.db').'?new_link=true');
		define('CIVICRM_DSN', 'mysql://'.$lc123config->getValue('config.user').':'.$lc123config->getValue('config.password').'@'.$lc123config->getValue('config.host').'/'.$lc123config->getValue('config.db').'?new_link=true');

		define('CIVICRM_UF_BASEURL', JURI :: base());

		$include_path = '.'.PATH_SEPARATOR.
            CIVICRM_ROOT.PATH_SEPARATOR.
            CIVICRM_ROOT.DS.'packages'.PATH_SEPARATOR.
            CIVICRM_ROOT.DS.'api'.PATH_SEPARATOR.
            CIVICRM_ROOT.DS.'api'.DS.'v2'.PATH_SEPARATOR.
            get_include_path();
		set_include_path($include_path);
	}

	function civiimport($path)
	{
		static $base;

		if (!$base) {
            $base =( CIVICRM_ROOT );
		}
		return JLoader::import($path, $base, '');
	}

}