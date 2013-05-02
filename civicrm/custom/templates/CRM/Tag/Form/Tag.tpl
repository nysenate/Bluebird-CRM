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
<script>
var BBCID = {/literal}{$entityID}{literal};
var BBActionConst = {/literal}{$action}{literal};
</script>
{/literal}
{literal}
<script src="/sites/default/themes/Bluebird/scripts/bbtree.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/nyss_skin/tags/tags.css" />
<script type="text/javascript">
BBTree.startInstance({pullSets: [291], buttonType: 'tagging'}); 
</script>
<style>
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
//5517
if ( !cj('.tag-section').hasClass('crm-processed-input') ) {
  cj('.tag-section .content').addClass('tagset-view-only');
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
