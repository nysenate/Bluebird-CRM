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

require_once 'CRM/Core/Form.php';

require_once 'CRM/Core/DAO/Mapping.php';
require_once 'CRM/Core/DAO/MappingField.php';

require_once 'CRM/Member/Import/Parser/Membership.php';

/**
 * This class gets the name of the file to upload
 */
class CRM_Member_Import_Form_MapField extends CRM_Core_Form {

    /**
     * cache of preview data values
     *
     * @var array
     * @access protected
     */
    protected $_dataValues;

    /**
     * mapper fields
     *
     * @var array
     * @access protected
     */
    protected $_mapperFields;

    /**
     * loaded mapping ID
     *
     * @var int
     * @access protected
     */
    protected $_loadedMappingId;

    /**
     * number of columns in import file
     *
     * @var int
     * @access protected
     */
    protected $_columnCount;


    /**
     * column headers, if we have them
     *
     * @var array
     * @access protected
     */
    protected $_columnHeaders;

    /**
     * an array of booleans to keep track of whether a field has been used in
     * form building already.
     *
     * @var array
     * @access protected
     */
    protected $_fieldUsed;
    
    /**
     * to store contactType
     *
     * @var int
     * @static
     */
    static $_contactType = null;

    
    /**
     * Attempt to resolve the header with our mapper fields
     *
     * @param header
     * @param mapperFields
     * @return string
     * @access public
     */
    public function defaultFromHeader($header, &$patterns) {
        foreach ($patterns as $key => $re) {
            /* Skip the first (empty) key/pattern */
            if (empty($re)) continue;
            /* if we've already used this field, move on */
            //             if ($this->_fieldUsed[$key])
            //                 continue;
            /* Scan through the headerPatterns defined in the schema for a
             * match */
           
            if (preg_match($re, $header)) {
                $this->_fieldUsed[$key] = true;
                return $key;
            }
        }
        return '';
    }

    /**
     * Guess at the field names given the data and patterns from the schema
     *
     * @param patterns
     * @param index
     * @return string
     * @access public
     */
    public function defaultFromData(&$patterns, $index) {
        $best = '';
        $bestHits = 0;
        $n = count($this->_dataValues);
        
        foreach ($patterns as $key => $re) {
            if (empty($re)) continue;

//             if ($this->_fieldUsed[$key])
//                 continue;

            /* Take a vote over the preview data set */
            $hits = 0;
            for ($i = 0; $i < $n; $i++) {
                if (preg_match($re, $this->_dataValues[$i][$index])) {
                    $hits++;
                }
            }

            if ($hits > $bestHits) {
                $bestHits = $hits;
                $best = $key;
            }
        }
    
        if ($best != '') {
            $this->_fieldUsed[$best] = true;
        }
        return $best;
    }

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        $this->_mapperFields = $this->get( 'fields' ); 
        asort($this->_mapperFields);

        $this->_columnCount = $this->get( 'columnCount' );
        $this->assign( 'columnCount' , $this->_columnCount );
        $this->_dataValues = $this->get( 'dataValues' );
        $this->assign( 'dataValues'  , $this->_dataValues );
        
        $skipColumnHeader = $this->controller->exportValue( 'UploadFile', 'skipColumnHeader' );
        $this->_onDuplicate = $this->get('onDuplicate',isset($onDuplicate) ? $onDuplicate : "");

        $highlightedFields  = array();
        if ( $skipColumnHeader ) {
            $this->assign( 'skipColumnHeader' , $skipColumnHeader );
            $this->assign( 'rowDisplayCount', 3 );
            /* if we had a column header to skip, stash it for later */
            $this->_columnHeaders = $this->_dataValues[0];
        } else {
            $this->assign( 'rowDisplayCount', 2 );
        }
       
