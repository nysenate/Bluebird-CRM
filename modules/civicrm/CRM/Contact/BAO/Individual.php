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

/**
 * Class contains functions for individual contact type
 */
require_once 'CRM/Contact/DAO/Contact.php';

class CRM_Contact_BAO_Individual extends CRM_Contact_DAO_Contact
{
    /**
     * This is a contructor of the class.
     */
    function __construct() 
    {
    }
    
    /**
     * Function is used to format the individual contact values
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param array  $contact  contact object
     *
     * @return object CRM_Contact_BAO_Contact object
     * @access public
     * @static
     */
    static function format( &$params, &$contact )
    {
        if ( ! self::dataExists($params ) ) {
            return;
        }

        $sortName   = $displayName = '';
        $firstName  = CRM_Utils_Array::value('first_name'   , $params, '');
        $middleName = CRM_Utils_Array::value('middle_name'  , $params, '');
        $lastName   = CRM_Utils_Array::value('last_name'    , $params, '');
        $prefix_id  = CRM_Utils_Array::value('prefix_id'    , $params, '');
        $suffix_id  = CRM_Utils_Array::value('suffix_id'    , $params, '');
        
        // get prefix and suffix names
        $prefixes = CRM_Core_PseudoConstant::individualPrefix();
        $suffixes = CRM_Core_PseudoConstant::individualSuffix();
        
        $prefix = $suffix = null;
        if ( $prefix_id ) {
            $prefix = $prefixes[$prefix_id];
            $params['individual_prefix'] = $prefix;
        }
        if ( $suffix_id ) {
            $suffix = $suffixes[$suffix_id];
            $params['individual_suffix'] = $suffix;
        }

        $params['is_deceased'] = CRM_Utils_Array::value( 'is_deceased', $params, false );
        
        $individual = null;
        if ( $contact->id ) {
            $individual = new CRM_Contact_BAO_Contact();
            $individual->id = $contact->id;
            if ( $individual->find( true ) ) {
                
                //lets allow to update single name field though preserveDBName
                //but if db having null value and params contain value, CRM-4330.
                $useDBNames = array( );
                
                foreach ( array( 'last', 'middle', 'first' ) as $name ) {
                    $dbName  = "{$name}_name";
                    $value   = $individual->$dbName;
                    
                    // the db has name values
                    if ( $value && CRM_Utils_Array::value( 'preserveDBName', $params ) ) {
                        $useDBNames[] = $name; 
                    }
                }
                
                foreach ( array( 'prefix', 'suffix' ) as $name ) {
                    $dbName  = "{$name}_id";
                    $value   = $individual->$dbName;
                    if ( $value && CRM_Utils_Array::value( 'preserveDBName', $params ) ) {
                        $useDBNames[] = $name; 
                    }
                }
                
                // CRM-4430
                //1. preserve db name if want
                //2. lets get value from param if exists.
                //3. if not in params, lets get from db.
                
                foreach ( array( 'last', 'middle', 'first' ) as $name ) {
                    $phpName = "{$name}Name";
                    $dbName  = "{$name}_name";
                    $value   = $individual->$dbName;
                    if ( in_array( $name, $useDBNames ) ) {
                        $params[$dbName]  = $value;
                        $contact->$dbName = $value;
                        $$phpName         = $value;
                    } else if ( array_key_exists( $dbName, $params )  ) {
                        $$phpName = $params[$dbName];
                    } else if ( $value ) {
                        $$phpName = $value; 
                    }
                }

                foreach ( array( 'prefix', 'suffix' ) as $name ) {
                    $phpName = $name;
                    $dbName  = "{$name}_id";
                    $vals    = "{$name}es";

                    $value   = $individual->$dbName;
                    if ( in_array( $name, $useDBNames ) ) {
                        $params[$dbName]  = $value;
                        $contact->$dbName = $value;
                        if ( $value ) {
                            $temp     = $$vals;
                            $$phpName = $temp[$value];
                        } else {
                            $$phpName = null;
                        }
                    } else if ( array_key_exists( $dbName, $params ) ) {
                        $temp = $$vals;
                        // CRM-5278
                        if ( ! empty( $params[$dbName] ) ) {
                            $$phpName = CRM_Utils_Array::value( $params[$dbName], $temp );
                        }
                    } else if ( $value ) {
                        $temp = $$vals;
                        $$phpName = $temp[$value];
                    }
                }
            }
        }

        //first trim before further processing.
        foreach ( array( 'lastName', 'firstName', 'middleName' ) as $fld ) {
            $$fld = trim( $$fld );
        }
        
        if ( $lastName || $firstName || $middleName ) { 
            // make sure we have values for all the name fields.
            $formatted  = $params;
            $nameParams = array( 'first_name'        => $firstName,
                                 'middle_name'       => $middleName,
                                 'last_name'         => $lastName, 
                                 'individual_suffix' => $suffix,
                                 'individual_prefix' => $prefix,
                                 'prefix_id'         => $prefix_id,
                                 'suffix_id'         => $suffix_id );
            // make sure we have all the name fields.
            foreach ( $nameParams as $name => $value ) {
                if ( !CRM_Utils_Array::value( $name, $formatted ) && $value ) {
                    $formatted[$name] = $value;
                }
            }
            
            // make sure we have values for all the name fields.
            $formatted  = $params;
            $nameParams = array( 'first_name'        => $firstName,
                                 'middle_name'       => $middleName,
                                 'last_name'         => $lastName, 
                                 'individual_suffix' => $suffix,
                                 'individual_prefix' => $prefix,
                                 'prefix_id'         => $prefix_id,
                                 'suffix_id'         => $suffix_id );
            // make sure we have all the name fields.
            foreach ( $nameParams as $name => $value ) {
                if ( !CRM_Utils_Array::value( $name, $formatted ) && $value ) {
                    $formatted[$name] = $value;
                }
            }

            $tokens = array( );
            CRM_Utils_Hook::tokens( $tokens );
            $tokenFields = array( );
            foreach ( $tokens as $category => $catTokens ) {
                foreach ( $catTokens as $token ) {
                    $tokenFields[] = $token;
                }
            }
            require_once 'CRM/Utils/Address.php';
            require_once 'CRM/Core/BAO/Preferences.php';
            
            //build the sort name.
            $format = CRM_Core_BAO_Preferences::value( 'sort_name_format' );
            $format = str_replace( 'contact.', '', $format );
            $sortName = CRM_Utils_Address::format( $formatted, $format,
                                                   false, false, true, $tokenFields );
            $sortName = trim( $sortName );
            
            //build the display name.
            $format = CRM_Core_BAO_Preferences::value( 'display_name_format' );
            $format = str_replace( 'contact.', '', $format );
            $displayName = CRM_Utils_Address::format( $formatted, $format,
                                                      false, false, true, $tokenFields );
            $displayName = trim( $displayName );
        }
        
        //start further check for email.
        if ( empty( $sortName ) || empty( $displayName ) ) {
            $email = null;
            if ( CRM_Utils_Array::value( 'email', $params ) && 
                 is_array( $params['email'] ) ) {
                foreach ($params['email'] as $emailBlock) {
                    if ( isset( $emailBlock['is_primary'] ) ) {
                        $email = $emailBlock['email'];
                        break;
                    }
                }
            }
            $uniqId = CRM_Utils_Array::value( 'user_unique_id', $params );
            if ( !$email && $contact->id ) $email = CRM_Contact_BAO_Contact::getPrimaryEmail( $contact->id );
        }
        
        //now set the names.
        $names = array( 'sortName' => 'sort_name' , 'displayName' => 'display_name' );
        foreach ( $names as $value => $name ) {
            if ( empty( $$value ) ) {
                if (  $email ) {
                    $$value = $email; 
                } else if ( $uniqId ) {
                    $$value = $uniqId;
                }
            }
            //finally if we could not pass anything lets keep db.
            if ( !empty( $$value ) ) $contact->$name = $$value;
        }
        
        $format = CRM_Utils_Date::getDateFormat( 'birth' );
        if ( $date = CRM_Utils_Array::value('birth_date', $params) ) {
            if ( in_array( $format, array('dd-mm', 'mm/dd' ) ) ) {
                $separator = '/';
                if ( $format == 'dd-mm' ) {
                    $separator = '-';
                }
                $date = $date . $separator . '1902';
            } else if ( in_array( $format, array( 'yy-mm' ) ) ) {
                $date = $date .'-01';
            } else if ( in_array( $format, array( 'M yy' ) ) ) {
                $date = $date. '-01';
            } else if ( in_array( $format, array( 'yy' ) ) ) {
                $date = $date.'-01-01';
            }
            $contact->birth_date = CRM_Utils_Date::processDate($date) ;
        } else if ( $contact->birth_date ) {
            $contact->birth_date = CRM_Utils_Date::isoToMysql( $contact->birth_date );
        }
        
        if ( $date = CRM_Utils_Array::value('deceased_date', $params) ) {
            if ( in_array( $format, array('dd-mm', 'mm/dd' ) ) ) {
                $separator = '/';
                if ( $format == 'dd-mm' ) {
                    $separator = '-';
                }
                $date = $date . $separator . '1902';
            } else if ( in_array( $format, array( 'yy-mm' ) ) ) {
                $date = $date .'-01'; 
            } else if ( in_array( $format, array( 'M yy' ) ) ) {
                $date = $date. '-01';
            } else if ( in_array( $format, array( 'yy' ) ) ) {
                $date = $date.'-01-01';
            }
            
            $contact->deceased_date = CRM_Utils_Date::processDate($date) ;
        } else if ( $contact->deceased_date ) {
            $contact->deceased_date = CRM_Utils_Date::isoToMysql( $contact->deceased_date );
        }
        
        if ( $middle_name = CRM_Utils_Array::value('middle_name', $params)) {
            $contact->middle_name = $middle_name;
        }
      
        return $contact;
    }

