<?php
use CRM_Backup_ExtensionUtil as E;

class CRM_Backup_Page_BackupListing extends CRM_Core_Page {

  public function run() {
    CRM_Core_Resources::singleton()->addStyleFile(E::LONG_NAME, 'css/BackupListing.css');

    $config = CRM_Backup_BAO::getConfig();
    $listing = CRM_Backup_BAO::getBackups($config['bkupdir'], $config['bbcfg']);
    //Civi::log()->debug(__FUNCTION__, ['$listing' => $listing]);

    $this->assign('listing', $listing);
    $this->assign('btn_create', CRM_Utils_System::url('civicrm/backup/create', 'reset=1'));

    parent::run();
  }

}
