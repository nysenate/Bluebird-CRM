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

require_once 'CRM/Core/DAO/Domain.php';

/**
 *
 */
class CRM_Core_BAO_Domain extends CRM_Core_DAO_Domain {
    /**
     * Cache for the current domain object
     */
    static $_domain = null;
    
    /**
     * Cache for a domain's location array
     */
    private $_location = null;
    
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_DAO_Domain object
     * @access public
     * @static
     */
    static function retrieve(&$params, &$defaults)
    {
        return CRM_Core_DAO::commonRetrieve( 'CRM_Core_DAO_Domain', $params, $defaults );
    }
    
    /**
     * Get the domain BAO 
     *
     * @return null|object CRM_Core_BAO_Domain
     * @access public
     * @static
     */
    static function &getDomain( ) {
        static $domain = null;
        if ( ! $domain ) {
            $domain = new CRM_Core_BAO_Domain();
            $domain->id = CRM_Core_Config::domainID( );
            if ( ! $domain->find(true) ) {
                CRM_Core_Error::fatal( );
            }
        }
        return $domain;
    }

    static function version( ) {
        return CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Domain',
                                            CRM_Core_Config::domainID( ),
                                            'version' );
    }

    /**
     * Get the location values of a domain
     *
     * @param NULL
     * 
     * @return array        Location::getValues
     * @access public
     */
    function &getLocationValues() {
        if ($this->_location == null) {
            $params = array(
                            'entity_id' => $this->id, 
                            'entity_table' => self::getTableName()
                            );
            $this->_location = CRM_Core_BAO_Location::getValues($params,  true);

            if ( empty($this->_location) ) {
                $this->_location = null;
            }
        }
        return $this->_location;
    }

    /**
     * Save the values of a domain
     *
     * @return domain array        
     * @access public
     */
    static function edit(&$params, &$id) {
        $domain     = new CRM_Core_DAO_Domain( );
        $domain->id = $id;
        $domain->copyValues( $params );
        $domain->save( );
        return $domain;
    }

    /**
     * Create a new domain
     *
     * @return domain array
     * @access public
     */
    static function create( $params ) {
        $domain = new CRM_Core_DAO_Domain( );
        $domain->copyValues( $params );
        $domain->save( );
        return $domain;
    }

    static function multipleDomains( ) {
        $session = CRM_Core_Session::singleton( );
        
        $numberDomains = $session->get( 'numberDomains' );
        if ( ! $numberDomains ) {
            $query = "SELECT count(*) from civicrm_domain";
            $numberDomains = CRM_Core_DAO::singleValueQuery( $query );
            $session->set( 'numberDomains', $numberDomains );
        }
        return $numberDomains > 1 ? true : false;
    }

    static function getNameAndEmail( ) 
    {
        require_once 'CRM/Core/OptionGroup.php';
        $fromEmailAddress = CRM_Core_OptionGroup::values( 'from_email_address', null, null, null, ' AND is_default = 1' );
        if ( !empty( $fromEmailAddress ) ) {
            require_once 'CRM/Utils/Mail.php';
            foreach ( $fromEmailAddress as $key => $value ) {
                $email    = CRM_Utils_Mail::pluckEmailFromHeader( $value );
                $fromName = CRM_Utils_Array::value( 1, explode('"', $value ) );
                break;
            }
            return array( $fromName, $email );
        }
        
        $url = CRM_Utils_System::url( 'civicrm/contact/domain', 
                                      'action=update&reset=1' );
        $status = ts( "There is no valid default from email address configured for the domain. You can configure here <a href='%1'>Configure From Email Address.</a>", array( 1 => $url ) );
        
        CRM_Core_Error::fatal( $status );
    }
    
    static function addContactToDomainGroup( $contactID ) {
        $groupID = self::getGroupId( );

        if ( $groupID ) {
            $contactIDs = array( $contactID );
            require_once 'CRM/Contact/DAO/GroupContact.php';
            CRM_Contact_BAO_GroupContact::addContactsToGroup( $contactIDs, $groupID );

            return $groupID;
        }
        return false;
    }

    static function getGroupId( ) {
        static $groupID = null;

        if ( $groupID ) {
            return $groupID;
        }

        if ( defined('CIVICRM_DOMAIN_GROUP_ID') && CIVICRM_DOMAIN_GROUP_ID ) {
            $groupID = CIVICRM_DOMAIN_GROUP_ID;
        } else if ( defined( 'CIVICRM_MULTISITE' ) && CIVICRM_MULTISITE ) {
            // create a group with that of domain name
            $title   = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Domain', 
                                                    CRM_Core_Config::domainID( ), 'name' );
            $groupID = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Group', 
                                                    $title, 'id', 'title' );
            if ( empty($groupID) && !empty($title) ) {
                $groupParams = array( 'title'            => $title,
                                      'is_active'        => 1,
                                      'no_parent'        => 1 );
                require_once 'CRM/Contact/BAO/Group.php';
                $group   = CRM_Contact_BAO_Group::create( $groupParams );
                $groupID = $group->id;
            }
        }
        return $groupID ? $groupID : false;
    }

    static function isDomainGroup( $groupId ) {
        $domainGroupID = self::getGroupId( );
        return $domainGroupID == $groupId ? true : false;
    }

    static function getChildGroupIds( ) {
        $domainGroupID = self::getGroupId( );
        $childGrps     = array();

        if ( $domainGroupID ) {
            require_once 'CRM/Contact/BAO/GroupNesting.php';
            $childGrps = CRM_Contact_BAO_GroupNesting::getChildGroupIds( $domainGroupID );
            $childGrps[] = $domainGroupID;
        }
        return $childGrps;
    }

    // function to retrieve a list of contact-ids that belongs to current domain/site.
    static function getContactList( ) {
        $siteGroups = CRM_Core_BAO_Domain::getChildGroupIds( );
        $siteContacts = array( );

        if ( ! empty( $siteGroups ) ) {
            $query = "
SELECT      cc.id
FROM        civicrm_contact cc
INNER JOIN  civicrm_group_contact gc ON 
           (gc.contact_id = cc.id AND gc.status = 'Added' AND gc.group_id IN (" . implode(',', $siteGroups) . "))";

            $dao =& CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch() ) {
                $siteContacts[] = $dao->id;
            }
        }
        return $siteContacts;
    }
}


