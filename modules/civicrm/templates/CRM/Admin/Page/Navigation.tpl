{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
{if $action eq 1 or $action eq 2 or $action eq 8}
    {include file="CRM/Admin/Form/Navigation.tpl"}
{else}
    <div id="help">
        {ts}Customize the CiviCRM navigation menu bar for your users here.{/ts} {help id="id-navigation"}
    </div>

<div class="crm-block crm-content-block">
    <div id="new-menu-item">
        <a href="{crmURL p="civicrm/admin/menu" q="action=add&reset=1"}" class="button" style="margin-left: 6px;"><span>&raquo; {ts}New Menu Item{/ts}</span></a>&nbsp;&nbsp;&nbsp;&nbsp;
        <span id="reset-menu" class="success-status" style="display:none">
        {capture assign=rebuildURL}{crmURL p='civicrm/admin/menu' q="reset=1"}{/capture}
        {ts 1=$rebuildURL}<a href='%1' title="Reload page"><strong>Click here</strong></a> to reload the page and see your changes in the menu bar above.{/ts}
        </span><br/><br/>
    </div>
    <div class="spacer"></div>
    <div id="navigation-tree" class="navigation-tree" style="height:auto; border-collapse:separate;"></div>
    <div class="spacer"></div>
</div>
    {literal}
    <script type="text/javascript">
    cj(function () {
        cj("#navigation-tree").tree({
            data  : {
                type  : "json",
                async : true, 
                url : {/literal}"{crmURL p='civicrm/ajax/menu' h=0 }&key={crmKey name='civicrm/ajax/menu'}"{literal}
            },
            rules : {
                droppable : [ "tree-drop" ],
                multiple : true,
                deletable : "all",
                draggable : "all"
            },
            ui : {
                context	: 
                [ 
                    { 
                        id		: "edit",
                        label	: "Edit", 
                        icon	: "create.png",
                        visible	: function (node, treeObject) { if(node.length != 1) return false; return treeObject.check("renameable", node); }, 
                        action	: function (node, treeObject) { 
                                    var nid = cj(node).attr('id');
                                    var nodeID = nid.substr( 5 );
                                    var editURL = {/literal}"{crmURL p='civicrm/admin/menu' h=0 q='action=update&reset=1&id='}"{literal} + nodeID;
                                    location.href =  editURL;  
                                  } 
                    },
                    "separator",
                    { 
                        id		: "rename",
                        label	: "Rename", 
                        icon	: "rename.png",
                        visible	: function (node, treeObject) { if(node.length != 1) return false; return treeObject.check("renameable", node); }, 
                        action	: function (node, treeObject) { treeObject.rename(node); } 
                    },
                    "separator",
                    { 
                        id		: "delete",
                        label	: "Delete",
                        icon	: "remove.png",
                        visible	: function (node, treeObject) { var ok = true; cj.each(node, function () { if(treeObject.check("deletable", this) == false) ok = false; return false; }); return ok; }, 
                        action	: function (node, treeObject) { cj.each(node, function () { treeObject.remove(this); }); } 
                    }
                ]
            },                
            callback : {
                onmove  : function( node, reference, type ) {
                    var postURL = {/literal}"{crmURL p='civicrm/ajax/menutree' h=0 }&key={crmKey name='civicrm/ajax/menutree'}"{literal};
                    cj.get( postURL + '&type=move&id=' + node.id + '&ref_id=' + (reference === -1 ? 0 : reference.id) + '&move_type=' + type, 
                        function (data) {
            			    cj("#reset-menu").show( );
            		    }
            		);                		                    
                },
                onrename : function( node ) {
                    var postURL = {/literal}"{crmURL p='civicrm/ajax/menutree' h=0 }&key={crmKey name='civicrm/ajax/menutree'}"{literal};
                    cj.get( postURL + '&type=rename&id=' + node.id + '&data=' + cj( node ).children("a:visible").text(), 
                        function (data) {
            			    cj("#reset-menu").show( );
            		    }
            		);
    			},
    			beforedelete : function( node ) {
    			    var nid = cj( node ).attr("id");
    			    var menuItem = cj("#" + nid ).find("a").html();
    			    var deleteMsg = {/literal}"Are you sure you want to delete this menu item: "{literal} + menuItem + {/literal}" ? This action can not be undone."{literal};
    				return confirm( deleteMsg );
    			},
    			ondelete : function ( node ) {
                    var postURL = {/literal}"{crmURL p='civicrm/ajax/menutree' h=0 }&key={crmKey name='civicrm/ajax/menutree'}"{literal};
                    cj.get( postURL + '&type=delete&id=' + node.id, 
                        function (data) {
            			    cj("#reset-menu").show( );
            		    }
            		);
    			}
            }
        });
    });

    </script>
    {/literal}

{/if}
