<?php /* Smarty version 2.6.26, created on 2010-05-25 14:31:16
         compiled from CRM/Contact/Form/Selector.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Selector.tpl', 30, false),array('function', 'counter', 'CRM/Contact/Form/Selector.tpl', 52, false),array('function', 'cycle', 'CRM/Contact/Form/Selector.tpl', 56, false),array('function', 'crmURL', 'CRM/Contact/Form/Selector.tpl', 66, false),array('modifier', 'crmMoney', 'CRM/Contact/Form/Selector.tpl', 71, false),array('modifier', 'crmDate', 'CRM/Contact/Form/Selector.tpl', 73, false),array('modifier', 'replace', 'CRM/Contact/Form/Selector.tpl', 81, false),array('modifier', 'mb_truncate', 'CRM/Contact/Form/Selector.tpl', 98, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pager.tpl", 'smarty_include_vars' => array('location' => 'top')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pagerAToZ.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<table summary="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Search results listings.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" class="selector">
  <thead class="sticky">
    <tr>
      <th scope="col" title="Select All Rows"><?php echo $this->_tpl_vars['form']['toggleSelect']['html']; ?>
</th>
      <?php if ($this->_tpl_vars['context'] == 'smog'): ?>
          <th scope="col">
            <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Status<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
          </th>
      <?php endif; ?>
      <?php $_from = $this->_tpl_vars['columnHeaders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['header']):
?>
        <th scope="col">
        <?php if ($this->_tpl_vars['header']['sort']): ?>
          <?php $this->assign('key', $this->_tpl_vars['header']['sort']); ?>
          <?php echo $this->_tpl_vars['sort']->_response[$this->_tpl_vars['key']]['link']; ?>

        <?php else: ?>
          <?php echo $this->_tpl_vars['header']['name']; ?>

        <?php endif; ?>
        </th>
      <?php endforeach; endif; unset($_from); ?>
    </tr>
  </thead>

  <?php echo smarty_function_counter(array('start' => 0,'skip' => 1,'print' => false), $this);?>


  <?php if ($this->_tpl_vars['id']): ?>
      <?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
        <tr id='rowid<?php echo $this->_tpl_vars['row']['contact_id']; ?>
' class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
">
            <?php $this->assign('cbName', $this->_tpl_vars['row']['checkbox']); ?>
            <td><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['cbName']]['html']; ?>
</td>
            <?php if ($this->_tpl_vars['context'] == 'smog'): ?>
              <?php if ($this->_tpl_vars['row']['status'] == 'Pending'): ?><td class="status-pending"}>
              <?php elseif ($this->_tpl_vars['row']['status'] == 'Removed'): ?><td class="status-removed">
              <?php else: ?><td><?php endif; ?>
              <?php echo $this->_tpl_vars['row']['status']; ?>
</td>
            <?php endif; ?>
            <td><?php echo $this->_tpl_vars['row']['contact_type']; ?>
</td>
            <td><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['row']['contact_id'])), $this);?>
"><?php echo $this->_tpl_vars['row']['sort_name']; ?>
</a></td>
            <?php $_from = $this->_tpl_vars['row']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['value']):
?> 
               <?php if (( $this->_tpl_vars['key'] != 'checkbox' ) && ( $this->_tpl_vars['key'] != 'action' ) && ( $this->_tpl_vars['key'] != 'contact_type' ) && ( $this->_tpl_vars['key'] != 'status' ) && ( $this->_tpl_vars['key'] != 'sort_name' ) && ( $this->_tpl_vars['key'] != 'contact_id' ) && ( $this->_tpl_vars['key'] != 'contact_sub_type' )): ?>
                <td>
                <?php if ($this->_tpl_vars['key'] == 'household_income_total'): ?>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['value'])) ? $this->_run_mod_handler('crmMoney', true, $_tmp) : smarty_modifier_crmMoney($_tmp)); ?>

		<?php elseif (strpos ( $this->_tpl_vars['key'] , '_date' ) !== false): ?>
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['value'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>

                <?php else: ?>
                    <?php echo $this->_tpl_vars['value']; ?>

                <?php endif; ?>
                     &nbsp;
                 </td>
               <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
            <td><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['action'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'xx', $this->_tpl_vars['row']['contact_id']) : smarty_modifier_replace($_tmp, 'xx', $this->_tpl_vars['row']['contact_id'])); ?>
</td>
        </tr>
     <?php endforeach; endif; unset($_from); ?>
  <?php else: ?>
      <?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
         <tr id='rowid<?php echo $this->_tpl_vars['row']['contact_id']; ?>
' class="<?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?>
" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click contact name to view a summary. Right-click anywhere in the row for an actions menu."<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>>
            <?php $this->assign('cbName', $this->_tpl_vars['row']['checkbox']); ?>
            <td><?php echo $this->_tpl_vars['form'][$this->_tpl_vars['cbName']]['html']; ?>
</td>
            <?php if ($this->_tpl_vars['context'] == 'smog'): ?>
                <?php if ($this->_tpl_vars['row']['status'] == 'Pending'): ?><td class="status-pending"}>
                <?php elseif ($this->_tpl_vars['row']['status'] == 'Removed'): ?><td class="status-removed">
                <?php else: ?><td><?php endif; ?>
                <?php echo $this->_tpl_vars['row']['status']; ?>
</td>
            <?php endif; ?>
            <td><?php echo $this->_tpl_vars['row']['contact_type']; ?>
</td>	
            <td><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['row']['contact_id'])), $this);?>
"><?php if ($this->_tpl_vars['row']['is_deleted']): ?><del><?php endif; ?><?php echo $this->_tpl_vars['row']['sort_name']; ?>
<?php if ($this->_tpl_vars['row']['is_deleted']): ?></del><?php endif; ?></a></td>
            <?php if ($this->_tpl_vars['action'] == 512 || $this->_tpl_vars['action'] == 256): ?>
              <td><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['street_address'])) ? $this->_run_mod_handler('mb_truncate', true, $_tmp, 22, "...", true) : smarty_modifier_mb_truncate($_tmp, 22, "...", true)); ?>
</td>
              <td><?php echo $this->_tpl_vars['row']['city']; ?>
</td>
              <td><?php echo $this->_tpl_vars['row']['state_province']; ?>
</td>
              <td><?php echo $this->_tpl_vars['row']['postal_code']; ?>
</td>
              <td><?php echo $this->_tpl_vars['row']['country']; ?>
</td>
              <td <?php if ($this->_tpl_vars['row']['on_hold']): ?>class="status-hold"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['email'])) ? $this->_run_mod_handler('mb_truncate', true, $_tmp, 17, "...", true) : smarty_modifier_mb_truncate($_tmp, 17, "...", true)); ?>
<?php if ($this->_tpl_vars['row']['on_hold']): ?>&nbsp;(On Hold)<?php endif; ?></td>
              <td><?php echo $this->_tpl_vars['row']['phone']; ?>
</td> 
           <?php else: ?>
              <?php $_from = $this->_tpl_vars['row']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['value']):
?>
                <?php if (( $this->_tpl_vars['key'] != 'checkbox' ) && ( $this->_tpl_vars['key'] != 'action' ) && ( $this->_tpl_vars['key'] != 'contact_type' ) && ( $this->_tpl_vars['key'] != 'contact_sub_type' ) && ( $this->_tpl_vars['key'] != 'status' ) && ( $this->_tpl_vars['key'] != 'sort_name' ) && ( $this->_tpl_vars['key'] != 'contact_id' )): ?>
                 <td><?php echo $this->_tpl_vars['value']; ?>
&nbsp;</td>
                <?php endif; ?>   
              <?php endforeach; endif; unset($_from); ?>
            <?php endif; ?>
            <td style='width:125px;'><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['action'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'xx', $this->_tpl_vars['row']['contact_id']) : smarty_modifier_replace($_tmp, 'xx', $this->_tpl_vars['row']['contact_id'])); ?>
</td>
         </tr>
    <?php endforeach; endif; unset($_from); ?>
  <?php endif; ?>
</table>

<!-- Context Menu populated as per component and permission-->
<ul id="contactMenu" class="contextMenu">
<?php $_from = $this->_tpl_vars['contextMenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['value']):
?>
  <li class="<?php echo $this->_tpl_vars['value']['ref']; ?>
"><a href="#<?php echo $this->_tpl_vars['value']['key']; ?>
"><?php echo $this->_tpl_vars['value']['title']; ?>
</a></li>
<?php endforeach; endif; unset($_from); ?>
</ul>
<script type="text/javascript">
     var fname = "<?php echo $this->_tpl_vars['form']['formName']; ?>
";	
    on_load_init_checkboxes(fname);
 <?php echo '
cj(document).ready( function() {
var url         = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/changeaction','q' => "reset=1&action=add&cid=changeid&context=changeaction",'h' => 0), $this);?>
<?php echo '";
var activityUrl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "action=browse&selectedChild=activity&reset=1&cid=changeid",'h' => 0), $this);?>
<?php echo '";
var emailUrl    = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/activity','q' => "atype=3&action=add&reset=1&cid=changeid",'h' => 0), $this);?>
<?php echo '";
var contactUrl  = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/changeaction','q' => "reset=1&cid=changeid",'h' => 0), $this);?>
<?php echo '";
// Show menu when contact row is right clicked
cj(".selector tr").contextMenu({
      menu: \'contactMenu\'
    }, function( action, el ) { 
        var contactId = el.attr(\'id\').substr(5);
        switch (action) {
          case \'activity\':
          case \'email\':
            eval( \'locationUrl = \'+action+\'Url;\');
            break;
          case \'add\':
            contactId += \'&action=update\';
          case \'view\':
            locationUrl = contactUrl.replace( /changeaction/g, action );
            break;
          default:
            locationUrl = url.replace( /changeaction/g, action );
            break;
        }
        eval( \'locationUrl = locationUrl.replace( /changeid/, contactId );\');
        var destination = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('q' => "force=1",'h' => 0), $this);?>
<?php echo '";
        window.location = locationUrl + \'&destination=\' + encodeURIComponent(destination);
   });
});
cj(\'ul#contactMenu\').mouseup( function(e){ 
   if( e.button !=0 ) {
    //when right or middle button clicked fire default right click popup
   }
});
'; ?>

</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/pager.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>