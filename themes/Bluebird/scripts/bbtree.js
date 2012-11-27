/*Init function for tree*/
function checkForTagTypes (treeData) {
	resetBBTree('main', 'init', treeData);
}
/*Acquires Ajax Block*/
function callTagAjax (local, modalTreeTop, pointToTab) {
	var pointToTab = cj('.crm-tagTabHeader .tab.active').attr('id');
	if(typeof pointToTab === 'undefined'){
		pointToTab = 'tagLabel_291';
	}
	var getPage = cj('.BBtree.edit').attr('class');
	var pageClasses = getPage.split(' ');
	//console.log(pageClasses[2]);
	//console.log('start of Tree Rebuild: ' + returnTime());
	cj.ajax({
		url: '/civicrm/ajax/tag/tree',
		data: {
			entity_type: 'civicrm_contact',
			entity_id: cid,
			entity_counts: 0
			},
		dataType: 'json',
		success: function(data, status, XMLHttpRequest) {
			//console.log('data returned for Tree Rebuild: ' + returnTime());
			/*set variables
			var displayObj = [];
			displayObj.tLvl = 0;
			/*error handler goes here*/
			if(data.code != 1) {alert('fails');}
			//console.log(cj('.crm-tagTabHeader li.tab').attr('id'));
			cj('.crm-tagTabHeader ul').html('');
			/*if(local != 'modal' && cj('.BBtree.edit.manage').length > 0)
			{
				cj('.BBtree.edit.manage').remove();
				cj('#crm-tagListWrap .crm-tagListInfo').after('<div class="BBtree edit manage loadingGif"></div>');
			}*/
			cj.each(data.message, function(i,tID){
				if(tID.id != '292') //if not positions
				{
					if(tID.children.length > 0){
						var checkTIDforPoint = 'tagLabel_' + tID.id;
						if(checkTIDforPoint == pointToTab)
						{
							cj('.crm-tagTabHeader ul').append('<li class="tab active" id="tagLabel_'+tID.id+'" onclick="swapTrees(this);return false;">'+tID.name+'</li>');
						}
						else
						{
							cj('.crm-tagTabHeader ul').append('<li class="tab" id="tagLabel_'+tID.id+'" onclick="swapTrees(this);return false;">'+tID.name+'</li>');
						}
						if(local == 'modal')
						{
							if(modalTreeTop == tID.id)
							{
								//console.log('gettingtomodal');
								resetBBTree('modal', 'init', tID, modalTreeTop);
							}
						}
						else {
							//if(pointToTab = '291')
							//{
								switch(tID.id)
								{
									
									case '291': resetBBTree(pageClasses[2], 'init', tID, 0, pointToTab); break;
									case '296': if(pageClasses[2] == 'manage'){cj('<div class="BBtree edit hidden tabbed" id="tagLabel_'+tID.id+'"></div>').appendTo('#crm-tagListWrap');resetBBTree('backup', i, tID, 0, pointToTab);}break;
								}
							//}
						}
					}
				}
			});

		}
			
	});
}
function resetBBTree(inpLoc, order, treeData, modalTreeTop, pointToTab) {
	//console.log('beginning of tree rebuild - resetBBTree: ' + returnTime());
	var treeLoc;
	switch(inpLoc)
	{
		case 'manage': treeLoc = '#crm-tagListWrap .BBtree.edit.manage';  callTagAjaxInitLoader(treeLoc, inpLoc); callTagListMain(treeLoc, treeData, pointToTab); setOpenTab(pointToTab); break;
		case 'tab': treeLoc = '#crm-tagListWrap .BBtree.edit.tab';  callTagAjaxInitLoader(treeLoc, inpLoc); callTagListMain(treeLoc, treeData); break;
		case 'contact': treeLoc = '#crm-tagListWrap .BBtree.edit.contact';  callTagAjaxInitLoader(treeLoc, inpLoc); callTagListMain(treeLoc, treeData); break;
		case 'hidden': case 'backup': treeLoc = '#crm-tagListWrap .BBtree.edit.hidden.tabbed'; callTagAjaxInitLoader(treeLoc, inpLoc); callTagListMain(treeLoc, treeData, pointToTab);setOpenTab(pointToTab); break;
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

				if(order == 'init'){ hoverTreeSlider(treeLoc)}
				//console.log('postJSON: ' + returnTime());
				postJSON(treeLoc);
				//console.log('post-postJSON: ' + returnTime());
				var treeLocationToRemoveGif = '.'+inpLoc;
				modalRemoveLoadingGif(treeLocationToRemoveGif);
				//console.log('end of tree rebuild: ' + returnTime());
			}
			if(inpLoc == 'modal') { 
				modalSelectOnClick();
				setTimeout(function(){
					var modalTIDhide = cj('#modalNameTid').attr('tid');
					var tagTIDtoHide = cj('#' + modalTIDhide).attr('tid');
					cj('.BBtree.modal #tagModalLabel_'+ tagTIDtoHide).html('');
				},500);
			}
			setCompleteLoop++;
			//swapTrees(pointToTab);

		}
		cj(treeLoc).unbind('ajaxComplete');

	});
}
/*Writes out the on page (not modal) Tree to an object*/
function callTagListMain(treeLoc, treeData, pointToTab) {
	//console.log('begin of render for Tree Rebuild: ' + treeLoc + ' '+ returnTime());
	var tID = treeData;
	delete displayObj;
	var displayObj = new Object();
	displayObj.tLvl = 0;
	//console.log('begin of render for Tree Rebuild 1: ' + returnTime());
	/*have to note when you step in and out of levels*/
	displayObj.output = '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+tID.id+'" style="display:none">';
	displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id+''+isItemChecked(tID.is_checked,tID.id)+' '+isItemReserved(tID.is_reserved,tID.id)+'" id="tagLabel_'+tID.id+'" description="'+escapePositions(tID.description, tID.id)+'" tID="'+tID.id+'"><div class="'+isItemChildless(tID.children.length)+'"></div><div class="tag">'+tID.name+'</div>';

	var tIDLabel = 'tagLabel_'+tID.id;
	displayObj.output += addControlBox(tIDLabel)+'</dt>';
	if(tID.children.length > 0){
		/*this is where the first iteration goes in*/
		displayObj.tLvl = displayObj.tLvl+1;
		displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+tID.id+'">';
		cj.each(tID.children, function(i, cID){
			var cIDChecked = isItemChecked(cID.is_checked,cID.id);
			displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+cID.id+''+cIDChecked+' '+isItemReserved(cID.is_reserved,cID.id)+'" id="tagLabel_'+cID.id+'" description="'+escapePositions(cID.description, tID.id)+'" tID="'+cID.id+'"><div class="'+isItemChildless(cID.children.length)+'"></div><div class="tag"><span class="name">'+cID.name+'</span><span class="entityCount">('+cID.entity_count+')</span></div>';
			var cIDLabel = 'tagLabel_'+cID.id;
			displayObj.output += addControlBox(cIDLabel, cIDChecked, cID.id, tID.id)+'</dt>';
			if(cID.children.length > 0){
				displayObj.tLvl = displayObj.tLvl+1;
				displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+cID.id+'">';
				cj.each(cID.children, function(i, iID){
					var iIDChecked = isItemChecked(iID.is_checked,iID.id);
					displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+iID.id+''+iIDChecked+' '+isItemReserved(iID.is_reserved,iID.id)+'" id="tagLabel_'+iID.id+'" description="'+escapePositions(iID.description, tID.id)+'" tID="'+iID.id+'"><div class="'+isItemChildless(iID.children.length)+'"></div><div class="tag"><span class="name">'+iID.name+'</span><span class="entityCount">('+iID.entity_count+')</span></div>';
					var iIDLabel = 'tagLabel_'+iID.id;
					displayObj.output += addControlBox(iIDLabel, iIDChecked, iID.id, tID.id)+'</dt>';
					if(iID.children.length > 0){
						displayObj.tLvl = displayObj.tLvl+1;
						displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+iID.id+'">';
						cj.each(iID.children, function(i, jID){
							var jIDChecked = isItemChecked(jID.is_checked,jID.id);
							displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+jID.id+''+jIDChecked+' '+isItemReserved(jID.is_reserved,jID.id)+'" id="tagLabel_'+jID.id+'" description="'+escapePositions(jID.description, tID.id)+'" tID="'+jID.id+'"><div class="'+isItemChildless(jID.children.length)+'"></div><div class="tag"><span class="name">'+jID.name+'</span><span class="entityCount">('+jID.entity_count+')</span></div>';
							var jIDLabel = 'tagLabel_'+jID.id;
							displayObj.output += addControlBox(jIDLabel, jIDChecked, jID.id, tID.id)+'</dt>';
							if(jID.children.length > 0){
								displayObj.tLvl = displayObj.tLvl+1;
								displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+jID.id+'">';
								cj.each(jID.children, function(i, kID){
									var kIDChecked = isItemChecked(kID.is_checked,kID.id);
									displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+kID.id+''+kIDChecked+' '+isItemReserved(kID.is_reserved,kID.id)+'" id="tagLabel_'+kID.id+'" description="'+escapePositions(kID.description, tID.id)+'" tID="'+kID.id+'"><div class="'+isItemChildless(kID.children.length)+'"></div><div class="tag"><span class="name">'+kID.name+'</span><span class="entityCount">('+kID.entity_count+')</span></div>';
									var kIDLabel = 'tagLabel_'+kID.id;
									displayObj.output += addControlBox(kIDLabel, kIDChecked, kID.id, tID.id)+'</dt>';
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
	writeDisplayObject(displayObj, treeLoc, pointToTab);
	//console.log('end of render for Tree Rebuild: ' + returnTime());
}
/*Writes out the modal tree to an object*/
function callTagListModal(treeLoc, tID, modalTreeTop) {
	//console.log('callingModal');
	callTagAjaxInitLoader(treeLoc);
	var displayObj = new Object();
	displayObj.tLvl = 0;
	if(tID.id == modalTreeTop)
	{
		/*have to note when you step in and out of levels*/
		displayObj.output = '<dl class="lv-'+displayObj.tLvl+'" id="tagModalLabel_'+tID.id+'" style="display:none">';
		displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id+''+isItemChecked(tID.is_checked,tID.id)+' '+isItemReserved(tID.is_reserved,tID.id)+'" id="tagModalLabel_'+tID.id+'" tID="'+tID.id+'"><div class=" '+isItemChildless(tID.children.length)+'"></div><div class="tag">'+tID.name+'</div></dt>';
		if(tID.children.length > 0){
			/*this is where the first iteration goes in*/
			displayObj.tLvl = displayObj.tLvl+1;
			displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagModalLabel_'+tID.id+'">';
			cj.each(tID.children, function(i, cID){
				displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+cID.id+''+isItemChecked(cID.is_checked,cID.id)+' '+isItemReserved(cID.is_reserved,cID.id)+'" id="tagModalLabel_'+cID.id+'" tID="'+cID.id+'"><div class="'+isItemChildless(cID.children.length)+'"></div><div class="tag">'+cID.name+'</div><span><input type="radio" class="selectRadio" name="selectTag"/></span></dt>';
				if(cID.children.length > 0){
					displayObj.tLvl = displayObj.tLvl+1;
					displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagModalLabel_'+cID.id+'">';
					cj.each(cID.children, function(i, iID){
						displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+iID.id+''+isItemChecked(iID.is_reserved,iID.id)+' '+isItemReserved(iID.is_checked,iID.id)+'" id="tagModalLabel_'+iID.id+'" tID="'+iID.id+'"><div class="'+isItemChildless(iID.children.length)+'"></div><div class="tag">'+iID.name+'</div><span><input type="radio" class="selectRadio" name="selectTag"/></span></dt>';
						if(iID.children.length > 0){
							displayObj.tLvl = displayObj.tLvl+1;
							displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagModalLabel_'+iID.id+'">';
							cj.each(iID.children, function(i, jID){
								displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+jID.id+''+isItemChecked(jID.is_reserved,jID.id)+' '+isItemReserved(jID.is_checked,jID.id)+'" id="tagModalLabel_'+jID.id+'" tID="'+jID.id+'"><div class="'+isItemChildless(jID.children.length)+'"></div><div class="tag">'+jID.name+'</div><span><input type="radio" class="selectRadio" name="selectTag"/></span></dt>';
								if(jID.children.length > 0){
									displayObj.tLvl = displayObj.tLvl+1;
									displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+jID.id+'">';
									cj.each(jID.children, function(i, kID){
										displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+kID.id+''+isItemChecked(kID.is_reserved,kID.id)+' '+isItemReserved(kID.is_checked,kID.id)+'" id="tagModalLabel_'+kID.id+'" tID="'+kID.id+'"><div class="'+isItemChildless(kID.children.length)+'"></div><div class="tag">'+kID.name+'</div><span><input type="radio" class="selectRadio" name="selectTag"/></span></dt>';
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
function escapePositions(position, tidNum)
{
	if(tidNum == 292)
	{
		return '';
	}
	else
	{
		return position;
	}
}
/*Tab Swapping functionality between Issue Codes and Keywords*/
function swapTrees(tab){
	var currentTree = cj('.BBtree.edit.manage').attr('id');
	var getTab = cj(tab).attr('id');
	if(currentTree != getTab){
		cj('.BBtree.edit#' + currentTree).addClass('hidden loadingGif');
		cj('.BBtree.edit#' + currentTree).removeClass('manage');
		//cj('.BBtree.edit#' + currentTree).children().hide();
		cj('.crm-tagTabHeader li.tab#' + getTab).addClass('active');
		cj('.crm-tagTabHeader li.tab#' + currentTree).removeClass('active');
		cj('.BBtree.edit#' + getTab).addClass('manage');
		cj('.BBtree.edit#' + getTab).children().show();
		cj('.BBtree.edit#' + getTab).removeClass('hidden loadingGif');
	}
	// if(swapID != tabID)
	// {
	// 	var toCopy = cj('.BBtree.tabbed'+tabID+'.hidden dl').html();
	// 	cj('.crm-tagListSwapArea').attr('tID', tabID);
	// 	cj('.BBtree.edit.manage').html('');
	// 	cj('.BBtree.edit.manage').append(toCopy);
	// 	hoverTreeSlider('.BBtree.edit');
	// }
}
/*Clears out the location to be written, and then jquery appends it to the space*/
function writeDisplayObject(displayObj, treeLoc, pointToTab) {
	cj(treeLoc).append(displayObj.output);
	//console.log(treeLoc + ' ' +  returnTime());
	if(treeLoc == '#crm-tagListWrap .BBtree.edit')
	{
		//console.log(pointToTab);
		cj('.crm-tagTabHeader li#'+pointToTab).addClass('active');
		cj(treeLoc).removeClass('loadingGif');
		cj(treeLoc).children().show();
	}
	if(treeLoc == '.ui-dialog-content .BBtree.modal')
	{
		cj(treeLoc).removeClass('loadingGif');
		cj(treeLoc).children().show();
	}
}
/*Loading Gif*/
function callTagAjaxInitLoader(treeLoc, inpLoc) {
	//console.log(treeLoc);
	switch(inpLoc)
	{
		case 'init': break;
		case 'manage': cj('.BBtree.edit.manage').detach();cj('<div class="BBtree edit manage" id="tagLabel_291"></div>').appendTo('#crm-tagListWrap'); break;
		case 'tab': break;
		case 'contact': break;
		case 'backup': cj('.BBtree.edit.hidden').detach();cj('<div class="BBtree edit hidden tabbed" id="tagLabel_296"></div>').appendTo('#crm-tagListWrap');  break;
		default: break;
	}
	cj(treeLoc).addClass('loadingGif');
}
/*Slider & Interface functionality portion of things, when a tree initializes, as it loads, it runs through each
tag and binds/unbindes their click functionality to slide up/down... and stops the propagation if you click on
individual boxes inside the functionality or radio buttons. Last portion is for the Admin console that tells
number of tags named*/
function hoverTreeSlider(treeLoc){
	//console.log('implement hover slider begin: ' + returnTime());
	cj(treeLoc + ' dt .treeButton').unbind('click');
	cj(treeLoc + ' dt .treeButton').click(function() {
		if(cj(this).parent().hasClass('lv-0'))
		{
			if(cj(this).parent().hasClass('open'))
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

			var tagLabel = cj(this).parent().attr('id');
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
	//console.log('implement hover slider begin 1: ' + returnTime());
	if(cj(treeLoc).hasClass('manage') || cj(treeLoc).hasClass('modal'))
	{

	} else {
		cj(treeLoc + ' dt .tag').click(function(e){
			if(cj(this).parent().find('input:checked').length == 0){
				cj(this).parent().find('input').attr('checked', true);
				var tagLabelCheckRA = cj(this).parent().attr('id');
				checkRemoveAdd(tagLabelCheckRA);
			}
			else{
				cj(this).parent().find('input:checked').attr('checked', false);
				var tagLabelCheckRA = cj(this).parent().attr('id');
				checkRemoveAdd(tagLabelCheckRA);
			}
		});
	}
	//console.log('implement hover slider begin 2: ' + returnTime());
	// cj(treeLoc + ' dt .fCB li').click(function(e) {
	// 	e.stopPropagation();
	// });
	// cj(treeLoc + ' dt .selectRadio').click(function(e) {
	// 	e.stopPropagation();
	// });
	//console.log('implement hover slider begin 3: ' + returnTime());
	cj('.BBtree.edit dt').unbind('mouseenter mouseleave');
	cj('.BBtree.edit dt').hover(
	function(){
		if(cj(this).attr('id') != 'tagLabel_291' && cj(this).attr('id') != 'tagLabel_296' )
		{ 
			var tagCount = ' ';
			tagCount += cj('span.entityCount', this).html().match(/[0-9]+/);
		}
		var tagName = cj('span.name', this).html();
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
	//console.log('implement hover slider end: ' + returnTime());
}
function isItemChildless(childLength)
{
	if(childLength > 0)
	{
		return('treeButton');
	}
	else
	{
		return '';
	}
}
/*This poorly named function determines which tags are stubs, and which need arrows*/
function postJSON(treeLoc){
	//console.log('postJSONFUNC Start: ' + returnTime());
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
	//console.log('start of ParentFinder: ' + returnTime());
	var checkedKids = cj('dt.checked');
	for(var i = 0;i < checkedKids.length;i++)
	{
		//console.log('insideFor: ' + returnTime());
		var idGrab = cj(checkedKids[i]).attr('id');
		giveParentsIndicator(idGrab,'add');
	}
	//console.log('start of ParentFinder: ' + returnTime());
}
/*giveParents marks the tags parents in question as being marked up the tree to give inheritance and notation
that there's tags underneath*/
function giveParentsIndicator(tagLabel,toggleParent){
	//console.log('start of ParentIndicator: ' + returnTime());
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
	//console.log('end of ParentIndicator: ' + returnTime());
}
/*This is the add functionality that hooks into the tag ajax to add new tags, makes a dialog with jQUI
and then creates a request on done.*/
function makeModalAdd(tagLabel){
	//console.log('called modalAddMake: ' + returnTime());
	cj("#dialog").show();
	cj("#dialog").dialog({
		draggable: true,
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
			tagInfo.name = cj('dt#' + tagLabel + ' .tag .name').html();
			tagInfo.depth = cj('dt#' + tagLabel).hasClass('lv-4');
			if(tagInfo.depth == false) {
				var addDialogInfo = '<div class="modalHeader">Add new tag under ' + tagInfo.name + '</div>';
				addDialogInfo += '<div class="modalInputs">';
				addDialogInfo += '<div><span>Tag Name:</span ><input type="text" name="tagName" /></div>';
				addDialogInfo += '<div><span>Description:</span ><input type="text" name="tagDescription" /></div>';
				addDialogInfo += '<div><span class="parentName" id="'+tagLabel+'"></span></div>';
				addDialogInfo += '<div><span style="display:none">Or Choose A New Location</span><div></div></div>';
				addDialogInfo += '<div><span>Reserved:</span><input type="checkbox" name="isReserved"/></div>';
				cj('#dialog').html(addDialogInfo);
				cj('#dialog input:[name=tagName]').focus();
				cj("#dialog").dialog( "option", "buttons", 
				[
					{
						text: "Done",
						click: function() {
							tagCreate = new Object();
							tagCreate.tagDescription = '';
							cj('.ui-dialog-buttonset .ui-button').css("visibility", "hidden");
							cj('.ui-dialog-buttonpane').append('<div class="loadingGif"></div>');
							modalSetLoadingGif('.manage');
							tagCreate.tagName = cj('#dialog .modalInputs input:[name=tagName]').val();
							tagCreate.tagDescription = cj('#dialog .modalInputs input:[name=tagDescription]').val();
							tagCreate.parentId = cj('#dialog .modalInputs .parentName').attr('id').replace('tagLabel_', '');
							tagCreate.isReserved = cj('#dialog .modalInputs input:checked[name=isReserved]').length;
							//console.log('call modal add ajax ' + returnTime());
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
									//console.log('success modal add ajax: ' + returnTime());
									if(data.code != 1)
									{
										alert(data.message);
										cj('.ui-dialog-buttonpane .loadingGif').hide();
										cj('.ui-dialog-buttonset .ui-button').css("visibility", "visible");
										modalRemoveLoadingGif('.manage');
									}
									else
									{
										cj('#dialog').dialog('close');
										cj('#dialog').dialog('destroy');
										callTagAjax();
									}
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
				var addDialogInfo = '<div class="modalHeader"><span class="parentName" id="'+tagLabel+'">' + tagInfo.name + ' cannot add more children.</span></div>';
				cj("#dialog").html(addDialogInfo);
				cj("#dialog").dialog( "option", "buttons", 
				[
					{
						text: "Cancel",
						click: function() { 
							cj(this).dialog("close"); 
							cj(this).dialog("destroy"); 
						}
					}
				]);
			}
		}
	});
}
/*This is the Remove functionality that hooks into the tag ajax to add new tags, only difference is is that
it breaks out an error message to something more user friendly. it can be broken out into it's own function if
there are a copious amount of errors in the future to worry about other than Child Tag issues*/
function makeModalRemove(tagLabel){
	cj("#dialog").show();
	cj("#dialog").dialog({
		draggable: true,
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
			//console.log(tagLabel);
			tagInfo = new Object();
			tagInfo.id = tagLabel;
			tagInfo.name = cj('.BBtree.edit dt#' + tagLabel + ' .tag .name').html();
			tagInfo.isReserved = cj('.BBtree.edit dt#' + tagLabel).hasClass('isReserved');
			if(tagInfo.isReserved == false) {
				var addDialogInfo = '<div class="modalHeader"><span class="parentName" id="'+tagLabel+'">Remove Tag: ' + tagInfo.name + '</span></div>';
				//search the string for all mentions of tid="number"
				var tagChildren = cj('.BBtree.edit.manage dl#'+ tagLabel).html();
				//console.log(tagChildren);
				cj("#dialog").dialog( "option", "buttons", [
					{
						text: "Done",
						click: function() {
							cj('.ui-dialog-buttonset .ui-button').css("visibility", "hidden");
							cj('.ui-dialog-buttonpane').append('<div class="loadingGif"></div>');
							modalSetLoadingGif('.manage');
							tagRemove = new Object();
							tagRemove.parentId = cj('#dialog .modalHeader .parentName').attr('id').replace('tagLabel_', '');
							if(tagChildren == null)
							{
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
							} else {
								alert("Cannot remove a parent tag. Try deleting subtags before deleting parent tag.");
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
		draggable: true,
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
			tagInfo.name = cj('.BBtree.manage dt#' + tagLabel + ' .tag .name').html();
			tagInfo.description = cj('.BBtree.edit dt#' + tagLabel).attr('description');
			tagInfo.reserved = cj('.BBtree.edit dt#'+tagLabel).hasClass('isReserved');
			var updateDialogInfo = '';
			if(tagInfo.reserved == true){
			tagInfo.reserved = 'checked';} else {
			tagInfo.reserved = '';}
			updateDialogInfo += '<div class="modalHeader">Update Tag ' + tagInfo.name + '</div>';
			updateDialogInfo += '<div class="modalInputs">';
			updateDialogInfo += '<div><span>Tag Name:</span ><input type="text" name="tagName" value="'+tagInfo.name+'" /></div>';
			updateDialogInfo += '<div><span>Description:</span ><input type="text" name="tagDescription" value="'+tagInfo.description+'"/></div>';
			updateDialogInfo += '<div><span>Reserved:</span><input type="checkbox" name="isReserved" '+tagInfo.reserved+'/></div>';
			cj('#dialog').html(updateDialogInfo);
			cj('#dialog input:[name=tagName]').focus();
		},
		buttons: {
			"Done": function () {
				cj('.ui-dialog-buttonset .ui-button').css("visibility", "hidden");
				cj('.ui-dialog-buttonpane').append('<div class="loadingGif"></div>');
				modalSetLoadingGif('.manage');
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
/*Merge hijacks the old process to use their autocomplete function*/

/*makes a modal tree, this is the more involved one than the rest because it's calling a tree structure and
having to replicate much of the same combinations, it moves to modalSelectOnclick to open a dialog box*/
// function makeModalTree(tagLabel){
// 	cj("#dialog").show( );
// 	cj("#dialog").dialog({
// 		closeOnEscape: true,
// 		draggable: true,
// 		height: 500,
// 		width: 400,
// 		title: "Move Tag",
// 		modal: true, 
// 		bgiframe: true,
// 		close:{ },
// 		overlay: { 
// 			opacity: 0.2, 
// 			background: "black" 
// 		},
// 		open: function() {
// 			tagInfo = new Object();
// 			tagInfo.id = tagLabel;
// 			tagInfo.name = cj('.BBtree.edit.manage dt#' + tagLabel + ' .tag .name').html();
// 			tagInfo.reserved = cj('.BBtree.edit.manage dt#'+tagLabel).hasClass('isReserved');
// 			tagInfo.children = cj('.BBtree.edit.manage dl#'+tagLabel).children('dt');
// 			console.log(tagInfo.children);
// 			var treeDialogInfo;
// 			if(tagInfo.reserved == true){
// 			treeDialogInfo = '<div class="modalHeader">This tag is reserved and cannot be moved</div>';
// 			cj('#dialog').html(treeDialogInfo);
// 			} else {
// 			treeDialogInfo = '<div class="modalHeader">Move <span tID="'+tagInfo.id+'">' + tagInfo.name + ' under Tag...</span></div>';
// 			treeDialogInfo += '<div class="BBtree modal"></div>';
// 			cj('#dialog').html(treeDialogInfo);
// 			var modalTreeTop = cj('.BBtree.edit.manage dt#' + tagLabel).parents('.lv-0').children('.lv-0').attr('tid');
			
// 			callTagAjax('modal', modalTreeTop);
// 			}
// 		},
// 		buttons: {
// 			"Cancel": function() { 
// 				cj(this).dialog("close"); 
// 				cj(this).dialog("destroy");
// 			}
// 		} 
// 	});
// }
// this is the second portion of the Modal box, where it takes the click function and makes a 'move' button
// function modalSelectOnClick() {
// 	cj('.BBtree input.selectRadio').click(function(){
// 		var destinationId = cj(this).parent().parent('dt').attr('tid');
// 		cj("#dialog").dialog( "option", "buttons", [
// 			{
// 				text: "Move",
// 				click: function() {
// 					cj('.ui-dialog-buttonset .ui-button').css("visibility", "hidden");
// 					cj('.ui-dialog-buttonpane').append('<div class="loadingGif"></div>');
// 					modalSetLoadingGif();
// 					tagMove = new Object();
// 					tagMove.currentId = cj('.ui-dialog-content .modalHeader span').attr('tID').replace('tagLabel_','');
// 					tagMove.destinationId = destinationId;
// 					cj.ajax({
// 						url: '/civicrm/ajax/tag/update',
// 						data: {
// 							id: tagMove.currentId,
// 							parent_id: tagMove.destinationId
// 						},
// 						dataType: 'json',
// 						success: function(data, status, XMLHttpRequest) {
// 							if(data.code != 1)
// 							{
// 								alert(data.message);
// 								cj('.ui-dialog-buttonpane .loadingGif').hide();
// 								cj('.ui-dialog-buttonset .ui-button').css("visibility", "block");
// 								modalRemoveLoadingGif();
// 							}
// 							else
// 							{
// 								cj('#dialog').dialog('close');
// 								cj('#dialog').dialog('destroy');
// 								callTagAjax();
// 							}
// 						}
// 					});

// 				}
// 			},
// 			{
// 				text: "Cancel",
// 				click: function() { 
// 					cj(this).dialog("close"); 
// 					cj(this).dialog("destroy"); 
// 				}
// 			}
// 		]);
// 	});
// }
function modalSetLoadingGif(zone)
{
	cj('.BBtree' + zone).children().hide(); 
	cj('.BBtree' + zone).addClass('loadingGif');
}
function modalRemoveLoadingGif(zone)
{
	cj('.BBtree' + zone).removeClass('loadingGif');
	cj('.BBtree' + zone).children().show(); 
}
function returnTime()
{
	var time = new Date();
	var rTime = time.getMinutes() + ':' + time.getSeconds() + ':' + time.getMilliseconds();
	return rTime;
}
function setOpenTab(pointToTab)
{

	cj('dl#' + pointToTab).show(); 
	cj('.BBtree.edit#' + pointToTab).removeClass('loadingGif');
	cj('.crm-tagTabHeader li#'+pointToTab).addClass('active');
}