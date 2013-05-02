{literal}
<script type="text/javascript">
  cj('.crm-group-search-form-block table td:nth-child(4)').css('width','60%');
  cj('.crm-group-search-form-block table td:nth-child(3)').hide();

  //6684
  cj('.crm-group-search-form-block table td:nth-child(2) input#group_type_1').remove();
  cj('.crm-group-search-form-block table td:nth-child(2) label[for=group_type_1]').remove();
  var emailList = cj('.crm-group-search-form-block table td:nth-child(2)').html().replace(/&nbsp;&nbsp;&nbsp;/g,'');
  cj('.crm-group-search-form-block table tr:nth-child(1) td:nth-child(2)').html(emailList);
</script>
{/literal}
