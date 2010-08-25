<?php /* Smarty version 2.6.26, created on 2010-07-07 15:35:05
         compiled from CRM/Case/Form/ActivityView.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Case/Form/ActivityView.tpl', 29, false),array('modifier', 'crmDate', 'CRM/Case/Form/ActivityView.tpl', 40, false),array('modifier', 'crmStripAlternatives', 'CRM/Case/Form/ActivityView.tpl', 63, false),array('modifier', 'nl2br', 'CRM/Case/Form/ActivityView.tpl', 63, false),array('function', 'crmURL', 'CRM/Case/Form/ActivityView.tpl', 84, false),)), $this); ?>
 <div class="crm-block crm-content-block crm-case-activity-view-block">
<?php if ($this->_tpl_vars['revs']): ?>
  <strong><?php echo $this->_tpl_vars['subject']; ?>
</strong> (<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>all revisions<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)<br />
  <?php echo '<table style="width: 95%; border: 1px solid #CCCCCC;"><tr style="background-color: #F6F6F6; color: #000000; border: 1px solid #CCCCCC;"><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Created By'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Created On'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>&nbsp;</th></tr>'; ?><?php $_from = $this->_tpl_vars['result']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?><?php echo '<tr '; ?><?php if ($this->_tpl_vars['row']['id'] == $this->_tpl_vars['latestRevisionID']): ?><?php echo 'style="font-weight: bold;"'; ?><?php endif; ?><?php echo '><td class="crm-case-activityview-form-block-name">'; ?><?php echo $this->_tpl_vars['row']['name']; ?><?php echo '</td><td class="crm-case-activityview-form-block-date">'; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['date'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?><?php echo '</td><td class="crm-case-activityview-form-block-'; ?><?php echo $this->_tpl_vars['row']['id']; ?><?php echo '"><a href="javascript:viewRevision( '; ?><?php echo $this->_tpl_vars['row']['id']; ?><?php echo ' );" title="'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'View this revision of the activity record.'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '">'; ?><?php if ($this->_tpl_vars['row']['id'] != $this->_tpl_vars['latestRevisionID']): ?><?php echo 'View Prior Revision'; ?><?php else: ?><?php echo 'View Current Revision'; ?><?php endif; ?><?php echo '</a></td></tr>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</table>'; ?>

<?php else: ?>
<?php if ($this->_tpl_vars['report']): ?>
<?php if ($this->_tpl_vars['caseID']): ?>
<div id="activity-content">
<?php endif; ?>        
<table class="crm-info-panel" id="crm-activity-view-table">
<?php $_from = $this->_tpl_vars['report']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['report'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['report']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['row']):
        $this->_foreach['report']['iteration']++;
?>
<tr class="crm-case-activity-view-<?php echo $this->_tpl_vars['row']['label']; ?>
">
    <td class="label"><?php echo $this->_tpl_vars['row']['label']; ?>
</td>
    <?php if (($this->_foreach['report']['iteration'] <= 1) && ( $this->_tpl_vars['activityID'] || $this->_tpl_vars['parentID'] || $this->_tpl_vars['latestRevisionID'] )): ?>         <td><?php echo $this->_tpl_vars['row']['value']; ?>
</td>
        <td style="padding-right: 50px; text-align: right; font-size: .9em;">
            <?php if ($this->_tpl_vars['activityID']): ?><a href="javascript:listRevisions(<?php echo $this->_tpl_vars['activityID']; ?>
);">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>List all revisions<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><?php if (! $this->_tpl_vars['latestRevisionID']): ?><br /><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>(this is the current revision)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?><br /><?php endif; ?>
            <?php if ($this->_tpl_vars['latestRevisionID']): ?><a href="javascript:viewRevision(<?php echo $this->_tpl_vars['latestRevisionID']; ?>
);">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>View current revision<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><br /><span style="color: red;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>(this is not the current revision)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span><br /><?php endif; ?>                   
            <?php if ($this->_tpl_vars['parentID']): ?><a href="javascript:viewRevision(<?php echo $this->_tpl_vars['parentID']; ?>
);">&raquo; <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Prompted by<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a><?php endif; ?>
        </td>
    <?php else: ?>
        <td colspan="2"><?php if ($this->_tpl_vars['row']['label'] == 'Details'): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['row']['value'])) ? $this->_run_mod_handler('crmStripAlternatives', true, $_tmp) : smarty_modifier_crmStripAlternatives($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
<?php elseif ($this->_tpl_vars['row']['type'] == 'Date'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['value'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
<?php else: ?><?php echo $this->_tpl_vars['row']['value']; ?>
<?php endif; ?></td>
    <?php endif; ?>
</tr>
<?php endforeach; endif; unset($_from); ?>
<?php if ($this->_tpl_vars['report']['customGroups']): ?>
    <?php $_from = $this->_tpl_vars['report']['customGroups']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['custom'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['custom']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['groupTitle'] => $this->_tpl_vars['customGroup']):
        $this->_foreach['custom']['iteration']++;
?>
        <tr style="background-color: #F6F6F6; color: #000000; border: 1px solid #CCCCCC; font-weight: bold" class="crm-case-activityview-form-block-groupTitle">
            <td colspan="3"><?php echo $this->_tpl_vars['groupTitle']; ?>
</td>
        </tr>
        <?php $_from = $this->_tpl_vars['customGroup']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fields'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fields']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['customField']):
        $this->_foreach['fields']['iteration']++;
?>
            <tr<?php if (! ($this->_foreach['fields']['iteration'] == $this->_foreach['fields']['total'])): ?> style="border-bottom: 1px solid #F6F6F6;"<?php endif; ?>>
	       <td class="label"><?php echo $this->_tpl_vars['customField']['label']; ?>
</td>
	       <td><?php echo $this->_tpl_vars['customField']['value']; ?>
</td>
            </tr>
        <?php endforeach; endif; unset($_from); ?>
    <?php endforeach; endif; unset($_from); ?>
<?php endif; ?>
</table>
<?php if ($this->_tpl_vars['caseID']): ?>
    <div class="crm-submit-buttons">
        <a class="button" href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/case','q' => "reset=1&id=".($this->_tpl_vars['caseID'])."&cid=".($this->_tpl_vars['contactID'])."&action=view"), $this);?>
"><span><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Manage Case<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
    </div>
<?php endif; ?>
<?php else: ?>
    <div class="messages status"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>This activity might not be attached to any case. Please investigate.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
<?php endif; ?>
<?php endif; ?>


<?php echo '
<script type="text/javascript">
function viewRevision( activityId ) {
      var cid= '; ?>
"<?php echo $this->_tpl_vars['contactID']; ?>
"<?php echo ';
      var viewUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/activity/view','h' => 0,'q' => "snippet=4"), $this);?>
"<?php echo ';
  	  cj("#activity-content").load( viewUrl + "&cid="+cid + "&aid=" + activityId);
}

function listRevisions( activityId ) {
      var cid= '; ?>
"<?php echo $this->_tpl_vars['contactID']; ?>
"<?php echo ';
      var viewUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/activity/view','h' => 0,'q' => "snippet=4"), $this);?>
"<?php echo ';
  	  cj("#activity-content").load( viewUrl + "&cid=" + cid + "&aid=" + activityId + "&revs=1" );
}
</script>
'; ?>

</div>