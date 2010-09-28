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

require_once 'CRM/Contact/DAO/GroupContactCache.php';

class CRM_Contact_BAO_GroupContactCache extends CRM_Contact_DAO_GroupContactCache {

    const
        NUM_CONTACTS_TO_INSERT = 200;

    /**
     * Check to see if we have cache entries for this group
     * if not, regenerate, else return
     *
     * @param int $groupID groupID of group that we are checking against
     *
     * @return boolean true if we did not regenerate, false if we did
     */
    static function check( $groupID ) {
        if ( empty( $groupID ) ) {
            return true;
        }

        if ( ! is_array( $groupID ) ) {
            $groupID = array( $groupID );
        }
        // note escapeString is a must here and we can't send the imploded value as second arguement to 
        // the executeQuery(), since that would put single quote around the string and such a string 
        // of comma separated integers would not work. 
        $groupID = CRM_Core_DAO::escapeString( implode( ', ', $groupID ) );

        $config  = CRM_Core_Config::singleton( );
        $smartGroupCacheTimeout = 
            isset( $config->smartGroupCacheTimeout ) && 
            is_numeric(  $config->smartGroupCacheTimeout ) ? $config->smartGroupCacheTimeout : 0;
        $query  = "
SELECT     g.id
FROM       civicrm_group g
WHERE      g.id IN ( {$groupID} ) AND g.saved_search_id = 1 AND 
          (g.cache_date IS NULL OR (TIMESTAMPDIFF(MINUTE, g.cache_date, NOW()) >= $smartGroupCacheTimeout))
";

        $dao      =& CRM_Core_DAO::executeQuery( $query );
        $groupIDs = array( );
        while ( $dao->fetch() ) {
            $groupIDs[] = $dao->id;
        }

        if ( empty( $groupIDs ) ) {
            return true;
        } else {
            self::add( $groupIDs );
            return false;
        }
    }

    static function add( $groupID ) {
        // first delete the current cache
        self::remove( $groupID );
        if ( ! is_array( $groupID ) ) {
            $groupID = array( $groupID );
        }

        $params['return.contact_id'] = 1;
        $params['offset']            = 0;
        $params['rowCount']          = 0;
        $params['sort']              = null;
        $params['smartGroupCache']   = false;

        require_once 'api/v2/Contact.php';
        
        $values = array( );
        foreach ( $groupID as $gid ) {
            $params['group'] = array( );
            $params['group'][$gid] = 1;

            // the below call update the cache table as a byproduct of the query
            $contacts = civicrm_contact_search( $params );
        }
    }

    static function store( &$groupID, &$values ) {
        $processed = false;

        // to avoid long strings, lets do NUM_CONTACTS_TO_INSERT values at a time
        while ( ! empty( $values ) ) {
            $processed = true;
            $input = array_splice( $values, 0, self::NUM_CONTACTS_TO_INSERT );
            $str   = implode( ',', $input );
            $sql = "REPLACE INTO civicrm_group_contact_cache (group_id,contact_id) VALUES $str;";
            CRM_Core_DAO::executeQuery( $sql );
        }

        // only update cache entry if we had any values
        if ( $processed ) {
            // also update the group with cache date information
            $now = date('YmdHis');
        } else {
            $now = 'null';
        }

        $groupIDs = implode( ',', $groupID );
        $sql = "
UPDATE civicrm_group
SET    cache_date = $now
WHERE  id IN ( $groupIDs )
";
        CRM_Core_DAO::executeQuery( $sql,
                                    CRM_Core_DAO::$_nullArray );
    }

