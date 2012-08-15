/*var location = '.BBtree';
var cID = 0;*/
function callTagAjax (location, cID, tag_type,  modalTreeTop) {
	cj(location).html();
	var pageLoc = returnLocation(location);
	//manage pages won't have a CID attached, this makes sure of it.
	if(pageLoc == 'manage')
	{
		cID = 0;
	}
	cj.ajax({
		url: '/civicrm/ajax/tag/tree',
		data: {
			entity_type: 'civicrm_contact',
			entity_id: cID
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
					if(tag_type == '291' && tID.id == '291')
					{
						var content = parseJsonData(location, tag_type, tID, pageLoc, modalTreeTop);
						//console.log(content);
						cj(location).html(content);
					}
					if(tag_type == '296'  && tID.id == '296')
					{

					}
				}
			});
		}
			
	});
	var d = new Date(); 
}
function returnLocation(location)
{
	var pageLocation;
	switch(location){
		case '.BBtree.edit.manage': pageLocation = 'manage';break;
		case '.BBtree.edit.tab': pageLocation = 'contact';break;
		default: console.log('Set up pageLocation variable in bbtree.js');break;			
	}
	return pageLocation;
}
//this is an updated version of the former callTagList, simpler, easier, whateverer. Does the same thing: turns the 
//json into data
function parseJsonData(location, tag_type, tID, pageLoc, modalTreeTop)
{
	var idName;
	//looks for modal
	switch (pageLoc){
		case 'contact':
		case 'manage':
			idName = "tagLabel_";
		break;
	}
	//we are at lvl 0
	
	var displayObj = new Object;
	displayObj.tLvl = 0;
	displayObj.output = '<dl class="lv-'+displayObj.tLvl+'" id="'+idName+tID.id+'" tLvl="'+displayObj.tLvl+'" style="">';
	displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id;
	if(tID.id != tag_type)
	{
		displayObj.output += isItemMarked(tID.is_checked,'checked')+' '+isItemMarked(tID.is_reserved,'isReserved')
	}
	displayObj.output += '" id="'+idName+tID.id+'" description="'+tID.description+'" tLvl="'+displayObj.tLvl+'" tID="'+tID.id+'"><div class="treeButton"></div><div class="tag">'+tID.name+'</div></dt>';
	displayObj.output = parseJsonInsides(location, tag_type, tID, idName, displayObj);
	setBBTree(location, tag_type, tID, idName, displayObj, modalTreeTop);
}
function setBBTree(location, tag_type, tID, idName, displayObj, modalTreeTop)
{
	
	cj(location).html(displayObj.output);
		/*here's where the issues lie in multiple slider, ajaxComplete wants to run a multitude of times depending
	on how many times you perform functions on the page, I tossed a do while in there in hopes that i'd do the*/
	var setCompleteLoop = 1;
	cj(location).ajaxComplete(function(e, xhr, settings) {
		while(setCompleteLoop == 1)
		{
			
			if(modalTreeTop != 'backup')
			{
				setTimeout(function(){
					
					if(navigator.appName == 'Microsoft Internet Explorer'){
						setTimeout(function(){hoverTreeSlider(location)},1800);
						setTimeout(function(){setArrows(location)},2000);
						setTimeout(function(){cj(location).removeClass('loadingGif');
						cj(location).children().show(); },4000);
					} else {
						hoverTreeSlider(location)
						setTimeout(function(){setArrows(location)},200);
						setTimeout(function(){cj(location).removeClass('loadingGif');
						cj(location).children().show(); },2000);
					}
				},1000);
				
			}
			if(modalTreeTop == 'modal') { 
				modalSelectOnClick();
			}
			setCompleteLoop++;
		}
		cj(location).unbind('ajaxComplete');
	});
}
function parseJsonInsides(location, tag_type, tID, idName, displayObj)
{
	//starting at the first level, write out the first tag, and then check if it has children
	if(tID.children.length >= 0)
	{
		cj.each(tID.children, function(i, cID){
			
			openChildJsonTag(location, tag_type, cID, idName, displayObj);
			writeJsonTag(location, tag_type, cID, idName, displayObj);
			closeChildJsonTag(location, tag_type, tID, idName, displayObj);
			
		});
	}
	
	if(displayObj.tLvl == 0){
		return(displayObj.output);	
	}
}
//print tag
function writeJsonTag(location, tag_type, tID, idName, displayObj)
{	
	var tidName = idName + tID.id;
	var isChecked = isItemMarked(tID.is_checked,'checked');
	displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id+' '+isItemMarked(tID.is_checked,'checked')+' '+isItemMarked(tID.is_reserved,'isReserved')+'" id="'+idName+tID.id+'" description="'+tID.description+'" tLvl="'+displayObj.tLvl+'"  tID="'+tID.id+'"><div class="treeButton"></div><div class="tag">'+tID.name+'</div>' + addControlBox(tID.name, isChecked, tidName, location) + '</dt>';
	if(tID.children.length > 0)
	{
		cj.each(tID.children, function(i, cID){
			var isCChecked = isItemMarked(cID.is_checked,'checked');
			var cidName = idName + cID.id;
			openChildJsonTag(location, tag_type, cID, idName, displayObj);
			displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+cID.id+' '+isItemMarked(cID.is_checked,'checked')+' '+isItemMarked(cID.is_reserved,'isReserved')+'" id="'+idName+cID.id+'" description="'+cID.description+'" tLvl="'+displayObj.tLvl+'" cID="'+cID.id+'"><div class="treeButton"></div><div class="tag">'+cID.name+'</div>' + addControlBox(cID.name, isCChecked, cidName, location) + '</dt>';
			parseJsonInsides(location, tag_type, cID, idName, displayObj);	
			closeChildJsonTag(location, tag_type, tID, idName, displayObj);
		});
	}
	return displayObj.output;
}
//open child tag
function openChildJsonTag(location, tag_type, tID, idName, displayObj)
{
	displayObj.tLvl++;
	displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="'+idName+tID.id+'" tLvl="'+displayObj.tLvl+'" style="">';
}
//close child tag
function closeChildJsonTag(location, tag_type, tID, idName, displayObj)
{
	displayObj.tLvl--;
	displayObj.output += '</dl>';
}
//parsing functions
function isItemMarked(value, type)
{
	if(value == true)
	{
		return(type);
	}
	else {
		return '';
	}
}
function hoverTreeSlider(treeLoc)
{
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
			var tagLv = cj(this).attr('tlvl');
			var isOpen = cj(treeLoc + ' dl#'+tagLabel).hasClass('open');
			switch(isOpen)
			{
				case false:
				tagLv++;
				cj(treeLoc + ' dt#'+tagLabel+' div').addClass('open');
				cj(treeLoc + ' dl#'+tagLabel + ' .lv-'+tagLv).slideDown('400', function() {
					cj(treeLoc + ' dl#'+tagLabel).addClass('open');
				});
				break;
				case true:
				tagLv++;
				cj(treeLoc + ' dt#'+tagLabel+' div').removeClass('open');
				cj(treeLoc + ' dl#'+tagLabel + ' .lv-'+tagLv).slideUp('400', function() {
					cj(treeLoc + ' dl#'+tagLabel).removeClass('open');
				});
				break;
				
			}
		}
	});
	cj(treeLoc + ' dt .fCB li').click(function(e) {
		e.stopPropagation();
	});
	cj(treeLoc + ' dt .selectRadio').click(function(e) {
			e.stopPropagation();
	});
	cj(treeLoc + ' dt').unbind('mouseenter mouseleave');
	cj(treeLoc + ' dt').hover(
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
function setArrows(treeLoc)
{
	cj(treeLoc + ' dt').each(function() {
		var idGrab = cj(this).attr('id');
		if(idGrab != '')
		{

			if(cj(treeLoc + ' dl#'+ idGrab + ' dl').length == 0)
			{
				
				cj(treeLoc + ' dt#' + idGrab + ' div').addClass('stub');
			}
		}
	});
	/*top level defaults*/
	cj('dt.lv-0').addClass('open');
	cj('dt.lv-0 .treeButton').addClass('open');
	runParentFinder(treeLoc);
}
/*This acquires an array of all classes marked as checked by the jquery tag writing (callTag), grabs their ID and
sends them to giveParents*/
function runParentFinder(location)
{
	var checkedKids = cj('dt.checked');
	for(var i = 0;i < checkedKids.length;i++)
	{
		var idGrab = cj(checkedKids[i]).attr('id');
		giveParentsIndicator(idGrab,'add',location);
	}
}
/*giveParents marks the tags parents in question as being marked up the tree to give inheritance and notation
that there's tags underneath*/
function giveParentsIndicator(tagLabel,toggleParent,location)
{
	if(toggleParent == 'add')
	{
		var parentElements = cj(location + ' dt#' + tagLabel).parents('dl');
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
function callTagAjaxInitLoader(treeLoc)
{
	cj(treeLoc).html('');
	cj(treeLoc).addClass('loadingGif');
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
//MODAL STUFF

/*makes a modal tree, this is the more involved one than the rest because it's calling a tree structure and
having to replicate much of the same combinations, it moves to modalSelectOnclick to open a dialog box*/
function makeModalTree(tagLabel)
{
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
function modalSelectOnClick()
{
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
/*This is the add functionality that hooks into the tag ajax to add new tags, makes a dialog with jQUI
and then creates a request on done.*/
function makeModalAdd(tagLabel)
{
	console.log(tagLabel);
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
function makeModalRemove(tagLabel)
{
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
function makeModalUpdate(tagLabel)
{
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

function addControlBox(tagLabel, IDChecked, tagID, location) {
	var pageLoc = returnLocation(location);
	console.log(tagID);
	var floatControlBox;
	var tagMouse = location +' dt#'+tagLabel;
	var displayChecked = '';
	if(IDChecked == 'checked'){
		displayChecked = 'display:inline;"';
	}
	floatControlBox = '<span class="fCB" style="padding:1px 0; float:right; '+displayChecked+'">';
	floatControlBox += '<ul>';
	switch(pageLoc)
	{
		case 'manage': 
			floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; float:left;" onclick="makeModalAdd(\''+ tagID +'\')"></li>';
			floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -17px 0px; float:left;" onclick="makeModalRemove(\''+ tagID +'\')"></li>';
			floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -34px 0px; float:left;" onclick="makeModalTree(\''+ tagID +'\')"></li>';
			floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -50px 0px; float:left;" onclick="makeModalUpdate(\''+ tagID +'\')"></li>';
			floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -66px 0px; float:left;" onclick="makeModalMerge(\''+ tagID +'\')"></li>';
		break;
		case 'contact':
			floatControlBox += '<li style="height:16px; width:16px; margin:-1px 4px 0 -2px; background:none; float:left;">';
			if(IDChecked == 'checked'){
				floatControlBox += '<input id="tag['+tagLabel+']" name="tag['+tagLabel+']" type="checkbox" onclick="checkRemoveAdd(\''+tagID+'\')" class="checkbox form-checkbox" value="1" checked></input></li></ul>';
			} 
			else {
				floatControlBox += '<input id="tag['+tagLabel+']" name="tag['+tagLabel+']" type="checkbox" onclick="checkRemoveAdd(\''+tagID+'\')" class="checkbox form-checkbox" value="1"></input></li></ul>';
			}
			floatControlBox += '</span>';
		break;
	}
	if(tagMouse != '.BBtree.edit dt#tagLabel_291')
	{
		return(floatControlBox);
	} else { return ''; }
}
function checkRemoveAdd(tagLabel) {
	var n = cj('.BBtree.edit dt#'+ tagLabel).hasClass('checked');
	tagLabelID = tagLabel.replace('tagLabel_', '');
	if(n == false)
	{
		cj('.BBtree.edit dt#'+tagLabel).addClass('checked');
		var temp = cj('.BBtree.edit dt#'+tagLabel+' .fCB').attr('style');
		temp += '; display:inline';
		cj('.BBtree.edit dt#'+tagLabel+' .fCB').attr('style', temp);
		giveParentsIndicator(tagLabel,'add');
	} else {
		findIDLv(tagLabel);
	}
}
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
	cj('dt#'+tagLabel+' .fCB').attr('style', 'padding:1px 0;float:right;'); 
}
