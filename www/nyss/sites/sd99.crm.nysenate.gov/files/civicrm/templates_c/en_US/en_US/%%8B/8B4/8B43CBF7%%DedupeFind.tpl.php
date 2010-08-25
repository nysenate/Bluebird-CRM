<?php /* Smarty version 2.6.26, created on 2010-08-20 09:21:54
         compiled from CRM/Admin/Page/DedupeFind.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Page/DedupeFind.tpl', 29, false),array('function', 'crmURL', 'CRM/Admin/Page/DedupeFind.tpl', 31, false),array('function', 'cycle', 'CRM/Admin/Page/DedupeFind.tpl', 36, false),)), $this); ?>
<?php if ($this->_tpl_vars['action'] == 2 || $this->_tpl_vars['action'] == 16): ?>
<div class="form-item">
  <table>
    <tr class="columnheader"><th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 1</th><th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 2 (<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Duplicate<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)</th><th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Threshold<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th><th>&nbsp;</th></tr>
    <?php $_from = $this->_tpl_vars['main_contacts']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['main_id'] => $this->_tpl_vars['main']):
?>
        <?php ob_start(); ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['main']['srcID'])), $this);?>
"><?php echo $this->_tpl_vars['main']['srcName']; ?>
</a><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('srcLink', ob_get_contents());ob_end_clean(); ?>
        <?php ob_start(); ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['main']['dstID'])), $this);?>
"><?php echo $this->_tpl_vars['main']['dstName']; ?>
</a><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('dstLink', ob_get_contents());ob_end_clean(); ?>
	<?php $this->assign('qParams', "reset=1&cid=".($this->_tpl_vars['main']['srcID'])."&oid=".($this->_tpl_vars['main']['dstID'])."&action=update&rgid=".($this->_tpl_vars['rgid'])); ?>
	<?php if ($this->_tpl_vars['gid']): ?><?php $this->assign('qParams', ($this->_tpl_vars['qParams'])."&gid=".($this->_tpl_vars['gid'])); ?><?php endif; ?>
        <?php ob_start(); ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/merge','q' => ($this->_tpl_vars['qParams'])), $this);?>
"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>merge<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('merge', ob_get_contents());ob_end_clean(); ?>
        <tr class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
          <td><?php echo $this->_tpl_vars['srcLink']; ?>
</td>
          <td><?php echo $this->_tpl_vars['dstLink']; ?>
</td>
          <td><?php echo $this->_tpl_vars['main']['weight']; ?>
</td>
          <td style="text-align: right;"><?php echo $this->_tpl_vars['merge']; ?>
</td>
        </tr>
    <?php endforeach; endif; unset($_from); ?>
  </table>
  <?php if ($this->_tpl_vars['cid']): ?>
    <table style="width: 45%; float: left; margin: 10px;">
      <tr class="columnheader"><th colspan="2"><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['main_contacts'][$this->_tpl_vars['cid']])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Merge %1 with<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th></tr>
      <?php $_from = $this->_tpl_vars['dupe_contacts'][$this->_tpl_vars['cid']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dupe_id'] => $this->_tpl_vars['dupe_name']):
?>
        <?php if ($this->_tpl_vars['dupe_name']): ?>
          <?php ob_start(); ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['dupe_id'])), $this);?>
"><?php echo $this->_tpl_vars['dupe_name']; ?>
</a><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('link', ob_get_contents());ob_end_clean(); ?>
          <?php ob_start(); ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/merge','q' => "reset=1&cid=".($this->_tpl_vars['cid'])."&oid=".($this->_tpl_vars['dupe_id'])), $this);?>
"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>merge<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('merge', ob_get_contents());ob_end_clean(); ?>
          <tr class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
"><td><?php echo $this->_tpl_vars['link']; ?>
</td><td style="text-align: right"><?php echo $this->_tpl_vars['merge']; ?>
</td></tr>
        <?php endif; ?>
      <?php endforeach; endif; unset($_from); ?>
    </table>
  <?php endif; ?>
</div>

<?php if ($this->_tpl_vars['context'] == 'search'): ?>
   <a href="<?php echo $this->_tpl_vars['backURL']; ?>
" class="button"><span>&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Done<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
<?php else: ?>
   <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/admin/dedupefind",'q' => "reset=1&rgid=".($this->_tpl_vars['rgid'])."&action=preview",'a' => 1), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('backURL', ob_get_contents());ob_end_clean(); ?>
   <a href="<?php echo $this->_tpl_vars['backURL']; ?>
" class="button"><span>&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Done<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
<?php endif; ?>
<div style="clear: both;"></div>
<?php else: ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Admin/Form/DedupeFind.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>