{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
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

<div class="crm-title">
  <h1 class="title">Tag Demographic Search</h1>
</div>

<div class="crm-block crm-form-block crm-contact-custom-search-form-block">
  <div class="crm-accordion-wrapper crm-custom_search_form-accordion {if $rows}collapsed{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Edit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      <div class="spacer"></div>
      <table class="form-layout-compressed">
        {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
        {foreach from=$elements item=element}
          <tr class="crm-contact-custom-search-form-row-{$element}">
            <td class="label">{$form.$element.label}</td>
            {if $element|strstr:'_date'}
              <td>{include file="CRM/common/jcalendar.tpl" elementName=$element}</td>
            {else}
              <td>{$form.$element.html}</td>
            {/if}
          </tr>
        {/foreach}
      </table>
      <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
    </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->

{if $rowsEmpty || $rows}
  <div class="crm-content-block">
    {if $rowsEmpty}
      {include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
    {/if}

    {if $rows}
      <div class="crm-results-block">
        <div class="crm-search-tasks">
          {*9990*}
          {if $quickExportUrl}
            <a class="button" id="quick_export" style="float: none; display: inline-block" href="{$quickExportUrl}">
              <span style="padding-right: 8px;">Export to CSV</span>
            </a>
          {/if}
        </div>

        {* This section displays the rows along and includes the paging controls *}
        <div class="crm-search-results">
          {strip}
          <table class="selector row-highlight" summary="{ts}Search results listings.{/ts}">
            <thead class="sticky">
              <tr>
              {foreach from=$columnHeaders item=header}
                <th scope="col">
                  {$header.name}
                </th>
              {/foreach}
              </tr>
            </thead>

            {counter start=0 skip=1 print=false}
            {assign var=demo value=''}
            {foreach from=$rows item=row}
              <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
                {foreach from=$columnHeaders item=header}
                  {assign var=fName value=$header.sort}
                  {*only show demo title on first row to create appearance of grouping*}
                  {if $fName eq 'demo'}
                    {if $demo eq $row.$fName}
                      <td></td>
                    {else}
                      <td class="bold">{$row.$fName}</td>
                      {assign var=demo value=$row.$fName}
                    {/if}
                  {else}
                    <td>{$row.$fName}</td>
                  {/if}
                {/foreach}
              </tr>
            {/foreach}
          </table>
          {/strip}
        {* END Actions/Results section *}
        </div>
      </div>
    {/if}
  </div>
{/if}
