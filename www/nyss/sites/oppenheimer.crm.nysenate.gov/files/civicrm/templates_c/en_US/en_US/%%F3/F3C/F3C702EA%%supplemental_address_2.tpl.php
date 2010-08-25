<?php /* Smarty version 2.6.26, created on 2010-08-17 16:57:02
         compiled from CRM/Contact/Form/Edit/Address/supplemental_address_2.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Edit/Address/supplemental_address_2.tpl', 31, false),)), $this); ?>
<?php if ($this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['supplemental_address_2']): ?>
   <tr>
      <td colspan="2">
          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['supplemental_address_2']['label']; ?>
<br />
          <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['supplemental_address_2']['html']; ?>
 <br >
          <span class="description font-italic"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Supplemental address info, e.g. c/o, department name, building name, etc.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
      </td>
   </tr>
<?php endif; ?>