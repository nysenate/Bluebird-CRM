<?php /* Smarty version 2.6.26, created on 2010-08-19 16:43:07
         compiled from CRM/Contact/Form/NewContact.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/NewContact.tpl', 30, false),array('function', 'crmURL', 'CRM/Contact/Form/NewContact.tpl', 70, false),)), $this); ?>
<?php if ($this->_tpl_vars['context'] != 'search'): ?>
    <tr id="contact-success" style="display:none;">
	<td></td>
	<td><span class="success-status"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>New contact has been created.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></span></td>
    </tr>
    <tr class="crm-new-contact-form-block-contact">
    	<td class="label"><?php echo $this->_tpl_vars['form']['contact']['label']; ?>
</td>
    	<td><?php echo $this->_tpl_vars['form']['contact']['html']; ?>

    	    <?php if ($this->_tpl_vars['form']['profiles']): ?>
    		&nbsp;&nbsp;<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>OR<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>&nbsp;&nbsp;<?php echo $this->_tpl_vars['form']['profiles']['html']; ?>
<div id="contact-dialog" style="display:none;"></div>
    	    <?php endif; ?>
    	</td>
    </tr>
<?php endif; ?>
<?php echo '
<script type="text/javascript">
  var allowMultiClient = Boolean('; ?>
<?php echo $this->_tpl_vars['multiClient']; ?>
<?php echo ');
  var newToken = \'\';
  var existingTokens = \'\';
  cj( function( ) {
      // add multiple client option if configured
      if ( allowMultiClient ) {
      	 addMultiClientOption( newToken );
      } else {
         addSingleClientOption( );
      }
  });

  function newContact( gid ) {
  
      if ( allowMultiClient ) { 
      	 existingTokens = \'\';
      	 var cid = cj(\'#contact\').val();
      	 var cids = new Array(); 
      	 cids = cid.split(\',\');
      	 var i = 0;
      	 cj(\'li.token-input-token-facebook\').each(function(){
		var displayName = cj(this).children(\'p\').text();
	 	existingTokens += \'{"name":"\'+displayName+\'","id":"\'+cids[i]+\'"},\';
	  	i++;
      	 });
      }

      var dataURL = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/profile/create','q' => 'reset=1&snippet=5&context=dialog','h' => 0), $this);?>
"<?php echo ';
      dataURL = dataURL + \'&gid=\' + gid;
      cj.ajax({
         url: dataURL,
         success: function( content ) {
             cj("#contact-dialog").show( ).html( content ).dialog({
         	    	title: "Create New Contact",
             		modal: true,
             		width: 680, 
             		overlay: { 
             			opacity: 0.5, 
             			background: "black" 
             		},

                 beforeclose: function(event, ui) {
                     cj(this).dialog("destroy");
		     cj("#contact-success").fadeOut(5000);
		     cj("#profiles").val(\'0\');
                 }
             });
         }
      });
  }
        
  function addMultiClientOption ( newToken ) {
  
      existingTokens = existingTokens + newToken;
      eval( \'existingTokens = [\' + existingTokens + \']\');
      eval( \'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } \');

      var hintText = "'; ?>
<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Type in a partial or complete name of an existing contact.<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '";
      var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/checkemail','q' => 'id=1&noemail=1','h' => 0), $this);?>
"<?php echo ';
      cj(\'#contact\').tokenInput( contactUrl, { prePopulate:existingTokens ,classes: tokenClass, hintText: hintText });
      cj(\'ul.token-input-list-facebook, div.token-input-dropdown-facebook\' ).css( \'width\', \'450px\');
      
  }
  
  function addSingleClientOption ( ) {
      var contactUrl = '; ?>
"<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest','q' => 'className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=newcontact','h' => 0), $this);?>
"<?php echo ';

      cj(\'#contact\').autocomplete( contactUrl, { 
          selectFirst : false, matchContains: true, minChars: 1
      }).result( function(event, data, formatted) { 
          cj("input[name=contact_select_id]").val(data[1]);
      }).focus( );

      cj("#contact").click( function( ) {
          cj("input[name=contact_select_id]").val(\'\');
      });
                                  
      cj("#contact").bind("keypress keyup", function(e) {
          if ( e.keyCode == 13 ) {
              return false;
          }
      });  
  }
</script>
'; ?>

