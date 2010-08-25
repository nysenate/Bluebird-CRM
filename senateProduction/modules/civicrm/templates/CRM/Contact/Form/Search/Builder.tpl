{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
{* Search Builder *}
<div class="messages help" id="help">
{capture assign=docLink}{docURL page="Search Builder" text="Search Builder Documentation"}{/capture}
<strong>{ts 1=$docLink}IMPORTANT: Search Builder requires you to use specific formats for your search values. Review the %1 before building your first search.{/ts}</strong> {help id='builder-intro'}
</div>

<div class="crm-form-block crm-search-form-block">
<div class="crm-accordion-wrapper crm-search_builder-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
 <div class="crm-accordion-header crm-master-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
        {ts}Edit Search Criteria{/ts}
</div><!-- /.crm-accordion-header -->
<div class="crm-accordion-body">
<div id = "searchForm">	
{* Table for adding search criteria. *}
{include file="CRM/Contact/Form/Search/table.tpl"}

<div class="clear"></div>
<div id="crm-submit-buttons">
    {$form.buttons.html}
</div>
</div>
</div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->
{if $rowsEmpty || $rows}
<div class="crm-content-block">
{if $rowsEmpty}
	<div class="crm-results-block crm-results-block-empty">
    {include file="CRM/Contact/Form/Search/EmptyResults.tpl"}
	</div>
{/if}

{if $rows}
	<div class="crm-results-block">
       {* This section handles form elements for action task select and submit *}         
       <div class="crm-search-tasks">
       {include file="CRM/Contact/Form/Search/ResultTasks.tpl"}
       </div>

       {* This section displays the rows along and includes the paging controls *}
       <div class="crm-search-results">
       {include file="CRM/Contact/Form/Selector.tpl"}
      </div>

    </div>
    {* END Actions/Results section *}

{/if}
</div>
{/if}
{$initHideBoxes}
<script type="text/javascript">
    var showBlock = new Array({$showBlock});
    var hideBlock = new Array({$hideBlock});

{* hide and display the appropriate blocks *}
    on_load_init_blocks( showBlock, hideBlock );
</script>
{literal}
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}