        //CRM-2219 removing other required fields since for updation only
        //membership id is required.
        if ( $this->_onDuplicate == CRM_Member_Import_Parser::DUPLICATE_UPDATE ) {
            $remove = array('membership_contact_id','email','first_name','last_name','external_identifier');
            foreach( $remove as $value ) {
                unset( $this->_mapperFields[$value] );
            }
            $highlightedFieldsArray = array( 'membership_id','membership_start_date','membership_type_id' );
            foreach ( $highlightedFieldsArray as $name ) {
                $highlightedFields[] = $name;
            }
        } else if( $this->_onDuplicate == CRM_Member_Import_Parser::DUPLICATE_SKIP ) {
            unset( $this->_mapperFields['membership_id'] );
            $highlightedFieldsArray = array('membership_contact_id','email', 'external_identifier', 'membership_start_date','membership_type_id');
            foreach ( $highlightedFieldsArray as $name ) {
                $highlightedFields[] = $name;
            }
        }    
        self::$_contactType = $this->get('contactType');
        $this->assign( 'highlightedFields', $highlightedFields );
    }
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        require_once "CRM/Core/BAO/Mapping.php";
        require_once "CRM/Core/OptionGroup.php";  
        //to save the current mappings
        if ( !$this->get('savedMapping') ) {
            $saveDetailsName = ts('Save this field mapping');
           $this->applyFilter('saveMappingName', 'trim');
            $this->add('text','saveMappingName',ts('Name'));
            $this->add('text','saveMappingDesc',ts('Description'));
        } else {
            $savedMapping = $this->get('savedMapping');
            
            list ($mappingName, $mappingContactType, $mappingLocation, $mappingPhoneType, $mappingRelation  ) = CRM_Core_BAO_Mapping::getMappingFields($savedMapping);
            
            $mappingName        = $mappingName[1];
            $mappingContactType = $mappingContactType[1];
            $mappingLocation    = CRM_Utils_Array::value('1',$mappingLocation);
            $mappingPhoneType   = CRM_Utils_Array::value('1',$mappingPhoneType);
            $mappingRelation    = CRM_Utils_Array::value('1',$mappingRelation);

            //mapping is to be loaded from database
   
            $params = array('id' => $savedMapping);
            $temp   = array ();
            $mappingDetails = CRM_Core_BAO_Mapping::retrieve($params, $temp);

            $this->assign('loadedMapping', $mappingDetails->name);
            $this->set('loadedMapping', $savedMapping);
            
            $getMappingName =&  new CRM_Core_DAO_Mapping();
            $getMappingName->id = $savedMapping;
            $getMappingName->mapping_type = 'Import Memberships';
            $getMappingName->find();
            while($getMappingName->fetch()) {
                $mapperName = $getMappingName->name;
            }

            $this->assign('savedName', $mapperName);

            $this->add('hidden','mappingId',$savedMapping);

            $this->addElement('checkbox','updateMapping',ts('Update this field mapping'), null);
            $saveDetailsName = ts('Save as a new field mapping');
            $this->add('text','saveMappingName',ts('Name'));
            $this->add('text','saveMappingDesc',ts('Description'));
        }
        
        $this->addElement('checkbox','saveMapping',$saveDetailsName, null, array('onclick' =>"showSaveDetails(this)"));
        
        $this->addFormRule( array( 'CRM_Member_Import_Form_MapField', 'formRule' ), $this );

        //-------- end of saved mapping stuff ---------

        $defaults = array( );
        $mapperKeys      = array_keys( $this->_mapperFields );
        $hasHeaders      = !empty($this->_columnHeaders);
        $headerPatterns  = $this->get( 'headerPatterns' );
        $dataPatterns    = $this->get( 'dataPatterns' );
        $hasLocationTypes = $this->get( 'fieldTypes' );
      

        /* Initialize all field usages to false */
        foreach ($mapperKeys as $key) {
            $this->_fieldUsed[$key] = false;
        }
        $this->_location_types = & CRM_Core_PseudoConstant::locationType();
        $sel1 = $this->_mapperFields;
        if ( !$this->get('onDuplicate') ) {
            unset($sel1['id']);
            unset($sel1['membership_id']);
        }

        $sel2[''] = null;

        $js = "<script type='text/javascript'>\n";
        $formName = 'document.forms.' . $this->_name;
        
        //used to warn for mismatch column count or mismatch mapping      
        $warning = 0;
        
        for ( $i = 0; $i < $this->_columnCount; $i++ ) {
            $sel =& $this->addElement('hierselect', "mapper[$i]", ts('Mapper for Field %1', array(1 => $i)), null);
            $jsSet = false;
            if( $this->get('savedMapping') ) {                                              
                if ( isset($mappingName[$i]) ) {
                    if ( $mappingName[$i] != ts('- do not import -')) {                                
                        
                        $mappingHeader = array_keys($this->_mapperFields, $mappingName[$i]);                        
                        
                        //When locationType is not set
                        $js .= "{$formName}['mapper[$i][1]'].style.display = 'none';\n";

                        //When phoneType is not set                           
                        $js .= "{$formName}['mapper[$i][2]'].style.display = 'none';\n";
                                                
                        $js .= "{$formName}['mapper[$i][3]'].style.display = 'none';\n";
                        
                        $defaults["mapper[$i]"] = array( $mappingHeader[0] );
                        $jsSet = true;
                    } else {
                        $defaults["mapper[$i]"] = array();
                    }                          
                    if ( ! $jsSet ) {
                        for ( $k = 1; $k < 4; $k++ ) {
                            $js .= "{$formName}['mapper[$i][$k]'].style.display = 'none';\n"; 
                        }
                    }
                } else {
                    // this load section to help mapping if we ran out of saved columns when doing Load Mapping
                    $js .= "swapOptions($formName, 'mapper[$i]', 0, 3, 'hs_mapper_".$i."_');\n";
                    
                    if ($hasHeaders) {
                        $defaults["mapper[$i]"] = array( $this->defaultFromHeader($this->_columnHeaders[$i],$headerPatterns) );
                    } else {
                        $defaults["mapper[$i]"] = array( $this->defaultFromData($dataPatterns, $i) );
                    }                    
                } //end of load mapping
            } else {
                $js .= "swapOptions($formName, 'mapper[$i]', 0, 3, 'hs_mapper_".$i."_');\n";
                if ($hasHeaders) {
                    // Infer the default from the skipped headers if we have them
                    $defaults["mapper[$i]"] = array(
                                                           $this->defaultFromHeader($this->_columnHeaders[$i], 
                                                                                    $headerPatterns),
                                                           //                     $defaultLocationType->id
                                                           0
                                                    );
                } else {
                    // Otherwise guess the default from the form of the data
                    $defaults["mapper[$i]"] = array(
                                                           $this->defaultFromData($dataPatterns, $i),
                                                           //                     $defaultLocationType->id
                                                           0
                                                    );
                }
            }
            $sel->setOptions(array($sel1, $sel2, (isset($sel3)) ? $sel3 : "", (isset($sel4)) ? $sel4 : ""));
        }
        $js .= "</script>\n";
        $this->assign('initHideBoxes', $js);
        
        //set warning if mismatch in more than 
        if (isset($mappingName) ) {
            if ( ($this->_columnCount != count($mappingName)) ) {
                $warning++;            
            }
        }
        if ( $warning != 0 && $this->get('savedMapping') ) {
            $session = CRM_Core_Session::singleton( );
            $session->setStatus( ts( 'The data columns in this import file appear to be different from the saved mapping. Please verify that you have selected the correct saved mapping before continuing.' ) );
        } else {
            $session = CRM_Core_Session::singleton( );
            $session->setStatus( null ); 
        }

        $this->setDefaults( $defaults );       

        $this->addButtons( array(
                                 array ( 'type'      => 'back',
                                         'name'      => ts('<< Previous') ),
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Continue >>'),
                                         'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
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
    static function formRule( $fields, $files, $self ) {
        $errors  = array( );
        
        if (!array_key_exists('savedMapping', $fields)) {
            $importKeys = array();
            foreach ($fields['mapper'] as $mapperPart) {
                $importKeys[] = $mapperPart[0];
            } 
            // FIXME: should use the schema titles, not redeclare them
            $requiredFields = array(
                                    'membership_contact_id'  => ts('Contact ID'),
                                    'membership_type_id'     => ts('Membership Type'),
                                    'membership_start_date'  => ts('Membership Start Date')
                                    );
            
            $contactTypeId = $self->get('contactType');
            $contactTypes  = array(
                                   CRM_Member_Import_Parser::CONTACT_INDIVIDUAL   => 'Individual',
                                   CRM_Member_Import_Parser::CONTACT_HOUSEHOLD    => 'Household',
                                   CRM_Member_Import_Parser::CONTACT_ORGANIZATION => 'Organization'
                                   );
            $params = array(
                            'level'        => 'Strict',
                            'contact_type' => $contactTypes[$contactTypeId]
                            );
            require_once 'CRM/Dedupe/BAO/RuleGroup.php';
            list($ruleFields, $threshold) = CRM_Dedupe_BAO_RuleGroup::dedupeRuleFieldsWeight( $params );
            $weightSum = 0;
            foreach ($importKeys as $key => $val) {
                if (array_key_exists($val,$ruleFields)) {
                    $weightSum += $ruleFields[$val];
                }
            }
            foreach ($ruleFields as $field => $weight) {
                $fieldMessage .= ' '.$field.'(weight '.$weight.')';
            }
            
            foreach ($requiredFields as $field => $title) {
                if (!in_array($field, $importKeys)) {
                    if( $field == 'membership_contact_id' ) {
                        if ( ( ( $weightSum >= $threshold || in_array('external_identifier', $importKeys ) ) &&
                               $self->_onDuplicate != CRM_Member_Import_Parser::DUPLICATE_UPDATE ) ||
                             in_array('membership_id', $importKeys) ) {
                            continue;    
                        } else {
                            $errors['_qf_default'] .= ts('Missing required contact matching fields.') . " $fieldMessage " . ts('(Sum of all weights should be greater than or equal to threshold: %1).',array(1 => $threshold)) . ' ' . ts('(OR Membership ID if update mode.)') . '<br />';
                        }
                        
                    } else {
                        $errors['_qf_default'] .= ts('Missing required field: %1', array(1 => $title)) . '<br />';
                    }
                }
            }
        }


        if ( CRM_Utils_Array::value( 'saveMapping', $fields ) ) {
            $nameField = CRM_Utils_Array::value( 'saveMappingName', $fields );
            if ( empty( $nameField ) ) {
                $errors['saveMappingName'] = ts('Name is required to save Import Mapping');
            } else {
                $mappingTypeId = CRM_Core_OptionGroup::getValue( 'mapping_type', 'Import Membership', 'name' );
               
                if ( CRM_Core_BAO_Mapping::checkMapping( $nameField, $mappingTypeId ) ) {
                    $errors['saveMappingName'] = ts('Duplicate Import Membership Mapping Name');
                }
            }
        }

        if ( !empty($errors) ) {
            if (!empty($errors['saveMappingName'])) {
                $_flag = 1;
                require_once 'CRM/Core/Page.php';
                $assignError = new CRM_Core_Page(); 
                $assignError->assign('mappingDetailsError', $_flag);
            }
            return $errors;
        }

        return true;
    }

    /**
     * Process the mapped fields and map it into the uploaded file
     * preview the file and extract some summary statistics
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        $params = $this->controller->exportValues( 'MapField' );
        //reload the mapfield if load mapping is pressed
        if( !empty($params['savedMapping']) ) {            
            $this->set('savedMapping', $params['savedMapping']);
            $this->controller->resetPage( $this->_name );
            return;
        }
        
        $fileName         = $this->controller->exportValue( 'UploadFile', 'uploadFile' );
        $skipColumnHeader = $this->controller->exportValue( 'UploadFile', 'skipColumnHeader' );

        $config = CRM_Core_Config::singleton( );
        $seperator = $config->fieldSeparator;

        $mapperKeys = array( );
        $mapper     = array( );
        $mapperKeys = $this->controller->exportValue( $this->_name, 'mapper' );
        $mapperKeysMain     = array();
        $mapperLocType      = array();
        $mapperPhoneType    = array();
        
        for ( $i = 0; $i < $this->_columnCount; $i++ ) {
            $mapper[$i]     = $this->_mapperFields[$mapperKeys[$i][0]];
            $mapperKeysMain[$i] = $mapperKeys[$i][0];
            
            if (is_numeric($mapperKeys[$i][1])) {
                $mapperLocType[$i] = $mapperKeys[$i][1];
            } else {
                $mapperLocType[$i] = null;
            }

            if ( !is_numeric($mapperKeys[$i][2])) {
                $mapperPhoneType[$i] = $mapperKeys[$i][2];
            } else {
                $mapperPhoneType[$i] = null;
            }
        }

        $this->set( 'mapper'    , $mapper     );
               
        // store mapping Id to display it in the preview page 
        $this->set('loadMappingId', $params['mappingId']);
        
        //Updating Mapping Records
        if ( CRM_Utils_Array::value('updateMapping', $params)) {
            $mappingFields = new CRM_Core_DAO_MappingField();
            $mappingFields->mapping_id = $params['mappingId'];
            $mappingFields->find( );
            
            $mappingFieldsId = array();                
            while($mappingFields->fetch()) {
                if ( $mappingFields->id ) {
                    $mappingFieldsId[$mappingFields->column_number] = $mappingFields->id;
                }
            }
                
            for ( $i = 0; $i < $this->_columnCount; $i++ ) {
                $updateMappingFields = new CRM_Core_DAO_MappingField();
                $updateMappingFields->id = $mappingFieldsId[$i];
                $updateMappingFields->mapping_id = $params['mappingId'];
                $updateMappingFields->column_number = $i;

                list($id, $first, $second) = explode('_', $mapperKeys[$i][0]);
                $updateMappingFields->name = $mapper[$i];
                $updateMappingFields->save();                
            }
        }
        
        //Saving Mapping Details and Records
        if ( CRM_Utils_Array::value('saveMapping', $params)) {
            $mappingParams = array('name'            => $params['saveMappingName'],
                                   'description'     => $params['saveMappingDesc'],
                                   'mapping_type_id' => CRM_Core_OptionGroup::getValue( 'mapping_type',
                                                                                        'Import Membership',
                                                                                        'name' ) );
            $saveMapping = CRM_Core_BAO_Mapping::add( $mappingParams ) ;

            for ( $i = 0; $i < $this->_columnCount; $i++ ) {                  
                
                $saveMappingFields = new CRM_Core_DAO_MappingField();
                $saveMappingFields->mapping_id = $saveMapping->id;
                $saveMappingFields->column_number = $i;                             
                
                list($id, $first, $second) = explode('_', $mapperKeys[$i][0]);
                $saveMappingFields->name = $mapper[$i];
                $saveMappingFields->save();
            }
            $this->set( 'savedMapping', $saveMappingFields->mapping_id );
        }

        $parser = new CRM_Member_Import_Parser_Membership( $mapperKeysMain ,$mapperLocType ,$mapperPhoneType );
        $parser->run( $fileName, $seperator, $mapper, $skipColumnHeader,
                      CRM_Member_Import_Parser::MODE_PREVIEW, $this->get('contactType') );
        // add all the necessary variables to the form
        $parser->set( $this );    
    }

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return ts('Match Fields');
    }

    
}


