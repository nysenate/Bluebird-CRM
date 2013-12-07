{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
{* this div is being used to apply special css *}
{*NYSS*}
{if !$printOnly}
  <div class="help">
  This report displays a log of database changes from April 14, 2012 forward, when the enhanced logging capabilities were enabled. Older log records may still be accessed using the "Database Log (Archived)" report, which may be accessed from Reports > Create Reports from Templates.
  </div>
{/if}

    {if $section eq 1}
    <div class="crm-block crm-content-block crm-report-layoutGraph-form-block">
        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    </div>
    {elseif $section eq 2}
    <div class="crm-block crm-content-block crm-report-layoutTable-form-block">
        {*include the table layout*}
        {include file="CRM/Report/Form/Layout/Table.tpl"}
	</div>
    {else}
    <div class="crm-block crm-form-block crm-report-field-form-block">
        {include file="CRM/Report/Form/Fields.tpl"}
    </div>
    
    <div class="crm-block crm-content-block crm-report-form-block">
        {*include actions*}
        {include file="CRM/Report/Form/Actions.tpl"}

        {*Statistics at the Top of the page*}
        {include file="CRM/Report/Form/Statistics.tpl" top=true}
    
        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    
        {*include the table layout*}
        {include file="CRM/Report/Form/Contact/LoggingSummaryTable.tpl"}{*NYSS*}
    	<br />
        {*Statistics at the bottom of the page*}
        {include file="CRM/Report/Form/Statistics.tpl" bottom=true}    
    
        {include file="CRM/Report/Form/ErrorMessage.tpl"}
    </div>
    {/if}

{*NYSS 6440*}
{literal}
  <link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/css/reportsCivicrm.css" />
{/literal}

{include file="CRM/Report/updateConfirm.tpl"}

{literal}
  <script type="text/javascript">
    cj('div.crm-tasks').insertBefore('div.crm-report-field-form-block');
  </script>
{/literal}
