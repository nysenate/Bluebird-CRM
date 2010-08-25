<?php /* Smarty version 2.6.26, created on 2010-08-16 22:21:40
         compiled from CRM/Contact/Form/Contact.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'ts', 'CRM/Contact/Form/Contact.tpl', 31, false),array('modifier', 'crmReplace', 'CRM/Contact/Form/Contact.tpl', 80, false),array('function', 'crmURL', 'CRM/Contact/Form/Contact.tpl', 263, false),)), $this); ?>
<?php if ($this->_tpl_vars['addBlock']): ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/".($this->_tpl_vars['blockName']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php else: ?>
<div class="crm-form-block crm-search-form-block">
<span style="float:right;"><a href="#expand" id="expand"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Expand all tabs<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></a></span>
<div class="crm-submit-buttons">
   <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>
<div class="crm-accordion-wrapper crm-contactDetails-accordion crm-accordion-open">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Contact Details<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
	
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body" id="contactDetails">
    <div id="contactDetails">
    <table>
        <tr>
        <td>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/".($this->_tpl_vars['contactType']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <span class="crm-button crm-button_qf_Contact_refresh_dedupe">
            <?php echo $this->_tpl_vars['form']['_qf_Contact_refresh_dedupe']['html']; ?>

        </span>
		</td><td>
		<table class="form-layout-compressed">
            <?php $_from = $this->_tpl_vars['blocks']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['block'] => $this->_tpl_vars['label']):
?>
               <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/".($this->_tpl_vars['block']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endforeach; endif; unset($_from); ?>
		</table>
		<table class="form-layout-compressed">
            <tr class="last-row">
              <td><?php echo $this->_tpl_vars['form']['contact_source']['label']; ?>
<br />
                  <?php echo $this->_tpl_vars['form']['contact_source']['html']; ?>

              </td>
              <td><?php echo $this->_tpl_vars['form']['external_identifier']['label']; ?>
<br />
                  <?php echo $this->_tpl_vars['form']['external_identifier']['html']; ?>

              </td>
              <?php if ($this->_tpl_vars['contactId']): ?>
				<td><label for="internal_identifier"><?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Internal Id<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?></label><br /><?php echo $this->_tpl_vars['contactId']; ?>
</td>
			  <?php endif; ?>
            </tr>            
        </table>
        </td>
        </tr>
        <?php if ($this->_tpl_vars['contactType'] == 'Individual'): ?>
        <tr>
        <td>
        </td>
        <td>
        <table>
         <tr>
         <td colspan="2">
            <?php echo $this->_tpl_vars['form']['current_employer']['label']; ?>
&nbsp;&nbsp;<br />
            <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['current_employer']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'twenty') : smarty_modifier_crmReplace($_tmp, 'class', 'twenty')); ?>

            <div id="employer_address" style="display:none;"></div>
        </td>
        <td>
            <?php echo $this->_tpl_vars['form']['job_title']['label']; ?>
<br />
            <?php echo $this->_tpl_vars['form']['job_title']['html']; ?>

        </td>
        <td>
            <?php echo $this->_tpl_vars['form']['nick_name']['label']; ?>
<br />
            <?php echo ((is_array($_tmp=$this->_tpl_vars['form']['nick_name']['html'])) ? $this->_run_mod_handler('crmReplace', true, $_tmp, 'class', 'big') : smarty_modifier_crmReplace($_tmp, 'class', 'big')); ?>

        </td>
        </tr>
        </table>
        </td>
        </tr>
        <?php endif; ?>
    </table>
    
            
        <?php if ($this->_tpl_vars['isDuplicate']): ?>
            &nbsp;&nbsp;
            <span class="crm-button crm-button_qf_Contact_upload_duplicate">
                <?php echo $this->_tpl_vars['form']['_qf_Contact_upload_duplicate']['html']; ?>

            </span>
        <?php endif; ?>
        <div class="spacer"></div>
    
        
    <?php $_from = $this->_tpl_vars['editOptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['name'] => $this->_tpl_vars['title']):
?>
        <?php if ($this->_tpl_vars['name'] == 'Address'): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/".($this->_tpl_vars['name']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?>
        
        
   </div>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
<div id='customData'></div>  
    <?php $_from = $this->_tpl_vars['editOptions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['name'] => $this->_tpl_vars['title']):
?>
        <?php if ($this->_tpl_vars['name'] != 'Address'): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/Contact/Form/Edit/".($this->_tpl_vars['name']).".tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	    <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?>
<div class="crm-submit-buttons">
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formButtons.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>

</div>
<?php echo '
<script type="text/javascript" >
var action = "'; ?>
<?php echo $this->_tpl_vars['action']; ?>
<?php echo '";
var removeCustomData = true;
showTab[0] = {"spanShow":"span#contact","divShow":"div#contactDetails"};
cj(function( ) {
	cj(showTab).each( function(){ 
        if( this.spanShow ) {
            cj(this.spanShow).removeClass( ).addClass(\'crm-accordion-open\');
            cj(this.divShow).show( );
        }
    });

	cj(\'.crm-accordion-body\').each( function() {
		//remove tab which doesn\'t have any element
		if ( ! cj.trim( cj(this).text() ) ) { 
			ele     = cj(this);
			prevEle = cj(this).prev();
			cj( ele ).remove();
			cj( prevEle).remove();
		}
		//open tab if form rule throws error
		if ( cj(this).children().find(\'span.crm-error\').text() ) {
			cj(this).show().prev().children(\'span:first\').removeClass( \'crm-accordion-closed\' ).addClass(\'crm-accordion-open\');
		}
	});

	highlightTabs( );
});

cj(\'a#expand\').click( function( ){
    if( cj(this).attr(\'href\') == \'#expand\') {   
        var message     = '; ?>
"<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Collapse all tabs<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"<?php echo ';
        cj(this).attr(\'href\', \'#collapse\');
        cj(\'.crm-accordion-closed\').removeClass(\'crm-accordion-closed\').addClass(\'crm-accordion-open\');
    } else {
        var message     = '; ?>
"<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>Expand all tabs<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>"<?php echo ';
        cj(\'.crm-accordion-open\').removeClass(\'crm-accordion-open\').addClass(\'crm-accordion-closed\');
        cj(this).attr(\'href\', \'#expand\');
    }
    cj(this).html(message);
});


function showHideSignature( blockId ) {
    cj(\'#Email_Signature_\' + blockId ).toggle( );   
}

function highlightTabs( ) {
    if ( action == 2 ) {
	//highlight the tab having data inside.
	cj(\'.crm-accordion-body :input\').each( function() { 
		var element = cj(this).closest(".crm-accordion-body").attr("id");
		if (element) {
		eval(\'var \' + element + \' = "";\');
		switch( cj(this).attr(\'type\') ) {
		case \'checkbox\':
		case \'radio\':
		  if( cj(this).is(\':checked\') ) {
		    eval( element + \' = true;\'); 
		  }
		  break;
		  
		case \'text\':
		case \'textarea\':
		  if( cj(this).val() ) {
		    eval( element + \' = true;\');
		  }
		  break;
		  
		case \'select-one\':
		case \'select-multiple\':
		  if( cj(\'select option:selected\' ) && cj(this).val() ) {
		    eval( element + \' = true;\');
		  }
		  break;		
		  
		case \'file\':
		  if( cj(this).next().html() ) eval( element + \' = true;\');
		  break;
  		}
		if( eval( element + \';\') ) { 
		  cj(this).closest(".crm-accordion-wrapper").addClass(\'crm-accordion-hasContent\');
		}
	     }
       });
    }
}

function removeDefaultCustomFields( ) {
     //execute only once
     if (removeCustomData) {
	 cj(".crm-accordion-wrapper").children().each( function() {
	    var eleId = cj(this).attr("id");
	    if ( eleId.substr(0,10) == "customData" ) { cj(this).parent("div").remove(); }
	 });
	 removeCustomData = false;
     }
}
 
</script>
'; ?>

<?php echo '
<script type="text/javascript">
cj(\'#current_employer\').addClass(\'loading-on\');
var dataUrl        = "'; ?>
<?php echo $this->_tpl_vars['employerDataURL']; ?>
<?php echo '";
var newContactText = "'; ?>
(<?php $this->_tag_stack[] = array('ts', array()); $_block_repeat=true;smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>new contact record<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_ts($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>)<?php echo '";
cj(\'#current_employer\').autocomplete( dataUrl, { 
                                      width        : 250, 
                                      selectFirst  : false,
                                      matchCase    : true, 
                                      matchContains: true
    }).result( function(event, data, formatted) {
        var foundContact   = ( parseInt( data[1] ) ) ? cj( "#current_employer_id" ).val( data[1] ) : cj( "#current_employer_id" ).val(\'\');
        if ( ! foundContact.val() ) {
            cj(\'div#employer_address\').html(newContactText).show();    
        } else {
            cj(\'div#employer_address\').html(\'\').hide();    
        }
    }).bind(\'change blur\', function() {
        if ( !cj( "#current_employer_id" ).val( ) ) {
            cj(\'div#employer_address\').html(newContactText).show();    
        }
});

// remove current employer id when current employer removed.
cj("form").submit(function() {
  if ( !cj(\'#current_employer\').val() ) cj( "#current_employer_id" ).val(\'\');
});

//current employer default setting
var employerId = "'; ?>
<?php echo $this->_tpl_vars['currentEmployer']; ?>
<?php echo '";
if ( employerId ) {
    var dataUrl = "'; ?>
<?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/ajax/rest','h' => 0,'q' => "className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&org=1&id="), $this);?>
<?php echo '" + employerId ;
    cj.ajax({ 
        url     : dataUrl,   
        async   : false,
        success : function(html){
            //fixme for showing address in div
            htmlText = html.split( \'|\' , 2);
            cj(\'input#current_employer\').val(htmlText[0]);
            cj(\'input#current_employer_id\').val(htmlText[1]);
        }
    }); 
}

cj("input#current_employer").click( function( ) {
    cj("input#current_employer_id").val(\'\');
});
</script>
'; ?>




<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/additionalBlocks.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "CRM/common/formNavigate.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php endif; ?>