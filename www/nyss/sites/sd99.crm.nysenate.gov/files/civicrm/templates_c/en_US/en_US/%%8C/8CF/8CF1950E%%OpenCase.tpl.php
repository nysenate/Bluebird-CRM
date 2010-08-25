<?php /* Smarty version 2.6.26, created on 2010-08-19 16:43:07
         compiled from CRM/Case/Form/Activity/OpenCase.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'help', 'CRM/Case/Form/Activity/OpenCase.tpl', 29, false),)), $this); ?>
<?php if ($this->_tpl_vars['context'] != 'caseActivity'): ?>
    <div class="crm-block crm-form-block crm-case-opencase-form-block"
    <tr class="crm-case-opencase-form-block-case_type_id">
	<td class="label"><?php echo $this->_tpl_vars['form']['case_type_id']['label']; ?>
<?php echo smarty_function_help(array('id' => "id-case_type",'file' => "CRM/Case/Form/Case.hlp"), $this);?>
</td>
	<td><?php echo $this->_tpl_vars['form']['case_type_id']['html']; ?>
</td>
    </tr>
    <tr class="crm-case-opencase-form-block-status_id">
	<td class="label"><?php echo $this->_tpl_vars['form']['status_id']['label']; ?>
</td>
	<td><?php echo $this->_tpl_vars['form']['status_id']['html']; ?>
</td>
    </tr>
    <tr class="crm-case-opencase-form-block-start_date">
        <td class="label"><?php echo $this->_tpl_vars['form']['start_date']['label']; ?>
</td>
        <td>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'start_date')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>       
        </td>
    </tr>
    </div>
<?php endif; ?>