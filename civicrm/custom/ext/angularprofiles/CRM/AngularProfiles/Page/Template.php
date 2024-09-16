<?php

use CRM_AngularProfiles_ExtensionUtil as E;

class CRM_AngularProfiles_Page_Template extends CRM_Core_Page {
  public function run() {
    CRM_Core_Region::instance('page-header')->add(array(
      'template' => 'CRM/UF/Page/ProfileTemplates.tpl',
    ));
    parent::run();
  }

  public static function getAngularSettings(): array {
    return [
      'backboneInitUrl' => E::url('js/initBackbone.js'),
    ];
  }

}
