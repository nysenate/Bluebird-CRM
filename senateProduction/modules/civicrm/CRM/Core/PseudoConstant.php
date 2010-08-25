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
 * Stores all constants and pseudo constants for CRM application.
 *
 * examples of constants are "Contact Type" which will always be either
 * 'Individual', 'Household', 'Organization'.
 *
 * pseudo constants are entities from the database whose values rarely
 * change. examples are list of countries, states, location types,
 * relationship types.
 *
 * currently we're getting the data from the underlying database. this
 * will be reworked to use caching.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */
class CRM_Core_PseudoConstant 
{
    /**
     * location type
     * @var array
     * @static
     */
    private static $locationType;
    
    /**
     * location vCard name
     * @var array
     * @static
     */
    private static $locationVcardName;
    
    /**
     * activity type
     * @var array
     * @static
     */
    private static $activityType = array( );
  
    /**
     * individual prefix
     * @var array
     * @static
     */
    private static $individualPrefix;

    /**
     * individual suffix
     * @var array
     * @static
     */
    private static $individualSuffix;

    /**
     * gender
     * @var array
     * @static
     */
    private static $gender;

    /**
     * im protocols
     * @var array
     * @static
     */
    private static $imProvider;

    /**
     * website protocols
     * @var array
     * @static
     */
    private static $websiteType;
    
    /**
     * im protocols
     * @var array
     * @static
     */
    private static $fromEmailAddress;

    /**
     * states, provinces
     * @var array
     * @static
     */
    private static $stateProvince;

    /**
     * counties
     * @var array
     * @static
     */
    private static $county;

    /** 
     * states/provinces abbreviations
     * @var array
     * @static
     */
    private static $stateProvinceAbbreviation;

    /**
     * country
     * @var array
     * @static
     */
    private static $country;


    /**
     * countryIsoCode
     * @var array
     * @static
     */
    private static $countryIsoCode;

    /**
     * tag
     * @var array
     * @static
     */
    private static $tag;

    /**
     * group
     * @var array
     * @static
     */
    private static $group;
    
    /**
     * groupIterator
     * @var mixed
     * @static
     */
    private static $groupIterator;

    /**
     * relationshipType
     * @var array
     * @static
     */
    private static $relationshipType;

    /**
     * civicrm groups that are not smart groups
     * @var array
     * @static
     */
    private static $staticGroup;

    /**
     * user framework groups
     * @var array
     * @static
     */
    private static $ufGroup;

    /**
     * custom groups
     * @var array
     * @static
     */
    private static $customGroup;

    /**
     * currency codes
     * @var array
     * @static
     */
    private static $currencyCode;
    
    /**
     * currency Symbols
     * @var array
     * @static
     */
    private static $currencySymbols;
    
    /**
     * project tasks
     * @var array
     * @static
     */
    private static $tasks;

    /**
     * preferred communication methods
     * @var array
     * @static
     */
    private static $pcm;
    
    /**
     * payment processor
     * @var array
     * @static
     */
    private static $paymentProcessor;
    
    /**
     * payment processor types
     * @var array
     * @static
     */
    private static $paymentProcessorType;
    
    /**
     * World Region
     * @var array
     * @static
     */
    private static $worldRegions;

    /**
     * honorType
     * @var array
     * @static
     */
    private static $honorType;

    /**
     * activity type
     * @var array
     * @static
     */
    private static $activityStatus = array( );
     
    /**
     * priority
     * @var array
     * @static
     */
    private static $priority = array( );

    /**
     * wysiwyg Editor
     * @var array
     * @static
     */
    private static $wysiwygEditor;

    /**
     * Mapping Types
     * @var array
     * @static
     */
    private static $mappingType;

    /**
     * Phone Types
     * @var array
     * @static
     */
    private static $phoneType;

    /**
     * Visibility
     * @var array
     * @static
     */
    private static $visibility;
    
    /**
     * Mail Protocols
     * @var array
     * @static
     */
    private static $mailProtocol;
    
    /**
     * Email Greeting
     * @var array
     * @static
     */
    private static $greeting = array( );

