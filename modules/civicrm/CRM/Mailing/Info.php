<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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

require_once 'CRM/Core/Component/Info.php';

/**
 * This class introduces component to the system and provides all the 
 * information about it. It needs to extend CRM_Core_Component_Info
 * abstract class.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */
class CRM_Mailing_Info extends CRM_Core_Component_Info
{

    // docs inherited from interface
    protected $keyword = 'mailing';
        

    // docs inherited from interface
    public function getInfo()
    {
        return array( 'name'                 => 'CiviMail',
                      'translatedName'       => ts('CiviMail'),
                      'title'                => 'CiviCRM Mailing Engine',
                      'search'               => 1,
                      'showActivitiesInCore' => 1 
                      );
    }


    static function workflowEnabled( ) {
        $config =& CRM_Core_Config::singleton( );

        $enableWorkflow = defined( 'CIVICRM_CIVIMAIL_WORKFLOW' ) ? (bool) CIVICRM_CIVIMAIL_WORKFLOW : false;

        return ( $enableWorkflow &&
                 $config->userFramework == 'Drupal' &&
                 module_exists( 'rules' ) ) ?
            true :
            false;
             
    }
    // docs inherited from interface
    public function getPermissions()
    {
        $permissions = array( 'access CiviMail', 
                              'access CiviMail subscribe/unsubscribe pages',
                              'delete in CiviMail',
                              'view public CiviMail content');

        if ( self::workflowEnabled( ) ) {
            $permissions[] = 'create mailings';
            $permissions[] = 'schedule mailings';
            $permissions[] = 'approve mailings';
        }

        return $permissions;
    }


    // docs inherited from interface
    public function getUserDashboardElement()
    {
        // no dashboard element for this component
        return null;
    }

    public function getUserDashboardObject( )
    {
        // no dashboard element for this component
        return null;
    }
    
    // docs inherited from interface  
    public function registerTab()
    {
        // this component doesn't use contact record tabs
        return null;
    }
    
    // docs inherited from interface  
    public function registerAdvancedSearchPane()
    {
        return array( 'title'   => ts( 'Mailings' ),
                      'weight'  => 20 );
    }    
    
    // docs inherited from interface    
    public function getActivityTypes()
    {
        return null;
    }

    // add shortcut to Create New
    public function creatNewShortcut( &$shortCuts ) {

    }
    
}
