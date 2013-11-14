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
{literal}
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/nyss_skin/tags/tags.css" />
{/literal}
{capture assign=docLink}{docURL page="user/organising-your-data/groups-and-tags"}{/capture}

{if $action eq 1 or $action eq 2 or $action eq 8}
    {include file="CRM/Admin/Form/Tag.tpl"}
{else}
<div class="crm-content-block">
    <div id="help">
        {ts 1=$docLink}Tags can be assigned to any contact record, and are a convenient way to find contacts. You can create as many tags as needed to organize and segment your records.{/ts} {*$docLink*}{*NYSS 6163*}
    </div>
  <div id="dialog">
  </div>
  <div class="JSTreeInit"></div>
  {literal}
  <script>
    var jsTreePageSettings = {
      pageElements: {
        wrapper: ['BBTreeContainer'],
        tagHolder: ['BBTree'],
        prefix: ['BBtree']
      },
      displaySettings: {
        wide:true,
        // lock:false
        edit:true
        // tagging:true
      },
    }
    jstree.init(jsTreePageSettings, jstree.views);
  </script>
  {/literal}
</div>
{/if}
{include file="CRM/common/crmeditable.tpl"}
