<?php

/**
 * @file
 *   drush integration for apachesolr.
 */

/**
 * Implements hook_drush_command().
 *
 * @return array
 *   An associative array describing your command(s).
 */
function apachesolr_drush_command() {
  $items = array();

  $items['solr-delete-index'] = array(
    'callback' => 'apachesolr_drush_solr_delete_index',
    'description' => dt('Deletes the content from the index. Can take content types as parameters.'),
    'arguments' => array(
      'types' => dt('Optional. A space delimited list of content types to be deleted from the index.'),
    ),
    'examples' => array(
      'drush solr-delete-index node' => 'Delete all node content from the index.',
      'drush solr-delete-index node:article' => 'Delete all content of the article content type from the index.',
      'drush solr-delete-index node:article node:blog' => 'Delete all content of the article and blog content types from the index.',
    ),
  );
  $items['solr-mark-all'] = array(
    'callback' => 'apachesolr_drush_solr_mark_for_reindex',
    'description' => dt('Marks content for reindexing. Can take content types as parameters.'),
    'arguments' => array(
      'types' => dt('Optional. A space delimited list of content types to be marked for reindexing.'),
    ),
  );
  $items['solr-index'] = array(
    'callback' => 'apachesolr_drush_solr_index',
    'description' => dt('Reindexes content marked for (re)indexing.'),
  );
  $items['solr-search'] = array(
    'callback' => 'apachesolr_drush_solr_search',
    'description' => dt('Search the site for keywords using Apache Solr'),
    'arguments' => array(
      'keywords' => dt('One or more keywords, separated by spaces.'),
    ),
  );
  $items['solr-get-env-id'] = array(
    'callback' => 'apachesolr_drush_solr_get_env_id',
    'description' => dt('Get the default Apache Solr environment id'),
    'arguments' => array(),
  );
  $items['solr-get-env-name'] = array(
    'callback' => 'apachesolr_drush_solr_get_env_name',
    'description' => dt('Get the default Apache Solr environment name, uses the default environment by default.'),
    'arguments' => array(
      'id' => dt('Optional. Apache Solr environment id.'),
    ),
  );
  $items['solr-get-env-url'] = array(
    'callback' => 'apachesolr_drush_solr_get_env_url',
    'description' => dt('Get the default Apache Solr environment url, uses the default environment by default.'),
    'arguments' => array(
      'id' => dt('Optional. Apache Solr environment id.'),
    ),
  );
  $items['solr-set-env-url'] = array(
    'callback' => 'apachesolr_drush_solr_set_env_url',
    'description' => dt('Set the url for an Apache Solr environment, uses the default environment by default.'),
    'arguments' => array(
      'url' => dt('Apache Solr server url string.'),
      'id' => dt('Optional. Apache Solr environment id.'),
    ),
  );
  return $items;
}

/**
 * Implements hook_drush_help().
 *
 * This function is called whenever a drush user calls
 * 'drush help <name-of-your-command>'
 *
 * @param string $section
 *   A string with the help section (prepend with 'drush:')
 *
 * @return string
 *   A string with the help text for your command.
 */
function apachesolr_drush_help($section) {
  switch ($section) {
    case 'drush:solr-delete-index':
      return dt("Used without parameters, this command deletes the entire Solr index. Used with parameters for content type, it deletes just the content types that are specified. After the index has been deleted, all content will be indexed again on future cron runs.");
    case 'drush:solr-mark-all':
      return dt("Used without parameters, this command marks all of the content in the Solr index for reindexing. Used with paramters for content type, it marks just the content types that are specified. Reindexing is different than deleting as the content is still searchable while it is in queue to be reindexed. Reindexing is done on future cron runs.");
    case 'drush:solr-index':
      return dt("Reindexes content marked for (re)indexing. If you want to reindex all content or content of a specific type, use solr-reindex first to mark that content.");
    case 'drush:solr-search':
      return dt('Executes a search against the site\'s Apache Solr search index and returns the restults.');
  }
}

