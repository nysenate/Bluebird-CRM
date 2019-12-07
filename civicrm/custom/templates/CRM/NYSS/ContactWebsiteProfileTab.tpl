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
{* Custom Data view mode*}
<div id="WebsiteProfile" class="view-content crm-block crm-content-block nyss-integration-tab">
  <h3>Website Profile</h3>

  {if $webUserURL}
    <div id="website-user-url">&raquo; <a href="{$webUserURL}" target="_blank">Visit user's website profile</a></div>
  {/if}

  {foreach from=$viewCustomData item=customValues key=customGroupId}
    {foreach from=$customValues item=cd_edit key=cvID}
      <table class="no-border crm-info-panel" id="{$cd_edit.name}">
        {foreach from=$cd_edit.fields item=element key=field_id}
          <tr>
            {if $element.options_per_line != 0}
              <td class="label">{$element.field_title}</td>
              <td class="html-adjust">
                {* sort by fails for option per line. Added a variable to iterate through the element array*}
                {foreach from=$element.field_value item=val}
                  {$val}
                  <br/>
                {/foreach}
              </td>
            {else}
              <td class="label">{$element.field_title}</td>
              {if $element.field_type == 'File'}
                {if $element.field_value.displayURL}
                  <td class="html-adjust">
                    <a href="{$element.field_value.displayURL}" class='crm-image-popup'>
                      <img src="{$element.field_value.displayURL}" height="100" width="100">
                    </a>
                  </td>
                {else}
                  <td class="html-adjust">
                    <a href="{$element.field_value.fileURL}">{$element.field_value.fileName}</a>
                  </td>
                {/if}
              {else}
                {if $element.field_data_type == 'Money'}
                  {if $element.field_type == 'Text'}
                    <td class="html-adjust">{$element.field_value|crmMoney}</td>
                  {else}
                    <td class="html-adjust">{$element.field_value}</td>
                  {/if}
                {else}
                  <td class="html-adjust">
                    {if $element.contact_ref_id}
                    <a href='{crmURL p="civicrm/contact/view" q="reset=1&cid=`$element.contact_ref_id`"}'>
                      {/if}
                      {if $element.field_data_type == 'Memo'}
                        {$element.field_value|nl2br}
                      {else}
                        {$element.field_value}
                      {/if}
                      {if $element.contact_ref_id}
                    </a>
                    {/if}
                  </td>
                {/if}
              {/if}
            {/if}
          </tr>
        {/foreach}
      </table>
    {/foreach}
  {/foreach}
</div>
