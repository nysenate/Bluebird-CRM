<?php /* Smarty version 2.6.26, created on 2010-08-24 15:37:12
         compiled from CRM/Activity/Form/Search.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Activity/Form/Search.tpl', 31, false),array('modifier', 'crmReplace', 'CRM/Activity/Form/Search.tpl', 39, false),)), $this); ?>
<div class="crm-form-block crm-search-form-block">
<div class="crm-accordion-wrapper crm-advanced_search_form-accordion <?php if ($this->_tpl_vars['rows']): ?>crm-accordion-closed<?php else: ?>crm-accordion-open<?php endif; ?>">
 <div class="crm-accordion-header crm-master-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Search Criteria<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
</div><!-- /.crm-accordion-header -->
<div class="crm-accordion-body">
  <div id="searchForm" class="form-item">
    <?php echo '<table class="form-layout"><tr><td class="font-size12pt" colspan="2">'; ?><?php echo $this->_tpl_vars['form']['sort_name']['label']; ?><?php echo '&nbsp;&nbsp;'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['sort_name']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'twenty') : smarty_modifier_crmReplace($_tmp, 'class', 'twenty')); ?><?php echo '&nbsp;&nbsp;&nbsp;'; ?><?php echo $this->_tpl_vars['form']['buttons']['html']; ?><?php echo '</td></tr>'; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Activity/Form/Search/Common.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '<tr><td colspan="2">'; ?><?php echo $this->_tpl_vars['form']['buttons']['html']; ?><?php echo '</td></tr></table>'; ?>

  </div>
</div>
</div>
</div>

<?php if ($this->_tpl_vars['rowsEmpty'] || $this->_tpl_vars['rows']): ?>
<div class="crm-content-block">
<?php if ($this->_tpl_vars['rowsEmpty']): ?>
	<div class="crm-results-block crm-results-block-empty">        
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Activity/Form/Search/EmptyResults.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['rows']): ?>
	<div class="crm-results-block">
    
              <div class="crm-search-tasks">
       <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/searchResultTasks.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</div>
       	   <div class="crm-search-results">
       <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Activity/Form/Selector.tpl", 'smarty_include_vars' => array('context' => 'Search')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</div>       
    </div>
<?php endif; ?>
</div>
<?php endif; ?>
<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>
