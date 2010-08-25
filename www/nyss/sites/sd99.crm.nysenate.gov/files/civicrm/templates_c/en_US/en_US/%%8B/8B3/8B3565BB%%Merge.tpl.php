<?php /* Smarty version 2.6.26, created on 2010-08-20 09:22:26
         compiled from CRM/Contact/Form/Merge.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Merge.tpl', 28, false),array('function', 'help', 'CRM/Contact/Form/Merge.tpl', 28, false),array('function', 'crmURL', 'CRM/Contact/Form/Merge.tpl', 32, false),array('function', 'cycle', 'CRM/Contact/Form/Merge.tpl', 43, false),array('modifier', 'substr', 'CRM/Contact/Form/Merge.tpl', 54, false),array('modifier', 'strrpos', 'CRM/Contact/Form/Merge.tpl', 60, false),)), $this); ?>
<div class="crm-block crm-form-block crm-contact-merge-form-block">
<div id="help">
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click <strong>Merge</strong> to move data from the Duplicate Contact on the left into the Main Contact. In addition to the contact data (address, phone, email...), you may choose to move all or some of the related activity records (groups, contributions, memberships, etc.).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo smarty_function_help(array('id' => 'intro'), $this);?>

</div>
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
<div class="action-link">
    	<a href="<?php echo CRM_Utils_System::crmURL(array('q' => "reset=1&cid=".($this->_tpl_vars['other_cid'])."&oid=".($this->_tpl_vars['main_cid'])), $this);?>
">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Flip between original and duplicate contacts.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
</div>
<table>
  <tr class="columnheader">
    <th>&nbsp;</th>
    <th><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['other_cid'])), $this);?>
"><?php echo $this->_tpl_vars['other_name']; ?>
</a> (duplicate)</th>
    <th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Mark All<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><br />==<?php echo $this->_tpl_vars['form']['toggleSelect']['html']; ?>
 ==&gt;</th>
    <th><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['main_cid'])), $this);?>
"><?php echo $this->_tpl_vars['main_name']; ?>
</a></th>
  </tr>

  <?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['row']):
?>
     <tr class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
        <td><?php echo $this->_tpl_vars['row']['title']; ?>
</td>
        <td>
           <?php if (! is_array ( $this->_tpl_vars['row']['other'] )): ?>
               <?php echo $this->_tpl_vars['row']['other']; ?>

           <?php else: ?>
               <?php echo $this->_tpl_vars['row']['other']['fileName']; ?>

           <?php endif; ?> 
        </td>
        <td style='white-space: nowrap'><?php if ($this->_tpl_vars['form'][$this->_tpl_vars['field']]): ?>==<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['field']]['html']; ?>
==&gt;<?php endif; ?></td>
        <td>
            <?php if (((is_array($_tmp=$this->_tpl_vars['row']['title'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 5) : substr($_tmp, 0, 5)) == 'Email' || ((is_array($_tmp=$this->_tpl_vars['row']['title'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 7) : substr($_tmp, 0, 7)) == 'Address' || ((is_array($_tmp=$this->_tpl_vars['row']['title'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 2) : substr($_tmp, 0, 2)) == 'IM' || ((is_array($_tmp=$this->_tpl_vars['row']['title'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 6) : substr($_tmp, 0, 6)) == 'OpenID' || ((is_array($_tmp=$this->_tpl_vars['row']['title'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 5) : substr($_tmp, 0, 5)) == 'Phone'): ?>

	        <?php $this->assign('position', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('strrpos', true, $_tmp, '_') : strrpos($_tmp, '_'))); ?>
                <?php $this->assign('blockId', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('substr', true, $_tmp, $this->_tpl_vars['position']+1) : substr($_tmp, $this->_tpl_vars['position']+1))); ?>
                <?php $this->assign('blockName', ((is_array($_tmp=$this->_tpl_vars['field'])) ? $this->_run_mod_handler('substr', true, $_tmp, 14, $this->_tpl_vars['position']-14) : substr($_tmp, 14, $this->_tpl_vars['position']-14))); ?>

                <?php echo $this->_tpl_vars['form']['location'][$this->_tpl_vars['blockName']][$this->_tpl_vars['blockId']]['locTypeId']['html']; ?>
&nbsp;
                <?php if ($this->_tpl_vars['blockName'] == 'address'): ?>
                <span id="main_<?php echo $this->_tpl_vars['blockName']; ?>
_<?php echo $this->_tpl_vars['blockId']; ?>
_overwrite"><?php if ($this->_tpl_vars['row']['main']): ?>(overwrite)<?php else: ?>(add)<?php endif; ?></span>
                <?php endif; ?> 

                <?php echo $this->_tpl_vars['form']['location'][$this->_tpl_vars['blockName']][$this->_tpl_vars['blockId']]['operation']['html']; ?>
&nbsp;<br />
            <?php endif; ?>
            <span id="main_<?php echo $this->_tpl_vars['blockName']; ?>
_<?php echo $this->_tpl_vars['blockId']; ?>
"><?php echo $this->_tpl_vars['row']['main']; ?>
</span>
        </td>
     </tr>
  <?php endforeach; endif; unset($_from); ?>

  <?php $_from = $this->_tpl_vars['rel_tables']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['paramName'] => $this->_tpl_vars['params']):
?>
    <tr class="<?php echo smarty_function_cycle(array('values' => "even-row,odd-row"), $this);?>
">
      <th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Move related...<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th><td><a href="<?php echo $this->_tpl_vars['params']['other_url']; ?>
"><?php echo $this->_tpl_vars['params']['title']; ?>
</a></td><td style='white-space: nowrap'>==<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['paramName']]['html']; ?>
==&gt;</td><td><a href="<?php echo $this->_tpl_vars['params']['main_url']; ?>
"><?php echo $this->_tpl_vars['params']['title']; ?>
</a></td>
    </tr>
  <?php endforeach; endif; unset($_from); ?>
</table>
<div class='form-item'>
  <!--<p><?php echo $this->_tpl_vars['form']['moveBelongings']['html']; ?>
 <?php echo $this->_tpl_vars['form']['moveBelongings']['label']; ?>
</p>-->
  <!--<p><?php echo $this->_tpl_vars['form']['deleteOther']['html']; ?>
 <?php echo $this->_tpl_vars['form']['deleteOther']['label']; ?>
</p>-->
</div>
<div class="form-item">
    <p><strong><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>WARNING: The duplicate contact record WILL BE DELETED after the merge is complete.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></strong></strong></p>
</div>
<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
</div>

<?php echo '
<script type="text/javascript">

cj(document).ready(function(){ 
    cj(\'table td input.form-checkbox\').each(function() {
       var ele = null;
       var element = cj(this).attr(\'id\').split(\'_\',3);

       switch ( element[\'1\'] ) {
           case \'addressee\':
                 var ele = \'#\' + element[\'0\'] + \'_\' + element[\'1\'];
                 break;

           case \'email\':
           case \'postal\':
                 var ele = \'#\' + element[\'0\'] + \'_\' + element[\'1\'] + \'_\' + element[\'2\'];
                 break;
       }

       if( ele ) {
          cj(this).bind( \'click\', function() {
 
              if( cj( this).attr( \'checked\' ) ){
                  cj(\'input\' + ele ).attr(\'checked\', true );
                  cj(\'input\' + ele + \'_custom\' ).attr(\'checked\', true );
              } else {
                  cj(\'input\' + ele ).attr(\'checked\', false );
                  cj(\'input\' + ele + \'_custom\' ).attr(\'checked\', false );
              }
          });
       }
    });
});

function mergeAddress( element, blockId ) {
   var allAddress = '; ?>
<?php echo $this->_tpl_vars['mainLocAddress']; ?>
<?php echo ';
   var address    = eval( "allAddress." + \'main_\' + element.value );
   var label      = \'(overwrite)\';

   if ( !address ) { 
     address = \'\';
     label   = \'(add)\';
   }

   cj( "#main_address_" + blockId ).html( address );	
   cj( "#main_address_" + blockId +"_overwrite" ).html( label );
}

</script>
'; ?>
