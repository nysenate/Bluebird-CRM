<?php

require_once('CRM/Report/Form.php');

/**
 * @file
 */

/**
 *
 * $Id$
 */
class CRM_Iats_Form_Report_Journal extends CRM_Report_Form {

  // Allow custom fieldsets for contacts and contributions
  protected $_customGroupExtends = [
    'Contact',
    'Individual',
    'Contribution',
  ];
  /* static private $processors = array();
  static private $version = array();
  static private $financial_types = array();
  static private $prefixes = array(); */
  static private $contributionStatus = array(); 
  static private $transaction_types = array(
    'VISA' => 'Visa',
    'ACHEFT' => 'ACH/EFT',
    'UNKNOW' => 'Uknown',
    'MC' => 'MasterCard',
    'AMX' => 'AMEX',
    'DSC' => 'Discover',
  );

  /**
   *
   */
  public function __construct() {
    self::$contributionStatus = CRM_Contribute_BAO_Contribution::buildOptions('contribution_status_id');
    $this->_columns = array(
      'civicrm_iats_journal' =>
        array(
          'fields' =>
            array(
              'id' => array('title' => 'CiviCRM Journal Id', 'default' => TRUE),
              'iats_id' => array('title' => 'iATS Journal Id', 'default' => TRUE),
              'tnid' => array('title' => 'Transaction ID', 'default' => TRUE),
              'tntyp' => array('title' => 'Transaction type', 'default' => TRUE),
              'agt' => array('title' => 'Client/Agent code', 'default' => TRUE),
              'cstc' => array('title' => 'Customer code', 'default' => TRUE),
              'inv' => array('title' => 'Invoice Reference', 'default' => TRUE),
              'dtm' => array('title' => 'Transaction date', 'default' => TRUE),
              'amt' => array('title' => 'Amount', 'default' => TRUE),
              'rst' => array('title' => 'Result string', 'default' => TRUE),
              'dtm' => array('title' => 'Transaction Date Time', 'default' => TRUE),
              'status_id' => array('title' => 'Payment Status', 'default' => TRUE),
            ),
          'order_bys' => 
            array(
              'id' => array('title' => ts('CiviCRM Journal Id'), 'default' => TRUE, 'default_order' => 'DESC'),
              'iats_id' => array('title' => ts('iATS Journal Id')),
              'dtm' => array('title' => ts('Transaction Date Time')),
            ),
          'filters' =>
             array(
               'dtm' => array(
                 'title' => 'Transaction date', 
                 'operatorType' => CRM_Report_Form::OP_DATE,
                 'type' => CRM_Utils_Type::T_DATE,
               ),
               'inv' => array(
                 'title' => 'Invoice Reference', 
                 'type' => CRM_Utils_Type::T_STRING,
               ),
               'amt' => array(
                 'title' => 'Amount', 
                 'operatorType' => CRM_Report_Form::OP_FLOAT,
                 'type' => CRM_Utils_Type::T_FLOAT
               ),
               'tntyp' => array(
                 'title' => 'Type', 
                 'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                 'options' => self::$transaction_types,
                 'type' => CRM_Utils_Type::T_STRING,
               ),
               'agt' => array(
                 'title' => 'Client/Agent code',
                 'type' => CRM_Utils_Type::T_STRING,
               ),
               'rst' => array(
                 'title' => 'Result string',
                 'type' => CRM_Utils_Type::T_STRING,
               ),
               'status_id' => array(
                 'title' => ts('Payment Status'),
                 'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                 'options' => self::$contributionStatus,
                 'type' => CRM_Utils_Type::T_INT,
               ),
             ),
	),
      'civicrm_contribution' => [
        'dao' => 'CRM_Contribute_DAO_Contribution',
        'fields' => [
          'contribution_id' => [
            'name' => 'id',
            'no_display' => TRUE,
            'required' => TRUE,
          ],
          'list_contri_id' => [
            'name' => 'id',
            'title' => ts('Contribution ID'),
          ],
          'financial_type_id' => [
            'title' => ts('Financial Type'),
          ],
          'contribution_status_id' => [
            'title' => ts('Contribution Status'),
          ],
          'contribution_page_id' => [
            'title' => ts('Contribution Page'),
          ],
          'source' => [
            'title' => ts('Source'),
          ],
          'payment_instrument_id' => [
            'title' => ts('Payment Type'),
          ],
          'currency' => [
            'required' => TRUE,
            'no_display' => TRUE,
          ],
          'trxn_id' => NULL,
          'receive_date' => NULL,
          'receipt_date' => NULL,
	  'thankyou_date' => NULL,
          'total_amount' => [
            'title' => ts('Amount'),
          ],
          'non_deductible_amount' => NULL,
          'fee_amount' => NULL,
          'net_amount' => NULL,
          'cancel_date' => [
            'title' => ts('Cancelled / Refunded Date'),
            'name' => 'contribution_cancel_date',
          ],
          'cancel_reason' => [
            'title' => ts('Cancellation / Refund Reason'),
          ],
        ],
      ],
    );
    $this->_columns = array_merge($this->_columns, $this->getColumns('Contact', [
      'order_bys_defaults' => ['sort_name' => 'ASC '],
      'fields_defaults' => ['sort_name'],
      'fields_excluded' => ['id'],
      'fields_required' => ['id'],
      'no_field_disambiguation' => TRUE,
    ]));
    parent::__construct();
  }

  /**
   *
   */
  public function getTemplateName() {
    return 'CRM/Report/Form.tpl';
  }

  /**
   *
   */
  public function from() {
    $this->_from = "FROM civicrm_iats_journal  {$this->_aliases['civicrm_iats_journal']}";
    if ( $this->isTableSelected('civicrm_contribution') 
      || $this->isTableSelected('civicrm_contact') 
    ) {
      $this->_from .= " LEFT JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']}
           ON {$this->_aliases['civicrm_contribution']}.invoice_id = {$this->_aliases['civicrm_iats_journal']}.inv \n";
    }
    if ( $this->isTableSelected('civicrm_contact') ) {
      $this->_from .= " LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
           ON {$this->_aliases['civicrm_contribution']}.contact_id = {$this->_aliases['civicrm_contact']}.id \n";
    }
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
    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        !empty($rows[$rowNum]['civicrm_contact_sort_name']) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact Summary for this Contact.");
      }
    }
  }

}