    /**
     * populate the object from the database. generic populate
     * method
     *
     * The static array $var is populated from the db
     * using the <b>$name DAO</b>. 
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @param array   $var        the associative array we will fill
     * @param string  $name       the name of the DAO
     * @param boolean $all        get all objects. default is to get only active ones.
     * @param string  $retrieve   the field that we are interested in (normally name, differs in some objects)
     * @param string  $filter     the field that we want to filter the result set with
     * @param string  $condition  the condition that gets passed to the final query as the WHERE clause
     *
     * @return void
     * @access public
     * @static
     */
    public static function populate( &$var, $name, $all = false, $retrieve = 'name',
                                     $filter = 'is_active', $condition = null, $orderby = null, $key = 'id' ) 
    {
        $cacheKey = "CRM_PC_{$name}_{$all}_{$key}_{$retrieve}_{$filter}_{$condition}_{$orderby}";
        $cache =& CRM_Utils_Cache::singleton( );
        $var = $cache->get( $cacheKey );
        if ( $var ) {
            return $var;
        }

        require_once(str_replace('_', DIRECTORY_SEPARATOR, $name) . ".php");
        eval( '$object = new ' . $name . '( );' );
        
        $object->selectAdd( );
        $object->selectAdd( "$key, $retrieve" );
        if ($condition) {
            $object->whereAdd($condition);
        }
        
        if (!$orderby) {
            $object->orderBy( $retrieve );
        } else {
            $object->orderBy( $orderby );
        }
        
        if ( ! $all ) {
            $object->$filter = 1;
        }
        
        $object->find( );
        $var = array( );
        while ( $object->fetch( ) ) {
            $var[$object->$key] = $object->$retrieve;
        }

        $cache->set( $cacheKey, $var );
    }

    /**
     * Flush given pseudoconstant so it can be reread from db
     * nex time it's requested.
     *
     * @access public
     * @static
     *
     * @param boolean $name pseudoconstant to be flushed
     *
     */
    public static function flush( $name )
    {
        self::$$name = null;
    }

    /**
     * Get all location types.
     *
     * The static array locationType is returned
     *
     * @access public
     * @static
     *
     * @param boolean $all - get All location types - default is to get only active ones.
     *
     * @return array - array reference of all location types.
     *
     */
    public static function &locationType( $all=false )
    {
        if ( ! self::$locationType ) {
            self::populate( self::$locationType, 'CRM_Core_DAO_LocationType', $all );
        }
        return self::$locationType;
    }


    /**
     * Get all location vCard names.
     *
     * The static array locationVcardName is returned
     *
     * @access public
     * @static
     *
     * @param boolean $all - get All location vCard names - default is to get only active ones.
     *
     * @return array - array reference of all location vCard names.
     *
     */
    public static function &locationVcardName( $all=false )
    {
        if ( ! self::$locationVcardName ) {
            self::populate( self::$locationVcardName, 'CRM_Core_DAO_LocationType', $all, 'vcard_name' );
        }
        return self::$locationVcardName;
    }

    /**
     * Get all Activty types.
     *
     * The static array activityType is returned
     * @param boolean $all - get All Activity  types - default is to get only active ones.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all activty types.
     */
    public static function &activityType( $all = true, 
                                          $includeCaseActivities = false, 
                                          $reset = false,
                                          $returnColumn = 'label' )
    {
        $index        = (int) $all . '_' . $returnColumn . '_' . (int) $includeCaseActivities;
        
        if ( ! array_key_exists( $index, self::$activityType ) || $reset ) {
            require_once 'CRM/Core/OptionGroup.php';
            $condition = null;
            if ( !$all ) {
                $condition    = 'AND filter = 0';
            } 
            $componentClause  = " v.component_id IS NULL";

            $componentIds = array( );
            require_once 'CRM/Core/Component.php';
            $compInfo     = CRM_Core_Component::getEnabledComponents( );
            
            // build filter for listing activity types only if their 
            // respective components are enabled
            foreach ( $compInfo as $compName => $compObj ) {
                if ( $compName !== 'CiviCase' ) {
                    $componentIds[] = $compObj->componentID;
                } else if ( $includeCaseActivities ) {
                    $componentIds[] = $compObj->componentID;
                }
                
            }
            
            if ( count($componentIds) ) {
                $componentIds     = implode( ',', $componentIds );
                $componentClause  = " ($componentClause OR v.component_id IN ($componentIds))";
            }
            $condition = $condition . ' AND ' . $componentClause;
            
            self::$activityType[$index] = CRM_Core_OptionGroup::values( 'activity_type', false, false, 
                                                                        false, $condition, $returnColumn );
        }
        return self::$activityType[$index];
    }

    /**
     * Get all Individual Prefix.
     *
     * The static array individualPrefix is returned
     *
     * @access public
     * @static
     *
     * @param boolean $all - get All Individual Prefix - default is to get only active ones.
     *
     * @return array - array reference of all individual prefix.
     *
     */
    public static function &individualPrefix( )
    {
        if ( ! self::$individualPrefix ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$individualPrefix = CRM_Core_OptionGroup::values('individual_prefix');
        }
        return self::$individualPrefix;
    }

