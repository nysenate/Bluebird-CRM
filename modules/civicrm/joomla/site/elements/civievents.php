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
  // Retrieve list of CiviCRM events
  // Active, current or future

  // Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementCivievents extends JElement {
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'CiviEvents';
	
	function fetchElement( $name, $value, &$node, $control_name ) {
		// Initiate CiviCRM
		require_once JPATH_ROOT.'/'.'administrator/components/com_civicrm/civicrm.settings.php';
		require_once 'CRM/Core/Config.php';
		$config =& CRM_Core_Config::singleton( );
        
		$params = array(
                        'version' 		     => '3',
                        'is_active'        	 => 1,
                        'isCurrent'      => 1,
                        'return.title'		 => 1,
                        'return.id'          => 1,
                        'return.end_date'    => 1,
                        'return.start_date'  => 1
                        );
        $events = civicrm_api('event', 'get', $params );
		$currentdate = date("Y-m-d H:i:s");
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('- Select Event -') );
		foreach ( $events['values'] as $event ) {
			if ( strtotime($event['start_date']) >= strtotime($currentdate) || 
			     strtotime($event['end_date']) >= strtotime($currentdate) ) {
				$options[] = JHTML::_( 'select.option', $event['id'], $event['event_title'] );
			}
		}
        
		return JHTML::_( 'select.genericlist', $options, ''.$control_name.'['.$name.']', null, 'value', 'text', $value, $control_name.$name );
        
	}
}
?>
