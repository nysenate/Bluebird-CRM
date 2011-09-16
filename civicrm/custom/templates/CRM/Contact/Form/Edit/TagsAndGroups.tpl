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
<style>
#mainTabContainer ul.token-input-list-facebook, #tagSelect ul.token-input-list-facebook {
  width: 310px;
}
#mainTabContainer div#Tag .tag-section, #tagSelect .tag-section {
  float: right;
  margin-right: 50px;
  width: 310px;
}
#crm-container div.form-item #civiTree *, #crm-container .crm-form-block #civiTree * {
	text-align:left; float:left; font-size: 1em; padding:0px;
}
#crm-container div.form-item #civiTree * dl, #crm-container .crm-form-block #civiTree * dl {
	padding-left:10px;
}
#crm-container div.form-item #civiTree * dt, #crm-container .crm-form-block #civiTree * dt {
	width:auto;
	min-width:0;
	display:inline;
	line-height:18px;
}
.BBtree.ui-dialog-content{
	font-size:95%;
}
.BBtree.edit {
	width:335px;
	padding:10px;
	line-height:150%;
	overflow-x:hidden;
	overflow-y:scroll;
	height:580px;
	background-color:#fff;
	
}
* .BBtree dl {
	margin: 0; padding-left:25px; width:250px;

}
.BBtree dt .treeButton {
	 background: url("/sites/default/themes/rayCivicrm/nyss_skin/images/icons-3e3e3e.png") no-repeat -32px -15px;
	 height:16px; width:15px; float:left; margin:0px 5px 0 0;
}
.BBtree dl.lv-0 {
	float:left;
}
.BBtree dt.lv-0 .treeButton {
	background-position: -32px -15px;
}
.BBtree dt.lv-0.open .treeButton {
	background-position: -64px -15px;
}
.BBtree dt div.treeButton.open {
	background-position:-64px -15px;
}
.BBtree dt div.treeButton.open.stub, .BBtree dt>div.stub {
	background-position:-79px -143px; cursor:default;
}
.BBtree dt div.tag {
	width:150px;
}
.BBtree dt {
	cursor:pointer;padding-left:5px;font-weight:normal;font-size:12px;
}
.BBtree dt.lv-0 {
	font-weight:bold;
}
.BBtree dt.subChecked {
	background-color: #F1F8EB;
	border: 1px dashed #B0D730;
	margin: 0 -1px -1px;
}
.BBtree dt.checked {
	background-color: #F1F8EB;
	border: 1px solid #B0D730;
	margin: 0 -1px -1px;
}
.BBtree .fCB ul{
margin:0;
}
.BBtree .fCB li, div.block-civicrm .BBtree .fCB li, #crm-container .BBtree .fCB li{
	list-style-type:none;
	background: url('/sites/default/themes/rayCivicrm/nyss_skin/images/fcb.png') transparent no-repeat;
}
/*adjust the following to choose the default open portion for the drop-down*/
.BBtree dl.lv-2, .BBtree dl.lv-3, .BBtree dl.lv-4, .BBtree dl.lv-5, .BBtree dl.lv-6 {
	display:none;
}
/*this will remove check boxes from individual levels*/
.BBtree.edit dl.lv-0{
	width:320px !important;
}
.BBtree.edit dt.lv-0{
	width:295px !important;
}
.BBtree.edit dl.lv-1{
	width:285px !important;
}
.BBtree.edit dt.lv-1{
	width:285px !important;
}
.BBtree.edit dl.lv-2{
	width:275px !important;
}
.BBtree.edit dt.lv-2{
	width:275px !important;
}
.BBtree.edit dl.lv-3{
	width:265px !important;
}
.BBtree.edit dt.lv-3{
	width:265px !important;
}
.BBtree.edit dl.lv-4{
	width:255px !important;
}
.BBtree.edit dt.lv-4 div.tag {
	width:255px !important;
}
.BBtree.edit dl.lv-5{
	width:245px !important;
}
.BBtree.edit dt.lv-5 div.tag {
	width:245px !important;
}
.BBtree. dl.lv-0{
	width:300px;
}
.BBtree dl.lv-1{
	width:275px;
}
.BBtree dl.lv-2{
	width:250px;
}
.BBtree dl.lv-3{
	width:225px;
}
.BBtree dl.lv-4{
	width:200px;
}
.BBtree dt.lv-4 div.tag {
	width:175px;
}
.BBtree dl.lv-5{
	width:175px;
}
.BBtree dt.lv-5 div.tag {
	width:150px;
}
</style>
{/literal}
{literal}
<script>
var globalDisplayObj;
cj(document).ready(function() {
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
				/*have to note when you step in and out of levels*/
				displayObj.output = '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+tID.id+'">';
				displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+tID.id+''+isItemChecked(tID.is_checked,tID.id)+'" id="tagLabel_'+tID.id+'" tID="'+tID.id+'"><div class="treeButton"></div><div class="tag">'+tID.name+'</div></dt>';
				if(tID.children.length > 0){
					/*this is where the first iteration goes in*/
					displayObj.tLvl = displayObj.tLvl+1;
					displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+tID.id+'">';
					cj.each(tID.children, function(i, cID){
						displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+cID.id+''+isItemChecked(cID.is_checked,cID.id)+'" id="tagLabel_'+cID.id+'" tID="'+cID.id+'"><div class="treeButton"></div><div class="tag">'+cID.name+'</div></dt>';
						if(cID.children.length > 0){
							displayObj.tLvl = displayObj.tLvl+1;
							displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+cID.id+'">';
							cj.each(cID.children, function(i, iID){
								displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+iID.id+''+isItemChecked(iID.is_checked,iID.id)+'" id="tagLabel_'+iID.id+'" tID="'+iID.id+'"><div class="treeButton"></div><div class="tag">'+iID.name+'</div></dt>';
								if(iID.children.length > 0){
									displayObj.tLvl = displayObj.tLvl+1;
									displayObj.output += '<dl class="lv-'+displayObj.tLvl+'" id="tagLabel_'+iID.id+'">';
									cj.each(iID.children, function(i, jID){
										displayObj.output += '<dt class="lv-'+displayObj.tLvl+' issueCode-'+jID.id+''+isItemChecked(jID.is_checked,jID.id)+'" id="tagLabel_'+jID.id+'" tID="'+jID.id+'"><div class="treeButton"></div><div class="tag">'+jID.name+'</div></dt>';
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

			});
			writeDisplayObject(displayObj);
		}
	});
	setTimeout("postJSON()",2500);
	setTimeout("hoverFunctionality()",2500);

});
function isItemChecked(dataObj,tagLabel){
	tagLabel = 'tagLabel_' + tagLabel;
	if(dataObj == true){ 
		return ' checked';
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
		var parentElements = cj('dt#' + tagLabel).parents('dl');
		for(var i = 0;i < parentElements.length;i++)
		{
			var idGrab = cj(parentElements[i]).attr('id');
		        if(!(cj(idGrab).hasClass('lv-0')) && !(cj(idGrab).hasClass('lv-1'))  )
		        {
		        	cj('dt#' + idGrab).addClass('subChecked');
		        }
		}
		
	}
	if(toggleParent == 'remove')
	{
	
	}
}
function writeDisplayObject(displayObj){

	cj('#civiTree').append(displayObj.output);
}
function postJSON() {
	/*this is where you write out the toggle loader and the lv-x question;*/
	cj('#civiTree dt').each(function() {
		
		var idGrab = cj(this).attr('id');
		if(idGrab != '')
		{

			if(cj('dl#'+ idGrab).length == 0)
			{
				cj('dt#' + idGrab + ' div').addClass('stub');
			}
		}
	});
	/*top level defaults*/
	cj('dt.lv-0').addClass('open');
	cj('dt.lv-0 .treeButton').addClass('open');
	runParentFinder();
}

function hoverFunctionality() {
	cj('#civiTree').css('background-color: #000');
	/*the following is all post-load*/
	cj('#civiTree dt').hover(
		function(){
			var tagLabel = cj(this).attr('id');
			addControlBox(tagLabel);
		},
		function(){
			var tagLabel = cj(this).attr('id');
			removeControlBox(tagLabel);
		}
	);
	cj('#civiTree dt').click(function() {
		
		if(cj(this).hasClass('lv-0'))
		{
			if(cj(this).hasClass('open'))
			{
				cj('dt.lv-0').removeClass('open');
				cj('dt.lv-0 .treeButton').removeClass('open');
				cj('dl.lv-1').slideUp();
			}
			else {
				cj('dt.lv-0').addClass('open');
				cj('dt.lv-0 .treeButton').addClass('open');
				cj('dl.lv-1').slideDown();
			}
		} else {
			var tagLabel = cj(this).attr('id');
			if(cj('dt#'+tagLabel).hasClass('activeFCB'))
			{
			}else{
				if(cj('dl#'+tagLabel).is(':hidden') )
				{
					cj('dt#'+tagLabel+' div').addClass('open');
					cj('dl#'+tagLabel).slideDown();
				}
				else
				{
					cj('dt#'+tagLabel+' div').removeClass('open');
					cj('dl#'+tagLabel).slideUp();
				}
			}
		}
	});
	return '1';

}
function makeModalAdd(tagLabel){
	
	cj("#dialog").show( );
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
			tagInfo.name = cj('dt#' + tagLabel + ' .tag').html()
			var addDialogInfo = '<div class="modalHeader">Add new tag under ' + tagInfo.name + '</div>';
			addDialogInfo += '<div class="modalInputs">';
			addDialogInfo += '<div><span>Tag Name:</span ><input type="text" name="tagName" /></div>';
			addDialogInfo += '<div><span>Description:</span ><input type="text" name="tagName" /></div>';
			addDialogInfo += '<div><span>Insert Under ' + tagInfo.name +'</span></div>';
			addDialogInfo += '<div><span>Or Choose A New Location</span><div></div></div>';
			addDialogInfo += '<div><span>Reserved:</span><input type="checkbox" name="isReserved"/></div>';
			cj("#dialog").html(addDialogInfo);
		},
		buttons: {
			"Done": function () {
			
			},
			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			}
		} 
	});
}
function makeModalTree(){
	cj("#dialog").show( );
	cj("#dialog").dialog({
		draggable: false,
		height: 500,
		width: 300,
		title: "Title Goes here",
		modal: true, 
		bgiframe: true,
		close:{ },
		overlay: { 
			opacity: 0.2, 
			background: "black" 
		},
		open: function() {
			cj('#dialog').addClass('BBtree');
			var dialogInfo = cj('#civiTree.edit').html();
			cj('#dialog').html(dialogInfo);
			cj('#dialog').attr('id', 'civiTree');
			setTimeout("postJSON()",2500);
			setTimeout("hoverFunctionality()",2500);

		},
		buttons: {
			"Cancel": function() { 
				cj(this).dialog("close"); 
				cj(this).dialog("destroy"); 
			}
		} 
	});
}
function addControlBox(tagLabel) {
	var floatControlBox;
	var tagMouse = 'dt#'+tagLabel;
	floatControlBox = '<span class="fCB" style="padding:1px 0;margin-top:0px;float:right;" onmouseover="cj(\''+ tagMouse +'\').addClass(\'activeFCB\');" onmouseout="cj(\''+ tagMouse +'\').removeClass(\'activeFCB\');">';
	floatControlBox += '<ul>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; float:left;" onclick="makeModalAdd(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -17px 0px; float:left;" onclick="makeModal()"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -34px 0px; float:left;" onclick="makeModal()"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -50px 0px; float:left;" onclick="makeModal()"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:-1px 4px 0 -2px; background:none; float:left;">';
	if(cj(tagMouse).hasClass('checked')){
		floatControlBox += '<input type="checkbox" checked onclick="checkRemoveAdd(\''+tagLabel+'\')"></input></li></ul>';
	} else {
		floatControlBox += '<input type="checkbox" onclick="checkRemoveAdd(\''+tagLabel+'\')"></input></li></ul>';
	}
	floatControlBox += '</span>';
	if(tagMouse != 'dt#tagLabel_291')
	{
		cj('dt#'+tagLabel).append(floatControlBox);
	}
}
function checkRemoveAdd(tagLabel) {
	var n = cj('dt#'+ tagLabel).hasClass('checked');
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
				cj('dt#'+tagLabel).addClass('checked');
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
				cj('dt#'+tagLabel).removeClass('checked');
			}
		});
	}
}
function removeControlBox(tagLabel) {
	cj('dt#'+tagLabel + ' .fCB').remove();
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
		    	<div id="civiTree" class="BBtree edit">
			
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
	<tr><td>{include file="CRM/common/Tag.tpl"}</td></tr>
    </table>   
{if $title}
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{/if}