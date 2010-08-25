<?php /* Smarty version 2.6.26, created on 2010-05-27 18:30:01
         compiled from CRM/Custom/Form/CustomData.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Custom/Form/CustomData.tpl', 39, false),)), $this); ?>
<?php if ($this->_tpl_vars['formEdit']): ?>
    <?php if ($this->_tpl_vars['cd_edit']['help_pre']): ?>
        <div class="messages help"><?php echo $this->_tpl_vars['cd_edit']['help_pre']; ?>
</div>
    <?php endif; ?>
    <table class="form-layout-compressed">
        <?php $_from = $this->_tpl_vars['cd_edit']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field_id'] => $this->_tpl_vars['element']):
?>
           <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/CustomField.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php endforeach; endif; unset($_from); ?>
    </table>
    <div class="spacer"></div>
    <?php if ($this->_tpl_vars['cd_edit']['help_post']): ?><div class="messages help"><?php echo $this->_tpl_vars['cd_edit']['help_post']; ?>
</div><?php endif; ?>
    <?php if ($this->_tpl_vars['cd_edit']['is_multiple'] && ( ( $this->_tpl_vars['cd_edit']['max_multiple'] == '' ) || ( $this->_tpl_vars['cd_edit']['max_multiple'] > 0 && $this->_tpl_vars['cd_edit']['max_multiple'] >= $this->_tpl_vars['cgCount'] ) )): ?>
        <div id="add-more-link-<?php echo $this->_tpl_vars['cgCount']; ?>
"><a href="javascript:buildCustomData('<?php echo $this->_tpl_vars['cd_edit']['extends']; ?>
',<?php if ($this->_tpl_vars['cd_edit']['subtype']): ?>'<?php echo $this->_tpl_vars['cd_edit']['subtype']; ?>
'<?php else: ?>'<?php echo $this->_tpl_vars['cd_edit']['extends_entity_column_id']; ?>
'<?php endif; ?>, '', <?php echo $this->_tpl_vars['cgCount']; ?>
, <?php echo $this->_tpl_vars['group_id']; ?>
, true );"><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['cd_edit']['title'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add another %1 record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></div>	
    <?php endif; ?>
<?php else: ?>
<?php $_from = $this->_tpl_vars['groupTree']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group_id'] => $this->_tpl_vars['cd_edit']):
?>    
 <div id="<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
" class="crm-accordion-wrapper crm-accordion_title-accordion <?php if ($this->_tpl_vars['cd_edit']['collapse_display']): ?>crm-accordion-closed<?php else: ?>crm-accordion-open<?php endif; ?>">
  <div class="crm-accordion-header">
   <div class="icon crm-accordion-pointer"></div>
    <?php echo $this->_tpl_vars['cd_edit']['title']; ?>

   </div><!-- /.crm-accordion-header -->
  <div id="<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
" class="crm-accordion-body">
            <?php if ($this->_tpl_vars['cd_edit']['help_pre']): ?>
                <div class="messages help"><?php echo $this->_tpl_vars['cd_edit']['help_pre']; ?>
</div>
            <?php endif; ?>
            <table class="form-layout-compressed">
                <?php $_from = $this->_tpl_vars['cd_edit']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field_id'] => $this->_tpl_vars['element']):
?>
                   <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/CustomField.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endforeach; endif; unset($_from); ?>
            </table>
	    <div class="spacer"></div>
            <?php if ($this->_tpl_vars['cd_edit']['help_post']): ?><div class="messages help"><?php echo $this->_tpl_vars['cd_edit']['help_post']; ?>
</div><?php endif; ?>
   </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
        <?php if ($this->_tpl_vars['cd_edit']['is_multiple'] && ( ( $this->_tpl_vars['cd_edit']['max_multiple'] == '' ) || ( $this->_tpl_vars['cd_edit']['max_multiple'] > 0 && $this->_tpl_vars['cd_edit']['max_multiple'] >= $this->_tpl_vars['cgCount'] ) )): ?>
            <div id="add-more-link-<?php echo $this->_tpl_vars['cgCount']; ?>
"><a href="javascript:buildCustomData('<?php echo $this->_tpl_vars['cd_edit']['extends']; ?>
',<?php if ($this->_tpl_vars['cd_edit']['subtype']): ?>'<?php echo $this->_tpl_vars['cd_edit']['subtype']; ?>
'<?php else: ?>'<?php echo $this->_tpl_vars['cd_edit']['extends_entity_column_id']; ?>
'<?php endif; ?>, '', <?php echo $this->_tpl_vars['cgCount']; ?>
, <?php echo $this->_tpl_vars['group_id']; ?>
, true );"><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['cd_edit']['title'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add another %1 record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></div>	
        <?php endif; ?>
    </div>
    <div id="custom_group_<?php echo $this->_tpl_vars['group_id']; ?>
_<?php echo $this->_tpl_vars['cgCount']; ?>
"></div>
<?php endforeach; endif; unset($_from); ?>
    <script type="text/javascript">
    <?php echo '
        cj(function() {
           cj().crmaccordions(); 
        });        
    '; ?>

    </script>
<?php endif; ?>