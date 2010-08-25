<?php /* Smarty version 2.6.26, created on 2010-08-24 15:11:37
         compiled from CRM/Case/Form/Search/Common.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Case/Form/Search/Common.tpl', 30, false),array('function', 'cycle', 'CRM/Case/Form/Search/Common.tpl', 34, false),)), $this); ?>
<?php if ($this->_tpl_vars['notConfigured']): ?>     <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Case/Page/ConfigureError.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
<tr>
  <td class="crm-case-common-form-block-case_type" width="25%"><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
    <br />
      <div class="listing-box" style="width: auto; height: 120px">
       <?php $_from = $this->_tpl_vars['form']['case_type_id']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['case_type_id_val']):
?>
        <div class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
                <?php echo $this->_tpl_vars['case_type_id_val']['html']; ?>

        </div>
      <?php endforeach; endif; unset($_from); ?>
      </div><br />
  </td>
  
  <td class="crm-case-common-form-block-case_status_id" width="25%">
    <?php echo $this->_tpl_vars['form']['case_status_id']['label']; ?>
<br /> 
    <?php echo $this->_tpl_vars['form']['case_status_id']['html']; ?>
<br /><br />	
    <?php if ($this->_tpl_vars['accessAllCases']): ?>
    <?php echo $this->_tpl_vars['form']['case_owner']['html']; ?>
 <span class="crm-clear-link">(<a href="javascript:unselectRadio('case_owner', '<?php echo $this->_tpl_vars['form']['formName']; ?>
')"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>clear<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>)</span><br />
    <?php endif; ?>
    <?php if ($this->_tpl_vars['form']['case_deleted']): ?>	
        <?php echo $this->_tpl_vars['form']['case_deleted']['html']; ?>
	
        <?php echo $this->_tpl_vars['form']['case_deleted']['label']; ?>
	
    <?php endif; ?>
  </td>
  <?php if ($this->_tpl_vars['form']['case_tags']): ?>
  <td class="crm-case-common-form-block-case_tags">
  <label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Tag(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label>
    <div id="Tag" class="listing-box">
      <?php $_from = $this->_tpl_vars['form']['case_tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tag_val']):
?> 
        <div class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
        	<?php echo $this->_tpl_vars['tag_val']['html']; ?>
 
        </div>
      <?php endforeach; endif; unset($_from); ?>
  </td>
<?php endif; ?>
</tr>     
<?php endif; ?>