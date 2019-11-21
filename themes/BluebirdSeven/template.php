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

  //echo '<pre>';print_r($page);echo $title;echo '</pre>';
}

function BluebirdSeven_preprocess_page(&$vars) {
  //echo '<pre>';print_r($vars);echo '</pre>';

  //set title when no roles
  $title = drupal_get_title();
  if ($title == 'Please Login' && !empty($vars['user']->uid)) {
    drupal_set_title('Bluebird Access Permission Required');
  }

  //only role is authenticated user
  if (count($vars['user']->roles) == 1 && isset($vars['user']->roles[2])) {
    $vars['userNoRoles'] = TRUE;
  }

  //$vars['myvar'] = "value";
}

function BluebirdSeven_preprocess_html(&$vars) {
  //echo '<pre>';print_r($vars);echo '</pre>';

  //add body class if no roles
  if ($vars['head_title_array']['title'] == 'Bluebird Access Permission Required') {
    $vars['classes_array'][] = 'user-no-roles';
  }

  //$vars['classes_array'][] = 'new-class';
}
