<?php

/**
 * @file
 * Base sort handler for a ldap attributes.
 */

/**
 *
 */
class ldap_views_handler_sort_attribute extends ldap_views_handler_sort {

  /**
   *
   */
  public function option_definition() {
    $options = parent::option_definition();
    $options['attribute_name'] = ['default' => ''];
    return $options;
  }

  /**
   *
   */
  public function options_form(&$form, &$form_state) {
    $ldap_data = new LdapQuery(ldap_views_get_qid($this->view));
    if (empty($ldap_data)) {
      $form['attribute_name'] = [
        '#markup' => 'You must select a valid LDAP search (Advanced::Query settings)',
      ];
      return;
    }

    parent::options_form($form, $form_state);
    $options = [];
    foreach ($ldap_data->attributes as $attribute) {
      $options[$attribute] = $attribute;
    }
    $form['attribute_name'] = [
      '#type' => 'select',
      '#title' => t('Attribute name'),
      '#description' => t('The attribute name from LDAP response'),
      '#options' => $options,
      '#default_value' => $this->options['attribute_name'],
      '#required' => TRUE,
    ];
  }

  /**
   * Called to add the sort to a query.
   */
  public function query() {
    $this->query->add_orderby($this->table, $this->options['attribute_name'], $this->options['order']);
  }

}
