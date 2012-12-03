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
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/nyss_skin/tags.css" />
<script src="/sites/default/themes/Bluebird/scripts/bbtree.js" type="text/javascript"></script>
<style>
.crm-tagTabHeader {height:15px; clear:both;}
.crm-tagTabHeader li, .crm-tagTabHeader li#tagLabel_291, .crm-tagTabHeader li#tagLabel_296 {float:left;margin-right:15px;background: transparent url(/sites/default/themes/Bluebird/nyss_skin/images/button.png) no-repeat scroll right -30px!important; list-style: none; width:135px; color:#fff; text-align:center;cursor:pointer; font-size:12px;}
.crm-tagTabHeader li:hover, .crm-tagTabHeader li#tagLabel_291:hover, .crm-tagTabHeader li#tagLabel_296:hover {color:#ccc;border-top:#457AA4 3px solid; margin-top:-3px;}
.crm-tagTabHeader li.active, .crm-tagTabHeader li#tagLabel_291.active, .crm-tagTabHeader li#tagLabel_296.active {border-top:#457AA4 3px solid; margin-top:-3px; font-size: 14px;}
#crm-container #crm-tagListWrap {clear:both;}
.BBtree.edit.manage {float:right; border-left:1px solid #ccc;}
.crm-tagLegend td div {
	background: url('/sites/default/themes/Bluebird/nyss_skin/images/fcb.png') transparent no-repeat;
	height:16px;
	width:16px;
	float:left;
	margin:0 10px 0 0;
}
.lv-0#tagLabel_291, .lv-0#tagLabel_296{
	font-size: 14px;
}
.crm-tagLegend td.addTag div {}
.crm-tagLegend td.removeTag div {background-position: -17px 0px;}
.crm-tagLegend td.moveTag div {background-position: -34px 0px; }
.crm-tagLegend td.updateTag div {background-position: -50px 0px;}
.crm-tagLegend td.mergeTag div {background-position: -66px 0px;}
.crm-tagLegend td.convertTag div {background-position: -107px 0px;}
.crm-tagLegend td.printTag div, #crm-container .BBtree dt.lv-0 .fCB ul li.printTag {

	background-image: url('/sites/default/themes/Bluebird/nyss_skin/images/icons-3e3e3e.png');
	background-position: -160px -96px;
}
dt.lv-0 .fCB ul li.printTag {
	height:16px;
	width:16px;
}
#crm-container .crm-tagLegend th {border: 0px;}
#crm-container .crm-tagLegend * {border:0px;}
@media print {
  body * {
    visibility:hidden;
  }
  #BBtree.edit.manage, #BBtree.edit.manage * {
    visibility:visible;
  }
  #BBtree.edit.manage {
    position:absolute;
    left:0;
    top:0;
  }
}
</style>
{/literal}
{literal}
<script type="text/javascript">
cid = 0;
cj(document).ready(function() {	

	callTagAjaxInitLoader('#crm-tagListWrap .BBtree.edit', 'init');
	callTagAjax();
});
function makeModalTree(tagLabel){
	//big warning: you can move tags deeper than you can see them, upgrade the depth asap.
	cj("#dialog").show( );
	cj("#dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		height: 500,
		width: 600,
		title: "Move Tag",
		modal: true, 
		bgiframe: true,
		close:{ },
		overlay: { 
			opacity: 0.2, 
			background: "black" 
		},
		open: function() {
			tagInfo = new Object();
			tagInfo.id = tagLabel;
			tagInfo.name = cj('.BBtree.edit.manage dt#' + tagLabel + ' .tag .name').html();
			tagInfo.number = cj('.BBtree.edit.manage dt#' + tagLabel).attr('tID');
			tagInfo.reserved = cj('.BBtree.edit.manage dt#'+tagLabel).hasClass('isReserved');
			

			
			var treeDialogInfo;
			if(tagInfo.reserved == true){ //if reserved
			treeDialogInfo = '<div class="modalHeader">This tag is reserved and cannot be moved</div>';
			cj('#dialog').html(treeDialogInfo);
			} 
			else {
			treeDialogInfo = '<div class="modalHeader">Move <span id="modalNameTid" tID="'+tagInfo.id+'">' + tagInfo.name + ' under Tag...</span></div>';
			treeDialogInfo += '<div class="BBtree modal move"></div>';
			cj('#dialog').html(treeDialogInfo);
			var modalTreeTop = cj('.BBtree.edit.manage dt#' + tagLabel).parents('.lv-0').children('.lv-0').attr('tid');
			
			callTagAjax('modal', modalTreeTop);

			}
		},
		buttons: {
			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy");
			}
		} 
	});
}
/*this is the second portion of the Modal box, where it takes the click function and makes a 'move' button*/
function modalSelectOnClick() {
	cj('.BBtree.modal input.selectRadio, .BBtree.modal div.tag').unbind('click');
	cj('.BBtree.modal input.selectRadio, .BBtree.modal div.tag').click(function(){
		var tagLabel = cj('.ui-dialog-content .modalHeader span').attr('tID');
		//search the string for all mentions of tid="number"
		var listOfChildTids = '';
		var tagChildren = cj('.BBtree.edit.manage dl#'+ tagLabel).html();
		if(tagChildren != null)
		{
			listOfChildTids += tagChildren.match(/tid=\"[0-9]*\"/g);
			for(i = 0;i<listOfChildTids.length;i++)
			{
				listOfChildTids[i] = listOfChildTids[i].replace("tid=\"",'');
				listOfChildTids[i] = listOfChildTids[i].replace("\"",'');
			}
		}
		if(cj('.BBtree.modal').hasClass('move'))
		{
			var destinationId = cj(this).parent().parent('dt').attr('tid');
			cj("#dialog").dialog( "option", "buttons", [
				{
					text: "Move",
					click: function() {
						cj('.ui-dialog-buttonset .ui-button').css("visibility", "hidden");
						cj('.ui-dialog-buttonpane').append('<div class="loadingGif"></div>');
						modalSetLoadingGif();
						tagMove = new Object();
						tagMove.currentId = cj('.ui-dialog-content .modalHeader span').attr('tID').replace('tagLabel_','');
						tagMove.destinationId = destinationId;
						var tidMatch = false;
						if(tagChildren != null)
						{
							for(i = 0;i<listOfChildTids.length;i++)
							{
								if((listOfChildTids[i] == tagMove.destinationId) || (tagMove.currentId == tagMove.destinationId))
								{
									tidMatch = true;
								}
							}
						}
						if(tidMatch == false)
						{	
							cj.ajax({
								url: '/civicrm/ajax/tag/update',
								data: {
									id: tagMove.currentId,
									parent_id: tagMove.destinationId,
									call_uri: window.location.href
								},
								dataType: 'json',
								success: function(data, status, XMLHttpRequest) {
									if(data.code != 1)
									{
										alert(data.message);
										cj('.ui-dialog-buttonpane .loadingGif').hide();
										cj('.ui-dialog-buttonset .ui-button').css("visibility", "visible");
										modalRemoveLoadingGif();
									}
									cj('#dialog').dialog('close');
									cj('#dialog').dialog('destroy');
									callTagAjax();
								}
							});
						}
						else{
							alert("Cannot move a parent under a child.");
							cj('.ui-dialog-buttonpane .loadingGif').hide();
							cj('.ui-dialog-buttonset .ui-button').css("visibility", "visible");
							modalRemoveLoadingGif();
						}

					}
				},
				{
					text: "Cancel",
					click: function() { 
						cj(this).dialog("close"); 
						cj(this).dialog("destroy"); 
					}
				}
			]);
		}
		if(cj('.BBtree.modal').hasClass('merge'))
		{
			tagLabel = cj('.modalHeader span').attr('tid');
			tagInfo = new Object();
			tagInfo.id = tagLabel;
			tagInfo.name = cj('.BBtree.manage dt#' + tagLabel + ' .tag .name').html();
			tagInfo.tid = cj('.BBtree.manage dt#' + tagLabel).attr('tid');
			var destinationId = cj(this).parent().parent('dt').attr('tid');
			cj("#dialog").dialog( "option", "buttons", [
				{
					text: "Merge ",
					click: function() {
						tagMerge = new Object();
						cj('.ui-dialog-buttonset .ui-button').css("visibility", "hidden");
						cj('.ui-dialog-buttonpane').append('<div class="loadingGif"></div>');
						modalSetLoadingGif();
						tagMerge.currentId = cj('.ui-dialog-content .modalHeader span').attr('tID').replace('tagLabel_','');
						tagMerge.destinationId = destinationId;
						var postUrl = {/literal}"{crmURL p='civicrm/ajax/mergeTags' h=0 }"{literal}; 
		 				var data    = 'fromId='+ tagMerge.currentId + '&toId='+ tagMerge.destinationId + "&key={/literal}{crmKey 	name='civicrm/ajax/mergeTags'}{literal}";
		 				var tidMatch = false;
		 				
		 				// var listOfToChildTids = '';
						// var toTagList = cj('.BBtree.edit.manage dl#tagLabel_'+ tagMerge.destinationId).html();
						// if(toTagList != null)
						// {
							
						// 	listOfToChildTids += toTagList.match(/tid=\"[0-9]*\"/g);
						// 	console.log(listOfToChildTids);
						// 	for(i = 0;i<listOfToChildTids.length;i++)
						// 	{
						// 		listOfToChildTids[i] = listOfToChildTids[i].replace("tid=\"",'');
						// 		listOfToChildTids[i] = listOfToChildTids[i].replace("\"",'');
						// 	}
						// 	var isListArray = cj.isArray(listOfToChildTids);
						// 	if((listOfChildTids.length > 0  && listOfToChildTids.length > 0) || !(isListArray))
				 		// 	{
				 		// 		tidMatch = true;
				 		// 	}
						// }
						//console.log(tagChildren);
						if(tagChildren != null)
						{
							tidMatch = true;
						}
			 			//console.log(tidMatch);
						if(tidMatch == false)
						{	
							cj.ajax({
								type     : "POST",
								url: postUrl,
								data: data,
								dataType: 'json',
								success: function(data, status, XMLHttpRequest) {
									if ( data.status == true ) {
										cj("#dialog").dialog("close"); 
										cj("#dialog").dialog("destroy"); 
										callTagAjax();
										if(cj('.contactTagsList.help').length < 1)
										{
											cj('.crm-content-block #help').after('<div class="contactTagsList help" id="tagStatusBar"></div>');
										}
										var toIdTag = cj('#tagLabel_' + tagMerge.destinationId + ' .tag .name').html();
										var msg = "<ul style=\"margin: 0 1.5em\"><li>'" + tagInfo.name + "' has been merged with '" + toIdTag + "'. All records previously tagged with '" + tagInfo.name + "' are now tagged with '" + toIdTag + "'.</li></ul>";
										cj('#tagLabel_' + tagInfo.tid).html(''); 
										cj('#tagStatusBar').html(msg);
									}
									else
									{
										cj('.ui-dialog-buttonpane .loadingGif').hide();
										cj('.ui-dialog-buttonset .ui-button').css("visibility", "visible");
										modalRemoveLoadingGif();
									}
									
								}	
							});
						}
						else {
							alert("Cannot merge a parent tag into another tag. Try moving sub-tags into the parent you want to merge into and then merge the tag into the destination");
							cj('.ui-dialog-buttonpane .loadingGif').hide();
							cj('.ui-dialog-buttonset .ui-button').css("visibility", "visible");
							modalRemoveLoadingGif();
						}

					}
				},
				{
					text: "Cancel",
					click: function() { 
						cj(this).dialog("close"); 
						cj(this).dialog("destroy"); 
					}
				}
			]);
		}
	});
}
function makeModalConvert(tagLabel){
	cj("#dialog").show( );
	cj("#dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		height: 300,
		width: 300,
		title: "Convert Keyword to Tag",
		modal: true, 
		bgiframe: true,
		close:{ },
		overlay: { 
			opacity: 0.2, 
			background: "black" 
		},
		open: function() {
			tagInfo = new Object();
			tagInfo.id = tagLabel;
			//console.log(tagInfo.id);
			tagInfo.name = cj('.BBtree.edit.manage dt#' + tagLabel + ' .tag .name').html();
			tagInfo.reserved = cj('.BBtree.edit.manage dt#'+tagLabel).hasClass('isReserved');
			var treeDialogInfo;
			if(tagInfo.reserved == true){
			treeDialogInfo = '<div class="modalHeader">This tag is reserved and cannot be converted</div>';
			cj('#dialog').html(treeDialogInfo);
			} else {
			treeDialogInfo = '<div class="modalHeader">Convert <span id="modalNameTid" tID="'+tagInfo.id+'">' + tagInfo.name + '</span> into an Issue Code.</div>';
			cj('#dialog').html(treeDialogInfo);
			var modalTreeTop = cj('.BBtree.edit.manage dt#' + tagLabel).parents('.lv-0').children('.lv-0').attr('tid');
			}
		},
		buttons: {
			"Convert": function() {
				cj('.ui-dialog-buttonset .ui-button').css("visibility", "hidden");
				cj('.ui-dialog-buttonpane').append('<div class="loadingGif"></div>');
				modalSetLoadingGif();
				tagMove = new Object();
				tagMove.currentId = cj('.ui-dialog-content .modalHeader span').attr('tID').replace('tagLabel_','');
				cj.ajax({
					url: '/civicrm/ajax/tag/update',
					data: {
						id: tagMove.currentId,
						parent_id: 291,
						call_uri: window.location.href
					},
					dataType: 'json',
					success: function(data, status, XMLHttpRequest) {
						if(data.code != 1)
						{
							alert(data.message);
							cj('.ui-dialog-buttonpane .loadingGif').hide();
							cj('.ui-dialog-buttonset .ui-button').css("visibility", "block");
							modalRemoveLoadingGif();
						}
						else
						{
							cj('#dialog').dialog('close');
							cj('#dialog').dialog('destroy');
							callTagAjax();
						}
					}
				});
			},
			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy");
			}
		} 
	});
}
function makeModalMerge(tagLabel){
	cj("#dialog").show( );
	cj("#dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		height: 500,
		width: 600,
		title: "Merge Tag",
		modal: true, 
		bgiframe: true,
		close:{ },
		overlay: { 
			opacity: 0.2, 
			background: "black" 
		},
		open: function() {
			tagInfo = new Object();
			tagInfo.id = tagLabel;
			tagInfo.name = cj('.BBtree.edit.manage dt#' + tagLabel + ' .tag .name').html();
			tagInfo.reserved = cj('.BBtree.edit.manage dt#'+tagLabel).hasClass('isReserved');
			var treeDialogInfo;
			if(tagInfo.reserved == true){
			treeDialogInfo = '<div class="modalHeader">This tag is reserved and cannot be merged</div>';
			cj('#dialog').html(treeDialogInfo);
			} else {
			treeDialogInfo = '<div class="modalHeader">Merge <span id="modalNameTid" tID="'+tagInfo.id+'">' + tagInfo.name + '</span> into Selected Tag...</div>';
			treeDialogInfo += '<div class="BBtree modal merge loadingGif"></div>';
			cj('#dialog').html(treeDialogInfo);
			var modalTreeTop = cj('.BBtree.edit.manage dt#' + tagLabel).parents('.lv-0').children('.lv-0').attr('tid');
			callTagAjax('modal', modalTreeTop);
			}
		},
		buttons: {
			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy");
			}
		} 
	});
}
function makeModalKWMerge(tagLabel){
	cj("#dialog").show( );
	cj("#dialog").dialog({
		closeOnEscape: true,
		draggable: true,
		height: 500,
		width: 600,
		title: "Merge Keyword into Tag",
		modal: true, 
		bgiframe: true,
		close:{ },
		overlay: { 
			opacity: 0.2, 
			background: "black" 
		},
		open: function() {
			tagInfo = new Object();
			tagInfo.id = tagLabel;
			tagInfo.name = cj('.BBtree.edit.manage dt#' + tagLabel + ' .tag .name').html();
			tagInfo.reserved = cj('.BBtree.edit.manage dt#'+tagLabel).hasClass('isReserved');
			var treeDialogInfo;
			if(tagInfo.reserved == true){
			treeDialogInfo = '<div class="modalHeader">This tag is reserved and cannot be merged</div>';
			cj('#dialog').html(treeDialogInfo);
			} else {
			treeDialogInfo = '<div class="modalHeader">Merge <span id="modalNameTid" tID="'+tagInfo.id+'">' + tagInfo.name + '</span> into Selected Tag...</div>';
			treeDialogInfo += '<div class="BBtree modal merge loadingGif"></div>';
			cj('#dialog').html(treeDialogInfo);
			var modalTreeTop = cj('.BBtree.edit.manage dt#' + tagLabel).parents('.lv-0').children('.lv-0').attr('tid');
			var keywordTreePull = cj('dl#tagLabel_296').html();
			cj('.BBtree.modal.merge.loadingGif').html(keywordTreePull);
			cj('.BBtree.modal.merge dt#'+ tagLabel).html('');
			cj('.BBtree.modal.merge span.fCB').html('');
			cj('.BBtree.modal.merge .tag .name').before('<span><input type="radio" class="selectRadio" name="selectTag" /></span>');
			cj('.BBtree.modal.merge #tagLabel_296 .tag input').html('');
			modalKWSelectOnClick();

			cj('.BBtree.modal.merge.loadingGif').removeClass('loadingGif');

			}
		},
		buttons: {
			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy");
			}
		} 
	});
}
function modalKWSelectOnClick() {
	cj('.BBtree.modal input.selectRadio, .BBtree.modal div.tag').unbind('click');
	cj('.BBtree.modal input.selectRadio, .BBtree.modal div.tag').click(function(){
		var tagLabel = cj('.ui-dialog-content .modalHeader span').attr('tID');
		//search the string for all mentions of tid="number"
		var listOfChildTids = '';
		var tagChildren = cj('.BBtree.edit.manage dl#'+ tagLabel).html();
		if(tagChildren != null)
		{
			listOfChildTids += tagChildren.match(/tid=\"[0-9]*\"/g);
			for(i = 0;i<listOfChildTids.length;i++)
			{
				listOfChildTids[i] = listOfChildTids[i].replace("tid=\"",'');
				listOfChildTids[i] = listOfChildTids[i].replace("\"",'');
			}
		}
		if(cj('.BBtree.modal').hasClass('merge'))
		{
			tagLabel = cj('.modalHeader span').attr('tid');
			tagInfo = new Object();
			tagInfo.id = tagLabel;
			tagInfo.name = cj('.BBtree.manage dt#' + tagLabel + ' .tag .name').html();
			tagInfo.tid = cj('.BBtree.manage dt#' + tagLabel).attr('tid');
			var destinationId = cj(this).parents('dt').attr('tid');
			cj("#dialog").dialog( "option", "buttons", [
				{
					text: "Merge ",
					click: function() {
						tagMerge = new Object();
						cj('.ui-dialog-buttonset .ui-button').css("visibility", "hidden");
						cj('.ui-dialog-buttonpane').append('<div class="loadingGif"></div>');
						modalSetLoadingGif();
						tagMerge.currentId = cj('.ui-dialog-content .modalHeader span').attr('tID').replace('tagLabel_','');
						tagMerge.destinationId = destinationId;
						var postUrl = {/literal}"{crmURL p='civicrm/ajax/mergeTags' h=0 }"{literal}; 
		 				var data    = 'fromId='+ tagMerge.currentId + '&toId='+ tagMerge.destinationId + "&key={/literal}{crmKey 	name='civicrm/ajax/mergeTags'}{literal}";
		 				//console.log(data);
		 				var tidMatch = false;
		 				
		 				// var listOfToChildTids = '';
						// var toTagList = cj('.BBtree.edit.manage dl#tagLabel_'+ tagMerge.destinationId).html();
						// if(toTagList != null)
						// {
							
						// 	listOfToChildTids += toTagList.match(/tid=\"[0-9]*\"/g);
						// 	console.log(listOfToChildTids);
						// 	for(i = 0;i<listOfToChildTids.length;i++)
						// 	{
						// 		listOfToChildTids[i] = listOfToChildTids[i].replace("tid=\"",'');
						// 		listOfToChildTids[i] = listOfToChildTids[i].replace("\"",'');
						// 	}
						// 	var isListArray = cj.isArray(listOfToChildTids);
						// 	if((listOfChildTids.length > 0  && listOfToChildTids.length > 0) || !(isListArray))
				 		// 	{
				 		// 		tidMatch = true;
				 		// 	}
						// }
						//console.log(tagChildren);
						if(tagChildren != null)
						{
							tidMatch = true;
						}
			 			//console.log(tidMatch);
						if(tidMatch == false)
						{	
							cj.ajax({
								type     : "POST",
								url: postUrl,
								data: data,
								dataType: 'json',
								success: function(data, status, XMLHttpRequest) {
									if ( data.status == true ) {
										cj("#dialog").dialog("close"); 
										cj("#dialog").dialog("destroy"); 
										callTagAjax();
										if(cj('.contactTagsList.help').length < 1)
										{
											cj('.crm-content-block #help').after('<div class="contactTagsList help" id="tagStatusBar"></div>');
										}
										var toIdTag = cj('#tagLabel_' + tagMerge.destinationId + ' .tag .name').html();
										var msg = "<ul style=\"margin: 0 1.5em\"><li>'" + tagInfo.name + "' has been merged with '" + toIdTag + "'. All records previously tagged with '" + tagInfo.name + "' are now tagged with '" + toIdTag + "'.</li></ul>";
										cj('#tagLabel_' + tagInfo.tid).html(''); 
										cj('#tagStatusBar').html(msg);
									}
									else
									{
										cj('.ui-dialog-buttonpane .loadingGif').hide();
										cj('.ui-dialog-buttonset .ui-button').css("visibility", "visible");
										modalRemoveLoadingGif();
									}
									
								}	
							});
						}
						else {
							alert("Cannot merge a parent tag into another tag. Try moving sub-tags into the parent you want to merge into and then merge the tag into the destination");
							cj('.ui-dialog-buttonpane .loadingGif').hide();
							cj('.ui-dialog-buttonset .ui-button').css("visibility", "visible");
							modalRemoveLoadingGif();
						}

					}
				},
				{
					text: "Cancel",
					click: function() { 
						cj(this).dialog("close"); 
						cj(this).dialog("destroy"); 
					}
				}
			]);
		}
	});
}
/*adds the control box to admin/page to +/-/->/i/? based on a set of conditions*/
function addControlBox(tagLabel, IDChecked, currentID, treeTop) {
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
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -17px 0px; float:left;" title="Remove Keyword" onclick="makeModalRemove(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -50px 0px; float:left;" title="Update Keyword" onclick="makeModalUpdate(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -66px 0px; float:left;" title="Merge Keyword" onclick="makeModalKWMerge(\''+ tagLabel +'\')"></li>';
		floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -107px 0px; float:left;" title="Convert Keyword" onclick="makeModalConvert(\''+ tagLabel +'\')"></li>';
	}
	floatControlBox += '</span>';
	if(tagMouse == 'dt#tagLabel_291')
	{
		return '<span class="fCB" style="padding:1px 0;float:right;"><ul><li class="printTag" style="height:16px; width:16px; margin:auto 1px; float:left;" onClick="printTags()"> </li><li style="height:16px; width:16px; margin:auto 1px; float:left;" title="Add New Tag" onclick="makeModalAdd(\''+ tagLabel +'\')"></li></ul></span>'; 
	}else if(tagMouse == 'dt#tagLabel_296')
	{
		return '<span class="fCB" style="padding:1px 0;float:right;"><ul><li class="printTag" style="height:16px; width:16px; margin:auto 1px; float:left;" onClick="printTags()"> </li><li style="height:16px; width:16px; margin:auto 1px; float:left;" title="Add New Tag" onclick="makeModalAdd(\''+ tagLabel +'\')"></li></ul></span>'; 
	} else { return(floatControlBox); }
}
/*Function for checking and unchecking tags and updating the server on it's request*/
// function checkRemoveAdd(tagLabel) {
// 	console.log('implement hover slider end: ' + returnTime());
// 	var tagCheck = tagLabel.match(/Modal/);
// 	if(tagCheck == -1)
// 	{
		
// 		var n = cj('.BBtree.edit dt#'+ tagLabel).hasClass('checked');
// 		tagLabelID = tagLabel.replace('tagLabel_', '');
// 		if(n == false)
// 		{
// 			cj.ajax({
// 				url: '/civicrm/ajax/entity_tag/create',
// 				data: {
// 					entity_type: 'civicrm_contact',
// 					entity_id: cid,
// 					tag_id: tagLabelID
// 					},
// 				dataType: 'json',
// 				success: function(data, status, XMLHttpRequest) {
// 					if(data.code != 1) {}
// 					cj('.BBtree.edit dt#'+tagLabel).addClass('checked');
// 					giveParentsIndicator(tagLabel,'add');
// 				}
// 			});
			
// 		} else {
// 			cj.ajax({
// 				url: '/civicrm/ajax/entity_tag/delete',
// 				data: {
// 					entity_type: 'civicrm_contact',
// 					entity_id: cid,
// 					tag_id: tagLabelID
// 					},
// 				dataType: 'json',
// 				success: function(data, status, XMLHttpRequest) {
// 					if(data.code != 1) {}
// 					findIDLv(tagLabel);
// 				}
// 			});
// 		}
// 	}
// }
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
function printTags()
{
	var data = cj('.BBtree.edit.manage').html();
	var mywindow = window.open('', 'PrintTags');
	mywindow.document.body.innerHTML="";
	mywindow.document.write('<!DOCTYPE html><html><head><title>Print Tags</title>');
    mywindow.document.write('<link type="text/css" rel="stylesheet" href="/sites/default/themes/Bluebird/nyss_skin/tags.css" />');
    mywindow.document.write('<style>');
    mywindow.document.write('body.popup .BBtree dt div.treeButton {background-position: -64px -15px;}');
    mywindow.document.write('body.popup .BBtree dl.lv-2, body.popup .BBtree dl.lv-3, body.popup .BBtree dl.lv-4, body.popup .BBtree dl.lv-5, body.popup .BBtree dl.lv-6  {display:block !important;}');
    mywindow.document.write('.BBtree dt:hover .fCB{display:none;}');
    mywindow.document.write('.BBtree dt:hover {background-color:transparent;');
    mywindow.document.write('</style>');
   //mywindow.document.write('<script type="text/javascript" src="/sites/all/modules/civicrm/packages/jquery/jquery.js"></'+'script>');
    mywindow.document.write('</head><body class="popup">');
    mywindow.document.write('<div class="BBtree edit manage" style="height:auto;width:auto;overflow-y:hidden;">');
    mywindow.document.write(data);
    mywindow.document.write('</div>');
    mywindow.document.write('</body></html>');
    mywindow.print();
    return true;
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
            <div class="BBtree edit manage" id="tagLabel_291">
	    
	    </div>
        </div>
        
</div>

{/if}
