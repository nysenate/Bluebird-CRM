<?php
declare(strict_types = 1);
use CRM_NYSS_Dedupe_ExtensionUtil as E;
use Civi\Core\Service\AutoService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @note This is a duplication of an SQL functions BB_NORMALIZE_ADDR() and BB_ADDR_REPLACE() in sql/shadow_func.sql.
 * Normally, I would avoid duplicating code, but we saw a performance hit after switching from the
 * mysqludf_preg library to Mysql built-in REGEXP functions (on percona). For the sake of performance, I've
 * moved the address normalization into PHP when possible. If you change this logic here, you should probably also
 * change it in shadow_func.sql.
 *
 * @service nyssdedupe.normalizer
 */
class CRM_NYSS_Dedupe_Service_Normalizer extends AutoService implements EventSubscriberInterface {

    /** @var */
    private $abbr = NULL;
    /**
     * @inject nyssdedupe.abbreviations
     */
    public function __construct(CRM_NYSS_Dedupe_Service_Abbreviations $abbr) {
        $this->abbr = $abbr;
    }

    public function replace_abbrev(string $str): string {
        $abbreviations = $this->abbr->getAddressAbbreviations();
        //print_r($abbreviations);
        $tokens = explode(' ', $str);
        foreach ($tokens as &$t) {
            if (is_array($abbreviations) && array_key_exists($t, $abbreviations)) {
                $t = $abbreviations[$t];
            }
        }
        $address = implode(' ', $tokens);
        return $address;
    }
    public function normalize_addr(string $addr): string {

        if ($addr === '') {
            return $addr;
        }

        // Strip 4-byte supplementary characters and common BMP symbols/emoji
        $addr = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $addr);
        $addr = preg_replace('/[\x{2600}-\x{27BF}]/u', '', $addr);

        // Lowercase + trim
        $address = mb_strtolower(trim($addr), 'UTF-8');

        // Strip ordinals from street numbers: 1st → 1, 2nd → 2, etc.
        $address = preg_replace('/([0-9])(st|nd|rd|th)/i', '$1', $address);

        // Normalize spacing: 7B, 7-B, 7 B → 7 B
        $address = preg_replace('/^([0-9]+)-?(\W+)\s/u', '$1 $2 ', $address);

        // Replace punctuation
        // NOTE: apostrophes are removed, others become spaces
        $address = str_replace(
            [',', '.', '-', ';', ':', '#'],
            ' ',
            $address
        );
        $address = str_replace("'", '', $address);

        // Replace with standardized abbreviations
        $address = $this->replace_abbrev($address);

        // Some other adhoc changes we need to make
        $address = str_replace('apt',   '',  $address);
        $address = str_replace('floor', 'fl', $address);
        $address = str_replace('east',  'e',  $address);
        $address = str_replace('north', 'n',  $address);
        $address = str_replace('west',  'w',  $address);
        $address = str_replace('south', 's',  $address);

        // Normalize the spaces on the way out the door
        $address = preg_replace('/ +/', ' ', trim($address));

        return $address;
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
