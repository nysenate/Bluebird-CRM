<?php /* Smarty version 2.6.26, created on 2010-08-19 16:07:48
         compiled from CRM/Admin/Form/Tag.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/Tag.tpl', 28, false),array('modifier', 'count', 'CRM/Admin/Form/Tag.tpl', 62, false),array('modifier', 'cat', 'CRM/Admin/Form/Tag.tpl', 66, false),)), $this); ?>
<div class="crm-block crm-form-block crm-tag-form-block">
<h3><?php if ($this->_tpl_vars['action'] == 1): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>New Tag <?php if ($this->_tpl_vars['isTagSet']): ?>Set<?php endif; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php elseif ($this->_tpl_vars['action'] == 2): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Tag <?php if ($this->_tpl_vars['isTagSet']): ?>Set<?php endif; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Tag <?php if ($this->_tpl_vars['isTagSet']): ?>Set<?php endif; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></h3>
    <?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2): ?>
    <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
    <table class="form-layout-compressed">
       <tr class="crm-tag-form-block-label">
          <td class="label"><?php echo $this->_tpl_vars['form']['name']['label']; ?>
</td>
          <td><?php echo $this->_tpl_vars['form']['name']['html']; ?>
</td>
       </tr>
       <tr class="crm-tag-form-block-description">
          <td class="label"><?php echo $this->_tpl_vars['form']['description']['label']; ?>
</td>
          <td><?php echo $this->_tpl_vars['form']['description']['html']; ?>
</td>
       </tr>
         <?php if ($this->_tpl_vars['form']['parent_id']['html']): ?>
       <tr class="crm-tag-form-block-parent_id">
 	  <td class="label"><?php echo $this->_tpl_vars['form']['parent_id']['label']; ?>
</td>
          <td><?php echo $this->_tpl_vars['form']['parent_id']['html']; ?>
</td>
       </tr>
	 <?php endif; ?>
       <tr class="crm-tag-form-block-used_for">	  
          <td class="label"><?php echo $this->_tpl_vars['form']['used_for']['label']; ?>
</td>
	  <td><?php echo $this->_tpl_vars['form']['used_for']['html']; ?>
 <br />
	        <span class="description">
	               <?php if ($this->_tpl_vars['is_parent']): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You can change the types of records which this tag can be used for by editing the 'Parent' tag.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	                <?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>What types of record(s) can this tag be used for?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	                <?php endif; ?>
	            </span>
	        </td>
        </tr>
        <tr class="crm-tag-form-block-is_reserved">
           <td class="label"><?php echo $this->_tpl_vars['form']['is_reserved']['label']; ?>
</td>
           <td><?php echo $this->_tpl_vars['form']['is_reserved']['html']; ?>
 <br /><span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Reserved tags can not be deleted. Users with 'administer reserved tags' permission can set or unset the reserved flag. You must uncheck 'Reserved' (and delete any child tags) before you can delete a tag.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 
           </td>
        </tr>
    </table>
        <?php if (count($this->_tpl_vars['parent_tags']) > 0): ?>
        <table class="form-layout-compressed">
            <tr><td><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Remove Parent?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label></td></tr>
            <?php $_from = $this->_tpl_vars['parent_tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tag_id'] => $this->_tpl_vars['ctag']):
?>
                <?php $this->assign('element_name', ((is_array($_tmp='remove_parent_tag_')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['tag_id']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['tag_id']))); ?>
                <tr><td>&nbsp;&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]['label']; ?>
</td></tr>
            <?php endforeach; endif; unset($_from); ?>
        </table><br />
        <?php endif; ?>
    <?php else: ?>
        <div class="status"><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['delName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to delete <b>%1</b> Tag?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><br /><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>This tag will be removed from any currently tagged contacts, and users will no longer be able to assign contacts to this tag.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
    <?php endif; ?>
    <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>