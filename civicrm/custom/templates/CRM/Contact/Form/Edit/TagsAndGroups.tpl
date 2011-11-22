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
{/literal}
{literal}
<script type="text/javascript">
var cidpre = /cid=\d*/.exec(document.location.search);
var cidsplit = /\d.*/.exec(cidpre);
if(cidsplit != null){
	var cid = cidsplit[0];
}
else { var cid = ''; }
cj(document).ready(function() {	
	
	resetBBTree('main', 'init');
});
function callTagListMain(treeLoc) {
	callTagAjaxInitLoader(treeLoc);
	cj.ajax({
		url: '/civicrm/ajax/tag/tree',
		data: {
			entity_type: 'civicrm_contact',
			entity_id: cid
			},
		dataType: 'json',
		success: function(data, status, XMLHttpRequest) {
			/*set variables*/
			var displayObj = [];
			displayObj.tLvl = 0;
			/*error handler goes here*/
			if(data.code != 1) {alert('fails');}
			cj.each(data.message, function(i,tID){
				if(tID.id == '291')
				{				
				/*have to note when you step in and out of levels*/
				displayObj.output = '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+tID.id+'">';
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
						displayObj.output += addControlBox(cIDLabel, cIDChecked, cID.id)+'</dt>';
						if(cID.children.length > 0){
							displayObj.tLvl = displayObj.tLvl+1;
							displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+cID.id+'">';
							cj.each(cID.children, function(i, iID){
								var iIDChecked = isItemChecked(iID.is_checked,iID.id);
								displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+iID.id+''+iIDChecked+' '+isItemReserved(iID.is_reserved,iID.id)+'" id="tagLabel_'+iID.id+'" description="'+iID.description+'" tID="'+iID.id+'"><div class="treeButton"></div><div class="tag">'+iID.name+'</div>';
								var iIDLabel = 'tagLabel_'+iID.id;
								displayObj.output += addControlBox(iIDLabel, iIDChecked, iID.id)+'</dt>';
								if(iID.children.length > 0){
									displayObj.tLvl = displayObj.tLvl+1;
									displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+iID.id+'">';
									cj.each(iID.children, function(i, jID){
										var jIDChecked = isItemChecked(jID.is_checked,jID.id);
										displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+jID.id+''+jIDChecked+' '+isItemReserved(jID.is_reserved,jID.id)+'" id="tagLabel_'+jID.id+'" description="'+jID.description+'" tID="'+jID.id+'"><div class="treeButton"></div><div class="tag">'+jID.name+'</div>';
										var jIDLabel = 'tagLabel_'+jID.id;
										displayObj.output += addControlBox(jIDLabel, jIDChecked, jID.id)+'</dt>';
										if(jID.children.length > 0){
											displayObj.tLvl = displayObj.tLvl+1;
											displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+jID.id+'">';
											cj.each(jID.children, function(i, kID){
												var kIDChecked = isItemChecked(kID.is_checked,kID.id);
												displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+kID.id+''+kIDChecked+' '+isItemReserved(kID.is_reserved,kID.id)+'" id="tagLabel_'+kID.id+'" description="'+kID.description+'" tID="'+kID.id+'"><div class="treeButton"></div><div class="tag">'+kID.name+'</div>';
												var kIDLabel = 'tagLabel_'+kID.id;
												displayObj.output += addControlBox(kIDLabel, kIDChecked, kID.id)+'</dt>';
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
				}

			});
			writeDisplayObject(displayObj, treeLoc);
		}

	});
}
function writeDisplayObject(displayObj, treeLoc) {
	cj(treeLoc).html('');
	cj(treeLoc).append(displayObj.output);
}

function callTagAjaxInitLoader(treeLoc) {
	cj(treeLoc).addClass('loadingGif');
}
function resetBBTree(inpLoc, order) {
	var treeLoc;
	switch(inpLoc)
	{
		case 'main': treeLoc = '#crm-tagListWrap .BBtree.edit';callTagListMain(treeLoc); break;
		case 'modal': treeLoc = '.ui-dialog-content .BBtree.modal'; callTagListModal(treeLoc);  break;
		default: alert('No Tree Found'); break;
	}
	cj(treeLoc).ajaxComplete(function(){
		cj(treeLoc).removeClass('loadingGif');
		if(navigator.appName == 'Microsoft Internet Explorer'){
			if(order == 'init'){ setTimeout(function(){hoverTreeSlider(treeLoc)},2000); }
			setTimeout(function(){postJSON(treeLoc)},2000);
		} else {
			if(order == 'init'){ setTimeout(function(){hoverTreeSlider(treeLoc)},200);}
			setTimeout(function(){postJSON(treeLoc)},200);
		}
		if(inpLoc == 'modal') { 
			modalSelectOnClick();
		}
	});
}
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
			
			if(cj('dl#'+tagLabel).is(':visible'))
			{
				cj(treeLoc + ' dt#'+tagLabel+' div').removeClass('open');
				cj(treeLoc + ' dl#'+tagLabel).slideUp();
			}
			if(cj('dl#'+tagLabel).is(':hidden') )
			{
				cj(treeLoc + ' dt#'+tagLabel+' div').addClass('open');
				cj(treeLoc + ' dl#'+tagLabel).slideDown();
			}
		}
	});
	cj(treeLoc + ' dt .fCB li').click(function(e) {
		e.stopPropagation();
	});
	cj(treeLoc + ' dt .selectRadio').click(function(e) {
			e.stopPropagation();
	});
}

function postJSON(treeLoc){
	/*this is where you write out the toggle loader and the lv-x question;*/
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
function isItemChecked(dataObj,tagLabel){
	tagLabel = 'tagLabel_' + tagLabel;
	if(dataObj == true){ 
		return ' checked';
	}
	else{ return '';}
}
function isItemReserved(dataObj,tagLabel){
	if(dataObj == '1'){ 
		return 'isReserved';
	}
	else{ return '';}
}
function runParentFinder(){
	var checkedKids = cj('dt.checked');
	for(var i = 0;i < checkedKids.length;i++)
	{
		var idGrab = cj(checkedKids[i]).attr('id');
		giveParentsIndicator(idGrab,'add');
	}
}
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
function addControlBox(tagLabel, IDChecked, tagID) {
	var floatControlBox;
	var tagMouse = '.BBtree.edit dt#'+tagLabel;
	floatControlBox = '<span class="fCB" style="padding:1px 0;float:right;">';
	floatControlBox += '<ul>';
	/*floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; float:left;" onclick="makeModalAdd(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -17px 0px; float:left;" onclick="makeModalRemove(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -34px 0px; float:left;" onclick="makeModalTree(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -50px 0px; float:left;" onclick="makeModalUpdate(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -66px 0px; float:left;" onclick="makeModalMerge(\''+ tagLabel +'\')"></li>';*/
	floatControlBox += '<li style="height:16px; width:16px; margin:-1px 4px 0 -2px; background:none; float:left;">';
	if(IDChecked == ' checked'){
		floatControlBox += '<input id="tag['+tagID+']" name="tag['+tagID+']" type="checkbox" class="checkbox form-checkbox" value="1" checked></input></li></ul>';
	} else {
		floatControlBox += '<input id="tag['+tagID+']" name="tag['+tagID+']" type="checkbox" class="checkbox form-checkbox" value="1"></input></li></ul>';
	}
	floatControlBox += '</span>';
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
{if $title}
<div id="dialog">

</div>
<div class="crm-accordion-wrapper crm-tagGroup-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{$title} 
  </div><!-- /.crm-accordion-header -->
  <div class="crm-accordion-body" id="tagGroup">
{/if}
    <table class="form-layout-compressed{if $context EQ 'profile'} crm-profile-tagsandgroups{/if}" style="width:98%">
	<tr>
	    {foreach key=key item=item from=$tagGroup}
		{* $type assigned from dynamic.tpl *}
		{if !$type || $type eq $key }
		<td width={cycle name=tdWidth values="70%","30%"}><span class="label">{if $title}{$form.$key.label}{/if}</span>
		    <div id="crm-tagListWrap">
		    {if $key eq 'tag'}
		    	<div class="BBtree edit">
			
			</div>
		    {else}
		    <table id="crm-tagGroupTable">
			{foreach key=k item=it from=$form.$key}
			    {if $k|is_numeric}
				<tr class={cycle values="'odd-row','even-row'" name=$key} id="crm-tagRow{$k}">
				    <td>
                   			<strong>{$it.html}</strong><br /> {*LCD retain for groups list*}
					{if $item.$k.description}
					    <div class="description">
						{$item.$k.description}
					    </div>
					{/if}
				    </td>
				</tr>
			    {/if}
			{/foreach}   
		    </table>
		    </div>
		    {/if}
		</td>
		{/if}
	    {/foreach}
	</tr>
	<tr><td colspan="2" class="groupTagsKeywords">{include file="CRM/common/Tag.tpl"}</td></tr>
    </table>   
{if $title}
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{/if}