<?php /* Smarty version 2.6.26, created on 2010-07-26 17:13:28
         compiled from CRM/Admin/Form/DedupeRules.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/DedupeRules.tpl', 28, false),array('function', 'help', 'CRM/Admin/Form/DedupeRules.tpl', 30, false),array('function', 'cycle', 'CRM/Admin/Form/DedupeRules.tpl', 53, false),)), $this); ?>

<div class="crm-block crm-form-block crm-dedupe-rules-form-block">
  <h2><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['contact_type'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Matching Rules for %1 Contacts<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h2>
    <div id="help">
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Configure up to five fields to evaluate when searching for 'suspected' duplicate contact records.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => "id-rules"), $this);?>

    </div>
    <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
  <table class="form-layout-compressed">
     <tr class="crm-dedupe-rules-form-block-label">
        <td class="label"><?php echo $this->_tpl_vars['form']['name']['label']; ?>
</td>
        <td><?php echo $this->_tpl_vars['form']['name']['html']; ?>
</td>
     </tr>
     <tr class="crm-dedupe-rules-form-block-level">
        <td class="label"><?php echo $this->_tpl_vars['form']['level']['label']; ?>
</td>
        <td><?php echo $this->_tpl_vars['form']['level']['html']; ?>
</td>
     </tr>
     <tr class="crm-dedupe-rules-form-block-is_default">
        <td class="label"><?php echo $this->_tpl_vars['form']['is_default']['label']; ?>
</td>
        <td><?php echo $this->_tpl_vars['form']['is_default']['html']; ?>
</td>
     </tr>
  </table>
  <table style="width: auto;">
     <tr class="columnheader"><th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Field<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th><th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Length<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th><th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Weight<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th></tr>
         <?php unset($this->_sections['count']);
$this->_sections['count']['name'] = 'count';
$this->_sections['count']['loop'] = is_array($_loop=5) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['count']['show'] = true;
$this->_sections['count']['max'] = $this->_sections['count']['loop'];
$this->_sections['count']['step'] = 1;
$this->_sections['count']['start'] = $this->_sections['count']['step'] > 0 ? 0 : $this->_sections['count']['loop']-1;
if ($this->_sections['count']['show']) {
    $this->_sections['count']['total'] = $this->_sections['count']['loop'];
    if ($this->_sections['count']['total'] == 0)
        $this->_sections['count']['show'] = false;
} else
    $this->_sections['count']['total'] = 0;
if ($this->_sections['count']['show']):

            for ($this->_sections['count']['index'] = $this->_sections['count']['start'], $this->_sections['count']['iteration'] = 1;
                 $this->_sections['count']['iteration'] <= $this->_sections['count']['total'];
                 $this->_sections['count']['index'] += $this->_sections['count']['step'], $this->_sections['count']['iteration']++):
$this->_sections['count']['rownum'] = $this->_sections['count']['iteration'];
$this->_sections['count']['index_prev'] = $this->_sections['count']['index'] - $this->_sections['count']['step'];
$this->_sections['count']['index_next'] = $this->_sections['count']['index'] + $this->_sections['count']['step'];
$this->_sections['count']['first']      = ($this->_sections['count']['iteration'] == 1);
$this->_sections['count']['last']       = ($this->_sections['count']['iteration'] == $this->_sections['count']['total']);
?>
         <?php ob_start(); ?>where_<?php echo $this->_sections['count']['index']; ?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('where', ob_get_contents());ob_end_clean(); ?>
         <?php ob_start(); ?>length_<?php echo $this->_sections['count']['index']; ?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('length', ob_get_contents());ob_end_clean(); ?>
         <?php ob_start(); ?>weight_<?php echo $this->_sections['count']['index']; ?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('weight', ob_get_contents());ob_end_clean(); ?>
     <tr class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
          <td><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['where']]['html']; ?>
</td>
          <td><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['length']]['html']; ?>
</td>
          <td><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['weight']]['html']; ?>
</td>
     </tr>
    <?php endfor; endif; ?>
    <tr class="columnheader"><th colspan="2" style="text-align: right;"><?php echo $this->_tpl_vars['form']['threshold']['label']; ?>
</th>
        <td><?php echo $this->_tpl_vars['form']['threshold']['html']; ?>
</td>
    </tr>
 </table>
  <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>