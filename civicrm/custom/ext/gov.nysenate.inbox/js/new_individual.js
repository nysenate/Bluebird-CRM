CRM.$(function($) {
  var message = $('div.message-body').html();
  if (message.length) {
    $('div.crm-profile-name-new_individual div.crm-submit-buttons').before('<div class="crm-accordion-wrapper crm-accordion collapsed new-indiv-message-details">\n' +
      '<div class="crm-accordion-header">Message Details</div>\n' +
      '<div class="crm-accordion-body">\n' +
      message +
      '</div></div>');
  }
});
