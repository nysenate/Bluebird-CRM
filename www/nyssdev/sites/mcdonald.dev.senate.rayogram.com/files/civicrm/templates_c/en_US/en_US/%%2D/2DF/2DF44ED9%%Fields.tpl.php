<?php /* Smarty version 2.6.26, created on 2010-06-23 10:43:17
         compiled from CRM/Report/Form/Fields.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Report/Form/Fields.tpl', 31, false),array('modifier', 'cat', 'CRM/Report/Form/Fields.tpl', 51, false),)), $this); ?>
<?php if (! $this->_tpl_vars['printOnly']): ?> <div <?php if (! $this->_tpl_vars['criteriaForm']): ?>style="display: none;"<?php endif; ?>> <div class="crm-accordion-wrapper crm-report_criteria-accordion crm-accordion_title-accordion <?php if ($this->_tpl_vars['rows']): ?>crm-accordion-closed<?php else: ?>crm-accordion-open<?php endif; ?>">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
  	<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Report Criteria<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
   </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">	
        <div id="id_<?php echo $this->_tpl_vars['formTpl']; ?>
">                 <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Report/Form/Criteria.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </div>   </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->       
</div> 
<?php if (( $this->_tpl_vars['instanceForm'] && $this->_tpl_vars['rows'] ) || $this->_tpl_vars['instanceFormError']): ?> <div class="crm-accordion-wrapper crm-report_setting-accordion crm-accordion_title-accordion <?php if ($this->_tpl_vars['rows']): ?>crm-accordion-closed<?php else: ?>crm-accordion-open<?php endif; ?>">
 <div class="crm-accordion-header" <?php if ($this->_tpl_vars['updateReportButton']): ?> onclick="hide('update-button'); return false;" <?php endif; ?> >
  <div class="icon crm-accordion-pointer"></div> 
  	<?php if ($this->_tpl_vars['mode'] == 'template'): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Create Report<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Report Settings<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?>
     </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
        <div id="id_<?php echo $this->_tpl_vars['instanceForm']; ?>
">
                <div id="instanceForm">
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Report/Form/Instance.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                    <?php $this->assign('save', ((is_array($_tmp=((is_array($_tmp='_qf_')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['form']['formName']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['form']['formName'])))) ? $this->_run_mod_handler('cat', true, $_tmp, '_submit_save') : smarty_modifier_cat($_tmp, '_submit_save'))); ?>
                        <div class="crm-submit-buttons">
                            <?php echo $this->_tpl_vars['form'][$this->_tpl_vars['save']]['html']; ?>

                        </div>
                </div>
        </div>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->    <?php endif; ?>     
    <?php if ($this->_tpl_vars['updateReportButton']): ?>
    <div id="update-button" class="crm-submit-buttons">
        &nbsp;&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['save']]['html']; ?>
 
    </div>           
    <?php endif; ?>

<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>


<?php endif; ?> 