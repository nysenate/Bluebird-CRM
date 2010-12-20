<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';
require_once 'CRM/Report/Utils/Report.php';

/**
 * Page for invoking report instances
 */
class CRM_Report_Page_InstanceList extends CRM_Core_Page 
{
   /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_links = null;

    public static function &info( $ovID = null, &$title = null) {

        $report = '';
        if ( $ovID ) {
            $report = " AND v.id = {$ovID} ";
        }
        $sql = "
        SELECT inst.id, inst.title, inst.report_id, inst.description, v.label, 
               ifnull( SUBSTRING(comp.name, 5), 'Contact' ) as compName
          FROM civicrm_option_group g
          LEFT JOIN civicrm_option_value v
                 ON v.option_group_id = g.id AND
                    g.name  = 'report_template'
          LEFT JOIN civicrm_report_instance inst
                 ON v.value = inst.report_id
          LEFT JOIN civicrm_component comp 
                 ON v.component_id = comp.id
            
         WHERE v.is_active = 1 {$report}

         ORDER BY v.weight
        ";
        $dao  = CRM_Core_DAO::executeQuery( $sql );
        $config = CRM_Core_Config::singleton( );
        $rows = array();
        $url  = 'civicrm/report/instance';
        while ( $dao->fetch( ) ) {
            $enabled = in_array( "Civi{$dao->compName}", $config->enableComponents );
            if ( $dao->compName == 'Contact') {
                $enabled = true;
            } 
            //filter report listings by permissions
            if ( !( $enabled && CRM_Report_Utils_Report::isInstancePermissioned( $dao->id ) ) ) {
                continue;
            }  

            if ( trim( $dao->title ) ) {
                if ( $ovID ) {
                    $title = ts("Report(s) created from the template: %1", array( 1 => $dao->label ) );
                }
                $rows[$dao->compName][$dao->id]['title']       = $dao->title;               
                $rows[$dao->compName][$dao->id]['label']       = $dao->label;
                $rows[$dao->compName][$dao->id]['description'] = $dao->description;               
                $rows[$dao->compName][$dao->id]['url']         = CRM_Utils_System::url( "{$url}/{$dao->id}", "reset=1");
                if ( CRM_Core_Permission::check( 'administer Reports' ) ) {
                    $rows[$dao->compName][$dao->id]['deleteUrl'] = 
                        CRM_Utils_System::url( "{$url}/{$dao->id}", 'action=delete&reset=1');
                }
            }
        }
        return $rows;
    }

    /**
     * run this page (figure out the action needed and perform it).
     *
     * @return void
     */
    function run() {
        //option value ID of the Report
        $ovID = $title = null;
        $ovID = CRM_Utils_Request::retrieve( 'ovid', 'Positive', $this );
        $rows =& self::info( $ovID, $title );
        
        $this->assign('list', $rows);
        if ( $ovID ) {
            $reportUrl  = CRM_Utils_System::url('civicrm/report/list', "reset=1");
            $this->assign( 'reportUrl', $reportUrl );
            $this->assign( 'title', $title);
        }
        // assign link to template list for users with appropriate permissions
        if ( CRM_Core_Permission::check ( 'administer Reports' ) ) {
            $templateUrl  = CRM_Utils_System::url('civicrm/report/template/list', "reset=1");
            $this->assign( 'templateUrl', $templateUrl );
        }
        return parent::run();
    }
}
