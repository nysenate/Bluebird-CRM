<?php /* Smarty version 2.6.26, created on 2010-07-07 15:33:06
         compiled from CRM/Contact/Form/ActionsButton.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/ActionsButton.tpl', 39, false),array('function', 'crmURL', 'CRM/Contact/Form/ActionsButton.tpl', 39, false),)), $this); ?>

<div id="crm-contact-actions-wrapper">
	<div id="crm-contact-actions-link"><span><div class="icon dropdown-icon"></div>Actions</span></div>
		<div class="ac_results" id="crm-contact-actions-list">
			<div class="crm-contact-actions-list-inner">
			  <div class="crm-contact_activities-list">
			  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Activity/Form/ActivityLinks.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			  </div>
			  
              <div class="crm-contact_print-list">
              <ul class="contact-print">
                  <li class="crm-contact-print">
                 		<a class="print" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Printer-friendly view of this page.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" href='<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/print','q' => "reset=1&print=1&cid=".($this->_tpl_vars['contactId'])), $this);?>
'">
                 		<span><div class="icon print-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Print Summary<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                 		</a>
                  </li>
                  <li><a class="vcard " title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>vCard record for this contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/vcard','q' => "reset=1&cid=".($this->_tpl_vars['contactId'])), $this);?>
"><span><div class="icon vcard-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>vCard<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></li>
                 <?php if ($this->_tpl_vars['dashboardURL']): ?>
                   <li class="crm-contact-dashboard">
                      <a href="<?php echo $this->_tpl_vars['dashboardURL']; ?>
" class="dashboard " title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>dashboard<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                       	<span><div class="icon dashboard-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact Dashboard<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                       </a>
                   </li>
                 <?php endif; ?>
                 <?php if ($this->_tpl_vars['userRecordUrl']): ?>
                   <li class="crm-contact-user-record">
                      <a href="<?php echo $this->_tpl_vars['userRecordUrl']; ?>
" class="user-record " title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>User Record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                         <span><div class="icon user-record-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>User Record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                      </a>
                   </li>
                 <?php endif; ?>
			  </ul>
			  </div>
			  <div class="crm-contact_actions-list">
			  <ul class="contact-actions">
			  	<?php $_from = $this->_tpl_vars['actionsMenuList']['moreActions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
					<?php if ($this->_tpl_vars['row']['href']): ?>
					<li class="crm-action-<?php echo $this->_tpl_vars['row']['ref']; ?>
">
						<a href="<?php echo $this->_tpl_vars['row']['href']; ?>
&cid=<?php echo $this->_tpl_vars['contactId']; ?>
" title="<?php echo $this->_tpl_vars['row']['title']; ?>
"><?php echo $this->_tpl_vars['row']['title']; ?>
</a>
					</li>
					<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
              </ul>
              </div>
			  
			  
			  <div class="clear"></div>
			</div>
		</div>
	</div>
<?php echo '
<script>

cj(\'body\').click(function() {
	 	$(\'#crm-contact-actions-list\').hide();
	 	});
	
	 cj(\'#crm-contact-actions-list\').click(function(event){
	     event.stopPropagation();
	 	});

cj(\'#crm-contact-actions-list li\').hover(
	function(){ cj(this).addClass(\'ac_over\');},
	function(){ cj(this).removeClass(\'ac_over\');}
	);

cj(\'#crm-contact-actions-link\').click(function(event) {
	cj(\'#crm-contact-actions-list\').toggle();
	event.stopPropagation();
	});

</script>
'; ?>
