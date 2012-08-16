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