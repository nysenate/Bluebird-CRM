<?php /* Smarty version 2.6.26, created on 2010-08-20 15:39:54
         compiled from CRM/Contact/Form/Task/AddToGroup.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Task/AddToGroup.tpl', 30, false),)), $this); ?>
<div class="crm-block crm-form-block crm-contact-task-addtogroup-form-block">
<table class="form-layout">
    <?php if ($this->_tpl_vars['group']['id']): ?>
       <tr class="crm-contact-task-addtogroup-form-block-group_id">
          <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Group<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
          <td><?php echo $this->_tpl_vars['form']['group_id']['html']; ?>
</td>
       </tr>
    <?php else: ?>
        <tr><td><?php echo $this->_tpl_vars['form']['group_option']['html']; ?>
</td></tr>
        <tr id="id_existing_group">
            <td>
                <table class="form-layout">
                <tr><td class="label"><?php echo $this->_tpl_vars['form']['group_id']['label']; ?>
<span class="marker">*</span></td><td><?php echo $this->_tpl_vars['form']['group_id']['html']; ?>
</td></tr>
                </table>
            </td>
        </tr>
        <tr id="id_new_group" class="html-adjust">
            <td>
                <table class="form-layout">
                <tr class="crm-contact-task-addtogroup-form-block-title">
                   <td class="label"><?php echo $this->_tpl_vars['form']['title']['label']; ?>
<span class="marker">*</span></td>
                   <td><?php echo $this->_tpl_vars['form']['title']['html']; ?>
</td>
                <tr>
                <tr class="crm-contact-task-addtogroup-form-block-description">
                   <td class="label"><?php echo $this->_tpl_vars['form']['description']['label']; ?>
</td>
                   <td><?php echo $this->_tpl_vars['form']['description']['html']; ?>
</td></tr>
                <?php if ($this->_tpl_vars['form']['group_type']): ?>
                <tr class="crm-contact-task-addtogroup-form-block-group_type">
		    <td class="label"><?php echo $this->_tpl_vars['form']['group_type']['label']; ?>
</td>
                    <td><?php echo $this->_tpl_vars['form']['group_type']['html']; ?>
</td>
                </tr>
                <?php endif; ?>
                </table>
            </td>
        </tr>
    <?php endif; ?>
</table>
<table class="form-layout">
        <tr><td><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Task.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td></tr>
</table>
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/showHide.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if (! $this->_tpl_vars['group']['id']): ?>
<?php echo '
<script type="text/javascript">
showElements();
function showElements() {
    if ( document.getElementsByName(\'group_option\')[0].checked ) {
      cj(\'#id_existing_group\').show();
      cj(\'#id_new_group\').hide();
    } else {
      cj(\'#id_new_group\').show();
      cj(\'#id_existing_group\').hide();  
    }
}
</script>
'; ?>
 
<?php endif; ?>