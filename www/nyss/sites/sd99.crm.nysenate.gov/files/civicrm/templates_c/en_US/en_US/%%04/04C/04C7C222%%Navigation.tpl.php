<?php /* Smarty version 2.6.26, created on 2010-08-20 15:28:22
         compiled from CRM/Admin/Form/Navigation.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/Navigation.tpl', 29, false),array('function', 'help', 'CRM/Admin/Form/Navigation.tpl', 35, false),)), $this); ?>
<div class="crm-block crm-form-block crm-navigation-form-block">
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
<fieldset><legend><?php if ($this->_tpl_vars['action'] == 1): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>New Menu<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php elseif ($this->_tpl_vars['action'] == 2): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Menu<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Menu<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></legend>
<table class="form-layout-compressed">
    <tr class="crm-navigation-form-block-label">
        <td class="label"><?php echo $this->_tpl_vars['form']['label']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['label']['html']; ?>
</td>
    </tr>
    <tr class="crm-navigation-form-block-url">
        <td class="label"><?php echo $this->_tpl_vars['form']['url']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['url']['html']; ?>
 <?php echo smarty_function_help(array('id' => "id-menu_url",'file' => "CRM/Admin/Form/Navigation.hlp"), $this);?>
</td>
    </tr>
    <tr class="crm-navigation-form-block-parent_id">
        <td class="label"><?php echo $this->_tpl_vars['form']['parent_id']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['parent_id']['html']; ?>
 <?php echo smarty_function_help(array('id' => "id-parent",'file' => "CRM/Admin/Form/Navigation.hlp"), $this);?>
</td>
    </tr>
    <tr class="crm-navigation-form-block-has_separator">
        <td class="label"><?php echo $this->_tpl_vars['form']['has_separator']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['has_separator']['html']; ?>
 <?php echo smarty_function_help(array('id' => "id-has_separator",'file' => "CRM/Admin/Form/Navigation.hlp"), $this);?>
</td>
    </tr>
    <tr class="crm-navigation-form-block-permission">
        <td class="label"><?php echo $this->_tpl_vars['form']['permission']['label']; ?>
<br /><?php echo smarty_function_help(array('id' => "id-menu_permission",'file' => "CRM/Admin/Form/Navigation.hlp"), $this);?>
</td><td><?php echo $this->_tpl_vars['form']['permission']['html']; ?>
</td>
    </tr>
    <tr class="crm-navigation-form-block-permission_operator">
        <td class="label">&nbsp;</td><td><?php echo $this->_tpl_vars['form']['permission_operator']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['permission_operator']['label']; ?>
 <?php echo smarty_function_help(array('id' => "id-permission_operator",'file' => "CRM/Admin/Form/Navigation.hlp"), $this);?>
</td>
    </tr>
    <tr class="crm-navigation-form-block-is_active">
        <td class="label"><?php echo $this->_tpl_vars['form']['is_active']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['is_active']['html']; ?>
</td>
    </tr>
</table>   
</fieldset>
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>