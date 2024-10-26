<div id="report-tab-set-results" class="civireport-criteria">
  <table class="report-layout">
    <tr class="crm-report crm-report-criteria-results">
      <td>
        <div>
          <label>{ts}Aggregate Function{/ts}</label>
          {$form.data_function.html}
        </div>
      </td>
      <td>
        <div id="data_function_field">
          <label>{ts}Aggregate On{/ts}</label>
          {$form.data_function_field.html}
        </div>
      </td>
    </tr>
  </table>
  <script type="text/javascript">
    {literal}
    CRM.$(function($) {
      toogleAggregateOnField();
      cj('#data_functions').on('change', function() {
        toogleAggregateOnField();
      });
    });

    /**
     * Toggles the visibility of the aggregate on field based on the
     * value of the data function field
     */
    function toogleAggregateOnField() {
      var aggregate_function_value = cj('#data_functions').val();

      if (aggregate_function_value !== 'COUNT') {
        cj('#data_function_field').show();
      }
      else {
        cj('#data_function_field').hide();
      }
    }

    {/literal}
  </script>
</div>
