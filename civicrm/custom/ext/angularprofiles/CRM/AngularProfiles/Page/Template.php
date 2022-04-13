<?php

require_once 'CRM/Core/Page.php';

class CRM_AngularProfiles_Page_Template extends CRM_Core_Page {
  function run() {
    CRM_Core_Region::instance('page-header')->add(array(
      'template' => 'CRM/UF/Page/ProfileTemplates.tpl',
    ));
    parent::run();
  }
}
