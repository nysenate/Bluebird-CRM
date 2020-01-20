<div class="help">
  {$message}
</div>

{if $recipContacts}
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$recipContacts item=row}
        <tr>
          <td>{$row.contact}</td>
          <td>{$row.email}</td>
        </tr>
      {/foreach}
    </tbody>
  </table>
{/if}
