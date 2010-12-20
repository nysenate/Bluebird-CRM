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

require_once 'CRM/ACL/DAO/ACL.php';

/**
 *  Access Control List
 */
class CRM_ACL_BAO_ACL extends CRM_ACL_DAO_ACL {
    static $_entityTable = null;
    static $_objectTable = null;
    static $_operation   = null;

    static $_fieldKeys   = null
;
    static function entityTable( ) {
        if ( ! self::$_entityTable ) {
            self::$_entityTable = array(
                                        'civicrm_contact'      => ts( 'Contact'       ),
                                        'civicrm_acl_role'    => ts( 'ACL Role'     ), );
        }
        return self::$_entityTable;
    }

    static function objectTable( ) {
        if ( ! self::$_objectTable ) {
            self::$_objectTable = array(
                                        'civicrm_contact'      => ts( 'Contact'       ),
                                        'civicrm_group'        => ts( 'Group'         ),
                                        'civicrm_saved_search' => ts( 'Contact Group' ),
                                        'civicrm_admin'        => ts( 'Administer'    ),
                                        'civicrm_admin'        => ts( 'Import'        ) );
        }
        return self::$_objectTable;
    }

    static function operation( ) {
        if ( ! self::$_operation ) {
            self::$_operation = array(
                                      'View'   => ts( 'View'   ),
                                      'Edit'   => ts( 'Edit'   ),
                                      'Create' => ts( 'Create' ),
                                      'Delete' => ts( 'Delete' ),
                                      'Search' => ts( 'Search' ),
                                      'All'    => ts( 'All' ),
                                      );
        }
        return self::$_operation;
    }

