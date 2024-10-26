<?php

/**
 * Sample URL:
//http://sd99.crmui.nysenate.gov/data/sd99/pubfiles/images/uploads/sunset_header_image_copy_9b190d89aa7ae1bec257666edf7767a8.jpg
 * https://stackoverflow.com/questions/13198131/how-to-save-an-html5-canvas-as-an-image-on-a-server
 * https://stackoverflow.com/questions/1532993/i-have-a-base64-encoded-png-how-do-i-write-the-image-to-a-file-in-php
 */

/**
 * Nyss.storefrie API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_nyss_Storefrie_spec(&$spec) {
  $spec['image'] = [
    'api.required' => 1,
    'title' => ts('Image in Base64 Format'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['url'] = [
    'api.required' => 1,
    'title' => ts('URL for Existing Image'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * Nyss.storefrie API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nyss_Storefrie($params) {
  $image = $params['image'];
  $url = $params['url'];

  $newUrl = _storefrie_storeImage($image, $url);

  if ($newUrl) {
    return civicrm_api3_create_success($newUrl, $params, 'Nyss', 'storefrie');
  }
  else {
    throw new API_Exception('Could not store image.');
  }
}

function _storefrie_storeImage($image, $url) {
  //get file name; add random number before hash
  $fileParts = explode('/', $url);
  $fileName = end($fileParts);
  $fileNameParts = explode('_', $fileName);
  $hashSuffix = end($fileNameParts);
  $rand = rand(10000,99999);
  $newFileName = str_replace($hashSuffix, $rand, $fileName).'_'.$hashSuffix;

  /*Civi::log()->debug(__FUNCTION__, [
    '$fileParts' => $fileParts,
    '$fileName' => $fileName,
    '$fileNameParts' => $fileNameParts,
    '$hashSuffix' => $hashSuffix,
    '$newFileName' => $newFileName,
  ]);*/

  //get path for image folder
  $bbconfig = get_bluebird_instance_config();
  $path = $bbconfig['data.rootdir'].'/'.$bbconfig['data_dirname'].'/pubfiles/images/uploads/';
  $filePath = $path.$newFileName;

  //strip pre-junk
  list(, $image) = explode(',', $image);

  //store image
  file_put_contents($filePath, base64_decode($image));
  $newUrl = str_replace($fileName, $newFileName, $url);

  /*Civi::log()->debug(__FUNCTION__, [
    '$bbconfig' => $bbconfig,
    '$filePath' => $filePath,
    '$newUrl' => $newUrl,
  ]);*/

  return $newUrl;
}

