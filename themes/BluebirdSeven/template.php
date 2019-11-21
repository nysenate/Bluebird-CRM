<?php

/**
 * Get rid of Home in breadcrumb trail.
 */
function BluebirdSeven_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];

  if (!empty($breadcrumb)) {
    // Provide a navigational heading to give context for breadcrumb links to
    // screen-reader users. Make the heading invisible with .element-invisible.
    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

    array_shift($breadcrumb); // Removes the Home item
    $output .= '<div class="breadcrumb">' . implode(' » ', $breadcrumb) . '</div>';

    return $output;
  }

  return NULL;
}

//unset login failed message
function BluebirdSeven_page_alter(&$page) {
  $errors = drupal_get_messages('error');
  foreach($errors['error'] as $error) {
    if(strpos($error, 'Sorry, unrecognized username or password') === FALSE &&
      strpos($error, 'Sorry, there have been more than 5 failed login attempts') === FALSE
    ) {
      drupal_set_message($error, 'error');
    }
  }
}
