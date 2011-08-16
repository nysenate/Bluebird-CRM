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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
/**
 * This class is to build the form for Deleting Group
 */
class CRM_Custom_Form_MoveField extends CRM_Core_Form {

    /**
     * the src group id
     *
     * @var int
     */
    protected $_srcGID;

    /**
     * the src field id
     *
     * @var int
     */
    protected $_srcFID;

    /**
     * the dst group id
     *
     * @var int
     */
    protected $_dstGID;

    /**
     * the dst field id
     *
     * @var int
     */
    protected $_dstFID;

    /**
     * The title of the field being moved
     *
     * @var string
     */
    protected $_label;

    /**
     * set up variables to build the form
     *
     * @return void
     * @acess protected
     */
    function preProcess( ) {
        $this->_srcFID    = CRM_Utils_Request::retrieve( 'fid', 'Positive',
                                                         $this, true );

        $this->_srcGID = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField',
                                                      $this->_srcFID,
                                                      'custom_group_id' );

        $this->_label  = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField',
                                                      $this->_srcFID,
                                                      'label' );

        CRM_Utils_System::setTitle( ts( 'Custom Field Move: %1',
                                        array( 1 => $this->_label ) ) );
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        
        $customGroup = CRM_Core_PseudoConstant::customGroup( );
        unset( $customGroup[$this->_srcGID] );
        if ( empty( $customGroup ) ) {
            CRM_Core_Error::statusBounce( ts( 'You need more than one custom group to move fields' ) );
        }

        $customGroup = array( ''  => ts( '- select -' ) ) + $customGroup;
        $this->add( 'select',
                    'dst_group_id',
                    ts( 'Destination Custom Group' ),
                    $customGroup,
                    true );
        $this->add('checkbox', 'is_copy', ts('Copy?'));
                    
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Move Custom Field'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );

        $this->addFormRule( array( 'CRM_Custom_Form_MoveField', 'formRule' ), $this );
    }

    static function formRule( $fields, $files, $self) {
        $errors = array( );

        $query = "
SELECT id
FROM   civicrm_custom_field
WHERE  custom_group_id = %1
AND    label = %2
";
        $params = array( 1 => array( $fields['dst_group_id'], 'Integer' ),
                         2 => array( $self->_label, 'String' ) );
        $count = CRM_Core_DAO::singleValueQuery( $query, $params );
        if ( $count > 0 ) {
            $errors['dst_group_id'] = ts( 'A field of the same label exists in the destination group' );
        }
        
        $tableName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomGroup',
                                                  $self->_srcGID,
                                                  'table_name' );
        $columnName = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField',
                                                  $self->_srcFID,
                                                  'column_name' );

        $query = "
SELECT count(*)
FROM   $tableName
WHERE  $columnName is not null
";
        $count = CRM_Core_DAO::singleValueQuery( $query,
                                                 CRM_Core_DAO::$_nullArray );
        if ( $count > 0 ) {
            $query = "
SELECT extends
FROM   civicrm_custom_group
WHERE  id IN ( %1, %2 )
";
            $params = array( 1 => array( $self->_srcGID, 'Integer' ),
                             2 => array( $fields['dst_group_id'], 'Integer' ) );
                
            $dao = CRM_Core_DAO::executeQuery( $query, $params );
            $extends = array( );
            while ( $dao->fetch( ) ) {
                $extends[] = $dao->extends;
            }
            if ( $extends[0] != $extends[1] ) {
                $errors['dst_group_id'] = ts( 'The extends type of dst group does not match the src field' );
            }
        }

        return empty( $errors ) ? true : $errors;
    }    

    /**
     * Process the form when submitted
     *
     * @return void
     * @access public
     */
    public function postProcess( ) {
        // step 1: copy and create dstField and column
        require_once 'CRM/Core/BAO/CustomField.php';
        $field = new CRM_Core_DAO_CustomField( );
        $field->id = $this->_srcFID;
        if ( ! $field->find( true ) ) {
            CRM_Core_Error::fatal( );
        }

        // now change the field group ID and save it, also unset the id
        unset( $field->id );
        
        // step 2: copy data from srcColumn to dstColumn
        $query = "
INSERT INTO $dstTable ( $entityID, $dstColumn )
SELECT $entityID, $srcColumn
FROM   $srcTable
ON DUPLICATE KEY UPDATE $dstColumn = $srcColumn";
        CRM_Core_DAO::query( $query, CRM_Core_DAO::$_nullArray );

        // step 3: remove srcField (which should also delete the srcColumn
        require_once 'CRM/Core/BAO/CustomField.php';
        $field = new CRM_Core_DAO_CustomField( );
        $field->id = $this->_srcFID;
        CRM_Core_BAO_CustomField::deleteField( $field );
    }

}
