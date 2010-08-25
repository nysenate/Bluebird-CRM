<?php /* Smarty version 2.6.26, created on 2010-05-27 13:01:35
         compiled from CRM/Admin/Form/Preferences/Address.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/Preferences/Address.tpl', 29, false),array('modifier', 'crmReplace', 'CRM/Admin/Form/Preferences/Address.tpl', 33, false),array('function', 'help', 'CRM/Admin/Form/Preferences/Address.tpl', 34, false),)), $this); ?>
<div class="crm-block crm-form-block crm-address-form-block">
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
<div class="form-item">
    <br /><fieldset><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Mailing Labels<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
        <table class="form-layout">
    		<tr class="crm-address-form-block-mailing_format">
    		    <td class="label"><?php echo $this->_tpl_vars['form']['mailing_format']['label']; ?>
</td>
    		    <td><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['mailing_format']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge12') : smarty_modifier_crmReplace($_tmp, 'class', 'huge12')); ?>
<br />
    			<span class="description"><?php $this->_tag_stack[] = array('ts', array('1' => "&#123;contact.state_province&#125;",'2' => "&#123;contact.state_province_name&#125;")); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Address format for mailing labels. Use the %1 token for state/province abbreviation or %2 for full name.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo smarty_function_help(array('id' => 'label-tokens'), $this);?>
</span>
    	            </td>
    		</tr>
    	</table>
    </fieldset>

    <fieldset><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Address Display<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
        <table class="form-layout">
    	    <tr class="crm-address-form-block-address_format">
    	        <td class="label"><?php echo $this->_tpl_vars['form']['address_format']['label']; ?>
</td>
    	        <td><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['address_format']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge12') : smarty_modifier_crmReplace($_tmp, 'class', 'huge12')); ?>
<br />
    	            <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Format for displaying addresses in the Contact Summary and Event Information screens.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><br /><?php $this->_tag_stack[] = array('ts', array('1' => "&#123;contact.state_province&#125;",'2' => "&#123;contact.state_province_name&#125;")); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Use %1 for state/province abbreviation or %2 for state province name.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo smarty_function_help(array('id' => 'address-tokens'), $this);?>
</span>
    	        </td>
    	    </tr>
    	</table>
    </fieldset>
		
    <fieldset><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Address Editing<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
        <table class="form-layout">
             <tr class="crm-address-form-block-address_options">
                <td class="label"><?php echo $this->_tpl_vars['form']['address_options']['label']; ?>

                <td><?php echo $this->_tpl_vars['form']['address_options']['html']; ?>
<br />
        	    <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select the fields to be included when editing a contact or event address.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                </td>
             </tr>
        </table>
    </fieldset>

    <fieldset><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Address Standardization<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
        <table class="form-layout">
             <tr class="crm-address-form-block-description">
                <td colspan="2">
    	            <span class="description"><?php $this->_tag_stack[] = array('ts', array('1' => "http://www.usps.com/webtools/address.htm")); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>CiviCRM includes an optional plugin for interfacing the the United States Postal Services (USPS) Address Standardization web service. You must register to use the USPS service at <a href='%1' target='_blank'>%1</a>. If you are approved, they will provide you with a User ID and the URL for the service.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
    	        </td>
            </tr>
            <tr class="crm-address-form-block-address_standardization_provider">
            	<td class="label"><?php echo $this->_tpl_vars['form']['address_standardization_provider']['label']; ?>
</td>
            	<td><?php echo $this->_tpl_vars['form']['address_standardization_provider']['html']; ?>
<br />
            	<span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Address Standardization Provider. Currently, only 'USPS' is supported.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                </td>
            </tr>
            <tr class="crm-address-form-block-address_standardization_userid">
            	<td class="label"><?php echo $this->_tpl_vars['form']['address_standardization_userid']['label']; ?>

            	<td><?php echo $this->_tpl_vars['form']['address_standardization_userid']['html']; ?>
<br />
            	<span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>USPS-provided User ID.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                </td>
            </tr>
            <tr class="crm-address-form-block-address_standardization_url">
            	<td class="label"><?php echo $this->_tpl_vars['form']['address_standardization_url']['label']; ?>

            	<td><?php echo $this->_tpl_vars['form']['address_standardization_url']['html']; ?>
<br />
            	<span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>USPS-provided web service URL.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            	</td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>