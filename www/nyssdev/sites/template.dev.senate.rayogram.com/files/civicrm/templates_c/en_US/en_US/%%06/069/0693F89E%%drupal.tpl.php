<?php /* Smarty version 2.6.26, created on 2010-06-07 12:59:25
         compiled from CRM/common/drupal.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'truncate', 'CRM/common/drupal.tpl', 35, false),)), $this); ?>
<?php if ($this->_tpl_vars['config']->debug): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/debug.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "Custom/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<div class="clear"></div>


<div id="crm-container" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
">


<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/action.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['defaultWysiwygEditor'] == 1): ?>
    <script type="text/javascript" src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
packages/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
    <script type="text/javascript" src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
packages/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<?php elseif ($this->_tpl_vars['defaultWysiwygEditor'] == 2): ?>
    <script type="text/javascript" src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
packages/ckeditor/ckeditor.js"></script>
<?php endif; ?>


<?php if ($this->_tpl_vars['pageTitle']): ?>
	<div class="crm-title">
		<h1 class="title"><?php if ($this->_tpl_vars['isDeleted']): ?><del><?php endif; ?><?php echo $this->_tpl_vars['pageTitle']; ?>
<?php if ($this->_tpl_vars['isDeleted']): ?></del><?php endif; ?></h1>
	</div>
<?php endif; ?>




<?php if (isset ( $this->_tpl_vars['localTasks'] ) && $this->_tpl_vars['localTasks']): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/localNav.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/status.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!-- .tpl file invoked: <?php echo $this->_tpl_vars['tplFile']; ?>
. Call via form.tpl if we have a form in the page. -->
<?php if (isset ( $this->_tpl_vars['isForm'] ) && $this->_tpl_vars['isForm']): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Form/".($this->_tpl_vars['formTpl']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['tplFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<div id="crm-seal"></div>

<?php if (! $this->_tpl_vars['urlIsPublic']): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmtooltip(); 
   cj().crmaccordions(); 
});
</script>
'; ?>

<script type="text/javascript">jQuery.noConflict(true);</script>
</div> 