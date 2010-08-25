<?php /* Smarty version 2.6.26, created on 2010-08-25 15:48:31
         compiled from CRM/Contact/Form/Relationship.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Relationship.tpl', 31, false),array('function', 'crmURL', 'CRM/Contact/Form/Relationship.tpl', 37, false),array('function', 'cycle', 'CRM/Contact/Form/Relationship.tpl', 200, false),array('modifier', 'crmDate', 'CRM/Contact/Form/Relationship.tpl', 43, false),)), $this); ?>
<?php if ($this->_tpl_vars['cdType']): ?>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Form/CustomData.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
  <?php if ($this->_tpl_vars['action'] == 4): ?>       <h3><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>View Relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></h3>
        <div class="crm-block crm-content-block crm-relationship-view-block">
        <table class="crm-info-panel">
	    <?php $_from = $this->_tpl_vars['viewRelationship']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
            <tr>
                <td class="label"><?php echo $this->_tpl_vars['row']['relation']; ?>
</td> 
                <td><a href="<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => "reset=1&cid=".($this->_tpl_vars['row']['cid'])), $this);?>
"><?php echo $this->_tpl_vars['row']['name']; ?>
</a></td>
            </tr>
            <?php if ($this->_tpl_vars['isCurrentEmployer']): ?>
                <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Current Employee?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Yes<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td></tr>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['row']['start_date']): ?>
                <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Start Date:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['start_date'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
</td></tr>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['row']['end_date']): ?>
                <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>End Date:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['row']['end_date'])) ? $this->_run_mod_handler('crmDate', true, $_tmp) : smarty_modifier_crmDate($_tmp)); ?>
</td></tr>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['row']['description']): ?>
                <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Description:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo $this->_tpl_vars['row']['description']; ?>
</td></tr>
            <?php endif; ?>
	        <?php $_from = $this->_tpl_vars['viewNote']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['rec']):
?>
		    <?php if ($this->_tpl_vars['rec']): ?>
			    <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Note:<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php echo $this->_tpl_vars['rec']; ?>
</td></tr>	
	   	    <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
            <?php if ($this->_tpl_vars['row']['is_permission_a_b']): ?>
                <?php if ($this->_tpl_vars['row']['rtype'] == 'a_b' && $this->_tpl_vars['is_contact_id_a']): ?>
                     <tr><td class="label">&nbsp;</td><td><strong>'<?php echo $this->_tpl_vars['displayName']; ?>
'</strong> can view and update information for <strong>'<?php echo $this->_tpl_vars['row']['display_name']; ?>
'</strong></td></tr>
                <?php else: ?>
                     <tr><td class="label">&nbsp;</td><td><strong>'<?php echo $this->_tpl_vars['row']['display_name']; ?>
'</strong> can view and update information for <strong>'<?php echo $this->_tpl_vars['displayName']; ?>
'</strong></td></tr>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['row']['is_permission_b_a']): ?>
                 <?php if ($this->_tpl_vars['row']['rtype'] == 'a_b' && $this->_tpl_vars['is_contact_id_a']): ?>   
                     <tr><td class="label">&nbsp;</td><td><strong>'<?php echo $this->_tpl_vars['row']['display_name']; ?>
'</strong> can view and update information for <strong>'<?php echo $this->_tpl_vars['displayName']; ?>
'</strong></td></tr>
                 <?php else: ?>
                     <tr><td class="label">&nbsp;</td><td><strong>'<?php echo $this->_tpl_vars['displayName']; ?>
'</strong> can view and update information for <strong>'<?php echo $this->_tpl_vars['row']['display_name']; ?>
'</strong></td></tr>
                 <?php endif; ?>   
            <?php endif; ?>
           
            <tr><td class="label"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Status<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></td><td><?php if ($this->_tpl_vars['row']['is_active']): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Enabled<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php else: ?> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Disabled<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></td></tr>

            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Custom/Page/CustomDataView.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php endforeach; endif; unset($_from); ?>
        </table>
        <div class="crm-submit-buttons"><input type="button" name='cancel' value="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Done<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" onclick="location.href='<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/contact/view','q' => 'action=browse&selectedChild=rel'), $this);?>
';"/></div>
        </div>
  <?php endif; ?>

  <?php if ($this->_tpl_vars['action'] == 2 | $this->_tpl_vars['action'] == 1): ?>     <h3><?php if ($this->_tpl_vars['action'] == 1): ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>New Relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php else: ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php endif; ?></h3>
    <div class="crm-block crm-form-block crm-relationship-form-block">
            <?php if ($this->_tpl_vars['action'] == 1): ?>
                <div class="description">
                <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Select the relationship type. Then locate target contact(s) for this relationship by entering a complete or partial name and clicking 'Search'.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                </div>
            <?php endif; ?>
            <table class="form-layout-compressed">
             <tr class="crm-relationship-form-block-relationship_type_id">
               <td class="label"><?php echo $this->_tpl_vars['form']['relationship_type_id']['label']; ?>
</td><td><?php echo $this->_tpl_vars['form']['relationship_type_id']['html']; ?>
</td>
            <?php if ($this->_tpl_vars['action'] == 2): ?>                 <?php echo '
                <script type="text/javascript">
                    var relType = 0;
                    cj( function( ) {
                        var relationshipType = cj(\'#relationship_type_id\'); 
                        relationshipType.change( function( ) { 
                            changeCustomData( \'Relationship\' );
                            currentEmployer( ); 
                        });
                        setPermissionStatus( relationshipType.val( ) ); 
                    });
                </script>
                '; ?>
 
                <td><label><?php echo $this->_tpl_vars['sort_name_b']; ?>
</label></td></tr>
                <tr class="crm-relationship-form-block-is_current_employer">
                  <td class="label">
                     <div id="employee"><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Current Employee?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label></div>
                     <div id="employer"><label><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Current Employer?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label></div>
                  </td>
                  <td id="current_employer"><?php echo $this->_tpl_vars['form']['is_current_employer']['html']; ?>
</td>
                </tr>  
            <?php else: ?>              </tr>
             <tr class="crm-relationship-form-block-rel_contact">
               <td class="label"><?php echo $this->_tpl_vars['form']['rel_contact']['label']; ?>
</td>
                <?php echo '
                  <script type="text/javascript">
                    var relType = 0;
                    cj( function( ) {
                        createRelation( );
                        var relationshipType = cj(\'#relationship_type_id\'); 
                        relationshipType.change( function() { 
                            cj(\'#relationship-refresh-save\').hide();
                            cj(\'#rel_contact\').val(\'\');
                            cj("input[name=rel_contact_id]").val(\'\');
                            createRelation( );
                            changeCustomData( \'Relationship\' );
                            setPermissionStatus( cj(this).val( ) ); 
                        });
                        setPermissionStatus( relationshipType.val( ) ); 
                    });
                    
                    function createRelation(  ) {
                        var relType    = cj(\'#relationship_type_id\').val( );
                        var relContact = cj(\'#rel_contact\');
                        if ( relType ) {
                             relContact.unbind( \'click\' );
                             cj("input[name=rel_contact_id]").val(\'\');
                             var dataUrl = '; ?>
'<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/ajax/rest",'h' => 0,'q' => "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=relationship&rel="), $this);?>
'<?php echo ' + relType;
                             relContact.autocomplete( dataUrl, { width : 180, selectFirst : false, matchContains: true });
                             relContact.result(function( event, data ) {
                               	cj("input[name=rel_contact_id]").val(data[1]);
                                cj(\'#relationship-refresh-save\').show( );
                                buildRelationFields( relType );
                             });
                        } else { 
                            relContact.unautocomplete( );
                            cj("input[name=rel_contact_id]").val(\'\');
                            relContact.click( function() { alert( \''; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Please select a relationship type first.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo ' ...\' );});
                        }
                    }       
				  </script>
                '; ?>

                <td><?php echo $this->_tpl_vars['form']['rel_contact']['html']; ?>
</td>
              </tr>
              </table>
                <div class="crm-submit-buttons">
                    <span id="relationship-refresh" class="crm-button crm-button-type-refresh crm-button_qf_Relationship_refresh.html"><?php echo $this->_tpl_vars['form']['_qf_Relationship_refresh']['html']; ?>
</span>
                    <span id="relationship-refresh-save" class="crm-button crm-button-type-save crm-button_qf_Relationship_refresh_save" style="display:none"><?php echo $this->_tpl_vars['form']['_qf_Relationship_refresh_save']['html']; ?>
</span>
                    <span class="crm-button crm-button-type-cancel crm-button_qf_Relationship_cancel"><?php echo $this->_tpl_vars['form']['_qf_Relationship_cancel']['html']; ?>
</span>
                </div>
                <div class="clear"></div>

              <?php if ($this->_tpl_vars['searchDone']): ?>  
                <?php if ($this->_tpl_vars['searchCount'] || $this->_tpl_vars['callAjax']): ?>
                    <?php if ($this->_tpl_vars['searchRows'] || $this->_tpl_vars['callAjax']): ?>                         <fieldset id="searchResult"><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Mark Target Contact(s) for this Relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
                        <div class="description">
                            <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Mark the target contact(s) for this relationship if it appears below. Otherwise you may modify the search name above and click Search again.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                        </div>
                        <?php echo ''; ?><?php if ($this->_tpl_vars['callAjax']): ?><?php echo '<div id="count_selected"> </div><br />'; ?><?php echo $this->_tpl_vars['form']['store_contacts']['html']; ?><?php echo ''; ?><?php if ($this->_tpl_vars['isEmployeeOf'] || $this->_tpl_vars['isEmployerOf']): ?><?php echo ''; ?><?php echo $this->_tpl_vars['form']['store_employer']['html']; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jsortable.tpl", 'smarty_include_vars' => array('sourceUrl' => $this->_tpl_vars['sourceUrl'],'useAjax' => 1,'callBack' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php echo ''; ?><?php endif; ?><?php echo '<table id="rel-contacts" class="pagerDisplay"><thead><tr class="columnheader"><th id="nosort" class="contact_select">&nbsp;</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Name'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th>'; ?><?php if ($this->_tpl_vars['isEmployeeOf']): ?><?php echo '<th id="nosort" class="current_employer">'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Current Employer?'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th>'; ?><?php elseif ($this->_tpl_vars['isEmployerOf']): ?><?php echo '<th id="nosort" class="current_employer">'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Current Employee?'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th>'; ?><?php endif; ?><?php echo '<!--<th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Street Address'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th>--><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'City'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'State'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Email'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th><th>'; ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo 'Phone'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</th></tr></thead><tbody>'; ?><?php if (! $this->_tpl_vars['callAjax']): ?><?php echo ''; ?><?php $_from = $this->_tpl_vars['searchRows']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?><?php echo '<tr class="'; ?><?php echo smarty_function_cycle(array('values' => "odd-row,even-row"), $this);?><?php echo '"><td class="contact_select">'; ?><?php echo $this->_tpl_vars['form']['contact_check'][$this->_tpl_vars['row']['id']]['html']; ?><?php echo '</td><td>'; ?><?php echo $this->_tpl_vars['row']['type']; ?><?php echo ' '; ?><?php echo $this->_tpl_vars['row']['name']; ?><?php echo '</td>'; ?><?php if ($this->_tpl_vars['isEmployeeOf']): ?><?php echo '<td>'; ?><?php echo $this->_tpl_vars['form']['employee_of'][$this->_tpl_vars['row']['id']]['html']; ?><?php echo '</td>'; ?><?php elseif ($this->_tpl_vars['isEmployerOf']): ?><?php echo '<td>'; ?><?php echo $this->_tpl_vars['form']['employer_of'][$this->_tpl_vars['row']['id']]['html']; ?><?php echo '</td>'; ?><?php endif; ?><?php echo '<!--<td>'; ?><?php echo $this->_tpl_vars['row']['street_address']; ?><?php echo '</td>--><td>'; ?><?php echo $this->_tpl_vars['row']['city']; ?><?php echo '</td><td>'; ?><?php echo $this->_tpl_vars['row']['state']; ?><?php echo '</td><td>'; ?><?php echo $this->_tpl_vars['row']['email']; ?><?php echo '</td><td>'; ?><?php echo $this->_tpl_vars['row']['phone']; ?><?php echo '</td></tr>'; ?><?php endforeach; endif; unset($_from); ?><?php echo ''; ?><?php else: ?><?php echo '<tr><td colspan="5" class="dataTables_empty">Loading data from server</td></tr>'; ?><?php endif; ?><?php echo '</tbody></table>'; ?>

                        </fieldset>
                        <div class="spacer"></div>
                    <?php else: ?>                         <?php if ($this->_tpl_vars['duplicateRelationship']): ?>  
                            <?php ob_start(); ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Duplicate relationship.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('infoMessage', ob_get_contents());ob_end_clean(); ?>
                        <?php else: ?>   
                            <?php ob_start(); ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Too many matching results. Please narrow your search by entering a more complete target contact name.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('infoMessage', ob_get_contents());ob_end_clean(); ?>
                        <?php endif; ?>  
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/info.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                    <?php endif; ?>
                <?php else: ?>                         <?php ob_start(); ?><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>No matching results for<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <ul><li><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['form']['rel_contact']['value'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Name like: %1<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></li><li><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact Type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>: <?php echo $this->_tpl_vars['contact_type_display']; ?>
</li></ul><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Check your spelling, or try fewer letters for the target contact name.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('infoMessage', ob_get_contents());ob_end_clean(); ?>
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/info.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>                
                <?php endif; ?>               <?php else: ?>
              <?php endif; ?>         <?php endif; ?>         
        <fieldset id = 'saveElements'>
            <div>
                <?php if ($this->_tpl_vars['action'] == 1): ?>
                <div id='addCurrentEmployer'>
                   <table class="form-layout-compressed">  
                       <tr class="crm-relationship-form-block-add_current_employer">
                         <td class="label"><?php echo $this->_tpl_vars['form']['add_current_employer']['label']; ?>
</td>
                         <td><?php echo $this->_tpl_vars['form']['add_current_employer']['html']; ?>
</td>
                       </tr>
                   </table> 
                </div>
                <div id='addCurrentEmployee'>
                   <table class="form-layout-compressed">   
                       <tr class="crm-relationship-form-block-add_current_employee">
                         <td class="label"><?php echo $this->_tpl_vars['form']['add_current_employee']['label']; ?>
</td>
                         <td><?php echo $this->_tpl_vars['form']['add_current_employee']['html']; ?>
</td>
                       </tr>
                   </table>
                </div> 
                <?php endif; ?>
                <table class="form-layout-compressed">
                    <tr class="crm-relationship-form-block-start_date">
                        <td class="label"><?php echo $this->_tpl_vars['form']['start_date']['label']; ?>
</td>
                        <td><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'start_date')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td></tr>
                    <tr class="crm-relationship-form-block-end_date">
                        <td class="label"><?php echo $this->_tpl_vars['form']['end_date']['label']; ?>
</td>
                        <td><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/jcalendar.tpl", 'smarty_include_vars' => array('elementName' => 'end_date')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><br />
                        <span class="description"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>If this relationship has start and/or end dates, specify them here.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></td>
                    </tr>
                    <tr class="crm-relationship-form-block-description">
                        <td class="label"><?php echo $this->_tpl_vars['form']['description']['label']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['form']['description']['html']; ?>
</td>
                    </tr>
                    <tr class="crm-relationship-form-block-note">
                        <td class="label"><?php echo $this->_tpl_vars['form']['note']['label']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['form']['note']['html']; ?>
</td>
                    </tr>
                    <tr class="crm-relationship-form-block-is_permission_a_b">
                        <td class="label"></td><td><?php echo $this->_tpl_vars['form']['is_permission_a_b']['html']; ?>

                        <span id='permision_a_b-a_b' class="hiddenElement">
                            <?php if ($this->_tpl_vars['action'] == 1): ?>
                                <strong>'<?php echo $this->_tpl_vars['sort_name_a']; ?>
'</strong> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>can view and update information for selected contact(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                            <?php else: ?>
                                <strong>'<?php echo $this->_tpl_vars['sort_name_a']; ?>
'</strong> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>can view and update information for <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <strong>'<?php echo $this->_tpl_vars['sort_name_b']; ?>
'</strong>
                            <?php endif; ?>
                        </span>
                        <span id ='permision_a_b-b_a' class="hiddenElement">
                            <?php if ($this->_tpl_vars['action'] == 1): ?>
                                <strong><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Selected contact(s)</strong> can view and update information for <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <strong>'<?php echo $this->_tpl_vars['sort_name_a']; ?>
'</strong>
                            <?php else: ?>
                                <strong>'<?php echo $this->_tpl_vars['sort_name_b']; ?>
'</strong> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>can view and update information for <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <strong>'<?php echo $this->_tpl_vars['sort_name_a']; ?>
'</strong>
                            <?php endif; ?>
                        </span>
                        </td>
                    </tr>
                    <tr class="crm-relationship-form-block-is_permission_b_a">
                        <td class="label"></td><td><?php echo $this->_tpl_vars['form']['is_permission_b_a']['html']; ?>

                        <span id='permision_b_a-b_a' class="hiddenElement">
                            <?php if ($this->_tpl_vars['action'] == 1): ?>
                                <strong>'<?php echo $this->_tpl_vars['sort_name_a']; ?>
'</strong> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>can view and update information for selected contact(s)<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
                            <?php else: ?>
                                <strong>'<?php echo $this->_tpl_vars['sort_name_a']; ?>
'</strong> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>can view and update information for <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <strong>'<?php echo $this->_tpl_vars['sort_name_b']; ?>
'</strong>
                            <?php endif; ?>
                        </span>
                        <span id ='permision_b_a-a_b' class="hiddenElement">
                            <?php if ($this->_tpl_vars['action'] == 1): ?>
                                <strong><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Selected contact(s)</strong> can view and update information for <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <strong>'<?php echo $this->_tpl_vars['sort_name_a']; ?>
'</strong>
                            <?php else: ?>
                                <strong>'<?php echo $this->_tpl_vars['sort_name_b']; ?>
'</strong> <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>can view and update information for <?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <strong>'<?php echo $this->_tpl_vars['sort_name_a']; ?>
'</strong>
                            <?php endif; ?>
                        </span>
                        </td>
                    </tr>
                    <tr class="crm-relationship-form-block-is_active">
                        <td class="label"><?php echo $this->_tpl_vars['form']['is_active']['label']; ?>
</td>
                        <td><?php echo $this->_tpl_vars['form']['is_active']['html']; ?>
</td>
                    </tr>
                </table>
                <?php echo '
                    <script type="text/javascript">
                        function setPermissionStatus( relTypeDirection ) {
                            var direction = relTypeDirection.split( \'_\' );
                            cj(\'#permision_a_b-\' + direction[1] + \'_\' + direction[2] ).show( );
                            cj(\'#permision_a_b-\' + direction[2] + \'_\' + direction[1] ).hide( );
                            cj(\'#permision_b_a-\' + direction[1] + \'_\' + direction[2] ).show( );
                            cj(\'#permision_b_a-\' + direction[2] + \'_\' + direction[1] ).hide( );                            
                        }
                    </script>
                '; ?>

            </div>
        <div id="customData"></div>
        <div class="spacer"></div>
        <div class="crm-submit-buttons" id="saveButtons"> <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div> 
        <?php if ($this->_tpl_vars['action'] == 1): ?>
            <div class="crm-submit-buttons" id="saveDetails">
            <span class="crm-button crm-button-type-save crm-button_qf_Relationship_refresh_savedetails"><?php echo $this->_tpl_vars['form']['_qf_Relationship_refresh_savedetails']['html']; ?>
</span>
            <span class="crm-button crm-button-type-cancel crm-button_qf_Relationship_cancel"><?php echo $this->_tpl_vars['form']['_qf_Relationship_cancel']['html']; ?>
</span>
            </div>
        <?php endif; ?>
      </div>   <?php endif; ?>
 
  <?php if ($this->_tpl_vars['action'] == 8): ?>
     <fieldset><legend><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Relationship<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></legend>
        <div class="status">
            <?php ob_start(); ?><?php echo $this->_tpl_vars['currentRelationships'][$this->_tpl_vars['id']]['relation']; ?>
<?php echo $this->_tpl_vars['disableRelationships'][$this->_tpl_vars['id']]['relation']; ?>
 <?php echo $this->_tpl_vars['currentRelationships'][$this->_tpl_vars['id']]['name']; ?>
<?php echo $this->_tpl_vars['disableRelationships'][$this->_tpl_vars['id']]['name']; ?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('relationshipsString', ob_get_contents());ob_end_clean(); ?>
            <?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['relationshipsString'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to delete the Relationship '%1'?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        </div>
        <div class="crm-submit-buttons"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
    </fieldset>	
  <?php endif; ?>
<?php endif; ?> 
<?php if ($this->_tpl_vars['callAjax']): ?>
<?php echo '
<script type="text/javascript">
var contact_checked  = new Array();
var employer_checked = new Array();
var employer_holdelement = new Array();
var countSelected = useEmployer = isRadio = 0;

'; ?>
 <?php if ($this->_tpl_vars['isEmployeeOf'] || $this->_tpl_vars['isEmployerOf']): ?> <?php echo '
var storeElement  = \'store_employers\';
var employerClass = \'current_employer\';
useEmployer = 1;
'; ?>
 <?php endif; ?> <?php if ($this->_tpl_vars['isEmployeeOf']): ?> <?php echo '
isRadio = 1;
'; ?>
 <?php endif; ?> <?php echo '

cj( function( ) {
    // clear old data if any
    cj(\'#store_contacts\').val(\'\');
    if ( useEmployer ) {
        cj(\'#store_employers\').val(\'\');
    } 

    cj(\'.pagerDisplay tbody tr .contact_select input\').live(\'click\', function () {
        var valueSelected = cj(this).val();	  
        if ( cj(this).attr(\'checked\') == true ) {   
            contact_checked[valueSelected] =  valueSelected;
            countSelected++;
        } else if( contact_checked[valueSelected] ) {
            delete contact_checked[valueSelected];
            countSelected--;
            if ( useEmployer && employer_holdelement[valueSelected] ) {
                cj( employer_holdelement[valueSelected] ).attr(\'checked\',false);
                delete employer_checked[valueSelected];
                delete employer_holdelement[valueSelected];
            } 
        }
        cj(\'#count_selected\').html(countSelected +\' Contacts selected.\')  
    } );

    if ( useEmployer ) {
        cj(\'.pagerDisplay tbody tr .\'+ employerClass +\' input\').live(\'click\', function () {
            var valueSelected = cj(this).val();	
            if ( isRadio ) {
                employer_checked = new Array();
            }
            if ( cj(this).attr(\'checked\') == true ) {
                // add validation to match with selected contacts
                if( !contact_checked[valueSelected] ) {
                    alert(\'Current employer / Current employee should be among the selected contacts.\');
                    cj(this).attr(\'checked\',false); 
                } else {
                    employer_checked[valueSelected] = valueSelected;
                    employer_holdelement[valueSelected] = this;
                }

            } else if ( employer_checked[valueSelected] ) {
                delete employer_checked[valueSelected]; 
                delete employer_holdelement[valueSelected];
            }
        } );
    }

});

function checkSelected( ) {
    cj(\'.pagerDisplay tbody tr .contact_select input\').each( function( ) {
        if ( contact_checked[cj(this).val()] ) { 
            cj(this).attr(\'checked\',true);
        }
    });

    if ( useEmployer ) {
        // register new elements
        employer_holdelement = new Array();
        cj(\'.pagerDisplay tbody tr .\'+ employerClass +\' input\').each( function( ) {
            if ( employer_checked[cj(this).val()] ) { 
                cj(this).attr(\'checked\',true);
                employer_holdelement[cj(this).val()] = this;
            }
        });  
    }	  	  
}

function submitAjaxData() {
    cj(\'#store_contacts\').val( contact_checked.toString() );
    if ( useEmployer )  {
        cj(\'#store_employers\').val( employer_checked.toString() ); 
    }
    return true;	 
}

</script>
'; ?>

<?php endif; ?>

<?php if (( $this->_tpl_vars['action'] == 1 ) || ( $this->_tpl_vars['action'] == 2 )): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/customData.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php echo '
<script type="text/javascript">

'; ?>
 <?php if ($this->_tpl_vars['searchRows']): ?> <?php echo '
cj(".contact_select .form-checkbox").each( function( ) {
    if (this) { 
        cj(this).attr(\'checked\',true);
    } 
});
'; ?>
 <?php endif; ?> <?php echo '

'; ?>
 <?php if ($this->_tpl_vars['action'] == 1): ?><?php echo ' 
cj(\'#saveDetails\').hide( );
cj(\'#addCurrentEmployer\').hide( );
cj(\'#addCurrentEmployee\').hide( );

cj(\'#rel_contact\').focus( function() {
    cj("input[name=rel_contact_id]").val(\'\');
    cj(\'#relationship-refresh\').show( );
    cj(\'#relationship-refresh-save\').hide( );	      
});

'; ?>
<?php if ($this->_tpl_vars['searchRows'] || $this->_tpl_vars['callAjax']): ?><?php echo ' 
show(\'saveElements\');
'; ?>
<?php else: ?><?php echo '
hide(\'saveElements\');
'; ?>
<?php endif; ?><?php endif; ?><?php echo '	

cj( function( ) {
    var relType = cj(\'#relationship_type_id\').val( );
    if ( relType ) {
        var relTypeId = relType.split("_");
        if (relTypeId) {
            buildCustomData( \'Relationship\', relTypeId[0]);
        }
    } else {
        buildCustomData(\'Relationship\');
    }
});

function buildRelationFields( relType ) {
    '; ?>
 <?php if ($this->_tpl_vars['action'] == 1): ?> <?php echo ' 
    if ( relType ) {
        var relTypeId = relType.split("_");
        if ( relTypeId[0] == 4 ) {
            if ( relTypeId[1] == \'a\' ) {
                show(\'addCurrentEmployee\');
                hide(\'addCurrentEmployer\');
            } else {
                hide(\'addCurrentEmployee\');
                show(\'addCurrentEmployer\');
            }
        }
        hide(\'relationship-refresh\');
        show(\'relationship-refresh-save\');
        show(\'details-save\');
        show(\'saveElements\');
        show(\'saveDetails\');
        '; ?>
<?php if ($this->_tpl_vars['searchRows'] || $this->_tpl_vars['callAjax']): ?><?php echo '
        hide(\'searchResult\');
        '; ?>
<?php endif; ?><?php echo '
        hide(\'saveButtons\');
    } 
    '; ?>
<?php endif; ?><?php echo ' 	 
}

function changeCustomData( cType ) {
    '; ?>
<?php if ($this->_tpl_vars['action'] == 1): ?> <?php echo '
    cj(\'#customData\').html(\'\');
    show(\'relationship-refresh\');
    hide(\'saveElements\');
    hide(\'addCurrentEmployee\');
    hide(\'addCurrentEmployer\');
    hide(\'saveDetails\');
    '; ?>
<?php if ($this->_tpl_vars['searchRows'] || $this->_tpl_vars['callAjax']): ?><?php echo '
    hide(\'searchResult\');
    '; ?>
<?php endif; ?><?php echo '
    '; ?>
<?php endif; ?> <?php echo '

    var relType = cj(\'#relationship_type_id\').val( );
    if ( relType ) {
        var relTypeId = relType.split("_");
        if (relTypeId) {
            buildCustomData( cType, relTypeId[0]);
        }
    } else {
        buildCustomData( cType );
    }
}

</script>
'; ?>

<?php endif; ?>
<?php if ($this->_tpl_vars['action'] == 2): ?>
<?php echo '
<script type="text/javascript">
   currentEmployer( );
   function currentEmployer( ) 
   {
      var relType = document.getElementById(\'relationship_type_id\').value;
      if ( relType == \'4_a_b\' ) {
           show(\'current_employer\', \'block\');
           show(\'employee\', \'block\');
           hide(\'employer\', \'block\');
      } else if ( relType == \'4_b_a\' ) {
	   show(\'current_employer\', \'block\');
           show(\'employer\', \'block\');
           hide(\'employee\', \'block\');
      } else {
           hide(\'employer\', \'block\');
           hide(\'employee\', \'block\');
	   hide(\'current_employer\', \'block\');
      }
   }
</script>
'; ?>

<?php endif; ?>