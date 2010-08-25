<?php /* Smarty version 2.6.26, created on 2010-07-12 10:46:26
         compiled from CRM/Custom/Page/CustomDataView.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'CRM/Custom/Page/CustomDataView.tpl', 31, false),array('function', 'crmURL', 'CRM/Custom/Page/CustomDataView.tpl', 35, false),array('function', 'crmKey', 'CRM/Custom/Page/CustomDataView.tpl', 116, false),array('block', 'ts', 'CRM/Custom/Page/CustomDataView.tpl', 35, false),)), $this); ?>
<?php $this->assign('showEdit', 1); ?>
<?php $_from = $this->_tpl_vars['viewCustomData']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['customGroupId'] => $this->_tpl_vars['customValues']):
?>
    <?php $_from = $this->_tpl_vars['customValues']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['cvID'] => $this->_tpl_vars['cd_edit']):
?>
	<table class="no-border">
	    <?php $this->assign('index', ((is_array($_tmp=$this->_tpl_vars['groupId'])) ? $this->_run_mod_handler('cat', true, $_tmp, "_".($this->_tpl_vars['cvID'])) : smarty_modifier_cat($_tmp, "_".($this->_tpl_vars['cvID'])))); ?>
	    <?php if ($this->_tpl_vars['editOwnCustomData'] || ( $this->_tpl_vars['showEdit'] && $this->_tpl_vars['editCustomData'] && $this->_tpl_vars['groupId'] )): ?>	
		<tr>
		    <td>
			<a href="<?php echo CRM_Utils_System::crmURL(array('p' => "civicrm/contact/view/cd/edit",'q' => "tableId=".($this->_tpl_vars['contactId'])."&cid=".($this->_tpl_vars['contactId'])."&groupId=".($this->_tpl_vars['groupId'])."&action=update&reset=1"), $this);?>
" class="button" style="margin-left: 6px;"><span><div class="icon edit-icon"></div><?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['cd_edit']['title'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit %1<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a><br/><br/>
		    </td>
		</tr>      
	    <?php endif; ?>
	    <?php $this->assign('showEdit', 0); ?>
	    <tr id="statusmessg_<?php echo $this->_tpl_vars['index']; ?>
" class="hiddenElement">
		<td><span class="success-status"></span></td>
	    </tr>	    
	    <tr>
		<td id="<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_<?php echo $this->_tpl_vars['index']; ?>
" class="section-shown form-item">
		    <div class="crm-accordion-wrapper crm-accordion_title-accordion <?php if ($this->_tpl_vars['cd_edit']['collapse_display'] == 0): ?>crm-accordion-open<?php else: ?>crm-accordion-closed<?php endif; ?>">
             <div class="crm-accordion-header">
              <div class="icon crm-accordion-pointer"></div> 
		      <?php echo $this->_tpl_vars['cd_edit']['title']; ?>

             </div>
            <div class="crm-accordion-body">			   
	        <?php if ($this->_tpl_vars['groupId'] && $this->_tpl_vars['cvID'] && $this->_tpl_vars['editCustomData']): ?>
	        <div class="crm-submit-buttons">
			<a href="javascript:showDelete( <?php echo $this->_tpl_vars['cvID']; ?>
, '<?php echo $this->_tpl_vars['cd_edit']['name']; ?>
_<?php echo $this->_tpl_vars['index']; ?>
', <?php echo $this->_tpl_vars['customGroupId']; ?>
, <?php echo $this->_tpl_vars['contactId']; ?>
 );" class="button delete-button" title="<?php $this->_tag_stack[] = array('ts', array('1' => $this->_tpl_vars['cd_edit']['title'])); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete this %1 record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>">
			 <span><div class="icon delete-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
            </a>
            </div>
            <?php endif; ?>
			<?php $_from = $this->_tpl_vars['cd_edit']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field_id'] => $this->_tpl_vars['element']):
?>
			    <table class="crm-info-panel">
				<tr>
				    <?php if ($this->_tpl_vars['element']['options_per_line'] != 0): ?>
					<td class="label"><?php echo $this->_tpl_vars['element']['field_title']; ?>
</td>
					<td class="html-adjust">
					    					    <?php $_from = $this->_tpl_vars['element']['field_value']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['val']):
?>
						<?php echo $this->_tpl_vars['val']; ?>
<br/>
					    <?php endforeach; endif; unset($_from); ?>
					</td>
				    <?php else: ?>
					<td class="label"><?php echo $this->_tpl_vars['element']['field_title']; ?>
</td>
					<?php if ($this->_tpl_vars['element']['field_type'] == 'File'): ?>
					    <?php if ($this->_tpl_vars['element']['field_value']['displayURL']): ?>
						<td class="html-adjust"><a href="javascript:imagePopUp('<?php echo $this->_tpl_vars['element']['field_value']['displayURL']; ?>
')" ><img src="<?php echo $this->_tpl_vars['element']['field_value']['displayURL']; ?>
" height = "100" width="100"></a></td>
					    <?php else: ?>
						<td class="html-adjust"><a href="<?php echo $this->_tpl_vars['element']['field_value']['fileURL']; ?>
"><?php echo $this->_tpl_vars['element']['field_value']['fileName']; ?>
</a></td>
					    <?php endif; ?>
					<?php else: ?>
					    <td class="html-adjust"><?php echo $this->_tpl_vars['element']['field_value']; ?>
</td>
					<?php endif; ?>
				    <?php endif; ?>
				</tr>
			    </table>
			<?php endforeach; endif; unset($_from); ?>
			</div>
			<div class="clear"></div>
		    </div>
		  </div>
		</td>
	    </tr>
	</table>

    <?php endforeach; endif; unset($_from); ?>
<?php endforeach; endif; unset($_from); ?>
    <?php echo '
	<script type="text/javascript">
	cj(function() {
        cj().crmaccordions(); 
        });
	</script>
    '; ?>

<?php if ($this->_tpl_vars['groupId']): ?>
<script type="text/javascript">
    <?php echo '
    function hideStatus( valueID, groupID ) {
        cj( \'#statusmessg_\'  + groupID + \'_\' + valueID ).hide( );
    }
    function showDelete( valueID, elementID, groupID, contactID ) {
        var confirmMsg = \''; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Are you sure you want to delete this record?<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo ' &nbsp; <a href="javascript:deleteCustomValue( \' + valueID + \',\\\'\' + elementID + \'\\\',\' + groupID + \',\' + contactID + \' );" style="text-decoration: underline;">'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Yes<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a>&nbsp;&nbsp;&nbsp;<a href="javascript:hideStatus( \' + valueID + \', \' +  groupID + \' );" style="text-decoration: underline;">'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>No<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '</a>\';
        cj( \'tr#statusmessg_\' + groupID + \'_\' + valueID ).show( ).children().find(\'span\').html( confirmMsg );
    }
    function deleteCustomValue( valueID, elementID, groupID, contactID ) {
        var postUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/customvalue','h' => 0), $this);?>
"<?php echo ';
        cj.ajax({
          type: "POST",
          data:  "valueID=" + valueID + "&groupID=" + groupID +"&contactId=" + contactID + "&key='; ?>
<?php echo smarty_function_crmKey(array('name' => 'civicrm/ajax/customvalue'), $this);?>
<?php echo '",    
          url: postUrl,
          success: function(html){
              cj( \'#\' + elementID ).hide( );
              var resourceBase   = '; ?>
"<?php echo $this->_tpl_vars['config']->resourceBase; ?>
"<?php echo ';
              var successMsg = \''; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>The selected record has been deleted.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo ' &nbsp;&nbsp;<a href="javascript:hideStatus( \' + valueID + \',\' + groupID + \');"><img title="'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>close<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '" src="\' +resourceBase+\'i/close.png"/></a>\';
              cj( \'tr#statusmessg_\'  + groupID + \'_\' + valueID ).show( ).children().find(\'span\').html( successMsg );
			  var element = cj( \'.ui-tabs-nav #tab_custom_\' + groupID + \' a\' );
			  cj(element).html(cj(element).attr(\'title\') + \' (\'+ html+\') \');
          }
        });
    }
    '; ?>

</script>
<?php endif; ?>
