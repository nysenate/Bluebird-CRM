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
{literal}
<script src="/sites/default/themes/Bluebird/scripts/bbtree.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/nyss_skin/tags/tags.css" />
<style>
.crm-tagListInfo {
    padding:15px;
    float:left;
    width:370px;
}
.tagInfoBody {
    margin-top:15px;
}
.tagInfoBody div {
    margin-top:2px;
    line-height:24px;
}
.crm-tagTreeDisplay {
    float:right;
}
#BBTreeContainer .BBTree {
    border:0;
    border-left:1px solid #ccc;
}
.container #status {
    display:none;
}
</style>
{/literal}
{literal}
<script type="text/javascript">
BBTree.startInstance({pullSets: [291, 296], buttonType: 'edit'}, 1); 
</script>
{/literal}
{capture assign=docLink}{docURL page="Tags Admin"}{/capture}
{if $action eq 1 or $action eq 2 or $action eq 8}
    {include file="CRM/Admin/Form/Tag.tpl"}	
{else}
<div class="crm-content-block">
    <div id="help">
        {ts 1=$docLink}Tags can be assigned to any contact record, and are a convenient way to find contacts. You can create as many tags as needed to organize and segment your records.{/ts} {*$docLink*}{*NYSS 6163*}
    </div>
    <div id="dialog">
    </div>
    <div class="crm-tagLegend">
    	<table>
    		<tr>
    			<th>Legend</th>
    		</tr>
    		<tr>
    			<td class="addTag"><div></div>Add Tag</td>
    			<td class="removeTag"><div></div>Remove Tag</td>
    			<td class="mergeTag"><div></div>Merge Tag</td>
    			<td class="convertTag"><div></div>Convert Tag</td>
    		</tr>
    		<tr>
    			<td class="updateTag"><div></div>Update Tag</td>
    			<td class="moveTag"><div></div>Move Tag</td>
    			<td class="printTag"><div></div>Print Tags</td>
    		</tr>
    	</table>
    </div>
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
				<div class="tagCount">Records with this Tag: <span></span></div>
			</div>
        </div>
        <!-- goes here -->
        <div class="crm-tagTreeDisplay">
            <div class="BBInit"></div>
            {literal}
            <script>
                BBTree.initContainer('', {pullSets: [291,296], buttonType: 'edit',tabLocation: 'crm-tagTabHeader'});
                //BBTree.initContainer('two', {pullSets: [296], buttonType: 'tagging'}, {entity_id: 216352, entity_type: 'civicrm_activity'});
            </script>
            {/literal}
        </div>
    </div>
</div>
{/if}
