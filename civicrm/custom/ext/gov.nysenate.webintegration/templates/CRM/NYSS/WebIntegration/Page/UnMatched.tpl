<div class="crm-unmatchedmessages-selector">
  <table class="contact-unmatchedmessages-selector-{$context} crm-ajax-table">
    <thead>
    <tr>
      <th data-data="unmatchedmessages_type" class="crm-contact-unmatchedmessages-unmatchedmessages_type">{ts}Type{/ts}</th>
      <th data-data="subject" cell-class="crmf-subject crm-editable" class="crm-contact-unmatchedmessages_subject">{ts}Subject{/ts}</th>
      <th data-data="source_contact_name" class="crm-contact-unmatchedmessages-source_contact">{ts}Added By{/ts}</th>
      <th data-data="target_contact_name" data-orderable="false" class="crm-contact-unmatchedmessages-target_contact">{ts}With{/ts}</th>
      <th data-data="assignee_contact_name" data-orderable="false" class="crm-contact-unmatchedmessages-assignee_contact">{ts}Assigned{/ts}</th>
      <th data-data="unmatchedmessages_date_time" class="crm-contact-unmatchedmessages-unmatchedmessages_date">{ts}Date{/ts}</th>
      <th data-data="status_id" cell-class="crmf-status_id crm-editable" cell-data-type="select" cell-data-refresh="true" class="crm-contact-unmatchedmessages-unmatchedmessages_status">{ts}Status{/ts}</th>
      <th data-data="links" data-orderable="false" class="crm-contact-unmatchedmessages-links">&nbsp;</th>
    </tr>
    </thead>
  </table>

  {literal}
  <script type="text/javascript">
    (function($) {
      var context = {/literal}"{$context}"{literal};
      CRM.$('table.contact-unmatchedmessages-selector-' + context).data({
        "ajax": {
          "url": {/literal}'{crmURL p="civicrm/ajax/contactunmatchedmessages" h=0 q="snippet=4&context=$context&cid=$contactId"}'{literal},
          "data": function (d) {
            d.unmatchedmessages_type_id = $('.crm-unmatchedmessages-selector-' + context + ' select#unmatchedmessages_type_filter_id').val(),
              d.unmatchedmessages_type_exclude_id = $('.crm-unmatchedmessages-selector-' + context + ' select#unmatchedmessages_type_exclude_filter_id').val()
          }
        }
      });
      $(function($) {
        $('.unmatchedmessages-search-options :input').change(function(){
          CRM.$('table.contact-unmatchedmessages-selector-' + context).DataTable().draw();
        });
      });
    })(CRM.$);
  </script>
  {/literal}
</div>
{include file="CRM/Case/Form/ActivityToCase.tpl" contactID=$contactId}
