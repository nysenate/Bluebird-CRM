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
{* this template is used for updating pledge payment*} 
<div class="crm-block crm-form-block crm-pledge-payment-form-block">
<fieldset><legend>{ts}Edit Pledge Payment{/ts}</legend> 
      <table class="form-layout-compressed">
        <tr><td class="label">{ts}Status{/ts}</td><td class="form-layout">{$status}</td></tr>
        <tr><td class="label">{$form.scheduled_date.label}</td>
            <td>{include file="CRM/common/jcalendar.tpl" elementName=scheduled_date}
            <span class="description">{ts}Scheduled Date for Pledge payment.{/ts}</span></td></tr>
        </td></tr>
      </table> 
       <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</fieldset>
</div> 
