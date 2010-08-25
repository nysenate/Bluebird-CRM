<?php /* Smarty version 2.6.26, created on 2010-07-06 10:38:35
         compiled from CRM/Import/Form/CSV.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Import/Form/CSV.tpl', 26, false),)), $this); ?>
<fieldset><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Upload CSV File<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
  <table class="form-layout">
    <tr>
        <td class="label"><?php echo $this->_tpl_vars['form']['uploadFile']['label']; ?>
</td>
        <td><?php echo $this->_tpl_vars['form']['uploadFile']['html']; ?>
<br />
            <div class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>File format must be comma-separated-values (CSV). File must be UTF8 encoded if it contains special characters (e.g. accented letters, etc.).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
            <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['uploadSize'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Maximum Upload File Size: %1 MB<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td><?php echo $this->_tpl_vars['form']['skipColumnHeader']['html']; ?>
 <?php echo $this->_tpl_vars['form']['skipColumnHeader']['label']; ?>

            <div class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Check this box if the first row of your file consists of field names (Example: 'First Name','Last Name','Email')<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
        </td>
    </tr>
  </table>
</fieldset>