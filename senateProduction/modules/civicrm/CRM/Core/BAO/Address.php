<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Core/DAO/Address.php';

/**
 * This is class to handle address related functions
 */
class CRM_Core_BAO_Address extends CRM_Core_DAO_Address 
{
    /**
     * Should we overwrite existing address, total hack for now
     * Please do not use this hack in other places, its totally gross
     */
    static $_overwrite = true;

    /**
     * takes an associative array and creates a address
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param boolean  $fixAddress   true if you need to fix (format) address values
     *                               before inserting in db
     *
     * @return array $blocks array of created address 
     * @access public
     * @static
     */
    static function create( &$params, $fixAddress, $entity = null ) 
    {
        if ( ! isset( $params['address'] ) ||
             ! is_array( $params['address'] ) ) {
            return;
        }

        $addresses = array( );
        $contactId = null;
        if ( ! $entity ) {
            $contactId = $params['contact_id'];
            //get all the addresses for this contact
            $addresses = self::allAddress( $contactId );
        } else {
            // get all address from location block
            $entityElements = array( 'entity_table' => $params['entity_table'],
                                     'entity_id'    => $params['entity_id']);
            $addresses = self::allEntityAddress( $entityElements );
        }

        $isPrimary = $isBilling = true;
        $blocks    = array( );

        $updateBlankLocInfo = CRM_Utils_Array::value( 'updateBlankLocInfo', $params, false );

        require_once "CRM/Core/BAO/Block.php";
        foreach ( $params['address'] as $key => $value ) {
            if ( !is_array( $value ) ) {
                continue;
            }

            if ( ! empty( $addresses ) && array_key_exists( $value['location_type_id'], $addresses ) ) {
                $value['id'] = $addresses[ $value['location_type_id'] ];
            }
            
            $addressExists = self::dataExists( $value );

            // Note there could be cases when address info already exist ($value[id] is set) for a contact/entity 
            // BUT info is not present at this time, and therefore we should be really careful when deleting the block. 
            // $updateBlankLocInfo will help take appropriate decision. CRM-5969
            if ( isset( $value['id'] ) && !$addressExists && $updateBlankLocInfo ) {
                //delete the existing record
                CRM_Core_BAO_Block::blockDelete( 'Address', array( 'id' => $value['id'] ) );
                continue;
            } else if ( !$addressExists ) {
                continue;
            }
            
            if ( $isPrimary && $value['is_primary'] ) {
                $isPrimary = false;
            } else {
                $value['is_primary'] = 0;
            }
            
            if ( $isBilling && CRM_Utils_Array::value( 'is_billing', $value) ) {
                $isBilling = false;
            } else {
                $value['is_billing'] = 0;
            }
            $value['contact_id'] = $contactId;

            $blocks[] = self::add( $value, $fixAddress );
        }

        return $blocks;
    }

    /**
     * takes an associative array and adds phone 
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     * @param boolean  $fixAddress   true if you need to fix (format) address values
     *                               before inserting in db
     *
     * @return object       CRM_Core_BAO_Address object on success, null otherwise
     * @access public
     * @static
     */
    static function add( &$params, $fixAddress ) 
    {
        static $customFields = null;
        $address = new CRM_Core_DAO_Address( );

        // fixAddress mode to be done
        if ( $fixAddress ) {
            CRM_Core_BAO_Address::fixAddress( $params );
        }

        $address->copyValues($params);

        $address->save( );

        if ( $address->id ) {
            if ( ! $customFields ) {
                require_once 'CRM/Core/BAO/CustomField.php';
                require_once 'CRM/Core/BAO/CustomValueTable.php';
                $customFields = 
                    CRM_Core_BAO_CustomField::getFields( 'Address', false, true );
            }
            if ( ! empty( $customFields ) ) {
                $addressCustom = CRM_Core_BAO_CustomField::postProcess( $params, 
                                                                        $customFields, 
                                                                        $address->id,
                                                                        'Address', 
                                                                        true );
            }
            if ( ! empty( $addressCustom ) ) {
                CRM_Core_BAO_CustomValueTable::store( $addressCustom, 'civicrm_address', $address->id );
            }
        }

        return $address;
    }

