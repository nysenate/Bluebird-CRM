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

{literal}
<style type="text/css">
<!--
#districts th {
	width: 70px;
}
#districts .IssueCodes th,
#districts .Keywords th,
#districts .Positions th {
	width: 150px;
}
#districts table {
	width: 100%;
}
-->
</style>
{/literal}

<div class="crm-block crm-content-block">
	<div id="ContactTypes">
    	<h3>Contact Type Counts</h3>
        <table>
        	<tr>{foreach from=$contactTypes key=type item=tcount}<th>{$type}</th>{/foreach}</tr>
            <tr>{foreach from=$contactTypes key=type item=tcount}<td>{$tcount}</td>{/foreach}</tr>
        </table>
    </div>
    
    <div id="help">All district counts are based on the contact's primary address only. Issue Code/Keyword counts are for contact records only (not tags attached to activities or cases) and represent the top 20 most common tags. Expand each panel to view the statistics. Calculations are real-time.</div>
    
    <table>
    <tr id="districts">
    <td width="25%">
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-SenateDistricts-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="SenateDistricts">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Senate Districts
       </div>
       <div class="crm-accordion-body SenateDistricts">
       		<table>
        	{foreach from=$contactSD key=sd item=sdcount}
            	<tr><th>{$sd}</th><td>{$sdcount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-AssemblyDistricts-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="AssemblyDistricts">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Assembly Districts
       </div>
       <div class="crm-accordion-body AssemblyDistricts">
       		<table>
        	{foreach from=$contactAD key=ad item=adcount}
            	<tr><th>{$ad}</th><td>{$adcount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    </td>
    
    <td width="25%">
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-CongressionalDistricts-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="CongressionalDistricts">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Congressional Districts
       </div>
       <div class="crm-accordion-body CongressionalDistricts">
       		<table>
        	{foreach from=$contactCD key=cd item=cdcount}
            	<tr><th>{$cd}</th><td>{$cdcount}</td></tr>
            {foreachelse}
            	<tr><td>There is currently no congressional district information in your database.</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-ElectionDistricts-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="ElectionDistricts">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Election Districts
       </div>
       <div class="crm-accordion-body ElectionDistricts">
       		<table>
        	{foreach from=$contactED key=ed item=edcount}
            	<tr><th>{$ed}</th><td>{$edcount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    </td>
    
    <td width="25%">
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-Counties-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="Counties">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Counties
       </div>
       <div class="crm-accordion-body Counties">
       		<table>
        	{foreach from=$contactCounty key=county item=countycount}
            	<tr><th>{$county}</th><td>{$countycount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-Towns-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="Towns">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Towns
       </div>
       <div class="crm-accordion-body Towns">
       		<table>
        	{foreach from=$contactTown key=town item=towncount}
            	<tr><th>{$town}</th><td>{$towncount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-Wards-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="Wards">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Wards
       </div>
       <div class="crm-accordion-body Wards">
       		<table>
        	{foreach from=$contactWard key=ward item=wardcount}
            	<tr><th>{$ward}</th><td>{$wardcount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    </td>
    
    <td width="25%">
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-IssueCodes-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="IssueCodes">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Issue Codes
       </div>
       <div class="crm-accordion-body IssueCodes">
       		<table>
        	{foreach from=$issueCodes key=ic item=iccount}
            	<tr><th>{$ic}</th><td>{$iccount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-Keywords-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="Keywords">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Keywords
       </div>
       <div class="crm-accordion-body Keywords">
       		<table>
        	{foreach from=$keywords key=k item=kcount}
            	<tr><th>{$k}</th><td>{$kcount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    <div class="crm-accordion-wrapper crm-ajax-accordion crm-Positions-accordion crm-accordion-closed">
       <div class="crm-accordion-header" id="Positions">
       		<div class="icon crm-accordion-pointer"></div>
       	 	Legislative Positions
       </div>
       <div class="crm-accordion-body Positions">
       		<table>
        	{foreach from=$positions key=p item=pcount}
            	<tr><th>{$p}</th><td>{$pcount}</td></tr>
            {/foreach}
        	</table>
       </div>
    </div>
    </td>
    
    </tr> <!--end districts-->
    </table>
</div>


