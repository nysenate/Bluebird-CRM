<?php
declare(strict_types = 1);

/**
 * Field-level handler methods for the NYSS Print Import Legacy extension.
 *
 * These methods convert individual values from one form to another without
 * modifying a record array directly. See Preparer for record-level mutations.
 *
 * Handlers accept a $context array that may contain both field-level defaults
 * (from Definitions) and runtime values such as the current import mode.
 *
 * Refactored from modules/nyss_io/nyss_io.module.
 */
class CRM_NYSS_PrintImport_Handler {

  /**
   * Convert a string to proper/title case with special-word handling.
   *
   * @param string $string
   * @param array $context
   *   - skipMixed: if TRUE, return unchanged when string already contains lowercase letters.
   *   - skipSpecial: if TRUE, skip the forced-word list pass and return after basic ucwords().
   *   - mode: import mode (MODE_INSERT or MODE_UPDATE), available but not used here.
   *
   * @return string
   */
  public static function convertProperCase(string $string, array $context = []): string {
      $skipMixed   = $context['skipMixed']   ?? FALSE;
      $skipSpecial = $context['skipSpecial'] ?? FALSE;

      //if mixed case, don't do anything
      if ($skipMixed && preg_match('/[a-z]/', $string)) return $string;

      $string = CRM_Utils_String::stripSpaces(ucwords(strtolower($string)));

      //if we skip special words processing, return now
      if ($skipSpecial) return $string;

      // list of words we want to force
      $forceWords = [
          'of', 'the', 'and', 'an', 'or', 'nor', 'but', 'is', 'if', 'then',
          'else', 'when', 'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to',
          'into', 'with', 'II', 'IV', 'UK', 'VI', 'III', 'VII', 'PO', 'McDonald', 'McClelland', 'RR'
      ];

      $words = explode(' ', $string);

      foreach ($words as $word) {

          $replace = [];

          //trim any non-word chars and replace with nothing for easier matching
          $cleanWord = preg_replace("/[^\w]/", '', $word);
          //nyss_out('debug', "cleanWord: $cleanWord", true);
          if (!empty($cleanWord)) $replace = preg_grep("/\b{$cleanWord}\b/i", $forceWords);

          $replace = array_values($replace);
          //nyss_out('debug', $replace, true);
          if (isset($replace[0])) $word = str_replace($cleanWord,$replace[0],$word);

          //if number followed by letter, uppercase
          if (preg_match("/^\d[a-z]/", $cleanWord)) {
              $word = strtoupper(($word));
          }

          $fixedWords[] = $word;
      }

      //nyss_out('debug', $fixedWords, true);
      $string = implode(' ',$fixedWords);

      return $string;
  }

  /**
   * Convert a string to lowercase.
   *
   * @param string $string
   * @param array $context
   *   - mode: import mode (MODE_INSERT or MODE_UPDATE), available but not used here.
   *
   * @return string
   */
  public static function convertLowerCase(string $string, array $context = []): string {
    return strtolower($string);
  }


}
