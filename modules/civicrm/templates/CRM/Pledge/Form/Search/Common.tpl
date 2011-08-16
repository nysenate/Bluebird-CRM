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
 <tr>
    <td>
     {$form.pledge_payment_date_low.label} 
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=pledge_payment_date_low}
    </td>
    <td>
     {$form.pledge_payment_date_high.label}
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=pledge_payment_date_high}
    </td> 
 </tr>
 <tr>
    <td colspan="2">
     <label>{ts}Pledge Payment Status{/ts} 
     <br />{$form.pledge_payment_status_id.html}
    </td>
 </tr>
 <tr>
    <td> 
     <label>{ts}Pledge Amounts{/ts} 
     <br />
     {$form.pledge_amount_low.label} {$form.pledge_amount_low.html} &nbsp;&nbsp; {$form.pledge_amount_high.label} {$form.pledge_amount_high.html}
    </td>
    <td>
     <label>{ts}Pledge Status{/ts} 
     <br />{$form.pledge_status_id.html}
    </td>
 </tr>
 <tr>
    <td>
     {$form.pledge_create_date_low.label} 
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=pledge_create_date_low}
    </td>
    <td>
     {$form.pledge_create_date_high.label}
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=pledge_create_date_high}
    </td> 
 </tr>
 <tr>
    <td>
     {$form.pledge_start_date_low.label} 
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=pledge_start_date_low}
    </td>
    <td>
     {$form.pledge_start_date_high.label}
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=pledge_start_date_high}
    </td> 
 </tr>
 <tr> 
    <td>  
     {$form.pledge_end_date_low.label} 
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=pledge_end_date_low}
    </td>
    <td> 
     {$form.pledge_end_date_high.label}
     <br />
     {include file="CRM/common/jcalendar.tpl" elementName=pledge_end_date_high}
    </td> 
 </tr>
 <tr>
    <td>
     <label>{ts}Contribution Type{/ts}</label> 
     <br />{$form.pledge_contribution_type_id.html}
    </td>
    <td>
      <label>{ts}Contribution Page{/ts}</label> 
      <br />{$form.pledge_contribution_page_id.html}
    </td> 
 </tr>
 <tr> 
    <td>
     {$form.pledge_in_honor_of.label} 
     <br />{$form.pledge_in_honor_of.html}
    </td>
    <td>
     {$form.pledge_test.html}&nbsp;{$form.pledge_test.label}
    </td>
 </tr>
 <tr> 
    <td colspan="2">
     {$form.pledge_frequency_unit.label}	
     <br /> {$form.pledge_frequency_interval.label} &nbsp; {$form.pledge_frequency_interval.html} &nbsp; 
     {$form.pledge_frequency_unit.html}
    </td>
 </tr>

{* campaign in pledge search *}
{include file="CRM/Campaign/Form/addCampaignToComponent.tpl" 
campaignContext="componentSearch" campaignTrClass='' campaignTdClass=''}

{if $pledgeGroupTree}
 <tr>
    <td colspan="2">
      {include file="CRM/Custom/Form/Search.tpl" groupTree=$pledgeGroupTree showHideLinks=false}
    </td>
 </tr>
{/if}