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
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/nyss_skin/tags.css" />
<script src="/sites/default/themes/Bluebird/scripts/bbtree.js" type="text/javascript"></script>
<style>
.crm-tagGroup-accordion #crm-tagListWrap {
	display:none;
}
.crm-tagGroupsList div.label, .crm-tagList div.label {
	cursor:pointer;
}
.crm-tagGroupsList div.label div.arrow, .crm-tagList div.label div.arrow {
	background: url('/sites/default/themes/Bluebird/nyss_skin/images/icons-3e3e3e.png') no-repeat -32px -15px;
	height: 16px;
	width: 15px;
	float: left;
	margin: 0px 5px 0 0px;
}
.crm-tagGroupsList div.label div.arrow.open, .crm-tagList div.label div.open {
	background-position:-64px -15px;
}
.BBtree.edit.contact .fCB {
	display:block;
}
</style>
<script type="text/javascript">
function rollDownGroup(tag){
	if(cj(tag + ' div.label div.arrow').hasClass('open'))
	{
		cj(tag + ' div.label div.arrow').removeClass('open');
	} else {
		cj(tag + ' div.label div.arrow').addClass('open');
	}
	cj(tag + ' #crm-tagListWrap').toggle('fast');
}
var cidpre = /cid=\d*/.exec(document.location.search);
var cidsplit = /\d.*/.exec(cidpre);
if(cidsplit != null){
	var cid = cidsplit[0];
}
else { var cid = ''; }
cj(document).ready(function() {	
	callTagAjaxInitLoader('#crm-tagListWrap .BBtree.edit');
	callTagAjax();
});
function addControlBox(tagLabel, IDChecked, tagID) {
	var floatControlBox;
	var tagMouse = '.BBtree.edit dt#'+tagLabel;
	var displayChecked = '';
	if(IDChecked == ' checked'){displayChecked = 'display:inline;"';}
	floatControlBox = '<span class="fCB" style="padding:1px 0; float:right; '+displayChecked+'">';
	floatControlBox += '<ul>';
	/*floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; float:left;" onclick="makeModalAdd(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -17px 0px; float:left;" onclick="makeModalRemove(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -34px 0px; float:left;" onclick="makeModalTree(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -50px 0px; float:left;" onclick="makeModalUpdate(\''+ tagLabel +'\')"></li>';
	floatControlBox += '<li style="height:16px; width:16px; margin:auto 1px; background-position: -66px 0px; float:left;" onclick="makeModalMerge(\''+ tagLabel +'\')"></li>';*/
	floatControlBox += '<li style="height:16px; width:16px; margin:-1px 4px 0 -2px; background:none; float:left;">';
	if(IDChecked == ' checked'){
		floatControlBox += '<input id="tag['+tagID+']" name="tag['+tagID+']" type="checkbox" onclick="checkRemoveAdd(\''+tagLabel+'\')" class="checkbox form-checkbox" value="1" checked></input></li></ul>';
	} else {
		floatControlBox += '<input id="tag['+tagID+']" name="tag['+tagID+']" type="checkbox" onclick="checkRemoveAdd(\''+tagLabel+'\')" class="checkbox form-checkbox" value="1"></input></li></ul>';
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
</script>
{/literal}
{if $title}
<div id="dialog">

</div>
<div class="crm-accordion-wrapper crm-tagGroup-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	<a href="#" class="whiteanchor">{$title}</a>
  </div><!-- /.crm-accordion-header -->
  <div class="crm-accordion-body" id="tagGroup">
{/if}

    <table class="form-layout-compressed{if $context EQ 'profile'} crm-profile-tagsandgroups{/if}" style="width:98%">
	
	    {foreach key=key item=item from=$tagGroup}
		{* $type assigned from dynamic.tpl *}
		{if !$type || $type eq $key }
		
			{if $key eq 'tag'}
			<tr>
				<td width="100%" class="crm-tagList"><div class="label" onClick="rollDownGroup('.crm-tagList');"><div class="arrow"></div>{if $title}{$form.$key.label}{/if}</div>
				    <div id="crm-tagListWrap">
					    <div class="BBtree edit contact">	
						</div>
						<div class="groupTagsKeywords">{include file="CRM/common/Tag.tpl"}</div>
					</div>
					
				</td>
			</tr>
			{/if}
			{if $key eq 'group'}
			<tr>
				<td width="100%" class="crm-tagGroupsList"><div class="label" onClick="rollDownGroup('.crm-tagGroupsList');"><div class="arrow"></div>{if $title}{$form.$key.label}{/if}</div>
				    <div id="crm-tagListWrap">
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
				</td>
			</tr>
			<tr></tr>
			{/if}
		{/if}
	    {/foreach}
	
    </table>   
{if $title}
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{/if}
