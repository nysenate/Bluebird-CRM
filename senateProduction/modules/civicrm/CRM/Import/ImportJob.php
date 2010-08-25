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
 | Version 3, 19 November 2009.                                       |
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

require_once 'CRM/Core/DAO.php';

/**
 * This class acts like a psuedo-BAO for transient import job tables
 */
 
class CRM_Import_ImportJob {
    
    protected $_tableName;
    protected $_primaryKeyName;
    protected $_statusFieldName;
    
    protected $_doGeocodeAddress;
    protected $_invalidRowCount;
    protected $_conflictRowCount;
    protected $_onDuplicate;
    protected $_newGroupName;
    protected $_newGroupDesc;
    protected $_groups;
    protected $_allGroups;
    protected $_newTagName;
    protected $_newTagDesc;
    protected $_tag;
    protected $_allTags;
    
    protected $_mapper;
    protected $_mapperKeys;
    protected $_mapperLocTypes;
    protected $_mapperPhoneTypes;
    protected $_mapperImProviders;
    protected $_mapperRelated;
    protected $_mapperRelatedContactType;
    protected $_mapperRelatedContactDetails;
    protected $_mapperRelatedContactLocType;
    protected $_mapperRelatedContactPhoneType;
    protected $_mapperRelatedContactImProvider;
    protected $_mapFields;
    
    protected $_parser;
    
    public function __construct( $tableName = null, $createSql = null, $createTable = false ) {
        $dao = new CRM_Core_DAO();
        $db = $dao->getDatabaseConnection();
        
        if ( $createTable ) {
            if ( !$createSql ) {
                CRM_Core_Error::fatal('Either an existing table name or an SQL query to build one are required');
            }
            
            // FIXME: we should regen this table's name if it exists rather than drop it
            if ( !$tableName ) {
                $tableName = 'civicrm_import_job_' . md5(uniqid(rand(), true));  
            }
            $db->query("DROP TABLE IF EXISTS $tableName");
            $db->query("CREATE TABLE $tableName ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci $createSql");
        }
        
        if ( !$tableName ) {
            CRM_Core_Error::fatal( 'Import Table is required.' );
        }
        
        $this->_tableName = $tableName;
        
        $this->_mapperKeys = array();
        $this->_mapperLocTypes = array();
        $this->_mapperPhoneTypes = array();
        $this->_mapperImProviders = array();
        $this->_mapperRelated = array();
        $this->_mapperRelatedContactType = array();
        $this->_mapperRelatedContactDetails = array();
        $this->_mapperRelatedContactLocType = array();
        $this->_mapperRelatedContactPhoneType = array();
        $this->_mapperRelatedContactImProvider = array();
    }
    
    public function getTableName() {
        return $this->_tableName;
    }
    
    public function isComplete( $dropIfComplete = true ) {
        if (!$this->_statusFieldName) {
            CRM_Core_Error::fatal("Could not get name of the import status field");
        }
        $query = "SELECT * FROM $this->_tableName
                  WHERE  $this->_statusFieldName = 'NEW' LIMIT 1";
        $result = CRM_Core_DAO::executeQuery( $query );
        if ($result->fetch()) {
            return false;
        }
        if ( $dropIfComplete ) {
            $query = "DROP TABLE $this->_tableName";
            CRM_Core_DAO::executeQuery( $query );
        }
        return true;
    }
    
    public function setJobParams( &$params )
    {       
        foreach ( $params as $param => $value ) {
            eval( "\$this->_$param = \$value;");
        }
    }
    
