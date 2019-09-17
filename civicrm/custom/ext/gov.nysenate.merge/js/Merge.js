CRM.$(function ($) {
  $('.crm-contact-merge-form-block tr').each(function() {
    var label = $(this).children('td:first-child').text().trim();
    //console.log('label: ', label);

    if (label === 'Privacy Options Note') {
      $(this).find('span.action_label').text('(migrate)');
    }
  });
});
