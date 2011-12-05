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
<style>
.BBtree.hidden {display:none;}
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
/*Init function for tree*/
function checkForTagTypes (treeData) {
	resetBBTree('main', 'init', treeData);
}
/*Acquires Ajax Block*/
function callTagAjax (local, modalTreeTop) {
	
	cj.ajax({
		url: '/civicrm/ajax/tag/tree',
		data: {
			entity_type: 'civicrm_contact'
			},
		dataType: 'json',
		success: function(data, status, XMLHttpRequest) {
			/*set variables*/
			var displayObj = [];
			displayObj.tLvl = 0;
			/*error handler goes here*/
			if(data.code != 1) {alert('fails');}
			cj('.crm-tagTabHeader ul').html('');
			cj.each(data.message, function(i,tID){
				if(tID.children.length > 0){
					cj('.crm-tagTabHeader ul').append('<li class="tab" tabID="'+i+'" onclick="swapTrees(this)">'+tID.name+'</li>');
					if(local == 'modal')
					{
						if(modalTreeTop == tID.id)
						{
							resetBBTree('modal', 'init', tID, modalTreeTop);
						}
					}
					else {
						switch(tID.id)
						{
							case '291': resetBBTree('main', 'init', tID);
							default: cj('<div class="BBtree edit hidden tabbed'+i+'"></div>').appendTo('#crm-tagListWrap');resetBBTree('backup', i, tID);break;
						}
					}
				}
			});
		}
			
	});
	var d = new Date(); 
}
/*Tab Swapping functionality between Issue Codes and Keywords*/
function swapTrees(tab){
	var tabID = cj(tab).attr('tabID');
	var swapID = cj('.crm-tagListSwapArea').attr('tID');
	if(swapID != tabID)
	{
		var toCopy = cj('.BBtree.tabbed'+tabID+'.hidden dl').html();
		cj('.crm-tagListSwapArea').attr('tID', tabID);
		cj('.BBtree.edit.manage').html('');
		cj('.BBtree.edit.manage').append(toCopy);
		setTimeout(function(){hoverTreeSlider('.BBtree.edit')},1000);
	}
}
/*Renders Tree*/
function resetBBTree(inpLoc, order, treeData, modalTreeTop) {
	var treeLoc;
	switch(inpLoc)
	{
		case 'main': treeLoc = '#crm-tagListWrap .BBtree.edit';callTagListMain(treeLoc, treeData); break;
		case 'backup': treeLoc = '#crm-tagListWrap .BBtree.hidden.tabbed'; treeLoc += order;callTagListMain(treeLoc, treeData); break;
		case 'modal': treeLoc = '.ui-dialog-content .BBtree.modal'; callTagListModal(treeLoc, treeData, modalTreeTop);  break;
		default: alert('No Tree Found'); break;
	}
	/*here's where the issues lie in multiple slider, ajaxComplete wants to run a multitude of times depending
	on how many times you perform functions on the page, I tossed a do while in there in hopes that i'd do the*/
	var setCompleteLoop = 1;
	cj(treeLoc).ajaxComplete(function(e, xhr, settings) {
		while(setCompleteLoop == 1)
		{
			
			if(inpLoc != 'backup')
			{
				setTimeout(function(){
					
					if(navigator.appName == 'Microsoft Internet Explorer'){
						if(order == 'init'){ setTimeout(function(){hoverTreeSlider(treeLoc)},1800); }
						setTimeout(function(){postJSON(treeLoc)},2000);
						setTimeout(function(){cj(treeLoc).removeClass('loadingGif');
						cj(treeLoc).children().show(); },4000);
					} else {
						if(order == 'init'){ hoverTreeSlider(treeLoc)}
						setTimeout(function(){postJSON(treeLoc)},200);
						setTimeout(function(){cj(treeLoc).removeClass('loadingGif');
						cj(treeLoc).children().show(); },2000);
					}
				},1000);
				
			}
			if(inpLoc == 'modal') { 
				modalSelectOnClick();
			}
			setCompleteLoop++;
		}
		cj(treeLoc).unbind('ajaxComplete');
	});
}
/*Writes out the on page (not modal) Tree to an object*/
function callTagListMain(treeLoc, treeData) {
	callTagAjaxInitLoader(treeLoc);	
	var tID = treeData;
	var displayObj = new Object();
	displayObj.tLvl = 0;
	/*have to note when you step in and out of levels*/
	displayObj.output = '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+tID.id+'" style="display:none">';
	displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id+''+isItemChecked(tID.is_checked,tID.id)+' '+isItemReserved(tID.is_reserved,tID.id)+'" id="tagLabel_'+tID.id+'" description="'+tID.description+'" tID="'+tID.id+'"><div class="treeButton"></div><div class="tag">'+tID.name+'</div>';

	var tIDLabel = 'tagLabel_'+tID.id;
	displayObj.output += addControlBox(tIDLabel)+'</dt>';
	if(tID.children.length > 0){
		/*this is where the first iteration goes in*/
		displayObj.tLvl = displayObj.tLvl+1;
		displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+tID.id+'">';
		cj.each(tID.children, function(i, cID){
			var cIDChecked = isItemChecked(cID.is_checked,cID.id);
			displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+cID.id+''+cIDChecked+' '+isItemReserved(cID.is_reserved,cID.id)+'" id="tagLabel_'+cID.id+'" description="'+cID.description+'" tID="'+cID.id+'"><div class="treeButton"></div><div class="tag">'+cID.name+'</div>';
			var cIDLabel = 'tagLabel_'+cID.id;
			displayObj.output += addControlBox(cIDLabel, cIDChecked, tID.id)+'</dt>';
			if(cID.children.length > 0){
				displayObj.tLvl = displayObj.tLvl+1;
				displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+cID.id+'">';
				cj.each(cID.children, function(i, iID){
					var iIDChecked = isItemChecked(iID.is_checked,iID.id);
					displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+iID.id+''+iIDChecked+' '+isItemReserved(iID.is_reserved,iID.id)+'" id="tagLabel_'+iID.id+'" description="'+iID.description+'" tID="'+iID.id+'"><div class="treeButton"></div><div class="tag">'+iID.name+'</div>';
					var iIDLabel = 'tagLabel_'+iID.id;
					displayObj.output += addControlBox(iIDLabel, iIDChecked, tID.id)+'</dt>';
					if(iID.children.length > 0){
						displayObj.tLvl = displayObj.tLvl+1;
						displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+iID.id+'">';
						cj.each(iID.children, function(i, jID){
							var jIDChecked = isItemChecked(jID.is_checked,jID.id);
							displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+jID.id+''+jIDChecked+' '+isItemReserved(jID.is_reserved,jID.id)+'" id="tagLabel_'+jID.id+'" description="'+jID.description+'" tID="'+jID.id+'"><div class="treeButton"></div><div class="tag">'+jID.name+'</div>';
							var jIDLabel = 'tagLabel_'+jID.id;
							displayObj.output += addControlBox(jIDLabel, jIDChecked, tID.id)+'</dt>';
							if(jID.children.length > 0){
								displayObj.tLvl = displayObj.tLvl+1;
								displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+jID.id+'">';
								cj.each(jID.children, function(i, kID){
									var kIDChecked = isItemChecked(kID.is_checked,kID.id);
									displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+kID.id+''+kIDChecked+' '+isItemReserved(kID.is_reserved,kID.id)+'" id="tagLabel_'+kID.id+'" description="'+kID.description+'" tID="'+kID.id+'"><div class="treeButton"></div><div class="tag">'+kID.name+'</div>';
									var kIDLabel = 'tagLabel_'+kID.id;
									displayObj.output += addControlBox(kIDLabel, kIDChecked, tID.id)+'</dt>';
								});
								displayObj.output += '</dl>';
								displayObj.tLvl = displayObj.tLvl-1;
							}
						});
						displayObj.output += '</dl>';
						displayObj.tLvl = displayObj.tLvl-1;
					}
				});
				displayObj.output += '</dl>';
				displayObj.tLvl = displayObj.tLvl-1;
			}
		});
		displayObj.output += '</dl>';
		displayObj.tLvl = displayObj.tLvl-1;
	}
	displayObj.output += '</dl>';
	writeDisplayObject(displayObj, treeLoc);
}
/*Writes out the modal tree to an object*/
function callTagListModal(treeLoc, tID, modalTreeTop) {
	callTagAjaxInitLoader(treeLoc);
	var displayObj = new Object();
	displayObj.tLvl = 0;
	if(tID.id == modalTreeTop)
	{
		/*have to note when you step in and out of levels*/
		displayObj.output = '<dl class="lv-'+displayObj.tLvl+'" id="tagModalLabel_'+tID.id+'" style="display:none">';
		displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id+''+isItemChecked(tID.is_checked,tID.id)+' '+isItemReserved(tID.is_reserved,tID.id)+'" id="tagModalLabel_'+tID.id+'" tID="'+tID.id+'"><div class="treeButton"></div><div class="tag">'+tID.name+'</div></dt>';
		if(tID.children.length > 0){
			/*this is where the first iteration goes in*/
			displayObj.tLvl = displayObj.tLvl+1;
			displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagModalLabel_'+tID.id+'">';
			cj.each(tID.children, function(i, cID){
				displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+cID.id+''+isItemChecked(cID.is_checked,cID.id)+' '+isItemReserved(cID.is_reserved,cID.id)+'" id="tagModalLabel_'+cID.id+'" tID="'+cID.id+'"><div class="treeButton"></div><div class="tag">'+cID.name+'</div><span><input type="radio" class="selectRadio" name="selectTag"/></span></dt>';
				if(cID.children.length > 0){
					displayObj.tLvl = displayObj.tLvl+1;
					displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagModalLabel_'+cID.id+'">';
					cj.each(cID.children, function(i, iID){
						displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+iID.id+''+isItemChecked(iID.is_reserved,iID.id)+' '+isItemReserved(iID.is_checked,iID.id)+'" id="tagModalLabel_'+iID.id+'" tID="'+iID.id+'"><div class="treeButton"></div><div class="tag">'+iID.name+'</div><span><input type="radio" class="selectRadio" name="selectTag"/></span></dt>';
						if(iID.children.length > 0){
							displayObj.tLvl = displayObj.tLvl+1;
							displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagModalLabel_'+iID.id+'">';
							cj.each(iID.children, function(i, jID){
								displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+jID.id+''+isItemChecked(jID.is_reserved,jID.id)+' '+isItemReserved(jID.is_checked,jID.id)+'" id="tagModalLabel_'+jID.id+'" tID="'+jID.id+'"><div class="treeButton"></div><div class="tag">'+jID.name+'</div><span><input type="radio" class="selectRadio" name="selectTag"/></span></dt>';
								if(jID.children.length > 0){
									displayObj.tLvl = displayObj.tLvl+1;
									displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+jID.id+'">';
									cj.each(jID.children, function(i, kID){
										displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+kID.id+''+isItemChecked(kID.is_reserved,kID.id)+' '+isItemReserved(kID.is_checked,kID.id)+'" id="tagModalLabel_'+kID.id+'" tID="'+kID.id+'"><div class="treeButton"></div><div class="tag">'+kID.name+'</div><span><input type="radio" class="selectRadio" name="selectTag"/></span></dt>';
									});
									displayObj.output += '</dl>';
									displayObj.tLvl = displayObj.tLvl-1;
								}
							});
							displayObj.output += '</dl>';
							displayObj.tLvl = displayObj.tLvl-1;
						}
					});
					displayObj.output += '</dl>';
					displayObj.tLvl = displayObj.tLvl-1;
				}
			});
			displayObj.output += '</dl>';
			displayObj.tLvl = displayObj.tLvl-1;
		}
		displayObj.output += '</dl>';
		writeDisplayObject(displayObj, treeLoc);
	}
}
/*Clears out the location to be written, and then jquery appends it to the space*/
function writeDisplayObject(displayObj, treeLoc) {
	cj(treeLoc).append(displayObj.output);
}
/*Loading Gif*/
function callTagAjaxInitLoader(treeLoc) {
	cj(treeLoc).html('');
	cj(treeLoc).addClass('loadingGif');
}
/*Slider & Interface functionality portion of things, when a tree initializes, as it loads, it runs through each
tag and binds/unbindes their click functionality to slide up/down... and stops the propagation if you click on
individual boxes inside the functionality or radio buttons. Last portion is for the Admin console that tells
number of tags named*/
function hoverTreeSlider(treeLoc){
	cj(treeLoc + ' dt').unbind('click');
	cj(treeLoc + ' dt').click(function() {
		if(cj(this).hasClass('lv-0'))
		{
			if(cj(this).hasClass('open'))
			{
				cj(treeLoc + ' dt.lv-0').removeClass('open');
				cj(treeLoc + ' dt.lv-0 .treeButton').removeClass('open');
				cj(treeLoc + ' dl.lv-1').slideUp();
			}
			else {
				cj(treeLoc + ' dt.lv-0').addClass('open');
				cj(treeLoc + ' dt.lv-0 .treeButton').addClass('open');
				cj(treeLoc + ' dl.lv-1').slideDown();
			}
		} else {

			var tagLabel = cj(this).attr('id');
			var isOpen = cj('dl#'+tagLabel).hasClass('open');
			switch(isOpen)
			{
				case true:
				cj(treeLoc + ' dt#'+tagLabel+' div').removeClass('open');
				cj(treeLoc + ' dl#'+tagLabel).slideUp('400', function() {
					cj('dl#'+tagLabel).removeClass('open');
				});
				break;
				case false:
				cj(treeLoc + ' dt#'+tagLabel+' div').addClass('open');
				cj(treeLoc + ' dl#'+tagLabel).slideDown('400', function() {
					cj('dl#'+tagLabel).addClass('open');
				});
			}
		}
	});
	cj(treeLoc + ' dt .fCB li').click(function(e) {
		e.stopPropagation();
	});
	cj(treeLoc + ' dt .selectRadio').click(function(e) {
			e.stopPropagation();
	});
	cj('.BBtree.edit dt').unbind('mouseenter mouseleave');
	cj('.BBtree.edit dt').hover(
	function(){
		var tagCount = 0;
		var tagName = cj(this).children('.tag').html();
		var tagId = cj(this).attr('tid');
		var isReserved = 'False';
		if(cj(this).hasClass('isReserved') == true)
		{
			isReserved = 'True';
		}
		cj('.crm-tagListInfo .tagInfoBody .tagName span').html(tagName);
		cj('.crm-tagListInfo .tagInfoBody .tagId span').html(tagId);
		cj('.crm-tagListInfo .tagInfoBody .tagDescription span').html(cj(this).attr('description'));
		cj('.crm-tagListInfo .tagInfoBody .tagReserved span').html(isReserved);
		cj('.crm-tagListInfo .tagInfoBody .tagCount span').html(tagCount);
	}, 
	function() {
		cj('.crm-tagListInfo .tagInfoBody .tagName span').html('');
		cj('.crm-tagListInfo .tagInfoBody .tagID span').html('');
		cj('.crm-tagListInfo .tagInfoBody .tagDescription span').html('');
		cj('.crm-tagListInfo .tagInfoBody .tagReserved span').html('');
		cj('.crm-tagListInfo .tagInfoBody .tagCount span').html('');
	});
}
/*This poorly named function determines which tags are stubs, and which need arrows*/
function postJSON(treeLoc){
	cj(treeLoc + ' dt').each(function() {
		var idGrab = cj(this).attr('id');
		if(idGrab != '')
		{

			if(cj(treeLoc + ' dl#'+ idGrab).length == 0)
			{
				cj(treeLoc + ' dt#' + idGrab + ' div').addClass('stub');
			}
		}
	});
	/*top level defaults*/
	cj('dt.lv-0').addClass('open');
	cj('dt.lv-0 .treeButton').addClass('open');
	runParentFinder();
}
/*is the Tag checked?*/
function isItemChecked(dataObj,tagLabel){
	tagLabel = 'tagLabel_' + tagLabel;
	if(dataObj == true){ 
		return ' checked';
	}
	else{ return '';}
}
/*is it reserved?*/
function isItemReserved(dataObj,tagLabel){
	if(dataObj == '1'){ 
		return 'isReserved';
	}
	else{ return '';}
}
/*This acquires an array of all classes marked as checked by the jquery tag writing (callTag), grabs their ID and
sends them to giveParents*/
function runParentFinder(){
	var checkedKids = cj('dt.checked');
	for(var i = 0;i < checkedKids.length;i++)
	{
		var idGrab = cj(checkedKids[i]).attr('id');
		giveParentsIndicator(idGrab,'add');
	}
}
/*giveParents marks the tags parents in question as being marked up the tree to give inheritance and notation
that there's tags underneath*/
function giveParentsIndicator(tagLabel,toggleParent){
	if(toggleParent == 'add')
	{
		var parentElements = cj('.BBtree.edit dt#' + tagLabel).parents('dl');
		for(var i = 0;i < parentElements.length;i++)
		{
			var idGrab = cj(parentElements[i]).attr('id');
		        if(!(cj(idGrab).hasClass('lv-0')) && !(cj(idGrab).hasClass('lv-1'))  )
		        {
		        	cj('.BBtree.edit dt#' + idGrab).addClass('subChecked');
		        }
		}
		
	}
	if(toggleParent == 'remove')
	{
	
	}
}
/*This is the add functionality that hooks into the tag ajax to add new tags, makes a dialog with jQUI
and then creates a request on done.*/
function makeModalAdd(tagLabel){
	cj("#dialog").show();
	cj("#dialog").dialog({
		draggable: false,
		height: 300,
		width: 300,
		title: "Add New Tag",
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
			tagInfo.name = cj('dt#' + tagLabel + ' .tag').html();
			
			var addDialogInfo = '<div class="modalHeader">Add new tag under ' + tagInfo.name + '</div>';
			addDialogInfo += '<div class="modalInputs">';
			addDialogInfo += '<div><span>Tag Name:</span ><input type="text" name="tagName" /></div>';
			addDialogInfo += '<div><span>Description:</span ><input type="text" name="tagDescription" /></div>';
			addDialogInfo += '<div><span class="parentName" id="'+tagLabel+'">Insert Under ' + tagInfo.name +'</span></div>';
			addDialogInfo += '<div><span style="display:none">Or Choose A New Location</span><div></div></div>';
			addDialogInfo += '<div><span>Reserved:</span><input type="checkbox" name="isReserved"/></div>';
			cj('#dialog').html(addDialogInfo);
			cj('#dialog input:[name=tagName]').focus();
		},
		buttons: {
			"Done": function () {
				tagCreate = new Object();
				tagCreate.tagName = cj('#dialog .modalInputs input:[name=tagName]').val();
				tagCreate.tagDescription = cj('#dialog .modalInputs input:[name=tagDescription]').val();
				tagCreate.parentId = cj('#dialog .modalInputs .parentName').attr('id').replace('tagLabel_', '');
				tagCreate.isReserved = cj('#dialog .modalInputs input:checked[name=isReserved]').length;
				cj.ajax({
					url: '/civicrm/ajax/tag/create',
					data: {
						name: tagCreate.tagName,
						description: tagCreate.tagDescription,
						parent_id: tagCreate.parentId,
						is_reserved: tagCreate.isReserved	
					},
					dataType: 'json',
					success: function(data, status, XMLHttpRequest) {
						if(data.code != 1)
						{
							alert(data.message);
						}
						cj('#dialog').dialog('close');
						cj('#dialog').dialog('destroy');
						callTagAjax();
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
/*This is the Remove functionality that hooks into the tag ajax to add new tags, only difference is is that
it breaks out an error message to something more user friendly. it can be broken out into it's own function if
there are a copious amount of errors in the future to worry about other than Child Tag issues*/
function makeModalRemove(tagLabel){
	cj("#dialog").show( );
	cj("#dialog").dialog({
		draggable: false,
		height: 300,
		width: 300,
		title: "Remove Tag...",
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
			tagInfo.name = cj('.BBtree.edit dt#' + tagLabel + ' .tag').html();
			tagInfo.isReserved = cj('.BBtree.edit dt#' + tagLabel).hasClass('isReserved');
			if(tagInfo.isReserved == false) {
				var addDialogInfo = '<div class="modalHeader"><span class="parentName" id="'+tagLabel+'">Remove Tag: ' + tagInfo.name + '</span></div>';
				cj("#dialog").dialog( "option", "buttons", [
					{
						text: "Done",
						click: function() {
							tagRemove = new Object();
							tagRemove.parentId = cj('#dialog .modalHeader .parentName').attr('id').replace('tagLabel_', '');
							cj.ajax({
								url: '/civicrm/ajax/tag/delete',
								data: {
									id: tagRemove.parentId
								},
								dataType: 'json',
								success: function(data, status, XMLHttpRequest) {
									if(data.code != 1)
									{
										if(data.message == 'DB Error: constraint violation')
										{
											alert('Error: Child Tag Exists');
										}
										else { alert(data.message); }
									}
									cj('#dialog').dialog('close');
									cj('#dialog').dialog('destroy');
									callTagAjax();
								}
							});

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
			} else {
			var addDialogInfo = '<div class="modalHeader"><span class="parentName" id="'+tagLabel+'">' + tagInfo.name + ' is a reserved tag and Cannot Be Removed</span></div>';
			}
			cj("#dialog").html(addDialogInfo);
		},
		buttons: {
			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			}
		} 
	});
}
/*Updates the tag with new info*/
function makeModalUpdate(tagLabel){
	cj("#dialog").show( );
	cj("#dialog").dialog({
		draggable: false,
		height: 300,
		width: 300,
		title: "Update Tag",
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
			tagInfo.name = cj('.BBtree.edit dt#' + tagLabel + ' .tag').html();
			tagInfo.description = cj('.BBtree.edit dt#' + tagLabel).attr('description');
			tagInfo.reserved = cj('.BBtree.edit dt#'+tagLabel).hasClass('isReserved');
			var updateDialogInfo = '';
			if(tagInfo.reserved == true){
			tagInfo.reserved = 'checked';} else {
			tagInfo.reserved = '';}
			updateDialogInfo += '<div class="modalHeader">Add new tag under ' + tagInfo.name + '</div>';
			updateDialogInfo += '<div class="modalInputs">';
			updateDialogInfo += '<div><span>Tag Name:</span ><input type="text" name="tagName" value="'+tagInfo.name+'" /></div>';
			updateDialogInfo += '<div><span>Description:</span ><input type="text" name="tagDescription" value="'+tagInfo.description+'"/></div>';
			updateDialogInfo += '<div><span>Reserved:</span><input type="checkbox" name="isReserved" '+tagInfo.reserved+'/></div>';
			cj('#dialog').html(updateDialogInfo);
			cj('#dialog input:[name=tagName]').focus();
		},
		buttons: {
			"Done": function () {
				tagUpdate = new Object();
				tagUpdate.tagName = cj('#dialog .modalInputs input:[name=tagName]').val();
				tagUpdate.tagDescription = cj('#dialog .modalInputs input:[name=tagDescription]').val();
				tagUpdate.currentId = tagLabel.replace('tagLabel_', '');
				tagUpdate.isReserved = cj('#dialog .modalInputs input:checked[name=isReserved]').length;
				cj.ajax({
					url: '/civicrm/ajax/tag/update',
					data: {
						name: tagUpdate.tagName,
						description: tagUpdate.tagDescription,
						id: tagUpdate.currentId,
						is_reserved: tagUpdate.isReserved	
					},
					dataType: 'json',
					success: function(data, status, XMLHttpRequest) {
						if(data.code != 1)
						{
							alert(data.message);
						}
						cj('#dialog').dialog('close');
						cj('#dialog').dialog('destroy');
						callTagAjax();
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
/*Merge hijacks the old process to use their autocomplete function*/
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
/*makes a modal tree, this is the more involved one than the rest because it's calling a tree structure and
having to replicate much of the same combinations, it moves to modalSelectOnclick to open a dialog box*/
function makeModalTree(tagLabel){
	cj("#dialog").show( );
	cj("#dialog").dialog({
		closeOnEscape: true,
		draggable: false,
		height: 500,
		width: 400,
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
			tagInfo.name = cj('.BBtree.edit.manage dt#' + tagLabel + ' .tag').html();
			tagInfo.reserved = cj('.BBtree.edit.manage dt#'+tagLabel).hasClass('isReserved');
			var treeDialogInfo;
			if(tagInfo.reserved == true){
			treeDialogInfo = '<div class="modalHeader">This tag is reserved and cannot be moved</div>';
			cj('#dialog').html(treeDialogInfo);
			} else {
			treeDialogInfo = '<div class="modalHeader">Move <span tID="'+tagInfo.id+'">' + tagInfo.name + ' under Tag...</span></div>';
			treeDialogInfo += '<div class="BBtree modal"></div>';
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
	cj('.BBtree input.selectRadio').click(function(){
		var destinationId = cj(this).parent().parent('dt').attr('tid');
		cj("#dialog").dialog( "option", "buttons", [
			{
				text: "Move",
				click: function() {
					tagMove = new Object();
					tagMove.currentId = cj('.ui-dialog-content .modalHeader span').attr('tID').replace('tagLabel_','');
					tagMove.destinationId = destinationId;
					cj.ajax({
						url: '/civicrm/ajax/tag/update',
						data: {
							id: tagMove.currentId,
							parent_id: tagMove.destinationId
						},
						dataType: 'json',
						success: function(data, status, XMLHttpRequest) {
							if(data.code != 1)
							{
								alert(data.message);
							}
							cj('#dialog').dialog('close');
							cj('#dialog').dialog('destroy');
							callTagAjax();
						}
					});

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
