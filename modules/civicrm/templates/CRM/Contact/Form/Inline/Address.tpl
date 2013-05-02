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
{* This file builds html for address block inline edit *}
<div id="Address_Block_{$blockId}"class="boxBlock crm-edit-address-block">
  <table class="form-layout crm-edit-address-form crm-inline-edit-form">
    <tr>
      <td>
        <div class="crm-submit-buttons"> 
          {include file="CRM/common/formButtons.tpl"}
        </div>
      </td>
    </tr>
    {if $masterAddress.$blockId gt 0 }
        <tr><td><div class="message status"><div class="icon inform-icon"></div>&nbsp; {ts 1=$masterAddress.$blockId}This address is shared with %1 contact record(s). Modifying this address will automatically update the shared address for these contacts.{/ts}</div></td></tr>
    {/if}
     <tr>
        <td>
           <span class="crm-address-element location_type_id-address-element">
            {$form.address.$blockId.location_type_id.label}&nbsp;{$form.address.$blockId.location_type_id.html}
            </span>&nbsp;
            <!--a href="#" title="{ts}Delete Address Block{/ts}">{ts}Delete this address{/ts}</a-->
        </td>
     </tr>
     <tr>
        <td>
           <span class="crm-address-element is_primary-address-element">{$form.address.$blockId.is_primary.html}</span>
           <span class="crm-address-element is_billing-address-element">{$form.address.$blockId.is_billing.html}</span>
        </td>
     </tr>
     
     {* include shared address template *}
     {include file="CRM/Contact/Form/ShareAddress.tpl"}
 
     <tr>
      <td>
        <table id="address_{$blockId}" class="form-layout-compressed">
           {* build address block w/ address sequence. *}
           {foreach item=addressElement from=$addressSequence}
            {include file=CRM/Contact/Form/Edit/Address/$addressElement.tpl}
           {/foreach}
           {include file=CRM/Contact/Form/Edit/Address/geo_code.tpl}
       </table>
      </td>
     </tr>
  </table>
  
  <div class="crm-edit-address-custom_data crm-inline-edit-form crm-address-custom-set-block-{$blockId}"> 
    {include file="CRM/Contact/Form/Edit/Address/CustomData.tpl"}
  </div> 
</div>

{include file="CRM/Contact/Form/Inline/InlineCommon.tpl"}

{literal}
<script type="text/javascript">
cj( function() {
  cj().crmaccordions(); 
  
  var blockId   = {/literal}{$blockId}{literal};
  var addressId = {/literal}{$addressId}{literal};
  
  // add ajax form submitting
  inlineEditForm( 'Address', 'address-block-'+ blockId,
    {/literal}{$contactId}{literal},
    null, blockId, addressId );

  cj('#address_{/literal}{$blockId}{literal}_location_type_id').change(function() {
    var ele = cj(this);
    var lt = ele.val();
    var container = ele.closest('div.crm-address-block');
    container.data('location-type-id', '');
    if (lt != '') {
      var ok = true;
      cj('.crm-address-block').each(function() {
        if (cj(this).data('location-type-id') == lt) {
          var label = cj('option:selected', ele).text();
          ele.val('');
          alert("{/literal}{ts escape='js'}Location type{/ts} {literal}" + label + "{/literal} {ts escape='js'}has already been assigned to another address. Please select another location type for this address.{/ts}{literal}");
          ok = false;
        }
      });
      if (ok) {
        container.data('location-type-id', lt);
      }
    }
  });
  cj(':checkbox[id*="[is_"]', 'form#Address_{/literal}{$blockId}{literal}').change(function() {
    if (cj(this).is(':checked')) {
      var ids = cj(this).attr('id').slice(-9);
      cj('.crm-address-block :checkbox:checked[id$="' + ids + '"]').not(this).removeAttr('checked');
    }
    else if (cj(this).is("[id*=is_primary]")) {
      alert("{/literal}{ts escape='js'}Please choose another address to be primary before changing this one.{/ts}{literal}");
      cj(this).attr('checked', 'checked');
    }
  });
});

</script>
{/literal}

