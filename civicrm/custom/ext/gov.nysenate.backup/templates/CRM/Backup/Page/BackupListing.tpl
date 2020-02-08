<div class="crm-block crm-content-block">
  <div class="action-link">
    <a class="button crm-popup new-option" href="{$btn_create}"><span><i class="crm-i fa-plus-circle"></i> Create New Backup</span></a>
  </div>

  <table class="row-highlight backup-listing">
    <tr>
      <th>Backup Filename</th>
      <th>Date Generated</th>
      <th></th>
    </tr>

    {foreach from=$listing item=row}
      <tr>
        <td>{$row.file}</td>
        <td>{$row.time_formatted}</td>
        <td>
          <a href="{$row.btn_restore_url}" class="button crm-popup"><i class="crm-i fa-refresh"></i> Restore</a>
          <a href="{$row.btn_delete_url}" class="button crm-popup"><i class="crm-i fa-remove"></i> Delete</a>
        </td>
      </tr>
    {/foreach}
  </table>
</div>
