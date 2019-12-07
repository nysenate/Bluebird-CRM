<div id="WebsiteAccount" class="crm-block crm-content-block nyss-integration-tab">
  <h3>Website Account Activity History</h3>

  <table class="report-layout display">
    <tr>
      <th class="reports-header">Account Action</th>
      <th class="reports-header">Date</th>
    </tr>
  {foreach from=$account item=row}
    <tr class="crm-report {cycle values='odd-row,even-row'}">
      <td>{$row.action}</td>
      <td>{$row.created}</td>
    </tr>
  {/foreach}
  </table>
</div>
