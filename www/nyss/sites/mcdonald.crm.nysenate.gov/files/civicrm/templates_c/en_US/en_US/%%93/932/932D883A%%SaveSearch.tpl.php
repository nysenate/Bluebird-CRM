<?php /* Smarty version 2.6.26, created on 2010-08-25 11:54:04
         compiled from CRM/Contact/Form/Task/SaveSearch.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Task/SaveSearch.tpl', 27, false),)), $this); ?>
<div class="crm-form-block crm-block crm-contact-task-createsmartgroup-form-block">
<h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Smart Group<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
    <?php if ($this->_tpl_vars['qill'][0]): ?>
        <div id="search-status">
            <ul>
                <?php $_from = $this->_tpl_vars['qill'][0]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['criteria']):
?>
                    <li><?php echo $this->_tpl_vars['criteria']; ?>
</li>
                <?php endforeach; endif; unset($_from); ?>
            </ul>
            <br />
        </div>
    <?php endif; ?>
  <table class="form-layout-compressed">
        <tr class="crm-contact-task-createsmartgroup-form-block-title">
            <td class="label"><?php echo $this->_tpl_vars['form']['title']['label']; ?>
</td>
            <td><?php echo $this->_tpl_vars['form']['title']['html']; ?>
</td>
        </tr>
	<tr class="crm-contact-task-createsmartgroup-form-block-description">
            <td class="label"><?php echo $this->_tpl_vars['form']['description']['label']; ?>
</td>
            <td><?php echo $this->_tpl_vars['form']['description']['html']; ?>
</td>
        </tr>
          <?php if ($this->_tpl_vars['form']['group_type']): ?>
        <tr class="crm-contact-task-createsmartgroup-form-block-group_type"> 
            <td class="label"><?php echo $this->_tpl_vars['form']['group_type']['label']; ?>
</td>
            <td><?php echo $this->_tpl_vars['form']['group_type']['html']; ?>
</td>
        </tr>
          <?php endif; ?>
  </table>
 <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>