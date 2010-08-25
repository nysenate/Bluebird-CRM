<?php /* Smarty version 2.6.26, created on 2010-06-10 16:27:51
         compiled from CRM/Export/Form/Select.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Export/Form/Select.tpl', 30, false),)), $this); ?>

<div id="help">
<p><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><strong>Export PRIMARY fields</strong> provides the most commonly used data values. This includes primary address information, preferred phone and email.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
<p><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click <strong>Select fields for export</strong> and then <strong>Continue</strong> to choose a subset of fields for export. This option allows you to export multiple specific locations (Home, Work, etc.) as well as custom data. You can also save your selections as a 'field mapping' so you can use it again later.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
</div>

<div class="crm-form-block crm-block">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/WizardHeader.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<fieldset>
  <div id="export-type" class="form-item">
    <dl>
        <dd>
         <?php $this->_tag_stack[] = array('ts', array('count' => $this->_tpl_vars['totalSelectedRecords'],'plural' => '%count records selected for export.')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>One record selected for export.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </dd> 
        <dd><?php echo $this->_tpl_vars['form']['exportOption']['html']; ?>
</dd>
	<dd><?php echo $this->_tpl_vars['form']['export']['html']; ?>
</dd>
	<dd><?php echo $this->_tpl_vars['form']['exportLabel']['html']; ?>
</dd>
    </dl>
      <div id="map">
       <?php if ($this->_tpl_vars['form']['mapping']): ?>
            <table class="form-layout-compressed">
            <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><?php echo $this->_tpl_vars['form']['mapping']['label']; ?>
 &nbsp; <?php echo $this->_tpl_vars['form']['mapping']['html']; ?>
</td></tr></table>
       <?php endif; ?>
      </div>
  </div>
</fieldset>

<div id="crm-submit-buttons">
    <?php echo $this->_tpl_vars['form']['buttons']['html']; ?>

</div>
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
