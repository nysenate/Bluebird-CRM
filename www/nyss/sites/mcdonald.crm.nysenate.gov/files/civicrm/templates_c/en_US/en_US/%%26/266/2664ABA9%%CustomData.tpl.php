<?php /* Smarty version 2.6.26, created on 2010-08-24 14:47:44
         compiled from CRM/Contact/Form/Edit/CustomData.tpl */ ?>

<script type="text/javascript">var showTab = Array( );</script>

<?php $_from = $this->_tpl_vars['groupTree']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group_id'] => $this->_tpl_vars['cd_edit']):
?>    
	<div class="crm-accordion-wrapper crm-address-accordion crm-accordion-closed">
		<div class="crm-accordion-header">
			<div id="custom<?php echo $this->_tpl_vars['group_id']; ?>
" class="icon crm-accordion-pointer"></div> 
			<?php echo $this->_tpl_vars['cd_edit']['title']; ?>

			</div><!-- /.crm-accordion-header -->
			
			<div id="customData<?php echo $this->_tpl_vars['group_id']; ?>
" class="crm-accordion-body">
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/CustomData.tpl", 'smarty_include_vars' => array('formEdit' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</div>
		<script type="text/javascript">
			<?php if ($this->_tpl_vars['cd_edit']['collapse_display'] == 0): ?>
				var eleSpan          = "span#custom<?php echo $this->_tpl_vars['group_id']; ?>
";
				var eleDiv           = "div#customData<?php echo $this->_tpl_vars['group_id']; ?>
";
				showTab[<?php echo $this->_tpl_vars['group_id']; ?>
] = <?php echo '{"spanShow":eleSpan,"divShow":eleDiv}'; ?>
;
			<?php else: ?>
				showTab[<?php echo $this->_tpl_vars['group_id']; ?>
] = <?php echo '{"spanShow":""}'; ?>
;
			<?php endif; ?>
		</script>
	</div>
<?php endforeach; endif; unset($_from); ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/customData.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>