<?php /* Smarty version 2.6.26, created on 2010-08-19 16:08:37
         compiled from CRM/Profile/Form/Dynamic.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'truncate', 'CRM/Profile/Form/Dynamic.tpl', 33, false),array('modifier', 'substr', 'CRM/Profile/Form/Dynamic.tpl', 112, false),array('modifier', 'cat', 'CRM/Profile/Form/Dynamic.tpl', 113, false),array('block', 'ts', 'CRM/Profile/Form/Dynamic.tpl', 102, false),array('function', 'crmURL', 'CRM/Profile/Form/Dynamic.tpl', 238, false),)), $this); ?>
<?php if ($this->_tpl_vars['context'] != 'dialog'): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
js/Common.js"></script>
<?php endif; ?>
<?php if (! empty ( $this->_tpl_vars['fields'] )): ?>
<div id="crm-container" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
">

    <?php if ($this->_tpl_vars['mode'] == 1 || $this->_tpl_vars['activeComponent'] != 'CiviCRM'): ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Form/body.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
    <?php echo ''; ?><?php if ($this->_tpl_vars['help_pre'] && $this->_tpl_vars['action'] != 4): ?><?php echo '<div class="messages help">'; ?><?php echo $this->_tpl_vars['help_pre']; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/CMSUser.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?><?php $this->assign('zeroField', 'Initial Non Existent Fieldset'); ?><?php echo ''; ?><?php $this->assign('fieldset', $this->_tpl_vars['zeroField']); ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['fieldName'] => $this->_tpl_vars['field']):
?><?php echo ''; ?><?php $this->assign('profileID', $this->_tpl_vars['field']['group_id']); ?><?php echo ''; ?><?php $this->assign('n', $this->_tpl_vars['field']['name']); ?><?php echo ''; ?><?php if ($this->_tpl_vars['form'][$this->_tpl_vars['n']]): ?><?php echo ''; ?><?php if ($this->_tpl_vars['field']['groupTitle'] != $this->_tpl_vars['fieldset']): ?><?php echo ''; ?><?php if ($this->_tpl_vars['fieldset'] != $this->_tpl_vars['zeroField']): ?><?php echo ''; ?><?php if ($this->_tpl_vars['groupHelpPost']): ?><?php echo '<div class="messages help">'; ?><?php echo $this->_tpl_vars['groupHelpPost']; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['mode'] != 8 && $this->_tpl_vars['mode'] != 4): ?><?php echo '</fieldset></div>'; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['mode'] != 8 && $this->_tpl_vars['mode'] != 4): ?><?php echo '<div '; ?><?php if ($this->_tpl_vars['context'] != 'dialog'): ?><?php echo 'id="profilewrap'; ?><?php echo $this->_tpl_vars['field']['group_id']; ?><?php echo '"'; ?><?php endif; ?><?php echo '><fieldset><legend>'; ?><?php echo $this->_tpl_vars['field']['groupTitle']; ?><?php echo '</legend>'; ?><?php endif; ?><?php echo ''; ?><?php $this->assign('fieldset', ($this->_tpl_vars['field']['groupTitle'])); ?><?php echo ''; ?><?php $this->assign('groupHelpPost', ($this->_tpl_vars['field']['groupHelpPost'])); ?><?php echo ''; ?><?php if ($this->_tpl_vars['field']['groupHelpPre']): ?><?php echo '<div class="messages help">'; ?><?php echo $this->_tpl_vars['field']['groupHelpPre']; ?><?php echo '</div>'; ?><?php endif; ?><?php echo '<div class="form-layout-compressed">'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['field']['options_per_line']): ?><?php echo '<div class="crm-section editrow_'; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '-section form-item" id="editrow-'; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '"><div class="label">'; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['n']]['label']; ?><?php echo '</div><div class="content edit-value">'; ?><?php $this->assign('count', '1'); ?><?php echo ''; ?><?php echo '<table class="form-layout-compressed"><tr>'; ?><?php echo ''; ?><?php $this->assign('index', '1'); ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['form'][$this->_tpl_vars['n']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['outer'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['outer']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['item']):
        $this->_foreach['outer']['iteration']++;
?><?php echo ''; ?><?php if ($this->_tpl_vars['index'] < 10): ?><?php echo ''; ?><?php $this->assign('index', ($this->_tpl_vars['index']+1)); ?><?php echo ''; ?><?php else: ?><?php echo '<td class="labels font-light">'; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['n']][$this->_tpl_vars['key']]['html']; ?><?php echo '</td>'; ?><?php if ($this->_tpl_vars['count'] == $this->_tpl_vars['field']['options_per_line']): ?><?php echo '</tr><tr>'; ?><?php $this->assign('count', '1'); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->assign('count', ($this->_tpl_vars['count']+1)); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</tr></table>'; ?><?php if ($this->_tpl_vars['field']['html_type'] == 'Radio' && $this->_tpl_vars['form']['formName'] == 'Edit' && $this->_tpl_vars['field']['is_view'] != 1): ?><?php echo '&nbsp;<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio(\''; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '\', \''; ?><?php echo $this->_tpl_vars['form']['formName']; ?><?php echo '\'); return false;">'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'clear'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a>)</span>'; ?><?php endif; ?><?php echo ''; ?><?php echo '</div><div class="clear"></div></div>'; ?><?php else: ?><?php echo '<div id="editrow-'; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '" class="crm-section editrow_'; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '-section form-item"><div class="label">'; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['n']]['label']; ?><?php echo '</div><div class="edit-value content">'; ?><?php if (((is_array($_tmp=$this->_tpl_vars['n'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 3) : substr($_tmp, 0, 3)) == 'im-'): ?><?php echo ''; ?><?php $this->assign('provider', ((is_array($_tmp=$this->_tpl_vars['n'])) ? $this->_run_mod_handler('cat', true, $_tmp, "-provider_id") : smarty_modifier_cat($_tmp, "-provider_id"))); ?><?php echo ''; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['provider']]['html']; ?><?php echo '&nbsp;'; ?><?php else: ?><?php echo ''; ?><?php $this->assign('websiteType', ((is_array($_tmp=$this->_tpl_vars['n'])) ? $this->_run_mod_handler('cat', true, $_tmp, "-website_type_id") : smarty_modifier_cat($_tmp, "-website_type_id"))); ?><?php echo ''; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['websiteType']]['html']; ?><?php echo '&nbsp;'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['n'] == 'email_greeting' || $this->_tpl_vars['n'] == 'postal_greeting' || $this->_tpl_vars['n'] == 'addressee'): ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Profile/Form/GreetingType.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?><?php elseif (( $this->_tpl_vars['n'] == 'group' && $this->_tpl_vars['form']['group'] ) || ( $this->_tpl_vars['n'] == 'tag' && $this->_tpl_vars['form']['tag'] )): ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/TagsAndGroups.tpl", 'smarty_include_vars' => array('type' => $this->_tpl_vars['n'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?><?php elseif (( $this->_tpl_vars['form'][$this->_tpl_vars['n']]['name'] == 'image_URL' )): ?><?php echo ''; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['n']]['html']; ?><?php echo ''; ?><?php if ($this->_tpl_vars['imageURL']): ?><?php echo '<div class="crm-section contact_image-section"><div class="content">'; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/ContactImage.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '</div></div>'; ?><?php endif; ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php if (( $this->_tpl_vars['field']['data_type'] == 'Date' || ( ( ( $this->_tpl_vars['n'] == 'birth_date' ) || ( $this->_tpl_vars['n'] == 'deceased_date' ) ) ) )): ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => $this->_tpl_vars['n'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['n']]['html']; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if (( ( $this->_tpl_vars['n'] == 'gender' ) || ( $this->_tpl_vars['field']['html_type'] == 'Radio' && $this->_tpl_vars['form']['formName'] == 'Edit' && $this->_tpl_vars['field']['is_required'] != 1 ) ) && ( $this->_tpl_vars['field']['is_view'] != 1 )): ?><?php echo '&nbsp;<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio(\''; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '\', \''; ?><?php echo $this->_tpl_vars['form']['formName']; ?><?php echo '\'); return false;">'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'clear'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a>)</span>'; ?><?php elseif ($this->_tpl_vars['field']['html_type'] == 'Autocomplete-Select'): ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/AutoComplete.tpl", 'smarty_include_vars' => array('element_name' => $this->_tpl_vars['n'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo '<div class="clear"></div></div>'; ?><?php if ($this->_tpl_vars['form'][$this->_tpl_vars['n']]['type'] == 'file'): ?><?php echo '<div class="crm-section file_displayURL-section file_displayURL'; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '-section"><div class="content">'; ?><?php echo $this->_tpl_vars['customFiles'][$this->_tpl_vars['n']]['displayURL']; ?><?php echo '</div></div><div class="crm-section file_deleteURL-section file_deleteURL'; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '-section"><div class="content">'; ?><?php echo $this->_tpl_vars['customFiles'][$this->_tpl_vars['n']]['deleteURL']; ?><?php echo '</div></div>'; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php echo ''; ?><?php if ($this->_tpl_vars['field']['help_post'] && $this->_tpl_vars['action'] != 4 && $this->_tpl_vars['form'][$this->_tpl_vars['n']]['html']): ?><?php echo '<div class="crm-section helprow-'; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '-section" id="helprow-'; ?><?php echo $this->_tpl_vars['n']; ?><?php echo '"><div class="content description">'; ?><?php echo $this->_tpl_vars['field']['help_post']; ?><?php echo '</div></div>'; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo '</div>'; ?><?php endforeach; endif; unset($_from); ?><?php echo ''; ?><?php if ($this->_tpl_vars['isCaptcha'] && ( $this->_tpl_vars['mode'] == 8 || $this->_tpl_vars['mode'] == 4 || $this->_tpl_vars['mode'] == 1 )): ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'CRM/common/ReCAPTCHA.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '<script type="text/javascript">cj(\'.recaptcha_label\').attr(\'width\', \'140px\');</script>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['field']['groupHelpPost']): ?><?php echo '<div class="messages help">'; ?><?php echo $this->_tpl_vars['field']['groupHelpPost']; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['mode'] != 8 && $this->_tpl_vars['mode'] != 4): ?><?php echo '</fieldset></div>'; ?><?php endif; ?><?php echo ''; ?><?php if (( $this->_tpl_vars['action'] == 1 && $this->_tpl_vars['mode'] == 4 ) || ( $this->_tpl_vars['action'] == 2 ) || ( $this->_tpl_vars['action'] == 8192 )): ?><?php echo '<div class="crm-submit-buttons">'; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?><?php if ($this->_tpl_vars['isDuplicate']): ?><?php echo '<span class="crm-button">'; ?><?php echo $this->_tpl_vars['form']['_qf_Edit_upload_duplicate']['html']; ?><?php echo '</span>'; ?><?php endif; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['help_post'] && $this->_tpl_vars['action'] != 4): ?><?php echo '<br /><div class="messages help">'; ?><?php echo $this->_tpl_vars['help_post']; ?><?php echo '</div>'; ?><?php endif; ?><?php echo ''; ?>


</div> 
<script type="text/javascript">
  <?php if ($this->_tpl_vars['drupalCms']): ?>
  <?php echo '
    if ( document.getElementsByName("cms_create_account")[0].checked ) {
       show(\'details\');
    } else {
       hide(\'details\');
    }
  '; ?>

  <?php endif; ?>
</script>
<?php endif; ?> 
<?php if ($this->_tpl_vars['drupalCms']): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/showHideByFieldValue.tpl", 'smarty_include_vars' => array('trigger_field_id' => 'create_account','trigger_value' => "",'target_element_id' => 'details','target_element_type' => 'block','field_type' => 'radio','invert' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php elseif ($this->_tpl_vars['statusMessage']): ?>
    <div class="messages status">
    <div class="icon inform-icon"></div>
       <?php echo $this->_tpl_vars['statusMessage']; ?>

    </div>
<?php endif; ?>
<?php echo '
<script type="text/javascript">
    
cj(document).ready(function(){ 
	cj(\'#selector tr:even\').addClass(\'odd-row \');
	cj(\'#selector tr:odd \').addClass(\'even-row\');
});
'; ?>

<?php if ($this->_tpl_vars['context'] == 'dialog'): ?>
<?php echo '
    var options = { 
        beforeSubmit:  showRequest  // pre-submit callback  
    }; 
    
    // bind form using \'ajaxForm\'
    cj(\'#Edit\').ajaxForm( options );

   	// pre-submit callback 
    function showRequest(formData, jqForm, options) { 
        // formData is an array; here we use $.param to convert it to a string to display it 
        // but the form plugin does this for you automatically when it submits the data 
        var queryString = cj.param(formData); 
        queryString = queryString + \'&snippet=5&gid=\' + '; ?>
"<?php echo $this->_tpl_vars['profileID']; ?>
"<?php echo ';
        var postUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/profile/create','h' => 0), $this);?>
"<?php echo '; 
        var response = cj.ajax({
           type: "POST",
           url: postUrl,
           async: false,
           data: queryString,
           dataType: "json",
           success: function( response ) {
               if ( response.newContactSuccess ) {
                   cj("#contact").val( response.sortName ).focus( );
		   if ( typeof(allowMultiClient) != "undefined" ) {
		      if ( allowMultiClient ) {
	              	 var newToken = \'{"name":"\'+response.sortName+\'","id":"\'+response.contactID+\'"},\';
		      	 cj(\'ul.token-input-list-facebook, div.token-input-dropdown-facebook\' ).remove();
		      	 addMultiClientOption(newToken);
		      }
		   }
                   cj("input[name=contact_select_id]").val( response.contactID );
                   cj("#contact-success").show( );
                   cj("#contact-dialog").dialog("close");
               }
           }
         }).responseText;

         cj("#contact-dialog").html( response );

        // here we could return false to prevent the form from being submitted; 
        // returning anything other than false will allow the form submit to continue 
        return false; 
    }

'; ?>
    
<?php endif; ?>
<?php echo '
</script>
'; ?>