    /**
     * Construct a WHERE clause to handle permissions to $object_*
     *
     * @param array ref $tables -   Any tables that may be needed in the FROM
     * @param string $operation -   The operation being attempted
     * @param string $object_table -    The table of the object in question
     * @param int $object_id    -   The ID of the object in question
     * @param int $acl_id   -       If it's a grant/revoke operation, the ACL ID
     * @param boolean $acl_role -  For grant operations, this flag determines if we're granting a single acl (false) or an entire group.
     * @return string           -   The WHERE clause, or 0 on failure
     * @access public
     * @static
     */
    public static function permissionClause(&$tables, $operation,
                                            $object_table = null, $object_id = null, 
                                            $acl_id = null, $acl_role = false) 
    {
        $dao = new CRM_ACL_DAO_ACL;
        
        $t = array(
            'ACL'           => self::getTableName(),
            'ACLRole'      => 'civicrm_acl_role',
            'ACLEntityRole' => CRM_ACL_DAO_EntityRole::getTableName(),
            'Contact'       => CRM_Contact_DAO_Contact::getTableName(),
            'Group'         => CRM_Contact_DAO_Group::getTableName(),
            'GroupContact'  => CRM_Contact_DAO_GroupContact::getTableName()
        );

        $session     = CRM_Core_Session::singleton();
        $contact_id  =  $session->get('userID');
        
        $where = " {$t['ACL']}.operation = '" .
                    CRM_Utils_Type::escape($operation, 'String') ."'";

        /* Include clause if we're looking for a specific table/id permission */
        if (!empty($object_table)) {
            $where .= " AND ( {$t['ACL']}.object_table IS null
                         OR ({$t['ACL']}.object_table   = '" .
                    CRM_Utils_Type::escape($object_table, 'String') ."'";
            if (!empty($object_id)) {
                $where .= " AND ({$t['ACL']}.object_id IS null
                            OR {$t['ACL']}.object_id = " .
                    CRM_Utils_Type::escape($object_id, 'Integer') . ')';
            }
            $where .= '))';
        }
            
        /* Include clause if we're granting an ACL or ACL Role */
        if (!empty($acl_id)) {
            $where .= " AND ({$t['ACL']}.acl_id IS null 
                        OR {$t['ACL']}.acl_id   = "
                    . CRM_Utils_Type::escape($acl_id, 'Integer') . ')';
            if ($acl_role) {
                $where .= " AND {$t['ACL']}.acl_table = '{$t['ACLRole']}'";
            } else {
                $where .= " AND {$t['ACL']}.acl_table = '{$t['ACL']}'";
            }
        }
        
        $query = array();
        
        /* Query for permissions granted to all contacts in the domain */
        $query[] = "SELECT      {$t['ACL']}.*, 0 as override
                    FROM        {$t['ACL']}
                    
                    WHERE       {$t['ACL']}.entity_table    = '{$t['Domain']}'
                            AND ($where)";

        /* Query for permissions granted to all contacts through an ACL group */
        $query[] = "SELECT      {$t['ACL']}.*, 0 as override
                    FROM        {$t['ACL']}
                    
                    INNER JOIN  {$t['ACLEntityRole']}
                            ON  ({$t['ACL']}.entity_table = '{$t['ACLRole']}'
                            AND     {$t['ACL']}.entity_id = 
                                    {$t['ACLEntityRole']}.acl_role_id)
                                    
                    INNER JOIN  {$t['ACLRole']}
                            ON      {$t['ACL']}.entity_id = 
                                    {$t['ACLRole']}.id
                    
                    WHERE       {$t['ACLEntityRole']}.entity_table =
                                    '{$t['Domain']}'
                            AND {$t['ACLRole']}.is_active      = 1
                            AND ($where)";
        
        /* Query for permissions granted directly to the contact */
        $query[] = "SELECT      {$t['ACL']}.*, 1 as override
                    FROM        {$t['ACL']}
                    
                    INNER JOIN  {$t['Contact']}
                            ON  ({$t['ACL']}.entity_table = '{$t['Contact']}'
                            AND     {$t['ACL']}.entity_id = {$t['Contact']}.id)
                    
                    WHERE       {$t['Contact']}.id          = $contact_id 
                            AND ($where)";

        /* Query for permissions granted to the contact through an ACL group */
        $query[] = "SELECT      {$t['ACL']}.*, 1 as override
                    FROM        {$t['ACL']}
                    
                    INNER JOIN  {$t['ACLEntityRole']}
                            ON  ({$t['ACL']}.entity_table = '{$t['ACLRole']}'
                            AND     {$t['ACL']}.entity_id =
                                    {$t['ACLEntityRole']}.acl_role_id)
                    
                    INNER JOIN  {$t['ACLRole']}
                            ON  {$t['ACL']}.entity_id = {$t['ACLRole']}.id
                    
                    WHERE       {$t['ACLEntityRole']}.entity_table = 
                                    '{$t['Contact']}' 
                        AND     {$t['ACLRole']}.is_active      = 1
                        AND     {$t['ACLEntityRole']}.entity_id  = $contact_id
                        AND     ($where)";

        /* Query for permissions granted to the contact through a group */
        $query[] = "SELECT      {$t['ACL']}.*, 0 as override
                    FROM        {$t['ACL']}
                    
                    INNER JOIN  {$t['GroupContact']}
                            ON  ({$t['ACL']}.entity_table = '{$t['Group']}'
                            AND     {$t['ACL']}.entity_id =
                                    {$t['GroupContact']}.group_id)
                    
                    WHERE       ($where)
                        AND     {$t['GroupContact']}.contact_id = $contact_id
                        AND     {$t['GroupContact']}.status     = 'Added')";


        /* Query for permissions granted through an ACL group to a Contact
         * group */
        $query[] = "SELECT      {$t['ACL']}.*, 0 as override
                    FROM        {$t['ACL']}
                    
                    INNER JOIN  {$t['ACLEntityRole']}
                            ON  ({$t['ACL']}.entity_table = '{$t['ACLRole']}'
                            AND     {$t['ACL']}.entity_id = 
                                    {$t['ACLEntityRole']}.acl_role_id)
                   
                    INNER JOIN  {$t['ACLRole']}
                            ON  {$t['ACL']}.entity_id = {$t['ACLRole']}.id
                   
                    INNER JOIN  {$t['GroupContact']}
                            ON  ({$t['ACLEntityRole']}.entity_table =
                                    '{$t['Group']}'
                            AND     {$t['ACLEntityRole']}.entity_id =
                                    {$t['GroupContact']}.group_id)
                    
                    WHERE       ($where)
                        AND     {$t['ACLRole']}.is_active      = 1
                        AND     {$t['GroupContact']}.contact_id = $contact_id
                        AND     {$t['GroupContact']}.status     = 'Added'";
                    
        $union = '(' . implode(') UNION DISTINCT (', $query) . ')';

        $dao->query($union);
        
        $allow    = array(0);
        $deny     = array(0);
        $override = array();

        while ($dao->fetch()) {
            /* Instant bypass for the following cases:
             * 1) the rule governs all tables
             * 2) the rule governs all objects in the table in question
             * 3) the rule governs the specific object we want
             */
            if (empty($dao->object_table) || 
                ($dao->object_table == $object_table 
                    && (empty($dao->object_id) 
                        || $dao->object_id == $object_id) ) )
            {
                $clause = 1;
            } 
            else 
            {
                /* Otherwise try to generate a clause for this rule */
                $clause = self::getClause(
                    $dao->object_table, $dao->object_id, $tables);
                
                /* If the clause returned is null, then the rule is a blanket
                 * (id is null) on a table other than the one we're interested
                 * in.  So skip it. */
                if (empty($clause)) {
                    continue;
                }
            }
            
            /* Now we figure out if this is an allow or deny rule, and possibly
             * a contact-level override */
            if ($dao->deny) {
                $deny[] = $clause;
            } else {
                $allow[] = $clause;
                
                if ($dao->override) {
                    $override[] = $clause;
                }
            }
        }

        $allows = '(' . implode(' OR ', $allow) . ')';
        $denies = '(' . implode(' OR ', $deny) . ')';
        if (!empty($override)) {
            $denies = '(NOT (' . implode(' OR ', $override) .") AND $denies)";
        }

        return "($allows AND NOT $denies)";
    }

