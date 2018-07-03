<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2015                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

namespace Civi\Api4\Action;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Service\Spec\SpecGatherer;
use Civi\Api4\Generic\Result;
use Civi\Api4\Service\Spec\SpecFormatter;

/**
 * Get fields for an entity
 *
 * @method $this setIncludeCustom(bool $value)
 * @method bool getIncludeCustom()
 * @method $this setAction(string $value)
 */
class GetFields extends AbstractAction {

  /**
   * Override default to allow open access
   * @inheritDoc
   */
  protected $checkPermissions = FALSE;

  /**
   * @var bool
   */
  protected $includeCustom = TRUE;

  /**
   * @var bool
   */
  protected $getOptions = FALSE;

  /**
   * @var string
   */
  protected $action = 'get';

  public function _run(Result $result) {
    /** @var SpecGatherer $gatherer */
    $gatherer = \Civi::container()->get('spec_gatherer');
    $spec = $gatherer->getSpec($this->getEntity(), $this->getAction(), $this->includeCustom);
    $specArray = SpecFormatter::specToArray($spec, $this->getOptions);
    // Fixme - $this->action ought to already be set. Might be a name conflict upstream causing it to be nullified?
    $result->action = 'getFields';
    $result->exchangeArray(array_values($specArray['fields']));
  }

  /**
   * @return string
   */
  public function getAction() {
    return $this->action;
  }

}
