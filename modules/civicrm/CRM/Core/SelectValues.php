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
 * One place to store frequently used values in Select Elements. Note that
 * some of the below elements will be dynamic, so we'll probably have a 
 * smart caching scheme on a per domain basis
 * 
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */
class CRM_Core_SelectValues 
{
   
    /**CRM/Core/SelectValues.php
     * different types of phones
     * @static
     */
    static function &phoneType()
    {
        static $phoneType = null;
        if (!$phoneType) {
            $phoneType = array(
                ''       => ts('- select -'),
                'Phone'  => ts('Phone'),
                'Mobile' => ts('Mobile'),
                'Fax'    => ts('Fax'),
                'Pager'  => ts('Pager')
            );
        }
        return $phoneType;
    }

    /**
     * preferred mail format
     * @static
     */
    static function &pmf()
    {
        static $pmf = null;
        if (!$pmf) {
            $pmf = array(
                         'Both' => ts('Both'),
                         'HTML' => ts('HTML'),
                         'Text' => ts('Text')
            );
            
        }
        return $pmf;
    }
    
    /**
     * privacy options
     * @static
     */
    static function &privacy()
    {
        static $privacy = null;
        if (!$privacy) {
            $privacy = array(
                'do_not_phone' => ts('Do not phone'),
                'do_not_email' => ts('Do not email'),
                'do_not_mail'  => ts('Do not mail'),
                'do_not_sms'   => ts('Do not sms'),
                'do_not_trade' => ts('Do not trade')
            );
        }
        return $privacy;
    }

    /**
     * various pre defined contact super types
     * @static
     */
    static function &contactType( )
    {
        static $contactType = null;
        if (!$contactType) {
            require_once 'CRM/Contact/BAO/ContactType.php';
            $contactType = array( '' => ts('- any contact type -') );
            $contactType = $contactType + CRM_Contact_BAO_ContactType::basicTypePairs( );
        }
        return $contactType;
    }

    /**
     * various pre defined unit list
     * @static
     */
    static function &unitList($unitType = null)
    {
        static $unitList = null;
        if (!$unitList) {
            $unitList = array(
                              ''             => ts('- select -'),
                              'day'          => ts('day'),
                              'month'        => ts('month'),
                              'year'         => ts('year')
                              );
            if ( $unitType == 'duration' ) {
                $unitAdd = array(
                                 'lifetime'      => ts('lifetime')
                                 );
                $unitList = array_merge( $unitList, $unitAdd);
            }
        }
        return $unitList;
    }

    /**
     * various pre defined period types
     * @static
     */
    static function &periodType()
    {
        static $periodType = null;
        if (!$periodType) {
            $periodType = array(
                 ''             => ts('- select -'),
                 'rolling'      => ts('rolling'),
                 'fixed'        => ts('fixed')
             );
        }
        return $periodType;
    }

    /**
     * various pre defined member visibility options
     * @static
     */
    static function &memberVisibility()
    {
        static $visible = null;
        if (!$visible) {
            $visible = array(
                 'Public'       => ts('Public'),
                 'Admin'        => ts('Admin')
             );
        }
        return $visible;
    }

    /**
     * various pre defined event dates
     * @static
     */
    static function &eventDate()
    {
        static $eventDate = null;
        if (!$eventDate) {
            $eventDate = array(
                 ''             => ts('- select -'),
                 'start_date'   => ts('start date'),
                 'end_date'     => ts('end date'),
                 'join_date'    => ts('member since')
             );
        }
        return $eventDate;
    }

    /**
     * Extended property (custom field) data types
     * @static
     */
    static function &customDataType()
    {
        static $customDataType = null;
        if (!$customDataType) {
            $customDataType = array(
                ''        => ts('- select -'),
                'String'  => ts('Text'),
                'Int'     => ts('Integer'),
                'Float'   => ts('Decimal Number'),
                'Money'   => ts('Money'),
                'Text'    => ts('Memo'),
                'Date'    => ts('Date'),
                'File'    => ts('File'),
                'Boolean' => ts('Yes/No'),
                'Link'    => ts('Link'),
                'Auto-complete'  => ts('Auto-complete')
            );
        }
        return $customDataType;
    }
    
    /**
     * Custom form field types
     * @static
     */
    static function &customHtmlType()
    {
        static $customHtmlType = null;
        if (!$customHtmlType) {
            $customHtmlType = array(
                ''                        => ts('- select -'),
                'Text'                    => ts('Single-line input field (text or numeric)'),
                'TextArea'                => ts('Multi-line text box (textarea)'),
                'Select'                  => ts('Drop-down (select list)'),
                'Radio'                   => ts('Radio buttons'),
                'Checkbox'                => ts('Checkbox(es)'),
                'Select Date'             => ts('Date selector'),
                'File'                    => ts('File'),
                'Select State / Province' => ts('State / Province selector'),
                'Select Country'          => ts('Country selector'),
                'RichTextEditor'          => ts('Rich Text Editor'),
                'Auto-complete'           => ts('Contact Reference')
                );
        }
        return $customHtmlType;
    }
    
