<div id="WebsiteActivityStream" class="crm-block crm-content-block">
  <h3>Website Activity Stream</h3>

  <table class="report-layout display">
    <tr>
      <th class="reports-header">Message Type</th>
      <th class="reports-header">Date</th>
      <th class="reports-header">Message</th>
    </tr>
  {foreach from=$activity item=row}
    <tr class="crm-report {cycle values='odd-row,even-row'}">
      <td>{$row.subject}</td>
      <td>{$row.modified_date}</td>
      <td>{$row.note}</td>
    </tr>
  {/foreach}
  </table>
</div>
