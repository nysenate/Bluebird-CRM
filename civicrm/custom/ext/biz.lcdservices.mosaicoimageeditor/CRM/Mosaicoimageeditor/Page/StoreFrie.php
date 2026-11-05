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
    catch (CRM_Core_Exception $e) {}
    //Civi::log()->debug(__FUNCTION__, ['$result' => $result]);

    // FileRobotPlugin.js expects a string. It passes the parsed response straight to Mosaico's src().
    // So, our response needs to send a string value.
    CRM_Utils_System::sendResponse(new \GuzzleHttp\Psr7\Response(
      200,
      ['Content-Type' => 'application/json'],
      json_encode($result['values'] ?? NULL, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    ));
  }
}
