<?php /* Smarty version 2.6.26, created on 2010-08-20 16:10:55
         compiled from CRM/Case/Form/CaseView.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Case/Form/CaseView.tpl', 33, false),array('function', 'crmURL', 'CRM/Case/Form/CaseView.tpl', 55, false),array('function', 'crmKey', 'CRM/Case/Form/CaseView.tpl', 346, false),array('function', 'crmAPI', 'CRM/Case/Form/CaseView.tpl', 504, false),array('modifier', 'crmDate', 'CRM/Case/Form/CaseView.tpl', 81, false),array('modifier', 'crmReplace', 'CRM/Case/Form/CaseView.tpl', 143, false),array('modifier', 'count', 'CRM/Case/Form/CaseView.tpl', 224, false),)), $this); ?>

<div class="crm-block crm-form-block crm-case-caseview-form-block">
<?php if ($this->_tpl_vars['showRelatedCases']): ?> 
    <table class="report">
      <tr class="columnheader">
    	  <th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Client Name<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	  <th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
	  <th></th>
      </tr>
      
      <?php $_from = $this->_tpl_vars['relatedCases']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['caseId'] => $this->_tpl_vars['row']):
?>
      <tr>
      	 <td class="crm-case-caseview-client_name label"><?php echo $this->_tpl_vars['row']['client_name']; ?>
</td>
	 <td class="crm-case-caseview-case_type label"><?php echo $this->_tpl_vars['row']['case_type']; ?>
</td>
	 <td class="label"><?php echo $this->_tpl_vars['row']['links']; ?>
</td>
      </tr>	
      <?php endforeach; endif; unset($_from); ?>
   </table>

<?php else: ?>
<h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Summary<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
    <table class="report">
	<?php if ($this->_tpl_vars['multiClient']): ?>
	<tr class="crm-case-caseview-client">
		<td colspan="4" class="label">
		<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Clients:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> 
		<?php $_from = $this->_tpl_vars['caseRoles']['client']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['clients'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['clients']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['client']):
        $this->_foreach['clients']['iteration']++;
?>
		  <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "action=view&reset=1&cid=".($this->_tpl_vars['client']['contact_id'])), $this);?>
" title="view contact record"><?php echo $this->_tpl_vars['client']['display_name']; ?>
</a><?php if (! ($this->_foreach['clients']['iteration'] == $this->_foreach['clients']['total'])): ?>, &nbsp; <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
		<a href="#" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>add new client to the case<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" onclick="addClient( );return false;">
			<span class="icon edit-icon"></span>
		</a>
	     <?php if ($this->_tpl_vars['hasRelatedCases']): ?>
        	<div class="crm-block relatedCases-link"><a href='#' onClick='viewRelatedCases( <?php echo $this->_tpl_vars['caseID']; ?>
, <?php echo $this->_tpl_vars['contactID']; ?>
 ); return false;'><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Related Cases<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></div>
        <?php endif; ?>
        </td>
	</tr>
	<?php endif; ?>
        <tr>
	    <?php if (! $this->_tpl_vars['multiClient']): ?>
             <td>
    		 <table class="form-layout-compressed" border="1">
    		 <?php $_from = $this->_tpl_vars['caseRoles']['client']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['client']):
?>
          	   <tr class="crm-case-caseview-display_name">
    		     <td class="label-left" style="padding: 0px"><?php echo $this->_tpl_vars['client']['display_name']; ?>
</td>
    		   </tr>
    	       <?php if ($this->_tpl_vars['client']['phone']): ?>
        		   <tr class="crm-case-caseview-phone">
        		     <td class="label-left description" style="padding: 0px"><?php echo $this->_tpl_vars['client']['phone']; ?>
</td>
        		   </tr>
    		   <?php endif; ?>
               <?php if ($this->_tpl_vars['client']['birth_date']): ?>
            	   <tr class="crm-case-caseview-birth_date">
                         <td class="label-left description" style="padding: 0px"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>DOB<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>: <?php echo ((is_array($_tmp=$this->_tpl_vars['client']['birth_date'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
</td>
                    </tr>
               <?php endif; ?>
             <?php endforeach; endif; unset($_from); ?>
    	     </table>
    	     <?php if ($this->_tpl_vars['hasRelatedCases']): ?>
             	<div class="crm-block relatedCases-link"><a href='#' onClick='viewRelatedCases( <?php echo $this->_tpl_vars['caseID']; ?>
, <?php echo $this->_tpl_vars['contactID']; ?>
 ); return false;'><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Related Cases<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></div>
             <?php endif; ?>
             </td>
	    <?php endif; ?>
        <td class="crm-case-caseview-case_type label">
            <span class="crm-case-summary-label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>:</span>&nbsp;<?php echo $this->_tpl_vars['caseDetails']['case_type']; ?>
&nbsp;<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/activity','q' => "action=add&reset=1&cid=".($this->_tpl_vars['contactId'])."&caseid=".($this->_tpl_vars['caseId'])."&selectedChild=activity&atype=".($this->_tpl_vars['changeCaseTypeId'])), $this);?>
" title="Change case type (creates activity record)"><span class="icon edit-icon"></span></a>
        </td>
        <td class="crm-case-caseview-case_status label">
            <span class="crm-case-summary-label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Status<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>:</span>&nbsp;<?php echo $this->_tpl_vars['caseDetails']['case_status']; ?>
&nbsp;<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/activity','q' => "action=add&reset=1&cid=".($this->_tpl_vars['contactId'])."&caseid=".($this->_tpl_vars['caseId'])."&selectedChild=activity&atype=".($this->_tpl_vars['changeCaseStatusId'])), $this);?>
" title="Change case status (creates activity record)"><span class="icon edit-icon"></span></a>
        </td>
        <td class="crm-case-caseview-case_start_date label">
            <span class="crm-case-summary-label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Start Date<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>:</span>&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['caseDetails']['case_start_date'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
&nbsp;<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/activity','q' => "action=add&reset=1&cid=".($this->_tpl_vars['contactId'])."&caseid=".($this->_tpl_vars['caseId'])."&selectedChild=activity&atype=".($this->_tpl_vars['changeCaseStartDateId'])), $this);?>
" title="Change case start date (creates activity record)"><span class="icon edit-icon"></span></a>
        </td>
        <td class="crm-case-caseview-<?php echo $this->_tpl_vars['caseID']; ?>
 label">
            <span class="crm-case-summary-label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case ID<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>:</span>&nbsp;<?php echo $this->_tpl_vars['caseID']; ?>

        </td>
    </tr>
    </table>
    <?php if ($this->_tpl_vars['hookCaseSummary']): ?>
      <div id="caseSummary">
      <?php $_from = $this->_tpl_vars['hookCaseSummary']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['div_id'] => $this->_tpl_vars['val']):
?>
        <div id="<?php echo $this->_tpl_vars['div_id']; ?>
"><label><?php echo $this->_tpl_vars['val']['label']; ?>
</label><div class="value"><?php echo $this->_tpl_vars['val']['value']; ?>
</div></div>
      <?php endforeach; endif; unset($_from); ?>
      </div>
    <?php endif; ?>

    <table class="form-layout">
        <tr class="crm-case-caseview-form-block-activity_type_id">
            <td><?php echo $this->_tpl_vars['form']['activity_type_id']['label']; ?>
<br /><?php echo $this->_tpl_vars['form']['activity_type_id']['html']; ?>
&nbsp;<input type="button" accesskey="N" value="Go" name="new_activity" onclick="checkSelection( this );"/></td>
	    <?php if ($this->_tpl_vars['hasAccessToAllCases']): ?>	
            <td>
                <span class="crm-button"><div class="icon print-icon"></div><input type="button"  value="Print Case Report" name="case_report_all" onclick="printCaseReport( );"/></span>
            </td> 
        </tr>
        <tr>
            <td class="crm-case-caseview-form-block-timeline_id"><?php echo $this->_tpl_vars['form']['timeline_id']['label']; ?>
<br /><?php echo $this->_tpl_vars['form']['timeline_id']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['_qf_CaseView_next']['html']; ?>
</td> 
            <td class="crm-case-caseview-form-block-report_id"><?php echo $this->_tpl_vars['form']['report_id']['label']; ?>
<br /><?php echo $this->_tpl_vars['form']['report_id']['html']; ?>
&nbsp;<input type="button" accesskey="R" value="Go" name="case_report" onclick="checkSelection( this );"/></td> 
        <?php else: ?>
            <td></td>
	    <?php endif; ?>
        </tr>

	<?php if ($this->_tpl_vars['mergeCases']): ?>
    	<tr class="crm-case-caseview-form-block-merge_case_id">
    	   <td colspan='2'><a href="#" onClick='cj("#merge_cases").toggle( ); return false;'><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Merge Case<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>	
    	        <span id='merge_cases' class='hide-block'>
    	            <?php echo $this->_tpl_vars['form']['merge_case_id']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['_qf_CaseView_next_merge_case']['html']; ?>

    	        </span>
    	   </td>
    	</tr>
	<?php endif; ?>

	<?php if (call_user_func ( array ( 'CRM_Core_Permission' , 'giveMeAllACLs' ) )): ?>
    	<tr class="crm-case-caseview-form-block-change_client_id">
    	   <td colspan='2'><a href="#" onClick='cj("#change_client").toggle( ); return false;'><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Assign to Another Client<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>	
    	    <span id='change_client' class='hide-block'>
    	        <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['change_client_id']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'twenty') : smarty_modifier_crmReplace($_tmp, 'class', 'twenty')); ?>
&nbsp;<?php echo $this->_tpl_vars['form']['_qf_CaseView_next_edit_client']['html']; ?>

    	    </span>
    	   </td>
    	</tr>
	<?php endif; ?>
    </table>

<div id="view-related-cases">
     <div id="related-cases-content"></div>
</div>

<div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed crm-case-roles-block">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Roles<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
    <span id="restmsg" class="msgok" style="display:none"></span>
 
    <?php if ($this->_tpl_vars['hasAccessToAllCases']): ?>
    <div class="crm-submit-buttons">
      <a class="button" href="#" onClick="Javascript:addRole();return false;"><span><div class="icon add-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add new role<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
    </div>
    <?php endif; ?>

    <table class="report-layout">
    	<tr class="columnheader">
    		<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Role<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Name<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	   	<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Phone<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
            <th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
            <?php if ($this->_tpl_vars['relId'] != 'client' && $this->_tpl_vars['hasAccessToAllCases']): ?>
    		    <th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Actions<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<?php endif; ?>
    	</tr>
		<?php $this->assign('rowNumber', 1); ?>
        <?php $_from = $this->_tpl_vars['caseRelationships']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relId'] => $this->_tpl_vars['row']):
?>
        <tr>
            <td class="crm-case-caseview-role-relation label"><?php echo $this->_tpl_vars['row']['relation']; ?>
</td>
            <td class="crm-case-caseview-role-name" id="relName_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "action=view&reset=1&cid=".($this->_tpl_vars['row']['cid'])), $this);?>
" title="view contact record"><?php echo $this->_tpl_vars['row']['name']; ?>
</a></td>
           
            <td class="crm-case-caseview-role-phone" id="phone_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><?php echo $this->_tpl_vars['row']['phone']; ?>
</td>
            <td class="crm-case-caseview-role-email" id="email_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><?php if ($this->_tpl_vars['row']['email']): ?>
            <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/activity','q' => "reset=1&action=add&atype=3&cid=".($this->_tpl_vars['row']['cid'])."&caseid=".($this->_tpl_vars['caseID'])), $this);?>
" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>compose and send an email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
            	<div class="icon email-icon" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>compose and send an email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"></div>
           	</a><?php endif; ?>
            </td>
          <?php if ($this->_tpl_vars['relId'] != 'client' && $this->_tpl_vars['hasAccessToAllCases']): ?>
            <td id ="edit_<?php echo $this->_tpl_vars['rowNumber']; ?>
">
            	<a href="#" title="edit case role" onclick="createRelationship( <?php echo $this->_tpl_vars['row']['relation_type']; ?>
, <?php echo $this->_tpl_vars['row']['cid']; ?>
, <?php echo $this->_tpl_vars['relId']; ?>
, <?php echo $this->_tpl_vars['rowNumber']; ?>
, '<?php echo $this->_tpl_vars['row']['relation']; ?>
' );return false;">
            	<div class="icon edit-icon" ></div>
            	</a> &nbsp;&nbsp;
            	<a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/rel','q' => "action=delete&reset=1&cid=".($this->_tpl_vars['contactID'])."&id=".($this->_tpl_vars['relId'])."&caseID=".($this->_tpl_vars['caseID'])), $this);?>
" onclick = "if (confirm('<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to remove this person from their case role<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>?') ) this.href+='&confirmed=1'; else return false;">
            	<div class="icon delete-icon" title="remove contact from case role"></div>
            	</a>
            	
            </td>
          <?php endif; ?>
        </tr>
		<?php $this->assign('rowNumber', ($this->_tpl_vars['rowNumber']+1)); ?>
        <?php endforeach; endif; unset($_from); ?>

        <?php $_from = $this->_tpl_vars['caseRoles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relTypeID'] => $this->_tpl_vars['relName']):
?>
         <?php if ($this->_tpl_vars['relTypeID'] != 'client'): ?> 
           <tr>
               <td class="crm-case-caseview-role-relName label"><?php echo $this->_tpl_vars['relName']; ?>
</td>
               <td class="crm-case-caseview-role-relName_<?php echo $this->_tpl_vars['rowNumber']; ?>
" id="relName_<?php echo $this->_tpl_vars['rowNumber']; ?>
">(not assigned)</td>
               <td class="crm-case-caseview-role-phone" id="phone_<?php echo $this->_tpl_vars['rowNumber']; ?>
"></td>
               <td class="crm-case-caseview-role-email" id="email_<?php echo $this->_tpl_vars['rowNumber']; ?>
"></td>
	       <?php if ($this->_tpl_vars['hasAccessToAllCases']): ?>               
	       <td id ="edit_<?php echo $this->_tpl_vars['rowNumber']; ?>
">
	       <a href="#" title="edit case role" onclick="createRelationship( <?php echo $this->_tpl_vars['relTypeID']; ?>
, null, null, <?php echo $this->_tpl_vars['rowNumber']; ?>
, '<?php echo $this->_tpl_vars['relName']; ?>
' );return false;">
	       	<div class="icon edit-icon"></div>
	       </a> 
	       </td>
	       <?php else: ?>
	       <td></td>
	       <?php endif; ?>
           </tr>
         <?php else: ?>
           <tr>
               <td rowspan="<?php echo count($this->_tpl_vars['relName']); ?>
" class="crm-case-caseview-role-label label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Client<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
	   <?php $_from = $this->_tpl_vars['relName']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['clientsRoles'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['clientsRoles']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['client']):
        $this->_foreach['clientsRoles']['iteration']++;
?>
               <?php if (! ($this->_foreach['clientsRoles']['iteration'] <= 1)): ?></tr><?php endif; ?>
               <td class="crm-case-caseview-role-sort_name" id="relName_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "action=view&reset=1&cid=".($this->_tpl_vars['client']['contact_id'])), $this);?>
" title="view contact record"><?php echo $this->_tpl_vars['client']['sort_name']; ?>
</a></td>
               <td class="crm-case-caseview-role-phone" id="phone_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><?php echo $this->_tpl_vars['client']['phone']; ?>
</td>
               <td class="crm-case-caseview-role-email" id="email_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><?php if ($this->_tpl_vars['client']['email']): ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/activity','q' => "reset=1&action=add&atype=3&cid=".($this->_tpl_vars['client']['contact_id'])."&caseid=".($this->_tpl_vars['caseID'])), $this);?>
" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>compose and send an email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"><div class="icon email-icon"></div></a>&nbsp;<?php endif; ?></td>
               <td></td>
           </tr>
           <?php endforeach; endif; unset($_from); ?>
         <?php endif; ?>
		<?php $this->assign('rowNumber', ($this->_tpl_vars['rowNumber']+1)); ?>
        <?php endforeach; endif; unset($_from); ?>
    </table>    
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->


<div id="dialog">
     <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Begin typing last name of contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><br/>
     <input type="text" id="rel_contact"/>
     <input type="hidden" id="rel_contact_id" value="">
</div>

<?php echo '
<script type="text/javascript">
var selectedContact = \'\';
var caseID = '; ?>
"<?php echo $this->_tpl_vars['caseID']; ?>
"<?php echo ';
var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest','q' => 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=newcontact','h' => 0), $this);?>
"<?php echo ';
cj( "#change_client_id").autocomplete( contactUrl, { width : 250, selectFirst : false, matchContains:true
                            }).result( function(event, data, formatted) { cj( "#contact_id" ).val( data[1] ); selectedContact = data[0];
                            }).bind( \'click\', function( ) { cj( "#contact_id" ).val(\'\'); });

cj("#dialog").hide( );

function addClient( ) {
    cj("#dialog").show( );

    cj("#dialog").dialog({
        title: "Add Client to the Case",
        modal: true,
		bgiframe: true,
		overlay: { opacity: 0.5, background: "black" },
		beforeclose: function(event, ui) { cj(this).dialog("destroy"); },

		open:function() {

			var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest','q' => 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=caseview','h' => 0), $this);?>
"<?php echo ';

			cj("#rel_contact").autocomplete( contactUrl, {
				width: 260,
				selectFirst: false,
	                        matchContains: true 
			});
			
			cj("#rel_contact").focus();
			cj("#rel_contact").result(function(event, data, formatted) {
				cj("input[id=rel_contact_id]").val(data[1]);
			});		    
		
		},

		buttons: { "Done": function() { cj(this).dialog("close"); cj(this).dialog("destroy"); }}	
	}
	)
}

function createRelationship( relType, contactID, relID, rowNumber, relTypeName ) {
    cj("#dialog").show( );

	cj("#dialog").dialog({
		title: "Assign Case Role",
		modal: true, 
		bgiframe: true,
		overlay: { 
			opacity: 0.5, 
			background: "black" 
		},
		beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

		open:function() {
			/* set defaults if editing */
			cj("#rel_contact").val( "" );
			cj("#rel_contact_id").val( null );
			if ( contactID ) {
				cj("#rel_contact_id").val( contactID );
				cj("#rel_contact").val( cj("#relName_" + rowNumber).text( ) );
			}

			var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest','q' => 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=caseview','h' => 0), $this);?>
