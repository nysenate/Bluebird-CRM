<?php /* Smarty version 2.6.26, created on 2010-08-20 11:57:42
         compiled from CRM/Contact/Form/Search/Builder.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'docURL', 'CRM/Contact/Form/Search/Builder.tpl', 28, false),array('function', 'help', 'CRM/Contact/Form/Search/Builder.tpl', 29, false),array('block', 'ts', 'CRM/Contact/Form/Search/Builder.tpl', 29, false),)), $this); ?>
<div class="messages help" id="help">
<?php ob_start(); ?><?php echo smarty_function_docURL(array('page' => 'Search Builder','text' => 'Search Builder Documentation'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('docLink', ob_get_contents());ob_end_clean(); ?>
<strong><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['docLink'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>IMPORTANT: Search Builder requires you to use specific formats for your search values. Review the %1 before building your first search.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></strong> <?php echo smarty_function_help(array('id' => 'builder-intro'), $this);?>

</div>

<div class="crm-form-block crm-search-form-block">
<div class="crm-accordion-wrapper crm-search_builder-accordion <?php if ($this->_tpl_vars['rows']): ?>crm-accordion-closed<?php else: ?>crm-accordion-open<?php endif; ?>">
 <div class="crm-accordion-header crm-master-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Search Criteria<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</div><!-- /.crm-accordion-header -->
<div class="crm-accordion-body">
<div id = "searchForm">	
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Search/table.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div class="clear"></div>
<div id="crm-submit-buttons">
    <?php echo $this->_tpl_vars['form']['buttons']['html']; ?>

</div>
</div>
</div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->
<?php if ($this->_tpl_vars['rowsEmpty'] || $this->_tpl_vars['rows']): ?>
<div class="crm-content-block">
<?php if ($this->_tpl_vars['rowsEmpty']): ?>
	<div class="crm-results-block crm-results-block-empty">
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Search/EmptyResults.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['rows']): ?>
	<div class="crm-results-block">
                
       <div class="crm-search-tasks">
       <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Search/ResultTasks.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
       </div>

              <div class="crm-search-results">
       <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Selector.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      </div>

    </div>
    
<?php endif; ?>
</div>
<?php endif; ?>
<?php echo $this->_tpl_vars['initHideBoxes']; ?>

<script type="text/javascript">
    var showBlock = new Array(<?php echo $this->_tpl_vars['showBlock']; ?>
);
    var hideBlock = new Array(<?php echo $this->_tpl_vars['hideBlock']; ?>
);

    on_load_init_blocks( showBlock, hideBlock );
</script>
<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>