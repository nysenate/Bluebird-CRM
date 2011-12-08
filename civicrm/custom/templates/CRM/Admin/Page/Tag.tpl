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
	callTagAjaxInitLoader('#crm-tagListWrap .BBtree.edit');
	callTagAjax();
});
function makeModalMerge(tagLabel){
	tagInfo = new Object();
	tagInfo.id = tagLabel;
	tagInfo.name = cj('.BBtree.edit dt#' + tagLabel + ' .tag').html();
	tagInfo.tid = cj('.BBtree.edit dt#' + tagLabel).attr('tid');
	cj("#dialog").show( );
	cj("#dialog").dialog({
		draggable: false,
		height: 300,
		width: 300,
		title: "Merge Tag",
		modal: true, 
		bgiframe: true,
		close: function(event, ui) { cj("#tag_name_modal").unautocomplete( ); },
		overlay: { 
			opacity: 0.2, 
			background: "black" 
		},
		open: function() {
			var updateDialogInfo = '';
			updateDialogInfo += '<div class="modalHeader">Merge Tag ' + tagInfo.name + ' Into:</div>';
			updateDialogInfo += '<input id="tag_name_modal" class="ac_input" type="text" autocomplete="off">';
			updateDialogInfo += '<input id="tag_name_id" type="hidden" value="">';
			cj('#dialog').html(updateDialogInfo);
			cj("#tag_name_modal").val( "" );
			cj("#tag_name_id").val( null );

			var tagUrl = {/literal}"{crmURL p='civicrm/ajax/mergeTagList' h=0}"{literal};
			tagUrl = tagUrl + "&fromId=" + tagInfo.tid;

			cj("#tag_name_modal").autocomplete( tagUrl, {
				width: 260,
				selectFirst: false,
				matchContains: true 
			});

			cj("#tag_name_modal").focus();
			cj("#tag_name_modal").result(function(event, data, formatted) {
				cj("input[id=tag_name_id]").val(data[1]);
				if ( data[2] == 1 ) {
				    cj('#used_for_warning').html("Warning: '" + fromTag + "' has different used-for options than the selected tag, which would be merged into the selected tag. Click Ok to proceed.");
				} else {
				    cj('#used_for_warning').html('');
				}
			});	
		},
		buttons: {
			"Ok": function() { 	    
				if ( ! cj("#tag_name_modal").val( ) ) {
					alert('{/literal}{ts escape="js"}Select valid tag from the list{/ts}{literal}.');
					return false;
				}
				var toId = cj("#tag_name_id").val( );
				if ( ! toId ) {
					alert('{/literal}{ts escape="js"}Select valid tag from the list{/ts}{literal}.');
					return false;
				}
				/* send synchronous request so that disabling any actions for slow servers*/
				var postUrl = {/literal}"{crmURL p='civicrm/ajax/mergeTags' h=0 }"{literal}; 
				var data    = 'fromId='+ tagInfo.tid + '&toId='+ toId + "&key={/literal}{crmKey name='civicrm/ajax/mergeTags'}{literal}";
             			cj.ajax({ type     : "POST", 
					url      : postUrl, 
					data     : data, 
					dataType : "json",
					success  : function( values ) {
						if ( values.status == true ) {
							cj('.crm-content-block #help').after('<div class="contactTagsList help" id="tagStatusBar"></div>');
							var toIdTag = cj('#tagLabel_' + toId).attr('description');
							var msg = "<ul style=\"margin: 0 1.5em\"><li>'" + tagInfo.name + "' has been merged with '" + toIdTag + "'. All records previously tagged with '" + tagInfo.name + "' are now tagged with '" + toIdTag + "'.</li></ul>";
							cj('#tagLabel_' + tagInfo.tid).html(''); 
							cj('#tagStatusBar').html(msg);
						}
						callTagAjax();
                      			}
                		});
                		cj(this).dialog("close"); 
				cj(this).dialog("destroy");
 			},
			"Cancel": function() { 

				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			}
		} 
	});
}
/*adds the control box to admin/page to +/-/->/i/? based on a set of conditions*/
function addControlBox(tagLabel, IDChecked, treeTop) {
	var floatControlBox = '';
	var tagMouse = 'dt#'+tagLabel;
	floatControlBox = '<span class="fCB" style="padding:1px 0;float:right;">';
	floatControlBox += '<ul>';
	if(treeTop == '291')
	{

		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; float:left;" title="Add New Tag" onclick="makeModalAdd(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -17px 0px; float:left;" title="Remove Tag" onclick="makeModalRemove(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -34px 0px; float:left;" title="Move Tag" onclick="makeModalTree(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -50px 0px; float:left;" title="Update Tag" onclick="makeModalUpdate(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -66px 0px; float:left;" title="Merge Tag" onclick="makeModalMerge(\''+ tagLabel +'\')"></li>';
	}
	if(treeTop == '296')
	{
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -17px 0px; float:left;" title="Remove Tag" onclick="makeModalRemove(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -50px 0px; float:left;" title="Update Tag" onclick="makeModalUpdate(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -66px 0px; float:left;" title="Merge Tag" onclick="makeModalMerge(\''+ tagLabel +'\')"></li>';
	}
	floatControlBox += '</span>';
	if(tagMouse == 'dt#tagLabel_291')
	{
		return '<span class="fCB" style="padding:1px 0;float:right;"><ul><li style="height:16px; width:16px; margin:auto 1px; float:left;" title="Add New Tag" onclick="makeModalAdd(\''+ tagLabel +'\')"></li></ul></span>'; 
	}else if(tagMouse == 'dt#tagLabel_296')
	{
		return '<span class="fCB" style="padding:1px 0;float:right;"><ul><li style="height:16px; width:16px; margin:auto 1px; float:left;" title="Add New Tag" onclick="makeModalAdd(\''+ tagLabel +'\')"></li></ul></span>'; 
	} else { return(floatControlBox); }
}
/*Function for checking and unchecking tags and updating the server on it's request*/
function checkRemoveAdd(tagLabel) {
	var n = cj('.BBtree.edit dt#'+ tagLabel).hasClass('checked');
	tagLabelID = tagLabel.replace('tagLabel_', '');
	if(n == false)
	{
		cj.ajax({
			url: '/civicrm/ajax/entity_tag/create',
			data: {
				entity_type: 'civicrm_contact',
				entity_id: cid,
				tag_id: tagLabelID
				},
			dataType: 'json',
			success: function(data, status, XMLHttpRequest) {
				if(data.code != 1) {alert('fails');}
				cj('.BBtree.edit dt#'+tagLabel).addClass('checked');
				giveParentsIndicator(tagLabel,'add');
			}
		});
		
	} else {
		cj.ajax({
			url: '/civicrm/ajax/entity_tag/delete',
			data: {
				entity_type: 'civicrm_contact',
				entity_id: cid,
				tag_id: tagLabelID
				},
			dataType: 'json',
			success: function(data, status, XMLHttpRequest) {
				if(data.code != 1) {alert('fails');}
				findIDLv(tagLabel);
			}
		});
	}
}
/*Checks each parent tag, and it's siblings to see if it can be unmarked as a hereditary choice*/
function findIDLv(tagLabel) {
	var idLv = cj('dt#'+tagLabel).attr('class').split(' ');
	if(idLv.length > 0)
	{
		for(var i = 0; i < idLv.length; i++){
			var checkForLv = idLv[i].search('lv\-.*');
			if(checkForLv >= 0)
			{
				var tagLv = idLv[i].replace('lv\-','');
				break;
			}
			else
			{
				alert('Error During Untagging');
			}
			
		}
	}
	var tagLvLabel = tagLabel;
	for(tagLv; tagLv >= 0; tagLv--){
		var findSibMatch = 0;
		findSibMatch += cj('dt#'+tagLvLabel).siblings('.subChecked').length;
		findSibMatch += cj('dt#'+tagLvLabel).siblings('.checked').length;
		if(findSibMatch == 0){
			tagLvLabel = cj('dt#'+tagLvLabel).parent().attr('id');
			cj('dt#'+tagLvLabel).removeClass('checked');
			cj('dt#'+tagLvLabel).removeClass('subChecked');
			break;
		}
		else{ break;}
	}
	cj('dt#'+tagLabel).removeClass('checked');
}
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