    /**
     * regenerates display_name for contacts with given prefixes/suffixes
     *
     * @param array $ids     the array with the prefix/suffix id governing which contacts to regenerate
     * @param int   $action  the action describing whether prefix/suffix was UPDATED or DELETED
     *
     * @return void
     */
    static function updateDisplayNames( &$ids, $action ) 
    {
        // get the proper field name (prefix_id or suffix_id) and its value
        $fieldName = '';
        foreach ($ids as $key => $value) {
            switch ($key) {
            case 'individualPrefix':
                $fieldName = 'prefix_id';
                $fieldValue = $value;
                break 2;
            case 'individualSuffix':
                $fieldName = 'suffix_id';
                $fieldValue = $value;
                break 2;
            }
        }
        if ($fieldName == '') return;

        // query for the affected individuals
        $fieldValue = CRM_Utils_Type::escape($fieldValue, 'Integer');
        $contact = new CRM_Contact_BAO_Contact( );
        $contact->$fieldName = $fieldValue;
        $contact->find();

        // iterate through the affected individuals and rebuild their display_names
        require_once 'CRM/Contact/BAO/Contact.php';
        while ($contact->fetch()) {
            $contact = new CRM_Contact_BAO_Contact();
            $contact->id = $contact->contact_id;
            if ($action == CRM_Core_Action::DELETE) {
                $contact->$fieldName = 'NULL';
                $contact->save();
            }
            $contact->display_name = $contact->displayName();
            $contact->save();
        }
    }

    /**
     * creates display name
     *
     * @return string  the constructed display name
     */
    function displayName()
    {
        $prefix =& CRM_Core_PseudoConstant::individualPrefix();
        $suffix =& CRM_Core_PseudoConstant::individualSuffix();
        return str_replace('  ', ' ', trim($prefix[$this->prefix_id] . ' ' . $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name . ' ' . $suffix[$this->suffix_id]));
    }

    /** 
     * Check if there is data to create the object 
     * 
     * @param array  $params         (reference ) an assoc array of name/value pairs 
     * 
     * @return boolean 
     * @access public 
     * @static 
     */ 
    static function dataExists( &$params ) 
    {
        if ( $params['contact_type'] == 'Individual' ) {
            return true; 
        } 

        return false;
    }

}


