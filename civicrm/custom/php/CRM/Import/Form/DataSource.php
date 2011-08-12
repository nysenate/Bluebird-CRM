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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Import/Parser/Contact.php';

/**
 * This class delegates to the chosen DataSource to grab the data to be
 *  imported.
 */
class CRM_Import_Form_DataSource extends CRM_Core_Form {
    
    private $_dataSource;
    
    private $_dataSourceIsValid = false;
    
    private $_dataSourceClassFile;

    /**
     * Before starting the import process, make sure that the current user
     * and system configuration will permit a successful import. Check file
     * permissions, database permissions, and datasource configuration.
     *
     * @return void
     * @access public
     */
    public function preProcess( ) {
        // The system configuration and the supplied form values are needed
        // in order to validate all the permissons and resource values.
        $config  = CRM_Core_Config::singleton();
        $this->_params = $this->controller->exportValues( $this->_name );

        // Test database user privilege to create table(Temporary) CRM-4725
        // Since the import process relies heavily on temporary tables, make sure we can make them
        CRM_Core_Error::ignoreException();
        $daoTestPrivilege = new CRM_Core_DAO();
        $daoTestPrivilege->query( "CREATE TEMPORARY TABLE import_job_permission_one(test int) ENGINE=InnoDB" );
        $daoTestPrivilege->query( "CREATE TEMPORARY TABLE import_job_permission_two(test int) ENGINE=InnoDB" );
        $daoTestPrivilege->query( "DROP TABLE IF EXISTS import_job_permission_one, import_job_permission_two" );
        CRM_Core_Error::setCallback();
        if( $daoTestPrivilege->_lastError ) {
            CRM_Core_Error::fatal( ts('Database Configuration Error: Insufficient permissions. Import requires that the CiviCRM database user has permission to create temporary tables. Contact your site administrator for assistance.') );
        }

        // Test the error log file permissions since they might be used during import
        $problem_files = array();
        $handler = opendir($config->uploadDir);
        $errorFiles = array( 'sqlImport.errors', 'sqlImport.conflicts', 'sqlImport.duplicates', 'sqlImport.mismatch' );
        while ($file = readdir($handler)) {
            if ( $file != '.' && $file != '..' && in_array( $file, $errorFiles) && !is_writable( $config->uploadDir . $file ) ) {
                $problem_files[] = $file;
            }
        }
        closedir($handler);
        if ( $problem_files ) {
            CRM_Core_Error::fatal (ts('<b>%1</b> file(s) in %2 directory are not writable. Listed file(s) might be used during the import to log the errors occurred during Import process. Contact your site administrator for assistance.', array( 1 => implode(', ', $problem_files), 2 => $config->uploadDir ) ) );
        }

        // First check $_GET then check exported parameters for a dataSource field
        $this->_dataSource = CRM_Utils_Request::retrieve('dataSource', 'String', CRM_Core_DAO::$_nullObject);
        $this->assign('showOnlyDataSourceFormPane', (bool)$this->_dataSource);
        if ( !$this->_dataSource ) {
            //considering dataSource as base criteria instead of hidden_dataSource.
            $dataSource_array = CRM_Utils_Array::value('dataSource', $this->_params);
            $this->_dataSource = CRM_Utils_Array::value('dataSource', $_POST, $dataSource_array);
        }

        // Configure the requested datasource is present and valid
        $this->_dataSourceIsValid = (strpos($this->_dataSource, 'CRM_Import_DataSource_' ) === 0);
        if ( $this->_dataSourceIsValid ) {
            $this->assign( 'showDataSourceFormPane', true );
            $dataSourcePath = explode( '_', $this->_dataSource );
            $templateFile = "CRM/Import/Form/" . $dataSourcePath[3] . ".tpl";
            $this->assign( 'dataSourceFormTemplateFile', $templateFile );
        }
    }
    
    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    
    public function buildQuickForm( ) {

        $this->assign( 'urlPath'   , "civicrm/import" );
        $this->assign( 'urlPathVar', 'snippet=4' );

        // If there's a dataSource in the query string, we need to load
        // the form from the chosen DataSource class
        if ( $this->_dataSourceIsValid ) {
            $this->_dataSourceClassFile = str_replace( '_', '/', $this->_dataSource ) . ".php";
            require_once $this->_dataSourceClassFile;
            eval( "{$this->_dataSource}::buildQuickForm( \$this );" );
        }

        // Add the set of datasources as a select dropdown.
        $this->add('select', 'dataSource', ts('Data Source'), $this->_getDataSources(), true,
                   array('onchange' => 'buildDataSourceFormBlock(this.value);'));
        // NYSS 3750 - Moved to setDefaultValues()
        // $this->setDefaults(array('dataSource' => 'CRM_Import_DataSource_CSV'));

        // Add the duplicate handling radio options
        $duplicateOptions = array();
        foreach( array( 'Skip'                  => CRM_Import_Parser::DUPLICATE_SKIP,
                        'Update'                => CRM_Import_Parser::DUPLICATE_UPDATE,
                        'Fill'                  => CRM_Import_Parser::DUPLICATE_FILL,
                        'No Duplicate Checking' => CRM_Import_Parser::DUPLICATE_NOCHECK) as
                        $text                   => $value ) {

            $duplicateOptions[] = HTML_QuickForm::createElement('radio', null, null, ts($text), $value);
        }
        $this->addGroup($duplicateOptions, 'onDuplicate', ts('For Duplicate Contacts'));
        // NYSS - Moved to setDefaultValues()
        // $this->setDefaults(array('onDuplicate' => CRM_Import_Parser::DUPLICATE_SKIP));

        // Build a select box for all the saved mappings in the system
        require_once "CRM/Core/BAO/Mapping.php";
        require_once "CRM/Core/OptionGroup.php";
        $mappingArray = CRM_Core_BAO_Mapping::getMappings(CRM_Core_OptionGroup::getValue('mapping_type', 'Import Contact', 'name'));
        $this->assign('savedMapping',$mappingArray);
        $this->addElement('select','savedMapping', ts('Mapping Option'), array('' => ts('- select -'))+$mappingArray);

        // NYSS - Moved to setDefaultValues()
        // if ( $loadeMapping = $this->get('loadedMapping') ) {
        //     $this->assign('loadedMapping', $loadeMapping );
        //     $this->setDefaults(array('savedMapping' => $loadeMapping));
        // }


        // NYSS - Regenerate the dedupe rule set on type change!
        // $js = array('onClick' => "buildSubTypes();");
		$js = array('onClick' => "buildSubTypes();buildDedupeRules();");

        // Build out the contact types option lists
        require_once 'CRM/Contact/BAO/ContactType.php';
        $contactOptions = array();
        foreach( array( 'Individual'   => CRM_Import_Parser::CONTACT_INDIVIDUAL,
                        'Household'    => CRM_Import_Parser::CONTACT_HOUSEHOLD,
                        'Organization' => CRM_Import_Parser::CONTACT_ORGANIZATION ) as
                        $type          => $value ) {

            if ( CRM_Contact_BAO_ContactType::isActive( $type ) ) {
                $contactOptions[] = HTML_QuickForm::createElement('radio', null, null, ts($type), $value, $js);
            }
        }
        $this->addGroup($contactOptions, 'contactType', ts('Contact Type'));
        // NYSS - Moved to setDefaultValues()
        // $this->setDefaults(array('contactType' => CRM_Import_Parser::CONTACT_INDIVIDUAL));


        // Add empty subtype select box to later be filled by javascript
        $this->addElement('select', 'subType', ts('Subtype'));
        // NYSS - Add a dedupe option group as well
		$this->addElement('select', 'dedupe' , ts( 'Dedupe Rule' ));


		// Create a different radio button for each allowed date format
        require_once 'CRM/Core/Form/Date.php';
        CRM_Core_Form_Date::buildAllowedDateFormats($this);

        // If a geocoder is configured, add an option to geocode on import as well
        $config = CRM_Core_Config::singleton();
        $geoCode = !empty($config->geocodeMethod);
        $this->assign( 'geoCode',$geoCode );
        if ($geoCode) {
            $this->addElement('checkbox', 'doGeocodeAddress', ts('Lookup mapping info during import?'));
        }


        // NYSS - Add a field separator option to allow for all *sv formats
		$this->addElement('text','fieldSeparator', ts('Import Field Separator'), array('size' => 2));

		// Add general navigation options
        $this->addButtons( array(
              array ('type'         => 'upload',
                     'name'         => ts('Continue >>'),
                     'spacing'      => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                     'isDefault'    => true),
              array ('type'         => 'cancel',
                     'name'         => ts('Cancel'))
        ));
    }

