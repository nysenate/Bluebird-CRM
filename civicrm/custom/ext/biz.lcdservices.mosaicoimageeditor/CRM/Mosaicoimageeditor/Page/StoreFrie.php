<?php
use CRM_Mosaicoimageeditor_ExtensionUtil as E;

class CRM_Mosaicoimageeditor_Page_StoreFrie extends CRM_Core_Page {

  public function run() {
    //Civi::log()->debug(__FUNCTION__, ['_REQUEST' => $_REQUEST]);

    $image = CRM_Utils_Request::retrieve('image', 'String');
    $url = CRM_Utils_Request::retrieve('url', 'String');
    $result = [];

    try {
      $result = civicrm_api3('Nyss', 'storefrie', [
        'image' => $image,
        'url' => $url,
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {}
    //Civi::log()->debug(__FUNCTION__, ['$result' => $result]);

    CRM_Utils_JSON::output(CRM_Utils_Array::value('values', $result));
  }
}
