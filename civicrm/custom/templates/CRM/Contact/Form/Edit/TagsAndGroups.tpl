{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
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
<script>
  var BBCID = {/literal}{$entityID}{literal};
  var BBActionConst = {/literal}{$action}{literal};
</script>
{/literal}

{crmScript url="/sites/default/themes/Bluebird/scripts/bbtree.js" region=form-body}
{crmStyle url="/sites/default/themes/Bluebird/css/tags/tags.css"}

{if $title}
<div class="crm-accordion-wrapper crm-tagGroup-accordion collapsed">
  <div class="crm-accordion-header">{$title}</div>
  <div class="crm-accordion-body" id="tagGroup">
{/if}
    <table class="form-layout-compressed{if $context EQ 'profile'} crm-profile-tagsandgroups{/if}">
      <tr>
        {if !$type || $type eq 'group'}
          <td>
            {if $groupElementType eq 'select'}
              <span class="label">{if $title}{$form.group.label}{/if}</span>
            {/if}
            {$form.group.html}
          </td>
        {/if}
        {if (!$type || $type eq 'tag') && $tree}
          <td width="70%">{if $title}<span class="label">{$form.tag.label}</span>{/if}
            <div id="dialog"></div>

            <div id="crm-tagListWrap">
              {*NYSS inject our custom tagset and pass parent id: keywords*}
              {include file="CRM/NYSS/Form/Tagset.tpl" parent=296}

              {*NYSS inject our custom tagtree*}
              {include file="CRM/NYSS/Form/Tagtree.tpl" level=1}

              {if $contactIssueCode_list}
                <div class="contactTagsList help"><span>{$contactIssueCode_list}</span></div>
                <div class="clear"></div>
              {/if}

              {*NYSS inject our custom tagset and pass parent id: leg positions*}
              {include file="CRM/NYSS/Form/Tagset.tpl" parent=292}
            </div>
          </td>
        {/if}
      </tr>
    </table>
{if $title}
  </div>
</div><!-- /.crm-accordion-wrapper -->
{/if}
