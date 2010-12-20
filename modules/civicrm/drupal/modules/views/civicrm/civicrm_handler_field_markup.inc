<?php
/**
 * A handler to run a field through check_markup, using the default format
 *
 */
class civicrm_handler_field_markup extends views_handler_field {
    function construct() {
        parent::construct();
    }

    function render($values) {
        $value = $values->{$this->field_alias};
        return check_markup($value, FILTER_FORMAT_DEFAULT, FALSE);
    }
}