    /**
     * various pre defined extensions for dynamic properties and groups
     *
     * @static
     */
    static function &customGroupExtends()
    {
        static $customGroupExtends = null;
        if (!$customGroupExtends) {
            $customGroupExtends = array(
                                        'Activity'     => ts('Activities'),
                                        'Relationship' => ts('Relationships'),
                                        'Contribution' => ts('Contributions'),
                                        'Group'        => ts('Groups'),
                                        'Membership'   => ts('Memberships'),
                                        'Event'        => ts('Events'),
                                        'Participant'  => ts('Participants'),
                                        'ParticipantRole'      => ts('Participants (Role)'),
                                        'ParticipantEventName' => ts('Participants (Event Name)'),
                                        'ParticipantEventType' => ts('Participants (Event Type)'),
                                        'Pledge'       => ts('Pledges'),
                                        'Grant'        => ts('Grants'),
                                        'Address'      => ts('Addresses'),
                                        );
            $contactTypes = self::contactType( );
            unset( $contactTypes[''] ); 
            $contactTypes = !empty( $contactTypes ) ? array( 'Contact' => 'Contacts' ) 
                + $contactTypes : array( );
            $customGroupExtends = array_merge( $contactTypes, $customGroupExtends );
        }
        return $customGroupExtends;
    }
    
    /**
     * styles for displaying the custom data group
     *
     * @static
     */
    static function &customGroupStyle()
    {
        static $customGroupStyle = null;
        if (!$customGroupStyle) {
            $customGroupStyle = array(
                'Tab'    => ts('Tab'),
                'Inline' => ts('Inline')
            );
        }
        return $customGroupStyle;
    }

    /**
     * for displaying the uf group types
     *
     * @static
     */
    static function &ufGroupTypes()
    {
        static $ufGroupType = null;
        if (!$ufGroupType) {
            $ufGroupType = array(
                                  'Profile'           => ts('Profile'),
                                  'Search Profile'    => ts('Search Results'),
                                  );
            $config = CRM_Core_Config::singleton( );
            if ( $config->userFramework == 'Drupal' ) {
                $ufGroupType += array(
                                      'User Registration' => ts('Drupal User Registration'),
                                      'User Account'      => ts('View/Edit Drupal User Account') );
            }
        }
        return $ufGroupType;
    }


    /**
     * the status of a contact within a group
     *
     * @static
     */
    static function &groupContactStatus()
    {
        static $groupContactStatus = null;
        if (!$groupContactStatus) {
            $groupContactStatus = array(
                'Added'     => ts('Added'),
                'Removed'   => ts('Removed'),
                'Pending'   => ts('Pending')
            );
        }
        return $groupContactStatus;
    }

    /**
     * list of Group Types
     * @static
     */
    static function &groupType()
    {
        static $groupType = null;
        if (!$groupType) {
            $groupType = array(
                'query'  => ts('Dynamic'),
                'static' => ts('Static')
            );
        }
        return $groupType;
    }
  
    
    /**
     * compose the parameters for a date select object
     *
     * @param  $type    the type of date
     * @param  $format  date format ( QF format) 
     *
     * @return array         the date array
     * @static
     */
    static function &date( $type = null, $format = null, $minOffset = null, $maxOffset = null )
    {

        $date = array(
                        'addEmptyOption'   => true,
                        'emptyOptionText'  => ts('- select -'),
                        'emptyOptionValue' => ''
                      );
        
        if ( $format ) {
            $date['format'] = $format;
        } else {
            if ( $type ) {
                require_once 'CRM/Core/DAO/PreferencesDate.php';
                $dao = new CRM_Core_DAO_PreferencesDate( );
                $dao->name = $type;
                if ( ! $dao->find( true ) ) {
                    CRM_Core_Error::fatal( );
                }
            }

            if ( $type == 'creditCard' ) {
                $minOffset = $dao->start;
                $maxOffset = $dao->end;
                $date['format'] = $dao->date_format;
                $date['addEmptyOption']   = true;
                $date['emptyOptionText']  = ts('- select -');
                $date['emptyOptionValue'] = '';
            }
            
            if ( !CRM_Utils_Array::value( 'format', $date ) ) {
                $date['format'] = 'M d';
            } 
        }
        
        $year = date('Y');
        $date['minYear'] = $year - $minOffset;
        $date['maxYear'] = $year + $maxOffset;
        return $date;
       
    }

