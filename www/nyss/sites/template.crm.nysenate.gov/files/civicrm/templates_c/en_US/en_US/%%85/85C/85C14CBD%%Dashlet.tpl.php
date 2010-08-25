<?php /* Smarty version 2.6.26, created on 2010-08-25 13:12:22
         compiled from CRM/Contact/Page/Dashlet.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Page/Dashlet.tpl', 27, false),array('function', 'help', 'CRM/Contact/Page/Dashlet.tpl', 28, false),array('function', 'crmURL', 'CRM/Contact/Page/Dashlet.tpl', 98, false),array('function', 'crmKey', 'CRM/Contact/Page/Dashlet.tpl', 100, false),)), $this); ?>
    <div id="help" style="padding: 1em;">
        <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Available dashboard elements - dashlets - are displayed in the dark gray top bar. Drag and drop dashlets onto the left or right columns below to add them to your dashboard. Changes are automatically saved. Click 'Done' to return to the normal dashboard view.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
        <?php echo smarty_function_help(array('id' => "id-dash_configure",'file' => "CRM/Contact/Page/Dashboard.hlp"), $this);?>

    </div><br/>
    <div class="dashlets-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Available Dashlets<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
    <div id="available-dashlets" class="dash-column">
        <?php $_from = $this->_tpl_vars['availableDashlets']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dashID'] => $this->_tpl_vars['row']):
?>
    	<div class="portlet">
    		<div class="portlet-header" id="<?php echo $this->_tpl_vars['dashID']; ?>
"><?php echo $this->_tpl_vars['row']['label']; ?>
<?php if ($this->_tpl_vars['admin'] && ! $this->_tpl_vars['row']['is_reserved']): ?>&nbsp;<a class="close-icon delete-dashlet"></a><?php endif; ?></div>
    	</div>
        <?php endforeach; endif; unset($_from); ?>
    </div>
    <br/>
    <div class="clear"></div>
    <div id="dashlets-header-col-0" class="dashlets-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Left Column<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
    <div id="dashlets-header-col-1" class="dashlets-header"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Right Column<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></div>
    <div id="existing-dashlets-col-0" class="dash-column">
        <?php $_from = $this->_tpl_vars['contactDashlets']['0']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dashID'] => $this->_tpl_vars['row']):
?>
    	<div class="portlet">
    		<div class="portlet-header" id="<?php echo $this->_tpl_vars['dashID']; ?>
"><?php echo $this->_tpl_vars['row']['label']; ?>
<?php if ($this->_tpl_vars['admin'] && ! $this->_tpl_vars['row']['is_reserved']): ?>&nbsp;<a class="close-icon delete-dashlet"></a><?php endif; ?></div>
    	</div>
        <?php endforeach; endif; unset($_from); ?>
    </div>
    
    <div id="existing-dashlets-col-1" class="dash-column">
        <?php $_from = $this->_tpl_vars['contactDashlets']['1']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dashID'] => $this->_tpl_vars['row']):
?>
    	<div class="portlet">
    		<div class="portlet-header" id="<?php echo $this->_tpl_vars['dashID']; ?>
"><?php echo $this->_tpl_vars['row']['label']; ?>
<?php if ($this->_tpl_vars['admin'] && ! $this->_tpl_vars['row']['is_reserved']): ?>&nbsp;<a class="close-icon delete-dashlet"></a><?php endif; ?></div>
    	</div>
        <?php endforeach; endif; unset($_from); ?>
    </div>

    <div class="clear"></div>

<?php echo '
<script type="text/javascript">
	cj(function() {
	    var currentReSortEvent;
		cj(".dash-column").sortable({
			connectWith: \'.dash-column\',
			update: saveSorting
		});

		cj(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
			.find(".portlet-header")
				.addClass("ui-widget-header ui-corner-all")
				.end()
			.find(".portlet-content");

		cj(".dash-column").disableSelection();
		
		function saveSorting(e, ui) {
            // this is to prevent double post call
		    if (!currentReSortEvent || e.originalEvent != currentReSortEvent) {
                currentReSortEvent = e.originalEvent;
                
                // Build a list of params to post to the server.
                var params = {};

                // post each columns
                dashletColumns = Array();
            
                // build post params
                cj(\'div[id^=existing-dashlets-col-]\').each( function( i ) {
                    cj(this).find(\'.portlet-header\').each( function( j ) {
                        var elementID = this.id;
                        var idState = elementID.split(\'-\');
                        params[\'columns[\' + i + \'][\' + idState[0] + \']\'] = idState[1];
                    });
                }); 
            
                // post to server
                var postUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/dashboard','h' => 0), $this);?>
"<?php echo ';
                params[\'op\'] = \'save_columns\';
                params[\'key\'] = '; ?>
"<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/dashboard'), $this);?>
"<?php echo ';
                cj.post( postUrl, params, function(response, status) {
                    // TO DO show done / disable escape action
                });
            }
        }
        
        cj(\'.delete-dashlet\').click( function( ) {
            var message = '; ?>
"<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Do you want to remove this dashlet as an 'Available Dashlet', AND delete it from all users' dashboards?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"<?php echo ';
            if ( confirm( message) ) {
                var dashletID = cj(this).parent().attr(\'id\');
                var idState = dashletID.split(\'-\')
                
                // Build a list of params to post to the server.
                var params = {};
                
                params[\'dashlet_id\'] = idState[0];

                // delete dashlet
                var postUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/dashboard','h' => 0), $this);?>
"<?php echo ';
                params[\'op\'] = \'delete_dashlet\';
                params[\'key\'] = '; ?>
"<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/dashboard'), $this);?>
"<?php echo ';
                cj.post( postUrl, params, function(response, status) {
                    // delete dom object
                    cj(\'#\' + dashletID ).parent().remove();
                });
            }
        });
	});
</script>
'; ?>
