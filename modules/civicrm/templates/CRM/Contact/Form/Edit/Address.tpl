{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
{* This file provides the plugin for the Address block *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller*}
{* @var $blockId Contains the current address block id, and assigned in the  CRM/Contact/Form/Location.php file *}

{if $title and $className eq 'CRM_Contact_Form_Contact'}
<div class="crm-accordion-wrapper crm-address-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{$title}
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body" id="addressBlock">
{/if}

 <div id="Address_Block_{$blockId}" {if $className eq 'CRM_Contact_Form_Contact'} class="boxBlock crm-edit-address-block" {/if}>
  {if $blockId gt 1}<fieldset><legend>Additional Address</legend>{/if}
  <table class="form-layout-compressed crm-edit-address-form">
     <tr>
	 {if $className eq 'CRM_Contact_Form_Contact'}
        <td id='Address-Primary-html' colspan="2">
           <span class="crm-address-element location_type_id-address-element">{$form.address.$blockId.location_type_id.label}
           {$form.address.$blockId.location_type_id.html}</span>
           <span class="crm-address-element is_primary-address-element">{$form.address.$blockId.is_primary.html}</span>
           <span class="crm-address-element is_billing-address-element">{$form.address.$blockId.is_billing.html}</span>
        </td>
	 {/if}
        {if $blockId gt 1}
            <td>
                <a href="#" title="{ts}Delete Address Block{/ts}" onClick="removeBlock( 'Address', '{$blockId}' ); return false;">{ts}Delete this address{/ts}</a>
            </td>
        {/if}
     </tr>
     {if $form.use_household_address} 
     <tr>
        <td>
            {$form.use_household_address.html}{$form.use_household_address.label}{help id="id-usehousehold" file="CRM/Contact/Form/Contact.hlp"}<br />
            <div id="share_household" style="display:none">
                {$form.shared_household.label}<br />
                {$form.shared_household.html|crmReplace:class:huge}&nbsp;&nbsp;<span id="show_address"></span>
				{if $mailToHouseholdID}<div id="shared_address">{$sharedHouseholdAddress}</div>{/if}
            </div>
        </td>
     </tr>
     {/if}
     <tr><td>

     <table id="address_{$blockId}" style="display:block" class="form-layout-compressed">
         {* build address block w/ address sequence. *}
         {foreach item=addressElement from=$addressSequence}
              {include file=CRM/Contact/Form/Edit/Address/$addressElement.tpl}
         {/foreach}
         {include file=CRM/Contact/Form/Edit/Address/geo_code.tpl}
     </table>

     </td></tr>
  </table>
  <div class="crm-edit-address-custom_data"> 
  {include file="CRM/Contact/Form/Edit/Address/CustomData.tpl"}
  </div> 

  {if $className eq 'CRM_Contact_Form_Contact'}
      <div id="addMoreAddress{$blockId}" class="crm-add-address-wrapper">
          <a href="#" class="button" onclick="buildAdditionalBlocks( 'Address', '{$className}' );return false;"><span><div class="icon add-icon"></div>{ts}Another Address{/ts}</span></a>
      </div>
  {/if}

{if $title and $className eq 'CRM_Contact_Form_Contact'}
</div>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
{/if}
{literal}
<script type="text/javascript">
{/literal}
{if $blockId eq 1}
{literal}
cj(document).ready( function() { 
    //shared household default setting
	if ( cj('#use_household_address').is(':checked') ) {
    	cj('table#address_1').hide(); 
        cj('#share_household').show(); 
    }
{/literal}
{if $mailToHouseholdID}
{literal}
		var dataUrl = "{/literal}{crmURL p='civicrm/ajax/search' h=0 q="hh=1&id=$mailToHouseholdID"}{literal}";
		cj.ajax({ 
            url     : dataUrl,   
            async   : false,
            success : function(html){ 
                        //fixme for showing address in div
                        htmlText = html.split( '|' , 2);
                        cj('input#shared_household').val(htmlText[0]);
                    }
                });
{/literal}
{/if}
{literal}
	//event handler for use_household_address check box
	cj('#use_household_address').click( function() { 
		cj('#share_household').toggle( );
        if( ! cj('#use_household_address').is(':checked')) {
            cj('table#address_1').show( );
        } else {
           cj('table#address_1').toggle( );
        }
	});	
});

var dataUrl = "{/literal}{$housholdDataURL}{literal}";
var newContactText = "{/literal}({ts}new contact record{/ts}){literal}";
cj('#shared_household').autocomplete( dataUrl, { width : 320, selectFirst : false, matchCase : true, matchContains: true
}).result( function(event, data, formatted) { 
    if( isNaN( data[1] ) ){
        cj( "span#show_address" ).html( newContactText ); 
        cj( "#shared_household_id" ).val( data[0] );
        cj( 'table#address_1' ).toggle( ); 
    } else {
        var locationTypeId = 'address_'+{/literal}{$blockId}{literal}+'_location_type_id';
        var isPrimary      = 'Address_'+{/literal}{$blockId}{literal}+'_IsPrimary';
        var isBilling      = 'Address_'+{/literal}{$blockId}{literal}+'_IsBilling';
        cj( 'table#address_1' ).hide( ); 
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
}).bind( 'change blur', function( ) {
    if ( !parseInt( cj( "#shared_household_id" ).val( ) ) ) {
        cj( "span#show_address" ).html( newContactText );
    }
});
{/literal}
{/if}	
{literal}										  
//to check if same location type is already selected.
function checkLocation( object, noAlert ) {
    var selectedText = cj( '#' + object + ' :selected').text();
	cj( 'td#Address-Primary-html select' ).each( function() {
		element = cj(this).attr('id');
		if ( cj(this).val() && element != object && selectedText == cj( '#' + element + ' :selected').text() ) {
			if ( ! noAlert ) {
			    var alertText = "{/literal}{ts escape='js'}Location type{/ts} {literal}" + selectedText + "{/literal} {ts escape='js'}has already been assigned to another address. Please select another location type for this address.{/ts}{literal}";
			    alert( alertText );
			}
			cj( '#' + object ).val('');
		}
	});
}
</script>
{/literal}
{literal}
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}

