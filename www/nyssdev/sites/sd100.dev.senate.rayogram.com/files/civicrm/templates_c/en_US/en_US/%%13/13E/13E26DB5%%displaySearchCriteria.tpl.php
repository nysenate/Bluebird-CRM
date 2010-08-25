<?php /* Smarty version 2.6.26, created on 2010-05-25 13:26:30
         compiled from CRM/common/displaySearchCriteria.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/common/displaySearchCriteria.tpl', 36, false),)), $this); ?>
<?php $_from = $this->_tpl_vars['qill']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sets'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sets']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['setKey'] => $this->_tpl_vars['orClauses']):
        $this->_foreach['sets']['iteration']++;
?>
    <?php if ($this->_foreach['sets']['total'] > 2): ?>
                <?php if (count ( $this->_tpl_vars['orClauses'] ) > 0): ?>
        <ul>
        <li>
        <?php $_from = $this->_tpl_vars['orClauses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['criteria'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['criteria']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['item']):
        $this->_foreach['criteria']['iteration']++;
?>
            <?php echo $this->_tpl_vars['item']; ?>

            <?php if (! ($this->_foreach['criteria']['iteration'] == $this->_foreach['criteria']['total'])): ?>
                <span class="font-italic">...<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>AND<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>...</span>
            <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
        </li>
        </ul>

                <?php if (! ($this->_foreach['sets']['iteration'] == $this->_foreach['sets']['total'])): ?>
            <ul class="menu"><li class="no-display"> 
            <?php if ($this->_tpl_vars['setKey'] == 0): ?>AND<br />
            <?php else: ?>OR<br />
            <?php endif; ?>
            </li></ul>
        <?php endif; ?>
        <?php endif; ?>

    <?php else: ?>
        <?php $_from = $this->_tpl_vars['orClauses']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['criteria'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['criteria']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['item']):
        $this->_foreach['criteria']['iteration']++;
?>
            <div class="qill">
            <?php echo $this->_tpl_vars['item']; ?>

            <?php if (! ($this->_foreach['criteria']['iteration'] == $this->_foreach['criteria']['total'])): ?>
                <span class="font-italic"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>...AND...<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            <?php endif; ?>
            </div>
        <?php endforeach; endif; unset($_from); ?>
    <?php endif; ?>
<?php endforeach; endif; unset($_from); ?>