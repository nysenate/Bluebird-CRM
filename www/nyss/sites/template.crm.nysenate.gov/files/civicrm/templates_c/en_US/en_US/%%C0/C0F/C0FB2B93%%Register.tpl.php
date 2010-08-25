<?php /* Smarty version 2.6.26, created on 2010-08-09 23:59:50
         compiled from CRM/Report/Form/Register.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Report/Form/Register.tpl', 27, false),)), $this); ?>
<?php if ($this->_tpl_vars['action'] == 8): ?>
  <h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Report Template<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
<?php elseif ($this->_tpl_vars['action'] == 2): ?>
  <h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Report Template<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
<?php else: ?>
  <h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>New Report Template<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
<?php endif; ?>
<div class="crm-block crm-form-block crm-report-register-form-block">	
<?php if ($this->_tpl_vars['action'] == 8): ?> 
    <table class="form-layout">
    <tr class="buttons">
        <td><div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
        </td>
        <td></td>
    </tr>
    <tr>
        <td colspan=2>
        <div class="messages status"> 
		  <div class="icon inform-icon"></div> &nbsp; 
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>WARNING: Deleting this option will result in the loss of all Report related records which use the option. This may mean the loss of a substantial amount of data, and the action cannot be undone. Do you want to continue?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </div>        
        </td>
    </tr>
<?php else: ?>
  	
    <table class="form-layout">
        <tr class="buttons crm-report-register-form-block-buttons">
            <td><div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
            </td>
            <td></td>
        </tr>
        <tr class="crm-report-register-form-block-label">
            <td class="label"><?php echo $this->_tpl_vars['form']['label']['label']; ?>
</td>
            <td class="view-value"><?php echo $this->_tpl_vars['form']['label']['html']; ?>
 <br /><span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Report title appear in the display screen.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            </td>
        </tr>	   
        <tr class="crm-report-register-form-block-description">
            <td class="label"><?php echo $this->_tpl_vars['form']['description']['label']; ?>
</td>
            <td class="view-value"><?php echo $this->_tpl_vars['form']['description']['html']; ?>
 <br /><span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Report description appear in the display screen.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            </td>
        </tr>	   
        <tr class="crm-report-register-form-block-url">
            <td class="label"><?php echo $this->_tpl_vars['form']['value']['label']; ?>
</td>
            <td class="view-value"><?php echo $this->_tpl_vars['form']['value']['html']; ?>
 <br /><span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Report Url must be like "contribute/summary"<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            </td>
        </tr>
        <tr class="crm-report-register-form-block-class">
            <td class="label"><?php echo $this->_tpl_vars['form']['name']['label']; ?>
</td>
            <td class="view-value"><?php echo $this->_tpl_vars['form']['name']['html']; ?>
 <br /><span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Report Class must be present before adding the report here, e.g. 'CRM_Report_Form_Contribute_Summary'<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            </td>
        </tr>
        <tr class="crm-report-register-form-block-weight">
            <td class="label"><?php echo $this->_tpl_vars['form']['weight']['label']; ?>
</td>
            <td class="view-value"><?php echo $this->_tpl_vars['form']['weight']['html']; ?>
</td>
        </tr>
        <tr class="crm-report-register-form-block-component">
            <td class="label"><?php echo $this->_tpl_vars['form']['component_id']['label']; ?>
</td>
            <td class="view-value"><?php echo $this->_tpl_vars['form']['component_id']['html']; ?>
 <br /><span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Specify the Report if it is belongs to any component like "CiviContribute"<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            </td>
        </tr>
        <tr class="crm-report-register-form-block-is_active">
            <td class="label"><?php echo $this->_tpl_vars['form']['is_active']['label']; ?>
</td>
            <td class="view-value"><?php echo $this->_tpl_vars['form']['is_active']['html']; ?>
</td>
        </tr>  
<?php endif; ?> 
    <tr class="buttons crm-report-register-form-block-buttons">
        <td><div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
        </td>
        <td></td>
    </tr>
    </table>  
</div>