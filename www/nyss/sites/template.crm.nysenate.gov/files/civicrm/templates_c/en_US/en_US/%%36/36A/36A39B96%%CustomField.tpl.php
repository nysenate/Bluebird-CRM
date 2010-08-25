<?php /* Smarty version 2.6.26, created on 2010-07-28 00:15:24
         compiled from CRM/Custom/Form/CustomField.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Custom/Form/CustomField.tpl', 58, false),)), $this); ?>
<?php $this->assign('element_name', $this->_tpl_vars['element']['element_name']); ?>
<?php if ($this->_tpl_vars['element']['is_view'] == 0): ?>    <?php if ($this->_tpl_vars['element']['help_pre']): ?>
        <tr class="custom_field-help-pre-row <?php echo $this->_tpl_vars['element']['element_name']; ?>
-row-help-pre">
            <td>&nbsp;</td>
            <td class="html-adjust description"><?php echo $this->_tpl_vars['element']['help_pre']; ?>
</td>
        </tr>
    <?php endif; ?>
     <?php if ($this->_tpl_vars['element']['options_per_line'] != 0): ?>
        <tr class="custom_field-row <?php echo $this->_tpl_vars['element']['element_name']; ?>
-row">
            <td class="label"><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]['label']; ?>
</td>
            <td class="html-adjust">
                <?php $this->assign('count', '1'); ?>
                <table class="form-layout-compressed" style="margin-top: -0.5em;">
                    <tr>
                                                <?php $this->assign('index', '1'); ?>
                        <?php $_from = $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['outer'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['outer']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['item']):
        $this->_foreach['outer']['iteration']++;
?>
                            <?php if ($this->_tpl_vars['index'] < 10): ?>
                                <?php $this->assign('index', ($this->_tpl_vars['index']+1)); ?>
                            <?php else: ?>
                                <td class="labels font-light"><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']][$this->_tpl_vars['key']]['html']; ?>
</td>
                                <?php if ($this->_tpl_vars['count'] == $this->_tpl_vars['element']['options_per_line']): ?>
                                    </tr>
                                    <tr>
                                    <?php $this->assign('count', '1'); ?>
                                <?php else: ?>
                                    <?php $this->assign('count', ($this->_tpl_vars['count']+1)); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; endif; unset($_from); ?>
                        <?php if ($this->_tpl_vars['element']['html_type'] == 'Radio'): ?>
                            <td><span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('<?php echo $this->_tpl_vars['element_name']; ?>
', '<?php echo $this->_tpl_vars['form']['formName']; ?>
'); return false;" ><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>clear<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>)</span></td>
                        <?php endif; ?>
                    </tr>
                </table>
            </td>
        </tr>
            
        <?php if ($this->_tpl_vars['element']['help_post']): ?>
            <tr class="custom_field-help-post-row <?php echo $this->_tpl_vars['element']['element_name']; ?>
-row-help-post">
                <td>&nbsp;</td>
                <td class="description"><?php echo $this->_tpl_vars['element']['help_post']; ?>
<br />&nbsp;</td>
            </tr>
             <?php endif; ?>
    <?php else: ?>
        <tr class="custom_field-row <?php echo $this->_tpl_vars['element']['element_name']; ?>
-row">
            <td class="label"><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]['label']; ?>
</td>                                
            <td class="html-adjust">
                <?php if ($this->_tpl_vars['element']['data_type'] != 'Date'): ?>
                    <?php echo $this->_tpl_vars['form'][$this->_tpl_vars['element_name']]['html']; ?>
&nbsp;
                <?php elseif ($this->_tpl_vars['element']['skip_calendar'] != true): ?>
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => $this->_tpl_vars['element_name'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endif; ?>
                
                <?php if ($this->_tpl_vars['element']['html_type'] == 'Radio'): ?>
                    <span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('<?php echo $this->_tpl_vars['element_name']; ?>
', '<?php echo $this->_tpl_vars['form']['formName']; ?>
'); return false;" ><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>clear<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>)</span> 
                <?php elseif ($this->_tpl_vars['element']['data_type'] == 'File'): ?>
                    <?php if ($this->_tpl_vars['element']['element_value']['data']): ?>
                        <span class="html-adjust"><br />
                            &nbsp;<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Attached File<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>: &nbsp;
                            <?php if ($this->_tpl_vars['element']['element_value']['displayURL']): ?>
                                <a href="javascript:popUp('<?php echo $this->_tpl_vars['element']['element_value']['displayURL']; ?>
')" ><img src="<?php echo $this->_tpl_vars['element']['element_value']['displayURL']; ?>
" height = "<?php echo $this->_tpl_vars['element']['element_value']['imageThumbHeight']; ?>
" width="<?php echo $this->_tpl_vars['element']['element_value']['imageThumbWidth']; ?>
"></a>
                            <?php else: ?>
                                <a href="<?php echo $this->_tpl_vars['element']['element_value']['fileURL']; ?>
"><?php echo $this->_tpl_vars['element']['element_value']['fileName']; ?>
</a>
                            <?php endif; ?>
                            <?php if ($this->_tpl_vars['element']['element_value']['deleteURL']): ?>
                                <br />
                            <?php echo $this->_tpl_vars['element']['element_value']['deleteURL']; ?>

                            <?php endif; ?>	
                        </span>  
                    <?php endif; ?> 
                <?php elseif ($this->_tpl_vars['element']['html_type'] == 'Autocomplete-Select'): ?>
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/AutoComplete.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endif; ?>
            </td>
        </tr>
        
        <?php if ($this->_tpl_vars['element']['help_post']): ?>

<td>&nbsp;</td>
<td class="description"><?php echo $this->_tpl_vars['element']['help_post']; ?>
<br />&nbsp;</td>
</tr>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>