    /**
     * Get all phone type
     * The static array phoneType is returned
     * 
     * @access public
     * @static
     *
     * @param boolean $all - get All phone type - default is to get
     * only active ones.
     *
     * @return array - array reference of all phone types.
     *
     */
    public static function &phoneType( )
    {
        if ( ! self::$phoneType ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$phoneType = CRM_Core_OptionGroup::values('phone_type');
        }
        return self::$phoneType;
    }
    
    /**
     * Get all Individual Suffix.
     *
     * The static array individualSuffix is returned
     *
     * @access public
     * @static
     *
     * @param boolean $all - get All Individual Suffix - default is to get only active ones.
     *
     * @return array - array reference of all individual suffix.
     *
     */
    public static function &individualSuffix( )
    {
        if ( ! self::$individualSuffix ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$individualSuffix = CRM_Core_OptionGroup::values('individual_suffix');
        }
        return self::$individualSuffix;
    }
    
    /**
     * Get all Gender.
     *
     * The static array gender is returned
     *
     * @access public
     * @static
     *
     * @param boolean $all - get All Gender - default is to get only active ones.
     *
     * @return array - array reference of all gender.
     *
     */
    public static function &gender( )
    {
        if ( ! self::$gender ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$gender = CRM_Core_OptionGroup::values('gender');
        }
        return self::$gender;
    }


    /**
     * Get all the IM Providers from database.
     *
     * The static array imProvider is returned, and if it's
     * called the first time, the <b>IM DAO</b> is used 
     * to get all the IM Providers.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all IM providers.
     *
     */
    public static function &IMProvider( ) 
    {
        if ( ! self::$imProvider ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$imProvider = CRM_Core_OptionGroup::values('instant_messenger_service');
        }        
        return self::$imProvider;
    }


    /**
     * Get all the website types from database.
     *
     * The static array websiteType is returned, and if it's
     * called the first time, the <b>Website DAO</b> is used 
     * to get all the Website Types.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all Website types.
     *
     */
    public static function &websiteType( ) 
    {
        if ( ! self::$websiteType ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$websiteType = CRM_Core_OptionGroup::values('website_type');
        }        
        return self::$websiteType;
    }

    /**
     * Get the all From Email Address from database.
     *
     * The static array $fromEmailAddress is returned, and if it's
     * called the first time, DAO is used 
     * to get all the From Email Address
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all From Email Address.
     */
    public static function &fromEmailAddress( ) 
    {
        if ( ! self::$fromEmailAddress ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$fromEmailAddress = CRM_Core_OptionGroup::values('from_email_address');
        }        
        return self::$fromEmailAddress;
    }
    
    /**
     * Get the all Mail Protocols from database.
     *
     * The static array mailProtocol is returned, and if it's
     * called the first time, the DAO is used 
     * to get all the Mail Protocol.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all Mail Protocols.
     */
    public static function &mailProtocol( ) 
    {
        if ( ! self::$mailProtocol ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$mailProtocol = CRM_Core_OptionGroup::values('mail_protocol');
        }        
        return self::$mailProtocol;
    }
    
    /**
     * Get all the State/Province from database.
     *
     * The static array stateProvince is returned, and if it's
     * called the first time, the <b>State Province DAO</b> is used 
     * to get all the States.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @param int $id -  Optional id to return
     * @return array - array reference of all State/Provinces.
     *
     */
    public static function &stateProvince($id = false, $limit = true)
    {
        if ( ( $id && !CRM_Utils_Array::value( $id, self::$stateProvince ) ) || !self::$stateProvince || !$id ) {
            $whereClause = false;
            $config = CRM_Core_Config::singleton();
            if ( $limit ) {
                // limit the state/province list to the countries specified in CIVICRM_PROVINCE_LIMIT
                $countryIsoCodes =& self::countryIsoCode();
                $limitCodes = $config->provinceLimit( );
                $limitIds = array();
                foreach ($limitCodes as $code) {
                    $limitIds = array_merge($limitIds, array_keys($countryIsoCodes, $code));
                }
                if ( !empty($limitIds) ) {
                    $whereClause = 'country_id IN (' . implode(', ', $limitIds) . ')';
                } else {
                    $whereClause = false;
                }
            }
            self::populate( self::$stateProvince, 'CRM_Core_DAO_StateProvince', true, 'name', 'is_active', $whereClause );

            // localise the province names if in an non-en_US locale
            global $tsLocale;
            if ($tsLocale != '' and $tsLocale != 'en_US') {
                $i18n =& CRM_Core_I18n::singleton();
                $i18n->localizeArray(self::$stateProvince, array('context' => 'province'));
                asort(self::$stateProvince);
            }
        }
        if ( $id ) {
            if ( array_key_exists( $id, self::$stateProvince) ) {
                return self::$stateProvince[$id];
            } else {
                $result = null;
                return $result;
            }
        }
        return self::$stateProvince;
    }