    static function remove( $groupID = null, $onceOnly = true ) {
        static $invoked = false;

        // typically this needs to happy only once per instance
        // this is especially true in import, where we dont need 
        // to do this all the time
        // this optimization is done only when no groupID is passed
        // i.e. cache is reset for all groups
        if ( $onceOnly &&
             $invoked  &&
             $groupID == null ) {
            return;
        }
        
        if ( $groupID == null ) {
            $invoked = true;
        }

        $config = CRM_Core_Config::singleton( );
        $smartGroupCacheTimeout = 
            isset( $config->smartGroupCacheTimeout ) && is_numeric(  $config->smartGroupCacheTimeout ) ? $config->smartGroupCacheTimeout : 0;

        if ( ! isset( $groupID ) ) {
            $query = "
DELETE     g
FROM       civicrm_group_contact_cache g
INNER JOIN civicrm_contact c ON c.id = g.contact_id
WHERE      g.group_id IN (
    SELECT id
    FROM   civicrm_group
    WHERE  TIMESTAMPDIFF(MINUTE, cache_date, NOW()) >= $smartGroupCacheTimeout   
)
";

            $update = "
UPDATE civicrm_group g
SET    cache_date = null
WHERE  TIMESTAMPDIFF(MINUTE, cache_date, NOW()) >= $smartGroupCacheTimeout
";
            $params = array( );
        } else if ( is_array( $groupID ) ) {
            $query = "
DELETE     g
FROM       civicrm_group_contact_cache g
WHERE      g.group_id IN ( %1 )
";
            $update = "
UPDATE civicrm_group g
SET    cache_date = null
WHERE  id IN ( %1 )
";
            $groupIDs = implode( ', ', $groupID );
            $params = array( 1 => array( $groupIDs, 'String' ) );
        } else {
            $query = "
DELETE     g
FROM       civicrm_group_contact_cache g
WHERE      g.group_id = %1
";
            $update = "
UPDATE civicrm_group g
SET    cache_date = null
WHERE  id = %1
";
            $params = array( 1 => array( $groupID, 'Integer' ) );
        }

        CRM_Core_DAO::executeQuery( $query , $params );

        // also update the cache_date for these groups
        CRM_Core_DAO::executeQuery( $update, $params );
    }
    
    /**
     * load the smart group cache for a saved search
     */
    static function load( &$group ) {
        $groupID       = $group->id;
        $savedSearchID = $group->saved_search_id;

        $sql         = null;
        $idName      = 'id';
        $customClass = null;
        if ( $savedSearchID ) {
            require_once 'CRM/Contact/BAO/SavedSearch.php';
            $ssParams =& CRM_Contact_BAO_SavedSearch::getSearchParams($savedSearchID);
            $returnProperties = array();
            if (CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_SavedSearch',
                                             $savedSearchID,
                                             'mapping_id' ) ) {
                require_once "CRM/Core/BAO/Mapping.php";
                $fv =& CRM_Contact_BAO_SavedSearch::getFormValues($savedSearchID);
                $returnProperties = CRM_Core_BAO_Mapping::returnProperties( $fv );
            }

            if ( isset( $ssParams['customSearchID'] ) ) {
                // if custom search
                require_once 'CRM/Contact/BAO/SearchCustom.php';
                
                // we split it up and store custom class
                // so temp tables are not destroyed if they are used
                // hence customClass is defined above at top of function
                $customClass = CRM_Contact_BAO_SearchCustom::customClass( $ssParams['customSearchID'],
                                                                          $savedSearchID );
                $searchSQL   = $customClass->contactIDs( );
                $idName = 'contact_id';
            } else {
                $query = new CRM_Contact_BAO_Query($ssParams, $returnProperties, null,
                                                    false, false, 1,
                                                    true, true, false );
                $query->_useGroupBy = false;
                $searchSQL =& $query->searchQuery( 0, 0, null,
                                                   false, false,
                                                   false, true, true, null );
            }
            $groupID = CRM_Utils_Type::escape($groupID, 'Integer');
            $sql = $searchSQL . 
                " AND contact_a.id NOT IN ( 
                              SELECT contact_id FROM civicrm_group_contact 
                              WHERE civicrm_group_contact.status = 'Removed' 
                              AND   civicrm_group_contact.group_id = $groupID ) ";
        }

        if ( $sql ) {
            $sql .= " UNION ";
        }

        // lets also store the records that are explicitly added to the group
        // this allows us to skip the group contact LEFT JOIN
        $sql .= "
SELECT contact_id as $idName
FROM   civicrm_group_contact
WHERE  civicrm_group_contact.status = 'Added'
  AND  civicrm_group_contact.group_id = $groupID ";

        $dao = CRM_Core_DAO::executeQuery( $sql,
                                           CRM_Core_DAO::$_nullArray );

        $values = array( );
        while ( $dao->fetch( ) ) {
            $values[] = "({$groupID},{$dao->$idName})";
        }

        $groupIDs = array( $groupID );
        self::remove( $groupIDs );
        self::store ( $groupIDs, $values );

        if ( $group->children ) {
            require_once 'CRM/Contact/BAO/Group.php';
            $childrenIDs = explode( ',', $group->children );
            foreach ( $childrenIDs as $childID ) {
                $contactIDs =& CRM_Contact_BAO_Group::getMember( $childID, false );
                $values = array( );
                foreach ( $contactIDs as $contactID => $dontCare) {
                    $values[] = "({$groupID},{$contactID})";
                }
                self::store ( $groupIDs, $values );
            }
        }
    }
}



