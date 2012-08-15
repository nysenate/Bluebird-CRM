{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/rayCivicrm/nyss_skin/tags.css" />
<script src="/sites/default/themes/rayCivicrm/scripts/bbtree.js" type="text/javascript"></script>
<style>
.crm-tagTabHeader {height:15px;}
.crm-tagTabHeader li {float:left;margin-right:15px;background: transparent url(/sites/default/themes/rayCivicrm/nyss_skin/images/button.png) no-repeat scroll right -30px!important; list-style: none; width:135px; color:#fff; text-align:center;cursor:pointer;}
.crm-tagTabHeader li:hover {color:#ccc;border-top:#457AA4 3px solid; margin-top:-3px;}
#crm-container #crm-tagListWrap {clear:both;}
.BBtree.edit.manage {float:right; border-left:1px solid #ccc;}
</style>
{/literal}
{literal}
<script type="text/javascript">
cj(document).ready(function() {	
	callTagAjax('.BBtree.edit.manage', '2', '291');
});
</script>
{/literal}
{capture assign=docLink}{docURL page="Tags Admin"}{/capture}
{if $action eq 1 or $action eq 2 or $action eq 8}
    {include file="CRM/Admin/Form/Tag.tpl"}	
{else}
<div class="crm-content-block">
    <div id="help">
        {ts 1=$docLink}Tags can be assigned to any contact record, and are a convenient way to find contacts. You can create as many tags as needed to organize and segment your records.{/ts} {$docLink}
    </div>
        <div id="dialog"></div>
	<div class="crm-tagTabHeader">
		<ul>
		</ul>
	</div>
	
	<div id="crm-tagListWrap">
	    
	    <div class="crm-tagListInfo">
		<h1 class="header title">Tag Info</h1>
		<div class="tagInfoBody">
			<div class="tagName">Tag Name: <span></span></div>
			<div class="tagId">Tag ID: <span></span></div>
			<div class="tagDescription">Tag Description: <span></span></div>
			<div class="tagReserved">Reserved: <span></span></div>
			<!--<div class="tagCount">Records with this Tag: <span></span></div>-->
		</div>
            </div>
            <div class="BBtree edit manage">
	    
	    </div>
            <div class="crm-tagListSwapArea" tid="0" style="display:none;"></div>
        </div>
        
</div>

{/if}
