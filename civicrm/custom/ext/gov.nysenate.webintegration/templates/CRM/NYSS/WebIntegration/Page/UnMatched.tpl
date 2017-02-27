<div class="crm-unmatchedmessages-selector">
  <table class="contact-unmatchedmessages-selector crm-ajax-table">
    <thead>
    <tr>
      <th data-data="contact" class="crm-unmatchedmessages">{ts}Contact{/ts}</th>
      <th data-data="county" class="crm-unmatchedmessages">{ts}County{/ts}</th>
      <th data-data="type" class="crm-unmatchedmessages">{ts}Type{/ts}</th>
      <th data-data="date" class="crm-unmatchedmessages">{ts}Date{/ts}</th>
      <th data-data="links" data-orderable="false" class="crm-unmatchedmessages">Actions</th>
    </tr>
    </thead>
  </table>

  {literal}
  <script type="text/javascript">
    (function($) {
      CRM.$('table.contact-unmatchedmessages-selector').data({
        "ajax": {
          "url": CRM.url('civicrm/nyss/ajax/unmatchedmessages', {snippet: 4})
        }
      });
      $(function($) {
        //retain in case we are asked to implement filters
        /*$('.unmatchedmessages-search-options :input').change(function(){
          CRM.$('table.contact-unmatchedmessages-selector').DataTable().draw();
        });*/
      });

      $('table.contact-unmatchedmessages-selector').
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
        });
    })(CRM.$);
  </script>
  {/literal}
</div>
