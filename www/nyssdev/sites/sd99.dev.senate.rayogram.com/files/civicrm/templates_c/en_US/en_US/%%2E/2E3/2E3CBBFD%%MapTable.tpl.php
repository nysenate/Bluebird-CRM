<?php /* Smarty version 2.6.26, created on 2010-07-06 10:41:50
         compiled from CRM/Import/Form/MapTable.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Import/Form/MapTable.tpl', 33, false),)), $this); ?>
<div class="crm-block crm-form-block crm-import-maptable-form-block">

 <div id="map-field">
    <?php echo '<table class="selector">'; ?><?php if ($this->_tpl_vars['loadedMapping']): ?><?php echo '<tr class="columnheader-dark"><th colspan="4">'; ?><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['savedName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Saved Field Mapping: %1'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</td></tr>'; ?><?php endif; ?><?php echo '<tr class="columnheader">'; ?><?php if ($this->_tpl_vars['showColNames']): ?><?php echo ''; ?><?php $this->assign('totalRowsDisplay', $this->_tpl_vars['rowDisplayCount']+1); ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php $this->assign('totalRowsDisplay', $this->_tpl_vars['rowDisplayCount']); ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php unset($this->_sections['rows']);
$this->_sections['rows']['name'] = 'rows';
$this->_sections['rows']['loop'] = is_array($_loop=$this->_tpl_vars['totalRowsDisplay']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['rows']['show'] = true;
$this->_sections['rows']['max'] = $this->_sections['rows']['loop'];
$this->_sections['rows']['step'] = 1;
$this->_sections['rows']['start'] = $this->_sections['rows']['step'] > 0 ? 0 : $this->_sections['rows']['loop']-1;
if ($this->_sections['rows']['show']) {
    $this->_sections['rows']['total'] = $this->_sections['rows']['loop'];
    if ($this->_sections['rows']['total'] == 0)
        $this->_sections['rows']['show'] = false;
} else
    $this->_sections['rows']['total'] = 0;
if ($this->_sections['rows']['show']):

            for ($this->_sections['rows']['index'] = $this->_sections['rows']['start'], $this->_sections['rows']['iteration'] = 1;
                 $this->_sections['rows']['iteration'] <= $this->_sections['rows']['total'];
                 $this->_sections['rows']['index'] += $this->_sections['rows']['step'], $this->_sections['rows']['iteration']++):
$this->_sections['rows']['rownum'] = $this->_sections['rows']['iteration'];
$this->_sections['rows']['index_prev'] = $this->_sections['rows']['index'] - $this->_sections['rows']['step'];
$this->_sections['rows']['index_next'] = $this->_sections['rows']['index'] + $this->_sections['rows']['step'];
$this->_sections['rows']['first']      = ($this->_sections['rows']['iteration'] == 1);
$this->_sections['rows']['last']       = ($this->_sections['rows']['iteration'] == $this->_sections['rows']['total']);
?><?php echo ''; ?><?php if ($this->_sections['rows']['iteration'] == 1 && $this->_tpl_vars['showColNames']): ?><?php echo '<td>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Column Names'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</td>'; ?><?php elseif ($this->_tpl_vars['showColNames']): ?><?php echo '<td>'; ?><?php $this->_tag_stack[] = array('ts', array('1' => $this->_sections['rows']['iteration']-1)); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Import Data (row %1)'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</td>'; ?><?php else: ?><?php echo '<td>'; ?><?php $this->_tag_stack[] = array('ts', array('1' => $this->_sections['rows']['iteration'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Import Data (row %1)'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</td>'; ?><?php endif; ?><?php echo ''; ?><?php endfor; endif; ?><?php echo '<td>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Matching CiviCRM Field'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</td></tr>'; ?><?php echo ''; ?><?php unset($this->_sections['cols']);
$this->_sections['cols']['name'] = 'cols';
$this->_sections['cols']['loop'] = is_array($_loop=$this->_tpl_vars['columnCount']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
?><?php echo ''; ?><?php $this->assign('i', $this->_sections['cols']['index']); ?><?php echo '<tr style="border: 1px solid #DDDDDD;">'; ?><?php if ($this->_tpl_vars['showColNames']): ?><?php echo '<td class="even-row labels">'; ?><?php echo $this->_tpl_vars['columnNames'][$this->_tpl_vars['i']]; ?><?php echo '</td>'; ?><?php endif; ?><?php echo ''; ?><?php unset($this->_sections['rows']);
$this->_sections['rows']['name'] = 'rows';
$this->_sections['rows']['loop'] = is_array($_loop=$this->_tpl_vars['rowDisplayCount']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['rows']['show'] = true;
$this->_sections['rows']['max'] = $this->_sections['rows']['loop'];
$this->_sections['rows']['step'] = 1;
$this->_sections['rows']['start'] = $this->_sections['rows']['step'] > 0 ? 0 : $this->_sections['rows']['loop']-1;
if ($this->_sections['rows']['show']) {
    $this->_sections['rows']['total'] = $this->_sections['rows']['loop'];
    if ($this->_sections['rows']['total'] == 0)
        $this->_sections['rows']['show'] = false;
} else
    $this->_sections['rows']['total'] = 0;
if ($this->_sections['rows']['show']):

            for ($this->_sections['rows']['index'] = $this->_sections['rows']['start'], $this->_sections['rows']['iteration'] = 1;
                 $this->_sections['rows']['iteration'] <= $this->_sections['rows']['total'];
                 $this->_sections['rows']['index'] += $this->_sections['rows']['step'], $this->_sections['rows']['iteration']++):
$this->_sections['rows']['rownum'] = $this->_sections['rows']['iteration'];
$this->_sections['rows']['index_prev'] = $this->_sections['rows']['index'] - $this->_sections['rows']['step'];
$this->_sections['rows']['index_next'] = $this->_sections['rows']['index'] + $this->_sections['rows']['step'];
$this->_sections['rows']['first']      = ($this->_sections['rows']['iteration'] == 1);
$this->_sections['rows']['last']       = ($this->_sections['rows']['iteration'] == $this->_sections['rows']['total']);
?><?php echo ''; ?><?php $this->assign('j', $this->_sections['rows']['index']); ?><?php echo '<td class="odd-row">'; ?><?php echo $this->_tpl_vars['dataValues'][$this->_tpl_vars['j']][$this->_tpl_vars['i']]; ?><?php echo '</td>'; ?><?php endfor; endif; ?><?php echo ''; ?><?php echo '<td class="form-item even-row'; ?><?php if ($this->_tpl_vars['wizard']['currentStepName'] == 'Preview'): ?><?php echo ' labels'; ?><?php endif; ?><?php echo '">'; ?><?php if ($this->_tpl_vars['wizard']['currentStepName'] == 'Preview'): ?><?php echo ''; ?><?php if ($this->_tpl_vars['relatedContactDetails'] && $this->_tpl_vars['relatedContactDetails'][$this->_tpl_vars['i']] != ''): ?><?php echo ''; ?><?php echo $this->_tpl_vars['mapper'][$this->_tpl_vars['i']]; ?><?php echo ' - '; ?><?php echo $this->_tpl_vars['relatedContactDetails'][$this->_tpl_vars['i']]; ?><?php echo ''; ?><?php if ($this->_tpl_vars['relatedContactLocType'] && $this->_tpl_vars['relatedContactLocType'][$this->_tpl_vars['i']] != ''): ?><?php echo '- '; ?><?php echo $this->_tpl_vars['relatedContactLocType'][$this->_tpl_vars['i']]; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['relatedContactPhoneType'] && $this->_tpl_vars['relatedContactPhoneType'][$this->_tpl_vars['i']] != ''): ?><?php echo '- '; ?><?php echo $this->_tpl_vars['relatedContactPhoneType'][$this->_tpl_vars['i']]; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php echo ''; ?><?php if ($this->_tpl_vars['relatedContactImProvider'] && $this->_tpl_vars['relatedContactImProvider'][$this->_tpl_vars['i']] != ''): ?><?php echo '- '; ?><?php echo $this->_tpl_vars['relatedContactImProvider'][$this->_tpl_vars['i']]; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php if ($this->_tpl_vars['locations'][$this->_tpl_vars['i']]): ?><?php echo ''; ?><?php echo $this->_tpl_vars['locations'][$this->_tpl_vars['i']]; ?><?php echo ' -'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['phones'][$this->_tpl_vars['i']]): ?><?php echo ''; ?><?php echo $this->_tpl_vars['phones'][$this->_tpl_vars['i']]; ?><?php echo ' -'; ?><?php endif; ?><?php echo ''; ?><?php echo ''; ?><?php if ($this->_tpl_vars['ims'][$this->_tpl_vars['i']]): ?><?php echo ''; ?><?php echo $this->_tpl_vars['ims'][$this->_tpl_vars['i']]; ?><?php echo ' -'; ?><?php endif; ?><?php echo ''; ?><?php echo ''; ?><?php echo $this->_tpl_vars['mapper'][$this->_tpl_vars['i']]; ?><?php echo ''; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php else: ?><?php echo ''; ?><?php echo $this->_tpl_vars['form']['mapper'][$this->_tpl_vars['i']]['html']; ?><?php echo ''; ?><?php endif; ?><?php echo '</td></tr>'; ?><?php endfor; endif; ?><?php echo '</table>'; ?>


    <?php if ($this->_tpl_vars['wizard']['currentStepName'] != 'Preview'): ?>
    <div>
    
    	<?php if ($this->_tpl_vars['loadedMapping']): ?> 
        	<span><?php echo $this->_tpl_vars['form']['updateMapping']['html']; ?>
 &nbsp;&nbsp; <?php echo $this->_tpl_vars['form']['updateMapping']['label']; ?>
</span>
    	<?php endif; ?>
    	<span><?php echo $this->_tpl_vars['form']['saveMapping']['html']; ?>
 &nbsp;&nbsp; <?php echo $this->_tpl_vars['form']['saveMapping']['label']; ?>
</span>
    	<div id="saveDetails" class="form-item">
    	      <table class="form-layout-compressed">
    		    <tr class="crm-import-maptable-form-block-saveMappingName">
                        <td class="label"><?php echo $this->_tpl_vars['form']['saveMappingName']['label']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['form']['saveMappingName']['html']; ?>
</td>
                    </tr>
    		    <tr class="crm-import-maptable-form-block-saveMappingName">
                        <td class="label"><?php echo $this->_tpl_vars['form']['saveMappingDesc']['label']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['form']['saveMappingDesc']['html']; ?>
</td>
                    </tr>
    	      </table>
    	</div>
    	<script type="text/javascript">
             <?php if ($this->_tpl_vars['mappingDetailsError']): ?>
                show('saveDetails');    
             <?php else: ?>
        	    hide('saveDetails');
             <?php endif; ?>
    
    	     <?php echo '   
 	         function showSaveDetails(chkbox) {
        		 if (chkbox.checked) {
        			document.getElementById("saveDetails").style.display = "block";
        			document.getElementById("saveMappingName").disabled = false;
        			document.getElementById("saveMappingDesc").disabled = false;
        		 } else {
        			document.getElementById("saveDetails").style.display = "none";
        			document.getElementById("saveMappingName").disabled = true;
        			document.getElementById("saveMappingDesc").disabled = true;
        		 }
             }
            cj(\'select[id^="mapper"][id$="[0]"]\').addClass(\'huge\');
            '; ?>

	    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/highLightImport.tpl", 'smarty_include_vars' => array('relationship' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>	    
	</script>
    </div>
    <?php endif; ?>
 </div>
</div>