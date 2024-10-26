<div class="crm-form-block crm-delete-trashed-wrapper">

  <div id="help">
    <p>This tool will cycle through all trashed contacts and permanently delete them from the system. <strong>Please use with caution.</strong></p>
    <p>There are {$trashCount} trashed contacts which will be deleted.</p>
  </div>

  <div id="delete-action" class="crm-section">
    <div class="label">Permanently Delete?</div>
    <div class="content">
      <a href="#"
         title="{ts}Permanently Delete Contacts{/ts}"
         id="processTrashed"
         class="button"><span>{ts}Continue{/ts}</span></a>
    </div>
    <div class="clear"></div>
  </div>

  <div id="output" style="display: none;">
    <h3>Deleting Contacts...</h3>
    <div class="content">Please be patient, this may take some time. You will receive notification when the process is complete.<br /><br />Working...<br /><br /></div>
    <div class="final"></div>
  </div>

</div>
