<?php /* Smarty version 2.6.26, created on 2010-07-28 00:15:23
         compiled from CRM/Contact/Form/Edit/Address/address_name.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Edit/Address/address_name.tpl', 31, false),)), $this); ?>
<?php if ($this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['name']): ?>
  <tr>
      <td colspan="2">
        <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['name']['label']; ?>
<br />
        <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['name']['html']; ?>
<br />
        <span class="description font-italic"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Name of this address block like "My House, Work Place,.." which can be used in address book <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
      </td>
  </tr>
<?php endif; ?>