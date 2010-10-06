{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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


{include file="Custom/header.tpl"}
<div class="clear"></div>


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

{* temporary hack to fix wysiysg editor failure if js compression is on *}
{if $defaultWysiwygEditor eq 1}
    <script type="text/javascript" src="{$config->resourceBase}packages/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
    <script type="text/javascript" src="{$config->resourceBase}packages/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
{elseif $defaultWysiwygEditor eq 2}
    <script type="text/javascript" src="{$config->resourceBase}packages/ckeditor/ckeditor.js"></script>
{/if}


{if $pageTitle}
	<div class="crm-title">
		<h1 class="title">{if $isDeleted}<del>{/if}{$pageTitle}{if $isDeleted}</del>{/if}</h1>
	</div>
{/if}


{*{include file="CRM/common/langSwitch.tpl"}*}


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

<div id="crm-seal"></div>

{if ! $urlIsPublic}
{include file="CRM/common/footer.tpl"}
{/if}

{literal}
<script type="text/javascript">
cj(function() {
   cj().crmtooltip(); 
   cj().crmaccordions(); 
});
</script>
{/literal}
{* We need to set jquery $ object back to $*}
<script type="text/javascript">jQuery.noConflict(true);</script>
</div> {* end crm-container div *}

