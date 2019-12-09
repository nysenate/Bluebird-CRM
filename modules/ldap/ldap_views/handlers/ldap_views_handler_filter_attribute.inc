<?php

/**
 * @file
 * Basic textfield filter to handle string filtering commands for a generic ldap attribute
 * Includes new criterias.
 */

/**
 *
 */
class ldap_views_handler_filter_attribute extends ldap_views_handler_filter {

  /**
   * Determine if a filter can be exposed.
   */
  public function can_expose() {
    return TRUE;
  }

  /**
   *
   */
  public function option_definition() {
    $options = parent::option_definition();
    $options['attribute_name'] = ['default' => ''];
    return $options;
  }

  /**
   * This kind of construct makes it relatively easy for a child class
   * to add or remove functionality by overriding this function and
   * adding/removing items from this array.
   */
  public function operators() {
    $operators = [
      'exists' => [
        'title' => t('Exists'),
        'method' => 'op_exists',
        'short' => t('exists'),
        'values' => 0,
      ],
      'not exists' => [
        'title' => t('Not exists'),
        'method' => 'op_exists',
        'short' => t('not exists'),
        'values' => 0,
      ],
    ];

    return parent::operators() + $operators;
  }

  /**
   * Provide a simple textfield for equality.
   */
  public function value_form(&$form, &$form_state) {
    $ldap_data = new LdapQuery(ldap_views_get_qid($this->view));

    if (empty($ldap_data)) {
      $form['attribute_name'] = [
        '#markup' => 'You must select a valid LDAP search (Advanced::Query settings)',
      ];
      return;
    }

    $options = [];
    foreach ($ldap_data->attributes as $attribute) {
      $options[$attribute] = $attribute;
    }

    if (empty($form_state['exposed'])) {
      $form['attribute_name'] = [
        '#type' => 'select',
        '#title' => t('Attribute name'),
        '#description' => t('The attribute name from LDAP response'),
        '#options' => $options,
        '#default_value' => $this->options['attribute_name'],
        '#required' => TRUE,
      ];
    }

    parent::value_form($form, $form_state);
  }

  /**
   *
   */
  public function op_equal($field) {
    parent::op_equal($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_contains($field) {
    parent::op_contains($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_starts($field) {
    parent::op_starts($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_not_starts($field) {
    parent::op_not_starts($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_ends($field) {
    parent::op_ends($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_not_ends($field) {
    parent::op_not_ends($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_not($field) {
    parent::op_not($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_greater_eq($field) {
    parent::op_greater_eq($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_less_eq($field) {
    parent::op_less_eq($this->options['attribute_name']);
  }

  /**
   *
   */
  public function op_exists($field) {
    parent::op_exists($this->options['attribute_name']);
  }

}
