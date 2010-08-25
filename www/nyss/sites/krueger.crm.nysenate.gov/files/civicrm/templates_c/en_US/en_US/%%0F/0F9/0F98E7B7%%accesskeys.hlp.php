<?php /* Smarty version 2.6.26, created on 2010-08-13 13:21:39
         compiled from CRM/common/accesskeys.hlp */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'htxt', 'CRM/common/accesskeys.hlp', 26, false),array('block', 'ts', 'CRM/common/accesskeys.hlp', 28, false),)), $this); ?>
<?php $this->_tag_stack[] = array('htxt', array('id' => 'accesskeys')); $_block_repeat=true;smarty_block_htxt($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
<p>
<strong><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Use the following key combinations for:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></strong>
</p>
<p></p>
<ul>
<li> ALT+SHIFT+E -<em> Edit Contact(View Contact Summary Page)</em></li>
<li> ALT+SHIFT+S -<em> Save Button</em></li>
<li> ALT+SHIFT+N -<em> Adding a new record in each tab</em></li>
</ul>
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_htxt($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>