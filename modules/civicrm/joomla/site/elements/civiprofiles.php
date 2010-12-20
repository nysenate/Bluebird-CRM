<?php
  /*
   +--------------------------------------------------------------------+
   | CiviCRM version 3.3                                                |
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

  // Retrieve list of CiviCRM profiles
  // Active
  // Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementCiviprofiles extends JElement {
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'CiviProfiles';
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		// Initiate CiviCRM
		require_once JPATH_ROOT.'/'.'administrator/components/com_civicrm/civicrm.settings.php';
		require_once 'CRM/Core/Config.php';
		$config =& CRM_Core_Config::singleton( );
		
		require_once 'api/v2/UFGroup.php';
        $ufGroups =civicrm_uf_profile_groups_get();
        $options[] = JHTML::_('select.option', '', JText::_('- Select Profile -') );
        foreach ( $ufGroups  as $key =>$values ) {
            $options[] = JHTML::_( 'select.option', $key, $values );
        }
        return JHTML::_( 'select.genericlist', $options, 'params[gid]', null, 'value', 'text', $value );
	}
}
?>