"<?php echo ';

			cj("#rel_contact").autocomplete( contactUrl, {
				width: 260,
				selectFirst: false,
	                        matchContains: true 
			});
			
			cj("#rel_contact").focus();
			cj("#rel_contact").result(function(event, data, formatted) {
				cj("input[id=rel_contact_id]").val(data[1]);
			});		    
		},

		buttons: { 
			"Ok": function() { 	    
				if ( ! cj("#rel_contact").val( ) ) {
					alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select valid contact from the list<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '.\');
					return false;
				}

				var sourceContact = '; ?>
"<?php echo $this->_tpl_vars['contactID']; ?>
"<?php echo ';
				var caseID        = '; ?>
"<?php echo $this->_tpl_vars['caseID']; ?>
"<?php echo ';

				var v1 = cj("#rel_contact_id").val( );

				if ( ! v1 ) {
					alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select valid contact from the list<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '.\');
					return false;
				}

				var postUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/relation','h' => 0), $this);?>
"<?php echo ';
                cj.post( postUrl, { rel_contact: v1, rel_type: relType, contact_id: sourceContact, rel_id: relID, case_id: caseID, key: '; ?>
"<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/relation'), $this);?>
"<?php echo ' },
                    function( data ) {
                        var resourceBase   = '; ?>
"<?php echo $this->_tpl_vars['config']->resourceBase; ?>
"<?php echo ';

			var html = \'\';			
			if ( data.status == \'process-relationship-success\' ) {
                            var contactViewUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => 'action=view&reset=1&cid=','h' => 0), $this);?>
"<?php echo ';	
                            var deleteUrl      = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/rel','q' => "action=delete&reset=1&cid=".($this->_tpl_vars['contactID'])."&caseID=".($this->_tpl_vars['caseID'])."&id=",'h' => 0), $this);?>
"<?php echo ';	
                            var html = \'<a href=\' + contactViewUrl + data.cid +\' title="view contact record">\' +  data.name +\'</a>\';
                            cj(\'#relName_\' + rowNumber ).html( html );
                            html = \'\';
                            html = \'<a onclick="createRelationship( \' + relType +\',\'+ data.cid +\', \' + data.rel_id +\', \' + rowNumber +\', \\\'\'+ relTypeName +\'\\\' ); return false" title="edit case role" href="#"><div class="icon edit-icon" ></div></a> &nbsp;&nbsp; <a href=\' + deleteUrl + data.rel_id +\' onclick = "if (confirm(\\\''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to delete this relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '?\\\') ) this.href +=\\\'&confirmed=1\\\'; else return false;"><div title="remove contact from case role" class="icon delete-icon"></div></a>\';
                            cj(\'#edit_\' + rowNumber ).html( html );

			} else {
			   html = \'<img src="\' +resourceBase+\'i/edit.png" title="edit case role" onclick="createRelationship( \' + relType +\',\'+ data.cid +\', \' + data.rel_id +\', \' + rowNumber +\', \\\'\'+ relTypeName +\'\\\' );">&nbsp;&nbsp;\';
			   var relTypeAdminLink = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/reltype','q' => 'reset=1','h' => 0), $this);?>
"<?php echo ';
			   var errorMsg = \''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The relationship type definition for the  <?php echo '\' + relTypeName + \''; ?>
 case role is not valid. Both sides of the relationship type must be an Individual or a subtype of Individual. You can review and edit relationship types at <a href="<?php echo '\' + relTypeAdminLink + \''; ?>
">Administer >> Option Lists >> Relationship Types</a><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '.\'; 

			   //display error message.
			   var imageIcon = "<a href=\'#\'  onclick=\'cj( \\"#restmsg\\" ).hide( ); return false;\'>" + \'<div class="icon close-icon"></div>\' + \'</a>\';
			   cj( \'#restmsg\' ).html( imageIcon + errorMsg  ).show( );
			}

                        html = \'\';
                        if ( data.phone ) {
                            html = data.phone;
                        }	
                        cj(\'#phone_\' + rowNumber ).html( html );

                        html = \'\';
                        if ( data.email ) {
                            var activityUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/activity','q' => "atype=3&action=add&reset=1&caseid=".($this->_tpl_vars['caseID'])."&cid=",'h' => 0), $this);?>
"<?php echo ';
                            html = \'<a href=\' + activityUrl + data.cid + \'><div title="compose and send an email" class="icon email-icon"></div></a>&nbsp;\';
                        } 
                        cj(\'#email_\' + rowNumber ).html( html );

                        }, \'json\' 
                    );

				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			},

			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			} 
		} 

	});
}

