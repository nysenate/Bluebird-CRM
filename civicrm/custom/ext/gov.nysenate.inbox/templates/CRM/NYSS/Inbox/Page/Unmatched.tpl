<div class="crm-unmatchedmessages-selector">
  <div class="inbox-unmatchedmessages-actions crm-section crm-form-block">
    <div class="crm-contact-form-block-range_filter crm-left crm-margin-right">
      {$form.range_filter.html|crmAddClass:big}
    </div>
    <div class="crm-contact-form-block-search crm-left crm-margin-right">
      {$form.search_filter.html|crmAddClass:twelve}
    </div>
    <div class="crm-contact-form-block-multiactions crm-right">
      <input type="button" class="multi_delete" value="Delete Selected" name="delete">
    </div>
    <div class="clear"></div>
  </div>

  <table class="inbox-messages-selector crm-ajax-table">
    <thead>
    <tr>
      <th data-data="id" data-orderable="false" class="crm-unmatchedmessages">{$toggleAll}</th>
      <th data-data="sender_name" class="crm-unmatchedmessages">Sender's Info</th>
      <th data-data="subject" class="crm-unmatchedmessages">Subject</th>
      <th data-data="updated_date" class="crm-unmatchedmessages">Date Forwarded</th>
      <th data-data="forwarder" class="crm-unmatchedmessages">Forwarded By</th>
      <th data-data="links" data-orderable="false" class="crm-unmatchedmessages crm-actions">Actions</th>
    </tr>
    </thead>
  </table>

  {literal}
    <script type="text/javascript">
      (function($) {
        CRM.$('table.inbox-messages-selector').data({
          "ajax": {
            "url": CRM.url('civicrm/nyss/inbox/ajax/unmatched', {snippet: 4, range: 30})
          }
        });
      })(CRM.$);
    </script>
  {/literal}
</div>
