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
{if $config->debug}
{include file="CRM/common/debug.tpl"}
{/if}

{*NYSS 5581 include unless explicity disabled*}
{if !$disableBBheader}
  {include file="Custom/header.tpl"}
  <div class="clear"></div>
{/if}

<div id="crm-container" class="crm-container{if $urlIsPublic} crm-public{/if}" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">

{*NYSS remove nav bar*}
{*crmNavigationMenu is_default=1*}

<div class="crm-title">
	<h1 class="title">
    {if $tplFile eq 'CRM/Contact/Page/View/Summary.tpl'}
      {php}
      //NYSS 2724 TODO: look at more complete solution to long titles overlapping action buttons
      $title = drupal_get_title();
      $strippedtitlelen = strlen(str_replace(' (deceased)', '', strip_tags($title)));
      $titlelen = strlen($title);

      if( $strippedtitlelen > 25 ) {
        $shorttitle = str_replace(' <span class="crm-contact-deceased">(deceased)</span>', '', $title);
        $shorttitle = substr($shorttitle, 0, $titlelen-($strippedtitlelen-20));
        print trim($shorttitle).'...';
      }
      else {
        print str_replace(' <span class="crm-contact-deceased">(deceased)</span>', '', $title);
      }
      {/php}
    {/if}
  </h1>
</div>

{crmRegion name='page-header'}
{/crmRegion}
<div class="clear"></div>

{if isset($localTasks) and $localTasks}
    {include file="CRM/common/localNav.tpl"}
{/if}
<div id="crm-main-content-wrapper">
  {include file="CRM/common/status.tpl"}
  {crmRegion name='page-body'}
    {if isset($isForm) and $isForm and isset($formTpl)}
      {include file="CRM/Form/$formTpl.tpl"}
    {else}
      {include file=$tplFile}
    {/if}
  {/crmRegion}
</div>

{crmRegion name='page-footer'}
<div id="crm-seal"></div>{*NYSS*}

{if $urlIsPublic}
  {include file="CRM/common/publicFooter.tpl"}
{else}
  {include file="CRM/common/footer.tpl"}
{/if}
{/crmRegion}


</div> {* end crm-container div *}
