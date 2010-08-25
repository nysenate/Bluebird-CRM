<?php /* Smarty version 2.6.26, created on 2010-08-10 00:11:53
         compiled from CRM/Report/Form/Instance.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Report/Form/Instance.tpl', 26, false),array('function', 'help', 'CRM/Report/Form/Instance.tpl', 29, false),array('modifier', 'crmReplace', 'CRM/Report/Form/Instance.tpl', 51, false),)), $this); ?>
<h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>General Settings<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
<table class="form-layout">
    <tr class="crm-report-instanceForm-form-block-title">
        <td class="report-label" width="20%"><?php echo $this->_tpl_vars['form']['title']['label']; ?>
 <?php echo smarty_function_help(array('id' => "id-report_title",'file' => "CRM/Report/Form/Settings.hlp"), $this);?>
</td>
        <td ><?php echo $this->_tpl_vars['form']['title']['html']; ?>
</td>
    </tr>
    <tr class="crm-report-instanceForm-form-block-description">
        <td class="report-label" width="20%"><?php echo $this->_tpl_vars['form']['description']['label']; ?>
</td>
        <td><?php echo $this->_tpl_vars['form']['description']['html']; ?>
</td>
    </tr>
    <tr class="crm-report-instanceForm-form-block-report_header">
        <td class="report-label" width="20%"><?php echo $this->_tpl_vars['form']['report_header']['label']; ?>
<?php echo smarty_function_help(array('id' => "id-report_header",'file' => "CRM/Report/Form/Settings.hlp"), $this);?>
</td>
        <td><?php echo $this->_tpl_vars['form']['report_header']['html']; ?>
</td>
    </tr>
    <tr class="crm-report-instanceForm-form-block-report_footer">
        <td class="report-label" width="20%"><?php echo $this->_tpl_vars['form']['report_footer']['label']; ?>
</td>
        <td><?php echo $this->_tpl_vars['form']['report_footer']['html']; ?>
</td>
    </tr>
</table>
<br/>

<h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email Delivery Settings<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => "id-email_settings",'file' => "CRM/Report/Form/Settings.hlp"), $this);?>
</h3>
<table class="form-layout">
    <tr class="crm-report-instanceForm-form-block-email_subject">
        <td class="report-label" width="20%"><?php echo $this->_tpl_vars['form']['email_subject']['label']; ?>
</td>
        <td><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['email_subject']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
    </tr>
    <tr class="crm-report-instanceForm-form-block-email_to">
        <td class="report-label"><?php echo $this->_tpl_vars['form']['email_to']['label']; ?>
</td>
        <td><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['email_to']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
    </tr>
    <tr class="crm-report-instanceForm-form-block-email_cc">
        <td class="report-label"><?php echo $this->_tpl_vars['form']['email_cc']['label']; ?>
</td>
        <td><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['email_cc']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
    </tr> 
</table>
<br/>

<h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Other Settings<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
<table class="form-layout">
    <tr class="crm-report-instanceForm-form-block-is_navigation">
	<td class="report-label"><?php echo $this->_tpl_vars['form']['is_navigation']['label']; ?>
</td>
        <td><?php echo $this->_tpl_vars['form']['is_navigation']['html']; ?>
<br />
            <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>All report instances are automatically included in the Report Listing page. Check this box to also add this report to the navigation menu.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
        </td>
    </tr>
    <tr class="crm-report-instanceForm-form-block-parent_id" id="navigation_menu">
	<td class="report-label"><?php echo $this->_tpl_vars['form']['parent_id']['label']; ?>
 <?php echo smarty_function_help(array('id' => "id-parent",'file' => "CRM/Admin/Form/Navigation.hlp"), $this);?>
</td>
	<td><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['parent_id']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
    </tr>
    <?php if ($this->_tpl_vars['config']->userFramework != 'Joomla'): ?>
        <tr class="crm-report-instanceForm-form-block-permission">
            <td class="report-label" width="20%"><?php echo $this->_tpl_vars['form']['permission']['label']; ?>
 <?php echo smarty_function_help(array('id' => "id-report_perms",'file' => "CRM/Report/Form/Settings.hlp"), $this);?>
</td>
            <td><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['permission']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
        </tr>
    <?php endif; ?>
    <tr class="crm-report-instanceForm-form-block-addToDashboard">
	    <td class="report-label"><?php echo $this->_tpl_vars['form']['addToDashboard']['label']; ?>
 <?php echo smarty_function_help(array('id' => "id-dash_avail",'file' => "CRM/Report/Form/Settings.hlp"), $this);?>
</td>
        <td><?php echo $this->_tpl_vars['form']['addToDashboard']['html']; ?>

            <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Users with appropriate permissions can add this report to their dashboard.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
        </td>
    </tr>
</table>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/showHideByFieldValue.tpl", 'smarty_include_vars' => array('trigger_field_id' => 'is_navigation','trigger_value' => "",'target_element_id' => 'navigation_menu','target_element_type' => "table-row",'field_type' => 'radio','invert' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['is_navigation']): ?>
 <script type="text/javascript">
     document.getElementById('is_navigation').checked = true;
     showHideByValue('is_navigation','','navigation_menu','table-row','radio',false);
 </script>
<?php endif; ?>

<?php echo '
<script type="text/javascript">
    cj( function(){
        var formName = '; ?>
"<?php echo $this->_tpl_vars['form']['formName']; ?>
"<?php echo ';
        cj(\'#_qf_\' + formName + \'_submit_save\').click (
            function(){
                if ( cj(\'#is_navigation\').attr(\'checked\') && cj(\'#parent_id\').val() == \'\') {
                    var confirmMsg = '; ?>
'<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You have chosen to include this report in the Navigation Menu without selecting a Parent Menu item from the dropdown. This will add the report to the top level menu bar. Are you sure you want to continue?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>'<?php echo '
                    return confirm(confirmMsg);                    
                }
            }
        );        
    });
</script>
'; ?>