    /**
     * Get all the State/Province abbreviations from the database.
     * 
     * Same as above, except gets the abbreviations instead of the names.
     *
     * @access public
     * @static
     * @param int $id  -     Optional id to return
     * @return array - array reference of all State/Province abbreviations.
     */
    public static function &stateProvinceAbbreviation($id = false, $limit = true )
    {
        if ( $id > 1 ) {
            $query = "
SELECT abbreviation
FROM   civicrm_state_province
WHERE  id = %1";
            $params = array( 1 => array( $id, 'Integer' ) );
            return CRM_Core_DAO::singleValueQuery( $query, $params );
        }

        if (!self::$stateProvinceAbbreviation || !$id ) {

            // limit the state/province list to the countries specified in CIVICRM_PROVINCE_LIMIT, unless id is specified
            $whereClause = false;

            if ( $limit ) {
                $config = CRM_Core_Config::singleton();
                $countryIsoCodes =& self::countryIsoCode();
                $limitCodes = $config->provinceLimit( );
                $limitIds = array();
                foreach ($limitCodes as $code) {
                    $tmpArray   = array_keys($countryIsoCodes, $code);
                    
                    if (!empty($tmpArray)) {
                        $limitIds[] = array_shift($tmpArray);
                    }
                }
                if ( !empty($limitIds) ) {
                    $whereClause = 'country_id IN (' . implode(', ', $limitIds) . ')';
                }
            } 
            self::populate( self::$stateProvinceAbbreviation, 'CRM_Core_DAO_StateProvince', true, 'abbreviation', 'is_active', $whereClause );
        }

        if ($id) {
            if (array_key_exists( $id, self::$stateProvinceAbbreviation) ) {
                return self::$stateProvinceAbbreviation[$id];
            } else {
                $result = null;
                return $result;
            }
        }
        return self::$stateProvinceAbbreviation;
    }


    /**
     * Get all the countries from database.
     *
     * The static array country is returned, and if it's
     * called the first time, the <b>Country DAO</b> is used 
     * to get all the countries.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @param int $id - Optional id to return
     * @return array - array reference of all countries.
     *
     */
    public static function country($id = false, $applyLimit = true) 
    {
        if ( ( $id && !CRM_Utils_Array::value( $id, self::$country ) ) || !self::$country || !$id  ) {

            $config = CRM_Core_Config::singleton();
            $limitCodes = array( );
            
            if ( $applyLimit ) {
                // limit the country list to the countries specified in CIVICRM_COUNTRY_LIMIT
                // (ensuring it's a subset of the legal values)
                // K/P: We need to fix this, i dont think it works with new setting files
                $limitCodes = $config->countryLimit( );
                if ( ! is_array( $limitCodes ) ) {
                    $limitCodes = array( $config->countryLimit => 1);
                }
                
                $limitCodes = array_intersect(self::countryIsoCode(), $limitCodes);
            }

            if (count($limitCodes)) {
                $whereClause = "iso_code IN ('" . implode("', '", $limitCodes) . "')";
            } else {
                $whereClause = null;
            }

            self::populate( self::$country, 'CRM_Core_DAO_Country', true, 'name', 'is_active', $whereClause );

            // if default country is set, percolate it to the top
            if ( $config->defaultContactCountry( ) ) {
                $countryIsoCodes =& self::countryIsoCode();
                $defaultID = array_search($config->defaultContactCountry( ), $countryIsoCodes); 
                if ( $defaultID !== false ) {
                    $default[$defaultID] = CRM_Utils_Array::value($defaultID,self::$country);
                    self::$country = $default + self::$country;
                }
            }

            // localise the country names if in an non-en_US locale
            global $tsLocale;
            if ($tsLocale != '' and $tsLocale != 'en_US') {
                $i18n =& CRM_Core_I18n::singleton();
                $i18n->localizeArray(self::$country, array('context' => 'country'));
                asort(self::$country);
            }
        }
        if ($id) {
            if (array_key_exists( $id , self::$country)) {
                return self::$country[$id];
            } else {
                return null;
            }
        }
        return self::$country;
    }

    /**
     * Get all the country ISO Code abbreviations from the database.
     *
     * The static array countryIsoCode is returned, and if it's
     * called the first time, the <b>Country DAO</b> is used
     * to get all the countries' ISO codes.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all country ISO codes.
     *
     */
    public static function &countryIsoCode( $id = false )
    {
        if (!self::$countryIsoCode) {
            self::populate( self::$countryIsoCode, 'CRM_Core_DAO_Country',
                            true, 'iso_code');
        }
        if ($id) { 
            if (array_key_exists($id, self::$countryIsoCode)) { 
                return self::$countryIsoCode[$id]; 
            } else { 
                return null; 
            } 
        } 
        return self::$countryIsoCode;
    }

