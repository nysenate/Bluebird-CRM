<div id="WebsiteActivityStream" class="crm-block crm-content-block">
  <h3>Website Activity Stream</h3>

  <table class="report-layout display">
    <tr>
      <th class="reports-header">Type</th>
      <th class="reports-header">Date</th>
      <th class="reports-header">Details</th>
    </tr>
  {foreach from=$activity item=row}
    <tr class="crm-report {cycle values='odd-row,even-row'}">
      <td>{$row.type}</td>
      <td>{$row.date}</td>
      <td>{$row.details}</td>
    </tr>
  {/foreach}
  </table>
</div>
