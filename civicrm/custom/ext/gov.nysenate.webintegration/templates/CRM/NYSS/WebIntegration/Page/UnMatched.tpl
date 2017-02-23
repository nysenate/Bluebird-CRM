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
        /*$('.unmatchedmessages-search-options :input').change(function(){
          CRM.$('table.contact-unmatchedmessages-selector').DataTable().draw();
        });*/
      });

      $('table.contact-unmatchedmessages-selector').
        on('click', '.view-msg', function(){
          var id = $(this).prop('id');
          id = id.replace('view-msg-', '');
          //console.log('id view: ', id);

          $('#msg-' + id).dialog({
            width: 500
          }).dialog('open');
          return false;
        }).
        on('click', '.create-activity', function(){
          var id = $(this).prop('id');
          id = id.replace('create-activity-', '');
          //console.log('id create: ', id);

          //TODO...
          return false;
        });
    })(CRM.$);
  </script>
  {/literal}
</div>
