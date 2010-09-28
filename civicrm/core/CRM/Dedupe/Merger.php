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

class CRM_Dedupe_Merger
{
    // FIXME: this should be auto-generated from the schema
    static $validFields = array(
        'addressee', 'addressee_custom', 'birth_date', 'contact_source', 'contact_type',
        'deceased_date', 'do_not_email', 'do_not_mail', 'do_not_sms', 'do_not_phone', 
        'do_not_trade', 'external_identifier', 'email_greeting', 'email_greeting_custom', 'first_name', 'gender', 
        'home_URL', 'household_name', 'image_URL', 
        'individual_prefix', 'individual_suffix', 'is_deceased', 'is_opt_out', 
        'job_title', 'last_name', 'legal_identifier', 'legal_name', 
        'middle_name', 'nick_name', 'organization_name', 'postal_greeting', 'postal_greeting_custom', 
        'preferred_communication_method', 'preferred_mail_format', 'sic_code'
    );

    // FIXME: consider creating a common structure with cidRefs() and eidRefs()
    // FIXME: the sub-pages references by the URLs should
    // be loaded dynamically on the merge form instead
    static function &relTables()
    {
        static $relTables;
        if (!$relTables) {
            $relTables = array(
                'rel_table_contributions' => array(
                    'title'  => ts('Contributions'),
                    'tables' => array('civicrm_contribution', 'civicrm_contribution_recur', 'civicrm_contribution_soft'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=contribute'),
                ),
                'rel_table_contribution_page' => array(
                    'title'  => ts('Contribution Pages'),
                    'tables' => array('civicrm_contribution_page'),
                    'url'    => CRM_Utils_System::url('civicrm/admin/contribute', 'reset=1&cid=$cid'),
                ),
                'rel_table_memberships' => array(
                    'title'  => ts('Memberships'),
                    'tables' => array('civicrm_membership', 'civicrm_membership_log', 'civicrm_membership_type'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=member'),
                ),
                'rel_table_participants' => array(
                    'title'  => ts('Participants'),
                    'tables' => array('civicrm_participant'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=participant'),
                ),
                'rel_table_events' => array(
                    'title'  => ts('Events'),
                    'tables' => array('civicrm_event'),
                    'url'    => CRM_Utils_System::url('civicrm/event/manage', 'reset=1&cid=$cid'),
                ),
                'rel_table_activities' => array(
                    'title'  => ts('Activities'),
                    'tables' => array('civicrm_activity', 'civicrm_activity_target', 'civicrm_activity_assignment'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=activity'),
                ),
                'rel_table_relationships' => array(
                    'title'  => ts('Relationships'),
                    'tables' => array('civicrm_relationship'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=rel'),
                ),
                'rel_table_custom_groups' => array(
                    'title'  => ts('Custom Groups'),
                    'tables' => array('civicrm_custom_group'),
                    'url'    => CRM_Utils_System::url('civicrm/admin/custom/group', 'reset=1'),
                ),    
                'rel_table_uf_groups' => array(
                    'title'  => ts('Profiles'),
                    'tables' => array('civicrm_uf_group'),
                    'url'    => CRM_Utils_System::url('civicrm/admin/uf/group', 'reset=1'),
                ),    
                'rel_table_groups' => array(
                    'title'  => ts('Groups'),
                    'tables' => array('civicrm_group_contact'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=group'),
                ),
                'rel_table_notes' => array(
                    'title'  => ts('Notes'),
                    'tables' => array('civicrm_note'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=note'),
                ),
                'rel_table_tags' => array(
                    'title'  => ts('Tags'),
                    'tables' => array('civicrm_entity_tag'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=tag'),
                ),
                'rel_table_mailings' => array(
                    'title'  => ts('Mailings'),
                    'tables' => array('civicrm_mailing', 'civicrm_mailing_event_queue', 'civicrm_mailing_event_subscribe' ),
                    'url'    => CRM_Utils_System::url('civicrm/mailing', 'reset=1&force=1&cid=$cid'),
                ),
                'rel_table_cases' => array(
                    'title'  => ts('Cases'),
                    'tables' => array('civicrm_case_contact'),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=case'),
                ),
                'rel_table_pcp' => array(
                    'title'  => ts('PCPs'),
                    'tables' => array('civicrm_pcp'),
                    'url'    => CRM_Utils_System::url('civicrm/contribute/pcp/manage', 'reset=1'),
                ),

                'rel_table_pledges' => array(
                    'title'  => ts('Pledges'),
                    'tables' => array('civicrm_pledge', 'civicrm_pledge_payment' ),
                    'url'    => CRM_Utils_System::url('civicrm/contact/view', 'reset=1&force=1&cid=$cid&selectedChild=pledge'),
                )
            );
        }
        return $relTables;
    }

    /**
     * Returns the related tables groups for which a contact has any info entered
     */
    static function getActiveRelTables($cid)
    {
        $cid = (int) $cid;
        $groups = array();

        $relTables =& self::relTables();
        $cidRefs   =& self::cidRefs();
        $eidRefs   =& self::eidRefs();
        foreach ($relTables as $group => $params) {
            $sqls = array();
            foreach ($params['tables'] as $table) {
                if (isset($cidRefs[$table])) {
                    foreach ($cidRefs[$table] as $field) {
                        $sqls[] = "SELECT COUNT(*) AS count FROM $table WHERE $field = $cid";
                    }
                }
                if (isset($eidRefs[$table])) {
                    foreach ($eidRefs[$table] as $entityTable => $entityId) {
                        $sqls[] = "SELECT COUNT(*) AS count FROM $table WHERE $entityId = $cid AND $entityTable = 'civicrm_contact'";
                    }
                }
                foreach ($sqls as $sql) {
                    if ( CRM_Core_DAO::singleValueQuery( $sql,
                                                         CRM_Core_DAO::$_nullArray ) > 0 ) {
                        $groups[] = $group;
                    }
                }
            }
        }
        return array_unique($groups);
    }

    /**
     * Return tables and their fields referencing civicrm_contact.contact_id explicitely
     */
    static function &cidRefs()
    {
        static $cidRefs;
        if (!$cidRefs) {
            // FIXME: this should be generated dynamically from the schema's 
            // foreign keys referencing civicrm_contact(id)
            $cidRefs = array(
                'civicrm_acl_cache'               => array('contact_id'),
                'civicrm_activity'                => array('source_contact_id'),
                'civicrm_activity_assignment'     => array('assignee_contact_id'),
                'civicrm_activity_target'         => array('target_contact_id'),
                'civicrm_case_contact'            => array('contact_id'),
                'civicrm_contact'                 => array('primary_contact_id'),
                'civicrm_contribution'            => array('contact_id', 'honor_contact_id'),
                'civicrm_contribution_page'       => array('created_id'),
                'civicrm_contribution_recur'      => array('contact_id'),
                'civicrm_contribution_soft'       => array('contact_id'),
                'civicrm_custom_group'            => array('created_id'),
                'civicrm_entity_tag'              => array('entity_id'),
                'civicrm_event'                   => array('created_id'),
                'civicrm_grant'                   => array('contact_id'),
                'civicrm_group_contact'           => array('contact_id'),
                'civicrm_group_organization'      => array('organization_id'),
                'civicrm_log'                     => array('modified_id'),
                'civicrm_mailing'                 => array('created_id', 'scheduled_id'),
                'civicrm_mailing_event_queue'     => array('contact_id'),
                'civicrm_mailing_event_subscribe' => array('contact_id'),
                'civicrm_membership'              => array('contact_id'),
                'civicrm_membership_log'          => array('modified_id'),
                'civicrm_membership_type'         => array('member_of_contact_id'),
                'civicrm_note'                    => array('contact_id'),
                'civicrm_participant'             => array('contact_id'),
                'civicrm_pcp'                     => array('contact_id'),
                'civicrm_preferences'             => array('contact_id'),
                'civicrm_relationship'            => array('contact_id_a', 'contact_id_b'),
                'civicrm_subscription_history'    => array('contact_id'),
                'civicrm_uf_match'                => array('contact_id'),
                'civicrm_uf_group'                => array('created_id'),
                'civicrm_pledge'                  => array('contact_id'),
            );
        }
        return $cidRefs;
    }

    /**
     * Return tables and their fields referencing civicrm_contact.contact_id with entity_id
     */
    static function &eidRefs()
    {
        static $eidRefs;
        if (!$eidRefs) {
            // FIXME: this should be generated dynamically from the schema
            // tables that reference contacts with entity_{id,table}
            $eidRefs = array(
                'civicrm_acl'              => array('entity_table'             => 'entity_id'),
                'civicrm_acl_entity_role'  => array('entity_table'             => 'entity_id'),
                'civicrm_entity_file'      => array('entity_table'             => 'entity_id'),
                'civicrm_log'              => array('entity_table'             => 'entity_id'),
                'civicrm_mailing_group'    => array('entity_table'             => 'entity_id'),
                'civicrm_note'             => array('entity_table'             => 'entity_id'),
                'civicrm_project'          => array('owner_entity_table'       => 'owner_entity_id'),
                'civicrm_task'             => array('owner_entity_table'       => 'owner_entity_id'),
                'civicrm_task_status'      => array('responsible_entity_table' => 'responsible_entity_id', 'target_entity_table' => 'target_entity_id'),
            );
        }
        return $eidRefs;
    }
    
    /**
     * return custom processing tables.
     */
    static function &cpTables( )
    {
        static $tables;
        if ( !$tables ) {
            $tables = array( 'civicrm_case_contact' => array( 'path'     => 'CRM_Case_BAO_Case',
                                                              'function' => 'mergeCases' ) );
        }
        
        return $tables;
    }
    
    /**
     * return payment related table.
     */
    static function &paymentTables( )
    {
        static $tables;
        if ( !$tables ) {
            $tables = array( 'civicrm_pledge', 'civicrm_membership', 'civicrm_participant' );
        }
        
        return $tables;
    }
    
    /**
     * return payment update Query.
     */
    static function paymentSql( $tableName, $mainContactId, $otherContactId )
    {
        $sqls = array( );
        if ( !$tableName || !$mainContactId || !$otherContactId ) {
            return $sqls;
        }
        
        $paymentTables = self::paymentTables( );
        if ( !in_array( $tableName, $paymentTables ) ) {
            return $sqls; 
        }
        
        switch ( $tableName ) {
        case 'civicrm_pledge' :
            $sqls[] = "
    UPDATE  IGNORE  civicrm_contribution contribution
INNER JOIN  civicrm_pledge_payment payment ON ( payment.contribution_id = contribution.id )
INNER JOIN  civicrm_pledge pledge ON ( pledge.id = payment.pledge_id )                                               
       SET  contribution.contact_id = $mainContactId
     WHERE  pledge.contact_id = $otherContactId";
            break;
        case 'civicrm_membership' :
            $sqls[] = "
    UPDATE  IGNORE  civicrm_contribution contribution
INNER JOIN  civicrm_membership_payment payment ON ( payment.contribution_id = contribution.id )
INNER JOIN  civicrm_membership membership ON ( membership.id = payment.membership_id )      
       SET  contribution.contact_id = $mainContactId
     WHERE  membership.contact_id = $otherContactId";
            break;
        case 'civicrm_participant' :
            $sqls[] = "
    UPDATE  IGNORE  civicrm_contribution contribution
INNER JOIN  civicrm_participant_payment payment ON ( payment.contribution_id = contribution.id )
INNER JOIN  civicrm_participant participant ON ( participant.id = payment.participant_id )      
       SET  contribution.contact_id = $mainContactId
     WHERE  participant.contact_id = $otherContactId";
            break;
        }
        
        return $sqls;
    }
    
    /**
     * Based on the provided two contact_ids and a set of tables, move the 
     * belongings of the other contact to the main one.
     */
    function moveContactBelongings($mainId, $otherId, $tables = false)
    {
        $cidRefs       = self::cidRefs( );
        $eidRefs       = self::eidRefs( );
        $cpTables      = self::cpTables( );
        $paymentTables = self::paymentTables( );
        
        $affected = array_merge(array_keys($cidRefs), array_keys($eidRefs));
        if ($tables !== false) {
            // if there are specific tables, sanitize the list
            $affected = array_unique(array_intersect($affected, $tables));
        } else { 
            // if there aren't any specific tables, don't affect the ones handled by relTables()
            $relTables =& self::relTables();
            $handled = array();
            foreach ($relTables as $params) {
                $handled = array_merge($handled, $params['tables']);
            }
            $affected = array_diff($affected, $handled);
        }
       
        $mainId  = (int) $mainId;
        $otherId = (int) $otherId;
                
        // use UPDATE IGNORE + DELETE query pair to skip on situations when 
        // there's a UNIQUE restriction on ($field, some_other_field) pair
        $sqls = array( );
        foreach ($affected as $table) {
            //here we require custom processing.
            if ( array_key_exists( $table, $cpTables ) ) {
                $path  = CRM_Utils_Array::value( 'path',     $cpTables[$table] );
                $fName = CRM_Utils_Array::value( 'function', $cpTables[$table] );
                if ( $path && $fName ) {
                    require_once( str_replace('_', DIRECTORY_SEPARATOR, $path ) . ".php" );
                    eval( "$path::$fName( $mainId, null, $otherId );");
                }
                continue;
            }
            
            if (isset($cidRefs[$table])) {
                foreach ($cidRefs[$table] as $field) {
                    // carry related contributions CRM-5359
                    if ( in_array( $table, $paymentTables ) ) {
                        $paymentSqls = self::paymentSql( $table, $mainId, $otherId ); 
                        $sqls = array_merge( $sqls, $paymentSqls ); 
                    }
                    
                    $sqls[] = "UPDATE IGNORE $table SET $field = $mainId WHERE $field = $otherId";
                    $sqls[] = "DELETE FROM $table WHERE $field = $otherId";
                }
            }
            if (isset($eidRefs[$table])) {
                foreach ($eidRefs[$table] as $entityTable => $entityId) {
                    $sqls[] = "UPDATE IGNORE $table SET $entityId = $mainId WHERE $entityId = $otherId AND $entityTable = 'civicrm_contact'";
                    $sqls[] = "DELETE FROM $table WHERE $entityId = $otherId AND $entityTable = 'civicrm_contact'";
                }
            }
        }

        // CRM-6184: if we’re moving relationships, update civicrm_contact.employer_id
        if (is_array($tables) and in_array('civicrm_relationship', $tables)) {
            $sqls[] = "UPDATE IGNORE civicrm_contact SET employer_id = $mainId WHERE employer_id = $otherId";
        }

        // call the SQL queries in one transaction
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        foreach ($sqls as $sql) {
            CRM_Core_DAO::executeQuery( $sql,
                                        CRM_Core_DAO::$_nullArray,
                                        true, null, true );
        }
        $transaction->commit( );
    }

    /**
     * Find differences between contacts.
     */
    function findDifferences($mainId, $otherId)
    {
        require_once 'api/v2/Contact.php';
        $mainParams  = array('contact_id' => (int) $mainId);
        $otherParams = array('contact_id' => (int) $otherId);
        // API 2 has to have the requested fields spelt-out for it
        foreach (self::$validFields as $field) {
            $mainParams["return.$field"] = $otherParams["return.$field"] = 1;
        }
        $main  =& civicrm_contact_get($mainParams);
        $other =& civicrm_contact_get($otherParams);
        
        //CRM-4524
        $main  = reset( $main );
        $other = reset( $other );
        
        if ($main['contact_type'] != $other['contact_type']) {
            return false;
        }

        $diffs = array();
        foreach (self::$validFields as $validField) {
            if ( CRM_Utils_Array::value( $validField, $main ) != CRM_Utils_Array::value( $validField, $other) ) {
                $diffs['contact'][] = $validField;
            }
        }

        require_once 'CRM/Core/BAO/CustomValueTable.php';
        $mainEvs  =& CRM_Core_BAO_CustomValueTable::getEntityValues($mainId);
        $otherEvs =& CRM_Core_BAO_CustomValueTable::getEntityValues($otherId);
        $keys = array_unique(array_merge(array_keys($mainEvs), array_keys($otherEvs)));
        foreach ($keys as $key) {
            if ($mainEvs[$key] != $otherEvs[$key]) $diffs['custom'][] = $key;
        }

        return $diffs;
    }
}


