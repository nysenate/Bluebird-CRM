<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

  /**
   * @file
   * The subclass simply adds properties,
   * for field-specific subclasses to use if they need to.
   */
class civicrm_cck_handler_filter extends content_handler_filter_many_to_one {
    protected $content_field;
    protected $value_form_type = 'textfield';

    function get_value_options() {
        $this->value_options = $this->allowed_values();
    }

    // Get allowed values from hook_allowed_values(), if any,
    // or from content_allowed_values();
    // mostly copied from parent
    function allowed_values() {
        $field = $this->content_field;
        $options = cckcivirelate_allowed_values($field);
        if ($this->value_form_type == 'select') { //leaving this in case we allow a choice later
            // For selects, HTML should be filtered out and entities left unencoded.
            // See content_allowed_values / content_filter_xss / filter_xss.
            $options = array_map(create_function('$opt', 'return html_entity_decode(strip_tags($opt), ENT_QUOTES);'), $options);
        }
        return (array) $options;
    }

    function value_form(&$form, &$form_state) {
        $this->get_value_options();
        $options = $this->value_options;
        $default_value = (array) $this->value;

        if (!empty($form_state['exposed'])) {
            $identifier = $this->options['expose']['identifier'];

            if (!empty($this->options['expose']['reduce'])) {
                $options = $this->reduce_value_options();

                if (empty($this->options['expose']['single']) && 
                    !empty($this->options['expose']['optional'])) {
                    $default_value = array();
                }
            }

            if (!empty($this->options['expose']['single'])) {
                if (!empty($this->options['expose']['optional']) && 
                    (empty($default_value) || !empty($this->options['expose']['reduce']))) {
                    $default_value = 'All';
                }
                else if (empty($default_value)) {
                    $keys = array_keys($options);
                    $default_value = array_shift($keys);
                }
                else {
                    $copy = $default_value;
                    $default_value = array_shift($copy);
                }
            }
        }

        $form['value'] = array(
                               '#type' => $this->value_form_type,
                               '#title' => $this->value_title,
                               '#options' => $options,
                               '#default_value' => $default_value,
                               // These are only valid for 'select' type, but do no harm to 
                               checkboxes.
                               '#multiple' => TRUE,
                               '#size' => count($options) > 8 ? 8 : count($options),
                               );

        if (!empty($form_state['exposed']) && 
            !isset($form_state['input'][$identifier])) {
            $form_state['input'][$identifier] = $default_value;
        }

        if (empty($form_state['exposed'])) {
            $this->helper->options_form($form, $form_state);
        }

        $form['value']['#autocomplete_path'] = 'cckcivirelate_ind/autocomplete';

    }

}
