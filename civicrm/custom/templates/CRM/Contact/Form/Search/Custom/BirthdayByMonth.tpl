{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
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
{assign var="panel" value=$smarty.get.panel}{*NYSS*}
<div class="crm-block crm-form-block crm-contact-custom-search-form-block">
  <div class="crm-accordion-wrapper crm-custom_search_form-accordion {if $rows}collapsed{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Edit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body" id="BirthdayByMonth">
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
        <table class="form-layout-compressed">
            <tr class="crm-contact-custom-search-form-row-start_date">
            	<td class="label"><label for="start_date">Birth date =</label></td>
            	<td>{$form.start_date.html}&nbsp;&nbsp;or later</td>
            </tr>
            <tr class="crm-contact-custom-search-form-row-end_date">
            	<td class="label"><label for="end_date">Birth date =</label></td>
            	<td>{$form.end_date.html}&nbsp;&nbsp;or earlier</td>
            </tr>
            <tr class="crm-contact-custom-search-form-row-age_start">
            	<td class="label"><label for="age_start">Age =</label></td>
            	<td>{$form.age_start.html}&nbsp;&nbsp;or older</td>
            </tr>
            <tr class="crm-contact-custom-search-form-row-age_end">
            	<td class="label"><label for="age_end">Age =</label></td>
            	<td>{$form.age_end.html}&nbsp;&nbsp;or younger</td>
            </tr>
            <tr class="crm-contact-custom-search-form-row-birth_month">
            	<td class="label"><label for="birth_month">Individual's Birth Month =</label></td>
            	<td>{$form.birth_month.html}</td>
            </tr>
            <tr class="crm-contact-custom-search-form-row-year_start">
            	<td class="label"><label for="year_start">Birth date: Year =</label></td>
            	<td>{$form.year_start.html}&nbsp;&nbsp;or later</td>
            </tr>
            <tr class="crm-contact-custom-search-form-row-year_end">
            	<td class="label"><label for="year_end">Birth date: Year =</label></td>
            	<td>{$form.year_end.html}&nbsp;&nbsp;or earlier</td>
            </tr>
            <tr class="crm-contact-custom-search-form-row-day_start">
            	<td class="label"><label for="day_start">Birth date: Day =</label></td>
            	<td>{$form.day_start.html}&nbsp;&nbsp;or later</td>
            </tr>
            <tr class="crm-contact-custom-search-form-row-day_end">
            	<td class="label"><label for="day_end">Birth date: Day =</label></td>
            	<td>{$form.day_end.html}&nbsp;&nbsp;or earlier</td>
            </tr>
            
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

{if $summary}
  {$summary.summary}: {$summary.total}
{/if}

{if $rows}
	<div class="crm-results-block">
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
    {* This section handles form elements for action task select and submit *}
    <div class="crm-search-tasks">
      {include file="CRM/Contact/Form/Search/ResultTasks.tpl"}
		</div>

    {* This section displays the rows along and includes the paging controls *}
    <div class="crm-search-results">
      {include file="CRM/common/pager.tpl" location="top"}

      {* Include alpha pager if defined. *}
      {if $atoZ}
        {include file="CRM/common/pagerAToZ.tpl"}
      {/if}
        
      {strip}
      <table class="selector row-highlight" summary="{ts}Search results listings.{/ts}">
        <thead class="sticky">
          <tr>
            <th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
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
            <th>&nbsp;</th>
          </tr>
        </thead>

        {counter start=0 skip=1 print=false}
        {foreach from=$rows item=row}
          <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
            {assign var=cbName value=$row.checkbox}
            <td>{$form.$cbName.html}</td>
              {foreach from=$columnHeaders item=header}
                {assign var=fName value=$header.sort}
                {if $fName eq 'sort_name'}
                  {*NYSS 4536/7928*}
                  <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`&key=`$qfKey`&context=custom"}">{$row.sort_name}</a></td>
                {else}
                  <td>{$row.$fName}</td>
                {/if}
              {/foreach}
            <td>{$row.action}</td>
          </tr>
        {/foreach}
      </table>
      {/strip}

      {include file="CRM/common/pager.tpl" location="bottom"}

      </p>
    {* END Actions/Results section *}
    </div>
    </div>
{/if}



</div>
{/if}

{literal}
<script type="text/javascript">
  //NYSS 7892
  cj(document).ready(function(){
    if ( cj('div.messages.status.no-popup').length ) {
      CRM.alert('No results found. Please revise your search criteria.', 'No Results', 'warning' );
    }
  });
</script>
{/literal}
