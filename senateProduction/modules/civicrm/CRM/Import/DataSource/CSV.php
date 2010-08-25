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

require_once 'CRM/Import/DataSource.php';

class CRM_Import_DataSource_CSV extends CRM_Import_DataSource
{
    const NUM_ROWS_TO_INSERT = 100;

    function getInfo()
    {
        return array('title' => ts('Comma-Separated Values (CSV)'));
    }

    function preProcess(&$form)
    {
    }

    function buildQuickForm(&$form)
    {
        $form->add('hidden', 'hidden_dataSource', 'CRM_Import_DataSource_CSV');

        $config = CRM_Core_Config::singleton();

        // FIXME: why do we limit the file size to 8 MiB if it's larger in config?
        $uploadFileSize = $config->maxImportFileSize >= 8388608 ? 8388608 : $config->maxImportFileSize;
        $uploadSize = round(($uploadFileSize / (1024*1024)), 2);
        $form->assign('uploadSize', $uploadSize);
        $form->add('file', 'uploadFile', ts('Import Data File'), 'size=30 maxlength=60', true);

        $form->setMaxFileSize($uploadFileSize);
        $form->addRule('uploadFile', ts('File size should be less than %1 MBytes (%2 bytes)', array(1 => $uploadSize, 2 => $uploadFileSize)), 'maxfilesize', $uploadFileSize);
        $form->addRule('uploadFile', ts('Input file must be in CSV format'), 'utf8File');
        $form->addRule('uploadFile', ts('A valid file must be uploaded.'), 'uploadedfile');

        $form->addElement('checkbox', 'skipColumnHeader', ts('First row contains column headers'));
    }

    function postProcess(&$params, &$db)
    {
        $file = $params['uploadFile']['name'];
        
        $result = self::_CsvToTable( $db, $file, $params['skipColumnHeader'],
                                     CRM_Utils_Array::value( 'import_table_name', $params ) );
        
        $this->set('originalColHeader', CRM_Utils_Array::value( 'original_col_header', $result ) );
        
        $table = $result['import_table_name'];
        require_once 'CRM/Import/ImportJob.php';
        $importJob = new CRM_Import_ImportJob($table);
        $this->set('importTableName', $importJob->getTableName());
    }

    /**
     * Create a table that matches the CSV file and populate it with the file's contents
     *
     * @param object $db     handle to the database connection
     * @param string $file   file name to load
     * @param bool   $headers  whether the first row contains headers
     * @param string $table  Name of table from which data imported.
     *
     * @return string  name of the created table
     */
    private static function _CsvToTable(&$db, $file, $headers = false, $table = null )
    {
        $result = array( );
        $fd = fopen($file, 'r');
        if (!$fd) CRM_Core_Error::fatal("Could not read $file");
        
        $config = CRM_Core_Config::singleton();
        $firstrow = fgetcsv($fd, 0, $config->fieldSeparator);
        
        // create the column names from the CSV header or as col_0, col_1, etc.
        if ($headers) {
            //need to get original headers.
            $result['original_col_header'] = $firstrow;
            
            $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
            $columns = array_map($strtolower, $firstrow);
            $columns = str_replace(' ', '_', $columns);
            $columns = preg_replace('/[^a-z_]/', '', $columns);
            
            // need to take care of null as well as duplicate col names.
            $duplicateColName = false;
            if ( count( $columns ) != count( array_unique( $columns ) ) ) {
                $duplicateColName = true;
            }
            
            if ( in_array( '', $columns ) || $duplicateColName ) {
                foreach ( $columns as $colKey => &$colName ) {
                    if ( !$colName ) {
                        $colName = "col_$colKey";
                    } else if ( $duplicateColName ) {
                        $colName .= "_$colKey";
                    }
                }
            }

            // CRM-4881: we need to quote column names, as they may be MySQL reserved words
            foreach ($columns as &$column) $column = "`$column`";

        } else {
            $columns = array();
            foreach ($firstrow as $i => $_) $columns[] = "col_$i";
        }
        
        // FIXME: we should regen this table's name if it exists rather than drop it
        if ( !$table ) {
            $table = 'civicrm_import_job_' . md5(uniqid(rand(), true));
        }
        
        $db->query("DROP TABLE IF EXISTS $table");

        $numColumns = count( $columns );
        $create = "CREATE TABLE $table (" . implode(' text, ', $columns) . " text) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
        $db->query($create);

        // the proper approach, but some MySQL installs do not have this enabled
        // $load = "LOAD DATA LOCAL INFILE '$file' INTO TABLE $table FIELDS TERMINATED BY '$config->fieldSeparator' OPTIONALLY ENCLOSED BY '\"'";
        // if ($headers) $load .= ' IGNORE 1 LINES';
        // $db->query($load);

        // parse the CSV line by line and build one big INSERT (while MySQL-escaping the CSV contents)
        if ( ! $headers ) {
            rewind($fd);
        }

        $sql = null;
        $first = true;
        $count = 0;
        while ($row = fgetcsv($fd, 0, $config->fieldSeparator)) {
            // skip rows that dont match column count, else we get a sql error
            if ( count( $row ) != $numColumns ) {
                continue;
            }

            if ( ! $first ) {
                $sql .= ', ';
            }

            $first = false;
            $row = array_map('civicrm_mysql_real_escape_string', $row);
            $sql .= "('" . implode("', '", $row) . "')";
            $count++;

            if ( $count >= self::NUM_ROWS_TO_INSERT && ! empty( $sql ) ) {
                $sql = "INSERT IGNORE INTO $table VALUES $sql";
                $db->query($sql);

                $sql   = null;
                $first = true;
                $count = 0;
            }
        }

        if ( ! empty( $sql ) ) {
            $sql = "INSERT IGNORE INTO $table VALUES $sql";
            $db->query($sql);
        }

        fclose($fd);
        
        //get the import tmp table name.
        $result['import_table_name'] = $table;
        
        return $result;
    }
}

function civicrm_mysql_real_escape_string( $string ) {
    static $dao = null;
    if ( ! $dao ) {
        $dao = new CRM_Core_DAO( );
    }
    return $dao->escape( $string );
}