/**
 * Selectively delete content from the apachesolr index.
 *
 * Each argument is a filter on what to delete from the index.
 * They are of the form entity (to delete all content of that
 * entity) or entity:bundle (to delete all content of that
 * bundle).
 */
function apachesolr_drush_solr_delete_index() {
  module_load_include('inc', 'apachesolr', 'apachesolr.index');
  $args = func_get_args();
  $env_id = apachesolr_default_environment();

  if (count($args) > 0) {
    foreach ($args as $type) {
      $parts = explode(':', $type);
      if (count($parts) === 1) {
        apachesolr_index_delete_index($env_id, $type);
      }
      elseif (count($parts) == 2) {
        apachesolr_index_delete_index($env_id, $parts[0], $parts[1]);
      }
      else {
        drush_set_error('The syntax for each type is either entity or entity:bundle');
      }
    }
  }
  else {
    apachesolr_index_delete_index($env_id);
  }

  drush_print(t('Deleted the Solr index'));
}

function apachesolr_drush_solr_mark_for_reindex() {
  module_load_include('inc', 'apachesolr', 'apachesolr.index');
  $args = func_get_args();
  $env_id = apachesolr_default_environment();
  if (count($args) > 0) {
    foreach ($args as $type) {
      apachesolr_index_mark_for_reindex($env_id, $type);
    }
  }
  else {
    apachesolr_index_mark_for_reindex($env_id);
  }
  drush_print(t('Marked content for reindexing'));
}

function apachesolr_drush_solr_index() {
  module_load_include('inc', 'apachesolr', 'apachesolr.admin');
  module_load_include('inc', 'apachesolr', 'apachesolr.index');
  $env_id = apachesolr_default_environment();
  apachesolr_index_batch_index_remaining($env_id);
  drush_backend_batch_process();
}

function apachesolr_drush_solr_search() {
  $args = func_get_args();
  $keys = implode(' ', $args);
  foreach (apachesolr_search_search_execute($keys) as $result) {
    $output = 'node/' . $result['node']->entity_id . ' ' . dt('by @name (user/@uid)', array('@name' => strip_tags($result['user']), '@uid' => $result['node']->is_uid)) . "\n";
    $output .= dt('title: ') . $result['title'] . "\n";
    $output .= preg_replace('/[\s]+/', ' ', strip_tags($result['snippet'])) . "\n\n";
    drush_print($output);
  }
}

function apachesolr_drush_solr_get_env_id() {
  $solr_env_id = apachesolr_default_environment();
  drush_print($solr_env_id);
}

function apachesolr_drush_solr_get_env_name() {
  $args = func_get_args();
  if (isset($args[0])) {
    $solr_env_id = $args[0];
  }
  else {
    $solr_env_id = apachesolr_default_environment();
  }

  $solr_env = apachesolr_environment_load($solr_env_id);
  drush_print($solr_env['name']);
}

function apachesolr_drush_solr_get_env_url() {
  $args = func_get_args();
  if (isset($args[0])) {
    $solr_env_id = $args[0];
  }
  else {
    $solr_env_id = apachesolr_default_environment();
  }

  $solr_env = apachesolr_environment_load($solr_env_id);
  if (!empty($solr_env['url'])) {
    drush_print($solr_env['url']);
  }
}

function apachesolr_drush_solr_set_env_url() {
  $args = func_get_args();

  // url
  if (!isset($args[0])) {
    return drush_set_error('DRUSH_VARIABLE_ERROR', dt('No apachesolr environment url specified'));
  }
  // environment id
  if (isset($args[1])) {
    $solr_env_id = $args[1];
  }
  else {
    $solr_env_id = apachesolr_default_environment();
  }

  $solr_env = array();
  $solr_env['url'] = $args[0];
  $solr_env['env_id'] = $solr_env_id;
  apachesolr_environment_save($solr_env);
}
