<?php /* Smarty version 2.6.26, created on 2010-07-07 15:30:28
         compiled from CRM/Activity/Form/ActivityLinks.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Activity/Form/ActivityLinks.tpl', 34, false),)), $this); ?>
<?php if ($this->_tpl_vars['cdType'] == false): ?>
<?php if ($this->_tpl_vars['contact_id']): ?>
<?php $this->assign('contactId', $this->_tpl_vars['contact_id']); ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['as_select']): ?> <select onchange="if (this.value) window.location='<?php echo $this->_tpl_vars['url']; ?>
'+ this.value; else return false" name="other_activity" id="other_activity" class="form-select">
  <option value=""><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>- new activity -<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></option>
<?php $_from = $this->_tpl_vars['activityTypes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['link']):
?>
  <option value="<?php echo $this->_tpl_vars['k']; ?>
"><?php echo $this->_tpl_vars['link']; ?>
</option>
<?php endforeach; endif; unset($_from); ?>
</select>

<?php else: ?>
<ul>
<?php $_from = $this->_tpl_vars['activityTypes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['link']):
?>
<li class="crm-activity-type_<?php echo $this->_tpl_vars['k']; ?>
"><a href="<?php echo $this->_tpl_vars['url']; ?>
<?php echo $this->_tpl_vars['k']; ?>
"><?php if ($this->_tpl_vars['k'] != 3): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Record <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?><?php echo $this->_tpl_vars['link']; ?>
</a></li>
<?php endforeach; endif; unset($_from); ?></ul>

<?php endif; ?>


<?php if ($this->_tpl_vars['hookLinks']): ?>
   <?php $_from = $this->_tpl_vars['hookLinks']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['link']):
?>
<?php if ($this->_tpl_vars['link']['img']): ?>
      <a href="<?php echo $this->_tpl_vars['link']['url']; ?>
"><img src="<?php echo $this->_tpl_vars['link']['img']; ?>
" alt="<?php echo $this->_tpl_vars['link']['title']; ?>
" /></a>&nbsp;
<?php endif; ?>
      <a href="<?php echo $this->_tpl_vars['link']['url']; ?>
"><?php echo $this->_tpl_vars['link']['title']; ?>
</a>&nbsp;&nbsp;
   <?php endforeach; endif; unset($_from); ?>
<?php endif; ?>

<?php endif; ?>