    /**
     * Given a table and id pair, return the filter clause
     *
     * @param string $table -   The table owning the object
     * @param int $id   -       The ID of the object
     * @param array ref $tables - Tables that will be needed in the FROM
     * @return string|null  -   WHERE-style clause to filter results, 
                                or null if $table or $id is null
     * @access public
     * @static
     */
    public static function getClause($table, $id, &$tables) {
        $table = CRM_Utils_Type::escape($table, 'String');
        $id = CRM_Utils_Type::escape($id, 'Integer');
        $whereTables = array( );

        $ssTable = CRM_Contact_BAO_SavedSearch::getTableName();

        if (empty($table)) {
            return null;
        } elseif ($table == $ssTable) {
            return CRM_Contact_BAO_SavedSearch::whereClause($id, $tables, $whereTables);
        } elseif (!empty($id)) {
            $tables[$table] = true;
            return "$table.id = $id";
        }
        return null;
    }

    /**
     * Construct an associative array of an ACL rule's properties
     *
     * @param
     * @return array    - Assoc. array of the ACL rule's properties
     * @access public
     */
    public function toArray() {
        $result = array();

        if ( ! self::$_fieldKeys ) {
            $fields =& CRM_ACL_DAO_ACL::fields( );
            self::$_fieldKeys = array_keys( $fields );
        }

        foreach ( self::$_fieldKeys as $field ) {
            $result[$field] = $this->$field;
        }
        return $result;
    }

