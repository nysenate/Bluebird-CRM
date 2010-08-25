<?php /* Smarty version 2.6.26, created on 2010-08-24 19:20:51
         compiled from CRM/common/debug.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'debug', 'CRM/common/debug.tpl', 27, false),)), $this); ?>
<?php if ($_GET['smartyDebug']): ?>
<?php echo smarty_function_debug(array(), $this);?>

<?php endif; ?>

<?php if ($_GET['sessionReset']): ?>
<?php echo $this->_tpl_vars['session']->reset($_GET['sessionReset']); ?>

<?php endif; ?>

<?php if ($_GET['sessionDebug']): ?>
<?php echo $this->_tpl_vars['session']->debug($_GET['sessionDebug']); ?>

<?php endif; ?>

<?php if ($_GET['directoryCleanup']): ?> 
<?php echo $this->_tpl_vars['config']->cleanup($_GET['directoryCleanup']); ?>

<?php endif; ?>

<?php if ($_GET['cacheCleanup']): ?> 
<?php echo $this->_tpl_vars['config']->clearDBCache(); ?>

<?php endif; ?>

<?php if ($_GET['configReset']): ?> 
<?php echo $this->_tpl_vars['config']->reset(); ?>

<?php endif; ?>