{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
{* Master tpl for Advanced Search *}

<div class="crm-form-block crm-search-form-block">

{include file="CRM/Contact/Form/Search/Intro.tpl"}

<div class="crm-accordion-wrapper crm-advanced_search_form-accordion {if !empty($ssID) or $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
 <div class="crm-accordion-header crm-master-accordion-header">
  <div class="icon crm-accordion-pointer"></div>
  {if !empty($ssID) or $rows}
  {if $savedSearch}
    {ts 1=$savedSearch.name}Edit %1 Smart Group Criteria{/ts}
  {else}
    {ts}Edit Search Criteria{/ts}
  {/if}
  {else}
  {if $savedSearch}
    {ts 1=$savedSearch.name}Edit %1 Smart Group Criteria{/ts}
  {else}
    {ts}Search Criteria{/ts}
  {/if}
  {/if}
 </div>
 <div class="crm-accordion-body">
  {include file="CRM/Contact/Form/Search/AdvancedCriteria.tpl"}
 </div>
</div>  
</div>

{if $rowsEmpty}
<div class="crm-content-block">
	<div class="crm-results-block crm-results-block-empty">
    {include file="CRM/Contact/Form/Search/EmptyResults.tpl"}
	</div>
</div>
{/if}

{if $rows}
<div class="crm-content-block">
	<div class="crm-results-block">
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
    
       {* This section handles form elements for action task select and submit *}
       <div class="crm-search-tasks">
       {if $taskFile}
          {if $taskContext}
            {include file=$taskFile context=$taskContext}
          {else}
            {include file=$taskFile}
          {/if}
       {else}
         {include file="CRM/Contact/Form/Search/ResultTasks.tpl"}
       {/if}
       </div>

       {* This section displays the rows along and includes the paging controls *}
       <div class="crm-search-results">
       {if $resultFile}
          {if $resultContext}
             {include file=$resultFile context=$resultContext}
          {else}
             {include file=$resultFile}
          {/if}
       {else}
         {include file="CRM/Contact/Form/Selector.tpl"}
       {/if}
       </div>

       {* END Actions/Results section *}
       </div>
</div>
{/if}
{literal}
<script type="text/javascript">
cj(function() { 
    cj().crmaccordions(); 
    if ( cj('#component_mode').val() != '7' ) {
      cj('#crm-display_relationship_type').hide( );
    }

    cj('#component_mode').change( function( ) {
        // reset task dropdown if user changes component mode and it exists
	    if ($("#task").length > 0) {
	        cj('#task').val( '' );
	    }
        var selectedValue = cj(this).val( );
        switch ( selectedValue ) {
            case '2':
            cj('.crm-CiviContribute-accordion').removeClass('crm-accordion-closed').addClass('crm-accordion-open') ;
            cj('#crm-display_relationship_type').hide( );
            cj('#display_relationship_type').val('');
            loadPanes('CiviContribute');
            break;

            case '3':
            cj('.crm-CiviEvent-accordion').removeClass('crm-accordion-closed').addClass('crm-accordion-open') ;
            cj('#display_relationship_type').val('');
            cj('#crm-display_relationship_type').hide( );
            loadPanes('CiviEvent');
            break;

            case '4':
            cj('.crm-activity-accordion').removeClass('crm-accordion-closed').addClass('crm-accordion-open') ;
            cj('#display_relationship_type').val('');
            cj('#crm-display_relationship_type').hide( );
            loadPanes('activity');
            break;

            case '5':
            cj('.crm-CiviMember-accordion').removeClass('crm-accordion-closed').addClass('crm-accordion-open') ;
            cj('#display_relationship_type').val('');
            cj('#crm-display_relationship_type').hide( );
            loadPanes('CiviMember');
            break;

            case '6':
            cj('.crm-CiviCase-accordion').removeClass('crm-accordion-closed').addClass('crm-accordion-open') ;
            cj('#display_relationship_type').val('');
            cj('#crm-display_relationship_type').hide( );
            loadPanes('CiviCase');
            break;

	    case '7':
            cj('#crm-display_relationship_type').show( );
            break;

            default:
            cj('#crm-display_relationship_type').hide( );
            cj('#display_relationship_type').val('');
            break;
        } 
    });
});
</script>
{/literal}

