<?php /* Smarty version 2.6.26, created on 2010-08-23 10:32:52
         compiled from CRM/Contact/Page/View/UserDashBoard.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Page/View/UserDashBoard.tpl', 31, false),)), $this); ?>
<table class="dashboard-elements">
<?php if ($this->_tpl_vars['showGroup']): ?>
    <tr>
        <td>
          <div class="header-dark">
          <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Your Group(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>  
          </div>	  
          <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/View/UserDashBoard/GroupContact.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>	
            
        </td>
    </tr>
<?php endif; ?>

    <?php $_from = $this->_tpl_vars['dashboardElements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['element']):
?>
    <tr>
        <td>
            <div class="header-dark"><?php echo $this->_tpl_vars['element']['sectionTitle']; ?>
</div>	        
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['element']['templatePath'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </td>
    </tr>
    <?php endforeach; endif; unset($_from); ?>
</table>