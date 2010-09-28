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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$config->lcMessages|truncate:2:"":true}">
 <head>
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="Shortcut Icon" type="image/x-icon" href="{$config->resourceBase}i/widget/favicon.png" />
{if $config->customCSSURL}
<link rel="stylesheet" href="{$config->customCSSURL}" type="text/css" />
{else}
<link rel="stylesheet" href="{$config->resourceBase}css/deprecate.css" type="text/css" />
<link rel="stylesheet" href="{$config->resourceBase}css/civicrm.css" type="text/css" />
{/if}
<link rel="stylesheet" href="{$config->resourceBase}css/standalone.css" type="text/css" />
<link rel="stylesheet" href="{$config->resourceBase}css/extras.css" type="text/css" />

{$pageHTMLHead}
{include file="CRM/common/jquery.tpl"}
{include file="CRM/common/action.tpl"}
{if $buildNavigation and !$urlIsPublic }
    {include file="CRM/common/Navigation.tpl" }
{/if}
<script type="text/javascript" src="{$config->resourceBase}js/Common.js"></script>

  <title>{$docTitle}</title>
</head>
<body>

{if $config->debug}
{include file="CRM/common/debug.tpl"}
{/if}

<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
<table border="0" cellpadding="0" cellspacing="0" id="content">
  <tr>
    {if $sidebarLeft}
    <td id="sidebar-left" valign="top">
         {$sidebarLeft}
    </td>
    {/if}
    <td id="main-content" valign="top">
    {if $breadcrumb}
    <div class="breadcrumb">
      {foreach from=$breadcrumb item=crumb key=key}
        {if $key != 0}
          &raquo;
        {/if}
        <a href="{$crumb.url}">{$crumb.title}</a>
      {/foreach}
      </div>
      {/if}
    
      <h1 class="title">{if $isDeleted}<del>{/if}{$pageTitle}{if $isDeleted}</del>{/if}</h1>
      
      {if $browserPrint}
      {* Javascript window.print link. Used for public pages where we can't do printer-friendly view. *}
      <div id="printer-friendly"><a href="javascript:window.print()" title="{ts}Print this page.{/ts}"><img src="{$config->resourceBase}i/print-icon.png" alt="{ts}Print this page.{/ts}" /></a></div>
      {else}
      {* Printer friendly link/icon. *}
      <div id="printer-friendly"><a href="{$printerFriendly}" title="{ts}Printer-friendly view of this page.{/ts}"><img src="{$config->resourceBase}i/print-icon.png" alt="{ts}Printer-friendly view of this page.{/ts}" /></a></div>
      {/if}
      
      <div class="spacer"></div>    
      
      {if $localTasks}
        {include file="CRM/common/localNav.tpl"}
      {/if}

      {include file="CRM/common/status.tpl"}

      <!-- .tpl file invoked: {$tplFile}. Call via form.tpl if we have a form in the page. -->
      <br style="clear: all;"/> 
      {if $isForm}
        {include file="CRM/Form/$formTpl.tpl"}
      {else}
        {include file=$tplFile}
      {/if}

      {if ! $urlIsPublic}	
      {include file="CRM/common/footer.tpl"}
      {/if}
	
    </td>

  </tr>
</table>

{* We need to set jquery $ object back to $*}
<script type="text/javascript">jQuery.noConflict(true);</script>

</div> {* end crm-container div *}
</body>
</html>
