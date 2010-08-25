<?php /* Smarty version 2.6.26, created on 2010-07-12 10:46:26
         compiled from CRM/Activity/Page/Tab.tpl */ ?>


<?php if ($this->_tpl_vars['action'] == 16 && $this->_tpl_vars['permission'] == 'edit' && ! $this->_tpl_vars['addAssigneeContact'] && ! $this->_tpl_vars['addTargetContact']): ?>
    <div class="action-link crm-activityLinks" style="text-align: left"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Activity/Form/ActivityLinks.tpl", 'smarty_include_vars' => array('as_select' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
<?php endif; ?>

<?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2 || $this->_tpl_vars['action'] == 8 || $this->_tpl_vars['action'] == 4 || $this->_tpl_vars['action'] == 32768): ?>     <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Activity/Form/Activity.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Activity/Selector/Activity.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>