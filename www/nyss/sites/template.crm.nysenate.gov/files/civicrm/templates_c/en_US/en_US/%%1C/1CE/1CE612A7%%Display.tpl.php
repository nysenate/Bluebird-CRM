<?php /* Smarty version 2.6.26, created on 2010-08-02 11:58:19
         compiled from CRM/Admin/Form/Preferences/Display.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/Preferences/Display.tpl', 37, false),array('function', 'docURL', 'CRM/Admin/Form/Preferences/Display.tpl', 37, false),)), $this); ?>
<div class="crm-block crm-form-block crm-preferences-display-form-block"> 
 <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
     <table class="form-layout">
        <?php if ($this->_tpl_vars['form']['contact_view_options']['html']): ?>
	    <tr class="crm-preferences-display-form-block-contact_view_options">
               <td class="label"><?php echo $this->_tpl_vars['form']['contact_view_options']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['contact_view_options']['html']; ?>
</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select the <strong>tabs</strong> that should be displayed when viewing a contact record. EXAMPLE: If your organization does not keep track of 'Relationships', then un-check this option to simplify the screen display. Tabs for Contributions, Pledges, Memberships, Events, Grants and Cases are also hidden if the corresponding component is not enabled.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_docURL(array('page' => 'Enable Components'), $this);?>
</td>
            </tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['form']['contact_edit_options']['html']): ?>        		       
	        <tr class="crm-preferences-display-form-block-contact_edit_options">
               <td class="label"><?php echo $this->_tpl_vars['form']['contact_edit_options']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['contact_edit_options']['html']; ?>
</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select the sections that should be included when adding or editing a contact record. EXAMPLE: If your organization does not record Gender and Birth Date for individuals, then simplify the form by un-checking this option.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
            </tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['form']['advanced_search_options']['html']): ?>
            <tr class="crm-preferences-display-form-block-advanced_search_options">
               <td class="label"><?php echo $this->_tpl_vars['form']['advanced_search_options']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['advanced_search_options']['html']; ?>
</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select the sections that should be included in the Basic and Advanced Search forms. EXAMPLE: If you don't track Relationships - then you do not need this section included in the advanced search form. Simplify the form by un-checking this option.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
               </td>
            </tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['form']['user_dashboard_options']['html']): ?>
            <tr class="crm-preferences-display-form-block-user_dashboard_options">
               <td class="label"><?php echo $this->_tpl_vars['form']['user_dashboard_options']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['user_dashboard_options']['html']; ?>
</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select the sections that should be included in the Contact Dashboard. EXAMPLE: If you don't want constituents to view their own contribution history, un-check that option.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
               </td>
            </tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['form']['wysiwyg_editor']['html']): ?>
            <tr class="crm-preferences-display-form-block-wysiwyg_editor">
               <td class="label"><?php echo $this->_tpl_vars['form']['wysiwyg_editor']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['wysiwyg_editor']['html']; ?>
</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select the HTML WYSIWYG Editor provided for fields that allow HTML formatting. Select 'Textarea' if you don't want to provide a WYSIWYG Editor (users will type text and / or HTML code into plain text fields).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
               </td>
            </tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['form']['display_name_format']['html']): ?>
            <tr class="crm-preferences-display-form-block-display_name_format" >
               <td class="label"><?php echo $this->_tpl_vars['form']['display_name_format']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['display_name_format']['html']; ?>
</td>
            </tr>
            <tr class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Display name format for individual contact display names.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
            </tr>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['form']['sort_name_format']['html']): ?>
            <tr class="crm-preferences-display-form-block-sort_name_format">
               <td class="label"><?php echo $this->_tpl_vars['form']['sort_name_format']['label']; ?>
</td>
               <td><?php echo $this->_tpl_vars['form']['sort_name_format']['html']; ?>
</td>
            </tr>
            <tr  class="crm-preferences-display-form-block-description">
               <td>&nbsp;</td>
               <td class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Sort name format for individual contact display names.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
            </tr>
          </table>
	<?php endif; ?>
    <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
   </div>