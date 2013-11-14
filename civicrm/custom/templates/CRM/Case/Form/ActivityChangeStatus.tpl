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
{* CiviCase - change activity status inline *}
<div class="crm-block crm-form-block crm-case-activitychangestatus-form-block">
  <table class="form-layout">
    <tr class="crm-case-activitychangestatus-form-block-status">
    {*NYSS 6426*}
	  <td class="label">{if $form.activity_change_status.label}{$form.activity_change_status.label}{else}New Status{/if}</td>
      <td>{if $form.activity_change_status.html}
        {$form.activity_change_status.html}
      {else}
        <select class="form-select" id="activity_change_status" name="activity_change_status">
          <option value="1">Scheduled</option>
          <option value="2">Completed</option>
          <option value="3">Cancelled</option>
          <option value="4">Left Message</option>
          <option value="5">Unreachable</option>
          <option value="6">Not Required</option>
          <option value="7">Draft</option>
        </select>
      {/if}
      </td>
    </tr>
  </table>
</div>