    /**
     * Get all the categories from database.
     *
     * The static array tag is returned, and if it's
     * called the first time, the <b>Tag DAO</b> is used 
     * to get all the categories.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all categories.
     *
     */
    public static function &tag()
    {
        if (!self::$tag) {
            self::populate( self::$tag, 'CRM_Core_DAO_Tag', true );
        }
        return self::$tag;
    }

    /**
    * Get all groups from database
    *
    * The static array group is returned, and if it's
    * called the first time, the <b>Group DAO</b> is used
    * to get all the groups.
    *
    * Note: any database errors will be trapped by the DAO.
    *
    * @param string $groupType     type of group(Access/Mailing) 
    * @param boolen $excludeHidden exclude hidden groups.
    *
    * @access public
    * @static
    *
    * @return array - array reference of all groups.
    *
    */
    public static function &allGroup( $groupType = null, $excludeHidden = true )
    {
        require_once 'CRM/Contact/BAO/Group.php';
        $condition = CRM_Contact_BAO_Group::groupTypeCondition( $groupType, $excludeHidden );

        if (!self::$group) {
            self::$group = array( );
        }

        $groupKey = $groupType ? $groupType : 'null';
        
        if ( ! isset( self::$group[$groupKey] ) ) {
            self::$group[$groupKey] = null;
            self::populate( self::$group[$groupKey], 'CRM_Contact_DAO_Group', false, 'title',
                            'is_active', $condition );
        }
        return self::$group[$groupKey];
    }
    
    /**
    * Create or get groups iterator (iterates over nested groups in a
    *  logical fashion)
    *
    * The GroupNesting instance is returned; it's created if this is being
    *  called for the first time
    *
    *
    * @access public
    * @static
    *
    * @return mixed - instance of CRM_Contact_BAO_GroupNesting
    *
    */
    public static function &groupIterator( $styledLabels = false )
    {
        if (!self::$groupIterator) {
            /*
             When used as an object, GroupNesting implements Iterator
             and iterates nested groups in a logical manner for us
            */
            require_once 'CRM/Contact/BAO/GroupNesting.php';
            self::$groupIterator = new CRM_Contact_BAO_GroupNesting( $styledLabels );
        }
        return self::$groupIterator;
    }

    /**
     * Get all permissioned groups from database
     *
     * The static array group is returned, and if it's
     * called the first time, the <b>Group DAO</b> is used 
     * to get all the groups.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @param string $groupType     type of group(Access/Mailing) 
     * @param boolen $excludeHidden exclude hidden groups.
     
     * @access public
     * @static
     *
     * @return array - array reference of all groups.
     *
     */
    public static function group( $groupType = null, $excludeHidden = true )
    {
        require_once 'CRM/Core/Permission.php';
        return CRM_Core_Permission::group( $groupType, $excludeHidden );
    }

    /**
     * Get all permissioned groups from database
     *
     * The static array group is returned, and if it's
     * called the first time, the <b>Group DAO</b> is used 
     * to get all the groups.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all groups.
     *
     */
    public static function &staticGroup( $onlyPublic = false,
                                         $groupType  = null )
    {
        if ( ! self::$staticGroup ) {
            $condition = 'saved_search_id = 0 OR saved_search_id IS NULL';
            if ( $onlyPublic ) {
                $condition .= " AND visibility != 'User and User Admin Only'";
            }
            if ( $groupType ) {
                require_once 'CRM/Contact/BAO/Group.php';
                $condition .= ' AND ' . CRM_Contact_BAO_Group::groupTypeCondition( $groupType );
            }
            
            self::populate( self::$staticGroup, 'CRM_Contact_DAO_Group', false, 'title', 'is_active', $condition, 'title' );
        }
        return self::$staticGroup;        
    }

    /**
     * Get all the custom groups
     *
     * @access public
     * @return array - array reference of all groups.
     * @static
     */
    public static function &customGroup( $reset = false )
    {
        if ( ! self::$customGroup || $reset ) {
            self::populate( self::$customGroup, 'CRM_Core_DAO_CustomGroup', false, 'title', 'is_active', null, 'title' );
        }
        return self::$customGroup;
    }

    /**
     * Get all the user framework groups
     *
     * @access public
     * @return array - array reference of all groups.
     * @static
     */
    public static function &ufGroup( )
    {
        if ( ! self::$ufGroup ) {
            self::populate( self::$ufGroup, 'CRM_Core_DAO_UFGroup', false, 'title', 'is_active', null, 'title' );
        }
        return self::$ufGroup;
    }

