<?php /* Smarty version 2.6.26, created on 2010-08-16 22:19:35
         compiled from CRM/Contact/Form/Search/table.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Search/table.tpl', 32, false),)), $this); ?>
 <div id="map-field">
  <?php echo ''; ?><?php unset($this->_sections['blocks']);
$this->_sections['blocks']['start'] = (int)1;
$this->_sections['blocks']['name'] = 'blocks';
$this->_sections['blocks']['loop'] = is_array($_loop=$this->_tpl_vars['blockCount']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['blocks']['show'] = true;
$this->_sections['blocks']['max'] = $this->_sections['blocks']['loop'];
$this->_sections['blocks']['step'] = 1;
if ($this->_sections['blocks']['start'] < 0)
    $this->_sections['blocks']['start'] = max($this->_sections['blocks']['step'] > 0 ? 0 : -1, $this->_sections['blocks']['loop'] + $this->_sections['blocks']['start']);
else
    $this->_sections['blocks']['start'] = min($this->_sections['blocks']['start'], $this->_sections['blocks']['step'] > 0 ? $this->_sections['blocks']['loop'] : $this->_sections['blocks']['loop']-1);
if ($this->_sections['blocks']['show']) {
    $this->_sections['blocks']['total'] = min(ceil(($this->_sections['blocks']['step'] > 0 ? $this->_sections['blocks']['loop'] - $this->_sections['blocks']['start'] : $this->_sections['blocks']['start']+1)/abs($this->_sections['blocks']['step'])), $this->_sections['blocks']['max']);
    if ($this->_sections['blocks']['total'] == 0)
        $this->_sections['blocks']['show'] = false;
} else
    $this->_sections['blocks']['total'] = 0;
if ($this->_sections['blocks']['show']):

            for ($this->_sections['blocks']['index'] = $this->_sections['blocks']['start'], $this->_sections['blocks']['iteration'] = 1;
                 $this->_sections['blocks']['iteration'] <= $this->_sections['blocks']['total'];
                 $this->_sections['blocks']['index'] += $this->_sections['blocks']['step'], $this->_sections['blocks']['iteration']++):
$this->_sections['blocks']['rownum'] = $this->_sections['blocks']['iteration'];
$this->_sections['blocks']['index_prev'] = $this->_sections['blocks']['index'] - $this->_sections['blocks']['step'];
$this->_sections['blocks']['index_next'] = $this->_sections['blocks']['index'] + $this->_sections['blocks']['step'];
$this->_sections['blocks']['first']      = ($this->_sections['blocks']['iteration'] == 1);
$this->_sections['blocks']['last']       = ($this->_sections['blocks']['iteration'] == $this->_sections['blocks']['total']);
?><?php echo ''; ?><?php $this->assign('x', $this->_sections['blocks']['index']); ?><?php echo '<div class="crm-search-block"><h3>'; ?><?php if ($this->_tpl_vars['x'] == 1): ?><?php echo ''; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Include contacts where'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Also include contacts where'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo ''; ?><?php endif; ?><?php echo '</h3><table>'; ?><?php unset($this->_sections['cols']);
$this->_sections['cols']['name'] = 'cols';
$this->_sections['cols']['loop'] = is_array($_loop=$this->_tpl_vars['columnCount'][$this->_tpl_vars['x']]) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['cols']['show'] = true;
$this->_sections['cols']['max'] = $this->_sections['cols']['loop'];
$this->_sections['cols']['step'] = 1;
$this->_sections['cols']['start'] = $this->_sections['cols']['step'] > 0 ? 0 : $this->_sections['cols']['loop']-1;
if ($this->_sections['cols']['show']) {
    $this->_sections['cols']['total'] = $this->_sections['cols']['loop'];
    if ($this->_sections['cols']['total'] == 0)
        $this->_sections['cols']['show'] = false;
} else
    $this->_sections['cols']['total'] = 0;
if ($this->_sections['cols']['show']):

            for ($this->_sections['cols']['index'] = $this->_sections['cols']['start'], $this->_sections['cols']['iteration'] = 1;
                 $this->_sections['cols']['iteration'] <= $this->_sections['cols']['total'];
                 $this->_sections['cols']['index'] += $this->_sections['cols']['step'], $this->_sections['cols']['iteration']++):
$this->_sections['cols']['rownum'] = $this->_sections['cols']['iteration'];
$this->_sections['cols']['index_prev'] = $this->_sections['cols']['index'] - $this->_sections['cols']['step'];
$this->_sections['cols']['index_next'] = $this->_sections['cols']['index'] + $this->_sections['cols']['step'];
$this->_sections['cols']['first']      = ($this->_sections['cols']['iteration'] == 1);
$this->_sections['cols']['last']       = ($this->_sections['cols']['iteration'] == $this->_sections['cols']['total']);
?><?php echo ''; ?><?php $this->assign('i', $this->_sections['cols']['index']); ?><?php echo '<tr><td class="form-item even-row">'; ?><?php echo $this->_tpl_vars['form']['mapper'][$this->_tpl_vars['x']][$this->_tpl_vars['i']]['html']; ?><?php echo ''; ?><?php echo $this->_tpl_vars['form']['operator'][$this->_tpl_vars['x']][$this->_tpl_vars['i']]['html']; ?><?php echo '&nbsp;&nbsp;'; ?><?php echo $this->_tpl_vars['form']['value'][$this->_tpl_vars['x']][$this->_tpl_vars['i']]['html']; ?><?php echo '</td></tr>'; ?><?php endfor; endif; ?><?php echo '<tr><td class="form-item even-row underline-effect">'; ?><?php echo $this->_tpl_vars['form']['addMore'][$this->_tpl_vars['x']]['html']; ?><?php echo '</td></tr></table></div>'; ?><?php endfor; endif; ?><?php echo '<div class="underline-effect">'; ?><?php echo $this->_tpl_vars['form']['addBlock']['html']; ?><?php echo '</div>'; ?>

 </div>