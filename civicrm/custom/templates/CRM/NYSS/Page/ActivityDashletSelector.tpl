{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
<div class="crm-allactivity-selector-{$context}">
  <details class="crm-accordion-bold crm-search_filters-accordion" open>
    <summary>
    {ts}Filter by Activity{/ts}
    </summary>
    <div class="crm-accordion-body">
      <form><!-- form element is here to fool the datepicker widget -->
      <table class="no-border form-layout-compressed all-activity-search-options">
        <tr>
          <td class="crm-contact-form-block-activity_type_filter_id crm-inline-edit-field">
            {$form.all_activity_type_filter_id.label}<br /> {$form.all_activity_type_filter_id.html|crmAddClass:medium}
          </td>
          <td class="crm-contact-form-block-activity_type_exclude_filter_id crm-inline-edit-field">
            {$form.all_activity_type_exclude_filter_id.label}<br /> {$form.all_activity_type_exclude_filter_id.html|crmAddClass:medium}
          </td>

          {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="all_activity_date_time" hideRelativeLabel=false}
          <td class="crm-contact-form-block-activity_status_filter_id crm-inline-edit-field">
            <label>{ts}Status{/ts}</label><br /> {$form.status_id.html|crmAddClass:medium}
          </td>
        </tr>
      </table>
      </form>
    </div>
  </details>
  <table class="contact-allactivity-selector-{$context} crm-ajax-table" style="width: 100%;">
    <thead>
    <tr>
      <th data-data="activity_type" class="crm-contact-activity-activity_type">{ts}Type{/ts}</th>
      <th data-data="subject" class="crmf-subject crm-editable" class="crm-contact-activity_subject">{ts}Subject{/ts}</th>
      <th data-data="source_contact_name" class="crm-contact-activity-source_contact">{ts}Added by{/ts}</th>
      <th data-data="target_contact_name" data-orderable="false" class="crm-contact-activity-target_contact">{ts}With{/ts}</th>
      <th data-data="assignee_contact_name" data-orderable="false" class="crm-contact-activity-assignee_contact">{ts}Assigned{/ts}</th>
      <th data-data="activity_date_time" class="crm-contact-activity-activity_date_time">{ts}Date{/ts}</th>
      <th data-data="status_id" cell-data-type="select" cell-data-refresh="true" class="crm-contact-activity-activity_status">{ts}Status{/ts}</th>
      <th data-data="links" data-orderable="false" class="crm-contact-activity-links">&nbsp;</th>
    </tr>
    </thead>
  </table>

  {literal}
    <script type="text/javascript">
      (function($, _) {
        var context = {/literal}"{$context}"{literal};
        CRM.$('table.contact-allactivity-selector-' + context).data({
          "ajax": {
            "url": {/literal}'{crmURL p="civicrm/ajax/allactivities" h=0 q="snippet=4&context=$context&cid=$contactId"}'{literal},
            "data": function (d) {
              var status_id = $('.crm-allactivity-selector-' + context + ' select#status_id').val() || [];
              d.activity_type_id = $('.crm-allactivity-selector-' + context + ' select#activity_type_filter_id').val(),
              d.activity_type_exclude_id = $('.crm-allactivity-selector-' + context + ' select#activity_type_exclude_filter_id').val(),
              d.activity_date_time_relative = $('select#all_activity_date_time_relative').val(),
              d.activity_date_time_low = $('#all_activity_date_time_low').val(),
              d.activity_date_time_high = $('#all_activity_date_time_high').val(),
              d.activity_status_id = status_id.join(',')
            }
          }
        });
        $(function($) {
          $('table.contact-allactivity-selector-' + context).on('xhr.dt', function(e, settings, json, xhr) {
            for (var i=0, ien=json.data.length; i<ien; i++) {
              json.data[i].subject = _.escape(json.data[i].subject);
            }
          });
          $('.all-activity-search-options :input').change(function(){
            $('table.contact-allactivity-selector-' + context).DataTable().draw();
          });
        });
      })(CRM.$, CRM._);
    </script>
  {/literal}
  <style type="text/css">
    {crmAPI var='statuses' entity='OptionValue' action='get' return="color,value" option_limit=0 option_group_id="activity_status"}
    {foreach from=$statuses.values item=status}
      {if !empty($status.color)}
        table.contact-activity-selector-{$context} tr.status-id-{$status.value} {ldelim}
          border-left: 3px solid {$status.color};
        {rdelim}
      {/if}
    {/foreach}
  </style>
</div>
{include file="CRM/Case/Form/ActivityToCase.tpl" contactID=$contactId}
