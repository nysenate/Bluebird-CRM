<?php

namespace Civi\Api4\Action\ContactLayout;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

/**
 * Fetch available tabs for the contact summary screen.
 */
class GetTabs extends AbstractAction {

  public function _run(Result $result) {
    $result->exchangeArray(\CRM_Contactlayout_BAO_ContactLayout::getAllTabs());
  }

}
