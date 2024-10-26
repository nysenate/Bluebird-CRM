<?php

/**
 * Filters API Helper.
 *
 * A Collection of helpers for API Filters.
 */
class CRM_Civicase_APIHelpers_Filters {

  /**
   * If a filter is not in array format it defaults to a equal filter.
   *
   * @param mixed $filter
   *   The non normalized filter. Either a value, or a proper filter.
   *
   * @return array
   *   The normalized filter.
   */
  public static function normalize($filter) {
    if (!is_array($filter)) {
      return ['=' => $filter];
    }

    return $filter;
  }

}