    public function runImport(&$form, $timeout = 55) {
        $mapper = $this->_mapper;
        foreach ($mapper as $key => $value) {
            $this->_mapperKeys[$key] = $mapper[$key][0];
            if (is_numeric($mapper[$key][1])) {
                $this->_mapperLocTypes[$key] = $mapper[$key][1];
            } else {
                $this->_mapperLocTypes[$key] = null;
            }
            //to store phoneType id and provider id separately for contact
            if ( is_numeric($mapper[$key][2]) ) {
                if ( CRM_Utils_Array::value( '0', $mapper[$key] ) == 'phone' ) {
                    $this->_mapperPhoneTypes[$key]  = $mapper[$key][2];
                    $this->_mapperImProviders[$key] = null;
                } else if ( CRM_Utils_Array::value( '0', $mapper[$key] ) == 'im' ) {
                    $this->_mapperImProviders[$key] = $mapper[$key][2];
                    $this->_mapperPhoneTypes[$key]  = null;
                }
            } else {
                $this->_mapperPhoneTypes[$key] = null;
                $this->_mapperImProviders[$key] = null;
            }
                        
            list($id, $first, $second) = explode('_', $mapper[$key][0]);
            if ( ($first == 'a' && $second == 'b') || ($first == 'b' && $second == 'a') ) {
                $relationType = new CRM_Contact_DAO_RelationshipType();
                $relationType->id = $id;
                $relationType->find(true);
                eval( '$this->_mapperRelatedContactType[$key] = $relationType->contact_type_'.$second.';');
                $this->_mapperRelated[$key] = $mapper[$key][0];
                $this->_mapperRelatedContactDetails[$key] = $mapper[$key][1];
                $this->_mapperRelatedContactLocType[$key] = $mapper[$key][2];
                
                //to store phoneType id and provider id separately for related contact
                if ( CRM_Utils_Array::value( '1', $mapper[$key] ) == 'phone' ) {
                    $this->_mapperRelatedContactPhoneType[$key] = $mapper[$key][3];
                    $this->_mapperRelatedContactImProvider[$key] = null;
                } else if ( CRM_Utils_Array::value( '1', $mapper[$key] ) == 'im' ) {
                    $this->_mapperRelatedContactImProvider[$key] = $mapper[$key][3];
                    $this->_mapperRelatedContactPhoneType[$key]  = null;
                } else {
                     $this->_mapperRelatedContactPhoneType[$key]  = null;
                     $this->_mapperRelatedContactImProvider[$key] = null;
                }
            } else {
                $this->_mapperRelated[$key] = null;
                $this->_mapperRelatedContactType[$key] = null;
                $this->_mapperRelatedContactDetails[$key] = null;
                $this->_mapperRelatedContactLocType[$key] = null;
                $this->_mapperRelatedContactPhoneType[$key] = null;
                $this->_mapperRelatedContactImProvider[$key] = null;
            }
        }
        
        require_once 'CRM/Import/Parser/Contact.php';
        $this->_parser = new CRM_Import_Parser_Contact( 
            $this->_mapperKeys, 
            $this->_mapperLocTypes,
            $this->_mapperPhoneTypes,
            $this->_mapperImProviders,
            $this->_mapperRelated, 
            $this->_mapperRelatedContactType,
            $this->_mapperRelatedContactDetails,
            $this->_mapperRelatedContactLocType, 
            $this->_mapperRelatedContactPhoneType, 
            $this->_mapperRelatedContactImProvider );
        
        $locationTypes  = CRM_Core_PseudoConstant::locationType();
        $phoneTypes  = CRM_Core_PseudoConstant::phoneType();
        $imProviders = CRM_Core_PseudoConstant::IMProvider();
        
        foreach ($mapper as $key => $value) {
            $header = array();
            list($id, $first, $second) = explode('_', $mapper[$key][0]);
            if ( ($first == 'a' && $second == 'b') || ($first == 'b' && $second == 'a') ) {
                $relationType = new CRM_Contact_DAO_RelationshipType();
                $relationType->id = $id;
                $relationType->find(true);
                
                $header[] = $relationType->name_a_b;
                $header[] = ucwords(str_replace("_", " ", $mapper[$key][1]));
                
                if ( isset($mapper[$key][2]) ) {
                    $header[] = $locationTypes[$mapper[$key][2]];
                }
                if ( isset($mapper[$key][3]) ) {
                    if ( CRM_Utils_Array::value( '1', $mapper[$key] ) == 'phone' ) {
                        $header[] = $phoneTypes[$mapper[$key][3]];
                    } else if ( CRM_Utils_Array::value( '1', $mapper[$key] ) == 'im' ) {
                        $header[] = $imProviders[$mapper[$key][3]];
                    }
                }
            } else {
                if ( isset($this->_mapFields[$mapper[$key][0]]) ) {
                    $header[] = $this->_mapFields[$mapper[$key][0]];
                    if ( isset($mapper[$key][1]) ) {
                        $header[] = $locationTypes[$mapper[$key][1]];
                    }
                    if ( isset($mapper[$key][2]) ) {
                        if( CRM_Utils_Array::value( '0', $mapper[$key] ) == 'phone' ) {
                            $header[] = $phoneTypes[$mapper[$key][2]];
                        } else if ( CRM_Utils_Array::value( '0', $mapper[$key] ) == 'im' ) {
                            $header[] = $imProviders[$mapper[$key][2]];
                        }
                    }
                }
            }            
            $mapperFields[] = implode(' - ', $header);
        }
        
        $this->_parser->run( $this->_tableName, $mapperFields,
                      CRM_Import_Parser::MODE_IMPORT,
                      $this->_contactType,
                      $this->_primaryKeyName,
                      $this->_statusFieldName,
                      $this->_onDuplicate,
                      $this->_statusID,
                      $this->_totalRowCount,
                      $this->_doGeocodeAddress,
                      CRM_Import_Parser::DEFAULT_TIMEOUT, 
                      $this->_contactSubType );
                      
        $contactIds = $this->_parser->getImportedContacts( );
        
        //get the related contactIds. CRM-2926
        $relatedContactIds = $this->_parser->getRelatedImportedContacts( );
        if ( $relatedContactIds ) { 
            $contactIds = array_merge( $contactIds, $relatedContactIds );
            if ( $form ) {
                $form->set('relatedCount', count($relatedContactIds) );
            }
        }
        
        if ( $this->_newGroupName || count($this->_groups) ) {
            $groupAdditions = $this->_addImportedContactsToNewGroup($contactIds,
                                                                    $this->_newGroupName,
                                                                    $this->_newGroupDesc);
            if ($form) $form->set('groupAdditions', $groupAdditions);
        }
        
        if ( $this->_newTagName || count($this->_tag) ) {
            $tagAdditions = $this->_tagImportedContactsWithNewTag($contactIds,
                                                                  $this->_newTagName,
                                                                  $this->_newTagDesc);
            if ($form) $form->set('tagAdditions', $tagAdditions);
        }
    }
    
