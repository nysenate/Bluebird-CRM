<?php

/**
 * Class CRM_Civicase_FileCategory
 *
 * FIXME: Find a better home for this. Probably in core.
 */
class CRM_Civicase_FileCategory {

  /**
   * Get a list of general file categories.
   *
   * @return array
   */
  public static function getCategoryLabels() {
    return array(
      'archive' => array(
        'text' => ts('Archive'),
        'icon' => 'fa-file-archive-o',
      ),
      'doc' => array(
        'text' => ts('Document'),
        'icon' => 'fa-file-text-o',
      ),
      'media' => array(
        'text' => ts('Media'),
        'icon' => 'fa-file-image-o',
      ),
      'present' => array(
        'text' => ts('Presentation'),
        'icon' => 'fa-file-powerpoint-o',
      ),
      'sheet' => array(
        'text' => ts('Spreadsheet'),
        'icon' => 'fa-file-excel-o',
      ),
      'other' => array(
        'text' => ts('Other'),
        'icon' => 'fa-file-o',
      ),
    );
  }

  /**
   * Get list of categories, labels & icons.
   *
   * Return format is suitable for a select2 input.
   *
   * @return array
   */
  public static function getCategories() {
    $cats = array();
    foreach (self::getCategoryLabels() as $v => $l) {
      $cats[] = array('id' => $v) + $l;
    }
    return $cats;
  }

  /**
   * Render a SQL expression which filters by category.
   *
   * @param string $field
   *   The SQL field to filter on. This should could contain a MIME type.
   *   Ex: 'f.mime_type'.
   * @param array $categories
   *   List of category names
   *   Ex: array('doc').
   *   Ex: array('doc','other').
   * @return string
   *   Ex: f.mime_type REGEX '^(application/pdf|text/html|application/msword)'
   *   Ex: f.mime_type NOT REGEX '^(application/vnd.ms-excel|^application/vnd.ms-powerpoint)'
   */
  public static function createSqlFilter($field, $categories) {
    $prefixes = self::getPrefixes();
    $allCategories = array_keys(self::getCategoryLabels());
    sort($categories);
    sort($allCategories);

    if ($categories === $allCategories) {
      return '1';
    }
    elseif (empty($categories)) {
      return '0';
    }

    if (in_array('other', $categories)) {
      $verb = 'NOT REGEXP';
      $unionCats = array_diff(array_keys($prefixes), $categories);
    }
    else {
      $verb = 'REGEXP';
      $unionCats = $categories;
    }

    $unionCats = array_diff($unionCats, array('other'));
    $union = array();
    foreach ($unionCats as $unionCat) {
      $union = array_unique(array_merge($prefixes[$unionCat], $union));
    }
    sort($union);

    $result = $field . ' ' . $verb . ' "^('  . CRM_Core_DAO::escapeString(implode('|', $union)) . ')"';
    return $result;
  }

  /**
   * Get a list of MySQL regular expressions.
   *
   * @return array
   *   Array(string $category => array $regexes).
   *   Ex: array('doc' => ['application/msword']).
   */
  private static function getPrefixes() {
    return array(
      'archive' => array(
        'application/gzip',
        'application/zip',
      ),
      'media' => array(
        'image/',
        'application/vnd.oasis.opendocument.graphics',
        'application/vnd.oasis.opendocument.image',
        'audio/',
        'video/',
      ),
      'doc' => array(
        'application/pdf',
        'application/msword',
        'application/rtf',
        'application/vnd.ms-word',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.openxmlformats-officedocument.wordprocessingml',
        'text/richtext',
        'text/plain',
        'text/html',
      ),
      'present' => array(
        'application/vnd.ms-powerpoint',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.openxmlformats-officedocument.presentationml',
      ),
      'sheet' => array(
        'application/vnd.ms-excel',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.openxmlformats-officedocument.spreadsheetml',
      ),
    );
  }

}
