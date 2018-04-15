CRM.$(function($) {
  $('div#wizard-steps').remove();
  $('div.crm-export-map-form-block h2').text('Select Fields to Export').css('padding-top', '12px');

  if ($('span.nyss-export-map-help').length == 0) {
    $('div.crm-export-map-form-block div.help').append('<span class="nyss-export-map-help"><p>Once you have selected all desired fields, click <strong>Export</strong> to generate and save the export file. Once you are finished, click <strong>Done</strong> to return to the search page.</p></span>');
  }
});
