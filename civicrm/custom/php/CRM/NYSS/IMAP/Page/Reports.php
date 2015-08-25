<?php

require_once 'CRM/Core/Page.php';
require_once 'CRM/Core/Error.php';
require_once 'CRM/Core/Form.php';

class CRM_NYSS_IMAP_Page_Reports extends CRM_Core_Page
{
  function run()
  {
    //Build the filter form
    $form = new CRM_Core_Form();
    $form->addElement('text', 'first_name', 'First Name');
    $form->addElement('text', 'last_name', 'Last Name');
    $form->addElement('text', 'city', 'City');
    $form->addElement('text', 'phone', 'Phone Number');
    $form->addElement('text', 'street_address', 'Street Address');

    $this->assign('form', $form->toSmarty());

    parent::run();
  } // run()
}