function viewRelatedCases( mainCaseID, contactID ) {
  cj("#view-related-cases").show( );
     cj("#view-related-cases").dialog({
        title: "Related Cases",
        modal: true, 
        width : "680px", 
        height: \'auto\', 
        resizable: true,
        bgiframe: true,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        },

        beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

        open:function() {

	    var dataUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/case','h' => 0,'q' => "snippet=4"), $this);?>
"<?php echo ';
	    dataUrl = dataUrl + \'&id=\' + mainCaseID + \'&cid=\' +contactID + \'&relatedCases=true&action=view&context=case&selectedChild=case\';

	     cj.ajax({ 
             	       url     : dataUrl,   
        	       async   : false,
        	       success : function(html){
            	       	         cj("#related-cases-content" ).html( html );
        		         }
    	     });   
        },

        buttons: { 
            "Done": function() { 	    
                cj(this).dialog("close"); 
                cj(this).dialog("destroy");
            }
        }
    });
}

cj(document).ready(function(){
   cj("#view-activity").hide( );
});
</script>
'; ?>


<?php if ($this->_tpl_vars['hasAccessToAllCases']): ?>
<div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed crm-case-other-relationships-block">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Other Relationships<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
  
  <?php if ($this->_tpl_vars['clientRelationships']): ?>
    <div class="crm-submit-buttons">
    <a class="button" href="#" onClick="window.location='<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/rel','q' => "action=add&reset=1&cid=".($this->_tpl_vars['contactId'])."&caseID=".($this->_tpl_vars['caseID'])), $this);?>
