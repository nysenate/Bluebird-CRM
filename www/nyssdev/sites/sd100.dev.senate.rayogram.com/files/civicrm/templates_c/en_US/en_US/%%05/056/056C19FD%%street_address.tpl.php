<?php /* Smarty version 2.6.26, created on 2010-05-24 17:42:28
         compiled from CRM/Contact/Form/Edit/Address/street_address.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Edit/Address/street_address.tpl', 32, false),array('function', 'help', 'CRM/Contact/Form/Edit/Address/street_address.tpl', 33, false),)), $this); ?>
<?php if ($this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_address']): ?>
    <tr id="streetAddress_<?php echo $this->_tpl_vars['blockId']; ?>
">
        <td colspan="2">
           <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_address']['label']; ?>
<br />
           <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_address']['html']; ?>

        <?php if ($this->_tpl_vars['parseStreetAddress'] == 1 && $this->_tpl_vars['action'] == 2): ?>
           &nbsp;&nbsp;<a href="#" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Address Elements<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" onClick="processAddressFields( 'addressElements' , '<?php echo $this->_tpl_vars['blockId']; ?>
', 1 );return false;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Address Elements<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
           <?php echo smarty_function_help(array('id' => "id-edit-street-elements",'file' => "CRM/Contact/Form/Contact.hlp"), $this);?>

        <?php endif; ?>
        <br />
        <span class="description font-italic"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Street number, street name, apartment/unit/suite - OR P.O. box<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span>
        </td>
    </tr>
        
    <?php if ($this->_tpl_vars['parseStreetAddress'] == 1 && $this->_tpl_vars['action'] == 2): ?>
           <tr id="addressElements_<?php echo $this->_tpl_vars['blockId']; ?>
" class=hiddenElement>
               <td>
                  <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_number']['label']; ?>
<br />
                  <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_number']['html']; ?>

                </td>
           
               <td>
                  <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_name']['label']; ?>
<br />
                  <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_name']['html']; ?>
<br />
               </td>
               
               <td colspan="2">
                  <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_unit']['label']; ?>
<br />       
                  <?php echo $this->_tpl_vars['form']['address'][$this->_tpl_vars['blockId']]['street_unit']['html']; ?>

                  <a href="#" title="<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Street Address<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>" onClick="processAddressFields( 'streetAddress', '<?php echo $this->_tpl_vars['blockId']; ?>
', 1 );return false;"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Edit Complete Street Address<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a>
                  <?php echo smarty_function_help(array('id' => "id-edit-complete-street",'file' => "CRM/Contact/Form/Contact.hlp"), $this);?>
 
               </td>
           </tr>
    <?php endif; ?>

<?php if ($this->_tpl_vars['parseStreetAddress'] == 1): ?>
<?php echo '
<script type="text/javascript">
function processAddressFields( name, blockId, loadData ) {

	if ( loadData ) { 
            var allAddressValues = '; ?>
<?php if ($this->_tpl_vars['allAddressFieldValues']): ?><?php echo $this->_tpl_vars['allAddressFieldValues']; ?>
<?php else: ?>''<?php endif; ?><?php echo ';

	    var streetName    = eval( "allAddressValues.street_name_"    + blockId );
	    var streetUnit    = eval( "allAddressValues.street_unit_"    + blockId );
	    var streetNumber  = eval( "allAddressValues.street_number_"  + blockId );
	    var streetAddress = eval( "allAddressValues.street_address_" + blockId );
	}

	var showBlockName = \'\';
	var hideBlockName = \'\';

        if ( name == \'addressElements\' ) {
             if ( loadData ) {
	          streetAddress = \'\';
	     }
	     
             showBlockName = \'addressElements_\' + blockId;		   
	     hideBlockName = \'streetAddress_\' + blockId;
	} else {
             if ( loadData ) {
                  streetNumber = streetName = streetUnit = \'\'; 
             }

             showBlockName = \'streetAddress_\' +  blockId;
             hideBlockName = \'addressElements_\'+ blockId;
       }

       show( showBlockName );
       hide( hideBlockName );

       // set the values.
       if ( loadData ) {
          cj( \'#address_\' + blockId +\'_street_name\'    ).val( streetName    );   
          cj( \'#address_\' + blockId +\'_street_unit\'    ).val( streetUnit    );
          cj( \'#address_\' + blockId +\'_street_number\'  ).val( streetNumber  );
          cj( \'#address_\' + blockId +\'_street_address\' ).val( streetAddress );
       }
}

</script>
'; ?>

<?php endif; ?>
<?php endif; ?>
