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
{* Template for Find Voters custom search. *}
<div class="crm-accordion-wrapper crm-custom_search_form-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
 <div class="crm-accordion-header crm-master-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
  {ts}Edit Search Criteria{/ts}
</div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">

<div id="searchForm" class="crm-block crm-form-block crm-contact-custom-search-findvoters-form-block">
      <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
        <table class="form-layout-compressed">
            {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
            {foreach from=$elements item=element}
                <tr class="crm-contact-custom-search-findvoters-form-block-{$element}">
                    <td colspan=2 class="label">{$form.$element.label}</td>
                    {if $element eq 'start_date'}
                        <td colspan=2>{include file="CRM/common/jcalendar.tpl" elementName=start_date}</td>
                    {elseif $element eq 'end_date'}
                        <td colspan=2>{include file="CRM/common/jcalendar.tpl" elementName=end_date}</td>
	            {else}
                        <td colspan=2>{$form.$element.html}</td>
                    {/if}
                </tr>
            {/foreach}
        </table>
      <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
  </div>
</div>
</div>
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
       <table class="form-layout-compressed">
       <tr>
    <td class="font-size12pt" style="width: 30%;">
        {if $savedSearch.name}{$savedSearch.name} ({ts}smart group{/ts}) - {/if}
        {ts count=$pager->_totalItems plural='%count Results'}%count Result{/ts}
    </td>
    
    {* Search criteria are passed to tpl in the $qill array *}
    <td class="nowrap">
    {if $qill}
      {include file="CRM/common/displaySearchCriteria.tpl"}
    {/if}
    </td>
  </tr>
  <tr>
    <td class="font-size11pt"> {ts}Select Records{/ts}:</td>
    <td class="nowrap">
        {$form.radio_ts.ts_all.html} {ts count=$pager->_totalItems plural='All %count records'}The found record{/ts} &nbsp; {if $pager->_totalItems > 1} {$form.radio_ts.ts_sel.html} {ts}Selected records only{/ts}{/if}
    </td>
  </tr>
  <tr>
    <td colspan="2">
     {* Hide export and print buttons in 'Add Members to Group' context. *}
     {if $context NEQ 'amtg'}
        {if $action eq 512}
          <ul>   
          {$form._qf_Advanced_next_print.html}&nbsp; &nbsp;
        {elseif $action eq 8192}
          {$form._qf_Builder_next_print.html}&nbsp; &nbsp;
        {elseif $action eq 16384}
          {* since this does not really work for a non standard search
          {$form._qf_Custom_next_print.html}&nbsp; &nbsp;
          *}
        {else}
            {$form._qf_Basic_next_print.html}&nbsp; &nbsp;
        {/if}
        {$form.task.html}
	
     {/if}
     {if $action eq 512}
       {$form._qf_Advanced_next_action.html}
     {elseif $action eq 8192}
       {$form._qf_Builder_next_action.html}&nbsp;&nbsp;
     {elseif $action eq 16384}
       {$form._qf_Custom_next_action.html}&nbsp;&nbsp;
     {else}
       {$form._qf_Basic_next_action.html}
     {/if}
     </td>
  </tr>
  </table>
 </div>

{literal}
<script type="text/javascript">
toggleTaskAction( );
</script>
{/literal}
</div>
{* This section displays the rows along and includes the paging controls *}
<div class="crm-search-results">
  {include file="CRM/common/pager.tpl" location="top"}
  {strip}
   <table class="selector" summary="{ts}Search results listings.{/ts}">
     <thead class="sticky">
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
       </thead>
       {counter start=0 skip=1 print=false}
         {foreach from=$rows item=row}
           <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
           {assign var=cbName value=$row.checkbox}
             <td>{$form.$cbName.html}</td>
             {foreach from=$columnHeaders item=header}
               {assign var=fName value=$header.sort}
                 {if $fName eq 'sort_name'}
                   <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.sort_name}</a></td>
                 {else}
                   <td>{$row.$fName}</td>
                 {/if}
             {/foreach}
             <td>{$row.action}</td>
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
    </p>
   {* END Actions/Results section *}
   </div>
</div>
{/if}

</div>
{/if}
{literal}
<script type="text/javascript">

cj(function() {
   cj().crmaccordions(); 
});

</script>
{/literal}