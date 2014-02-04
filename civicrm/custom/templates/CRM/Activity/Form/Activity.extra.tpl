{literal}
<script type="text/javascript">
  //7466
  cj('#swap_target_assignee').remove();

  //7305
  if ( !cj('label[for=activity_type_id] span.crm-marker').length ) {
    cj('label[for=activity_type_id]').append(' <span class="crm-marker" title="This field is required.">*</span>');
  }
</script>
{/literal}
