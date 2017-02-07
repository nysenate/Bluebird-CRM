{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
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

{if not $urlIsPublic}
<div id="header-access">
  {ts}Access Keys:{/ts}{help id='accesskeys'}
</div>
{/if}

{php}
global $user;
$rolesList = implode('',$user->roles);
$role = str_replace('authenticated user','', $rolesList);
{/php}

{*qfKey built in civihooks.module*}
{$quickSearch_qfKey}

<div class="civi-search-section">

  <div class="civi-contact-search">
    <div class="civi-search-title">Find Contacts</div>
    <div class="input-wrapper">
      {include file="Custom/quickSearch.tpl"}
    </div>
  </div>

  <div class="civi-general-search">
    <div class="civi-search-title">Find Anything!</div>
    {if call_user_func(array('CRM_Core_Permission','giveMeAllACLs'))}
      <form id="id_search_block" name="findAnything" method="get" action="/civicrm/contact/search/custom">
        <div class="input-wrapper" id="gen-search-wrapper">
          <input type="text" class="form-text" id="civi_text_search" name="text" value="enter any text" style="width:193px;">
          <input type="hidden" name="force" value="true">
          <input type="hidden" name="csid" value="15">
          <input type="submit" value="Go" name="_qf_Custom_refresh" class="form-submit default tgif">
        </div>
      </form>
    {/if}
  </div>

  <span class="primary-link create-link">
    <span id="create-link" class="main-menu-item">
      <div class="skin-icon link-icon"></div>
      CREATE
    </span>
    <div class="menu-container">
      <ul class="menu-ul innerbox">
        {if call_user_func(array('CRM_Core_Permission','check'), 'view all activities')}
          <li><div class="menu-item">
          <a href="{crmURL p='civicrm/activity' q='reset=1&action=add&context=standalone'}">New Activity</a></div></li>
        {/if}
        {if call_user_func(array('CRM_Core_Permission','check'), 'access all cases and activities')}
          <li><div class="menu-item">
          <a href="{crmURL p='civicrm/case/add' q='reset=1&action=add&atype=13&context=standalone'}">New Case</a></div></li>
        {/if}
        {if call_user_func(array('CRM_Core_Permission','check'), 'add contacts')}
          <li><div class="menu-item">
          <a href="{crmURL p='civicrm/activity/email/add' q='atype=3&action=add&reset=1&context=standalone'}">New Email</a></div></li>
        {/if}
        {if call_user_func(array('CRM_Core_Permission','check'), 'add contacts')}
          <li style="position: relative;" class="menu-separator"><div class="menu-item"></div></li>
          <li><div class="menu-item">
          <a href="{crmURL p='civicrm/contact/add' q='reset=1&ct=Individual'}">New Individual</a></div></li>
          <li><div class="menu-item">
          <a href="{crmURL p='civicrm/contact/add' q='reset=1&ct=Household'}">New Household</a></div></li>
          <li><div class="menu-item">
          <a href="{crmURL p='civicrm/contact/add' q='reset=1&ct=Organization'}">New Organization</a></div></li>
        {/if}
        {if call_user_func(array('CRM_Core_Permission','check'), 'edit groups')}
          <li style="position: relative;" class="menu-separator"><div class="menu-item"></div></li>
          <li><div class="menu-item">
          <a href="{crmURL p='civicrm/group/add' q='reset=1'}">New Group</a></div></li>
        {/if}
      </ul>
    </div>
  </span><!-- /.custom-search-link -->
</div>

<div id="bluebirds"></div>
<div class="clear"></div>
<div class="civi-navigation-section crm-container">
  <div class="civi-menu">
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
        <a href="{$printerFriendly}" title="{ts}Printer-friendly view of this page.{/ts}" target="_blank">
          <div class="ui-icon ui-icon-print"></div>
        </a>
      </div>
    {/if}
    <ul id="nyss-menu">
      {$navigation}
    </ul>

    {literal}
      <script type="text/javascript">
      //show/hide submenus on click
      cj('#nyss-menu').ready(function() {
        cj("#nyss-menu > li:has(ul)").click(function () {
          cj("#nyss-menu > li:has(ul) ul").hide();
          cj("#nyss-menu > li:has(ul)").removeClass('selected');

          cj(this).children('ul').show();
          cj(this).addClass('selected');
        });
      });

      //hide submenus on clicking away from menu
      cj(document).mouseup(function (e) {
        if (!cj("#nyss-menu").is(e.target)
          && cj("#nyss-menu").has(e.target).length === 0)
        {
          cj("#nyss-menu > li:has(ul) ul").hide();
          cj("#nyss-menu > li:has(ul)").removeClass('selected');
        }
      });
      </script>
    {/literal}
  </div><!-- /.civi-menu -->
</div>
