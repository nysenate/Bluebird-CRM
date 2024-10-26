<div class="help">
  <p class="ng-binding">Based on current data, approximately {$recipientCount} contacts will receive a copy of the mailing.</p>
  <p>Below is a sample of the first {$partialCount} recipients.</p>
  <p>If individual contacts are separately modified, added, or removed, then the final list may change.</p>
</div>

<table>
  <thead>
  <tr>
    <th>Name</th>
    <th>Email</th>
  </tr>
  </thead>
  <tbody>
    {foreach from=$rows item=row}
      <tr>
        <td>{$row.name}</td>
        <td>{$row.email}</td>
      </tr>
    {/foreach}
  </tbody>
</table>
