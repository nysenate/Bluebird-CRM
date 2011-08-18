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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Selector/Base.php';
require_once 'CRM/Core/Selector/API.php';

require_once 'CRM/Utils/Pager.php';
require_once 'CRM/Utils/Sort.php';

require_once 'CRM/Contact/BAO/Contact.php';


/**
 * This class is used to retrieve and display a range of
 * contacts that match the given criteria (specifically for
 * results of advanced search options.
 *
 */
class CRM_Mailing_Selector_Event    extends CRM_Core_Selector_Base 
                                    implements CRM_Core_Selector_API 
{
    /**
     * array of supported links, currenly null
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * what event type are we browsing?
     */
    private $_event;

    /**
     * should we only count distinct contacts?
     */
    private $_is_distinct;
    
    /**
     * which mailing are we browsing events from?
     */
    private $_mailing_id;

    /**
     * do we want events tied to a specific job?
     */
    private $_job_id;

    /**
     * for click-through events, do we only want those from a specific url?
     */
    private $_url_id;
    
    /**
     * we use desc to remind us what that column is, name is used in the tpl
     *
     * @var array
     */
    public $_columnHeaders;

    /**
     * Class constructor
     *
     * @param string $event         The event type (queue/delivered/open...)
     * @param boolean $distinct     Count only distinct contact events?
     * @param int $mailing          ID of the mailing to query
     * @param int $job              ID of the job to query.  If null, all jobs from $mailing are queried.
     * @param int $url              If the event type is a click-through, do we want only those from a specific url?
     *
     * @return CRM_Contact_Selector_Profile
     * @access public
     */
    function __construct($event, $distinct, $mailing, $job = null, $url = null )
    {
        $this->_event_type  = $event;
        $this->_is_distinct = $distinct;
        $this->_mailing_id  = $mailing;
        $this->_job_id      = $job;
        $this->_url_id      = $url;
    }//end of constructor


    /**
     * This method returns the links that are given for each search row.
     *
     * @return array
     * @access public
     * @static
     */
    static function &links()
    {
        return self::$_links;
    } //end of function


    /**
     * getter for array of the parameters required for creating pager.
     *
     * @param 
     * @access public
     */
    function getPagerParams($action, &$params) 
    {
        $params['csvString']    = null;
        $params['rowCount']     = CRM_Utils_Pager::ROWCOUNT;
        $params['status']       = ts('%1 %%StatusMessage%%', array(1 =>
        $this->eventToTitle()));
        
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
    }//end of function


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
    function &getColumnHeaders($action = null, $output = null) 
    {
        require_once 'CRM/Mailing/BAO/Mailing.php';
        $mailing = CRM_Mailing_BAO_Mailing::getTableName();

        require_once 'CRM/Contact/BAO/Contact.php';
        $contact    = CRM_Contact_BAO_Contact::getTableName();

        require_once 'CRM/Core/BAO/Email.php';
        $email      = CRM_Core_BAO_Email::getTableName();

        require_once 'CRM/Mailing/BAO/Job.php';
        $job = CRM_Mailing_BAO_Job::getTableName();
        if ( ! isset( $this->_columnHeaders ) ) {
                 
            $this->_columnHeaders = array( 
                array(
                    'name'      => ts('Contact'),
                    'sort'      => $contact.'.sort_name',
                    'direction' => CRM_Utils_Sort::ASCENDING
                ), 
                array(
                    'name'      => ts('Email Address'),
                    'sort'      => $email.'.email',
                    'direction' => CRM_Utils_Sort::DONTCARE,

                ), 
            );

            switch( $this->_event_type ) {
            case 'queue':
                $dateSort = $job.'.start_date';
                break;

            case 'delivered':
                require_once 'CRM/Mailing/Event/BAO/Delivered.php';
                $dateSort = CRM_Mailing_Event_BAO_Delivered::getTableName().'.time_stamp';
                break;
                
            case 'opened':
                require_once 'CRM/Mailing/Event/BAO/Opened.php';
                $dateSort = CRM_Mailing_Event_BAO_Opened::getTableName().'.time_stamp';
                break;
                
            case 'bounce':
                require_once 'CRM/Mailing/Event/BAO/Bounce.php';
                $dateSort = CRM_Mailing_Event_BAO_Bounce::getTableName().'.time_stamp';
                $this->_columnHeaders = array_merge($this->_columnHeaders, 
                                                    array( array(
                                                                 'name'  => ts('Bounce Type'),
                                                                 ),
                                                           array(
                                                                 'name'  => ts('Bounce Reason'),
                                                                 ),
                                                           ));
                break;
                
            case 'forward':
                require_once 'CRM/Mailing/Event/BAO/Forward.php';
                $dateSort = CRM_Mailing_Event_BAO_Forward::getTableName().'.time_stamp';
                
                $this->_columnHeaders = array_merge($this->_columnHeaders,
                                                    array( array(
                                                                 'name'  => ts('Forwarded Email'),
                                                                 ),
                                                           ));
                break;
                
            case 'reply':
                require_once 'CRM/Mailing/Event/BAO/Reply.php';
                $dateSort = CRM_Mailing_Event_BAO_Reply::getTableName().'.time_stamp';
                break;
                
            case 'unsubscribe':
                require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';
                $dateSort = CRM_Mailing_Event_BAO_Unsubscribe::getTableName().'.time_stamp';
                $this->_columnHeaders = array_merge($this->_columnHeaders, array( array(
                                                                                        'name'  => ts('Opt-Out'),
                                                                                    ),
                                                                                  ));
                break;
                
            case 'click':
                require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';
                $dateSort = CRM_Mailing_Event_BAO_TrackableURLOpen::getTableName().'.time_stamp';
                $this->_columnHeaders = array_merge($this->_columnHeaders, array( array(
                                                                                        'name'  => ts('URL'),
                                                                                        ),
                                                                                  ));
                break;
                
            default:
                return 0;
            }

            $this->_columnHeaders = array_merge($this->_columnHeaders,
                array(
                    array(
                        'name'      => ts('Date'),
                        'sort'      => $dateSort,
                        'direction' => CRM_Utils_Sort::DESCENDING,
                    ), 
                ));
        }
        return $this->_columnHeaders;
    }


    /**
     * Returns total number of rows for the query.
     *
     * @param 
     * @return int Total number of rows 
     * @access public
     */
    function getTotalCount($action)
    {
        switch($this->_event_type) {

        case 'queue':
            require_once 'CRM/Mailing/Event/BAO/Queue.php';
            $event = new CRM_Mailing_Event_BAO_Queue();
            return $event->getTotalCount(   $this->_mailing_id, 
                                            $this->_job_id );
            break;

        case 'delivered':
            require_once 'CRM/Mailing/Event/BAO/Delivered.php';
            $event = new CRM_Mailing_Event_BAO_Delivered();
            return $event->getTotalCount(   $this->_mailing_id, 
                                            $this->_job_id,
                                            $this->_is_distinct );
            break;

        case 'opened':
            require_once 'CRM/Mailing/Event/BAO/Opened.php';
            $event = new CRM_Mailing_Event_BAO_Opened();
            return $event->getTotalCount(   $this->_mailing_id, 
                                            $this->_job_id,
                                            $this->_is_distinct );
            break;

        case 'bounce':
            require_once 'CRM/Mailing/Event/BAO/Bounce.php';
            $event = new CRM_Mailing_Event_BAO_Bounce();
            return $event->getTotalCount(   $this->_mailing_id, 
                                            $this->_job_id,
                                            $this->_is_distinct );
            break;

        case 'forward':
            require_once 'CRM/Mailing/Event/BAO/Forward.php';
            $event = new CRM_Mailing_Event_BAO_Forward();
            return $event->getTotalCount(   $this->_mailing_id,
                                            $this->_job_id,
                                            $this->_is_distinct );
            break;
        case 'reply':
            require_once 'CRM/Mailing/Event/BAO/Reply.php';
            $event = new CRM_Mailing_Event_BAO_Reply();
            return $event->getTotalCount(   $this->_mailing_id, 
                                            $this->_job_id,
                                            $this->_is_distinct );
            break;

        case 'unsubscribe':
            require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';
            $event = new CRM_Mailing_Event_BAO_Unsubscribe();
            return $event->getTotalCount(   $this->_mailing_id, 
                                            $this->_job_id,
                                            $this->_is_distinct );
            break;

        case 'click':
            require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';
            $event = new CRM_Mailing_Event_BAO_TrackableURLOpen();
            return $event->getTotalCount(   $this->_mailing_id, 
                                            $this->_job_id,
                                            $this->_is_distinct,
                                            $this->_url_id );
            break;

        default:
            return 0;
        }
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
    function &getRows($action, $offset, $rowCount, $sort, $output = null) {
        switch($this->_event_type) {

        case 'queue':
            require_once 'CRM/Mailing/Event/BAO/Queue.php';
            return
                CRM_Mailing_Event_BAO_Queue::getRows($this->_mailing_id,
                                                     $this->_job_id, $offset, $rowCount, $sort);
            break;

        case 'delivered':
            require_once 'CRM/Mailing/Event/BAO/Delivered.php';
            return
                CRM_Mailing_Event_BAO_Delivered::getRows($this->_mailing_id,
                                                         $this->_job_id, $this->_is_distinct,
                                                         $offset, $rowCount, $sort);

            break;

        case 'opened':
            require_once 'CRM/Mailing/Event/BAO/Opened.php';
            return
                CRM_Mailing_Event_BAO_Opened::getRows($this->_mailing_id,
                                                      $this->_job_id, $this->_is_distinct,
                                                      $offset, $rowCount, $sort);
            break;

        case 'bounce':
            require_once 'CRM/Mailing/Event/BAO/Bounce.php';
            return
                CRM_Mailing_Event_BAO_Bounce::getRows($this->_mailing_id,
                                                      $this->_job_id, $this->_is_distinct,
                                                      $offset, $rowCount, $sort);
            break;

        case 'forward':
            require_once 'CRM/Mailing/Event/BAO/Forward.php';
            return
                CRM_Mailing_Event_BAO_Forward::getRows($this->_mailing_id,
                                                       $this->_job_id, $this->_is_distinct,
                                                       $offset, $rowCount, $sort);

        case 'reply':
            require_once 'CRM/Mailing/Event/BAO/Reply.php';
            return
                CRM_Mailing_Event_BAO_Reply::getRows($this->_mailing_id,
                                                     $this->_job_id, $this->_is_distinct,
                                                     $offset, $rowCount, $sort);
            break;

        case 'unsubscribe':
            require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';
            return
                CRM_Mailing_Event_BAO_Unsubscribe::getRows($this->_mailing_id,
                                                           $this->_job_id, $this->_is_distinct,
                                                           $offset, $rowCount, $sort);
            break;

        case 'click':
            require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';
            return
                CRM_Mailing_Event_BAO_TrackableURLOpen::getRows(
                                                                $this->_mailing_id, $this->_job_id, 
                                                                $this->_is_distinct, $this->_url_id,
                                                                $offset, $rowCount, $sort);
            break;

        default:
            return null;
        }
    }

    /**
     * name of export file.
     *
     * @param string $output type of output
     * @return string name of the file
     */
    function getExportFileName( $output = 'csv') {
    }

    function eventToTitle() {
        static $events = null;

        if (empty($events)) {
            $events = array(
                'queue'     => ts('Intended Recipients'),
                'delivered' => ts('Successful Deliveries'),
                'bounce'    => ts('Bounces'),
                'forward'   => ts('Forwards'),
                'reply'     => $this->_is_distinct 
                            ? ts('Unique Replies') 
                            : ts('Replies'),
                'unsubscribe' => ts('Unsubscribe Requests'),
                'click'     => $this->_is_distinct 
                            ? ts('Unique Click-throughs') 
                            : ts('Click-throughs'),
                'opened'    => $this->_is_distinct
                            ? ts('Unique Tracked Opens')
                            : ts('Tracked Opens')
            );
        }
        return $events[$this->_event_type];
    }

    function getTitle() {
        return $this->eventToTitle();
    }
}//end of class