    /**
     * Retrieve ACLs for a contact or group.  Note that including a contact id
     * without a group id will return those ACL rules which are granted
     * directly to the contact, but not those granted to the contact through
     * any/all of his group memberships.
     *
     * @param int $contact_id       -   ID of a contact to search for
     * @param int $group_id         -   ID of a group to search for
     * @param boolean $aclRoles    -   Should we include ACL Roles
     * @return array                -   Array of assoc. arrays of ACL rules 
     * @access public
     * @static
     */
    public static function &getACLs($contact_id = null, $group_id = null, $aclRoles = false) {
        $results = array();

        if ( empty( $contact_id ) ) {
            return $results;
        }

        $contact_id = CRM_Utils_Type::escape($contact_id, 'Integer');
        if ( $group_id ) {
            $group_id   = CRM_Utils_Type::escape($group_id, 'Integer');
        }
        
        $rule       = new CRM_ACL_BAO_ACL();

        require_once 'CRM/Contact/BAO/Group.php';
        require_once 'CRM/Contact/BAO/GroupContact.php';
        require_once 'CRM/Contact/BAO/Contact.php';

        $acl        = self::getTableName();
        $contact    = CRM_Contact_BAO_Contact::getTableName();
        $c2g        = CRM_Contact_BAO_GroupContact::getTableName();
        $group      = CRM_Contact_BAO_Group::getTableName();
        
        $query      = " SELECT      $acl.*
                        FROM        $acl ";
        
        if (!empty($group_id)) {
            $query .= " INNER JOIN  $c2g
                            ON      $acl.entity_id      = $c2g.group_id
                        WHERE       $acl.entity_table   = '$group'
                            AND     $acl.is_active      = 1
                            AND     $c2g.group_id       = $group_id";
                        
            if (!empty($contact_id)) {
                $query .= " AND     $c2g.contact_id     = $contact_id
                            AND     $c2g.status         = 'Added'";
            }
        } else {
            if (!empty($contact_id)) {
                $query .= " WHERE   $acl.entity_table   = '$contact'
                            AND     $acl.entity_id      = $contact_id";
            
            }
        }

        $rule->query($query);
        
        while ($rule->fetch()) {
            $results[$rule->id] = $rule->toArray( );
        }

        if ($aclRoles) {
            $results += self::getACLRoles($contact_id, $group_id);
        }

        return $results;
    }
    
    /**
     * Get all of the ACLs through ACL groups
     *
     * @param int $contact_id   -   ID of a contact to search for
     * @param int $group_id     -   ID of a group to search for
     * @return array            -   Array of assoc. arrays of ACL rules
     * @access public
     * @static
     */
    public static function &getACLRoles($contact_id = null, $group_id = null) {
        $contact_id = CRM_Utils_Type::escape($contact_id, 'Integer');
        if ( $group_id ) {
            $group_id   = CRM_Utils_Type::escape($group_id, 'Integer');
        }

        $rule       = new CRM_ACL_BAO_ACL();

        require_once 'CRM/ACL/DAO/EntityRole.php';
        $acl           = self::getTableName();
        $aclRole      = 'civicrm_acl_role';
        $aclRoleJoin  = CRM_ACL_DAO_EntityRole::getTableName();
        $contact       = CRM_Contact_BAO_Contact::getTableName();
        $c2g           = CRM_Contact_BAO_GroupContact::getTableName();
        $group         = CRM_Contact_BAO_Group::getTableName();
        
        $query =    "   SELECT          $acl.* 
                        FROM            $acl
                        INNER JOIN      civicrm_option_group og
                                ON      og.name = 'acl_role'
                        INNER JOIN      civicrm_option_value ov
                                ON      $acl.entity_table   = '$aclRole'
                                AND     ov.option_group_id  = og.id
                                AND     $acl.entity_id      = ov.value";
                                
        if (!empty($group_id)) {
            $query .= " INNER JOIN  $c2g
                            ON      $acl.entity_id     = $c2g.group_id
                        WHERE       $acl.entity_table  = '$group'
                            AND     $acl.is_active     = 1
                            AND     $c2g.group_id           = $group_id";
                        
            if (!empty($contact_id)) {
                $query .= " AND     $c2g.contact_id = $contact_id
                            AND     $c2g.status = 'Added'";
            }
            
        } else {
            if (!empty($contact_id)) {
                $query .= " WHERE   $acl.entity_table  = '$contact'
                                AND $acl.is_active     = 1
                                AND $acl.entity_id     = $contact_id";
            
            }
        }

        $results = array();
        
        $rule->query($query);
        
        while ($rule->fetch()) {
            $results[$rule->id] =& $rule->toArray();
        }
        
        return $results;
    }


