<?php

namespace Civi\Api4\Action\ContactLayout;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

/**
 * Fetch available blocks for the contact summary screen.
 */
class GetBlocks extends AbstractAction {

  public function _run(Result $result) {
    $blocks = \CRM_Contactlayout_BAO_ContactLayout::getAllBlocks();
    // Transform to non-associative arrays
    foreach ($blocks as &$group) {
      $group['blocks'] = array_values($group['blocks']);
      $result[] = $group;
    }
  }

}
