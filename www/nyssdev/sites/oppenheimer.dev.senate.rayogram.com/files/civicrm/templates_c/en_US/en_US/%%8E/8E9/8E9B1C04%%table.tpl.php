<?php /* Smarty version 2.6.26, created on 2010-07-07 16:01:10
         compiled from CRM/Export/Form/table.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Export/Form/table.tpl', 31, false),)), $this); ?>
 <div id="map-field">
    <?php echo '<table>'; ?><?php if ($this->_tpl_vars['loadedMapping']): ?><?php echo '<tr class="columnheader-dark"><th colspan="4">'; ?><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['savedName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Using Field Mapping: %1'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</td></tr>'; ?><?php endif; ?><?php echo '<tr class="columnheader"><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Fields to Include in Export File'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th></tr>'; ?><?php echo ''; ?><?php unset($this->_sections['cols']);
$this->_sections['cols']['name'] = 'cols';
$this->_sections['cols']['loop'] = is_array($_loop=$this->_tpl_vars['columnCount']['1']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
?><?php echo ''; ?><?php $this->assign('i', $this->_sections['cols']['index']); ?><?php echo '<tr><td class="form-item even-row">'; ?><?php echo $this->_tpl_vars['form']['mapper']['1'][$this->_tpl_vars['i']]['html']; ?><?php echo '</td></tr>'; ?><?php endfor; endif; ?><?php echo '<tr><td class="form-item even-row underline-effect">'; ?><?php echo $this->_tpl_vars['form']['addMore']['1']['html']; ?><?php echo '</td></tr></table>'; ?>



    <div>
	<?php if ($this->_tpl_vars['loadedMapping']): ?>
            <span><?php echo $this->_tpl_vars['form']['updateMapping']['html']; ?>
<?php echo $this->_tpl_vars['form']['updateMapping']['label']; ?>
&nbsp;&nbsp;&nbsp;</span>
	<?php endif; ?>
	<span><?php echo $this->_tpl_vars['form']['saveMapping']['html']; ?>
<?php echo $this->_tpl_vars['form']['saveMapping']['label']; ?>
</span>
    <div id="saveDetails" class="form-item">
      <table class="form-layout-compressed">
         <tr><td class="label"><?php echo $this->_tpl_vars['form']['saveMappingName']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['saveMappingName']['html']; ?>
</td></tr>
         <tr><td class="label"><?php echo $this->_tpl_vars['form']['saveMappingDesc']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['saveMappingDesc']['html']; ?>
</td></tr>
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
         '; ?>
	     
    cj('Select[id^="mapper[1]"][id$="[1]"]').addClass('huge');
	</script>
    </div>

 </div>