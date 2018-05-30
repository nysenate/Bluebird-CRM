CRM.$(function($) {
  var message = $('div.message-details-wrapper').html();
  if (message.length) {
    $(document).ready(function() {
      $('input#street_address-Primary').removeClass('huge').addClass('big');
      $('div.crm-profile-name-new_individual').after('<div class="crm-accordion-wrapper crm-accordion message-details">' + message + '</div>');

      $('form.CRM_Profile_Form_Edit div.click-message').text('Click the colored areas below to populate the corresponding fields.');
      $('form.CRM_Profile_Form_Edit div.click-message').show();

      //insert values from body on click
      $('span.phone').click(function(){
        $('input#phone-1-1').val($(this).text());
      });
      $('span.email_address').click(function(){
        $('input#email-Primary').val($(this).text());
      });
      $('span.name').click(function(){
        var $e = $(this),
          j = $e.data('json'),
          check = ['first', 'middle', 'last'];
        for (v in j) {
          var select = '.CRM_Profile_Form_Edit #' + v + '_name';
          if ($(select).length) {
            $(select).val(j[v]);
          }
        }
      });

      //address is a single element with parsed values in data
      $('span.zip').click(function(){
        var json = $(this).data('json');
        $('input#city-Primary').val(json.city);
        $('input#postal_code-Primary').val(json.zip);

        //state select: determine id from label
        $('select#state_province-Primary option').each(function(){
          if ($(this).text() == json.state) {
            var stateId = $(this).val();
            $('select#state_province-Primary').val(stateId).trigger('change');
            return false;
          }
        });
      });
    });
  }
});
