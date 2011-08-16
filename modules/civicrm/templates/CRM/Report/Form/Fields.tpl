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
{if !$printOnly} {* NO print section starts *}
<div {if !$criteriaForm}style="display: none;"{/if}> {* criteria section starts *}
<div class="crm-accordion-wrapper crm-report_criteria-accordion crm-accordion_title-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
  	{ts}Report Criteria{/ts}
   </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">	
        <div id="id_{$formTpl}"> {* search section starts *}
                {include file="CRM/Report/Form/Criteria.tpl"}
        </div> {* search div section ends *}
  </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->       
</div> {* criteria section ends *}

{if $instanceForm OR $instanceFormError} {* settings section starts *}
<div class="crm-accordion-wrapper crm-report_setting-accordion crm-accordion_title-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
 <div class="crm-accordion-header" {if $updateReportButton} onclick="hide('update-button'); return false;" {/if} >
  <div class="icon crm-accordion-pointer"></div> 
  	{if $mode eq 'template'}{ts}Create Report{/ts}{else}{ts}Report Settings{/ts}{/if}
     </div><!-- /.crm-accordion-header -->
 <div class="crm-accordion-body">
        <div id="id_{$instanceForm}">
                <div id="instanceForm">
                    {include file="CRM/Report/Form/Instance.tpl"}
                    {assign var=save value="_qf_"|cat:$form.formName|cat:"_submit_save"}
                        <div class="crm-submit-buttons">
                            {$form.$save.html}
                        </div>
                </div>
        </div>
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->  
{if $updateReportButton}
<div id='update-button' class="crm-submit-buttons">
   {$form.$save.html}
</div>
{/if}
{/if} {* settings section ends *}

{literal}
<script type="text/javascript">
cj(function() {
   cj().crmaccordions(); 
});
</script>
{/literal}

{/if} {* NO print section ends *}