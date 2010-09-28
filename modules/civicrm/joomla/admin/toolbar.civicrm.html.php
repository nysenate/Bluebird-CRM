<?php
/**
* @version		$Id: toolbar.contact.html.php 10381 2008-06-01 03:35:53Z pasamio $
* @package		Joomla
* @subpackage	Contact
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('No direct access allowed'); 

/**
* @package		Joomla
* @subpackage	Contact
*/
class TOOLBAR_civicrm
{
	/**
	* Draws the tool bar for CiviCRM
	*/

    function _EDIT( $displayAction ) {
        
		JToolBarHelper::title( JText::_( 'CiviCRM' ) .': <small><small>[ '. $displayAction .' ]</small></small>', 'generic.png' );
               
	}

    function _DEFAULT( ) {
        
		JToolBarHelper::title( JText::_( 'CiviCRM' ), 'generic.png' );
               
	}
}