{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
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
{* Default template custom searches. This template is used automatically if templateFile() function not defined in
   custom search .php file. If you want a different layout, clone and customize this file and point to new file using
   templateFile() function.*}

<div class="help">Use this tool to generate counts for tags based on date ranges for when the contacts were tagged.</div>

<div class="crm-block crm-form-block crm-contact-custom-search-form-block">
  <div class="crm-accordion-wrapper crm-custom_search_form-accordion {if $rows}collapsed{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Edit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body" id="BirthdayByMonth">
      <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
      </div>
      <table class="form-layout-compressed">
        <tr class="crm-contact-custom-search-form-row-tag-type">
          <td class="label"><label for="tag_type">{$form.tag_type.label}</label></td>
          <td>{$form.tag_type.html}</td>
        </tr>
        <tr class="crm-contact-custom-search-form-row-start_date">
          <td class="label"><label for="start_date">{$form.start_date.label}</label></td>
          <td>{include file="CRM/common/jcalendar.tpl" elementName=start_date}</td>
        </tr>
        <tr class="crm-contact-custom-search-form-row-end_date">
          <td class="label"><label for="end_date">{$form.end_date.label}</label></td>
          <td>{include file="CRM/common/jcalendar.tpl" elementName=end_date}</td>
        </tr>
        <tr class="crm-contact-custom-search-form-row-action-type">
          <td class="label"><label for="action_type">{$form.action_type.label}</label></td>
          <td>{$form.action_type.html}</td>
        </tr>
      </table>
        <div class="crm-submit-buttons">
          {include file="CRM/common/formButtons.tpl" location="bottom"}
        </div>
    </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->

{if $rowsEmpty || $rows}
  <div class="crm-content-block">
    {if $rowsEmpty}
      {include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
    {/if}

    {if $summary}
      {$summary.summary}: {$summary.total}
    {/if}

    {if $rows}
      <div class="crm-results-block">
        {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
        {* This section handles form elements for action task select and submit *}
        <div class="crm-search-tasks">
          {*9990*}
          {if $quickExportUrl}
            <a class="button" id="quick_export" style="float: none; display: inline-block" href="{$quickExportUrl}">
              <span style="padding-right: 8px;">Quick Export</span>
            </a>
          {/if}
        </div>

        {* This section displays the rows along and includes the paging controls *}
        <div class="crm-search-results">
          {include file="CRM/common/pager.tpl" location="top"}

          {* Include alpha pager if defined. *}
          {if $atoZ}
            {include file="CRM/common/pagerAToZ.tpl"}
          {/if}

          {strip}
          <table class="selector" summary="{ts}Search results listings.{/ts}">
            <thead class="sticky">
              <tr>
                {foreach from=$columnHeaders item=header}
                  <th scope="col">
                    {if $header.sort}
                      {assign var='key' value=$header.sort}
                      {$sort->_response.$key.link}
                    {else}
                      {$header.name}
                    {/if}
                  </th>
                {/foreach}
              </tr>
            </thead>

            {counter start=0 skip=1 print=false}
            {foreach from=$rows item=row}
              <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
                {foreach from=$columnHeaders item=header}
                  {assign var=fName value=$header.sort}
                  {if $fName eq 'sort_name'}
                    <td></td>
                  {else}
                    <td>{$row.$fName}</td>
                  {/if}
                {/foreach}
              </tr>
            {/foreach}
          </table>
          {/strip}

          <script type="text/javascript">
            {* this function is called to change the color of selected row(s) *}
            var fname = "{$form.formName}";
            on_load_init_checkboxes(fname);
          </script>

          {include file="CRM/common/pager.tpl" location="bottom"}
        {* END Actions/Results section *}
        </div>
      </div>
    {/if}
  </div>
{/if}

{literal}
<script type="text/javascript">
  cj(document).ready(function(){
    if ( cj('div.messages.status.no-popup').length ) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning' );
    }
  });

  //9990 - move quick export button
  cj('#quick_export').
    appendTo('div.crm-search-tasks table tr:nth-child(3) td');
</script>
{/literal}
