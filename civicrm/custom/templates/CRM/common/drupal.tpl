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
{if $config->debug}
{include file="CRM/common/debug.tpl"}
{/if}

{*NYSS*}
{include file="Custom/header.tpl"}
<div class="clear"></div>

<div id="crm-container" class="crm-container{if $urlIsPublic} crm-public{/if}" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">

{include file="CRM/common/action.tpl"}
{*NYSS remove nav*}

{* temporary hack to fix wysiysg editor failure if js compression is on *}
{if $defaultWysiwygEditor eq 1}
  <script type="text/javascript" src="{$config->resourceBase}packages/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
  <script type="text/javascript" src="{$config->resourceBase}packages/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
{elseif $defaultWysiwygEditor eq 2}
  <script type="text/javascript" src="{$config->resourceBase}packages/ckeditor/ckeditor.js"></script>
{/if}

<div class="crm-title">
	<h1 class="title">
    {if $isDeleted}<del>{/if}
    {if $tplFile eq 'CRM/Contact/Page/View/Summary.tpl'}
      {php}
      //NYSS 2724 TODO: look at more complete solution to long titles overlapping action buttons
      $title = drupal_get_title();
      $strippedtitlelen = strlen(strip_tags($title));
      $titlelen = strlen($title);

      if( $strippedtitlelen > 28 ) {
        $shorttitle = substr( $title, 0, $titlelen-($strippedtitlelen-25));
        print $shorttitle.'...';
      }
      else {
        print $title;
      }
      {/php}
    {else}
      {php}
        print str_replace('CiviCRM', 'Bluebird', drupal_get_title());
      {/php}
    {/if}
    {if $isDeleted}</del>{/if}
    </h1>
</div>

{crmRegion name='page-header'}
{/crmRegion}

{*{include file="CRM/common/langSwitch.tpl"}*}


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
<div id="crm-seal"></div>

{if ! $urlIsPublic}
{include file="CRM/common/footer.tpl"}
{/if}
{/crmRegion}


{literal}
<script type="text/javascript">
cj(function() {
  cj().crmtooltip();
  cj().crmAccordions();
});
</script>
{/literal}
</div> {* end crm-container div *}
