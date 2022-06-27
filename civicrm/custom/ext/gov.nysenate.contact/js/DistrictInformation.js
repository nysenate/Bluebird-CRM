CRM.$(function($) {
  $('div[id^=District_Information] table.form-layout-compressed').after('<a href="#/" class="nyss-expand-districtinfo">&raquo; View Additional District Information Fields</a>');

  $('tr[class*=custom_51]').hide();
  $('tr[class*=custom_52]').hide();
  $('tr[class*=custom_53]').hide();
  $('tr[class*=custom_54]').hide();
  $('tr[class*=custom_55]').hide();
  $('tr[class*=custom_56]').hide();
  $('tr[class*=custom_57]').hide();

  $('.nyss-expand-districtinfo').click(function(e) {
    e.preventDefault();

    $(this).prev('.form-layout-compressed').find('tr[class*=custom_51]').show();
    $(this).prev('.form-layout-compressed').find('tr[class*=custom_52]').show();
    $(this).prev('.form-layout-compressed').find('tr[class*=custom_53]').show();
    $(this).prev('.form-layout-compressed').find('tr[class*=custom_54]').show();
    $(this).prev('.form-layout-compressed').find('tr[class*=custom_55]').show();
    $(this).prev('.form-layout-compressed').find('tr[class*=custom_56]').show();
    $(this).prev('.form-layout-compressed').find('tr[class*=custom_57]').show();
    $(this).hide();
  });
});
