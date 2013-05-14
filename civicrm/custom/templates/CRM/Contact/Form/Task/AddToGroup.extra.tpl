{literal}
<script type="text/javascript">
  var acg = cj('tr.crm-contact-task-addtogroup-form-block-group_type td[class!="label"]').html().replace(/&nbsp;/g,'');
  cj('tr.crm-contact-task-addtogroup-form-block-group_type td[class!="label"]').html(acg);
  cj('#group_type_1').remove();
  cj('label[for=group_type_1]').remove();
</script>
{/literal}
