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
<tr class= "{if $cd_edit.collapse_display}hiddenElement{/if}">
{if $element.options_per_line != 0}
      <td class="label">{$element.field_title}</td>
      <td>
          {* sort by fails for option per line. Added a variable to iterate through the element array*}
          {foreach from=$element.field_value item=val}
              {$val}
          {/foreach}
      </td>
  {else}
      <td class="label">{$element.field_title}</td>
      {if $element.field_type == 'File'}
          {if $element.field_value.displayURL}
              <td><a href="javascript:imagePopUp('{$element.field_value.displayURL}')" ><img src="{$element.field_value.displayURL}" height = "{$element.field_value.imageThumbHeight}" width="{$element.field_value.imageThumbWidth}"></a></td>
          {else}
              <td class="html-adjust"><a href="{$element.field_value.fileURL}">{$element.field_value.fileName}</a></td>
          {/if}
      {else}
          <td class="html-adjust">{$element.field_value}</td>
      {/if}
{/if}
</tr>
