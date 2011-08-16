<?php 

function civicrm_engage_admin_settings($form_state = NULL){

   $form['civicrm_rules_post_entities']= array(
    '#title'=> t("Entities enabled for Post Rules"),
    '#type' => 'checkbox',
    '#options' => array(''),
    '#description' => t('Do you want a group-break between each side of the street'),
    '#default_value' => variable_get('civicrm_engage_pagebreak_odd_even',"1"),
  );

  return system_settings_form($form);
}
