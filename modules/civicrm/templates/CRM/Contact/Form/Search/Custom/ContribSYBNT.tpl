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
{* Template for "Sample" custom search component. *}
{assign var="showBlock" value="'searchForm'"}
{assign var="hideBlock" value="'searchForm_show','searchForm_hide'"}

<div id="searchForm_show" class="form-item">
    <a href="#" onclick="hide('searchForm_show'); show('searchForm'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}" /></a>
    <label>{ts}Edit Search Criteria{/ts}</label>
</div>

<div id="searchForm" class="crm-block crm-form-block crm-contact-custom-search-contribSYBNT-form-block">
    <fieldset>
        <legend><span id="searchForm_hide"><a href="#" onclick="hide('searchForm','searchForm_hide'); show('searchForm_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}" /></a></span>{ts}Search Criteria{/ts}</legend>
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
        <table class="form-layout-compressed">
            <tr class="crm-contact-custom-search-contribSYBNT-form-block-min_amount_1">
                <td><label>{ts}Amount One: Min/Max{/ts}</label></td>
                <td>{$form.min_amount_1.html}</td>
                <td>{$form.max_amount_1.html}</td>
                <td>&nbsp;</td>
            </tr>
            <tr class="crm-contact-custom-search-contribSYBNT-form-block-inclusion_date_one">
                <td><label>Inclusion Date One: Start/End</label></td>
                <td>{include file="CRM/common/jcalendar.tpl" elementName=start_date_1}</td>
                <td>{include file="CRM/common/jcalendar.tpl" elementName=end_date_1}</td>
                <td>{$form.is_first_amount.html}&nbsp;{ts}First time donor only?{/ts}</td>
            </tr>
            <tr class="crm-contact-custom-search-contribSYBNT-form-block-min_amount_2">
                <td><label>{ts}Amount Two: Min/Max{/ts}</label></td>
                <td>{$form.min_amount_2.html}</td>
                <td>{$form.max_amount_2.html}</td>
                <td>&nbsp;</td>
            </tr>
            <tr class="crm-contact-custom-search-contribSYBNT-form-block-inclusion_date_two">
                <td><label>Inclusion Date Two: Start/End</label></td>
                <td>{include file="CRM/common/jcalendar.tpl" elementName=start_date_2}</td>
                <td>{include file="CRM/common/jcalendar.tpl" elementName=end_date_2}</td>
                <td>&nbsp;</td>
            </tr>
            <tr class="crm-contact-custom-search-contribSYBNT-form-block-exclude_min_amount">
                <td><label>Exclusion Amount: Min/Max</label></td>
                <td>{$form.exclude_min_amount.html}</td>
                <td>{$form.exclude_max_amount.html}</td>
                <td>&nbsp;</td>
            </tr>
            <tr class="crm-contact-custom-search-contribSYBNT-form-block-exclusion_date">
                <td><label>Exclusion Date: Start/End</label></td>
                <td>{include file="CRM/common/jcalendar.tpl" elementName=exclude_start_date}</td>
                <td>{include file="CRM/common/jcalendar.tpl" elementName=exclude_end_date}</td>
                <td>&nbsp;</td>
            </tr>
         </table> 
         <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
    </fieldset>
</div>

{if $rowsEmpty}
    {include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
{/if}

{if $summary}
    {$summary.summary}: {$summary.total}
{/if}

{if $rows}
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
    {assign var="showBlock" value="'searchForm_show'"}
    {assign var="hideBlock" value="'searchForm'"}    
    <fieldset>    
        {* This section handles form elements for action task select and submit *}
        {include file="CRM/Contact/Form/Search/ResultTasks.tpl"}

        {* This section displays the rows along and includes the paging controls *}
        <p>

        {include file="CRM/common/pager.tpl" location="top"}

        {include file="CRM/common/pagerAToZ.tpl"}

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
    </fieldset>
    {* END Actions/Results section *}
{/if}

<script type="text/javascript">
    var showBlock = new Array({$showBlock});
    var hideBlock = new Array({$hideBlock});

    {* hide and display the appropriate blocks *}
    on_load_init_blocks( showBlock, hideBlock );
</script>

