<div class="crm-unmatchedmessages-selector">
  <h2 class="crm-title">{$title}</h2>

  <div class="inbox-unmatchedmessages-actions crm-section crm-form-block">
    <div class="crm-contact-form-block-range_filter crm-left crm-margin-right">
      {$form.range_filter.html|crmAddClass:big}
    </div>
    <div class="crm-contact-form-block-search crm-left crm-margin-right">
      {$form.search_filter.html|crmAddClass:twelve}
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
          var search = $('#search_filter').val();
          CRM.$('table.inbox-unmatchedmessages-selector').DataTable().ajax.
            url(CRM.url('civicrm/nyss/inbox/ajax/unmatched', {snippet: 4, range: range, term: search})).load();
        });

        CRM.$('#search_filter').keypress(function(){
          var range = $('#range_filter').val();
          var term = $('#search_filter').val();
          CRM.$('table.inbox-unmatchedmessages-selector').DataTable().ajax.
          url(CRM.url('civicrm/nyss/inbox/ajax/unmatched', {snippet: 4, range: range, term: JSON.stringify(term)})).load();
        });
      })(CRM.$);
    </script>
  {/literal}
</div>
