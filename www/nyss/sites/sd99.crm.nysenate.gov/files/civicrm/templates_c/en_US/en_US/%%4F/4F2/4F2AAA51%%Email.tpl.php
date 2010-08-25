<?php /* Smarty version 2.6.26, created on 2010-08-20 12:04:02
         compiled from CRM/Contact/Form/Task/Email.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Task/Email.tpl', 30, false),array('function', 'help', 'CRM/Contact/Form/Task/Email.tpl', 36, false),array('function', 'crmURL', 'CRM/Contact/Form/Task/Email.tpl', 114, false),array('modifier', 'crmReplace', 'CRM/Contact/Form/Task/Email.tpl', 60, false),)), $this); ?>
<div class="crm-block crm-form-block crm-contactEmail-form-block">
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
<?php if ($this->_tpl_vars['suppressedEmails'] > 0): ?>
    <div class="status">
        <p><?php $this->_tag_stack[] = array('ts', array('count' => $this->_tpl_vars['suppressedEmails'],'plural' => 'Email will NOT be sent to %count contacts - (no email address on file, or communication preferences specify DO NOT EMAIL, or contact is deceased).')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email will NOT be sent to %count contact - (no email address on file, or communication preferences specify DO NOT EMAIL, or contact is deceased).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></p>
    </div>
<?php endif; ?>
<table class="form-layout-compressed">
    <tr class="crm-contactEmail-form-block-fromEmailAddress">
       <td class="label"><?php echo $this->_tpl_vars['form']['fromEmailAddress']['label']; ?>
</td>
       <td><?php echo $this->_tpl_vars['form']['fromEmailAddress']['html']; ?>
 <?php echo smarty_function_help(array('id' => "id-from_email",'file' => "CRM/Contact/Form/Task/Email.hlp"), $this);?>
</td>
    </tr>
    <tr class="crm-contactEmail-form-block-recipient">
       <td class="label"><?php if ($this->_tpl_vars['single'] == false): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Recipient(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php echo $this->_tpl_vars['form']['to']['label']; ?>
<?php endif; ?></td>
       <td><?php echo $this->_tpl_vars['form']['to']['html']; ?>
<?php if ($this->_tpl_vars['noEmails'] == true): ?>&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['emailAddress']['html']; ?>
<?php endif; ?>
    <div class="spacer"></div>
       <span class="bold"><a href="#" id="addcc"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add CC<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#" id="addbcc"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add BCC<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></span>
       </td>
    </tr>
    <tr class="crm-contactEmail-form-block-cc_id" id="cc" <?php if (! $this->_tpl_vars['form']['cc_id']['value']): ?>style="display:none;"<?php endif; ?>>
        <td class="label"><?php echo $this->_tpl_vars['form']['cc_id']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['cc_id']['html']; ?>
</td>
    </tr>
    <tr class="crm-contactEmail-form-block-bcc_id" id="bcc" <?php if (! $this->_tpl_vars['form']['bcc_id']['value']): ?>style="display:none;"<?php endif; ?>>
        <td class="label"><?php echo $this->_tpl_vars['form']['bcc_id']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['bcc_id']['html']; ?>
</td>
    </tr>

<?php if ($this->_tpl_vars['emailTask']): ?>
    <tr class="crm-contactEmail-form-block-template">
        <td class="label"><?php echo $this->_tpl_vars['form']['template']['label']; ?>
</td>
        <td><?php echo $this->_tpl_vars['form']['template']['html']; ?>
</td>
    </tr>
<?php endif; ?>
    <tr class="crm-contactEmail-form-block-subject">
       <td class="label"><?php echo $this->_tpl_vars['form']['subject']['label']; ?>
</td>
       <td><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['subject']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
&nbsp;
        <a href="#" onClick="return showToken('Subject', 3);"><?php echo $this->_tpl_vars['form']['token3']['label']; ?>
</a>
	    <?php echo smarty_function_help(array('id' => "id-token-subject",'file' => "CRM/Contact/Form/Task/Email.hlp"), $this);?>

        <div id='tokenSubject' style="display:none">
	      <input style="border:1px solid #999999;" type="text" id="filter3" size="20" name="filter3" onkeyup="filter(this, 3)"/><br />
	      <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Begin typing to filter list of tokens<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span><br/>
	      <?php echo $this->_tpl_vars['form']['token3']['html']; ?>

        </div>
       </td>
    </tr>
</table>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Task/EmailCommon.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div class="spacer"> </div>

<?php if ($this->_tpl_vars['single'] == false): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Task.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['suppressedEmails'] > 0): ?>
   <?php $this->_tag_stack[] = array('ts', array('count' => $this->_tpl_vars['suppressedEmails'],'plural' => 'Email will NOT be sent to %count contacts.')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email will NOT be sent to %count contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<?php endif; ?>
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>
<script type="text/javascript">
var toContact = ccContact = bccContact = '';

<?php if ($this->_tpl_vars['toContact']): ?>
    toContact  = <?php echo $this->_tpl_vars['toContact']; ?>
;
<?php endif; ?>

<?php if ($this->_tpl_vars['ccContact']): ?>
    ccContact  = <?php echo $this->_tpl_vars['ccContact']; ?>
;
<?php endif; ?>

<?php if ($this->_tpl_vars['bccContact']): ?>
    bccContact = <?php echo $this->_tpl_vars['bccContact']; ?>
;
<?php endif; ?>

<?php echo '
cj(\'#addcc\').toggle( function() { cj(this).text(\'Remove CC\');
                                  cj(\'tr#cc\').show().find(\'ul\').find(\'input\').focus();
                   },function() { cj(this).text(\'Add CC\');cj(\'#cc_id\').val(\'\');
                                  cj(\'tr#cc ul li:not(:last)\').remove();cj(\'#cc\').hide();
});
cj(\'#addbcc\').toggle( function() { cj(this).text(\'Remove BCC\');
                                   cj(\'tr#bcc\').show().find(\'ul\').find(\'input\').focus();
                    },function() { cj(this).text(\'Add BCC\');cj(\'#bcc_id\').val(\'\');
                                   cj(\'tr#bcc ul li:not(:last)\').remove();cj(\'#bcc\').hide();
});

eval( \'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } \');

var hintText = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Type in a partial or complete name or email address of an existing contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
var sourceDataUrl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/checkemail','h' => 0), $this);?>
<?php echo '";
var toDataUrl     = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/checkemail','q' => 'id=1','h' => 0), $this);?>
<?php echo '";

cj( "#to"     ).tokenInput( toDataUrl, { prePopulate: toContact, classes: tokenClass, hintText: hintText });
cj( "#cc_id"  ).tokenInput( sourceDataUrl, { prePopulate: ccContact, classes: tokenClass, hintText: hintText });
cj( "#bcc_id" ).tokenInput( sourceDataUrl, { prePopulate: bccContact, classes: tokenClass, hintText: hintText });
cj( \'ul.token-input-list-facebook, div.token-input-dropdown-facebook\' ).css( \'width\', \'450px\' );
</script>
'; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formNavigate.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>