<div id="report-tab-set-aggregates" class="civireport-criteria">
  <table class="report-layout">
    <tr class="crm-report crm-report-criteria-aggregate">
      <td>
        <div id='crm-custom_fields'>
          <label>{ts}Select Row Fields{/ts}</label>
          {$form.aggregate_row_headers.html}
        </div>
      </td>
      <td>
        <div id="row_date_header_fields">
          <label>Group Date by</label>
          {$form.aggregate_row_date_grouping.html}
        </div>
      </td>
      <td>
        <label>{ts}Select Column Header{/ts}</label>
        {$form.aggregate_column_headers.html}
      </td>
      <td>
      <div id="column_date_header_fields">
        <label>Group Date by</label>
        {$form.aggregate_column_date_grouping.html}
      </div>
      </td>
    </tr>
  </table>

  <script type="text/javascript">
    {literal}
    CRM.$(function($) {
      toogleDateColumnGroupingField();
      toogleDateRowGroupingField();
      cj('#aggregate_column_headers').on('change', function() {
        toogleDateColumnGroupingField();
      });

      cj('#aggregate_row_headers').on('change', function() {
        toogleDateRowGroupingField();
      });
    });

    /**
     * Toggles the visibility of the column date grouping field based on the
     * value of the aggregate column header field
     */
    function toogleDateColumnGroupingField() {
      toogleDateGroupingField('column_date_header_fields', 'aggregate_column_headers');
    }

    /**
     * Toggles the visibility of the row date grouping field based on the
     * value of the aggregate row header field
     */
    function toogleDateRowGroupingField() {
      toogleDateGroupingField('row_date_header_fields', 'aggregate_row_headers');
    }

    /**
     * Toggles the visibility of the date grouping field based on the
     * value of the row/column header field
     */
    function toogleDateGroupingField(date_selector, field_selector) {
      var dateFields = {/literal} {$aggregateDateFields}{literal}
      var field_header_value = cj('#' + field_selector).val();

      if (field_header_value in dateFields) {
        cj('#' + date_selector).show();
      }
      else {
        cj('#' + date_selector).hide();
      }
    }
    {/literal}
  </script>
</div>
