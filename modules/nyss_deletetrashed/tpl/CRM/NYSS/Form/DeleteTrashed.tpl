{literal}
<style type="text/css">
  #output {
    border: 1px solid orange;
    border-radius: 4px 4px 4px 4px;
    background-color: #fffacd;
    padding: 10px;
    max-height: 400px;
    overflow: auto;
  }
  #output h3 {
    padding-left: 0;
  }
</style>
{/literal}

<div class="crm-block">
<div class="crm-form-block">

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
</div>  

{literal}
<script type="text/javascript">
  cj('#processTrashed').click(function(){
    var result = confirm('Are you sure you want to permanently delete all trashed contacts?');
    if ( result != true ) {
      return;
    }

    cj('#output').show();
    cj('#delete-action').hide();

    //trigger data load
    var dataUrl = "{/literal}{crmURL p='civicrm/nyss/processtrashed' h=0 }{literal}";

    cj.ajax({
      url: dataUrl,
      success: function(data, textStatus, jqXHR){
        cj('div.final').html(data);
      },
      error: function( jqXHR, textStatus, errorThrown ) {
        return false;
      }
    });
  });
</script>
{/literal}
