CRM.$(function($) {
  //7403 hide visibility filter
  $('.crm-group-search-form-block table select#visibility').parent('td').hide();

  //6684 remove access control filter
  $('td#group_type-block input#group_type_search_1').remove();
  $('td#group_type-block label[for=group_type_search_1]').remove();
  if ($('td#group_type-block').length) {
    var emailList = cj('td#group_type-block').html();
    $('td#group_type-block').html(emailList.replace(/&nbsp;/gi,''));
  }

  //5855 help text tweaks
  $('div.help a.helpicon').remove();
  if ($('.nyss-groups-help').length === 0) {
    $('div.help').append('<span class="nyss-groups-help">Groups which will be used for mass email should be marked "Email List" in the group settings. Use the text box and type/status options to filter the list of existing groups.</span>');
  }
});