	// NYSS 3750 - New method to set all the default values in one place
	function setDefaultValues( ) {
 	        $config =& CRM_Core_Config::singleton( );
 	        $defaults = array( 'dataSource'     => 'CRM_Import_DataSource_CSV',
 	                           'onDuplicate'    => CRM_Import_Parser::DUPLICATE_SKIP,
 	                           'contactType'    => CRM_Import_Parser::CONTACT_INDIVIDUAL,
 	                           'fieldSeparator' => $config->fieldSeparator,
							 );
 	
 	        if ( $loadeMapping = $this->get('loadedMapping') ) {
 	            $this->assign('loadedMapping', $loadeMapping );
 	            $defaults['savedMapping'] = $loadeMapping;
 	        }
 	
 	        return $defaults;
 	}

    /*
     * Dynamically build a list of data sources based on file name convention
     */
    private function _getDataSources() {
        // Open the data source dir and scan it for class files
        $config = CRM_Core_Config::singleton();
        $dataSourceDir = $config->importDataSourceDir;
        $dataSources = array( );
        if (!is_dir($dataSourceDir)) {
            CRM_Core_Error::fatal( "Import DataSource directory $dataSourceDir does not exist" );
        }
        if (!$dataSourceHandle = opendir($dataSourceDir)) {
            CRM_Core_Error::fatal( "Unable to access DataSource directory $dataSourceDir" );
        }

        while (($dataSourceFile = readdir($dataSourceHandle)) !== false) {
            $fileType = filetype($dataSourceDir . $dataSourceFile);
            $matches = array( );
            if (($fileType == 'file' || $fileType == 'link') &&
                preg_match('/^(.+)\.php$/',$dataSourceFile,$matches)) {
                $dataSourceClass = "CRM_Import_DataSource_" . $matches[1];
                require_once $dataSourceDir . DIRECTORY_SEPARATOR . $dataSourceFile;
                eval("\$info = $dataSourceClass::getInfo();");
                $dataSources[$dataSourceClass] = $info['title'];
            }
        }
        closedir($dataSourceHandle);
        return $dataSources;
    }
    
