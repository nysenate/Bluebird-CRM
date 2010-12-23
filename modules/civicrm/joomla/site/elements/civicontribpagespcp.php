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

  // Retrieve list of CiviCRM PCP's contribution pages
  // Active

  // Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementCiviContribPagesPCP extends JElement {
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'CiviContribPagesPCP';
	
	function fetchElement( $name, $value, &$node, $control_name ) {
		// Initiate CiviCRM
		require_once JPATH_ROOT.'/'.'administrator/components/com_civicrm/civicrm.settings.php';
		require_once 'CRM/Core/Config.php';
		$config =& CRM_Core_Config::singleton( );
        
		// Get list of all ContribPagesPCP  and assign to options array
		$options = array();
		
        $query = "SELECT cp.id, cp.title FROM civicrm_contribution_page cp, civicrm_pcp_block pcp" 
            ." WHERE cp.is_active =1 AND pcp.is_active =1 AND pcp.entity_id = cp.id AND pcp.entity_table = 'civicrm_contribution_page'"
            ." ORDER BY cp.title";
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch( ) ) {
            $options[] = JHTML::_( 'select.option', $dao->id, $dao->title ); 
        }
      	return JHTML::_( 'select.genericlist', $options, 'params[id]', null, 'value', 'text', $value );
	}
}
?>