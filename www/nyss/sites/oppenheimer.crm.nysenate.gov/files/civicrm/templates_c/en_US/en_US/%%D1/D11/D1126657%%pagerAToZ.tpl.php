<?php /* Smarty version 2.6.26, created on 2010-08-16 12:12:46
         compiled from CRM/common/pagerAToZ.tpl */ ?>
<div id="alpha-filter">
    <ul>
    <?php $_from = $this->_tpl_vars['aToZ']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['letter']):
?>
        <li <?php if ($this->_tpl_vars['letter']['class']): ?>class="<?php echo $this->_tpl_vars['letter']['class']; ?>
"<?php endif; ?>><?php echo $this->_tpl_vars['letter']['item']; ?>
</li>
    <?php endforeach; endif; unset($_from); ?>
    </ul>
</div>