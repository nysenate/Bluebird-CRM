<?php /* Smarty version 2.6.26, created on 2010-08-25 13:20:09
         compiled from CRM/Activity/Form/Activity.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Activity/Form/Activity.tpl', 52, false),array('modifier', 'explode', 'CRM/Activity/Form/Activity.tpl', 53, false),array('modifier', 'escape', 'CRM/Activity/Form/Activity.tpl', 123, false),array('modifier', 'crmReplace', 'CRM/Activity/Form/Activity.tpl', 162, false),array('modifier', 'crmDate', 'CRM/Activity/Form/Activity.tpl', 172, false),array('modifier', 'crmStripAlternatives', 'CRM/Activity/Form/Activity.tpl', 193, false),array('block', 'ts', 'CRM/Activity/Form/Activity.tpl', 81, false),array('block', 'edit', 'CRM/Activity/Form/Activity.tpl', 147, false),)), $this); ?>
<?php if ($this->_tpl_vars['cdType']): ?>
   <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/CustomData.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
    <div class="crm-block crm-form-block crm-activity-form-block">
        <?php echo '
    <script type="text/javascript">
    var target_contact = assignee_contact = \'\';

    '; ?>

    <?php $_from = $this->_tpl_vars['target_contact']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['name']):
?>
         <?php echo ' target_contact += \'{"name":"\'+'; ?>
"<?php echo $this->_tpl_vars['name']; ?>
"<?php echo '+\'","id":"\'+'; ?>
"<?php echo $this->_tpl_vars['id']; ?>
"<?php echo '+\'"},\';'; ?>

    <?php endforeach; endif; unset($_from); ?>
    <?php echo ' eval( \'target_contact = [\' + target_contact + \']\'); '; ?>


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

    <?php $_from = ((is_array($_tmp=",")) ? $this->_run_mod_handler('explode', true, $_tmp, "target,assignee") : explode($_tmp, "target,assignee")); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
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
    var tokenDataUrl_target  = "'; ?>
<?php echo $this->_tpl_vars['tokenUrl']; ?>
&context=activity_target<?php echo '";
    var tokenDataUrl_assignee  = "'; ?>
<?php echo $this->_tpl_vars['tokenUrl']; ?>
&context=activity_assignee<?php echo '";
    var assigneeDataUrl  = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest?fnName=civicrm/contact/search&json=1&return[contact_id]&group=3&context=activity_assignee'), $this);?>
<?php echo '";
    
    var hintText = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Type in a partial or complete name of an existing contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
    cj( "#target_contact_id"  ).tokenInput( tokenDataUrl_target, { prePopulate: target_contact, classes: tokenClass, hintText: hintText });
    cj( "#assignee_contact_id").customTokenInput( assigneeDataUrl, { prePopulate: assignee_contact, classes: tokenClass, hintText: hintText });
    cj(\'#source_contact_id\').autocomplete( sourceDataUrl, { width : 180, selectFirst : false, hintText: hintText, matchContains: true, minChars: 2
                                }).result( function(event, data, formatted) { cj( "#source_contact_qid" ).val( data[1] );
                                }).bind( \'click\', function( ) { cj( "#source_contact_qid" ).val(\'\'); });
    });
    </script>
    '; ?>

    <?php if (! $this->_tpl_vars['action'] || ( $this->_tpl_vars['action'] == 1 ) || ( $this->_tpl_vars['action'] == 2 )): ?>
        <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
    <?php endif; ?>
      
        <?php if ($this->_tpl_vars['action'] == 8): ?>             <table class="form-layout">
             <tr>
                <td colspan="2">
                    <div class="status"><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['delName'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to delete '%1'?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
                </td>
             </tr>
               
        <?php elseif ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2 || $this->_tpl_vars['action'] == 4 || $this->_tpl_vars['context'] == 'search' || $this->_tpl_vars['context'] == 'smog'): ?>
            <?php if ($this->_tpl_vars['activityTypeDescription']): ?>  
                <div id="help"><?php echo $this->_tpl_vars['activityTypeDescription']; ?>
</div>
            <?php endif; ?>

            <table class="<?php if ($this->_tpl_vars['action'] == 4): ?>view-layout<?php else: ?>form-layout<?php endif; ?>">
             <?php if ($this->_tpl_vars['context'] == 'standalone' || $this->_tpl_vars['context'] == 'smog'): ?>
                <tr class="crm-activity-form-block-activity_type_id">
                   <td class="label"><?php echo $this->_tpl_vars['form']['activity_type_id']['label']; ?>
</td><td class="view-value"><?php echo $this->_tpl_vars['form']['activity_type_id']['html']; ?>
</td>
                </tr>
             <?php endif; ?>
             <tr class="crm-activity-form-block-source_contact_id">
                <td class="label"><?php echo $this->_tpl_vars['form']['source_contact_id']['label']; ?>
</td>
                <td class="view-value">
                    <?php if ($this->_tpl_vars['admin'] && $this->_tpl_vars['action'] != 4): ?><?php echo $this->_tpl_vars['form']['source_contact_id']['html']; ?>
 <?php else: ?> <?php echo $this->_tpl_vars['source_contact_value']; ?>
 <?php endif; ?>
                </td>
             </tr>

             <tr class="crm-activity-form-block-target_contact_id">
             <?php if ($this->_tpl_vars['single'] == false): ?>
                <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>With Contact(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                <td class="view-value" style="white-space: normal"><?php echo ((is_array($_tmp=$this->_tpl_vars['with'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</td>
             <?php elseif ($this->_tpl_vars['action'] != 4): ?>
                <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>With Contact<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                <td><?php echo $this->_tpl_vars['form']['target_contact_id']['html']; ?>
</td>
		     <?php else: ?>
                <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>With Contact<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                <td class="view-value" style="white-space: normal">
        			<?php $_from = $this->_tpl_vars['target_contact']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['name']):
?>
        			  <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['id'])), $this);?>
"><?php echo $this->_tpl_vars['name']; ?>
</a>;&nbsp;
        			<?php endforeach; endif; unset($_from); ?>
		        </td>
             <?php endif; ?>
             </tr>
             
             <tr class="crm-activity-form-block-assignee_contact_id">
             <?php if ($this->_tpl_vars['action'] == 4): ?>
                <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Assigned To <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td class="view-value">
			    <?php $_from = $this->_tpl_vars['assignee_contact']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['name']):
?>
			        <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['id'])), $this);?>
"><?php echo $this->_tpl_vars['name']; ?>
</a>;&nbsp;
			    <?php endforeach; endif; unset($_from); ?>
                </td>
             <?php else: ?>
                <td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Assigned To <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                <td><?php echo $this->_tpl_vars['form']['assignee_contact_id']['html']; ?>

                   <?php $this->_tag_stack[] = array('edit', array()); $_block_repeat=true;smarty_block_edit($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>You can optionally assign this activity to someone. Assigned activities will appear in their Activities listing at CiviCRM Home.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                           <?php if ($this->_tpl_vars['config']->activityAssigneeNotification): ?>
                               <br /><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>A copy of this activity will be emailed to each Assignee.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                           <?php endif; ?>
                         </span>
                   <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_edit($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                </td>
             <?php endif; ?>
             </tr>

            <?php if ($this->_tpl_vars['activityTypeFile']): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/".($this->_tpl_vars['crmDir'])."/Form/Activity/".($this->_tpl_vars['activityTypeFile']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>

             <tr class="crm-activity-form-block-subject">
                <td class="label"><?php echo $this->_tpl_vars['form']['subject']['label']; ?>
</td><td class="view-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['subject']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
             </tr>
             <tr class="crm-activity-form-block-location">
                <td class="label"><?php echo $this->_tpl_vars['form']['location']['label']; ?>
</td><td class="view-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['location']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
</td>
             </tr> 
             <tr class="crm-activity-form-block-activity_date_time">
                <td class="label"><?php echo $this->_tpl_vars['form']['activity_date_time']['label']; ?>
</td>
                <?php if ($this->_tpl_vars['action'] != 4): ?>
                    <td class="view-value"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'activity_date_time')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
                <?php else: ?>
                    <td class="view-value"><?php echo ((is_array($_tmp=$this->_tpl_vars['form']['activity_date_time']['html'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
</td>
                <?php endif; ?>
             </tr>
             <tr class="crm-activity-form-block-duration">
                <td class="label"><?php echo $this->_tpl_vars['form']['duration']['label']; ?>
</td>
                <td class="view-value">
                    <?php echo $this->_tpl_vars['form']['duration']['html']; ?>

                    <?php if ($this->_tpl_vars['action'] != 4): ?><span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Total time spent on this activity (in minutes).<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?>
                </td>
             </tr> 
             <tr class="crm-activity-form-block-status_id">
                <td class="label"><?php echo $this->_tpl_vars['form']['status_id']['label']; ?>
</td><td class="view-value"><?php echo $this->_tpl_vars['form']['status_id']['html']; ?>
</td>
             </tr> 
             <tr class="crm-activity-form-block-details">
               <td class="label"><?php echo $this->_tpl_vars['form']['details']['label']; ?>
</td>
        	        <?php if ($this->_tpl_vars['activityTypeName'] == 'Print PDF Letter'): ?>
            		  <td class="view-value report">
            	    	    <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['details']['value'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>

            		  </td>
            		<?php else: ?>
            	      <td class="view-value report">
            	    	    <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['form']['details']['html'])) ? $this->_run_mod_handler('crmStripAlternatives', true, $_tmp) : smarty_modifier_crmStripAlternatives($_tmp)))) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>

            		  </td>
            		<?php endif; ?>     
               </td>
             </tr> 
             <tr class="crm-activity-form-block-priority_id">
                <td class="label"><?php echo $this->_tpl_vars['form']['priority_id']['label']; ?>
</td><td class="view-value"><?php echo $this->_tpl_vars['form']['priority_id']['html']; ?>
</td>
             </tr>
             <?php if ($this->_tpl_vars['form']['tag']['html']): ?>
             <tr class="crm-activity-form-block-tag">
                <td class="label"><?php echo $this->_tpl_vars['form']['tag']['label']; ?>
</td>
                <td class="view-value"><div class="crm-select-container"><?php echo $this->_tpl_vars['form']['tag']['html']; ?>
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
                    </script>
                    '; ?>

                </td>
             </tr>
             <?php endif; ?>
             <tr class="crm-activity-form-block-tag_set"><td colspan="2"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/Tag.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td></tr>	     
             <tr class="crm-activity-form-block-custom_data">
                <td colspan="2">
	            <?php if ($this->_tpl_vars['action'] == 4): ?> 
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Page/CustomDataView.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php else: ?>
                    <div id="customData"></div>
                <?php endif; ?> 
                </td>
             </tr> 
             <tr class="crm-activity-form-block-attachment">
                <td colspan="2">
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Form/attachment.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                </td>
             </tr>

             <?php if ($this->_tpl_vars['action'] != 4): ?>                  <tr class="crm-activity-form-block-schedule_followup">
                    <td colspan="2">
                     	<div class="crm-accordion-wrapper crm-accordion_title-accordion crm-accordion-closed">
						 <div class="crm-accordion-header">
						  <div class="icon crm-accordion-pointer"></div>
							<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Schedule Follow-up<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>                    
						  </div><!-- /.crm-accordion-header -->
					 	<div class="crm-accordion-body">
                        <table class="form-layout-compressed">
                           <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Schedule Follow-up Activity<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td>
                               <td><?php echo $this->_tpl_vars['form']['followup_activity_type_id']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['interval']['label']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['interval']['html']; ?>
&nbsp;<?php echo $this->_tpl_vars['form']['interval_unit']['html']; ?>
                          </td>
                           </tr>
                           <tr>
                              <td class="label"><?php echo $this->_tpl_vars['form']['followup_activity_subject']['label']; ?>
</td>
                              <td><?php echo $this->_tpl_vars['form']['followup_activity_subject']['html']; ?>
</td>
                           </tr>
                        </table>
                       </div><!-- /.crm-accordion-body -->
					 </div><!-- /.crm-accordion-wrapper -->
					<?php echo ' 
					<script type="text/javascript">
					cj(function() {
					   cj().crmaccordions(); 
					});
					</script>
					'; ?>

					 
					 </td>
                 </tr>
             <?php endif; ?>
        <?php endif; ?>         <tr class="buttons">
            <td>
                <?php if ($this->_tpl_vars['action'] == 4 || $this->_tpl_vars['action'] == 8 || $this->_tpl_vars['action'] == 32768): ?>
	    	    <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
		        <?php endif; ?>
	        </td>
            <td>
            <?php if ($this->_tpl_vars['action'] == 4 && $this->_tpl_vars['activityTName'] != 'Inbound Email'): ?> 
	            <?php if (! $this->_tpl_vars['context']): ?>
	                <?php $this->assign('context', 'activity'); ?>
	            <?php endif; ?>
                <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/activity','q' => "reset=1&atype=".($this->_tpl_vars['atype'])."&action=update&reset=1&id=".($this->_tpl_vars['entityID'])."&cid=".($this->_tpl_vars['contactId'])."&context=".($this->_tpl_vars['context'])), $this);?>
" class="edit button" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                <span><div class="icon edit-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                </a>
                <a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view/activity','q' => "reset=1&atype=".($this->_tpl_vars['atype'])."&action=delete&reset=1&id=".($this->_tpl_vars['entityID'])."&cid=".($this->_tpl_vars['contactId'])."&context=".($this->_tpl_vars['context'])), $this);?>
" class="delete button" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
                <span><div class="icon delete-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
                </a>
	        <?php endif; ?>
	        </td>
        </tr> 
    </table>   

    <?php if (! $this->_tpl_vars['action'] || ( $this->_tpl_vars['action'] == 1 ) || ( $this->_tpl_vars['action'] == 2 )): ?>
        <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
    <?php endif; ?>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Case/Form/ActivityToCase.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

    <?php if ($this->_tpl_vars['action'] == 1 || $this->_tpl_vars['action'] == 2 || $this->_tpl_vars['context'] == 'search' || $this->_tpl_vars['context'] == 'smog'): ?>
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
    		<?php else: ?>
    		    <?php echo '
    		    if ( cj("#activity_type_id").val( ) ) {
    		        buildCustomData( \''; ?>
<?php echo $this->_tpl_vars['customDataType']; ?>
<?php echo '\', cj("#activity_type_id").val( ) );
    	        }
    	        '; ?>

    		<?php endif; ?>
    		<?php echo '
    	});

        </script>
        '; ?>

    <?php endif; ?>
    <?php if (! $this->_tpl_vars['form']['case_select']): ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formNavigate.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
    </div><?php endif; ?> 