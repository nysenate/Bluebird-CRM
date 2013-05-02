{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{if $config->debug}
{include file="CRM/common/debug.tpl"}
{/if}

{* include wysiwyg related files*}
{include file="CRM/common/wysiwyg.tpl"}

<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">


{include file="CRM/common/action.tpl"}
{crmNavigationMenu is_default=1}

{if isset($browserPrint) and $browserPrint}
{* Javascript window.print link. Used for public pages where we can't do printer-friendly view. *}
<div id="printer-friendly">
<a href="javascript:window.print()" title="{ts}Print this page.{/ts}">
	<div class="ui-icon ui-icon-print"></div>
</a>
</div>
{else}
{* Printer friendly link/icon. *}
<div id="printer-friendly">
<a href="{$printerFriendly}" title="{ts}Printer-friendly view of this page.{/ts}">
	<div class="ui-icon ui-icon-print"></div>
</a>
</div>
{/if}

{crmRegion name='page-header'}
{/crmRegion}
<div class="clear"></div>

{if isset($localTasks) and $localTasks}
    {include file="CRM/common/localNav.tpl"}
{/if}

{include file="CRM/common/status.tpl"}

{crmRegion name='page-body'}
<!-- .tpl file invoked: {$tplFile}. Call via form.tpl if we have a form in the page. -->
{if isset($isForm) and $isForm}
    {include file="CRM/Form/$formTpl.tpl"}
{else}
    {include file=$tplFile}
{/if}
{/crmRegion}

{crmRegion name='page-footer'}
{if ! $urlIsPublic}
{include file="CRM/common/footer.tpl"}
{/if}
{/crmRegion}

{literal}
<script type="text/javascript">
cj(function() {
   cj().crmtooltip();
});

cj(document).ready(function() {
  advmultiselectResize();
});

cj(window).resize(function() {
  advmultiselectResize();
});
</script>
{/literal}
</div> {* end crm-container div *}
