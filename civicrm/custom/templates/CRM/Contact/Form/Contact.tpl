{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{* This form is for Contact Add/Edit interface *}

{*NYSS need to retrieve and assign custom record id as its unique to each record*}
{foreach from=$form item=field}
    {if $field.name|substring:0:6 eq 'custom'}
        {assign var=customfield value="_"|explode:$field.name}
        {if $customfield.2|substring:0:1 neq '-'}
                {assign var=customId value=$customfield.2}
        {/if}
    {/if}
{/foreach}

{if $addBlock}
{include file="CRM/Contact/Form/Edit/$blockName.tpl"}
{else}
<div class="crm-form-block crm-search-form-block">
{*NYSS - remove
{if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM') }
    <a href='{crmURL p="civicrm/admin/setting/preferences/display" q="reset=1"}' title="{ts}Click here to configure the panes.{/ts}"><span class="icon settings-icon"></span></a>
{/if}
*}
<span style="float:right;"><a href="#expand" id="expand">{ts}Expand all tabs{/ts}</a></span>
<div class="crm-submit-buttons">
  {*NYSS 6340 move save matching button (and rename)*}
  {if $isDuplicate}
    <span class="crm-button crm-button_qf_Contact_upload_duplicate">
      {$form._qf_Contact_upload_duplicate.html}
    </span>
    {include file="CRM/common/formButtons.tpl" location="top"}
    {literal}
    <script style="text/javascript">
      //cj('span.crm-button crm-button_qf_Contact_upload_duplicate').insertBefore(cj('span.crm-button_qf_Contact_upload_view'));
      cj('input#_qf_Contact_upload_duplicate').val('Save Contact Anyway');
      cj('span.crm-button-type-upload').hide();
    </script>
    {/literal}
  {else}
    {include file="CRM/common/formButtons.tpl" location="top"}
  {/if}
</div>
<div class="crm-accordion-wrapper crm-contactDetails-accordion crm-accordion-open">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{ts}Contact Details{/ts}
	
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body" id="contactDetails">
    <table>
        <tr>
        	<td>
        	{include file="CRM/Contact/Form/Edit/$contactType.tpl"}
        	<span class="crm-button crm-button_qf_Contact_refresh_dedupe">
        	    {$form._qf_Contact_refresh_dedupe.html}
        	</span>
			</td>
		</tr>
        <tr>
        	<td>
        		{foreach from = $editOptions item ="title" key="name"}
        		{if $name eq "Address"}
        	        {include file="CRM/Contact/Form/Edit/$name.tpl"}
        		{/if}
        		{/foreach}
        	</td>
        </tr>
       	
        <tr>
        	<td>
            	<div class="subHeader">Communication Details</div>
            </td>
        </tr>
		<tr>
			<td>
				<table class="crm-section contact_information-section form-layout-compressed">
        	    {foreach from=$blocks item="label" key="block"}
        	       {include file="CRM/Contact/Form/Edit/$block.tpl"}
        	    {/foreach}
				</table>
        	</td>
        </tr>
        
        {if $contactType eq "Individual"}
        <tr>
        	<td>
            	<div class="subHeader">Employment</div>
            </td>
        </tr>
        <tr>
			<td>
            	<table class="form-layout-compressed individual-contact-details">
        	    <tr>
                <td>
                	{$form.current_employer.label}&nbsp;&nbsp;<br />
                    {assign var=formtexttwenty value='form-text twenty'}
        	    	{$form.current_employer.html|crmReplace:class:$formtexttwenty}
        	    	<div id="employer_address" style="display:none;"></div>
				</td>
				<td>
        	    	{$form.job_title.label}<br />
        	    	{$form.job_title.html}
				</td>
                </tr>
                </table>
            </td>
        </tr>
        {/if}
    </table>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{*NYSS manually insert indiv custom fields so we can control layout/eliminate dups*}
{if $contactType eq "Individual"}
<div class="crm-accordion-wrapper crm-address-accordion crm-accordion-open">
	<div class="crm-accordion-header">
		<div id="custom1" class="icon crm-accordion-pointer"></div> 
			Additional Constituent Information
		</div><!-- /.crm-accordion-header -->
			
		<div id="customData1" class="crm-accordion-body">
        <table class="form-layout-compressed">
        <tr class="custom_field-row">
            <td class="html-adjust" width="20%">
            	{assign var='custom_18' value=$groupTree.1.fields.18.element_name}
        		{$form.$custom_18.label}<br />
				{$form.$custom_18.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$custom_18}', '{$form.formName}'); return false;" >{ts}clear{/ts}</a>)</span>
            </td>
            <td class="html-adjust" width="20%">
            	{assign var='custom_17' value=$groupTree.1.fields.17.element_name}
        		{$form.$custom_17.label}<br />
				{$form.$custom_17.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$custom_17}', '{$form.formName}'); return false;" >{ts}clear{/ts}</a>)</span>
            </td>
            <td class="html-adjust" width="60%">
            	{assign var='custom_19' value=$groupTree.1.fields.19.element_name}
        		{$form.$custom_19.label}<br />
				{$form.$custom_19.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('{$custom_19}', '{$form.formName}'); return false;" >{ts}clear{/ts}</a>)</span>
            </td>
        </tr>
        <tr class="custom_field-row">
        	<td class="html-adjust">
            	{assign var='custom_16' value=$groupTree.1.fields.16.element_name}
        		{$form.$custom_16.label}<br />
				{$form.$custom_16.html}
            </td>
            <td class="html-adjust">
            	{assign var='custom_21' value=$groupTree.1.fields.21.element_name}
        		{$form.$custom_21.label}<br />
				{$form.$custom_21.html}
            </td>
            <td class="html-adjust" rowspan="2">
            	{assign var='custom_20' value=$groupTree.1.fields.20.element_name}
        		{$form.$custom_20.label}<br />
				{$form.$custom_20.html}
            </td>
        </tr>
        <tr class="custom_field-row">
        	<td class="html-adjust">
            	{assign var='custom_23' value=$groupTree.1.fields.23.element_name}
        		{$form.$custom_23.label}<br />
				{$form.$custom_23.html}
            </td>
            <td class="html-adjust">
            	{assign var='custom_24' value=$groupTree.1.fields.24.element_name}
        		{$form.$custom_24.label}<br />
				{include file="CRM/common/jcalendar.tpl" elementName=$custom_24}
            </td>
        </tr>
        </table>
		</div>
</div>
{/if}

<div id='customData'></div>  
    {foreach from = $editOptions item = "title" key="name"}
        {if $name neq "Address" }
		{include file="CRM/Contact/Form/Edit/$name.tpl"}
	    {/if}
    {/foreach}
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

</div>
{literal}
<script type="text/javascript" >
var action = "{/literal}{$action}{literal}";
var removeCustomData = true;
showTab[0] = {"spanShow":"span#contact","divShow":"div#contactDetails"};
cj(function( ) {
    cj().crmaccordions( ); 
	cj(showTab).each( function(){ 
        if( this.spanShow ) {
            cj(this.spanShow).removeClass( ).addClass('crm-accordion-open');
            cj(this.divShow).show( );
        }
    });

	cj('.crm-accordion-body').each( function() {
		//remove tab which doesn't have any element
		if ( ! cj.trim( cj(this).text() ) ) { 
			ele     = cj(this);
			prevEle = cj(this).prev();
			cj( ele ).remove();
			cj( prevEle).remove();
		}
		//open tab if form rule throws error
		if ( cj(this).children( ).find('span.crm-error').text( ).length > 0 ) {
			cj(this).parent( ).removeClass( 'crm-accordion-closed' ).addClass('crm-accordion-open');
		}
	});

	highlightTabs( );
	
	//NYSS 1748 call validate plugin
    cj("#Contact").validate( );
});

cj('a#expand').click( function( ){
    if( cj(this).attr('href') == '#expand') {   
        var message     = {/literal}"{ts}Collapse all tabs{/ts}"{literal};
        cj(this).attr('href', '#collapse');
        cj('.crm-accordion-closed').removeClass('crm-accordion-closed').addClass('crm-accordion-open');
    } else {
        var message     = {/literal}"{ts}Expand all tabs{/ts}"{literal};
        cj('.crm-accordion-open').removeClass('crm-accordion-open').addClass('crm-accordion-closed');
        cj(this).attr('href', '#expand');
    }
    cj(this).html(message);
    return false;
});

function showHideSignature( blockId ) {
    cj('#Email_Signature_' + blockId ).toggle( );   
}

function highlightTabs( ) {
    if ( action == 2 ) {
	//highlight the tab having data inside.
	cj('.crm-accordion-body :input').each( function() { 
		var element = cj(this).closest(".crm-accordion-body").attr("id");
		if (element) {
		eval('var ' + element + ' = "";');
		switch( cj(this).attr('type') ) {
		case 'checkbox':
		case 'radio':
		  if( cj(this).is(':checked') ) {
		    eval( element + ' = true;'); 
		  }
		  break;
		  
		case 'text':
		case 'textarea':
		  if( cj(this).val() ) {
		    eval( element + ' = true;');
		  }
		  break;
		  
		case 'select-one':
		case 'select-multiple':
		  if( cj('select option:selected' ) && cj(this).val() ) {
		    eval( element + ' = true;');
		  }
		  break;		
		  
		case 'file':
		  if( cj(this).next().html() ) eval( element + ' = true;');
		  break;
  		}
		if( eval( element + ';') ) { 
		  cj(this).closest(".crm-accordion-wrapper").addClass('crm-accordion-hasContent');
		}
	     }
       });
    }
}

/*function removeDefaultCustomFields( ) {
     //execute only once
     if (removeCustomData) {
	 cj(".crm-accordion-wrapper").children().each( function() {
	    var eleId = cj(this).attr("id");
	    if ( eleId.substr(0,10) == "customData" ) { cj(this).parent("div").remove(); }
	 });
	 removeCustomData = false;
     }
}*/

//NYSS 3527 - set comm preferences
function processDeceased( ) {
  if ( cj("#is_deceased").is(':checked') ) {
    cj('#privacy_do_not_phone').attr('checked', 'checked').attr('onclick', 'return false');
    cj('#privacy_do_not_email').attr('checked', 'checked').attr('onclick', 'return false');
    cj('#privacy_do_not_mail').attr('checked', 'checked').attr('onclick', 'return false');
    cj('#privacy_do_not_sms').attr('checked', 'checked').attr('onclick', 'return false');
    cj('#privacy_do_not_trade').attr('checked', 'checked').attr('onclick', 'return false');
    cj('#is_opt_out').attr('checked', 'checked').attr('onclick', 'return false');

    cj('#preferred_communication_method_1').removeAttr('checked').attr('onclick', 'return false');
    cj('#preferred_communication_method_2').removeAttr('checked').attr('onclick', 'return false');
    cj('#preferred_communication_method_3').removeAttr('checked').attr('onclick', 'return false');
    cj('#preferred_communication_method_4').removeAttr('checked').attr('onclick', 'return false');
    cj('#preferred_communication_method_5').removeAttr('checked').attr('onclick', 'return false');
  }
  else {
    cj('#privacy_do_not_phone').removeAttr('onclick');
    cj('#privacy_do_not_email').removeAttr('onclick');
    cj('#privacy_do_not_mail').removeAttr('onclick');
    cj('#privacy_do_not_sms').removeAttr('onclick');
    cj('#privacy_do_not_trade').removeAttr('onclick');
    cj('#is_opt_out').removeAttr('onclick');

    cj('#preferred_communication_method_1').removeAttr('onclick');
    cj('#preferred_communication_method_2').removeAttr('onclick');
    cj('#preferred_communication_method_3').removeAttr('onclick');
    cj('#preferred_communication_method_4').removeAttr('onclick');
    cj('#preferred_communication_method_5').removeAttr('onclick');
  }
}
processDeceased();
 
</script>
{/literal}
{literal}
<script type="text/javascript">
cj('#current_employer').addClass('loading-on');
var dataUrl        = "{/literal}{$employerDataURL}{literal}";
var newContactText = "{/literal}({ts}new contact record{/ts}){literal}";
cj('#current_employer').autocomplete( dataUrl, { 
                                      width        : 250, 
                                      selectFirst  : false,
                                      matchCase    : true, 
                                      matchContains: true
    }).result( function(event, data, formatted) {
        var foundContact   = ( parseInt( data[1] ) ) ? cj( "#current_employer_id" ).val( data[1] ) : cj( "#current_employer_id" ).val('');
        if ( ! foundContact.val() ) {
            cj('div#employer_address').html(newContactText).show();    
        } else {
            cj('div#employer_address').html('').hide();    
        }
    }).bind('change blur', function() {
        if ( !cj( "#current_employer_id" ).val( ) ) {
            cj('div#employer_address').html(newContactText).show();    
        }
});

// remove current employer id when current employer removed.
cj("form").submit(function() {
  if ( !cj('#current_employer').val() ) cj( "#current_employer_id" ).val('');
});

//current employer default setting
var employerId = "{/literal}{$currentEmployer}{literal}";
if ( employerId ) {
    var dataUrl = "{/literal}{crmURL p='civicrm/ajax/rest' h=0 q="className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&org=1&id=" }{literal}" + employerId ;
    cj.ajax({ 
        url     : dataUrl,   
        async   : false,
        success : function(html){
            //fixme for showing address in div
            htmlText = html.split( '|' , 2);
            cj('input#current_employer').val(htmlText[0]);
            cj('input#current_employer_id').val(htmlText[1]);
        }
    }); 
}

cj("input#current_employer").click( function( ) {
    cj("input#current_employer_id").val('');
});
</script>
{/literal}

{* include common additional blocks tpl *}
{include file="CRM/common/additionalBlocks.tpl"}

{* include jscript to warn if unsaved form field changes *}
{include file="CRM/common/formNavigate.tpl"}

{/if}