    /**
     * values for UF form visibility options
     *
     * @static
     */
    static function ufVisibility( $isGroup = false ) {
        static $_visibility = null;
        if ( ! $_visibility ) {
            $_visibility = array(
                                 'User and User Admin Only'  => ts('User and User Admin Only'),
                                 'Public Pages'              => ts('Public Pages'),
                                 'Public Pages and Listings' => ts('Public Pages and Listings'),
                                 );
            if ( $isGroup ) {
                unset( $_visibility['Public Pages and Listings'] );
            } 
        }
        return $_visibility;
    }

    /**
     * different type of Mailing Components
     *
     * @static
     * return array
     */
    static function &mailingComponents( ) {
        static $components = null;

        if (! $components ) {
            $components = array( 'Header'      => ts('Header'),
                                 'Footer'      => ts('Footer'),
                                 'Reply'       => ts('Reply Auto-responder'),
                                 'OptOut'      => ts('Opt-out Message'),
                                 'Subscribe'   => ts('Subscription Confirmation Request'),
                                 'Welcome'     => ts('Welcome Message'),
                                 'Unsubscribe' => ts('Unsubscribe Message'),
                                 'Resubscribe' => ts('Resubscribe Message'),
                                 );
        }
        return $components;
    }

    /**
     * Function to get hours
     *
     * 
     * @static
     */
    function getHours () 
    {
        for ($i = 0; $i <= 6; $i++ ) {
            $hours[$i] = $i;
        }
        return $hours;
    }

    /**
     * Function to get minutes
     *
     * 
     * @static
     */
    function getMinutes () 
    {
        for ($i = 0; $i < 60; $i = $i+15 ) {
            $minutes[$i] = $i;
        }
        return $minutes;
    }


    /**
     * Function to get the Map Provider 
     * 
     * @return array $map array of map providers
     * @static
     */
    static function &mapProvider()
    {
        static $map = null;
        if (!$map) {
            $map = array(
                         'Yahoo'  => ts('Yahoo'),
                         'Google' => ts('Google')
                         );
        }
        return $map;
    }

    /**
     * different type of Mailing Tokens
     *
     * @static
     * return array
     */
    static function &mailingTokens( ) 
    {
        static $tokens = null;

        if (! $tokens ) {
            $tokens = array( '{action.unsubscribe}'    => ts('Unsubscribe via email'),
                             '{action.unsubscribeUrl}' => ts('Unsubscribe via web page'),
                             '{action.resubscribe}'    => ts('Resubscribe via email'),
                             '{action.resubscribeUrl}' => ts('Resubscribe via web page'),
                             '{action.optOut}'         => ts('Opt out via email'),
                             '{action.optOutUrl}'      => ts('Opt out via web page'),
                             '{action.forward}'        => ts('Forward this email (link)'),
                             '{action.reply}'          => ts('Reply to this email (link)'),
                             '{action.subscribeUrl}'   => ts('Subscribe via web page'),
                             '{domain.name}'           => ts('Domain name'),
                             '{domain.address}'        => ts('Domain (organization) address'),
                             '{domain.phone}'          => ts('Domain (organization) phone'),
                             '{domain.email}'          => ts('Domain (organization) email'),
                             '{mailing.name}'          => ts('Mailing name'),
                             '{mailing.group}'         => ts('Mailing group')    
                          );
        }
        return $tokens;
    }
    
    /**
     * different type of Contact Tokens
     *
     * @static
     * return array
     */
    static function &contactTokens( ) 
    {
        static $tokens = null;
        if ( ! $tokens ) {
            require_once 'CRM/Contact/BAO/Contact.php';
            require_once 'CRM/Core/BAO/CustomField.php';
            $additionalFields =  array( 'checksum'     => array( 'title' => ts('Checksum') ),
                                        'contact_id'   => array( 'title' => ts('Internal Contact ID') ) );
            $exportFields = array_merge( CRM_Contact_BAO_Contact::exportableFields( ), $additionalFields );

            $values = array_merge( array_keys( $exportFields ) );
            unset($values[0]); 
            
            //FIXME:skipping some tokens for time being.
            $skipTokens = array( 'is_bulkmail', 'group', 'tag', 'contact_sub_type', 'note', 
                                 'is_deceased','deceased_date','legal_identifier','contact_sub_type', 'user_unique_id'
                                 );
            $customFields = array();
            $customFields = CRM_Core_BAO_CustomField::getFields('Individual');
            
            foreach($values as $key => $val) {
                if ( in_array($val, $skipTokens) ) {
                    continue;
                } 
                //keys for $tokens should be constant. $token Values are changed for Custom Fields. CRM-3734
                if ( $customFieldId = CRM_Core_BAO_CustomField::getKeyID( $val ) ) {
                    $tokens["{contact.$val}"] = $customFields[$customFieldId]['label']." :: ".$customFields[$customFieldId]['groupTitle'];
                } else {
                    $tokens["{contact.$val}"] = $exportFields[$val]['title'];
                }
            }

            // might as well get all the hook tokens to
            require_once 'CRM/Utils/Hook.php';
            $hookTokens = array( );
            CRM_Utils_Hook::tokens( $hookTokens );
            foreach ( $hookTokens as $category => $tokenValues ) {
                foreach ( $tokenValues as $key => $value ) {
                    if (is_numeric( $key )) {
                        $key = $value;
                    }
                    if (!preg_match('/^\{[^\}]+\}$/', $key)) {
                        $key = '{' . $key . '}';
                    }
                    if (preg_match('/^\{([^\}]+)\}$/', $value, $matches)) {
                        $value = $matches[1];
                    }
                    $tokens[$key] = $value;
                }
            }
        }
        
        return $tokens;
    }
    
