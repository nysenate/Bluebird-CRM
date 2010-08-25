<?php /* Smarty version 2.6.26, created on 2010-08-16 22:19:14
         compiled from CRM/common/status.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/common/status.tpl', 49, false),array('function', 'docURL', 'CRM/common/status.tpl', 49, false),)), $this); ?>

<?php if ($this->_tpl_vars['session']->getStatus(false)): ?>
    <?php $this->assign('status', $this->_tpl_vars['session']->getStatus(true)); ?>
    <div class="messages status">
    	<div class="icon inform-icon"></div>&nbsp;
        <?php if (is_array ( $this->_tpl_vars['status'] )): ?>
            <?php $_from = $this->_tpl_vars['status']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['statLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['statLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['statItem']):
        $this->_foreach['statLoop']['iteration']++;
?>
                <?php if (($this->_foreach['statLoop']['iteration'] <= 1)): ?>
                    <?php if ($this->_tpl_vars['statItem']): ?><h3><?php echo $this->_tpl_vars['statItem']; ?>
</h3><div class='spacer'></div><?php endif; ?>
                <?php else: ?>               
                   <ul><li><?php echo $this->_tpl_vars['statItem']; ?>
</li></ul>
                <?php endif; ?>                
            <?php endforeach; endif; unset($_from); ?>
        <?php else: ?>
            <?php echo $this->_tpl_vars['status']; ?>

        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (! $this->_tpl_vars['urlIsPublic'] && $this->_tpl_vars['config']->debug): ?>
    <div class="messages status">
      <div class="icon inform-icon"></div>
        &nbsp;<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>WARNING: Debug is currently enabled in Global Settings.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_docURL(array('page' => 'Debugging'), $this);?>

    </div>
<?php endif; ?>