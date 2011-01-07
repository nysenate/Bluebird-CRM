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

require_once 'CRM/Member/Page/DashBoard.php';

/**
 * Page for displaying list of Payment-Instrument
 */
class CRM_Member_Page_DashBoard extends CRM_Core_Page 
{
    /** 
     * Heart of the viewing process. The runner gets all the meta data for 
     * the contact and calls the appropriate type of page to view. 
     * 
     * @return void 
     * @access public 
     * 
     */ 
    function preProcess( ) 
    {
        require_once "CRM/Member/BAO/MembershipType.php";
        require_once "CRM/Member/BAO/Membership.php";
        CRM_Utils_System::setTitle( ts('CiviMember') );
        $membershipSummary = array();
        $preMonth = CRM_Utils_Date::customFormat(date( "Y-m-d", mktime(0, 0, 0, date("m")-1,01,date("Y"))) , '%Y%m%d');
        $preMonthEnd = CRM_Utils_Date::customFormat(date( "Y-m-t", mktime(0, 0, 0, date("m")-1,01,date("Y"))) , '%Y%m%d');
        $preMonthYear =  mktime(0, 0, 0, substr($preMonth, 4, 2), 1, substr($preMonth, 0, 4));
        
        $today = getdate();
        $date    = CRM_Utils_Date::getToday();
        $isCurrentMonth = 0;
        if ( ($ym = CRM_Utils_Array::value('date', $_GET)) ) {
            if ( preg_match('/^\d{6}$/', $ym) == 0 || ! checkdate(substr($ym, 4, 2), 1, substr($ym, 0, 4)) || substr($ym, 0, 1) == 0) {
                CRM_Core_Error::fatal( ts('Invalid date query "%1" in URL (valid syntax is yyyymm).', array(1 => $ym)) );
            }
            $isPreviousMonth = 0;
            $isCurrentMonth = substr($ym, 0, 4) == $today['year'] && substr($ym, 4, 2) == $today['mon'];
            $ymd = date('Ymd', mktime(0, 0, -1, substr($ym, 4, 2)+1, 1, substr($ym, 0, 4)));
            $monthStartTs = mktime(0, 0, 0, substr($ym, 4, 2), 1, substr($ym, 0, 4));
            $current = CRM_Utils_Date::customFormat( $date, '%Y%m%d' );
        }
        else {
            $ym  = sprintf("%04d%02d",     $today['year'], $today['mon']);
            $ymd = sprintf("%04d%02d%02d", $today['year'], $today['mon'], $today['mday']);
            $monthStartTs = mktime(0, 0, 0, $today['mon'], 1, $today['year']);
            $current = null;          
            $isCurrentMonth = 1;
            $isPreviousMonth = 1;
        }
        $monthStart = $ym . '01';
        $yearStart = substr($ym, 0, 4) . '0101';
        
        $membershipTypes = CRM_Member_BAO_MembershipType::getMembershipTypes(false);
        //$membership = new CRM_Member_BAO_Membership;//added
        foreach ( $membershipTypes as $key => $value ) {
            $membershipSummary[$key]['premonth'] = array(
                                                         'count'=>CRM_Member_BAO_Membership::getMembershipStarts($key ,$preMonth, 
                                                                                                                 $preMonthEnd ),
                                                         'name' => $value
                                                         );
            
            $membershipSummary[$key]['month'] = array(
                                                      'count'=>CRM_Member_BAO_Membership::getMembershipStarts($key ,$monthStart, $ymd),
                                                      'name' => $value
                                                      );
            
            $membershipSummary[$key]['year'] = array(
                                                     'count'=>CRM_Member_BAO_Membership::getMembershipStarts($key ,$yearStart, $ymd),
                                                     'name' => $value
                                                     );
            
            $membershipSummary[$key]['current'] = array(
                                                        'count'=>CRM_Member_BAO_Membership::getMembershipCount($key, $current),
                                                        'name' => $value
                                                      );
            
            $membershipSummary[$key]['total'] = array( 'count' => CRM_Member_BAO_Membership::getMembershipCount($key, $ymd) );
        }
        require_once "CRM/Member/BAO/MembershipStatus.php";
        $status = CRM_Member_BAO_MembershipStatus::getMembershipStatusCurrent();
        $status = implode(',' , $status );
           
        foreach( $membershipSummary as $typeID => $details) {
            foreach ( $details as $key => $value ) {
                switch ($key) {
                case 'premonth':
                    $membershipSummary[$typeID][$key]['url'] = CRM_Utils_System::url( 'civicrm/member/search',"reset=1&force=1&status=$status&type=$typeID&start=$preMonth&end=$preMonthEnd" );
                    break;
                case 'month':
                    $membershipSummary[$typeID][$key]['url'] = CRM_Utils_System::url( 'civicrm/member/search',"reset=1&force=1&status=$status&type=$typeID&start=$monthStart&end=$ymd" );
                    break;
                case 'year':
                    $membershipSummary[$typeID][$key]['url'] = CRM_Utils_System::url( 'civicrm/member/search',"reset=1&force=1&status=$status&type=$typeID&start=$yearStart&end=$ymd" );
                    break;
                case 'current':
                    $membershipSummary[$typeID][$key]['url'] = CRM_Utils_System::url( 'civicrm/member/search',"reset=1&force=1&status=$status&type=$typeID" );
                    break;
                case 'total':
                    if (! $isCurrentMonth ) {
                        $membershipSummary[$typeID][$key]['url'] = CRM_Utils_System::url('civicrm/member/search',
                                                                       "reset=1&force=1&start=&end=$ymd&status=$status&type=$typeID"); 
                    } else {
                        $membershipSummary[$typeID][$key]['url'] = CRM_Utils_System::url('civicrm/member/search',
                                                                                        "reset=1&force=1&status=$status");
                    }
                    break;
                }
            }
        }
        
        $totalCount = array();
        $totalCountPreMonth = $totalCountMonth = $totalCountYear = $totalCountCurrent = $totalCountTotal = 0;
        foreach( $membershipSummary as $key => $value ) {
            $totalCountPreMonth   = $totalCountPreMonth   +  $value['premonth']['count'];
            $totalCountMonth      = $totalCountMonth      +  $value['month']['count'];
            $totalCountYear       = $totalCountYear       +  $value['year']['count'];
            $totalCountCurrent    = $totalCountCurrent    +  $value['current']['count'];
            $totalCountTotal      = $totalCountTotal      +  $value['total']['count'];
        }
        
        
        $totalCount['premonth'] = array("count" => $totalCountPreMonth,
                                        "url"   => CRM_Utils_System::url( 'civicrm/member/search',
                                                                          "reset=1&force=1&status=$status&start=$preMonth&end=$preMonthEnd" ),
                                        ); 
        $totalCount['month'] = array("count" => $totalCountMonth,
                                     "url"   => CRM_Utils_System::url( 'civicrm/member/search',
                                                                       "reset=1&force=1&status=$status&start=$monthStart&end=$ymd" ),
                                     );
        $totalCount['year'] = array("count" => $totalCountYear,
                                    "url"   => CRM_Utils_System::url( 'civicrm/member/search',
                                                                      "reset=1&force=1&status=$status&start=$yearStart&end=$ymd" ),
                                    );
        $totalCount['current'] = array("count" => $totalCountCurrent,
                                       "url"   => CRM_Utils_System::url( 'civicrm/member/search',
                                                                         "reset=1&force=1&status=$status" ),
                                       );
        $totalCount['total'] = array("count" => $totalCountTotal,
                                     "url"   => CRM_Utils_System::url( 'civicrm/member/search',
                                                                       "reset=1&force=1&status=$status" ),
                                      );
        if (! $isCurrentMonth ) {
            $totalCount['total'] = array( "count" => $totalCountTotal,
                                          "url"   => CRM_Utils_System::url( 'civicrm/member/search',
                                                                            "reset=1&force=1&status=$status&start=&end=$ymd" )
                                          );
        }
        
        $this->assign('membershipSummary' , $membershipSummary);
        $this->assign('totalCount'        , $totalCount);
        $this->assign('month'             , date('F', $monthStartTs));
        $this->assign('year'              , date('Y', $monthStartTs));
        $this->assign('premonth'          , date('F', $preMonthYear));
        $this->assign('currentMonth'      , date('F'));
        $this->assign('currentYear'       , date('Y'));
        $this->assign('isCurrent'         , $isCurrentMonth);
        $this->assign('preMonth'          , $isPreviousMonth );
    }

    /** 
     * This function is the main function that is called when the page loads, 
     * it decides the which action has to be taken for the page. 
     *                                                          
     * return null        
     * @access public 
     */                                                          
    function run( ) { 
        $this->preProcess( );
        
        $controller = new CRM_Core_Controller_Simple( 'CRM_Member_Form_Search', ts('Member'), null ); 
        $controller->setEmbedded( true ); 
        $controller->reset( ); 
        $controller->set( 'limit', 20 );
        $controller->set( 'force', 1 );
        $controller->set( 'context', 'dashboard' ); 
        $controller->process( ); 
        $controller->run( ); 
        
        return parent::run( );
    }

}


