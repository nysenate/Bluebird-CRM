<?php

/**
 * @file
 * Basic textfield filter to handle string filtering commands
 * including equality, contains, not contains, etc.
 */

/**
 *
 */
class ldap_views_handler_filter extends views_handler_filter {
  /**
   * Exposed filter options.
   */
  public $always_multiple = TRUE;

  /**
   *
   */
  public function option_definition() {
    $options = parent::option_definition();
    $options['expose']['contains']['required'] = ['default' => FALSE];
    return $options;
  }

  /**
   * This kind of construct makes it relatively easy for a child class
   * to add or remove functionality by overriding this function and
   * adding/removing items from this array.
   */
  public function operators() {
    $operators = [
      '=' => [
        'title' => t('Is equal to'),
        'short' => t('='),
        'method' => 'op_equal',
        'values' => 1,
      ],
      '!=' => [
        'title' => t('Is not equal to'),
        'short' => t('!='),
        'method' => 'op_equal',
        'values' => 1,
      ],
      'contains' => [
        'title' => t('Contains'),
        'short' => t('contains'),
        'method' => 'op_contains',
        'values' => 1,
      ],
      'starts' => [
        'title' => t('Starts with'),
        'short' => t('begins'),
        'method' => 'op_starts',
        'values' => 1,
      ],
      'not_starts' => [
        'title' => t('Does not start with'),
        'short' => t('not_begins'),
        'method' => 'op_not_starts',
        'values' => 1,
      ],
      'ends' => [
        'title' => t('Ends with'),
        'short' => t('ends'),
        'method' => 'op_ends',
        'values' => 1,
      ],
      'not_ends' => [
        'title' => t('Does not end with'),
        'short' => t('not_ends'),
        'method' => 'op_not_ends',
        'values' => 1,
      ],
      'not' => [
        'title' => t('Does not contain'),
        'short' => t('!has'),
        'method' => 'op_not',
        'values' => 1,
      ],
      'greater' => [
        'title' => t('Greater than or equal to'),
        'short' => t('greater_eq'),
        'method' => 'op_greater_eq',
        'values' => 1,
      ],
      'less' => [
        'title' => t('Less than or equal to'),
        'short' => t('less_eq'),
        'method' => 'op_less_eq',
        'values' => 1,
      ],
    ];

    return $operators;
  }

  /**
   * Build strings from the operators() for 'select' options.
   */
  public function operator_options($which = 'title') {
    $options = [];
    foreach ($this->operators() as $id => $info) {
      $options[$id] = $info[$which];
    }

    return $options;
  }

  /**
   *
   */
  public function admin_summary() {
    if (!empty($this->options['exposed'])) {
      return t('exposed');
    }

    $options = $this->operator_options('short');
    $output = '';
    if (!empty($options[$this->operator])) {
      $output = check_plain($options[$this->operator]);
    }
    if (in_array($this->operator, $this->operator_values(1))) {
      $output .= ' ' . check_plain($this->value);
    }
    return $output;
  }

  /**
   *
   */
  public function operator_values($values = 1) {
    $options = [];
    foreach ($this->operators() as $id => $info) {
      if (isset($info['values']) && $info['values'] == $values) {
        $options[] = $id;
      }
    }

    return $options;
  }

  /**
   * Provide a simple textfield for equality.
   */
  public function value_form(&$form, &$form_state) {
    // We have to make some choices when creating this as an exposed
    // filter form. For example, if the operator is locked and thus
    // not rendered, we can't render dependencies; instead we only
    // render the form items we need.
    $which = 'all';
    if (!empty($form['operator'])) {
      $source = ($form['operator']['#type'] == 'radios') ? 'radio:options[operator]' : 'edit-options-operator';
    }
    if (!empty($form_state['exposed'])) {
      $identifier = $this->options['expose']['identifier'];

      if (empty($this->options['expose']['use_operator']) || empty($this->options['expose']['operator_id'])) {
        // Exposed and locked.
        $which = in_array($this->operator, $this->operator_values(1)) ? 'value' : 'none';
      }
      else {
        $source = 'edit-' . drupal_html_id($this->options['expose']['operator_id']);
      }
    }

    if ($which == 'all' || $which == 'value') {
      $form['value'] = [
        '#type' => 'textfield',
        '#title' => t('Value'),
        '#size' => 30,
        '#default_value' => $this->value,
      ];
      if (!empty($form_state['exposed']) && !isset($form_state['input'][$identifier])) {
        $form_state['input'][$identifier] = $this->value;
      }

      if ($which == 'all') {
        $form['value'] += [
          '#dependency' => [$source => $this->operator_values(1)],
        ];
      }
    }

    if (!isset($form['value'])) {
      // Ensure there is something in the 'value'.
      $form['value'] = [
        '#type' => 'value',
        '#value' => NULL,
      ];
    }
  }

  /**
   * Add this filter to the query.
   *
   * Due to the nature of fapi, the value and the operator have an unintended
   * level of indirection. You will find them in $this->operator
   * and $this->value respectively.
   */
  public function query() {
    $this->ensure_my_table();
    $field = $this->real_field;

    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  /**
   *
   */
  public function op_equal($field) {
    $this->query->add_where($this->options['group'], $field, $this->value, $this->operator);
  }

  /**
   *
   */
  public function op_contains($field) {
    $this->query->add_where($this->options['group'], $field, "*$this->value*", '=');
  }

  /**
   *
   */
  public function op_starts($field) {
    $this->query->add_where($this->options['group'], $field, "$this->value*", '=');
  }

  /**
   *
   */
  public function op_not_starts($field) {
    $this->query->add_where($this->options['group'], $field, "$this->value*", '!=');
  }

  /**
   *
   */
  public function op_ends($field) {
    $this->query->add_where($this->options['group'], $field, "*$this->value", '=');
  }

  /**
   *
   */
  public function op_not_ends($field) {
    $this->query->add_where($this->options['group'], $field, "*$this->value", '!=');
  }

  /**
   *
   */
  public function op_not($field) {
    $this->query->add_where($this->options['group'], $field, "*$this->value*", '!=');
  }

  /**
   *
   */
  public function op_greater_eq($field) {
    $this->query->add_where($this->options['group'], $field, $this->value, '>=');
  }

  /**
   *
   */
  public function op_less_eq($field) {
    $this->query->add_where($this->options['group'], $field, $this->value, '<=');
  }

  /**
   *
   */
  public function op_exists($field) {
    $this->query->add_where($this->options['group'], $field, '*', $this->operator == 'exists' ? '=' : '!=');
  }

}
