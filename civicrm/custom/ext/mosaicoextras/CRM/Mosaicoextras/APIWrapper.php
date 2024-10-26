<?php

use Civi\API\Event\AuthorizedTrait;

class CRM_Mosaicoextras_APIWrapper {

  use AuthorizedTrait;

  public static function authorize($event) {
    $request = $event->getApiRequestSig();
    if ($request !== '4.mosaicotemplate.get') {
      return;
    }
    if (\Drupal::currentUser()->hasPermission('delete Mosaico templates')) {
      $event->setAuthorized(TRUE);
    }
  }

}
