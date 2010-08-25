<?php /* Smarty version 2.6.26, created on 2010-08-20 12:59:24
         compiled from CRM/Profile/Page/Dynamic.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'truncate', 'CRM/Profile/Page/Dynamic.tpl', 28, false),)), $this); ?>
<?php if (! empty ( $this->_tpl_vars['row'] )): ?> 
        <div id="crm-container" lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
" xml:lang="<?php echo ((is_array($_tmp=$this->_tpl_vars['config']->lcMessages)) ? $this->_run_mod_handler('truncate', true, $_tmp, 2, "", true) : smarty_modifier_truncate($_tmp, 2, "", true)); ?>
">
        <?php if ($this->_tpl_vars['overlayProfile']): ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Profile/Page/Overlay.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php else: ?>
            <?php $_from = $this->_tpl_vars['row']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['profile'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['profile']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['rowName'] => $this->_tpl_vars['value']):
        $this->_foreach['profile']['iteration']++;
?>
              <div id="row-<?php echo $this->_foreach['profile']['iteration']; ?>
" class="crm-section <?php echo $this->_foreach['profile']['iteration']; ?>
-section">
                <div class="label">
                    <?php echo $this->_tpl_vars['rowName']; ?>

                </div>
                 <div class="content">
                    <?php echo $this->_tpl_vars['value']; ?>

                 </div>
                 <div class="clear"></div>
              </div>
            <?php endforeach; endif; unset($_from); ?>
        <?php endif; ?>
    </div>
<?php endif; ?> 