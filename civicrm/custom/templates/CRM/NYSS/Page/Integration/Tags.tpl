<div id="WebsiteTags" class="crm-block crm-content-block nyss-integration-tab">
  {foreach from=$tags key=tagset item=tag}
    <h3>{$tagset}</h3>

    {if !empty($tag)}
      <table class="report-layout display">
        <tr>
          <th class="reports-header">Tag</th>
          <th class="reports-header">Description/Link</th>
        </tr>

        {foreach from=$tag item=row}
          <tr class="crm-report {cycle values='odd-row,even-row'}">
            <td>{$row.name}</td>
            <td>{$row.description}</td>
          </tr>
        {/foreach}
      </table>
    {/if}

  {/foreach}
</div>
