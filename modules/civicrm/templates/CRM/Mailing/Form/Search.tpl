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
<div class="crm-block crm-form-block crm-search-form-block">
<table class="form-layout">
    <tr>
        <td>{$form.mailing_name.label}<br />
            {$form.mailing_name.html|crmReplace:class:big} {help id="id-mailing_name"}
        </td>
        <td class="nowrap">{$form.mailing_from.label}<br />
            {include file="CRM/common/jcalendar.tpl" elementName=mailing_from}
        </td>
        <td class="nowrap">{$form.mailing_to.label}<br />
            {include file="CRM/common/jcalendar.tpl" elementName=mailing_to}
        </td> 
    </tr>
    <tr> 
        <td colspan="3">{$form.sort_name.label}<br />
            {$form.sort_name.html|crmReplace:class:big} {help id="id-create_sort_name"}
        </td>
    </tr>
    <tr>
        <td>{$form.buttons.html}</td><td colspan="2"></td>
    </tr>
</table>
</div>