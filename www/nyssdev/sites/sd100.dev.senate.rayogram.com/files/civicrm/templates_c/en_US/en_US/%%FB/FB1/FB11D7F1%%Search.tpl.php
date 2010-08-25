<?php /* Smarty version 2.6.26, created on 2010-05-24 17:19:01
         compiled from CRM/Custom/Form/Search.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Custom/Form/Search.tpl', 30, false),array('modifier', 'cat', 'CRM/Custom/Form/Search.tpl', 42, false),array('modifier', 'crmReplace', 'CRM/Custom/Form/Search.tpl', 89, false),)), $this); ?>
<?php if ($this->_tpl_vars['groupTree']): ?>
<?php $_from = $this->_tpl_vars['groupTree']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['group_id'] => $this->_tpl_vars['cd_edit']):
?>
<?php if ($this->_tpl_vars['showHideLinks'] || $this->_tpl_vars['form']['formName'] == 'Advanced'): ?>
  <div id="<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_show" class="section-hidden section-hidden-border">
    <a href="#" onclick="hide('<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_show'); show('<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
'); return false;"><img src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/TreePlus.gif" class="action-icon" alt="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>open section<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" /></a><label><?php echo $this->_tpl_vars['cd_edit']['title']; ?>
</label><br />
  </div>
<?php endif; ?>

  <div id="<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
" class="form-item">
  <fieldset id="<?php echo $this->_tpl_vars['cd_edit']['extends_entity_column_value']; ?>
"><legend>
<?php if ($this->_tpl_vars['showHideLinks'] || $this->_tpl_vars['form']['formName'] == 'Advanced'): ?>
<a href="#" onclick="hide('<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
'); show('<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_show'); return false;"><img src="<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/TreeMinus.gif" class="action-icon" alt="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>close section<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" /></a>
<?php endif; ?>
<?php echo $this->_tpl_vars['cd_edit']['title']; ?>
</legend>
    <dl>
    <?php $_from = $this->_tpl_vars['cd_edit']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field_id'] => $this->_tpl_vars['element']):
?>
      <?php $this->assign('element_name', ((is_array($_tmp='custom_')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['field_id']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['field_id']))); ?>
      <?php if ($this->_tpl_vars['element']['options_per_line'] != 0): ?>
         <dt><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]['label']; ?>
</dt>
         <dd>
            <?php $this->assign('count', '1'); ?>
            <?php echo '<table class="form-layout-compressed"><tr>'; ?><?php echo ''; ?><?php $this->assign('index', '1'); ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['outer'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['outer']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['item']):
        $this->_foreach['outer']['iteration']++;
?><?php echo ''; ?><?php if ($this->_tpl_vars['index'] < 10): ?><?php echo ' '; ?><?php echo ''; ?><?php $this->assign('index', ($this->_tpl_vars['index']+1)); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php if ($this->_tpl_vars['element']['html_type'] == 'CheckBox' && ($this->_foreach['outer']['iteration'] == $this->_foreach['outer']['total']) == 1): ?><?php echo ' '; ?><?php echo '</tr><tr><td class="op-checkbox" colspan="'; ?><?php echo $this->_tpl_vars['element']['options_per_line']; ?><?php echo '" style="padding-top: 0px;">'; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']][$this->_tpl_vars['key']]['html']; ?><?php echo '</td>'; ?><?php else: ?><?php echo '<td class="labels font-light">'; ?><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']][$this->_tpl_vars['key']]['html']; ?><?php echo '</td>'; ?><?php if ($this->_tpl_vars['count'] == $this->_tpl_vars['element']['options_per_line']): ?><?php echo '</tr><tr>'; ?><?php $this->assign('count', '1'); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->assign('count', ($this->_tpl_vars['count']+1)); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php endforeach; endif; unset($_from); ?><?php echo '</tr>'; ?><?php if ($this->_tpl_vars['element']['html_type'] == 'Radio'): ?><?php echo '<tr style="line-height: .75em; margin-top: 1px;"><td> <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio(\''; ?><?php echo $this->_tpl_vars['element_name']; ?><?php echo '\', \''; ?><?php echo $this->_tpl_vars['form']['formName']; ?><?php echo '\'); return false;">'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'clear'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a>)</span></td></tr>'; ?><?php endif; ?><?php echo '</table>'; ?>

            </dd>
        <?php else: ?>
            <?php $this->assign('type', ($this->_tpl_vars['element']['html_type'])); ?>
            <?php $this->assign('element_name', ((is_array($_tmp='custom_')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['field_id']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['field_id']))); ?>
            <?php if ($this->_tpl_vars['element']['is_search_range']): ?>
                <?php $this->assign('element_name_from', ((is_array($_tmp=$this->_tpl_vars['element_name'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_from') : smarty_modifier_cat($_tmp, '_from'))); ?>
                <?php $this->assign('element_name_to', ((is_array($_tmp=$this->_tpl_vars['element_name'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_to') : smarty_modifier_cat($_tmp, '_to'))); ?>
                <?php if ($this->_tpl_vars['element']['data_type'] != 'Date'): ?>
                    <dt><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name_from']]['label']; ?>
</dt><dd>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['form'][$this->_tpl_vars['element_name_from']]['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'six') : smarty_modifier_crmReplace($_tmp, 'class', 'six')); ?>

                    &nbsp;&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name_to']]['label']; ?>
&nbsp;&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['form'][$this->_tpl_vars['element_name_to']]['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'six') : smarty_modifier_crmReplace($_tmp, 'class', 'six')); ?>

                <?php elseif ($this->_tpl_vars['element']['skip_calendar'] != true): ?>
                    <dt><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name_from']]['label']; ?>
</dt><dd>
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => $this->_tpl_vars['element_name_from'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                    &nbsp;&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name_to']]['label']; ?>
&nbsp;&nbsp;
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => $this->_tpl_vars['element_name_to'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endif; ?>
            <?php else: ?>
                <dt><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]['label']; ?>
</dt><dd>&nbsp;
                <?php if ($this->_tpl_vars['element']['data_type'] != 'Date'): ?>
                    <?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]['html']; ?>

                <?php elseif ($this->_tpl_vars['element']['skip_calendar'] != true): ?>
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => $this->_tpl_vars['element_name'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>    
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['element']['html_type'] == 'Radio'): ?>
                &nbsp; <a href="#" title="unselect" onclick="unselectRadio('<?php echo $this->_tpl_vars['element_name']; ?>
', '<?php echo $this->_tpl_vars['form']['formName']; ?>
'); return false;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>unselect<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
            <?php elseif ($this->_tpl_vars['element']['html_type'] == 'Autocomplete-Select'): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/AutoComplete.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
            </dd>
	    <?php endif; ?>
	    <?php endforeach; endif; unset($_from); ?>
	    </dl>
	 </fieldset>
    </div>
 
<?php if ($this->_tpl_vars['form']['formName'] == 'Advanced'): ?>
<script type="text/javascript">
<?php if ($this->_tpl_vars['cd_edit']['collapse_adv_display'] == 0): ?>
	hide("<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_show"); show("<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
");
<?php else: ?>
	show("<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_show"); hide("<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
");
<?php endif; ?>
</script>
<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
<?php endif; ?>
