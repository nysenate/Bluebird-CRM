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

require_once 'CRM/Contact/Form/Search/Custom/Base.php';
require_once 'CRM/Contact/BAO/SavedSearch.php';

class CRM_Contact_Form_Search_Custom_Group
   extends    CRM_Contact_Form_Search_Custom_Base
   implements CRM_Contact_Form_Search_Interface {

    protected $_formValues;

    protected $_tableName = null;

    protected $_where = ' (1) ';

    function __construct( &$formValues ) {
        $this->_formValues = $formValues;
        $this->_columns = array( ts('Contact Id')   => 'contact_id'  ,
                                 ts('Contact Type') => 'contact_type',
                                 ts('Name')         => 'sort_name',
                                 ts('Group Name')   => 'gname',
                                 ts('Tag Name')     => 'tname' );
        
        $this->_includeGroups   = CRM_Utils_Array::value( 'includeGroups', $this->_formValues, array( ) );
        $this->_excludeGroups   = CRM_Utils_Array::value( 'excludeGroups', $this->_formValues, array( ) ); 
        $this->_includeTags     = CRM_Utils_Array::value( 'includeTags', $this->_formValues  , array( ) ); 
        $this->_excludeTags     = CRM_Utils_Array::value( 'excludeTags', $this->_formValues  , array( ) );

        //define variables
        $this->_allSearch = false; 
        $this->_groups    = false;
        $this->_tags      = false;
        $this->_andOr      = $this->_formValues['andOr'];
        

        //make easy to check conditions for groups and tags are
        //selected or it is empty search
        if ( empty( $this->_includeGroups ) && empty( $this->_excludeGroups ) &&
             empty( $this->_includeTags ) && empty($this->_excludeTags) ) {
            //empty search
            $this->_allSearch = true;
        }
        
        if ( ! empty( $this->_includeGroups ) || ! empty( $this->_excludeGroups ) ) {
            //group(s) selected
            $this->_groups = true;
        }

        if ( ! empty( $this->_includeTags ) ||  ! empty( $this->_excludeTags )) {
            //tag(s) selected
            $this->_tags = true;  
        }
    }

    function __destruct( ) {
        // mysql drops the tables when connectiomn is terminated
        // cannot drop tables here, since the search might be used
        // in other parts after the object is destroyed
    }
    
    function buildForm( &$form ) {

        $groups         =& CRM_Core_PseudoConstant::group( );

        $tags           =& CRM_Core_PseudoConstant::tag( );
        if ( count($groups) == 0 || count($tags) == 0 ) {
            CRM_Core_Session::setStatus( ts("Atleast one Group and Tag must be present, for Custom Group / Tag search.") );
            $url = CRM_Utils_System::url( 'civicrm/contact/search/custom/list', 'reset=1' );
            CRM_Utils_System::redirect($url);
        } 

        $inG =& $form->addElement('advmultiselect', 'includeGroups', 
                                  ts('Include Group(s)') . ' ', $groups,
                                  array('size'  => 5,
                                        'style' => 'width:240px',
                                        'class' => 'advmultiselect')
                                  );
        
        $outG =& $form->addElement('advmultiselect', 'excludeGroups', 
                                   ts('Exclude Group(s)') . ' ', $groups,
                                   array('size'  => 5,
                                         'style' => 'width:240px',
                                         'class' => 'advmultiselect')
                                   );
        $andOr =& $form->addElement('checkbox', 'andOr', 'Combine With (AND, Uncheck For OR)', null, 
                                    array('checked'=>'checked'));
        
        $int =& $form->addElement('advmultiselect', 'includeTags', 
                                  ts('Include Tag(s)') . ' ', $tags,
                                  array('size'  => 5,
                                        'style' => 'width:240px',
                                        'class' => 'advmultiselect')
                                  );
        
        $outt =& $form->addElement('advmultiselect', 'excludeTags', 
                                   ts('Exclude Tag(s)') . ' ', $tags,
                                   array('size'  => 5,
                                         'style' => 'width:240px',
                                         'class' => 'advmultiselect')
                                   );

        //add/remove buttons for groups
        $inG->setButtonAttributes('add',  array('value' => ts('Add >>')));;
        $outG->setButtonAttributes('add', array('value' => ts('Add >>')));;
        $inG->setButtonAttributes('remove',  array('value' => ts('<< Remove')));;
        $outG->setButtonAttributes('remove', array('value' => ts('<< Remove')));;

        //add/remove buttons for tags
        $int->setButtonAttributes('add',  array('value' => ts('Add >>')));;
        $outt->setButtonAttributes('add', array('value' => ts('Add >>')));;
        $int->setButtonAttributes('remove',  array('value' => ts('<< Remove')));;
        $outt->setButtonAttributes('remove', array('value' => ts('<< Remove')));;
        
        /**
         * if you are using the standard template, this array tells the template what elements
         * are part of the search criteria
         */
        $form->assign( 'elements', array( 'includeGroups', 'excludeGroups', 'andOr', 'includeTags', 'excludeTags') );
       
    }
    
    function all( $offset = 0, $rowcount = 0, $sort = null,
                  $includeContactIDs = false, $justIDs = false ) {
        if ( $justIDs ) {
            $selectClause = "DISTINCT(contact_a.id)  as contact_id";
        } else {
            $selectClause = "DISTINCT(contact_a.id)  as contact_id,
                         contact_a.contact_type as contact_type,
                         contact_a.sort_name    as sort_name";
            
            //distinguish column according to user selection
            if ( $this->_includeGroups && ( ! $this->_includeTags ) ) {
                unset( $this->_columns['Tag Name'] );
                $selectClause .= ", GROUP_CONCAT(DISTINCT group_names ORDER BY group_names ASC ) as gname";
            } else if ( $this->_includeTags && ( ! $this->_includeGroups ) ) {
                unset( $this->_columns['Group Name'] );
                $selectClause .= ", GROUP_CONCAT(DISTINCT tag_names  ORDER BY tag_names ASC ) as tname";
            } else {
                if ( !empty($this->_includeTags ) && !empty( $this->_includeGroups ) ) {
                    $selectClause .=", GROUP_CONCAT(DISTINCT group_names ORDER BY group_names ASC ) as gname , GROUP_CONCAT(DISTINCT tag_names ORDER BY tag_names ASC ) as tname";
                } 
            }
        }
        
        $from  = $this->from( );
        
        $where = $this->where( $includeContactIDs );
        
        $sql = " SELECT $selectClause $from WHERE  $where ";
        if ( ! $justIDs  && ! $this->_allSearch ) {
            $sql .= " GROUP BY contact_id ";  
        } 
        
        // Define ORDER BY for query in $sort, with default value
        if ( ! $justIDs ) {
            if ( ! empty( $sort ) ) {
                if ( is_string( $sort ) ) {
                    $sql .= " ORDER BY $sort ";
                } else {
                    $sql .= " ORDER BY " . trim( $sort->orderBy() );
                }
            } else {
                $sql .= " ORDER BY contact_id ASC";
            }
        }
        
        if ( $offset >= 0 && $rowcount > 0 ) {
            $sql .= " LIMIT $offset, $rowcount ";
        }
        
        return $sql;
        
    }
    
    function from( ) {
        
        //define table name
        $randomNum = md5( uniqid( ) );
        $this->_tableName = "civicrm_temp_custom_{$randomNum}";

        //block for Group search
        $smartGroup = array( );
        if ( $this->_groups || $this->_allSearch ) { 
            require_once 'CRM/Contact/DAO/Group.php';
            $group = new CRM_Contact_DAO_Group( );
            $group->is_active = 1;
            $group->find();
            while( $group->fetch( ) ) {
                $allGroups[] = $group->id;
                if( $group->saved_search_id ) {
                    $smartGroup[$group->saved_search_id] = $group->id;
                    
                }
            }
            $includedGroups = implode( ',',$allGroups );
            
            if ( ! empty( $this->_includeGroups ) ) { 
                $iGroups = implode( ',', $this->_includeGroups );
            } else {
                //if no group selected search for all groups 
                $iGroups = null;
            }
            if ( is_array( $this->_excludeGroups ) ) {
                $xGroups = implode( ',', $this->_excludeGroups );
            } else {
                $xGroups = 0;
            }
            
            $sql = "CREATE TEMPORARY TABLE Xg_{$this->_tableName} ( contact_id int primary key) ENGINE=HEAP";  
            CRM_Core_DAO::executeQuery( $sql );
            
            //used only when exclude group is selected 
            if( $xGroups != 0 ) {
                $excludeGroup = 
                  "INSERT INTO  Xg_{$this->_tableName} ( contact_id )
                  SELECT  DISTINCT civicrm_group_contact.contact_id
                  FROM civicrm_group_contact, civicrm_contact                    
                  WHERE 
                     civicrm_contact.id = civicrm_group_contact.contact_id AND 
                     civicrm_group_contact.status = 'Added' AND
                     civicrm_group_contact.group_id IN( {$xGroups})";
                
                CRM_Core_DAO::executeQuery( $excludeGroup );
                
                //search for smart group contacts
                foreach( $this->_excludeGroups as $keys => $values ) {
                    if ( in_array( $values, $smartGroup ) ) {
                        $ssId = CRM_Utils_Array::key( $values, $smartGroup );
                        
                        $smartSql = CRM_Contact_BAO_SavedSearch::contactIDsSQL( $ssId );
                        
                        $smartSql = $smartSql. " AND contact_a.id NOT IN ( 
                              SELECT contact_id FROM civicrm_group_contact 
                              WHERE civicrm_group_contact.group_id = {$values} AND civicrm_group_contact.status = 'Removed')";
                        
                        $smartGroupQuery = " INSERT IGNORE INTO Xg_{$this->_tableName}(contact_id) $smartSql";
                        
                        CRM_Core_DAO::executeQuery( $smartGroupQuery );
                    }
                }
                
            }
            
            $sql = "CREATE TEMPORARY TABLE Ig_{$this->_tableName} ( id int PRIMARY KEY AUTO_INCREMENT,
                                                                   contact_id int,
                                                                   group_names varchar(64)) ENGINE=HEAP";
            
            CRM_Core_DAO::executeQuery( $sql );

            if ( $iGroups ) {
                $includeGroup = 
                "INSERT INTO Ig_{$this->_tableName} (contact_id, group_names)
                 SELECT              civicrm_contact.id as contact_id, civicrm_group.title as group_name
                 FROM                civicrm_contact
                    INNER JOIN       civicrm_group_contact
                            ON       civicrm_group_contact.contact_id = civicrm_contact.id
                    LEFT JOIN        civicrm_group
                            ON       civicrm_group_contact.group_id = civicrm_group.id";
            } else {
                $includeGroup = 
                "INSERT INTO Ig_{$this->_tableName} (contact_id, group_names)
                 SELECT              civicrm_contact.id as contact_id, ''
                 FROM                civicrm_contact";
            }


            //used only when exclude group is selected
            if( $xGroups != 0 ) {
                $includeGroup .= " LEFT JOIN        Xg_{$this->_tableName}
                                          ON       civicrm_contact.id = Xg_{$this->_tableName}.contact_id";
            }

            if ( $iGroups ) {
                $includeGroup .= " WHERE           
                                     civicrm_group_contact.status = 'Added'  AND
                                     civicrm_group_contact.group_id IN($iGroups)";
            } else {
                $includeGroup .= " WHERE ( 1 ) ";          
            }

            //used only when exclude group is selected
            if ( $xGroups != 0 ) {
                $includeGroup .=" AND  Xg_{$this->_tableName}.contact_id IS null";
            }
            
            CRM_Core_DAO::executeQuery( $includeGroup );
            
            //search for smart group contacts

            foreach( $this->_includeGroups as $keys => $values ) {
                if ( in_array( $values, $smartGroup ) ) {
                    
                    $ssId = CRM_Utils_Array::key( $values, $smartGroup );
                
                    $smartSql = CRM_Contact_BAO_SavedSearch::contactIDsSQL( $ssId );
                    
                    $smartSql .= " AND contact_a.id NOT IN ( 
                              SELECT contact_id FROM civicrm_group_contact
                              WHERE civicrm_group_contact.group_id = {$values} AND civicrm_group_contact.status = 'Removed')";
                    
                    //used only when exclude group is selected
                    if( $xGroups != 0 ) {
                        $smartSql .= " AND contact_a.id NOT IN (SELECT contact_id FROM  Xg_{$this->_tableName})";
                    }
                    
                    $smartGroupQuery = " INSERT IGNORE INTO Ig_{$this->_tableName}(contact_id) 
                                     $smartSql";
                
                    CRM_Core_DAO::executeQuery( $smartGroupQuery );
                    $insertGroupNameQuery = "UPDATE IGNORE Ig_{$this->_tableName}
                                         SET group_names = (SELECT title FROM civicrm_group
                                                            WHERE civicrm_group.id = $values)
                                         WHERE Ig_{$this->_tableName}.contact_id IS NOT NULL 
                                         AND Ig_{$this->_tableName}.group_names IS NULL";
                    CRM_Core_DAO::executeQuery($insertGroupNameQuery );
                }
            }
        }//group contact search end here;

        //block for Tags search
        if ( $this->_tags || $this->_allSearch ) {
            //find all tags 
            require_once 'CRM/Core/DAO/Tag.php';
            $tag = new CRM_Core_DAO_Tag( );
            $tag->is_active = 1;
            $tag->find();
            while( $tag->fetch( ) ) {
                $allTags[] = $tag->id;
            }
            $includedTags = implode( ',',$allTags );
            
            if ( ! empty( $this->_includeTags ) ) { 
                $iTags = implode( ',', $this->_includeTags );
            } else {
                //if no group selected search for all groups 
                $iTags = null;
            }
            if ( is_array( $this->_excludeTags ) ) {
                $xTags = implode( ',', $this->_excludeTags );
            } else {
                $xTags = 0;
            }
                       
            $sql = "CREATE TEMPORARY TABLE Xt_{$this->_tableName} ( contact_id int primary key) ENGINE=HEAP";  
            CRM_Core_DAO::executeQuery( $sql );
            
            //used only when exclude tag is selected
            if( $xTags != 0 ) {
                $excludeTag = 
                    "INSERT INTO  Xt_{$this->_tableName} ( contact_id )
                  SELECT  DISTINCT civicrm_entity_tag.entity_id
                  FROM civicrm_entity_tag, civicrm_contact                    
                  WHERE 
                     civicrm_entity_tag.entity_table = 'civicrm_contact' AND
                     civicrm_contact.id = civicrm_entity_tag.entity_id AND 
                     civicrm_entity_tag.tag_id IN( {$xTags})";
            
                CRM_Core_DAO::executeQuery( $excludeTag );
            }
        
            $sql = "CREATE TEMPORARY TABLE It_{$this->_tableName} ( id int PRIMARY KEY AUTO_INCREMENT,
                                                               contact_id int,
                                                               tag_names varchar(64)) ENGINE=HEAP";
                       
            CRM_Core_DAO::executeQuery( $sql );
            
            if ( $iTags ) {
                $includeTag = 
                "INSERT INTO It_{$this->_tableName} (contact_id, tag_names)
                 SELECT              civicrm_contact.id as contact_id, civicrm_tag.name as tag_name
                 FROM                civicrm_contact
                    INNER JOIN       civicrm_entity_tag
                            ON       ( civicrm_entity_tag.entity_table = 'civicrm_contact' AND
                                       civicrm_entity_tag.entity_id = civicrm_contact.id )
                    LEFT JOIN        civicrm_tag
                            ON       civicrm_entity_tag.tag_id = civicrm_tag.id";
            } else {
                $includeTag = 
                "INSERT INTO It_{$this->_tableName} (contact_id, tag_names)
                 SELECT              civicrm_contact.id as contact_id, ''
                 FROM                civicrm_contact";
            }

            //used only when exclude tag is selected
            if( $xTags != 0 ) {
                $includeTag .= " LEFT JOIN        Xt_{$this->_tableName}
                                       ON       civicrm_contact.id = Xt_{$this->_tableName}.contact_id";
            }
            if ( $iTags ) {
                $includeTag .= " WHERE   civicrm_entity_tag.tag_id IN($iTags)";
            } else {
                $includeTag .= " WHERE ( 1 ) ";
            }

            //used only when exclude tag is selected
            if ( $xTags != 0 ) {
                $includeTag .=" AND  Xt_{$this->_tableName}.contact_id IS null";
            }
            
            CRM_Core_DAO::executeQuery( $includeTag );
        }  

        $from = " FROM civicrm_contact contact_a";

        /*
         * check the situation and set booleans
         */
        if ($iGroups != 0) {
            $iG = true;
        } else {
            $iG = false;
        }
        if ($iTags != 0) {
            $iT = true;
        } else {
            $iT = false;
        }
        if ($xGroups != 0) {
            $xG = true;
        } else {
            $xG = false;
        }
        if ($xTags != 0) {
            $xT = true;
        } else {
            $xT = false;
        }
        if( !$this->_groups || !$this->_tags )  $this->_andOr = 1;
        /*
         * Set from statement depending on array sel
         */
        if ($iG && $iT && $xG && $xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " INNER JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL )
                    AND contact_a.id NOT IN(SELECT contact_id FROM Xg_{$this->_tableName})
                    AND contact_a.id NOT IN(SELECT contact_id FROM Xt_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $from .= " LEFT JOIN Xg_{$this->_tableName} temptable3 ON (contact_a.id = temptable3.contact_id)";
                $from .= " LEFT JOIN Xt_{$this->_tableName} temptable4 ON (contact_a.id = temptable4.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL OR
                    temptable3.contact_id IS NOT NULL OR temptable4.contact_id IS NOT NULL)";
            }
        }
        if ($iG && $iT && $xG && !$xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " INNER JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL )
                    AND contact_a.id NOT IN(SELECT contact_id FROM Xg_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $from .= " LEFT JOIN Xg_{$this->_tableName} temptable3 ON (contact_a.id = temptable3.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL OR
                    temptable3.contact_id IS NOT NULL)";
            }
        }
        if ($iG && $iT && !$xG && $xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " INNER JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL )
                    AND contact_a.id NOT IN(SELECT contact_id FROM Xt_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $from .= " LEFT JOIN Xt_{$this->_tableName} temptable3 ON (contact_a.id = temptable3.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL OR
                    temptable3.contact_id IS NOT NULL)";
            }
        }
        if ($iG && $iT && !$xG && !$xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " INNER JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL )";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL)";
            }
        }
        if ($iG && !$iT && $xG && $xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL) AND contact_a.id NOT IN(
                    SELECT contact_id FROM Xg_{$this->_tableName}) AND contact_a.id NOT IN(
                    SELECT contact_id FROM Xt_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN Xg_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $from .= " LEFT JOIN Xt_{$this->_tableName} temptable3 ON (contact_a.id = temptable3.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL OR
                    temptable3.contact_id IS NOT NULL)";
            }
        }
        if ($iG && !$iT && $xG && !$xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL) AND contact_a.id NOT IN(
                    SELECT contact_id FROM Xg_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN Xg_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL)";
            }
        }
        if ($iG && !$iT && !$xG && $xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL) AND contact_a.id NOT IN(
                    SELECT contact_id FROM Xt_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN Xt_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL)";
            }
        }
        if ($iG && !$iT && !$xG && !$xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL)";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL)";
            }
        }
        if (!$iG && $iT && $xG && $xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL) AND contact_a.id NOT IN(
                    SELECT contact_id FROM Xg_{$this->_tableName}) AND contact_a.id NOT IN(
                    SELECT contact_id FROM Xt_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN Xg_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $from .= " LEFT JOIN Xt_{$this->_tableName} temptable3 ON (contact_a.id = temptable3.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL OR
                    temptable3.contact_id IS NOT NULL)";
            }
        }
        if (!$iG && $iT && $xG && !$xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL) AND contact_a.id NOT IN(
                    SELECT contact_id FROM Xg_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN Xg_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL)";
            }
        }
        if (!$iG && $iT && !$xG && $xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL) AND contact_a.id NOT IN(
                    SELECT contact_id FROM Xt_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN Xt_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL)";
            }
        }
        if (!$iG && $iT && !$xG && !$xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL)";
            } else {
                $from .= " LEFT JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL)";
            }
        }
        if (!$iG && !$iT && $xG && $xT) {
            if ($this->_andOr == 1) {
                $this->_where = "contact_a.id NOT IN(SELECT contact_id FROM Xg_{$this->_tableName})
                    AND contact_a.id NOT IN(SELECT contact_id FROM Xt_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Xg_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN Xt_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL)";
            }
        }
        if (!$iG && !$iT && !$xG && $xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN It_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "contact_a.id NOT IN(SELECT contact_id FROM Xt_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Xt_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN It_{$this->_tableName} temptable2 ON (contact_a.id = temptable2.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL OR temptable2.contact_id IS NOT NULL)";
            }
        }
        if (!$iG && !$iT && $xG && !$xT) {
            if ($this->_andOr == 1) {
                $from .= " INNER JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "contact_a.id NOT IN(SELECT contact_id FROM Xg_{$this->_tableName})";
            } else {
                $from .= " LEFT JOIN Ig_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $from .= " LEFT JOIN Xg_{$this->_tableName} temptable1 ON (contact_a.id = temptable1.contact_id)";
                $this->_where = "( temptable1.contact_id IS NOT NULL)";
            }
        }

        $from .= " LEFT JOIN civicrm_email ON ( contact_a.id = civicrm_email.contact_id AND ( civicrm_email.is_primary = 1 OR civicrm_email.is_bulkmail = 1 ) )";

        return $from;
    }

    function where( $includeContactIDs = false ) {
         
        if ( $includeContactIDs ) {
            $contactIDs = array( );
            
            foreach ( $this->_formValues as $id => $value ) {
                if ( $value &&
                     substr( $id, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) {
                    $contactIDs[] = substr( $id, CRM_Core_Form::CB_PREFIX_LEN );
                }
            }
            
            if ( ! empty( $contactIDs ) ) {
                $contactIDs = implode( ', ', $contactIDs );
                $clauses[] = "contact_a.id IN ( $contactIDs )";
            }
            $where = "{$this->_where} AND " . implode( ' AND ', $clauses );
        } else {
            $where = $this->_where;
        }
           
        return $where;
    }

    /* 
     * Functions below generally don't need to be modified
     */
    function count( ) {
        $sql = $this->all( );
           
        $dao = CRM_Core_DAO::executeQuery( $sql );
        return $dao->N;
    }
       
    function contactIDs( $offset = 0, $rowcount = 0, $sort = null) { 
        return $this->all( $offset, $rowcount, $sort, false, true );
    }

    function &columns( ) {
        return $this->_columns;
    }

    function summary( ) {
        return null;
    }

    function templateFile( ) {
        return 'CRM/Contact/Form/Search/Custom.tpl';
    }

}