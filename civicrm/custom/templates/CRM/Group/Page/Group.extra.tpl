{literal}
<script type="text/javascript">
  cj('.crm-group-search-form-block table td:nth-child(4)').css('width','60%');
  cj('.crm-group-search-form-block table td:nth-child(3)').hide();

  //6684
  cj('.crm-group-search-form-block table td:nth-child(2) input#group_type_1').remove();
  cj('.crm-group-search-form-block table td:nth-child(2) label[for=group_type_1]').remove();
  var emailList = cj('.crm-group-search-form-block table td:nth-child(2)').html().replace(/&nbsp;&nbsp;&nbsp;/g,'');
  cj('.crm-group-search-form-block table tr:nth-child(1) td:nth-child(2)').html(emailList);

  //5855 help text tweaks
  cj('div#help div.helpicon').remove();
  cj('div#help').append('Groups which will be used for mass email should be marked "Email List" in the group settings. Use the text box and type/status options to filter the list of existing groups.');
  cj('div#help').html(cj('div#help').html().replace('&nbsp;&nbsp;&nbsp;', ''));

  //7373
  cj('div.crm-block div#help a.helpicon').remove();
</script>
{/literal}