    /**
     * Call the DataSource's postProcess method to take over
     * and then setup some common data structures for the next step
     *
     * @return void
     * @access public
     */
    public function postProcess( ) {
        // Cut things short if somehow impossibly we have an invalid data source
        if(!$this->_dataSourceIsValid) {
            CRM_Core_Error::fatal("Invalid DataSource on form post. This shouldn't happen!");
        }


        // Reset the next page in the import process
        $this->controller->resetPage( 'MapField' );


        //Fetch the parameters for this particular page
        $this->_params = $this->controller->exportValues( $this->_name );


        // Request the following additional parameters from the import controller
        // Also persist these values in the session for future pages
        foreach ( array(  'onDuplicate'     => 'onDuplicate',
                          'dedupe'          => 'dedupe',
                          'contactType'     => 'contactType',
                          'contactSubType'  => 'subType',
                          'dateFormats'     => 'dateFormats',
                          'savedMapping'    => 'savedMapping') as
                          $storeName        => $storeValueName ) {

                $$storeName = $this->exportValue( $storeValueName );
                $this->set( $storeName, $$storeName );
        }


        // A couple values from the controller page need to be added to the session as well
        $this->set('dataSource', $this->_params['dataSource'] );
        $this->set('skipColumnHeader', CRM_Utils_Array::value( 'skipColumnHeader', $this->_params ) );
        // This is doing the same thing as $this->set, why so inconsistent?
        // $session = CRM_Core_Session::singleton();
        // $session->set('dateTypes', $dateFormats);
        $this->set('dateTypes', $dateFormats);


        // Get the PEAR::DB_MYSQL object
        $dao = new CRM_Core_DAO();
        $db = $dao->getDatabaseConnection();


        // TODO: We'll want to modify the interface to allow specification of a job name
        // It must be unique since it makes up the table name so use uniqid(rand(),true) for now
        $import_job_name = md5(uniqid(rand(),true));
        $import_table_name = $this->_params['import_table_name'] = "civicrm_import_job_$import_job_name";


        // Read the input file into the new import_table_name table now.
        // TODO: Fix up the SQL::postProcess
        require_once $this->_dataSourceClassFile;
        eval( "$this->_dataSource::postProcess( \$this->_params, \$db );" );


        // Alter the import data table to have a primary key and a set of status fields
        $fieldNames = $this->_prepareImportTable( $db, $import_table_name );


        // Run the parser on the basis of what little information we've collected so far.
        $mapper = array( );
        $parser = new CRM_Import_Parser_Contact( $mapper );
        $parser->setMaxLinesToProcess( 100 );
        // NYSS 3750 - Add a dedupe argument to the parser run method
        // $parser->run( $importTableName, $mapper,
        //               CRM_Import_Parser::MODE_MAPFIELD, $contactType,
        //               $fieldNames['pk'], $fieldNames['status'],
        //               DUPLICATE_SKIP, null, null, false, CRM_Import_Parser::DEFAULT_TIMEOUT, $contactSubType );*/
        $parser->run( $import_table_name,
                      $mapper,
                      CRM_Import_Parser::MODE_MAPFIELD,
                      $contactType,
                      $fieldNames['pk'],
                      $fieldNames['status'],
                      DUPLICATE_SKIP,
                      null, null, false,
                      CRM_Import_Parser::DEFAULT_TIMEOUT,
                      $contactSubType,
                      $dedupe);


        // Add all the necessary variables to the session for the next form
        $parser->set( $this );
    }
    
