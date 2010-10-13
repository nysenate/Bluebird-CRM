<?php

// Retrieve list of CiviCRM events
// Active, current or future, online

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementCivieventsonline extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'CiviEventsOnline';
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		// Initiate CiviCRM
		require_once JPATH_ROOT.'/'.'administrator/components/com_civicrm/civicrm.settings.php';
		require_once 'CRM/Core/Config.php';
		$config =& CRM_Core_Config::singleton( );
				
		require_once 'api/v2/Event.php';
		$params = array(
                  'is_online_registration'        => 1,
				  'is_active'        			  => 1,
				  'return.title'			  	  => 1,
                  'return.id'                     => 1,
                  'return.end_date'               => 1,
                  'return.start_date' 			  => 1
                  );
    	$events = civicrm_event_search( &$params );
		$currentdate = date("Y-m-d H:i:s");
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('- Select Event -') );
		foreach ( $events as $event ) {
			if ( $event['start_date'] > $currentdate || $event['end_date'] < $currentdate ) {
				$options[] = JHTML::_('select.option', $event['id'], $event['event_title']);
			}
		}
		
		return JHTML::_('select.genericlist', $options, 'params[id]', null, 'value', 'text', $value);

	}
}
?>