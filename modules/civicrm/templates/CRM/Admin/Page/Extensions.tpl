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

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Admin/Form/Extensions.tpl"}
{else}
    {if not $extEnabled}
      <div class="crm-content-block crm-block">
        <div class="messages status">
             <div class="icon inform-icon"></div>
             {capture assign='returnURL'}{crmURL p='civicrm/admin/extensions' q='reset=1'}{/capture}
             {capture assign='adminURL'}{crmURL p='civicrm/admin/setting/path' q="reset=1&destination=$returnURL"}{/capture}
             {ts 1=$adminURL}Your extensions directory is not set. Click <a href='%1'>here</a> to set the extension directory.{/ts}
        </div>
      </div>
    {else} {* extEnabled *}
      {if $action ne 1 and $action ne 2}
          <div class="action-link">
              <a href="{crmURL q="reset=1"}" id="new" class="button"><span><div class="icon refresh-icon"></div>{ts}Refresh{/ts}</span></a>
          </div>
      {/if}

      <div class="messages help">
        <p>{ts}CiviCRM extensions mechanism allow you to install small functional additions. They provide new functionality in three areas: Custom Searches, Report Templates and Payment Processors. In order to install the extension, you need to upload it manually to Extensions Directory, reload this page and click Install. It will also become available on Custom Reports, Report Templates and Payment Processor Types management screens.{/ts}</p>
        <strong>{ts}Please note that extensions functionality are in testing period in 3.3 release cycle. Get in touch with CiviCRM core team on project forums if you want to provide the community with your extension.{/ts}</strong>
      </div>

      <h3>{ts}Installed extensions{/ts}</h3>
      {include file="CRM/common/enableDisable.tpl"}
      {include file="CRM/common/jsortable.tpl"}
      {if $rows}
        <div id="extensions">
          {strip}
          {* handle enable/disable actions*} 
          <table id="installed-extensions" class="display">
            <thead>
              <tr>
                <th>{ts}Extension name{/ts}</th>
                <th>{ts}Version{/ts}</th>
                <th>{ts}Enabled?{/ts}</th>
                <th>{ts}Type{/ts}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$rows item=row}
              <tr id="row_{$row.id}" class="crm-installed-extensions crm-installed-extensions_{$row.id}{if NOT $row.is_active} disabled{/if}">
                <td class="crm-installed-extensions-label">
                    <a class="collapsed" href="#"></a>&nbsp;{$row.label} ( {$row.key} )
                </td>
                <td class="crm-installed-extensions-label">{$row.version}</td>
                <td class="crm-installed-extensions-is_active" id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                <td class="crm-installed-extensions-description">{$row.type|capitalize}</td>
                <td>{$row.action|replace:'xx':$row.id}</td>
              </tr>
              <tr class="hiddenElement" id="crm-installed-extensions-details-{$row.id}">
                  <td>
                      {include file="CRM/Admin/Page/ExtensionDetails.tpl" extension=$row}
                  </td>
                  <td></td><td></td><td></td><td></td>
              </tr>
              {/foreach}
            </tbody>
          </table>
          {/strip}
        </div>

      {else}
        <div class="messages status">
             <div class="icon inform-icon"></div>
            {ts}You have not installed any extensions.{/ts}
        </div>    
      {/if}
    <br/>
    <h3>{ts}Uploaded extensions{/ts}</h3>
          {if $rowsUploaded}
            <div id="extensionsUploaded">
              {strip}
              {* handle enable/disable actions*} 
              <table id="uploaded-extensions" class="display">
                <thead>
                  <tr>
                    <th>{ts}Extension name{/ts}</th>
                    <th>{ts}Version{/ts}</th>
                    <th>{ts}Enabled?{/ts}</th>
                    <th>{ts}Type{/ts}</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>                
                  {assign var='rowCount' value = 1}    
                  {foreach from=$rowsUploaded item=row}
                  <tr id="uploaded-row_{$rowCount}" class="crm-uploaded-extensions crm-uploaded-extensions_{$rowCount}">
                    <td class="crm-uploaded-extensions-label"> <a class="collapsed" href="#"></a>&nbsp;{$row.label} ( {$row.key} )
                        <span class="hiddenElement description"><br/><br/>{$row.description}</span>
                    </td>
                    <td class="crm-uploaded-extensions-label">{$row.version}</td>	
                    <td class="crm-uploaded-extensions-is_active" id="uploaded-row_{$rowCount}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
                    <td class="crm-uploaded-extensions-description">{$row.type|capitalize}</td>
                    <td>{$row.action|replace:'xx':$rowCount}</td>
                  </tr>
                  <tr class="hiddenElement" id="crm-uploaded-extensions-details-{$row.id}">
                      <td>
                          {include file="CRM/Admin/Page/ExtensionDetails.tpl" extension=$row}
                      </td>
                      <td></td><td></td><td></td><td></td>
                  </tr>                  
                  {assign var='rowCount' value = $rowCount+1} 
                  {/foreach}
                </tbody>
              </table>
              {/strip}
            </div>
          {else}
              <div class="messages status">
                   <div class="icon inform-icon"></div>
                  {ts}There are no uploaded extensions to be installed.{/ts}
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