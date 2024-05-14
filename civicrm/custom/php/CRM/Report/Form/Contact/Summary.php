<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */
class CRM_Report_Form_Contact_Summary extends CRM_Report_Form {

  public $_summary;

  protected $_emailField = FALSE;

  protected $_phoneField = FALSE;

  protected $_customGroupExtends = [
    'Contact',
    'Individual',
    'Household',
    'Organization',
  ];

  public $_drilldownReport = ['contact/detail' => 'Link to Detail Report'];

  /**
   * This report has not been optimised for group filtering.
   *
   * The functionality for group filtering has been improved but not
   * all reports have been adjusted to take care of it. This report has not
   * and will run an inefficient query until fixed.
   *
   * @var bool
   * @see https://issues.civicrm.org/jira/browse/CRM-19170
   */
  protected $groupFilterNotOptimised = TRUE;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->_autoIncludeIndexedFieldsAsOrderBys = 1;
    $this->_columns = [
      'civicrm_contact' => [
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array_merge(
          $this->getBasicContactFields(),
          [
            'modified_date' => [
              'title' => ts('Modified Date'),
              'default' => FALSE,
            ],
            //NYSS 9515
            'created_date' => [
              'title' => ts('Created Date'),
            ],
          ]
        ),
        'filters' => $this->getBasicContactFilters() +
          //NYSS 9515
          [
            'created_date' => [
              'title' => ts('Created Date'),
              'default' => 'this.month',
              'operatorType' => CRM_Report_Form::OP_DATE,
              'type' => CRM_Utils_Type::T_DATE,
            ],
          ],
        'grouping' => 'contact-fields',
        'order_bys' => [
          'sort_name' => [
            //NYSS 4236 alter defaults
            'title' => ts('Contact Name'),
            'default' => '1',
            'default_weight' => '0',
            'default_order' => 'ASC',
          ],
          'first_name' => [
            'name' => 'first_name',
            'title' => ts('First Name'),
          ],
          'gender_id' => [
            'name' => 'gender_id',
            'title' => ts('Gender'),
          ],
          'birth_date' => [
            'name' => 'birth_date',
            'title' => ts('Birth Date'),
          ],
          'contact_type' => [
            'title' => ts('Contact Type'),
          ],
          'contact_sub_type' => [
            'title' => ts('Contact Subtype'),
          ],
          //NYSS 9515
          'created_date' => [
            'title' => ts('Created Date'),
          ],
        ],
      ],
      'civicrm_email' => [
        'dao' => 'CRM_Core_DAO_Email',
        'fields' => [
          'email' => [
            'title' => ts('Email'),
            'no_repeat' => TRUE,
          ],
        ],
        'grouping' => 'contact-fields',
        'order_bys' => [
          //NYSS
          /*'email' => [
            'title' => ts('Email'),
          ],*/
        ],
      ],
      'civicrm_phone' => [
        'dao' => 'CRM_Core_DAO_Phone',
        'fields' => [
          'phone' => NULL,
          'phone_ext' => [
            'title' => ts('Phone Extension'),
          ],
        ],
        'grouping' => 'contact-fields',
      ],
    ] + $this->getAddressColumns(['group_bys' => FALSE]);

    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    parent::__construct();
  }

  public function preProcess() {
    parent::preProcess();
  }

  //NYSS 5057 - remove some of the custom field order bys
  function buildForm( ) {
    parent::buildForm( );

    $elements   = $this->_elementIndex;
    $orderByEle = $elements['order_bys[1][column]'];
    $orderBys   =& $this->_elements[$orderByEle];

    $removeOrderBys = array(
      'custom_64', //privacy options note
      'custom_25', //DOS
      'custom_26', //EIN
      'custom_58', //Ethnicity
      'custom_62', //Other Ethnicity
      'custom_16', //Professional Accreditation
    );
    foreach ( $orderBys->_options as $k => $fld ) {
      if ( in_array( $fld['attr']['value'], $removeOrderBys ) ) {
        unset( $orderBys->_options[$k] );
      }
    }
    //CRM_Core_Error::debug_var('orderBys',$orderBys);
  }

  /**
   * @param $fields
   * @param $files
   * @param self $self
   *
   * @return array
   */
  public static function formRule($fields, $files, $self) {
    $errors = $grouping = [];
    return $errors;
  }

  public function from() {
    $this->_from = "
        FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom} ";
    $this->joinAddressFromContact();
    $this->joinPhoneFromContact();
    $this->joinEmailFromContact();
    $this->joinCountryFromAddress();
  }

  public function postProcess() {
    $this->beginPostProcess();
    $sql = $this->buildQuery(TRUE);
    $rows = [];
    $this->buildRows($sql, $rows);
    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  /**
   * Alter display of rows.
   *
   * Iterate through the rows retrieved via SQL and make changes for display purposes,
   * such as rendering contacts as links.
   *
   * @param array $rows
   *   Rows generated by SQL, with an array for each row.
   */
  public function alterDisplay(&$rows) {
    $entryFound = FALSE;

    foreach ($rows as $rowNum => $row) {
      // make count columns point to detail report
      // convert sort name to links
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        //NYSS 2059
        $url = CRM_Utils_System::url( 'civicrm/contact/view',
          'reset=1&cid='.$row['civicrm_contact_id'],
          $this->_absoluteUrl, $this->_id, $this->_drilldownReport
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts('View Contact Detail Report for this contact');
        $entryFound = TRUE;
      }

      // Handle ID to label conversion for contact fields
      $entryFound = $this->alterDisplayContactFields($row, $rows, $rowNum, 'contact/summary', 'View Contact Summary') ? TRUE : $entryFound;

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
    }
  }

}
