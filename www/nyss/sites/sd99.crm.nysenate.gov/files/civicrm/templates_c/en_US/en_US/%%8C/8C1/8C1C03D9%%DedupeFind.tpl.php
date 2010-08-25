<?php /* Smarty version 2.6.26, created on 2010-08-20 09:21:54
         compiled from CRM/Admin/Form/DedupeFind.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/DedupeFind.tpl', 28, false),)), $this); ?>
<div class="crm-block crm-form-block crm-dedupe-find-form-block">
<div id="help">
    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You can search all contacts for duplicates or limit the search to a specific group. After initiating the rule, please be patient as it may take some time to fully process.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 
</div>
   <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div> 
   <table class="form-layout-compressed">
     <tr class="crm-dedupe-find-form-block-group_id">
       <td class="label"><?php echo $this->_tpl_vars['form']['group_id']['label']; ?>
</td>
       <td><?php echo $this->_tpl_vars['form']['group_id']['html']; ?>
</td>
     </tr>
   </table>
  <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>