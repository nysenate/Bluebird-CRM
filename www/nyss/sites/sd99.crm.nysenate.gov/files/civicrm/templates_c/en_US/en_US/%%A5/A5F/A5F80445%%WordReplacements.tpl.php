<?php /* Smarty version 2.6.26, created on 2010-08-25 13:36:38
         compiled from CRM/Admin/Form/WordReplacements.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Admin/Form/WordReplacements.tpl', 44, false),array('function', 'crmURL', 'CRM/Admin/Form/WordReplacements.tpl', 86, false),)), $this); ?>

<?php if ($this->_tpl_vars['soInstance']): ?>
<tr id="string_override_row_<?php echo $this->_tpl_vars['soInstance']; ?>
">
  <td class="even-row"><?php echo $this->_tpl_vars['form']['enabled'][$this->_tpl_vars['soInstance']]['html']; ?>
</td>	
  <td class="even-row"><?php echo $this->_tpl_vars['form']['old'][$this->_tpl_vars['soInstance']]['html']; ?>
</td>
  <td class="even-row"><?php echo $this->_tpl_vars['form']['new'][$this->_tpl_vars['soInstance']]['html']; ?>
</td>
  <td class="even-row"><?php echo $this->_tpl_vars['form']['cb'][$this->_tpl_vars['soInstance']]['html']; ?>
</td>
</tr>

<?php else: ?>
<div class="crm-form crm-form-block crm-string_override-form-block">
<table class="form-layout-compressed">
	<tr>
	    <td>
      	    <table>
		<tr class="columnheader">
		    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Enabled<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
		    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Original<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    		    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Replacement<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    		    <td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Exact Match?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
    		</tr>

 		<?php unset($this->_sections['numStrings']);
$this->_sections['numStrings']['name'] = 'numStrings';
$this->_sections['numStrings']['start'] = (int)1;
$this->_sections['numStrings']['step'] = ((int)1) == 0 ? 1 : (int)1;
$this->_sections['numStrings']['loop'] = is_array($_loop=$this->_tpl_vars['numStrings']+1) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['numStrings']['show'] = true;
$this->_sections['numStrings']['max'] = $this->_sections['numStrings']['loop'];
if ($this->_sections['numStrings']['start'] < 0)
    $this->_sections['numStrings']['start'] = max($this->_sections['numStrings']['step'] > 0 ? 0 : -1, $this->_sections['numStrings']['loop'] + $this->_sections['numStrings']['start']);
else
    $this->_sections['numStrings']['start'] = min($this->_sections['numStrings']['start'], $this->_sections['numStrings']['step'] > 0 ? $this->_sections['numStrings']['loop'] : $this->_sections['numStrings']['loop']-1);
if ($this->_sections['numStrings']['show']) {
    $this->_sections['numStrings']['total'] = min(ceil(($this->_sections['numStrings']['step'] > 0 ? $this->_sections['numStrings']['loop'] - $this->_sections['numStrings']['start'] : $this->_sections['numStrings']['start']+1)/abs($this->_sections['numStrings']['step'])), $this->_sections['numStrings']['max']);
    if ($this->_sections['numStrings']['total'] == 0)
        $this->_sections['numStrings']['show'] = false;
} else
    $this->_sections['numStrings']['total'] = 0;
if ($this->_sections['numStrings']['show']):

            for ($this->_sections['numStrings']['index'] = $this->_sections['numStrings']['start'], $this->_sections['numStrings']['iteration'] = 1;
                 $this->_sections['numStrings']['iteration'] <= $this->_sections['numStrings']['total'];
                 $this->_sections['numStrings']['index'] += $this->_sections['numStrings']['step'], $this->_sections['numStrings']['iteration']++):
$this->_sections['numStrings']['rownum'] = $this->_sections['numStrings']['iteration'];
$this->_sections['numStrings']['index_prev'] = $this->_sections['numStrings']['index'] - $this->_sections['numStrings']['step'];
$this->_sections['numStrings']['index_next'] = $this->_sections['numStrings']['index'] + $this->_sections['numStrings']['step'];
$this->_sections['numStrings']['first']      = ($this->_sections['numStrings']['iteration'] == 1);
$this->_sections['numStrings']['last']       = ($this->_sections['numStrings']['iteration'] == $this->_sections['numStrings']['total']);
?>
		<?php $this->assign('soInstance', $this->_sections['numStrings']['index']); ?>

		<tr id="string_override_row_<?php echo $this->_tpl_vars['soInstance']; ?>
">
		    <td class="even-row" style="text-align: center; vertical-align: middle;"><?php echo $this->_tpl_vars['form']['enabled'][$this->_tpl_vars['soInstance']]['html']; ?>
</td>	
  		    <td class="even-row"><?php echo $this->_tpl_vars['form']['old'][$this->_tpl_vars['soInstance']]['html']; ?>
</td>
  		    <td class="even-row"><?php echo $this->_tpl_vars['form']['new'][$this->_tpl_vars['soInstance']]['html']; ?>
</td>
		    <td class="even-row" style="text-align: center; vertical-align: middle;"><?php echo $this->_tpl_vars['form']['cb'][$this->_tpl_vars['soInstance']]['html']; ?>
</td>
		</tr>

                </div> 
    		<?php endfor; endif; ?>
    	    </table>
       	    </td>
	</tr>
    </table>
 <div class="crm-submit-buttons" ><a class="button" onClick="Javascript:buildStringOverrideRow( false );return false;"><span><div class="icon add-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add row<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> </div>
	
</div>
<?php endif; ?>

<?php echo '
<script type="text/javascript">
function buildStringOverrideRow( curInstance ) 
{
   var rowId = \'string_override_row_\';

   if ( curInstance ) {
      if ( curInstance <= 10 ) return;
      currentInstance  = curInstance;
      previousInstance = currentInstance - 1;  
   } else {
      var previousInstance = cj( \'[id^="\'+ rowId +\'"]:last\' ).attr(\'id\').slice( rowId.length );
      var currentInstance = parseInt( previousInstance ) + 1;
   }

   var dataUrl  = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('q' => 'snippet=4'), $this);?>
"<?php echo ' ;
   dataUrl     += "&instance="+currentInstance;
   
   var prevInstRowId = \'#string_override_row_\' + previousInstance;
  
   cj.ajax({ url     : dataUrl,   
             async   : false,
             success : function( html ) { 
	     cj( prevInstRowId ).after( html ); 
	     cj(\'#old_\'+currentInstance).TextAreaResizer();
	     cj(\'#new_\'+currentInstance).TextAreaResizer();
	     }	     
   });
}

cj( function( ) {
  '; ?>

  <?php if ($this->_tpl_vars['stringOverrideInstances']): ?>
     <?php $_from = $this->_tpl_vars['stringOverrideInstances']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['index'] => $this->_tpl_vars['instance']):
?>
        buildStringOverrideRow( <?php echo $this->_tpl_vars['instance']; ?>
 );
     <?php endforeach; endif; unset($_from); ?>  
  <?php endif; ?>
  <?php echo '
});
</script>
'; ?>
