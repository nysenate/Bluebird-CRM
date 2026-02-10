<?php
declare(strict_types = 1);
use CRM_NYSS_Dedupe_ExtensionUtil as E;
use Civi\Core\Service\AutoService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @todo make address_abbreviations a civicrm entity. Then, we won't need this. It will be queryable via API
 * @service nyssdedupe.abbreviations
 */
class CRM_NYSS_Dedupe_Service_Abbreviations extends AutoService implements EventSubscriberInterface {

    private static ?array $abbreviations = NULL;

    public static function getAddressAbbreviations() : array {

        if (!is_array(self::$abbreviations) || !count(self::$abbreviations)) {
            self::$abbreviations = [];
            $sql = "SELECT raw_value, normalized
            FROM address_abbreviations";

            $result = CRM_Core_DAO::executeQuery($sql);

            while ($result->fetch()) {
                self::$abbreviations[$result->raw_value] = $result->normalized  ;
                // Each column becomes a property on $dao
                //$id   = $dao->id;
                //$name = $dao->display_name;
            }

            //self::$abbreviations = CRM_Core_DAO::executeQuery($sql)->fetchAll();
        }

        return self::$abbreviations;
    }
  // TIP: Many services implement `EventSubscriberInterface`. However, this can be omitted if you don't need it.

  public static function getSubscribedEvents(): array {
    return [
      // '&hook_civicrm_alterContent' => ['onAlterContent', 0],
      // '&hook_civicrm_postCommit::Contribution' => ['onContribute', 0],
      // TIP: For hooks based on GenericHookEvent, the "&" will expand arguments.
    ];
  }

  // /**
  //  * @see \CRM_Utils_Hook::alterContent()
  //  */
  // public function onAlterContent(&$content, $context, $tplName, &$object) { ... }

  // /**
  //  * @see \CRM_Utils_Hook::postCommit()
  //  */
  // public function onContribute($op, $objectName, $objectId, $objectRef = NULL) { ... }

}
