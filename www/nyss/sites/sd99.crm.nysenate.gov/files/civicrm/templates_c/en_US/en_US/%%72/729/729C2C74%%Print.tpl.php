<?php /* Smarty version 2.6.26, created on 2010-08-24 19:43:05
         compiled from CRM/Contact/Form/Task/Print.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Task/Print.tpl', 40, false),array('function', 'cycle', 'CRM/Contact/Form/Task/Print.tpl', 50, false),)), $this); ?>
<?php if ($this->_tpl_vars['rows']): ?>
<div class="crm-submit-buttons element-right">
     <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>
<div class="spacer"></div>
<div>
<br />
<table>
  <tr class="columnheader">
<?php if ($this->_tpl_vars['id']): ?>
  <?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['header']):
?>
     <th><?php echo $this->_tpl_vars['header']; ?>
</th>
  <?php endforeach; endif; unset($_from); ?>
<?php else: ?>
    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Name<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Address<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>City<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>State<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Postal<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Phone<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
<?php endif; ?>
  </tr>
<?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
    <tr class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
<?php if ($this->_tpl_vars['id']): ?>
        <td><?php echo $this->_tpl_vars['row']['sort_name']; ?>
</td>
         <?php $_from = $this->_tpl_vars['row']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['value']):
?>
           <?php if (( $this->_tpl_vars['key'] != 'checkbox' ) && ( $this->_tpl_vars['key'] != 'action' ) && ( $this->_tpl_vars['key'] != 'contact_type' ) && ( $this->_tpl_vars['key'] != 'status' ) && ( $this->_tpl_vars['key'] != 'contact_id' ) && ( $this->_tpl_vars['key'] != 'sort_name' )): ?>
              <td><?php echo $this->_tpl_vars['value']; ?>
</td>
           <?php endif; ?>
         <?php endforeach; endif; unset($_from); ?>

<?php else: ?>
        <td><?php echo $this->_tpl_vars['row']['sort_name']; ?>
</td>
        <td><?php $this->assign('street_address', '1-street_address'); ?><?php echo $this->_tpl_vars['row'][$this->_tpl_vars['street_address']]; ?>
</td>
        <td><?php $this->assign('city', '1-city'); ?><?php echo $this->_tpl_vars['row'][$this->_tpl_vars['city']]; ?>
</td>
        <td><?php $this->assign('state_province', '1-state_province'); ?><?php echo $this->_tpl_vars['row'][$this->_tpl_vars['state_province']]; ?>
</td>
        <td><?php $this->assign('postal_code', '1-postal_code'); ?><?php echo $this->_tpl_vars['row'][$this->_tpl_vars['postal_code']]; ?>
</td>
        <td><?php $this->assign('email', '1-email'); ?><?php echo $this->_tpl_vars['row'][$this->_tpl_vars['email']]; ?>
</td>
        <td><?php $this->assign('phone', '1-phone-1'); ?><?php echo $this->_tpl_vars['row'][$this->_tpl_vars['phone']]; ?>
</td>
<?php endif; ?>
    </tr>
<?php endforeach; endif; unset($_from); ?>
</table>
</div>

<div class="crm-submit-buttons element-right">
     <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>

<?php else: ?>
   <div class="messages status">
  <div class="icon inform-icon"></div>
       <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There are no records selected for Print.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
  </div>
<?php endif; ?>