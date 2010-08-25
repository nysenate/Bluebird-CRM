<?php /* Smarty version 2.6.26, created on 2010-05-27 18:09:28
         compiled from CRM/Contact/Page/View/Note.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Page/View/Note.tpl', 30, false),array('modifier', 'crmDate', 'CRM/Contact/Page/View/Note.tpl', 33, false),array('modifier', 'nl2br', 'CRM/Contact/Page/View/Note.tpl', 34, false),array('modifier', 'mb_truncate', 'CRM/Contact/Page/View/Note.tpl', 100, false),array('modifier', 'count_characters', 'CRM/Contact/Page/View/Note.tpl', 102, false),array('function', 'crmURL', 'CRM/Contact/Page/View/Note.tpl', 35, false),array('function', 'cycle', 'CRM/Contact/Page/View/Note.tpl', 98, false),)), $this); ?>
<div class="view-content">
<?php if ($this->_tpl_vars['action'] == 4): ?>    <?php if ($this->_tpl_vars['notes']): ?>
        <fieldset>
          <legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>View Note<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
          <table class="view-layout">
            <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Subject<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo $this->_tpl_vars['note']['subject']; ?>
</td></tr>
            <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Date:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['note']['modified_date'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
</td></tr>
            <tr><td class="label"></td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['note']['note'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td></tr>
            <tr><td></td><td><input type="button" name='cancel' value="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Done<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" onclick="location.href='<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => 'action=browse&selectedChild=note'), $this);?>
';"/></td></tr>        
          </table>
        </fieldset>
        <?php endif; ?>
<?php elseif ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2): ?> 	<div class="crm-form-block">
	<div class="section note-subject-section no-label">

	 	<div class="content">
	 	   <?php echo $this->_tpl_vars['form']['subject']['label']; ?>
 <?php echo $this->_tpl_vars['form']['subject']['html']; ?>
 
	 	</div>
	 	<div class="clear"></div> 
	</div>
	<div class="section note-body-section no-label">
	 <div class="content">
	    <?php echo $this->_tpl_vars['form']['note']['html']; ?>

	 </div>
	 <div class="clear"></div> 
	</div>
	<div class="section note-buttons-section no-label">
	 <div class="content">
	    <?php echo $this->_tpl_vars['form']['buttons']['html']; ?>

	 </div>
	 <div class="clear"></div> 
	</div>
    </div>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formNavigate.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
<?php if (( $this->_tpl_vars['action'] == 8 )): ?>
<fieldset><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Note<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
<div class=status><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['notes'][$this->_tpl_vars['id']]['note'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to delete the note '%1'?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
<dl><dt></dt><dd><?php echo $this->_tpl_vars['form']['buttons']['html']; ?>
</dd></dl>
</fieldset>

<?php endif; ?>

<?php if ($this->_tpl_vars['permission'] == 'edit' && ( $this->_tpl_vars['action'] == 16 || $this->_tpl_vars['action'] == 4 || $this->_tpl_vars['action'] == 8 )): ?>
   <div class="action-link">
	 <a accesskey="N" href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/note','q' => "cid=".($this->_tpl_vars['contactId'])."&action=add"), $this);?>
" class="button"><span><div class="icon add-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add Note<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
   </div>
   <div class="clear"></div>
<?php endif; ?>
<div class="crm-content-block">

<?php if ($this->_tpl_vars['notes']): ?>
<div class="crm-results-block">
    <h3>Notes</h3>
<div id="notes">
    <?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jsortable.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '<table id="options" class="display"><thead><tr><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Note'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Subject'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Date'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Created By'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th></th></tr></thead>'; ?><?php $_from = $this->_tpl_vars['notes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['note']):
?><?php echo '<tr id="cnote_'; ?><?php echo $this->_tpl_vars['note']['id']; ?><?php echo '" class="'; ?><?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?><?php echo '"><td>'; ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['note']['note'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)))) ? $this->_run_mod_handler('mb_truncate', true, $_tmp, 80, "...", true) : smarty_modifier_mb_truncate($_tmp, 80, "...", true)); ?><?php echo ''; ?><?php echo ''; ?><?php $this->assign('noteSize', ((is_array($_tmp=$this->_tpl_vars['note']['note'])) ? $this->_run_mod_handler('count_characters', true, $_tmp, true) : smarty_modifier_count_characters($_tmp, true))); ?><?php echo ''; ?><?php if ($this->_tpl_vars['noteSize'] > 80): ?><?php echo '<a href="'; ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/note','q' => "action=view&selectedChild=note&reset=1&cid=".($this->_tpl_vars['contactId'])."&id=".($this->_tpl_vars['note']['id'])), $this);?><?php echo '">'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo '(more)'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a>'; ?><?php endif; ?><?php echo '</td><td>'; ?><?php echo $this->_tpl_vars['note']['subject']; ?><?php echo '</td><td>'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['note']['modified_date'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?><?php echo '</td><td><a href="'; ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['note']['contact_id'])), $this);?><?php echo '">'; ?><?php echo $this->_tpl_vars['note']['createdBy']; ?><?php echo '</a></td><td class="nowrap">'; ?><?php echo $this->_tpl_vars['note']['action']; ?><?php echo '</td></tr>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</table>'; ?>

 </div>
</div>
<?php elseif (! ( $this->_tpl_vars['action'] == 1 )): ?>
   <div class="messages status">
    <dl>
        <dt><div class="icon inform-icon"></div></dt>
        <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/note','q' => "cid=".($this->_tpl_vars['contactId'])."&action=add"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('crmURL', ob_get_contents());ob_end_clean(); ?>
        <dd><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['crmURL'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There are no Notes for this contact. You can <a accesskey="N" href='%1'>add one</a>.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></dd>
    </dl>
   </div>
<?php endif; ?>
</div>
</div>