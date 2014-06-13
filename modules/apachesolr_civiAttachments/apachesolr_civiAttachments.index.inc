<?php

/**
 * @file
 * Indexing-related functions.
 */

/**
 * Checks if a file is of a MIME type that is to be excluded from the index.
 *
 * The MIME types of excluded files are built and cached each time the file
 * attachments settings form is saved.
 *
 * @param $filename
 *   A string, the MIME type of the file.
 *
 * @return
 *   Boolean, TRUE if the $filemime is allowed, otherwise FALSE.
 */
function apachesolr_civiAttachments_allowed_mime($filemime) {

  $excluded = variable_get('apachesolr_civiAttachments_excluded_mime', FALSE);
  if ($excluded === FALSE) {
    // Build the list of excluded MIME types.
    $excluded = array();
    $extensions = variable_get('apachesolr_civiAttachments_excluded_extensions', FALSE);
    if ($extensions !== FALSE) {
      $extensions = explode(' ', $extensions);
    }
    else {
      $extensions = apachesolr_civiAttachments_default_excluded();
    }
    foreach ($extensions as $ext) {
      $ext = trim($ext);
      if ($ext) {
        $mime = file_get_mimetype('dummy.' . $ext);
        $excluded[$mime] = 1;
      }
    }
    variable_set('apachesolr_civiAttachments_excluded_mime', $excluded);
  }
  return empty($excluded[$filemime]);
}

/**
 * Parse the attachment getting just the raw text.
 *
 * @throws Exception
 */
function apachesolr_civiAttachments_get_attachment_text($file) {
  $indexer_table = apachesolr_get_indexer_table('civiFile');

  if (!apachesolr_civiAttachments_is_file($file->path)) {
    return FALSE;
  }

  $filepath = $file->path;
  // No need to use java for plain text files.
  if ($file->mime_type == 'text/plain' || $file->mime_type == 'text/x-diff') {
    $text = file_get_contents($filepath);
    // TODO - try to detect encoding and convert to UTF-8.
    // Strip bad control characters.
    $text = iconv("UTF-8", "UTF-8//IGNORE", $text);
    $text = trim(apachesolr_clean_text($text));
    return $text;
  }

  $hash = hash('sha256', file_get_contents($filepath));
  if ($hash === FALSE) {
    watchdog('Apache Solr Attachments', 'sha256 hash algorithm is not supported', NULL, WATCHDOG_ERROR);
    return FALSE;
  }


  $cached = db_query("SELECT * FROM {{$indexer_table}} WHERE entity_id = :entity_id", array(':entity_id' => $file->file_id))->fetchAssoc();

  if (!is_null($cached['body']) && ($cached['hash'] == $hash)) {
    // No need to re-extract.
    return $cached['body'];
  }

  if (variable_get('apachesolr_civiAttachments_extract_using', 'tika') == 'tika') {
    $text = apachesolr_civiAttachments_extract_using_tika($filepath);
  }
  else {
    // Extract using Solr.
    try {
      list($text, $metadata) = apachesolr_civiAttachments_extract_using_solr($filepath);
    }
    catch (Exception $e) {
      // Exceptions from Solr may be transient, or indicate a problem with a specific file.
      watchdog('Apache Solr Attachments', "Exception occurred sending %filepath to Solr\n!message", array('%filepath' => $file->path, '!message' => nl2br(check_plain($e->getMessage()))), WATCHDOG_ERROR);
      return FALSE;
    }
  }

  // Strip bad control characters.
  $text = iconv("UTF-8", "UTF-8//IGNORE", $text);
  $text = trim(apachesolr_clean_text($text));

  // Save the extracted, cleaned text to the DB.
  db_update($indexer_table)->fields(array('hash' => $hash, 'body' => $text))->condition('entity_id', $file->file_id)->execute();

  return $text;
}

/**
 * For a file path, try to extract text using a local tika jar.
 *
 * @throws Exception
 */
function apachesolr_civiAttachments_extract_using_tika($filepath) {
  $tika_path = realpath(variable_get('apachesolr_civiAttachments_tika_path', ''));

  $tika = realpath($tika_path . '/' . variable_get('apachesolr_civiAttachments_tika_jar', 'tika-app-1.1.jar'));
  if (!$tika || !is_file($tika)) {
    throw new Exception(t('Invalid path or filename for tika application jar.'));
  }

  $cmd = '';
  // Add a work-around for a MAMP bug + java 1.5.
  if (strpos(ini_get('extension_dir'), 'MAMP/')) {
    $cmd .= 'export DYLD_LIBRARY_PATH=""; ';
  }
  // Support UTF-8 encoded filenames.
  if (mb_detect_encoding($filepath, 'ASCII,UTF-8', TRUE) == 'UTF-8') {
    $cmd .= 'export LANG="en_US.utf-8"; ';
    setlocale(LC_CTYPE, 'UTF8', 'en_US.UTF-8');
  }

  // By default force UTF-8 output.
  $cmd .= escapeshellcmd(variable_get('apachesolr_civiAttachments_java', 'java')) . ' ' .
    escapeshellarg(variable_get('apachesolr_civiAttachments_java_opts', '-Dfile.encoding=UTF8')) .
    ' -cp ' . escapeshellarg($tika_path) . ' -jar ' . escapeshellarg($tika) .
    ' -t ' . escapeshellarg($filepath);

  return shell_exec($cmd);
}

