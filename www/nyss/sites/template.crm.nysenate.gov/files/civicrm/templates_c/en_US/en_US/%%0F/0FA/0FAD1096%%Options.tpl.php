<?php /* Smarty version 2.6.26, created on 2010-08-10 15:33:16
         compiled from CRM/Admin/Form/Options.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/Options.tpl', 27, false),array('function', 'docURL', 'CRM/Admin/Form/Options.tpl', 41, false),)), $this); ?>
<h3><?php if ($this->_tpl_vars['action'] == 1): ?><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['GName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>New %1 Option<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php elseif ($this->_tpl_vars['action'] == 8): ?><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['GName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete %1 Option<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['GName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit %1 Option<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></h3>
<div class="crm-block crm-form-block crm-admin-options-form-block">
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>		      
	<?php if ($this->_tpl_vars['action'] == 8): ?>
      <div class="messages status">
        <div class="icon inform-icon"></div>
             <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['GName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>WARNING: Deleting this option will result in the loss of all %1 related records which use the option.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>This may mean the loss of a substantial amount of data, and the action cannot be undone.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Do you want to continue?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
      </div>
    <?php else: ?>
    <table class="form-layout-compressed">
        <?php if ($this->_tpl_vars['gName'] == 'custom_search'): ?> 
           <tr class="crm-admin-options-form-block-custom_search_path">
             <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Custom Search Path<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
             <td><?php echo $this->_tpl_vars['form']['label']['html']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Enter the "class path" for this custom search here.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_docURL(array('page' => 'Custom Search Components'), $this);?>

             </td>
           </tr>
        <?php elseif ($this->_tpl_vars['gName'] == 'from_email_address'): ?> 
           <tr class="crm-admin-options-form-block-from_email_address">
             <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>FROM Email Address<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php if ($this->_tpl_vars['action'] == 2): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'CRM/Core/I18n/Dialog.tpl', 'smarty_include_vars' => array('table' => 'civicrm_option_value','field' => 'label','id' => $this->_tpl_vars['id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?></td>
             <td><?php echo $this->_tpl_vars['form']['label']['html']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Include double-quotes (&quot;) around the name and angle-brackets (&lt; &gt;) around the email address.<br />EXAMPLE: <em>&quot;Client Services&quot; &lt;clientservices@example.org&gt;</em><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><span>
             </td>
           </tr>
        <?php elseif ($this->_tpl_vars['gName'] == 'redaction_rule'): ?> 
           <tr class="crm-admin-options-form-block-expression">
             <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Match Value or Expression<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php if ($this->_tpl_vars['action'] == 2): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'CRM/Core/I18n/Dialog.tpl', 'smarty_include_vars' => array('table' => 'civicrm_option_value','field' => 'label','id' => $this->_tpl_vars['id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?></td>
             <td><?php echo $this->_tpl_vars['form']['label']['html']; ?>
<br />
                <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>A "string value" or regular expression to be redacted (replaced).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
             </td>
           </tr>
        <?php else: ?> 
           <tr class="crm-admin-options-form-block-label">
             <td class="label"><?php echo $this->_tpl_vars['form']['label']['label']; ?>
 <?php if ($this->_tpl_vars['action'] == 2): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'CRM/Core/I18n/Dialog.tpl', 'smarty_include_vars' => array('table' => 'civicrm_option_value','field' => 'label','id' => $this->_tpl_vars['id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?></td>
             <td class="html-adjust"><?php echo $this->_tpl_vars['form']['label']['html']; ?>
<br />
               <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The option label is displayed to users.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
             </td>
           </tr>
        <?php endif; ?>
    	<?php if ($this->_tpl_vars['gName'] == 'case_status'): ?> 
            <tr class="crm-admin-options-form-block-grouping">
	            <td class="label"><?php echo $this->_tpl_vars['form']['grouping']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['grouping']['html']; ?>
</td>
            </tr>
	    <?php endif; ?>
        <?php if ($this->_tpl_vars['gName'] == 'custom_search'): ?>
           <tr class="crm-admin-options-form-block-search_title">
             <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Search Title<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
             <td><?php echo $this->_tpl_vars['form']['description']['html']; ?>
<br />
               <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>This title is displayed to users in the Custom Search listings.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
             </td>
           </tr>
        <?php else: ?>
           <?php if ($this->_tpl_vars['gName'] == 'redaction_rule'): ?>
              <tr class="crm-admin-options-form-block-replacement">
                 <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Replacement (prefix)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                 <td><?php echo $this->_tpl_vars['form']['value']['html']; ?>
<br />
                   <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Matched values are replaced with this prefix plus a unique code. EX: If replacement prefix for &quot;Vancouver&quot; is <em>city_</em>, occurrences will be replaced with <em>city_39121</em>.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                 </td>
              </tr>
            <?php else: ?>
              <tr class="crm-admin-options-form-block-value">
                <td class="label"><?php echo $this->_tpl_vars['form']['value']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['value']['html']; ?>
</td>
              </tr>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['name']['html']): ?>               <tr class="crm-admin-options-form-block-name">
                <td class="label"><?php echo $this->_tpl_vars['form']['name']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['name']['html']; ?>
<br />
                   <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The class name which implements this functionality.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                </td>
              </tr>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['form']['filter']['html']): ?>               <tr class="crm-admin-options-form-block-filter">
                <td class="label"><?php echo $this->_tpl_vars['form']['filter']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['filter']['html']; ?>
</td>
              </tr>
            <?php endif; ?> 
              <tr class="crm-admin-options-form-block-desciption">
                <td class="label"><?php echo $this->_tpl_vars['form']['description']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['description']['html']; ?>
<br />
            <?php if ($this->_tpl_vars['gName'] == 'activity_type'): ?>
               <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Description is included at the top of the activity edit and view pages for this type of activity.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                </td>
              </tr>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($this->_tpl_vars['gName'] == 'participant_status'): ?>
              <tr class="crm-admin-options-form-block-visibility_id">
                <td class="label"><?php echo $this->_tpl_vars['form']['visibility_id']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['visibility_id']['html']; ?>
</td>
              </tr>	
        <?php endif; ?>
              <tr class="crm-admin-options-form-block-weight">
                <td class="label"><?php echo $this->_tpl_vars['form']['weight']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['weight']['html']; ?>
</td>
              </tr>
        <?php if ($this->_tpl_vars['form']['component_id']['html']): ?>               <tr class="crm-admin-options-form-block-component_id"> 
                <td class="label"><?php echo $this->_tpl_vars['form']['component_id']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['component_id']['html']; ?>
</td>
              </tr>
        <?php endif; ?>
              <tr class="crm-admin-options-form-block-is_active">
                <td class="label"><?php echo $this->_tpl_vars['form']['is_active']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['is_active']['html']; ?>
</td>
              </tr>
        <?php if ($this->_tpl_vars['showDefault']): ?>
              <tr class="crm-admin-options-form-block-is_default">
                <td class="label"><?php echo $this->_tpl_vars['form']['is_default']['label']; ?>
</td>
                <td><?php echo $this->_tpl_vars['form']['is_default']['html']; ?>
</td>
              </tr>
        <?php endif; ?>
        <?php if ($this->_tpl_vars['showContactFilter']): ?>           <tr class="crm-admin-options-form-block-contactOptions"> 
             <td class="label"><?php echo $this->_tpl_vars['form']['contactOptions']['label']; ?>
</td>
             <td><?php echo $this->_tpl_vars['form']['contactOptions']['html']; ?>
</td>
           </tr>
        <?php endif; ?>
	  </table>
    <?php endif; ?>
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
 </fieldset>
</div>