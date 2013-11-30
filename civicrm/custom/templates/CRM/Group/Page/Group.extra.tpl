{literal}
<script type="text/javascript">
  //7403 hide visibility filter
  cj('.crm-group-search-form-block table select#visibility').parent('td').hide();

  //6684 remove access control filter
  cj('.crm-group-search-form-block table td#group_type-block input#group_type_1').remove();
  cj('.crm-group-search-form-block table td#group_type-block label[for=group_type_1]').remove();
  if (cj('.crm-group-search-form-block table td#group_type-block').length) {
    var emailList = cj('.crm-group-search-form-block table td#group_type-block').html().replace(/&nbsp;&nbsp;&nbsp;/g,'');
    cj('.crm-group-search-form-block table tr:nth-child(1) td#group_type-block').html(emailList);
  }

  //expand status filter to fill space better
  cj('.crm-group-search-form-block table input#group_status_1').parent('td').css('width', '30em');

  //5855 help text tweaks
  cj('div#help div.helpicon').remove();
  cj('div#help').append('Groups which will be used for mass email should be marked "Email List" in the group settings. Use the text box and type/status options to filter the list of existing groups.');
  cj('div#help').html(cj('div#help').html().replace('&nbsp;&nbsp;&nbsp;', ''));

  //7373
  cj('div.crm-block div#help a.helpicon').remove();
</script>
{/literal}
