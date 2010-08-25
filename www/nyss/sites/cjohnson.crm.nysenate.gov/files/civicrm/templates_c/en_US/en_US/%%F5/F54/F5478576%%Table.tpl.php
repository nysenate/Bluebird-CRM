<?php /* Smarty version 2.6.26, created on 2010-08-13 11:34:47
         compiled from CRM/Report/Form/Layout/Table.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', 'CRM/Report/Form/Layout/Table.tpl', 61, false),array('modifier', 'cat', 'CRM/Report/Form/Layout/Table.tpl', 63, false),array('modifier', 'crmDate', 'CRM/Report/Form/Layout/Table.tpl', 74, false),array('modifier', 'truncate', 'CRM/Report/Form/Layout/Table.tpl', 78, false),array('modifier', 'crmMoney', 'CRM/Report/Form/Layout/Table.tpl', 81, false),)), $this); ?>
<?php if (( ! $this->_tpl_vars['chartEnabled'] || ! $this->_tpl_vars['chartSupported'] ) && $this->_tpl_vars['rows']): ?>
    <?php if ($this->_tpl_vars['pager'] && $this->_tpl_vars['pager']->_response && $this->_tpl_vars['pager']->_response['numPages'] > 1): ?>
        <div class="report-pager">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pager.tpl", 'smarty_include_vars' => array('location' => 'top','noForm' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </div>
    <?php endif; ?>
    <table class="report-layout">
        <thead class="sticky">
        <tr> 
            <?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['header']):
?>
                <?php $this->assign('class', ""); ?>
                <?php if ($this->_tpl_vars['header']['type'] == 1024 || $this->_tpl_vars['header']['type'] == 1): ?>
        		    <?php $this->assign('class', "class='reports-header-right'"); ?>
                <?php else: ?>
                    <?php $this->assign('class', "class='reports-header'"); ?>
                <?php endif; ?>
                <?php if (! $this->_tpl_vars['skip']): ?>
                   <?php if ($this->_tpl_vars['header']['colspan']): ?>
                       <th colspan=<?php echo $this->_tpl_vars['header']['colspan']; ?>
><?php echo $this->_tpl_vars['header']['title']; ?>
</th>
                      <?php $this->assign('skip', true); ?>
                      <?php $this->assign('skipCount', ($this->_tpl_vars['header']['colspan'])); ?>
                      <?php $this->assign('skipMade', 1); ?>
                   <?php else: ?>
                       <th <?php echo $this->_tpl_vars['class']; ?>
><?php echo $this->_tpl_vars['header']['title']; ?>
</th> 
                   <?php $this->assign('skip', false); ?>
                   <?php endif; ?>
                <?php else: ?>                    <?php $this->assign('skipMade', ($this->_tpl_vars['skipMade']+1)); ?>
                   <?php if ($this->_tpl_vars['skipMade'] >= $this->_tpl_vars['skipCount']): ?><?php $this->assign('skip', false); ?><?php endif; ?>
                <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
        </tr>          
        </thead>
       
        <?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['rowid'] => $this->_tpl_vars['row']):
?>
            <tr  class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
 crm-report" id="crm-report_<?php echo $this->_tpl_vars['rowid']; ?>
">
                <?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['header']):
?>
                    <?php $this->assign('fieldLink', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_link') : smarty_modifier_cat($_tmp, '_link'))); ?>
                    <?php $this->assign('fieldHover', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_hover') : smarty_modifier_cat($_tmp, '_hover'))); ?>
                    <td class="crm-report-<?php echo $this->_tpl_vars['field']; ?>
<?php if ($this->_tpl_vars['header']['type'] == 1024 || $this->_tpl_vars['header']['type'] == 1): ?> report-contents-right<?php elseif ($this->_tpl_vars['row'][$this->_tpl_vars['field']] == 'Subtotal'): ?> report-label<?php endif; ?>">
                        <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]): ?>
                            <a title="<?php echo $this->_tpl_vars['row'][$this->_tpl_vars['fieldHover']]; ?>
" href="<?php echo $this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]; ?>
">
                        <?php endif; ?>
                        
                        <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['field']] == 'Subtotal'): ?>
                            <?php echo $this->_tpl_vars['row'][$this->_tpl_vars['field']]; ?>

                        <?php elseif ($this->_tpl_vars['header']['type'] & 4): ?>
                            <?php if ($this->_tpl_vars['header']['group_by'] == 'MONTH' || $this->_tpl_vars['header']['group_by'] == 'QUARTER'): ?>
                                <?php echo ((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['config']->dateformatPartial) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['config']->dateformatPartial)); ?>

                            <?php elseif ($this->_tpl_vars['header']['group_by'] == 'YEAR'): ?>	
                                <?php echo ((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['config']->dateformatYear) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['config']->dateformatYear)); ?>

                            <?php else: ?>		
                                <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('truncate', true, $_tmp, 10, '') : smarty_modifier_truncate($_tmp, 10, '')))) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>

                            <?php endif; ?>	
                        <?php elseif ($this->_tpl_vars['header']['type'] == 1024): ?>
                            <span class="nowrap"><?php echo ((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmMoney', true, $_tmp) : smarty_modifier_crmMoney($_tmp)); ?>
</span>
                        <?php else: ?>
                            <?php echo $this->_tpl_vars['row'][$this->_tpl_vars['field']]; ?>

                        <?php endif; ?>
                        
                        <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]): ?></a><?php endif; ?>
                    </td>
                <?php endforeach; endif; unset($_from); ?>
            </tr>
        <?php endforeach; endif; unset($_from); ?>
        
        <?php if ($this->_tpl_vars['grandStat']): ?>
                        <tr class="total-row">
                <?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['header']):
?>
                    <td class="report-label">
                        <?php if ($this->_tpl_vars['header']['type'] == 1024): ?>
                            <?php echo ((is_array($_tmp=$this->_tpl_vars['grandStat'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmMoney', true, $_tmp) : smarty_modifier_crmMoney($_tmp)); ?>

                        <?php else: ?>
                            <?php echo $this->_tpl_vars['grandStat'][$this->_tpl_vars['field']]; ?>

                        <?php endif; ?>
                    </td>
                <?php endforeach; endif; unset($_from); ?>
            </tr>
                    <?php endif; ?>
    </table>
    <?php if ($this->_tpl_vars['pager'] && $this->_tpl_vars['pager']->_response && $this->_tpl_vars['pager']->_response['numPages'] > 1): ?>
        <div class="report-pager">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pager.tpl", 'smarty_include_vars' => array('noForm' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </div>
    <?php endif; ?>
<?php endif; ?>        