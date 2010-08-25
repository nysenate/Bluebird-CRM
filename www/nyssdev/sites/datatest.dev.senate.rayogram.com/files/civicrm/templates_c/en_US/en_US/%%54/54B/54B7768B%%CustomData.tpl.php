<?php /* Smarty version 2.6.26, created on 2010-05-27 18:30:00
         compiled from CRM/Contact/Form/Edit/Address/CustomData.tpl */ ?>

    <?php $_from = $this->_tpl_vars['address_groupTree'][$this->_tpl_vars['blockId']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group_id'] => $this->_tpl_vars['cd_edit']):
?>

<div id="<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_<?php echo $this->_tpl_vars['group_id']; ?>
_<?php echo $this->_tpl_vars['blockId']; ?>
" class="form-item">
<div class="crm-accordion-wrapper crm-accordion-inner crm-<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_<?php echo $this->_tpl_vars['group_id']; ?>
_<?php echo $this->_tpl_vars['blockId']; ?>
-accordion <?php if ($this->_tpl_vars['cd_edit']['collapse_display'] == 0): ?>crm-accordion-open<?php else: ?>crm-accordion-closed<?php endif; ?>">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
<?php echo $this->_tpl_vars['cd_edit']['title']; ?>


 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">

                <?php if ($this->_tpl_vars['cd_edit']['help_pre']): ?>
                    <div class="messages help"><?php echo $this->_tpl_vars['cd_edit']['help_pre']; ?>
</div>
                <?php endif; ?>
                <table class="form-layout-compressed">
                    <?php $_from = $this->_tpl_vars['cd_edit']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field_id'] => $this->_tpl_vars['element']):
?>
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/Address/CustomField.tpl", 'smarty_include_vars' => array()));
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

        <div id="custom_group_<?php echo $this->_tpl_vars['group_id']; ?>
_<?php echo $this->_tpl_vars['blockId']; ?>
"></div>
</div>
    <?php endforeach; endif; unset($_from); ?>