    /** 
     * Get all ACLs granted to a contact through all group memberships
     *
     * @param int $contact_id       -   The contact's ID
     * @param boolean $aclRoles     -   Include ACL Roles?
     * @return array                -   Assoc array of ACL rules
     * @access public
     * @static
     */
    public static function &getGroupACLs($contact_id, $aclRoles = false) {
        $contact_id = CRM_Utils_Type::escape($contact_id, 'Integer');

        $rule       = new CRM_ACL_BAO_ACL();

        require_once 'CRM/Contact/BAO/GroupContact.php';
        require_once 'CRM/Contact/BAO/Group.php';

        $acl        = self::getTableName();
        $c2g        = CRM_Contact_BAO_GroupContact::getTableName();
        $group      = CRM_Contact_BAO_Group::getTableName();
        $results    = array();

        if ( $contact_id ) {
            $query      = "
SELECT      $acl.*
  FROM      $acl 
INNER JOIN  $c2g
        ON  $acl.entity_id      = $c2g.group_id
     WHERE  $acl.entity_table   = '$group'
       AND  $c2g.contact_id     = $contact_id
       AND  $c2g.status         = 'Added'";

            $rule->query($query);
            
            while ($rule->fetch()) {
                $results[$rule->id] =& $rule->toArray();
            }
        }

        if ($aclRoles) {
            $results += self::getGroupACLRoles($contact_id);
        }
        
        return $results;
    }

    /**
    * Get all of the ACLs for a contact through ACL groups owned by Contact
    * groups.
    *
    * @param int $contact_id   -   ID of a contact to search for
    * @return array            -   Array of assoc. arrays of ACL rules
    * @access public
    * @static
    */
    public static function &getGroupACLRoles($contact_id) {
        $contact_id = CRM_Utils_Type::escape($contact_id, 'Integer');
        
        $rule       = new CRM_ACL_BAO_ACL();
                                                                                
        $acl        = self::getTableName();
        $aclRole   = 'civicrm_acl_role';
        
        require_once 'CRM/ACL/DAO/EntityRole.php';

        $aclER      = CRM_ACL_DAO_EntityRole::getTableName( );
        $c2g        = CRM_Contact_BAO_GroupContact::getTableName();
        $group      = CRM_Contact_BAO_Group::getTableName();
     
        $query =    "   SELECT          $acl.* 
                        FROM            $acl
                        INNER JOIN      civicrm_option_group og
                                ON      og.name = 'acl_role'
                        INNER JOIN      civicrm_option_value ov
                                ON      $acl.entity_table   = '$aclRole'
                                AND     ov.option_group_id  = og.id
                                AND     $acl.entity_id      = ov.value
                                AND     ov.is_active        = 1
                        INNER JOIN      $aclER
                                ON      $aclER.acl_role_id = $acl.entity_id
                                AND     $aclER.is_active    = 1
                        INNER JOIN  $c2g
                                ON      $aclER.entity_id      = $c2g.group_id
                                AND     $aclER.entity_table   = 'civicrm_group'
                        WHERE       $acl.entity_table       = '$aclRole'
                            AND     $acl.is_active          = 1
                            AND     $c2g.contact_id         = $contact_id
                            AND     $c2g.status             = 'Added'";
            
        $results = array();

        $rule->query($query);
        
        while ($rule->fetch()) {
            $results[$rule->id] =& $rule->toArray();
        }
        
        // also get all acls for "Any Role" case
        // and authenticated User Role if present
        $roles = "0";
        $session = CRM_Core_Session::singleton( );
        if ( $session->get( 'ufID' ) > 0 ) {
            $roles .= ",2";
        }

        $query      = "
SELECT $acl.*
  FROM $acl 
 WHERE $acl.entity_id      IN ( $roles )
   AND $acl.entity_table   = 'civicrm_acl_role'
";

        $rule->query($query);
        while ($rule->fetch()) {
            $results[$rule->id] =& $rule->toArray();
        }

        return $results;
    }


