/*
* BBTree JS 0.3
* Now with infinite looping!
* And modular abilities!
* Last Updated: 1-15-2013
* Coded: Dan Pozzie (dpozzie@gmail.com)
*/

//alias for basic functionality, also provides pathing for calling function types
var BBTree = {
	startInstance: function(config)
	{	
		//Check remote timestamp first
		//then check cookie timestamp	
		//if cookies found skip getAjaxData
		//if cookie is found, send json to separate.
		//Config - BBTree.startInstance({writeSets: [291,296], treeTypeSet: 'edit'}); 
		callTree.setCurrentSettings(config);
	    //have to use a queue with ajax data
	    cj({})
	    	.queue(BBTree.getAjaxData)
	    	.queue(BBTree.writeTree);
	    //IDEA HERE IS TO GET THE DATA ASYNCHRONOUSLY THE FIRST TIME, IF DATA DOESN'T EXIST.
	    //AND THEN WRITE IT. THIS IS IMPORTANT BECAUSE WE DON'T WANT TO READ THE DATA EVERY TIME
	    //YOU GO AND FIND NEW CONTACTS. YOU WANT TO READ THE CONTACT DATA, NOT THE ENTIRE TREE
	    //AND THEN APPLY THE CONTACT DATA
	    //ALSO REMEMBER TO SET entity_counts: 1
	    //COOKIES REMEMBER CHECK COOKIES


	    //civicrm/ajax/entity_tag/get

	},
	initContainer: function()
	{
		callTree.treeSetupPage();
	},
	getAjaxData: function(next)
	{
		 callTree.callTreeAjax(function(){next();});
	},
	writeTree: function(next)
	{
		callTree.writeParsedData();//written, but hidden
		callTree.slideDownTree();
		callTree.setTagInfo();
		//now you need to find out what type of tree it is, and add other JS related tomfoolery with
		//like tagging functionality and special hover overs
		cj.each(callTree.currentSettings.displaySettings.writeSets, function(i, className){
			cj('.'+addTagLabel(className)).removeClass('loadingGif');
		});
		next();
	}
	/*
		Dynamically Added
		----------------
		SeparateTreeAjax
		----------------
		BBTree["rawJsonData"] = {};
		BBTree["parsedJsonData"] = {};
		BBTree["pulledData"] = {};
	*/
};
//
var callTree =  {
	defaultSettings: {
		pageSettings:{
			wrapper: '#BBTreeContainer',
			idName: 'BBTreeContainer',
			tagHolder: 'BBTree',
			container: 'div',
			hiddenTag: 'hidden'
		},
		displaySettings: { //Sets the default when the page has to be refreshed internally
			writeSets: [291], //Set [one], or [other] to show only one, use [291,296] for both (when you want to show KW & IC)
			treeCodeSet: 291, //IssueCodes = 291, KW = 296. Sets default tree to show first.
			currentTree: 291,
			treeTypeSet: 'tagging' //Sets default type to appear: edit, modal or tagging versions... adds 'boxes/checks'
		},
		callSettings:{
			ajaxUrl: '/civicrm/ajax/tag/tree',
			ajaxSettings:{
				entity_type: 'civicrm_contact',
				entity_id: 0,
				call_uri: window.location.href,
				entity_counts: 0
			},
			callback: false
		}
	},
	setCurrentSettings: function(config){
		if(config)
		{
			callTree["pulledConfig"] = {};
			cj.each(
				config,function(i, value){
	            	callTree.pulledConfig[i] = value;
	       	});
	    }
	    cj.extend(true, callTree.defaultSettings, callTree.pulledConfig); //sets the inital settings
		callTree['currentSettings'] = callTree.defaultSettings; //this is what EVERYTHING is based off of...
	},
	treeSetupPage: function(){ 
		//overwrites defaults technically can post these to a cookie and rewrite ::TODO::
		if(cj(callTree.currentSettings.pageSettings.wrapper).length == 0) //needs to append a div right after the function is called
		{
			document.write('<div id="BBTreeContainer"></div>'); //container needs to hold the size and shape
		}
		//make this a function to build x trees with y attributes, and everyone is hidden but the first
		callTree.buildBoxes(); //sends # of boxes to buildBoxes
		//cj(callTree.defaultSettings.pageSettings.wrapper).append('<div class="BBTree '+ this.config.displaySettings.treeTypeSet.toLowerCase() +'"></div>');
	},
	callTreeAjax: function(callback){
		var pageCID = getPageCID();
		cj.extend(callTree.currentSettings.callSettings.ajaxSettings.entity_id,pageCID); //overwrites CID if page is different. Check Add Contact?
		cj.ajax({
			url: callTree.currentSettings.callSettings.ajaxUrl,
			data: {
				entity_type: callTree.currentSettings.callSettings.ajaxSettings.entity_type,
				entity_id: callTree.currentSettings.callSettings.ajaxSettings.entity_id,
				call_uri: callTree.currentSettings.callSettings.ajaxSettings.call_uri,
				entity_counts: callTree.currentSettings.callSettings.ajaxSettings.entity_counts
			},
			dataType: 'json',
			success: function(data, status, XMLHttpRequest) {
				if(data.code != 1) {
					alert('Error');
				}
				else{
					callTree.separateTreeAjax(data.message);
					callback();
				}
			} 
		});
	},
	separateTreeAjax: function(data){
		//if cookie is found, send json to here.
		/*
		var dataStore = $.cookie("basket-data", JSON.stringify($("#ArticlesHolder").data()));
		var data=JSON.parse($.cookie("basket-data"))
		*/
		BBTree["rawJsonData"] = {}; //add new data properties
		BBTree["parsedJsonData"] = {};

		cj.each(data, function(i,tID){
			if(cj.inArray(parseFloat(tID.id), callTree.currentSettings.displaySettings.writeSets)>-1) //Checks against Allowed Sets
			{
				BBTree.rawJsonData[tID.id] = {'name':tID.name, 'children':tID.children};
				var displayObj = callTree.writeTreeInit(tID);
				callTree.currentSettings.displaySettings.currentTree = tID.id;
				callTree.parseTreeAjax(tID, displayObj);
			}
		});
	},
	writeTreeInit: function(tID){
		//start testing
		var displayObj = new Object;
		displayObj.tLvl = 0;
		displayObj.treeTop = tID.id;
		var tagLabel = addTagLabel(tID.id); //writes the identifying tag label
		displayObj.output = '<dl class="lv-'+displayObj.tLvl+'" id="'+tagLabel+'" tLvl="'+displayObj.tLvl+'">';
		displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id;
		if(cj.inArray(parseFloat(tID.id), callTree.currentSettings.displaySettings.writeSets)>-1) //only writes the 
		{
			if(callTree.currentSettings.callSettings.ajaxSettings.entity_id != 0)
			{
				displayObj.output += isItemMarked(tID.is_checked,'checked');
			}
			displayObj.output += ' ' + isItemMarked(tID.is_reserved,'isReserved');
		}
		displayObj.output += '" id="'+tagLabel+'" description="'+tID.description+'" tLvl="'+displayObj.tLvl+'" tID="'+tID.id+'">';
		displayObj.output += '<div class="ddControl '+isItemChildless(tID.children.length)+'"></div><div class="tag">'+tID.name+'</div>';
		displayObj.output += addControlBox(tagLabel, displayObj.treeTop, isItemMarked(tID.is_checked,'checked')) + '</dt>';
		return displayObj;
	},
	parseTreeAjax: function(tID, displayObj){
		var treeData = callTree.parseJsonInsides(tID, displayObj);
		BBTree.parsedJsonData[tID.id] = {'name':tID.name, 'data':treeData};
	},
	parseJsonInsides: function(tID, displayObj){
		if(tID.children.length >= 0) //as long as you're greater than or equal to 0, to start. opening the first tag will ++ it
		{
			cj.each(tID.children, function(i, cID){
				callTree.openChildJsonTag(cID, displayObj);
				callTree.writeJsonTag(cID, displayObj);
				callTree.closeChildJsonTag(tID, displayObj);
			});
		}
		if(displayObj.tLvl == 0){ //means you've reached the end!
			return(displayObj.output);	
		}
	},
	writeJsonTag: function(tID, displayObj){	
		var tagLabel = addTagLabel(tID.id);
		var isChecked = isItemMarked(tID.is_checked,'checked');
		displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id+' '+isItemMarked(tID.is_reserved,'isReserved')+'" id="'+tagLabel+'" description="'+tID.description+'" tLvl="'+displayObj.tLvl+'"  tID="'+tID.id+'"><div class="ddControl '+isItemChildless(tID.children.length)+'"></div><div class="tag">'+tID.name+'</div>'+addEntityCount(tID.entity_count)+ addControlBox(tagLabel, displayObj.treeTop, isItemMarked(tID.is_checked,'checked'))  + '</dt>';
		if(tID.children.length > 0)
		{
			cj.each(tID.children, function(i, cID){
				var isCChecked = isItemMarked(cID.is_checked,'checked');
				tagLabel = addTagLabel(cID.id);
				callTree.openChildJsonTag(cID,displayObj);
				displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+cID.id+' '+isItemMarked(cID.is_reserved,'isReserved')+'" id="'+tagLabel+'" description="'+cID.description+'" tLvl="'+displayObj.tLvl+'" cID="'+cID.id+'"><div class="ddControl '+isItemChildless(cID.children.length)+'"></div><div class="tag">'+cID.name+'</div>'+addEntityCount(cID.entity_count) + addControlBox(tagLabel, displayObj.treeTop, isItemMarked(tID.is_checked,'checked'))  + '</dt>';
				callTree.parseJsonInsides(cID,displayObj);	
				callTree.closeChildJsonTag(tID, displayObj);
			});
		}
		return displayObj.output;
	},
	openChildJsonTag: function(tID, displayObj){
		var tagLabel = addTagLabel(tID.id);
		displayObj.tLvl++;
		displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="'+tagLabel+'" tLvl="'+displayObj.tLvl+'" >';	
	},
	closeChildJsonTag: function(tID, displayObj){
		displayObj.tLvl--;
		displayObj.output += '</dl>';
	},
	buildBoxes: function() //reads from currentSettings to make the boxes to put lists in
	{
		cj.each(callTree.currentSettings.displaySettings.writeSets, function(i, className){
			var treeBox = '<div class="'+ callTree.currentSettings.pageSettings.tagHolder +' '+ callTree.currentSettings.displaySettings.treeTypeSet.toLowerCase() + ' ';
			if(className != callTree.currentSettings.displaySettings.treeCodeSet) //hide all boxes that aren't 'default' 
			{
				treeBox += 'hidden ';
			}
			else { //or else we give it the 'loading' treatment
				treeBox += 'loadingGif '; 
			}
			treeBox += addTagLabel(className);
			treeBox += '" id="'+addTagLabel(className)+'"></div>';
			cj(callTree.currentSettings.pageSettings.wrapper).append(treeBox);
		});	
	},
	writeParsedData: function()//write the tree to the CORRECT div
	{
		callTree.writeTabs();
		cj.each(callTree.currentSettings.displaySettings.writeSets, function(i, className){
			var treeTarget = callTree.currentSettings.pageSettings.wrapper + ' ';
			treeTarget += '.'+ callTree.currentSettings.pageSettings.tagHolder;
			treeTarget += '.'+ callTree.currentSettings.displaySettings.treeTypeSet.toLowerCase();
			treeTarget += '.'+ addTagLabel(className);
			cj(treeTarget).append(BBTree.parsedJsonData[className].data);
		});
	},
	writeTabs: function()
	{
		//need to figure out how to 
		if(cj('.crm-tagTabHeader ul li').length > 0)
		{
			cj('.crm-tagTabHeader ul').html('');
		}
		cj.each(callTree.currentSettings.displaySettings.writeSets, function(i, className){
			var tabInfo = {id: callTree.currentSettings.displaySettings.writeSets[i], name: BBTree.parsedJsonData[callTree.currentSettings.displaySettings.writeSets[i]].name, position: i, length: callTree.currentSettings.displaySettings.writeSets.length, isActive: ''};

			if(className == callTree.currentSettings.displaySettings.treeCodeSet) //hide all boxes that aren't 
			{
				tabInfo.isActive = 'active';
			}
			var tabHTML = '<li class="tab '+ tabInfo.isActive+ '" id="' +addTagLabel(tabInfo.id) + '" onclick="callTree.swapTrees(this);return false;">'+tabInfo.name+'</li>';
			cj('.crm-tagTabHeader ul').append(tabHTML);
		});
		
	},
	slideDownTree: function()
	{
		var treeLoc = '.'+callTree.currentSettings.pageSettings.tagHolder+'.'+callTree.currentSettings.displaySettings.treeTypeSet.toLowerCase();
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
					cj(treeLoc + ' dl#'+tagLabel).children('dl').slideUp('400', function() {
						cj('dl#'+tagLabel).removeClass('open');
					});
					break;
					case false:
					cj(treeLoc + ' dt#'+tagLabel+' div').addClass('open');
					cj(treeLoc + ' dl#'+tagLabel).children('dl').slideDown('400', function() {
						cj('dl#'+tagLabel).addClass('open');
					});
				}
			}
		});
	},
	setTagInfo: function()
	{
		var treeLoc = '.'+callTree.currentSettings.pageSettings.tagHolder+'.'+callTree.currentSettings.displaySettings.treeTypeSet.toLowerCase();
		cj(treeLoc+' dt').unbind('mouseenter mouseleave');
		cj(treeLoc+' dt').hover(
		function(){
			if(cj(this).attr('id') != 'tagLabel_291' && cj(this).attr('id') != 'tagLabel_296' )
			{ 
				var tagCount = ' ';
				tagCount += cj('span.entityCount', this).html().match(/[0-9]+/);
				if(tagCount == ' ' +null)
				{
					tagCount = cj('span.entityCount', this).html();
				}
			}
			var tagName = cj('div.tag', this).html();
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
	},
	swapTrees: function(tab)
	{
		var currentTree = addTagLabel(callTree.currentSettings.displaySettings.treeCodeSet);
		var treeLoc = '.'+callTree.currentSettings.pageSettings.tagHolder+'.'+callTree.currentSettings.displaySettings.treeTypeSet.toLowerCase();
		var getTab = cj(tab).attr('id');
		console.log(currentTree);
		console.log(treeLoc);
		console.log(getTab);

		if(currentTree != getTab){
			cj(treeLoc +'#' + currentTree).addClass('hidden');
			//cj('.BBtree.edit#' + currentTree).children().hide();
			cj('.crm-tagTabHeader li.tab#' + getTab).addClass('active');
			cj('.crm-tagTabHeader li.tab#' + currentTree).removeClass('active');
			cj(treeLoc +'#' + getTab).removeClass('hidden');
			callTree.currentSettings.displaySettings.treeCodeSet = [removeTagLabel(getTab)];
		}
	}
	//still need a reload tree option.
	//make sure to capture which ones are 'open'
	//write a different addControlBox function that functions based on the parameters sent
};


var BBTreeEdit = {
	//ON EDIT, make sure to update the saved data.

}
var BBTreeTag = {

}

//simple functions, oft repeated grouped by type
function getActiveTab()
{

}
//finds Page CID
function getPageCID()
{
	var cid = 0 ;
	var cidpre = /cid=\d*/.exec(document.location.search);
	var cidsplit = /\d.*/.exec(cidpre);
	if(cidsplit)
	{
		cid = cidsplit[0];
	}
	return cid;
}
function addTagLabel(tag)
{
	return 'tagLabel_' + tag;
}
function removeTagLabel(tag)
{
	return tag.replace('tagLabel_', '');
}
//checks to see if the user can add or remove tags
function getUserEditLevel()
{
	cj.ajax({
		url: '/civicrm/ajax/entity_tag/checkUserLevel/',
		data: {
			call_uri: window.location.href
		},
		dataType: 'json',
		success: function(data, status, XMLHttpRequest) {
			return data.code; //true = can use
		}
	});
}
//marks item as checked or reserved
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
//if has children, return either arrow or nothing
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
//add Entity Span
function addEntityCount(count)
{
	if(callTree.currentSettings.callSettings.ajaxSettings.entity_counts != 0)
	{
		var add = '<span class="entityCount">('+count+')</span>';
		return add;
	}
	else{
		var add = '<span class="entityCount" style="display:none">Unknown</span>';
		return add;
	}
}
//adds Control Box
function addControlBox(tagLabel, treeTop, isChecked) {
	var floatControlBox;
	if(callTree.currentSettings.displaySettings.treeTypeSet == 'edit')
	{
		floatControlBox = '<span class="fCB">';
		floatControlBox += '<ul>';
		if(treeTop == callTree.currentSettings.displaySettings.currentTree)
		{

			floatControlBox += '<li class="addTag" title="Add New Tag" onclick="makeModalAdd(\''+ tagLabel +'\')"></li>';
			floatControlBox += '<li class="removeTag" title="Remove Tag" onclick="makeModalRemove(\''+ tagLabel +'\')"></li>';
			floatControlBox += '<li class="moveTag" title="Move Tag" onclick="makeModalTree(\''+ tagLabel +'\')"></li>';
			floatControlBox += '<li class="updateTag" title="Update Tag" onclick="makeModalUpdate(\''+ tagLabel +'\')"></li>';
			floatControlBox += '<li class="mergeTag" title="Merge Tag" onclick="makeModalMerge(\''+ tagLabel +'\')"></li>';
		}
		if(treeTop == callTree.currentSettings.displaySettings.currentTree)
		{
			floatControlBox += '<li class="removeTag" title="Remove Keyword" onclick="makeModalRemove(\''+ tagLabel +'\')"></li>';
			floatControlBox += '<li class="updateTag" title="Update Keyword" onclick="makeModalUpdate(\''+ tagLabel +'\')"></li>';
			floatControlBox += '<li class="mergeTag" title="Merge Keyword" onclick="makeModalKWMerge(\''+ tagLabel +'\')"></li>';
			floatControlBox += '<li class="convertTag" title="Convert Keyword" onclick="makeModalConvert(\''+ tagLabel +'\')"></li>';
		}
		floatControlBox += '</span>';
		if(tagLabel == 'tagLabel_291' || tagLabel == 'tagLabel_296')
		{
			return '<span class="fCB" ><ul><li class="printTag"  onClick="printTags()"> </li><li class="addTag" title="Add New Tag" onclick="makeModalAdd(\''+ tagLabel +'\')"></li></ul></span>'; 
		} else { return(floatControlBox); }
	}
	if(callTree.currentSettings.displaySettings.treeTypeSet == 'tagging')
	{
		console.log('here');
		var displayChecked = '';
		floatControlBox = '<span class="fCB">';
		floatControlBox += '<ul>';
		floatControlBox += '<li>';
		if(isChecked == ' checked'){
			floatControlBox += '<input type="checkbox" class="checkbox checked"  checked onclick="checkRemoveAdd(\''+tagLabel+'\')"></input></li></ul>';
		} else {
			floatControlBox += '<input type="checkbox" class="checkbox" onclick="checkRemoveAdd(\''+tagLabel+'\')"></input></li></ul>';
		}
		floatControlBox += '</span>';
		if(tagLabel != 'tagLabel_291' && tagLabel != 'tagLabel_296')
		{
			return(floatControlBox);
		} else { 
			return ''; 
		}
	}
}
//remove at the end
function returnTime()
{
	var time = new Date();
	var rTime = time.getMinutes() + ':' + time.getSeconds() + ':' + time.getMilliseconds();
	console.log(rTime);
}
