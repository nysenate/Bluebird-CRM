<?php /* Smarty version 2.6.26, created on 2010-05-24 18:46:21
         compiled from CRM/Contact/Form/Task/Map.tpl */ ?>
<div class='spacer'></div>
<?php if ($this->_tpl_vars['mapProvider'] == 'Google'): ?>
  
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Task/Map/Google.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['mapProvider'] == 'Yahoo'): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Task/Map/Yahoo.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<p></p>
<div class="form-item">                     
<?php echo $this->_tpl_vars['form']['buttons']['html']; ?>
                                                                                      
</div>                            