    /**
     * Get all the project tasks
     *
     * @access public
     * @return array - array reference of all tasks
     * @static
     */
    public static function &tasks( )
    {
        if ( ! self::$tasks ) {
            self::populate( self::$tasks, 'CRM_Project_DAO_Task', false, 'title', 'is_active', null, 'title' );
        }
        return self::$tasks;
    }

    /**
     * Get all Relationship Types  from database.
     *
     * The static array group is returned, and if it's
     * called the first time, the <b>RelationshipType DAO</b> is used 
     * to get all the relationship types.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @param string $valueColumnName db column name/label.
     *
     * @access public
     * @static
     *
     * @return array - array reference of all relationship types.
     */
    public static function &relationshipType( $valueColumnName = 'label' )
    {
        if ( !CRM_Utils_Array::value($valueColumnName, self::$relationshipType) ) {
            self::$relationshipType[$valueColumnName] = array( );
            
            //now we have name/label columns CRM-3336
            $column_a_b = "{$valueColumnName}_a_b";
            $column_b_a = "{$valueColumnName}_b_a";
            
            require_once 'CRM/Contact/DAO/RelationshipType.php';
            $relationshipTypeDAO = new CRM_Contact_DAO_RelationshipType();
            $relationshipTypeDAO->selectAdd();
            $relationshipTypeDAO->selectAdd("id, {$column_a_b}, {$column_b_a}, contact_type_a, contact_type_b, contact_sub_type_a, contact_sub_type_b");
            $relationshipTypeDAO->is_active = 1;
            $relationshipTypeDAO->find();
            while($relationshipTypeDAO->fetch()) {
                
                self::$relationshipType[$valueColumnName][$relationshipTypeDAO->id] 
                    = array( $column_a_b          => $relationshipTypeDAO->$column_a_b,
                             $column_b_a          => $relationshipTypeDAO->$column_b_a,
                             'contact_type_a'     => "$relationshipTypeDAO->contact_type_a",
                             'contact_type_b'     => "$relationshipTypeDAO->contact_type_b",
                             'contact_sub_type_a' => "$relationshipTypeDAO->contact_sub_type_a",
                             'contact_sub_type_b' => "$relationshipTypeDAO->contact_sub_type_b" );
            }
        }
        
        return self::$relationshipType[$valueColumnName];
    }

    /**
     * Get all the Currency Symbols from Database
     *
     * @access public
     * @return array - array reference of all Currency Symbols
     * @static
     */
    public static function &currencySymbols( $name = 'symbol' )
    {
        self::populate( self::$currencySymbols, 'CRM_Core_DAO_Currency', true, $name, null, null, 'name');
        return self::$currencySymbols;
    }

    /**
     * get all the ISO 4217 currency codes
     *
     * so far, we use this for validation only, so there's no point of putting this into the database
     *
     * @access public
     * @return array - array reference of all currency codes
     * @static
     */
    public static function &currencyCode()
    {
        if (!self::$currencyCode) {
            self::$currencyCode = array('AFN','ALL','DZD','USD','EUR','AOA','XCD','XCD','ARS','AMD',
                'AWG','AUD','EUR','AZM','BSD','BHD','BDT','BBD','BYR','EUR','BZD','XOF','BMD','INR',
                'BTN','BOB','BOV','BAM','BWP','NOK','BRL','USD','BND','BGN','XOF','BIF','KHR','XAF',
                'CAD','CVE','KYD','XAF','XAF','CLP','CLF','CNY','AUD','AUD','COP','COU','KMF','XAF',
                'CDF','NZD','CRC','XOF','HRK','CUP','CYP','CZK','DKK','DJF','XCD','DOP','USD','EGP',
                'SVC','USD','XAF','ERN','EEK','ETB','FKP','DKK','FJD','EUR','EUR','EUR','XPF','EUR',
                'XAF','GMD','GEL','EUR','GHC','GIP','EUR','DKK','XCD','EUR','USD','GTQ','GNF','GWP',
                'XOF','GYD','HTG','USD','AUD','EUR','HNL','HKD','HUF','ISK','INR','IDR','XDR','IRR',
                'IQD','EUR','ILS','EUR','JMD','JPY','JOD','KZT','KES','AUD','KPW','KRW','KWD','KGS',
                'LAK','LVL','LBP','ZAR','LSL','LRD','LYD','CHF','LTL','EUR','MOP','MKD','MGA','MWK',
                'MYR','MVR','XOF','MTL','USD','EUR','MRO','MUR','EUR','MXN','MXV','USD','MDL','EUR',
                'MNT','XCD','MAD','MZM','MMK','ZAR','NAD','AUD','NPR','EUR','ANG','XPF','NZD','NIO',
                'XOF','NGN','NZD','AUD','USD','NOK','OMR','PKR','USD','PAB','USD','PGK','PYG','PEN',
                'PHP','NZD','PLN','EUR','USD','QAR','EUR','ROL','RON','RUB','RWF','SHP','XCD','XCD',
                'EUR','XCD','WST','EUR','STD','SAR','XOF','CSD','EUR','SCR','SLL','SGD','SKK','SIT',
                'SBD','SOS','ZAR','EUR','LKR','SDD','SRD','NOK','SZL','SEK','CHF','CHW','CHE','SYP',
                'TWD','TJS','TZS','THB','USD','XOF','NZD','TOP','TTD','TND','TRY','TRL','TMM','USD',
                'AUD','UGX','UAH','AED','GBP','USD','USS','USN','USD','UYU','UZS','VUV','VEB','VND',
                'USD','USD','XPF','MAD','YER','ZMK','ZWD','XAU','XBA','XBB','XBC','XBD','XPD','XPT',
                'XAG','XFU','XFO','XTS','XXX');
        }
        return self::$currencyCode;
    }

