<div id="WebsiteAccount" class="crm-block crm-content-block">
  <h3>Website Account Activity History</h3>

  <table class="report-layout display">
    <tr>
      <th class="reports-header">Account Action</th>
      <th class="reports-header">Date</th>
    </tr>
  {foreach from=$account item=row}
    <tr>
      <td class="crm-report {cycle values='odd-row,even-row'}">{$row.action}</td>
      <td class="crm-report">{$row.created}</td>
    </tr>
  {/foreach}
  </table>
</div>
