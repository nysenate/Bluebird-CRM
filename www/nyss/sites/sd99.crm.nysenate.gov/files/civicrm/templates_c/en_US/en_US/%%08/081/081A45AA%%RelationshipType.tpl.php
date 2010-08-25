<?php /* Smarty version 2.6.26, created on 2010-08-24 16:08:09
         compiled from CRM/Admin/Page/RelationshipType.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Admin/Page/RelationshipType.tpl', 26, false),array('function', 'docURL', 'CRM/Admin/Page/RelationshipType.tpl', 27, false),array('function', 'help', 'CRM/Admin/Page/RelationshipType.tpl', 30, false),array('function', 'cycle', 'CRM/Admin/Page/RelationshipType.tpl', 61, false),array('block', 'ts', 'CRM/Admin/Page/RelationshipType.tpl', 29, false),array('modifier', 'replace', 'CRM/Admin/Page/RelationshipType.tpl', 70, false),)), $this); ?>
<?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/custom/group','q' => "reset=1"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('customURL', ob_get_contents());ob_end_clean(); ?>
<?php ob_start(); ?><?php echo smarty_function_docURL(array('page' => 'Relationship Types'), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('docLink', ob_get_contents());ob_end_clean(); ?>
<div id="help">
    <p><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Relationship types describe relationships between people, households and organizations. Relationship types labels describe the relationship from the perspective of each of the two entities (e.g. Parent <-> Child, Employer <-> Employee). For some types of relationships, the labels may be the same in both directions (e.g. Spouse <-> Spouse).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo $this->_tpl_vars['docLink']; ?>
</p>
    <p><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['customURL'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You can define as many additional relationships types as needed to cover the types of relationships you want to track. Once a relationship type is created, you may also define custom fields to extend relationship information for that type from <a href='%1'>Administer CiviCRM &raquo; Custom Data</a>.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo smarty_function_help(array('id' => 'id-relationship-types'), $this);?>
 </p>
</div>

<?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2 || $this->_tpl_vars['action'] == 4 || $this->_tpl_vars['action'] == 8): ?>
   <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Admin/Form/RelationshipType.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>	
<?php else: ?>

<?php if ($this->_tpl_vars['rows']): ?>
<?php if (! ( $this->_tpl_vars['action'] == 1 && $this->_tpl_vars['action'] == 2 )): ?>
    <div class="action-link">
	    <a href="<?php echo CRM_Utils_System::crmURL(array('q' => "action=add&reset=1"), $this);?>
" id="newRelationshipType" class="button"><span><div class="icon add-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add Relationship Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
    </div>
<?php endif; ?>

<div id="ltype">

    <?php echo ''; ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/enableDisable.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jsortable.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo '<table id="options" class="display"><thead><tr><th id="sortable">'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Relationship A to B'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Relationship B to A'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Contact Type A'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Contact Type B'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th></th></tr></thead>'; ?><?php $_from = $this->_tpl_vars['rows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?><?php echo '<tr id="row_'; ?><?php echo $this->_tpl_vars['row']['id']; ?><?php echo '" class="'; ?><?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?><?php echo ' '; ?><?php echo $this->_tpl_vars['row']['class']; ?><?php echo ''; ?><?php if (! $this->_tpl_vars['row']['is_active']): ?><?php echo ' disabled'; ?><?php endif; ?><?php echo ' crm-relationship"><td class="crm-relationship-label_a_b">'; ?><?php echo $this->_tpl_vars['row']['label_a_b']; ?><?php echo '</td><td class="crm-relationship-label_b_a">'; ?><?php echo $this->_tpl_vars['row']['label_b_a']; ?><?php echo '</td><td class="crm-relationship-contact_type_a_display">'; ?><?php if ($this->_tpl_vars['row']['contact_type_a_display']): ?><?php echo ' '; ?><?php echo $this->_tpl_vars['row']['contact_type_a_display']; ?><?php echo ''; ?><?php if ($this->_tpl_vars['row']['contact_sub_type_a']): ?><?php echo ' - '; ?><?php echo $this->_tpl_vars['row']['contact_sub_type_a']; ?><?php echo ' '; ?><?php endif; ?><?php echo ''; ?><?php else: ?><?php echo ' '; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'All Contacts'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo ' '; ?><?php endif; ?><?php echo ' </td><td class="crm-relationship-contact_type_b_display">'; ?><?php if ($this->_tpl_vars['row']['contact_type_b_display']): ?><?php echo ' '; ?><?php echo $this->_tpl_vars['row']['contact_type_b_display']; ?><?php echo ''; ?><?php if ($this->_tpl_vars['row']['contact_sub_type_b']): ?><?php echo ' - '; ?><?php echo $this->_tpl_vars['row']['contact_sub_type_b']; ?><?php echo ''; ?><?php endif; ?><?php echo ' '; ?><?php else: ?><?php echo ' '; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'All Contacts'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo ' '; ?><?php endif; ?><?php echo ' </td><td>'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['action'])) ? $this->_run_mod_handler('replace', true, $_tmp, 'xx', $this->_tpl_vars['row']['id']) : smarty_modifier_replace($_tmp, 'xx', $this->_tpl_vars['row']['id'])); ?><?php echo '</td></tr>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</table>'; ?>


        <?php if (! ( $this->_tpl_vars['action'] == 1 && $this->_tpl_vars['action'] == 2 )): ?>
            <div class="action-link">
        	    <a href="<?php echo CRM_Utils_System::crmURL(array('q' => "action=add&reset=1"), $this);?>
" id="newRelationshipType" class="button"><span><div class="icon add-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add Relationship Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
            </div>
        <?php endif; ?>
</div>
<?php else: ?>
    <div class="messages status">
        <div class="icon inform-icon"></div>
        <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/reltype','q' => "action=add&reset=1"), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('crmURL', ob_get_contents());ob_end_clean(); ?>
        <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['crmURL'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There are no relationship types present. You can <a href='%1'>add one</a>.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    </div>    
<?php endif; ?>
<?php endif; ?>