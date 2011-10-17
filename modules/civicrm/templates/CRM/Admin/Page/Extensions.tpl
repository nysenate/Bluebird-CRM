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

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Admin/Form/Extensions.tpl"}
{else}
    {capture assign='adminURL'}{crmURL p='civicrm/admin/setting/path' q="reset=1&civicrmDestination=/civicrm/admin/extensions?reset=1"}{/capture}
    {if !$extEnabled }
      <div class="crm-content-block crm-block">
        <div class="messages status">
             <div class="icon inform-icon"></div>
             {ts 1=$adminURL}Your extensions directory is not set or is not writable. Click <a href='%1'>here</a> to set the extension directory.{/ts}
        </div>
      </div>
    {else} {* extEnabled *}
      {if $action ne 1 and $action ne 2}
          <div class="action-link">
              <a href="{crmURL q="reset=1"}" id="new" class="button"><span><div class="icon refresh-icon"></div>{ts}Refresh{/ts}</span></a>
          </div>
      {/if}

      <div class="messages help">
        <p>{ts 1=$adminURL}CiviCRM extensions allow you to install additional features for your site. They can provide new functionality in three areas: Custom Searches, Report Templates and Payment Processors. In order to install an extension, you need to upload it manually to your <a href="%1">Extensions Directory</a>, reload this page and click Install. Once installed, extensions become available under the Custom Searches, Report Templates or Payment Processor Types administrative screens.{/ts}</p>
        <strong>{ts 1="http://forum.civicrm.org"}Please note that extensions are in a testing period during the 3.3 release cycle. Get in touch with the CiviCRM core team on the <a href="%1">community forum</a> if you've developed an extension which you want to share with the community.{/ts}</strong>
      </div>

      {include file="CRM/common/enableDisable.tpl"}
      {include file="CRM/common/jsortable.tpl"}
      {if $extensionRows}
        <div id="extensions">
          {strip}
          {* handle enable/disable actions*} 
          <table id="extensions" class="display">
            <thead>
              <tr>
                <th>{ts}Extension name (key){/ts}</th>
                <th>{ts}Status{/ts}</th>
                <th>{ts}Version{/ts}</th>
                <th>{ts}Enabled?{/ts}</th>
                <th>{ts}Type{/ts}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$extensionRows item=row}
              <tr id="row_{$row.id}" class="crm-extensions crm-extensions_{$row.id}{if NOT $row.is_active} disabled{/if}{if $row.upgradable} extension-upgradable{elseif $row.status eq 'installed'} extension-installed{/if}">
                <td class="crm-extensions-label">
                    <a class="collapsed" href="#"></a>&nbsp;<strong>{$row.label}</strong><br/>({$row.key})
                </td>
		<td class="crm-extensions-label">{$row.statusLabel} {if $row.upgradable}<br/>({ts}Outdated{/ts}){/if}</td>
                <td class="crm-extensions-label">{$row.version} {if $row.upgradable}<br/>({$row.upgradeVersion}){/if}</td>
                <td class="crm-extensions-is_active" id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td class="crm-extensions-description">{$row.type|capitalize}</td>
                <td>{$row.action|replace:'xx':$row.id}</td>
              </tr>
              <tr class="hiddenElement" id="crm-extensions-details-{$row.id}">
                  <td>
                      {include file="CRM/Admin/Page/ExtensionDetails.tpl" extension=$row}
                  </td>
                  <td></td><td></td><td></td><td></td><td></td>
              </tr>
              {/foreach}
            </tbody>
          </table>
          {/strip}
        </div>

      {else}
        <div class="messages status">
             <div class="icon inform-icon"></div>
            {ts}You have no locally available extensions and didn't download any information about publically available extensions from our server. Please click "Refresh" to update information about available extensions.{/ts}
        </div>    
      {/if}


          {if $action ne 1 and $action ne 2}
              <div class="action-link">
                <a href="{crmURL q="reset=1"}" id="new" class="button"><span><div class="icon refresh-icon"></div>{ts}Refresh{/ts}</span></a>
              </div>
          {/if}
          
          {literal}
          <script type="text/javascript">
            cj( function( ) {
                cj('.collapsed').click( function( ) {
                    var currentObj = cj( this );
                    if ( currentObj.hasClass( 'expanded') ) {
                        currentObj.removeClass( 'expanded' );
                        currentObj.parent( ).parent( ).next( ).hide( );
                    } else {
                        currentObj.addClass( 'expanded' );
                        currentObj.parent( ).parent( ).next( ).show( ); 
                    }
                    
                    return false;
                });
            });
          </script>
          {/literal}
    {/if}
{/if}
