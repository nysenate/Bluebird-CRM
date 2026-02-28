<?php

/**
 * This class provides the functionality to export large data sets for print production.
 * Moved to extension from custom/php/CRM/Contact/Form/Task/ExportPrintProductionResult.php
 */
class CRM_NYSS_PrintExport_Form_Task_PrintExportResult extends CRM_Contact_Form_Task
{
  /**
   * @var string
   */
  protected $_name;

  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  function buildQuickForm( )
  {
    CRM_Utils_System::setTitle( ts('Print Production Export Results') );

    $status = $this->get('status');
    $statusOutput = $this->get('statusOutput');

    $this->assign('status', $status);
    $this->assign('statusOutput', $statusOutput);

    $this->addButtons(
      array(
        array(
          'type' => 'next',
          'name' => ts('Return to Search Results'),
          'isDefault' => TRUE,
        ),
      )
    );
  }// buildQuickForm()

}