    /**
     * Get all ACLs owned by a given contact, including domain and group-level.
     *
     * @param int $contact_id   -   The contact ID
     * @return array            -   Assoc array of ACL rules
     * @access public
     * @static
     */
    public static function &getAllByContact($contact_id) {
        $result = array();

        /* First, the contact-specific ACLs, including ACL Roles */
        $result += self::getACLs($contact_id, null, true);

        /* Then, all ACLs granted through group membership */
        $result += self::getGroupACLs($contact_id, true);
        
        return $result;
    }

    static function create( &$params ) {
        $dao = new CRM_ACL_DAO_ACL( );
        $dao->copyValues( $params );
        $dao->save( );
    }

    static function retrieve( &$params, &$defaults ) {
        CRM_Core_DAO::commonRetrieve( 'CRM_ACL_DAO_ACL', $params, $defaults );
    }    

    /**
    * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        require_once 'CRM/Core/BAO/Cache.php';
        // note this also resets any ACL cache 
        CRM_Core_BAO_Cache::deleteGroup( 'contact fields' );

        return CRM_Core_DAO::setFieldValue( 'CRM_ACL_DAO_ACL', $id, 'is_active', $is_active );
    }

    static function check( $str, $contactID ) {
        require_once 'CRM/ACL/BAO/Cache.php';
        
        $acls =& CRM_ACL_BAO_Cache::build( $contactID );

        $aclKeys = array_keys( $acls );
        $aclKeys = implode( ',', $aclKeys );

         if ( empty ( $aclKeys) ) {
          return false;
        }
        

        $query = "
SELECT count( a.id )
  FROM civicrm_acl_cache c, civicrm_acl a
 WHERE c.acl_id       =  a.id
   AND a.is_active    =  1
   AND a.object_table =  %1
   AND a.id           IN ( $aclKeys )
";
        $params  = array( 1 => array( $str, 'String' ) );

        $count =& CRM_Core_DAO::singleValueQuery( $query, $params );
        return ( $count ) ? true : false;
    }

    public static function whereClause( $type, &$tables, &$whereTables, $contactID = null ) {
        require_once 'CRM/ACL/BAO/Cache.php';

        $acls =& CRM_ACL_BAO_Cache::build( $contactID );
        //CRM_Core_Error::debug( "a: $contactID", $acls );

        $whereClause = null;
        $clauses = array( );

        if ( ! empty( $acls ) ) {
            $aclKeys = array_keys( $acls );
            $aclKeys = implode( ',', $aclKeys );

            $query = "
SELECT   a.operation, a.object_id
  FROM   civicrm_acl_cache c, civicrm_acl a
 WHERE   c.acl_id       =  a.id
   AND   a.is_active    =  1
   AND   a.object_table = 'civicrm_saved_search'
   AND   a.id        IN ( $aclKeys )
ORDER BY a.object_id
";
            
            $dao =& CRM_Core_DAO::executeQuery( $query );
        
            // do an or of all the where clauses u see
            $ids = array( );
            while ( $dao->fetch( ) ) {
                // make sure operation matches the type TODO
                if ( self::matchType( $type, $dao->operation ) ) {
                    if ( ! $dao->object_id ) {
                        $ids = array( );
                        $whereClause = ' ( 1 ) ';
                        break;
                    } 
                    $ids[] = $dao->object_id;
                }
            }
            
            if ( ! empty( $ids ) ) {
                $ids = implode( ',', $ids );
                $query = "
SELECT g.*
  FROM civicrm_group g
 WHERE g.id IN ( $ids )
";
                $dao =& CRM_Core_DAO::executeQuery( $query );
                while ( $dao->fetch( ) ) {
                    // currently operation is restrcited to VIEW/EDIT
                    if ( $dao->where_clause ) {
                        $clauses[] = $dao->where_clause;
                        if ( $dao->select_tables ) {
                            $tables = array_merge( $tables,
                                                   unserialize( $dao->select_tables ) );
                        }
                        if ( $dao->where_tables ) {
                            $whereTables = array_merge( $whereTables,
                                                        unserialize( $dao->where_tables ) );
                        }
                    }
                    
                    if ( ( $dao->saved_search_id ||
                           $dao->children ||
                           $dao->parents ) &&
                         $dao->cache_date == null ) {
                        require_once 'CRM/Contact/BAO/GroupContactCache.php';
                        CRM_Contact_BAO_GroupContactCache::load( $dao );
                    }
                }
            }
        }

        if ( ! empty( $clauses ) ) {
            $whereClause = ' ( ' . implode( ' OR ', $clauses ) . ' ) ';
        }

        // call the hook to get additional whereClauses
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::aclWhereClause( $type, $tables, $whereTables, $contactID, $whereClause );

        if ( empty( $whereClause ) ) {
            $whereClause = ' ( 0 ) ';
        }

        return $whereClause;
    }

    public static function group( $type,
                                  $contactID = null,
                                  $tableName = 'civicrm_saved_search',
                                  $allGroups = null,
                                  $includedGroups = null ) {
        require_once 'CRM/ACL/BAO/Cache.php';

        $acls =& CRM_ACL_BAO_Cache::build( $contactID );

        if ( ! empty( $includedGroups ) &&
             is_array( $includedGroups ) ) {
            $ids  = $includedGroups;
        } else {
            $ids  = array( );
        }

        if ( ! empty( $acls ) ) {
            $aclKeys = array_keys( $acls );
            $aclKeys = implode( ',', $aclKeys );

            $query = "
SELECT   a.operation, a.object_id
  FROM   civicrm_acl_cache c, civicrm_acl a
 WHERE   c.acl_id       =  a.id
   AND   a.is_active    =  1
   AND   a.object_table = %1
   AND   a.id        IN ( $aclKeys )
ORDER BY a.object_id
";
            $params = array( 1 => array( $tableName, 'String' ) );
            $dao =& CRM_Core_DAO::executeQuery( $query, $params );
            while ( $dao->fetch( ) ) {
                if ( $dao->object_id ) {
                    if ( self::matchType( $type, $dao->operation ) ) {
                        $ids[] = $dao->object_id;
                    }
                } else {
                    // this user has got the permission for all objects of this type
                    // check if the type matches
                    if ( self::matchType( $type, $dao->operation ) ) {
                        foreach ( $allGroups as $id => $dontCare ) {
                            $ids[] = $id;
                        }
                    }
                    break;
                }
            }
        }

        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::aclGroup( $type, $contactID, $tableName, $allGroups, $ids );
        
        return $ids;
    }

    static function matchType( $type, $operation ) {
        $typeCheck = false;
        switch ( $operation ) {
        case 'All':
            $typeCheck = true;
            break;
                    
        case 'View':
            if ( $type == CRM_ACL_API::VIEW ) {
                $typeCheck = true;
            }
            break;

        case 'Edit':
            if ( $type == CRM_ACL_API::VIEW || $type == CRM_ACL_API::EDIT ) {
                $typeCheck = true;
            }
            break;
                        
        case 'Create':
            if ( $type == CRM_ACL_API::CREATE ) {
                $typeCheck = true;
            }
            break;

        case 'Delete':
            if ( $type == CRM_ACL_API::DELETE ) {
                $typeCheck = true;
            }
            break;

        case 'Search':
            if ( $type == CRM_ACL_API::SEARCH ) {
                $typeCheck = true;
            }
            break;

        }
        return $typeCheck;
    }

    /**
    * Function to delete ACL records 
     * 
     * @param  int  $aclId     ID of the ACL record to be deleted.
     * 
     * @access public
     * @static
     */
    static function del($aclId) 
    {
        // delete all entries from the acl cache
        require_once 'CRM/ACL/BAO/Cache.php';
        CRM_ACL_BAO_Cache::resetCache( );

        $acl = new CRM_ACL_DAO_ACL();
        $acl->id = $aclId;
        $acl->delete();
    }
        
}


