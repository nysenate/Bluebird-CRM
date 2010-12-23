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
{* Export Wizard - Step 2 *}
{* @var $form Contains the array for the form elements and other form associated information assigned to the template by the controller *}
<div class="crm-block crm-form-block crm-export-form-block">

 <div id="help">
    <p>{ts}<strong>Export PRIMARY fields</strong> provides the most commonly used data values. This includes primary address information, preferred phone and email.{/ts}</p>
    <p>{ts}Click <strong>Select fields for export</strong> and then <strong>Continue</strong> to choose a subset of fields for export. This option allows you to export multiple specific locations (Home, Work, etc.) as well as custom data. You can also save your selections as a 'field mapping' so you can use it again later.{/ts}</p>
 </div>

 {* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
 {include file="CRM/common/WizardHeader.tpl"}

 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
 <div id="export-type">
  <div class="crm-section crm-exportOption-section">
    <h3>{ts count=$totalSelectedRecords plural='%count records selected for export.'}One record selected for export.{/ts}</h3>
    <div class="content-no-label crm-content-exportOption">
        {$form.exportOption.html}
   </div>
  </div>
  
  <div id="map" class="crm-section crm-export-mapping-section">
      {if $form.mapping }
        <div class="label crm-label-export-mapping">
            {$form.mapping.label}
        </div>
        <div class="content crm-content-export-mapping">
            {$form.mapping.html}
        </div>
		<div class="clear"></div> 
      {/if}
  </div>
  
  {if $taskName eq 'Export Contacts'}
  <div class="crm-section crm-export-mergeOptions-section">
    <div class="label crm-label-mergeOptions">{ts}Merge Options{/ts} {help id="id-export_merge_options"}</div>
    <div class="content crm-content-mergeSameAddress">
        &nbsp;{$form.merge_same_address.html}
    </div>
    <div class="content crm-content-mergeSameHousehold">
        &nbsp;{$form.merge_same_household.html}
    </div>
	<div class="clear"></div> 
  </div>
  {/if}
    
 </div>

 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
{literal}
  <script type="text/javascript">
     function showMappingOption( )
     {
	var element = document.getElementsByName("exportOption");

	if ( element[1].checked ) { 
	  show('map');
        } else {
	  hide('map');
	}
     } 
   showMappingOption( );
  </script>
{/literal}
