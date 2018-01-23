CRM.$(function($) {
  var message = $('div.message-details-wrapper').html();
  if (message.length) {
    $(document).ready(function() {
      $('input#street_address-Primary').removeClass('huge').addClass('big');
      $('div.crm-profile-name-new_individual').after('<div class="crm-accordion-wrapper crm-accordion message-details">' + message + '</div>');

      $('form.CRM_Profile_Form_Edit div.message-body-section').before('<div class="crm-section click-message">Click the colored areas below to populate the corresponding fields.</div>');
      $('form.CRM_Profile_Form_Edit div.click-message').show();

      //insert values from body on click
      $('span.phone').click(function(){
        $('input#phone-1-1').val($(this).text());
      });
      $('span.email_address').click(function(){
        $('input#email-Primary').val($(this).text());
      });
      $('span.name').click(function(){
        $('input#last_name').val($(this).text());
      });
      $('span.zip').click(function(){
        $('input#postal_code-Primary').val($(this).text());
      });
    });
  }
});
