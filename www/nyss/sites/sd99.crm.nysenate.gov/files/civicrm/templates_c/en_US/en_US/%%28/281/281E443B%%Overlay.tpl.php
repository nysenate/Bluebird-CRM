<?php /* Smarty version 2.6.26, created on 2010-08-20 07:38:54
         compiled from CRM/Profile/Page/Overlay.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'CRM/Profile/Page/Overlay.tpl', 31, false),)), $this); ?>
<?php if ($this->_tpl_vars['overlayProfile']): ?>
<table>
<tr><td><?php echo $this->_tpl_vars['displayName']; ?>
</td></tr>
<tr><td>
<?php $this->assign('count', '0'); ?>
<?php $this->assign('totalRows', count($this->_tpl_vars['row'])); ?>
<div class="crm-summary-col-0">
<?php $_from = $this->_tpl_vars['profileFields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['rowName'] => $this->_tpl_vars['field']):
?>
  <?php if ($this->_tpl_vars['count'] > $this->_tpl_vars['totalRows']/2): ?>
    </div>
    </td><td>
    <div class="crm-summary-col-1">
    <?php $this->assign('count', '1'); ?>
  <?php endif; ?>
  <div class="crm-section <?php echo $this->_tpl_vars['rowName']; ?>
-section">
    <div class="label">
        <?php echo $this->_tpl_vars['field']['label']; ?>

    </div>
     <div class="content">
        <?php echo $this->_tpl_vars['field']['value']; ?>

     </div>
     <div class="clear"></div>
  </div>
  <?php $this->assign('count', ($this->_tpl_vars['count']+1)); ?>
<?php endforeach; endif; unset($_from); ?>
</div>
</td></tr>
</table>
<?php endif; ?>