    /**
     * Get all the County from database.
     *
     * The static array county is returned, and if it's
     * called the first time, the <b>County DAO</b> is used 
     * to get all the Counties.
     *
     * Note: any database errors will be trapped by the DAO.
     *
     * @access public
     * @static
     *
     * @param int $id -  Optional id to return
     * @return array - array reference of all Counties
     *
     */
    public static function &county($id = false)
    {
        if (!self::$county) {

            $config = CRM_Core_Config::singleton();
            // order by id so users who populate civicrm_county can have more control over sort by the order they load the counties
            self::populate( self::$county, 'CRM_Core_DAO_County', true, 'name', null, null, 'id');
        }
        if ($id) {
            if (array_key_exists($id, self::$county)) {
                return self::$county[$id];
            } else {
                return null;
            }
        }
        return self::$county;
    }

    /**
     * Get all the Preferred Communication Methods from database.
     *
     * @access public
     * @static
     *
     * @return array self::pcm - array reference of all preferred communication methods.
     *
     */
    public static function &pcm( ) 
    {
        if ( ! self::$pcm ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$pcm = CRM_Core_OptionGroup::values('preferred_communication_method');
        }        
        return self::$pcm;
    }

    /**
     * Get all active payment processors
     *
     * The static array paymentProcessor is returned
     *
     * @access public
     * @static
     *
     * @param boolean $all  - get payment processors     - default is to get only active ones.
     * @param boolean $test - get test payment processors
     *
     * @return array - array of all payment processors
     *
     */
    public static function &paymentProcessor( $all = false, $test = false, $additionalCond = null )
    {
        $condition  = "is_test = ";
        $condition .=  ( $test ) ? '1' : '0';

        if ( $additionalCond ) {
            $condition .= " AND ( $additionalCond ) ";
        }

        if ( ! self::$paymentProcessor ) {
            self::populate( self::$paymentProcessor, 'CRM_Core_DAO_PaymentProcessor', $all, 
                            'name', 'is_active', $condition, 'is_default desc, name' );
        }
        return self::$paymentProcessor;
    }

    /**
     * Get all active payment processors
     *
     * The static array paymentProcessorType is returned
     *
     * @access public
     * @static
     *
     * @param boolean $all  - get payment processors     - default is to get only active ones.
     *
     * @return array - array of all payment processor types
     *
     */
    public static function &paymentProcessorType( $all = false )
    {
        if ( ! self::$paymentProcessorType ) {
            self::populate( self::$paymentProcessorType, 'CRM_Core_DAO_PaymentProcessorType', $all, 
                            'title', 'is_active', null, 'is_default, title', 'name' );
        }
        return self::$paymentProcessorType;
    }

     /**
     * Get all the World Regions from Database
     *
     * @access public
     * @return array - array reference of all World Regions
     * @static
     */
    public static function &worldRegion( $id = false )
    {
        if ( !self::$worldRegions ) {
            self::populate( self::$worldRegions, 'CRM_Core_DAO_Worldregion', true, 'name', null, null, 'id');
        }

        if ( $id ) {
            if ( array_key_exists( $id , self::$worldRegions) ) {
                return self::$worldRegions[$id];
            } else {
                return null;
            }
        }

        return self::$worldRegions;
    }
    
    /**
     * Get all Honor Type.
     *
     * The static array honorType is returned
     *
     * @access public
     * @static
     *
     * @param boolean $all - get All Honor Type.
     *
     * @return array - array reference of all Honor Types.
     *
     */
    public static function &honor( )
    {
        if ( ! self::$honorType ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$honorType = CRM_Core_OptionGroup::values('honor_type');
        }
        return self::$honorType;
    }

