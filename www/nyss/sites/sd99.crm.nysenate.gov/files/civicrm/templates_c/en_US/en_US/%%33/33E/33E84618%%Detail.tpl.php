<?php /* Smarty version 2.6.26, created on 2010-08-23 15:58:10
         compiled from CRM/Report/Form/Contact/Detail.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'CRM/Report/Form/Contact/Detail.tpl', 64, false),array('modifier', 'crmDate', 'CRM/Report/Form/Contact/Detail.tpl', 73, false),array('modifier', 'truncate', 'CRM/Report/Form/Contact/Detail.tpl', 77, false),array('modifier', 'crmMoney', 'CRM/Report/Form/Contact/Detail.tpl', 80, false),array('modifier', 'replace', 'CRM/Report/Form/Contact/Detail.tpl', 97, false),array('modifier', 'upper', 'CRM/Report/Form/Contact/Detail.tpl', 97, false),array('function', 'cycle', 'CRM/Report/Form/Contact/Detail.tpl', 107, false),)), $this); ?>
    <?php if (! $this->_tpl_vars['section']): ?>
    <div class="crm-block crm-form-block crm-report-field-form-block">    
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Report/Form/Fields.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </div>
    <?php endif; ?>    
	
<div class="crm-block crm-content-block crm-report-form-block">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Report/Form/Actions.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php if (! $this->_tpl_vars['section']): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Report/Form/Statistics.tpl", 'smarty_include_vars' => array('top' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
    <?php if ($this->_tpl_vars['rows']): ?>
        <div class="report-pager">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pager.tpl", 'smarty_include_vars' => array('location' => 'top','noForm' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </div>
        <?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
                	<table class="report-layout crm-report_contact_civireport">
                            <tr>
                                <?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['header']):
?>
                                    <?php if (! $this->_tpl_vars['skip']): ?>
                                        <?php if ($this->_tpl_vars['header']['colspan']): ?>
                                            <th colspan=<?php echo $this->_tpl_vars['header']['colspan']; ?>
><?php echo $this->_tpl_vars['header']['title']; ?>
</th>
                                            <?php $this->assign('skip', true); ?>
                                            <?php $this->assign('skipCount', ($this->_tpl_vars['header']['colspan'])); ?>
                                            <?php $this->assign('skipMade', 1); ?>
                                        <?php else: ?>
                                            <th><?php echo $this->_tpl_vars['header']['title']; ?>
</th>
                                            <?php $this->assign('skip', false); ?>
                                        <?php endif; ?>
                                    <?php else: ?>                                         <?php $this->assign('skipMade', ($this->_tpl_vars['skipMade']+1)); ?>
                                        <?php if ($this->_tpl_vars['skipMade'] >= $this->_tpl_vars['skipCount']): ?><?php $this->assign('skip', false); ?><?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; endif; unset($_from); ?>
                            </tr>               
                            <tr class="group-row crm-report">
                                <?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['header']):
?>
                                    <?php $this->assign('fieldLink', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_link') : smarty_modifier_cat($_tmp, '_link'))); ?>
                                    <?php $this->assign('fieldHover', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_hover') : smarty_modifier_cat($_tmp, '_hover'))); ?>
                                    <td  class="report-contents crm-report_<?php echo $this->_tpl_vars['field']; ?>
">
                                        <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]): ?><a title="<?php echo $this->_tpl_vars['row'][$this->_tpl_vars['fieldHover']]; ?>
" href="<?php echo $this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]; ?>
"><?php endif; ?>
                        
                                        <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['field']] == 'Subtotal'): ?>
                                            <?php echo $this->_tpl_vars['row'][$this->_tpl_vars['field']]; ?>

                                        <?php elseif ($this->_tpl_vars['header']['type'] == 12): ?>
                                            <?php if ($this->_tpl_vars['header']['group_by'] == 'MONTH' || $this->_tpl_vars['header']['group_by'] == 'QUARTER'): ?>
                                                <?php echo ((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['config']->dateformatPartial) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['config']->dateformatPartial)); ?>

                                            <?php elseif ($this->_tpl_vars['header']['group_by'] == 'YEAR'): ?>	
                                                <?php echo ((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmDate', true, $_tmp, $this->_tpl_vars['config']->dateformatYear) : smarty_modifier_crmDate($_tmp, $this->_tpl_vars['config']->dateformatYear)); ?>

                                            <?php else: ?>				
                                                <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('truncate', true, $_tmp, 10, '') : smarty_modifier_truncate($_tmp, 10, '')))) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>

                                            <?php endif; ?>	
                                        <?php elseif ($this->_tpl_vars['header']['type'] == 1024): ?>
                                            <?php echo ((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmMoney', true, $_tmp) : smarty_modifier_crmMoney($_tmp)); ?>

                                        <?php else: ?>
                                            <?php echo $this->_tpl_vars['row'][$this->_tpl_vars['field']]; ?>

                                        <?php endif; ?>
				
                                        <?php if ($this->_tpl_vars['row']['contactID']): ?> <?php endif; ?>
                                    
                                        <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]): ?></a><?php endif; ?>
                                    </td>
                                <?php endforeach; endif; unset($_from); ?>
                            </tr>
                        </table>

                        <?php if ($this->_tpl_vars['columnHeadersComponent']): ?>
                            <?php $this->assign('componentContactId', $this->_tpl_vars['row']['contactID']); ?>
                            <?php $_from = $this->_tpl_vars['columnHeadersComponent']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['component'] => $this->_tpl_vars['pheader']):
?>
                                <?php if ($this->_tpl_vars['componentRows'][$this->_tpl_vars['componentContactId']][$this->_tpl_vars['component']]): ?>
                                    <h3><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['component'])) ? $this->_run_mod_handler('replace', true, $_tmp, '_civireport', '') : smarty_modifier_replace($_tmp, '_civireport', '')))) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)); ?>
</h3>
                        	<table class="report-layout crm-report_<?php echo $this->_tpl_vars['component']; ?>
">
                        	                            		<tr>
                        		    <?php $_from = $this->_tpl_vars['pheader']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['header']):
?>
                        			<th><?php echo $this->_tpl_vars['header']['title']; ?>
</th>
                        		    <?php endforeach; endif; unset($_from); ?>
                        		</tr>
                             
                        	    <?php $_from = $this->_tpl_vars['componentRows'][$this->_tpl_vars['componentContactId']][$this->_tpl_vars['component']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['rowid'] => $this->_tpl_vars['row']):
?>
                        		<tr class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
 crm-report" id="crm-report_<?php echo $this->_tpl_vars['rowid']; ?>
">
                        		    <?php $_from = $this->_tpl_vars['columnHeadersComponent'][$this->_tpl_vars['component']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['header']):
?>
                        			<?php $this->assign('fieldLink', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_link') : smarty_modifier_cat($_tmp, '_link'))); ?>
                                                <?php $this->assign('fieldHover', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_hover') : smarty_modifier_cat($_tmp, '_hover'))); ?>
                        			<td class="report-contents crm-report_<?php echo $this->_tpl_vars['field']; ?>
">
                        			    <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]): ?>
                        				<a title="<?php echo $this->_tpl_vars['row'][$this->_tpl_vars['fieldHover']]; ?>
 "href="<?php echo $this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]; ?>
">
                        			    <?php endif; ?>
                        
                        			    <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['field']] == 'Sub Total'): ?>
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
                        				<?php echo ((is_array($_tmp=$this->_tpl_vars['row'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmMoney', true, $_tmp) : smarty_modifier_crmMoney($_tmp)); ?>

                        			    <?php else: ?>
                        				<?php echo $this->_tpl_vars['row'][$this->_tpl_vars['field']]; ?>

                        			    <?php endif; ?>
                        
                        			    <?php if ($this->_tpl_vars['row'][$this->_tpl_vars['fieldLink']]): ?></a><?php endif; ?>
                        			</td>
                        		    <?php endforeach; endif; unset($_from); ?>
                        		</tr>
                        	    <?php endforeach; endif; unset($_from); ?>
                        	</table>
                            <?php endif; ?>	
                            <?php endforeach; endif; unset($_from); ?>
                        <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>

	<div class="report-pager">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pager.tpl", 'smarty_include_vars' => array('noForm' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </div>
        <br />
        <?php if ($this->_tpl_vars['grandStat']): ?>
            <table class="report-layout">
                <tr>
                    <?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['header']):
?>
                        <td>
                            <strong>
                                <?php if ($this->_tpl_vars['header']['type'] == 1024): ?>
                                    <?php echo ((is_array($_tmp=$this->_tpl_vars['grandStat'][$this->_tpl_vars['field']])) ? $this->_run_mod_handler('crmMoney', true, $_tmp) : smarty_modifier_crmMoney($_tmp)); ?>

                                <?php else: ?>
                                    <?php echo $this->_tpl_vars['grandStat'][$this->_tpl_vars['field']]; ?>

                                <?php endif; ?>
                            </strong>
                        </td>
                    <?php endforeach; endif; unset($_from); ?>
                </tr>
            </table>
        <?php endif; ?>
        
        <?php if (! $this->_tpl_vars['section']): ?>
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Report/Form/Statistics.tpl", 'smarty_include_vars' => array('bottom' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php endif; ?>
    <?php endif; ?> 
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Report/Form/ErrorMessage.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>