    /**
     * format the address params to have reasonable values
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return void
     * @access public
     * @static
     */
    static function fixAddress( &$params ) 
    {
        if ( CRM_Utils_Array::value( 'billing_street_address', $params ) ) {
            //Check address is comming from online contribution / registration page
            //Fixed :CRM-5076
            $billing = array( 'street_address'    => 'billing_street_address',
                              'city'              => 'billing_city',
                              'postal_code'       => 'billing_postal_code',
                              'state_province'    => 'billing_state_province',
                              'state_province_id' => 'billing_state_province_id',
                              'country'           => 'billing_country',
                              'country_id'        => 'billing_country_id'
                              );
            
            foreach ( $billing as $key => $val ) {
                if ( $value = CRM_Utils_Array::value( $val, $params ) ) {
                    if ( CRM_Utils_Array::value( $key, $params ) ) {
                        unset($params[$val]);
                    } else {
                        //add new key and removed old
                        $params[$key] = $value;
                        unset($params[$val]);
                    }
                }
            }
        }
        
        /* Split the zip and +4, if it's in US format */
        if ( CRM_Utils_Array::value( 'postal_code', $params ) &&
             preg_match('/^(\d{4,5})[+-](\d{4})$/',
                        $params['postal_code'], 
                        $match) ) {
            $params['postal_code']        = $match[1];
            $params['postal_code_suffix'] = $match[2];
        }

        // add country id if not set
        if ( ( ! isset( $params['country_id'] ) || ! is_numeric( $params['country_id'] ) ) &&
             isset( $params['country'] ) ) {
            $country       = new CRM_Core_DAO_Country( );
            $country->name = $params['country'];
            if ( ! $country->find(true) ) {
                $country->name = null;
                $country->iso_code = $params['country'];
                $country->find(true);
            }
            $params['country_id'] = $country->id;
        }

        // add state_id if state is set
        if ( ( ! isset( $params['state_province_id'] ) || ! is_numeric( $params['state_province_id'] ) )
             && isset( $params['state_province'] ) ) {
            if ( ! empty( $params['state_province'] ) ) {
                $state_province       = new CRM_Core_DAO_StateProvince();
                $state_province->name = $params['state_province'];
                
                // add country id if present
                if ( isset( $params['country_id'] ) ) {
                    $state_province->country_id = $params['country_id'];
                }
                
                if ( ! $state_province->find(true) ) {
                    $state_province->name = null;
                    $state_province->abbreviation = $params['state_province'];
                    $state_province->find(true);
                }
                $params['state_province_id'] = $state_province->id;
            } else {
                $params['state_province_id'] = 'null';
            }
        }

            
        // currently copy values populates empty fields with the string "null"
        // and hence need to check for the string null
        if ( isset( $params['state_province_id'] ) && 
             is_numeric( $params['state_province_id'] ) &&
             ( !isset($params['country_id']) || empty($params['country_id'])) ) {
            // since state id present and country id not present, hence lets populate it
            // jira issue http://issues.civicrm.org/jira/browse/CRM-56
            $params['country_id'] = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_StateProvince',
                                                                 $params['state_province_id'],
                                                                 'country_id' );
        }

        //special check to ignore non numeric values if they are not
        //detected by formRule(sometimes happens due to internet latency), also allow user to unselect state/country
        if ( isset( $params['state_province_id'] ) ) {
            if ( ! trim( $params['state_province_id'] ) ) {
                $params['state_province_id'] = 'null'; 
            } else if ( ! is_numeric( $params['state_province_id'] ) ||
                        ( (int ) $params['state_province_id'] < 1000 ) ) {
                // CRM-3393 ( the hacky 1000 check)
                $params['state_province_id'] = 'null'; 
            }
        }

        if ( isset( $params['country_id'] ) ) {
            if ( ! trim( $params['country_id'] ) ) {
                $params['country_id'] = 'null'; 
            } else if ( ! is_numeric( $params['country_id'] ) ||
                        ( (int ) $params['country_id'] < 1000 ) ) {
                // CRM-3393 ( the hacky 1000 check)
                $params['country_id'] = 'null';
            }
        }

        // add state and country names from the ids
        if ( isset( $params['state_province_id'] ) && is_numeric( $params['state_province_id'] ) ) {
            $params['state_province'] = CRM_Core_PseudoConstant::stateProvinceAbbreviation( $params['state_province_id'] );
        }

        if ( isset( $params['country_id'] ) && is_numeric( $params['country_id'] ) ) {
            $params['country'] = CRM_Core_PseudoConstant::country($params['country_id']);
        }
        
        $config = CRM_Core_Config::singleton( );

        require_once 'CRM/Core/BAO/Preferences.php';
        $asp = CRM_Core_BAO_Preferences::value( 'address_standardization_provider' );
        // clean up the address via USPS web services if enabled
        if ($asp === 'USPS') {
            require_once 'CRM/Utils/Address/USPS.php';
            CRM_Utils_Address_USPS::checkAddress( $params );
        }
        
