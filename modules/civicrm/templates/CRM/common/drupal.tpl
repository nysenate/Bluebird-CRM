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
{if $config->debug}
{include file="CRM/common/debug.tpl"}
{/if}

<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">

{* we should uncomment below code only when we are experimenting with new css for specific pages and comment css inclusion in civicrm.module*}
{*if $config->customCSSURL}
    <link rel="stylesheet" href="{$config->customCSSURL}" type="text/css" />
{else}
    {assign var="revamp" value=0}
    {foreach from=$config->revampPages item=page}
        {if $page eq $tplFile}
            {assign var="revamp" value=1}
        {/if}
    {/foreach}
    
    {if $revamp eq 0}
        <link rel="stylesheet" href="{$config->resourceBase}css/civicrm.css" type="text/css" />
    {else}
        <link rel="stylesheet" href="{$config->resourceBase}css/civicrm-new.css" type="text/css" />
    {/if}
    <link rel="stylesheet" href="{$config->resourceBase}css/extras.css" type="text/css" />
{/if*}

{include file="CRM/common/action.tpl"}
{if $buildNavigation }
    {include file="CRM/common/Navigation.tpl" }
{/if}

{* temporary hack to fix wysiysg editor failure if js compression is on *}
{if $defaultWysiwygEditor eq 1}
    <script type="text/javascript" src="{$config->resourceBase}packages/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
    <script type="text/javascript" src="{$config->resourceBase}packages/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
{elseif $defaultWysiwygEditor eq 2}
    <script type="text/javascript" src="{$config->resourceBase}packages/ckeditor/ckeditor.js"></script>
{/if}

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

{*{include file="CRM/common/langSwitch.tpl"}*}

<div class="clear"></div>

{if isset($localTasks) and $localTasks}
    {include file="CRM/common/localNav.tpl"}
{/if}

{include file="CRM/common/status.tpl"}

<!-- .tpl file invoked: {$tplFile}. Call via form.tpl if we have a form in the page. -->
{if isset($isForm) and $isForm}
    {include file="CRM/Form/$formTpl.tpl"}
{else}
    {include file=$tplFile}
{/if}

{if ! $urlIsPublic}
{include file="CRM/common/footer.tpl"}
{/if}

{literal}
<script type="text/javascript">
cj(function() {
   cj().crmtooltip(); 
});
</script>
{/literal}
{* We need to set jquery $ object back to $*}
<script type="text/javascript">jQuery.noConflict(true);</script>
</div> {* end crm-container div *}