'; return false;">
    <span><div class="icon add-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add client relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></span>
    </div>
	
    <table class="report-layout otherRelationships">
    	<tr class="columnheader">
    		<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Client Relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Name<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Phone<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	</tr>
        <?php $_from = $this->_tpl_vars['clientRelationships']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relId'] => $this->_tpl_vars['row']):
?>
        <tr id="otherRelationship-<?php echo $this->_tpl_vars['row']['cid']; ?>
">
            <td class="crm-case-caseview-otherrelationship-relation label"><?php echo $this->_tpl_vars['row']['relation']; ?>
</td>
            <td class="crm-case-caseview-otherrelationship-name" id="relName_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "action=view&reset=1&cid=".($this->_tpl_vars['row']['cid'])), $this);?>
" title="view contact record"><?php echo $this->_tpl_vars['row']['name']; ?>
</a></td>
            <td class="crm-case-caseview-otherrelationship-phone" id="phone_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><?php echo $this->_tpl_vars['row']['phone']; ?>
</td>
	        <td class="crm-case-caseview-otherrelationship-email" id="email_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><?php if ($this->_tpl_vars['row']['email']): ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/activity','q' => "reset=1&action=add&atype=3&cid=".($this->_tpl_vars['row']['cid'])."&caseid=".($this->_tpl_vars['caseID'])), $this);?>
" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>compose and send an email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"><div class="icon email-icon"></div></a>&nbsp;<?php endif; ?></td>
        </tr>
		<?php $this->assign('rowNumber', ($this->_tpl_vars['rowNumber']+1)); ?>
        <?php endforeach; endif; unset($_from); ?>
    </table>
  <?php else: ?>
    <div class="messages status">
      <div class="icon inform-icon"></div>
          <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/rel','q' => "action=add&reset=1&cid=".($this->_tpl_vars['contactId'])."&caseID=".($this->_tpl_vars['caseID'])), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('crmURL', ob_get_contents());ob_end_clean(); ?>
          <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['crmURL'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There are no Relationships entered for this client. You can <a accesskey="N" href='%1'>add one</a>.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    </div>
  <?php endif; ?>

  <br />
  
  <?php if ($this->_tpl_vars['globalRelationships']): ?>
    <div class="crm-submit-buttons">
        <a class="button" href="#"  onClick="window.location='<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/group/search','q' => "reset=1&context=amtg&amtgID=".($this->_tpl_vars['globalGroupInfo']['id'])), $this);?>
'; return false;">
        <span><div class="icon add-icon"></div><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['globalGroupInfo']['title'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add members to %1<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
    </div>
	
    <table class="report-layout globalrelationship">
    	<tr class="columnheader">
    		<th><?php echo $this->_tpl_vars['globalGroupInfo']['title']; ?>
</th>
            <th>Organization</th>
    		<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Phone<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    		<th><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></th>
    	</tr>
        <?php $_from = $this->_tpl_vars['globalRelationships']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['relId'] => $this->_tpl_vars['row']):
?>

				<?php $this->assign('contact_id', $this->_tpl_vars['row']['contact_id']); ?>
		<?php echo smarty_function_crmAPI(array('entity' => 'contact','action' => 'search','var' => 'organization','contact_id' => $this->_tpl_vars['contact_id'],'return' => "current_employer,current_employer_id"), $this);?>


        <tr id="caseResource-<?php echo $this->_tpl_vars['row']['contact_id']; ?>
">
            <td class="crm-case-caseview-globalrelationship-sort_name label" id="relName_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "action=view&reset=1&cid=".($this->_tpl_vars['row']['contact_id'])), $this);?>
" title="view contact record"><?php echo $this->_tpl_vars['row']['sort_name']; ?>
</a></td>
            <td class="crm-case-caseview-globalrelationship-organization label" id="organization_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "action=view&reset=1&cid=".($this->_tpl_vars['organization'][$this->_tpl_vars['contact_id']]['current_employer_id'])), $this);?>
" title="view organization record"><?php echo $this->_tpl_vars['organization'][$this->_tpl_vars['contact_id']]['current_employer']; ?>
</a></td>
            <td class="crm-case-caseview-globalrelationship-phone" id="phone_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><?php echo $this->_tpl_vars['row']['phone']; ?>
</td>
	    	<td class="crm-case-caseview-globalrelationship-email" id="email_<?php echo $this->_tpl_vars['rowNumber']; ?>
"><?php if ($this->_tpl_vars['row']['email']): ?><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/activity','q' => "reset=1&action=add&atype=3&cid=".($this->_tpl_vars['row']['contact_id'])."&caseid=".($this->_tpl_vars['caseID'])), $this);?>
" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>compose and send an email<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"><div title="compose and send an email" class="icon email-icon"></div></a>&nbsp;<?php endif; ?></td>
        </tr>
		<?php $this->assign('rowNumber', ($this->_tpl_vars['rowNumber']+1)); ?>
        <?php endforeach; endif; unset($_from); ?>
    </table>
  <?php elseif ($this->_tpl_vars['globalGroupInfo']['id']): ?>
    <div class="messages status">
      <div class="icon inform-icon"></div>&nbsp;        
          <?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/group/search','q' => "reset=1&context=amtg&amtgID=".($this->_tpl_vars['globalGroupInfo']['id'])), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('crmURL', ob_get_contents());ob_end_clean(); ?>
          <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['crmURL'],'2' => $this->_tpl_vars['globalGroupInfo']['title'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The group %2 has no members. You can <a href='%1'>add one</a>.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    </div>
  <?php endif; ?>

 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

<?php endif; ?>  

<div id="addRoleDialog">
<?php echo $this->_tpl_vars['form']['role_type']['label']; ?>
<br />
<?php echo $this->_tpl_vars['form']['role_type']['html']; ?>

<br /><br />
    <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Begin typing last name of contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><br/>
    <input type="text" id="role_contact"/>
    <input type="hidden" id="role_contact_id" value="">
</div>

<?php echo '
<script type="text/javascript">

cj("#addRoleDialog").hide( );
function addRole() {
    cj("#addRoleDialog").show( );

	cj("#addRoleDialog").dialog({
		title: "Add Role",
		modal: true,
		bgiframe: true, 
		overlay: { 
			opacity: 0.5, 
			background: "black" 
		},

        beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

		open:function() {
			/* set defaults if editing */
			cj("#role_contact").val( "" );
			cj("#role_contact_id").val( null );

			var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest','q' => 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=caseview','h' => 0), $this);?>
"<?php echo ';

			cj("#role_contact").autocomplete( contactUrl, {
				width: 260,
				selectFirst: false,
				matchContains: true 
			});
			
			cj("#role_contact").focus();
			cj("#role_contact").result(function(event, data, formatted) {
				cj("input[id=role_contact_id]").val(data[1]);
			});		    
		},

		buttons: { 
			"Ok": function() { 	    
				if ( ! cj("#role_contact").val( ) ) {
					alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select valid contact from the list<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '.\');
					return false;
				}

				var sourceContact = '; ?>
"<?php echo $this->_tpl_vars['contactID']; ?>
"<?php echo ';
				var caseID        = '; ?>
"<?php echo $this->_tpl_vars['caseID']; ?>
"<?php echo ';
				var relID         = null;

				var v1 = cj("#role_contact_id").val( );

				if ( ! v1 ) {
					alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select valid contact from the list<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '.\');
					return false;
				}

				var v2 = cj("#role_type").val();
				if ( ! v2 ) {
					alert(\''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select valid type from the list<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '.\');
					return false;
				}
				
               /* send synchronous request so that disabling any actions for slow servers*/
				var postUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/relation','h' => 0), $this);?>
"<?php echo '; 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy");
                		var data = \'rel_contact=\'+ v1 + \'&rel_type=\'+ v2 + \'&contact_id=\'+sourceContact + \'&rel_id=\'+ relID + \'&case_id=\' + caseID + "&key='; ?>
<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/relation'), $this);?>
<?php echo '";
                		cj.ajax({ type     : "POST", 
					  url      : postUrl, 
					  data     : data, 
					  async    : false,
					  dataType : "json",
					  success  : function( values ) {
					  	    	if ( values.status == \'process-relationship-success\' ) {
               						     window.location.reload();
							} else {
							     var relTypeName = cj("#role_type :selected").text();  
							     var relTypeAdminLink = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/admin/reltype','q' => 'reset=1','h' => 0), $this);?>
"<?php echo ';
			  				     var errorMsg = \''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The relationship type definition for the  <?php echo '\' + relTypeName + \''; ?>
 case role is not valid. Both sides of the relationship type must be an Individual or a subtype of Individual. You can review and edit relationship types at <a href="<?php echo '\' + relTypeAdminLink + \''; ?>
">Administer >> Option Lists >> Relationship Types</a><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '.\'; 

			   				     //display error message.
			   				     var imageIcon = "<a href=\'#\'  onclick=\'cj( \\"#restmsg\\" ).hide( ); return false;\'>" + \'<div class="icon close-icon"></div>\' + \'</a>\';
			   				     cj( \'#restmsg\' ).html( imageIcon + errorMsg  ).show( );  
							}
					  	    }
				       });
 			},

			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			} 
		} 

	});
}

