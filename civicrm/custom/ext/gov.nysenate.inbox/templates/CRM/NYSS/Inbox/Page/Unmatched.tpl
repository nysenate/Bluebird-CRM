<div class="crm-unmatchedmessages-selector">
  <h2 class="crm-title">{$title}</h2>

  <div class="inbox-unmatchedmessages-actions crm-section crm-form-block">
    <div class="crm-contact-form-block-range_filter crm-left">
      {$form.range_filter.label} {$form.range_filter.html|crmAddClass:big}
    </div>
    <div class="crm-contact-form-block-delete crm-right">
      <input type="button" class="multi_delete" value="Delete Selected" name="delete">
    </div>
    <div class="clear"></div>
  </div>

  <table class="inbox-unmatchedmessages-selector crm-ajax-table">
    <thead>
    <tr>
      <th data-data="id" data-orderable="false" class="crm-unmatchedmessages">{$toggleAll}</th>
      <th data-data="sender_info" class="crm-unmatchedmessages">Sender's Info</th>
      <th data-data="subject" class="crm-unmatchedmessages">Subject</th>
      <th data-data="date_forwarded" class="crm-unmatchedmessages">Date Forwarded</th>
      <th data-data="forwarded_by" class="crm-unmatchedmessages">Forwarded By</th>
      <th data-data="links" data-orderable="false" class="crm-unmatchedmessages">Actions</th>
    </tr>
    </thead>
  </table>

  {literal}
    <script type="text/javascript">
      (function($) {
        CRM.$('table.inbox-unmatchedmessages-selector').data({
          "ajax": {
            "url": CRM.url('civicrm/nyss/inbox/ajax/unmatched', {snippet: 4, range: 30})
          }
        });

        //redraw table when range filter changes
        CRM.$('#range_filter').change(function(){
          var range = $('#range_filter').val();
          CRM.$('table.inbox-unmatchedmessages-selector').DataTable().ajax.
            url(CRM.url('civicrm/nyss/inbox/ajax/unmatched', {snippet: 4, range: range})).load();
        });

        /*$('table.contact-unmatchedmessages-selector').
        on('click', '.view-msg', function(){
          var msgId = $(this).prop('id').replace('view-msg-', '');

          $('#msg-' + msgId).dialog({
            width: 500
          }).dialog('open');

          return false;
        }).
        on('click', '.create-activity', function(){
          var msgId = $(this).prop('id').replace('create-activity-', '');
          var contactId = $(this).attr('contact_id');
          var msgNote = $('div#msg-' + msgId).html();

          //determine the activity ID
          var msgType = $('table.contact-unmatchedmessages-selector tr#' + msgId + ' span.msg-type').text();
          var msgTypeId = 0;
          switch (msgType) {
            case 'Direct':
              msgTypeId = CRM.vars.NYSS.unmatched_activity_direct;
              break;
            case 'Contextual':
              msgTypeId = CRM.vars.NYSS.unmatched_activity_contextual;
              break;
            default:
          }

          var url = '/civicrm/activity/add?reset=1&action=add&atype=' + msgTypeId + '&cid=' + contactId + '&msgId=' + msgId + '&msgNote=' + msgNote;

          CRM.loadForm(url).
          on('crmFormSuccess', function(event, data){
            //redraw so list is reloaded
            CRM.$('table.contact-unmatchedmessages-selector').DataTable().draw();
          });

          return false;
        });*/
      })(CRM.$);
    </script>
  {/literal}
</div>
