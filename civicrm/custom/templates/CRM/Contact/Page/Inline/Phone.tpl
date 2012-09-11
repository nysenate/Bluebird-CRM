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
{* template for building phone block*}
<div class="crm-table2div-layout" id="crm-phone-content">
  <div class="crm-clear"> <!-- start of main -->
    {if $permission EQ 'edit'}
      {if $phone}
       <div class="crm-config-option">
        <a id="edit-phone" class="hiddenElement crm-link-action" title="{ts}click to add or edit phone numbers{/ts}">
          <span class="batch-edit"></span>{ts}add or edit phone{/ts}
        </a>
      </div>
      {else}
        <div>
          <a id="edit-phone" class="crm-link-action empty-phone" title="{ts}click to add a phone number{/ts}">
            <span class="batch-edit"></span>{ts}add phone{/ts}
          </a>
        </div>
      {/if}
    {/if}
    {foreach from=$phone item=item}
      {if $item.phone || $item.phone_ext}
        <div class="crm-label">{$item.location_type}&nbsp;{$item.phone_type}</div>
        <div class="crm-content crm-contact_phone {if $item.is_primary eq 1}primary{/if}">
          <span {if $privacy.do_not_phone} class="do-not-phone" title="{ts}Privacy flag: Do Not Phone{/ts}"{/if}>
    {$item.phone}{if $item.phone_ext}&nbsp;&nbsp;{ts}ext.{/ts} {$item.phone_ext}{/if}
          </span>
        </div>
      {/if}
    {/foreach}
   </div> <!-- end of main -->
</div>

{if $permission EQ 'edit'}
{literal}
<script type="text/javascript">
cj(function(){
    cj('#phone-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-phone').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      if ( !cj('#edit-phone').hasClass('empty-phone') ) { 
        cj('#edit-phone').hide();
      }
    });

    cj('#edit-phone').click( function() {
        var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal}; 
        
        addCiviOverlay('.crm-summary-phone-block');
        cj.ajax({ 
          data: { 'class_name':'CRM_Contact_Form_Inline_Phone' },
          url: dataUrl,
          async: false
        }).done( function(response) {
          cj('#phone-block').html( response );
        });
        
        removeCiviOverlay('.crm-summary-phone-block');
    });
});
</script>
{/literal}
{/if}
