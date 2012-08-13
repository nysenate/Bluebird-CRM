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
<div class="crm-table2div-layout" id="crm-demographic-content">
    <div class="crm-clear"> <!-- start of main -->
      {if $permission EQ 'edit'}
        <div class="crm-config-option">
          <a id="edit-demographic" class="hiddenElement crm-link-action" title="{ts}click to add or edit demographics{/ts}">
          <span class="batch-edit"></span>{ts}add or edit demographics{/ts}
          </a>
        </div>
      {/if}

      <div class="crm-label">{ts}Gender{/ts}</div>
      <div class="crm-content crm-contact-gender_display">{$gender_display}</div>
      <div class="crm-label">{ts}Date of birth{/ts}</div>
      <div class="crm-content crm-contact-birth_date_display">
          {if $birthDateViewFormat}	 
              {$birth_date_display|crmDate:$birthDateViewFormat}
          {else}
              {$birth_date_display|crmDate}
          {/if}
          &nbsp;
      </div>
      {if $is_deceased eq 1}
         {if $deceased_date}
          <div class="crm-label">{ts}Date Deceased{/ts}</div>
          <div class="crm-content crm-contact-deceased_date_display">
           {if $birthDateViewFormat}          
            {$deceased_date_display|crmDate:$birthDateViewFormat}
           {else}
            {$deceased_date_display|crmDate}
           {/if}
         </div>
         {else}
          <div class="crm-label"></div>
          <div class="crm-content crm-contact-deceased_message"><span class="font-red upper">{ts}Contact is Deceased{/ts}</span></div>
         {/if}
       {else}
          <div class="crm-label">{ts}Age{/ts}</div>
          <div class="crm-content crm-contact-age_display">{if $age.y}{ts count=$age.y plural='%count years'}%count year{/ts}{elseif $age.m}{ts count=$age.m plural='%count months'}%count month{/ts}{/if}</div>
       {/if}
    </div> <!-- end of main -->
  </div>

{if $permission EQ 'edit'}
{literal}
<script type="text/javascript">
cj(function(){
    cj('#demographic-block').mouseenter( function() {
      cj(this).addClass('crm-inline-edit-hover');
      cj('#edit-demographic').show();
    }).mouseleave( function() {
      cj(this).removeClass('crm-inline-edit-hover');
      cj('#edit-demographic').hide();
    });

    cj('#edit-demographic').click( function() {
        var dataUrl = {/literal}"{crmURL p='civicrm/ajax/inline' h=0 q='snippet=5&reset=1&cid='}{$contactId}"{literal}; 
        
        addCiviOverlay('.crm-summary-demographic-block');
        cj.ajax({
          data: { 'class_name':'CRM_Contact_Form_Inline_Demographics' },
          url: dataUrl,
          async: false
        }).done( function(response) {
          cj('#demographic-block').html( response );
        });
        
        removeCiviOverlay('.crm-summary-demographic-block');
    });
});
</script>
{/literal}
{/if}
