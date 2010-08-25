<?php /* Smarty version 2.6.26, created on 2010-06-23 10:43:17
         compiled from CRM/common/showHideByFieldValue.tpl */ ?>

<script type="text/javascript">
    var trigger_field_id = '<?php echo $this->_tpl_vars['trigger_field_id']; ?>
';
    var trigger_value = '<?php echo $this->_tpl_vars['trigger_value']; ?>
';
    var target_element_id = '<?php echo $this->_tpl_vars['target_element_id']; ?>
';
    var target_element_type = '<?php echo $this->_tpl_vars['target_element_type']; ?>
';
    var field_type  = '<?php echo $this->_tpl_vars['field_type']; ?>
';
    var invert = <?php echo $this->_tpl_vars['invert']; ?>
;

    showHideByValue(trigger_field_id, trigger_value, target_element_id, target_element_type, field_type, invert);

</script>  