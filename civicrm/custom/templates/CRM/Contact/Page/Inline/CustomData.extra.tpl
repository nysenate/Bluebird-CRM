{literal}
<script type="text/javascript">
  //5638 custom data
  var custLink1 = cj('#custom-set-block-1 .crm-config-option a').html().replace('add or edit custom set', 'add or edit constituent information');
  cj('#custom-set-block-1 .crm-config-option a').html(custLink1);

  var custLink2 = cj('#custom-set-block-5 .crm-config-option a').html().replace('add or edit custom set', 'add or edit attachments');
  cj('#custom-set-block-5 .crm-config-option a').html(custLink2);

  //5637 reduce block width after returning from inline form
  cj('#custom-set-block-1 #crm-container-snippet').css('width','auto');
</script>
{/literal}