</script>
'; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Case/Form/ActivityToCase.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['showTags'] || $this->_tpl_vars['showTagsets']): ?>

<div id="casetags" class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-open crm-case-tags-block">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
  <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Tags<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
  <?php if ($this->_tpl_vars['tags']): ?>
    <div class="crm-block crm-content-block crm-case-caseview-display-tags"><?php echo $this->_tpl_vars['tags']; ?>
</div>
  <?php endif; ?>

  <?php $_from = $this->_tpl_vars['tagset']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['displayTagset']):
?>
      <?php if ($this->_tpl_vars['displayTagset']['entityTagsArray']): ?>
          <div class="crm-block crm-content-block crm-case-caseview-display-tagset">
              &nbsp;&nbsp;<?php echo $this->_tpl_vars['displayTagset']['parentName']; ?>
:
              <?php $_from = $this->_tpl_vars['displayTagset']['entityTagsArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['tagsetList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['tagsetList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['val']):
        $this->_foreach['tagsetList']['iteration']++;
?>
                  &nbsp;<?php echo $this->_tpl_vars['val']['name']; ?>
<?php if (! ($this->_foreach['tagsetList']['iteration'] == $this->_foreach['tagsetList']['total'])): ?>,<?php endif; ?>
              <?php endforeach; endif; unset($_from); ?>
          </div>
      <?php endif; ?>
  <?php endforeach; endif; unset($_from); ?>

  <?php if (! tags && ! $this->_tpl_vars['displayTagset']['entityTagsArray']): ?>
    <div class="status">
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>There are no tags currently assigend to this case.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
    </div>
  <?php endif; ?>

  <div class="crm-submit-buttons"><input type="button" class="form-submit" onClick="javascript:addTags()" value=<?php if ($this->_tpl_vars['tags'] || $this->_tpl_vars['displayTagset']['entityTagsArray']): ?>"<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Tags<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"<?php else: ?>"<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Add Tags<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"<?php endif; ?> /></div>

 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

    <div id="manageTags">
        <div class="label"><?php echo $this->_tpl_vars['form']['case_tag']['label']; ?>
</div>
        <div class="view-value"><div class="crm-select-container"><?php echo $this->_tpl_vars['form']['case_tag']['html']; ?>
</div>
        <div style="text-align:left;"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/Tag.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
    </div>
    </div>

<?php echo '
<script type="text/javascript">
cj("select[multiple]").crmasmSelect({
    addItemTarget: \'bottom\',
    animate: true,
    highlight: true,
    sortable: true,
    respectParents: true
});

cj("#manageTags").hide( );
function addTags() {
    cj("#manageTags").show( );

    cj("#manageTags").dialog({
        title: "Change Case Tags",
        modal: true,
        bgiframe: true,
        width : 450,
        overlay: { 
            opacity: 0.5, 
            background: "black" 
        },

        beforeclose: function(event, ui) {
            cj(this).dialog("destroy");
        },

        open:function() {
            /* set defaults if editing */
        },

        buttons: { 
            "Save": function() { 
                var tagsChecked = \'\';	    
                var caseID      = '; ?>
<?php echo $this->_tpl_vars['caseID']; ?>
<?php echo ';	

                cj("#manageTags #tags option").each( function() {
                    if ( cj(this).attr(\'selected\') == true) {
                        if ( !tagsChecked ) {
                            tagsChecked = cj(this).val() + \'\';
                        } else {
                            tagsChecked = tagsChecked + \',\' + cj(this).val();
                        }
                    }
                });
                
                var tagList = \'\';
                cj("#manageTags input[name^=taglist]").each( function( ) {
                    if ( !tagsChecked ) {
                        tagsChecked = cj(this).val() + \'\';
                    } else {
                        tagsChecked = tagsChecked + \',\' + cj(this).val();
                    }
                });
                
                var postUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/ajax/processtags','h' => 0), $this);?>
"<?php echo '; 
                var data = \'case_id=\' + caseID + \'&tag=\' + tagsChecked + \'&key=\' + '; ?>
"<?php echo smarty_function_crmKey(array('name' => 'civicrm/case/ajax/processtags'), $this);?>
"<?php echo ';

                cj.ajax({ type: "POST", url: postUrl, data: data, async: false });
                cj(this).dialog("close"); 
                cj(this).dialog("destroy");

                // Temporary workaround for problems with SSL connections being too
                // slow. The relationship doesn\'t get created because the page reload
                // happens before the ajax call.
                // In general this reload needs improvement, which is already on the list for phase 2.
                var sdate = (new Date()).getTime();
                var curDate = sdate;
                while(curDate-sdate < 2000) {
                    curDate = (new Date()).getTime();
                }
                
                //due to caching issues we use redirection rather than reload
                document.location = '; ?>
'<?php echo CRM_Utils_System::crmURL(array('q' => "action=view&reset=1&id=".($this->_tpl_vars['caseID'])."&cid=".($this->_tpl_vars['contactID'])."&context=".($this->_tpl_vars['context']),'h' => 0), $this);?>
'<?php echo ';
            },

            "Cancel": function() { 
                cj(this).dialog("close"); 
                cj(this).dialog("destroy"); 
            } 
        } 

    });
}
    
</script>
'; ?>


<?php endif; ?> 
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/activityView.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div class="crm-accordion-wrapper crm-case_activities-accordion crm-accordion-open crm-case-activities-block">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Case Activities<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
 </div><!-- /.crm-accordion-header -->
 <div id="activities" class="crm-accordion-body">
    <span id='fileOnCaseStatusMsg' style="display:none;"></span><!-- Displays status from copy to case -->
<div id="view-activity">
     <div id="activity-content"></div>
</div>


  <div>
<div class="crm-accordion-wrapper crm-accordion-inner crm-search_filters-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Search Filters<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">

  <table class="no-border form-layout-compressed" id="searchOptions">
    <tr>
        <td class="crm-case-caseview-form-block-repoter_id"colspan="2"><label for="reporter_id"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Reporter/Role<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label><br />
            <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['reporter_id']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'twenty') : smarty_modifier_crmReplace($_tmp, 'class', 'twenty')); ?>

        </td>
        <td class="crm-case-caseview-form-block-status_id"><label for="status_id"><?php echo $this->_tpl_vars['form']['status_id']['label']; ?>
</label><br />
            <?php echo $this->_tpl_vars['form']['status_id']['html']; ?>

        </td>
	<td style="vertical-align: bottom;">
		<span class="crm-button"><input class="form-submit default" name="_qf_Basic_refresh" value="Search" type="button" onclick="search()"; /></span>
	</td>
    </tr>
    <tr>
        <td class="crm-case-caseview-form-block-activity_date_low">
	    <?php echo $this->_tpl_vars['form']['activity_date_low']['label']; ?>
<br />
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'activity_date_low')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </td>
        <td class="crm-case-caseview-form-block-activity_date_high"> 
            <?php echo $this->_tpl_vars['form']['activity_date_high']['label']; ?>
