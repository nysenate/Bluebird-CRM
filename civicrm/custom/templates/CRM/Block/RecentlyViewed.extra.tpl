{literal}
<script type="text/javascript">
  cj('.crm-actions-delete').remove();
 
  function getChangeLogCount( ) {
    var postUrl = {/literal}"{crmURL p='civicrm/ajax/changelog' h=0 }"{literal};
    {/literal}
    {if $contactId}
    {literal}
      var contactID = {/literal}{$contactId};{literal}
      cj.ajax({
        type: "POST",
        data:  "contactId=" + contactID + "&key={/literal}{crmKey name='civicrm/ajax/changelog'}{literal}",
        url: postUrl,
        success: function(returnHtml){
          var resourceBase   = {/literal}"{$config->resourceBase}"{literal};
          var successMsg = '';
          var element = cj('#log_count');
          if(!isNaN(returnHtml)) {
            element.html(returnHtml);
          } else {
            element.html('');
          }
        }
      });
    {/literal}
    {/if}
    {literal}
  }
</script>
{/literal}
