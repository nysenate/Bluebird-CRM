<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
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
namespace Civi\FlexMailer\Listener;

use Civi\FlexMailer\Event\ComposeBatchEvent;
use Civi\FlexMailer\FlexMailerTask;

class ToHeader extends BaseListener {

  /**
   * Inject the "To:" header.
   *
   * @param \Civi\FlexMailer\Event\ComposeBatchEvent $e
   */
  public function onCompose(ComposeBatchEvent $e) {
    if (!$this->isActive()) {
      return;
    }

    $names = $this->getContactNames($e->getTasks());
    foreach ($e->getTasks() as $task) {
      /** @var FlexMailerTask $task */

      $task->setMailParam('toEmail', $task->getAddress());

      if (isset($names[$task->getContactId()])) {
        $task->setMailParam('toName', $names[$task->getContactId()]);
      }
      else {
        $task->setMailParam('toName', '');
      }
    }
  }

  /**
   * Lookup contact names as a batch.
   *
   * @param array <FlexMailerTask> $tasks
   * @return array
   *   Array(int $contactId => string $displayName).
   */
  protected function getContactNames($tasks) {
    $ids = array();
    foreach ($tasks as $task) {
      /** @var FlexMailerTask $task */
      $ids[$task->getContactId()] = $task->getContactId();
    }

    if (!$ids) {
      return array();
    }

    $idString = implode(',', array_filter($ids, 'is_numeric'));

    $query = \CRM_Core_DAO::executeQuery(
      "SELECT id, display_name FROM civicrm_contact WHERE id in ($idString)");
    $names = array();
    while ($query->fetch()) {
      $names[$query->id] = $query->display_name;
    }
    return $names;
  }

}