    /**
     * Get all Activity Statuses.
     *
     * The static array activityStatus is returned
     *
     * @access public
     * @static
     * @return array - array reference of all activity statuses
     */
    public static function &activityStatus( $column = 'label' )
    {
        if ( ! array_key_exists($column, self::$activityStatus) ) {
            self::$activityStatus[$column] = array( );

            require_once 'CRM/Core/OptionGroup.php';
            self::$activityStatus[$column] = CRM_Core_OptionGroup::values('activity_status', false, 
                                                                          false, false, null, $column);
        }

        return self::$activityStatus[$column];
    }
    
    /**
     * Get all Priorities
     *
     * The static array Priority is returned
     *
     * @access public
     * @static
     * @return array - array reference of all Priority
     */
    public static function &priority(  )
    {
        if ( ! self::$priority ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$priority = CRM_Core_OptionGroup::values('priority');
        }
        
        return self::$priority;
    }

    /**
     * Get all WYSIWYG Editors.
     *
     * The static array wysiwygEditor is returned
     *
     * @access public
     * @static
     * @return array - array reference of all wysiwygEditors
     */
    public static function &wysiwygEditor( )
    {
        if ( ! self::$wysiwygEditor ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$wysiwygEditor = CRM_Core_OptionGroup::values('wysiwyg_editor');
        }
        return self::$wysiwygEditor;
    }

    /**
     * Get all Visibility levels.
     *
     * The static array visibility is returned
     *
     * @access public
     * @static
     *
     * @return array - array reference of all Visibility levels.
     *
     */
    public static function &visibility( $column = 'label' )
    {
        if ( ! self::$visibility ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$visibility = CRM_Core_OptionGroup::values( 'visibility',false,false, false, null, $column );
        }
        return self::$visibility;
    }
    
    /**
     * Get all mapping types
     *
     * @return array - array reference of all mapping types
     * @access public
     * @static
     */
    public static function &mappingTypes( )
    {
        if ( ! self::$mappingType ) {
            require_once 'CRM/Core/OptionGroup.php';
            self::$mappingType = CRM_Core_OptionGroup::values('mapping_type');
        }
        return self::$mappingType;
    }

    public static function &stateProvinceForCountry( $countryID ) {
        $query = "
SELECT civicrm_state_province.name name, civicrm_state_province.id id
  FROM civicrm_state_province
WHERE country_id = %1
ORDER BY name";
        $params = array( 1 => array( $countryID, 'Integer' ) );

        $dao = CRM_Core_DAO::executeQuery( $query, $params );

        $result = array( );
        while ( $dao->fetch( ) ) {
            $result[$dao->id] = $dao->name;
        }
        // localise the stateProvince names if in an non-en_US locale
        $config = CRM_Core_Config::singleton( );
        global $tsLocale;
        if ( $tsLocale != '' and $tsLocale != 'en_US' ) {
            $i18n =& CRM_Core_I18n::singleton();
            $i18n->localizeArray( $result );
            asort( $result );
        }
        return $result;
    }

    /**
     * Get all types of Greetings.
     *
     * The static array of greeting is returned
     *
     * @access public
     * @static
     *
     * @param $filter - get All Email Greetings - default is to get only active ones.
     *
     * @return array - array reference of all greetings.
     *
     */
	public static function greeting( $filter, $columnName = 'label' )
    { 
		$index = $filter['greeting_type'] .'_'.$columnName;
		$filterCondition = null;
	    if ( ! CRM_Utils_Array::value( $index, self::$greeting ) ) {
			if ( CRM_Utils_Array::value( 'contact_type', $filter ) ) {
				$filterVal = 'v.filter =';
				switch( $filter['contact_type'] ) {
				case 'Individual': 
					$filterVal .= "1";
					break;
				case 'Household':
					$filterVal .= "2";
					break;
				case 'Organization':
					$filterVal .= "3";
					break;
				}			
				$filterCondition .= "AND (v.filter = 0 OR {$filterVal}) "; 
			}	 
			   
            require_once 'CRM/Core/OptionGroup.php';
            self::$greeting[$index] = CRM_Core_OptionGroup::values( $filter['greeting_type'], null, null, null, 
																	$filterCondition, $columnName );
        }
        return self::$greeting[$index];
    }

    /**
     * Get all the Languages from database.
     *
     * @access public
     * @static
     *
     * @return array self::languages - array reference of all languages
     *
     */
    public static function &languages( ) 
    {
        static $_languages = null;

        if ( ! $_languages ) {
            require_once 'CRM/Core/OptionGroup.php';
            $_languages = CRM_Core_OptionGroup::values('languages');
        }        
        return $_languages;
    }

}


