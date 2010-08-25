<?php /* Smarty version 2.6.26, created on 2010-08-24 12:59:33
         compiled from CRM/Export/Form/Select.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Export/Form/Select.tpl', 31, false),array('function', 'help', 'CRM/Export/Form/Select.tpl', 63, false),)), $this); ?>
<div class="crm-block crm-form-block crm-export-form-block">

 <div id="help">
    <p><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><strong>Export PRIMARY fields</strong> provides the most commonly used data values. This includes primary address information, preferred phone and email. Select this option and click <strong>Continue</strong> to immediately generate and save the export file.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
    <p><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click <strong>Select fields for export</strong> and then <strong>Continue</strong> to choose a subset of fields for export. This option allows you to export multiple specific locations (Home, Work, etc.) as well as custom data. You can also save your selections as a 'field mapping' so you can use it again later.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
 </div>

 <h3>Export All or Selected Fields</h3>
 <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
 <div id="export-type">
  <div class="crm-section crm-exportOption-section">
    <h3><?php $this->_tag_stack[] = array('ts', array('count' => $this->_tpl_vars['totalSelectedRecords'],'plural' => '%count records selected for export.')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>One record selected for export.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
    <div class="content-no-label crm-content-exportOption">
        <?php echo $this->_tpl_vars['form']['exportOption']['html']; ?>

   </div>
  </div>
  
  <div id="map" class="crm-section crm-export-mapping-section">
      <?php if ($this->_tpl_vars['form']['mapping']): ?>
        <div class="label crm-label-export-mapping">
            <?php echo $this->_tpl_vars['form']['mapping']['label']; ?>

        </div>
        <div class="content crm-content-export-mapping">
            <?php echo $this->_tpl_vars['form']['mapping']['html']; ?>

        </div>
		<div class="clear"></div> 
      <?php endif; ?>
  </div>
  
  <?php if ($this->_tpl_vars['taskName'] == 'Export Contacts'): ?>
  <div class="crm-section crm-export-mergeOptions-section">
    <div class="label crm-label-mergeOptions"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Merge Options<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => "id-export_merge_options"), $this);?>
</div>
    <div class="content crm-content-mergeSameAddress">
        &nbsp;<?php echo $this->_tpl_vars['form']['merge_same_address']['html']; ?>

    </div>
    <div class="content crm-content-mergeSameHousehold">
        &nbsp;<?php echo $this->_tpl_vars['form']['merge_same_household']['html']; ?>

    </div>
	<div class="clear"></div> 
  </div>
  <?php endif; ?>
    
 </div>

 <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>
<?php echo '
  <script type="text/javascript">
     function showMappingOption( )
     {
	var element = document.getElementsByName("exportOption");

	if ( element[1].checked ) { 
	  show(\'map\');
        } else {
	  hide(\'map\');
	}
     } 
   showMappingOption( );
  </script>
'; ?>
