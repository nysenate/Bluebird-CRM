<?php

/**
 * When Civi performs file searches, use Drupal's Solr integration.
 */
class DrupalSolrCiviAttachmentSearch implements CRM_Core_FileSearchInterface {
  /**
   * @param array $query any of the following:
   *  - text: string, plain text to search for
   *  - parent_table: string|array - entity to which file is directly attached
   *  - parent_id: int|array - entity to which file is directly attached
   *  - xparent_table: string|array - business-entity to which file is attached (directly or indirectly)
   *  - xparent_id: int|array - business-entity to which file is attached (directly or indirectly)
   * @param int $limit
   * @param int $offset
   * @return array each item has keys:
   *  - file_id: int
   *  - parent_table: string - entity to which file is directly attached
   *  - parent_id: int - entity to which file is directly attached
   *  - xparent_table: string - business-entity to which file is attached (directly or indirectly)
   *  - xparent_id: int - business-entity to which file is attached (directly or indirectly)
   */
  public function search($query, $limit = self::DEFAULT_SEARCH_LIMIT, $offset = self::DEFAULT_SEARCH_OFFSET) {
    $params = array();
    if ($limit) {
      $params['rows'] = $limit;
    }
    if ($offset) {
      $params['start'] = $offset;
    }

    $q = "entity_type:civiFile AND content:({$query['text']})";
    foreach (array('parent_table', 'parent_id', 'xparent_table', 'xparent_id') as $field) {
      if (isset($query[$field])) {
        $values = is_array($query[$field]) ? $query[$field] : array($query[$field]);
        $exprs = implode(' OR ', $values);
        $q .= " AND ss_civicrm_{$field}:({$exprs})";
      }
    }

    $query = apachesolr_get_solr()->search($q, $params);
    if ($query->code == 200) {
      return $this->formatResult($query->response);
    }
    else {
      CRM_Core_Error::debug_var('failedSolrQuery', $query);
      throw new CRM_Core_Exception("File search service returned an error.");
    }
  }

  /**
   * @param stdClass $response Solr Response
   * @return array
   *  - file_id: int
   *  - parent: array, dynamic FKs (entity_table+entity_id) to the entity to which the file is directly attached
   *  - xparent: array, dynamic FKs (entity_table+entity_id) to the business-object to which the file is attached
   *    (possibly indirectly)
   */
  public function formatResult($response) {
    $matches = array();
    foreach ($response->docs as $doc) {
      $match = array();
      $match['file_id'] = $doc->entity_id;
      $match['parent_table'] = isset($doc->ss_civicrm_xparent_table) ? $doc->ss_civicrm_xparent_table : NULL;
      $match['parent_id'] = isset($doc->ss_civicrm_xparent_id) ? $doc->ss_civicrm_xparent_id : NULL;
      $match['xparent_table'] = isset($doc->ss_civicrm_xparent_table) ? $doc->ss_civicrm_xparent_table : NULL;
      $match['xparent_id'] = isset($doc->ss_civicrm_xparent_id) ? $doc->ss_civicrm_xparent_id : NULL;
      $matches[] = $match;
    }
    return $matches;
  }
}
