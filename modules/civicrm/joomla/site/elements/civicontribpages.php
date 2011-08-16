<?php
  /*
   +--------------------------------------------------------------------+
   | CiviCRM version 3.4                                                |
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

  // Retrieve list of CiviCRM contribution pages
  // Active
  // Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementCiviContribPages extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'CiviContribPages';
	
	function fetchElement( $name, $value, &$node, $control_name)	{
		// Initiate CiviCRM
		require_once JPATH_ROOT.'/'.'administrator/components/com_civicrm/civicrm.settings.php';
		require_once 'CRM/Core/Config.php';
		$config =& CRM_Core_Config::singleton( );
        
        $options = array();
        $options[] = JHTML::_('select.option', '0', JText::_('- Select Contribution Page -') );
        $query = 'SELECT id,title  FROM civicrm_contribution_page WHERE is_active = 1 ORDER BY title';
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch( ) ) {
            $options[] = JHTML::_( 'select.option', $dao->id, $dao->title ); 
        }
        return JHTML::_( 'select.genericlist', $options, $control_name .'[' . $name . ']', 
                         null, 'value', 'text', $value, $control_name.$name );
	}
}
?>