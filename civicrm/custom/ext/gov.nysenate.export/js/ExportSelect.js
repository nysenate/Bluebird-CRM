CRM.$(function($) {
  $('div.crm-export-form-block div.help').replaceWith($('div.nyss-export-help'));
  $('div.nyss-export-opts').insertAfter('div.crm-content-additionalGroup');

  $('div#wizard-steps').remove();
  $('div.crm-export-form-block h2').text('Export All or Selected Fields').css('padding-top', '12px');
  $('div.crm-content-mergeOptions').html($('div.crm-content-mergeOptions').html().replace('&nbsp;', ''));

  //rename buttons
  var element = document.getElementsByName("exportOption");
  if (element[1].checked) {
    cj('#map').show();
    cj('#_qf_Select_next-top').val('Continue >> ');
    cj('#_qf_Select_next-bottom').val('Continue >> ');
  } else {
    cj('#map').hide();
    cj('#_qf_Select_next-top').val('Export ');
    cj('#_qf_Select_next-bottom').val('Export ');
  }
});
