<?php /* Smarty version 2.6.26, created on 2010-07-06 10:37:47
         compiled from CRM/Contact/Page/DashBoard.tpl */ ?>

<?php if (empty ( $this->_tpl_vars['hookContent'] )): ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/DashBoardDashlet.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
    <?php if ($this->_tpl_vars['hookContentPlacement'] != 2 && $this->_tpl_vars['hookContentPlacement'] != 3): ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/DashBoardDashlet.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>

    <?php $_from = $this->_tpl_vars['hookContent']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['title'] => $this->_tpl_vars['content']):
?>
    <fieldset><legend><?php echo $this->_tpl_vars['title']; ?>
</legend>
        <?php echo $this->_tpl_vars['content']; ?>

    </fieldset>
    <?php endforeach; endif; unset($_from); ?>

    <?php if ($this->_tpl_vars['hookContentPlacement'] == 2): ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Page/DashBoardDashlet.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
<?php endif; ?>