/**
 * For a file path, try to extract text using Solr 1.4+.
 *
 * @throws Exception
 */
function apachesolr_civiAttachments_extract_using_solr($filepath) {
  // Extract using Solr.
  // We allow Solr to throw exceptions - they will be caught
  // by apachesolr.module.
  $env_id = apachesolr_default_environment();
  $solr = apachesolr_get_solr($env_id);
  $filename = basename($filepath);
  $params = array(
    'resource.name' => $filename,
    'extractFormat' => 'text', // Matches the -t command for the tika CLI app.
  );
  // Construct a multi-part form-data POST body in $data.
  $boundary = '--' . hash('sha256', uniqid(REQUEST_TIME));
  $data = "--{$boundary}\r\n";
  // The 'filename' used here becomes the property name in the response.
  $data .= 'Content-Disposition: form-data; name="file"; filename="extracted"';
  $data .= "\r\nContent-Type: application/octet-stream\r\n\r\n";
  $data .= file_get_contents($filepath);
  $data .= "\r\n--{$boundary}--\r\n";
  $headers = array('Content-Type' => 'multipart/form-data; boundary=' . $boundary);
  $options = array(
    'method' => 'POST',
    'headers' => $headers,
    'data' => $data,
  );
  $response = $solr->makeServletRequest(APACHESOLR_CIVIATTACHMENTS_EXTRACTING_SERVLET, $params, $options);
  return array($response->extracted, $response->extracted_metadata);
}

/**
 * Records that a parent entity is using a file.
 *
 * @param $file
 *   A file object.
 * @param $module
 *   The name of the module using the file.
 * @param $type
 *   The type of the object that contains the referenced file.
 * @param $id
 *   The unique, numeric ID of the object containing the referenced file.
 * @param $count
 *   (optional) The number of references to add to the object. Defaults to 1.
 *
 */
function apachesolr_civiAttachments_add_file_usage($fileDAO, $parent_entity_type, $parent_entity_id) {
  $indexer_table = apachesolr_get_indexer_table('civiFile');
  db_merge($indexer_table)
    ->key(array(
        'entity_type' => 'civiFile',
        'entity_id' => $fileDAO->file_id,
        'parent_entity_type' => $parent_entity_type,
        'parent_entity_id' => $parent_entity_id,
      ))
    ->fields(array(
        'bundle' => 'civiFile',
        'status' => 1,
	'changed' => REQUEST_TIME,
        ))->execute();
}

/**
 * Removes a record to indicate that an entity is no longer using a file.
 *
 * @param $file
 *   A file object.
 * @param $parent_entity_type
 *   (optional) The type of the object that contains the referenced file. May
 *   be omitted if all module references to a file are being deleted.
 * @param $parent_entity_id
 *   (optional) The unique, numeric ID of the object containing the referenced
 *   file. May be omitted if all module references to a file are being deleted.
 *
 */
function apachesolr_civiAttachments_delete_file_usage(stdClass $file, $parent_entity_type = NULL, $parent_entity_id = NULL) {
  $indexer_table = apachesolr_get_indexer_table('civiFile');
  $query = db_delete($indexer_table)
    ->condition('entity_type', 'civiFile')
    ->condition('entity_id', $file->fid)
    ->condition('parent_entity_type', $parent_entity_type)
    ->condition('parent_entity_id', $parent_entity_id);
}

function apachesolr_civiAttachments_clean_index_table() {
  $indexer_table = apachesolr_get_indexer_table('civiFile');
  // Clean all entries where parent_entity_id is empty
  db_delete($indexer_table)
  ->condition('parent_entity_id', 0)
  ->execute();
  // Clean all entries from entity types that should not be indexed
  foreach (entity_get_info() as $entity_type => $entity_info) {
    if (empty($entity_info['apachesolr']['indexable'])) {
       db_delete($indexer_table)
      ->condition('parent_entity_type', $entity_type)
      ->execute();
    }
  }
}