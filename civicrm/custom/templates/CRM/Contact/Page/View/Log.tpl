{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
<div id="changeLog" class="view-content nyss-integration-tab">{*NYSS*}
    <h3>{ts}Change Log:{/ts} {$displayName}</h3>{*NYSS*}
   {if $useLogging}
     <div class='instance_data'><div class="crm-loading-element"></div></div>
   {else}
    <div class="form-item">
     {if $logCount > 0}
       <table>
       <tr class="columnheader"><th>{ts}Changed By{/ts}</th><th>{ts}Change Date{/ts}</th><th>Description</th></tr>{*NYSS*}
       {foreach from=$log item=row}
         <tr class="{cycle values="odd-row,even-row"}">
            <td> {$row.image}&nbsp;<a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$row.id`"}">{$row.name}</a></td>
            <td>{$row.date|crmDate}</td>
            <td>{$row.description}</td>{*NYSS*}
         </tr>
       {/foreach}
       </table>
     {else}
     <div class="messages status no-popup">
      {icon icon="fa-info-circle"}{/icon}
      {ts}None found.{/ts}
     </div>
     {/if}
    </div>
   {/if}
 </p>
</div>

{if $useLogging}
{literal}
  <script type="text/javascript">
  CRM.$(function($) {
    $('#changeLog .instance_data').on('crmLoad', function(e, data) {
      CRM.tabHeader.updateCount('#tab_log', data.totalRows);
    });
    CRM.reloadChangeLogTab = function(url) {
      if (url) {
        $('#changeLog .instance_data').crmSnippet({url: url});
      }
      $('#changeLog .instance_data').crmSnippet('refresh');
    };
    CRM.incrementChangeLogTab = function() {
      CRM.tabHeader.updateCount('#tab_log', 1 + CRM.tabHeader.getCount('#tab_log'));
    };
    CRM.reloadChangeLogTab({/literal}"{$instanceUrl}"{literal});

    //NYSS 6960
    var dataURL = {/literal}"{$instanceUrl}"{literal};
    cj.ajax({
      url: dataURL,
      beforeSend: function(){
        var procHtml = '<div id="contact-log-report_processing" class="dataTables_processing">Processing...</div>';
        cj('#instance_data').before(procHtml);
      },
      success: function( content ) {
        cj('#contact-log-report_processing').hide();
        cj('#instance_data').show( ).html( content );
      }
    });
  });

  </script>
{/literal}
{/if}
