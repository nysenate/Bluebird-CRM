{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
<div id = "addressBlockId" class="crm-accordion-wrapper crm-address-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{$title}
 </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body" id="addressBlock">
{/if}

 <div id="Address_Block_{$blockId}" {if $className eq 'CRM_Contact_Form_Contact'} class="boxBlock crm-edit-address-block" {/if}>
  {if $blockId gt 1}<fieldset><legend>{ts}Additional Address{/ts}</legend>{/if}
  <table class="form-layout-compressed crm-edit-address-form">
     {if $masterAddress.$blockId gt 0 }
        <tr><td><div class="message status"><div class="icon inform-icon"></div>&nbsp; {ts 1=$masterAddress.$blockId}This address is shared with %1 contact record(s). Modifying this address will automatically update the shared address for these contacts.{/ts}</div></td></tr>
     {/if}
     <tr>
	 {if $className eq 'CRM_Contact_Form_Contact'}
        <td id='Address-Primary-html' colspan="2">
           <span class="crm-address-element location_type_id-address-element">{$form.address.$blockId.location_type_id.label}
           {$form.address.$blockId.location_type_id.html}</span>
           <span class="crm-address-element is_primary-address-element">{$form.address.$blockId.is_primary.html}</span>
           <span class="crm-address-element is_billing-address-element">{$form.address.$blockId.is_billing.html}</span>
        </td>
     {if $blockId gt 0}
         <td>
             <a href="#" title="{ts}Delete Address Block{/ts}" onClick="removeBlock( 'Address', '{$blockId}' ); return false;">{ts}Delete this address{/ts}</a>
         </td>
     {/if}
     </tr>
     <script type="text/javascript">
     {literal}
         function showHideSharedAddress( blockNo, showSelect ) {
             // based on checkbox, show or hide
             if ( cj( '#address\\[' + blockNo + '\\]\\[use_shared_address\\]' ).attr( 'checked') ) {
                 if ( showSelect && cj( '#shared-address-display-' + blockNo ).length == 0 ) {
                     cj( '#shared-address-' + blockNo ).show( );
                 }
                 
                 cj( 'table#address_' + blockNo ).hide( );
                 cj( '#shared-address-display-' + blockNo ).show( );
                 cj( '#shared-address-display-cancel-' + blockNo ).hide( );
             } else {
                 cj( '#shared-address-' + blockNo ).hide( );
                 cj( 'table#address_' + blockNo ).show( );
                 cj( '#shared-address-display-' + blockNo ).hide( );
                 cj( '#shared-address-display-cancel-' + blockNo ).hide( );
             }
         }
     {/literal}
     </script>
     
     <tr>
        <td>
            {$form.address.$blockId.use_shared_address.html}{$form.address.$blockId.use_shared_address.label}{help id="id-sharedAddress" file="CRM/Contact/Form/Contact.hlp"}<br />
            {if $sharedAddresses.$blockId.shared_address_display}
                <span class="shared-address-display" id="shared-address-display-name-{$blockId}">
                    {$sharedAddresses.$blockId.shared_address_display.name}
                </span>
                
                <span class="shared-address-display" id="shared-address-display-{$blockId}" onclick="cj(this).hide( );cj('#shared-address-display-name-{$blockId}').hide( );cj('#shared-address-display-cancel-{$blockId}').show( );cj('#shared-address-{$blockId}').show( );">
                    {$sharedAddresses.$blockId.shared_address_display.address} <a href='#' onclick='return false;'>( {ts}Change current shared address{/ts} )</a>
                </span>
                
                <span id="shared-address-display-cancel-{$blockId}" class="hiddenElement" onclick="cj(this).hide( );cj('#shared-address-display-name-{$blockId}').show( );cj('#shared-address-display-{$blockId}').show( );cj('#shared-address-{$blockId}').hide( );">
                    <a href='#' onclick='return false;'>( {ts}Cancel{/ts} )</a>
                </span>
            {/if}
            <table id="shared-address-{$blockId}" class="form-layout-compressed hiddenElement">
               {include file="CRM/Contact/Form/NewContact.tpl" blockNo="$blockId"}
            </table>
        </td>
     </tr>
     
     <script type="text/javascript">
     {literal}
     cj( function( ) {
         var blockNo = {/literal}{$blockId}{literal};
         
         // call this when form loads
         showHideSharedAddress( blockNo, true );
         
         // handle check / uncheck of checkbox
         cj( '#address\\[' + blockNo + '\\]\\[use_shared_address\\]' ).click( function( ) {
             showHideSharedAddress( blockNo, true );
         });
         
         // start of code to add onchange event for hidden element
         var contactHiddenElement = 'input[name=contact_select_id[' + blockNo +']]';
         
         // store initial value
         var _default  = cj( contactHiddenElement ).val();

         // observe changes
         cj( contactHiddenElement ).change(function( ) {
            var sharedContactId = cj( this ).val( );
            if ( !sharedContactId || isNaN( sharedContactId ) ) {
                return;
            }
            
            var addressHTML = '';
            cj( ).crmAPI( 'location', 'get', { 'contact_id': sharedContactId, 'version': '3.0' }, {
                  success: function( response ) {
                      if ( response.address ) {
                          var selected = 'checked';
                          var addressExists = false;
                          cj.each( response.address, function( i, val ) {
                              if ( i > 1 ) {
                                  selected = '';
                              } else {
                                  cj( 'input[name="address[' + blockNo + '][master_id]"]' ).val( val.id );
                              }
                              addressHTML = addressHTML + '<input type="radio" name="selected_shared_address-'+ blockNo +'" value=' + val.id + ' ' + selected +'>' + val.display + '<br/>'; 
                              addressExists = true; 
                          });

                          if ( addressExists  ) {
                              cj( '#shared-address-' + blockNo + ' .shared-address-list' ).remove( );
                              cj( '#shared-address-' + blockNo ).append( '<tr class="shared-address-list"><td></td><td>' + addressHTML + '</td></tr>');
                              cj( 'input[name^=selected_shared_address-]' ).click( function( ) {
                                  // get the block id
                                  var elemId = cj(this).attr( 'name' ).split('-');
                                  cj( 'input[name="address[' + elemId[1] + '][master_id]"]' ).val( cj(this).val( ) );
                              });
                          } else {
                              var helpText = {/literal}"{ts}Selected contact does not have an address. Please select a contact with address else add an address to the existing selected contact."{/ts}{literal};        
                              cj( '#shared-address-' + blockNo + ' .shared-address-list' ).remove( );
                              cj( '#shared-address-' + blockNo ).append( '<tr class="shared-address-list"><td></td><td>' + helpText + '</td></tr>');
                          }
                      }
                  },
                  ajaxURL: {/literal}"{crmURL p='civicrm/ajax/rest' h=0}"{literal} 
            });            
         });

         // continuous check for changed value
         setInterval(function( ) {
           if ( cj( contactHiddenElement ).val( ) != _default ) {
             // trigger native
             cj( contactHiddenElement ).change( );

             // update stored value
             _default = cj( contactHiddenElement ).val( );
           }  

         }, 500);
         
         // end of code to add onchange event for hidden element
     });
     {/literal}
     </script>
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

