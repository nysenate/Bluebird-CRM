<?php /* Smarty version 2.6.26, created on 2010-08-25 15:03:35
         compiled from CRM/common/print.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'truncate', 'CRM/common/print.tpl', 28, false),array('modifier', 'strip_tags', 'CRM/common/print.tpl', 31, false),array('block', 'ts', 'CRM/common/print.tpl', 31, false),array('function', 'crmURL', 'CRM/common/print.tpl', 33, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
">

<head>
  <title><?php if ($this->_tpl_vars['pageTitle']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['pageTitle'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
<?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Printer-Friendly View<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <base href="<?php echo CRM_Utils_System::crmURL(array('p' => "",'a' => true), $this);?>
" /><!--[if IE]></base><![endif]-->
  <style type="text/css" media="screen, print">@import url(<?php echo $this->_tpl_vars['config']->resourceBase; ?>
css/civicrm.css);</style>
  <style type="text/css" media="screen, print">@import url(<?php echo $this->_tpl_vars['config']->resourceBase; ?>
css/extras.css);</style>
  <style type="text/css" media="print">@import url(<?php echo $this->_tpl_vars['config']->resourceBase; ?>
css/print.css);</style>
  <style type="text/css">@import url(<?php echo $this->_tpl_vars['config']->resourceBase; ?>
css/skins/aqua/theme.css);</style>
  <script type="text/javascript" src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
js/Common.js"></script>
</head>

<body>
<?php if ($this->_tpl_vars['config']->debug): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/debug.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jquery.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<div id="crm-container" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
">
<?php if ($this->_tpl_vars['session']->getStatus(false)): ?>
<div class="messages status">
  <div class="icon inform-icon"></div>
  <?php echo $this->_tpl_vars['session']->getStatus(true); ?>

</div>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['display_name'] ) && $this->_tpl_vars['display_name']): ?>
    <h3 style="margin: .25em;"><?php echo $this->_tpl_vars['display_name']; ?>
</h3>
<?php endif; ?>

<!-- .tpl file invoked: <?php echo $this->_tpl_vars['tplFile']; ?>
. Call via form.tpl if we have a form in the page. -->
<?php if ($this->_tpl_vars['isForm']): ?>
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


</div> </body>
</html>