    /**
     * Add a PK and status column to the import table so we can track our progress
     * Returns the name of the primary key and status columns
     *
     * @return array
     * @access private
     */
    private function _prepareImportTable( $db, $importTableName ) {
        /* TODO: Add a check for an existing _status field;
         *  if it exists, create __status instead and return that
         */
        $statusFieldName = '_status';
        $primaryKeyName  = '_id';
        
        $this->set( 'primaryKeyName', $primaryKeyName );
        $this->set( 'statusFieldName', $statusFieldName );
        
        /* Make sure the PK is always last! We rely on this later.
         * Should probably stop doing that at some point, but it
         * would require moving to associative arrays rather than
         * relying on numerical order of the fields. This could in
         * turn complicate matters for some DataSources, which
         * would also not be good. Decisions, decisions...
         */
        $alterQuery = "ALTER TABLE $importTableName
                       ADD COLUMN $statusFieldName VARCHAR(32)
                            DEFAULT 'NEW' NOT NULL,
                       ADD COLUMN ${statusFieldName}Msg TEXT,
                       ADD COLUMN $primaryKeyName INT PRIMARY KEY NOT NULL
                               AUTO_INCREMENT";
        $db->query( $alterQuery );
        
        return array('status' => $statusFieldName, 'pk' => $primaryKeyName);
    }
    
    /**
     * Return a descriptive name for the page, used in wizard header
     *
     *
     * @return string
     * @access public
     */
    public function getTitle( ) {
        return ts('Choose Data Source');
    }
    
}
