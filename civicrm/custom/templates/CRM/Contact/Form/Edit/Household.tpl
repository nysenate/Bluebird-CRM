{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
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
{* tpl for building Household related fields *}
<table class="form-layout-compressed">
    <tr>
       <td>{$form.household_name.label}<br/>
         {$form.household_name.html}
       </td>

       <td>{$form.nick_name.label}<br/>
       {$form.nick_name.html}</td>

       <td>{if $action == 1 and $contactSubType}&nbsp;{else}
              {$form.contact_sub_type.label}<br />
              {$form.contact_sub_type.html}
           {/if}
       </td>
       {*NYSS*}
       <td>
       		{$form.contact_source.label}<br />
          {$form.contact_source.html|crmReplace:class:big}
       </td>
       <td>
        	{$form.external_identifier.label}<br />
            {$form.external_identifier.value}
       </td>
       <td>
        	<label for="internal_identifier">{ts}Internal Id{/ts}</label><br />
          {$contactId}
       </td>
     </tr>
</table>

{literal}
<script type="text/javascript">
  //NYSS 7306
  cj('label[for=household_name]').append(' <span class="crm-marker" title="This field is required.">*</span>');
</script>
{/literal}
