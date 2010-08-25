<?php /* Smarty version 2.6.26, created on 2010-08-10 00:11:52
         compiled from CRM/Report/Form/Criteria.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'CRM/Report/Form/Criteria.tpl', 91, false),array('modifier', 'cat', 'CRM/Report/Form/Criteria.tpl', 94, false),)), $this); ?>
    <?php if ($this->_tpl_vars['colGroups']): ?>

	           <h3>Display Columns</h3>
 
        <?php $_from = $this->_tpl_vars['colGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dnc'] => $this->_tpl_vars['grpFields']):
?>
            <?php $this->assign('count', '0'); ?>
            <table class="criteria-group">
                <?php if ($this->_tpl_vars['grpFields']['group_title']): ?><tr><td colspan=4>&raquo;&nbsp;<?php echo $this->_tpl_vars['grpFields']['group_title']; ?>
:</td></tr><?php endif; ?>
                <tr class="crm-report crm-report-criteria-field crm-report-criteria-field-<?php echo $this->_tpl_vars['dnc']; ?>
">
                    <?php $_from = $this->_tpl_vars['grpFields']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['title']):
?>
                        <?php $this->assign('count', ($this->_tpl_vars['count']+1)); ?>
                        <td width="25%"><?php echo $this->_tpl_vars['form']['fields'][$this->_tpl_vars['field']]['html']; ?>
</td>
                        <?php if (!($this->_tpl_vars['count'] % 4)): ?>
                            </tr><tr class="crm-report crm-report-criteria-field crm-report-criteria-field_<?php echo $this->_tpl_vars['dnc']; ?>
">
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?>
                    <?php if (!(!($this->_tpl_vars['count'] % 4))): ?>
                        <td colspan="4 - ($count % 4)"></td>
                    <?php endif; ?>
                </tr>
            </table>
        <?php endforeach; endif; unset($_from); ?>
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['groupByElements']): ?>
        <h3>Group by Columns</h3>
        <?php $this->assign('count', '0'); ?>
        <table class="report-layout">
            <tr class="crm-report crm-report-criteria-groupby">
                <?php $_from = $this->_tpl_vars['groupByElements']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dnc'] => $this->_tpl_vars['gbElem']):
?>
                    <?php $this->assign('count', ($this->_tpl_vars['count']+1)); ?>
                    <td width="25%" <?php if ($this->_tpl_vars['form']['fields'][$this->_tpl_vars['gbElem']]): ?> onClick="selectGroupByFields('<?php echo $this->_tpl_vars['gbElem']; ?>
');"<?php endif; ?>>
                        <?php echo $this->_tpl_vars['form']['group_bys'][$this->_tpl_vars['gbElem']]['html']; ?>

                        <?php if ($this->_tpl_vars['form']['group_bys_freq'][$this->_tpl_vars['gbElem']]['html']): ?>:<br>
                            &nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['group_bys_freq'][$this->_tpl_vars['gbElem']]['label']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['group_bys_freq'][$this->_tpl_vars['gbElem']]['html']; ?>

                        <?php endif; ?>
                    </td>
                    <?php if (!($this->_tpl_vars['count'] % 4)): ?>
                        </tr><tr class="crm-report crm-report-criteria-groupby">
                    <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?>
                <?php if (!(!($this->_tpl_vars['count'] % 4))): ?>
                    <td colspan="4 - ($count % 4)"></td>
                <?php endif; ?>
            </tr>
        </table>      
    <?php endif; ?>

    <?php if ($this->_tpl_vars['form']['options']['html'] || $this->_tpl_vars['form']['options']['html']): ?>
        <h3>Other Options</h3>
        <table class="report-layout">
            <tr class="crm-report crm-report-criteria-groupby">
	        <td><?php echo $this->_tpl_vars['form']['options']['html']; ?>
</td>
	        <?php if ($this->_tpl_vars['form']['blank_column_end']): ?>
	            <td><?php echo $this->_tpl_vars['form']['blank_column_end']['label']; ?>
&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['blank_column_end']['html']; ?>
</td>
                <?php endif; ?>
            </tr>
        </table>
    <?php endif; ?>
  
    <?php if ($this->_tpl_vars['filters']): ?>
        <h3>Set Filters</h3>
        <table class="report-layout">
            <?php $_from = $this->_tpl_vars['filters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tableName'] => $this->_tpl_vars['table']):
?>
 	        <?php $this->assign('filterCount', count($this->_tpl_vars['table'])); ?>
	        <?php if ($this->_tpl_vars['colGroups'][$this->_tpl_vars['tableName']]['group_title'] && $this->_tpl_vars['filterCount'] >= 1): ?></table><table class="report-layout"><tr class="crm-report crm-report-criteria-filter crm-report-criteria-filter-<?php echo $this->_tpl_vars['tableName']; ?>
"><td colspan=3>&raquo;&nbsp;<?php echo $this->_tpl_vars['colGroups'][$this->_tpl_vars['tableName']]['group_title']; ?>
:</td></tr><?php endif; ?> 
                <?php $_from = $this->_tpl_vars['table']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['fieldName'] => $this->_tpl_vars['field']):
?>
                    <?php $this->assign('fieldOp', ((is_array($_tmp=$this->_tpl_vars['fieldName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_op') : smarty_modifier_cat($_tmp, '_op'))); ?>
                    <?php $this->assign('filterVal', ((is_array($_tmp=$this->_tpl_vars['fieldName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_value') : smarty_modifier_cat($_tmp, '_value'))); ?>
                    <?php $this->assign('filterMin', ((is_array($_tmp=$this->_tpl_vars['fieldName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_min') : smarty_modifier_cat($_tmp, '_min'))); ?>
                    <?php $this->assign('filterMax', ((is_array($_tmp=$this->_tpl_vars['fieldName'])) ? $this->_run_mod_handler('cat', true, $_tmp, '_max') : smarty_modifier_cat($_tmp, '_max'))); ?>
                    <?php if ($this->_tpl_vars['field']['operatorType'] & 4): ?>
                        <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-<?php echo $this->_tpl_vars['tableName']; ?>
">
                            <td class="label report-contents"><?php echo $this->_tpl_vars['field']['title']; ?>
</td>
                            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Core/DateRange.tpl", 'smarty_include_vars' => array('fieldName' => $this->_tpl_vars['fieldName'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                        </tr>
                    <?php elseif ($this->_tpl_vars['form'][$this->_tpl_vars['fieldOp']]['html']): ?>
                        <tr class="report-contents crm-report crm-report-criteria-filter crm-report-criteria-filter-<?php echo $this->_tpl_vars['tableName']; ?>
" <?php if ($this->_tpl_vars['field']['no_display']): ?> style="display: none;"<?php endif; ?>>
                            <td class="label report-contents"><?php echo $this->_tpl_vars['field']['title']; ?>
</td>
                            <td class="report-contents"><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['fieldOp']]['html']; ?>
</td>
                            <td>
                               <span id="<?php echo $this->_tpl_vars['filterVal']; ?>
_cell"><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['filterVal']]['label']; ?>
&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['filterVal']]['html']; ?>
</span>
                               <span id="<?php echo $this->_tpl_vars['filterMin']; ?>
_max_cell"><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['filterMin']]['label']; ?>
&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['filterMin']]['html']; ?>
&nbsp;&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['filterMax']]['label']; ?>
&nbsp;<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['filterMax']]['html']; ?>
</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; endif; unset($_from); ?>
            <?php endforeach; endif; unset($_from); ?>
        </table>
    <?php endif; ?>
 
    <?php echo '
    <script type="text/javascript">
    '; ?>

        <?php $_from = $this->_tpl_vars['filters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tableName'] => $this->_tpl_vars['table']):
?>
            <?php $_from = $this->_tpl_vars['table']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['fieldName'] => $this->_tpl_vars['field']):
?>
		<?php echo 'var val = "dnc";'; ?>

		<?php if (! ( $this->_tpl_vars['field']['operatorType'] == 4 ) && ! $this->_tpl_vars['field']['no_display']): ?> 
                    <?php echo 'var val = document.getElementById("'; ?>
<?php echo $this->_tpl_vars['fieldName']; ?>
_op<?php echo '").value;'; ?>

		<?php endif; ?>
                <?php echo 'showHideMaxMinVal( "'; ?>
<?php echo $this->_tpl_vars['fieldName']; ?>
<?php echo '", val );'; ?>

            <?php endforeach; endif; unset($_from); ?>
        <?php endforeach; endif; unset($_from); ?>

        <?php echo '
        function showHideMaxMinVal( field, val ) {
            var fldVal    = field + "_value_cell";
            var fldMinMax = field + "_min_max_cell";
            if ( val == "bw" || val == "nbw" ) {
                cj(\'#\' + fldVal ).hide();
                cj(\'#\' + fldMinMax ).show();
            } else if (val =="nll") {
                cj(\'#\' + fldVal).hide() ;
                cj(\'#\' + field + \'_value\').val(\'\');
                cj(\'#\' + fldMinMax ).hide();
            } else {
                cj(\'#\' + fldVal ).show();
                cj(\'#\' + fldMinMax ).hide();
            }
        }
	    
	function selectGroupByFields(id) {
	    var field = \'fields\\[\'+ id+\'\\]\';
	    var group = \'group_bys\\[\'+ id+\'\\]\';	
	    var groups = document.getElementById( group ).checked;
	    if ( groups == 1 ) {
	        document.getElementById( field ).checked = true;	
	    } else {
	        document.getElementById( field ).checked = false;	    
	    }	
	}
    </script>
    '; ?>


    <div><?php echo $this->_tpl_vars['form']['buttons']['html']; ?>
</div>