    public function setFormVariables( $form ) {
        $this->_parser->set( $form, CRM_Import_Parser::MODE_IMPORT );
    }
    
    private function _addImportedContactsToNewGroup( $contactIds,
                                                     $newGroupName, $newGroupDesc ) {
        
        $newGroupId = null;
        
        if ($newGroupName) {
            /* Create a new group */
            $gParams = array(
                             'name'          => $newGroupName,
                             'title'         => $newGroupName,
                             'description'   => $newGroupDesc,
                             'is_active'     => true,
                             );
            $group = CRM_Contact_BAO_Group::create($gParams);
            $this->_groups[] = $newGroupId = $group->id;
        }
        
        if (is_array($this->_groups)) {
            $groupAdditions = array();
            foreach ($this->_groups as $groupId) {
                $addCount = CRM_Contact_BAO_GroupContact::addContactsToGroup($contactIds, $groupId);
                $totalCount = $addCount[1];
                if ($groupId == $newGroupId) {
                    $name = $newGroupName;
                    $new = true;
                } else {
                    $name = $this->_allGroups[$groupId];
                    $new = false;
                }
                $groupAdditions[] = array(
                                          'url'      => CRM_Utils_System::url( 'civicrm/group/search',
                                                                               'reset=1&force=1&context=smog&gid=' . $groupId ),
                                          'name'     => $name,
                                          'added'    => $totalCount,
                                          'notAdded' => $addCount[2],
                                          'new'      => $new
                                          );
            }
            return $groupAdditions;
        }
        return false;
    }
    
    private function _tagImportedContactsWithNewTag( $contactIds,
        $newTagName, $newTagDesc ) {
        
        $newTagId = null;
        if ($newTagName) {
            /* Create a new Tag */
            $tagParams = array(
                               'name'          => $newTagName,
                               'title'         => $newTagName,
                               'description'   => $newTagDesc,
                               'is_selectable' => true,
                               'used_for'      => 'civicrm_contact' 
                               );
            require_once 'CRM/Core/BAO/Tag.php';
            $id = array();
            $addedTag = CRM_Core_BAO_Tag::add($tagParams,$id);
            $this->_tag[$addedTag->id] = 1;
        }
        //add Tag to Import   

        if(is_array($this->_tag)) {

            $tagAdditions = array();
            require_once "CRM/Core/BAO/EntityTag.php";
            foreach ($this->_tag as $tagId =>$val) {
                $addTagCount = CRM_Core_BAO_EntityTag::addEntitiesToTag( $contactIds, $tagId );
                $totalTagCount = $addTagCount[1];
                if ($tagId == $addedTag->id) {
                    $tagName = $newTagName;
                    $new = true;
                } else {
                    $tagName = $this->_allTags[$tagId];
                    $new = false;
                }
                $tagAdditions[] = array(
                                        'url'      => CRM_Utils_System::url( 'civicrm/contact/search',
                                                                             'reset=1&force=1&context=smog&id=' . $tagId ),
                                        'name'     => $tagName,
                                        'added'    => $totalTagCount,
                                        'notAdded' => $addTagCount[2],
                                        'new'      => $new
                                        );
            }
            return $tagAdditions;
        }
        return false;
    }
    
    public static function getIncompleteImportTables() {
        $dao = new CRM_Core_DAO();
        $database = $dao->database();
        $query = "SELECT   TABLE_NAME FROM INFORMATION_SCHEMA
                  WHERE    TABLE_SCHEMA = ? AND
                           TABLE_NAME LIKE 'civicrm_import_job_%'
                  ORDER BY TABLE_NAME";
        $result = CRM_Core_DAO::executeQuery($query, array($database));
        $incompleteImportTables = array();
        while ($importTable = $result->fetch()) {
            if (!$this->isComplete($importTable)) {
                $incompleteImportTables[] = $importTable;
            }
        }
        return $incompleteImportTables;
    }
}
