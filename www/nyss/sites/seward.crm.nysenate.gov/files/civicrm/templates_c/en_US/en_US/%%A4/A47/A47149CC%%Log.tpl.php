<?php /* Smarty version 2.6.26, created on 2010-08-16 22:20:51
         compiled from CRM/Contact/Page/View/Log.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Page/View/Log.tpl', 28, false),array('function', 'cycle', 'CRM/Contact/Page/View/Log.tpl', 34, false),array('function', 'crmURL', 'CRM/Contact/Page/View/Log.tpl', 35, false),array('modifier', 'crmDate', 'CRM/Contact/Page/View/Log.tpl', 36, false),)), $this); ?>
<div id="changeLog" class="view-content">
    <p></p>
    <div class="bold"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Change Log:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo $this->_tpl_vars['displayName']; ?>
</div>
    <div class="form-item">
     <?php if ($this->_tpl_vars['logCount'] > 0): ?>  	
       <table>
       <tr class="columnheader"><th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Changed By<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th><th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Change Date<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th></tr>
       <?php $_from = $this->_tpl_vars['log']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
         <tr class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
            <td> <?php echo $this->_tpl_vars['row']['image']; ?>
&nbsp;<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "action=view&reset=1&cid=".($this->_tpl_vars['row']['id'])), $this);?>
"><?php echo $this->_tpl_vars['row']['name']; ?>
</a></td>
            <td><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['date'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
</td>
         </tr>
       <?php endforeach; endif; unset($_from); ?>
       </table>
     <?php else: ?>
     <div class="messages status">	
      <div class="icon inform-icon"></div> &nbsp;
      <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>No modifications have been logged for this contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
     </div>	
     <?php endif; ?>
    </div>
 </p>
</div>