<br /> 
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'activity_date_high')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </td>
        <td class="crm-case-caseview-form-block-activity_type_filter_id">
            <?php echo $this->_tpl_vars['form']['activity_type_filter_id']['label']; ?>
<br />
            <?php echo $this->_tpl_vars['form']['activity_type_filter_id']['html']; ?>

        </td>
    </tr>
    <?php if ($this->_tpl_vars['form']['activity_deleted']): ?>    
    	<tr class="crm-case-caseview-form-block-activity_deleted">
	     <td>
		 <?php echo $this->_tpl_vars['form']['activity_deleted']['html']; ?>
<?php echo $this->_tpl_vars['form']['activity_deleted']['label']; ?>

	     </td>
	</tr>
	<?php endif; ?>
  </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

  <table id="activities-selector"  class="nestedActivitySelector" style="display:none"></table>

 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->



<?php echo '
<script type="text/javascript">
cj(document).ready(function(){

    var dataUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/activity','h' => 0,'q' => 'snippet=4&caseID='), $this);?>
<?php echo $this->_tpl_vars['caseID']; ?>
"<?php echo ';
    dataUrl = dataUrl + \'&cid='; ?>
<?php echo $this->_tpl_vars['contactID']; ?>
<?php echo '\';
    dataUrl = dataUrl + \'&userID='; ?>
<?php echo $this->_tpl_vars['userID']; ?>
<?php echo '\'    

    '; ?>
<?php if ($this->_tpl_vars['fulltext']): ?><?php echo '
    	dataUrl = dataUrl + \'&context='; ?>
<?php echo $this->_tpl_vars['fulltext']; ?>
<?php echo '\';
    '; ?>
<?php endif; ?><?php echo '

    cj("#activities-selector").flexigrid
    (
        {
            url: dataUrl,
            dataType: \'json\',
            resizable: false,
            colModel : [

            {display: \'Date\',               name : \'display_date\',  width : 100,  sortable : true, align: \'left\'},
            {display: \'Subject\',            name : \'subject\',       width : 105,  sortable : true, align: \'left\'},
            {display: \'Type\',               name : \'type\',          width : 100,  sortable : true, align: \'left\'},
            {display: \'With\',               name : \'with_contacts\', width : 100,  sortable : false,align: \'left\'},
            {display: \'Reporter / Assignee\',name : \'reporter\',      width : 100,  sortable : true, align: \'left\'},
            {display: \'Status\',             name : \'status\',        width : 65,   sortable : true, align: \'left\'},
            {display: \'\',                   name : \'links\',         width : 110,  align: \'left\'},
            {name : \'class\', hide: true, width: 1}  // this col is use for applying css classes
            ],
            usepager: true,
            useRp: true,
            rp: 40,
            width:810,
            showToggleBtn: false,
            height: \'auto\',
            nowrap: false,
            onSuccess:setSelectorClass
        }
    );	
}
);

function search(com)
{   
    var activity_date_low  = cj("#activity_date_low").val();
    var activity_date_high = cj("#activity_date_high").val();

    var activity_deleted = 0;
    if ( cj("#activity_deleted:checked").val() == 1 ) {
        activity_deleted = 1;
    }
    cj(\'#activities-selector\').flexOptions({
	    newp:1, 
		params:[{name:\'reporter_id\', value: cj("select#reporter_id").val()},
			{name:\'status_id\', value: cj("select#status_id").val()},
			{name:\'activity_type_id\', value: cj("select#activity_type_filter_id").val()},
			{name:\'activity_date_low\', value: activity_date_low},
			{name:\'activity_date_high\', value: activity_date_high},
			{name:\'activity_deleted\', value: activity_deleted }
			]
		});
    
    cj("#activities-selector").flexReload(); 
}

function checkSelection( field ) {
    var validationMessage = \'\';
    var validationField   = \'\';
    var successAction     = \'\';
    var forceValidation   = false;
   
    var clientName = new Array( );
    clientName = selectedContact.split(\'::\');
    var fName = field.name;

    switch ( fName )  {
        case \'_qf_CaseView_next\' :
            validationMessage = \''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Please select an activity set from the list.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\';
            validationField   = \'timeline_id\';
            successAction     = "confirm(\''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to add a set of scheduled activities to this case?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\');";
            break;

        case \'new_activity\' :
            validationMessage = \''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Please select an activity type from the list.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\';
            validationField   = \'activity_type_id\';
            if ( document.getElementById(\'activity_type_id\').value == 3 ) {
                successAction     = "window.location=\''; ?>
<?php echo $this->_tpl_vars['newActivityEmailUrl']; ?>
<?php echo '\' + document.getElementById(\'activity_type_id\').value";            
            } else {
                successAction     = "window.location=\''; ?>
<?php echo $this->_tpl_vars['newActivityUrl']; ?>
<?php echo '\' + document.getElementById(\'activity_type_id\').value";                
            }
            break;

        case \'case_report\' :
            validationMessage = \''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Please select a report from the list.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\';
            validationField   = \'report_id\';
            successAction     = "window.location=\''; ?>
<?php echo $this->_tpl_vars['reportUrl']; ?>
<?php echo '\' + document.getElementById(\'report_id\').value";
            break;
 
        case \'_qf_CaseView_next_merge_case\' :
            validationMessage = \''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Please select a case from the list to merge with.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\';
            validationField   = \'merge_case_id\';
            break;
        case \'_qf_CaseView_next_edit_client\' :
            validationMessage = \''; ?>
<?php $this->_tag_stack[] = array('ts', array('escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Please select a client for this case.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\';
	    if ( cj(\'#contact_id\').val( ) == \''; ?>
<?php echo $this->_tpl_vars['contactID']; ?>
<?php echo '\' ) {
	       	forceValidation = true;
                validationMessage = \''; ?>
<?php $this->_tag_stack[] = array('ts', array('1' => "'+clientName[0]+'",'escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>%1 is already assigned to this case. Please select some other client for this case.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\';
            }
            validationField   = \'change_client_id\';
	    successAction     = "confirm( \''; ?>
<?php $this->_tag_stack[] = array('ts', array('1' => "'+clientName[0]+'",'escape' => 'js')); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to reassign this case and all related activities and relationships to %1?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '\' )";
            break;   	    
    }	

    if ( forceValidation || ( document.getElementById( validationField ).value == \'\' ) ) {
        alert( validationMessage );
        return false;
    } else if ( successAction ) {
        return eval( successAction );
    }
}


function setSelectorClass( ) {
    cj("#activities-selector td:last-child").each( function( ) {
       cj(this).parent().addClass(cj(this).text() );
    });
}
function printCaseReport( ){
 
 	var dataUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/case/report/print'), $this);?>
"<?php echo ';
 	dataUrl     = dataUrl+ \'&all=1&cid='; ?>
<?php echo $this->_tpl_vars['contactID']; ?>
<?php echo '\' 
                      +\'&caseID='; ?>
<?php echo $this->_tpl_vars['caseID']; ?>
<?php echo '\';
        window.location = dataUrl;
}
	
</script>
'; ?>


<div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array('location' => 'bottom')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>

<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>



</div>

<?php endif; ?> </div>