<?php /* Smarty version 2.6.26, created on 2010-08-24 14:47:44
         compiled from CRM/common/formNavigate.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/common/formNavigate.tpl', 30, false),)), $this); ?>
<?php echo '
<script type="text/javascript">
     cj( function( ) {
         cj("#'; ?>
<?php echo $this->_tpl_vars['form']['formName']; ?>
<?php echo '").FormNavigate("'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You have unsaved changes.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '"); 
     });
</script>
'; ?>

