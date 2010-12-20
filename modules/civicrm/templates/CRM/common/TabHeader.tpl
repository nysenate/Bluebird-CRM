{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
{if $tabHeader and count($tabHeader) gt 1}
<div id="mainTabContainer">
<ul>
   {foreach from=$tabHeader key=tabName item=tabValue}
      <li id="tab_{$tabName}" class="crm-tab-button ui-corner-all {if !$tabValue.valid}disabled{/if}">
      {if $tabValue.link and $tabValue.active}
         <a href="{$tabValue.link}" title="{$tabValue.title}{if !$tabValue.valid} ({ts}disabled{/ts}){/if}"><span> </span> {$tabValue.title}</a>
      {else}
         <span {if !$tabValue.valid}title="{ts}disabled{/ts}"{/if}>{$tabValue.title}</span>
      {/if}
      </li>
   {/foreach}
</ul>
</div>
{/if}


<script type="text/javascript"> 
   var selectedTab = 'EventInfo';
   {if $selectedTab}selectedTab = "{$selectedTab}";{/if}
   var spinnerImage = '<img src="{$config->resourceBase}i/loading.gif" style="width:10px;height:10px"/>';    
{literal}
//explicitly stop spinner
function stopSpinner( ) {
 cj('li.crm-tab-button').each(function(){ cj(this).find('span').text(' ');})	 
}

    cj( function() {
        var tabIndex = cj('#tab_' + selectedTab).prevAll().length
        cj("#mainTabContainer").tabs( {
            selected: tabIndex,
            spinner: spinnerImage,
            select: function(event, ui) {
                // we need to change the action of parent form, so that form submits to correct page
                var url = cj.data(ui.tab, 'load.tabs');
                {/literal}{if $config->userFramework eq 'Drupal'}{literal}
                    var actionUrl = url.split( '?' );
                    var actualUrl = actionUrl[0];
                {/literal}{else}{literal}
                    var actionUrl = url.split( '&' );
                    var actualUrl = actionUrl[0] + '&' + actionUrl[1];
                {/literal}{/if}{literal}

                cj(this).parents("form").attr("action", actualUrl )                
                
                if ( !global_formNavigate ) {
                    var message = '{/literal}{ts escape="js"}Confirm\n\nAre you sure you want to navigate away from this tab?\n\nYou have unsaved changes.\n\nPress OK to continue, or Cancel to stay on the current tab.{/ts}{literal}';
                    if ( !confirm( message ) ) {
                        return false;
                    } else {
                        global_formNavigate = true;
                    }
                }
                return true;
            },
	    load: stopSpinner
        });        
    });
{/literal}
</script>
