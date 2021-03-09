<script type="text/javascript">
    {literal}
    CRM.$(function($) {
      CRM.$('#case_category_instance_type').insertAfter(CRM.$('.crm-admin-options-form-block-value'));
    });
    {/literal}
</script>

<table>
  <tr id="case_category_instance_type">
    <td class="label"> {$form.case_category_instance_type.label} </td>
    <td> {$form.case_category_instance_type.html} </td>
  </tr>
</table>
