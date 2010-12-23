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
<div class="crm-block crm-form-block crm-search-form-block">

<h3>{ts}Find Groups{/ts}</h3>
<div class="form-item">
<table class="form-layout">
    <tr>
        <td>{$form.title.label}<br />
            {$form.title.html}<br />
            <span class="description font-italic">
                {ts}Complete OR partial group name.{/ts}
            </span>
        </td>
        <td>{$form.group_type.label}<br />
            {$form.group_type.html}<br />
            <span class="description font-italic">
                {ts}Filter search by group type(s).{/ts}
            </span>
        </td>
        <td>{$form.visibility.label}<br />
            {$form.visibility.html}<br />
            <span class="description font-italic">
                {ts}Filter search by visibility.{/ts}
            </span>
        </td>
	<td>
            <label> Status</label><br />		
	    {$form.active_status.html}
	    {$form.active_status.label}&nbsp;
	    {$form.inactive_status.html}
            {$form.inactive_status.label}		
	 </td>
    </tr>
     <tr>
        <td>{$form.buttons.html}</td><td colspan="2">
    </tr>
</table>
</div>
</div>