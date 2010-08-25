<?php /* Smarty version 2.6.26, created on 2010-08-20 12:04:03
         compiled from CRM/Contact/Form/Task/EmailCommon.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Task/EmailCommon.tpl', 39, false),array('function', 'help', 'CRM/Contact/Form/Task/EmailCommon.tpl', 40, false),array('modifier', 'crmReplace', 'CRM/Contact/Form/Task/EmailCommon.tpl', 99, false),)), $this); ?>
<?php if (! $this->_tpl_vars['emailTask']): ?>
<table class="form-layout-compressed">
    <tr>
	    <td class="label"><?php echo $this->_tpl_vars['form']['template']['label']; ?>
</td>
	    <td><?php echo $this->_tpl_vars['form']['template']['html']; ?>
</td>
    </tr>
</table>
<?php endif; ?>

<div class="crm-accordion-wrapper crm-html_email-accordion crm-accordion-open">
<div class="crm-accordion-header">
    <div class="icon crm-accordion-pointer"></div> 
    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>HTML Format<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    <?php echo smarty_function_help(array('id' => "id-message-text",'file' => "CRM/Contact/Form/Task/Email.hlp"), $this);?>

</div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
  <span class="helpIcon" id="helphtml">
	<a href="#" onClick="return showToken('Html', 2);"><?php echo $this->_tpl_vars['form']['token2']['label']; ?>
</a>
	<?php echo smarty_function_help(array('id' => "id-token-html",'file' => "CRM/Contact/Form/Task/Email.hlp"), $this);?>

	<div id="tokenHtml" style="display:none;">
	    <input style="border:1px solid #999999;" type="text" id="filter2" size="20" name="filter2" onkeyup="filter(this, 2)"/><br />
	    <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Begin typing to filter list of tokens<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span><br/>
	    <?php echo $this->_tpl_vars['form']['token2']['html']; ?>

	</div>
    </span>
    <div class="clear"></div>
    <div class='html'>
	<?php if ($this->_tpl_vars['editor'] == 'textarea'): ?>
	    <div class="help description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>NOTE: If you are composing HTML-formatted messages, you may want to enable a Rich Text (WYSIWYG) editor (Administer &raquo; Configure &raquo; Global Settings &raquo; Site Preferences).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
	<?php endif; ?>
	<?php echo $this->_tpl_vars['form']['html_message']['html']; ?>
<br />
    </div>
  </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
   
    
    
    
<div class="crm-accordion-wrapper crm-plaint_text_email-accordion crm-accordion-closed">
<div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
  <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Plain-Text Format<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	</div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
 
 <span class="helpIcon" id="helptext">
	<a href="#" onClick="return showToken('Text', 1);"><?php echo $this->_tpl_vars['form']['token1']['label']; ?>
</a>
	<?php echo smarty_function_help(array('id' => "id-token-text",'file' => "CRM/Contact/Form/Task/Email.hlp"), $this);?>

	<div id='tokenText' style="display:none">
	    <input  style="border:1px solid #999999;" type="text" id="filter1" size="20" name="filter1" onkeyup="filter(this, 1)"/><br />
	    <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Begin typing to filter list of tokens<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span><br/>
	    <?php echo $this->_tpl_vars['form']['token1']['html']; ?>

	</div>
    </span>
    <div class="clear"></div>
 
    <div class='text'>
	<?php echo $this->_tpl_vars['form']['text_message']['html']; ?>
<br />
    </div>
  </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->    
<div id="editMessageDetails" class="section">
    <div id="updateDetails" class="section" >
	<?php echo $this->_tpl_vars['form']['updateTemplate']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['updateTemplate']['label']; ?>

    </div>
    <div class="section">
	<?php echo $this->_tpl_vars['form']['saveTemplate']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['saveTemplate']['label']; ?>

    </div>
</div>

<div id="saveDetails" class="section">
   <div class="label"><?php echo $this->_tpl_vars['form']['saveTemplateName']['label']; ?>
</div>
   <div class="content"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['saveTemplateName']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</div>
</div>

<?php if (! $this->_tpl_vars['noAttach']): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Form/attachment.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Mailing/Form/InsertTokens.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>

