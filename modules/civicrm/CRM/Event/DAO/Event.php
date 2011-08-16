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
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Event_DAO_Event extends CRM_Core_DAO
{
    /**
     * static instance to hold the table name
     *
     * @var string
     * @static
     */
    static $_tableName = 'civicrm_event';
    /**
     * static instance to hold the field values
     *
     * @var array
     * @static
     */
    static $_fields = null;
    /**
     * static instance to hold the FK relationships
     *
     * @var string
     * @static
     */
    static $_links = null;
    /**
     * static instance to hold the values that can
     * be imported / apu
     *
     * @var array
     * @static
     */
    static $_import = null;
    /**
     * static instance to hold the values that can
     * be exported / apu
     *
     * @var array
     * @static
     */
    static $_export = null;
    /**
     * static value to see if we should log any modifications to
     * this table in the civicrm_log table
     *
     * @var boolean
     * @static
     */
    static $_log = true;
    /**
     * Event
     *
     * @var int unsigned
     */
    public $id;
    /**
     * Event Title (e.g. Fall Fundraiser Dinner)
     *
     * @var string
     */
    public $title;
    /**
     * Brief summary of event. Text and html allowed. Displayed on Event Registration form and can be used on other CMS pages which need an event summary.
     *
     * @var text
     */
    public $summary;
    /**
     * Full description of event. Text and html allowed. Displayed on built-in Event Information screens.
     *
     * @var text
     */
    public $description;
    /**
     * Event Type ID.Implicit FK to civicrm_option_value where option_group = event_type.
     *
     * @var int unsigned
     */
    public $event_type_id;
    /**
     * Should we expose the participant list? Implicit FK to civicrm_option_value where option_group = participant_listing.
     *
     * @var int unsigned
     */
    public $participant_listing_id;
    /**
     * Public events will be included in the iCal feeds. Access to private event information may be limited using ACLs.
     *
     * @var boolean
     */
    public $is_public;
    /**
     * Date and time that event starts.
     *
     * @var datetime
     */
    public $start_date;
    /**
     * Date and time that event ends. May be NULL if no defined end date/time
     *
     * @var datetime
     */
    public $end_date;
    /**
     * If true, include registration link on Event Info page.
     *
     * @var boolean
     */
    public $is_online_registration;
    /**
     * Text for link to Event Registration form which is displayed on Event Information screen when is_online_registration is true.
     *
     * @var string
     */
    public $registration_link_text;
    /**
     * Date and time that online registration starts.
     *
     * @var datetime
     */
    public $registration_start_date;
    /**
     * Date and time that online registration ends.
     *
     * @var datetime
     */
    public $registration_end_date;
    /**
     * Maximum number of registered participants to allow. After max is reached, a custom Event Full message is displayed. If NULL, allow unlimited number of participants.
     *
     * @var int unsigned
     */
    public $max_participants;
    /**
     * Message to display on Event Information page and INSTEAD OF Event Registration form if maximum participants are signed up. Can include email address/info about getting on a waiting list, etc. Text and html allowed.
     *
     * @var text
     */
    public $event_full_text;
    /**
     * Is this a PAID event? If true, one or more fee amounts must be set and a Payment Processor must be configured for Online Event Registration.
     *
     * @var boolean
     */
    public $is_monetary;
    /**
     * Contribution type assigned to paid event registrations for this event. Required if is_monetary is true.
     *
     * @var int unsigned
     */
    public $contribution_type_id;
    /**
     * Payment Processor for this Event (if is_monetary is true)
     *
     * @var int unsigned
     */
    public $payment_processor_id;
    /**
     * Include a map block on the Event Information page when geocode info is available and a mapping provider has been specified?
     *
     * @var boolean
     */
    public $is_map;
    /**
     * Is this Event enabled or disabled/cancelled?
     *
     * @var boolean
     */
    public $is_active;
    /**
     *
     * @var string
     */
    public $fee_label;
    /**
     * If true, show event location.
     *
     * @var boolean
     */
    public $is_show_location;
    /**
     * FK to Location Block ID
     *
     * @var int unsigned
     */
    public $loc_block_id;
    /**
     * Participant role ID. Implicit FK to civicrm_option_value where option_group = participant_role.
     *
     * @var int unsigned
     */
    public $default_role_id;
    /**
     * Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.
     *
     * @var text
     */
    public $intro_text;
    /**
     * Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.
     *
     * @var text
     */
    public $footer_text;
    /**
     * Title for Confirmation page.
     *
     * @var string
     */
    public $confirm_title;
    /**
     * Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.
     *
     * @var text
     */
    public $confirm_text;
    /**
     * Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.
     *
     * @var text
     */
    public $confirm_footer_text;
    /**
     * If true, confirmation is automatically emailed to contact on successful registration.
     *
     * @var boolean
     */
    public $is_email_confirm;
    /**
     * text to include above standard event info on confirmation email. emails are text-only, so do not allow html for now
     *
     * @var text
     */
    public $confirm_email_text;
    /**
     * FROM email name used for confirmation emails.
     *
     * @var string
     */
    public $confirm_from_name;
    /**
     * FROM email address used for confirmation emails.
     *
     * @var string
     */
    public $confirm_from_email;
    /**
     * comma-separated list of email addresses to cc each time a confirmation is sent
     *
     * @var string
     */
    public $cc_confirm;
    /**
     * comma-separated list of email addresses to bcc each time a confirmation is sent
     *
     * @var string
     */
    public $bcc_confirm;
    /**
     * FK to civicrm_option_value.
     *
     * @var int unsigned
     */
    public $default_fee_id;
    /**
     * FK to civicrm_option_value.
     *
     * @var int unsigned
     */
    public $default_discount_fee_id;
    /**
     * Title for ThankYou page.
     *
     * @var string
     */
    public $thankyou_title;
    /**
     * ThankYou Text.
     *
     * @var text
     */
    public $thankyou_text;
    /**
     * Footer message.
     *
     * @var text
     */
    public $thankyou_footer_text;
    /**
     * if true - allows the user to send payment directly to the org later
     *
     * @var boolean
     */
    public $is_pay_later;
    /**
     * The text displayed to the user in the main form
     *
     * @var text
     */
    public $pay_later_text;
    /**
     * The receipt sent to the user instead of the normal receipt text
     *
     * @var text
     */
    public $pay_later_receipt;
    /**
     * if true - allows the user to register multiple participants for event
     *
     * @var boolean
     */
    public $is_multiple_registrations;
    /**
     * if true - allows the user to register multiple registrations from same email address.
     *
     * @var boolean
     */
    public $allow_same_participant_emails;
    /**
     * Whether the event has waitlist support.
     *
     * @var boolean
     */
    public $has_waitlist;
    /**
     * Whether participants require approval before they can finish registering.
     *
     * @var boolean
     */
    public $requires_approval;
    /**
     * Expire pending but unconfirmed registrations after this many hours.
     *
     * @var int unsigned
     */
    public $expiration_time;
    /**
     * Text to display when the event is full, but participants can signup for a waitlist.
     *
     * @var text
     */
    public $waitlist_text;
    /**
     * Text to display when the approval is required to complete registration for an event.
     *
     * @var text
     */
    public $approval_req_text;
    /**
     * whether the event has template
     *
     * @var boolean
     */
    public $is_template;
    /**
     * Event Template Title
     *
     * @var string
     */
    public $template_title;
    /**
     * FK to civicrm_contact, who created this event
     *
     * @var int unsigned
     */
    public $created_id;
    /**
     * Date and time that event was created.
     *
     * @var datetime
     */
    public $created_date;
    /**
     * 3 character string, value from config setting or input via user.
     *
     * @var string
     */
    public $currency;
    /**
     * The campaign for which this event has been created.
     *
     * @var int unsigned
     */
    public $campaign_id;
    /**
     * class constructor
     *
     * @access public
     * @return civicrm_event
     */
    function __construct()
    {
        parent::__construct();
    }
    /**
     * return foreign links
     *
     * @access public
     * @return array
     */
    function &links()
    {
        if (!(self::$_links)) {
            self::$_links = array(
                'payment_processor_id' => 'civicrm_payment_processor:id',
                'loc_block_id' => 'civicrm_loc_block:id',
                'created_id' => 'civicrm_contact:id',
                'campaign_id' => 'civicrm_campaign:id',
            );
        }
        return self::$_links;
    }
    /**
     * returns all the column names of this table
     *
     * @access public
     * @return array
     */
    function &fields()
    {
        if (!(self::$_fields)) {
            self::$_fields = array(
                'id' => array(
                    'name' => 'id',
                    'type' => CRM_Utils_Type::T_INT,
                    'required' => true,
                ) ,
                'event_title' => array(
                    'name' => 'title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Event Title') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'import' => true,
                    'where' => 'civicrm_event.title',
                    'headerPattern' => '/(event.)?title$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'summary' => array(
                    'name' => 'summary',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Event Summary') ,
                    'rows' => 4,
                    'cols' => 60,
                ) ,
                'event_description' => array(
                    'name' => 'description',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Event Description') ,
                    'rows' => 8,
                    'cols' => 60,
                ) ,
                'event_type_id' => array(
                    'name' => 'event_type_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Event Type ID') ,
                ) ,
                'participant_listing_id' => array(
                    'name' => 'participant_listing_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Participant Listing ID') ,
                ) ,
                'is_public' => array(
                    'name' => 'is_public',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Is Event Public') ,
                    'default' => '',
                ) ,
                'event_start_date' => array(
                    'name' => 'start_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Event Start Date') ,
                    'import' => true,
                    'where' => 'civicrm_event.start_date',
                    'headerPattern' => '/^start|(s(tart\s)?date)$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'event_end_date' => array(
                    'name' => 'end_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Event End Date') ,
                    'import' => true,
                    'where' => 'civicrm_event.end_date',
                    'headerPattern' => '/^end|(e(nd\s)?date)$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'is_online_registration' => array(
                    'name' => 'is_online_registration',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Is Online Registration') ,
                ) ,
                'registration_link_text' => array(
                    'name' => 'registration_link_text',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Event Registration Link Text') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'registration_start_date' => array(
                    'name' => 'registration_start_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Registration Start Date') ,
                ) ,
                'registration_end_date' => array(
                    'name' => 'registration_end_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Registration End Date') ,
                ) ,
                'max_participants' => array(
                    'name' => 'max_participants',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Max Participants') ,
                    'default' => 'UL',
                ) ,
                'event_full_text' => array(
                    'name' => 'event_full_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Event Information') ,
                    'rows' => 4,
                    'cols' => 60,
                ) ,
                'is_monetary' => array(
                    'name' => 'is_monetary',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'contribution_type_id' => array(
                    'name' => 'contribution_type_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'payment_processor_id' => array(
                    'name' => 'payment_processor_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Core_DAO_PaymentProcessor',
                ) ,
                'is_map' => array(
                    'name' => 'is_map',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'is_active' => array(
                    'name' => 'is_active',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'fee_label' => array(
                    'name' => 'fee_label',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Fee Label') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'import' => true,
                    'where' => 'civicrm_event.fee_label',
                    'headerPattern' => '/^fee|(f(ee\s)?label)$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'is_show_location' => array(
                    'name' => 'is_show_location',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('show location') ,
                    'default' => '',
                ) ,
                'loc_block_id' => array(
                    'name' => 'loc_block_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Core_DAO_LocBlock',
                ) ,
                'default_role_id' => array(
                    'name' => 'default_role_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Participant Role') ,
                    'import' => true,
                    'where' => 'civicrm_event.default_role_id',
                    'headerPattern' => '',
                    'dataPattern' => '',
                    'export' => true,
                    'default' => '',
                ) ,
                'intro_text' => array(
                    'name' => 'intro_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Introductory Message') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'footer_text' => array(
                    'name' => 'footer_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Footer Message') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'confirm_title' => array(
                    'name' => 'confirm_title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Confirmation Title') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'default' => 'UL',
                ) ,
                'confirm_text' => array(
                    'name' => 'confirm_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Confirm Text') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'confirm_footer_text' => array(
                    'name' => 'confirm_footer_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Footer Text') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'is_email_confirm' => array(
                    'name' => 'is_email_confirm',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Is confirm email') ,
                ) ,
                'confirm_email_text' => array(
                    'name' => 'confirm_email_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Confirmation Email Text') ,
                    'rows' => 4,
                    'cols' => 50,
                ) ,
                'confirm_from_name' => array(
                    'name' => 'confirm_from_name',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Confirm From Name') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'confirm_from_email' => array(
                    'name' => 'confirm_from_email',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Confirm From Email') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'cc_confirm' => array(
                    'name' => 'cc_confirm',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Cc Confirm') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'bcc_confirm' => array(
                    'name' => 'bcc_confirm',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Bcc Confirm') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                ) ,
                'default_fee_id' => array(
                    'name' => 'default_fee_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'default_discount_fee_id' => array(
                    'name' => 'default_discount_fee_id',
                    'type' => CRM_Utils_Type::T_INT,
                ) ,
                'thankyou_title' => array(
                    'name' => 'thankyou_title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('ThankYou Title') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'default' => 'UL',
                ) ,
                'thankyou_text' => array(
                    'name' => 'thankyou_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('ThankYou Text') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'thankyou_footer_text' => array(
                    'name' => 'thankyou_footer_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Footer Text') ,
                    'rows' => 6,
                    'cols' => 50,
                ) ,
                'is_pay_later' => array(
                    'name' => 'is_pay_later',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'pay_later_text' => array(
                    'name' => 'pay_later_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Pay Later Text') ,
                ) ,
                'pay_later_receipt' => array(
                    'name' => 'pay_later_receipt',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Pay Later Receipt') ,
                ) ,
                'is_multiple_registrations' => array(
                    'name' => 'is_multiple_registrations',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'allow_same_participant_emails' => array(
                    'name' => 'allow_same_participant_emails',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Does Event allow multiple registrations from same email address?') ,
                ) ,
                'has_waitlist' => array(
                    'name' => 'has_waitlist',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Has Waitlist') ,
                ) ,
                'requires_approval' => array(
                    'name' => 'requires_approval',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                    'title' => ts('Requires Approval') ,
                ) ,
                'expiration_time' => array(
                    'name' => 'expiration_time',
                    'type' => CRM_Utils_Type::T_INT,
                    'title' => ts('Expiration Time') ,
                ) ,
                'waitlist_text' => array(
                    'name' => 'waitlist_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Waitlist Text') ,
                    'rows' => 4,
                    'cols' => 60,
                ) ,
                'approval_req_text' => array(
                    'name' => 'approval_req_text',
                    'type' => CRM_Utils_Type::T_TEXT,
                    'title' => ts('Approval Req Text') ,
                    'rows' => 4,
                    'cols' => 60,
                ) ,
                'is_template' => array(
                    'name' => 'is_template',
                    'type' => CRM_Utils_Type::T_BOOLEAN,
                ) ,
                'template_title' => array(
                    'name' => 'template_title',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Event Template Title') ,
                    'maxlength' => 255,
                    'size' => CRM_Utils_Type::HUGE,
                    'import' => true,
                    'where' => 'civicrm_event.template_title',
                    'headerPattern' => '/(template.)?title$/i',
                    'dataPattern' => '',
                    'export' => true,
                ) ,
                'created_id' => array(
                    'name' => 'created_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Contact_DAO_Contact',
                ) ,
                'created_date' => array(
                    'name' => 'created_date',
                    'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
                    'title' => ts('Event Created Date') ,
                ) ,
                'currency' => array(
                    'name' => 'currency',
                    'type' => CRM_Utils_Type::T_STRING,
                    'title' => ts('Currency') ,
                    'maxlength' => 3,
                    'size' => CRM_Utils_Type::FOUR,
                    'import' => true,
                    'where' => 'civicrm_event.currency',
                    'headerPattern' => '/cur(rency)?/i',
                    'dataPattern' => '/^[A-Z]{3}$/i',
                    'export' => true,
                ) ,
                'campaign_id' => array(
                    'name' => 'campaign_id',
                    'type' => CRM_Utils_Type::T_INT,
                    'FKClassName' => 'CRM_Campaign_DAO_Campaign',
                ) ,
            );
        }
        return self::$_fields;
    }
    /**
     * returns the names of this table
     *
     * @access public
     * @return string
     */
    function getTableName()
    {
        global $dbLocale;
        return self::$_tableName . $dbLocale;
    }
    /**
     * returns if this table needs to be logged
     *
     * @access public
     * @return boolean
     */
    function getLog()
    {
        return self::$_log;
    }
    /**
     * returns the list of fields that can be imported
     *
     * @access public
     * return array
     */
    function &import($prefix = false)
    {
        if (!(self::$_import)) {
            self::$_import = array();
            $fields = & self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('import', $field)) {
                    if ($prefix) {
                        self::$_import['event'] = & $fields[$name];
                    } else {
                        self::$_import[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_import;
    }
    /**
     * returns the list of fields that can be exported
     *
     * @access public
     * return array
     */
    function &export($prefix = false)
    {
        if (!(self::$_export)) {
            self::$_export = array();
            $fields = & self::fields();
            foreach($fields as $name => $field) {
                if (CRM_Utils_Array::value('export', $field)) {
                    if ($prefix) {
                        self::$_export['event'] = & $fields[$name];
                    } else {
                        self::$_export[$name] = & $fields[$name];
                    }
                }
            }
        }
        return self::$_export;
    }
}