    /**
     * get qf mappig for all date parts.
     *
     */
    static function &qfDatePartsMapping( )
    {
        static $qfDatePartsMapping = null;
        if ( !$qfDatePartsMapping ) {
            $qfDatePartsMapping = array(
                                        '%b' => 'M',
                                        '%B' => 'F',
                                        '%d' => 'd',
                                        '%e' => 'j',
                                        '%E' => 'j',
                                        '%f' => 'S',
                                        '%H' => 'H',
                                        '%I' => 'h',
                                        '%k' => 'G',
                                        '%l' => 'g',
                                        '%m' => 'm',
                                        '%M' => 'i',
                                        '%p' => 'a',
                                        '%P' => 'A',
                                        '%Y' => 'Y'
                                        );
        }
        
        return $qfDatePartsMapping;
    }
    
    /**
     *  CiviCRM supported date input formats
     */
    static function getDatePluginInputFormats( ) {
        $dateInputFormats = array( 
                                  "mm/dd/yy"      => ts('mm/dd/yyyy (12/31/2009)'),
                                  "dd/mm/yy"      => ts('dd/mm/yyyy (31/12/2009)'),
                                  "yy-mm-dd"      => ts('yyyy-mm-dd (2009-12-31)'),
                                  "dd-mm-yy"      => ts('dd-mm-yyyy (31-12-2009)'),
                                  'dd.mm.yy'      => ts('dd.mm.yyyy (31.12.2009)'),
                                  "M d, yy"       => ts('M d, yyyy (Dec 31, 2009)'),
                                  'd M yy'        => ts('d M yyyy (31 Dec 2009)'),
                                  "MM d, yy"      => ts('MM d, yyyy (December 31, 2009)'),
                                  'd MM yy'       => ts('d MM yyyy (31 December 2009)'),
                                  "DD, d MM yy"   => ts('DD, d MM yyyy (Thursday, 31 December 2009)'),
                                  "mm/dd"         => ts('mm/dd (12/31)'),
                                  "dd-mm"         => ts('dd-mm (31-12)'),
                                  "yy-mm"         => ts('yyyy-mm (2009-12)'),
                                  'M yy'          => ts('M yyyy (Dec 2009)'),
                                  "yy"            => ts('yyyy (2009)')   
                                   );
        
        /*
         Year greater than 2000 get wrong result for following format
         echo date( 'Y-m-d', strtotime( '7 Nov, 2001') );
         echo date( 'Y-m-d', strtotime( '7 November, 2001') );
         Return current year
         expected :: 2001-11-07
         output   :: 2009-11-07
         However
         echo date( 'Y-m-d', strtotime( 'Nov 7, 2001') );
         echo date( 'Y-m-d', strtotime( 'November 7, 2001') );
         gives proper result
        */
        
        return $dateInputFormats;
    }
    
    /**
     * Map date plugin and actual format that is used by PHP 
     */
    static function datePluginToPHPFormats( ) {
        $dateInputFormats = array( "mm/dd/yy"      => 'm/d/Y',
                                   "dd/mm/yy"      => 'd/m/Y',
                                   "yy-mm-dd"      => 'Y-m-d',
                                   "dd-mm-yy"      => 'd-m-Y',
                                   "dd.mm.yy"      => 'd.m.Y',                                 
                                   "M d, yy"       => 'M j, Y',
                                   "d M yy"        => 'j M Y',
                                   "MM d, yy"      => 'F j, Y',
                                   "d MM yy"       => 'j F Y',
                                   "DD, d MM yy"   => 'l, j F Y',                                   
                                   "mm/dd"         => 'm/d',
                                   "dd-mm"         => 'd-m',
                                   "yy-mm"         => 'Y-m',
                                   "M yy"          => 'M Y',
                                   "yy"            => 'Y'
                                  );
        return $dateInputFormats;
    }
    
    /**
     * Time formats
     */
    static function getTimeFormats( ) {
        $timeFormats = array( '1' => ts( '12 Hours' ),
                              '2' => ts( '24 Hours' ) );
        return $timeFormats;
    }
}
