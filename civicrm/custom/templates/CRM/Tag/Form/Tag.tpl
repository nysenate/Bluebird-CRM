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
{* this template is used for adding/editing tags  *}
{literal}
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/nyss_skin/tags.css" />
<script src="/sites/default/themes/Bluebird/scripts/bbtree.js" type="text/javascript"></script>
<style>
/*.hit {ldelim}padding-left:10px;{rdelim}*/ /*NYSS*/
.tree li {ldelim}padding-left:10px;{rdelim}
#Tag .tree .collapsable .hit {ldelim}background:url('{$config->resourceBase}i/menu-expanded.png') no-repeat left 8px;padding-left: 9px;cursor:pointer{rdelim}
#Tag .tree .expandable .hit {ldelim}background:url('{$config->resourceBase}i/menu-collapsed.png') no-repeat left 6px;padding-left: 9px;cursor:pointer{rdelim}
#Tag #tagtree .highlighted {ldelim}background-color:lightgrey;{rdelim}
.jstree-icon {ldelim}border: 1px solid white;{rdelim} /*NYSS*/
/*to add to tags.css*/
#crm-tagListWrap {padding:10px; height:auto;}
</style>
{/literal}
{*NYSS*}
<div id="TagGroups" class="view-content">
<h3>{if !$hideContext}{ts}Tags{/ts}{/if}</h3>
    <div id="dialog">
    
    </div>
    {*NYSS add list of Issue Codes*}
    {if $contactIssueCode_list}
    	<div class="contactTagsList help"><strong>Issue Codes: </strong><span>{$contactIssueCode_list}</span></div>
    	<div class="clear"></div>
    {/if}
    <div id="crm-tagListWrap">

    {include file="CRM/common/Tag.tpl"}
    <script>cj('<div class="BBtree edit tab"></div>').insertAfter('.crm-section.tag-section.contact-tagset-296-section');</script>
        {*NYSS add list of leg positions with descriptions*}
        {if $legpositions}
        <div class="clear_left"></div>
        <div class="legpositions help"><span class="label">Legislative Position Descriptions</span><br />
        	<ul>
            {foreach from=$legpositions item="legposition"}
            	{if $legposition.description && $legposition.description neq 'No description available.'}
                	<li><strong>{$legposition.name}</strong> :: {$legposition.description}</li>
                {/if}
            {/foreach}
            </ul>
        </div>
        {/if}
    </div>

    {* Show Edit Tags link if in View mode *}
    {if $permission EQ 'edit' AND $action eq 16}
        <!--</fieldset>-->
    {else}
       <div class="form-item unobstructive">{$form.buttons.html}</div>
       <!--</fieldset>-->
    {/if}
  

    
</div>

<script type="text/javascript">

options = {ldelim} ajaxURL:"{crmURL p='civicrm/ajax/rest' h=0}"
       ,closetxt:'<div class="ui-icon ui-icon-close" style="float:left"></div>'
      {rdelim};//NYSS 5436
entityID={$entityID};
entityTable='{$entityTable}';
{literal}
var pageType = 'edit';
var cidpre = /cid=\d*/.exec(document.location.search);
var cidsplit = /\d.*/.exec(cidpre);
cid = cidsplit[0];
function hideStatus( ) {
    cj( '#restmsg' ).hide( );
}
cj(document).ready(function() {	
	callTagAjaxInitLoader('#crm-tagListWrap .BBtree.edit');
	callTagAjax();
});
function addControlBox(tagLabel, IDChecked) {
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
		floatControlBox += '<input type="checkbox" class="checkbox" checked onclick="checkRemoveAdd(\''+tagLabel+'\')"></input></li></ul>';
	} else {
		floatControlBox += '<input type="checkbox" class="checkbox" onclick="checkRemoveAdd(\''+tagLabel+'\')"></input></li></ul>';
	}
	floatControlBox += '</span>';
	if(tagMouse != '.BBtree.edit dt#tagLabel_291')
	{
		return(floatControlBox);
	} else { return ''; }
}
function checkRemoveAdd(tagLabel) {
	//for some reason there's still an onclick on issue codes.
	if(tagLabel != 'tagLabel_291'){
		//console.log('top of cRA: ' + returnTime());
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
					//console.log('success of cRA ajax: ' + returnTime());
					if(data.code != 1) {alert('fails');}
					cj('.BBtree.edit dt#'+tagLabel).addClass('checked');
					var temp = cj('.BBtree.edit dt#'+tagLabel+' .fCB').attr('style');
					temp += '; display:inline';
					cj('.BBtree.edit dt#'+tagLabel+' .fCB').attr('style', temp);
					giveParentsIndicator(tagLabel,'add');
					var tabCounter = cj('li#tab_tag em').html();
					var tagLiteralName = cj('.BBtree.edit dt#'+ tagLabel + ' .tag .name').html();
					var headList = cj('.contactTagsList.help span').html();
					if(headList)
					{
						var headSplit = headList.split(" • ");
						var appendAfter = headSplit.length;
						headSplit[appendAfter] = tagLiteralName;
						headSplit.sort();
						headList = headSplit.join(" • ");
						cj('.contactTagsList.help span').html(headList);
					}
					else
					{
						headList = tagLiteralName;
						cj('#TagGroups #dialog').append('<div class="contactTagsList help"><strong>Issue Codes: </strong><span>' + headList + '</span></div>');
					}
					cj('li#tab_tag em').html('').html(parseFloat(tabCounter)+1);
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
					var tabCounter = cj('li#tab_tag em').html();
					var tagLiteralName = cj('.BBtree.edit dt#'+ tagLabel + ' .name').html();
					var headList = cj('.contactTagsList.help span').html();
					var headSplit = headList.split(" • ");
					var appendAfter = headSplit.length;
					for(var i=0; i<headSplit.length;i++ )
					{ 
					if(headSplit[i]==tagLiteralName)
						headSplit.splice(i,1); 
					} 
					headList = headSplit.join(" • ");
					cj('.contactTagsList.help span').html(headList);
					cj('li#tab_tag em').html('').html(parseFloat(tabCounter)-1);
				}
			});
		}
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

{if $action eq 1 or $action eq 2 }
 <script type="text/javascript">
 {* this function is called to change the color of selected row(s) *}
    var fname = "{$form.formName}";	
    on_load_init_check(fname);
 </script>
{/if}
<script type="text/javascript">
  //load_init_check(fname);
</script>

