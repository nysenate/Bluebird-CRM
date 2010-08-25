<?php /* Smarty version 2.6.26, created on 2010-05-26 18:04:52
         compiled from CRM/Report/Form/ErrorMessage.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Report/Form/ErrorMessage.tpl', 28, false),)), $this); ?>
	
<?php if ($this->_tpl_vars['outputMode'] == 'html' && ! $this->_tpl_vars['rows']): ?>	
    <div class="messages status">	
        <div class="icon inform-icon"></div>&nbsp; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Sorry. No results found.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>		
    </div>
<?php endif; ?>