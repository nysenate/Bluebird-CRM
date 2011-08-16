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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Contact/BAO/Query.php';

/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Member_Selector_Search extends CRM_Core_Selector_Base implements CRM_Core_Selector_API 
{
    /**
     * This defines two actions- View and Edit.
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     * @static
     */
    static $_columnHeaders;

    /**
     * Properties of contact we're interested in displaying
     * @var array
     * @static
     */
    static $_properties = array( 'contact_id', 
                                 'membership_id',
                                 'contact_type',
                                 'sort_name',
                                 'membership_type',
                                 'join_date',
                                 'membership_start_date',
                                 'membership_end_date',
                                 'membership_source',
                                 'status_id',
                                 'member_is_test',
                                 'owner_membership_id',
                                 'membership_status',
                                 'member_campaign_id'
                                 );

    /** 
     * are we restricting ourselves to a single contact 
     * 
     * @access protected   
     * @var boolean   
     */   
    protected $_single = false;

    /**  
     * are we restricting ourselves to a single contact  
     *  
     * @access protected    
     * @var boolean    
     */    
    protected $_limit = null;

    /**
     * what context are we being invoked from
     *   
     * @access protected     
     * @var string
     */     
    protected $_context = null;

    /**
     * queryParams is the array returned by exportValues called on
     * the HTML_QuickForm_Controller for that page.
     *
     * @var array
     * @access protected
     */
    public $_queryParams;

    /**
     * represent the type of selector
     *
     * @var int
     * @access protected
     */
    protected $_action;

    /** 
     * The additional clause that we restrict the search with 
     * 
     * @var string 
     */ 
    protected $_memberClause = null;

    /** 
     * The query object
     * 
     * @var string 
     */ 
    protected $_query;

    /**
     * Class constructor
     *
     * @param array   $queryParams array of parameters for query
     * @param int     $action - action of search basic or advanced.
     * @param string  $memberClause if the caller wants to further restrict the search (used in memberships)
     * @param boolean $single are we dealing only with one contact?
     * @param int     $limit  how many memberships do we want returned
     *
     * @return CRM_Contact_Selector
     * @access public
     */
    function __construct(&$queryParams,
                         $action = CRM_Core_Action::NONE,
                         $memberClause = null,
                         $single = false,
                         $limit = null,
                         $context = 'search' ) 
    {
        // submitted form values
        $this->_queryParams =& $queryParams;

        $this->_single  = $single;
        $this->_limit   = $limit;
        $this->_context = $context;

        $this->_memberClause = $memberClause;
        
        // type of selector
        $this->_action = $action;
        $this->_query = new CRM_Contact_BAO_Query( $this->_queryParams, null, null, false, false,
                                                    CRM_Contact_BAO_Query::MODE_MEMBER );
        $this->_query->_distinctComponentClause = " DISTINCT(civicrm_membership.id)";
    }//end of constructor


    /**
     * This method returns the links that are given for each search row.
     * currently the links added for each row are 
     * 
     * - View
     * - Edit
     *
     * @return array
     * @access public
     *
     */
    static function &links( $status = 'all', 
                            $isPaymentProcessor = null, 
                            $accessContribution = null, 
                            $qfKey = null, 
                            $context = null,
                            $isCancelSupported = false )
    {
        $extraParams = null;
        if ( $context == 'search' ) $extraParams .= '&compContext=membership';
        if ( $qfKey ) $extraParams .= "&key={$qfKey}";
        
        if ( !self::$_links['view'] ) {
            self::$_links['view'] = array(
                                          CRM_Core_Action::VIEW   => array(
                                                                   'name'     => ts('View'),
                                                                   'url'      => 'civicrm/contact/view/membership',
                                                                   'qs'       => 'reset=1&id=%%id%%&cid=%%cid%%&action=view&context=%%cxt%%&selectedChild=member'.$extraParams,
                                                                   'title'    => ts('View Membership'),
                                                                   )
                                  );
        }
        if ( !isset(self::$_links['all']) || !self::$_links['all'] ) {       
            $extraLinks = array(
                                CRM_Core_Action::UPDATE => array(
                                                                 'name'  => ts('Edit'),
                                                                 'url'   => 'civicrm/contact/view/membership',
                                                                 'qs'    => 'reset=1&action=update&id=%%id%%&cid=%%cid%%&context=%%cxt%%'.$extraParams,
                                                                 'title' => ts('Edit Membership'),
                                                                 ),
                                CRM_Core_Action::DELETE => array(
                                                                 'name'  => ts('Delete'),
                                                                 'url'   => 'civicrm/contact/view/membership',
                                                                 'qs'    => 'reset=1&action=delete&id=%%id%%&cid=%%cid%%&context=%%cxt%%'.$extraParams,
                                                                 'title' => ts('Delete Membership'),
                                                                 ),
                                
                                CRM_Core_Action::RENEW => array(
                                                                'name'  => ts('Renew'),
                                                                'url'   => 'civicrm/contact/view/membership',
                                                                'qs'    => 'reset=1&action=renew&id=%%id%%&cid=%%cid%%&context=%%cxt%%'.$extraParams,
                                                                'title' => ts('Renew Membership')
                                                                ),
                                CRM_Core_Action::FOLLOWUP => array(
                                                                   'name'  => ts('Renew-Credit Card'),
                                                                   'url'   => 'civicrm/contact/view/membership',
                                                                   'qs'    => 'action=renew&reset=1&cid=%%cid%%&id=%%id%%&context=%%cxt%%&mode=live'.$extraParams,
                                                                   'title' => ts('Renew Membership Using Credit Card')
                                                                   ),
                                );
            if( ! $isPaymentProcessor || ! $accessContribution ) {
                //unset the renew with credit card when payment
                //processor is not available or user not permitted to make contributions
                unset( $extraLinks[CRM_Core_Action::FOLLOWUP] );
            }
            
            self::$_links['all'] = self::$_links['view'] + $extraLinks;
        }
        
        if ( $isCancelSupported ) {
            self::$_links['all'][CRM_Core_Action::DISABLE] = array( 
                                                                   'name' => ts('Cancel Subscription'),
                                                                   'url'  => 'civicrm/contribute/unsubscribe',
                                                                   'qs'   => 'reset=1&mid=%%id%%&context=%%cxt%%'.$extraParams,
                                                                   'title'=> 'Cancel Auto Renew Subscription'
                                                                    );
        } else if ( isset( self::$_links['all'][CRM_Core_Action::DISABLE] ) ) {
            unset( self::$_links['all'][CRM_Core_Action::DISABLE] );
        }
        
        return self::$_links[$status];
    } //end of function


    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['status']       = ts('Member') . ' %%StatusMessage%%';
        $params['csvString']    = null;
        if ( $this->_limit ) {
            $params['rowCount']     = $this->_limit;
        } else {
            $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;
        }

        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    } //end of function

    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        return $this->_query->searchQuery( 0, 0, null,
                                           true, false, 
                                           false, false, 
                                           false, 
                                           $this->_memberClause );
    }

    
    /**
     * returns all the rows in the given offset and rowCount
     *
     * @param enum   $action   the action being performed
     * @param int    $offset   the row number to start from
     * @param int    $rowCount the number of rows to return
     * @param string $sort     the sql string that describes the sort order
     * @param enum   $output   what should the result set include (web/email/csv)
     *
     * @return int   the total number of rows for this action
     */
     function &getRows($action, $offset, $rowCount, $sort, $output = null) 
     {
         // check if we can process credit card registration
         require_once 'CRM/Core/PseudoConstant.php';
         $processors = CRM_Core_PseudoConstant::paymentProcessor( false, false,
                                                                 "billing_mode IN ( 1, 3 )" );
         if ( count( $processors ) > 0 ) {
             $this->_isPaymentProcessor = true;
         } else {
             $this->_isPaymentProcessor = false;
         }

         // Only show credit card membership signup and renewal if user has CiviContribute permission
         if ( CRM_Core_Permission::access( 'CiviContribute' ) ) {
             $this->_accessContribution = true;
         } else {
             $this->_accessContribution = false;
         }
         
         //get all campaigns.
         require_once 'CRM/Campaign/BAO/Campaign.php';
         $allCampaigns = CRM_Campaign_BAO_Campaign::getCampaigns( null, null, false, false, false, true );
        
         $result = $this->_query->searchQuery( $offset, $rowCount, $sort,
                                               false, false, 
                                               false, false, 
                                               false, 
                                               $this->_memberClause );
                                               
         // process the result of the query
         $rows = array( );
         
         //CRM-4418 check for view, edit, delete
         $permissions = array( CRM_Core_Permission::VIEW );
         if ( CRM_Core_Permission::check( 'edit memberships' ) ) {
             $permissions[] = CRM_Core_Permission::EDIT;
         }
         if ( CRM_Core_Permission::check( 'delete in CiviMember' ) ) {
             $permissions[] = CRM_Core_Permission::DELETE;
         }
         $mask = CRM_Core_Action::mask( $permissions );
         
         while ($result->fetch()) {
             $row = array();
             
             // the columns we are interested in
             foreach (self::$_properties as $property) {             
                 if ( property_exists( $result, $property ) ) {
                     $row[$property] = $result->$property;
                 }
             }
             
             //carry campaign on selectors.
             $row['campaign'] = CRM_Utils_Array::value( $result->member_campaign_id, $allCampaigns );
             $row['campaign_id'] = $result->member_campaign_id;
             
             if ( CRM_Utils_Array::value('member_is_test', $row) ) {
                 $row['membership_type'] = $row['membership_type'] . " (test)";
             }

             $row['checkbox'] = CRM_Core_Form::CB_PREFIX . $result->membership_id;

             if ( ! isset( $result->owner_membership_id ) ) {
                 // unset renew and followup link for deceased membership
                 $currentMask = $mask;
                 if ( $result->membership_status == 'Deceased' ) {
                     $currentMask = $currentMask & ~CRM_Core_Action::RENEW & ~CRM_Core_Action::FOLLOWUP;
                 }
                 
                 $isCancelSupported = CRM_Member_BAO_Membership::isCancelSubscriptionSupported( $row['membership_id'] );
                 $row['action']   = CRM_Core_Action::formLink( self::links( 'all', 
                                                                            $this->_isPaymentProcessor, 
                                                                            $this->_accessContribution, 
                                                                            $this->_key,
                                                                            $this->_context,
                                                                            $isCancelSupported ), 
                                                               $currentMask,
                                                               array( 'id'  => $result->membership_id,
                                                                      'cid' => $result->contact_id,
                                                                      'cxt' => $this->_context ) );
             } else {
                 $row['action']   = CRM_Core_Action::formLink( self::links( 'view' ) , $mask,
                                                               array( 'id'  => $result->membership_id,
                                                                      'cid' => $result->contact_id,
                                                                      'cxt' => $this->_context ) );
             }
             
             $autoRenew = false;
             if ( isset( $result->membership_recur_id ) && $result->membership_recur_id ) $autoRenew =  true;
             $row['auto_renew'] = $autoRenew;
             
             require_once( 'CRM/Contact/BAO/Contact/Utils.php' );
             $row['contact_type' ] = 
                 CRM_Contact_BAO_Contact_Utils::getImage( $result->contact_sub_type ? 
                                                          $result->contact_sub_type : $result->contact_type ,false,$result->contact_id);
             
             $rows[] = $row;
         }
         
         return $rows;
     
     }
     
     
     /**
      * @return array              $qill         which contains an array of strings
      * @access public
      */
     
     // the current internationalisation is bad, but should more or less work
     // for most of "European" languages
     public function getQILL( )
     {
         return $this->_query->qill( );
     }
     
     /** 
      * returns the column headers as an array of tuples: 
     * (name, sortName (key to the sort array)) 
     * 
     * @param string $action the action being performed 
     * @param enum   $output what should the result set include (web/email/csv) 
     * 
     * @return array the column headers that need to be displayed 
     * @access public 
     */ 
    public function &getColumnHeaders( $action = null, $output = null ) 
    {
        if ( ! isset( self::$_columnHeaders ) ) {
            self::$_columnHeaders = array(
                                          array(
                                                'name'      => ts('Type'),
                                                'sort'      => 'membership_type_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array('name'      => ts('Member Since'),
                                                'sort'      => 'join_date',
                                                'direction' => CRM_Utils_Sort::DESCENDING,
                                                ),
                                          array(
                                                'name'      => ts('Start Date'),
                                                'sort'      => 'membership_start_date',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('End Date'),
                                                'sort'      => 'membership_end_date',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Source'),
                                                'sort'      => 'membership_source',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                          array(
                                                'name'      => ts('Status'),
                                                'sort'      => 'status_id',
                                                'direction' => CRM_Utils_Sort::DONTCARE,
                                                ),
                                         
                                          array(
                                                'name'      => ts('Auto-renew?'),
                                                ),
                                          array('desc' => ts('Actions') ),
                                          );

            if ( ! $this->_single ) {
                $pre = array( 
                             array('desc' => ts('Contact Type') ), 
                             array( 
                                   'name'      => ts('Name'), 
                                   'sort'      => 'sort_name', 
                                   'direction' => CRM_Utils_Sort::DONTCARE, 
                                   )
                             );
                self::$_columnHeaders = array_merge( $pre, self::$_columnHeaders );
            }
        }
        return self::$_columnHeaders;
    }
    
    function alphabetQuery( ) {
        return $this->_query->searchQuery( null, null, null, false, false, true );
    }
    
    function &getQuery( ) {
        return $this->_query;
    }

    /** 
     * name of export file. 
     * 
     * @param string $output type of output 
     * @return string name of the file 
     */ 
     function getExportFileName( $output = 'csv') { 
         return ts('CiviCRM Member Search'); 
     } 
     
     
}//end of class