        // add latitude and longitude and format address if needed
        if ( ! empty( $config->geocodeMethod ) ) {
            require_once( str_replace('_', DIRECTORY_SEPARATOR, $config->geocodeMethod ) . '.php' );
            eval( $config->geocodeMethod . '::format( $params );' );
        } 
    }

    /**
     * Check if there is data to create the object
     *
     * @param array  $params    (reference ) an assoc array of name/value pairs
     *
     * @return boolean
     * 
     * @access public
     * @static
     */
    static function dataExists( &$params )
    {
        // if we should not overwrite, then the id is not relevant.
        if ( self::$_overwrite ) {
            //return true;
        }

        $config = CRM_Core_Config::singleton( );
        foreach ($params as $name => $value) {
            if ( in_array ($name, array ('is_primary', 'location_type_id', 'id', 'contact_id', 'is_billing', 'display' ) ) ) {
                continue;
            } else if ( !empty($value) ) {
                if ( substr( $name, 0, 14 ) == 'state_province' ) {
                    // hack to skip  - type first
                    // letter(s) - for state_province CRM-2649
                    $selectOption = ts('- type first letter(s) -');
                    if ( $value != $selectOption ) {
                        return true;
                    }
                } else if ( substr( $name, 0, 7 ) == 'country' ) { // name could be country or country id
                    // make sure its different from the default country
                    // iso code
                    $defaultCountry     =& $config->defaultContactCountry( );
                    // full name
                    $defaultCountryName =& $config->defaultContactCountryName( );
                    
                    if ( $defaultCountry ) {
                        if ( $value == $defaultCountry     ||
                             $value == $defaultCountryName ||
                             $value == $config->defaultContactCountry ) {
                            // do nothing
                        } else {
                            return true;
                        }
                    } else {
                        // return if null default
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array   $entityBlock   associated array of fields
     * @param boolean $microformat   if microformat output is required
     * @param int     $fieldName     conditional field name
     *
     * @return array  $addresses     array with address fields
     * @access public
     * @static
     */
    static function &getValues( &$entityBlock, $microformat = false, $fieldName = 'contact_id' )
    {
        if ( empty ( $entityBlock ) ) {
            return null;
        }
        $addresses = array( );
        $address = new CRM_Core_BAO_Address();
       
        if ( ! CRM_Utils_Array::value( 'entity_table' , $entityBlock ) ) {
            $address->$fieldName = CRM_Utils_Array::value( $fieldName ,$entityBlock );
        } else {
            $addressIds = array();
            $addressIds = self::allEntityAddress($entityBlock );
           
            if( !empty($addressIds[1]) ) {
                $address->id = $addressIds[1];
            } else {
                return $addresses;
            }
        } 
        //get primary address as a first block.
        $address->orderBy( 'is_primary desc, id' );
        
        $address->find( );
        
        $count = 1;
        while ( $address->fetch( ) ) {
            // deprecate reference.
            if ( $count > 1 ) { 
                foreach ( array( 'state', 'state_name', 'country', 'world_region' ) as $fld ) {
                    if ( isset( $address->$fld ) ) unset( $address->$fld );
                }
            }
            $stree = $address->street_address;
            $values = array( );
            CRM_Core_DAO::storeValues( $address, $values );
           
            // add state and country information: CRM-369
            if ( ! empty( $address->state_province_id ) ) {
                $address->state      = CRM_Core_PseudoConstant::stateProvinceAbbreviation( $address->state_province_id, false );
                $address->state_name = CRM_Core_PseudoConstant::stateProvince( $address->state_province_id, false );
            }

            if ( ! empty( $address->country_id ) ) {
                $address->country = CRM_Core_PseudoConstant::country( $address->country_id );
                
                //get world region 
                $regionId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Country', $address->country_id, 'region_id' );
                
                $address->world_region = CRM_Core_PseudoConstant::worldregion( $regionId );
            }
            
            $address->addDisplay( $microformat );

            $values['display'     ] = $address->display;
            $values['display_text'] = $address->display_text;

            $addresses[$count] = $values;
            
            //unset is_primary after first block. Due to some bug in earlier version
            //there might be more than one primary blocks, hence unset is_primary other than first
            if ( $count > 1 ) {
                unset($addresses[$count]['is_primary']);
            }

            $count++;
        }
        
        return $addresses;
    }
    
    /**
     * Add the formatted address to $this-> display
     *
     * @param NULL
     * 
     * @return void
     *
     * @access public
     *
     */
    function addDisplay( $microformat = false )
    {
        require_once 'CRM/Utils/Address.php';
        $fields = array(
                        'address_id'             => $this->id, // added this for CRM 1200
                        'address_name'           => str_replace( '', ' ', $this->name ), //CRM-4003
                        'street_address'         => $this->street_address,
                        'supplemental_address_1' => $this->supplemental_address_1,
                        'supplemental_address_2' => $this->supplemental_address_2,
                        'city'                   => $this->city,
                        'state_province_name'    => isset($this->state_name) ? $this->state_name : "",
                        'state_province'         => isset($this->state) ? $this->state : "",
                        'postal_code'            => isset($this->postal_code) ? $this->postal_code : "",
                        'postal_code_suffix'     => isset($this->postal_code_suffix) ? $this->postal_code_suffix : "",
                        'country'                => isset($this->country) ? $this->country : "",
                        'world_region'           => isset($this->world_region) ? $this->world_region : ""
                        );
        
        if( isset( $this->county_id ) && $this->county_id ) {
            $fields['county'] = CRM_Core_Pseudoconstant::county($this->county_id);
        } else {
            $fields['county'] = null;
        }

        $this->display      = CRM_Utils_Address::format($fields, null, $microformat);
        $this->display_text = CRM_Utils_Address::format($fields);
    }

    /**
     *
     * 
     *
     */ 
    static function setOverwrite( $overwrite ) 
    {
        self::$_overwrite = $overwrite;
    }

    /**
     * Get all the addresses for a specified contact_id, with the primary address being first
     *
     * @param int $id the contact id
     *
     * @return array  the array of adrress data
     * @access public
     * @static
     */
    static function allAddress( $id ) 
    {
        if ( !$id ) {
            return null;
        }

        $query = "
SELECT civicrm_address.id as address_id, civicrm_address.location_type_id as location_type_id
FROM civicrm_contact, civicrm_address 
WHERE civicrm_address.contact_id = civicrm_contact.id AND civicrm_contact.id = %1
ORDER BY civicrm_address.is_primary DESC, civicrm_address.location_type_id DESC, address_id ASC";
        $params = array( 1 => array( $id, 'Integer' ) );

        $addresses = array( );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        while ( $dao->fetch( ) ) {
            $addresses[$dao->location_type_id] = $dao->address_id;
        }
        return $addresses;
    }
    
     /**
     * Get all the addresses for a specified location_block id, with the primary address being first
     *
     * @param array $entityElements the array containing entity_id and
     * entity_table name
     *
     * @return array  the array of adrress data
     * @access public
     * @static
     */
    static function allEntityAddress( &$entityElements ) 
    {
        if ( empty($entityElements) ) {
            return $addresses;
        }
        
        $entityId    = $entityElements['entity_id'];
        $entityTable = $entityElements['entity_table'];

        $sql = "
SELECT civicrm_address.id as address_id    
FROM civicrm_loc_block loc, civicrm_location_type ltype, civicrm_address, {$entityTable} ev
WHERE ev.id = %1
  AND loc.id = ev.loc_block_id
  AND civicrm_address.id IN (loc.address_id, loc.address_2_id)
  AND ltype.id = civicrm_address.location_type_id
ORDER BY civicrm_address.is_primary DESC, civicrm_address.location_type_id DESC, address_id ASC ";
               
        $params = array( 1 => array( $entityId, 'Integer' ) );
        $addresses = array( );
        $dao =& CRM_Core_DAO::executeQuery( $sql, $params );
        $locationCount = 1;
        while ( $dao->fetch( ) ) {
            $addresses[$locationCount] = $dao->address_id;
            $locationCount++;
        }
        return $addresses;
    }

    static function addStateCountryMap( &$stateCountryMap,
                                        $defaults = null ) 
    {
        // first fix the statecountry map if needed
        if ( empty( $stateCountryMap ) ) {
            return;
        }
        
        $config = CRM_Core_Config::singleton( );
        if ( ! isset( $config->stateCountryMap ) ) {
            $config->stateCountryMap = array( );
        }

        $config->stateCountryMap = array_merge( $config->stateCountryMap,
                                                $stateCountryMap );
    }

    static function fixAllStateSelects( &$form, &$defaults ) 
    {
        $config = CRM_Core_Config::singleton( );

        if ( ! empty(  $config->stateCountryMap ) ) {
            foreach ( $config->stateCountryMap as $index => $match ) {
                if ( array_key_exists( 'state_province', $match ) &&
                     array_key_exists( 'country', $match ) ) {
                    require_once 'CRM/Contact/Form/Edit/Address.php';
                    CRM_Contact_Form_Edit_Address::fixStateSelect( $form,
                                                              $match['country'],
                                                              $match['state_province'],
                                                              CRM_Utils_Array::value( $match['country'],
                                                                                      $defaults ) );
                } else {
                    unset( $config->stateCountryMap[$index] );
                }
            }
        }
    }
    
    /* Function to get address sequence
     *
     * @return  array of address sequence.
     */
    static function addressSequence(  ) 
    {
        $config = CRM_Core_Config::singleton( );
        $addressSequence = $config->addressSequence();
        
        $countryState = $cityPostal = false;
        foreach ( $addressSequence as $key => $field ) {
            if ( in_array( $field, array( 'country', 'state_province' ) ) && !$countryState ) {
                $countryState = true;
                $addressSequence[$key] = 'country_state_province';
            } else if ( in_array( $field, array( 'city', 'postal_code' ) ) && !$cityPostal ) {
                $cityPostal = true;
                $addressSequence[$key] = 'city_postal_code';
            } else if (  in_array( $field, array( 'country', 'state_province', 'city', 'postal_code' ) ) ) {
                unset( $addressSequence[$key] );
            }
        }
        
        return $addressSequence;
    }
    
    /**
     * Parse given street address string in to street_name, 
     * street_unit, 'street_number and street_number_suffix
     * eg "54A Excelsior Ave. Apt 1C", or "917 1/2 Elm Street"
     * 
     * @param  string   Street address including number and apt
     *
     * @return array    $parseFields    parsed fields values.
     * @access public
     * @static
     */
    static function parseStreetAddress( $streetAddress ) 
    {
        $parseFields = array( 'street_name'          => '', 
                              'street_unit'          => '',
                              'street_number'        => '', 
                              'street_number_suffix' => '' );
        
        if ( empty( $streetAddress ) ) {
            return $parseFields;
        }
        
        $streetAddress = trim( $streetAddress );
        
        // get street number and suffix.
        $matches = array( );
        if ( preg_match( '/^[A-Za-z0-9]+([^\s]+)/', $streetAddress, $matches ) ) {
            $steetNumAndSuffix = $matches[0];
            
            // get street number.
            $matches = array( );
            if ( preg_match( '/^(\d+)/', $steetNumAndSuffix, $matches ) ) {
                $parseFields['street_number'] = $matches[0];
            }
            
            // consider remaining part as suffix.
            $suffix = preg_replace( '/^(\d+)/', '', $steetNumAndSuffix );
            $parseFields['street_number_suffix'] = trim( $suffix ); 
            
            // unset from main street address.
            $streetAddress = preg_replace( '/^[A-Za-z0-9]+([^\s]+)/', '', $streetAddress );
            $streetAddress = trim( $streetAddress );
        } else if ( preg_match( '/^(\d+)/', $streetAddress, $matches ) ) {
            $parseFields['street_number'] = $matches[0];
            // unset from main street address.
            $streetAddress = preg_replace( '/^(\d+)/', '', $streetAddress );
            $streetAddress = trim( $streetAddress );
        }
        
        // suffix might be like 1/2
        $matches = array( );
        if ( preg_match( '/^\d\/\d/', $streetAddress, $matches ) ) {
            $parseFields['street_number_suffix'] .= $matches[0];
            
            // unset from main street address.
            $streetAddress = preg_replace( '/^\d+\/\d+/', '', $streetAddress );
            $streetAddress = trim( $streetAddress );
        }
        
        // now get the street unit.
        // supportable street unit formats.
        $streetUnitFormats = array( 'APT',  'APARTMENT',  'BSMT',  'BASEMENT',  'BLDG', 'BUILDING', 
                                    'DEPT', 'DEPARTMENT', 'FL',    'FLOOR',     'FRNT', 'FRONT',  
                                    'HNGR', 'HANGER',     'LBBY',  'LOBBY',     'LOWR', 'LOWER',
                                    'OFC',  'OFFICE',     'PH',    'PENTHOUSE', 'TRLR', 'TRAILER', 
                                    'UPPR', 'RM',         'ROOM',  'SIDE',      'SLIP', 'KEY',  
                                    'LOT',  'PIER',       'REAR',  'SPC',       'SPACE', 
                                    'STOP', 'STE',        'SUITE', 'UNIT',      '#'  );
        
        $streetUnitPreg = '/('. implode( '|', $streetUnitFormats ) . ')(.+)?/i';
        $matches = array( );
        if ( preg_match( $streetUnitPreg, $streetAddress, $matches ) ) {
            $parseFields['street_unit'] = $matches[0];
            $streetAddress = str_replace( $matches[0], '', $streetAddress );
            $streetAddress = trim( $streetAddress );
        }
        
        // consider remaining string as street name.
        $parseFields['street_name'] = $streetAddress;
        
        return $parseFields;
    }
    
}


