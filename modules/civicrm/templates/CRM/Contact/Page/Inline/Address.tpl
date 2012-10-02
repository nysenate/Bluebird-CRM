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
{if $add }
  <div class="crm-table2div-layout">
    <div class="crm-clear"> <!-- start of main -->
      <div class="crm-config-option">
        <a id="edit-address-block-{$locationIndex}" class="hiddenElement crm-link-action" title="{ts}click to edit address{/ts}" locno="{$locationIndex}" aid="{$add.id}">
        <span class="batch-edit"></span>{ts}edit address{/ts}
        </a>
      </div>
      <div class="crm-label">
        {ts 1=$add.location_type}%1&nbsp;Address{/ts}
        {if $config->mapProvider AND
            !empty($add.geo_code_1) AND
            is_numeric($add.geo_code_1) AND
            !empty($add.geo_code_2) AND
            is_numeric($add.geo_code_2)
        }
        <br /><a href="{crmURL p='civicrm/contact/map' q="reset=1&cid=`$contactId`&lid=`$add.location_type_id`"}" title="{ts 1=`$add.location_type`}Map %1 Address{/ts}"><span class="geotag">{ts}Map{/ts}</span></a>
        {/if}
      </div>
      <div class="crm-content">
        {if !empty($sharedAddresses.$locationIndex.shared_address_display.name)}
          <strong>{ts 1=$sharedAddresses.$locationIndex.shared_address_display.name}Address belongs to %1{/ts}</strong><br />
        {/if}
        {$add.display|nl2br}
      </div>
    </div>
  </div>

  <!-- add custom data -->
  {foreach from=$add.custom item=customGroup key=cgId} {* start of outer foreach *}
    {assign var="isAddressCustomPresent" value=1}
    {foreach from=$customGroup item=customValue key=cvId}
    <div id="address_custom_{$cgId}_{$locationIndex}" 
    class="crm-accordion-wrapper crm-address-custom-{$cgId}-{$locationIndex}-accordion 
    {if $customValue.collapse_display}crm-accordion-closed{else}crm-accordion-open{/if}">
    <div class="crm-accordion-header">
      <div class="icon crm-accordion-pointer"></div>
      {$customValue.title}
    </div>
    <div class="crm-accordion-body">
      <div class="crm-table2div-layout">
        <div class="crm-clear">
          {foreach from=$customValue.fields item=customField key=cfId}
          <div class="crm-label">
            {$customField.field_title}
          </div>
          <div class="crm-content">
            {$customField.field_value}
          </div>
          {/foreach}
          </div>
        </div>
      </div>
    </div>
    {/foreach}
  {/foreach} {* end of outer custom group foreach *}
  <!-- end custom data -->
  {literal}
  <script type="text/javascript">
    cj( function() {
      cj().crmaccordions();
      cj('#address-block-{/literal}{$locationIndex}{literal}').data('location-type-id', {/literal}{$add.location_type_id}{literal});
    });
  </script>
  {/literal}
{else}
    <div class="crm-table2div-layout">
      <div class="crm-clear">
        <a id="edit-address-block-{$locationIndex}" class="crm-link-action empty-address-block-{$locationIndex}" title="{ts}click to add address{/ts}" locno="{$locationIndex}" aid=0>
        <span class="batch-edit"></span>{ts}add address{/ts}
        </a>
      </div>
    </div>
{/if}

{if $appendBlockIndex and $permission EQ 'edit'}
  {if $appendBlockIndex is odd}
    <div class="contactCardLeft crm-address_{$appendBlockIndex} crm-address-block appendAddLink">
  {else}
    <div class="contactCardRight crm-address_{$appendBlockIndex} crm-address-block appendAddLink">
  {/if}

      <div class="crm-summary-block crm-address-block" id="address-block-{$appendBlockIndex}" locno="{$appendBlockIndex}">
        <div class="crm-table2div-layout">
          <div class="crm-clear">
            <a id="edit-address-block-{$appendBlockIndex}" class="crm-link-action empty-address-block-{$appendBlockIndex}" title="{ts}click to add address{/ts}" locno="{$appendBlockIndex}" aid=0>
            <span class="batch-edit"></span>{ts}add address{/ts}
            </a>
          </div>
        </div>
      </div>
    </div>
{/if}

