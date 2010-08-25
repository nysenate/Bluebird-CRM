<?php /* Smarty version 2.6.26, created on 2010-08-24 16:23:39
         compiled from CRM/Core/DateRange.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'CRM/Core/DateRange.tpl', 27, false),)), $this); ?>
<?php $this->assign('relativeName', ((is_array($_tmp=$this->_tpl_vars['fieldName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_relative') : smarty_modifier_cat($_tmp, '_relative'))); ?>
<td ><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['relativeName']]['html']; ?>
</td>
<td>   
    <span id="absolute_<?php echo $this->_tpl_vars['relativeName']; ?>
"> 
        <?php $this->assign('fromName', ((is_array($_tmp=$this->_tpl_vars['fieldName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_from') : smarty_modifier_cat($_tmp, '_from'))); ?>
        <?php echo $this->_tpl_vars['form'][$this->_tpl_vars['fromName']]['label']; ?>

        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => $this->_tpl_vars['fromName'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 
        <?php $this->assign('toName', ((is_array($_tmp=$this->_tpl_vars['fieldName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_to') : smarty_modifier_cat($_tmp, '_to'))); ?>
        <?php echo $this->_tpl_vars['form'][$this->_tpl_vars['toName']]['label']; ?>

        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => $this->_tpl_vars['toName'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 
    </span>   
            
</td>
<?php echo '
<script type="text/javascript">
    var val       = document.getElementById("'; ?>
<?php echo $this->_tpl_vars['relativeName']; ?>
<?php echo '").value;
    var fieldName = "'; ?>
<?php echo $this->_tpl_vars['relativeName']; ?>
<?php echo '";
    showAbsoluteRange( val, fieldName );

    function showAbsoluteRange( val, fieldName ) {
        if ( val == "0" ) {
            cj(\'#absolute_\'+ fieldName).show();
        } else {
            cj(\'#absolute_\'+ fieldName).hide();
        }
    }
</script>
'; ?>
        