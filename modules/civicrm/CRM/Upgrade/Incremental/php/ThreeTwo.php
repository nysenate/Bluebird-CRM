<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
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

class CRM_Upgrade_Incremental_php_ThreeTwo {
    
    function verifyPreDBstate ( &$errors ) {
        return true;
    }
    
    function upgrade_3_2_alpha1( $rev ) 
    {
        //CRM-5666 -if user already have 'access CiviCase'
        //give all new permissions and drop access CiviCase.
        $config = CRM_Core_Config::singleton( );
        if ( $config->userFramework == 'Drupal' ) {
            db_query( "UPDATE {permission} SET perm = REPLACE( perm, 'access CiviCase', 'access my cases and activities, access all cases and activities, administer CiviCase' )" );
            //insert core acls.
            $casePermissions = array( 'delete in CiviCase',
                                      'administer CiviCase', 
                                      'access my cases and activities', 
                                      'access all cases and activities', );
            require_once 'CRM/ACL/DAO/ACL.php';
            $aclParams = array( 'name'         => 'Core ACL',
                                'deny'         => 0,
                                'acl_id'       => NULL,
                                'object_id'    => NULL,
                                'acl_table'    => NULL,
                                'entity_id'    => 1,
                                'operation'    => 'All',
                                'is_active'    => 1,
                                'entity_table' => 'civicrm_acl_role' );
            foreach ( $casePermissions as $per ) {
                $aclParams['object_table'] = $per;
                $acl = new CRM_ACL_DAO_ACL( );
                $acl->object_table = $per;
                if ( !$acl->find( true ) ) {
                    $acl->copyValues( $aclParams );
                    $acl->save( );
                }
            }
            //drop 'access CiviCase' acl
            CRM_Core_DAO::executeQuery( "DELETE FROM civicrm_acl WHERE object_table = 'access CiviCase'" );
        }
        
        $upgrade =& new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );
    }

    function upgrade_3_2_beta4($rev)
    {
        $upgrade = new CRM_Upgrade_Form;
        
        $config =& CRM_Core_Config::singleton();
        $seedLocale = $config->lcMessages;

        //handle missing civicrm_uf_field.help_pre
        $hasLocalizedPreHelpCols = false;
        
        // CRM-6451: for multilingual sites we need to find the optimal
        // locale to use as the final civicrm_membership_status.name column
        $domain = new CRM_Core_DAO_Domain;
        $domain->find(true);
        $locales = array( );
        if ($domain->locales) {
            $locales = explode(CRM_Core_DAO::VALUE_SEPARATOR, $domain->locales);
            // optimal: an English locale
            foreach (array('en_US', 'en_GB', 'en_AU') as $loc) {
                if (in_array($loc, $locales)) {
                    $seedLocale = $loc;
                    break;
                }
            }

            // if no English and no $config->lcMessages: use the first available
            if ( !$seedLocale ) $seedLocale = $locales[0];

            $upgrade->assign('seedLocale', $seedLocale);
            $upgrade->assign('locales',    $locales);
            
            $localizedColNames = array( );
            foreach ( $locales as $loc ) {
                $localizedName = "help_pre_{$loc}";
                $localizedColNames[$localizedName] = $localizedName;
            }
            $columns = CRM_Core_DAO::executeQuery( 'SHOW COLUMNS FROM civicrm_uf_field' );
            while ( $columns->fetch( ) ) {
                if ( strpos( $columns->Field, 'help_pre' ) !== false &&
                     in_array( $columns->Field, $localizedColNames ) ) {
                    $hasLocalizedPreHelpCols = true;
                    break;
                }
            }
        }
        $upgrade->assign( 'hasLocalizedPreHelpCols',  $hasLocalizedPreHelpCols);
        
        $upgrade->processSQL($rev);

        // now civicrm_membership_status.name has possibly localised strings, so fix them
        $i18n = new CRM_Core_I18n($seedLocale);
        $statuses = array(
            array(
                'name'                        => 'New',
                'start_event'                 => 'join_date',
                'end_event'                   => 'join_date',
                'end_event_adjust_unit'       => 'month',
                'end_event_adjust_interval'   => '3',
                'is_current_member'           => '1',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Current',
                'start_event'                 => 'start_date',
                'end_event'                   => 'end_date',
                'is_current_member'           => '1',
                'is_admin'                    => '0',
                'is_default'                  => '1',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Grace',
                'start_event'                 => 'end_date',
                'end_event'                   => 'end_date',
                'end_event_adjust_unit'       => 'month',
                'end_event_adjust_interval'   => '1',
                'is_current_member'           => '1',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Expired',
                'start_event'                 => 'end_date',
                'start_event_adjust_unit'     => 'month',
                'start_event_adjust_interval' => '1',
                'is_current_member'           => '0',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Pending',
                'start_event'                 => 'join_date',
                'end_event'                   => 'join_date',
                'is_current_member'           => '0',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '1',
            ),
            array(
                'name'                        => 'Cancelled',
                'start_event'                 => 'join_date',
                'end_event'                   => 'join_date',
                'is_current_member'           => '0',
                'is_admin'                    => '0',
                'is_default'                  => '0',
                'is_reserved'                 => '0',
            ),
            array(
                'name'                        => 'Deceased',
                'is_current_member'           => '0',
                'is_admin'                    => '1',
                'is_default'                  => '0',
                'is_reserved'                 => '1',
            ),
        );

        require_once 'CRM/Member/DAO/MembershipStatus.php';
        $statusIds = array( );
        $insertedNewRecord = false;
        foreach ($statuses as $status) {
            $dao = new CRM_Member_DAO_MembershipStatus;

            // try to find an existing English status
            $dao->name = $status['name'];

//             // if not found, look for translated status name
//             if (!$dao->find(true)) {
//                 $found     = false;
//                 $dao->name = $i18n->translate($status['name']);
//             }
            
            // if found, update name and is_reserved
            if ($dao->find(true)) {
                $dao->name        = $status['name'];
                $dao->is_reserved = $status['is_reserved'];
                if ( $status['is_reserved'] ) {
                    $dao->is_active = 1; 
                }
                // if not found, prepare a new row for insertion
            } else {
                $insertedNewRecord = true;
                foreach ($status as $property => $value) {
                    $dao->$property = $value;
                }
                $dao->weight = CRM_Utils_Weight::getDefaultWeight('CRM_Member_DAO_MembershipStatus');
            }
            
            // add label (translated name) and save (UPDATE or INSERT)
            $dao->label = $i18n->translate($status['name']);
            $dao->save();
            
            $statusIds[$dao->id] = $dao->id;
        }
        
        //disable all status those are customs.
        if ( $insertedNewRecord  ) {
            $sql = '
UPDATE  civicrm_membership_status 
   SET  is_active = 0 
 WHERE  id NOT IN ( ' . implode( ',', $statusIds ) . ' )';
            CRM_Core_DAO::executeQuery( $sql );
        }
    
    }
    
    function upgrade_3_2_1($rev)
    {
        //CRM-6565 check if Activity Index is already exists or not.
        $addActivityTypeIndex = true;
        $indexes = CRM_Core_DAO::executeQuery( 'SHOW INDEXES FROM civicrm_activity' );
        while ( $indexes->fetch( ) ) {
            if( $indexes->Key_name == 'UI_activity_type_id' ){
                $addActivityTypeIndex = false;
            }
        }
        // CRM-6563: restrict access to the upload dir, tighten access to the config-and-log dir
        $config =& CRM_Core_Config::singleton();
        require_once 'CRM/Utils/File.php';
        CRM_Utils_File::restrictAccess($config->uploadDir);
        CRM_Utils_File::restrictAccess($config->configAndLogDir);
        $upgrade = new CRM_Upgrade_Form;
        $upgrade->assign( 'addActivityTypeIndex', $addActivityTypeIndex );
        $upgrade->processSQL($rev);
		
		//NYSS v1.1 Drupal related updates
		
		//update permissions
		//SOS
		db_query( "UPDATE {permission} SET perm = 'access CiviCRM, access CiviReport, access Report Criteria, access all custom data, access uploaded files, add contacts, administer Reports, delete contacts, edit all contacts, edit groups, profile listings, profile view, view all activities, view all contacts' WHERE rid = 6" );
		//Administrator
		db_query( "UPDATE {permission} SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, assign roles, access administration pages, administer users' WHERE rid = 4" );
		//Office Admin
		db_query( "UPDATE {permission} SET perm = 'create users, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer Reports, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile listings, profile listings and forms, profile view, view all activities, view all contacts, assign roles, access administration pages, administer users' WHERE rid = 9" );
		//Print Production
		db_query( "UPDATE {permission} SET perm = 'access CiviCRM, access CiviReport, access all custom data, edit groups, import contacts, profile listings, profile view, view all contacts' WHERE rid = 7" );
		//Superuser
		db_query( "UPDATE {permission} SET perm = 'create users, delete users with role Administrator, delete users with role Analytics User, delete users with role Conference Services, delete users with role Data Entry, delete users with role Office Administrator, delete users with role Office Manager, delete users with role Print Production, delete users with role SOS, delete users with role Staff, delete users with role Volunteer, edit users with role Administrator, edit users with role Analytics User, edit users with role Conference Services, edit users with role Data Entry, edit users with role Office Administrator, edit users with role Office Manager, edit users with role Print Production, edit users with role SOS, edit users with role Staff, edit users with role Volunteer, administer blocks, use PHP for block visibility, access CiviCRM, access CiviReport, access Contact Dashboard, access Report Criteria, access all cases and activities, access all custom data, access deleted contacts, access my cases and activities, access uploaded files, add contacts, administer CiviCRM, administer CiviCase, administer Reports, administer Tagsets, administer reserved tags, delete activities, delete contacts, delete in CiviCase, edit all contacts, edit groups, import contacts, merge duplicate contacts, profile create, profile edit, profile listings, profile listings and forms, profile view, translate CiviCRM, view all activities, view all contacts, assign roles, access administration pages, access user profiles, administer permissions, administer users, administer userprotect' WHERE rid = 3" );
		
		//handle actions/triggers
		//action (check params field)
		db_query( "INSERT INTO `actions` (`aid`, `type`, `callback`, `parameters`, `description`) VALUES
('1', 'system', 'system_goto_action', 'a:1:{s:3:\"url\";s:7:\"civicrm\";}', 'Redirect to CiviCRM Dashboard')" );
		db_query( "INSERT INTO `actions_aid` (`aid`) VALUES (1)" );
		//triggers
		db_query( "
INSERT INTO `menu_links` (`menu_name`, `mlid`, `plid`, `link_path`, `router_path`, `link_title`, `options`, `module`, `hidden`, `external`, `has_children`, `expanded`, `weight`, `depth`, `customized`, `p1`, `p2`, `p3`, `p4`, `p5`, `p6`, `p7`, `p8`, `p9`, `updated`) VALUES
('navigation', 337, 17, 'admin/build/trigger', 'admin/build/trigger', 'Triggers', 'a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:36:\"Tell Drupal when to execute actions.\";}}', 'system', 0, 0, 0, 0, 0, 3, 0, 2, 17, 337, 0, 0, 0, 0, 0, 0, 0),
('navigation', 338, 15, 'admin/help/trigger', 'admin/help/trigger', 'trigger', 'a:0:{}', 'system', -1, 0, 0, 0, 0, 3, 0, 2, 15, 338, 0, 0, 0, 0, 0, 0, 0),
('navigation', 339, 337, 'admin/build/trigger/unassign', 'admin/build/trigger/unassign', 'Unassign', 'a:1:{s:10:\"attributes\";a:1:{s:5:\"title\";s:34:\"Unassign an action from a trigger.\";}}', 'system', -1, 0, 0, 0, 0, 4, 0, 2, 17, 337, 339, 0, 0, 0, 0, 0, 0); " );
		db_query( "
INSERT INTO `menu_router` (`path`, `load_functions`, `to_arg_functions`, `access_callback`, `access_arguments`, `page_callback`, `page_arguments`, `fit`, `number_parts`, `tab_parent`, `tab_root`, `title`, `title_callback`, `title_arguments`, `type`, `block_callback`, `description`, `position`, `weight`, `file`) VALUES
('admin/build/trigger', '', '', 'trigger_access_check', 'a:1:{i:0;s:4:\"node\";}', 'trigger_assign', 'a:0:{}', 7, 3, '', 'admin/build/trigger', 'Triggers', 't', '', 6, '', 'Tell Drupal when to execute actions.', '', 0, 'modules/trigger/trigger.admin.inc'),
('admin/build/trigger/comment', '', '', 'trigger_access_check', 'a:1:{i:0;s:7:\"comment\";}', 'trigger_assign', 'a:1:{i:0;s:7:\"comment\";}', 15, 4, 'admin/build/trigger', 'admin/build/trigger', 'Comments', 't', '', 128, '', '', '', 0, 'modules/trigger/trigger.admin.inc'),
('admin/build/trigger/cron', '', '', 'user_access', 'a:1:{i:0;s:18:\"administer actions\";}', 'trigger_assign', 'a:1:{i:0;s:4:\"cron\";}', 15, 4, 'admin/build/trigger', 'admin/build/trigger', 'Cron', 't', '', 128, '', '', '', 0, 'modules/trigger/trigger.admin.inc'),
('admin/build/trigger/node', '', '', 'trigger_access_check', 'a:1:{i:0;s:4:\"node\";}', 'trigger_assign', 'a:1:{i:0;s:4:\"node\";}', 15, 4, 'admin/build/trigger', 'admin/build/trigger', 'Content', 't', '', 128, '', '', '', 0, 'modules/trigger/trigger.admin.inc'),
('admin/build/trigger/taxonomy', '', '', 'trigger_access_check', 'a:1:{i:0;s:8:\"taxonomy\";}', 'trigger_assign', 'a:1:{i:0;s:8:\"taxonomy\";}', 15, 4, 'admin/build/trigger', 'admin/build/trigger', 'Taxonomy', 't', '', 128, '', '', '', 0, 'modules/trigger/trigger.admin.inc'),
('admin/build/trigger/unassign', '', '', 'user_access', 'a:1:{i:0;s:18:\"administer actions\";}', 'drupal_get_form', 'a:1:{i:0;s:16:\"trigger_unassign\";}', 15, 4, '', 'admin/build/trigger/unassign', 'Unassign', 't', '', 4, '', 'Unassign an action from a trigger.', '', 0, 'modules/trigger/trigger.admin.inc'),
('admin/build/trigger/user', '', '', 'trigger_access_check', 'a:1:{i:0;s:4:\"user\";}', 'trigger_assign', 'a:1:{i:0;s:4:\"user\";}', 15, 4, 'admin/build/trigger', 'admin/build/trigger', 'Users', 't', '', 128, '', '', '', 0, 'modules/trigger/trigger.admin.inc'),
('admin/help/trigger', '', '', 'user_access', 'a:1:{i:0;s:27:\"access administration pages\";}', 'help_page', 'a:1:{i:0;i:2;}', 7, 3, '', 'admin/help/trigger', 'trigger', 't', '', 4, '', '', '', 0, 'modules/help/help.admin.inc');
" );
		db_query( "UPDATE system SET status = 1, schema_version = 0 WHERE filename = 'modules/trigger/trigger.module';" );
		db_query( "
CREATE TABLE IF NOT EXISTS `trigger_assignments` (
  `hook` varchar(32) NOT NULL DEFAULT '',
  `op` varchar(32) NOT NULL DEFAULT '',
  `aid` varchar(255) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hook`,`op`,`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		" );
		db_query( "
INSERT INTO `trigger_assignments` (`hook`, `op`, `aid`, `weight`) VALUES
('user', 'view', '1', 1);
		" );
		//NYSS end
		
    }
  }
