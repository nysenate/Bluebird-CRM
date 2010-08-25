<?php /* Smarty version 2.6.26, created on 2010-08-24 15:34:23
         compiled from CRM/Case/Form/Activity/ChangeCaseStatus.tpl */ ?>
   <div class="crm-block crm-form-block crm-case-changecasestatus-form-block">
    <tr class="crm-case-changecasestatus-form-block-case_status_id">
    	<td class="label"><?php echo $this->_tpl_vars['form']['case_status_id']['label']; ?>
</td>
	<td><?php echo $this->_tpl_vars['form']['case_status_id']['html']; ?>
</td>
    </tr>     
    <?php if ($this->_tpl_vars['groupTree']): ?>
        <tr>
            <td colspan="2"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/CustomData.tpl", 'smarty_include_vars' => array('noPostCustomButton' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
        </tr>
    <?php endif; ?>
   </div>