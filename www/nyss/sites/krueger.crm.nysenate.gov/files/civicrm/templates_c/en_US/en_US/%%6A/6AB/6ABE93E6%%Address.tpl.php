<?php /* Smarty version 2.6.26, created on 2010-08-17 10:26:18
         compiled from CRM/Contact/Form/Edit/Address.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Edit/Address.tpl', 52, false),array('function', 'help', 'CRM/Contact/Form/Edit/Address.tpl', 59, false),array('function', 'crmURL', 'CRM/Contact/Form/Edit/Address.tpl', 107, false),array('modifier', 'crmReplace', 'CRM/Contact/Form/Edit/Address.tpl', 62, false),)), $this); ?>

<?php if ($this->_tpl_vars['title'] && $this->_tpl_vars['className'] == 'CRM_Contact_Form_Contact'): ?>
<div class="crm-address-section">
<h3>
	<?php echo $this->_tpl_vars['title']; ?>

</h3>
<?php endif; ?>

<?php if ($this->_tpl_vars['blockId'] > 1): ?><div class="spacer"></div><?php endif; ?>

 <div id="Address_Block_<?php echo $this->_tpl_vars['blockId']; ?>
" <?php if ($this->_tpl_vars['className'] == 'CRM_Contact_Form_Contact'): ?> class="boxBlock" <?php endif; ?>>
  <table class="form-layout-compressed">
     <tr>
	 <?php if ($this->_tpl_vars['className'] == 'CRM_Contact_Form_Contact'): ?>
        <td id='Address-Primary-html' colspan="2">
           <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['location_type_id']['label']; ?>

           <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['location_type_id']['html']; ?>

           <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['is_primary']['html']; ?>

                   </td>
	 <?php endif; ?>
        <?php if ($this->_tpl_vars['blockId'] > 1): ?>
            <td>
                <a href="#" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Delete Address Block<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" onClick="removeBlock( 'Address', '<?php echo $this->_tpl_vars['blockId']; ?>
' ); return false;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>delete<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
            </td>
        <?php endif; ?>
     </tr>
     <?php if ($this->_tpl_vars['form']['use_household_address']): ?> 
     <tr>
        <td>
            <?php echo $this->_tpl_vars['form']['use_household_address']['html']; ?>
<?php echo $this->_tpl_vars['form']['use_household_address']['label']; ?>
<?php echo smarty_function_help(array('id' => "id-usehousehold"), $this);?>
<br />
            <div id="share_household" style="display:none">
                <?php echo $this->_tpl_vars['form']['shared_household']['label']; ?>
<br />
                <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['shared_household']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'huge') : smarty_modifier_crmReplace($_tmp, 'class', 'huge')); ?>
&nbsp;&nbsp;<span id="show_address"></span>
				<?php if ($this->_tpl_vars['mailToHouseholdID']): ?><div id="shared_address"><?php echo $this->_tpl_vars['sharedHouseholdAddress']; ?>
</div><?php endif; ?>
            </div>
        </td>
     </tr>
     <?php endif; ?>
     <tr><td>

     <table id="address_<?php echo $this->_tpl_vars['blockId']; ?>
" style="display:block" class="form-layout-compressed">
     		<?php $_from = $this->_tpl_vars['addressSequence']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['addressElement']):
?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/Address/".($this->_tpl_vars['addressElement']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php endforeach; endif; unset($_from); ?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/Address/geo_code.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
     </table>

     </td></tr>
  </table>

  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/Address/CustomData.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

  <?php if ($this->_tpl_vars['className'] == 'CRM_Contact_Form_Contact'): ?>
      <div id="addMoreAddress<?php echo $this->_tpl_vars['blockId']; ?>
" class="crm-add-address-wrapper">
          <a href="#" class="button" onclick="buildAdditionalBlocks( 'Address', '<?php echo $this->_tpl_vars['className']; ?>
' );return false;"><span><div class="icon add-icon"></div><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>add address<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></a>
      </div>
  <?php endif; ?>

<?php if ($this->_tpl_vars['title'] && $this->_tpl_vars['className'] == 'CRM_Contact_Form_Contact'): ?>
</div>
</div><!-- /.crm-accordion-wrapper -->
<?php endif; ?>
<?php echo '
<script type="text/javascript">
'; ?>

<?php if ($this->_tpl_vars['blockId'] == 1): ?>
<?php echo '
cj(document).ready( function() { 
    //shared household default setting
	if ( cj(\'#use_household_address\').is(\':checked\') ) {
    	cj(\'table#address_1\').hide(); 
        cj(\'#share_household\').show(); 
    }
'; ?>

<?php if ($this->_tpl_vars['mailToHouseholdID']): ?>
<?php echo '
		var dataUrl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/search','h' => 0,'q' => "hh=1&id=".($this->_tpl_vars['mailToHouseholdID'])), $this);?>
<?php echo '";
		cj.ajax({ 
            url     : dataUrl,   
            async   : false,
            success : function(html){ 
                        //fixme for showing address in div
                        htmlText = html.split( \'|\' , 2);
                        cj(\'input#shared_household\').val(htmlText[0]);
                    }
                });
'; ?>

<?php endif; ?>
<?php echo '
	//event handler for use_household_address check box
	cj(\'#use_household_address\').click( function() { 
		cj(\'#share_household\').toggle( );
        if( ! cj(\'#use_household_address\').is(\':checked\')) {
            cj(\'table#address_1\').show( );
        } else {
           cj(\'table#address_1\').toggle( );
        }
	});	
});

var dataUrl = "'; ?>
<?php echo $this->_tpl_vars['housholdDataURL']; ?>
<?php echo '";
var newContactText = "'; ?>
(<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>new contact record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)<?php echo '";
cj(\'#shared_household\').autocomplete( dataUrl, { width : 320, selectFirst : false, matchCase : true, matchContains: true
}).result( function(event, data, formatted) { 
    if( isNaN( data[1] ) ){
        cj( "span#show_address" ).html( newContactText ); 
        cj( "#shared_household_id" ).val( data[0] );
        cj( \'table#address_1\' ).toggle( ); 
    } else {
        var locationTypeId = \'address_\'+'; ?>
<?php echo $this->_tpl_vars['blockId']; ?>
<?php echo '+\'_location_type_id\';
        var isPrimary      = \'Address_\'+'; ?>
<?php echo $this->_tpl_vars['blockId']; ?>
<?php echo '+\'_IsPrimary\';
        var isBilling      = \'Address_\'+'; ?>
<?php echo $this->_tpl_vars['blockId']; ?>
<?php echo '+\'_IsBilling\';
        cj( \'table#address_1\' ).hide( ); 
        cj( "span#show_address" ).html( data[0] ); 
        cj( "#shared_household_id" ).val( data[1] );
        cj( "#"+locationTypeId ).val(data[2]); 
        if( data[3] == 1 ) {
            cj( "#"+isPrimary ).attr("checked","checked");
        } else {
            cj( "#"+isPrimary ).removeAttr("checked");
        }
        if( data[4] == 1 ) {
            cj( "#"+isBilling ).attr("checked","checked");
        } else {
            cj( "#"+isBilling ).removeAttr("checked");
        } 
    }
}).bind( \'change blur\', function( ) {
    if ( !parseInt( cj( "#shared_household_id" ).val( ) ) ) {
        cj( "span#show_address" ).html( newContactText );
    }
});
'; ?>

<?php endif; ?>	
<?php echo '										  
//to check if same location type is already selected.
function checkLocation( object, noAlert ) {
    var selectedText = cj( \'#\' + object + \' :selected\').text();
	cj( \'td#Address-Primary-html select\' ).each( function() {
		element = cj(this).attr(\'id\');
		if ( cj(this).val() && element != object && selectedText == cj( \'#\' + element + \' :selected\').text() ) {
			if ( ! noAlert ) {
			    var alertText = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Location type<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> <?php echo '" + selectedText + "'; ?>
 <?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>has already been assigned to another address. Please select another location type for this address.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
			    alert( alertText );
			}
			cj( \'#\' + object ).val(\'\');
		}
	});
}
</script>
'; ?>

<?php echo '
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
'; ?>

