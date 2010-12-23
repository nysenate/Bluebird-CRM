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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/DAO/MappingField.php';

/**
 * This class gets the name of the file to upload
 */
class CRM_Export_Form_Map extends CRM_Core_Form 
{
    
    /**
     * mapper fields
     *
     * @var array
     * @access protected
     */
    protected $_mapperFields;

    /**
     * number of columns in import file
     *
     * @var int
     * @access protected
     */
    protected $_exportColumnCount;

    /**
     * loaded mapping ID
     *
     * @var int
     * @access protected
     */
    protected $_mappingId;

   
    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function preProcess() {

        $this->_exportColumnCount = $this->get('exportColumnCount');
        if (! $this->_exportColumnCount ) {
            $this->_exportColumnCount = 10;
        } else {
            $this->_exportColumnCount = $this->_exportColumnCount + 10;
        }
        
        $this->_mappingId =  $this->get('mappingId');
    }
    
    public function buildQuickForm( ) 
    {
        require_once "CRM/Core/BAO/Mapping.php";
        CRM_Core_BAO_Mapping::buildMappingForm($this, 'Export', $this->_mappingId, $this->_exportColumnCount, $blockCnt = 2, $this->get('exportMode'));

        $this->addButtons( array(
                                 array ( 'type'      => 'back',
                                         'name'      => ts('<< Previous') ),
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Export >>'),
                                         'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ),
                                 array ( 'type'      => 'done',
                                         'name'      => ts('Done') ),
                                 )
                           );
    }


    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $fields, $values, $mappingTypeId ) 
    {
        $errors  = array( );

        if ( CRM_Utils_Array::value( 'saveMapping', $fields ) && $fields['_qf_Map_next']) {
            $nameField = CRM_Utils_Array::value( 'saveMappingName', $fields );
            if ( empty( $nameField ) ) {
                $errors['saveMappingName'] = ts('Name is required to save Export Mapping');
            } else {
                //check for Duplicate mappingName
                if ( CRM_Core_BAO_Mapping::checkMapping( $nameField,  $mappingTypeId ) ) {
                    $errors['saveMappingName'] = ts('Duplicate Export Mapping Name');
                }
            }
        }
        
        if ( !empty($errors) ) {
            $_flag = 1;
            require_once 'CRM/Core/Page.php';
            $assignError = new CRM_Core_Page(); 
            $assignError->assign('mappingDetailsError', $_flag);
            return $errors;
        } else {
            return true;
        }
    }    


    /**
     * Process the uploaded file
     *
     * @return void
     * @access public
     */
    public function postProcess( ) {
        $params = $this->controller->exportValues( $this->_name );
        
        $currentPath = CRM_Utils_System::currentPath( );
        
        $urlParams = null;
        $qfKey = CRM_Utils_Request::retrieve( 'qfKey', 'String', $this );
        require_once 'CRM/Utils/Rule.php';
        if ( CRM_Utils_Rule::qfKey( $qfKey ) ) $urlParams = "&qfKey=$qfKey"; 
        
        //get the button name
        $buttonName = $this->controller->getButtonName('done');
        $buttonName1 = $this->controller->getButtonName('next');
        if ( $buttonName == '_qf_Map_done') {
            $this->set('exportColumnCount',null);
            $this->controller->resetPage( $this->_name );
            return CRM_Utils_System::redirect( CRM_Utils_System::url($currentPath, 'force=1'.$urlParams ) );
        }

        if ( $this->controller->exportValue( $this->_name, 'addMore' ) )  {
            $this->set( 'exportColumnCount', $this->_exportColumnCount );
            return;
        }

        $mapperKeys = $params['mapper'][1];  
       
        $checkEmpty = 0;
        foreach( $mapperKeys as $value ) {
            if ($value[0]) {
                $checkEmpty++;
            }
        }

        if ( !$checkEmpty ) {
            $this->set('mappingId', null);
            require_once 'CRM/Utils/System.php';            
            CRM_Utils_System::redirect( CRM_Utils_System::url( $currentPath, '_qf_Map_display=true'.$urlParams ) );
        }

        if ( $buttonName1 == '_qf_Map_next' ) {
            if ( CRM_Utils_Array::value('updateMapping', $params)) { 
                //save mapping fields
                CRM_Core_BAO_Mapping::saveMappingFields($params, $params['mappingId'] );
            }
            
            if ( CRM_Utils_Array::value('saveMapping', $params) ) { 
                $mappingParams = array('name'            => $params['saveMappingName'],
                                       'description'     => $params['saveMappingDesc'],
                                       'mapping_type_id' => $this->get( 'mappingTypeId') );
                
                $saveMapping = CRM_Core_BAO_Mapping::add( $mappingParams );
                
                //save mapping fields
                CRM_Core_BAO_Mapping::saveMappingFields($params, $saveMapping->id);
            }
        }

        //get the csv file
        require_once "CRM/Export/BAO/Export.php";
        CRM_Export_BAO_Export::exportComponents( $this->get( 'selectAll' ),
                                                 $this->get( 'componentIds' ),
                                                 $this->get( 'queryParams' ),
                                                 $this->get( CRM_Utils_Sort::SORT_ORDER ),
                                                 $mapperKeys,
                                                 $this->get( 'returnProperties' ),
                                                 $this->get( 'exportMode' ),
                                                 $this->get( 'componentClause' ),
                                                 $this->get( 'componentTable' ),
                                                 $this->get( 'mergeSameAddress' ),
                                                 $this->get( 'mergeSameHousehold' )
                                                 );
    }
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle( ) {
        return ts('Select Fields to Export');
    }

}


