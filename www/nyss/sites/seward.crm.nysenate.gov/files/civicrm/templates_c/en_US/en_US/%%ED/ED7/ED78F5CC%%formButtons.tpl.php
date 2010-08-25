<?php /* Smarty version 2.6.26, created on 2010-08-16 22:21:41
         compiled from CRM/common/formButtons.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substring', 'CRM/common/formButtons.tpl', 32, false),array('modifier', 'crmReplace', 'CRM/common/formButtons.tpl', 34, false),array('modifier', 'crmBtnType', 'CRM/common/formButtons.tpl', 38, false),)), $this); ?>

   
<?php $_from = $this->_tpl_vars['form']['buttons']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['btns'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['btns']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['button']):
        $this->_foreach['btns']['iteration']++;
?>
    <?php if (((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('substring', true, $_tmp, 0, 4) : smarty_modifier_substring($_tmp, 0, 4)) == '_qf_'): ?>
        <?php if ($this->_tpl_vars['location']): ?>
          <?php $this->assign('html', ((is_array($_tmp=$this->_tpl_vars['form']['buttons'][$this->_tpl_vars['key']]['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'id', ($this->_tpl_vars['key'])."-".($this->_tpl_vars['location'])) : smarty_modifier_crmReplace($_tmp, 'id', ($this->_tpl_vars['key'])."-".($this->_tpl_vars['location'])))); ?>
        <?php else: ?>
          <?php $this->assign('html', $this->_tpl_vars['form']['buttons'][$this->_tpl_vars['key']]['html']); ?>
        <?php endif; ?>
        <span class="crm-button crm-button-type-<?php echo ((is_array($_tmp=$this->_tpl_vars['key'])) ? $this->_run_mod_handler('crmBtnType', true, $_tmp) : smarty_modifier_crmBtnType($_tmp)); ?>
 crm-button<?php echo $this->_tpl_vars['key']; ?>
"><?php echo $this->_tpl_vars['html']; ?>
</span>
    <?php endif; ?>
<?php endforeach; endif; unset($_from); ?>