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
<link type="text/css" rel="stylesheet" media="screen,projection" href="/sites/default/themes/Bluebird/nyss_skin/tags/tags.css" />
<style>
#crm-tagListWrap {padding:10px; height:auto;}
</style>
{/literal}
{*NYSS*}
<div id="TagGroups" class="view-content">
<h3>{if !$hideContext}{ts}Tags{/ts}{/if}</h3>
    <div id="dialog">
    
    </div>

    <div class="JSTreeInit"></div>
    {literal}
    <script>
        console.log("trying");
        var jsTreePageSettings = {
          pageElements: {
            wrapper: ['BBTreeContainer'],
            tagHolder: ['BBTree'],
            prefix: ['BBtree']
          },
          dataSettings: {
            pullSets: [291,296,292],
            // pullSets: [291],
            // entity_id: 18002
            entity_id: BBCID
          },
          displaySettings: {
            // wide:false
            // lock:false
            // edit:true
            tagging:true
          },
          callAjax: {
            // data: undefined,
            // url: 'localtagdata.json'
          }
        }
        console.log("trying");
        jstree.init(jsTreePageSettings, jstree.views);
    </script>
    {/literal}
    
  

    
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
<script type="text/javascript">
  //load_init_check(fname);
</script>
