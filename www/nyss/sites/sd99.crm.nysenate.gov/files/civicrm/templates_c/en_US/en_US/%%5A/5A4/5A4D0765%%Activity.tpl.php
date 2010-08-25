<?php /* Smarty version 2.6.26, created on 2010-08-23 13:04:16
         compiled from CRM/Case/Form/Activity.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Case/Form/Activity.tpl', 56, false),array('function', 'cycle', 'CRM/Case/Form/Activity.tpl', 240, false),array('modifier', 'explode', 'CRM/Case/Form/Activity.tpl', 57, false),array('modifier', 'escape', 'CRM/Case/Form/Activity.tpl', 127, false),array('modifier', 'crmReplace', 'CRM/Case/Form/Activity.tpl', 188, false),array('block', 'ts', 'CRM/Case/Form/Activity.tpl', 86, false),array('block', 'edit', 'CRM/Case/Form/Activity.tpl', 172, false),)), $this); ?>
<div class="crm-block crm-form-block">
<?php if ($this->_tpl_vars['cdType']): ?>
   <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/CustomData.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
    <?php if ($this->_tpl_vars['action'] != 8 && $this->_tpl_vars['action'] != 32768): ?>

<?php echo '
<script type="text/javascript">
var target_contact = assignee_contact = target_contact_id = \'\';
'; ?>


<?php if ($this->_tpl_vars['targetContactValues']): ?>
<?php $_from = $this->_tpl_vars['targetContactValues']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['name']):
?>
     <?php echo ' target_contact += \'{"name":"\'+'; ?>
"<?php echo $this->_tpl_vars['name']; ?>
"<?php echo '+\'","id":"\'+'; ?>
"<?php echo $this->_tpl_vars['id']; ?>
"<?php echo '+\'"},\';'; ?>

<?php endforeach; endif; unset($_from); ?>
<?php echo ' eval( \'target_contact = [\' + target_contact + \']\'); '; ?>

<?php endif; ?>

<?php if ($this->_tpl_vars['assigneeContactCount']): ?>
<?php $_from = $this->_tpl_vars['assignee_contact']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['name']):
?>
     <?php echo ' assignee_contact += \'{"name":"\'+'; ?>
"<?php echo $this->_tpl_vars['name']; ?>
"<?php echo '+\'","id":"\'+'; ?>
"<?php echo $this->_tpl_vars['id']; ?>
"<?php echo '+\'"},\';'; ?>

<?php endforeach; endif; unset($_from); ?>
<?php echo ' eval( \'assignee_contact = [\' + assignee_contact + \']\'); '; ?>

<?php endif; ?>

<?php echo '
var target_contact_id = assignee_contact_id = null;
//loop to set the value of cc and bcc if form rule.
var toDataUrl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/checkemail','q' => 'id=1&noemail=1','h' => 0), $this);?>
<?php echo '"; '; ?>

<?php $_from = ((is_array($_tmp=",")) ? $this->_run_mod_handler('explode', true, $_tmp, "assignee,target") : explode($_tmp, "assignee,target")); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['element']):
?>
  <?php $this->assign('currentElement', ($this->_tpl_vars['element'])."_contact_id"); ?>
  <?php if ($this->_tpl_vars['form'][$this->_tpl_vars['currentElement']]['value']): ?>
     <?php echo ' var '; ?>
<?php echo $this->_tpl_vars['currentElement']; ?>
<?php echo ' = cj.ajax({ url: toDataUrl + "&cid='; ?>
<?php echo $this->_tpl_vars['form'][$this->_tpl_vars['currentElement']]['value']; ?>
<?php echo '", async: false }).responseText;'; ?>

  <?php endif; ?>
<?php endforeach; endif; unset($_from); ?>

<?php echo '
if ( target_contact_id ) {
  eval( \'target_contact = \' + target_contact_id );
}
if ( assignee_contact_id ) {
  eval( \'assignee_contact = \' + assignee_contact_id );
}

cj(document).ready( function( ) {
'; ?>

<?php if ($this->_tpl_vars['source_contact'] && $this->_tpl_vars['admin'] && $this->_tpl_vars['action'] != 4): ?> 
<?php echo ' cj( \'#source_contact_id\' ).val( "'; ?>
<?php echo $this->_tpl_vars['source_contact']; ?>
<?php echo '");'; ?>

<?php endif; ?>

<?php echo '

eval( \'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } \');

var sourceDataUrl = "'; ?>
<?php echo $this->_tpl_vars['dataUrl']; ?>
<?php echo '";
var tokenDataUrl  = "'; ?>
<?php echo $this->_tpl_vars['tokenUrl']; ?>
<?php echo '";
var assigneeDataUrl  = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest?fnName=civicrm/contact/search&json=1&return[contact_id]&group=3'), $this);?>
<?php echo '";

var hintText = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Type in a partial or complete name or email address of an existing contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
cj( "#assignee_contact_id").customTokenInput( assigneeDataUrl, { prePopulate: assignee_contact, classes: tokenClass, hintText: hintText });
cj( "#target_contact_id"  ).tokenInput( tokenDataUrl, { prePopulate: target_contact,   classes: tokenClass, hintText: hintText });
cj( \'ul.token-input-list-facebook, div.token-input-dropdown-facebook\' ).css( \'width\', \'450px\' );
cj( "#source_contact_id").autocomplete( sourceDataUrl, { width : 180, selectFirst : false, matchContains:true
                            }).result( function(event, data, formatted) { cj( "#source_contact_qid" ).val( data[1] );
                            }).bind( \'click\', function( ) { cj( "#source_contact_qid" ).val(\'\'); });
});
</script>
'; ?>


    <?php endif; ?>

        <legend>
           <?php if ($this->_tpl_vars['action'] == 8): ?>
              <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
           <?php elseif ($this->_tpl_vars['action'] == 4): ?>
              <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>View<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
           <?php elseif ($this->_tpl_vars['action'] == 32768): ?>
              <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Restore<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
           <?php endif; ?>
        </legend>
        <?php if ($this->_tpl_vars['action'] == 8 || $this->_tpl_vars['action'] == 32768): ?>
            <div class="messages status"> 
              <div class="icon inform-icon"></div> &nbsp;
              <?php if ($this->_tpl_vars['action'] == 8): ?>
                 <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['activityTypeName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click Delete to move this &quot;%1&quot; activity to the Trash.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
              <?php else: ?>
                 <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['activityTypeName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Click Restore to retrieve this &quot;%1&quot; activity from the Trash.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
              <?php endif; ?>  
            </div><br /> 
        <?php else: ?>
        <table class="form-layout">
            <?php if ($this->_tpl_vars['activityTypeDescription']): ?>
           <tr>
              <div id="help"><?php echo $this->_tpl_vars['activityTypeDescription']; ?>
</div>
           </tr>
            <?php endif; ?>
           <tr id="with-clients" class="crm-case-form-block-client_name">
	       <?php if (! $this->_tpl_vars['multiClient']): ?>
              <td class="label font-size12pt"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Client<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
              <td class="view-value font-size12pt"><?php echo ((is_array($_tmp=$this->_tpl_vars['client_name'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
&nbsp;&nbsp;&nbsp;&nbsp;
	       <?php else: ?>
              <td class="label font-size12pt"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Clients<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
              <td class="view-value font-size12pt">
		  <?php $_from = $this->_tpl_vars['client_names']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['clients'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['clients']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['client']):
        $this->_foreach['clients']['iteration']++;
?>
		            <?php echo $this->_tpl_vars['client']['display_name']; ?>
<?php if (! ($this->_foreach['clients']['iteration'] == $this->_foreach['clients']['total'])): ?>; &nbsp; <?php endif; ?>
                  <?php endforeach; endif; unset($_from); ?>

	       <?php endif; ?>

	       <?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2): ?>
		    <br />
		    <a href="#" onClick="buildTargetContact(1); return false;">
		    <span id="with-other-contacts-link" class="add-remove-link hide-block">&raquo; 
		    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>With other contact(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
		    </a>
	       <?php endif; ?>

	       </td>
           </tr>

    	   <?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2): ?>
           <tr class="crm-case-form-block-target_contact_id hide-block"  id="with-contacts-widget">
               <td class="label font-size10pt"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>With Contact<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
               <td><?php echo $this->_tpl_vars['form']['target_contact_id']['html']; ?>

                   <a href="#" onClick="buildTargetContact(1); return false;">
		      <span id="with-clients-link" class="add-remove-link">&raquo; 
		           <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>With client(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                      </span>
		   </a>
		</td>
        	<td><?php echo $this->_tpl_vars['form']['hidden_target_contact']['html']; ?>
</td>
           </tr>
    	   <?php endif; ?>
           <tr class="crm-case-form-block-activityTypeName">
              <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Activity Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
              <td class="view-value bold"><?php echo ((is_array($_tmp=$this->_tpl_vars['activityTypeName'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
           </tr>
           <tr class="crm-case-form-block-source_contact_id">
              <td class="label"><?php echo $this->_tpl_vars['form']['source_contact_id']['label']; ?>
</td>
              <td class="view-value"> <?php if ($this->_tpl_vars['admin']): ?><?php echo $this->_tpl_vars['form']['source_contact_id']['html']; ?>
<?php endif; ?></td>
            </tr>
           <tr class="crm-case-form-block-assignee_contact_id">
              <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Assigned To <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
              <td><?php echo $this->_tpl_vars['form']['assignee_contact_id']['html']; ?>
                   
                  <?php $this->_tag_stack[] = array('edit', array()); $_block_repeat=true;smarty_block_edit($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><span class="description">
                        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You can optionally assign this activity to someone.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                        <?php if ($this->_tpl_vars['config']->activityAssigneeNotification): ?>
                             <br /><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>A copy of this activity will be emailed to each Assignee.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                        <?php endif; ?>
                        </span>
                  <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_edit($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
              </td>
            </tr>

                        <?php if ($this->_tpl_vars['activityTypeFile']): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Case/Form/Activity/".($this->_tpl_vars['activityTypeFile']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
	    <?php if ($this->_tpl_vars['activityTypeFile'] != 'ChangeCaseStartDate'): ?>
            <tr class="crm-case-form-block-subject">
              <td class="label"><?php echo $this->_tpl_vars['form']['subject']['label']; ?>
</td><td class="view-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['subject']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
            </tr>
	    <?php endif; ?>
           <tr class="crm-case-form-block-medium_id">
              <td class="label"><?php echo $this->_tpl_vars['form']['medium_id']['label']; ?>
</td>
              <td class="view-value"><?php echo $this->_tpl_vars['form']['medium_id']['html']; ?>
&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['location']['label']; ?>
 &nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['form']['location']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
           </tr> 
           <tr class="crm-case-form-block-activity_date_time">
              <td class="label"><?php echo $this->_tpl_vars['form']['activity_date_time']['label']; ?>
</td>
              <td class="view-value"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'activity_date_time')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
           </tr>
           <tr class="crm-case-form-block-duration">
              <td class="label"><?php echo $this->_tpl_vars['form']['duration']['label']; ?>
</td>
              <td class="view-value">
                <?php echo $this->_tpl_vars['form']['duration']['html']; ?>

                 <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Total time spent on this activity (in minutes).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
              </td>
           </tr> 
           <tr class="crm-case-form-block-status_id">
              <td class="label"><?php echo $this->_tpl_vars['form']['status_id']['label']; ?>
</td><td class="view-value"><?php echo $this->_tpl_vars['form']['status_id']['html']; ?>
</td>
           </tr>
	   <tr class="crm-case-form-block-priority_id">
              <td class="label"><?php echo $this->_tpl_vars['form']['priority_id']['label']; ?>
</td><td class="view-value"><?php echo $this->_tpl_vars['form']['priority_id']['html']; ?>
</td>
           </tr>
           <tr>
              <td colspan="2"><div id="customData"></div></td>
           </tr>
           <tr class="crm-case-form-block-details">
              <td class="label"><?php echo $this->_tpl_vars['form']['details']['label']; ?>
</td><td class="view-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['details']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
           </tr>
           <tr>
              <td colspan="2"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Form/attachment.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
           </tr>
           <?php if ($this->_tpl_vars['searchRows']): ?>             <tr>
                <td colspan="2">
                    <div id="sendcopy" class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Send a Copy<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
   </div><!-- /.crm-accordion-header -->
 <div id="sendcopy" class="crm-accordion-body">
                   
                    <div class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email a complete copy of this activity record to other people involved with the case. Click the top left box to select all.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
                   <?php echo '<table><tr class="columnheader"><th>'; ?><?php echo $this->_tpl_vars['form']['toggleSelect']['html']; ?><?php echo '&nbsp;</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Case Role'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Name'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Email'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th></tr>'; ?><?php $_from = $this->_tpl_vars['searchRows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['row']):
?><?php echo '<tr class="'; ?><?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?><?php echo '"><td class="crm-case-form-block-contact_'; ?><?php echo $this->_tpl_vars['id']; ?><?php echo '">'; ?><?php echo $this->_tpl_vars['form']['contact_check'][$this->_tpl_vars['id']]['html']; ?><?php echo '</td><td class="crm-case-form-block-role">'; ?><?php echo $this->_tpl_vars['row']['role']; ?><?php echo '</td><td class="crm-case-form-block-display_name">'; ?><?php echo $this->_tpl_vars['row']['display_name']; ?><?php echo '</td><td class="crm-case-form-block-email">'; ?><?php echo $this->_tpl_vars['row']['email']; ?><?php echo '</td></tr>'; ?><?php endforeach; endif; unset($_from); ?><?php echo '</table>'; ?>

                  </div>
                </td>
            </tr>
       </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

            <?php endif; ?>
           <tr>
              <td colspan="2">
              
<div id="follow-up" class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
 <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Schedule Follow-up<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
  </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">

                    <table class="form-layout-compressed">
                        <tr class="crm-case-form-block-followup_activity_type_id">
			    <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Schedule Follow-up Activity<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                            <td><?php echo $this->_tpl_vars['form']['followup_activity_type_id']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['interval']['label']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['interval']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['interval_unit']['html']; ?>
</td>
                        </tr>
                        <tr class="crm-case-form-block-followup_activity_subject">
                           <td class="label"><?php echo $this->_tpl_vars['form']['followup_activity_subject']['label']; ?>
</td>
                           <td><?php echo $this->_tpl_vars['form']['followup_activity_subject']['html']; ?>
</td>
                        </tr>
                    </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
              </td>
           </tr>

	   <?php if ($this->_tpl_vars['form']['tag']['html']): ?>
             <tr class="crm-case-form-block-tag">
                <td class="label"><?php echo $this->_tpl_vars['form']['tag']['label']; ?>
</td>
                <td class="view-value"><div class="crm-select-container"><?php echo $this->_tpl_vars['form']['tag']['html']; ?>
</div>
                                        <?php echo '
                                        <script type="text/javascript">
                                                               $("select[multiple]").crmasmSelect({
                                                                        addItemTarget: \'bottom\',
                                                                        animate: true,
                                                                        highlight: true,
                                                                        sortable: true,
                                                                        respectParents: true
                                                               });
                                        </script>
                                        '; ?>


                </td>
             </tr>
             <?php endif; ?>
           <?php endif; ?>
       </table>
     
     <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>

    <?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/customData.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php echo '
        <script type="text/javascript">
            cj(document).ready(function() {
                '; ?>

                buildCustomData( '<?php echo $this->_tpl_vars['customDataType']; ?>
' );
                <?php if ($this->_tpl_vars['customDataSubType']): ?>
                    buildCustomData( '<?php echo $this->_tpl_vars['customDataType']; ?>
', <?php echo $this->_tpl_vars['customDataSubType']; ?>
 );
                <?php endif; ?>
                <?php echo '
            });
        </script>
        '; ?>

    <?php endif; ?>

    <?php if ($this->_tpl_vars['action'] != 8 && $this->_tpl_vars['action'] != 32768): ?> 
        <script type="text/javascript">
            <?php if ($this->_tpl_vars['searchRows']): ?>
                cj('sendcopy').toggleClass('crm-accordion-open');
                cj('sendcopy').toggleClass('crm-accordion-closed');            
            <?php endif; ?>

            cj('follow-up').toggleClass('crm-accordion-open');
            cj('follow-up').toggleClass('crm-accordion-closed');  

        </script>
    <?php endif; ?>
    
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formNavigate.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

    <?php echo '
    <script type="text/javascript">   

    '; ?>
<?php if ($this->_tpl_vars['action'] == 2 || $this->_tpl_vars['action'] == 1): ?><?php echo '
    cj(document).ready( function( ) {
       var reset = '; ?>
<?php if ($this->_tpl_vars['targetContactValues']): ?>true<?php else: ?>false<?php endif; ?><?php echo ';	    
       buildTargetContact( reset );
    });'; ?>

    <?php endif; ?><?php echo '
    
    function buildTargetContact( resetVal ) {
	 var hideWidget  = showWidget = false;	
    	 var value       = cj("#hidden_target_contact").attr( \'checked\' );	      
	 
	 if ( resetVal ) {
	     if ( value ) {
	       hideWidget  = true;
	       value       = false;
	     } else {
	       showWidget  = true;
	       value       = true;
	     }
	 } else {
            if ( value ) {
	       showWidget = true;
	     } else {
	       hideWidget = true;
	     }
	 }
	 
	 if ( hideWidget ) {
	    cj(\'#with-clients-link\').hide( );
	    cj(\'#with-contacts-widget\').hide( );
	    cj(\'#with-clients\').show( );
	    cj(\'#with-other-contacts-link\').show( );
  	 }
	 if ( showWidget ) {
	    cj(\'#with-contacts-widget\').show( );
	    cj(\'#with-clients-link\').show( );

	    cj(\'#with-other-contacts-link\').hide( );
	    cj(\'#with-clients\').hide( );
	 }
	 cj("#hidden_target_contact").attr( \'checked\', value );
    }	
    </script>
    '; ?>


<?php endif; ?> </script>
</div>